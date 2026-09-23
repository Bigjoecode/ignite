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
    'first_name', 'last_name', 'phone', 'email', 'notes', 'source'];

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

/** Brings requests that are only in bookings.jsonl into the table. Returns how many were added. */
function booking_import_file(): int
{
    $file = cfg('data_dir') . '/bookings.jsonl';
    if (!is_file($file)) {
        return 0;
    }
    $added = 0;
    $known = db()->query('SELECT id FROM bookings')->fetchAll(PDO::FETCH_COLUMN);
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $record = json_decode($line, true);
        if (is_array($record) && !empty($record['id']) && !in_array($record['id'], $known, true) && booking_store($record)) {
            $added++;
        }
    }
    return $added;
}

/** Requests newest first. $filters: status, office, q (name, phone or email). */
function booking_list(array $filters = [], int $limit = 200): array
{
    $where = [];
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
    $sql  = 'SELECT * FROM bookings' . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . ' ORDER BY created_at DESC LIMIT ' . $limit;
    $stmt = db()->prepare($sql);
    $stmt->execute($args);
    return $stmt->fetchAll();
}

/** How many requests sit in each status, for the filter tabs. */
function booking_counts(): array
{
    $counts = ['' => 0] + array_map(static fn(): int => 0, BOOKING_STATUSES);
    foreach (db()->query('SELECT status, COUNT(*) AS n FROM bookings GROUP BY status')->fetchAll() as $row) {
        $counts[$row['status']] = (int) $row['n'];
        $counts['']            += (int) $row['n'];
    }
    return $counts;
}

function booking_new_count(): int
{
    try {
        return (int) db()->query("SELECT COUNT(*) FROM bookings WHERE status = 'new'")->fetchColumn();
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

function booking_update(string $id, string $status, string $note, int $userId): bool
{
    if (!isset(BOOKING_STATUSES[$status])) {
        return false;
    }
    $stmt = db()->prepare('UPDATE bookings SET status = ?, staff_note = ?, updated_at = ?, updated_by = ? WHERE id = ?');
    $stmt->execute([$status, mb_substr($note, 0, 2000), date('c'), $userId, $id]);
    return $stmt->rowCount() > 0;
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
