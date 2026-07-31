<?php /** @var string $content */ ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Mascardi Lifestyle') ?></title>
<meta name="description" content="Mascardi Lifestyle — Kenya's first luxury car ownership lifestyle programme.">
<link rel="stylesheet" href="/assets/vendor/aos/aos.css">
<link rel="stylesheet" href="<?= asset('css/site.css') ?>">
</head>
<body class="site-body<?= !empty($bodyClass) ? ' ' . e($bodyClass) : '' ?>">
<?= $content ?>
<script defer src="/assets/vendor/aos/aos.js"></script>
<script defer src="/assets/vendor/vanilla-tilt/vanilla-tilt.min.js"></script>
<script defer src="<?= asset('js/site.js') ?>"></script>
</body>
</html>
