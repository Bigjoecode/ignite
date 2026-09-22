<?php
// Footer for ad landing pages: the office's details and one more way to book.
// Vars: $meta['office'] (may be null).
$lpOffice = $meta['office'] ?? null;
$lpPhone  = $lpOffice['phone'] ?? cfg('phone');
$lpTel    = $lpOffice['tel'] ?? cfg('phone_tel');
?>
<footer class="lp-foot">
  <div class="lp-foot__wrap">
    <div class="lp-foot__office">
      <img src="/assets/img/ignitelogo.png" alt="Ignite Orthodontics" width="300" height="52" loading="lazy" decoding="async">
<?php if ($lpOffice): ?>
      <p class="lp-foot__name">Ignite Orthodontics <?= e($lpOffice['name']) ?></p>
      <p><?= e($lpOffice['address_full']) ?></p>
<?php if ($lpOffice['hours']): ?>
      <p><?= e(implode(' · ', $lpOffice['hours'])) ?></p>
<?php endif; ?>
<?php endif; ?>
      <p><a href="tel:<?= e($lpTel) ?>"><?= e($lpPhone) ?></a><?php if (!empty($lpOffice['email'])): ?> &middot; <a href="mailto:<?= e($lpOffice['email']) ?>"><?= e($lpOffice['email']) ?></a><?php endif; ?></p>
    </div>
    <div class="lp-foot__cta">
      <p>Your first visit is a no-cost consultation.</p>
      <a class="lp-top__book" href="<?= e(booking_url()) ?>">Book Free Consultation</a>
    </div>
  </div>
  <div class="lp-foot__bar">
    <p>&copy; <?= date('Y') ?> Ignite Orthodontics. All rights reserved.</p>
    <p><a href="/privacy-policy/">Privacy Policy</a> &middot; <a href="/terms-and-conditions/">Terms and Conditions</a></p>
  </div>
</footer>
