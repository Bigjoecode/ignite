<?php
// Vars: $row (a bookings row, or the empty one for a new booking), $problems, $isNew
$opts    = require APP . '/data/booking.php';
$name    = trim($row['first_name'] . ' ' . $row['last_name']);
$id      = (string) $row['id'];
$action  = $isNew ? '/admin/bookings/create/' : '/admin/bookings/' . rawurlencode($id) . '/';
$trashed = !$isNew && $row['trashed_at'];
$value   = static fn(string $key): string => (string) ($row[$key] ?? '');
?>
<div class="adm-head">
  <h1><?= $isNew ? 'Add a booking' : e($name !== '' ? $name : '(no name)') ?></h1>
  <a class="adm-btn" href="/admin/bookings/">&larr; All bookings</a>
<?php if ($isNew): ?>
  <p class="adm-head__sub">For a request that came in by phone or at the desk. Website requests arrive here on their own.</p>
<?php else: ?>
  <p class="adm-head__sub">
    Requested <?= e(booking_local_time($row['created_at'])) ?><?php
      if ($value('source') !== '') echo ', from ', $value('source')[0] === '/' ? '<a href="' . e($value('source')) . '" target="_blank" rel="noopener">' . e($value('source')) . '</a>' : e($value('source'));
    ?>.
  </p>
<?php endif; ?>
</div>

<?php if ($trashed): ?>
<p class="adm-notice adm-notice--warning">This booking is in the trash. Restore it to work on it again.</p>
<?php endif; ?>
<?php foreach ($problems as $problem): ?>
<p class="adm-notice adm-notice--error" role="alert"><?= $problem ?></p>
<?php endforeach; ?>

<form method="post" action="<?= e($action) ?>" class="adm-booking" novalidate>
  <?= csrf_field() ?>
  <div class="adm-card adm-box">
    <h2>The patient</h2>
    <div class="adm-grid-2">
      <label class="adm-field">
        <span>First name</span>
        <input type="text" name="first_name" value="<?= e($value('first_name')) ?>" maxlength="60" autocomplete="off">
      </label>
      <label class="adm-field">
        <span>Last name</span>
        <input type="text" name="last_name" value="<?= e($value('last_name')) ?>" maxlength="60" autocomplete="off">
      </label>
      <label class="adm-field">
        <span>Phone</span>
        <input type="text" name="phone" value="<?= e($value('phone')) ?>" maxlength="30" autocomplete="off">
      </label>
      <label class="adm-field">
        <span>Email</span>
        <input type="email" name="email" value="<?= e($value('email')) ?>" maxlength="160" autocomplete="off">
      </label>
    </div>

    <h2>What they want</h2>
    <div class="adm-grid-2">
      <label class="adm-field">
        <span>Office</span>
        <select name="office">
          <option value="">Choose an office</option>
<?php foreach (locations() as $slug => $office): ?>
          <option value="<?= e($slug) ?>"<?= $value('office') === $slug ? ' selected' : '' ?>><?= e($office['name']) ?></option>
<?php endforeach; ?>
        </select>
      </label>
      <label class="adm-field">
        <span>For</span>
        <select name="patient">
          <option value="">Not said</option>
<?php foreach ($opts['patients'] as $key => $patient): ?>
          <option value="<?= e($key) ?>"<?= $value('patient') === $key ? ' selected' : '' ?>><?= e($patient[0]) ?></option>
<?php endforeach; ?>
        </select>
      </label>
      <label class="adm-field">
        <span>Treatment</span>
        <select name="treatment">
          <option value="">Not said</option>
<?php foreach ($opts['treatments'] as $key => $treatment): ?>
          <option value="<?= e($key) ?>"<?= $value('treatment') === $key ? ' selected' : '' ?>><?= e($treatment[0]) ?></option>
<?php endforeach; ?>
        </select>
      </label>
      <label class="adm-field">
        <span>Preferred time</span>
        <select name="time">
          <option value="">Not said</option>
<?php foreach ($opts['times'] as $key => $label): ?>
          <option value="<?= e($key) ?>"<?= $value('time') === $key ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
        </select>
      </label>
      <label class="adm-field">
        <span>Preferred day</span>
        <input type="date" name="date" value="<?= e($value('date') !== 'first' ? $value('date') : '') ?>">
      </label>
    </div>
    <p class="adm-help">Leave the day empty for &ldquo;first available&rdquo;.</p>

    <label class="adm-field">
      <span>What the patient said</span>
      <textarea name="notes" rows="4"><?= e($value('notes')) ?></textarea>
    </label>
  </div>

  <div class="adm-card adm-box">
    <h2>Where it stands</h2>
    <label class="adm-field">
      <span>Status</span>
      <select name="status">
<?php foreach (BOOKING_STATUSES as $key => $label): ?>
        <option value="<?= e($key) ?>"<?= $value('status') === $key ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
      </select>
    </label>
    <label class="adm-field">
      <span>Notes for the team</span>
      <textarea name="staff_note" rows="6" placeholder="Called and left a message, appointment set for the 5th&hellip;"><?= e($value('staff_note')) ?></textarea>
    </label>
<?php if (!$isNew && $row['updated_at']): ?>
    <p class="adm-help">Last changed <?= e(booking_local_time($row['updated_at'])) ?>.</p>
<?php endif; ?>
    <button type="submit" class="adm-btn adm-btn--primary"><?= $isNew ? 'Add booking' : 'Save changes' ?></button>
<?php if (!$isNew): ?>
    <p class="adm-help adm-booking__danger">
<?php if ($trashed): ?>
      <button type="submit" class="adm-link" formaction="/admin/bookings/<?= e(rawurlencode($id)) ?>/restore/">Restore</button>
      <button type="submit" class="adm-link adm-link--danger" formaction="/admin/bookings/<?= e(rawurlencode($id)) ?>/delete/" data-adm-confirm="Delete this booking for good? This cannot be undone.">Delete permanently</button>
<?php else: ?>
      <button type="submit" class="adm-link adm-link--danger" formaction="/admin/bookings/<?= e(rawurlencode($id)) ?>/trash/" data-adm-confirm="Move this booking to the trash?">Move to trash</button>
<?php endif; ?>
    </p>
<?php endif; ?>
  </div>
</form>
