<?php
/**
 * Creates an office page, the way the dashboard would.
 *
 *   php app/cli/add-office.php "Allen Park" [--phone="(313) 462-0143"] [--street="..."]
 *                              [--zip=48101] [--email=...] [--hours="Mon-Fri: 9AM - 5PM|Sat: Closed"]
 *                              [--slug=allen-park] [--draft] [--dry-run]
 *
 * Only the name is required. Anything left out stays empty, which is deliberate:
 * an empty street shows the address as "Allen Park, MI" rather than inventing one,
 * empty hours are not printed, and an office with no phone number falls back to the
 * practice's main line. Fill them in later in the dashboard under Locations.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap.php';
require_once APP . '/sanitize.php';
require_once APP . '/admin/pages.php';

$name = $argv[1] ?? '';
if ($name === '' || str_starts_with($name, '--')) {
    fwrite(STDERR, "usage: php app/cli/add-office.php \"Allen Park\" [--phone=\"(313) 462-0143\"] [--street=...] [--zip=...] [--email=...] [--hours=\"Mon-Fri: 9AM - 5PM|Sat: Closed\"] [--slug=...] [--draft] [--dry-run]\n");
    exit(1);
}

$arg = static function (string $key, string $fallback = '') use ($argv): string {
    foreach ($argv as $given) {
        if (str_starts_with($given, "--{$key}=")) {
            return substr($given, strlen($key) + 3);
        }
    }
    return $fallback;
};
$dryRun = in_array('--dry-run', $argv, true);
$draft  = in_array('--draft', $argv, true);
$slug   = $arg('slug') !== '' ? $arg('slug') : admin_page_unique_slug($name, 'location', 0);

$stmt = db()->prepare("SELECT id FROM pages WHERE type = 'location' AND slug = ?");
$stmt->execute([$slug]);
if ($stmt->fetch()) {
    fwrite(STDERR, "there is already an office at /locations/{$slug}/\n");
    exit(1);
}

$tpl   = template('location-full');
$notes = [];
// only the office's own details are stored; every other section is left out so the
// layout draws its usual content, exactly as the offices seeded with the site do
$data  = admin_page_data($tpl, ['office' => [
    'street' => $arg('street'),
    'city'   => $arg('city', $name),
    'state'  => $arg('state', 'MI'),
    'zip'    => $arg('zip'),
    'phone'  => $arg('phone'),
    'email'  => $arg('email'),
    'hours'  => str_replace('|', "\n", $arg('hours')),
]], $notes);
$data = ['office' => $data['office']];

$now = date('Y-m-d H:i:s');
$row = [
    'type'        => 'location',
    'slug'        => $slug,
    'title'       => $name,
    'template'    => $tpl['key'],
    'description' => "Braces and clear aligners at Ignite Orthodontics {$name}. Book a no-cost consultation.",
    'seo_title'   => '',
    'image'       => '',
    'data'        => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    'status'      => $draft ? 'draft' : 'published',
    'menu'        => 0,
    // the offices are listed in a set order, so a new one joins the end of it
    'menu_order'  => 1 + (int) db()->query("SELECT MAX(menu_order) FROM pages WHERE type = 'location'")->fetchColumn(),
    'created_at'  => $now,
    'updated_at'  => $now,
];

echo ($draft ? 'DRAFT  ' : 'PUBLISH '), '/locations/', $slug, "/  ", $name, "\n";
foreach (['street', 'zip', 'phone', 'email', 'hours'] as $field) {
    $value = $data['office'][$field] ?? '';
    echo '  ', str_pad($field, 8), is_array($value) ? implode(' / ', $value) : ($value !== '' ? $value : '(empty, to be filled in later)'), "\n";
}
foreach (array_unique($notes) as $note) {
    echo "  note: {$note}\n";
}
if ($dryRun) {
    echo "dry run, nothing saved\n";
    exit(0);
}

$cols = implode(', ', array_keys($row));
$vals = ':' . implode(', :', array_keys($row));
db()->prepare("INSERT INTO pages ({$cols}) VALUES ({$vals})")->execute($row);
echo "saved\n";
