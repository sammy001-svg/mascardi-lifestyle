<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class MpesaTransaction
{
    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO mpesa_transactions (transaction_type, order_id, event_registration_id, phone_number, amount_cents,
                merchant_request_id, checkout_request_id, status)
             VALUES (:transaction_type, :order_id, :event_registration_id, :phone_number, :amount_cents,
                :merchant_request_id, :checkout_request_id, :status)'
        );
        $stmt->execute([
            'transaction_type' => $data['transaction_type'],
            'order_id' => $data['order_id'] ?? null,
            'event_registration_id' => $data['event_registration_id'] ?? null,
            'phone_number' => $data['phone_number'],
            'amount_cents' => $data['amount_cents'],
            'merchant_request_id' => $data['merchant_request_id'] ?? null,
            'checkout_request_id' => $data['checkout_request_id'] ?? null,
            'status' => $data['status'] ?? 'initiated',
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function findByCheckoutRequestId(string $checkoutRequestId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM mpesa_transactions WHERE checkout_request_id = :id LIMIT 1');
        $stmt->execute(['id' => $checkoutRequestId]);
        return $stmt->fetch() ?: null;
    }

    public static function findLatestForOrder(int $orderId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM mpesa_transactions WHERE order_id = :id ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetch() ?: null;
    }

    /** Idempotent: only applies the update if the transaction is still pending/initiated. */
    public static function markResolved(PDO $pdo, int $id, string $status, ?string $receiptNumber, ?string $resultCode, ?string $resultDesc, ?string $rawPayload): bool
    {
        $stmt = $pdo->prepare("SELECT status FROM mpesa_transactions WHERE id = :id FOR UPDATE");
        $stmt->execute(['id' => $id]);
        $current = $stmt->fetchColumn();

        if ($current === false || !in_array($current, ['initiated', 'pending'], true)) {
            return false; // already resolved — ignore duplicate/retried callback
        }

        $pdo->prepare(
            'UPDATE mpesa_transactions SET status = :status, mpesa_receipt_number = :receipt,
                result_code = :result_code, result_desc = :result_desc, raw_callback_payload = :raw
             WHERE id = :id'
        )->execute([
            'status' => $status,
            'receipt' => $receiptNumber,
            'result_code' => $resultCode,
            'result_desc' => $resultDesc,
            'raw' => $rawPayload,
            'id' => $id,
        ]);

        return true;
    }

    public static function markPending(int $id): void
    {
        Database::connection()->prepare("UPDATE mpesa_transactions SET status = 'pending' WHERE id = :id AND status = 'initiated'")
            ->execute(['id' => $id]);
    }

    /** Records the identifiers Daraja returns once an STK push has actually been sent. */
    public static function recordStkPushSent(int $id, ?string $merchantRequestId, string $checkoutRequestId): void
    {
        Database::connection()->prepare(
            'UPDATE mpesa_transactions SET merchant_request_id = :mid, checkout_request_id = :cid, status = :status WHERE id = :id'
        )->execute([
            'mid' => $merchantRequestId,
            'cid' => $checkoutRequestId,
            'status' => 'pending',
            'id' => $id,
        ]);
    }
}
