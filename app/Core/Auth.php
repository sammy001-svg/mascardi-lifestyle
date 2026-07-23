<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\AdminUser;

final class Auth
{
    private const SESSION_KEY = 'admin_user_id';
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public static function attempt(string $email, string $password): bool|string
    {
        $user = AdminUser::findByEmail($email);

        if (!$user) {
            return 'Invalid email or password.';
        }

        if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            return 'This account is temporarily locked due to failed login attempts. Try again later.';
        }

        if ((int) $user['is_active'] !== 1) {
            return 'This account has been deactivated.';
        }

        if (!password_verify($password, $user['password_hash'])) {
            AdminUser::registerFailedLogin((int) $user['id'], self::MAX_FAILED_ATTEMPTS, self::LOCKOUT_MINUTES);
            return 'Invalid email or password.';
        }

        AdminUser::registerSuccessfulLogin((int) $user['id']);

        Session::regenerate();
        Session::set(self::SESSION_KEY, (int) $user['id']);

        return true;
    }

    public static function check(): bool
    {
        return Session::get(self::SESSION_KEY) !== null;
    }

    public static function user(): ?array
    {
        $id = Session::get(self::SESSION_KEY);
        if ($id === null) {
            return null;
        }
        return AdminUser::find((int) $id);
    }

    public static function logout(): void
    {
        Session::remove(self::SESSION_KEY);
        Session::destroy();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Response::redirect('/admin/index.php?module=auth&action=login');
        }
    }

    public static function requireRole(string $role): void
    {
        $user = self::user();
        if (!$user || $user['role'] !== $role) {
            http_response_code(403);
            exit('403 — You do not have permission to access this page.');
        }
    }
}
