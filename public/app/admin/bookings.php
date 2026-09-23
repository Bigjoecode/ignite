<?php
declare(strict_types=1);

// Bookings screen: the consultation requests that came in from the website.

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

/** One request, with its notes and what to do next. */
function admin_booking_view(array $user, string $id): void
{
    $row = booking_find($id);
    if (!$row) {
        admin_not_found($user);
        exit;
    }
    admin_render('booking', ['row' => $row], 'Booking', $user);
}

/** Saves the status and the practice's note. */
function admin_booking_save(array $user, string $id): void
{
    $status = (string) ($_POST['status'] ?? '');
    $note   = trim((string) ($_POST['staff_note'] ?? ''));
    if (!booking_find($id)) {
        admin_not_found($user);
        exit;
    }
    if (!booking_update($id, $status, $note, (int) $user['id'])) {
        admin_flash('error', 'That status is not one we know, so nothing was changed.');
    } else {
        admin_flash('success', 'Booking updated.');
    }
    redirect('/admin/bookings/' . rawurlencode($id) . '/', 303);
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
            $r['source'] !== '' ? 'igniteorthodontics.com' . $r['source'] : '',
            $r['id'],
        ]);
    }
    fclose($out);
    exit;
}
