<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal dependency-free .env loader. Supports KEY=VALUE lines, blank
 * lines, # comments, and single/double-quoted values. No interpolation.
 * Values loaded here take precedence over real process environment vars
 * only within Env::get() — nothing is written to putenv()/$_ENV.
 */
final class Env
{
    /** @var array<string, string> */
    private static array $values = [];

    public static function load(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return; // .env is optional — config files fall back to defaults
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $eq = strpos($line, '=');
            if ($eq === false) {
                continue;
            }

            $key = trim(substr($line, 0, $eq));
            $value = trim(substr($line, $eq + 1));

            if ($key === '') {
                continue;
            }

            // Strip surrounding quotes if present.
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            self::$values[$key] = $value;
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        if (array_key_exists($key, self::$values)) {
            return self::$values[$key];
        }

        $fromProcess = getenv($key);
        return $fromProcess !== false ? $fromProcess : $default;
    }

    public static function getInt(string $key, int $default): int
    {
        $value = self::get($key, (string) $default);
        return is_numeric($value) ? (int) $value : $default;
    }
}
