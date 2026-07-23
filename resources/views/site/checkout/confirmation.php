<?php
/** @var array $settings */
/** @var array $order */
/** @var array $items */
use App\Core\Money;
use App\Core\View;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<section class="section section--offset-header">
    <div class="container" style="max-width:640px;">
        <div style="text-align:center;margin-bottom:40px;">
            <span class="section-head__eyebrow">Payment Confirmed</span>
            <h1 class="section-head__title" style="margin-top:18px;">Thank You</h1>
            <p style="color:var(--color-gray-600);">Order <strong><?= e($order['order_number']) ?></strong> is confirmed. A member of the Mascardi team will be in touch about delivery.</p>
        </div>

        <div style="border:1px solid var(--color-gray-300);padding:20px;">
            <?php foreach ($items as $item): ?>
                <div style="display:flex;justify-content:space-between;font-size:0.88rem;margin-bottom:10px;">
                    <span><?= (int) $item['quantity'] ?> &times; <?= e($item['product_name_snapshot']) ?></span>
                    <span><?= e(Money::format((int) $item['line_total_cents'])) ?></span>
                </div>
            <?php endforeach; ?>
            <div style="display:flex;justify-content:space-between;font-weight:700;padding-top:12px;border-top:1px solid var(--color-gray-300);">
                <span>Total Paid</span>
                <span><?= e(Money::format((int) $order['total_cents'])) ?></span>
            </div>
        </div>

        <div style="text-align:center;margin-top:32px;">
            <a href="<?= site_url('shop') ?>" class="btn btn-dark">Continue Shopping</a>
        </div>
    </div>
</section>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
