<?php
// "Spotlight" layout family (service-spotlight): the bold split-hero design.
// Sections and content come from the page's template (app/templates.php).
// Vars: $page from page_prepare().
$d    = $page['d'];
$star = '<svg viewBox="0 0 24 24"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>';
?>
<div class="kp">
<?php foreach ($page['on'] as $key): $s = $d[$key] ?? []; ?>
<?php switch ($key):

    case 'hero': ?>
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-content">
                <h1><?= tpl_em($s['heading'], 'span') ?></h1>
                <p><?= e($s['lead']) ?></p>
                <div class="hero-actions">
                    <a href="<?= e(booking_url()) ?>" class="btn btn-primary"><?= e($s['button']) ?></a>
                    <a href="tel:<?= e(cfg('phone_tel')) ?>" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: white;">Call <?= e(cfg('phone')) ?></a>
                </div>
<?php if ($s['points']): ?>
                <div class="hero-trust-bar">
<?php foreach ($s['points'] as $point): ?>
                    <div class="trust-item"><?= $star ?><span><?= e($point) ?></span></div>
<?php endforeach; ?>
                </div>
<?php endif; ?>
            </div>

            <div class="hero-image-box">
                <img src="<?= e($s['image']) ?>" alt="<?= e($s['image_alt']) ?>" class="hero-img">
            </div>
        </div>
    </section>
<?php break;

    case 'highlight': ?>
    <section class="early-assessment">
        <div class="container">
            <div class="section-header">
<?php if (tpl_has($s, 'kicker')): ?>
                <span class="badge-pill"><?= e($s['kicker']) ?></span>
<?php endif; ?>
                <h2><?= e($s['heading']) ?></h2>
<?php if (tpl_has($s, 'lead')): ?>
                <p><?= e($s['lead']) ?></p>
<?php endif; ?>
            </div>

            <div class="early-grid">
                <div class="early-card-highlight">
                    <h3><?= e($s['card_title']) ?></h3>
                    <p><?= e($s['card_text']) ?></p>
<?php if ($s['checklist']): ?>
                    <ul class="benefit-checklist">
<?php foreach ($s['checklist'] as $line): ?>
                        <li><?= e($line) ?></li>
<?php endforeach; ?>
                    </ul>
<?php endif; ?>
                </div>

                <div class="early-right-col">
                    <div style="margin-bottom: 24px; border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-md);">
                        <img src="<?= e($s['image']) ?>" alt="<?= e($s['image_alt']) ?>" style="width: 100%; height: 200px; object-fit: cover; display: block;">
                    </div>
<?php if ($s['boxes']): ?>
                    <div class="phases-comparison">
<?php foreach ($s['boxes'] as $box): ?>
                        <div class="phase-box">
<?php if (tpl_has($box, 'tag')): ?>
                            <span class="phase-tag"><?= e($box['tag']) ?></span>
<?php endif; ?>
                            <h4><?= e($box['title']) ?></h4>
                            <p><?= e($box['text']) ?></p>
                        </div>
<?php endforeach; ?>
                    </div>
<?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php break;

    case 'options': ?>
    <section class="treatments-section">
        <div class="container">
            <div class="section-header">
<?php if (tpl_has($s, 'kicker')): ?>
                <span class="badge-pill"><?= e($s['kicker']) ?></span>
<?php endif; ?>
                <h2><?= e($s['heading']) ?></h2>
<?php if (tpl_has($s, 'lead')): ?>
                <p><?= e($s['lead']) ?></p>
<?php endif; ?>
            </div>

            <div class="treatments-grid">
<?php foreach ($s['items'] as $i => $item): ?>
                <div class="treatment-card <?= $i % 2 ? 'card-orange' : 'card-white' ?>">
                    <div>
                        <h3><?= e($item['title']) ?></h3>
                        <p><?= e($item['text']) ?></p>
                    </div>
