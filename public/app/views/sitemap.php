<?php
// Sitemap for people (/sitemap/), built from the same places sitemap.xml reads:
// the CMS pages, the static pages and the blog. Nothing is listed by hand, so a
// new treatment page, office or post appears here as soon as it is published.
// Vars: $groups — [heading, note, [[href, label, note], ...]]
?>
<div class="ig-home">

  <section class="pg-hero">
    <div class="ig-wrap">
      <span class="ig-kicker">Sitemap</span>
      <h1>Every Page On <em>Ignite Orthodontics.</em></h1>
      <p class="pg-hero__lead">A plain list of everything on this website, grouped the way the site is organised. If you cannot find what you are looking for, request a consultation and our team will help.</p>
      <div class="ig-btns">
        <a class="ig-btn ig-btn--primary" href="<?= e(booking_url()) ?>">Schedule a Consultation</a>
      </div>
    </div>
  </section>

  <section class="ig-sec">
    <div class="ig-wrap">
      <div class="sm-grid">
<?php foreach ($groups as $i => [$heading, $note, $links]): ?>
<?php if (!$links) continue; ?>
        <nav class="sm-group" aria-labelledby="sm-<?= (int) $i ?>">
          <h2 id="sm-<?= (int) $i ?>"><?= e($heading) ?><span><?= count($links) ?></span></h2>
<?php if ($note !== ''): ?>
          <p class="sm-group__note"><?= e($note) ?></p>
<?php endif; ?>
          <ul>
<?php foreach ($links as [$href, $label, $sub]): ?>
            <li>
              <a href="<?= e($href) ?>"><?= e($label) ?></a>
<?php if ($sub !== ''): ?>
              <span><?= e($sub) ?></span>
<?php endif; ?>
            </li>
<?php endforeach; ?>
          </ul>
        </nav>
<?php endforeach; ?>
      </div>
    </div>
  </section>

<?php require APP . '/views/partials/consult-cta.php'; ?>

</div>
