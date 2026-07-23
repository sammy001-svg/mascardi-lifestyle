<?php
/** @var array $settings */
/** @var array $products */
/** @var array $categories */
/** @var string $activeCategory */
use App\Core\View;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<section class="section section--offset-header">
    <div class="container">
        <div class="section-head" style="text-align:left;margin-left:0;margin-bottom:32px;">
            <span class="section-head__eyebrow">Shop Mascardi</span>
            <h1 class="section-head__title">The Full Collection</h1>
        </div>

        <?php if (!empty($categories)): ?>
            <div class="shop-filters">
                <a href="<?= site_url('shop') ?>" class="<?= $activeCategory === '' ? 'is-active' : '' ?>">All</a>
                <?php foreach ($categories as $category): ?>
                    <a href="<?= site_url('shop?category=' . urlencode($category['slug'])) ?>" class="<?= $activeCategory === $category['slug'] ? 'is-active' : '' ?>"><?= e($category['name']) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <div class="empty-note">No products found.</div>
        <?php else: ?>
            <div class="shop-page-grid">
                <?php foreach ($products as $product): ?>
                    <?= View::renderPartial('partials/site/product-card', ['product' => $product]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
