<?php
declare(strict_types=1);

// Who gets told about a booking request, and the email itself.
//
// The addresses are set in the dashboard (Settings → Booking notifications) and
// kept in the settings table as JSON: one list that gets every request, plus an
// extra list per office. `lead_email` in app/config.local.php still works and is
// added to the "everything" list, so an older setup keeps working.

const BOOKING_EMAILS_KEY = 'booking_emails';
const BOOKING_EMAILS_MAX = 10;              // per list, so a typo cannot fan out

/** ['all' => [email, ...], 'offices' => [office slug => [email, ...]]] */
function booking_emails(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $saved = json_decode((string) db_setting(db(), BOOKING_EMAILS_KEY), true);
    $all   = booking_clean_emails($saved['all'] ?? []);
    if ($lead = trim((string) cfg('lead_email'))) {
        $all = booking_clean_emails(array_merge($all, [$lead]));
    }
    $offices = [];
    foreach (locations() as $slug => $office) {
        $list = booking_clean_emails($saved['offices'][$slug] ?? []);
        if ($list) {
            $offices[$slug] = $list;
        }
    }
    return $cache = ['all' => $all, 'offices' => $offices];
}

/** Keeps valid addresses only, without duplicates or header breaks. */
function booking_clean_emails(array|string $input): array
{
    $items = is_string($input) ? preg_split('/[\r\n,;]+/', $input) : $input;
    $clean = [];
    foreach ($items as $item) {
        $item = trim(str_replace(["\r", "\n"], '', (string) $item));
        if ($item !== '' && filter_var($item, FILTER_VALIDATE_EMAIL) && !in_array($item, $clean, true)) {
            $clean[] = $item;
        }
        if (count($clean) >= BOOKING_EMAILS_MAX) {
            break;
        }
    }
    return $clean;
}

/** Everyone to email about a request for this office. */
function booking_recipients(string $office): array
{
    $emails = booking_emails();
    return booking_clean_emails(array_merge($emails['all'], $emails['offices'][$office] ?? []));
}

function booking_save_emails(array $all, array $offices): void
{
    $clean = ['all' => booking_clean_emails($all), 'offices' => []];
    foreach ($offices as $slug => $list) {
        if (isset(locations()[$slug]) && ($list = booking_clean_emails($list))) {
            $clean['offices'][$slug] = $list;
        }
    }
    db_set_setting(db(), BOOKING_EMAILS_KEY, json_encode($clean, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

/** Subject and body of the email for one saved request. */
function booking_email(array $r): array
{
    $opts   = require APP . '/data/booking.php';
    $office = locations()[$r['office']] ?? null;
    $name   = trim(preg_replace('/[\r\n]+/', ' ', ($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')));
    $label  = static fn(?array $pair): string => $pair[0] ?? '';
    $day    = ($r['date'] ?? '') === 'first' || !($d = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($r['date'] ?? '')))
        ? 'First available'
        : $d->format('l, F j');
    $sent   = isset($r['created_at']) ? (new DateTimeImmutable($r['created_at']))->setTimezone(new DateTimeZone('America/Detroit'))->format('D, M j Y, g:ia') : '';

    $virtual = ($r['kind'] ?? '') === 'virtual';
    if ($virtual && !empty($r['start_at'])) {
        $when = (new DateTimeImmutable($r['start_at']))->setTimezone(new DateTimeZone('America/Detroit'));
        $day  = $when->format('l, F j') . ' at ' . ltrim($when->format('g:ia'), '0');
    }

    $lines = [
        $virtual ? 'New video consultation booked' : 'New consultation request',
        '',
        'Name: ' . $name,
        'Phone: ' . ($r['phone'] ?? ''),
        'Email: ' . ($r['email'] ?? ''),
        '',
        'For: ' . $label($opts['patients'][$r['patient'] ?? ''] ?? null),
        'Treatment: ' . $label($opts['treatments'][$r['treatment'] ?? ''] ?? null),
        ($virtual ? 'Nearest office: ' : 'Office: ') . ($office ? $office['name'] . ' (' . $office['address_full'] . ')' : ($r['office'] ?? '')),
        ($virtual ? 'Appointment: ' : 'Preferred: ') . $day . ($virtual ? ' (Eastern)' : ', ' . ($opts['times'][$r['time'] ?? ''] ?? '')),
    ];
    if ($virtual && !empty($r['meet_url'])) {
        $lines[] = 'Join: ' . $r['meet_url'];
        $lines[] = 'The patient has the invite and the same link by email.';
    }
    $lines = array_merge($lines, [
        '',
        'Notes:',
        ($r['notes'] ?? '') !== '' ? $r['notes'] : '-',
        '',
        'Came from: ' . (($r['source'] ?? '') !== '' ? 'igniteorthodontics.com' . $r['source'] : 'the booking page'),
        'Sent: ' . $sent,
        'Request ID: ' . ($r['id'] ?? ''),
    ]);

    return [
        'subject' => ($virtual ? 'Video consultation booked: ' : 'Consultation request: ')
            . ($name !== '' ? $name : 'new patient') . ' (' . ($office['name'] ?? $r['office'] ?? '') . ')',
        'body'    => implode("\n", $lines) . "\n",
    ];
}

/**
 * Emails one saved request to everyone set up for its office.
 * Each address is mailed on its own, so recipients never see each other.
 * Returns [sent addresses, addresses the mail server refused].
 */
function booking_notify(array $record): array
{
    $mail = booking_email($record);
    $from = 'From: Ignite Orthodontics <no-reply@igniteorthodontics.com>';
    $back = preg_replace('/[\r\n]+/', '', (string) ($record['email'] ?? ''));
    if ($back !== '' && filter_var($back, FILTER_VALIDATE_EMAIL)) {
        $from .= "\r\nReply-To: " . $back;
    }
    $sent = $failed = [];
    foreach (booking_recipients((string) ($record['office'] ?? '')) as $to) {
        if (@mail($to, $mail['subject'], $mail['body'], $from . "\r\nContent-Type: text/plain; charset=utf-8")) {
            $sent[] = $to;
        } else {
            $failed[] = $to;
        }
    }
    return [$sent, $failed];
}
