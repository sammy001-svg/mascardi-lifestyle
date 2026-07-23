<?php /** @var array $settings */ ?>
<header class="site-header" id="siteHeader">
    <div class="container site-header__inner">
        <a href="#top" class="site-header__logo"><?= e($settings['site_name'] ?? 'Mascardi Lifestyle') ?></a>
        <nav class="site-header__nav" id="siteNav">
            <a href="#pillars">Pillars</a>
            <a href="#partners">Partners</a>
            <a href="#shop">Shop</a>
            <a href="#events">Events</a>
        </nav>
        <button class="site-header__toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">&#9776;</button>
    </div>
</header>
