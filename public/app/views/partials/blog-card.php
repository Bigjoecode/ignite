<?php
// One article card for the blog grid and "Keep reading". Vars: $p (post from posts()).
?>
<article class="bl-card" data-bl-item data-cat="<?= e(post_topic($p['category'])) ?>" data-text="<?= e(strtolower($p['title'] . ' ' . $p['description'] . ' ' . $p['category'])) ?>">
  <div class="bl-card__img">
    <img src="<?= e($p['image']) ?>" alt="" loading="lazy" decoding="async">
  </div>
  <div class="bl-card__body">
    <span class="bl-pill"><?= e($p['category']) ?></span>
    <h3><a href="/blog/<?= e($p['slug']) ?>/"><?= e($p['title']) ?></a></h3>
    <p><?= e($p['description']) ?></p>
    <div class="bl-meta">
      <time datetime="<?= e($p['published']) ?>"><?= e(post_date($p['published'], 'M j, Y')) ?></time>
      <span class="bl-dot" aria-hidden="true"></span>
      <span><?= (int) $p['minutes'] ?> min read</span>
    </div>
  </div>
</article>
