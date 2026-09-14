<?php
declare(strict_types=1);

// PHP builds without mbstring (some local installs): byte-safe enough for length caps
if (!function_exists('mb_substr')) {
    function mb_substr(string $string, int $start, ?int $length = null): string
    {
        return substr($string, $start, $length);
    }
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function cfg(string $key)
{
    global $config;
    return $config[$key] ?? null;
}

/** Offices keyed by slug, with display address and map query derived. */
function locations(): array
{
    static $locations;
    if ($locations === null) {
        $locations = [];
        foreach (require APP . '/data/locations.php' as $l) {
            $l['address_full'] = "{$l['street']}, {$l['city']}, MI" . ($l['zip'] !== '' ? " {$l['zip']}" : '');
            $l['map_q']        = "{$l['street']} {$l['city']} MI {$l['zip']}";
            $locations[$l['slug']] = $l;
        }
    }
    return $locations;
}

function pages(): array
{
    static $pages;
    return $pages ??= require APP . '/data/pages.php';
}

/** Asset URL with a modification-time query so browsers pick up new deploys. */
function asset(string $file): string
{
    $path = WEBROOT . '/assets/' . $file;
    return '/assets/' . $file . (is_file($path) ? '?v=' . filemtime($path) : '');
}

function render(string $view, array $vars = [], array $meta = []): void
{
    extract($vars);
    ob_start();
    require APP . '/views/' . $view . '.php';
    $content = ob_get_clean();

    $meta += ['title' => cfg('site_name'), 'description' => '', 'css' => [], 'js' => [], 'noindex' => false, 'path' => '/'];
    require APP . '/views/layout.php';
}

function redirect(string $to, int $code = 301): void
{
    header('Location: ' . $to, true, $code);
    exit;
}
