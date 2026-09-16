<?php
// "Classic" layout family: the office-page design (service-classic, location-full,
// location-compact). Sections, their order and their content come from the page's
// template (app/templates.php); this view only draws them.
// Vars: $page from page_prepare().
$d      = $page['d'];
$office = $page['office'];
$phone  = $office['phone'] ?? cfg('phone');
$tel    = $office['tel'] ?? cfg('phone_tel');
$icons  = $page['tpl']['icons'];
$pin    = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>';
$phoneIcon = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.2.4 2.4.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1-9.4 0-17-7.6-17-17 0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2z"/></svg>';
$mailIcon  = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4.2-8 4.8-8-4.8V6l8 4.8L20 6v2.2z"/></svg>';
$caret     = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 15.5 4.5 8l1.6-1.6L12 12.3l5.9-5.9L19.5 8z"/></svg>';
?>
<div class="ig-locpage">
<?php foreach ($page['on'] as $key): $s = $d[$key] ?? []; ?>
<?php switch ($key):

    case 'hero': ?>
  <section class="ig-lhero">
    <div class="ig-lhero__media">
      <img src="<?= e($s['image']) ?>" alt="<?= e($s['image_alt']) ?>" fetchpriority="high" decoding="async">
    </div>
    <div class="ig-wrap">
      <div class="ig-lhero__inner">
        <div class="ig-lhero__copy">
<?php if (tpl_has($s, 'pill')): ?>
          <span class="ig-lhero__pill"><?= e($s['pill']) ?></span>
<?php endif; ?>
          <h1><?= tpl_em($s['heading']) ?></h1>
<?php if ($office): ?>
          <p class="ig-lhero__addr"><?= $pin ?><?= e($office['address_full']) ?></p>
<?php elseif (tpl_has($s, 'lead')): ?>
          <p class="ig-lhero__addr"><?= e($s['lead']) ?></p>
<?php endif; ?>
          <div class="ig-btns ig-btns--c">
            <a class="ig-btn ig-btn--primary" href="#ig-consult" data-book>Schedule Now</a>
            <a class="ig-btn ig-btn--white" href="tel:<?= e($tel) ?>">Call Now</a>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php break;

    case 'intro': ?>
  <section class="ig-sec ig-sec--after-hero">
    <div class="ig-wrap">
      <div class="ig-head">
<?php if (tpl_has($s, 'kicker')): ?>
        <span class="ig-kicker"><?= e($s['kicker']) ?></span>
<?php endif; ?>
        <h2 class="ig-h2"><?= tpl_em($s['heading']) ?></h2>
<?php if (tpl_has($s, 'text')): ?>
        <p class="ig-lead ig-standout__lead"><?= e($s['text']) ?></p>
<?php endif; ?>
<?php if (tpl_has($s, 'text2')): ?>
        <p class="ig-lead ig-standout__lead" style="margin-top:1.4em;"><?= e($s['text2']) ?></p>
<?php endif; ?>
      </div>
    </div>
  </section>
<?php break;

    case 'highlights': ?>
  <section class="ig-sec ig-standout">
    <div class="ig-wrap">
      <div class="ig-standout__grid">
<?php foreach ($s['items'] as $item): ?>
        <a class="ig-so" href="#ig-consult" data-book>
          <img src="<?= e($item['image']) ?>" alt="<?= e($item['image_alt'] ?? '') ?>" width="288" height="467" loading="lazy" decoding="async">
          <div class="ig-so__inner">
            <h3 class="ig-so__title"><?= e($item['title']) ?></h3>
            <p class="ig-so__text"><?= e($item['text']) ?></p>
          </div>
        </a>
<?php endforeach; ?>
      </div>
    </div>
  </section>
<?php break;

    case 'offers': ?>
  <section class="ig-sec">
    <div class="ig-wrap">
      <div class="ig-head">
<?php if (tpl_has($s, 'kicker')): ?>
        <span class="ig-kicker"><?= e($s['kicker']) ?></span>
<?php endif; ?>
        <h2 class="ig-h2"><?= tpl_em($s['heading']) ?></h2>
<?php if (tpl_has($s, 'lead')): ?>
        <p class="ig-lead ig-standout__lead"><?= e($s['lead']) ?></p>
<?php endif; ?>
      </div>

      <div class="ig-offers">
<?php foreach ($s['items'] as $item): ?>
        <div class="ig-offer">
          <div class="ig-offer__bg"><img src="<?= e($item['image']) ?>" alt="" width="288" height="467" loading="lazy" decoding="async"></div>
          <div class="ig-offer__av"><img src="<?= e($item['image']) ?>" alt="<?= e($item['image_alt'] ?? '') ?>" width="288" height="467" loading="lazy" decoding="async"></div>
          <h3 class="ig-offer__title"><?= tpl_offer($item['title']) ?></h3>
          <p class="ig-offer__desc"><?= e($item['text']) ?></p>
