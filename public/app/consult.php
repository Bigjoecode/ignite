<?php
declare(strict_types=1);

// The virtual consultation: the rules the practice sets (how long a call is,
// which hours, how far ahead) and the free times that come out of them once
// Google Calendar has been asked what is already booked.

require_once APP . '/google-calendar.php';

const CONSULT_KEY  = 'consult';
const CONSULT_ZONE = 'America/Detroit';
const CONSULT_DAYS = ['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday',
    'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'];

/** What the practice set in Settings, with sensible defaults. */
function consult_settings(): array
{
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }
    $saved = json_decode((string) db_setting(db(), CONSULT_KEY), true) ?: [];
    return $settings = [
        'enabled'      => !empty($saved['enabled']),
        'calendar_id'  => trim((string) ($saved['calendar_id'] ?? '')),
        'book_as'      => trim((string) ($saved['book_as'] ?? '')),
        'slot_minutes' => max(5, min(120, (int) ($saved['slot_minutes'] ?? 15))),
        'days_ahead'   => max(1, min(60, (int) ($saved['days_ahead'] ?? 14))),
        'lead_hours'   => max(0, min(72, (int) ($saved['lead_hours'] ?? 2))),
        // used for the video link until Google Calendar is connected: the practice's own
        // meeting room (a Google Meet or Zoom link that is always the same)
        'meeting_link' => trim((string) ($saved['meeting_link'] ?? '')),
        'hours'        => array_map(
            static fn(string $day): string => trim((string) ($saved['hours'][$day] ?? '')),
            array_combine(array_keys(CONSULT_DAYS), array_keys(CONSULT_DAYS))
        ),
    ];
}

function consult_save_settings(array $input): void
{
    $hours = [];
    foreach (array_keys(CONSULT_DAYS) as $day) {
        $hours[$day] = consult_clean_hours((string) ($input['hours'][$day] ?? ''));
    }
    db_set_setting(db(), CONSULT_KEY, json_encode([
        'enabled'      => !empty($input['enabled']),
        'calendar_id'  => mb_substr(trim((string) ($input['calendar_id'] ?? '')), 0, 200),
        'book_as'      => mb_substr(trim((string) ($input['book_as'] ?? '')), 0, 160),
        'slot_minutes' => (int) ($input['slot_minutes'] ?? 15),
        'days_ahead'   => (int) ($input['days_ahead'] ?? 14),
        'lead_hours'   => (int) ($input['lead_hours'] ?? 2),
        'meeting_link' => filter_var(trim((string) ($input['meeting_link'] ?? '')), FILTER_VALIDATE_URL) ?: '',
        'hours'        => $hours,
    ], JSON_UNESCAPED_SLASHES));
}

/** "9:00-17:00, 18:00 - 19:30" → "09:00-17:00, 18:00-19:30". Anything unreadable is dropped. */
function consult_clean_hours(string $text): string
{
    $ranges = [];
    foreach (preg_split('/[,;]+/', $text) as $range) {
        if (preg_match('/^\s*(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})\s*$/', $range, $m)) {
            [, $fromH, $fromM, $toH, $toM] = $m;
            $from = sprintf('%02d:%02d', min(23, (int) $fromH), min(59, (int) $fromM));
            $to   = sprintf('%02d:%02d', min(23, (int) $toH), min(59, (int) $toM));
            if ($from < $to) {
                $ranges[] = $from . '-' . $to;
            }
        }
    }
    return implode(', ', $ranges);
}

/** True when the page can offer real times rather than asking for preferences. */
function consult_live(): bool
{
    $s = consult_settings();
    return $s['enabled'] && array_filter($s['hours']) !== [];
}

/** True when appointments are written to the practice's Google Calendar with a Meet link. */
function consult_uses_google(): bool
{
    $s = consult_settings();
    return $s['calendar_id'] !== '' && $s['book_as'] !== '' && gcal_key() !== null;
}

/**
 * The bookable times, as ['2026-09-24' => [['start' => ISO8601, 'label' => '9:00 am'], ...], ...].
 * Empty when nothing is free; null when the calendar could not be reached, so the page
 * can say so instead of pretending the diary is full.
 */
