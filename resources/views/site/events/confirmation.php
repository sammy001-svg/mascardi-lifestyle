<?php
/** @var array $settings */
/** @var array $registration */
/** @var array $event */
use App\Core\Money;
use App\Core\View;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<section class="section section--offset-header">
    <div class="container" style="max-width:560px;">
        <div style="text-align:center;margin-bottom:36px;">
            <span class="section-head__eyebrow"><?= $registration['status'] === 'confirmed' ? "You're Confirmed" : 'Registration Received' ?></span>
            <h1 class="section-head__title" style="margin-top:18px;"><?= e($event['title']) ?></h1>
            <p style="color:var(--color-gray-600);"><?= e(date('l, j F Y \a\t g:ia', strtotime($event['starts_at']))) ?><?= $event['venue'] ? ' &middot; ' . e($event['venue']) : '' ?></p>
        </div>

        <div style="border:1px solid var(--color-black);padding:28px;text-align:center;">
            <p style="font-size:0.72rem;letter-spacing:0.14em;text-transform:uppercase;color:var(--color-gray-600);margin:0 0 10px;">Ticket Code</p>
            <p style="font-size:1.8rem;font-weight:800;letter-spacing:0.06em;margin:0 0 20px;"><?= e($registration['ticket_code']) ?></p>
            <div style="display:flex;justify-content:space-between;font-size:0.88rem;border-top:1px solid var(--color-gray-300);padding-top:18px;">
                <span><?= e($registration['attendee_name']) ?></span>
                <span><?= (int) $registration['quantity'] ?> <?= (int) $registration['quantity'] === 1 ? 'guest' : 'guests' ?></span>
            </div>
            <?php if ((int) $registration['total_amount_cents'] > 0): ?>
                <div style="display:flex;justify-content:space-between;font-size:0.88rem;margin-top:8px;">
                    <span>Amount Paid</span>
                    <span><?= e(Money::format((int) $registration['total_amount_cents'])) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <p style="text-align:center;color:var(--color-gray-600);font-size:0.85rem;margin-top:20px;">Save this code — present it at check-in.</p>

        <div style="text-align:center;margin-top:28px;">
            <a href="<?= site_url('events') ?>" class="btn btn-dark">Browse More Events</a>
        </div>
    </div>
</section>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
