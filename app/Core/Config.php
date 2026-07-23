<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static ?array $config = null;
    private static ?array $mpesa = null;

    public static function get(?string $key = null): mixed
    {
        if (self::$config === null) {
            $path = dirname(__DIR__, 2) . '/config/config.php';
            if (!file_exists($path)) {
                throw new \RuntimeException('config/config.php not found. Copy config/config.example.php and fill in real values.');
            }
            self::$config = require $path;
        }

        if ($key === null) {
            return self::$config;
        }

        return self::$config[$key] ?? null;
    }

    public static function mpesa(?string $key = null): mixed
    {
        if (self::$mpesa === null) {
            $path = dirname(__DIR__, 2) . '/config/mpesa.php';
            if (!file_exists($path)) {
                throw new \RuntimeException('config/mpesa.php not found. Copy config/mpesa.example.php and fill in real values.');
            }
            self::$mpesa = require $path;
        }

        if ($key === null) {
            return self::$mpesa;
        }

        return self::$mpesa[$key] ?? null;
    }

    public static function isProduction(): bool
    {
        return self::get('app_env') === 'production';
    }
}
