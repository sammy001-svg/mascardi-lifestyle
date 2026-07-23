<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Pillar
{
    public static function all(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM pillars';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY sort_order ASC, name ASC';
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM pillars WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM pillars WHERE slug = :slug';
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
            'INSERT INTO pillars (slug, name, description, image_path, link_url, sort_order, is_active)
             VALUES (:slug, :name, :description, :image_path, :link_url, :sort_order, :is_active)'
        );
        $stmt->execute([
            'slug' => $data['slug'],
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
            'image_path' => $data['image_path'] ?: null,
            'link_url' => $data['link_url'] ?: null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? 1,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $sql = 'UPDATE pillars SET slug = :slug, name = :name, description = :description,
                link_url = :link_url, sort_order = :sort_order, is_active = :is_active';
        $params = [
            'slug' => $data['slug'],
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
            'link_url' => $data['link_url'] ?: null,
            'sort_order' => $data['sort_order'] ?? 0,
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
        Database::connection()->prepare('DELETE FROM pillars WHERE id = :id')->execute(['id' => $id]);
    }
}
