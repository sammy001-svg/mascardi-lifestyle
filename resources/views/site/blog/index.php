<?php
/**
 * @var array  $settings
 * @var array  $posts
 * @var array  $categories
 * @var array|null $activeCategory
 * @var int    $currentPage
 * @var int    $totalPages
 * @var int    $totalPosts
 */
use App\Core\View;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<style>
/* ── Blog Listing Page ─────────────────────────────────────────── */
.blog-page { background: var(--color-bg, #09090b); min-height: 100vh; }

/* Hero banner */
.blog-hero {
    position: relative;
    padding: 110px 0 70px;
    background: linear-gradient(135deg, #0a0a0f 0%, #111127 50%, #0a0a0f 100%);
    overflow: hidden;
    text-align: center;
}
.blog-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 60% 40% at 20% 50%, rgba(212,175,55,0.06) 0%, transparent 70%),
        radial-gradient(ellipse 40% 60% at 80% 30%, rgba(139,92,246,0.04) 0%, transparent 70%);
    pointer-events: none;
}
.blog-hero__eyebrow {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #d4af37;
    margin-bottom: 18px;
}
.blog-hero h1 {
    font-size: clamp(2.4rem, 5vw, 3.8rem);
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #fff;
    margin: 0 0 20px;
}
.blog-hero__sub {
    font-size: 1.05rem;
    color: rgba(255,255,255,0.55);
    max-width: 520px;
    margin: 0 auto;
    line-height: 1.65;
}

/* Layout */
.blog-body { max-width: 1280px; margin: 0 auto; padding: 60px 24px 80px; display: grid; grid-template-columns: 1fr 280px; gap: 48px; }
@media (max-width: 900px) { .blog-body { grid-template-columns: 1fr; } .blog-sidebar { order: -1; } }

/* Post grid */
.blog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 32px; align-content: start; }

/* Post card */
.post-card {
    background: rgba(255,255,255,0.035);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}
