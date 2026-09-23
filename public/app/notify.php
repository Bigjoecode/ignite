<?php
declare(strict_types=1);

// Who gets told about a booking request, and the emails themselves.
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
 * Confirms a video consultation to the patient, with the appointment attached so
 * it can be added to their own calendar in one tap. Sent by us, so it arrives even
 * when Google is not connected and no calendar invite goes out.
 */
function booking_confirm_patient(array $r): bool
{
    require_once APP . '/consult.php';          // for the appointment length
    $email = trim((string) ($r['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($r['start_at'])) {
        return false;
    }
    $zone   = new DateTimeZone('America/Detroit');
    $start  = (new DateTimeImmutable($r['start_at']))->setTimezone($zone);
    $office = locations()[$r['office']] ?? null;
    $when   = $start->format('l, F j') . ' at ' . ltrim($start->format('g:ia'), '0') . ' (Eastern time)';
    $link   = (string) ($r['meet_url'] ?? '');

    $lines = [
        'Hi ' . trim((string) ($r['first_name'] ?? '')) . ',',
        '',
        'Your free video consultation with Ignite Orthodontics is booked.',
        '',
        'When: ' . $when,
        'How long: about ' . consult_settings()['slot_minutes'] . ' minutes',
    ];
    $lines[] = $link !== ''
        ? 'Join here: ' . $link
        : 'We will send you the video link before your appointment.';
    $lines = array_merge($lines, [
        '',
        'There is nothing to install — the link opens in your browser, on a phone or a computer.',
        'It helps to be somewhere with decent light so we can see your smile.',
        '',
        'Need to change or cancel it? Call us on ' . cfg('phone') . ' and we will sort it out.',
        '',
        $office ? 'Ignite Orthodontics ' . $office['name'] : 'Ignite Orthodontics',
        $office['address_full'] ?? '',
        'igniteorthodontics.com',
    ]);

    $boundary = 'ig' . bin2hex(random_bytes(8));
    $body = "--{$boundary}\r\nContent-Type: text/plain; charset=utf-8\r\n\r\n"
        . implode("\n", $lines) . "\r\n"
        . "--{$boundary}\r\nContent-Type: text/calendar; charset=utf-8; method=REQUEST; name=\"appointment.ics\"\r\n"
        . "Content-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"appointment.ics\"\r\n\r\n"
        . chunk_split(base64_encode(booking_ics($r))) . "\r\n--{$boundary}--";

    return @mail(
        $email,
        'Your video consultation: ' . $start->format('D, M j') . ' at ' . ltrim($start->format('g:ia'), '0'),
        $body,
        "From: Ignite Orthodontics <no-reply@igniteorthodontics.com>\r\n"
            . 'Reply-To: ' . (booking_emails()['all'][0] ?? 'no-reply@igniteorthodontics.com') . "\r\n"
            . "MIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"{$boundary}\""
    );
}

/** The appointment as a calendar file, for the patient's own calendar app. */
function booking_ics(array $r): string
{
    require_once APP . '/consult.php';
    $utc    = new DateTimeZone('UTC');
    $start  = (new DateTimeImmutable($r['start_at']))->setTimezone($utc);
    $end    = $start->modify('+' . consult_settings()['slot_minutes'] . ' minutes');
    $link   = (string) ($r['meet_url'] ?? '');
    $office = locations()[$r['office']] ?? null;
    $escape = static fn(string $text): string => str_replace(["\\", "\n", ',', ';'], ['\\\\', '\\n', '\\,', '\\;'], $text);

    return implode("\r\n", [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//Ignite Orthodontics//Booking//EN',
        'METHOD:REQUEST',
        'BEGIN:VEVENT',
        'UID:' . ($r['id'] ?? bin2hex(random_bytes(8))) . '@igniteorthodontics.com',
        'DTSTAMP:' . gmdate('Ymd\THis\Z'),
        'DTSTART:' . $start->format('Ymd\THis\Z'),
        'DTEND:' . $end->format('Ymd\THis\Z'),
        'SUMMARY:' . $escape('Video consultation with Ignite Orthodontics' . ($office ? ' ' . $office['name'] : '')),
        'DESCRIPTION:' . $escape($link !== '' ? 'Join here: ' . $link : 'We will send you the video link before your appointment.'),
        'LOCATION:' . $escape($link !== '' ? $link : 'Online'),
        'STATUS:CONFIRMED',
        'END:VEVENT',
        'END:VCALENDAR',
    ]) . "\r\n";
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
