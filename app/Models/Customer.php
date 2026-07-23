<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Customer
{
    /** Upserts by normalized phone number and returns the customer id. */
    public static function upsert(string $name, ?string $email, string $normalizedPhone): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare('SELECT id FROM customers WHERE phone = :phone LIMIT 1');
        $stmt->execute(['phone' => $normalizedPhone]);
        $existingId = $stmt->fetchColumn();

        if ($existingId !== false) {
            $pdo->prepare('UPDATE customers SET name = :name, email = :email WHERE id = :id')->execute([
                'name' => $name,
                'email' => $email ?: null,
                'id' => $existingId,
            ]);
            return (int) $existingId;
        }

        $stmt = $pdo->prepare('INSERT INTO customers (name, email, phone) VALUES (:name, :email, :phone)');
        $stmt->execute([
            'name' => $name,
            'email' => $email ?: null,
            'phone' => $normalizedPhone,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM customers WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
