<?php
/**
 * @var array $settings
 * @var array $post
 * @var array $recent
 */
use App\Core\View;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<style>
/* ── Blog Article Page ──────────────────────────────────────────── */
.article-page { background: #09090b; min-height: 100vh; }

/* Hero */
.article-hero {
    position: relative;
    min-height: 420px;
    display: flex;
    align-items: flex-end;
    padding: 0 0 60px;
    overflow: hidden;
    background: #0a0a0f;
}
.article-hero__bg {
    position: absolute; inset: 0;
    background-size: cover;
    background-position: center;
    filter: brightness(0.3) saturate(0.8);
    transition: transform 8s ease;
}
.article-hero:hover .article-hero__bg { transform: scale(1.03); }
.article-hero::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(9,9,11,0.95) 0%, rgba(9,9,11,0.4) 60%, transparent 100%);
}
.article-hero__inner {
    position: relative; z-index: 1;
    width: 100%; max-width: 820px;
    margin: 0 auto;
    padding: 0 24px;
}
.article-hero__cat {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #d4af37;
    margin-bottom: 16px;
    text-decoration: none;
    transition: opacity 0.2s;
}
.article-hero__cat:hover { opacity: 0.7; }
.article-hero h1 {
    font-size: clamp(1.9rem, 4vw, 3rem);
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #fff;
    line-height: 1.2;
    margin: 0 0 20px;
}
.article-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    font-size: 0.82rem;
    color: rgba(255,255,255,0.45);
}
.article-meta span { display: flex; align-items: center; gap: 5px; }

/* Content layout */
.article-body {
    max-width: 1100px;
    margin: 0 auto;
    padding: 60px 24px 80px;
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 60px;
    align-items: start;
}
@media (max-width: 860px) { .article-body { grid-template-columns: 1fr; } }

/* Prose */
.article-prose {
    color: rgba(255,255,255,0.75);
    font-size: 1.05rem;
    line-height: 1.85;
}
.article-prose h2,
.article-prose h3,
.article-prose h4 { color: #fff; margin: 1.8em 0 0.6em; font-weight: 700; line-height: 1.3; }
.article-prose h2 { font-size: 1.7rem; }
.article-prose h3 { font-size: 1.35rem; }
.article-prose p { margin: 0 0 1.4em; }
.article-prose a { color: #d4af37; text-decoration: underline; text-underline-offset: 3px; }
.article-prose img { width: 100%; border-radius: 12px; margin: 1.5em 0; }
.article-prose blockquote {
    border-left: 3px solid #d4af37;
    margin: 2em 0; padding: 12px 20px;
    font-style: italic;
    color: rgba(255,255,255,0.55);
    background: rgba(212,175,55,0.05);
    border-radius: 0 8px 8px 0;
}
.article-prose ul, .article-prose ol { margin: 0 0 1.4em; padding-left: 1.5em; }
.article-prose li { margin-bottom: 0.5em; }
.article-prose pre {
    background: #111; border: 1px solid rgba(255,255,255,0.08); border-radius: 10px;
    padding: 18px; overflow-x: auto; font-size: 0.9em; margin: 1.5em 0;
}
.article-prose code { background: rgba(255,255,255,0.07); padding: 2px 6px; border-radius: 4px; font-size: 0.88em; }

/* Back link */
.article-back {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 0.8rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;
    color: rgba(255,255,255,0.4);
    text-decoration: none;
    transition: color 0.2s;
    margin-bottom: 36px;
}
.article-back:hover { color: #d4af37; }

/* Sidebar */
.article-sidebar { display: flex; flex-direction: column; gap: 24px; }
.aside-widget {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    padding: 22px;
}
.aside-widget__title {
    font-size: 0.68rem; font-weight: 800; letter-spacing: 0.18em;
    text-transform: uppercase; color: #d4af37; margin-bottom: 16px;
}

/* Recent post mini cards */
.recent-post { display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
.recent-post:last-child { border-bottom: none; padding-bottom: 0; }
.recent-post__thumb {
    width: 60px; height: 50px; border-radius: 8px;
    object-fit: cover; flex-shrink: 0;
    background: #1a1a2e;
}
.recent-post__text a {
    font-size: 0.82rem; font-weight: 600; color: rgba(255,255,255,0.75);
    text-decoration: none; line-height: 1.4; display: block; margin-bottom: 4px;
}
.recent-post__text a:hover { color: #d4af37; }
.recent-post__date { font-size: 0.72rem; color: rgba(255,255,255,0.3); }
</style>

<div class="article-page">
    <!-- Hero -->
    <div class="article-hero">
        <?php if ($post['cover_image_path']): ?>
            <div class="article-hero__bg"
                 style="background-image:url('<?= e(upload_url($post['cover_image_path'])) ?>');"></div>
        <?php else: ?>
            <div class="article-hero__bg"
                 style="background:linear-gradient(135deg,#0f0f1a,#1a1a2e);"></div>
        <?php endif; ?>
        <div class="article-hero__inner">
            <?php if ($post['category_name']): ?>
                <a class="article-hero__cat"
                   href="<?= site_url('blog?category=' . e($post['category_slug'])) ?>">
                    ← <?= e($post['category_name']) ?>
                </a>
            <?php endif; ?>
            <h1><?= e($post['title']) ?></h1>
            <div class="article-meta">
                <?php if ($post['author_name']): ?>
                    <span>✍️ <?= e($post['author_name']) ?></span>
                <?php endif; ?>
                <span>📅 <?= e(date('d M Y', strtotime($post['published_at']))) ?></span>
            </div>
        </div>
    </div>

    <!-- Article body + sidebar -->
    <div class="article-body">
        <div>
            <a class="article-back" href="<?= site_url('blog') ?>">← All Articles</a>
            <div class="article-prose">
                <?= $post['body'] ?: '<p style="color:rgba(255,255,255,0.4);">Article content coming soon.</p>' ?>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="article-sidebar">
            <?php if (!empty($recent)): ?>
                <div class="aside-widget">
                    <div class="aside-widget__title">Recent Articles</div>
                    <?php foreach ($recent as $r): ?>
                        <div class="recent-post">
                            <?php if ($r['cover_image_path']): ?>
                                <img class="recent-post__thumb"
                                     src="<?= e(upload_url($r['cover_image_path'])) ?>"
                                     alt="<?= e($r['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="recent-post__thumb" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem;">✍️</div>
                            <?php endif; ?>
                            <div class="recent-post__text">
                                <a href="<?= site_url('blog/' . e($r['slug'])) ?>"><?= e($r['title']) ?></a>
                                <div class="recent-post__date"><?= e(date('d M Y', strtotime($r['published_at']))) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="aside-widget" style="background:linear-gradient(135deg,rgba(212,175,55,0.08),rgba(212,175,55,0.02));border-color:rgba(212,175,55,0.2);text-align:center;">
                <div class="aside-widget__title">Mascardi Lifestyle</div>
                <p style="font-size:0.85rem;color:rgba(255,255,255,0.5);line-height:1.6;margin-bottom:16px;">
                    Kenya's most exclusive automotive lifestyle experience.
                </p>
                <a href="<?= site_url('contact') ?>"
                   style="display:block;padding:10px 18px;background:#d4af37;color:#09090b;border-radius:8px;font-weight:700;font-size:0.82rem;text-decoration:none;letter-spacing:0.06em;text-transform:uppercase;">
                    Contact Us
                </a>
            </div>
        </aside>
    </div>
</div>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
