<?php /** @var array $settings */ ?>
<?php $cartCount = \App\Services\CartService::count(); ?>
<header class="site-header" id="siteHeader">
    <div class="container site-header__inner">
        <a href="<?= site_url() ?>" class="site-header__logo"><?= e($settings['site_name'] ?? 'Mascardi Lifestyle') ?></a>
        <nav class="site-header__nav" id="siteNav">
            <a href="<?= site_url() ?>#pillars">Pillars</a>
            <a href="<?= site_url() ?>#partners">Partners</a>
            <a href="<?= site_url('shop') ?>">Shop</a>
            <a href="<?= site_url('events') ?>">Events</a>
            <a href="<?= site_url('contact') ?>">Contact</a>
        </nav>
        <div class="site-header__actions">
            <a href="<?= site_url('cart') ?>" class="site-header__cart" aria-label="View cart">
                <span>Cart</span>
                <?php if ($cartCount > 0): ?><span class="site-header__cart-count"><?= (int) $cartCount ?></span><?php endif; ?>
            </a>
            <button class="site-header__toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">&#9776;</button>
        </div>
    </div>
</header>
