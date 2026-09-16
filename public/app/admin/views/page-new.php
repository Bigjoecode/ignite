<?php
// Vars: $type, $templates — the first step of Add New: choose a layout.
[$heading] = PAGE_TYPES[$type];
?>
<div class="adm-head">
  <h1><?= $type === 'location' ? 'Add a new office page' : 'Add a new service page' ?></h1>
  <a class="adm-btn" href="/admin/pages/?type=<?= e($type) ?>">Back to <?= e(strtolower($heading)) ?></a>
  <p class="adm-head__sub">Pick the layout to start from. Every layout arrives filled with example content you can edit, and you can switch layouts later without losing your words.</p>
</div>

<div class="adm-tpls">
<?php foreach ($templates as $key => $tpl): ?>
  <a class="adm-tpl" href="/admin/pages/new/?type=<?= e($type) ?>&template=<?= e($key) ?>">
    <?= admin_shape($tpl['shape']) ?>
    <div class="adm-tpl__body">
      <h2><?= e($tpl['name']) ?></h2>
      <p><?= e($tpl['tagline']) ?></p>
      <p class="adm-tpl__sections"><?= count($tpl['sections']) ?> sections you can switch on or off</p>
    </div>
    <span class="adm-tpl__go">Use this layout &rarr;</span>
  </a>
<?php endforeach; ?>
</div>
