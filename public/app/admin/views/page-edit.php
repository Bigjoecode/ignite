<?php
// Vars: $row (pages row, or rejected input merged in), $tpl (template), $data, $paths, $parents, $hasChildren
$id     = (int) $row['id'];
$type   = $row['type'];
$state  = $id ? admin_page_state($row) : 'Draft';
$isLive = $id && $row['status'] === 'published';
$url    = admin_page_url($row);
$base   = $type === 'location' ? 'igniteorthodontics.com/locations/' : 'igniteorthodontics.com/';
// a nested service page is stored as parent/page: the editor shows the two parts separately
$slugParts  = explode('/', (string) $row['slug'], 2);
$parentSlug = count($slugParts) === 2 ? $slugParts[0] : '';
$leafSlug   = count($slugParts) === 2 ? $slugParts[1] : $slugParts[0];
$prefix     = $parentSlug !== '' ? $parentSlug . '/' : '';
// which sections are switched on: what was saved, or the template's own defaults
$hidden = array_key_exists('_off', $data) ? array_flip((array) $data['_off']) : null;
?>
<form class="adm-editor adm-editor--page" method="post" action="/admin/pages/save/" data-adm-editor data-adm-page novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= $id ?>">
  <input type="hidden" name="type" value="<?= e($type) ?>">

  <div class="adm-head">
    <h1><?= $id ? 'Edit page' : 'Add new page' ?></h1>
<?php if ($id): ?>
    <a class="adm-btn" href="/admin/pages/new/?type=<?= e($type) ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Add New</a>
<?php endif; ?>
  </div>

  <div class="adm-editor__grid">
    <div class="adm-editor__main">

      <input class="adm-title-input" type="text" name="title" value="<?= e($row['title']) ?>"
             placeholder="<?= $type === 'location' ? 'Office name, for example Sterling Heights' : 'Add title' ?>"
             aria-label="Page title" maxlength="120" data-adm-title>

      <div class="adm-permalink">
        <span class="adm-permalink__label">Web address:</span>
        <span class="adm-permalink__base"><?= e($base) ?><span data-adm-parent-prefix><?= e($prefix) ?></span></span>
        <input type="text" name="slug" value="<?= e($leafSlug) ?>" placeholder="created-from-the-title" aria-label="Page web address" maxlength="80" data-adm-slug data-locked="<?= $leafSlug !== '' ? '1' : '0' ?>">
        <span>/</span>
<?php if ($isLive): ?>
        <a href="<?= e($url) ?>" target="_blank" rel="noopener">View page &nearr;</a>
<?php endif; ?>
      </div>
<?php if ($isLive): ?>
      <p class="adm-help adm-help--warn" data-adm-slug-warning hidden>Changing the address of a live page moves it. Visitors following the old link are sent to the new address automatically.</p>
<?php endif; ?>

      <p class="adm-help adm-sections__intro">
        This page is built from the sections below. Open a section to edit it, and switch off any you do not want.
<?php if ($type === 'location'): ?>
        Type <code>{office}</code> in any text to drop in the office name.
<?php endif; ?>
      </p>

      <div class="adm-sections" data-adm-sections>
<?php foreach ($tpl['sections'] as $key => $section):
    $locked = !empty($section['locked']);
    $on     = $hidden !== null ? !isset($hidden[$key]) : empty($section['off']);
?>
        <section class="adm-sec<?= $on ? '' : ' is-off' ?>" data-adm-sec>
          <header class="adm-sec__head">
            <button type="button" class="adm-sec__toggle" data-adm-sec-toggle aria-expanded="false">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
              <span><?= e($section['label']) ?></span>
            </button>
<?php if ($locked): ?>
            <input type="hidden" name="show[<?= e($key) ?>]" value="1">
            <span class="adm-sec__always">Always on</span>
<?php else: ?>
            <label class="adm-switch">
              <input type="checkbox" name="show[<?= e($key) ?>]" value="1"<?= $on ? ' checked' : '' ?> data-adm-sec-show>
              <span>Show on the page</span>
            </label>
<?php endif; ?>
          </header>
          <div class="adm-sec__body" hidden>
<?php if (!empty($section['help'])): ?>
            <p class="adm-help adm-sec__help"><?= e($section['help']) ?></p>
<?php endif; ?>
<?php if (empty($section['fields'])): ?>
            <p class="adm-help">This section has no settings &mdash; switch it on to show it.</p>
<?php else: ?>
<?php admin_fields('f[' . $key . ']', $section['fields'], (array) ($data[$key] ?? []), $paths); ?>
<?php endif; ?>
          </div>
        </section>
<?php endforeach; ?>
      </div>
    </div>

    <aside class="adm-editor__side">

      <section class="adm-card adm-box adm-publish">
        <h2>Publish</h2>
        <dl class="adm-publish__meta">
          <div><dt>Status</dt><dd><span class="adm-status adm-status--<?= e(strtolower($state)) ?>"><?= e($state) ?></span></dd></div>
          <div><dt>Address</dt><dd class="adm-publish__url"><?= e($type === 'location' ? '/locations/' : '/') ?><span data-adm-parent-prefix><?= e($prefix) ?></span><span data-adm-url-slug><?= e($leafSlug !== '' ? $leafSlug : '…') ?></span>/</dd></div>
<?php if ($id && $row['updated_at']): ?>
          <div><dt>Last saved</dt><dd><?= e(admin_datetime($row['updated_at'])) ?></dd></div>
