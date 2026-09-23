<?php
declare(strict_types=1);

// Admin CMS router: /admin/... Included from public/index.php ($path is the request path without slashes).

require_once APP . '/auth.php';
require_once APP . '/sanitize.php';
require_once APP . '/media.php';
require_once APP . '/admin/functions.php';
require_once APP . '/admin/fields.php';
require_once APP . '/admin/pages.php';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store, private');
header('Referrer-Policy: same-origin');

$route  = trim(substr($path, 5), '/');          // e.g. "", "login", "posts", "posts/12", "media/upload"
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST' && !csrf_valid()) {
    if (admin_wants_json()) {
        admin_json(419, ['ok' => false, 'error' => 'Your session expired. Reload the page and try again.']);
    }
    admin_flash('error', 'Your session expired, so nothing was saved. Please try again.');
    redirect('/admin/' . ($route === 'login' ? 'login/' : ''), 303);
}

/* ---------- sign in / sign out (no login required) ---------- */
if ($route === 'login') {
    if (current_admin()) {
        redirect('/admin/posts/', 303);
    }
    $error = null;
    $email = '';
    if ($method === 'POST') {
        $email  = (string) ($_POST['email'] ?? '');
        $result = admin_attempt_login($email, (string) ($_POST['password'] ?? ''));
        if (is_array($result)) {
            $next = (string) ($_GET['next'] ?? '');
            redirect(preg_match('#^/admin/[A-Za-z0-9/_?=&%.-]*$#', $next) && strpos($next, '//') === false ? $next : '/admin/posts/', 303);
        }
        $error = $result;
    }
    admin_render('login', ['error' => $error, 'email' => $email], 'Sign in', null);
    exit;
}

if ($route === 'logout' && $method === 'POST') {
    admin_logout();
    redirect('/admin/login/', 303);
}

/* ---------- everything below needs a signed-in admin ---------- */
$user = require_admin(admin_wants_json());

if ($route === '') {
    redirect('/admin/posts/', 302);
}

// posts
if ($route === 'posts' && $method === 'GET') {
    admin_posts_index($user);
    exit;
}
if ($route === 'posts/new' && $method === 'GET') {
    admin_post_editor($user, null);
    exit;
}
if ($route === 'posts/save' && $method === 'POST') {
    admin_save_post($user);
    exit;
}
if (preg_match('#^posts/(\d+)$#', $route, $m) && $method === 'GET') {
    admin_post_editor($user, (int) $m[1]);
    exit;
}
if (preg_match('#^posts/(\d+)/preview$#', $route, $m) && $method === 'GET') {
    admin_preview((int) $m[1]);
    exit;
}
if (preg_match('#^posts/(\d+)/(trash|restore|delete)$#', $route, $m) && $method === 'POST') {
    admin_post_status((int) $m[1], $m[2]);
    exit;
}

// service and office pages
if ($route === 'pages' && $method === 'GET') {
    admin_pages_index($user);
    exit;
}
if ($route === 'pages/new' && $method === 'GET') {
    admin_page_new($user);
    exit;
}
if ($route === 'pages/save' && $method === 'POST') {
    admin_save_page($user);
    exit;
}
if ($route === 'pages/preview' && $method === 'POST') {
    admin_page_preview_post($user);
    exit;
}
if (preg_match('#^pages/(\d+)$#', $route, $m) && $method === 'GET') {
    admin_page_editor($user, (int) $m[1]);
    exit;
}
if (preg_match('#^pages/(\d+)/preview$#', $route, $m) && $method === 'GET') {
    admin_page_preview((int) $m[1]);
    exit;
}
if (preg_match('#^pages/(\d+)/(trash|restore|delete|duplicate)$#', $route, $m) && $method === 'POST') {
    admin_page_action((int) $m[1], $m[2]);
    exit;
}

// media library
if ($route === 'media' && $method === 'GET') {
    admin_render('media', [], 'Media Library', $user);
    exit;
}
if ($route === 'media/list' && $method === 'GET') {
    admin_media_list();
}
if ($route === 'media/upload' && $method === 'POST') {
    admin_media_upload($user);
}
if ($route === 'media/url' && $method === 'POST') {
    admin_media_url($user);
}
if (preg_match('#^media/(\d+)$#', $route, $m) && $method === 'POST') {
    admin_media_update((int) $m[1]);
}
if (preg_match('#^media/(\d+)/delete$#', $route, $m) && $method === 'POST') {
    media_delete((int) $m[1]);
    admin_json(200, ['ok' => true]);
}

// settings (booking notification emails)
if ($route === 'settings') {
    require_once APP . '/admin/settings.php';
    admin_settings($user, $method);
    exit;
}

// account
if ($route === 'account') {
    admin_account($user, $method);
    exit;
}

admin_not_found($user);
