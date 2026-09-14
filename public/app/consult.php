<?php
// POST /consult — saves a consultation request from any site form.
// Leads are appended to leads.jsonl in the data dir, outside the web root,
// until the admin CMS stores them in MySQL. Optionally emailed (lead_email).
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function reply(int $code, array $body): void
{
    http_response_code($code);
    echo json_encode($body);
    exit;
}

$fail = 'We could not send your request. Please call us at ' . cfg('phone') . '.';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    reply(405, ['ok' => false, 'error' => 'Method not allowed.']);
}

$field = static fn(string $key, int $max): string => mb_substr(trim((string) ($_POST[$key] ?? '')), 0, $max);

// honeypot: hidden from people, filled in by bots — pretend success
if ($field('website', 200) !== '') {
    reply(200, ['ok' => true]);
}

$lead = [
    'name'     => $field('full_name', 120),
    'phone'    => $field('phone', 40),
    'email'    => $field('email', 160),
    'zip'      => $field('zip', 10),
    'message'  => $field('message', 3000),
    'location' => $field('location', 60),
    'source'   => $field('source', 200),
    'consent'  => !empty($_POST['consent']),
];

$bad = [];
if ($lead['name'] === '') $bad[] = 'name';
if (strlen(preg_replace('/\D/', '', $lead['phone'])) < 10) $bad[] = 'phone number';
if (!filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) $bad[] = 'email';
if (!preg_match('/^\d{5}(-\d{4})?$/', $lead['zip'])) $bad[] = 'zip code';
if (!$lead['consent']) $bad[] = 'consent checkbox';
if ($bad) {
    reply(422, ['ok' => false, 'error' => 'Please check your ' . implode(', ', $bad) . '.']);
}
if ($lead['location'] !== '' && !isset(locations()[$lead['location']])) {
    $lead['location'] = '';
}

$dir = cfg('data_dir');
if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
    reply(500, ['ok' => false, 'error' => $fail]);
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
        reply(429, ['ok' => false, 'error' => 'Too many requests. Please call us at ' . cfg('phone') . '.']);
    }
}

$lead = ['id' => bin2hex(random_bytes(8)), 'created_at' => gmdate('c')] + $lead + [
    'ip'         => $ip,
    'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 300),
];

$file = $dir . '/leads.jsonl';
$line = json_encode($lead, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
if (file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
    reply(500, ['ok' => false, 'error' => $fail]);
}
@chmod($file, 0600);

if ($to = cfg('lead_email')) {
    $office  = $lead['location'] !== '' ? locations()[$lead['location']]['name'] : 'Not specified';
    $subject = 'New consultation request: ' . preg_replace('/[\r\n]+/', ' ', $lead['name']);
    $body    = "New consultation request\n\n"
        . "Name: {$lead['name']}\nPhone: {$lead['phone']}\nEmail: {$lead['email']}\nZip: {$lead['zip']}\n"
        . "Office: {$office}\nPage: {$lead['source']}\n\nMessage:\n{$lead['message']}\n";
    @mail($to, $subject, $body, "From: Ignite Orthodontics <no-reply@igniteorthodontics.com>\r\nReply-To: {$lead['email']}");
}

reply(200, ['ok' => true]);
