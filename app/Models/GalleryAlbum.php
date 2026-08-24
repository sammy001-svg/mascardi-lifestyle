<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class GalleryAlbum
{
    public static function all(bool $activeOnly = false): array
    {
        $sql = 'SELECT a.*,
                    (SELECT COUNT(*) FROM gallery_images gi WHERE gi.album_id = a.id) AS image_count
                FROM gallery_albums a';
        if ($activeOnly) {
            $sql .= ' WHERE a.is_active = 1';
        }
        $sql .= ' ORDER BY a.sort_order ASC, a.created_at DESC';
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT a.*,
                    (SELECT COUNT(*) FROM gallery_images gi WHERE gi.album_id = a.id) AS image_count
             FROM gallery_albums a WHERE a.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT a.*,
                    (SELECT COUNT(*) FROM gallery_images gi WHERE gi.album_id = a.id) AS image_count
             FROM gallery_albums a WHERE a.slug = :slug AND a.is_active = 1 LIMIT 1'
        );
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql    = 'SELECT COUNT(*) FROM gallery_albums WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function images(int $albumId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM gallery_images WHERE album_id = :album_id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['album_id' => $albumId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO gallery_albums (slug, name, description, cover_image_path, sort_order, is_active)
             VALUES (:slug, :name, :description, :cover_image_path, :sort_order, :is_active)'
        );
        $stmt->execute([
            'slug'             => $data['slug'],
            'name'             => $data['name'],
            'description'      => $data['description'] ?: null,
            'cover_image_path' => $data['cover_image_path'] ?: null,
            'sort_order'       => (int) ($data['sort_order'] ?? 0),
            'is_active'        => (int) ($data['is_active'] ?? 1),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $sql = 'UPDATE gallery_albums SET
                    slug = :slug, name = :name, description = :description,
                    sort_order = :sort_order, is_active = :is_active';
        $params = [
            'slug'       => $data['slug'],
            'name'       => $data['name'],
            'description'=> $data['description'] ?: null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active'  => (int) ($data['is_active'] ?? 1),
            'id'         => $id,
        ];

        if (array_key_exists('cover_image_path', $data) && $data['cover_image_path'] !== null) {
            $sql .= ', cover_image_path = :cover_image_path';
            $params['cover_image_path'] = $data['cover_image_path'];
        }

        $sql .= ' WHERE id = :id';
        Database::connection()->prepare($sql)->execute($params);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM gallery_albums WHERE id = :id')->execute(['id' => $id]);
    }
}
