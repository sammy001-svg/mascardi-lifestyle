<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Money;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Uploader;
use App\Core\Validator;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\Event;

final class EventsController
{
    public function index(): void
    {
        View::render('admin/events/index', [
            'pageTitle' => 'Events',
            'pageSubtitle' => 'Scheduled experiences and ticketed events',
            'activeModule' => 'events',
            'events' => Event::all(),
        ]);
    }

    public function create(): void
    {
        View::render('admin/events/form', [
            'pageTitle' => 'Add Event',
            'activeModule' => 'events',
            'event' => null,
        ]);
    }

    public function store(): void
    {
        [$data, $errors] = $this->validate(Request::all([
            'title', 'slug', 'description', 'event_type', 'ticket_price', 'capacity', 'venue', 'starts_at', 'ends_at',
        ]));

        if ($errors) {
            redirect_with_errors(admin_url('events', 'create'), $errors, $_POST);
        }

        if (Event::slugExists($data['slug'])) {
            redirect_with_errors(admin_url('events', 'create'), ['slug' => ['That slug is already in use.']], $_POST);
        }

        if ($file = Request::file('image')) {
            try {
                $data['image_path'] = Uploader::storeImage($file, 'events');
            } catch (\RuntimeException $e) {
                redirect_with_errors(admin_url('events', 'create'), ['image' => [$e->getMessage()]], $_POST);
            }
        }

        $data['is_active'] = Request::boolInput('is_active') ? 1 : 0;

        $id = Event::create($data);
        ActivityLog::record(Auth::user()['id'] ?? null, 'event.create', 'event', $id, $data['title']);

        Session::flash('success', 'Event created.');
        Response::redirect(admin_url('events'));
    }

    public function edit(): void
    {
        $id = Request::intInput('id');
        $event = Event::find($id);
        if (!$event) {
            Response::notFound();
        }

        View::render('admin/events/form', [
            'pageTitle' => 'Edit Event',
            'activeModule' => 'events',
            'event' => $event,
        ]);
    }

    public function update(): void
    {
        $id = Request::intInput('id');
        $event = Event::find($id);
        if (!$event) {
            Response::notFound();
        }

        [$data, $errors] = $this->validate(Request::all([
            'title', 'slug', 'description', 'event_type', 'ticket_price', 'capacity', 'venue', 'starts_at', 'ends_at',
        ]));

        if ($errors) {
            redirect_with_errors(admin_url('events', 'edit', ['id' => $id]), $errors, $_POST);
        }

        if (Event::slugExists($data['slug'], $id)) {
            redirect_with_errors(admin_url('events', 'edit', ['id' => $id]), ['slug' => ['That slug is already in use.']], $_POST);
        }

        if ($file = Request::file('image')) {
            try {
                $data['image_path'] = Uploader::storeImage($file, 'events');
                Uploader::delete($event['image_path']);
            } catch (\RuntimeException $e) {
                redirect_with_errors(admin_url('events', 'edit', ['id' => $id]), ['image' => [$e->getMessage()]], $_POST);
            }
        }

        $data['is_active'] = Request::boolInput('is_active') ? 1 : 0;

        Event::update($id, $data);
        ActivityLog::record(Auth::user()['id'] ?? null, 'event.update', 'event', $id, $data['title']);

        Session::flash('success', 'Event updated.');
        Response::redirect(admin_url('events'));
    }

    public function delete(): void
    {
        $id = Request::intInput('id');
        $event = Event::find($id);
        if ($event) {
            Uploader::delete($event['image_path']);
            Event::delete($id); // event_registrations cascade-delete via FK
            ActivityLog::record(Auth::user()['id'] ?? null, 'event.delete', 'event', $id, $event['title']);
            Session::flash('success', 'Event deleted.');
        }
        Response::redirect(admin_url('events'));
    }

    private function validate(array $input): array
    {
        if (empty($input['slug']) && !empty($input['title'])) {
            $input['slug'] = slugify($input['title']);
        } else {
            $input['slug'] = slugify((string) ($input['slug'] ?? ''));
        }

        $v = new Validator($input);
        $v->required('title', 'Title')->maxLength('title', 180, 'Title');
        $v->required('slug', 'Slug');
        $v->required('event_type', 'Event type');
        $v->required('starts_at', 'Start date/time');

        if (($input['event_type'] ?? '') === 'paid') {
            $v->required('ticket_price', 'Ticket price')->numeric('ticket_price', 'Ticket price')->min('ticket_price', 0, 'Ticket price');
        }
        if (($input['capacity'] ?? '') !== '') {
            $v->numeric('capacity', 'Capacity')->min('capacity', 1, 'Capacity');
        }

        if ($v->fails()) {
            return [$input, $v->errors()];
        }

        $data = [
            'title' => $input['title'],
            'slug' => $input['slug'],
            'description' => $input['description'] ?: null,
            'event_type' => $input['event_type'] === 'paid' ? 'paid' : 'free',
            'ticket_price_cents' => $input['ticket_price'] !== '' ? Money::toCents($input['ticket_price']) : 0,
            'capacity' => $input['capacity'] !== '' ? (int) $input['capacity'] : null,
            'venue' => $input['venue'] ?: null,
            'starts_at' => str_replace('T', ' ', $input['starts_at']) . (strlen($input['starts_at']) === 16 ? ':00' : ''),
            'ends_at' => $input['ends_at'] !== '' ? str_replace('T', ' ', $input['ends_at']) . (strlen($input['ends_at']) === 16 ? ':00' : '') : null,
        ];

        return [$data, []];
    }
}