.post-card:hover {
    transform: translateY(-6px);
    border-color: rgba(212,175,55,0.3);
    box-shadow: 0 24px 60px rgba(0,0,0,0.4);
}
.post-card__img {
    position: relative;
    aspect-ratio: 16/9;
    overflow: hidden;
    background: #1a1a2e;
}
.post-card__img img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.post-card:hover .post-card__img img { transform: scale(1.05); }
.post-card__img-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    font-size: 3rem;
    background: linear-gradient(135deg, #0f0f1a, #1a1a2e);
    color: rgba(255,255,255,0.15);
}
.post-card__badge {
    position: absolute; top: 14px; left: 14px;
    background: rgba(212,175,55,0.15);
    border: 1px solid rgba(212,175,55,0.3);
    color: #d4af37;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 100px;
    backdrop-filter: blur(8px);
}
.post-card__body { padding: 24px; flex: 1; display: flex; flex-direction: column; gap: 10px; }
.post-card__date { font-size: 0.78rem; color: rgba(255,255,255,0.38); letter-spacing: 0.05em; }
.post-card__title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #f4f4f5;
    line-height: 1.4;
    text-decoration: none;
    transition: color 0.2s;
}
.post-card__title:hover { color: #d4af37; }
.post-card__excerpt {
    font-size: 0.875rem;
    color: rgba(255,255,255,0.5);
    line-height: 1.65;
    flex: 1;
}
.post-card__cta {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 0.82rem;
    font-weight: 700;
    color: #d4af37;
    text-decoration: none;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin-top: 4px;
    transition: gap 0.2s;
}
.post-card__cta:hover { gap: 10px; }

/* Empty state */
.blog-empty {
    grid-column: 1/-1;
    text-align: center;
    padding: 80px 24px;
}
.blog-empty__icon { font-size: 3.5rem; margin-bottom: 18px; }
.blog-empty__text { color: rgba(255,255,255,0.4); font-size: 1.05rem; }

/* Sidebar */
.blog-sidebar { display: flex; flex-direction: column; gap: 28px; }
.blog-widget {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    padding: 24px;
}
.blog-widget__title {
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #d4af37;
    margin-bottom: 18px;
}
.blog-widget__cats { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 2px; }
.blog-widget__cats li a {
    display: flex; justify-content: space-between; align-items: center;
    padding: 8px 12px;
    border-radius: 8px;
    color: rgba(255,255,255,0.65);
    text-decoration: none;
    font-size: 0.875rem;
    transition: background 0.2s, color 0.2s;
}
.blog-widget__cats li a:hover,
.blog-widget__cats li a.is-active { background: rgba(212,175,55,0.1); color: #d4af37; }

/* Pagination */
.blog-pagination {
    grid-column: 1 / -1;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
}
.blog-pagination a,
.blog-pagination span {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 40px; height: 40px;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s, color 0.2s;
}
.blog-pagination a { border: 1px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.6); }
.blog-pagination a:hover { background: rgba(212,175,55,0.12); border-color: rgba(212,175,55,0.3); color: #d4af37; }
.blog-pagination .is-active { background: #d4af37; color: #09090b; border-color: #d4af37; }
.blog-pagination span { color: rgba(255,255,255,0.3); }
</style>

<div class="blog-page">
    <!-- Hero -->
    <section class="blog-hero">
        <div>
            <span class="blog-hero__eyebrow">Mascardi Lifestyle</span>
            <h1>The Blog</h1>
            <p class="blog-hero__sub">Stories, insights & updates from Kenya's premier luxury car lifestyle programme.</p>
        </div>
    </section>

    <!-- Body: grid + sidebar -->
    <div class="blog-body">
        <!-- Post grid -->
        <div>
            <div class="blog-grid">
                <?php if (empty($posts)): ?>
                    <div class="blog-empty">
                        <div class="blog-empty__icon">✍️</div>
                        <p class="blog-empty__text">
                            <?= $activeCategory ? 'No posts in this category yet.' : 'No posts published yet. Check back soon.' ?>
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card" data-aos="fade-up">
                            <div class="post-card__img">
                                <?php if ($post['cover_image_path']): ?>
                                    <img src="<?= e(upload_url($post['cover_image_path'])) ?>"
                                         alt="<?= e($post['title']) ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="post-card__img-placeholder">✍️</div>
                                <?php endif; ?>
                                <?php if ($post['category_name']): ?>
                                    <span class="post-card__badge"><?= e($post['category_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="post-card__body">
                                <div class="post-card__date">
                                    <?= e(date('d M Y', strtotime($post['published_at']))) ?>
                                </div>
                                <a class="post-card__title" href="<?= site_url('blog/' . e($post['slug'])) ?>">
                                    <?= e($post['title']) ?>
                                </a>
                                <?php if ($post['excerpt']): ?>
                                    <p class="post-card__excerpt"><?= e(mb_strimwidth($post['excerpt'], 0, 140, '…')) ?></p>
                                <?php endif; ?>
                                <a class="post-card__cta" href="<?= site_url('blog/' . e($post['slug'])) ?>">
                                    Read Article &rarr;
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="blog-pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>">&larr;</a>
                    <?php endif; ?>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <?php if ($p === $currentPage): ?>
                            <span class="is-active"><?= $p ?></span>
                        <?php else: ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>">&rarr;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="blog-sidebar">
            <!-- Categories -->
            <?php if (!empty($categories)): ?>
                <div class="blog-widget">
                    <div class="blog-widget__title">Topics</div>
                    <ul class="blog-widget__cats">
                        <li>
                            <a href="<?= site_url('blog') ?>" class="<?= !$activeCategory ? 'is-active' : '' ?>">
                                All Posts <span>→</span>
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="<?= site_url('blog?category=' . e($cat['slug'])) ?>"
                                   class="<?= $activeCategory && $activeCategory['id'] == $cat['id'] ? 'is-active' : '' ?>">
                                    <?= e($cat['name']) ?> <span>→</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- CTA box -->
            <div class="blog-widget" style="background:linear-gradient(135deg,rgba(212,175,55,0.08),rgba(212,175,55,0.02));border-color:rgba(212,175,55,0.2);">
                <div class="blog-widget__title">Join the Club</div>
                <p style="font-size:0.87rem;color:rgba(255,255,255,0.55);line-height:1.65;margin-bottom:18px;">
                    Become part of Kenya's most exclusive automotive lifestyle community.
                </p>
                <a href="<?= site_url('contact') ?>" style="display:block;text-align:center;padding:11px 20px;background:#d4af37;color:#09090b;border-radius:8px;font-weight:700;font-size:0.85rem;letter-spacing:0.06em;text-decoration:none;text-transform:uppercase;">
                    Get in Touch
                </a>
            </div>
        </aside>
    </div>
</div>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
