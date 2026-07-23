<?php
/** @var array $events */
use App\Core\Money;
?>
<section class="section section--black" id="events">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <span class="section-head__eyebrow">Events</span>
            <h2 class="section-head__title">Community, In Person</h2>
            <p class="section-head__subtitle">Ticketed experiences and member RSVPs — golf days, driving experiences, and curated socials.</p>
        </div>

        <?php if (empty($events)): ?>
            <div class="empty-note">No upcoming events yet — check back soon.</div>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach (array_slice($events, 0, 3) as $i => $event): ?>
                    <a href="<?= site_url('events/' . $event['slug']) ?>" class="event-card js-tilt" data-aos="fade-up" data-aos-delay="<?= $i * 90 ?>" style="display:block;color:inherit;">
                        <div class="event-card__date"><?= e(date('D, j M \a\t g:ia', strtotime($event['starts_at']))) ?></div>
                        <h3 class="event-card__title"><?= e($event['title']) ?></h3>
                        <p class="event-card__desc">
                            <?= e($event['venue'] ?: 'Venue TBA') ?> &middot;
                            <?= $event['event_type'] === 'paid' ? e(Money::format((int) $event['ticket_price_cents'])) : 'Free RSVP' ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
            <div style="text-align:center;margin-top:36px;">
                <a href="<?= site_url('events') ?>" class="btn btn-light">View All Events</a>
            </div>
        <?php endif; ?>
    </div>
</section>
