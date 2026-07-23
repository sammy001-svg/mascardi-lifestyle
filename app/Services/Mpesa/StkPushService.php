<?php

declare(strict_types=1);

namespace App\Services\Mpesa;

use App\Core\Config;
use App\Models\MpesaTransaction;
use RuntimeException;

final class StkPushService
{
    /**
     * Initiates an STK push and records a mpesa_transactions row.
     *
     * @param string $transactionType 'order' | 'event_ticket'
     * @param int $amountCents Total in cents — converted to whole KES for Daraja, which rejects decimals.
     * @param string $normalizedPhone MSISDN in 2547XXXXXXXX format.
     * @param string $accountReference Shown to the customer, e.g. the order number.
     * @param string $transactionDesc Short description, e.g. "Mascardi Lifestyle order".
     * @return array{transaction_id:int, checkout_request_id:string}
     * @throws RuntimeException if Daraja rejects the request or is unreachable.
     */
    public static function initiate(
        string $transactionType,
        int $amountCents,
        string $normalizedPhone,
        string $accountReference,
        string $transactionDesc,
        ?int $orderId = null,
        ?int $eventRegistrationId = null
    ): array {
        $shortcode = (string) Config::mpesa('shortcode');
        $passkey = (string) Config::mpesa('passkey');
        $timestamp = date('YmdHis');
        $password = base64_encode($shortcode . $passkey . $timestamp);
        $wholeShillings = (int) round($amountCents / 100);
        $callbackUrl = rtrim((string) Config::mpesa('callback_base_url'), '/') . '/mpesa/callback.php';

        $body = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $wholeShillings,
            'PartyA' => $normalizedPhone,
            'PartyB' => $shortcode,
            'PhoneNumber' => $normalizedPhone,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => substr($accountReference, 0, 12),
            'TransactionDesc' => substr($transactionDesc, 0, 13),
        ];

        $transactionId = MpesaTransaction::create([
            'transaction_type' => $transactionType,
            'order_id' => $orderId,
            'event_registration_id' => $eventRegistrationId,
            'phone_number' => $normalizedPhone,
            'amount_cents' => $amountCents,
            'status' => 'initiated',
        ]);

        $result = DarajaClient::post('/mpesa/stkpush/v1/processrequest', $body);
        $data = $result['data'];

        if ($result['status'] !== 200 || empty($data['CheckoutRequestID'])) {
            $reason = $data['errorMessage'] ?? $data['ResponseDescription'] ?? 'The M-Pesa prompt could not be sent. Please try again.';
            throw new RuntimeException($reason);
        }

        $checkoutRequestId = (string) $data['CheckoutRequestID'];

        MpesaTransaction::recordStkPushSent($transactionId, $data['MerchantRequestID'] ?? null, $checkoutRequestId);

        return ['transaction_id' => $transactionId, 'checkout_request_id' => $checkoutRequestId];
    }
}
