<?php
// Virtual consultation page (/virtual-consultation/): pick a real time, confirm it,
// leave your details, done — the appointment is booked and the video link is sent.
// Posts to /book-virtual (app/book-virtual.php). Vars: $slots (day => slots, or null), $office.
$bk     = require APP . '/data/booking.php';
$check  = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5.5 12.5l4 4L18.5 7.5"/></svg>';
$s      = consult_settings();
$mins   = (int) $s['slot_minutes'];
$open   = is_array($slots) ? array_filter($slots) : [];   // only days with times are worth listing
$live   = $open !== [];
$first  = $live ? array_key_first($open) : '';
$phone  = $office['phone'] ?? cfg('phone');
$tel    = $office['tel'] ?? cfg('phone_tel');
$shown  = 8;    // times per day before "show all"
?>
<div class="bk vc">

  <section class="bk-hero">
    <div class="bk-wrap">
      <span class="bk-kicker">Free Virtual Consultation</span>
      <h1>Meet Your Orthodontist <em>From Home</em></h1>
      <p>A <?= $mins ?>-minute video call with our team, at no cost. Show us your smile, ask your questions and find out what treatment would involve&mdash;before you come in.</p>
    </div>
  </section>

  <div class="bk-wrap bk-grid">
    <div class="bk-card" data-vc-root>

<?php if ($live): ?>
      <ol class="vc-steps__bar" data-vc-bar>
        <li class="is-on"><span>1</span>Choose a time</li>
        <li><span>2</span>Your details</li>
      </ol>

      <form class="ig-bk__body" action="/book-virtual" method="post" novalidate data-vc-form>
        <input type="text" name="website" value="" class="ig-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
        <input type="hidden" name="source" value="/virtual-consultation/">

        <fieldset class="ig-bk__pane" data-vc-step>
          <legend class="ig-bk__q">Choose a date &amp; time</legend>
          <p class="ig-bk__sub">
            Showing availability for a free video consultation. All times are Eastern.
<?php if ($first !== '' && consult_day_label($first) !== 'Today'): ?>
            Our next opening is <b><?= e(consult_day_label($first)) ?></b>.
<?php endif; ?>
          </p>

<?php foreach ($open as $ymd => $times): $day = new DateTimeImmutable($ymd); ?>
          <div class="vc-day">
            <h3 class="vc-day__name"><?= e(consult_day_short($ymd)) ?><span><?= e($day->format('j F')) ?></span></h3>
            <div class="vc-day__times" role="radiogroup" aria-label="Times on <?= e(consult_day_label($ymd)) ?>">
<?php foreach ($times as $i => $slot): ?>
              <label class="vc-slot<?= $i >= $shown ? ' vc-slot--more' : '' ?>"<?= $i >= $shown ? ' hidden' : '' ?>>
                <input type="radio" name="slot" value="<?= e($slot['start']) ?>" data-label="<?= e(consult_day_label($ymd) . ', ' . $slot['label']) ?>" data-day="<?= e($day->format('l jS F Y')) ?>" data-time="<?= e($slot['label']) ?>" required>
                <b><?= e($slot['label']) ?></b>
              </label>
<?php endforeach; ?>
<?php if (count($times) > $shown): ?>
              <button type="button" class="vc-more" data-vc-more>+ show all <?= count($times) ?></button>
<?php endif; ?>
            </div>
          </div>
<?php endforeach; ?>

          <button class="ig-bk__next vc-continue" type="button" data-vc-next disabled>Continue</button>
        </fieldset>

        <fieldset class="ig-bk__pane" data-vc-step hidden>
          <legend class="ig-bk__q">Your details</legend>
          <p class="ig-bk__sub">We will email you the video link and a calendar invite as soon as you are booked.</p>

          <div class="vc-picked" data-vc-picked>
            <span class="vc-picked__ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 7v5l3 2"/><circle cx="12" cy="12" r="9"/></svg></span>
            <div>
              <b data-vc-picked-time></b>
              <span data-vc-picked-day></span>
              <span><?= $mins ?>-minute video consultation</span>
            </div>
            <button type="button" class="vc-picked__change" data-vc-back>Change</button>
          </div>

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
<?php if (!isset($office['slug'])): ?>
                <option value="" selected disabled>Choose your closest office</option>
<?php endif; ?>
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
          <li><?= $check ?>A <?= $mins ?>-minute call with our orthodontic team</li>
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
        <ol class="vc-how">
          <li><b>Pick a time</b><span>Any free <?= $mins ?>-minute slot that suits you.</span></li>
          <li><b>Get the link</b><span>Your video link and calendar invite arrive by email straight away.</span></li>
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

<?php if ($live): ?>
<div class="vc-modal" data-vc-modal hidden>
  <div class="vc-modal__box" role="dialog" aria-modal="true" aria-labelledby="vcModalTitle">
    <button type="button" class="vc-modal__close" data-vc-modal-close aria-label="Close">&times;</button>
    <h2 id="vcModalTitle" class="vc-modal__day" data-vc-modal-day></h2>
    <ul class="vc-modal__rows">
      <li>
        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 7v5l3 2"/><circle cx="12" cy="12" r="9"/></svg></span>
        <div><b data-vc-modal-time></b><span>Video consultation (<?= $mins ?> mins)</span></div>
      </li>
      <li>
        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M4 21a8 8 0 0 1 16 0"/></svg></span>
        <div><b>Ignite Orthodontics team</b><span>Board-certified orthodontic care</span></div>
      </li>
      <li>
        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M15 10l4.5-2.5v9L15 14z M3 7h12v10H3z"/></svg></span>
        <div><b>Online, by video</b><span>Free consultation &mdash; no cost, no obligation</span></div>
      </li>
    </ul>
    <button type="button" class="vc-modal__book" data-vc-modal-book>Book this time &rarr;</button>
  </div>
</div>
<?php endif; ?>
