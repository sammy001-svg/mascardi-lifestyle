<?php
/** @var string $activeModule */
$navItem = static function (string $icon, string $label, string $module, string $activeModule, bool $enabled = true) {
    $isActive = $activeModule === $module;
    if (!$enabled) {
        echo '<span class="admin-nav__link" style="opacity:.45;cursor:not-allowed;">';
        echo '<span class="admin-nav__icon">' . $icon . '</span>' . e($label);
        echo '<span class="badge badge-gray" style="margin-left:auto;">soon</span></span>';
        return;
    }
    echo '<a class="admin-nav__link' . ($isActive ? ' is-active' : '') . '" href="' . admin_url($module) . '">';
    echo '<span class="admin-nav__icon">' . $icon . '</span>' . e($label);
    echo '</a>';
};
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar__brand"><span class="dot"></span> MASCARDI LIFESTYLE</div>
    <nav class="admin-nav">
        <div class="admin-nav__section">Overview</div>
        <?php $navItem('&#9679;', 'Dashboard', 'dashboard', $activeModule); ?>

        <div class="admin-nav__section">Content</div>
        <?php $navItem('&#9635;', 'Pillars', 'pillars', $activeModule); ?>
        <?php $navItem('&#9670;', 'Partners', 'partners', $activeModule); ?>

        <div class="admin-nav__section">Blog</div>
        <?php $navItem('&#9998;', 'Posts', 'blog-posts', $activeModule); ?>
        <?php $navItem('&#128214;', 'Categories', 'blog-categories', $activeModule); ?>

        <div class="admin-nav__section">Gallery</div>
        <?php $navItem('&#128247;', 'Albums', 'gallery', $activeModule); ?>

        <div class="admin-nav__section">Commerce</div>
        <?php $navItem('&#128722;', 'Products', 'products', $activeModule); ?>
        <?php $navItem('&#128179;', 'Orders', 'orders', $activeModule); ?>

        <div class="admin-nav__section">Events</div>
        <?php $navItem('&#127903;', 'Events', 'events', $activeModule); ?>
        <?php $navItem('&#127915;', 'Registrations', 'registrations', $activeModule); ?>

        <div class="admin-nav__section">Communication</div>
        <?php $unreadMessages = \App\Models\ContactMessage::unreadCount(); ?>
        <a class="admin-nav__link<?= $activeModule === 'messages' ? ' is-active' : '' ?>" href="<?= admin_url('messages') ?>">
            <span class="admin-nav__icon">&#9993;</span>Messages
            <?php if ($unreadMessages > 0): ?><span class="badge badge-rose" style="margin-left:auto;"><?= (int) $unreadMessages ?></span><?php endif; ?>
        </a>

        <div class="admin-nav__section">System</div>
        <?php $navItem('&#128247;', 'Media Library', 'media', $activeModule); ?>
        <?php $navItem('&#9881;', 'Settings', 'settings', $activeModule); ?>
        <?php $navItem('&#128101;', 'Admin Users', 'admin-users', $activeModule, false); ?>
    </nav>
    <div class="admin-sidebar__footer">&copy; <?= date('Y') ?> Mascardi Lifestyle</div>
</aside>
