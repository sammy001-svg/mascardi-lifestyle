<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\Setting;

final class SettingsController
{
    private const KEYS = [
        'site_name',
        'hero_youtube_id',
        'hero_overlay_text',
        'footer_tagline',
        'footer_phone',
        'footer_email',
        'footer_address',
        'social_instagram_url',
        'social_facebook_url',
        'social_linkedin_url',
    ];

    public function index(): void
    {
        View::render('admin/settings/index', [
            'pageTitle' => 'Site Settings',
            'pageSubtitle' => 'Hero video, marketing copy, and footer content',
            'activeModule' => 'settings',
            'settings' => Setting::all(),
        ]);
    }

    public function update(): void
    {
        $data = Request::all(self::KEYS);

        // Accept a full YouTube URL or a bare video ID and normalize to just the ID.
        $data['hero_youtube_id'] = $this->extractYoutubeId((string) $data['hero_youtube_id']);

        Setting::setMany($data);
        ActivityLog::record(Auth::user()['id'] ?? null, 'settings.update', 'setting', null, null);

        Session::flash('success', 'Settings saved.');
        Response::redirect(admin_url('settings'));
    }

    private function extractYoutubeId(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('#youtu\.be/([A-Za-z0-9_-]{6,})#', $value, $m)) {
            return $m[1];
        }
        if (preg_match('#[?&]v=([A-Za-z0-9_-]{6,})#', $value, $m)) {
            return $m[1];
        }
        if (preg_match('#youtube\.com/embed/([A-Za-z0-9_-]{6,})#', $value, $m)) {
            return $m[1];
        }

        // Already looks like a bare video ID.
        return $value;
    }
}
