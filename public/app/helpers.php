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

/**
 * Published blog posts keyed by slug, newest first (managed in /admin; stored in the CMS database).
 * Scheduled posts appear once their publish time has passed.
 */
function posts(): array
{
    static $posts;
    if ($posts === null) {
        $posts = [];
        $stmt = db()->prepare("SELECT * FROM posts WHERE status = 'published' AND published_at <= ? ORDER BY published_at DESC, id DESC");
        $stmt->execute([date('Y-m-d H:i:s')]);
        foreach ($stmt as $row) {
            $post = post_prepare($row);
            $posts[$post['slug']] = $post;
        }
    }
    return $posts;
}

/** Shapes a posts-table row for the blog views (also used by the admin preview). */
function post_prepare(array $row): array
{
    $published = substr((string) ($row['published_at'] ?: $row['created_at']), 0, 10);
    $updated   = substr((string) $row['updated_at'], 0, 10);
    $post = [
        'id'          => (int) $row['id'],
        'slug'        => $row['slug'],
        'title'       => $row['title'] !== '' ? $row['title'] : 'Untitled post',
        'description' => $row['description'],
        'category'    => $row['category'] !== '' ? $row['category'] : 'Articles',
        'published'   => $published,
        'updated'     => $updated > $published ? $updated : $published,
        'image'       => $row['image'] !== '' ? $row['image'] : '/assets/img/IMG_20260814_125716.jpg',
        'image_alt'   => $row['image_alt'],
        'featured'    => (bool) $row['featured'],
        'faq'         => json_decode((string) $row['faq'], true) ?: [],
    ];
    [$post['body'], $post['toc']] = post_toc((string) $row['body']);
    $post['words']   = str_word_count(strip_tags($post['body']));
    $post['minutes'] = max(1, (int) ceil($post['words'] / 200));
    return $post;
}

/** Gives each <h2> an id and returns [body, table of contents]. */
function post_toc(string $html): array
{
    $toc = [];
    $html = preg_replace_callback('#<h2>(.*?)</h2>#s', static function (array $m) use (&$toc): string {
        $text = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8'));
        $id   = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($text)), '-');
        $toc[] = [$id, $text];
        return '<h2 id="' . $id . '">' . $m[1] . '</h2>';
    }, $html);
    return [$html, $toc];
}

function post_date(string $ymd, string $format = 'F j, Y'): string
{
    return date($format, strtotime($ymd));
}

/** URL-safe topic key, e.g. "Kids & Teens" -> "kids-teens" (used by /blog/?topic=). */
function post_topic(string $category): string
{
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($category)), '-');
}

/** Schema.org BlogPosting + BreadcrumbList (+ FAQPage) for a post page. */
function post_schema(array $post): array
{
    $base = cfg('base_url');
    $url  = $base . '/blog/' . $post['slug'] . '/';
    $org  = ['@type' => 'Organization', 'name' => cfg('site_name'), 'url' => $base . '/',
             'logo' => ['@type' => 'ImageObject', 'url' => $base . '/assets/img/ignitelogo.png']];
    $graph = [
        [
            '@type'            => 'BlogPosting',
            'headline'         => $post['title'],
            'description'      => $post['description'],
            'image'            => $base . $post['image'],
            'datePublished'    => $post['published'],
            'dateModified'     => $post['updated'],
            'author'           => $org,
            'publisher'        => $org,
            'mainEntityOfPage' => $url,
            'articleSection'   => $post['category'],
            'wordCount'        => $post['words'],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $base . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => $base . '/blog/'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $post['title'], 'item' => $url],
            ],
        ],
    ];
    if (!empty($post['faq'])) {
        $graph[] = [
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(static fn(array $f): array => [
                '@type' => 'Question', 'name' => $f[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
            ], $post['faq']),
        ];
    }
    return ['@context' => 'https://schema.org', '@graph' => $graph];
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
