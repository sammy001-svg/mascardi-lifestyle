<?php

declare(strict_types=1);

namespace App\Core;

final class Money
{
    public static function toCents(float|string $shillings): int
    {
        return (int) round(((float) $shillings) * 100);
    }

    public static function toShillings(int $cents): float
    {
        return $cents / 100;
    }

    /** Whole-shilling amount for M-Pesa, which rejects decimal amounts. */
    public static function toWholeShillings(int $cents): int
    {
        return (int) round($cents / 100);
    }

    public static function format(int $cents, string $currency = 'KES'): string
    {
        return $currency . ' ' . number_format($cents / 100, 2);
    }
}
