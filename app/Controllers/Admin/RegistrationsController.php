<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\EventRegistration;

final class RegistrationsController
{
    public function index(): void
    {
        $eventId = Request::query('event_id', '');
        $registrations = EventRegistration::all();

        if ($eventId !== '') {
            $registrations = array_values(array_filter(
                $registrations,
                static fn (array $r) => (int) $r['event_id'] === (int) $eventId
            ));
        }

        View::render('admin/registrations/index', [
            'pageTitle' => 'Registrations',
            'pageSubtitle' => 'Event RSVPs and ticket sales',
            'activeModule' => 'registrations',
            'registrations' => $registrations,
            'events' => Event::all(),
            'currentEventId' => $eventId,
        ]);
    }

    public function checkIn(): void
    {
        $id = Request::intInput('id');
        $registration = EventRegistration::find($id);

        if ($registration && EventRegistration::checkIn($id)) {
            ActivityLog::record(Auth::user()['id'] ?? null, 'registration.check_in', 'event_registration', $id, $registration['ticket_code']);
            Session::flash('success', 'Checked in ' . $registration['attendee_name'] . '.');
        } else {
            Session::flash('error', 'Could not check in — ticket is not confirmed or was already checked in.');
        }

        Response::redirect((string) Request::input('redirect_to', admin_url('registrations')));
    }

    public function export(): void
    {
        $eventId = Request::query('event_id', '');
        $registrations = EventRegistration::all();
        if ($eventId !== '') {
            $registrations = array_values(array_filter(
                $registrations,
                static fn (array $r) => (int) $r['event_id'] === (int) $eventId
            ));
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="registrations.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Event', 'Attendee', 'Email', 'Phone', 'Quantity', 'Status', 'Payment Status', 'Ticket Code', 'Checked In', 'Registered At']);
        foreach ($registrations as $r) {
            fputcsv($out, [
                $r['event_title'],
                $r['attendee_name'],
                $r['attendee_email'],
                $r['attendee_phone'],
                $r['quantity'],
                $r['status'],
                $r['payment_status'],
                $r['ticket_code'],
                $r['checked_in_at'] ?: '',
                $r['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }
}
