<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class EventRegistration
{
    public static function generateTicketCode(): string
    {
        return 'TKT-' . strtoupper(bin2hex(random_bytes(4)));
    }

    public static function create(PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO event_registrations (event_id, attendee_name, attendee_email, attendee_phone, quantity,
                status, payment_status, total_amount_cents, ticket_code)
             VALUES (:event_id, :attendee_name, :attendee_email, :attendee_phone, :quantity,
                :status, :payment_status, :total_amount_cents, :ticket_code)'
        );
        $stmt->execute([
            'event_id' => $data['event_id'],
            'attendee_name' => $data['attendee_name'],
            'attendee_email' => $data['attendee_email'] ?: null,
            'attendee_phone' => $data['attendee_phone'],
            'quantity' => $data['quantity'],
            'status' => $data['status'],
            'payment_status' => $data['payment_status'],
            'total_amount_cents' => $data['total_amount_cents'],
            'ticket_code' => $data['ticket_code'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM event_registrations WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByTicketCode(string $ticketCode): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM event_registrations WHERE ticket_code = :code LIMIT 1');
        $stmt->execute(['code' => $ticketCode]);
        return $stmt->fetch() ?: null;
    }

    public static function forEvent(int $eventId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM event_registrations WHERE event_id = :id ORDER BY created_at DESC'
        );
        $stmt->execute(['id' => $eventId]);
        return $stmt->fetchAll();
    }

    public static function all(): array
    {
        return Database::connection()->query(
            'SELECT r.*, e.title AS event_title, e.slug AS event_slug
             FROM event_registrations r
             JOIN events e ON e.id = r.event_id
             ORDER BY r.created_at DESC'
        )->fetchAll();
    }

    public static function confirm(PDO $pdo, int $id, ?string $notes = null): void
    {
        $pdo->prepare(
            "UPDATE event_registrations SET status = 'confirmed', payment_status = :payment_status, admin_notes = :notes WHERE id = :id"
        )->execute([
            'payment_status' => 'paid',
            'notes' => $notes,
            'id' => $id,
        ]);
    }

    public static function markFailed(PDO $pdo, int $id): void
    {
        $pdo->prepare("UPDATE event_registrations SET payment_status = 'failed' WHERE id = :id")
            ->execute(['id' => $id]);
    }

    public static function checkIn(int $id): bool
    {
        $stmt = Database::connection()->prepare(
            "UPDATE event_registrations SET checked_in_at = NOW() WHERE id = :id AND status = 'confirmed' AND checked_in_at IS NULL"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function counts(): array
    {
        $rows = Database::connection()->query(
            "SELECT status, COUNT(*) AS c FROM event_registrations GROUP BY status"
        )->fetchAll();
        $out = [];
        foreach ($rows as $row) {
            $out[$row['status']] = (int) $row['c'];
        }
        return $out;
    }
}
