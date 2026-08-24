<?php
/**
 * @var array  $settings
 * @var array  $upcoming   Events whose starts_at is in the future
 * @var array  $past       Events whose starts_at has already passed
 */
use App\Core\Money;
use App\Core\View;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<style>
/* ── Events page tweaks ─────────────────────────────────────────── */
.events-page-section { padding: 64px 0 0; }
.events-section-divider {
    display: flex; align-items: center; gap: 18px;
    margin: 56px 0 32px;
}
.events-section-divider__label {
    white-space: nowrap;
    font-size: 0.72rem; font-weight: 800;
    letter-spacing: 0.2em; text-transform: uppercase;
    color: var(--color-gold, #d4af37);
}
.events-section-divider__line {
    flex: 1; height: 1px;
    background: linear-gradient(to right, rgba(212,175,55,0.35), transparent);
}
.past-grid { opacity: 0.72; }
.past-grid .event-list-card { filter: grayscale(0.3); }
.past-grid .event-list-card:hover { filter: none; }
.event-list-card__past-badge {
    display: inline-block;
    font-size: 0.68rem; font-weight: 800; letter-spacing: 0.12em;
    text-transform: uppercase;
    background: rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.45);
    border-radius: 100px;
    padding: 3px 10px;
    margin-bottom: 8px;
}
.empty-note {
    padding: 40px 0;
    color: rgba(255,255,255,0.35);
    font-size: 0.95rem;
}
</style>

<section class="section section--offset-header events-page-section">
    <div class="container">

        <!-- ── Upcoming ──────────────────────────────────────── -->
        <div class="section-head" style="text-align:left;margin-left:0;margin-bottom:32px;">
            <span class="section-head__eyebrow">Events</span>
            <h1 class="section-head__title">Upcoming Experiences</h1>
        </div>

        <?php if (empty($upcoming)): ?>
            <div class="empty-note">No upcoming events right now — check back soon.</div>
        <?php else: ?>
            <div class="event-list-grid">
                <?php foreach ($upcoming as $event): ?>
                    <a href="<?= site_url('events/' . $event['slug']) ?>" class="event-list-card js-tilt">
                        <div class="event-list-card__media">
                            <?php if ($event['image_path']): ?>
                                <img src="<?= e(upload_url($event['image_path'])) ?>" alt="<?= e($event['title']) ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="event-list-card__body">
                            <div class="event-list-card__date"><?= e(date('D, j M Y \a\t g:ia', strtotime($event['starts_at']))) ?></div>
                            <h2 class="event-list-card__title"><?= e($event['title']) ?></h2>
                            <div class="event-list-card__meta"><?= e($event['venue'] ?: 'Venue TBA') ?></div>
                            <?php if ($event['event_type'] === 'paid'): ?>
                                <strong><?= e(Money::format((int) $event['ticket_price_cents'])) ?></strong>
                            <?php else: ?>
                                <strong>Free — RSVP</strong>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ── Past Events ───────────────────────────────────── -->
        <?php if (!empty($past)): ?>
            <div class="events-section-divider">
                <span class="events-section-divider__label">Past Events</span>
                <span class="events-section-divider__line"></span>
            </div>

            <div class="event-list-grid past-grid">
                <?php foreach ($past as $event): ?>
                    <a href="<?= site_url('events/' . $event['slug']) ?>" class="event-list-card js-tilt">
                        <div class="event-list-card__media">
                            <?php if ($event['image_path']): ?>
                                <img src="<?= e(upload_url($event['image_path'])) ?>" alt="<?= e($event['title']) ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="event-list-card__body">
                            <span class="event-list-card__past-badge">Concluded</span>
                            <div class="event-list-card__date"><?= e(date('D, j M Y', strtotime($event['starts_at']))) ?></div>
                            <h2 class="event-list-card__title"><?= e($event['title']) ?></h2>
                            <div class="event-list-card__meta"><?= e($event['venue'] ?: '—') ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
