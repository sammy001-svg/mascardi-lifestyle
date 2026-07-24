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
                    <?php $hasImage = !empty($pillar['image_path']); ?>
                    <a class="pillar-card js-tilt<?= $hasImage ? ' pillar-card--image' : '' ?>"
                       href="<?= e($pillar['link_url'] ?: '#pillars') ?>"
                       data-aos="fade-up" data-aos-delay="<?= ($i % 4) * 90 ?>"
                       data-tilt-max="10" data-tilt-speed="500" data-tilt-scale="1.03"
                       data-tilt-glare="true" data-tilt-max-glare="0.18">
                        <?php if ($hasImage): ?>
                            <div class="pillar-card__media">
                                <img class="pillar-card__img" src="<?= e(upload_url($pillar['image_path'])) ?>" alt="<?= e($pillar['name']) ?>" loading="lazy">
                                <div class="pillar-card__scrim"></div>
                            </div>
                        <?php endif; ?>
                        <span class="pillar-card__num" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
                        <div class="pillar-card__label">
                            <p class="pillar-card__name"><?= e($pillar['name']) ?></p>
                            <span class="pillar-card__explore">Explore <span class="pillar-card__arrow">&rarr;</span></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
