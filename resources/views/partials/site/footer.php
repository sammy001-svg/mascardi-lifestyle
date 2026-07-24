<?php /** @var array $settings */ ?>
<footer class="site-footer">
    <div class="container">
        <div class="site-footer__grid">
            <div>
                <div class="site-footer__brand"><?= e($settings['site_name'] ?? 'Mascardi Lifestyle') ?></div>
                <p class="site-footer__tagline"><?= e($settings['footer_tagline'] ?? 'Experience the Difference.') ?></p>
            </div>
            <div>
                <h4>Explore</h4>
                <ul>
                    <li><a href="<?= site_url() ?>#pillars">Pillars</a></li>
                    <li><a href="<?= site_url() ?>#partners">Partners</a></li>
                    <li><a href="<?= site_url('shop') ?>">Shop Mascardi</a></li>
                    <li><a href="<?= site_url('events') ?>">Events</a></li>
                    <li><a href="<?= site_url('contact') ?>">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4>Contact</h4>
                <ul>
                    <?php if (!empty($settings['footer_address'])): ?><li><?= e($settings['footer_address']) ?></li><?php endif; ?>
                    <?php if (!empty($settings['footer_phone'])): ?><li><a href="tel:<?= e($settings['footer_phone']) ?>"><?= e($settings['footer_phone']) ?></a></li><?php endif; ?>
                    <?php if (!empty($settings['footer_email'])): ?><li><a href="mailto:<?= e($settings['footer_email']) ?>"><?= e($settings['footer_email']) ?></a></li><?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="site-footer__bottom">
            <span>&copy; <?= date('Y') ?> <?= e($settings['site_name'] ?? 'Mascardi Lifestyle') ?>. All rights reserved.</span>
            <div class="site-footer__social">
                <?php if (!empty($settings['social_instagram_url'])): ?><a href="<?= e($settings['social_instagram_url']) ?>" target="_blank" rel="noopener" aria-label="Instagram">IG</a><?php endif; ?>
                <?php if (!empty($settings['social_facebook_url'])): ?><a href="<?= e($settings['social_facebook_url']) ?>" target="_blank" rel="noopener" aria-label="Facebook">FB</a><?php endif; ?>
                <?php if (!empty($settings['social_linkedin_url'])): ?><a href="<?= e($settings['social_linkedin_url']) ?>" target="_blank" rel="noopener" aria-label="LinkedIn">IN</a><?php endif; ?>
            </div>
        </div>
    </div>
</footer>
