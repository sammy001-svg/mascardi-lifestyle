<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Event
{
    public static function all(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM events';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY starts_at ASC';
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function upcoming(int $limit = 6): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM events WHERE is_active = 1 AND starts_at >= NOW() ORDER BY starts_at ASC LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM events WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM events WHERE slug = :slug AND is_active = 1 LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM events WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO events (slug, title, description, image_path, event_type, ticket_price_cents, capacity, venue, starts_at, ends_at, is_active)
             VALUES (:slug, :title, :description, :image_path, :event_type, :ticket_price_cents, :capacity, :venue, :starts_at, :ends_at, :is_active)'
        );
        $stmt->execute([
            'slug' => $data['slug'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'image_path' => $data['image_path'] ?: null,
            'event_type' => $data['event_type'],
            'ticket_price_cents' => $data['event_type'] === 'paid' ? $data['ticket_price_cents'] : 0,
            'capacity' => $data['capacity'] !== null ? $data['capacity'] : null,
            'venue' => $data['venue'] ?: null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?: null,
            'is_active' => $data['is_active'] ?? 1,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $sql = 'UPDATE events SET slug = :slug, title = :title, description = :description, event_type = :event_type,
                ticket_price_cents = :ticket_price_cents, capacity = :capacity, venue = :venue,
                starts_at = :starts_at, ends_at = :ends_at, is_active = :is_active';
        $params = [
            'slug' => $data['slug'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'event_type' => $data['event_type'],
            'ticket_price_cents' => $data['event_type'] === 'paid' ? $data['ticket_price_cents'] : 0,
            'capacity' => $data['capacity'] !== null ? $data['capacity'] : null,
            'venue' => $data['venue'] ?: null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?: null,
            'is_active' => $data['is_active'] ?? 1,
            'id' => $id,
        ];

        if (array_key_exists('image_path', $data) && $data['image_path'] !== null) {
            $sql .= ', image_path = :image_path';
            $params['image_path'] = $data['image_path'];
        }

        $sql .= ' WHERE id = :id';

        Database::connection()->prepare($sql)->execute($params);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM events WHERE id = :id')->execute(['id' => $id]);
    }

    /**
     * Locks the event row (within the caller's transaction) and returns the
     * capacity along with how many seats confirmed registrations already hold.
     * capacity === null means unlimited.
     */
    public static function lockForCapacityCheck(PDO $pdo, int $eventId): array
    {
        $stmt = $pdo->prepare('SELECT capacity FROM events WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $eventId]);
        $capacity = $stmt->fetchColumn();

        $usedStmt = $pdo->prepare(
            "SELECT COALESCE(SUM(quantity), 0) FROM event_registrations WHERE event_id = :id AND status = 'confirmed'"
        );
        $usedStmt->execute(['id' => $eventId]);
        $used = (int) $usedStmt->fetchColumn();

        return [
            'capacity' => $capacity !== false && $capacity !== null ? (int) $capacity : null,
            'used' => $used,
        ];
    }
}
