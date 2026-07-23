<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Models\Product;

/**
 * Session-based cart. Only product IDs + quantities are stored in the
 * session; prices/names/stock are always re-read live from the database so
 * the cart never trusts stale or client-tampered pricing.
 */
final class CartService
{
    private const SESSION_KEY = 'cart';

    public static function add(int $productId, int $quantity = 1): void
    {
        $cart = self::raw();
        $cart[$productId] = ($cart[$productId] ?? 0) + max(1, $quantity);
        Session::set(self::SESSION_KEY, $cart);
    }

    public static function updateQuantity(int $productId, int $quantity): void
    {
        $cart = self::raw();
        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }
        Session::set(self::SESSION_KEY, $cart);
    }

    public static function remove(int $productId): void
    {
        $cart = self::raw();
        unset($cart[$productId]);
        Session::set(self::SESSION_KEY, $cart);
    }

    public static function clear(): void
    {
        Session::set(self::SESSION_KEY, []);
    }

    private static function raw(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    /**
     * Resolved cart line items with live product data. Silently drops any
     * product that's been deactivated/deleted since it was added, and clamps
     * quantity to available stock.
     */
    public static function items(): array
    {
        $cart = self::raw();
        $items = [];

        foreach ($cart as $productId => $quantity) {
            $product = Product::find((int) $productId);
            if (!$product || !$product['is_active']) {
                continue;
            }

            $quantity = min((int) $quantity, max(0, (int) $product['stock_quantity']));
            if ($quantity <= 0) {
                continue;
            }

            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'line_total_cents' => $quantity * (int) $product['price_cents'],
            ];
        }

        return $items;
    }

    public static function subtotalCents(): int
    {
        return array_sum(array_column(self::items(), 'line_total_cents'));
    }

    public static function count(): int
    {
        return array_sum(array_column(self::items(), 'quantity'));
    }

    public static function isEmpty(): bool
    {
        return self::count() === 0;
    }
}
