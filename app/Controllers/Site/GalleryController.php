<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Response;
use App\Core\View;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\Setting;

final class GalleryController
{
    public function index(): void
    {
        View::render('site/gallery/index', [
            'pageTitle' => 'Gallery — Mascardi Lifestyle',
            'settings'  => Setting::all(),
            'albums'    => GalleryAlbum::all(activeOnly: true),
        ], 'site');
    }

    public function show(string $slug): void
    {
        $album = GalleryAlbum::findBySlug($slug);

        if (!$album) {
            Response::notFound();
        }

        View::render('site/gallery/show', [
            'pageTitle' => e($album['name']) . ' — Mascardi Lifestyle Gallery',
            'settings'  => Setting::all(),
            'album'     => $album,
            'images'    => GalleryImage::allForAlbum((int) $album['id']),
            'albums'    => GalleryAlbum::all(activeOnly: true),
        ], 'site');
    }
}
