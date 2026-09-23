<?php
// Vars: $rows, $counts, $filters (status, office, q)
$query = static function (array $changes) use ($filters): string {
    $params = array_filter(array_merge($filters, $changes), static fn(string $v): bool => $v !== '');
    return $params ? '?' . http_build_query($params) : '';
};
$tabs = ['' => 'All'] + BOOKING_STATUSES;
?>
<div class="adm-head">
  <h1>Bookings</h1>
  <a class="adm-btn" href="/admin/bookings/export.csv<?= e($query([])) ?>">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 19h16"/></svg>Download as a spreadsheet
  </a>
  <p class="adm-head__sub">Every consultation request from the website. They are also emailed to the addresses in <a href="/admin/settings/">Settings</a>.</p>
</div>

<div class="adm-toolbar">
  <nav class="adm-tabs" aria-label="Filter bookings">
<?php foreach ($tabs as $key => $label): ?>
<?php if ($key !== '' && !$counts[$key] && $filters['status'] !== $key) continue; ?>
    <a href="/admin/bookings/<?= e($query(['status' => (string) $key])) ?>"<?= $filters['status'] === (string) $key ? ' class="is-active" aria-current="page"' : '' ?>><?= e($label) ?> <span>(<?= (int) $counts[$key] ?>)</span></a>
<?php endforeach; ?>
  </nav>
  <form class="adm-search" method="get" action="/admin/bookings/" role="search">
    <input type="hidden" name="status" value="<?= e($filters['status']) ?>">
    <select name="office" aria-label="Office">
      <option value="">All offices</option>
<?php foreach (locations() as $slug => $office): ?>
      <option value="<?= e($slug) ?>"<?= $filters['office'] === $slug ? ' selected' : '' ?>><?= e($office['name']) ?></option>
<?php endforeach; ?>
    </select>
    <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Name, phone or email" aria-label="Search bookings">
    <button class="adm-btn" type="submit">Search</button>
  </form>
</div>

<?php if (!$rows): ?>
<div class="adm-card adm-empty">
<?php if ($filters['q'] !== '' || $filters['office'] !== '' || $filters['status'] !== ''): ?>
  <h2>No bookings match</h2>
  <p><a href="/admin/bookings/">Show them all</a></p>
<?php else: ?>
  <h2>No requests yet</h2>
  <p>Consultation requests from the website appear here as soon as they come in.</p>
<?php endif; ?>
</div>
<?php else: ?>
<div class="adm-card adm-table-wrap">
  <table class="adm-table">
    <thead>
      <tr>
        <th scope="col">Patient</th>
        <th scope="col">Office</th>
        <th scope="col">Wants</th>
        <th scope="col">Status</th>
        <th scope="col">Received</th>
      </tr>
    </thead>
    <tbody>
<?php foreach ($rows as $row):
    $name = trim($row['first_name'] . ' ' . $row['last_name']);
    $link = '/admin/bookings/' . rawurlencode($row['id']) . '/';
?>
      <tr>
        <td>
          <a class="adm-post-title" href="<?= e($link) ?>"><?= e($name !== '' ? $name : '(no name)') ?></a>
          <div class="adm-row-actions">
            <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $row['phone'])) ?>"><?= e($row['phone']) ?></a>
            <a href="mailto:<?= e($row['email']) ?>"><?= e($row['email']) ?></a>
          </div>
<?php if ($row['source'] !== ''): ?>
          <div class="adm-muted adm-path">from <?= e($row['source']) ?></div>
<?php endif; ?>
        </td>
        <td><?= e(locations()[$row['office']]['name'] ?? $row['office']) ?></td>
        <td>
          <?= e(booking_choice('treatment', $row['treatment'])) ?>
          <div class="adm-muted"><?= e(booking_choice('patient', $row['patient'])) ?> &middot; <?= e(booking_day($row['date'])) ?>, <?= e(booking_choice('time', $row['time'])) ?></div>
        </td>
        <td><span class="adm-status adm-status--<?= e($row['status']) ?>"><?= e(BOOKING_STATUSES[$row['status']] ?? $row['status']) ?></span></td>
        <td class="adm-date"><?= e(booking_local_time($row['created_at'])) ?></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
