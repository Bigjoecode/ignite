<?php // Vars: $error, $email ?>
<div class="adm-login">
  <div class="adm-login__card">
    <img class="adm-login__logo" src="/assets/img/ignitelogo.png" alt="Ignite Orthodontics">
    <h1>Sign in to the dashboard</h1>
    <p class="adm-login__sub">Manage blog posts and the media library.</p>

<?php if ($error): ?>
    <p class="adm-notice adm-notice--error" role="alert"><?= e($error) ?></p>
<?php endif; ?>

    <form method="post" class="adm-form" novalidate>
      <?= csrf_field() ?>
      <label class="adm-field">
        <span>Email</span>
        <input type="email" name="email" value="<?= e($email) ?>" autocomplete="username" required autofocus>
      </label>
      <label class="adm-field">
        <span>Password</span>
        <input type="password" name="password" autocomplete="current-password" required>
      </label>
      <button class="adm-btn adm-btn--primary adm-btn--block" type="submit">Sign in</button>
    </form>
  </div>
  <a class="adm-login__back" href="/">&larr; Back to igniteorthodontics.com</a>
</div>
