<?php
/** @var array $events */
use App\Core\Money;
$now = time();
?>
<section class="section section--black" id="events">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <span class="section-head__eyebrow">Events</span>
            <h2 class="section-head__title">Community, In Person</h2>
            <p class="section-head__subtitle">Ticketed experiences and member socials — from golf days and driving experiences to curated evenings. A look at what's coming and what we've shared.</p>
        </div>

        <?php if (empty($events)): ?>
            <div class="empty-note">No events yet — check back soon.</div>
        <?php else: ?>
            <div class="event-hl-grid">
                <?php foreach (array_slice($events, 0, 6) as $i => $event): ?>
                    <?php $isPast = strtotime($event['starts_at']) < $now; ?>
                    <a href="<?= site_url('events/' . $event['slug']) ?>" class="event-hl-card js-tilt" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 90 ?>">
                        <div class="event-hl-card__media">
                            <?php if (!empty($event['image_path'])): ?>
                                <img src="<?= e(upload_url($event['image_path'])) ?>" alt="<?= e($event['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <span class="event-hl-card__placeholder">Mascardi Events</span>
                            <?php endif; ?>
                            <span class="event-hl-card__badge<?= $isPast ? ' event-hl-card__badge--past' : '' ?>"><?= $isPast ? 'Past Event' : 'Upcoming' ?></span>
                        </div>
                        <div class="event-hl-card__body">
                            <div class="event-hl-card__date"><?= e(date('D, j M Y', strtotime($event['starts_at']))) ?></div>
                            <h3 class="event-hl-card__title"><?= e($event['title']) ?></h3>
                            <p class="event-hl-card__meta">
                                <?= e($event['venue'] ?: 'Venue TBA') ?>
                                <?php if (!$isPast): ?>
                                    &middot; <?= $event['event_type'] === 'paid' ? e(Money::format((int) $event['ticket_price_cents'])) : 'Free RSVP' ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <div style="text-align:center;margin-top:40px;">
                <a href="<?= site_url('events') ?>" class="btn btn-light">View All Events</a>
            </div>
        <?php endif; ?>
    </div>
</section>
