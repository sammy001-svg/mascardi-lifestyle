<?php
/** @var array $settings */
/** @var array $items */
/** @var int $subtotalCents */
use App\Core\Money;
use App\Core\View;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<section class="section section--offset-header">
    <div class="container container--narrow">
        <div class="section-head section-head--left" style="margin-bottom:32px;">
            <span class="section-head__eyebrow">Shop Mascardi</span>
            <h1 class="section-head__title">Your Cart</h1>
        </div>

        <?php if ($success = flash_message('success')): ?>
            <div class="alert alert--success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="empty-note" style="text-align:left;padding:40px 24px;">
                Your cart is empty. <a href="<?= site_url('shop') ?>">Browse the shop</a>.
            </div>
        <?php else: ?>
            <div class="cart-list">
                <?php foreach ($items as $item): $p = $item['product']; ?>
                    <div class="cart-row">
                        <div class="cart-row__media">
                            <?php if ($p['primary_image'] ?? null): ?>
                                <img src="<?= e(upload_url($p['primary_image'])) ?>" alt="<?= e($p['name']) ?>">
                            <?php endif; ?>
                        </div>
                        <div class="cart-row__info">
                            <p class="cart-row__name"><?= e($p['name']) ?></p>
                            <p class="cart-row__unit"><?= e(Money::format((int) $p['price_cents'])) ?> each</p>
                        </div>
                        <form method="post" action="<?= site_url('cart/update') ?>" class="cart-row__qty">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                            <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="1" max="<?= (int) $p['stock_quantity'] ?>">
                            <button type="submit" class="btn btn-dark btn-sm" style="padding:9px 16px;">Update</button>
                        </form>
                        <p class="cart-row__total"><?= e(Money::format((int) $item['line_total_cents'])) ?></p>
                        <form method="post" action="<?= site_url('cart/remove') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                            <button type="submit" class="cart-row__remove">Remove</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <span class="cart-summary__label">Subtotal</span>
                <span class="cart-summary__value"><?= e(Money::format($subtotalCents)) ?></span>
            </div>
            <a href="<?= site_url('checkout') ?>" class="btn btn-dark btn--block">Proceed to Checkout</a>
        <?php endif; ?>
    </div>
</section>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
