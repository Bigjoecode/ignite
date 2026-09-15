<?php
$canonical = cfg('base_url') . $meta['path'];
$ogImage   = cfg('base_url') . ($meta['image'] ?? '/assets/img/home-page-hero-image-kids-ortho.jpg');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5" />
<title><?= e($meta['title']) ?></title>
<?php if ($meta['description'] !== ''): ?>
<meta name="description" content="<?= e($meta['description']) ?>">
<?php endif; ?>
<?php if ($meta['noindex']): ?>
<meta name="robots" content="noindex, follow">
<?php else: ?>
<meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />
<link rel="canonical" href="<?= e($canonical) ?>">
<?php endif; ?>
<meta property="og:type" content="<?= e($meta['og_type'] ?? 'website') ?>">
<meta property="og:site_name" content="<?= e(cfg('site_name')) ?>">
<meta property="og:title" content="<?= e($meta['title']) ?>">
<?php if ($meta['description'] !== ''): ?>
<meta property="og:description" content="<?= e($meta['description']) ?>">
<?php endif; ?>
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<?php if (!empty($meta['published'])): ?>
<meta property="article:published_time" content="<?= e($meta['published']) ?>">
<meta property="article:modified_time" content="<?= e($meta['modified'] ?? $meta['published']) ?>">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
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
<link rel="stylesheet" href="<?= asset('css/booking.css') ?>">
<link rel="stylesheet" href="<?= asset('css/footer.css') ?>">
<?php if (!empty($meta['schema'])): ?>
<script type="application/ld+json"><?= json_encode($meta['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<?php endif; ?>
</head>
<body<?= !empty($meta['book_office']) ? ' data-book-office="' . e($meta['book_office']) . '"' : '' ?>>
<a class="ig-skip" href="#main">Skip to content</a>
<?php if (!empty($meta['preview_id'])): ?>
<div style="position:fixed;left:50%;bottom:18px;transform:translateX(-50%);z-index:100001;display:flex;align-items:center;gap:14px;padding:10px 12px 10px 18px;border-radius:999px;background:#051A39;color:#fff;font:600 14px/1.2 Poppins,system-ui,sans-serif;box-shadow:0 12px 32px rgba(0,0,0,.35)">
  Preview &mdash; not visible to visitors
  <a href="/admin/posts/<?= (int) $meta['preview_id'] ?>/" style="padding:8px 14px;border-radius:999px;background:#F47421;color:#fff;text-decoration:none">Back to editor</a>
</div>
<?php endif; ?>
<?php require APP . '/views/partials/header.php'; ?>
<main id="main">
<?= $content ?>
</main>
<?php require APP . '/views/partials/footer.php'; ?>
<?php require APP . '/views/partials/booking.php'; ?>
<script src="<?= asset('js/header.js') ?>" defer></script>
<?php foreach ($meta['js'] as $js): ?>
<script src="<?= asset('js/' . $js) ?>" defer></script>
<?php endforeach; ?>
<script src="<?= asset('js/booking.js') ?>" defer></script>
</body>
</html>
