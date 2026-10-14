<div class="ig-home">

  <section class="pg-hero">
    <div class="ig-wrap">
      <span class="ig-kicker">Our Locations</span>
      <h1><?= count(locations()) ?> Offices Across <em>Michigan.</em></h1>
      <p class="pg-hero__lead">Find the Ignite Orthodontics office closest to home, work or school, then book your no-cost consultation.</p>
      <div class="ig-btns">
        <a class="ig-btn ig-btn--primary" href="<?= e(booking_url()) ?>">Schedule a Consultation</a>
      </div>
    </div>
  </section>

<?php $finderPhones = true; // the directory is where the numbers belong
require APP . '/views/partials/finder.php'; ?>

<?php require APP . '/views/partials/consult-cta.php'; ?>

</div>
