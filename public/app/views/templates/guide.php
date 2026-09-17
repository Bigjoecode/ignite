<?php
// "Guide" layout family (service-guide): a long-form treatment page. A header,
// then the blocks the admin arranged (app/guide-blocks.php), then the office
// list and the booking band. Vars: $page from page_prepare().
$d      = $page['d'];
$phone  = (string) cfg('phone');
$tel    = (string) cfg('phone_tel');
$parent = page_parent($page);
$check  = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>';
$arrow  = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
$quote  = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 7H6a2 2 0 0 0-2 2v4h5v5h1a4 4 0 0 0 4-4V9 M20 7h-4a2 2 0 0 0-2 2"/></svg>';

$book = static function (string $label, string $class = 'gd-btn gd-btn--primary'): string {
    return trim($label) === '' ? '' : '<a class="' . $class . '" href="#ig-consult" data-book>' . e($label) . '</a>';
};
$call = static function (string $class) use ($phone, $tel): string {
    return '<a class="' . $class . '" href="tel:' . e($tel) . '">Call ' . e($phone) . '</a>';
};
// eyebrow, heading and intro shared by most blocks
$head = static function (array $b, string $class = 'gd-head'): string {
    $out = '';
    if (tpl_has($b, 'eyebrow')) $out .= '<span class="gd-eyebrow">' . e($b['eyebrow']) . '</span>';
    if (tpl_has($b, 'heading')) $out .= '<h2 class="gd-h2">' . tpl_em($b['heading']) . '</h2>';
    if (tpl_has($b, 'intro'))   $out .= '<div class="gd-intro">' . guide_paragraphs($b['intro']) . '</div>';
    return $out === '' ? '' : '<div class="' . $class . '">' . $out . '</div>';
};
$tone = static fn(array $b): string => 'gd-sec gd-sec--' . (in_array($b['tone'] ?? '', ['white', 'tint', 'navy'], true) ? $b['tone'] : 'white');
$cols = static function (int $n, int $max = 4): int {
    if ($n <= 1) return 1;
    if ($n <= $max) return $n;
    return $n % 3 === 0 || $max < 4 ? 3 : ($n % 4 === 0 ? 4 : 3);
};
?>
<div class="gd">
<?php foreach ($page['on'] as $key): $s = $d[$key] ?? []; ?>
<?php switch ($key):

    case 'hero': ?>
  <section class="gd-hero">
    <div class="gd-wrap gd-hero__grid">
      <div class="gd-hero__copy">
        <nav class="gd-crumbs" aria-label="Breadcrumb">
          <ol>
            <li><a href="/">Home</a></li>
<?php if ($parent): ?>
            <li><a href="/<?= e($parent['slug']) ?>/"><?= e($parent['title']) ?></a></li>
<?php endif; ?>
            <li aria-current="page"><?= e($page['title']) ?></li>
          </ol>
        </nav>
<?php if (tpl_has($s, 'eyebrow')): ?>
        <span class="gd-pill"><?= e($s['eyebrow']) ?></span>
<?php endif; ?>
        <h1><?= tpl_em($s['heading']) ?></h1>
<?php if (tpl_has($s, 'lead')): ?>
        <div class="gd-hero__lead"><?= guide_paragraphs($s['lead']) ?></div>
<?php endif; ?>
        <div class="gd-actions">
          <?= $book($s['button']) ?>
          <?= $call('gd-btn gd-btn--ghost') ?>
        </div>
<?php if ($s['points']): ?>
        <ul class="gd-trust">
<?php foreach ($s['points'] as $point): ?>
          <li><?= $check ?><?= e($point) ?></li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
      </div>

      <div class="gd-hero__media">
        <div class="gd-hero__frame">
<?php if (tpl_has($s, 'image')): ?>
          <img src="<?= e($s['image']) ?>" alt="<?= e($s['image_alt']) ?>" fetchpriority="high" decoding="async">
<?php endif; ?>
        </div>
<?php if (tpl_has($s, 'price')): ?>
        <div class="gd-price">
          <span class="gd-price__label">Flexible monthly payments</span>
          <p class="gd-price__value"><?= tpl_em($s['price']) ?></p>
<?php if (tpl_has($s, 'price_note')): ?>
          <p class="gd-price__note"><?= e($s['price_note']) ?></p>
