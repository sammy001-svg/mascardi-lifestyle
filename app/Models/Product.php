<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Product
{
    public static function all(bool $activeOnly = false): array
    {
        $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                       (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS primary_image
                FROM products p
                LEFT JOIN product_categories c ON c.id = p.category_id';
        if ($activeOnly) {
            $sql .= ' WHERE p.is_active = 1';
        }
        $sql .= ' ORDER BY p.is_featured DESC, p.created_at DESC';
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function featured(int $limit = 12): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT p.*,
                    (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS primary_image
             FROM products p
             WHERE p.is_active = 1
             ORDER BY p.is_featured DESC, p.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM products WHERE slug = :slug AND is_active = 1 LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM products WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function images(int $productId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM product_images WHERE product_id = :id ORDER BY is_primary DESC, sort_order ASC'
        );
        $stmt->execute(['id' => $productId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO products (category_id, sku, name, slug, description, price_cents, compare_at_price_cents, stock_quantity, is_featured, is_active)
             VALUES (:category_id, :sku, :name, :slug, :description, :price_cents, :compare_at_price_cents, :stock_quantity, :is_featured, :is_active)'
        );
        $stmt->execute([
            'category_id' => $data['category_id'] ?: null,
            'sku' => $data['sku'] ?: null,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?: null,
            'price_cents' => $data['price_cents'],
            'compare_at_price_cents' => $data['compare_at_price_cents'] ?: null,
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'is_featured' => $data['is_featured'] ?? 0,
            'is_active' => $data['is_active'] ?? 1,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::connection()->prepare(
            'UPDATE products SET category_id = :category_id, sku = :sku, name = :name, slug = :slug,
                description = :description, price_cents = :price_cents, compare_at_price_cents = :compare_at_price_cents,
                stock_quantity = :stock_quantity, is_featured = :is_featured, is_active = :is_active
             WHERE id = :id'
        )->execute([
            'category_id' => $data['category_id'] ?: null,
            'sku' => $data['sku'] ?: null,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?: null,
            'price_cents' => $data['price_cents'],
            'compare_at_price_cents' => $data['compare_at_price_cents'] ?: null,
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'is_featured' => $data['is_featured'] ?? 0,
            'is_active' => $data['is_active'] ?? 1,
            'id' => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM products WHERE id = :id')->execute(['id' => $id]);
    }

    public static function addImage(int $productId, string $imagePath, bool $isPrimary = false, int $sortOrder = 0): int
    {
        $pdo = Database::connection();

        if ($isPrimary) {
            $pdo->prepare('UPDATE product_images SET is_primary = 0 WHERE product_id = :id')->execute(['id' => $productId]);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO product_images (product_id, image_path, sort_order, is_primary) VALUES (:product_id, :image_path, :sort_order, :is_primary)'
        );
        $stmt->execute([
            'product_id' => $productId,
            'image_path' => $imagePath,
            'sort_order' => $sortOrder,
            'is_primary' => $isPrimary ? 1 : 0,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function findImage(int $imageId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM product_images WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $imageId]);
        return $stmt->fetch() ?: null;
    }

    public static function deleteImage(int $imageId): void
    {
        Database::connection()->prepare('DELETE FROM product_images WHERE id = :id')->execute(['id' => $imageId]);
    }

    public static function makeImagePrimary(int $productId, int $imageId): void
    {
        $pdo = Database::connection();
        $pdo->prepare('UPDATE product_images SET is_primary = 0 WHERE product_id = :id')->execute(['id' => $productId]);
        $pdo->prepare('UPDATE product_images SET is_primary = 1 WHERE id = :id AND product_id = :product_id')
            ->execute(['id' => $imageId, 'product_id' => $productId]);
    }

    /**
     * Locks the product row and decrements stock within the caller's transaction.
     * Returns false (without decrementing) if there isn't enough stock left —
     * the caller is expected to flag the order for manual review in that case.
     */
    public static function decrementStockForUpdate(PDO $pdo, int $productId, int $quantity): bool
    {
        $stmt = $pdo->prepare('SELECT stock_quantity FROM products WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $productId]);
        $current = $stmt->fetchColumn();

        if ($current === false || (int) $current < $quantity) {
            return false;
        }

        $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :id')
            ->execute(['qty' => $quantity, 'id' => $productId]);

        return true;
    }
}
