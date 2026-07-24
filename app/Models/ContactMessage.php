<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class ContactMessage
{
    public static function all(string $filter = 'all'): array
    {
        $sql = 'SELECT * FROM contact_messages';
        if ($filter === 'unread') {
            $sql .= ' WHERE is_read = 0';
        } elseif ($filter === 'read') {
            $sql .= ' WHERE is_read = 1';
        }
        $sql .= ' ORDER BY created_at DESC, id DESC';
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM contact_messages WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO contact_messages (name, email, phone, subject, message, ip_address)
             VALUES (:name, :email, :phone, :subject, :message, :ip_address)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'subject' => $data['subject'] ?: null,
            'message' => $data['message'],
            'ip_address' => $data['ip_address'] ?: null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function markRead(int $id, bool $read = true): void
    {
        Database::connection()
            ->prepare('UPDATE contact_messages SET is_read = :read WHERE id = :id')
            ->execute(['read' => $read ? 1 : 0, 'id' => $id]);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM contact_messages WHERE id = :id')->execute(['id' => $id]);
    }

    public static function unreadCount(): int
    {
        return (int) Database::connection()
            ->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')
            ->fetchColumn();
    }
}
