<?php
/**
 * @var array  $settings
 * @var array  $event
 * @var bool   $isPast
 */
use App\Core\Money;
use App\Core\View;

$isPaid = $event['event_type'] === 'paid';
$err    = field_errors('name') ?: field_errors('phone') ?: field_errors('email') ?: field_errors('quantity');

// Flash error from a blocked past-event registration POST
$flashError = flash_message('error');
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<?php if ($isPast): ?>
<style>
/* Subtle grayscale wash for the hero image on past events */
.event-detail__media img { filter: grayscale(0.3) brightness(0.85); }
</style>
<?php endif; ?>

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
                <span class="section-head__eyebrow">
                    <?= $isPast ? 'Past Event' : 'Event' ?>
                </span>

                <?php if ($isPast): ?>
                    <span style="display:inline-block;margin-left:10px;font-size:0.72rem;font-weight:800;
                                 letter-spacing:0.12em;text-transform:uppercase;
                                 background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.45);
                                 border-radius:100px;padding:3px 12px;">Concluded</span>
                <?php endif; ?>

                <h1 class="event-detail__title" style="margin-top:16px;"><?= e($event['title']) ?></h1>

                <div class="event-detail__meta-row">&#128197; <?= e(date('l, j F Y \a\t g:ia', strtotime($event['starts_at']))) ?></div>
                <?php if ($event['venue']): ?>
                    <div class="event-detail__meta-row">&#128205; <?= e($event['venue']) ?></div>
                <?php endif; ?>
                <div class="event-detail__meta-row">
                    <?php if ($isPaid): ?>
                        <strong><?= e(Money::format((int) $event['ticket_price_cents'])) ?> per ticket</strong>
                    <?php else: ?>
                        <strong>Free — RSVP required</strong>
                    <?php endif; ?>
                </div>

                <div style="border-top:1px solid var(--color-gray-300);margin-top:28px;padding-top:28px;">

                    <?php if ($isPast): ?>
                        <!-- ── PAST EVENT: no registration form ──────────── -->
                        <div style="
                            background: rgba(255,255,255,0.04);
                            border: 1px solid rgba(255,255,255,0.1);
                            border-radius: 14px;
                            padding: 28px 24px;
                            text-align: center;
                        ">
                            <div style="font-size:2.4rem;margin-bottom:14px;">🏁</div>
                            <p style="font-weight:700;font-size:1.05rem;color:rgba(255,255,255,0.75);margin:0 0 8px;">
                                This event has already taken place.
                            </p>
                            <p style="font-size:0.87rem;color:rgba(255,255,255,0.4);margin:0 0 22px;line-height:1.6;">
                                Registrations are closed. Stay tuned for future Mascardi experiences.
                            </p>
                            <a href="<?= site_url('events') ?>"
                               style="display:inline-block;padding:11px 26px;background:#d4af37;color:#09090b;
                                      border-radius:8px;font-weight:700;font-size:0.85rem;
                                      letter-spacing:0.06em;text-decoration:none;text-transform:uppercase;">
                                View Upcoming Events
                            </a>
                        </div>

                    <?php else: ?>
                        <!-- ── FUTURE EVENT: show registration form ───────── -->
                        <p class="event-detail__form-title"><?= $isPaid ? 'Buy Your Ticket' : 'Reserve Your Spot' ?></p>

                        <?php if ($flashError): ?>
                            <div class="alert alert--error"><?= e($flashError) ?></div>
                        <?php endif; ?>

                        <?php if ($err): ?>
                            <div class="alert alert--error"><?= e($err[0]) ?></div>
                        <?php endif; ?>

                        <form method="post" action="<?= site_url('events/' . $event['slug'] . '/register') ?>">
                            <?= csrf_field() ?>
                            <div class="field">
                                <label class="field__label" for="name">Full name</label>
                                <input class="field__input" type="text" id="name" name="name" value="<?= old('name') ?>" required>
                            </div>
                            <div class="field">
                                <label class="field__label" for="phone">M-Pesa / contact phone</label>
                                <input class="field__input" type="tel" id="phone" name="phone" value="<?= old('phone') ?>" placeholder="07XX XXX XXX" required>
                            </div>
                            <div class="field">
                                <label class="field__label" for="email">Email <span class="field__opt">(optional)</span></label>
                                <input class="field__input" type="email" id="email" name="email" value="<?= old('email') ?>">
                            </div>
                            <div class="field">
                                <label class="field__label" for="quantity"><?= $isPaid ? 'Number of tickets' : 'Number of guests' ?></label>
                                <input class="qty-input" type="number" id="quantity" name="quantity" value="<?= old('quantity', '1') ?>" min="1" max="10">
                            </div>
                            <button type="submit" class="btn btn-dark btn--block" style="margin-top:8px;">
                                <?= $isPaid ? 'Pay with M-Pesa' : 'Reserve Free Spot' ?>
                            </button>
                        </form>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
