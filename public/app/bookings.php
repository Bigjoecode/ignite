<?php
declare(strict_types=1);

// Consultation requests in the database, so the practice can work through them
// in the dashboard. Every request is still appended to bookings.jsonl first —
// that file is the record of what came in; this table is the working copy.

const BOOKING_STATUSES = [
    'new'       => 'New',
    'contacted' => 'Contacted',
    'booked'    => 'Appointment booked',
    'closed'    => 'Closed',
];

const BOOKING_FIELDS = ['id', 'created_at', 'status', 'office', 'patient', 'treatment', 'date', 'time',
    'first_name', 'last_name', 'phone', 'email', 'notes', 'source', 'kind', 'start_at', 'meet_url', 'event_id'];

/** Saves one request. Never throws: the request is already safe in bookings.jsonl. */
function booking_store(array $record): bool
{
    try {
        $row = ['status' => 'new'];
        foreach (BOOKING_FIELDS as $field) {
            $row[$field] = (string) ($record[$field] ?? $row[$field] ?? '');
        }
        $cols = implode(', ', array_keys($row));
        $vals = ':' . implode(', :', array_keys($row));
        db()->prepare("INSERT OR IGNORE INTO bookings ({$cols}) VALUES ({$vals})")->execute($row);
        return true;
    } catch (Throwable $e) {
        error_log('booking_store: ' . $e->getMessage());
        return false;
    }
}

/**
 * Takes a video consultation time for this patient. Returns false when someone
 * else got it first: the database index, not a check-then-write, decides, so two
 * people clicking the same slot at the same moment cannot both be booked.
 */
function booking_reserve_slot(array $record): bool
{
    $row = ['status' => 'booked'];
    foreach (BOOKING_FIELDS as $field) {
        $row[$field] = (string) ($record[$field] ?? $row[$field] ?? '');
    }
    $cols = implode(', ', array_keys($row));
    $vals = ':' . implode(', :', array_keys($row));
    try {
        db()->prepare("INSERT INTO bookings ({$cols}) VALUES ({$vals})")->execute($row);
        return true;
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'UNIQUE')) {
            return false;
        }
        throw $e;
    }
}

/** Gives a held time back, when the appointment could not be completed. */
function booking_release_slot(string $id): void
{
    try {
        db()->prepare('DELETE FROM bookings WHERE id = ?')->execute([$id]);
    } catch (Throwable $e) {
        error_log('booking_release_slot: ' . $e->getMessage());
    }
}

/**
 * Brings requests that are only in bookings.jsonl into the table, for the ones
 * taken before the table existed. Only requests newer than the last import are
 * looked at, so a request deleted in the dashboard does not come back.
 * Returns how many were added.
 */
function booking_import_file(): int
{
    $file = cfg('data_dir') . '/bookings.jsonl';
    if (!is_file($file)) {
        return 0;
    }
    $done  = (string) (db_setting(db(), 'bookings_imported_until') ?? '');
    $added = 0;
    $latest = $done;
    $known = array_merge(db()->query('SELECT id FROM bookings')->fetchAll(PDO::FETCH_COLUMN), booking_deleted_ids());
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $record = json_decode($line, true);
        $at     = (string) ($record['created_at'] ?? '');
        if (!is_array($record) || empty($record['id']) || $at === '' || ($done !== '' && $at < $done)) {
            continue;
        }
        if (!in_array($record['id'], $known, true) && booking_store($record)) {
            $added++;
        }
        $latest = max($latest, $at);
    }
    if ($latest !== $done) {
        db_set_setting(db(), 'bookings_imported_until', $latest);
    }
    return $added;
}

