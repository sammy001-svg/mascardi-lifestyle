<?php
/** @var array $settings */
/** @var array $order */
/** @var array|null $transaction */
use App\Core\Money;
use App\Core\View;

$hasActivePush = $transaction && !empty($transaction['checkout_request_id']) && in_array($transaction['status'], ['initiated', 'pending'], true);
$hasFailed = $transaction && in_array($transaction['status'], ['failed', 'cancelled', 'timeout'], true);
$neverStarted = !$transaction || empty($transaction['checkout_request_id']);
$startError = flash_message('error');
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<section class="section section--offset-header">
    <div class="container" style="max-width:560px;text-align:center;">
        <span class="section-head__eyebrow">Order <?= e($order['order_number']) ?></span>
        <h1 class="section-head__title" style="margin-top:18px;"><?= e(Money::format((int) $order['total_cents'])) ?></h1>

        <div id="paymentWaiting"
             data-ref="<?= e($transaction['checkout_request_id'] ?? '') ?>"
             data-status-url="/mpesa/status.php"
             data-confirmation-url="<?= site_url('checkout/confirmation/' . $order['order_number']) ?>"
             style="margin-top:34px;">

            <?php if ($hasActivePush): ?>
                <div data-state="waiting">
                    <p style="font-weight:700;margin-bottom:8px;">Check your phone</p>
                    <p style="color:var(--color-gray-600);font-size:0.92rem;">Enter your M-Pesa PIN on <?= e($order['customer_phone']) ?> to complete this payment.</p>
                    <div style="margin:26px auto;width:36px;height:36px;border:2px solid var(--color-gray-300);border-top-color:var(--color-black);border-radius:50%;animation:spin 0.8s linear infinite;"></div>
                </div>
                <div data-state="failed" style="display:none;">
                    <p style="font-weight:700;margin-bottom:8px;">Payment wasn't completed</p>
                    <p style="color:var(--color-gray-600);font-size:0.92rem;margin-bottom:22px;">The M-Pesa request was cancelled or timed out.</p>
                    <form method="post" action="<?= site_url('checkout/retry/' . $order['order_number']) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-dark">Try Again</button>
                    </form>
                </div>
            <?php elseif ($hasFailed || $neverStarted): ?>
                <p style="font-weight:700;margin-bottom:8px;">Payment wasn't started</p>
                <p style="color:var(--color-gray-600);font-size:0.92rem;margin-bottom:22px;"><?= e($startError ?: "We couldn't send the M-Pesa prompt. Your order is saved — you can try again.") ?></p>
                <form method="post" action="<?= site_url('checkout/retry/' . $order['order_number']) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-dark">Try Again</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>@keyframes spin { to { transform: rotate(360deg); } }</style>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
