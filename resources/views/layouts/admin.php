<?php
/** @var string $content */
/** @var string $activeModule */
use App\Core\Auth;
use App\Core\View;

$adminUser = Auth::user();
$activeModule = $activeModule ?? '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Dashboard') ?> — Mascardi Lifestyle Admin</title>
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin-body">
<div class="admin-shell">
    <?= View::renderPartial('partials/admin/sidebar', ['activeModule' => $activeModule]) ?>
    <div class="admin-main">
        <?= View::renderPartial('partials/admin/topbar', [
            'adminUser' => $adminUser,
            'pageTitle' => $pageTitle ?? 'Dashboard',
            'pageSubtitle' => $pageSubtitle ?? '',
        ]) ?>
        <main class="admin-content">
            <?= View::renderPartial('partials/admin/flash-messages') ?>
            <?= $content ?>
        </main>
    </div>
</div>
<script defer src="<?= asset('js/admin.js') ?>"></script>
</body>
</html>
