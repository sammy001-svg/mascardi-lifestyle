<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Uploader;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\MediaUpload;

final class MediaController
{
    private const PER_PAGE = 60;

    public function index(): void
    {
        $search = (string) Request::query('search', '');
        $page = max(1, Request::intInput('page', 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $items = MediaUpload::all($search !== '' ? $search : null, self::PER_PAGE, $offset);
        $total = MediaUpload::count($search !== '' ? $search : null);

        View::render('admin/media/index', [
            'pageTitle' => 'Media Library',
            'pageSubtitle' => 'Every image uploaded across the admin',
            'activeModule' => 'media',
            'items' => $items,
            'search' => $search,
            'page' => $page,
            'totalPages' => (int) ceil($total / self::PER_PAGE),
        ]);
    }

    public function store(): void
    {
        $adminId = Auth::user()['id'] ?? null;
        $stored = 0;
        $lastError = null;
        foreach (Request::normalizeFiles('images') as $file) {
            try {
                Uploader::storeImage($file, 'library', $adminId);
                $stored++;
            } catch (\RuntimeException $e) {
                $lastError = $e->getMessage();
            }
        }

        if ($stored > 0) {
            ActivityLog::record($adminId, 'media.upload', 'media_upload', null, "{$stored} file(s)");
        }

        if ($stored > 0 && $lastError !== null) {
            Session::flash('success', "{$stored} file(s) uploaded. Some were skipped: {$lastError}");
        } elseif ($stored > 0) {
            Session::flash('success', "{$stored} file(s) uploaded.");
        } else {
            Session::flash('error', $lastError ?? 'No valid files were uploaded.');
        }

        Response::redirect(admin_url('media'));
    }

    public function delete(): void
    {
        $id = Request::intInput('id');
        $media = MediaUpload::find($id);
        if (!$media) {
            Response::redirect(admin_url('media'));
        }

        $usages = MediaUpload::usages($media['file_path']);
        if ($usages) {
            $labels = array_map(static fn(array $u) => $u['type'] . ' "' . $u['label'] . '"', $usages);
            Session::flash('error', 'Cannot delete — still used by: ' . implode(', ', $labels));
            Response::redirect(admin_url('media'));
        }

        Uploader::delete($media['file_path']); // also removes the media_uploads row
        ActivityLog::record(Auth::user()['id'] ?? null, 'media.delete', 'media_upload', $id, $media['original_filename']);
        Session::flash('success', 'File deleted.');
        Response::redirect(admin_url('media'));
    }

    public function picker(): void
    {
        $search = (string) Request::query('search', '');
        $items = MediaUpload::all($search !== '' ? $search : null, self::PER_PAGE, 0);

        header('Content-Type: text/html; charset=utf-8');
        echo View::renderPartial('admin/media/_grid', ['items' => $items, 'showDelete' => false]);
    }
}
