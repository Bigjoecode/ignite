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
    return $s['enabled'] && $s['calendar_id'] !== '' && $s['book_as'] !== '' && gcal_key() !== null;
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

    $busy = gcal_busy($s['calendar_id'], $s['book_as'], $now, $last);
    if ($busy === null) {
        return null;
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
        if ($slots) {
            $days[$day->format('Y-m-d')] = $slots;
        }
    }
    return $days;
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
