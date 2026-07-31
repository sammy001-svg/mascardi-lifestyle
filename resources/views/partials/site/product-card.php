<?php
/** @var array $product */
/** @var bool $compact */
use App\Core\Money;
$inStock = (int) $product['stock_quantity'] > 0;
$compact = $compact ?? false;
?>
<div class="product-card js-tilt<?= $compact ? ' product-card--compact' : '' ?>">
    <a href="<?= site_url('shop/' . $product['slug']) ?>" class="product-card__media">
        <?php if (!empty($product['primary_image'])): ?>
            <img class="product-card__img" src="<?= e(upload_url($product['primary_image'])) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
        <?php else: ?>
            Product photo
        <?php endif; ?>
        <?php if ($product['is_featured']): ?><span class="product-card__badge">Featured</span><?php endif; ?>
    </a>
    <div class="product-card__body">
        <a href="<?= site_url('shop/' . $product['slug']) ?>" style="color:inherit;">
            <p class="product-card__name"><?= e($product['name']) ?></p>
        </a>
        <p class="product-card__price">
            <?php if (!empty($product['compare_at_price_cents'])): ?>
                <span class="product-card__compare"><?= e(Money::format((int) $product['compare_at_price_cents'])) ?></span>
            <?php endif; ?>
            <?= e(Money::format((int) $product['price_cents'])) ?>
        </p>
        <?php if ($inStock): ?>
            <form method="post" action="<?= site_url('cart/add') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI'] ?? site_url('shop')) ?>">
                <button type="submit" class="product-card__btn product-card__btn--live">Add to Cart</button>
            </form>
        <?php else: ?>
            <button type="button" class="product-card__btn product-card__btn--out" disabled>Out of Stock</button>
        <?php endif; ?>
    </div>
</div>
