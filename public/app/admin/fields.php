<?php
declare(strict_types=1);

// Turns a template's field definitions (app/templates.php) into editor controls.
// Every control is named f[section][field], so app/admin/pages.php can read the
// whole page back with the same schema that drew it.

/** The value to show in the editor: what was saved, or the template's default. */
function admin_field_value(array $field, array $values)
{
    if (array_key_exists($field['key'], $values)) {
        return $values[$field['key']];
    }
    return $field['default'] ?? ($field['type'] === 'list' ? [] : '');
}

function admin_fields(string $name, array $fields, array $values, array $paths): void
{
    foreach ($fields as $field) {
        admin_field($name, $field, $values, $paths);
    }
}

function admin_field(string $name, array $field, array $values, array $paths): void
{
    $key   = $field['key'];
    $id    = 'f-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($name . '-' . $key));
    $input = $name . '[' . $key . ']';
    $value = admin_field_value($field, $values);
    $help  = $field['help'] ?? '';
    if (!empty($field['em'])) {
        $help = trim($help . ' Put *stars* around words to show them in orange.');
    }

    if ($field['type'] === 'list') {
        admin_field_list($input, $field, is_array($value) ? array_values($value) : [], $paths);
        return;
    }

    echo '<div class="adm-f">';
    if ($field['type'] !== 'image') {
        echo '<label class="adm-f__label" for="' . e($id) . '">' . e($field['label']) . '</label>';
    }

    switch ($field['type']) {
        case 'image':
            admin_field_image($input, $field, $values);
            break;

        case 'richtext':
            echo '<textarea id="' . e($id) . '" name="' . e($input) . '" rows="14" data-adm-rte>' . e((string) $value) . '</textarea>';
            break;

        case 'textarea':
            echo '<textarea id="' . e($id) . '" name="' . e($input) . '" rows="3" maxlength="2000">' . e((string) $value) . '</textarea>';
            break;

        case 'lines':
            echo '<textarea id="' . e($id) . '" name="' . e($input) . '" rows="5">' . e(is_array($value) ? implode("\n", $value) : (string) $value) . '</textarea>';
            break;

        case 'select':
            echo '<select id="' . e($id) . '" name="' . e($input) . '">';
            foreach ($field['options'] ?? [] as $option => $label) {
                echo '<option value="' . e($option) . '"' . ((string) $value === (string) $option ? ' selected' : '') . '>' . e($label) . '</option>';
            }
            echo '</select>';
            break;

        case 'link':
            echo '<input type="text" id="' . e($id) . '" name="' . e($input) . '" value="' . e((string) $value) . '" list="adm-paths" placeholder="/clear-aligners/" maxlength="300">';
            if ($help === '') {
                $help = 'A page on this site, for example /clear-aligners/. Leave empty for no link.';
            }
            break;

        default:
            echo '<input type="text" id="' . e($id) . '" name="' . e($input) . '" value="' . e((string) $value) . '" maxlength="300">';
    }

    if ($help !== '') {
        echo '<p class="adm-help">' . e($help) . '</p>';
    }
    echo '</div>';
}

function admin_field_image(string $input, array $field, array $values): void
{
    $src = (string) admin_field_value($field, $values);
    $alt = array_key_exists($field['key'] . '_alt', $values)
        ? (string) $values[$field['key'] . '_alt']
        : (string) ($field['alt_default'] ?? '');
    $icon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4z M4 15l4.5-4.5 4 4 3-3L20 16 M15.5 9.5h.01"/></svg>';
    ?>
    <span class="adm-f__label"><?= e($field['label']) ?></span>
    <div class="adm-img" data-adm-img>
      <input type="hidden" name="<?= e($input) ?>" value="<?= e($src) ?>" data-adm-img-input>
      <button type="button" class="adm-img__pick" data-adm-img-pick aria-label="Choose a photo">
        <img src="<?= e($src) ?>" alt="" data-adm-img-preview<?= $src === '' ? ' hidden' : '' ?>>
        <span data-adm-img-empty<?= $src !== '' ? ' hidden' : '' ?>><?= $icon ?>Choose a photo</span>
      </button>
      <input type="text" name="<?= e($input . '_alt') ?>" value="<?= e($alt) ?>" maxlength="200" placeholder="Describe the photo" data-adm-img-alt>
      <div class="adm-img__actions"<?= $src === '' ? ' hidden' : '' ?> data-adm-img-actions>
        <button type="button" class="adm-link" data-adm-img-pick>Replace</button>
        <button type="button" class="adm-link adm-link--danger" data-adm-img-remove>Remove</button>
      </div>
    </div>
    <?php
}

/** A repeating group: cards, offers, questions and the like. */
function admin_field_list(string $input, array $field, array $items, array $paths): void
{
    $label = $field['item'] ?? 'Item';
    $max   = (int) ($field['max'] ?? 20);
    ?>
    <div class="adm-list" data-adm-list data-name="<?= e($input) ?>" data-max="<?= $max ?>" data-label="<?= e($label) ?>">
      <div class="adm-list__head">
        <span class="adm-f__label"><?= e($field['label']) ?></span>
        <span class="adm-help" data-adm-list-count></span>
      </div>
      <div class="adm-list__rows" data-adm-list-rows>
<?php foreach ($items as $i => $item): ?>
<?= admin_field_row($input, $field, (array) $item, (string) $i, $label, $paths) ?>
<?php endforeach; ?>
      </div>
      <template data-adm-list-template><?= admin_field_row($input, $field, [], '__i__', $label, $paths) ?></template>
      <button type="button" class="adm-btn" data-adm-list-add>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Add <?= e(strtolower($label)) ?>
      </button>
    </div>
    <?php
}

function admin_field_row(string $input, array $field, array $item, string $index, string $label, array $paths): string
{
    ob_start();
    ?>
        <div class="adm-item" data-adm-item>
          <div class="adm-item__head">
            <b data-adm-item-title><?= e($label) ?></b>
            <div class="adm-item__tools">
              <button type="button" class="adm-icon-btn" data-adm-item-up aria-label="Move up"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg></button>
              <button type="button" class="adm-icon-btn" data-adm-item-down aria-label="Move down"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12l7 7 7-7"/></svg></button>
              <button type="button" class="adm-icon-btn" data-adm-item-remove aria-label="Remove <?= e(strtolower($label)) ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
            </div>
          </div>
          <div class="adm-item__body">
<?php admin_fields($input . '[' . $index . ']', $field['fields'], $item, $paths); ?>
          </div>
        </div>
    <?php
    return (string) ob_get_clean();
}

/** The little wireframe shown on each layout card. */
function admin_shape(array $shape): string
{
    $out = '';
    foreach ($shape as $row) {
        $out .= '<i class="adm-shape__' . e($row) . '"></i>';
    }
    return '<span class="adm-shape" aria-hidden="true">' . $out . '</span>';
}
