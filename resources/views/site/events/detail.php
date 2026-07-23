<?php
/** @var array $settings */
/** @var array $event */
use App\Core\Money;
use App\Core\View;

$isPaid = $event['event_type'] === 'paid';
$err = field_errors('name') ?: field_errors('phone') ?: field_errors('email') ?: field_errors('quantity');
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<section class="section section--offset-header">
    <div class="container">
        <div class="event-detail">
            <div>
                <div class="event-detail__media">
                    <?php if ($event['image_path']): ?>
                        <img src="<?= e(upload_url($event['image_path'])) ?>" alt="<?= e($event['title']) ?>">
                    <?php endif; ?>
                </div>
                <?php if ($event['description']): ?>
                    <p class="event-detail__desc"><?= nl2br(e($event['description'])) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <span class="section-head__eyebrow">Event</span>
                <h1 class="event-detail__title" style="margin-top:16px;"><?= e($event['title']) ?></h1>
                <div class="event-detail__meta-row">&#128197; <?= e(date('l, j F Y \a\t g:ia', strtotime($event['starts_at']))) ?></div>
                <?php if ($event['venue']): ?><div class="event-detail__meta-row">&#128205; <?= e($event['venue']) ?></div><?php endif; ?>
                <div class="event-detail__meta-row">
                    <?php if ($isPaid): ?>
                        <strong><?= e(Money::format((int) $event['ticket_price_cents'])) ?> per ticket</strong>
                    <?php else: ?>
                        <strong>Free — RSVP required</strong>
                    <?php endif; ?>
                </div>

                <div style="border-top:1px solid var(--color-gray-300);margin-top:28px;padding-top:28px;">
                    <p class="event-detail__form-title"><?= $isPaid ? 'Buy Your Ticket' : 'Reserve Your Spot' ?></p>

                    <?php if ($err): ?>
                        <p style="border:1px solid var(--color-black);padding:14px 18px;margin-bottom:20px;"><?= e($err[0]) ?></p>
                    <?php endif; ?>

                    <form method="post" action="<?= site_url('events/' . $event['slug'] . '/register') ?>">
                        <?= csrf_field() ?>
                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="display:block;font-size:0.82rem;font-weight:700;margin-bottom:6px;" for="name">Full name</label>
                            <input style="width:100%;padding:13px;border:1px solid var(--color-gray-300);" type="text" id="name" name="name" value="<?= old('name') ?>" required>
                        </div>
                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="display:block;font-size:0.82rem;font-weight:700;margin-bottom:6px;" for="phone">M-Pesa / contact phone</label>
                            <input style="width:100%;padding:13px;border:1px solid var(--color-gray-300);" type="tel" id="phone" name="phone" value="<?= old('phone') ?>" placeholder="07XX XXX XXX" required>
                        </div>
                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="display:block;font-size:0.82rem;font-weight:700;margin-bottom:6px;" for="email">Email <span style="font-weight:400;color:var(--color-gray-600);">(optional)</span></label>
                            <input style="width:100%;padding:13px;border:1px solid var(--color-gray-300);" type="email" id="email" name="email" value="<?= old('email') ?>">
                        </div>
                        <div class="form-group" style="margin-bottom:24px;">
                            <label style="display:block;font-size:0.82rem;font-weight:700;margin-bottom:6px;" for="quantity"><?= $isPaid ? 'Number of tickets' : 'Number of guests' ?></label>
                            <input style="width:100px;padding:13px;border:1px solid var(--color-gray-300);text-align:center;" type="number" id="quantity" name="quantity" value="<?= old('quantity', '1') ?>" min="1" max="10">
                        </div>
                        <button type="submit" class="btn btn-dark" style="width:100%;"><?= $isPaid ? 'Pay with M-Pesa' : 'Reserve Free Spot' ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
