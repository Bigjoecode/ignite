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

function pages(): array
{
    static $pages;
    return $pages ??= require APP . '/data/pages.php';
}

/* ============================================================
   SERVICE AND OFFICE PAGES (managed in /admin/pages/)
   ============================================================ */

/** Where a page type lives: /page/, /locations/office/, /lp/office/page/. */
function page_url_prefix(string $type): string
{
    return ['location' => '/locations/', 'lp' => '/lp/'][$type] ?? '/';
}

/** Page types that can sit one level under another page of the same type. */
function page_nests(string $type): bool
{
    return $type === 'service' || $type === 'lp';
}

/** Rows of one page type ('service', 'location' or 'lp'), in menu order. */
function page_rows(string $type, string $status = 'published'): array
{
    static $cache = [];
    $key = $type . '/' . $status;
    if (!isset($cache[$key])) {
        $stmt = db()->prepare('SELECT * FROM pages WHERE type = ? AND status = ? ORDER BY menu_order, title');
        $stmt->execute([$type, $status]);
        $cache[$key] = $stmt->fetchAll();
    }
    return $cache[$key];
}

function page_find(string $type, string $slug, string $status = 'published'): ?array
{
    $stmt = db()->prepare('SELECT * FROM pages WHERE type = ? AND slug = ? AND status = ?');
    $stmt->execute([$type, $slug, $status]);
    return $stmt->fetch() ?: null;
}

/** Offices keyed by slug, in menu order. Falls back to the starter file if the database has none. */
function locations(): array
{
    static $locations;
    if ($locations !== null) {
        return $locations;
    }
    $locations = [];
    try {
        foreach (page_rows('location') as $row) {
            $locations[$row['slug']] = page_office($row);
        }
    } catch (Throwable $e) {
        error_log('locations: ' . $e->getMessage());
    }
    if (!$locations) {
        foreach (require APP . '/data/locations.php' as $l) {
            $locations[$l['slug']] = page_office_shape($l['slug'], $l['name'], $l);
        }
    }
    return $locations;
}

/** Service pages shown in the Treatments menu, as [href, label, summary]. */
function service_menu(): array
{
    static $menu;
    if ($menu !== null) {
        return $menu;
    }
    $menu = [];
    try {
        foreach (page_rows('service') as $row) {
            if ((int) $row['menu'] === 1) {
                $menu[] = ['/' . $row['slug'] . '/', $row['title'], $row['description']];
            }
        }
    } catch (Throwable $e) {
        error_log('service menu: ' . $e->getMessage());
    }
    return $menu;
}

/** Where an old address now points, for slugs that changed after publishing. */
function page_redirect(string $from): ?string
{
    try {
        $stmt = db()->prepare('SELECT to_path FROM redirects WHERE from_path = ?');
        $stmt->execute([$from]);
        $to = $stmt->fetchColumn();
    } catch (Throwable $e) {
        return null;
    }
    return $to === false ? null : (string) $to;
}

function page_office(array $row): array
{
    $data = json_decode((string) $row['data'], true) ?: [];
    return page_office_shape($row['slug'], $row['title'], (array) ($data['office'] ?? []));
}

/** The office fields every view uses, with the address, phone link and hours derived. */
function page_office_shape(string $slug, string $name, array $f): array
{
    $get   = static fn(string $k): string => trim((string) ($f[$k] ?? ''));
    $state = $get('state') !== '' ? $get('state') : 'MI';
    $zip   = $get('zip');

    $digits = preg_replace('/\D/', '', $get('phone'));
    $tel    = '';
    if (strlen($digits) === 10) {
        $tel = '+1' . $digits;
    } elseif (strlen($digits) === 11 && $digits[0] === '1') {
        $tel = '+' . $digits;
    }

    $hours = $f['hours'] ?? '';
    $hours = is_array($hours) ? $hours : preg_split('/\R/', (string) $hours);
    $hours = array_values(array_filter(array_map('trim', $hours ?: []), static fn(string $l): bool => $l !== ''));

    return [
        'slug'         => $slug,
        'name'         => $name,
        'street'       => $get('street'),
        'city'         => $get('city') !== '' ? $get('city') : $name,
        'state'        => $state,
        'zip'          => $zip,
        'phone'        => $get('phone') !== '' ? $get('phone') : (string) cfg('phone'),
        'tel'          => $tel,
        'email'        => $get('email'),
        'hours'        => $hours,
        'address_full' => implode(', ', array_filter([$get('street'), $get('city'), trim($state . ' ' . $zip)])),
        'map_q'        => trim($get('street') . ' ' . $get('city') . ' ' . $state . ' ' . $zip),
    ];
}

