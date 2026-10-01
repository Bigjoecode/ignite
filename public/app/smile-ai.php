<?php
declare(strict_types=1);

// Turns a patient's photo into a preview of a straighter smile.
//
// Until an API key is added in Settings this does nothing at all: the page still
// takes the photo and the team still answers by hand, which is why the key can be
// added whenever the practice is ready without touching the page.
//
// Whatever comes back is an illustration. Every screen that shows one says so.

const SMILE_AI_KEY      = 'smile_ai';
const SMILE_AI_TIMEOUT  = 60;
const SMILE_AI_PROMPT   = 'Edit this portrait so the teeth appear straight, evenly aligned and naturally white, '
    . 'as they would look after orthodontic treatment. Keep the same person, the same face, skin tone, lips, '
    . 'expression, lighting and background. Change nothing except the teeth. The result must look like a real '
    . 'photograph of this person, not an illustration.';

/** What the practice set in Settings. */
function smile_ai_settings(bool $fresh = false): array
{
    static $settings = null;
    if ($fresh) {
        $settings = null;
    }
    if ($settings !== null) {
        return $settings;
    }
    $saved = json_decode((string) db_setting(db(), SMILE_AI_KEY), true) ?: [];
    return $settings = [
        'provider'  => in_array($saved['provider'] ?? '', ['gemini', 'openai'], true) ? $saved['provider'] : '',
        'key'       => (string) ($saved['key'] ?? ''),
        'daily_cap' => max(0, min(1000, (int) ($saved['daily_cap'] ?? 50))),
        'keep_days' => max(1, min(3650, (int) ($saved['keep_days'] ?? SMILE_KEEP_DAYS))),
    ];
}

function smile_ai_save_settings(array $input): void
{
    $now = smile_ai_settings();
    db_set_setting(db(), SMILE_AI_KEY, json_encode([
        'provider'  => in_array($input['provider'] ?? '', ['gemini', 'openai'], true) ? $input['provider'] : '',
        // an empty box leaves the saved key alone, so it never has to be retyped
        'key'       => trim((string) ($input['key'] ?? '')) !== '' ? trim((string) $input['key']) : $now['key'],
        'daily_cap' => (int) ($input['daily_cap'] ?? 50),
        'keep_days' => (int) ($input['keep_days'] ?? SMILE_KEEP_DAYS),
    ], JSON_UNESCAPED_SLASHES));
    smile_ai_settings(true);   // so a save is visible straight away
}

/** True when a preview can actually be generated right now. */
function smile_ai_ready(): bool
{
    $s = smile_ai_settings();
    return $s['provider'] !== '' && $s['key'] !== '';
}

/** How many previews were made today, against the cap. */
function smile_ai_used_today(): int
{
    $used = json_decode((string) db_setting(db(), 'smile_ai_used'), true) ?: [];
    return (int) ($used[date('Y-m-d')] ?? 0);
}

function smile_ai_count_one(): void
{
    $today = date('Y-m-d');
    $used  = json_decode((string) db_setting(db(), 'smile_ai_used'), true) ?: [];
    $used  = array_filter($used, static fn(string $day): bool => $day >= date('Y-m-d', strtotime('-30 days')), ARRAY_FILTER_USE_KEY);
    $used[$today] = ($used[$today] ?? 0) + 1;
    db_set_setting(db(), 'smile_ai_used', json_encode($used));
}

/**
 * Makes the preview and stores it beside the original.
 * Returns the stored path, or null when it is switched off, capped or the service failed.
 */
function smile_ai_preview(string $photoStored, string $id): ?string
{
    $s = smile_ai_settings();
    if (!smile_ai_ready()) {
        return null;
    }
    if ($s['daily_cap'] > 0 && smile_ai_used_today() >= $s['daily_cap']) {
        error_log('smile-ai: daily cap of ' . $s['daily_cap'] . ' reached');
        return null;
    }
    $source = smile_photo_path($photoStored);
    if (!$source) {
        return null;
    }

    smile_ai_count_one();   // counted before the call, so a failure cannot be retried into a big bill
    $jpeg = $s['provider'] === 'gemini'
        ? smile_ai_gemini($source, $s['key'])
        : smile_ai_openai($source, $s['key']);
    if ($jpeg === null) {
        return null;
    }

    $dest = dirname($source) . '/' . $id . '-preview.jpg';
    if (@file_put_contents($dest, $jpeg) === false) {
        return null;
    }
    @chmod($dest, 0600);
    return str_replace(cfg('data_dir') . '/', '', $dest);
}

/** Google Gemini image editing. Returns the new image, or null. */
function smile_ai_gemini(string $source, string $key): ?string
{
    $reply = smile_ai_http(
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent',
        ['x-goog-api-key: ' . $key, 'Content-Type: application/json'],
        json_encode(['contents' => [['parts' => [
            ['text' => SMILE_AI_PROMPT],
            ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => base64_encode((string) file_get_contents($source))]],
        ]]]])
    );
    foreach ($reply['candidates'][0]['content']['parts'] ?? [] as $part) {
        if (!empty($part['inline_data']['data'])) {
            return (string) base64_decode((string) $part['inline_data']['data'], true);
        }
        if (!empty($part['inlineData']['data'])) {
            return (string) base64_decode((string) $part['inlineData']['data'], true);
        }
    }
    error_log('smile-ai gemini: ' . json_encode($reply['error'] ?? $reply));
    return null;
}

/** OpenAI image editing. Returns the new image, or null. */
function smile_ai_openai(string $source, string $key): ?string
{
    $boundary = 'smile' . bin2hex(random_bytes(8));
    $part = static fn(string $name, string $value): string =>
        "--{$boundary}\r\nContent-Disposition: form-data; name=\"{$name}\"\r\n\r\n{$value}\r\n";
    $body = $part('model', 'gpt-image-1')
        . $part('prompt', SMILE_AI_PROMPT)
        . $part('size', '1024x1024')
        . "--{$boundary}\r\nContent-Disposition: form-data; name=\"image\"; filename=\"smile.jpg\"\r\n"
        . "Content-Type: image/jpeg\r\n\r\n" . file_get_contents($source) . "\r\n"
        . "--{$boundary}--\r\n";

    $reply = smile_ai_http(
        'https://api.openai.com/v1/images/edits',
        ['Authorization: Bearer ' . $key, 'Content-Type: multipart/form-data; boundary=' . $boundary],
        $body
    );
    if (!empty($reply['data'][0]['b64_json'])) {
        return (string) base64_decode((string) $reply['data'][0]['b64_json'], true);
    }
    error_log('smile-ai openai: ' . json_encode($reply['error'] ?? $reply));
    return null;
}

/** One call to the image service. Returns the decoded reply, or ['error' => ...]. */
function smile_ai_http(string $url, array $headers, string $body): array
{
    if (isset($GLOBALS['smile_ai_test'])) {          // the test harness answers instead
        return ($GLOBALS['smile_ai_test'])($url, $body);
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => SMILE_AI_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 8,
    ]);
    $raw   = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return ['error' => ['message' => $error]];
    }
    $decoded = json_decode((string) $raw, true);
    return is_array($decoded) ? $decoded : ['error' => ['message' => 'unreadable reply']];
}
