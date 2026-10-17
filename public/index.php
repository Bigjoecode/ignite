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
if ($path === 'see-smile')    { require APP . '/see-smile.php'; exit; }
if ($path === 'lead-partial') { require APP . '/lead-partial.php'; exit; }
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
        'js'          => ['lead-draft.js', 'booking.js'],
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
        'js'          => ['lead-draft.js', 'virtual.js'],
        'book_office' => $office['slug'] ?? '',
    ]);
    exit;
}

// See Your Smile: send a photo, see what treatment could change
if ($path === 'see-your-smile') {
    require_once APP . '/smile.php';
    require_once APP . '/smile-ai.php';
    $office = locations()[(string) ($_GET['office'] ?? '')] ?? null;
    render('see-smile', ['office' => $office, 'aiOn' => smile_ai_ready()], $meta + [
        'title'       => 'See Your Smile | Ignite Orthodontics',
        'description' => 'Send a photo of your smile and see what orthodontic treatment could change, with a free opinion from our team.',
        'css'         => ['booking.css', 'virtual.css', 'smile.css'],
        'js'          => ['lead-draft.js', 'smile.js'],
        'book_office' => $office['slug'] ?? '',
    ]);
    exit;
}

if ($path === 'see-your-smile/image') {
    require_once APP . '/smile.php';
    require_once APP . '/bookings.php';
    $id      = (string) ($_GET['ref'] ?? '');
    $which   = ($_GET['which'] ?? '') === 'preview' ? 'result_path' : 'photo_path';
    $request = booking_find($id);
    if (!$request || ($request['kind'] ?? '') !== 'smile'
        || !smile_image_link_valid($id, (string) ($_GET['which'] ?? ''), (int) ($_GET['exp'] ?? 0), (string) ($_GET['sig'] ?? ''))) {
        http_response_code(404);
        exit;
    }
    smile_send_image((string) $request[$which]);
}

if ($path === 'see-your-smile/sent') {
    require_once APP . '/smile.php';
    require_once APP . '/bookings.php';
    $id      = (string) ($_GET['ref'] ?? '');
    $request = booking_find($id);
    $request = $request && ($request['kind'] ?? '') === 'smile' ? $request : null;
    render('see-smile-sent', [
        'request'    => $request,
        'photoUrl'   => $request && $request['photo_path'] !== '' ? smile_image_url($id, 'photo') : '',
        'previewUrl' => $request && $request['result_path'] !== '' ? smile_image_url($id, 'preview') : '',
    ], $meta + [
        'title'   => 'Your Smile Preview | Ignite Orthodontics',
        'css'     => ['booking.css', 'virtual.css', 'smile.css'],
        'noindex' => true,
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
        'schema' => schema_all_offices(),   // the practice and all its offices
    ]);
    exit;
}

if ($path === 'locations') {
    $names = array_column(locations(), 'name');
    render('locations', [], $meta + [
        'title'       => 'Our Locations | Ignite Orthodontics',
        'description' => 'Find your nearest Ignite Orthodontics office in ' . implode(', ', array_slice($names, 0, -1)) . ' or ' . end($names) . '.',
        'css' => ['home.css', 'page.css'], 'js' => ['home.js'],
        'schema' => schema_all_offices(),
    ]);
    exit;
}

// office pages, managed in /admin/pages/
if (preg_match('#^locations/([a-z0-9-]+)$#', $path, $m) && ($row = page_find('location', $m[1]))) {
    page_render(page_prepare($row));
    exit;
}

