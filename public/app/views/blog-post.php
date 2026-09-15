<?php
$list  = array_values(posts());
$index = array_search($post['slug'], array_column($list, 'slug'), true);
$newer = $index > 0 ? $list[$index - 1] : null;
$older = $list[$index + 1] ?? null;

// same topic first, then the most recent
$related = array_values(array_filter($list, static fn(array $p): bool => $p['slug'] !== $post['slug']));
usort($related, static fn(array $a, array $b): int =>
    (($b['category'] === $post['category']) <=> ($a['category'] === $post['category'])) ?: strcmp($b['published'], $a['published']));
$related = array_slice($related, 0, 3);

$toc = $post['toc'];
if (!empty($post['faq'])) $toc[] = ['faq', 'Frequently asked questions'];

$url     = cfg('base_url') . '/blog/' . $post['slug'] . '/';
$updated = $post['updated'] !== $post['published'];
$copyIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/></svg>';
?>
<div class="bl-progress" aria-hidden="true"><b data-bl-progress></b></div>

<div class="ig-home bl">
  <article class="bl-post" data-bl-article>

    <header class="bl-post__head">
      <div class="ig-wrap">
        <nav class="bl-crumbs" aria-label="Breadcrumb">
          <ol>
            <li><a href="/">Home</a></li>
            <li><a href="/blog/">Blog</a></li>
            <li aria-current="page"><?= e($post['title']) ?></li>
          </ol>
        </nav>

        <a class="bl-pill" href="/blog/?topic=<?= e(post_topic($post['category'])) ?>"><?= e($post['category']) ?></a>
        <h1><?= e($post['title']) ?></h1>
        <p class="bl-post__lead"><?= e($post['description']) ?></p>

        <div class="bl-byline">
          <span class="bl-avatar" aria-hidden="true">IO</span>
          <div>
            <b>Ignite Orthodontics Team</b>
            <div class="bl-meta">
              <time datetime="<?= e($post['published']) ?>"><?= e(post_date($post['published'])) ?></time>
<?php if ($updated): ?>
              <span class="bl-dot" aria-hidden="true"></span>
              <span>Updated <time datetime="<?= e($post['updated']) ?>"><?= e(post_date($post['updated'])) ?></time></span>
<?php endif; ?>
              <span class="bl-dot" aria-hidden="true"></span>
              <span><?= (int) $post['minutes'] ?> min read</span>
            </div>
          </div>
        </div>
      </div>
    </header>

    <figure class="bl-post__hero">
      <div class="ig-wrap">
        <img src="<?= e($post['image']) ?>" alt="<?= e($post['image_alt']) ?>" fetchpriority="high" decoding="async">
      </div>
    </figure>

    <div class="ig-wrap bl-post__layout">
      <div class="bl-post__main">

<?php if ($toc): ?>
        <details class="bl-toc-mobile">
          <summary>On this page</summary>
          <ol data-bl-toc>
<?php foreach ($toc as [$id, $label]): ?>
            <li><a href="#<?= e($id) ?>"><?= e($label) ?></a></li>
<?php endforeach; ?>
          </ol>
        </details>
<?php endif; ?>

        <div class="bl-prose">
<?= $post['body'] /* trusted HTML from app/data/posts */ ?>
        </div>

<?php if (!empty($post['faq'])): ?>
        <section class="bl-faq" aria-labelledby="faq">
          <h2 id="faq">Frequently asked questions</h2>
<?php foreach ($post['faq'] as $i => [$q, $a]): ?>
          <details<?= $i === 0 ? ' open' : '' ?>>
            <summary><?= e($q) ?></summary>
            <p><?= e($a) ?></p>
          </details>
<?php endforeach; ?>
        </section>
<?php endif; ?>

        <div class="bl-endbar">
          <p>Found this helpful? Share it with someone who's thinking about treatment.</p>
          <button type="button" class="bl-copy" data-bl-copy data-url="<?= e($url) ?>"><?= $copyIcon ?><span>Copy link</span></button>
        </div>
      </div>

      <aside class="bl-post__aside">
        <div class="bl-toc">
<?php if ($toc): ?>
          <p class="bl-toc__title">On this page</p>
          <nav aria-label="On this page">
            <ol data-bl-toc>
<?php foreach ($toc as [$id, $label]): ?>
              <li><a href="#<?= e($id) ?>"><?= e($label) ?></a></li>
<?php endforeach; ?>
            </ol>
          </nav>
<?php endif; ?>
          <div class="bl-toc__actions">
            <a class="bl-toc__cta" href="/contact-us/" data-book>Book a no-cost consultation</a>
            <button type="button" class="bl-copy" data-bl-copy data-url="<?= e($url) ?>"><?= $copyIcon ?><span>Copy link</span></button>
          </div>
        </div>
      </aside>
    </div>
  </article>

<?php if ($newer || $older): ?>
  <nav class="ig-wrap bl-pager" aria-label="More articles">
<?php if ($older): ?>
    <a class="bl-pager__prev" href="/blog/<?= e($older['slug']) ?>/"><span>&larr; Previous article</span><b><?= e($older['title']) ?></b></a>
<?php endif; ?>
<?php if ($newer): ?>
    <a class="bl-pager__next" href="/blog/<?= e($newer['slug']) ?>/"><span>Next article &rarr;</span><b><?= e($newer['title']) ?></b></a>
<?php endif; ?>
  </nav>
<?php endif; ?>

<?php if ($related): ?>
  <section class="bl-related">
    <div class="ig-wrap">
      <div class="bl-related__head">
        <span class="ig-kicker">Keep Reading</span>
        <h2>More From <em>the Blog.</em></h2>
      </div>
      <div class="bl-grid">
<?php foreach ($related as $p) require APP . '/views/partials/blog-card.php'; ?>
      </div>
      <div class="bl-related__all"><a class="ig-btn ig-btn--navy" href="/blog/">View all articles</a></div>
    </div>
  </section>
<?php endif; ?>

<?php require APP . '/views/partials/consult-cta.php'; ?>

</div>
