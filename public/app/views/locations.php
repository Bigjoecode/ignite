<div class="ig-home">

  <section class="pg-hero">
    <div class="ig-wrap">
      <span class="ig-kicker">Our Locations</span>
      <h1><?= count(locations()) ?> Offices Across <em>Michigan.</em></h1>
      <p class="pg-hero__lead">Find the Ignite Orthodontics office closest to home, work or school, then book your no-cost consultation.</p>
      <div class="ig-btns">
        <a class="ig-btn ig-btn--primary" href="#ig-consult">Schedule a Consultation</a>
        <a class="ig-btn ig-btn--white" href="tel:<?= e(cfg('phone_tel')) ?>">Call <?= e(cfg('phone')) ?></a>
      </div>
    </div>
  </section>

<?php require APP . '/views/partials/finder.php'; ?>

<?php require APP . '/views/partials/consult-cta.php'; ?>

</div>
