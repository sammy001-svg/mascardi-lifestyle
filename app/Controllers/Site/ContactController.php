<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\ContactMessage;
use App\Models\Setting;

final class ContactController
{
    public function index(): void
    {
        View::render('site/contact/index', [
            'pageTitle' => 'Contact — Mascardi Lifestyle',
            'bodyClass' => 'inner-page',
            'settings' => Setting::all(),
        ], 'site');
    }

    public function submit(): void
    {
        // Honeypot: a hidden field real users never see. If it's filled, it's a
        // bot — quietly pretend success without storing anything.
        if (trim((string) Request::input('website', '')) !== '') {
            Session::flash('success', 'Thank you — your message has been sent.');
            Response::redirect(site_url('contact') . '#contact-form');
        }

        $input = Request::all(['name', 'email', 'phone', 'subject', 'message']);

        $v = new Validator($input);
        $v->required('name', 'Name')->maxLength('name', 150, 'Name');
        $v->required('email', 'Email')->email('email', 'Email')->maxLength('email', 190, 'Email');
        $v->required('message', 'Message');
        $v->maxLength('subject', 200, 'Subject');

        if ($v->fails()) {
            redirect_with_errors(site_url('contact') . '#contact-form', $v->errors(), $_POST);
        }

        ContactMessage::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'subject' => $input['subject'],
            'message' => $input['message'],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        Session::flash('success', 'Thank you — your message has been sent. We\'ll be in touch shortly.');
        Response::redirect(site_url('contact') . '#contact-form');
    }
}
