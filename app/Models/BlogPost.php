<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class BlogPost
{
    public static function all(): array
    {
        return Database::connection()->query(
            'SELECT p.*, c.name AS category_name, u.name AS author_name
             FROM blog_posts p
             LEFT JOIN blog_categories c ON c.id = p.category_id
             LEFT JOIN admin_users u ON u.id = p.author_id
             ORDER BY p.created_at DESC'
        )->fetchAll();
    }

    /**
     * Published posts, paginated — for the public blog listing.
     */
    public static function published(int $limit = 9, int $offset = 0, ?int $categoryId = null): array
    {
        $sql    = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
                   FROM blog_posts p
                   LEFT JOIN blog_categories c ON c.id = p.category_id
                   WHERE p.status = \'published\' AND p.published_at <= NOW()';
        $params = [];

        if ($categoryId !== null) {
            $sql .= ' AND p.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        $sql .= ' ORDER BY p.published_at DESC LIMIT :limit OFFSET :offset';

        $stmt = Database::connection()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countPublished(?int $categoryId = null): int
    {
        $sql    = "SELECT COUNT(*) FROM blog_posts WHERE status = 'published' AND published_at <= NOW()";
        $params = [];
        if ($categoryId !== null) {
            $sql .= ' AND category_id = :category_id';
            $params['category_id'] = $categoryId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name
             FROM blog_posts p
             LEFT JOIN blog_categories c ON c.id = p.category_id
             LEFT JOIN admin_users u ON u.id = p.author_id
             WHERE p.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name
             FROM blog_posts p
             LEFT JOIN blog_categories c ON c.id = p.category_id
             LEFT JOIN admin_users u ON u.id = p.author_id
             WHERE p.slug = :slug AND p.status = 'published' AND p.published_at <= NOW()
             LIMIT 1"
        );
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql    = 'SELECT COUNT(*) FROM blog_posts WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Latest N published posts for homepage / sidebar widgets */
    public static function recent(int $limit = 3): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM blog_posts p
             LEFT JOIN blog_categories c ON c.id = p.category_id
             WHERE p.status = 'published' AND p.published_at <= NOW()
             ORDER BY p.published_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO blog_posts
                (category_id, author_id, slug, title, excerpt, body, cover_image_path, status, published_at)
             VALUES
                (:category_id, :author_id, :slug, :title, :excerpt, :body, :cover_image_path, :status, :published_at)'
        );
        $stmt->execute([
            'category_id'      => $data['category_id'] ?: null,
            'author_id'        => $data['author_id'] ?: null,
            'slug'             => $data['slug'],
            'title'            => $data['title'],
            'excerpt'          => $data['excerpt'] ?: null,
            'body'             => $data['body'] ?: null,
            'cover_image_path' => $data['cover_image_path'] ?: null,
            'status'           => $data['status'],
            'published_at'     => $data['published_at'] ?: null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $sql = 'UPDATE blog_posts SET
                    category_id = :category_id, author_id = :author_id,
                    slug = :slug, title = :title, excerpt = :excerpt,
                    body = :body, status = :status, published_at = :published_at';
        $params = [
            'category_id'  => $data['category_id'] ?: null,
            'author_id'    => $data['author_id'] ?: null,
            'slug'         => $data['slug'],
            'title'        => $data['title'],
            'excerpt'      => $data['excerpt'] ?: null,
            'body'         => $data['body'] ?: null,
            'status'       => $data['status'],
            'published_at' => $data['published_at'] ?: null,
            'id'           => $id,
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
        Database::connection()->prepare('DELETE FROM blog_posts WHERE id = :id')->execute(['id' => $id]);
    }
}
