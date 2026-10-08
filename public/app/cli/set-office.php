<?php
/**
 * Changes an office's details, the way the dashboard would.
 *
 *   php app/cli/set-office.php SLUG [--phone="(248) 658-8681"] [--street="1190 E 12 Mile Rd"]
 *                              [--city="Madison Heights"] [--state=MI] [--zip=48071]
 *                              [--email=...] [--hours="Mon-Fri: 9AM - 5PM|Sat: Closed"] [--dry-run]
 *
 * Only what is given changes; anything else is left alone. The address on the page,
 * the map, the tap-to-call link, the schema data, the booking page, the footer and
 * every landing page for that city all read from here, so this is the only place a
 * detail has to be changed.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap.php';

$slug   = $argv[1] ?? '';
$dryRun = in_array('--dry-run', $argv, true);
$fields = ['street', 'city', 'state', 'zip', 'phone', 'email', 'hours'];

$given = [];
foreach ($argv as $arg) {
    foreach ($fields as $field) {
        if (str_starts_with($arg, "--{$field}=")) {
            $given[$field] = substr($arg, strlen($field) + 3);
        }
    }
}
if (isset($given['hours'])) {
    $given['hours'] = str_replace('|', "\n", $given['hours']);
}

if ($slug === '' || !$given) {
    fwrite(STDERR, "usage: php app/cli/set-office.php SLUG --phone=\"(248) 658-8681\" --street=\"...\" --zip=48071 [--dry-run]\n");
    exit(1);
}
if (isset($given['phone']) && $given['phone'] !== '' && strlen(preg_replace('/\D/', '', $given['phone'])) !== 10) {
    fwrite(STDERR, "that is not a 10-digit US number, so it would not become a tap-to-call link\n");
    exit(1);
}

$stmt = db()->prepare("SELECT id, title, data FROM pages WHERE type = 'location' AND slug = ?");
$stmt->execute([$slug]);
$row = $stmt->fetch();
if (!$row) {
    fwrite(STDERR, "no office page at /locations/{$slug}/\n");
    exit(1);
}

$data = json_decode($row['data'], true);
if (!isset($data['office']) || !is_array($data['office'])) {
    fwrite(STDERR, "that page has no office section\n");
    exit(1);
}

echo $row['title'], "\n";
foreach ($given as $field => $value) {
    $was = (string) ($data['office'][$field] ?? '');
    $was = is_array($was) ? implode(' / ', $was) : $was;
    echo '  ', str_pad($field, 7), ($was !== '' ? $was : '(empty)'), '  ->  ', ($value !== '' ? str_replace("\n", ' / ', $value) : '(empty)'), "\n";
    $data['office'][$field] = $value;
}

if ($dryRun) {
    echo "dry run, nothing saved\n";
    exit(0);
}

db()->prepare('UPDATE pages SET data = ?, updated_at = ? WHERE id = ?')->execute([
    json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    date('Y-m-d H:i:s'),
    $row['id'],
]);
echo "saved\n";
