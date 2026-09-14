<?php
// "Book a No-Cost Consultation" band (photo left, call-to-action right).
// Used by home, location and content pages. Optional: $ctaImage, $ctaPhone, $ctaTel.
$ctaImage = $ctaImage ?? '/assets/img/IMG_20260814_125716.jpg';
$ctaPhone = $ctaPhone ?? cfg('phone');
$ctaTel   = $ctaTel ?? cfg('phone_tel');
$officeCount = count(locations());
$officeWord  = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve'][$officeCount] ?? $officeCount;
$check = '<span class="ig-cc__tick" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5.5 12.5l4 4L18.5 7.5"/></svg></span>';
?>
  <section class="ig-cc" id="ig-consult" aria-labelledby="igCcTitle">
    <div class="ig-cc__media">
      <img src="<?= e($ctaImage) ?>" alt="" width="1920" height="1080" loading="lazy" decoding="async">
    </div>

    <div class="ig-cc__panel">
      <span class="ig-cc__kicker">Your First Visit Is On Us</span>
      <h2 class="ig-cc__title" id="igCcTitle">Book A <em>No-Cost Consultation</em></h2>
      <p class="ig-cc__lead">Talk with our team, explore your treatment options, and get clear next steps&mdash;without pressure or obligation.</p>

      <ul class="ig-cc__list">
        <li><?= $check ?>Complimentary orthodontic evaluation</li>
        <li><?= $check ?>Clear treatment options and estimated costs</li>
        <li><?= $check ?>Flexible appointments across <?= e($officeWord) ?> Michigan offices</li>
      </ul>

      <div class="ig-cc__btns">
        <a class="ig-cc__btn ig-cc__btn--call" href="tel:<?= e($ctaTel) ?>">Call <?= e($ctaPhone) ?></a>
        <a class="ig-cc__btn ig-cc__btn--out" href="/locations/">Choose a Location</a>
      </div>

      <p class="ig-cc__note">No referral needed. New patients are welcome.</p>
    </div>
  </section>