<?php if (tpl_has($item, 'terms')): ?>
          <p class="ig-offer__terms"><?= e($item['terms']) ?></p>
<?php endif; ?>
        </div>
<?php endforeach; ?>
      </div>

      <div class="ig-btns ig-btns--c" style="margin-top:clamp(30px,3.6vw,64px);">
        <a class="ig-btn ig-btn--primary" href="#ig-consult" data-book>Schedule Now</a>
        <a class="ig-btn ig-btn--navy" href="tel:<?= e($tel) ?>">Call Now</a>
      </div>
    </div>
  </section>
<?php break;

    case 'treatments': ?>
  <section class="ig-sec">
    <div class="ig-wrap">
      <div class="ig-head">
<?php if (tpl_has($s, 'kicker')): ?>
        <span class="ig-kicker"><?= e($s['kicker']) ?></span>
<?php endif; ?>
        <h2 class="ig-h2"><?= tpl_em($s['heading']) ?></h2>
<?php if (tpl_has($s, 'lead')): ?>
        <p class="ig-lead"><?= e($s['lead']) ?></p>
<?php endif; ?>
      </div>

      <div class="ig-treats">
<?php foreach ($s['items'] as $item): $link = tpl_link($item['link'] ?? ''); ?>
        <<?= $link ? 'a' : 'div' ?> class="ig-treat"<?= $link ? ' href="' . e($link) . '"' : '' ?>>
          <div class="ig-treat__img">
            <img src="<?= e($item['image']) ?>" alt="<?= e($item['image_alt'] ?? '') ?>" width="288" height="467" loading="lazy" decoding="async">
          </div>
          <div class="ig-treat__body">
            <h3><?= e($item['title']) ?></h3>
            <p><?= e($item['text']) ?></p>
          </div>
        </<?= $link ? 'a' : 'div' ?>>
<?php endforeach; ?>
      </div>

      <div class="ig-btns ig-btns--c" style="margin-top:clamp(28px,3.2vw,44px);">
        <a class="ig-btn ig-btn--primary" href="#ig-consult" data-book>Schedule Now</a>
        <a class="ig-btn ig-btn--out" href="tel:<?= e($tel) ?>">Call Now</a>
      </div>
    </div>
  </section>
<?php break;

    case 'ages': ?>
  <section class="ig-sec">
    <div class="ig-wrap">
      <div class="ig-head">
<?php if (tpl_has($s, 'kicker')): ?>
        <span class="ig-kicker"><?= e($s['kicker']) ?></span>
<?php endif; ?>
        <h2 class="ig-h2"><?= tpl_em($s['heading']) ?></h2>
<?php if (tpl_has($s, 'lead')): ?>
        <p class="ig-lead"><?= e($s['lead']) ?></p>
<?php endif; ?>
      </div>

      <div class="ig-ages">
<?php foreach ($s['items'] as $item): ?>
        <div class="ig-age">
          <img src="<?= e($item['image']) ?>" alt="<?= e($item['image_alt'] ?? '') ?>" width="288" height="467" loading="lazy" decoding="async">
          <div class="ig-age__body">
            <h3><?= e($item['title']) ?></h3>
            <p><?= e($item['text']) ?></p>
<?php if (tpl_has($item, 'button')): ?>
            <a class="ig-btn ig-btn--primary" href="#ig-consult" data-book><?= e($item['button']) ?></a>
<?php endif; ?>
          </div>
        </div>
<?php endforeach; ?>
      </div>
    </div>
  </section>
<?php break;

    case 'cta_mid': ?>
  <section class="ig-sec ig-cta">
    <div class="ig-wrap">
      <h2><?= tpl_em($s['heading']) ?></h2>
<?php if (tpl_has($s, 'text')): ?>
      <p><?= e($s['text']) ?></p>
<?php endif; ?>
      <div class="ig-btns ig-btns--c">
        <a class="ig-btn ig-btn--primary" href="#ig-consult" data-book>Schedule Now</a>
        <a class="ig-btn ig-btn--white" href="tel:<?= e($tel) ?>">Call <?= e($phone) ?></a>
      </div>
    </div>
  </section>
<?php break;

    case 'consult':
    $ctaImage = $s['image'];
    $ctaPhone = $phone;
    $ctaTel   = $tel;
    require APP . '/views/partials/consult-cta.php';
    break;

    case 'contact': ?>
  <section class="ig-sec ig-sec--tint">
    <div class="ig-wrap">
      <div class="ig-mapwrap">
        <div>
<?php if (tpl_has($s, 'kicker')): ?>
          <span class="ig-kicker"><?= e($s['kicker']) ?></span>
