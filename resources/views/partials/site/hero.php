<?php
/** @var array $settings */
$youtubeId = trim($settings['hero_youtube_id'] ?? '');
$overlayText = $settings['hero_overlay_text'] ?? 'Belong to the Difference';

$embedParams = http_build_query([
    'autoplay' => 1,
    'mute' => 1,
    'loop' => 1,
    'playlist' => $youtubeId, // required by YouTube for a single video to loop
    'controls' => 0,
    'modestbranding' => 1,
    'rel' => 0,
    'iv_load_policy' => 3,
    'disablekb' => 1,
    'playsinline' => 1,
    'showinfo' => 0,
]);
?>
<section class="hero" id="top">
    <div class="hero__media">
        <?php if ($youtubeId !== ''): ?>
            <iframe
                src="https://www.youtube-nocookie.com/embed/<?= e($youtubeId) ?>?<?= $embedParams ?>"
                title="Mascardi Lifestyle"
                allow="autoplay; encrypted-media"
                aria-hidden="true"
                tabindex="-1"
            ></iframe>
        <?php endif; ?>
        <div class="hero__scrim"></div>
    </div>
    <div class="hero__content">
        <div class="hero__eyebrow" data-aos="fade-up">Mascardi Lifestyle</div>
        <h1 class="hero__title" data-aos="fade-up" data-aos-delay="120"><?= e($overlayText) ?></h1>
        <div class="hero__actions" data-aos="fade-up" data-aos-delay="240">
            <a href="http://mascardisystems.co.ke/showroom/vehicles.php" target="_blank" rel="noopener" class="btn btn-solid-light">Explore Mascardi Cars</a>
            <a href="#pillars" class="btn btn-light">Discover the Pillars</a>
        </div>
    </div>
    <div class="hero__scroll-hint">
        <span>Scroll</span>
        <span class="line"></span>
    </div>
</section>