// the sitemap people read; sitemap.xml is the one search engines read
if ($path === 'sitemap') {
    $treatments = [];
    foreach (page_rows('service') as $row) {
        // a nested page is shown under its parent's name, e.g. "Types of Braces: Ceramic Braces"
        $label = strpos($row['slug'], '/') === false
            ? $row['title']
            : (($parent = page_find('service', explode('/', $row['slug'])[0])) ? $parent['title'] . ': ' . $row['title'] : $row['title']);
        $treatments[] = ['/' . $row['slug'] . '/', $label, (string) $row['description']];
    }
    usort($treatments, static fn(array $a, array $b): int => strcmp($a[1], $b[1]));

    $offices = [];
    foreach (locations() as $l) {
        $offices[] = ['/locations/' . $l['slug'] . '/', 'Ignite Orthodontics ' . $l['name'], $l['address_full']];
    }

    $patient = [];
    foreach (pages() as $slug => $p) {
        // drafts are live but unfinished, and the legal pages get their own group
        // treatments has its own entry above, and the legal pages get their own group
        if (!empty($p['draft']) || in_array($slug, ['treatments', 'privacy-policy', 'terms-and-conditions'], true)) {
            continue;
        }
        $patient[] = ['/' . $slug . '/', $p['title'], (string) $p['description']];
    }

    $posts = [];
    foreach (posts() as $post) {
        $posts[] = ['/blog/' . $post['slug'] . '/', $post['title'], date('j F Y', strtotime($post['published']))];
    }

    require_once APP . '/consult.php';
    $book = [['/booking/', 'Book a Consultation', 'Request a no-cost visit at any office.']];
    if (consult_settings()['enabled']) {
        $book[] = ['/virtual-consultation/', 'Virtual Consultation', 'Meet an orthodontist by video.'];
    }
    $book[] = ['/see-your-smile/', 'See Your Smile', 'Send a photo and get a free opinion.'];

    render('sitemap', ['groups' => [
        ['Main pages', '', [
            ['/', 'Home', 'Braces and clear aligners across ' . count(locations()) . ' Michigan offices.'],
            ['/locations/', 'Our Locations', 'Every Ignite Orthodontics office.'],
            ['/treatments/', 'Our Treatments', 'All the ways we straighten teeth.'],
            ['/blog/', 'Blog', 'Advice on braces, aligners and orthodontic care.'],
        ]],
        ['Book a visit', '', $book],
        ['Treatments', 'Every treatment page on the site.', $treatments],
        ['Our offices', 'Each office page has its address, hours, map and own phone number.', $offices],
        ['For patients', '', $patient],
        ['From the blog', '', $posts],
        ['Legal', '', [
            ['/privacy-policy/', 'Privacy Policy', 'What this website collects and why.'],
            ['/terms-and-conditions/', 'Terms and Conditions', 'The terms for using this website.'],
        ]],
    ]], $meta + [
        'title'       => 'Sitemap | Ignite Orthodontics',
        'description' => 'Every page on igniteorthodontics.com, grouped by treatments, offices, patient information and the blog.',
        'css' => ['home.css', 'page.css', 'sitemap.css'], 'js' => ['home.js'],
    ]);
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

// the orthodontist has a page built for him, not the generic text template
if ($path === 'meet-the-doctor' && isset(pages()[$path]) && doctor_profile()) {
    $p = pages()[$path];
    render('doctor-page', ['p' => $p], $meta + [
        'title'       => $p['title'] . ' | Ignite Orthodontics',
        'description' => $p['description'],
        'css' => ['doctor-page.css'],   // the layout adds consult.css for the booking band
        'schema' => ($doctor = schema_doctor()) !== []
            ? ['@context' => 'https://schema.org', '@graph' => [$doctor, schema_practice()]]
            : null,
    ]);
    exit;
}

if (isset(pages()[$path])) {
    $p = pages()[$path];
    render('page', ['p' => $p, 'slug' => $path], $meta + [
        'title'       => $p['title'] . ' | Ignite Orthodontics',
        'description' => $p['description'],
        'css' => array_merge(['home.css', 'page.css'], !empty($p['doctor']) ? ['doctor.css'] : []), 'js' => ['home.js'],
        'noindex' => !empty($p['draft']),
        // the doctor page is where the orthodontist is described, so it says so in the markup
        'schema' => $path === 'meet-the-doctor' && ($doctor = schema_doctor()) !== []
            ? ['@context' => 'https://schema.org', '@graph' => [$doctor, schema_practice()]]
            : null,
    ]);
    exit;
}

// a page that was published at a different address keeps its old links working
if (($to = page_redirect('/' . $path . '/')) !== null) {
    redirect($to);
}

http_response_code(404);
render('404', [], ['path' => $uri, 'title' => 'Page Not Found | Ignite Orthodontics', 'noindex' => true, 'css' => ['home.css', 'page.css']]);
