<?php
/** @var array $settings */
/** @var array $items */
/** @var int $subtotalCents */
use App\Core\Money;
use App\Core\View;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<section class="section section--offset-header">
    <div class="container" style="max-width:640px;">
        <div class="section-head" style="text-align:left;margin-left:0;margin-bottom:32px;">
            <span class="section-head__eyebrow">Checkout</span>
            <h1 class="section-head__title">Complete Your Order</h1>
        </div>

        <?php if ($err = field_errors('name') ?: field_errors('phone') ?: field_errors('email')): ?>
            <p style="border:1px solid var(--color-black);padding:14px 18px;margin-bottom:24px;"><?= e($err[0]) ?></p>
        <?php endif; ?>

        <div style="border:1px solid var(--color-gray-300);padding:20px;margin-bottom:32px;">
            <?php foreach ($items as $item): ?>
                <div style="display:flex;justify-content:space-between;font-size:0.88rem;margin-bottom:10px;">
                    <span><?= (int) $item['quantity'] ?> &times; <?= e($item['product']['name']) ?></span>
                    <span><?= e(Money::format((int) $item['line_total_cents'])) ?></span>
                </div>
            <?php endforeach; ?>
            <div style="display:flex;justify-content:space-between;font-weight:700;padding-top:12px;border-top:1px solid var(--color-gray-300);">
                <span>Total</span>
                <span><?= e(Money::format($subtotalCents)) ?></span>
            </div>
        </div>

        <form method="post" action="<?= site_url('checkout') ?>">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom:18px;">
                <label style="display:block;font-size:0.82rem;font-weight:700;margin-bottom:6px;" for="name">Full name</label>
                <input style="width:100%;padding:13px;border:1px solid var(--color-gray-300);" type="text" id="name" name="name" value="<?= old('name') ?>" required>
            </div>
            <div class="form-group" style="margin-bottom:18px;">
                <label style="display:block;font-size:0.82rem;font-weight:700;margin-bottom:6px;" for="phone">M-Pesa phone number</label>
                <input style="width:100%;padding:13px;border:1px solid var(--color-gray-300);" type="tel" id="phone" name="phone" value="<?= old('phone') ?>" placeholder="07XX XXX XXX" required>
                <p style="font-size:0.78rem;color:var(--color-gray-600);margin-top:6px;">You'll receive an M-Pesa prompt on this number to complete payment.</p>
            </div>
            <div class="form-group" style="margin-bottom:18px;">
                <label style="display:block;font-size:0.82rem;font-weight:700;margin-bottom:6px;" for="email">Email <span style="font-weight:400;color:var(--color-gray-600);">(optional)</span></label>
                <input style="width:100%;padding:13px;border:1px solid var(--color-gray-300);" type="email" id="email" name="email" value="<?= old('email') ?>">
            </div>
            <div class="form-group" style="margin-bottom:28px;">
                <label style="display:block;font-size:0.82rem;font-weight:700;margin-bottom:6px;" for="delivery_notes">Delivery notes <span style="font-weight:400;color:var(--color-gray-600);">(optional)</span></label>
                <textarea style="width:100%;padding:13px;border:1px solid var(--color-gray-300);min-height:80px;" id="delivery_notes" name="delivery_notes"><?= old('delivery_notes') ?></textarea>
            </div>
            <button type="submit" class="btn btn-dark" style="width:100%;">Pay with M-Pesa</button>
        </form>
    </div>
</section>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
