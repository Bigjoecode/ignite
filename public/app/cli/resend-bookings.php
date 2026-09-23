<?php
/**
 * Emails consultation requests that are already saved in bookings.jsonl —
 * for requests that came in before the notification addresses were set up.
 *
 *   php app/cli/resend-bookings.php [--since=YYYY-MM-DD] [--id=abc123] [--to=name@example.com] [--dry-run]
 *
 * Without --to, each request goes to the addresses set in the dashboard for its
 * office (Settings → Booking notifications). Every email is marked as a resend so
 * nobody calls a patient twice by mistake.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap.php';
require_once APP . '/notify.php';

$arg     = static function (string $name) use ($argv): ?string {
    foreach ($argv as $a) {
        if (str_starts_with($a, "--{$name}=")) {
            return substr($a, strlen($name) + 3);
        }
    }
    return null;
};
$since   = $arg('since');
$onlyId  = $arg('id');
$to      = $arg('to');
$dryRun  = in_array('--dry-run', $argv, true);

$file = cfg('data_dir') . '/bookings.jsonl';
if (!is_file($file)) {
    fwrite(STDERR, "no bookings file at {$file}\n");
    exit(1);
}
if ($to !== null && !booking_clean_emails($to)) {
    fwrite(STDERR, "--to is not an email address\n");
    exit(1);
}

echo $dryRun ? "DRY RUN — nothing will be emailed\n" : '';
$count = 0;
foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $r = json_decode($line, true);
    if (!is_array($r)) {
        continue;
    }
    if ($since !== null && substr((string) ($r['created_at'] ?? ''), 0, 10) < $since) {
        continue;
    }
    if ($onlyId !== null && ($r['id'] ?? '') !== $onlyId) {
        continue;
    }

    $mail       = booking_email($r);
    $recipients = $to !== null ? booking_clean_emails($to) : booking_recipients((string) ($r['office'] ?? ''));
    $name       = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
    if (!$recipients) {
        echo "SKIP  {$r['id']}  {$name} — nobody is set up for this office\n";
        continue;
    }

    $sent = [];
    foreach ($recipients as $address) {
        if ($dryRun) {
            $sent[] = $address;
            continue;
        }
        $ok = @mail(
            $address,
            '[Earlier request] ' . $mail['subject'],
            "This request came in before booking notifications were switched on, so it is being sent now.\n"
                . "It may already have been dealt with.\n\n" . str_repeat('-', 46) . "\n\n" . $mail['body'],
            "From: Ignite Orthodontics <no-reply@igniteorthodontics.com>\r\nReply-To: " . (string) ($r['email'] ?? '') . "\r\nContent-Type: text/plain; charset=utf-8"
        );
        if ($ok) {
            $sent[] = $address;
        }
    }
    echo ($sent ? 'SENT  ' : 'FAIL  ') . ($r['id'] ?? '?') . "  {$name}  ({$r['created_at']})  -> " . implode(', ', $sent ?: $recipients) . "\n";
    $count += $sent ? 1 : 0;
}
echo ($dryRun ? "would send {$count}" : "sent {$count}") . " request(s)\n";
