<?php
/** @var array $settings */
/** @var array $pillar */
/** @var int $index */
/** @var array $partners */
use App\Core\View;

$hasImage = !empty($pillar['image_path']);
$eyebrow = $index > 0 ? sprintf('Pillar %02d', $index) : 'Pillar';
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<section class="pillar-hero<?= $hasImage ? ' pillar-hero--image' : '' ?>">
    <?php if ($hasImage): ?>
        <img class="pillar-hero__img" src="<?= e(upload_url($pillar['image_path'])) ?>" alt="<?= e($pillar['name']) ?>">
        <div class="pillar-hero__scrim"></div>
    <?php endif; ?>
    <div class="container pillar-hero__inner">
        <span class="pillar-hero__eyebrow"><?= e($eyebrow) ?></span>
        <h1 class="pillar-hero__title"><?= e($pillar['name']) ?></h1>
        <?php if (!empty($pillar['description'])): ?>
            <p class="pillar-hero__intro"><?= e($pillar['description']) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container container--readable">
        <?php if (!empty(trim((string) $pillar['body']))): ?>
            <div class="pillar-body">
                <?php foreach (preg_split('/\n\s*\n/', trim($pillar['body'])) as $para): ?>
                    <?php if (trim($para) !== ''): ?><p><?= nl2br(e(trim($para))) ?></p><?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="pillar-body pillar-body--empty">Full details about this pillar are coming soon.</p>
        <?php endif; ?>

        <?php if (!empty($pillar['link_url'])): ?>
            <div style="margin-top:34px;">
                <a href="<?= e($pillar['link_url']) ?>" target="_blank" rel="noopener" class="btn btn-dark">Learn More</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($partners)): ?>
    <section class="section section--muted">
        <div class="container">
            <div class="section-head" data-aos="fade-up">
                <span class="section-head__eyebrow">Partners</span>
                <h2 class="section-head__title">In This Pillar</h2>
            </div>
            <div class="partners-grid">
                <?php foreach ($partners as $partner): ?>
                    <?php $hasSite = !empty($partner['website_url']); ?>
                    <<?= $hasSite ? 'a' : 'div' ?> class="partner-tile"<?= $hasSite ? ' href="' . e($partner['website_url']) . '" target="_blank" rel="noopener"' : '' ?>>
                        <?php if (!empty($partner['logo_path'])): ?>
                            <img src="<?= e(upload_url($partner['logo_path'])) ?>" alt="<?= e($partner['name']) ?>">
                        <?php else: ?>
                            <span class="partner-tile__name"><?= e($partner['name']) ?></span>
                        <?php endif; ?>
                    </<?= $hasSite ? 'a' : 'div' ?>>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="section pillar-back">
    <div class="container" style="text-align:center;">
        <a href="<?= site_url() ?>#pillars" class="btn btn-dark">&larr; Explore All Pillars</a>
    </div>
</section>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
