<?php
// "Book a No-Cost Consultation" — same markup as the home-preview design.
// Needs home.css + home.js inside .ig-home. Optional: $formHeading.
$formHeading = $formHeading ?? 'Book A No-Cost Consultation';
?>
  <section class="ig-form-sec" id="ig-consult">
    <div class="ig-form-grid">

      <div class="ig-form__media">
        <img src="/assets/img/IMG_20260814_125716.jpg" alt="Orthodontist holding a braces model and a clear aligner" width="1920" height="1080" loading="lazy" decoding="async">
      </div>

      <div class="ig-form__panel">
        <div class="ig-form__inner">

          <h2><?= e($formHeading) ?></h2>

        <form class="ig-form" id="igConsultForm" action="/consult" method="post" novalidate>
          <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="ig-hp" aria-hidden="true">

          <div class="ig-form__row">
            <input name="full_name" type="text" placeholder="Full Name *" aria-label="Full Name" autocomplete="name" required>
            <input name="phone" type="tel" placeholder="Contact Number *" aria-label="Contact Number" autocomplete="tel" required>
          </div>

          <div class="ig-form__row">
            <input name="email" type="email" placeholder="Email *" aria-label="Email" autocomplete="email" required>
            <input name="zip" type="text" placeholder="Zip Code *" aria-label="Zip Code" inputmode="numeric" autocomplete="postal-code" required>
          </div>

          <textarea name="message" rows="4" placeholder="Write a Message" aria-label="Write a Message"></textarea>

          <label class="ig-consent">
            <input type="checkbox" name="consent" required>
            <span>Checking this box is my signature to agree to receive text messages and calls about my healthcare and for marketing purposes, including autodialed, from <strong>Ignite Orthodontics</strong> at the number below. Messages may occasionally be sent outside of normal business hours, including weekends. I understand that this consent is not a condition of purchasing any goods or services, I can opt out at any time, message/data rates may apply per my phone plan, and opting-in includes acceptance of our <a href="/privacy-policy/">Privacy Policy</a> and <a href="/terms-and-conditions/">Terms and Conditions</a>. Contact: <a href="tel:<?= e(cfg('phone_tel')) ?>"><?= e(cfg('phone')) ?></a>.</span>
          </label>

          <button class="ig-btn ig-btn--primary ig-form__submit" type="submit">Schedule Now</button>
          <p class="ig-form__note" id="igFormNote">Fields marked * are required.</p>
        </form>

        </div>
      </div>
    </div>
  </section>
