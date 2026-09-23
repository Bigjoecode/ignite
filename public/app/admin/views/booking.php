<?php
// Vars: $row (one bookings row)
$name  = trim($row['first_name'] . ' ' . $row['last_name']);
$phone = preg_replace('/[^0-9+]/', '', $row['phone']);
?>
<div class="adm-head">
  <h1><?= e($name !== '' ? $name : '(no name)') ?></h1>
  <a class="adm-btn" href="/admin/bookings/">&larr; All bookings</a>
  <p class="adm-head__sub">Requested <?= e(booking_local_time($row['created_at'])) ?>.</p>
</div>

<div class="adm-booking">
  <div class="adm-card adm-box">
    <h2>The request</h2>
    <dl class="adm-booking__list">
      <div><dt>Phone</dt><dd><a href="tel:<?= e($phone) ?>"><?= e($row['phone']) ?></a></dd></div>
      <div><dt>Email</dt><dd><a href="mailto:<?= e($row['email']) ?>"><?= e($row['email']) ?></a></dd></div>
      <div><dt>Office</dt><dd><?= e(locations()[$row['office']]['name'] ?? $row['office']) ?></dd></div>
      <div><dt>For</dt><dd><?= e(booking_choice('patient', $row['patient'])) ?></dd></div>
      <div><dt>Treatment</dt><dd><?= e(booking_choice('treatment', $row['treatment'])) ?></dd></div>
      <div><dt>Preferred</dt><dd><?= e(booking_day($row['date'])) ?>, <?= e(booking_choice('time', $row['time'])) ?></dd></div>
      <div><dt>Came from</dt><dd><?= $row['source'] !== '' ? '<a href="' . e($row['source']) . '" target="_blank" rel="noopener">' . e($row['source']) . '</a>' : '<span class="adm-muted">the booking page</span>' ?></dd></div>
      <div><dt>Request ID</dt><dd class="adm-muted"><?= e($row['id']) ?></dd></div>
    </dl>
<?php if ($row['notes'] !== ''): ?>
    <h2>What they wrote</h2>
    <p class="adm-booking__quote"><?= nl2br(e($row['notes'])) ?></p>
<?php endif; ?>
  </div>

  <form method="post" action="/admin/bookings/<?= e(rawurlencode($row['id'])) ?>/" class="adm-card adm-box">
    <?= csrf_field() ?>
    <h2>Where it stands</h2>
    <label class="adm-field">
      <span>Status</span>
      <select name="status">
<?php foreach (BOOKING_STATUSES as $key => $label): ?>
        <option value="<?= e($key) ?>"<?= $row['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
      </select>
    </label>
    <label class="adm-field">
      <span>Notes for the team</span>
      <textarea name="staff_note" rows="6" placeholder="Called and left a message, appointment set for the 5th&hellip;"><?= e($row['staff_note']) ?></textarea>
    </label>
<?php if ($row['updated_at']): ?>
    <p class="adm-help">Last changed <?= e(booking_local_time($row['updated_at'])) ?>.</p>
<?php endif; ?>
    <button type="submit" class="adm-btn adm-btn--primary">Save</button>
  </form>
</div>
