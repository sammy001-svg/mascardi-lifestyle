<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\ContactMessage;

final class MessagesController
{
    public function index(): void
    {
        $filter = (string) Request::query('filter', 'all');
        if (!in_array($filter, ['all', 'unread', 'read'], true)) {
            $filter = 'all';
        }

        View::render('admin/messages/index', [
            'pageTitle' => 'Messages',
            'pageSubtitle' => 'Contact form submissions from the public site',
            'activeModule' => 'messages',
            'messages' => ContactMessage::all($filter),
            'filter' => $filter,
            'unreadCount' => ContactMessage::unreadCount(),
        ]);
    }

    public function show(): void
    {
        $id = Request::intInput('id');
        $message = ContactMessage::find($id);
        if (!$message) {
            Response::notFound();
        }

        // Opening a message marks it read.
        if ((int) $message['is_read'] === 0) {
            ContactMessage::markRead($id, true);
            $message['is_read'] = 1;
        }

        View::render('admin/messages/show', [
            'pageTitle' => 'Message from ' . $message['name'],
            'activeModule' => 'messages',
            'message' => $message,
        ]);
    }

    public function toggleRead(): void
    {
        $id = Request::intInput('id');
        $message = ContactMessage::find($id);
        if ($message) {
            ContactMessage::markRead($id, (int) $message['is_read'] === 0);
        }
        Response::redirect(admin_url('messages'));
    }

    public function delete(): void
    {
        $id = Request::intInput('id');
        $message = ContactMessage::find($id);
        if ($message) {
            ContactMessage::delete($id);
            ActivityLog::record(Auth::user()['id'] ?? null, 'message.delete', 'contact_message', $id, $message['name']);
            Session::flash('success', 'Message deleted.');
        }
        Response::redirect(admin_url('messages'));
    }
}
