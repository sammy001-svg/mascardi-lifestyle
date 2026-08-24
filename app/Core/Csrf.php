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
        // When an upload exceeds post_max_size, PHP discards the ENTIRE POST body
        // — $_POST and $_FILES both come back empty even though data was sent.
        // That otherwise surfaces as a misleading "session expired" CSRF error,
        // so detect it and report the real cause: the upload was too large.
        if (
            ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
            && empty($_POST) && empty($_FILES)
            && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
        ) {
            http_response_code(413);
            Logger::warning('POST exceeded post_max_size (' . ini_get('post_max_size') . ') for ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
            exit('Your upload was too large. The combined size of the selected images exceeded the server limit — please upload fewer or smaller images at a time.');
        }

        $token = $_POST['_csrf'] ?? null;

        if (!self::verify($token)) {
            http_response_code(419);
            Logger::warning('CSRF verification failed for ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
            exit('Your session expired or the form was tampered with. Please go back and try again.');
        }
    }
}
