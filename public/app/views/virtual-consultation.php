<?php
// Virtual consultation page (/virtual-consultation/). Two modes:
//  - the calendar is connected: real 15-minute slots, booked on the spot with a Meet link
//  - it is not, or Google could not be reached: the visitor asks for a time and we confirm
// Posts to /book-virtual (app/book-virtual.php). Vars: $slots (day => slots, or null), $office.
$bk     = require APP . '/data/booking.php';
$icon   = static fn(string $d): string => '<span class="ig-bk__ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="' . $d . '"/></svg></span>';
$check  = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5.5 12.5l4 4L18.5 7.5"/></svg>';
$live   = is_array($slots) && $slots !== [];
$days   = $live ? array_keys($slots) : [];
$phone  = $office['phone'] ?? cfg('phone');
$tel    = $office['tel'] ?? cfg('phone_tel');
$mins   = consult_settings()['slot_minutes'];
?>
<div class="bk vc">

  <section class="bk-hero">
    <div class="bk-wrap">
      <span class="bk-kicker">Virtual Consultation</span>
      <h1>Meet Your Orthodontist <em>From Home</em></h1>
      <p>A <?= (int) $mins ?>-minute video call with our team, at no cost. Show us your smile, ask your questions and find out what treatment would involve&mdash;before you come in.</p>
    </div>
  </section>

  <div class="bk-wrap bk-grid">
    <div class="bk-card" data-vc-root>

<?php if ($live): ?>
      <form class="ig-bk__body" action="/book-virtual" method="post" novalidate data-vc-form>
        <input type="text" name="website" value="" class="ig-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
        <input type="hidden" name="source" value="/virtual-consultation/">

        <fieldset class="ig-bk__pane" data-vc-step>
          <legend class="ig-bk__q">Pick a time that suits you</legend>
          <p class="ig-bk__sub">These are the times our team is free. Eastern time.</p>

          <div class="vc-days" role="tablist" aria-label="Days with free times">
<?php foreach ($days as $i => $ymd): $d = new DateTimeImmutable($ymd); ?>
            <button type="button" class="vc-day<?= $i === 0 ? ' is-on' : '' ?>" role="tab" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" data-vc-day="<?= e($ymd) ?>">
              <span><?= e(consult_day_label($ymd) === 'Today' || consult_day_label($ymd) === 'Tomorrow' ? consult_day_label($ymd) : $d->format('D')) ?></span>
              <b><?= e($d->format('M j')) ?></b>
              <i><?= count($slots[$ymd]) ?> free</i>
            </button>
<?php endforeach; ?>
          </div>

<?php foreach ($slots as $ymd => $times): ?>
          <div class="vc-times" data-vc-times="<?= e($ymd) ?>"<?= $ymd === $days[0] ? '' : ' hidden' ?>>
            <p class="ig-bk__label"><?= e(consult_day_label($ymd)) ?></p>
            <div class="vc-times__grid" role="radiogroup" aria-label="Free times on <?= e(consult_day_label($ymd)) ?>">
<?php foreach ($times as $slot): ?>
              <label class="vc-slot">
                <input type="radio" name="slot" value="<?= e($slot['start']) ?>" data-label="<?= e(consult_day_label($ymd) . ', ' . $slot['label']) ?>" required>
                <b><?= e($slot['label']) ?></b>
              </label>
<?php endforeach; ?>
            </div>
          </div>
<?php endforeach; ?>

          <button class="ig-bk__next" type="button" data-vc-next disabled>Continue</button>
        </fieldset>

        <fieldset class="ig-bk__pane" data-vc-step hidden>
          <legend class="ig-bk__q">Where should we send the link?</legend>
          <p class="ig-bk__sub">You will get a calendar invite with a Google Meet link straight away.</p>
          <div class="ig-bk__summary" data-vc-summary></div>

          <div class="ig-bk__grid">
            <label class="ig-bk__field"><span>First name <i aria-hidden="true">*</i></span><input type="text" name="first_name" autocomplete="given-name" maxlength="60" required></label>
            <label class="ig-bk__field"><span>Last name <i aria-hidden="true">*</i></span><input type="text" name="last_name" autocomplete="family-name" maxlength="60" required></label>
            <label class="ig-bk__field"><span>Phone <i aria-hidden="true">*</i></span><input type="tel" name="phone" autocomplete="tel" inputmode="tel" maxlength="30" required></label>
            <label class="ig-bk__field"><span>Email <i aria-hidden="true">*</i></span><input type="email" name="email" autocomplete="email" maxlength="160" required></label>
            <label class="ig-bk__field"><span>Who is it for?</span>
              <select name="patient">
