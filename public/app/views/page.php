<div class="ig-home">

  <section class="pg-hero">
    <div class="ig-wrap">
      <span class="ig-kicker"><?= e($p['kicker']) ?></span>
      <h1><?= $p['h1'] /* trusted markup from data/pages.php */ ?></h1>
      <p class="pg-hero__lead"><?= e($p['lead']) ?></p>
      <div class="ig-btns">
        <a class="ig-btn ig-btn--primary" href="#ig-consult">Schedule a Consultation</a>
        <a class="ig-btn ig-btn--white" href="tel:<?= e(cfg('phone_tel')) ?>">Call <?= e(cfg('phone')) ?></a>
      </div>
    </div>
  </section>

<?php if (!empty($p['sections']) || !empty($p['links']) || !empty($p['links_auto']) || !empty($p['note'])): ?>
  <section class="ig-sec">
    <div class="ig-wrap pg-body">
<?php if (!empty($p['note'])): ?>
      <p class="pg-note"><?= e($p['note']) ?></p>
<?php endif; ?>
<?php foreach ($p['sections'] ?? [] as $i => [$heading, $text]): ?>
      <h2<?= $slug === 'types-of-braces' && $i === 0 ? ' id="metal"' : '' ?>><?= e($heading) ?></h2>
      <p><?= e($text) ?></p>
<?php endforeach; ?>
<?php $links = !empty($p['links_auto']) ? service_menu() : ($p['links'] ?? []); ?>
<?php if ($links): ?>
      <div class="pg-links">
<?php foreach ($links as [$href, $label, $text]): ?>
        <a class="pg-link" href="<?= e($href) ?>"><b><?= e($label) ?></b><span><?= e($text) ?></span></a>
<?php endforeach; ?>
      </div>
<?php endif; ?>
    </div>
  </section>
<?php endif; ?>

<?php if (!empty($p['finder'])) require APP . '/views/partials/finder.php'; ?>

<?php require APP . '/views/partials/consult-cta.php'; ?>

</div>