<?php endif; ?>
        </div>
<?php endif; ?>
      </div>
    </div>
  </section>
<?php break;

    case 'content':
    foreach ($s['blocks'] as $b):
    switch ($b['_type']):

    case 'text':
    $media = tpl_has($b, 'image'); ?>
  <section class="<?= $tone($b) ?>">
    <div class="gd-wrap <?= $media ? 'gd-split gd-split--' . ($b['image_side'] === 'left' ? 'left' : 'right') : 'gd-narrow' ?>">
      <div class="gd-split__copy">
        <?= $head($b, 'gd-head gd-head--left') ?>
<?php if ($b['list']): ?>
        <ul class="gd-list gd-list--<?= e(in_array($b['list_style'], ['check', 'dot', 'quote'], true) ? $b['list_style'] : 'check') ?><?= !$media && count($b['list']) > 5 && $b['list_style'] !== 'quote' ? ' gd-list--cols' : '' ?>">
<?php foreach ($b['list'] as $item): ?>
          <li><?= $b['list_style'] === 'check' || $b['list_style'] === '' ? '<span class="gd-tick">' . $check . '</span>' : '' ?><span><?= tpl_em($item) ?></span></li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
<?php if (tpl_has($b, 'outro')): ?>
        <div class="gd-intro gd-outro"><?= guide_paragraphs($b['outro']) ?></div>
<?php endif; ?>
<?php if (tpl_has($b, 'button')): ?>
        <div class="gd-actions gd-actions--block"><?= $book($b['button']) ?></div>
<?php endif; ?>
      </div>
<?php if ($media): ?>
      <div class="gd-split__media">
        <img src="<?= e($b['image']) ?>" alt="<?= e($b['image_alt']) ?>" loading="lazy" decoding="async">
      </div>
<?php endif; ?>
    </div>
  </section>
<?php break;

    case 'cards': ?>
  <section class="<?= $tone($b) ?>">
    <div class="gd-wrap">
      <?= $head($b) ?>
      <div class="gd-cards gd-cols-<?= $cols(count($b['items'])) ?>">
<?php foreach ($b['items'] as $item): ?>
        <article class="gd-card">
          <span class="gd-card__icon"><?= guide_icon($item['icon']) ?></span>
          <h3><?= e($item['title']) ?></h3>
<?php if (tpl_has($item, 'text')): ?>
          <p><?= e($item['text']) ?></p>
<?php endif; ?>
        </article>
<?php endforeach; ?>
      </div>
<?php if (tpl_has($b, 'note')): ?>
      <div class="gd-note"><?= guide_paragraphs($b['note']) ?></div>
<?php endif; ?>
<?php if (tpl_has($b, 'button')): ?>
      <div class="gd-actions gd-actions--center"><?= $book($b['button']) ?></div>
<?php endif; ?>
    </div>
  </section>
<?php break;

    case 'steps':
    $n = count($b['items']); ?>
  <section class="<?= $tone($b) ?>">
    <div class="gd-wrap">
      <?= $head($b) ?>
      <ol class="gd-steps gd-steps--<?= $n === 5 ? 5 : ($n === 4 ? 4 : ($n <= 3 ? max(1, $n) : 3)) ?>">
<?php foreach ($b['items'] as $i => $item): ?>
        <li class="gd-step">
          <span class="gd-step__n"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <h3><?= e($item['title']) ?></h3>
<?php if (tpl_has($item, 'text')): ?>
          <p><?= e($item['text']) ?></p>
<?php endif; ?>
        </li>
<?php endforeach; ?>
      </ol>
<?php if (tpl_has($b, 'note')): ?>
      <div class="gd-note"><?= guide_paragraphs($b['note']) ?></div>
<?php endif; ?>
<?php if (tpl_has($b, 'button')): ?>
      <div class="gd-actions gd-actions--center"><?= $book($b['button']) ?></div>
<?php endif; ?>
    </div>
  </section>
<?php break;

    case 'panels':
    $n = count($b['items']); ?>
  <section class="<?= $tone($b) ?>">
    <div class="gd-wrap">
      <?= $head($b) ?>
      <div class="gd-panels gd-cols-<?= $n === 3 ? 3 : min(2, max(1, $n)) ?>">
<?php foreach ($b['items'] as $item): $link = tpl_link($item['link'] ?? ''); ?>
        <article class="gd-panel">
