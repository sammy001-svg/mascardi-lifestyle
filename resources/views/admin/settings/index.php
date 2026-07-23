<?php
/** @var array $settings */
$s = static fn (string $key) => e($settings[$key] ?? '');
?>
<div class="page-header">
    <div>
        <h1>Site Settings</h1>
        <p>Controls the hero video, campaign overlay text, and footer content on the public site.</p>
    </div>
</div>

<form method="post" action="<?= admin_url('settings', 'update') ?>">
    <?= csrf_field() ?>

    <div class="card" style="margin-bottom:20px;">
        <div class="card__header"><h2>Hero Section</h2></div>
        <div class="card__body">
            <div class="form-group">
                <label class="form-label" for="hero_youtube_id">Hero video (YouTube URL or video ID)</label>
                <input class="form-control" type="text" id="hero_youtube_id" name="hero_youtube_id" value="<?= $s('hero_youtube_id') ?>" placeholder="https://youtu.be/...">
                <div class="form-hint">Plays muted &amp; on loop as the homepage background via a privacy-enhanced YouTube embed.</div>
            </div>
            <div class="form-group">
                <label class="form-label" for="hero_overlay_text">Overlay marketing text</label>
                <input class="form-control" type="text" id="hero_overlay_text" name="hero_overlay_text" value="<?= $s('hero_overlay_text') ?>" placeholder="Belong to the Difference">
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div class="card__header"><h2>General</h2></div>
        <div class="card__body">
            <div class="form-group">
                <label class="form-label" for="site_name">Site name</label>
                <input class="form-control" type="text" id="site_name" name="site_name" value="<?= $s('site_name') ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="footer_tagline">Tagline</label>
                <input class="form-control" type="text" id="footer_tagline" name="footer_tagline" value="<?= $s('footer_tagline') ?>" placeholder="Experience the Difference.">
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div class="card__header"><h2>Footer &amp; Contact</h2></div>
        <div class="card__body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="footer_phone">Phone</label>
                    <input class="form-control" type="text" id="footer_phone" name="footer_phone" value="<?= $s('footer_phone') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="footer_email">Email</label>
                    <input class="form-control" type="email" id="footer_email" name="footer_email" value="<?= $s('footer_email') ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="footer_address">Address</label>
                <input class="form-control" type="text" id="footer_address" name="footer_address" value="<?= $s('footer_address') ?>">
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div class="card__header"><h2>Social Links</h2></div>
        <div class="card__body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="social_instagram_url">Instagram URL</label>
                    <input class="form-control" type="url" id="social_instagram_url" name="social_instagram_url" value="<?= $s('social_instagram_url') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="social_facebook_url">Facebook URL</label>
                    <input class="form-control" type="url" id="social_facebook_url" name="social_facebook_url" value="<?= $s('social_facebook_url') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="social_linkedin_url">LinkedIn URL</label>
                    <input class="form-control" type="url" id="social_linkedin_url" name="social_linkedin_url" value="<?= $s('social_linkedin_url') ?>">
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Settings</button>
</form>
