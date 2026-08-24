<?php
/**
 * @var array $settings
 * @var array $album
 * @var array $images
 * @var array $albums
 */
use App\Core\View;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<style>
/* ── Gallery Album Detail Page ──────────────────────────────────── */
.album-page { background: #09090b; min-height: 100vh; }

/* Hero */
.album-hero {
    position: relative;
    height: 380px;
    display: flex; align-items: flex-end;
    padding: 0 0 50px;
    overflow: hidden;
    background: #0a0a0f;
}
.album-hero__bg {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
    filter: brightness(0.28) saturate(0.75);
    transition: transform 8s ease;
}
.album-hero:hover .album-hero__bg { transform: scale(1.03); }
.album-hero::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(9,9,11,0.95) 0%, rgba(9,9,11,0.4) 60%, transparent 100%);
}
.album-hero__inner {
    position: relative; z-index: 1;
    width: 100%; max-width: 1280px;
    margin: 0 auto; padding: 0 24px;
}
.album-hero__back {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 0.75rem; font-weight: 800;
    letter-spacing: 0.12em; text-transform: uppercase;
    color: rgba(255,255,255,0.45);
    text-decoration: none;
    margin-bottom: 16px;
    transition: color 0.2s;
}
.album-hero__back:hover { color: #d4af37; }
.album-hero h1 {
    font-size: clamp(1.9rem, 3.5vw, 3rem);
    font-weight: 800; letter-spacing: -0.02em;
    color: #fff; margin: 0 0 10px;
}
.album-hero__meta { font-size: 0.82rem; color: rgba(255,255,255,0.4); }
.album-hero__meta span { color: #d4af37; }

/* Lightbox grid */
.album-body { max-width: 1280px; margin: 0 auto; padding: 48px 24px 80px; }

/* Masonry-style responsive grid */
.album-grid {
    columns: 4;
    column-gap: 14px;
}
@media (max-width: 1100px) { .album-grid { columns: 3; } }
@media (max-width: 700px)  { .album-grid { columns: 2; } }
@media (max-width: 420px)  { .album-grid { columns: 1; } }

/* CSS lightbox: each image is a link to an anchor, the anchor shows a full-screen overlay */
.album-item {
    break-inside: avoid;
    margin-bottom: 14px;
    display: block;
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none;
    cursor: zoom-in;
}
.album-item img {
    width: 100%; display: block;
    transition: transform 0.4s ease, filter 0.3s ease;
    filter: brightness(0.92);
}
.album-item:hover img { transform: scale(1.04); filter: brightness(0.7); }
.album-item__overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 60%);
    display: flex; align-items: flex-end;
    padding: 14px;
    opacity: 0;
    transition: opacity 0.3s;
}
.album-item:hover .album-item__overlay { opacity: 1; }
.album-item__caption {
    font-size: 0.8rem; color: rgba(255,255,255,0.8);
    font-style: italic; line-height: 1.3;
}

