<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

final class AuthController
{
    public function login(): void
    {
        if (Auth::check()) {
            Response::redirect(admin_url('dashboard'));
        }

        View::render('admin/auth/login', [
            'title' => 'Sign in',
        ], 'auth');
    }

    public function attempt(): void
    {
        $email = (string) Request::input('email', '');
        $password = (string) Request::input('password', '');

        if ($email === '' || $password === '') {
            redirect_with_errors(admin_url('auth', 'login'), ['email' => ['Email and password are required.']], ['email' => $email]);
        }

        $result = Auth::attempt($email, $password);

        if ($result !== true) {
            redirect_with_errors(admin_url('auth', 'login'), ['email' => [$result]], ['email' => $email]);
        }

        Response::redirect(admin_url('dashboard'));
    }

    public function logout(): void
    {
        Auth::logout();
        Response::redirect(admin_url('auth', 'login'));
    }
}
