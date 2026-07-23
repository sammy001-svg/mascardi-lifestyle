<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }

    public static function verify(?string $submittedToken): bool
    {
        $sessionToken = $_SESSION[self::SESSION_KEY] ?? null;

        if (!is_string($sessionToken) || !is_string($submittedToken) || $submittedToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $submittedToken);
    }

    public static function verifyRequestOrFail(): void
    {
        $token = $_POST['_csrf'] ?? null;

        if (!self::verify($token)) {
            http_response_code(419);
            Logger::warning('CSRF verification failed for ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
            exit('Your session expired or the form was tampered with. Please go back and try again.');
        }
    }
}
