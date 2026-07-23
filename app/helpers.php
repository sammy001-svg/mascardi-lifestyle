<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Session;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function upload_url(?string $path): ?string
{
    if (!$path) {
        return null;
    }
    return '/uploads/' . ltrim($path, '/');
}

function site_url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function admin_url(string $module, string $action = 'index', array $extra = []): string
{
    $params = array_merge(['module' => $module, 'action' => $action], $extra);
    return '/admin/index.php?' . http_build_query($params);
}

function csrf_field(): string
{
    return Csrf::field();
}

/**
 * Reads and clears a previous form submission's values, for repopulating
 * a form after a validation error redirect.
 */
function old(string $key, string $default = ''): string
{
    static $bag = null;
    if ($bag === null) {
        $bag = Session::get('_old', []);
        Session::remove('_old');
    }
    return e($bag[$key] ?? $default);
}

/**
 * Reads and clears validation errors for a given field, set before a
 * validation-failure redirect.
 */
function field_errors(string $key): array
{
    static $bag = null;
    if ($bag === null) {
        $bag = Session::get('_errors', []);
        Session::remove('_errors');
    }
    return $bag[$key] ?? [];
}

function has_field_error(string $key): bool
{
    return !empty(field_errors($key));
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

function flash_message(string $key): ?string
{
    return Session::flash($key);
}

function redirect_with_errors(string $url, array $errors, array $old = []): never
{
    Session::set('_errors', $errors);
    Session::set('_old', $old);
    App\Core\Response::redirect($url);
}
