<?php
/**
 * Sets an office's phone number, the way the dashboard would.
 *
 *   php app/cli/set-office-phone.php SLUG "(947) 254-1718" [--dry-run]
 *
 * The tap-to-call link, the schema data and the booking page all read the number
 * from here, so this is the only place it has to be changed.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap.php';

$slug   = $argv[1] ?? '';
$phone  = $argv[2] ?? '';
$dryRun = in_array('--dry-run', $argv, true);

if ($slug === '' || $phone === '') {
    fwrite(STDERR, "usage: php app/cli/set-office-phone.php SLUG \"(947) 254-1718\" [--dry-run]\n");
    exit(1);
}
if (strlen(preg_replace('/\D/', '', $phone)) !== 10) {
    fwrite(STDERR, "that is not a 10-digit US number, so it would not become a tap-to-call link\n");
    exit(1);
}

$stmt = db()->prepare("SELECT id, title, data FROM pages WHERE type = 'location' AND slug = ?");
$stmt->execute([$slug]);
$row = $stmt->fetch();
if (!$row) {
    fwrite(STDERR, "no office page with the address /locations/{$slug}/\n");
    exit(1);
}

$data = json_decode($row['data'], true);
if (!isset($data['office']) || !is_array($data['office'])) {
    fwrite(STDERR, "that page has no office section to put a phone number in\n");
    exit(1);
}

$was = (string) ($data['office']['phone'] ?? '');
$data['office']['phone'] = $phone;

echo $row['title'], ': ', $was === '' ? '(empty)' : $was, ' -> ', $phone, "\n";
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
