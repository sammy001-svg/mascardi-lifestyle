<?php
/**
 * @var array $settings
 * @var array $albums
 */
use App\Core\View;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<style>
/* ── Gallery Listing Page ───────────────────────────────────────── */
.gallery-page { background: #09090b; min-height: 100vh; }

/* Hero */
.gallery-hero {
    position: relative;
    padding: 110px 0 70px;
    background: linear-gradient(135deg, #0a0a0f 0%, #0f1117 50%, #0a0a0f 100%);
    text-align: center;
    overflow: hidden;
}
.gallery-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 50% 50% at 30% 50%, rgba(212,175,55,0.06) 0%, transparent 70%),
        radial-gradient(ellipse 40% 60% at 70% 30%, rgba(139,92,246,0.04) 0%, transparent 70%);
    pointer-events: none;
}
.gallery-hero__eyebrow {
    display: inline-block;
    font-size: 0.72rem; font-weight: 800;
    letter-spacing: 0.22em; text-transform: uppercase;
    color: #d4af37; margin-bottom: 18px;
}
.gallery-hero h1 {
    font-size: clamp(2.4rem, 5vw, 3.8rem);
    font-weight: 800; letter-spacing: -0.02em;
    color: #fff; margin: 0 0 18px;
}
.gallery-hero__sub {
    font-size: 1.05rem; color: rgba(255,255,255,0.5);
    max-width: 480px; margin: 0 auto; line-height: 1.65;
}

/* Albums grid */
.gallery-grid-wrap { max-width: 1280px; margin: 0 auto; padding: 60px 24px 80px; }
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 28px;
}
@media (max-width: 600px) { .gallery-grid { grid-template-columns: 1fr; } }

/* Album card */
.album-card {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    aspect-ratio: 4/3;
    background: #0f0f1a;
    cursor: pointer;
    display: block;
    text-decoration: none;
    transition: transform 0.35s ease, box-shadow 0.35s ease;
}
.album-card:hover { transform: scale(1.02); box-shadow: 0 30px 70px rgba(0,0,0,0.5); }
.album-card__img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease, filter 0.4s ease;
    filter: brightness(0.75) saturate(0.9);
}
.album-card:hover .album-card__img {
    transform: scale(1.06);
    filter: brightness(0.55) saturate(0.8);
}
.album-card__placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    font-size: 3.5rem;
    background: linear-gradient(135deg, #0f0f1a, #1a1a2e);
    color: rgba(255,255,255,0.1);
}
.album-card__overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.1) 55%, transparent 100%);
    display: flex; flex-direction: column; justify-content: flex-end;
    padding: 26px;
    transition: background 0.3s;
}
.album-card__count {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.7rem; font-weight: 800;
    letter-spacing: 0.15em; text-transform: uppercase;
    color: #d4af37;
    margin-bottom: 8px;
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.3s 0.05s, transform 0.3s 0.05s;
}
.album-card:hover .album-card__count { opacity: 1; transform: translateY(0); }
.album-card__name {
    font-size: 1.35rem; font-weight: 800;
    color: #fff; line-height: 1.25;
    letter-spacing: -0.01em;
}
.album-card__desc {
    font-size: 0.82rem; color: rgba(255,255,255,0.55);
    margin-top: 6px; line-height: 1.5;
    max-height: 0; overflow: hidden;
    transition: max-height 0.35s ease, opacity 0.35s ease, margin 0.3s ease;
    opacity: 0;
}
.album-card:hover .album-card__desc {
    max-height: 80px;
    opacity: 1;
    margin-top: 8px;
}
.album-card__cta {
    display: inline-flex; align-items: center; gap: 6px;
    margin-top: 14px;
    font-size: 0.8rem; font-weight: 700;
    letter-spacing: 0.1em; text-transform: uppercase;
    color: #d4af37;
    opacity: 0;
    transform: translateY(6px);
    transition: opacity 0.3s 0.08s, transform 0.3s 0.08s;
}
.album-card:hover .album-card__cta { opacity: 1; transform: translateY(0); }

/* Empty state */
.gallery-empty { text-align: center; padding: 100px 24px; color: rgba(255,255,255,0.35); }
.gallery-empty__icon { font-size: 4rem; margin-bottom: 20px; }
</style>

<div class="gallery-page">
    <!-- Hero -->
    <section class="gallery-hero">
        <div>
            <span class="gallery-hero__eyebrow">Mascardi Lifestyle</span>
            <h1>Gallery</h1>
            <p class="gallery-hero__sub">Curated moments from Kenya's most exclusive automotive lifestyle programme.</p>
        </div>
    </section>

    <!-- Albums -->
    <div class="gallery-grid-wrap">
        <?php if (empty($albums)): ?>
            <div class="gallery-empty">
                <div class="gallery-empty__icon">📷</div>
                <p>No albums published yet. Check back soon.</p>
            </div>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($albums as $album): ?>
                    <a class="album-card" href="<?= site_url('gallery/' . e($album['slug'])) ?>"
                       data-aos="fade-up" aria-label="<?= e($album['name']) ?> — <?= (int) $album['image_count'] ?> photos">
                        <?php if ($album['cover_image_path']): ?>
                            <img class="album-card__img"
                                 src="<?= e(upload_url($album['cover_image_path'])) ?>"
                                 alt="<?= e($album['name']) ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="album-card__placeholder">📷</div>
                        <?php endif; ?>
                        <div class="album-card__overlay">
                            <div class="album-card__count">📷 <?= (int) $album['image_count'] ?> photos</div>
                            <div class="album-card__name"><?= e($album['name']) ?></div>
                            <?php if ($album['description']): ?>
                                <div class="album-card__desc"><?= e(mb_strimwidth($album['description'], 0, 100, '…')) ?></div>
                            <?php endif; ?>
                            <div class="album-card__cta">View Album →</div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
