<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Setting;

final class ShopController
{
    public function index(): void
    {
        $categorySlug = Request::query('category', '');
        $products = Product::all(true);

        if ($categorySlug !== '') {
            $products = array_values(array_filter(
                $products,
                static fn (array $p) => ($p['category_slug'] ?? null) === $categorySlug
            ));
        }

        View::render('site/shop/index', [
            'pageTitle' => 'Shop Mascardi — Mascardi Lifestyle',
            'bodyClass' => 'inner-page',
            'settings' => Setting::all(),
            'products' => $products,
            'categories' => ProductCategory::all(),
            'activeCategory' => $categorySlug,
        ], 'site');
    }

    public function show(string $slug): void
    {
        $product = Product::findBySlug($slug);
        if (!$product) {
            Response::notFound();
        }

        View::render('site/shop/product-detail', [
            'pageTitle' => $product['name'] . ' — Mascardi Lifestyle',
            'bodyClass' => 'inner-page',
            'settings' => Setting::all(),
            'product' => $product,
            'images' => Product::images($product['id']),
        ], 'site');
    }
}
