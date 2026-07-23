<?php /** @var array $partners */ ?>
<section class="section section--muted" id="partners">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <span class="section-head__eyebrow">Partners</span>
            <h2 class="section-head__title">Brands Behind the Benefits</h2>
            <p class="section-head__subtitle">The partner network is growing — new names are added as Mascardi Lifestyle expands.</p>
        </div>

        <?php if (empty($partners)): ?>
            <div class="empty-note">Partner brands coming soon.</div>
        <?php else: ?>
            <div class="partners-grid" data-aos="fade-up">
                <?php foreach ($partners as $partner): ?>
                    <?php if ($partner['website_url']): ?>
                        <a class="partner-tile" href="<?= e($partner['website_url']) ?>" target="_blank" rel="noopener" title="<?= e($partner['name']) ?>">
                            <img src="<?= e(upload_url($partner['logo_path'])) ?>" alt="<?= e($partner['name']) ?>" loading="lazy">
                        </a>
                    <?php else: ?>
                        <div class="partner-tile" title="<?= e($partner['name']) ?>">
                            <img src="<?= e(upload_url($partner['logo_path'])) ?>" alt="<?= e($partner['name']) ?>" loading="lazy">
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
