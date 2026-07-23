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

    /**
     * Normalizes a multi-file <input type="file" name="key[]" multiple> submission
     * (PHP's $_FILES[$key] is parallel arrays) into a flat list of single-file
     * arrays shaped like a normal $_FILES entry, skipping empty slots.
     */
    public static function normalizeFiles(string $key): array
    {
        if (empty($_FILES[$key]) || !is_array($_FILES[$key]['name'] ?? null)) {
            return [];
        }

        $result = [];
        $count = count($_FILES[$key]['name']);
        for ($i = 0; $i < $count; $i++) {
            if (($_FILES[$key]['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $result[] = [
                'name' => $_FILES[$key]['name'][$i],
                'type' => $_FILES[$key]['type'][$i],
                'tmp_name' => $_FILES[$key]['tmp_name'][$i],
                'error' => $_FILES[$key]['error'][$i],
                'size' => $_FILES[$key]['size'][$i],
            ];
        }
        return $result;
    }
}
