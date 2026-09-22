<?php
// Booking page (/booking/): the consultation request, one question at a time.
// Every booking button on the site links here; on an office page the link is
// /booking/?office=slug, and then that office is fixed and its step is skipped.
// Posts to /book (app/book.php), then goes to /thank-you/. Choices: app/data/booking.php.
// Vars: $office (the fixed office, or null).
$bk    = require APP . '/data/booking.php';
$icon  = static fn(string $d): string => '<span class="ig-bk__ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="' . $d . '"/></svg></span>';
$pin   = 'M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7z M12 11.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z';
$check = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5.5 12.5l4 4L18.5 7.5"/></svg>';
$start = new DateTimeImmutable('tomorrow');
$total = $office ? 4 : 5;
$phone = $office['phone'] ?? cfg('phone');
$tel   = $office['tel'] ?? cfg('phone_tel');
?>
<div class="bk">

  <section class="bk-hero">
    <div class="bk-wrap">
      <span class="bk-kicker">Book a No-Cost Consultation</span>
<?php if ($office): ?>
      <h1>Book Your Visit at <em>Ignite Orthodontics <?= e($office['name']) ?></em></h1>
<?php else: ?>
      <h1>Request Your <em>No-Cost Consultation</em></h1>
<?php endif; ?>
      <p>Answer a few quick questions&mdash;it takes about a minute. We&rsquo;ll call or text you to confirm your appointment time.</p>
    </div>
  </section>

  <div class="bk-wrap bk-grid">

    <div class="bk-card" data-bk-root>
      <div class="ig-bk__head">
        <button class="ig-bk__back" type="button" data-bk-back aria-label="Previous step" disabled>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
        </button>
        <div class="ig-bk__progress" aria-hidden="true"><b data-bk-bar></b></div>
        <p class="ig-bk__count" data-bk-count aria-live="polite">Step 1 of <?= $total ?></p>
      </div>

      <form class="ig-bk__body" action="/book" method="post" novalidate data-bk-form>
        <input type="text" name="website" value="" class="ig-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
<?php if ($office): ?>
        <input type="hidden" name="office" value="<?= e($office['slug']) ?>" data-label="<?= e($office['name']) ?> office" data-phone="<?= e($office['phone']) ?>" data-tel="<?= e($office['tel']) ?>">
<?php endif; ?>

        <fieldset class="ig-bk__pane" data-step data-auto>
          <legend class="ig-bk__q">Who is the appointment for?</legend>
          <p class="ig-bk__sub">Your first visit is a no-cost consultation with our orthodontic team.</p>
          <div class="ig-bk__opts">
<?php foreach ($bk['patients'] as $key => [$label, $path]): ?>
            <label class="ig-bk__opt"><input type="radio" name="patient" value="<?= e($key) ?>" data-label="<?= e($label) ?>" required><?= $icon($path) ?><span><?= e($label) ?></span></label>
<?php endforeach; ?>
          </div>
          <button class="ig-bk__next" type="button" data-bk-next disabled>Continue</button>
        </fieldset>

        <fieldset class="ig-bk__pane" data-step data-auto hidden>
          <legend class="ig-bk__q">What are you interested in?</legend>
          <p class="ig-bk__sub">Not sure yet? That is exactly what the consultation is for.</p>
          <div class="ig-bk__opts ig-bk__opts--2">
<?php foreach ($bk['treatments'] as $key => [$label, $path]): ?>
            <label class="ig-bk__opt"><input type="radio" name="treatment" value="<?= e($key) ?>" data-label="<?= e($label) ?>" required><?= $icon($path) ?><span><?= e($label) ?></span></label>
<?php endforeach; ?>
          </div>
          <button class="ig-bk__next" type="button" data-bk-next disabled>Continue</button>
        </fieldset>

<?php if (!$office): ?>
        <fieldset class="ig-bk__pane" data-step hidden>
          <legend class="ig-bk__q">Which office works best?</legend>
          <p class="ig-bk__sub">Search by city, zip code or office name.</p>
          <input type="hidden" name="office" value="" data-required data-label="" data-phone="" data-tel="">
<?php
    // what the search box matches against (assets/js/booking.js)
    $offices = array_values(array_map(static fn(array $l): array => [
        'slug' => $l['slug'], 'name' => $l['name'], 'street' => $l['street'], 'city' => $l['city'],
        'state' => $l['state'], 'zip' => $l['zip'], 'address' => $l['address_full'], 'phone' => $l['phone'], 'tel' => $l['tel'],
    ], locations()));
?>
          <div class="bk-find" data-bk-find data-offices="<?= e(json_encode($offices, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>">
            <div class="bk-find__field">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15z M21 21l-5.2-5.2"/></svg>
              <input type="text" id="bkFind" role="combobox" aria-label="Search for an office by city, zip code or name" aria-autocomplete="list" aria-expanded="false" aria-controls="bkFindList" autocomplete="off" spellcheck="false" placeholder="e.g. Sterling Heights or 48313" data-bk-find-input>
            </div>
            <ul class="bk-find__list" id="bkFindList" role="listbox" aria-label="Matching offices" hidden data-bk-find-list></ul>
            <p class="bk-find__msg" data-bk-find-msg aria-live="polite"></p>
          </div>
          <div class="bk-picked" data-bk-picked hidden>
            <?= $icon($pin) ?>
            <div><b data-bk-picked-name></b><span data-bk-picked-addr></span></div>
            <button type="button" class="bk-picked__change" data-bk-picked-change>Change</button>
          </div>
          <button class="ig-bk__next" type="button" data-bk-next disabled>Continue</button>
        </fieldset>
<?php endif; ?>

        <fieldset class="ig-bk__pane" data-step data-auto hidden>
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

        <fieldset class="ig-bk__pane" data-step hidden>
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

    <aside class="bk-side">
<?php if ($office): ?>
      <div class="bk-box bk-office">
        <span class="bk-box__label">Your office</span>
        <h2>Ignite Orthodontics <?= e($office['name']) ?></h2>
        <p class="bk-office__addr"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= $pin ?>"/></svg><?= e($office['address_full']) ?></p>
<?php if ($office['hours']): ?>
        <ul class="bk-office__hours">
<?php foreach ($office['hours'] as $line): ?>
          <li><?= e($line) ?></li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
        <a class="bk-office__change" href="/booking/">Booking for a different office?</a>
      </div>
<?php endif; ?>

      <div class="bk-box">
        <span class="bk-box__label">At your consultation</span>
        <ul class="bk-checks">
          <li><?= $check ?>A complimentary orthodontic evaluation</li>
          <li><?= $check ?>Clear treatment options and estimated costs</li>
          <li><?= $check ?>Insurance and flexible payment options explained</li>
          <li><?= $check ?>No referral needed &mdash; new patients welcome</li>
        </ul>
      </div>

      <div class="bk-box bk-call">
        <span class="bk-box__label">Prefer to talk?</span>
        <a class="bk-call__btn" href="tel:<?= e($tel) ?>">Call <?= e($phone) ?></a>
      </div>
    </aside>
  </div>
</div>