<?php endif; ?>
        </dl>

        <div class="adm-publish__row">
<?php if (!$isLive): ?>
          <button type="submit" name="action" value="save_draft" class="adm-btn">Save Draft</button>
<?php else: ?>
          <button type="submit" name="action" value="unpublish" class="adm-btn" data-adm-confirm="Switch this page to a draft? It will be removed from the site until you publish it again.">Switch to Draft</button>
<?php endif; ?>
          <button type="submit" class="adm-btn" formaction="/admin/pages/preview/" formtarget="_blank" data-adm-preview-post>Preview</button>
        </div>

        <div class="adm-publish__foot">
<?php if ($id): ?>
          <button type="submit" formaction="/admin/pages/<?= $id ?>/trash/" class="adm-link adm-link--danger" data-adm-confirm="Move this page to the trash? It will be removed from the site.">Move to Trash</button>
<?php else: ?>
          <span></span>
<?php endif; ?>
<?php if ($isLive): ?>
          <button type="submit" name="action" value="update" class="adm-btn adm-btn--primary">Update</button>
<?php else: ?>
          <button type="submit" name="action" value="publish" class="adm-btn adm-btn--primary">Publish</button>
<?php endif; ?>
        </div>
      </section>

      <section class="adm-card adm-box">
        <h2><label for="adm-template">Layout</label></h2>
        <select id="adm-template" name="template" data-adm-template>
<?php foreach (templates_for($type) as $key => $option): ?>
          <option value="<?= e($key) ?>"<?= $key === $tpl['key'] ? ' selected' : '' ?>><?= e($option['name']) ?></option>
<?php endforeach; ?>
        </select>
        <p class="adm-help"><?= e($tpl['tagline']) ?></p>
        <button type="submit" name="action" value="switch_template" class="adm-btn" data-adm-template-apply hidden>Switch layout</button>
        <p class="adm-help" data-adm-template-note hidden>Your words are kept. Sections the new layout does not have are remembered in case you switch back.</p>
      </section>

      <section class="adm-card adm-box">
        <h2>Search results</h2>
        <p class="adm-help">How this page looks in Google. The summary is also used on treatment cards.</p>
        <div class="adm-seo" data-adm-seo>
          <p class="adm-seo__title" data-adm-seo-title></p>
          <p class="adm-seo__url">igniteorthodontics.com<?= e($type === 'location' ? '/locations/' : '/') ?><span data-adm-parent-prefix><?= e($prefix) ?></span><span data-adm-url-slug><?= e($leafSlug !== '' ? $leafSlug : '…') ?></span>/</p>
          <p class="adm-seo__desc" data-adm-seo-desc></p>
        </div>
        <label class="adm-field">
          <span>Summary</span>
          <textarea name="description" rows="3" maxlength="320" data-adm-count="160" data-adm-seo-desc-input><?= e($row['description']) ?></textarea>
          <p class="adm-counter" data-adm-counter aria-live="polite"></p>
        </label>
        <label class="adm-field">
          <span>Browser tab title (optional)</span>
          <input type="text" name="seo_title" value="<?= e($row['seo_title']) ?>" maxlength="200" placeholder="<?= e($row['title'] !== '' ? $row['title'] . ' | Ignite Orthodontics' : 'Page name | Ignite Orthodontics') ?>" data-adm-seo-title-input>
        </label>
      </section>

      <section class="adm-card adm-box">
        <h2><?= $type === 'location' ? 'Menu order' : 'Menu' ?></h2>
<?php if ($type === 'service'): ?>
        <label class="adm-check">
          <input type="checkbox" name="menu" value="1"<?= $row['menu'] ? ' checked' : '' ?>>
          <span>Show this page in the Treatments menu and in the footer</span>
        </label>
<?php endif; ?>
<?php if ($type === 'service'): ?>
        <label class="adm-field">
          <span>Parent page</span>
          <select name="parent" data-adm-parent<?= $hasChildren ? ' disabled' : '' ?>>
            <option value="">None &mdash; top level</option>
<?php foreach ($parents as $parentKey => $parentTitle): ?>
            <option value="<?= e($parentKey) ?>"<?= $parentKey === $parentSlug ? ' selected' : '' ?>><?= e($parentTitle) ?></option>
<?php endforeach; ?>
          </select>
        </label>
        <p class="adm-help"><?= $hasChildren ? 'Other pages sit under this one, so it stays at the top level.' : 'Puts this page under another, for example /types-of-braces/ceramic-braces/.' ?></p>
<?php endif; ?>
        <label class="adm-field">
          <span>Order</span>
          <input type="text" inputmode="numeric" name="menu_order" value="<?= (int) $row['menu_order'] ?>" maxlength="3">
        </label>
        <p class="adm-help">Lower numbers come first<?= $type === 'location' ? ' in the locations menu, the office list and the booking popup.' : '.' ?></p>
      </section>

      <section class="adm-card adm-box">
        <h2>Share image</h2>
        <p class="adm-help">Used when the page is shared on social media.</p>
<?php admin_field_image('image', ['key' => 'image', 'label' => 'Image', 'type' => 'image'], ['image' => $row['image']]); ?>
      </section>
    </aside>
  </div>

  <datalist id="adm-paths">
<?php foreach ($paths as $path): ?>
    <option value="<?= e($path) ?>"></option>
<?php endforeach; ?>
  </datalist>
</form>

<script src="<?= admin_asset('tinymce/tinymce.min.js') ?>"></script>