<?php foreach ($bk['patients'] as $key => [$label]): ?>
                <option value="<?= e($key) ?>"<?= $key === 'self' ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
              </select>
            </label>
            <label class="ig-bk__field"><span>Nearest office <i aria-hidden="true">*</i></span>
              <select name="office" required>
<?php foreach (locations() as $slug => $each): ?>
                <option value="<?= e($slug) ?>"<?= ($office['slug'] ?? '') === $slug ? ' selected' : '' ?>><?= e($each['name']) ?></option>
<?php endforeach; ?>
              </select>
            </label>
            <label class="ig-bk__field ig-bk__field--full"><span>What would you like to talk about? <em>(optional)</em></span><textarea name="notes" rows="3" maxlength="1000"></textarea></label>
          </div>

          <label class="ig-bk__consent">
            <input type="checkbox" name="consent" value="1" required>
            <span>Checking this box is my signature to agree to receive text messages and calls about my healthcare and for marketing purposes, including autodialed, from Ignite Orthodontics at the number provided. Messages may occasionally be sent outside of normal business hours, including weekends. I understand that this consent is not a condition of purchasing any goods or services, I can opt out at any time, message/data rates may apply per my phone plan, and opting-in includes acceptance of our <a href="/privacy-policy/" target="_blank" rel="noopener">Privacy Policy</a> and <a href="/terms-and-conditions/" target="_blank" rel="noopener">Terms and Conditions</a>.</span>
          </label>

          <p class="ig-bk__error" data-vc-error role="alert" hidden></p>
          <button class="ig-bk__next ig-bk__submit" type="submit" data-vc-submit>Confirm My Video Visit</button>
          <p class="ig-bk__note">No cost and no obligation.</p>
        </fieldset>
      </form>

<?php else: ?>
      <div class="ig-bk__body vc-ask">
        <h2 class="ig-bk__q">Ask for a video visit</h2>
        <p class="ig-bk__sub">Tell us when suits you and we will confirm your video visit and send the link.</p>
        <ul class="bk-checks vc-ask__list">
          <li><?= $check ?>A <?= (int) $mins ?>-minute call with our orthodontic team</li>
          <li><?= $check ?>No cost and no obligation</li>
          <li><?= $check ?>Nothing to install&mdash;the link opens in your browser</li>
        </ul>
        <a class="ig-bk__next ig-bk__submit vc-ask__btn" href="<?= e(booking_url($office['slug'] ?? '')) ?>">Request My Visit</a>
        <p class="ig-bk__note">Or call us on <a href="tel:<?= e($tel) ?>"><?= e($phone) ?></a> and we will arrange it.</p>
      </div>
<?php endif; ?>

    </div>

    <aside class="bk-side">
      <div class="bk-box">
        <span class="bk-box__label">How it works</span>
        <ol class="vc-steps">
          <li><b>Pick a time</b><span>Choose any free <?= (int) $mins ?>-minute slot.</span></li>
          <li><b>Get the link</b><span>A calendar invite with a Google Meet link arrives by email.</span></li>
          <li><b>Join from anywhere</b><span>Open the link on your phone or computer at that time.</span></li>
        </ol>
      </div>

      <div class="bk-box">
        <span class="bk-box__label">On the call</span>
        <ul class="bk-checks">
          <li><?= $check ?>We look at your smile and what is bothering you</li>
          <li><?= $check ?>You hear which treatments would suit</li>
          <li><?= $check ?>We explain costs, insurance and payment plans</li>
          <li><?= $check ?>No pressure to book anything</li>
        </ul>
      </div>

      <div class="bk-box bk-call">
        <span class="bk-box__label">Prefer to come in?</span>
        <a class="bk-call__btn" href="<?= e(booking_url($office['slug'] ?? '')) ?>">Book an office visit</a>
      </div>
    </aside>
  </div>
</div>
