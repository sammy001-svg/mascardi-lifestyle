<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Phone;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\MpesaTransaction;
use App\Models\Order;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\Mpesa\StkPushService;
use App\Services\OrderService;

final class CheckoutController
{
    public function index(): void
    {
        if (CartService::isEmpty()) {
            Response::redirect(site_url('cart'));
        }

        View::render('site/checkout/index', [
            'pageTitle' => 'Checkout — Mascardi Lifestyle',
            'bodyClass' => 'inner-page',
            'settings' => Setting::all(),
            'items' => CartService::items(),
            'subtotalCents' => CartService::subtotalCents(),
        ], 'site');
    }

    public function store(): void
    {
        if (CartService::isEmpty()) {
            Response::redirect(site_url('cart'));
        }

        $input = Request::all(['name', 'phone', 'email', 'delivery_notes']);

        $v = new Validator($input);
        $v->required('name', 'Full name')->maxLength('name', 150, 'Full name');
        $v->required('phone', 'M-Pesa phone number');
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
            redirect_with_errors(site_url('checkout'), $errors, $_POST);
        }

        $order = OrderService::createFromCart(
            $input['name'],
            $input['email'] ?: null,
            $normalizedPhone,
            $input['delivery_notes'] ?: null
        );

        $this->attemptStkPush($order['order_id'], $order['order_number'], $order['total_cents'], $normalizedPhone);

        Response::redirect(site_url('checkout/waiting/' . $order['order_number']));
    }

    public function waiting(string $orderNumber): void
    {
        $order = Order::findByOrderNumber($orderNumber);
        if (!$order) {
            Response::notFound();
        }

        if ($order['payment_status'] === 'paid') {
            Response::redirect(site_url('checkout/confirmation/' . $orderNumber));
        }

        View::render('site/checkout/waiting-payment', [
            'pageTitle' => 'Confirming Payment — Mascardi Lifestyle',
            'bodyClass' => 'inner-page',
            'settings' => Setting::all(),
            'order' => $order,
            'transaction' => MpesaTransaction::findLatestForOrder((int) $order['id']),
        ], 'site');
    }

    public function retry(string $orderNumber): void
    {
        $order = Order::findByOrderNumber($orderNumber);
        if (!$order || $order['payment_status'] === 'paid') {
            Response::redirect(site_url('checkout/waiting/' . $orderNumber));
        }

        $this->attemptStkPush((int) $order['id'], $order['order_number'], (int) $order['total_cents'], $order['customer_phone']);

        Response::redirect(site_url('checkout/waiting/' . $orderNumber));
    }

    public function confirmation(string $orderNumber): void
    {
        $order = Order::findByOrderNumber($orderNumber);
        if (!$order) {
            Response::notFound();
        }

        View::render('site/checkout/confirmation', [
            'pageTitle' => 'Order Confirmed — Mascardi Lifestyle',
            'bodyClass' => 'inner-page',
            'settings' => Setting::all(),
            'order' => $order,
            'items' => Order::items((int) $order['id']),
        ], 'site');
    }

    private function attemptStkPush(int $orderId, string $orderNumber, int $totalCents, string $normalizedPhone): void
    {
        try {
            StkPushService::initiate(
                'order',
                $totalCents,
                $normalizedPhone,
                $orderNumber,
                'Mascardi order',
                $orderId
            );
        } catch (\RuntimeException $e) {
            // Order already exists and is safely in pending_payment — the
            // waiting page detects the missing checkout_request_id and offers
            // a retry button rather than losing the order.
            Session::flash('error', $e->getMessage());
        }
    }
}
