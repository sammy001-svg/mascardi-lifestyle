<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Setting;
use App\Services\CartService;

final class CartController
{
    public function index(): void
    {
        View::render('site/cart/index', [
            'pageTitle' => 'Your Cart — Mascardi Lifestyle',
            'bodyClass' => 'inner-page',
            'settings' => Setting::all(),
            'items' => CartService::items(),
            'subtotalCents' => CartService::subtotalCents(),
        ], 'site');
    }

    public function add(): void
    {
        $productId = Request::intInput('product_id');
        $quantity = max(1, Request::intInput('quantity', 1));

        if ($productId > 0) {
            CartService::add($productId, $quantity);
            Session::flash('success', 'Added to your cart.');
        }

        Response::redirect((string) Request::input('redirect_to', site_url('shop')));
    }

    public function update(): void
    {
        $productId = Request::intInput('product_id');
        $quantity = Request::intInput('quantity', 1);
        CartService::updateQuantity($productId, $quantity);

        Response::redirect(site_url('cart'));
    }

    public function remove(): void
    {
        CartService::remove(Request::intInput('product_id'));
        Session::flash('success', 'Item removed from your cart.');

        Response::redirect(site_url('cart'));
    }
}
