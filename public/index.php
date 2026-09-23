<?php
declare(strict_types=1);

// local dev (php -S ... public/index.php): let the built-in server send real files
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

require __DIR__ . '/app/bootstrap.php';

$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = trim($uri, '/');

// admin CMS has its own router (login, posts, media, account)
if ($path === 'admin' || strncmp($path, 'admin/', 6) === 0) { require APP . '/admin/router.php'; exit; }

// endpoints without the trailing-slash convention
if ($path === 'book') { require APP . '/book.php'; exit; }
if ($path === 'book-virtual') { require APP . '/book-virtual.php'; exit; }
if ($path === 'sitemap.xml') { require APP . '/sitemap.php'; exit; }

// one canonical URL per page: always a trailing slash
if ($path !== '' && substr($uri, -1) !== '/') {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    redirect('/' . $path . '/' . ($qs !== '' ? '?' . $qs : ''));
}

$aliases = [
    'home' => '', 'terms-of-service' => 'terms-and-conditions', 'contact' => 'contact-us', 'about' => 'about-us', 'thankyou' => 'thank-you', 'book-now' => 'booking', 'appointment' => 'booking',
    // closed offices
    'locations/8-mile' => 'locations', 'locations/lathrup-village' => 'locations',
];
if (array_key_exists($path, $aliases)) {
    redirect($aliases[$path] === '' ? '/' : '/' . $aliases[$path] . '/');
}

// links from the old booking popup (/?book=1&office=slug) open the booking page
if (isset($_GET['book'])) {
    redirect(booking_url((string) ($_GET['office'] ?? '')));
}

$meta = ['path' => $path === '' ? '/' : '/' . $path . '/'];

// consultation requests: /booking/, or /booking/?office=slug with that office fixed
if ($path === 'booking') {
    $office = locations()[(string) ($_GET['office'] ?? '')] ?? null;
    render('booking', ['office' => $office], $meta + [
        'title'       => $office ? 'Book a Consultation at Ignite Orthodontics ' . $office['name'] : 'Book a No-Cost Consultation | Ignite Orthodontics',
        'description' => 'Request a no-cost orthodontic consultation at Ignite Orthodontics in about a minute. Choose a preferred day and time and we will confirm your visit.',
        'css'         => ['booking.css'],
        'js'          => ['booking.js'],
        'book_office' => $office['slug'] ?? '',
    ]);
    exit;
}

// video consultations: the free times come from the practice's Google Calendar
if ($path === 'virtual-consultation') {
    require_once APP . '/consult.php';
    $office = locations()[(string) ($_GET['office'] ?? '')] ?? null;
    render('virtual-consultation', ['slots' => consult_slots(), 'office' => $office], $meta + [
        'title'       => 'Virtual Consultation | Ignite Orthodontics',
        'description' => 'Meet an Ignite Orthodontics orthodontist by video, at no cost. Pick a time that suits you and we will send a Google Meet link.',
        'css'         => ['booking.css', 'virtual.css'],
        'js'          => ['virtual.js'],
        'book_office' => $office['slug'] ?? '',
    ]);
    exit;
}

if ($path === 'virtual-consultation/booked') {
    require_once APP . '/consult.php';
    require_once APP . '/bookings.php';
    $booking = booking_find((string) ($_GET['ref'] ?? ''));

    // "Add to my calendar" hands over the appointment as a calendar file
    if ($booking && ($booking['kind'] ?? '') === 'virtual' && isset($_GET['add'])) {
        require_once APP . '/notify.php';
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="ignite-consultation.ics"');
        header('Cache-Control: no-store');
        echo booking_ics($booking);
        exit;
    }
    render('virtual-booked', ['booking' => $booking && $booking['kind'] === 'virtual' ? $booking : null], $meta + [
        'title'   => 'Your Video Visit Is Booked | Ignite Orthodontics',
        'css'     => ['booking.css', 'virtual.css'],
        'noindex' => true,
    ]);
    exit;
}

if ($path === '') {
    render('home', [], $meta + [
        'title'       => 'Ignite Orthodontics | Braces & Clear Aligners in Michigan',
        'description' => 'Braces and clear aligners for kids, teens and adults at ' . count(locations()) . ' Ignite Orthodontics offices in Michigan. Flexible payment options and no-cost consultations.',
        'css' => ['home.css'], 'js' => ['home.js'],
    ]);
    exit;
}

if ($path === 'locations') {
    $names = array_column(locations(), 'name');
    render('locations', [], $meta + [
        'title'       => 'Our Locations | Ignite Orthodontics',
        'description' => 'Find your nearest Ignite Orthodontics office in ' . implode(', ', array_slice($names, 0, -1)) . ' or ' . end($names) . '.',
        'css' => ['home.css', 'page.css'], 'js' => ['home.js'],
    ]);
    exit;
}

// office pages, managed in /admin/pages/
if (preg_match('#^locations/([a-z0-9-]+)$#', $path, $m) && ($row = page_find('location', $m[1]))) {
    page_render(page_prepare($row));
    exit;
}

if ($path === 'thank-you') {
    render('thank-you', [], $meta + [
        'title' => 'Thank You | Ignite Orthodontics',
        'css'   => ['home.css', 'page.css', 'booking.css'],
        'noindex' => true,
    ]);
    exit;
}

if ($path === 'blog') {
    render('blog-index', [], $meta + [
        'title'       => 'Orthodontic Tips & Advice | Ignite Orthodontics Blog',
        'description' => 'Practical advice on braces, clear aligners and orthodontic care for kids, teens and adults from the Ignite Orthodontics team.',
        'css' => ['home.css', 'page.css', 'blog.css'], 'js' => ['blog.js'],
    ]);
    exit;
}

if (preg_match('#^blog/([a-z0-9-]+)$#', $path, $m) && isset(posts()[$m[1]])) {
    $post = posts()[$m[1]];
    render('blog-post', ['post' => $post], $meta + [
        'title'       => $post['title'] . ' | Ignite Orthodontics',
        'description' => $post['description'],
        'css' => ['home.css', 'page.css', 'blog.css'], 'js' => ['blog.js'],
        'og_type'   => 'article',
        'image'     => $post['image'],
        'published' => $post['published'],
        'modified'  => $post['updated'],
        'schema'    => post_schema($post),
    ]);
    exit;
}

// ad landing pages, managed in /admin/pages/?type=lp: /lp/farmingtonhills/ and /lp/farmingtonhills/braces-kids/
if (preg_match('#^lp/([a-z0-9-]+(?:/[a-z0-9-]+)?)$#', $path, $m) && ($row = page_find('lp', $m[1]))) {
    page_render(page_prepare($row));
    exit;
}

// service pages, managed in /admin/pages/ (one level of nesting: /types-of-braces/ceramic-braces/)
if (preg_match('#^[a-z0-9-]+(?:/[a-z0-9-]+)?$#', $path) && ($row = page_find('service', $path))) {
    page_render(page_prepare($row));
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

// a page that was published at a different address keeps its old links working
if (($to = page_redirect('/' . $path . '/')) !== null) {
    redirect($to);
}

http_response_code(404);
render('404', [], ['path' => $uri, 'title' => 'Page Not Found | Ignite Orthodontics', 'noindex' => true, 'css' => ['home.css', 'page.css']]);
