<?php
declare(strict_types=1);

// Talks to Google Calendar for the virtual consultation page: which times are
// free, and booking one. Signed in as a service account that acts as a Workspace
// user (docs/google-calendar-setup.md), which is what lets it create Meet links.
//
// No library: a service account sign-in is a signed JWT swapped for an access
// token, and the rest is three REST calls. Every function returns null on a
// problem and logs it, so the page can fall back to asking for preferred times.

const GCAL_SCOPE     = 'https://www.googleapis.com/auth/calendar';
const GCAL_KEY_FILE  = 'google-calendar.json';    // in the data dir, never in git or the web root
const GCAL_TIMEOUT   = 8;

/** The service account key, or null when it has not been installed yet. */
function gcal_key(): ?array
{
    static $key = false;
    if ($key !== false) {
        return $key;
    }
    $path = cfg('data_dir') . '/' . GCAL_KEY_FILE;
    if (!is_file($path)) {
        return $key = null;
    }
    $json = json_decode((string) file_get_contents($path), true);
    if (!is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
        error_log('gcal: ' . GCAL_KEY_FILE . ' is not a service account key');
        return $key = null;
    }
    return $key = $json;
}

/** An access token for the Workspace user we book as, cached until it expires. */
function gcal_token(string $bookAs): ?string
{
    $key = gcal_key();
    if (!$key || $bookAs === '') {
        return null;
    }
    $cacheFile = cfg('data_dir') . '/google-token.json';
    $cached    = is_file($cacheFile) ? json_decode((string) file_get_contents($cacheFile), true) : null;
    if (is_array($cached) && ($cached['user'] ?? '') === $bookAs && ($cached['expires'] ?? 0) > time() + 60) {
        return (string) $cached['token'];
    }

    $now    = time();
    $claims = [
        'iss'   => $key['client_email'],
        'sub'   => $bookAs,                 // act as this person, so Meet links can be made
        'scope' => GCAL_SCOPE,
        'aud'   => 'https://oauth2.googleapis.com/token',
        'iat'   => $now,
        'exp'   => $now + 3600,
    ];
    $base = gcal_base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']))
        . '.' . gcal_base64url(json_encode($claims));
    $signature = '';
    if (!openssl_sign($base, $signature, $key['private_key'], OPENSSL_ALGO_SHA256)) {
        error_log('gcal: could not sign the token request');
        return null;
    }

    $reply = gcal_http('POST', 'https://oauth2.googleapis.com/token', [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $base . '.' . gcal_base64url($signature),
    ], null, true);
    if (!isset($reply['access_token'])) {
        error_log('gcal: sign-in failed: ' . json_encode($reply));
        return null;
    }

    @file_put_contents($cacheFile, json_encode([
        'user'    => $bookAs,
        'token'   => $reply['access_token'],
        'expires' => $now + (int) ($reply['expires_in'] ?? 3600),
    ]));
    @chmod($cacheFile, 0600);
    return (string) $reply['access_token'];
}

function gcal_base64url(string $raw): string
{
    return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
}

/**
 * The times that are already taken on the calendar, as [[start, end], ...] in UTC.
 * Returns null when Google could not be reached, which is different from "nothing is booked".
 */
function gcal_busy(string $calendarId, string $bookAs, DateTimeImmutable $from, DateTimeImmutable $to): ?array
{
    $token = gcal_token($bookAs);
    if (!$token || $calendarId === '') {
        return null;
    }
    $reply = gcal_http('POST', 'https://www.googleapis.com/calendar/v3/freeBusy', [
        'timeMin' => $from->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'),
        'timeMax' => $to->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'),
        'items'   => [['id' => $calendarId]],
    ], $token);

    $busy = $reply['calendars'][$calendarId]['busy'] ?? null;
    if (!is_array($busy)) {
        error_log('gcal: free/busy failed: ' . json_encode($reply));
        return null;
    }
    $periods = [];
    foreach ($busy as $period) {
        try {
            $periods[] = [new DateTimeImmutable($period['start']), new DateTimeImmutable($period['end'])];
        } catch (Throwable $e) {
            // a period we cannot read is safer skipped than treated as free
        }
    }
    return $periods;
}

/**
 * Books the slot. Returns ['id' => event id, 'meet' => join link, 'html' => calendar link],
 * or null if it could not be created — including when Google says the slot is gone.
 */
function gcal_create_event(string $calendarId, string $bookAs, array $event): ?array
{
    $token = gcal_token($bookAs);
    if (!$token || $calendarId === '') {
        return null;
    }
    $body = [
        'summary'     => $event['summary'],
        'description' => $event['description'],
        'start'       => ['dateTime' => $event['start'], 'timeZone' => $event['timezone']],
        'end'         => ['dateTime' => $event['end'],   'timeZone' => $event['timezone']],
        'attendees'   => array_values(array_filter([
            $event['patient_email'] !== '' ? ['email' => $event['patient_email'], 'displayName' => $event['patient_name']] : null,
        ])),
        'reminders'   => ['useDefault' => true],
        'conferenceData' => [
            'createRequest' => [
                'requestId'             => bin2hex(random_bytes(8)),
                'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
            ],
        ],
    ];
    $url = 'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($calendarId)
        . '/events?conferenceDataVersion=1&sendUpdates=all';

    $reply = gcal_http('POST', $url, $body, $token);
    if (empty($reply['id'])) {
        error_log('gcal: could not create the event: ' . json_encode($reply));
        return null;
    }
    return [
        'id'   => (string) $reply['id'],
        'meet' => (string) ($reply['hangoutLink'] ?? ''),
        'html' => (string) ($reply['htmlLink'] ?? ''),
    ];
}

/** One HTTP call to Google. Returns the decoded reply, or ['error' => ...]. */
function gcal_http(string $method, string $url, array $body, ?string $token, bool $form = false): array
{
    if (isset($GLOBALS['gcal_http_test'])) {       // the test harness answers instead of Google
        return ($GLOBALS['gcal_http_test'])($method, $url, $body, $token);
    }
    $headers = ['Accept: application/json'];
    if ($token !== null) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    $headers[] = $form ? 'Content-Type: application/x-www-form-urlencoded' : 'Content-Type: application/json';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_POSTFIELDS     => $form ? http_build_query($body) : json_encode($body),
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => GCAL_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 4,
    ]);
    $raw   = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return ['error' => ['message' => $error]];
    }
    $decoded = json_decode((string) $raw, true);
    return is_array($decoded) ? $decoded : ['error' => ['message' => 'unreadable reply from Google']];
}
