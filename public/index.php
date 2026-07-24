<?php

declare(strict_types=1);

define('PUBLIC_PATH', __DIR__);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\Site\CartController;
use App\Controllers\Site\CheckoutController;
use App\Controllers\Site\ContactController;
use App\Controllers\Site\EventsController;
use App\Controllers\Site\HomeController;
use App\Controllers\Site\ShopController;
use App\Core\Request;
use App\Core\Router;

$router = new Router();

$router->get('/', [new HomeController(), 'index']);

$shop = new ShopController();
$router->get('/shop', [$shop, 'index']);
$router->get('/shop/{slug}', [$shop, 'show']);

$cart = new CartController();
$router->get('/cart', [$cart, 'index']);
$router->post('/cart/add', [$cart, 'add']);
$router->post('/cart/update', [$cart, 'update']);
$router->post('/cart/remove', [$cart, 'remove']);

$checkout = new CheckoutController();
$router->get('/checkout', [$checkout, 'index']);
$router->post('/checkout', [$checkout, 'store']);
$router->get('/checkout/waiting/{orderNumber}', [$checkout, 'waiting']);
$router->post('/checkout/retry/{orderNumber}', [$checkout, 'retry']);
$router->get('/checkout/confirmation/{orderNumber}', [$checkout, 'confirmation']);

$contact = new ContactController();
$router->get('/contact', [$contact, 'index']);
$router->post('/contact', [$contact, 'submit']);

$events = new EventsController();
$router->get('/events', [$events, 'index']);
$router->get('/events/waiting/{ticketCode}', [$events, 'waiting']);
$router->post('/events/retry/{ticketCode}', [$events, 'retry']);
$router->get('/events/confirmation/{ticketCode}', [$events, 'confirmation']);
$router->get('/events/{slug}', [$events, 'show']);
$router->post('/events/{slug}/register', [$events, 'register']);

$router->dispatch(Request::method(), Request::path());
