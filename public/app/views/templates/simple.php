<?php
// "Simple" layout family (service-simple): navy header, written content and
// optional link cards. Sections come from the page's template (app/templates.php).
// Vars: $page from page_prepare().
$d = $page['d'];
// headings get ids, so link cards and menus can point at a section of the page
[$bodyHtml] = post_toc((string) ($d['body']['html'] ?? ''));
?>
<div class="ig-home">
<?php foreach ($page['on'] as $key): $s = $d[$key] ?? []; ?>
<?php switch ($key):

    case 'hero': ?>
  <section class="pg-hero">
    <div class="ig-wrap">
<?php if (tpl_has($s, 'kicker')): ?>
      <span class="ig-kicker"><?= e($s['kicker']) ?></span>
<?php endif; ?>
      <h1><?= tpl_em($s['heading']) ?></h1>
<?php if (tpl_has($s, 'lead')): ?>
      <p class="pg-hero__lead"><?= e($s['lead']) ?></p>
<?php endif; ?>
      <div class="ig-btns">
        <a class="ig-btn ig-btn--primary" href="#ig-consult" data-book>Schedule a Consultation</a>
        <a class="ig-btn ig-btn--white" href="tel:<?= e(cfg('phone_tel')) ?>">Call <?= e(cfg('phone')) ?></a>
      </div>
    </div>
  </section>
<?php break;

    case 'body': ?>
<?php if (tpl_has($s, 'note') || tpl_has($s, 'html')): ?>
  <section class="ig-sec">
    <div class="ig-wrap pg-body">
<?php if (tpl_has($s, 'note')): ?>
      <p class="pg-note"><?= e($s['note']) ?></p>
<?php endif; ?>
<?php if (tpl_has($s, 'html')): ?>
      <div class="bl-prose"><?= $bodyHtml /* cleaned by clean_post_html() on save */ ?></div>
<?php endif; ?>
    </div>
  </section>
<?php endif; ?>
<?php break;

    case 'links': ?>
<?php if ($s['items']): ?>
  <section class="ig-sec" style="padding-top:0;">
    <div class="ig-wrap pg-body">
      <div class="pg-links">
<?php foreach ($s['items'] as $item): $link = tpl_link($item['link'] ?? ''); ?>
        <<?= $link ? 'a' : 'div' ?> class="pg-link"<?= $link ? ' href="' . e($link) . '"' : '' ?>>
          <b><?= e($item['label']) ?></b><span><?= e($item['text']) ?></span>
        </<?= $link ? 'a' : 'div' ?>>
<?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>
<?php break;

    case 'offices':
    require APP . '/views/partials/finder.php';
    break;

    case 'consult':
    $ctaImage = $s['image'];
    require APP . '/views/partials/consult-cta.php';
    break;

endswitch; ?>
<?php endforeach; ?>
</div>
