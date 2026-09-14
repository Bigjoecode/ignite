<?php
// POST /book — consultation request from the booking popup (assets/js/booking.js).
// Requests are appended to bookings.jsonl in the data dir (outside the web root)
// and emailed to lead_email when it is configured. Responds with JSON.
declare(strict_types=1);

// a visit to /book in the browser just opens the popup on the home page
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    redirect('/?book=1', 303);
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function reply(int $code, array $body): void
{
    http_response_code($code);
    echo json_encode($body);
    exit;
}

$callUs = 'Please try again later.';

// only accept submissions sent from this site's own pages
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) {
    reply(403, ['ok' => false, 'error' => 'This request could not be accepted. ' . $callUs]);
}

$field = static fn(string $key, int $max): string => mb_substr(trim((string) ($_POST[$key] ?? '')), 0, $max);

// honeypot: invisible to people, filled in by bots — pretend success
if ($field('website', 200) !== '') {
    reply(200, ['ok' => true, 'redirect' => '/thank-you/']);
}

$opts = require APP . '/data/booking.php';
$req  = [
    'patient'    => $field('patient', 20),
    'treatment'  => $field('treatment', 20),
    'office'     => $field('office', 60),
    'date'       => $field('date', 10),
    'time'       => $field('time', 20),
    'first_name' => $field('first_name', 60),
    'last_name'  => $field('last_name', 60),
    'phone'      => $field('phone', 30),
    'email'      => $field('email', 160),
    'notes'      => $field('notes', 1000),
    'consent'    => !empty($_POST['consent']),
];

$today  = new DateTimeImmutable('today');
$dateOk = $req['date'] === 'first';
if (!$dateOk && preg_match('/^\d{4}-\d{2}-\d{2}$/', $req['date'])) {
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $req['date']);
    $dateOk = $d && $d->format('Y-m-d') === $req['date']
        && $d >= $today && $d <= $today->modify('+' . ($opts['days_ahead'] + 1) . ' days');
}
$digits = strlen(preg_replace('/\D/', '', $req['phone']));

// [popup step, what to fix] — the popup jumps back to the first problem
$errors = [];
if (!isset($opts['patients'][$req['patient']]))     $errors[] = [1, 'who the appointment is for'];
if (!isset($opts['treatments'][$req['treatment']])) $errors[] = [2, 'treatment'];
if (!isset(locations()[$req['office']]))            $errors[] = [3, 'office'];
if (!$dateOk)                                        $errors[] = [4, 'preferred day'];
if (!isset($opts['times'][$req['time']]))           $errors[] = [4, 'preferred time'];
if ($req['first_name'] === '')                       $errors[] = [5, 'first name'];
if ($req['last_name'] === '')                        $errors[] = [5, 'last name'];
if ($digits < 10 || $digits > 15)                    $errors[] = [5, 'phone number'];
if (!filter_var($req['email'], FILTER_VALIDATE_EMAIL)) $errors[] = [5, 'email'];
if (!$req['consent'])                                $errors[] = [5, 'consent checkbox'];
if ($errors) {
    reply(422, [
        'ok'    => false,
        'step'  => $errors[0][0],
        'error' => 'Please check your ' . implode(', ', array_unique(array_column($errors, 1))) . '.',
    ]);
}

$dir = cfg('data_dir');
if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
    reply(500, ['ok' => false, 'error' => 'We could not send your request. ' . $callUs]);
}

// at most 5 requests per IP per 10 minutes
$ip  = $_SERVER['REMOTE_ADDR'] ?? '';
$now = time();
$rl  = fopen($dir . '/ratelimit.json', 'c+');
if ($rl && flock($rl, LOCK_EX)) {
    $hits = json_decode(stream_get_contents($rl) ?: '{}', true) ?: [];
    foreach ($hits as $k => $times) {
        $hits[$k] = array_values(array_filter($times, static fn($t) => $t > $now - 600));
        if (!$hits[$k]) unset($hits[$k]);
    }
    $key = hash('sha256', $ip);
    $limited = count($hits[$key] ?? []) >= 5;
    if (!$limited) $hits[$key][] = $now;
    ftruncate($rl, 0);
    rewind($rl);
    fwrite($rl, json_encode($hits));
    flock($rl, LOCK_UN);
    fclose($rl);
    if ($limited) {
        reply(429, ['ok' => false, 'error' => 'Too many requests. ' . $callUs]);
    }
}

$office    = locations()[$req['office']];
$dateLabel = $req['date'] === 'first' ? 'First available' : DateTimeImmutable::createFromFormat('!Y-m-d', $req['date'])->format('l, F j');
$record    = ['id' => bin2hex(random_bytes(8)), 'created_at' => gmdate('c'), 'status' => 'new'] + $req + [
    'source'     => $field('source', 200),
    'ip'         => $ip,
    'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 300),
];

$file = $dir . '/bookings.jsonl';
$line = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
if (file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
    reply(500, ['ok' => false, 'error' => 'We could not send your request. ' . $callUs]);
}
@chmod($file, 0600);

if ($to = cfg('lead_email')) {
    $name    = preg_replace('/[\r\n]+/', ' ', $req['first_name'] . ' ' . $req['last_name']);
    $subject = "Consultation request: {$name} ({$office['name']})";
    $body    = "New consultation request\n\n"
        . "Name: {$name}\nPhone: {$req['phone']}\nEmail: {$req['email']}\n\n"
        . "For: {$opts['patients'][$req['patient']][0]}\n"
        . "Treatment: {$opts['treatments'][$req['treatment']][0]}\n"
        . "Office: {$office['name']} ({$office['address_full']})\n"
        . "Preferred: {$dateLabel}, {$opts['times'][$req['time']]}\n\n"
        . "Notes:\n" . ($req['notes'] !== '' ? $req['notes'] : '-') . "\n\n"
        . "Request ID: {$record['id']}\n";
    @mail($to, $subject, $body, "From: Ignite Orthodontics <no-reply@igniteorthodontics.com>\r\nReply-To: {$req['email']}");
}

reply(200, ['ok' => true, 'redirect' => '/thank-you/']);
