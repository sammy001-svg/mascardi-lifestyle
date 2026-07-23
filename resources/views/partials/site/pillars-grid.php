<?php /** @var array $pillars */ ?>
<section class="section" id="pillars">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <span class="section-head__eyebrow">The Ecosystem</span>
            <h2 class="section-head__title">Eight Pillars, One Standard</h2>
            <p class="section-head__subtitle">Every Mascardi car purchase unlocks a curated world of partner benefits across these eight worlds.</p>
        </div>

        <?php if (empty($pillars)): ?>
            <div class="empty-note">Pillars will appear here once added from the admin panel.</div>
        <?php else: ?>
            <div class="pillar-grid">
                <?php foreach ($pillars as $i => $pillar): ?>
                    <a class="pillar-card js-tilt" href="<?= e($pillar['link_url'] ?: '#pillars') ?>" data-aos="fade-up" data-aos-delay="<?= ($i % 4) * 80 ?>">
                        <?php if (!empty($pillar['image_path'])): ?>
                            <img class="pillar-card__img" src="<?= e(upload_url($pillar['image_path'])) ?>" alt="<?= e($pillar['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="pillar-card__img pillar-card__img--placeholder">Image coming soon</div>
                        <?php endif; ?>
                        <div class="pillar-card__scrim"></div>
                        <div class="pillar-card__label">
                            <span class="pillar-card__index">0<?= $i + 1 ?></span>
                            <p class="pillar-card__name"><?= e($pillar['name']) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
