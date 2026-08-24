<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Uploader;
use App\Core\Validator;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\MediaUpload;

final class GalleryAlbumsController
{
    public function index(): void
    {
        View::render('admin/gallery-albums/index', [
            'pageTitle'    => 'Gallery Albums',
            'pageSubtitle' => 'Group and showcase photos for visitors',
            'activeModule' => 'gallery',
            'albums'       => GalleryAlbum::all(),
        ]);
    }

    public function create(): void
    {
        View::render('admin/gallery-albums/form', [
            'pageTitle'    => 'New Album',
            'activeModule' => 'gallery',
            'album'        => null,
            'images'       => [],
        ]);
    }

    public function store(): void
    {
        [$data, $errors] = $this->validate(Request::all(['name', 'slug', 'description', 'sort_order']));

        if ($errors) {
            redirect_with_errors(admin_url('gallery', 'create'), $errors, $_POST);
        }

        if (GalleryAlbum::slugExists($data['slug'])) {
            redirect_with_errors(admin_url('gallery', 'create'), ['slug' => ['That slug is already in use.']], $_POST);
        }

        $data['is_active']        = Request::boolInput('is_active') ? 1 : 0;
        $data['cover_image_path'] = $this->handleCoverImage(null, Auth::user()['id'] ?? null);

        $id = GalleryAlbum::create($data);
        $this->handleGalleryImages($id, Auth::user()['id'] ?? null);

        ActivityLog::record(Auth::user()['id'] ?? null, 'gallery_album.create', 'gallery_album', $id, $data['name']);
        Session::flash('success', 'Album created.');
        Response::redirect(admin_url('gallery', 'edit', ['id' => $id]));
    }

    public function edit(): void
    {
        $id    = Request::intInput('id');
        $album = GalleryAlbum::find($id);
        if (!$album) {
            Response::notFound();
        }

        View::render('admin/gallery-albums/form', [
            'pageTitle'    => 'Edit Album',
            'activeModule' => 'gallery',
            'album'        => $album,
            'images'       => GalleryImage::allForAlbum($id),
        ]);
    }

    public function update(): void
    {
        $id    = Request::intInput('id');
        $album = GalleryAlbum::find($id);
        if (!$album) {
            Response::notFound();
        }

        [$data, $errors] = $this->validate(Request::all(['name', 'slug', 'description', 'sort_order']));

        if ($errors) {
            redirect_with_errors(admin_url('gallery', 'edit', ['id' => $id]), $errors, $_POST);
        }

        if (GalleryAlbum::slugExists($data['slug'], $id)) {
            redirect_with_errors(admin_url('gallery', 'edit', ['id' => $id]), ['slug' => ['That slug is already in use.']], $_POST);
        }

        $data['is_active'] = Request::boolInput('is_active') ? 1 : 0;

        $newCover = $this->handleCoverImage($album['cover_image_path'] ?? null, Auth::user()['id'] ?? null);
        if ($newCover !== null) {
            $data['cover_image_path'] = $newCover;
        }

        GalleryAlbum::update($id, $data);
        $this->handleGalleryImages($id, Auth::user()['id'] ?? null);

        ActivityLog::record(Auth::user()['id'] ?? null, 'gallery_album.update', 'gallery_album', $id, $data['name']);
        Session::flash('success', 'Album updated.');
        Response::redirect(admin_url('gallery', 'edit', ['id' => $id]));
    }

    public function delete(): void
    {
        $id    = Request::intInput('id');
        $album = GalleryAlbum::find($id);
        if ($album) {
            // Delete all gallery images from disk
            $images = GalleryImage::allForAlbum($id);
            GalleryAlbum::delete($id); // cascade deletes gallery_images rows
            foreach ($images as $image) {
                if (empty(MediaUpload::usages($image['image_path']))) {
                    Uploader::delete($image['image_path']);
                }
            }
            if (!empty($album['cover_image_path']) && empty(MediaUpload::usages($album['cover_image_path']))) {
                Uploader::delete($album['cover_image_path']);
            }
            ActivityLog::record(Auth::user()['id'] ?? null, 'gallery_album.delete', 'gallery_album', $id, $album['name']);
            Session::flash('success', 'Album deleted.');
        }
        Response::redirect(admin_url('gallery'));
    }

    public function deleteImage(): void
    {
        $imageId = Request::intInput('image_id');
        $albumId = Request::intInput('id');
        $image   = GalleryImage::find($imageId);

        if ($image && (int) $image['album_id'] === $albumId) {
            GalleryImage::delete($imageId);
            if (empty(MediaUpload::usages($image['image_path']))) {
                Uploader::delete($image['image_path']);
            }
        }
        Response::redirect(admin_url('gallery', 'edit', ['id' => $albumId]));
    }

    // ------------------------------------------------------------------ //

    private function handleCoverImage(?string $existing, ?int $adminId): ?string
    {
        $pickedIds = (array) ($_POST['picked_cover_ids'] ?? []);
        if (!empty($pickedIds)) {
            $media = MediaUpload::find((int) $pickedIds[0]);
            if ($media) {
                return $media['file_path'];
            }
        }

        $files = Request::normalizeFiles('cover_image');
        if (!empty($files)) {
            try {
                return Uploader::storeImage($files[0], 'gallery', $adminId);
            } catch (\RuntimeException) {
                // Keep existing
            }
        }

        return null;
    }

    private function handleGalleryImages(int $albumId, ?int $adminId): void
    {
        $existing  = GalleryImage::countForAlbum($albumId);
        $sortOrder = $existing;

        $captions = (array) ($_POST['captions'] ?? []);
        $capIdx   = 0;

        foreach (Request::normalizeFiles('images') as $file) {
            try {
                $path = Uploader::storeImage($file, 'gallery', $adminId);
            } catch (\RuntimeException) {
                $capIdx++;
                continue;
            }
            $caption = $captions[$capIdx] ?? null;
            GalleryImage::create($albumId, $path, $caption ?: null, $sortOrder);
            $sortOrder++;
            $capIdx++;
        }

        // Pick from media library
        foreach ((array) ($_POST['picked_media_ids'] ?? []) as $rawId) {
            $media = MediaUpload::find((int) $rawId);
            if (!$media) {
                continue;
            }
            GalleryImage::create($albumId, $media['file_path'], null, $sortOrder);
            $sortOrder++;
        }
    }

    private function validate(array $input): array
    {
        if (empty($input['slug']) && !empty($input['name'])) {
            $input['slug'] = slugify($input['name']);
        } else {
            $input['slug'] = slugify((string) ($input['slug'] ?? ''));
        }

        $v = new Validator($input);
        $v->required('name', 'Name')->maxLength('name', 150, 'Name');
        $v->required('slug', 'Slug');

        if ($v->fails()) {
            return [$input, $v->errors()];
        }

        return [[
            'name'        => $input['name'],
            'slug'        => $input['slug'],
            'description' => $input['description'] ?: null,
            'sort_order'  => (int) ($input['sort_order'] ?? 0),
        ], []];
    }
}
