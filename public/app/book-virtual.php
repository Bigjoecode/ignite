<?php
// POST /book-virtual — books a video consultation in the practice's Google Calendar.
// The slot is checked again here, so two people picking the same time cannot both get it.
// Saved like any other booking (kind "virtual"), then Google emails the patient the invite.
declare(strict_types=1);

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    redirect('/virtual-consultation/', 303);
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once APP . '/consult.php';
require_once APP . '/bookings.php';
require_once APP . '/notify.php';

function vc_reply(int $code, array $body): void
{
    http_response_code($code);
    echo json_encode($body);
    exit;
}

$again = 'Please try again, or call us and we will arrange it.';

// only accept submissions sent from this site's own pages
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) {
    vc_reply(403, ['ok' => false, 'error' => 'This request could not be accepted. ' . $again]);
}

$field = static fn(string $key, int $max): string => mb_substr(trim((string) ($_POST[$key] ?? '')), 0, $max);

// honeypot: invisible to people, filled in by bots — pretend success
if ($field('website', 200) !== '') {
    vc_reply(200, ['ok' => true, 'redirect' => '/thank-you/']);
}

$req = [
    'slot'       => $field('slot', 40),
    'office'     => $field('office', 60),
    'patient'    => $field('patient', 20),
    'treatment'  => $field('treatment', 20),
    'first_name' => $field('first_name', 60),
    'last_name'  => $field('last_name', 60),
    'phone'      => $field('phone', 30),
    'email'      => $field('email', 160),
    'notes'      => $field('notes', 1000),
];
$digits = strlen(preg_replace('/\D/', '', $req['phone']));

$errors = [];
if ($req['first_name'] === '')                          $errors[] = ['first_name', 'first name'];
if ($req['last_name'] === '')                           $errors[] = ['last_name', 'last name'];
if ($digits < 10 || $digits > 15)                       $errors[] = ['phone', 'phone number'];
if (!filter_var($req['email'], FILTER_VALIDATE_EMAIL))  $errors[] = ['email', 'email'];
if (!isset(locations()[$req['office']]))                $errors[] = ['office', 'office'];
if (empty($_POST['consent']))                           $errors[] = ['consent', 'consent checkbox'];
if ($errors) {
    vc_reply(422, [
        'ok'    => false,
        'field' => $errors[0][0],
        'error' => 'Please check your ' . implode(', ', array_unique(array_column($errors, 1))) . '.',
    ]);
}

$times = consult_slot_times($req['slot']);
if (!$times) {
    vc_reply(409, ['ok' => false, 'field' => 'slot', 'error' => 'Sorry, that time has just been taken. Please pick another one.']);
}
[$start, $end] = $times;

$name   = trim($req['first_name'] . ' ' . $req['last_name']);
$office = locations()[$req['office']];
$s      = consult_settings();
$event  = gcal_create_event($s['calendar_id'], $s['book_as'], [
    'summary'     => 'Virtual consultation: ' . $name,
    'description' => "Video consultation booked on igniteorthodontics.com\n\n"
        . "Patient: {$name}\nPhone: {$req['phone']}\nEmail: {$req['email']}\n"
        . 'For: ' . (booking_choice('patient', $req['patient']) ?: 'not said') . "\n"
        . 'Interested in: ' . (booking_choice('treatment', $req['treatment']) ?: 'not said') . "\n"
        . "Nearest office: {$office['name']}\n\n"
        . 'Notes: ' . ($req['notes'] !== '' ? $req['notes'] : '-'),
    'start'         => $start->format('c'),
    'end'           => $end->format('c'),
    'timezone'      => CONSULT_ZONE,
    'patient_email' => $req['email'],
    'patient_name'  => $name,
]);
if (!$event) {
    vc_reply(502, ['ok' => false, 'error' => 'We could not confirm that time just now. ' . $again]);
}

$record = [
    'id'         => bin2hex(random_bytes(8)),
    'created_at' => gmdate('c'),
    'status'     => 'booked',
    'kind'       => 'virtual',
    'office'     => $req['office'],
    'patient'    => $req['patient'],
    'treatment'  => $req['treatment'],
    'date'       => $start->setTimezone(new DateTimeZone(CONSULT_ZONE))->format('Y-m-d'),
    'time'       => '',
    'first_name' => $req['first_name'],
    'last_name'  => $req['last_name'],
    'phone'      => $req['phone'],
    'email'      => $req['email'],
    'notes'      => $req['notes'],
    'source'     => mb_substr((string) ($_POST['source'] ?? '/virtual-consultation/'), 0, 200),
    'start_at'   => $start->format('c'),
    'meet_url'   => $event['meet'],
    'event_id'   => $event['id'],
];

// the appointment is already in the calendar; a storage or mail problem must not lose it
$file = cfg('data_dir') . '/bookings.jsonl';
@file_put_contents($file, json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
@chmod($file, 0600);
booking_store($record);
booking_notify($record);

vc_reply(200, ['ok' => true, 'redirect' => '/virtual-consultation/booked/?ref=' . $record['id']]);