<?php if (tpl_has($item, 'tag')): ?>
                    <span class="tag-accent"><?= e($item['tag']) ?></span>
<?php endif; ?>
                </div>
<?php endforeach; ?>
            </div>
        </div>
    </section>
<?php break;

    case 'steps': ?>
    <section class="experience-section">
        <div class="container">
            <div class="section-header">
<?php if (tpl_has($s, 'kicker')): ?>
                <span class="badge-pill"><?= e($s['kicker']) ?></span>
<?php endif; ?>
                <h2><?= e($s['heading']) ?></h2>
<?php if (tpl_has($s, 'lead')): ?>
                <p><?= e($s['lead']) ?></p>
<?php endif; ?>
            </div>

            <div class="experience-grid" style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 40px; align-items: center;">
                <div class="steps-wrapper" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
<?php foreach ($s['items'] as $i => $item): ?>
                    <div class="step-card">
                        <div class="step-number"><?= $i + 1 ?></div>
                        <h4><?= e($item['title']) ?></h4>
                        <p><?= e($item['text']) ?></p>
                    </div>
<?php endforeach; ?>
                </div>

                <div style="border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-md); height: 100%; min-height: 380px;">
                    <img src="<?= e($s['image']) ?>" alt="<?= e($s['image_alt']) ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                </div>
            </div>
        </div>
    </section>
<?php break;

    case 'afford': ?>
    <section class="affordability-section">
        <div class="container">
            <div class="section-header text-center">
<?php if (tpl_has($s, 'kicker')): ?>
                <span class="badge-pill" style="background: rgba(255,255,255,0.15); color: #60A5FA;"><?= e($s['kicker']) ?></span>
<?php endif; ?>
                <h2 style="color: white;"><?= e($s['heading']) ?></h2>
<?php if (tpl_has($s, 'lead')): ?>
                <p style="color: rgba(255,255,255,0.85);"><?= e($s['lead']) ?></p>
<?php endif; ?>
            </div>

            <div class="afford-horizontal-grid" style="display: grid; grid-template-columns: 0.9fr 1.1fr; gap: 40px; align-items: center; margin-top: 40px;">
                <div style="border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-md); height: 100%; min-height: 360px;">
                    <img src="<?= e($s['image']) ?>" alt="<?= e($s['image_alt']) ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px;">
<?php foreach ($s['items'] as $item): ?>
                    <div class="afford-box" style="text-align: left; padding: 22px 24px;">
                        <h3 style="font-size: 1.4rem; margin-bottom: 6px;"><?= e($item['title']) ?></h3>
                        <p style="font-size: 0.92rem; margin: 0;"><?= e($item['text']) ?></p>
                    </div>
<?php endforeach; ?>
<?php if (tpl_has($s, 'button')): ?>
                    <div style="margin-top: 10px;">
                        <a href="<?= e(booking_url()) ?>" class="btn btn-primary" style="width: 100%;"><?= e($s['button']) ?></a>
                    </div>
<?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php break;

    case 'faq': ?>
    <section class="faq-section" id="faq">
        <div class="container">
            <div class="section-header">
<?php if (tpl_has($s, 'kicker')): ?>
                <span class="badge-pill"><?= e($s['kicker']) ?></span>
<?php endif; ?>
                <h2><?= tpl_plain($s['heading']) ?></h2>
            </div>

            <div class="faq-container">
<?php foreach ($s['items'] as $i => $item): ?>
                <div class="faq-item<?= $i === 0 ? ' active' : '' ?>">
                    <button class="faq-question" onclick="toggleFaq(this)">
                        <?= e($item['q']) ?>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer"><?= e($item['a']) ?></div>
                </div>
<?php endforeach; ?>
            </div>
        </div>
    </section>
<?php break;

    case 'consult':
    $ctaImage = $s['image'];
    require APP . '/views/partials/consult-cta.php';
    break;

endswitch; ?>
<?php endforeach; ?>
</div>
