<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class GalleryImage
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM gallery_images WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function allForAlbum(int $albumId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM gallery_images WHERE album_id = :album_id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['album_id' => $albumId]);
        return $stmt->fetchAll();
    }

    public static function create(int $albumId, string $imagePath, ?string $caption, int $sortOrder): int
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO gallery_images (album_id, image_path, caption, sort_order)
             VALUES (:album_id, :image_path, :caption, :sort_order)'
        );
        $stmt->execute([
            'album_id'   => $albumId,
            'image_path' => $imagePath,
            'caption'    => $caption ?: null,
            'sort_order' => $sortOrder,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM gallery_images WHERE id = :id')->execute(['id' => $id]);
    }

    public static function countForAlbum(int $albumId): int
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM gallery_images WHERE album_id = :album_id');
        $stmt->execute(['album_id' => $albumId]);
        return (int) $stmt->fetchColumn();
    }
}
