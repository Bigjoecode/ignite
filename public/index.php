<?php
declare(strict_types=1);

// local dev (php -S ... public/index.php): let the built-in server send real files
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

require __DIR__ . '/app/bootstrap.php';

$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = trim($uri, '/');

// endpoints without the trailing-slash convention
if ($path === 'book') { require APP . '/book.php'; exit; }
if ($path === 'sitemap.xml') { require APP . '/sitemap.php'; exit; }

// one canonical URL per page: always a trailing slash
if ($path !== '' && substr($uri, -1) !== '/') {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    redirect('/' . $path . '/' . ($qs !== '' ? '?' . $qs : ''));
}

$aliases = ['home' => '', 'terms-of-service' => 'terms-and-conditions', 'contact' => 'contact-us', 'about' => 'about-us', 'thankyou' => 'thank-you'];
if (array_key_exists($path, $aliases)) {
    redirect($aliases[$path] === '' ? '/' : '/' . $aliases[$path] . '/');
}

$meta = ['path' => $path === '' ? '/' : '/' . $path . '/'];

if ($path === '') {
    render('home', [], $meta + [
        'title'       => 'Ignite Orthodontics | Braces & Clear Aligners in Michigan',
        'description' => 'Braces and clear aligners for kids, teens and adults at 6 Ignite Orthodontics offices in Michigan. Flexible payment options and no-cost consultations.',
        'css' => ['home.css'], 'js' => ['home.js'],
    ]);
    exit;
}

if ($path === 'locations') {
    render('locations', [], $meta + [
        'title'       => 'Our Locations | Ignite Orthodontics',
        'description' => 'Find your nearest Ignite Orthodontics office in Sterling Heights, Madison Heights, Farmington Hills, Flint, Highland Park or Lathrup Village.',
        'css' => ['home.css', 'page.css'], 'js' => ['home.js'],
    ]);
    exit;
}

if (preg_match('#^locations/([a-z0-9-]+)$#', $path, $m) && isset(locations()[$m[1]])) {
    $loc = locations()[$m[1]];
    render('location', ['loc' => $loc], $meta + [
        'title'       => "Orthodontist in {$loc['city']}, MI | Ignite Orthodontics {$loc['name']}",
        'description' => "Braces and clear aligners at Ignite Orthodontics {$loc['name']}, {$loc['address_full']}. Book a no-cost consultation or call {$loc['phone']}.",
        'css' => ['location.css'], 'js' => ['location.js'],
        'book_office' => $loc['slug'], // booking popup preselects this office
    ]);
    exit;
}

if ($path === 'braces-for-kids') {
    render('kids', [], $meta + [
        'title'       => 'Braces for Kids & Early Orthodontics | Ignite Orthodontics',
        'description' => 'Gentle, affordable braces for kids and teens. Early growth guidance, flexible payment plans and stress-free orthodontic care for children.',
        'css' => ['kids.css'], 'js' => ['kids.js'],
    ]);
    exit;
}

if ($path === 'thank-you') {
    render('thank-you', [], $meta + [
        'title' => 'Thank You | Ignite Orthodontics',
        'css'   => ['home.css', 'page.css'],
        'noindex' => true,
    ]);
    exit;
}

if (isset(pages()[$path])) {
    $p = pages()[$path];
    render('page', ['p' => $p, 'slug' => $path], $meta + [
        'title'       => $p['title'] . ' | Ignite Orthodontics',
        'description' => $p['description'],
        'css' => ['home.css', 'page.css'], 'js' => ['home.js'],
        'noindex' => !empty($p['draft']),
    ]);
    exit;
}

http_response_code(404);
render('404', [], ['path' => $uri, 'title' => 'Page Not Found | Ignite Orthodontics', 'noindex' => true, 'css' => ['home.css', 'page.css']]);
