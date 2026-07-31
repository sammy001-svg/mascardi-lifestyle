<?php
/** @var array $settings */
/** @var array $items */
/** @var int $subtotalCents */
use App\Core\Money;
use App\Core\View;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<section class="section section--offset-header">
    <div class="container container--form">
        <div class="section-head section-head--left" style="margin-bottom:32px;">
            <span class="section-head__eyebrow">Checkout</span>
            <h1 class="section-head__title">Complete Your Order</h1>
        </div>

        <?php if ($err = field_errors('name') ?: field_errors('phone') ?: field_errors('email')): ?>
            <div class="alert alert--error"><?= e($err[0]) ?></div>
        <?php endif; ?>

        <div class="order-summary">
            <?php foreach ($items as $item): ?>
                <div class="order-summary__row">
                    <span><?= (int) $item['quantity'] ?> &times; <?= e($item['product']['name']) ?></span>
                    <span><?= e(Money::format((int) $item['line_total_cents'])) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="order-summary__total">
                <span>Total</span>
                <span><?= e(Money::format($subtotalCents)) ?></span>
            </div>
        </div>

        <form method="post" action="<?= site_url('checkout') ?>">
            <?= csrf_field() ?>
            <div class="field">
                <label class="field__label" for="name">Full name</label>
                <input class="field__input" type="text" id="name" name="name" value="<?= old('name') ?>" required>
            </div>
            <div class="field">
                <label class="field__label" for="phone">M-Pesa phone number</label>
                <input class="field__input" type="tel" id="phone" name="phone" value="<?= old('phone') ?>" placeholder="07XX XXX XXX" required>
                <p class="field__hint">You'll receive an M-Pesa prompt on this number to complete payment.</p>
            </div>
            <div class="field">
                <label class="field__label" for="email">Email <span class="field__opt">(optional)</span></label>
                <input class="field__input" type="email" id="email" name="email" value="<?= old('email') ?>">
            </div>
            <div class="field">
                <label class="field__label" for="delivery_notes">Delivery notes <span class="field__opt">(optional)</span></label>
                <textarea class="field__input field__textarea" id="delivery_notes" name="delivery_notes" style="min-height:90px;"><?= old('delivery_notes') ?></textarea>
            </div>
            <button type="submit" class="btn btn-dark btn--block" style="margin-top:8px;">Pay with M-Pesa</button>
        </form>
    </div>
</section>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