<?php endif; ?>
          <h2 class="ig-h2"><?= tpl_em($s['heading']) ?></h2>

          <ul class="ig-info">
<?php if ($office && $office['address_full'] !== ''): ?>
            <li><?= $pin ?><span><?= e($office['address_full']) ?></span></li>
<?php endif; ?>
<?php if ($office && $office['phone'] !== ''): ?>
            <li><?= $phoneIcon ?><span><a href="tel:<?= e($office['tel']) ?>"><?= e($office['phone']) ?></a></span></li>
<?php endif; ?>
<?php if ($office && $office['email'] !== ''): ?>
            <li><?= $mailIcon ?><span><a href="mailto:<?= e($office['email']) ?>"><?= e($office['email']) ?></a></span></li>
<?php endif; ?>
          </ul>

<?php if ($office && $office['hours']): ?>
          <ul class="ig-info" style="margin-top:14px;">
<?php foreach ($office['hours'] as $line): ?>
            <li><span><?= e($line) ?></span></li>
<?php endforeach; ?>
          </ul>
<?php endif; ?>
<?php if (tpl_has($s, 'note')): ?>
          <p class="ig-lead" style="margin-top:18px;"><?= e($s['note']) ?></p>
<?php endif; ?>
        </div>

        <div class="ig-mapbox">
          <img src="<?= e($s['image']) ?>" alt="<?= e($s['image_alt']) ?>" width="288" height="467" loading="lazy" decoding="async">
        </div>
      </div>
    </div>
  </section>
<?php break;

    case 'conditions': ?>
  <section class="ig-sec">
    <div class="ig-wrap">
      <div class="ig-head">
<?php if (tpl_has($s, 'kicker')): ?>
        <span class="ig-kicker"><?= e($s['kicker']) ?></span>
<?php endif; ?>
        <h2 class="ig-h2"><?= tpl_em($s['heading']) ?></h2>
<?php if (tpl_has($s, 'lead')): ?>
        <p class="ig-lead"><?= e($s['lead']) ?></p>
<?php endif; ?>
      </div>

      <div class="ig-conds">
<?php foreach ($s['items'] as $item): $icon = $icons[$item['icon'] ?? ''] ?? reset($icons); ?>
        <div class="ig-cond">
          <div class="ig-cond__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="<?= e($icon) ?>"/></svg></div>
          <h3><?= e($item['title']) ?></h3>
          <p><?= e($item['text']) ?></p>
        </div>
<?php endforeach; ?>
      </div>

      <div class="ig-btns ig-btns--c" style="margin-top:clamp(28px,3.2vw,44px);">
        <a class="ig-btn ig-btn--primary" href="#ig-consult" data-book>Book A No-Cost Consultation</a>
        <a class="ig-btn ig-btn--out" href="tel:<?= e($tel) ?>">Call Now</a>
      </div>
    </div>
  </section>
<?php break;

    case 'cta_end': ?>
  <section class="ig-sec ig-cta ig-cta--split">
    <div class="ig-wrap">
      <div class="ig-cta__grid">
        <div>
<?php if (tpl_has($s, 'kicker')): ?>
          <span class="ig-kicker"><?= e($s['kicker']) ?></span>
<?php endif; ?>
          <h2><?= tpl_em($s['heading']) ?></h2>
<?php if (tpl_has($s, 'text')): ?>
          <p><?= e($s['text']) ?></p>
<?php endif; ?>
          <div class="ig-btns">
            <a class="ig-btn ig-btn--primary" href="#ig-consult" data-book>Schedule Now</a>
            <a class="ig-btn ig-btn--white" href="tel:<?= e($tel) ?>">Call <?= e($phone) ?></a>
          </div>
        </div>
        <div class="ig-cta__img">
          <img src="<?= e($s['image']) ?>" alt="<?= e($s['image_alt']) ?>" width="826" height="591" loading="lazy" decoding="async">
        </div>
      </div>
    </div>
  </section>
<?php break;

    case 'faq': ?>
  <section class="ig-sec ig-sec--tint">
    <div class="ig-wrap">
      <div class="ig-head">
<?php if (tpl_has($s, 'kicker')): ?>
        <span class="ig-kicker"><?= e($s['kicker']) ?></span>
<?php endif; ?>
        <h2 class="ig-h2"><?= tpl_em($s['heading']) ?></h2>
      </div>

      <div class="ig-faq">
        <div id="igFaqList">
<?php foreach ($s['items'] as $item): ?>
          <div class="ig-q">
            <button class="ig-q__btn" type="button" aria-expanded="false">
              <?= e($item['q']) ?>
              <?= $caret ?>
            </button>
            <div class="ig-q__a"><p><?= e($item['a']) ?></p></div>
          </div>
<?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
<?php break;

endswitch; ?>
<?php endforeach; ?>
</div>
