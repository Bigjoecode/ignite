<?php
// "Find Ignite Orthodontics Near You" — same markup as the home-preview design,
// rendered from the office data. Needs home.css + home.js inside .ig-home.
$locs = locations();
$pin   = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>';
$phone = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.2.4 2.4.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1-9.4 0-17-7.6-17-17 0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2z"/></svg>';
$mail  = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4.2-8 4.8-8-4.8V6l8 4.8L20 6v2.2z"/></svg>';
?>
  <section class="ig-sec ig-loc">
    <div class="ig-wrap">

      <div class="ig-loc__head">
        <span class="ig-kicker">Find Your Nearest Office</span>
        <h2>Find Ignite Orthodontics <em>Near You.</em></h2>
        <p>Looking for an orthodontist close to home, work, or school? Find the Ignite Orthodontics location that&rsquo;s most convenient for you.</p>
      </div>

      <div class="ig-loc__states">
        <a class="ig-state" href="/locations/">
          <?= $pin ?>
          Michigan <b>&mdash; <?= count($locs) ?> Locations</b>
        </a>
      </div>

      <div class="ig-loc__search">
        <input id="igLocInput" type="text" placeholder="Enter your city or zip code" aria-label="Enter your city or zip code" autocomplete="postal-code">
        <button class="ig-btn ig-btn--navy" type="button" id="igLocBtn">Find My Nearest Location</button>
      </div>
      <p class="ig-loc__msg" id="igLocMsg" role="status"></p>

      <div class="ig-loc__body">
        <div class="ig-loc__list">
<?php foreach ($locs as $l): ?>
          <article class="ig-office">
            <h3>Ignite Orthodontics &mdash; <?= e($l['name']) ?></h3>
            <p class="ig-office__addr"><?= e($l['street']) ?><br><?= e($l['city']) ?>, MI<?= $l['zip'] !== '' ? ' ' . e($l['zip']) : '' ?></p>
            <div class="ig-office__row">
              <div class="ig-office__contact">
                <a href="tel:<?= e($l['tel']) ?>"><?= $phone ?> <?= e($l['phone']) ?></a>
                <a href="mailto:<?= e($l['email']) ?>"><?= $mail ?> <?= e($l['email']) ?></a>
              </div>
              <div class="ig-office__links">
                <a class="ig-btn" href="https://maps.google.com/?q=<?= urlencode($l['map_q']) ?>" target="_blank" rel="noopener">View on Map</a>
                <a class="ig-btn" href="/locations/<?= e($l['slug']) ?>/">View Location</a>
              </div>
            </div>
          </article>
<?php endforeach; ?>
        </div>

        <div class="ig-loc__map">
          <iframe
            src="https://maps.google.com/maps?q=Michigan&amp;z=7&amp;output=embed"
            title="Map of Ignite Orthodontics locations"
            loading="lazy" referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen></iframe>
        </div>
      </div>
    </div>
  </section>
