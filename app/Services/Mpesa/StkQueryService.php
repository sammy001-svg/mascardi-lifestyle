<?php

declare(strict_types=1);

namespace App\Services\Mpesa;

use App\Core\Config;

/**
 * Actively asks Daraja for an STK push's outcome — used as a fallback when
 * the webhook callback hasn't arrived (network blip, or a user clicking
 * "Check status" while waiting). Resolves through the same idempotent path
 * as the webhook, so calling this after the webhook already landed is safe.
 */
final class StkQueryService
{
    public static function query(string $checkoutRequestId): array
    {
        $shortcode = (string) Config::mpesa('shortcode');
        $passkey = (string) Config::mpesa('passkey');
        $timestamp = date('YmdHis');
        $password = base64_encode($shortcode . $passkey . $timestamp);

        $result = DarajaClient::post('/mpesa/stkpushquery/v1/query', [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ]);

        $data = $result['data'];
        $resultCode = $data['ResultCode'] ?? null;

        // ResultCode 1032/1037-style "still processing" responses (or a non-200
        // HTTP status) mean we genuinely don't know yet — don't resolve the
        // transaction, just report back so the UI can keep waiting.
        if ($resultCode === null || $resultCode === '') {
            return ['known' => false, 'raw' => $data];
        }

        $resultDesc = (string) ($data['ResultDesc'] ?? $data['errorMessage'] ?? '');
        CallbackHandler::resolve($checkoutRequestId, (int) $resultCode, $resultDesc, null, json_encode($data));

        return ['known' => true, 'result_code' => (int) $resultCode, 'raw' => $data];
    }
}
