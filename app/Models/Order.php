<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Order
{
    public static function generateOrderNumber(): string
    {
        return 'MSC-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    public static function create(PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO orders (order_number, customer_id, customer_name, customer_email, customer_phone,
                status, payment_method, payment_status, subtotal_cents, total_cents, currency, delivery_notes)
             VALUES (:order_number, :customer_id, :customer_name, :customer_email, :customer_phone,
                :status, :payment_method, :payment_status, :subtotal_cents, :total_cents, :currency, :delivery_notes)'
        );
        $stmt->execute([
            'order_number' => $data['order_number'],
            'customer_id' => $data['customer_id'],
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?: null,
            'customer_phone' => $data['customer_phone'],
            'status' => $data['status'] ?? 'pending_payment',
            'payment_method' => 'mpesa',
            'payment_status' => $data['payment_status'] ?? 'unpaid',
            'subtotal_cents' => $data['subtotal_cents'],
            'total_cents' => $data['total_cents'],
            'currency' => 'KES',
            'delivery_notes' => $data['delivery_notes'] ?: null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function addItem(PDO $pdo, int $orderId, array $item): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name_snapshot, unit_price_cents_snapshot, quantity, line_total_cents)
             VALUES (:order_id, :product_id, :product_name_snapshot, :unit_price_cents_snapshot, :quantity, :line_total_cents)'
        );
        $stmt->execute([
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'product_name_snapshot' => $item['product_name_snapshot'],
            'unit_price_cents_snapshot' => $item['unit_price_cents_snapshot'],
            'quantity' => $item['quantity'],
            'line_total_cents' => $item['line_total_cents'],
        ]);
    }

    public static function all(?string $status = null): array
    {
        $sql = 'SELECT * FROM orders';
        $params = [];
        if ($status) {
            $sql .= ' WHERE status = :status';
            $params['status'] = $status;
        }
        $sql .= ' ORDER BY created_at DESC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByOrderNumber(string $orderNumber): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM orders WHERE order_number = :n LIMIT 1');
        $stmt->execute(['n' => $orderNumber]);
        return $stmt->fetch() ?: null;
    }

    public static function items(int $orderId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM order_items WHERE order_id = :id');
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $id, string $status): void
    {
        Database::connection()->prepare('UPDATE orders SET status = :status WHERE id = :id')
            ->execute(['status' => $status, 'id' => $id]);
    }

    public static function updatePaymentStatus(PDO $pdo, int $id, string $paymentStatus, ?string $orderStatus = null): void
    {
        if ($orderStatus !== null) {
            $pdo->prepare('UPDATE orders SET payment_status = :ps, status = :os WHERE id = :id')
                ->execute(['ps' => $paymentStatus, 'os' => $orderStatus, 'id' => $id]);
        } else {
            $pdo->prepare('UPDATE orders SET payment_status = :ps WHERE id = :id')
                ->execute(['ps' => $paymentStatus, 'id' => $id]);
        }
    }

    public static function setAdminNotes(int $id, string $notes): void
    {
        Database::connection()->prepare('UPDATE orders SET admin_notes = :notes WHERE id = :id')
            ->execute(['notes' => $notes, 'id' => $id]);
    }

    public static function counts(): array
    {
        $rows = Database::connection()->query('SELECT status, COUNT(*) AS c FROM orders GROUP BY status')->fetchAll();
        $out = [];
        foreach ($rows as $row) {
            $out[$row['status']] = (int) $row['c'];
        }
        return $out;
    }
}
