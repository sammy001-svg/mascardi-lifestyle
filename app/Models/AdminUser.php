<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class AdminUser
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM admin_users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM admin_users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(): array
    {
        return Database::connection()
            ->query('SELECT id, name, email, role, is_active, last_login_at, created_at FROM admin_users ORDER BY name')
            ->fetchAll();
    }

    public static function registerFailedLogin(int $id, int $maxAttempts, int $lockoutMinutes): void
    {
        $pdo = Database::connection();
        $pdo->prepare('UPDATE admin_users SET failed_login_attempts = failed_login_attempts + 1 WHERE id = :id')
            ->execute(['id' => $id]);

        $stmt = $pdo->prepare('SELECT failed_login_attempts FROM admin_users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $attempts = (int) $stmt->fetchColumn();

        if ($attempts >= $maxAttempts) {
            $lockUntil = date('Y-m-d H:i:s', time() + $lockoutMinutes * 60);
            $pdo->prepare('UPDATE admin_users SET locked_until = :locked_until WHERE id = :id')
                ->execute(['locked_until' => $lockUntil, 'id' => $id]);
        }
    }

    public static function registerSuccessfulLogin(int $id): void
    {
        Database::connection()
            ->prepare('UPDATE admin_users SET failed_login_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = :id')
            ->execute(['id' => $id]);
    }

    public static function create(string $name, string $email, string $password, string $role): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO admin_users (name, email, password_hash, role, is_active) VALUES (:name, :email, :hash, :role, 1)'
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function updatePassword(int $id, string $password): void
    {
        Database::connection()
            ->prepare('UPDATE admin_users SET password_hash = :hash WHERE id = :id')
            ->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id]);
    }

    public static function setActive(int $id, bool $active): void
    {
        Database::connection()
            ->prepare('UPDATE admin_users SET is_active = :active WHERE id = :id')
            ->execute(['active' => $active ? 1 : 0, 'id' => $id]);
    }
}
