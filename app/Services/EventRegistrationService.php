<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Event;
use App\Models\EventRegistration;
use RuntimeException;

final class EventRegistrationService
{
    /**
     * Free RSVP: confirmed immediately, capacity checked/locked right away
     * since there's no payment step to defer it to.
     *
     * @return array{registration_id:int, ticket_code:string}
     */
    public static function rsvpFree(array $event, string $name, ?string $email, string $normalizedPhone, int $quantity): array
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $capacityInfo = Event::lockForCapacityCheck($pdo, (int) $event['id']);
            if ($capacityInfo['capacity'] !== null && $capacityInfo['used'] + $quantity > $capacityInfo['capacity']) {
                $pdo->rollBack();
                throw new RuntimeException('This event is fully booked.');
            }

            $ticketCode = EventRegistration::generateTicketCode();
            $registrationId = EventRegistration::create($pdo, [
                'event_id' => $event['id'],
                'attendee_name' => $name,
                'attendee_email' => $email,
                'attendee_phone' => $normalizedPhone,
                'quantity' => $quantity,
                'status' => 'confirmed',
                'payment_status' => 'not_required',
                'total_amount_cents' => 0,
                'ticket_code' => $ticketCode,
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        return ['registration_id' => $registrationId, 'ticket_code' => $ticketCode];
    }

    /**
     * Paid ticket: registration is created pending_payment, capacity is only
     * checked/locked once payment actually confirms (CallbackHandler) — same
     * "don't reserve on abandoned checkout" principle as shop orders.
     *
     * @return array{registration_id:int, ticket_code:string, total_cents:int}
     */
    public static function createPendingTicket(array $event, string $name, ?string $email, string $normalizedPhone, int $quantity): array
    {
        $totalCents = (int) $event['ticket_price_cents'] * $quantity;
        $ticketCode = EventRegistration::generateTicketCode();

        $pdo = Database::connection();
        $registrationId = EventRegistration::create($pdo, [
            'event_id' => $event['id'],
            'attendee_name' => $name,
            'attendee_email' => $email,
            'attendee_phone' => $normalizedPhone,
            'quantity' => $quantity,
            'status' => 'pending_payment',
            'payment_status' => 'unpaid',
            'total_amount_cents' => $totalCents,
            'ticket_code' => $ticketCode,
        ]);

        return ['registration_id' => $registrationId, 'ticket_code' => $ticketCode, 'total_cents' => $totalCents];
    }
}
