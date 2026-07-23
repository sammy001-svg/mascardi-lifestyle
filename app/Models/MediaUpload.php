<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class MediaUpload
{
    public static function all(?string $search = null, int $limit = 60, int $offset = 0): array
    {
        $pdo = Database::connection();
        $sql = 'SELECT * FROM media_uploads';
        $params = [];
        if ($search !== null && $search !== '') {
            $sql .= ' WHERE original_filename LIKE :search';
            $params['search'] = '%' . $search . '%';
        }
        $sql .= ' ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset';

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count(?string $search = null): int
    {
        $pdo = Database::connection();
        $sql = 'SELECT COUNT(*) FROM media_uploads';
        $params = [];
        if ($search !== null && $search !== '') {
            $sql .= ' WHERE original_filename LIKE :search';
            $params['search'] = '%' . $search . '%';
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM media_uploads WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function record(string $filePath, string $originalFilename, string $mimeType, int $sizeBytes, ?int $uploadedBy): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO media_uploads (file_path, original_filename, mime_type, size_bytes, uploaded_by)
             VALUES (:file_path, :original_filename, :mime_type, :size_bytes, :uploaded_by)'
        );
        $stmt->execute([
            'file_path' => $filePath,
            'original_filename' => $originalFilename,
            'mime_type' => $mimeType,
            'size_bytes' => $sizeBytes,
            'uploaded_by' => $uploadedBy,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function deleteByPath(string $filePath): void
    {
        Database::connection()->prepare('DELETE FROM media_uploads WHERE file_path = :path')->execute(['path' => $filePath]);
    }

    /**
     * Every place a file_path string can be referenced today. Four plain
     * SELECTs rather than a UNION: Database disables PDO::ATTR_EMULATE_PREPARES,
     * and native prepared statements can't bind the same named placeholder
     * more than once in a single query. Admin-only, cold-path action.
     */
    public static function usages(string $filePath): array
    {
        $pdo = Database::connection();
        $usages = [];

        $stmt = $pdo->prepare('SELECT id, name FROM pillars WHERE image_path = :path');
        $stmt->execute(['path' => $filePath]);
        foreach ($stmt->fetchAll() as $row) {
            $usages[] = ['type' => 'Pillar', 'id' => (int) $row['id'], 'label' => $row['name']];
        }

        $stmt = $pdo->prepare('SELECT id, name FROM partners WHERE logo_path = :path');
        $stmt->execute(['path' => $filePath]);
        foreach ($stmt->fetchAll() as $row) {
            $usages[] = ['type' => 'Partner', 'id' => (int) $row['id'], 'label' => $row['name']];
        }

        $stmt = $pdo->prepare(
            'SELECT p.id, p.name FROM product_images pi
             JOIN products p ON p.id = pi.product_id
             WHERE pi.image_path = :path'
        );
        $stmt->execute(['path' => $filePath]);
        foreach ($stmt->fetchAll() as $row) {
            $usages[] = ['type' => 'Product photo', 'id' => (int) $row['id'], 'label' => $row['name']];
        }

        $stmt = $pdo->prepare('SELECT id, title FROM events WHERE image_path = :path');
        $stmt->execute(['path' => $filePath]);
        foreach ($stmt->fetchAll() as $row) {
            $usages[] = ['type' => 'Event', 'id' => (int) $row['id'], 'label' => $row['title']];
        }

        return $usages;
    }
}
