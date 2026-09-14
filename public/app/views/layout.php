<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($meta['title']) ?></title>
<?php if ($meta['description'] !== ''): ?>
<meta name="description" content="<?= e($meta['description']) ?>">
<?php endif; ?>
<?php if ($meta['noindex']): ?>
<meta name="robots" content="noindex, follow">
<?php else: ?>
<link rel="canonical" href="<?= e(cfg('base_url') . $meta['path']) ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(cfg('site_name')) ?>">
<meta property="og:title" content="<?= e($meta['title']) ?>">
<meta property="og:url" content="<?= e(cfg('base_url') . $meta['path']) ?>">
<meta property="og:image" content="<?= e(cfg('base_url')) ?>/assets/img/home-page-hero-image-kids-ortho.jpg">
<meta name="theme-color" content="#0B2A55">
<link rel="icon" type="image/png" href="/assets/img/ignitelogo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="<?= asset('css/site.css') ?>">
<link rel="stylesheet" href="<?= asset('css/header.css') ?>">
<?php foreach ($meta['css'] as $css): ?>
<link rel="stylesheet" href="<?= asset('css/' . $css) ?>">
<?php endforeach; ?>
<link rel="stylesheet" href="<?= asset('css/consult.css') ?>">
<link rel="stylesheet" href="<?= asset('css/footer.css') ?>">
</head>
<body>
<a class="ig-skip" href="#main">Skip to content</a>
<?php require APP . '/views/partials/header.php'; ?>
<main id="main">
<?= $content ?>
</main>
<?php require APP . '/views/partials/footer.php'; ?>
<script src="<?= asset('js/site.js') ?>"></script>
<script src="<?= asset('js/header.js') ?>" defer></script>
<?php foreach ($meta['js'] as $js): ?>
<script src="<?= asset('js/' . $js) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