/** Requests newest first. $filters: status ('trash' for the trash), office, q (name, phone or email). */
function booking_list(array $filters = [], int $limit = 200): array
{
    $where = [($filters['status'] ?? '') === 'trash' ? 'trashed_at IS NOT NULL' : 'trashed_at IS NULL'];
    $args  = [];
    if (!empty($filters['status']) && isset(BOOKING_STATUSES[$filters['status']])) {
        $where[] = 'status = ?';
        $args[]  = $filters['status'];
    }
    if (!empty($filters['office']) && isset(locations()[$filters['office']])) {
        $where[] = 'office = ?';
        $args[]  = $filters['office'];
    }
    if (($q = trim((string) ($filters['q'] ?? ''))) !== '') {
        $where[] = '(first_name || " " || last_name LIKE ? OR phone LIKE ? OR email LIKE ?)';
        $like    = '%' . $q . '%';
        $args    = array_merge($args, [$like, $like, $like]);
    }
    $stmt = db()->prepare('SELECT * FROM bookings WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC LIMIT ' . $limit);
    $stmt->execute($args);
    return $stmt->fetchAll();
}

/** How many requests sit in each status, plus '' for all and 'trash', for the filter tabs. */
function booking_counts(): array
{
    $counts = ['' => 0, 'trash' => 0] + array_map(static fn(): int => 0, BOOKING_STATUSES);
    foreach (db()->query('SELECT status, trashed_at IS NULL AS live, COUNT(*) AS n FROM bookings GROUP BY status, live')->fetchAll() as $row) {
        if (!$row['live']) {
            $counts['trash'] += (int) $row['n'];
            continue;
        }
        $counts[$row['status']] = (int) $row['n'];
        $counts['']            += (int) $row['n'];
    }
    return $counts;
}

