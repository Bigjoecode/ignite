<?php
// POST /lead-partial — someone filled in their details on the See Your Smile page
// but never pressed the button. Their details are saved so the practice can follow
// up, marked "Started, never finished".
//
// They have NOT agreed to anything: the consent box is on the submit they did not
// make. The dashboard says so on every one of these, because that is the difference
// between a lead and someone who simply typed in a box.
declare(strict_types=1);

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once APP . '/bookings.php';

// only from this site's own pages
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) {
    http_response_code(403);
    exit;
}

$field = static fn(string $key, int $max): string => mb_substr(trim((string) ($_POST[$key] ?? '')), 0, $max);

$draft = $field('draft_id', 32);
$email = $field('email', 160);
$phone = $field('phone', 30);

// nothing worth keeping without a way to reach them
if (!preg_match('/^[a-f0-9]{16,32}$/', $draft)
    || (!filter_var($email, FILTER_VALIDATE_EMAIL) && strlen(preg_replace('/\D/', '', $phone)) < 10)) {
    http_response_code(204);
    exit;
}

$existing = booking_find($draft);
if ($existing && $existing['status'] !== 'unfinished') {
    http_response_code(204);      // they finished after all; leave the real request alone
    exit;
}

$record = [
    'id'         => $draft,
    'created_at' => gmdate('c'),
    'status'     => 'unfinished',
    'kind'       => $field('kind', 20) === 'virtual' ? 'virtual' : 'smile',
    'office'     => isset(locations()[$field('office', 60)]) ? $field('office', 60) : '',
    'first_name' => $field('first_name', 60),
    'last_name'  => $field('last_name', 60),
    'phone'      => $phone,
    'email'      => $email,
    'concerns'   => $field('concerns', 20),
    'notes'      => $field('notes', 1000),
    'source'     => mb_substr((string) ($_POST['source'] ?? ''), 0, 200),
    'date'       => 'first',
];

if ($existing) {
    db()->prepare('UPDATE bookings SET first_name = ?, last_name = ?, phone = ?, email = ?, office = ?, concerns = ?, notes = ? WHERE id = ? AND status = ?')
        ->execute([$record['first_name'], $record['last_name'], $record['phone'], $record['email'],
            $record['office'], $record['concerns'], $record['notes'], $draft, 'unfinished']);
} else {
    booking_store($record);       // no email to the offices: this is not a request yet
}

http_response_code(204);