/**
 * Shapes a pages-table row for the template views: template defaults filled in,
 * {office}/{city} replaced, and the visible sections listed in template order.
 */
function page_prepare(array $row): array
{
    $tpl    = template($row['template']) ?? template(template_default($row['type']));
    $data   = json_decode((string) $row['data'], true) ?: [];
    $office = null;
    if ($row['type'] === 'location') {
        $office = page_office($row);
    } elseif ($row['type'] === 'lp') {
        // a landing page advertises one office, chosen in its settings
        $office = locations()[(string) ($data['settings']['office'] ?? '')] ?? null;
    }
    $tokens = $office ? ['{office}' => $office['name'], '{city}' => $office['city']] : [];
    // a saved page lists the sections it hides; seeded pages use the template's own defaults
    $hidden = array_key_exists('_off', $data) ? array_flip((array) $data['_off']) : null;

    $d = $on = [];
    foreach ($tpl['sections'] as $key => $section) {
        $d[$key] = tpl_section($section, (array) ($data[$key] ?? []), $tokens);
        if ($hidden !== null ? !isset($hidden[$key]) : empty($section['off'])) {
            $on[] = $key;
        }
    }

    return [
        'id'          => (int) $row['id'],
        'type'        => $row['type'],
        'slug'        => $row['slug'],
        'title'       => $row['title'],
        'status'      => $row['status'],
        'template'    => $tpl['key'],
        'tpl'         => $tpl,
        'seo_title'   => (string) $row['seo_title'],
        'description' => (string) $row['description'],
        'image'       => (string) $row['image'],
        'updated'     => substr((string) $row['updated_at'], 0, 10),
        'office'      => $office,
        'path'        => page_url_prefix($row['type']) . $row['slug'] . '/',
        'd'           => $d,
        'on'          => $on,
    ];
}

/** One section's values: what was saved, or the template's default for anything never set. */
function tpl_section(array $section, array $stored, array $tokens): array
{
    $out = [];
    foreach ($section['fields'] ?? [] as $field) {
        $key = $field['key'];
        if ($field['type'] === 'blocks') {
            // a list where each entry picks its own set of fields by '_type'
            $blocks = array_key_exists($key, $stored) && is_array($stored[$key])
                ? array_values($stored[$key])
                : ($field['default'] ?? []);
            $out[$key] = [];
            foreach ($blocks as $block) {
                $block = (array) $block;
                $type  = (string) ($block['_type'] ?? '');
                if (isset($field['types'][$type])) {
                    $out[$key][] = ['_type' => $type] + tpl_section(['fields' => $field['types'][$type]['fields']], $block, $tokens);
                }
            }
            continue;
        }
        if ($field['type'] === 'list') {
            $items = array_key_exists($key, $stored) && is_array($stored[$key])
                ? array_values($stored[$key])
                : ($field['default'] ?? []);
            $out[$key] = array_map(
                static fn($item): array => tpl_section(['fields' => $field['fields']], (array) $item, $tokens),
                $items
            );
            continue;
        }
        $value = array_key_exists($key, $stored) ? $stored[$key] : ($field['default'] ?? '');
        if ($field['type'] === 'lines') {
            $out[$key] = array_values(array_filter(
                array_map(static fn(string $l): string => strtr(trim($l), $tokens), preg_split('/\R/', is_array($value) ? implode("\n", $value) : (string) $value) ?: []),
                static fn(string $l): bool => $l !== ''
            ));
            continue;
        }
        $out[$key] = strtr((string) $value, $tokens);
        if ($field['type'] === 'image') {
            $alt = array_key_exists($key . '_alt', $stored) ? (string) $stored[$key . '_alt'] : (string) ($field['alt_default'] ?? '');
            $out[$key . '_alt'] = strtr($alt, $tokens);
        }
    }
    return $out;
}

/** True when a template field has something to show. */
function tpl_has(array $section, string $key): bool
{
    return isset($section[$key]) && trim((string) $section[$key]) !== '';
}

/** Page text with *highlighted words* wrapped for the brand colour. */
function tpl_em(string $text, string $tag = 'em'): string
{
    return preg_replace('/\*([^*]+)\*/', '<' . $tag . '>$1</' . $tag . '>', e($text));
}

/** The same text with the highlight markers simply removed. */
function tpl_plain(string $text): string
{
    return str_replace('*', '', e($text));
}

/** Offer titles: | breaks the line and * becomes the small-print marker. */
function tpl_offer(string $text): string
{
    return str_replace(['|', '*'], ['<br>', '<sup>*</sup>'], e($text));
}

/** A link entered in the admin, if it stays on this site. */
function tpl_link(string $href): string
{
    $href = trim($href);
    if ($href === '' || strncmp($href, '//', 2) === 0) {
        return '';
    }
    return $href[0] === '/' || $href[0] === '#' ? $href : '';
}