/* CSS lightbox overlays (no JS needed) */
.lightbox-target {
    display: none;
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,0.94);
    align-items: center; justify-content: center;
    flex-direction: column;
    padding: 24px;
    animation: lbFade 0.25s ease;
}
@keyframes lbFade { from { opacity: 0; } to { opacity: 1; } }
.lightbox-target:target { display: flex; }
.lightbox-target img {
    max-width: 90vw; max-height: 82vh;
    object-fit: contain;
    border-radius: 10px;
    box-shadow: 0 30px 80px rgba(0,0,0,0.5);
}
.lightbox-target__caption {
    margin-top: 14px;
    font-size: 0.88rem; color: rgba(255,255,255,0.55);
    font-style: italic;
    text-align: center;
}
.lightbox-close {
    position: absolute; top: 22px; right: 26px;
    font-size: 1.8rem;
    color: rgba(255,255,255,0.5);
    text-decoration: none;
    line-height: 1;
    transition: color 0.2s, transform 0.2s;
}
.lightbox-close:hover { color: #d4af37; transform: rotate(90deg); }
.lightbox-nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    font-size: 2rem; color: rgba(255,255,255,0.4);
    text-decoration: none;
    transition: color 0.2s;
    padding: 8px 20px;
}
.lightbox-nav:hover { color: #d4af37; }
.lightbox-nav--prev { left: 16px; }
.lightbox-nav--next { right: 16px; }

/* Empty state */
.album-empty { text-align: center; padding: 80px 24px; color: rgba(255,255,255,0.35); }
.album-empty__icon { font-size: 3.5rem; margin-bottom: 18px; }

/* Other albums strip */
.other-albums { max-width: 1280px; margin: 0 auto; padding: 0 24px 80px; }
.other-albums__title {
    font-size: 0.7rem; font-weight: 800; letter-spacing: 0.2em;
    text-transform: uppercase; color: #d4af37; margin-bottom: 22px;
}
.other-albums__strip {
    display: flex; gap: 16px; overflow-x: auto;
    padding-bottom: 8px;
    scrollbar-width: thin; scrollbar-color: rgba(212,175,55,0.3) transparent;
}
.other-album-thumb {
    flex-shrink: 0;
    width: 160px; height: 110px;
    border-radius: 12px; overflow: hidden;
    position: relative;
    display: block; text-decoration: none;
    transition: transform 0.3s;
    border: 1px solid rgba(255,255,255,0.07);
}
.other-album-thumb:hover { transform: scale(1.04); }
.other-album-thumb img, .other-album-thumb__placeholder {
    width: 100%; height: 100%; object-fit: cover;
    filter: brightness(0.65);
}
.other-album-thumb__placeholder {
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; background: #1a1a2e; color: rgba(255,255,255,0.15);
}
.other-album-thumb__label {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    font-size: 0.75rem; font-weight: 700;
    color: rgba(255,255,255,0.85);
    padding: 12px 10px 8px;
}
</style>

<div class="album-page">
    <!-- Hero -->
    <div class="album-hero">
        <div class="album-hero__bg"
             style="background-image:url('<?= $album['cover_image_path'] ? e(upload_url($album['cover_image_path'])) : '' ?>');
                    <?= !$album['cover_image_path'] ? 'background:linear-gradient(135deg,#0f0f1a,#1a1a2e);' : '' ?>">
        </div>
        <div class="album-hero__inner">
            <a class="album-hero__back" href="<?= site_url('gallery') ?>">← All Albums</a>
            <h1><?= e($album['name']) ?></h1>
            <div class="album-hero__meta">
                <span><?= (int) $album['image_count'] ?></span> photo<?= $album['image_count'] != 1 ? 's' : '' ?>
                <?php if ($album['description']): ?>
                    &nbsp;·&nbsp; <?= e($album['description']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Lightbox targets (hidden until clicked) -->
    <?php foreach ($images as $i => $img): ?>
        <?php
            $prevImg = $images[$i - 1] ?? null;
            $nextImg = $images[$i + 1] ?? null;
        ?>
        <div class="lightbox-target" id="lb-<?= (int) $img['id'] ?>">
            <a class="lightbox-close" href="#">&times;</a>
            <?php if ($prevImg): ?>
                <a class="lightbox-nav lightbox-nav--prev" href="#lb-<?= (int) $prevImg['id'] ?>">&#8249;</a>
            <?php endif; ?>
            <img src="<?= e(upload_url($img['image_path'])) ?>" alt="<?= e($img['caption'] ?? '') ?>">
            <?php if ($img['caption']): ?>
                <div class="lightbox-target__caption"><?= e($img['caption']) ?></div>
            <?php endif; ?>
            <?php if ($nextImg): ?>
                <a class="lightbox-nav lightbox-nav--next" href="#lb-<?= (int) $nextImg['id'] ?>">&#8250;</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <!-- Masonry photo grid -->
    <div class="album-body">
        <?php if (empty($images)): ?>
            <div class="album-empty">
                <div class="album-empty__icon">📷</div>
                <p>No photos in this album yet.</p>
            </div>
        <?php else: ?>
            <div class="album-grid">
                <?php foreach ($images as $img): ?>
                    <a class="album-item"
                       href="#lb-<?= (int) $img['id'] ?>"
                       aria-label="<?= e($img['caption'] ?? 'View photo') ?>"
                       data-aos="fade-up">
                        <img src="<?= e(upload_url($img['image_path'])) ?>"
                             alt="<?= e($img['caption'] ?? '') ?>"
                             loading="lazy">
                        <?php if ($img['caption']): ?>
                            <div class="album-item__overlay">
                                <span class="album-item__caption"><?= e($img['caption']) ?></span>
                            </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Other albums -->
    <?php $otherAlbums = array_filter($albums, fn($a) => (int) $a['id'] !== (int) $album['id']); ?>
    <?php if (!empty($otherAlbums)): ?>
        <div class="other-albums">
            <div class="other-albums__title">More Albums</div>
            <div class="other-albums__strip">
                <?php foreach ($otherAlbums as $oa): ?>
                    <a class="other-album-thumb" href="<?= site_url('gallery/' . e($oa['slug'])) ?>">
                        <?php if ($oa['cover_image_path']): ?>
                            <img src="<?= e(upload_url($oa['cover_image_path'])) ?>" alt="<?= e($oa['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="other-album-thumb__placeholder">📷</div>
                        <?php endif; ?>
                        <div class="other-album-thumb__label"><?= e($oa['name']) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
