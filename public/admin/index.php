<?php

declare(strict_types=1);

define('PUBLIC_PATH', dirname(__DIR__));

require dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

/** @var array<string, class-string> $moduleMap */
$moduleMap = [
    'auth' => \App\Controllers\Admin\AuthController::class,
    'dashboard' => \App\Controllers\Admin\DashboardController::class,
    'pillars' => \App\Controllers\Admin\PillarsController::class,
    'partners' => \App\Controllers\Admin\PartnersController::class,
    'products' => \App\Controllers\Admin\ProductsController::class,
    'product-categories' => \App\Controllers\Admin\ProductCategoriesController::class,
    'orders' => \App\Controllers\Admin\OrdersController::class,
    'events' => \App\Controllers\Admin\EventsController::class,
    'registrations' => \App\Controllers\Admin\RegistrationsController::class,
    'media' => \App\Controllers\Admin\MediaController::class,
    'settings' => \App\Controllers\Admin\SettingsController::class,
];

$module = Request::query('module', 'dashboard');
$action = Request::query('action', 'index');

if (!isset($moduleMap[$module])) {
    Response::notFound();
}

// Every module except "auth" requires an authenticated admin session.
if ($module !== 'auth') {
    Auth::requireLogin();
}

$controllerClass = $moduleMap[$module];
$controller = new $controllerClass();

if (!method_exists($controller, $action) || !is_callable([$controller, $action])) {
    Response::notFound();
}

// All state-changing (POST) admin requests must carry a valid CSRF token.
if (Request::isPost()) {
    \App\Core\Csrf::verifyRequestOrFail();
}

$controller->$action();