function booking_new_count(): int
{
    try {
        return (int) db()->query("SELECT COUNT(*) FROM bookings WHERE status = 'new' AND trashed_at IS NULL")->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function booking_find(string $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM bookings WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/** A request the practice takes itself, over the phone or at the desk. */
function booking_create(array $fields, int $userId): string
{
    $id  = bin2hex(random_bytes(8));
    $row = booking_clean($fields) + [
        'id'         => $id,
        'created_at' => gmdate('c'),
        'source'     => 'added in the dashboard',
        'updated_at' => date('c'),
        'updated_by' => $userId,
    ];
    $cols = implode(', ', array_keys($row));
    $vals = ':' . implode(', :', array_keys($row));
    db()->prepare("INSERT INTO bookings ({$cols}) VALUES ({$vals})")->execute($row);
    return $id;
}

/** Saves the details, the status and the practice's note. */
function booking_update(string $id, array $fields, int $userId): bool
{
    $row  = booking_clean($fields) + ['updated_at' => date('c'), 'updated_by' => $userId];
    $sets = implode(', ', array_map(static fn(string $k): string => "{$k} = :{$k}", array_keys($row)));
    $stmt = db()->prepare("UPDATE bookings SET {$sets} WHERE id = :id");
    $stmt->execute($row + ['id' => $id]);
    return $stmt->rowCount() > 0;
}

/** Only the fields the dashboard is allowed to set, cleaned and within the known choices. */
function booking_clean(array $f): array
{
    $pick = static fn(string $group, string $key): string => booking_choice($group, $key) !== '' ? $key : '';
    $date = trim((string) ($f['date'] ?? ''));
    return [
        'status'     => isset(BOOKING_STATUSES[$f['status'] ?? '']) ? (string) $f['status'] : 'new',
        'office'     => isset(locations()[$f['office'] ?? '']) ? (string) $f['office'] : '',
        'patient'    => $pick('patient', (string) ($f['patient'] ?? '')),
        'treatment'  => $pick('treatment', (string) ($f['treatment'] ?? '')),
        'date'       => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : 'first',
        'time'       => $pick('time', (string) ($f['time'] ?? '')),
        'first_name' => mb_substr(trim((string) ($f['first_name'] ?? '')), 0, 60),
        'last_name'  => mb_substr(trim((string) ($f['last_name'] ?? '')), 0, 60),
        'phone'      => mb_substr(trim((string) ($f['phone'] ?? '')), 0, 30),
        'email'      => mb_substr(trim((string) ($f['email'] ?? '')), 0, 160),
        'notes'      => mb_substr(trim((string) ($f['notes'] ?? '')), 0, 2000),
        'staff_note' => mb_substr(trim((string) ($f['staff_note'] ?? '')), 0, 2000),
    ];
}

/** What is wrong with the form, as messages for the person filling it in. */
function booking_problems(array $f): array
{
    $problems = [];
    if (trim((string) ($f['first_name'] ?? '')) === '' && trim((string) ($f['last_name'] ?? '')) === '') {
        $problems[] = 'Enter the patient&rsquo;s name.';
    }
    if (trim((string) ($f['phone'] ?? '')) === '' && trim((string) ($f['email'] ?? '')) === '') {
        $problems[] = 'Enter a phone number or an email address, so the practice can reach them.';
    }
    if (($f['email'] ?? '') !== '' && !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) {
        $problems[] = 'That email address does not look right.';
    }
    if (!isset(locations()[$f['office'] ?? ''])) {
        $problems[] = 'Choose the office.';
    }
    return $problems;
}

function booking_trash(string $id, bool $trashed, int $userId): bool
{
    $stmt = db()->prepare('UPDATE bookings SET trashed_at = ?, updated_at = ?, updated_by = ? WHERE id = ?');
    $stmt->execute([$trashed ? date('c') : null, date('c'), $userId, $id]);
    return $stmt->rowCount() > 0;
}

/** Gone for good. The request stays in bookings.jsonl if it came from the website. */
function booking_delete(string $id): bool
{
    $stmt = db()->prepare('DELETE FROM bookings WHERE id = ?');
    $stmt->execute([$id]);
    if ($stmt->rowCount() < 1) {
        return false;
    }
    // remember it, so the file import does not bring it back
    $gone = booking_deleted_ids();
    $gone[] = $id;
    db_set_setting(db(), 'bookings_deleted', json_encode(array_slice(array_unique($gone), -500)));
    return true;
}

function booking_deleted_ids(): array
{
    $ids = json_decode((string) db_setting(db(), 'bookings_deleted'), true);
    return is_array($ids) ? $ids : [];
}

/** "Tue, Sep 22 2026, 9:19am" in the practice's own time. */
function booking_local_time(string $utc): string
{
    try {
        return (new DateTimeImmutable($utc))->setTimezone(new DateTimeZone('America/Detroit'))->format('D, M j Y, g:ia');
    } catch (Throwable $e) {
        return $utc;
    }
}

/** When it is: the agreed time for a video visit, or what the visitor asked for. */
function booking_when(array $row): string
{
    if (($row['kind'] ?? '') === 'virtual' && !empty($row['start_at'])) {
        try {
            $start = (new DateTimeImmutable($row['start_at']))->setTimezone(new DateTimeZone('America/Detroit'));
            return $start->format('D, M j') . ' at ' . ltrim($start->format('g:ia'), '0');
        } catch (Throwable $e) {
            // fall through to the preferred day below
        }
    }
    $time = booking_choice('time', (string) ($row['time'] ?? ''));
    return booking_day((string) ($row['date'] ?? '')) . ($time !== '' ? ', ' . $time : '');
}

/** The day the visitor asked for, as written on the booking page. */
function booking_day(string $date): string
{
    if ($date === '' || $date === 'first') {
        return 'First available';
    }
    $day = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $day ? $day->format('l, F j') : $date;
}

/** Labels for the choices the visitor made ('patient', 'treatment', 'time'). */
function booking_choice(string $group, string $key): string
{
    static $opts = null;
    $opts ??= require APP . '/data/booking.php';
    $value = ($opts[$group . 's'] ?? $opts[$group] ?? [])[$key] ?? '';
    return is_array($value) ? (string) $value[0] : (string) $value;
}
