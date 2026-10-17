<?php
// /meet-the-doctor/ — a page built for the orthodontist rather than the generic text
// template, which repeated his name and photo twice over and left the hero half empty.
//
// His name, credentials, photo, bio and qualifications all come from cfg('doctor_*') in
// bootstrap.php, the same source the profile block on the office pages reads, so a
// correction there reaches both. The treatment cards come from the CMS, so a new
// treatment page appears here on its own.
//
// Vars: $p (the entry in data/pages.php, for the title and description).
$doc = doctor_profile();
$steps = [
    ['Your consultation', 'An examination and a conversation about what you want from your smile. It costs nothing and there is no obligation.'],
    ['Your options, in full', 'You are shown what is possible, how long each approach takes and what it costs, before you decide anything.'],
    ['A plan written for you', 'Your treatment plan is built around your case and your life, not picked off a list.'],
    ['Seen through to the end', 'Adjustment visits are built into the plan, and retainers are fitted once active treatment finishes.'],
];
?>
<div class="dr">

  <section class="dr-hero">
    <div class="dr-wrap dr-hero__grid">
      <div class="dr-hero__copy">
        <span class="dr-kicker">Your Care Team</span>
        <h1>Meet <em><?= e($doc['name']) ?></em></h1>
<?php if ($doc['credential'] !== ''): ?>
        <p class="dr-hero__cred"><?= e($doc['credential']) ?></p>
<?php endif; ?>
        <p class="dr-hero__lead">Your treatment is planned and followed by an orthodontist, not handed off. Dr. Alqurashi sees patients across our <?= count(locations()) ?> Michigan offices.</p>
        <div class="dr-btns">
          <a class="dr-btn" href="<?= e(booking_url()) ?>">Schedule a Consultation</a>
          <a class="dr-btn dr-btn--ghost" href="/locations/">See our offices</a>
        </div>
      </div>
<?php if ($doc): ?>
      <figure class="dr-hero__media">
        <img src="<?= e(asset('img/' . $doc['photo'])) ?>" alt="<?= e($doc['name']) ?>, orthodontist at <?= e(cfg('site_name')) ?>"
             width="<?= (int) $doc['photo_w'] ?>" height="<?= (int) $doc['photo_h'] ?>" fetchpriority="high" decoding="async">
      </figure>
<?php endif; ?>
    </div>
  </section>

<?php if ($doc['badges']): ?>
  <section class="dr-creds">
    <div class="dr-wrap">
      <ul class="dr-creds__grid">
<?php foreach ($doc['badges'] as $badge): ?>
        <li>
          <span class="dr-creds__mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5.5 12.5l4 4L18.5 7.5"/></svg></span>
          <?= e($badge) ?>
        </li>
<?php endforeach; ?>
      </ul>
    </div>
  </section>
<?php endif; ?>

<?php if ($doc['bio']): ?>
  <section class="dr-sec">
    <div class="dr-wrap dr-about">
      <div class="dr-about__head">
        <span class="dr-kicker">About</span>
        <h2>Training behind the<br><em>treatment plan</em></h2>
      </div>
      <div class="dr-about__body">
<?php foreach ($doc['bio'] as $paragraph): ?>
        <p><?= e($paragraph) ?></p>
<?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php $treatments = service_menu(); ?>
<?php if ($treatments): ?>
  <section class="dr-sec dr-sec--tint">
    <div class="dr-wrap">
      <div class="dr-head">
        <span class="dr-kicker">What he treats</span>
        <h2>Treatments <em>he provides</em></h2>
        <p>Children, teens and adults, with the approach that suits the case.</p>
      </div>
      <div class="dr-cards">
<?php foreach ($treatments as [$href, $label, $text]): ?>
        <a class="dr-card" href="<?= e($href) ?>">
          <b><?= e($label) ?></b>
<?php if ($text !== ''): ?>
          <span><?= e($text) ?></span>
<?php endif; ?>
          <i aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></i>
        </a>
<?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

  <section class="dr-sec">
    <div class="dr-wrap">
      <div class="dr-head">
        <span class="dr-kicker">Your visit</span>
        <h2>What that means <em>for you</em></h2>
        <p>Whichever office you choose, your care follows the same pattern.</p>
      </div>
      <ol class="dr-steps">
<?php foreach ($steps as $i => [$heading, $text]): ?>
        <li>
          <span class="dr-steps__n"><?= $i + 1 ?></span>
          <b><?= e($heading) ?></b>
          <span class="dr-steps__t"><?= e($text) ?></span>
        </li>
<?php endforeach; ?>
      </ol>
    </div>
  </section>

  <section class="dr-sec dr-sec--tint">
    <div class="dr-wrap dr-team">
      <div>
        <span class="dr-kicker">Not only the clinical side</span>
        <h2>The team <em>around him</em></h2>
        <p>Our treatment coordinators and clinical staff at each office handle appointments, insurance benefits and payment plans, so the questions that are not clinical get answered by someone whose job that is.</p>
      </div>
      <div class="dr-team__links">
        <a href="/patient-info/"><b>Patient Info</b><span>What happens at your first visit and after.</span></a>
        <a href="/insurance-financing/"><b>Insurance &amp; Financing</b><span>How benefits and payment plans work.</span></a>
        <a href="/locations/"><b>Our Locations</b><span>Find the office nearest you.</span></a>
      </div>
    </div>
  </section>

<?php require APP . '/views/partials/consult-cta.php'; ?>

</div>
