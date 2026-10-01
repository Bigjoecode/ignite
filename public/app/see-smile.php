<?php
// POST /see-smile — a photo sent in from the See Your Smile page.
// The photo is stored privately, the request appears in the dashboard, the offices
// are emailed, and a preview is generated when the practice has switched that on.
declare(strict_types=1);

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    redirect('/see-your-smile/', 303);
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once APP . '/smile.php';
require_once APP . '/smile-ai.php';
require_once APP . '/notify.php';

function smile_reply(int $code, array $body): void
{
    http_response_code($code);
    echo json_encode($body);
    exit;
}

$again = 'Please try again, or call us and we will take a look for you.';

// only accept submissions sent from this site's own pages
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) {
    smile_reply(403, ['ok' => false, 'error' => 'This request could not be accepted. ' . $again]);
}

$field = static fn(string $key, int $max): string => mb_substr(trim((string) ($_POST[$key] ?? '')), 0, $max);

// honeypot: invisible to people, filled in by bots — pretend success
if ($field('website', 200) !== '') {
    smile_reply(200, ['ok' => true, 'redirect' => '/see-your-smile/sent/']);
}

$req = [
    'first_name' => $field('first_name', 60),
    'last_name'  => $field('last_name', 60),
    'phone'      => $field('phone', 30),
    'email'      => $field('email', 160),
    'office'     => $field('office', 60),
    'concerns'   => $field('concerns', 20),
    'notes'      => $field('notes', 1000),
];

$errors = [];
if ($req['first_name'] === '')                          $errors[] = ['first_name', 'first name'];
if (!filter_var($req['email'], FILTER_VALIDATE_EMAIL))  $errors[] = ['email', 'email'];
if ($req['phone'] !== '' && strlen(preg_replace('/\D/', '', $req['phone'])) < 10) $errors[] = ['phone', 'phone number'];
if (!isset(locations()[$req['office']]))                $errors[] = ['office', 'nearest office'];
if (empty($_POST['consent']))                           $errors[] = ['consent', 'consent checkbox'];
if ($errors) {
    smile_reply(422, [
        'ok'    => false,
        'field' => $errors[0][0],
        'error' => 'Please check your ' . implode(', ', array_unique(array_column($errors, 1))) . '.',
    ]);
}

$id = bin2hex(random_bytes(8));
[$photo, $problem] = smile_store_photo($_FILES['photo'] ?? [], $id);
if ($problem !== null) {
    smile_reply(422, ['ok' => false, 'field' => 'photo', 'error' => $problem]);
}

$record = [
    'id'          => $id,
    'created_at'  => gmdate('c'),
    'status'      => 'new',
    'kind'        => 'smile',
    'office'      => $req['office'],
    'first_name'  => $req['first_name'],
    'last_name'   => $req['last_name'],
    'phone'       => $req['phone'],
    'email'       => $req['email'],
    'notes'       => $req['notes'],
    'concerns'    => $req['concerns'],
    'photo_path'  => $photo,
    'source'      => mb_substr((string) ($_POST['source'] ?? '/see-your-smile/'), 0, 200),
    'date'        => 'first',
];

if (!booking_store($record)) {
    smile_reply(500, ['ok' => false, 'error' => 'We could not save your photo just now. ' . $again]);
}

// the preview, when the practice has an image service set up; the request stands either way
$preview = smile_ai_preview($photo, $id);
if ($preview !== null) {
    $record['result_path'] = $preview;
    db()->prepare('UPDATE bookings SET result_path = ? WHERE id = ?')->execute([$preview, $id]);
}

booking_notify($record);

smile_reply(200, ['ok' => true, 'redirect' => '/see-your-smile/sent/?ref=' . $id]);
