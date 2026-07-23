<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\MpesaTransaction;
use App\Models\Order;

final class OrdersController
{
    private const VALID_STATUSES = ['pending_payment', 'paid', 'processing', 'completed', 'cancelled', 'failed', 'refunded'];

    public function index(): void
    {
        $status = Request::query('status', '');
        $status = in_array($status, self::VALID_STATUSES, true) ? $status : null;

        View::render('admin/orders/index', [
            'pageTitle' => 'Orders',
            'pageSubtitle' => 'Shop Mascardi orders and payment status',
            'activeModule' => 'orders',
            'orders' => Order::all($status),
            'counts' => Order::counts(),
            'currentStatus' => $status,
        ]);
    }

    public function show(): void
    {
        $id = Request::intInput('id');
        $order = Order::find($id);
        if (!$order) {
            Response::notFound();
        }

        View::render('admin/orders/show', [
            'pageTitle' => 'Order ' . $order['order_number'],
            'activeModule' => 'orders',
            'order' => $order,
            'items' => Order::items($id),
            'transaction' => MpesaTransaction::findLatestForOrder($id),
            'statuses' => self::VALID_STATUSES,
        ]);
    }

    public function updateStatus(): void
    {
        $id = Request::intInput('id');
        $order = Order::find($id);
        if (!$order) {
            Response::notFound();
        }

        $status = (string) Request::input('status');
        if (in_array($status, self::VALID_STATUSES, true)) {
            Order::updateStatus($id, $status);
            ActivityLog::record(Auth::user()['id'] ?? null, 'order.status_change', 'order', $id, $status);
            Session::flash('success', 'Order status updated.');
        }

        $notes = (string) Request::input('admin_notes', '');
        Order::setAdminNotes($id, $notes);

        Response::redirect(admin_url('orders', 'show', ['id' => $id]));
    }
}
