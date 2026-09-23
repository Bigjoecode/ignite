<?php
declare(strict_types=1);

// Settings screen: who is emailed when a consultation request comes in.

require_once APP . '/notify.php';
require_once APP . '/consult.php';

/** GET shows the form; POST saves it and can send a test email. */
function admin_settings(array $user, string $method): void
{
    if ($method === 'POST') {
        consult_save_settings((array) ($_POST['consult'] ?? []));
        $all     = (string) ($_POST['all'] ?? '');
        $offices = [];
        foreach (locations() as $slug => $office) {
            $offices[$slug] = (string) (($_POST['office'] ?? [])[$slug] ?? '');
        }

        booking_save_emails(booking_clean_emails($all), array_map('booking_clean_emails', $offices));

        // anything that was not an email address is dropped, so say which
        $bad = [];
        foreach ([$all] + $offices as $text) {
            foreach (preg_split('/[\r\n,;]+/', $text) as $line) {
                if (($line = trim($line)) !== '' && !filter_var($line, FILTER_VALIDATE_EMAIL)) {
                    $bad[] = '"' . $line . '"';
                }
            }
        }
        $saved   = admin_settings_emails();
        $nobody  = !$saved['all'] && !array_filter($saved['offices']);
        $notes   = [];
        if ($bad) {
            $notes[] = 'These are not email addresses, so they were not saved: ' . implode(', ', array_unique($bad)) . '.';
        }
        if ($nobody) {
            $notes[] = 'Nobody is set to receive bookings, so no emails will be sent. Requests are still saved.';
        }

        if (($_POST['action'] ?? '') === 'test') {
            [$sent, $failed] = admin_settings_send_test((string) ($_POST['test_office'] ?? ''), $user, $saved);
            if ($sent) {
                $notes[] = 'A test request was emailed to ' . implode(', ', $sent) . '.';
            } elseif ($failed) {
                $notes[] = 'The server refused to email ' . implode(', ', $failed) . '. Ask the host to check email sending.';
            }
            admin_flash($sent ? 'success' : 'error', 'Settings saved. ' . implode(' ', $notes));
        } else {
            admin_flash($bad || $nobody ? 'warning' : 'success', 'Booking notification settings saved. ' . implode(' ', $notes));
        }
        redirect('/admin/settings/', 303);
    }

    $saved = admin_settings_emails();
    admin_render('settings', [
        'all'      => implode("\n", $saved['all']),
        'offices'  => array_map(static fn(array $list): string => implode("\n", $list), $saved['offices']),
        'locked'   => booking_clean_emails((string) cfg('lead_email')),   // set in config.local.php, not editable here
        'consult'  => consult_settings(),
        'hasKey'   => gcal_key() !== null,
        'keyPath'  => cfg('data_dir') . '/' . GCAL_KEY_FILE,
        'slotCheck' => admin_consult_check(),
    ], 'Settings', $user);
}

/**
 * Whether the virtual consultation page can offer real times right now, as
 * [ok, what to say]. It actually asks Google, so the practice sees the truth.
 */
function admin_consult_check(): array
{
    $s = consult_settings();
    if (!$s['enabled']) {
        return [false, 'Switched off. The page asks visitors to request a time instead.'];
    }
    if (!array_filter($s['hours'])) {
        return [false, 'Set the hours you are available on at least one day, otherwise there is nothing to offer.'];
    }
    $slots = consult_slots();
    if ($slots === null) {
        return [false, 'Google would not answer. Check the calendar is shared with the service account.'];
    }

    $count = array_sum(array_map('count', $slots));
    $how   = consult_uses_google()
        ? 'Booking into Google Calendar, with a Meet link for each appointment.'
        : ($s['meeting_link'] !== ''
            ? 'Taking bookings without Google: patients get your meeting room link.'
            : 'Taking bookings, but no meeting link is set, so patients are told you will send one.');

    return $count > 0
        ? [true, $how . ' ' . $count . ' free ' . ($count === 1 ? 'time' : 'times') . ' over the next ' . $s['days_ahead'] . ' days.']
        : [true, $how . ' Every time in the next ' . $s['days_ahead'] . ' days is taken, so the page has nothing to offer.'];
}

/** The saved lists, read fresh (booking_emails() caches for the request). */
function admin_settings_emails(): array
{
    $saved   = json_decode((string) db_setting(db(), BOOKING_EMAILS_KEY), true) ?: [];
    $offices = [];
    foreach (locations() as $slug => $office) {
        $offices[$slug] = booking_clean_emails($saved['offices'][$slug] ?? []);
    }
    return ['all' => booking_clean_emails($saved['all'] ?? []), 'offices' => $offices];
}

/** Sends a clearly marked sample request, so the practice can check it arrives. */
function admin_settings_send_test(string $office, array $user, array $saved): array
{
    $office  = isset(locations()[$office]) ? $office : (string) array_key_first(locations());
    $to      = booking_clean_emails(array_merge(
        $saved['all'],
        $saved['offices'][$office] ?? [],
        booking_clean_emails((string) cfg('lead_email'))
    ));
    $opts    = require APP . '/data/booking.php';
    $mail    = booking_email([
        'id'         => 'test-' . date('Ymd-His'),
        'created_at' => gmdate('c'),
        'patient'    => (string) array_key_first($opts['patients']),
        'treatment'  => (string) array_key_first($opts['treatments']),
        'office'     => $office,
        'date'       => 'first',
        'time'       => (string) array_key_first($opts['times']),
        'first_name' => 'Test',
        'last_name'  => 'Request',
        'phone'      => '(000) 000-0000',
        'email'      => $user['email'],
        'notes'      => 'Test sent from the dashboard by ' . $user['name'] . '. No patient is waiting for a call.',
        'source'     => '/admin/settings/',
    ]);

    $sent = $failed = [];
    foreach ($to as $address) {
        $ok = @mail(
            $address,
            '[TEST] ' . $mail['subject'],
            "This is a test of the booking notification emails.\nNobody has requested an appointment.\n\n"
                . str_repeat('-', 46) . "\n\n" . $mail['body'],
            "From: Ignite Orthodontics <no-reply@igniteorthodontics.com>\r\nReply-To: {$user['email']}\r\nContent-Type: text/plain; charset=utf-8"
        );
        $ok ? $sent[] = $address : $failed[] = $address;
    }
    return [$sent, $failed];
}
