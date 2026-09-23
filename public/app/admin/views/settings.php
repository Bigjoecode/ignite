<?php // Vars: $user, $all, $offices (office slug => addresses, one per line), $locked ?>
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
