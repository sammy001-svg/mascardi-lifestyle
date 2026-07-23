<?php
/** @var array $settings */
/** @var array $items */
/** @var int $subtotalCents */
use App\Core\Money;
use App\Core\View;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<section class="section section--offset-header">
    <div class="container" style="max-width:840px;">
        <div class="section-head" style="text-align:left;margin-bottom:32px;">
            <span class="section-head__eyebrow">Shop Mascardi</span>
            <h1 class="section-head__title">Your Cart</h1>
        </div>

        <?php if ($success = flash_message('success')): ?>
            <p style="border:1px solid var(--color-gray-300);padding:14px 18px;margin-bottom:24px;"><?= e($success) ?></p>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="empty-note" style="text-align:left;padding:40px 24px;">
                Your cart is empty. <a href="<?= site_url('shop') ?>" style="text-decoration:underline;">Browse the shop</a>.
            </div>
        <?php else: ?>
            <div style="border-top:1px solid var(--color-gray-300);">
                <?php foreach ($items as $item): $p = $item['product']; ?>
                    <div style="display:flex;gap:20px;align-items:center;padding:20px 0;border-bottom:1px solid var(--color-gray-300);">
                        <div style="width:84px;height:84px;background:var(--color-gray-100);flex-shrink:0;overflow:hidden;">
                            <?php if ($p['primary_image'] ?? null): ?>
                                <img src="<?= e(upload_url($p['primary_image'])) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                            <?php endif; ?>
                        </div>
                        <div style="flex:1;">
                            <p style="margin:0 0 6px;font-weight:700;"><?= e($p['name']) ?></p>
                            <p style="margin:0;color:var(--color-gray-600);font-size:0.88rem;"><?= e(Money::format((int) $p['price_cents'])) ?> each</p>
                        </div>
                        <form method="post" action="<?= site_url('cart/update') ?>" style="display:flex;align-items:center;gap:8px;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                            <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="1" max="<?= (int) $p['stock_quantity'] ?>" style="width:64px;padding:8px;border:1px solid var(--color-gray-300);text-align:center;">
                            <button type="submit" class="btn btn-dark btn-sm" style="padding:8px 14px;">Update</button>
                        </form>
                        <p style="width:110px;text-align:right;font-weight:700;margin:0;"><?= e(Money::format((int) $item['line_total_cents'])) ?></p>
                        <form method="post" action="<?= site_url('cart/remove') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                            <button type="submit" style="background:none;border:none;color:var(--color-gray-600);text-decoration:underline;cursor:pointer;font-size:0.82rem;">Remove</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;padding:28px 0;">
                <span style="font-size:1.1rem;font-weight:700;">Subtotal</span>
                <span style="font-size:1.1rem;font-weight:700;"><?= e(Money::format($subtotalCents)) ?></span>
            </div>
            <a href="<?= site_url('checkout') ?>" class="btn btn-dark" style="width:100%;">Proceed to Checkout</a>
        <?php endif; ?>
    </div>
</section>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
