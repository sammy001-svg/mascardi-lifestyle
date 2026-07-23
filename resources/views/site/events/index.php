<?php
/** @var array $settings */
/** @var array $events */
use App\Core\Money;
use App\Core\View;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<section class="section section--offset-header">
    <div class="container">
        <div class="section-head" style="text-align:left;margin-left:0;margin-bottom:32px;">
            <span class="section-head__eyebrow">Events</span>
            <h1 class="section-head__title">Upcoming Experiences</h1>
        </div>

        <?php if (empty($events)): ?>
            <div class="empty-note">No upcoming events right now — check back soon.</div>
        <?php else: ?>
            <div class="event-list-grid">
                <?php foreach ($events as $event): ?>
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
    </div>
</section>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
