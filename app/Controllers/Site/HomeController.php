<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\View;
use App\Models\Event;
use App\Models\Partner;
use App\Models\Pillar;
use App\Models\Product;
use App\Models\Setting;

final class HomeController
{
    public function index(): void
    {
        View::render('site/home', [
            'pageTitle' => 'Mascardi Lifestyle — Experience the Difference',
            'settings' => Setting::all(),
            'pillars' => Pillar::all(true),
            'partners' => Partner::all(true),
            'products' => Product::featured(16),
            'events' => Event::upcoming(3),
        ], 'site');
    }
}
