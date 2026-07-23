<?php
/** @var array $adminUser */
$initials = '';
foreach (explode(' ', trim($adminUser['name'] ?? 'A')) as $part) {
    $initials .= mb_strtoupper(mb_substr($part, 0, 1));
}
$initials = mb_substr($initials, 0, 2) ?: 'A';
?>
<header class="admin-topbar">
    <div>
        <div class="admin-topbar__title"><?= e($pageTitle ?? 'Dashboard') ?></div>
        <?php if (!empty($pageSubtitle)): ?>
            <div class="admin-topbar__subtitle"><?= e($pageSubtitle) ?></div>
        <?php endif; ?>
    </div>
    <div class="admin-topbar__user">
        <span><?= e($adminUser['name'] ?? '') ?></span>
        <span class="admin-avatar"><?= e($initials) ?></span>
        <a href="<?= admin_url('auth', 'logout') ?>" class="btn btn-outline btn-sm">Log out</a>
    </div>
</header>