<?php if (tpl_has($item, 'label')): ?>
          <span class="gd-panel__label"><?= e($item['label']) ?></span>
<?php endif; ?>
          <h3><?= e($item['title']) ?></h3>
          <?= guide_paragraphs($item['text']) ?>
<?php if ($link !== ''): ?>
          <a class="gd-link" href="<?= e($link) ?>"><?= e(tpl_has($item, 'link_label') ? $item['link_label'] : 'Learn more') ?><?= $arrow ?></a>
<?php endif; ?>
        </article>
<?php endforeach; ?>
      </div>
<?php if (tpl_has($b, 'note')): ?>
      <div class="gd-note"><?= guide_paragraphs($b['note']) ?></div>
<?php endif; ?>
<?php if (tpl_has($b, 'button')): ?>
      <div class="gd-actions gd-actions--center"><?= $book($b['button']) ?></div>
<?php endif; ?>
    </div>
  </section>
<?php break;

    case 'compare':
    $labels = array_filter(array_column($b['rows'], 'label'), static fn($l): bool => trim((string) $l) !== ''); ?>
  <section class="<?= $tone($b) ?>">
    <div class="gd-wrap">
      <?= $head($b) ?>
      <div class="gd-table-wrap">
        <table class="gd-table<?= $labels ? '' : ' gd-table--nolabels' ?>">
          <thead>
            <tr>
<?php if ($labels): ?>
              <td></td>
<?php endif; ?>
<?php foreach ($b['columns'] as $c => $column): ?>
              <th scope="col"<?= $c === 0 ? ' class="is-featured"' : '' ?>><?= e($column) ?></th>
<?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
<?php foreach ($b['rows'] as $row): ?>
            <tr>
<?php if ($labels): ?>
              <th scope="row"><?= e($row['label']) ?></th>
<?php endif; ?>
<?php foreach ($b['columns'] as $c => $column): $value = (string) ($row['values'][$c] ?? ''); ?>
              <td<?= $c === 0 ? ' class="is-featured"' : '' ?>><?php
                if (strcasecmp($value, 'Yes') === 0) echo '<span class="gd-yes">' . $check . 'Yes</span>';
                elseif (strcasecmp($value, 'No') === 0) echo '<span class="gd-no">No</span>';
                else echo e($value);
              ?></td>
<?php endforeach; ?>
            </tr>
<?php endforeach; ?>
          </tbody>
        </table>
      </div>
<?php if (tpl_has($b, 'note')): ?>
      <div class="gd-note"><?= guide_paragraphs($b['note']) ?></div>
<?php endif; ?>
<?php if (tpl_has($b, 'button')): ?>
      <div class="gd-actions gd-actions--center"><?= $book($b['button']) ?></div>
<?php endif; ?>
    </div>
  </section>
<?php break;

    case 'pricing': ?>
  <section class="<?= $tone($b) ?> gd-pricing">
    <div class="gd-wrap">
      <?= $head($b) ?>
      <div class="gd-pay gd-cols-<?= $cols(count($b['items']), 3) ?>">
<?php foreach ($b['items'] as $item): ?>
        <article class="gd-pay__item">
          <span class="gd-card__icon"><?= guide_icon($item['icon']) ?></span>
          <h3><?= e($item['title']) ?></h3>
          <p><?= e($item['text']) ?></p>
        </article>
<?php endforeach; ?>
      </div>
<?php if (tpl_has($b, 'note')): ?>
      <div class="gd-note"><?= guide_paragraphs($b['note']) ?></div>
<?php endif; ?>
<?php if (tpl_has($b, 'button')): ?>
      <div class="gd-actions gd-actions--center"><?= $book($b['button']) ?></div>
<?php endif; ?>
    </div>
  </section>
