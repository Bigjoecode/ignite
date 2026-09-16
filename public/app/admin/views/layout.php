<?php
// Admin shell. Vars: $title, $content, $flash, $user (null on the sign-in page), $route.
// [url, label, icon path, route keys, page type for the two Pages entries]
$nav = [
    ['/admin/pages/?type=service',  'Service Pages', 'M6 3h9l5 5v13H6z M14 3v6h6', ['pages'], 'service'],
    ['/admin/pages/?type=location', 'Locations',     'M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7z M12 11.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z', ['pages'], 'location'],
    ['/admin/posts/',     'Posts',         'M4 5h16M4 10h16M4 15h10M4 20h7', ['posts']],
    ['/admin/posts/new/', 'Add New Post',  'M12 5v14M5 12h14', ['posts/new']],
    ['/admin/media/',     'Media Library', 'M4 5h16v14H4z M4 15l4.5-4.5 4 4 3-3L20 16 M15.5 9.5h.01', ['media']],
    ['/admin/account/',   'Your Account',  'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M4 21a8 8 0 0 1 16 0', ['account']],
];
$currentRoute = $GLOBALS['route'] ?? '';
$navType      = $GLOBALS['admin_nav_type'] ?? '';   // which kind of page is open
$isActive = static function (array $keys, string $type) use ($currentRoute, $navType): bool {
    foreach ($keys as $key) {
        if ($key === 'pages') return strncmp($currentRoute, 'pages', 5) === 0 && $navType === $type;
        if ($key === 'posts' && $currentRoute !== 'posts/new' && ($currentRoute === 'posts' || preg_match('#^posts/\d+#', $currentRoute))) return true;
        if ($key !== 'posts' && strncmp($currentRoute, $key, strlen($key)) === 0) return true;
    }
    return false;
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5" />
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<title><?= e($title) ?> &lsaquo; Ignite Orthodontics Admin</title>
<link rel="icon" type="image/png" href="/assets/img/ignitelogo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="<?= admin_asset('admin.css') ?>">
</head>
<body class="adm<?= $user ? '' : ' adm--bare' ?>">

<?php if ($user): ?>
<header class="adm-top">
  <button class="adm-top__menu" type="button" data-adm-menu aria-label="Open menu" aria-expanded="false">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
  </button>
  <a class="adm-top__brand" href="/admin/posts/"><img src="/assets/img/ignitelogo.png" alt="Ignite Orthodontics"><span>Admin</span></a>
  <a class="adm-top__site" href="/" target="_blank" rel="noopener">View site</a>
  <div class="adm-top__user">
    <span class="adm-top__avatar" aria-hidden="true"><?= e(strtoupper(mb_substr($user['name'], 0, 1))) ?></span>
    <span class="adm-top__name"><?= e($user['name']) ?></span>
    <form method="post" action="/admin/logout/"><?= csrf_field() ?><button type="submit">Sign out</button></form>
  </div>
</header>

<nav class="adm-side" data-adm-side aria-label="Admin">
  <ul>
<?php foreach ($nav as $item): [$href, $label, $icon, $keys] = $item; $navItemType = $item[4] ?? ''; ?>
    <li><a href="<?= e($href) ?>"<?= $isActive($keys, $navItemType) ? ' class="is-active" aria-current="page"' : '' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= e($icon) ?>"/></svg><?= e($label) ?></a></li>
<?php endforeach; ?>
  </ul>
  <a class="adm-side__site" href="/blog/" target="_blank" rel="noopener">Open the blog &nearr;</a>
</nav>
<?php endif; ?>

<main class="adm-main" id="main">
<?php if ($flash): ?>
  <div class="adm-flash" role="status">
<?php foreach ($flash as [$type, $message]): ?>
    <p class="adm-notice adm-notice--<?= e($type) ?>"><?= e($message) ?></p>
<?php endforeach; ?>
  </div>
<?php endif; ?>
<?= $content ?>
</main>

<?php if ($user): ?>
<?php require APP . '/admin/views/partials/media-modal.php'; ?>
<script src="<?= admin_asset('admin.js') ?>" defer></script>
<?php endif; ?>
</body>
</html>
