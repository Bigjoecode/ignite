<?php
// Vars: $user, $all, $offices (office slug => addresses, one per line), $locked,
//       $consult (the virtual consultation settings), $hasKey, $keyPath, $slotCheck [ok, message]
[$consultOk, $consultSays] = $slotCheck;
?>
<div class="adm-head">
  <h1>Settings</h1>
</div>

<form method="post" class="adm-card adm-box adm-settings" novalidate>
  <?= csrf_field() ?>
  <h2>Booking notifications</h2>
  <p class="adm-help">
    Every consultation request from the website is emailed to the addresses below and saved on the server.
    Put one address per line.
  </p>

  <label class="adm-field">
    <span>Every booking goes to</span>
    <textarea name="all" rows="3" spellcheck="false" placeholder="booking@igniteorthodontics.com"><?= e($all) ?></textarea>
  </label>
  <p class="adm-help">These addresses receive requests for every office.</p>

<?php if ($locked): ?>
  <p class="adm-help">Also sending to <strong><?= e(implode(', ', $locked)) ?></strong>, which is set in the server's <code>app/config.local.php</code>.</p>
<?php endif; ?>

  <h2>Office addresses</h2>
  <p class="adm-help">Anyone added here is emailed as well, but only for that office's requests. Leave an office empty if it has nobody of its own.</p>

<?php foreach (locations() as $slug => $office): ?>
  <label class="adm-field">
    <span><?= e($office['name']) ?></span>
    <textarea name="office[<?= e($slug) ?>]" rows="2" spellcheck="false"><?= e($offices[$slug] ?? '') ?></textarea>
  </label>
<?php endforeach; ?>

  <h2>Virtual consultations</h2>
  <p class="adm-help">
    The video consultation page at <a href="/virtual-consultation/" target="_blank" rel="noopener">/virtual-consultation/</a>
    offers the times that are free on a Google Calendar and books them with a Meet link.
    Setting it up is described in <code>docs/google-calendar-setup.md</code>.
  </p>
  <p class="adm-notice adm-notice--<?= $consultOk ? 'success' : 'warning' ?>"><?= e($consultSays) ?></p>

  <label class="adm-check">
    <input type="checkbox" name="consult[enabled]" value="1"<?= $consult['enabled'] ? ' checked' : '' ?>>
    <span>Offer video consultations on the website</span>
  </label>

  <label class="adm-field">
    <span>Calendar ID</span>
    <input type="text" name="consult[calendar_id]" value="<?= e($consult['calendar_id']) ?>" maxlength="200" spellcheck="false" placeholder="c_1a2b3c@group.calendar.google.com">
  </label>
  <label class="adm-field">
    <span>Book as</span>
    <input type="email" name="consult[book_as]" value="<?= e($consult['book_as']) ?>" maxlength="160" spellcheck="false" placeholder="booking@igniteorthodontics.com">
  </label>
  <p class="adm-help">
    The Google Workspace person the appointments are organised by. The key file is read from
    <code><?= e($keyPath) ?></code><?= $hasKey ? ' and is in place.' : ', which is not there yet.' ?>
  </p>

  <div class="adm-grid-2">
    <label class="adm-field">
      <span>How long is a call?</span>
      <select name="consult[slot_minutes]">
<?php foreach ([10, 15, 20, 30, 45, 60] as $minutes): ?>
        <option value="<?= $minutes ?>"<?= $consult['slot_minutes'] === $minutes ? ' selected' : '' ?>><?= $minutes ?> minutes</option>
<?php endforeach; ?>
      </select>
    </label>
    <label class="adm-field">
      <span>How far ahead can they book?</span>
      <select name="consult[days_ahead]">
<?php foreach ([7 => '1 week', 14 => '2 weeks', 21 => '3 weeks', 30 => '30 days', 60 => '60 days'] as $days => $label): ?>
        <option value="<?= $days ?>"<?= $consult['days_ahead'] === $days ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
      </select>
    </label>
    <label class="adm-field">
      <span>Shortest notice</span>
      <select name="consult[lead_hours]">
<?php foreach ([0 => 'Any time from now', 1 => '1 hour', 2 => '2 hours', 4 => '4 hours', 24 => 'The next day'] as $hours => $label): ?>
        <option value="<?= $hours ?>"<?= $consult['lead_hours'] === $hours ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
      </select>
    </label>
  </div>

  <h3 class="adm-settings__sub">Hours you offer video calls</h3>
  <p class="adm-help">For example <code>09:00-12:00, 14:00-17:00</code>. Leave a day empty for none. Within these hours, only times that are free on the calendar are offered.</p>
  <div class="adm-grid-2">
<?php foreach (CONSULT_DAYS as $key => $label): ?>
    <label class="adm-field">
      <span><?= e($label) ?></span>
      <input type="text" name="consult[hours][<?= e($key) ?>]" value="<?= e($consult['hours'][$key]) ?>" maxlength="120" spellcheck="false" placeholder="<?= in_array($key, ['sat', 'sun'], true) ? 'none' : '09:00-17:00' ?>">
    </label>
<?php endforeach; ?>
  </div>

  <div class="adm-settings__actions">
    <button type="submit" class="adm-btn adm-btn--primary">Save changes</button>
    <span class="adm-settings__test">
      <button type="submit" name="action" value="test" class="adm-btn">Save and send a test</button>
      <select name="test_office" aria-label="Office to test">
<?php foreach (locations() as $slug => $office): ?>
        <option value="<?= e($slug) ?>"><?= e($office['name']) ?></option>
<?php endforeach; ?>
      </select>
    </span>
  </div>
  <p class="adm-help">The test email is clearly marked as a test and goes to everyone set up for the chosen office.</p>
</form>
