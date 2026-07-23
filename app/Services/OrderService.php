<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Customer;
use App\Models\Order;
use RuntimeException;

final class OrderService
{
    /**
     * Snapshots the current session cart into a real order + order_items,
     * upserts the customer record, and clears the cart. Pricing always comes
     * from the live product data in CartService::items(), never from client input.
     */
    public static function createFromCart(string $name, ?string $email, string $normalizedPhone, ?string $deliveryNotes): array
    {
        $items = CartService::items();
        if (empty($items)) {
            throw new RuntimeException('Your cart is empty.');
        }

        $customerId = Customer::upsert($name, $email, $normalizedPhone);
        $subtotalCents = array_sum(array_column($items, 'line_total_cents'));

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $orderId = Order::create($pdo, [
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $customerId,
                'customer_name' => $name,
                'customer_email' => $email,
                'customer_phone' => $normalizedPhone,
                'subtotal_cents' => $subtotalCents,
                'total_cents' => $subtotalCents,
                'delivery_notes' => $deliveryNotes,
            ]);

            foreach ($items as $item) {
                Order::addItem($pdo, $orderId, [
                    'product_id' => $item['product']['id'],
                    'product_name_snapshot' => $item['product']['name'],
                    'unit_price_cents_snapshot' => $item['product']['price_cents'],
                    'quantity' => $item['quantity'],
                    'line_total_cents' => $item['line_total_cents'],
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        CartService::clear();

        $order = Order::find($orderId);

        return [
            'order_id' => $orderId,
            'order_number' => $order['order_number'],
            'total_cents' => $subtotalCents,
        ];
    }
}