function consult_slots(): ?array
{
    $s = consult_settings();
    if (!consult_live()) {
        return null;
    }
    $zone  = new DateTimeZone(CONSULT_ZONE);
    $now   = new DateTimeImmutable('now', $zone);
    $first = $now->modify('+' . $s['lead_hours'] . ' hours');
    $last  = $now->setTime(23, 59)->modify('+' . $s['days_ahead'] . ' days');

    // times we have already given away, and — once Google is connected — whatever
    // else is in the practice's calendar
    $busy = consult_booked($now, $last);
    if (consult_uses_google()) {
        $calendar = gcal_busy($s['calendar_id'], $s['book_as'], $now, $last);
        if ($calendar === null) {
            return null;    // the calendar decides, so do not guess while it is unreachable
        }
        $busy = array_merge($busy, $calendar);
    }

    $days = [];
    for ($day = $now->setTime(0, 0); $day <= $last; $day = $day->modify('+1 day')) {
        $key    = strtolower($day->format('D'));
        $ranges = array_filter(array_map('trim', explode(',', $s['hours'][$key] ?? '')));
        $slots  = [];
        foreach ($ranges as $range) {
            [$from, $to] = array_map('trim', explode('-', $range));
            $start = $day->setTime((int) substr($from, 0, 2), (int) substr($from, 3, 2));
            $stop  = $day->setTime((int) substr($to, 0, 2), (int) substr($to, 3, 2));
            for (; $start < $stop; $start = $start->modify('+' . $s['slot_minutes'] . ' minutes')) {
                $end = $start->modify('+' . $s['slot_minutes'] . ' minutes');
                if ($end > $stop || $start < $first || consult_overlaps($start, $end, $busy)) {
                    continue;
                }
                $slots[] = ['start' => $start->format('c'), 'label' => ltrim($start->format('g:i a'), '0')];
            }
        }
        // every day in the window is listed, so the page can show "no times" days too
        $days[$day->format('Y-m-d')] = $slots;
    }
    return $days;
}

/** Video visits already booked in the window, as [[start, end], ...]. */
function consult_booked(DateTimeImmutable $from, DateTimeImmutable $to): array
{
    $minutes = consult_settings()['slot_minutes'];
    // a day either side, because the stored times carry an offset that shifts with
    // daylight saving; the overlap check below is what decides exactly
    $stmt = db()->prepare(
        "SELECT start_at FROM bookings
          WHERE kind = 'virtual' AND trashed_at IS NULL AND start_at IS NOT NULL
            AND start_at >= ? AND start_at <= ?"
    );
    $stmt->execute([$from->modify('-1 day')->format('c'), $to->modify('+1 day')->format('c')]);

    $busy = [];
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $start) {
        try {
            $taken  = new DateTimeImmutable((string) $start);
            $busy[] = [$taken, $taken->modify('+' . $minutes . ' minutes')];
        } catch (Throwable $e) {
            // a time we cannot read cannot block anything
        }
    }
    return $busy;
}

function consult_overlaps(DateTimeImmutable $start, DateTimeImmutable $end, array $busy): bool
{
    foreach ($busy as [$busyStart, $busyEnd]) {
        if ($start < $busyEnd && $end > $busyStart) {
            return true;
        }
    }
    return false;
}

/** Checks a chosen time is one the page really offers, and hands back its start and end. */
function consult_slot_times(string $isoStart): ?array
{
    $slots = consult_slots();
    if (!$slots) {
        return null;
    }
    foreach ($slots as $day) {
        foreach ($day as $slot) {
            if ($slot['start'] === $isoStart) {
                $start = new DateTimeImmutable($isoStart);
                return [$start, $start->modify('+' . consult_settings()['slot_minutes'] . ' minutes')];
            }
        }
    }
    return null;
}

/** "Today", "Tomorrow" or "Friday" — the date itself is printed beside it. */
function consult_day_short(string $ymd): string
{
    $label = consult_day_label($ymd);
    if ($label === 'Today' || $label === 'Tomorrow') {
        return $label;
    }
    $day = DateTimeImmutable::createFromFormat('!Y-m-d', $ymd, new DateTimeZone(CONSULT_ZONE));
    return $day ? $day->format('l') : $ymd;
}

/** "Thursday, September 24" and "9:00 am", for headings and emails. */
function consult_day_label(string $ymd): string
{
    $day   = DateTimeImmutable::createFromFormat('!Y-m-d', $ymd, new DateTimeZone(CONSULT_ZONE));
    $today = new DateTimeImmutable('today', new DateTimeZone(CONSULT_ZONE));
    if (!$day) {
        return $ymd;
    }
    if ($day == $today) {
        return 'Today';
    }
    if ($day == $today->modify('+1 day')) {
        return 'Tomorrow';
    }
    return $day->format('l, F j');
}
