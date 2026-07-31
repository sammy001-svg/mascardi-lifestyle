<?php
/** @var array $settings */
/** @var array $product */
/** @var array $images */
use App\Core\Money;
use App\Core\View;

$inStock = (int) $product['stock_quantity'] > 0;
$mainImage = $images[0]['image_path'] ?? null;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<section class="section section--offset-header">
    <div class="container">
        <div class="product-detail">
            <div>
                <div class="product-detail__gallery-main" id="mainImage">
                    <?php if ($mainImage): ?>
                        <img src="<?= e(upload_url($mainImage)) ?>" alt="<?= e($product['name']) ?>">
                    <?php endif; ?>
                </div>
                <?php if (count($images) > 1): ?>
                    <div class="product-detail__thumbs">
                        <?php foreach ($images as $image): ?>
                            <img src="<?= e(upload_url($image['image_path'])) ?>" alt="" data-full="<?= e(upload_url($image['image_path'])) ?>" class="js-thumb">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <h1 class="product-detail__title"><?= e($product['name']) ?></h1>
                <p class="product-detail__price">
                    <?php if (!empty($product['compare_at_price_cents'])): ?>
                        <span class="product-card__compare"><?= e(Money::format((int) $product['compare_at_price_cents'])) ?></span>
                    <?php endif; ?>
                    <?= e(Money::format((int) $product['price_cents'])) ?>
                </p>
                <?php if ($product['description']): ?>
                    <p class="product-detail__desc"><?= nl2br(e($product['description'])) ?></p>
                <?php endif; ?>

                <p class="product-detail__stock"><?= $inStock ? (int) $product['stock_quantity'] . ' in stock' : 'Out of stock' ?></p>

                <?php if ($inStock): ?>
                    <form method="post" action="<?= site_url('cart/add') ?>" class="buy-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                        <input type="hidden" name="redirect_to" value="<?= site_url('cart') ?>">
                        <input class="qty-input" type="number" name="quantity" value="1" min="1" max="<?= (int) $product['stock_quantity'] ?>">
                        <button type="submit" class="btn btn-dark btn--grow">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <button type="button" class="btn btn-dark btn--disabled" disabled>Out of Stock</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
