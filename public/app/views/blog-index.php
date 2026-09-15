<?php
$all = posts();
$featured = null;
foreach ($all as $candidate) {
    if (!empty($candidate['featured'])) { $featured = $candidate; break; }
}
$featured = $featured ?? (reset($all) ?: null);
$rest   = array_filter($all, static fn(array $p): bool => !$featured || $p['slug'] !== $featured['slug']);
$topics = array_values(array_unique(array_column($all, 'category')));
$arrow  = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
?>
<div class="ig-home bl">

  <section class="bl-hero">
    <div class="ig-wrap">
      <span class="ig-kicker">The Ignite Orthodontics Blog</span>
      <h1>Orthodontic Tips <em>&amp; Advice.</em></h1>
      <p class="bl-hero__lead">Straightforward guidance on braces, clear aligners and caring for your smile, for kids, teens and adults.</p>

<?php if ($all): ?>
      <div class="bl-tools">
        <label class="bl-search">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4.2-4.2"/></svg>
          <input type="search" placeholder="Search articles" aria-label="Search articles" data-bl-search>
        </label>
        <div class="bl-chips" role="group" aria-label="Filter articles by topic">
          <button type="button" class="bl-chip is-active" data-bl-cat="" aria-pressed="true">All</button>
<?php foreach ($topics as $topic): ?>
          <button type="button" class="bl-chip" data-bl-cat="<?= e(post_topic($topic)) ?>" aria-pressed="false"><?= e($topic) ?></button>
<?php endforeach; ?>
        </div>
      </div>
<?php endif; ?>
    </div>
  </section>

  <section class="bl-list">
    <div class="ig-wrap">
<?php if (!$all): ?>
      <p class="bl-empty">New articles are on the way. Check back soon.</p>
<?php else: ?>

<?php if ($featured): ?>
      <article class="bl-feature" data-bl-item data-cat="<?= e(post_topic($featured['category'])) ?>" data-text="<?= e(strtolower($featured['title'] . ' ' . $featured['description'] . ' ' . $featured['category'])) ?>">
        <div class="bl-feature__img">
          <img src="<?= e($featured['image']) ?>" alt="<?= e($featured['image_alt']) ?>" fetchpriority="high" decoding="async">
        </div>
        <div class="bl-feature__body">
          <div class="bl-feature__tags">
            <span class="bl-flag">Featured</span>
            <span class="bl-pill"><?= e($featured['category']) ?></span>
          </div>
          <h2><a href="/blog/<?= e($featured['slug']) ?>/"><?= e($featured['title']) ?></a></h2>
          <p><?= e($featured['description']) ?></p>
          <div class="bl-meta">
            <time datetime="<?= e($featured['published']) ?>"><?= e(post_date($featured['published'], 'M j, Y')) ?></time>
            <span class="bl-dot" aria-hidden="true"></span>
            <span><?= (int) $featured['minutes'] ?> min read</span>
          </div>
          <span class="bl-readmore" aria-hidden="true">Read article <?= $arrow ?></span>
        </div>
      </article>
<?php endif; ?>

<?php if ($rest): ?>
      <div class="bl-grid">
<?php foreach ($rest as $p) require APP . '/views/partials/blog-card.php'; ?>
      </div>
<?php endif; ?>

      <div class="bl-empty" data-bl-empty hidden>
        <p><b>No articles match your search.</b> Try a different word or browse all topics.</p>
      </div>
<?php endif; ?>
    </div>
  </section>

<?php require APP . '/views/partials/consult-cta.php'; ?>

</div>
