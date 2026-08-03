<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Response;
use App\Core\View;
use App\Models\Partner;
use App\Models\Pillar;
use App\Models\Setting;

final class PillarsController
{
    public function show(string $slug): void
    {
        $pillar = Pillar::findBySlug($slug);
        if (!$pillar) {
            Response::notFound();
        }

        // Position (1–8) among the active pillars, for the "Pillar 0X" eyebrow.
        $index = 0;
        foreach (Pillar::all(true) as $i => $p) {
            if ((int) $p['id'] === (int) $pillar['id']) {
                $index = $i + 1;
                break;
            }
        }

        View::render('site/pillars/show', [
            'pageTitle' => $pillar['name'] . ' — Mascardi Lifestyle',
            'bodyClass' => 'inner-page',
            'settings' => Setting::all(),
            'pillar' => $pillar,
            'index' => $index,
            'partners' => Partner::forPillar((int) $pillar['id']),
        ], 'site');
    }
}
