<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class BlogCategory
{
    public static function all(): array
    {
        return Database::connection()
            ->query('SELECT * FROM blog_categories ORDER BY sort_order ASC, name ASC')
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM blog_categories WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM blog_categories WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql    = 'SELECT COUNT(*) FROM blog_categories WHERE slug = :slug';
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
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO blog_categories (name, slug, sort_order) VALUES (:name, :slug, :sort_order)'
        );
        $stmt->execute([
            'name'       => $data['name'],
            'slug'       => $data['slug'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::connection()->prepare(
            'UPDATE blog_categories SET name = :name, slug = :slug, sort_order = :sort_order WHERE id = :id'
        )->execute([
            'name'       => $data['name'],
            'slug'       => $data['slug'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'id'         => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM blog_categories WHERE id = :id')->execute(['id' => $id]);
    }
}
