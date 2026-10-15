<?php
// The orthodontist, shown on the offices he sees patients at and on their landing pages.
// Everything here comes from cfg('doctor_*') in bootstrap.php, so there is one place to
// correct a detail. Nothing renders unless a name and a photo are set.
// Optional: $doctorTone ('tint' for the shaded background), $doctorHeading.
$doc = doctor_profile();
if (!$doc) return;
$doctorTone    = $doctorTone ?? '';
$doctorHeading = $doctorHeading ?? 'Meet Your Orthodontist';
?>
  <section class="ig-sec<?= $doctorTone === 'tint' ? ' ig-sec--tint' : '' ?> ig-doc">
    <div class="ig-wrap">
      <div class="ig-doc__grid">

        <figure class="ig-doc__media">
          <img src="<?= e(asset('img/' . $doc['photo'])) ?>" alt="<?= e($doc['name']) ?>, orthodontist at <?= e(cfg('site_name')) ?>"
               width="<?= (int) $doc['photo_w'] ?>" height="<?= (int) $doc['photo_h'] ?>" loading="lazy" decoding="async">
        </figure>

        <div class="ig-doc__copy">
          <span class="ig-kicker"><?= e($doctorHeading) ?></span>
          <h2 class="ig-h2"><?= e($doc['name']) ?></h2>
<?php if ($doc['credential'] !== ''): ?>
          <p class="ig-doc__cred"><?= e($doc['credential']) ?></p>
<?php endif; ?>
<?php foreach ($doc['bio'] as $paragraph): ?>
          <p><?= e($paragraph) ?></p>
<?php endforeach; ?>
<?php if ($doc['badges']): ?>
          <ul class="ig-doc__badges">
<?php foreach ($doc['badges'] as $badge): ?>
            <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5.5 12.5l4 4L18.5 7.5"/></svg><?= e($badge) ?></li>
<?php endforeach; ?>
          </ul>
<?php endif; ?>
          <div class="ig-btns">
            <a class="ig-btn ig-btn--primary" href="<?= e(booking_url()) ?>">Schedule a Consultation</a>
<?php if (($GLOBALS['ig_doctor_page'] ?? true)): ?>
            <a class="ig-btn ig-btn--out" href="/meet-the-doctor/">Read his full profile</a>
<?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </section>
