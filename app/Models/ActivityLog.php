<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ActivityLog
{
    public static function record(?int $adminUserId, string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void
    {
        Database::connection()->prepare(
            'INSERT INTO activity_log (admin_user_id, action, entity_type, entity_id, details)
             VALUES (:admin_user_id, :action, :entity_type, :entity_id, :details)'
        )->execute([
            'admin_user_id' => $adminUserId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details,
        ]);
    }

    public static function recent(int $limit = 20): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT al.*, au.name AS admin_name FROM activity_log al
             LEFT JOIN admin_users au ON au.id = al.admin_user_id
             ORDER BY al.created_at DESC LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
