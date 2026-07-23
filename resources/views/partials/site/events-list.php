<section class="section section--black" id="events">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <span class="section-head__eyebrow">Events</span>
            <h2 class="section-head__title">Community, In Person</h2>
            <p class="section-head__subtitle">Ticketed experiences and member RSVPs are launching soon — golf days, driving experiences, and curated socials.</p>
        </div>

        <div class="events-grid">
            <?php for ($i = 0; $i < 3; $i++): ?>
                <div class="event-card" data-aos="fade-up" data-aos-delay="<?= $i * 90 ?>">
                    <div class="event-card__date">Details Coming Soon</div>
                    <h3 class="event-card__title">To Be Announced</h3>
                    <p class="event-card__desc">Ticketing and RSVPs open here once the first Mascardi Lifestyle event is scheduled.</p>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>
