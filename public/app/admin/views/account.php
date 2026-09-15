<?php // Vars: $user, $errors ?>
<div class="adm-head">
  <h1>Your Account</h1>
</div>

<?php foreach ($errors as $error): ?>
<p class="adm-notice adm-notice--error" role="alert"><?= e($error) ?></p>
<?php endforeach; ?>

<form method="post" class="adm-card adm-box adm-account" novalidate>
  <?= csrf_field() ?>
  <h2>Profile</h2>
  <label class="adm-field">
    <span>Name</span>
    <input type="text" name="name" value="<?= e($user['name']) ?>" maxlength="80" required autocomplete="name">
  </label>
  <label class="adm-field">
    <span>Email</span>
    <input type="email" value="<?= e($user['email']) ?>" disabled>
  </label>

  <h2>Change password</h2>
  <p class="adm-help">Leave these empty to keep your current password.</p>
  <label class="adm-field">
    <span>Current password</span>
    <input type="password" name="current_password" autocomplete="current-password">
  </label>
  <label class="adm-field">
    <span>New password</span>
    <input type="password" name="new_password" minlength="12" autocomplete="new-password">
  </label>
  <label class="adm-field">
    <span>Confirm new password</span>
    <input type="password" name="confirm_password" minlength="12" autocomplete="new-password">
  </label>
  <p class="adm-help">At least 12 characters.</p>

  <button type="submit" class="adm-btn adm-btn--primary">Save changes</button>
</form>
