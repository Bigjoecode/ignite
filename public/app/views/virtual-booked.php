<?php
// Shown straight after a video visit is booked (/virtual-consultation/booked/?ref=...).
// Vars: $booking (the row) or null when the reference is not one we know.
$when = $booking && $booking['start_at']
    ? (new DateTimeImmutable($booking['start_at']))->setTimezone(new DateTimeZone(CONSULT_ZONE))
    : null;
?>
<div class="bk vc-done">
  <div class="bk-wrap">
<?php if ($when): ?>
    <span class="vc-done__tick" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5.5 12.5l4 4L18.5 7.5"/></svg></span>
    <h1>You&rsquo;re booked, <?= e($booking['first_name']) ?></h1>
    <p class="vc-done__when"><?= e($when->format('l, F j')) ?> at <?= e(ltrim($when->format('g:i a'), '0')) ?> <span>Eastern time</span></p>
    <p>We have emailed <b><?= e($booking['email']) ?></b> a calendar invite with your Google Meet link. Open it at that time from your phone or computer&mdash;there is nothing to install.</p>
<?php if ($booking['meet_url']): ?>
    <a class="vc-done__join" href="<?= e($booking['meet_url']) ?>">Your Google Meet link</a>
    <p class="vc-done__note">The same link is in your invite, so you do not need to keep this page.</p>
<?php endif; ?>
    <p class="vc-done__help">Need to change it? Call us on <a href="tel:<?= e(cfg('phone_tel')) ?>"><?= e(cfg('phone')) ?></a>.</p>
<?php else: ?>
    <h1>That link has expired</h1>
    <p>Your appointment is still booked if you received the invite. If you are not sure, call us on <a href="tel:<?= e(cfg('phone_tel')) ?>"><?= e(cfg('phone')) ?></a> and we will check.</p>
    <a class="vc-done__join" href="/virtual-consultation/">Book a video visit</a>
<?php endif; ?>
  </div>
</div>
