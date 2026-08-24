<?php
/**
 * @var array $settings
 *
 * Expected settings keys (all optional, fall back to empty):
 *   site_name, footer_tagline, footer_address, footer_phone, footer_email,
 *   social_instagram_url, social_facebook_url, social_linkedin_url,
 *   social_twitter_url, social_youtube_url
 */
$name    = e($settings['site_name']    ?? 'Mascardi Lifestyle');
$tagline = e($settings['footer_tagline'] ?? "Kenya's Premier Automotive Lifestyle Programme.");
?>

<footer class="sf" role="contentinfo">

    <!-- ── Top rule ─────────────────────────────────────────────── -->
    <div class="sf__top-rule" aria-hidden="true"></div>

    <!-- ── Main grid ─────────────────────────────────────────────── -->
    <div class="container sf__body">

        <!-- Column 1 · Brand + socials -->
        <div class="sf__col sf__col--brand">
            <a href="<?= site_url() ?>" class="sf__wordmark" aria-label="<?= $name ?> — home">
                <?= $name ?>
            </a>
            <p class="sf__tagline"><?= $tagline ?></p>

            <!-- Decorative thin line -->
            <div class="sf__brand-rule" aria-hidden="true"></div>

            <!-- Social icons -->
            <?php
            $socials = [
                'instagram' => ['url' => $settings['social_instagram_url'] ?? '', 'label' => 'Instagram', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>'],
                'facebook'  => ['url' => $settings['social_facebook_url']  ?? '', 'label' => 'Facebook',  'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>'],
                'linkedin'  => ['url' => $settings['social_linkedin_url']  ?? '', 'label' => 'LinkedIn',  'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>'],
                'twitter'   => ['url' => $settings['social_twitter_url']   ?? '', 'label' => 'X / Twitter','svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.265 5.637 5.9-5.637Zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>'],
                'youtube'   => ['url' => $settings['social_youtube_url']   ?? '', 'label' => 'YouTube',   'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 0 0 1.46 6.42 29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58 2.78 2.78 0 0 0 1.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.96-1.96A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg>'],
            ];
            $activeSocials = array_filter($socials, fn($s) => !empty($s['url']));
            ?>
            <?php if (!empty($activeSocials)): ?>
                <div class="sf__socials">
                    <?php foreach ($activeSocials as $social): ?>
                        <a href="<?= e($social['url']) ?>"
                           class="sf__social-btn"
                           target="_blank"
                           rel="noopener noreferrer"
                           aria-label="<?= e($social['label']) ?> — <?= $name ?>">
                            <?= $social['svg'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Column 2 · Explore links -->
        <div class="sf__col">
            <h3 class="sf__col-title">Explore</h3>
            <nav aria-label="Footer explore navigation">
                <ul class="sf__links">
                    <li><a href="<?= site_url() ?>#pillars">Pillars</a></li>
                    <li><a href="<?= site_url() ?>#partners">Partners</a></li>
                    <li><a href="<?= site_url('shop') ?>">Shop</a></li>
                    <li><a href="<?= site_url('events') ?>">Events</a></li>
                    <li><a href="<?= site_url('blog') ?>">Blog</a></li>
                    <li><a href="<?= site_url('gallery') ?>">Gallery</a></li>
                    <li><a href="<?= site_url('contact') ?>">Contact</a></li>
                </ul>
            </nav>
        </div>

        <!-- Column 3 · Contact details -->
        <div class="sf__col">
            <h3 class="sf__col-title">Connect</h3>
            <ul class="sf__contact-list">
                <?php if (!empty($settings['footer_address'])): ?>
                    <li>
                        <!-- pin icon -->
                        <svg class="sf__contact-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span><?= nl2br(e($settings['footer_address'])) ?></span>
                    </li>
                <?php endif; ?>
                <?php if (!empty($settings['footer_phone'])): ?>
                    <li>
                        <!-- phone icon -->
                        <svg class="sf__contact-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.6 3.4 2 2 0 0 1 3.57 1.22h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.72a16 16 0 0 0 5.37 5.37l.86-.86a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21 15.7v1.22z"/></svg>
                        <a href="tel:<?= e(preg_replace('/\s+/', '', $settings['footer_phone'])) ?>"><?= e($settings['footer_phone']) ?></a>
                    </li>
                <?php endif; ?>
                <?php if (!empty($settings['footer_email'])): ?>
                    <li>
                        <!-- email icon -->
                        <svg class="sf__contact-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <a href="mailto:<?= e($settings['footer_email']) ?>"><?= e($settings['footer_email']) ?></a>
                    </li>
                <?php endif; ?>
                <?php if (empty($settings['footer_address']) && empty($settings['footer_phone']) && empty($settings['footer_email'])): ?>
                    <li style="color:var(--sf-muted);">Contact details coming soon.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Column 4 · CTA card -->
        <div class="sf__col">
            <div class="sf__cta-card">
                <p class="sf__cta-eyebrow">Join Us</p>
                <h3 class="sf__cta-title">Become a Member</h3>
                <p class="sf__cta-body">
                    Step into Kenya's most exclusive automotive lifestyle community.
                    Drive together. Live the difference.
                </p>
                <a href="<?= site_url('contact') ?>" class="sf__cta-btn">Get in Touch</a>
            </div>
        </div>

    </div><!-- /.sf__body -->

    <!-- ── Bottom bar ────────────────────────────────────────────── -->
    <div class="sf__bottom">
        <div class="container sf__bottom-inner">
            <span class="sf__copy">&copy; <?= date('Y') ?> <?= $name ?>. All rights reserved.</span>
            <nav class="sf__legal" aria-label="Legal links">
                <a href="<?= site_url('contact') ?>">Privacy</a>
                <span aria-hidden="true">·</span>
                <a href="<?= site_url('contact') ?>">Terms</a>
            </nav>
            <span class="sf__accreditation">Kenya's Premier Automotive Lifestyle Programme</span>
        </div>
    </div>

</footer>