/** Schema.org data for a service or office page. */
function page_schema(array $p): array
{
    $base = cfg('base_url');
    $url  = $base . $p['path'];
    $org  = ['@type' => 'Organization', 'name' => cfg('site_name'), 'url' => $base . '/',
             'logo' => ['@type' => 'ImageObject', 'url' => $base . '/assets/img/ignitelogo.png']];

    $crumbs = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $base . '/']];
    if ($p['type'] === 'location') {
        $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => 'Our Locations', 'item' => $base . '/locations/'];
    } elseif (($parent = page_parent($p)) !== null) {
        $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $parent['title'], 'item' => $base . page_url_prefix($parent['type']) . $parent['slug'] . '/'];
    }
    $crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => $p['title'], 'item' => $url];

    if ($p['type'] === 'location' && $p['office']) {
        $o = $p['office'];
        $main = [
            '@type' => 'Dentist',
            'name'  => cfg('site_name') . ' ' . $p['title'],
            'url'   => $url,
            'parentOrganization' => $org,
            'address' => array_filter([
                '@type'           => 'PostalAddress',
                'streetAddress'   => $o['street'],
                'addressLocality' => $o['city'],
                'addressRegion'   => $o['state'],
                'postalCode'      => $o['zip'],
                'addressCountry'  => 'US',
            ]),
        ];
        if ($o['tel'] !== '')   $main['telephone'] = $o['tel'];
        if ($o['email'] !== '') $main['email'] = $o['email'];
        if ($p['image'] !== '') $main['image'] = $base . $p['image'];
    } else {
        $main = ['@type' => 'WebPage', 'name' => $p['title'], 'url' => $url, 'description' => $p['description'], 'publisher' => $org];
    }

    $graph = [$main, ['@type' => 'BreadcrumbList', 'itemListElement' => $crumbs]];

    $faq = in_array('faq', $p['on'], true) ? ($p['d']['faq']['items'] ?? []) : [];
    // guide pages keep their questions in FAQ blocks
    foreach ($p['d']['content']['blocks'] ?? [] as $block) {
        if ($block['_type'] === 'faq') {
            $faq = array_merge($faq, $block['items']);
        }
    }
    $faq = array_values(array_filter($faq, static fn(array $f): bool => trim((string) ($f['q'] ?? '')) !== '' && trim((string) ($f['a'] ?? '')) !== ''));
    if ($faq) {
        $graph[] = [
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(static fn(array $f): array => [
                '@type' => 'Question', 'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ], $faq),
        ];
    }
    return ['@context' => 'https://schema.org', '@graph' => $graph];
}

/** The published page a nested service page sits under (types-of-braces for types-of-braces/ceramic-braces). */
function page_parent(array $p): ?array
{
    if (!page_nests($p['type']) || strpos($p['slug'], '/') === false) {
        return null;
    }
    try {
        return page_find($p['type'], strstr($p['slug'], '/', true));
    } catch (Throwable $e) {
        return null;
    }
}

/** Renders a prepared page through its template's layout. */
function page_render(array $p, array $meta = []): void
{
    $title = $p['seo_title'] !== '' ? $p['seo_title'] : $p['title'] . ' | ' . cfg('site_name');
    $meta += [
        'path'        => $p['path'],
        'title'       => $title,
        'description' => $p['description'],
        'css'         => $p['tpl']['css'],
        'js'          => $p['tpl']['js'],
        'schema'      => page_schema($p),
    ];
    if ($p['image'] !== '') {
        $meta += ['image' => $p['image']];
    }
    if ($p['office']) {
        $meta += ['book_office' => $p['office']['slug']]; // booking links on this page fix this office
    }
    if ($p['type'] === 'lp') {
        // ad landing pages: slim header and footer, hidden from Google unless switched on
        $meta += ['layout' => 'lp', 'noindex' => ($p['d']['settings']['index'] ?? 'no') !== 'yes', 'office' => $p['office']];
    }
    render('templates/' . $p['tpl']['family'], ['page' => $p], $meta);
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

/**
 * The booking page, with the office already chosen when the visitor is on an office page
 * (or when one is passed): /booking/?office=sterling-heights.
 */
function booking_url(?string $office = null): string
{
    $office = $office ?? (string) ($GLOBALS['ig_book_office'] ?? '');
    return '/booking/' . ($office !== '' && isset(locations()[$office]) ? '?office=' . rawurlencode($office) : '');
}

function render(string $view, array $vars = [], array $meta = []): void
{
    // every booking link on this page (header included) carries the page's office
    $GLOBALS['ig_book_office'] = (string) ($meta['book_office'] ?? '');
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
