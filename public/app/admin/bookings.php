<?php
declare(strict_types=1);

// Bookings screen: the consultation requests from the website, plus the ones
// the practice takes itself. Add, edit, trash, restore and delete.

require_once APP . '/bookings.php';

/** The list, with the status tabs, the office filter and a search box. */
function admin_bookings_index(array $user): void
{
    booking_import_file();   // anything that only reached bookings.jsonl

    $filters = [
        'status' => (string) ($_GET['status'] ?? ''),
        'office' => (string) ($_GET['office'] ?? ''),
        'q'      => mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 60),
    ];

    admin_render('bookings', [
        'rows'    => booking_list($filters),
        'counts'  => booking_counts(),
        'filters' => $filters,
    ], 'Bookings', $user);
}

/** The form for a request the practice takes itself. */
function admin_booking_new(array $user): void
{
    admin_render('booking-edit', [
        'row'      => ['id' => '', 'created_at' => gmdate('c'), 'status' => 'new', 'office' => (string) ($_GET['office'] ?? ''),
            'patient' => '', 'treatment' => '', 'date' => 'first', 'time' => '', 'first_name' => '', 'last_name' => '',
            'phone' => '', 'email' => '', 'notes' => '', 'staff_note' => '', 'source' => '', 'trashed_at' => null, 'updated_at' => null],
        'problems' => [],
        'isNew'    => true,
    ], 'Add a booking', $user);
}

/** One request: everything it holds, and what to do next. */
function admin_booking_view(array $user, string $id): void
{
    $row = booking_find($id);
    if (!$row) {
        admin_not_found($user);
        exit;
    }
    admin_render('booking-edit', ['row' => $row, 'problems' => [], 'isNew' => false], 'Booking', $user);
}

/** Saves a new request, or the changes to one. */
function admin_booking_save(array $user, string $id): void
{
    $isNew = $id === '';
    if (!$isNew && !booking_find($id)) {
        admin_not_found($user);
        exit;
    }
    $fields = array_intersect_key($_POST, array_flip(['status', 'office', 'patient', 'treatment', 'date', 'time',
        'first_name', 'last_name', 'phone', 'email', 'notes', 'staff_note']));

    if ($problems = booking_problems($fields)) {
        admin_render('booking-edit', [
            'row'      => $fields + ($isNew ? ['id' => '', 'created_at' => gmdate('c'), 'source' => '', 'trashed_at' => null, 'updated_at' => null] : booking_find($id)),
            'problems' => $problems,
            'isNew'    => $isNew,
        ], $isNew ? 'Add a booking' : 'Booking', $user);
        exit;
    }

    if ($isNew) {
        $id = booking_create($fields, (int) $user['id']);
        admin_flash('success', 'Booking added.');
    } else {
        booking_update($id, $fields, (int) $user['id']);
        admin_flash('success', 'Booking saved.');
    }
    redirect('/admin/bookings/' . rawurlencode($id) . '/', 303);
}

/** Trash, restore or delete for good. */
function admin_booking_action(array $user, string $id, string $action): void
{
    if (!booking_find($id)) {
        admin_not_found($user);
        exit;
    }
    if ($action === 'delete') {
        booking_delete($id);
        admin_flash('success', 'Booking deleted.');
        redirect('/admin/bookings/?status=trash', 303);
    }
    booking_trash($id, $action === 'trash', (int) $user['id']);
    admin_flash('success', $action === 'trash' ? 'Booking moved to the trash.' : 'Booking restored.');
    redirect($action === 'trash' ? '/admin/bookings/' : '/admin/bookings/' . rawurlencode($id) . '/', 303);
}

/** Every request as a spreadsheet, so the practice can work offline or hand it over. */
function admin_bookings_export(): void
{
    $rows = booking_list([
        'status' => (string) ($_GET['status'] ?? ''),
        'office' => (string) ($_GET['office'] ?? ''),
        'q'      => (string) ($_GET['q'] ?? ''),
    ], 5000);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="ignite-bookings-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");   // so Excel reads the accents correctly
    fputcsv($out, ['Received', 'Status', 'Name', 'Phone', 'Email', 'Office', 'For', 'Treatment', 'Preferred day', 'Preferred time', 'Notes from patient', 'Our notes', 'Came from', 'Request ID']);
    foreach ($rows as $r) {
        fputcsv($out, [
            booking_local_time($r['created_at']),
            BOOKING_STATUSES[$r['status']] ?? $r['status'],
            trim($r['first_name'] . ' ' . $r['last_name']),
            $r['phone'],
            $r['email'],
            locations()[$r['office']]['name'] ?? $r['office'],
            booking_choice('patient', $r['patient']),
            booking_choice('treatment', $r['treatment']),
            booking_day($r['date']),
            booking_choice('time', $r['time']),
            $r['notes'],
            $r['staff_note'],
            $r['source'] !== '' ? (str_starts_with($r['source'], '/') ? 'igniteorthodontics.com' . $r['source'] : $r['source']) : '',
            $r['id'],
        ]);
    }
    fclose($out);
    exit;
}
