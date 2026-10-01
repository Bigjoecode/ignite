<?php
// Shown after a photo is sent (/see-your-smile/sent/?ref=...).
// Vars: $request (the row) or null, $photoUrl, $previewUrl (one-time links, may be '').
?>
<div class="bk vc-done sm-done">
  <div class="bk-wrap">
<?php if ($request): ?>
    <span class="vc-done__tick" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5.5 12.5l4 4L18.5 7.5"/></svg></span>
    <h1>Thanks, <?= e($request['first_name']) ?></h1>

<?php if ($previewUrl !== ''): ?>
    <p class="vc-done__when">Here is what a straighter smile could look like</p>
    <div class="sm-compare">
      <figure><img src="<?= e($photoUrl) ?>" alt="Your photo"><figcaption>Your photo</figcaption></figure>
      <figure><img src="<?= e($previewUrl) ?>" alt="A preview of a straighter smile"><figcaption>Could look like</figcaption></figure>
    </div>
    <p class="sm-disclaim"><b>This is an illustration only.</b> It is not a treatment plan, a diagnosis or a promise of results. What is actually possible depends on your teeth and bite, which is what the consultation is for.</p>
<?php else: ?>
    <p class="vc-done__when">Your photo is with our team</p>
    <p>One of our orthodontic team will look at it and come back to you at <b><?= e($request['email']) ?></b> with what treatment would involve&mdash;which options suit, roughly how long, and what it would cost.</p>
<?php endif; ?>

    <div class="vc-done__actions">
      <a class="vc-done__join" href="/virtual-consultation/">Book a free video consultation</a>
      <a class="vc-done__ics" href="<?= e(booking_url($request['office'])) ?>">Book an office visit</a>
    </div>
    <p class="vc-done__help">Questions in the meantime? Call us on <a href="tel:<?= e(cfg('phone_tel')) ?>"><?= e(cfg('phone')) ?></a>.</p>
<?php else: ?>
    <h1>That link has expired</h1>
    <p>If you sent us a photo, it is safely with our team and they will be in touch. If you are not sure it arrived, call us on <a href="tel:<?= e(cfg('phone_tel')) ?>"><?= e(cfg('phone')) ?></a>.</p>
    <a class="vc-done__join" href="/see-your-smile/">Send a photo</a>
<?php endif; ?>
  </div>
</div>