<?php break;

    case 'doctor':
    $initials = implode('', array_map(static fn(string $w): string => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', trim(preg_replace('/^Dr\.?\s+|,.*$/i', '', $b['name']))) ?: [], 0, 2))); ?>
  <section class="<?= $tone($b) ?>">
    <div class="gd-wrap">
      <div class="gd-doc">
        <div class="gd-doc__media">
<?php if (tpl_has($b, 'image')): ?>
          <img src="<?= e($b['image']) ?>" alt="<?= e($b['image_alt'] ?: $b['name']) ?>" loading="lazy" decoding="async">
<?php else: ?>
          <span class="gd-doc__avatar" aria-hidden="true"><?= e(strtoupper($initials)) ?></span>
<?php endif; ?>
        </div>
        <div class="gd-doc__copy">
          <?= $head(['eyebrow' => $b['eyebrow'], 'heading' => $b['heading']], 'gd-head gd-head--left') ?>
<?php if (tpl_has($b, 'name')): ?>
          <p class="gd-doc__name"><?= e($b['name']) ?></p>
<?php endif; ?>
<?php if (tpl_has($b, 'credential')): ?>
          <span class="gd-doc__badge"><?= guide_icon('badge') ?><?= e($b['credential']) ?></span>
<?php endif; ?>
          <div class="gd-intro"><?= guide_paragraphs($b['intro']) ?></div>
        </div>
      </div>
    </div>
  </section>
<?php break;

    case 'reviews':
    $n = count($b['items']); ?>
  <section class="<?= $tone($b) ?>">
    <div class="gd-wrap">
      <?= $head($b) ?>
      <div class="gd-reviews gd-cols-<?= $n % 3 === 0 || $n === 5 ? 3 : min(2, max(1, $n)) ?>">
<?php foreach ($b['items'] as $item): ?>
        <figure class="gd-review">
          <span class="gd-review__mark"><?= $quote ?></span>
          <blockquote><?= e(trim($item['quote'], " \"“”")) ?></blockquote>
<?php if (tpl_has($item, 'name')): ?>
          <figcaption><?= e(ltrim($item['name'], "—- ")) ?></figcaption>
<?php endif; ?>
        </figure>
<?php endforeach; ?>
      </div>
    </div>
  </section>
<?php break;

    case 'cta': ?>
  <section class="gd-cta gd-cta--<?= $b['tone'] === 'navy' ? 'navy' : 'orange' ?>">
    <div class="gd-wrap gd-cta__inner">
      <div class="gd-cta__copy">
<?php if (tpl_has($b, 'eyebrow')): ?>
        <span class="gd-eyebrow"><?= e($b['eyebrow']) ?></span>
<?php endif; ?>
        <h2><?= tpl_em($b['heading']) ?></h2>
<?php if (tpl_has($b, 'intro')): ?>
        <div class="gd-cta__text"><?= guide_paragraphs($b['intro']) ?></div>
<?php endif; ?>
      </div>
      <div class="gd-cta__side">
<?php if (tpl_has($b, 'price')): ?>
        <p class="gd-cta__price"><?= tpl_em($b['price']) ?></p>
<?php endif; ?>
        <div class="gd-actions">
          <?= $book($b['button'], 'gd-btn gd-btn--light') ?>
<?php if ($b['call'] !== 'no'): ?>
          <?= $call('gd-btn gd-btn--ghost') ?>
<?php endif; ?>
        </div>
      </div>
    </div>
  </section>
<?php break;

    case 'faq': ?>
  <section class="<?= $tone($b) ?>">
    <div class="gd-wrap gd-faqwrap">
      <div class="gd-faqwrap__head">
        <?= $head(['eyebrow' => $b['eyebrow'], 'heading' => $b['heading']], 'gd-head gd-head--left') ?>
        <p class="gd-faqwrap__help">Still have a question? Our team is happy to help.</p>
        <div class="gd-actions">
          <?= $book('Ask at a free consultation', 'gd-btn gd-btn--primary gd-btn--sm') ?>
        </div>
      </div>
      <div class="gd-faq">
<?php foreach ($b['items'] as $i => $item): ?>
        <details<?= $i === 0 ? ' open' : '' ?>>
          <summary><?= e($item['q']) ?></summary>
          <div class="gd-faq__a"><?= guide_paragraphs($item['a']) ?></div>
        </details>
<?php endforeach; ?>
      </div>
    </div>
  </section>
<?php break;

    endswitch;
    endforeach;
    break;

    case 'offices': ?>
  <div class="ig-home gd-offices"><?php require APP . '/views/partials/finder.php'; ?></div>
<?php break;

    case 'consult':
    $ctaImage = $s['image'];
    require APP . '/views/partials/consult-cta.php';
    break;

endswitch; ?>
<?php endforeach; ?>
</div>
