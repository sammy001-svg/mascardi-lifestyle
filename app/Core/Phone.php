<?php

declare(strict_types=1);

namespace App\Core;

final class Phone
{
    /**
     * Normalizes a Kenyan phone number to Safaricom's expected 2547XXXXXXXX /
     * 2541XXXXXXXX MSISDN format. Accepts 07XXXXXXXX, 7XXXXXXXX, 01XXXXXXXX,
     * +2547XXXXXXXX, 2547XXXXXXXX. Returns null if the input can't be parsed.
     */
    public static function normalizeKenyan(string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if (str_starts_with($digits, '254') && strlen($digits) === 12) {
            return $digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '254' . substr($digits, 1);
        }

        if (strlen($digits) === 9 && (str_starts_with($digits, '7') || str_starts_with($digits, '1'))) {
            return '254' . $digits;
        }

        return null;
    }

    public static function isValidKenyan(string $raw): bool
    {
        return self::normalizeKenyan($raw) !== null;
    }
}
