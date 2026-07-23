<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Phone;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\MpesaTransaction;
use App\Models\Setting;
use App\Services\EventRegistrationService;
use App\Services\Mpesa\StkPushService;

final class EventsController
{
    public function index(): void
    {
        View::render('site/events/index', [
            'pageTitle' => 'Events — Mascardi Lifestyle',
            'bodyClass' => 'inner-page',
            'settings' => Setting::all(),
            'events' => Event::upcoming(50),
        ], 'site');
    }

    public function show(string $slug): void
    {
        $event = Event::findBySlug($slug);
        if (!$event) {
            Response::notFound();
        }

        View::render('site/events/detail', [
            'pageTitle' => $event['title'] . ' — Mascardi Lifestyle',
            'bodyClass' => 'inner-page',
            'settings' => Setting::all(),
            'event' => $event,
        ], 'site');
    }

    public function register(string $slug): void
    {
        $event = Event::findBySlug($slug);
        if (!$event) {
            Response::notFound();
        }

        $input = Request::all(['name', 'phone', 'email', 'quantity']);
        $quantity = max(1, (int) ($input['quantity'] ?: 1));

        $v = new Validator($input);
        $v->required('name', 'Full name')->maxLength('name', 150, 'Full name');
        $v->required('phone', 'Phone number');
        $v->email('email', 'Email');

        $normalizedPhone = Phone::normalizeKenyan((string) $input['phone']);
        if ($normalizedPhone === null) {
            $v->required('__phone_invalid__', 'A valid Safaricom number (e.g. 07XXXXXXXX)');
        }

        if ($v->fails()) {
            $errors = $v->errors();
            if (isset($errors['__phone_invalid__'])) {
                $errors['phone'] = $errors['__phone_invalid__'];
                unset($errors['__phone_invalid__']);
            }
            redirect_with_errors(site_url('events/' . $slug), $errors, $_POST);
        }

        if ($event['event_type'] === 'free') {
            try {
                $result = EventRegistrationService::rsvpFree($event, $input['name'], $input['email'] ?: null, $normalizedPhone, $quantity);
            } catch (\RuntimeException $e) {
                redirect_with_errors(site_url('events/' . $slug), ['quantity' => [$e->getMessage()]], $_POST);
            }

            Response::redirect(site_url('events/confirmation/' . $result['ticket_code']));
        }

        $result = EventRegistrationService::createPendingTicket($event, $input['name'], $input['email'] ?: null, $normalizedPhone, $quantity);

        try {
            StkPushService::initiate(
                'event_ticket',
                $result['total_cents'],
                $normalizedPhone,
                $result['ticket_code'],
                'Event ticket',
                null,
                $result['registration_id']
            );
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }

        Response::redirect(site_url('events/waiting/' . $result['ticket_code']));
    }

    public function waiting(string $ticketCode): void
    {
        $registration = EventRegistration::findByTicketCode($ticketCode);
        if (!$registration) {
            Response::notFound();
        }

        if ($registration['status'] === 'confirmed') {
            Response::redirect(site_url('events/confirmation/' . $ticketCode));
        }

        View::render('site/events/waiting-payment', [
            'pageTitle' => 'Confirming Payment — Mascardi Lifestyle',
            'bodyClass' => 'inner-page',
            'settings' => Setting::all(),
            'registration' => $registration,
            'event' => Event::find((int) $registration['event_id']),
            'transaction' => MpesaTransaction::findLatestForEventRegistration((int) $registration['id']),
        ], 'site');
    }

    public function retry(string $ticketCode): void
    {
        $registration = EventRegistration::findByTicketCode($ticketCode);
        if (!$registration || $registration['status'] === 'confirmed') {
            Response::redirect(site_url('events/waiting/' . $ticketCode));
        }

        $event = Event::find((int) $registration['event_id']);

        try {
            StkPushService::initiate(
                'event_ticket',
                (int) $registration['total_amount_cents'],
                $registration['attendee_phone'],
                $ticketCode,
                'Event ticket',
                null,
                (int) $registration['id']
            );
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }

        Response::redirect(site_url('events/waiting/' . $ticketCode));
    }

    public function confirmation(string $ticketCode): void
    {
        $registration = EventRegistration::findByTicketCode($ticketCode);
        if (!$registration) {
            Response::notFound();
        }

        View::render('site/events/confirmation', [
            'pageTitle' => 'Registration Confirmed — Mascardi Lifestyle',
            'bodyClass' => 'inner-page',
            'settings' => Setting::all(),
            'registration' => $registration,
            'event' => Event::find((int) $registration['event_id']),
        ], 'site');
    }
}
