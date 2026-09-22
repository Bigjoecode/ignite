<?php
// Slim header for ad landing pages (/lp/...): logo, the office, call and book.
// No site menu, so visitors stay on the offer. Vars: $meta['office'] (may be null).
$lpOffice = $meta['office'] ?? null;
$lpPhone  = $lpOffice['phone'] ?? cfg('phone');
$lpTel    = $lpOffice['tel'] ?? cfg('phone_tel');
?>
<header class="lp-top">
  <div class="lp-top__bar">
    <div class="lp-top__brand">
      <img src="/assets/img/ignitelogo.png" alt="Ignite Orthodontics" width="300" height="52">
<?php if ($lpOffice): ?>
      <span class="lp-top__office"><?= e($lpOffice['name']) ?></span>
<?php endif; ?>
    </div>
    <div class="lp-top__actions">
      <a class="lp-top__call" href="tel:<?= e($lpTel) ?>" aria-label="Call Ignite Orthodontics<?= $lpOffice ? ' ' . e($lpOffice['name']) : '' ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.2.4 2.4.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1-9.4 0-17-7.6-17-17 0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2z"/></svg>
        <span><?= e($lpPhone) ?></span>
      </a>
      <a class="lp-top__book" href="<?= e(booking_url()) ?>">Book Free Consultation</a>
    </div>
  </div>
</header>
