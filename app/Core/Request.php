<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        // Strip a sub-directory prefix if the app isn't served from the domain root.
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }
        $uri = '/' . ltrim($uri, '/');
        return rtrim($uri, '/') === '' ? '/' : rtrim($uri, '/');
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    public static function input(string $key, mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    /** Every requested key defaults to '' (not null) when absent from the
     *  request — controllers consistently treat "field missing" the same as
     *  "field submitted empty" (e.g. optional selects/inputs), so this keeps
     *  every ?: / !== '' check downstream safe from null-related TypeErrors. */
    public static function all(array $keys): array
    {
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = self::input($key, '');
        }
        return $out;
    }

    public static function query(string $key, mixed $default = null): mixed
    {
        $value = $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    public static function file(string $key): ?array
    {
        if (empty($_FILES[$key]) || ($_FILES[$key]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        return $_FILES[$key];
    }

    public static function intInput(string $key, int $default = 0): int
    {
        return (int) self::input($key, $default);
    }

    public static function boolInput(string $key): bool
    {
        $value = self::input($key);
        return $value === '1' || $value === 1 || $value === 'on' || $value === true;
    }
}
