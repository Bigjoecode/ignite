<?php
// Booking popup: a 5-step consultation request that posts to /book and redirects
// to /thank-you/. Opened by any [data-book] element or link to #ig-consult, or by
// /?book=1[&office=slug] (assets/js/booking.js). Choices: app/data/booking.php.
$bk    = require APP . '/data/booking.php';
$icon  = static fn(string $d): string => '<span class="ig-bk__ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="' . $d . '"/></svg></span>';
$pin   = 'M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7z M12 11.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z';
$start = new DateTimeImmutable('tomorrow');
?>
<div class="ig-bk" id="igBook" role="dialog" aria-modal="true" aria-label="Book a no-cost consultation" hidden>
  <div class="ig-bk__sheet">

    <div class="ig-bk__head">
      <button class="ig-bk__back" type="button" data-bk-back aria-label="Previous step" disabled>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
      </button>
      <div class="ig-bk__progress" aria-hidden="true"><b data-bk-bar></b></div>
      <button class="ig-bk__close" type="button" data-bk-close aria-label="Close booking">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>

    <form class="ig-bk__body" action="/book" method="post" novalidate data-bk-form>
      <input type="text" name="website" value="" class="ig-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
      <p class="ig-bk__count" data-bk-count aria-live="polite">Step 1 of 5</p>

      <fieldset class="ig-bk__pane" data-step="1" data-auto>
        <legend class="ig-bk__q">Who is the appointment for?</legend>
        <p class="ig-bk__sub">Your first visit is a no-cost consultation with our orthodontic team.</p>
        <div class="ig-bk__opts">
<?php foreach ($bk['patients'] as $key => [$label, $path]): ?>
          <label class="ig-bk__opt"><input type="radio" name="patient" value="<?= e($key) ?>" data-label="<?= e($label) ?>" required><?= $icon($path) ?><span><?= e($label) ?></span></label>
<?php endforeach; ?>
        </div>
        <button class="ig-bk__next" type="button" data-bk-next disabled>Continue</button>
      </fieldset>

      <fieldset class="ig-bk__pane" data-step="2" data-auto>
        <legend class="ig-bk__q">What are you interested in?</legend>
        <p class="ig-bk__sub">Not sure yet? That is exactly what the consultation is for.</p>
        <div class="ig-bk__opts ig-bk__opts--2">
<?php foreach ($bk['treatments'] as $key => [$label, $path]): ?>
          <label class="ig-bk__opt"><input type="radio" name="treatment" value="<?= e($key) ?>" data-label="<?= e($label) ?>" required><?= $icon($path) ?><span><?= e($label) ?></span></label>
<?php endforeach; ?>
        </div>
        <button class="ig-bk__next" type="button" data-bk-next disabled>Continue</button>
      </fieldset>

      <fieldset class="ig-bk__pane" data-step="3" data-auto>
        <legend class="ig-bk__q">Which office works best?</legend>
        <p class="ig-bk__sub">Choose the Ignite Orthodontics office you would like to visit.</p>
        <div class="ig-bk__opts ig-bk__opts--2">
<?php foreach (locations() as $l): ?>
          <label class="ig-bk__opt"><input type="radio" name="office" value="<?= e($l['slug']) ?>" data-label="<?= e($l['name']) ?> office" data-phone="<?= e($l['phone']) ?>" data-tel="<?= e($l['tel']) ?>" required><?= $icon($pin) ?><span><?= e($l['name']) ?><small><?= e($l['street']) ?>, <?= e($l['city']) ?></small></span></label>
<?php endforeach; ?>
        </div>
        <button class="ig-bk__next" type="button" data-bk-next disabled>Continue</button>
      </fieldset>

      <fieldset class="ig-bk__pane" data-step="4" data-auto>
        <legend class="ig-bk__q">When would you like to come in?</legend>
        <p class="ig-bk__sub">Pick a preferred day and time. We will confirm the exact appointment with you.</p>

        <p class="ig-bk__label" id="igBkDay">Preferred day</p>
        <div class="ig-bk__days" role="radiogroup" aria-labelledby="igBkDay">
          <label class="ig-bk__opt ig-bk__opt--chip ig-bk__opt--wide"><input type="radio" name="date" value="first" data-label="First available" required><b>First available</b><span>Soonest opening</span></label>
<?php for ($i = 0; $i < $bk['days_ahead']; $i++): $d = $start->modify("+{$i} days"); ?>
          <label class="ig-bk__opt ig-bk__opt--chip"><input type="radio" name="date" value="<?= $d->format('Y-m-d') ?>" data-label="<?= $d->format('D, M j') ?>" required><span><?= $d->format('D') ?></span><b><?= $d->format('M j') ?></b></label>
<?php endfor; ?>
        </div>

        <p class="ig-bk__label" id="igBkTime">Preferred time</p>
        <div class="ig-bk__times" role="radiogroup" aria-labelledby="igBkTime">
<?php foreach ($bk['times'] as $key => $label): ?>
          <label class="ig-bk__opt ig-bk__opt--chip"><input type="radio" name="time" value="<?= e($key) ?>" data-label="<?= e($label) ?>" required><b><?= e($label) ?></b></label>
<?php endforeach; ?>
        </div>
        <button class="ig-bk__next" type="button" data-bk-next disabled>Continue</button>
      </fieldset>

      <fieldset class="ig-bk__pane" data-step="5">
        <legend class="ig-bk__q">How can we reach you?</legend>
        <p class="ig-bk__sub">We will call or text to confirm your appointment time.</p>
        <div class="ig-bk__summary" data-bk-summary></div>

        <div class="ig-bk__grid">
          <label class="ig-bk__field"><span>First name <i aria-hidden="true">*</i></span><input type="text" name="first_name" autocomplete="given-name" maxlength="60" required></label>
          <label class="ig-bk__field"><span>Last name <i aria-hidden="true">*</i></span><input type="text" name="last_name" autocomplete="family-name" maxlength="60" required></label>
          <label class="ig-bk__field"><span>Phone <i aria-hidden="true">*</i></span><input type="tel" name="phone" autocomplete="tel" inputmode="tel" maxlength="30" required></label>
          <label class="ig-bk__field"><span>Email <i aria-hidden="true">*</i></span><input type="email" name="email" autocomplete="email" maxlength="160" required></label>
          <label class="ig-bk__field ig-bk__field--full"><span>Anything we should know? <em>(optional)</em></span><textarea name="notes" rows="3" maxlength="1000"></textarea></label>
        </div>

        <label class="ig-bk__consent">
          <input type="checkbox" name="consent" value="1" required>
          <span>Checking this box is my signature to agree to receive text messages and calls about my healthcare and for marketing purposes, including autodialed, from Ignite Orthodontics at the number provided. Messages may occasionally be sent outside of normal business hours, including weekends. I understand that this consent is not a condition of purchasing any goods or services, I can opt out at any time, message/data rates may apply per my phone plan, and opting-in includes acceptance of our <a href="/privacy-policy/" target="_blank" rel="noopener">Privacy Policy</a> and <a href="/terms-and-conditions/" target="_blank" rel="noopener">Terms and Conditions</a>.</span>
        </label>

        <p class="ig-bk__error" data-bk-error role="alert" hidden></p>
        <button class="ig-bk__next ig-bk__submit" type="submit" data-bk-submit>Request My Appointment</button>
        <p class="ig-bk__note">No cost and no obligation.</p>
      </fieldset>
    </form>
  </div>
</div>
