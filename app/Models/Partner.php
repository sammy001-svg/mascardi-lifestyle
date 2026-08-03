<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Partner
{
    public static function all(bool $activeOnly = false): array
    {
        $sql = 'SELECT p.*, pl.name AS pillar_name FROM partners p LEFT JOIN pillars pl ON pl.id = p.pillar_id';
        if ($activeOnly) {
            $sql .= ' WHERE p.is_active = 1';
        }
        $sql .= ' ORDER BY p.sort_order ASC, p.name ASC';
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM partners WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function forPillar(int $pillarId, bool $activeOnly = true): array
    {
        $sql = 'SELECT * FROM partners WHERE pillar_id = :pillar_id';
        if ($activeOnly) {
            $sql .= ' AND is_active = 1';
        }
        $sql .= ' ORDER BY sort_order ASC, name ASC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['pillar_id' => $pillarId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO partners (pillar_id, name, logo_path, website_url, category, sort_order, is_active)
             VALUES (:pillar_id, :name, :logo_path, :website_url, :category, :sort_order, :is_active)'
        );
        $stmt->execute([
            'pillar_id' => $data['pillar_id'] ?: null,
            'name' => $data['name'],
            'logo_path' => $data['logo_path'],
            'website_url' => $data['website_url'] ?: null,
            'category' => $data['category'] ?: null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? 1,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $sql = 'UPDATE partners SET pillar_id = :pillar_id, name = :name, website_url = :website_url,
                category = :category, sort_order = :sort_order, is_active = :is_active';
        $params = [
            'pillar_id' => $data['pillar_id'] ?: null,
            'name' => $data['name'],
            'website_url' => $data['website_url'] ?: null,
            'category' => $data['category'] ?: null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? 1,
            'id' => $id,
        ];

        if (!empty($data['logo_path'])) {
            $sql .= ', logo_path = :logo_path';
            $params['logo_path'] = $data['logo_path'];
        }

        $sql .= ' WHERE id = :id';

        Database::connection()->prepare($sql)->execute($params);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM partners WHERE id = :id')->execute(['id' => $id]);
    }
}
