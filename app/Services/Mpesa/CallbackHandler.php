<?php

declare(strict_types=1);

namespace App\Services\Mpesa;

use App\Core\Database;
use App\Core\Logger;
use App\Models\MpesaTransaction;
use App\Models\Order;
use App\Models\Product;

/**
 * Resolves an STK push outcome (from either the Safaricom webhook or an
 * active status query) into the linked order/ticket. Idempotent: safe to
 * call more than once for the same checkout_request_id — Safaricom retries
 * callbacks, and the "check status" button can race with a webhook that's
 * already landed.
 */
final class CallbackHandler
{
    /** Entry point for the public Safaricom webhook (public/mpesa/callback.php). */
    public static function handleWebhook(string $rawJson): void
    {
        $payload = json_decode($rawJson, true);
        $stkCallback = $payload['Body']['stkCallback'] ?? null;

        if (!is_array($stkCallback) || empty($stkCallback['CheckoutRequestID'])) {
            Logger::warning('Malformed M-Pesa callback payload: ' . $rawJson);
            return;
        }

        $resultCode = (int) ($stkCallback['ResultCode'] ?? 1);
        $resultDesc = (string) ($stkCallback['ResultDesc'] ?? '');
        $receiptNumber = null;

        if ($resultCode === 0) {
            foreach ($stkCallback['CallbackMetadata']['Item'] ?? [] as $item) {
                if (($item['Name'] ?? '') === 'MpesaReceiptNumber') {
                    $receiptNumber = (string) ($item['Value'] ?? '');
                }
            }
        }

        self::resolve((string) $stkCallback['CheckoutRequestID'], $resultCode, $resultDesc, $receiptNumber, $rawJson);
    }

    /** Shared resolution path used by both the webhook and StkQueryService. */
    public static function resolve(string $checkoutRequestId, int $resultCode, string $resultDesc, ?string $receiptNumber, ?string $rawPayload): void
    {
        $mpesaTx = MpesaTransaction::findByCheckoutRequestId($checkoutRequestId);
        if (!$mpesaTx) {
            Logger::warning('M-Pesa callback for unknown CheckoutRequestID: ' . $checkoutRequestId);
            return;
        }

        $status = $resultCode === 0 ? 'success' : 'failed';
        $pdo = Database::connection();

        $pdo->beginTransaction();
        try {
            $applied = MpesaTransaction::markResolved($pdo, (int) $mpesaTx['id'], $status, $receiptNumber, (string) $resultCode, $resultDesc, $rawPayload);

            if (!$applied) {
                // Already resolved by an earlier callback/query — nothing further to do.
                $pdo->commit();
                return;
            }

            if ($mpesaTx['transaction_type'] === 'order' && $mpesaTx['order_id']) {
                self::resolveOrder($pdo, (int) $mpesaTx['order_id'], $status);
            }

            // Event ticket resolution lands in Phase 4 alongside the events/ticketing feature.

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Logger::error('Failed to resolve M-Pesa transaction ' . $checkoutRequestId . ': ' . $e->getMessage());
            throw $e;
        }
    }

    private static function resolveOrder(\PDO $pdo, int $orderId, string $paymentStatus): void
    {
        if ($paymentStatus !== 'success') {
            Order::updatePaymentStatus($pdo, $orderId, 'failed');
            return;
        }

        $items = Order::items($orderId);
        $oversold = false;

        foreach ($items as $item) {
            if ($item['product_id'] === null) {
                continue;
            }
            $ok = Product::decrementStockForUpdate($pdo, (int) $item['product_id'], (int) $item['quantity']);
            if (!$ok) {
                $oversold = true;
            }
        }

        Order::updatePaymentStatus($pdo, $orderId, 'paid', 'paid');

        if ($oversold) {
            Order::setAdminNotes($orderId, 'Oversold at payment time — one or more items exceeded available stock. Needs manual review.');
        }
    }
}
