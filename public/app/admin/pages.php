<?php
declare(strict_types=1);

// Admin CMS: service pages and office pages (used by app/admin/router.php).
// A page's content is whatever its template (app/templates.php) defines, so the
// editor form, the saved data and the validation all come from the same schema.

const PAGE_TYPES = [
    'service'  => ['Service Pages', 'Treatment and information pages, at igniteorthodontics.com/page-name/'],
    'location' => ['Locations', 'One page per office, at igniteorthodontics.com/locations/office/'],
];

function admin_page_type(string $value): string
{
    return isset(PAGE_TYPES[$value]) ? $value : 'service';
}

function admin_page_url(array $row): string
{
    return $row['type'] === 'location' ? '/locations/' . $row['slug'] . '/' : '/' . $row['slug'] . '/';
}

function admin_page_state(array $row): string
{
    return ['published' => 'Published', 'trash' => 'Trash'][$row['status']] ?? 'Draft';
}

function admin_find_page(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM pages WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/** Addresses a service page may not take, because something else answers there. */
function admin_reserved_slugs(): array
{
    return array_merge(
        ['admin', 'blog', 'book', 'locations', 'assets', 'uploads', 'admin-assets', 'sitemap.xml', 'robots.txt',
         'thank-you', 'thankyou', 'booking', 'book-now', 'appointment', 'home', 'contact', 'about', 'terms-of-service'],
        array_keys(pages())
    );
}

/* ============================================================
   LIST
   ============================================================ */

function admin_pages_index(array $user): void
{
    $type = admin_page_type((string) ($_GET['type'] ?? 'service'));
    $tab  = (string) ($_GET['status'] ?? 'all');
    $q    = trim((string) ($_GET['q'] ?? ''));
    $GLOBALS['admin_nav_type'] = $type;

    $where = ['type = ?', "status != 'trash'"];
    $args  = [$type];
    if (in_array($tab, ['published', 'draft', 'trash'], true)) {
        $where = ['type = ?', 'status = ?'];
        $args  = [$type, $tab];
    } else {
        $tab = 'all';
    }
    if ($q !== '') {
        $where[] = '(title LIKE ? OR slug LIKE ?)';
        array_push($args, '%' . $q . '%', '%' . $q . '%');
    }
    $stmt = db()->prepare('SELECT * FROM pages WHERE ' . implode(' AND ', $where) . ' ORDER BY menu_order, title');
    $stmt->execute($args);
    $rows = $stmt->fetchAll();

    $counts = db()->prepare(
        "SELECT SUM(status != 'trash') AS all_pages,
                SUM(status = 'published') AS published,
                SUM(status = 'draft') AS draft,
                SUM(status = 'trash') AS trash
         FROM pages WHERE type = ?"
    );
    $counts->execute([$type]);

    admin_render('pages', [
        'rows' => $rows, 'type' => $type, 'tab' => $tab, 'q' => $q,
        'counts' => array_map('intval', $counts->fetch()),
    ], PAGE_TYPES[$type][0], $user);
}

/* ============================================================
   EDITOR
   ============================================================ */

function admin_blank_page(string $type, string $template): array
{
    return [
        'id' => 0, 'type' => $type, 'slug' => '', 'title' => '', 'template' => $template,
        'description' => '', 'seo_title' => '', 'image' => '', 'data' => '{}',
        'menu' => 0, 'menu_order' => 0, 'status' => 'draft', 'created_at' => null, 'updated_at' => null,
    ];
}

/** Add New: pick a layout first, then open the editor with that layout. */
function admin_page_new(array $user): void
{
    $type = admin_page_type((string) ($_GET['type'] ?? 'service'));
    $GLOBALS['admin_nav_type'] = $type;
    $template = (string) ($_GET['template'] ?? '');
    $chosen   = template($template);

    if ($chosen && $chosen['type'] === $type) {
        admin_page_editor($user, null, admin_blank_page($type, $template));
        return;
    }
    admin_render('page-new', ['type' => $type, 'templates' => templates_for($type)], 'Choose a layout', $user);
}

function admin_page_editor(array $user, ?int $id, ?array $blank = null): void
{
    $row = $id ? admin_find_page($id) : $blank;
    if (!$row) {
        admin_not_found($user);
    }
    $GLOBALS['admin_nav_type'] = $row['type'];

    // input that failed validation, or a layout change waiting to be saved
    admin_session();
    $old = $_SESSION['page_form'] ?? null;
    unset($_SESSION['page_form']);
    if (is_array($old) && (int) $old['id'] === (int) $row['id'] && $old['type'] === $row['type']) {
        $row = array_merge($row, $old);
    }

    $tpl = template($row['template']) ?? template(template_default($row['type']));
    admin_render('page-edit', [
        'row'   => $row,
        'tpl'   => $tpl,
        'data'  => json_decode((string) $row['data'], true) ?: [],
        'paths' => admin_internal_paths(),
        'parents' => $row['type'] === 'service' ? admin_parent_options((int) $row['id']) : [],
        'hasChildren' => admin_page_has_children($row),
    ], $id ? 'Edit page' : 'Add new page', $user);
}

/** Every address on this site, offered as suggestions for "Links to" fields. */
function admin_internal_paths(): array
{
    $paths = ['/', '/blog/', '/locations/'];
    foreach (array_keys(pages()) as $slug) {
        $paths[] = '/' . $slug . '/';
    }
    foreach (['service', 'location'] as $type) {
        $stmt = db()->prepare("SELECT type, slug FROM pages WHERE type = ? AND status = 'published' ORDER BY slug");
        $stmt->execute([$type]);
        foreach ($stmt as $row) {
            $paths[] = admin_page_url($row);
        }
    }
    sort($paths);
    return array_values(array_unique($paths));
}

/* ============================================================
   SAVING
   ============================================================ */

/** Reads the posted fields for one template, cleaning every value by its type. */
function admin_page_data(array $tpl, array $posted, array &$notes): array
{
    $data = [];
    foreach ($tpl['sections'] as $key => $section) {
        $data[$key] = admin_page_fields($section['fields'] ?? [], (array) ($posted[$key] ?? []), $notes);
    }
    return $data;
}

function admin_page_fields(array $fields, array $posted, array &$notes): array
{
    $out = [];
    foreach ($fields as $field) {
        $key   = $field['key'];
        $value = $posted[$key] ?? null;

        switch ($field['type']) {
            case 'blocks':
                $blocks = [];
                foreach ((array) $value as $block) {
                    $block = (array) $block;
                    $type  = (string) ($block['_type'] ?? '');
                    if (isset($field['types'][$type])) {
                        $blocks[] = ['_type' => $type] + admin_page_fields($field['types'][$type]['fields'], $block, $notes);
                    }
                }
                $out[$key] = array_slice($blocks, 0, 60);
                break;

            case 'list':
                $items = [];
                foreach ((array) $value as $item) {
                    $row = admin_page_fields($field['fields'], (array) $item, $notes);
                    // a row where everything was cleared is dropped
                    if (implode('', array_map(static fn($v): string => is_array($v) ? implode('', $v) : (string) $v, $row)) !== '') {
                        $items[] = $row;
                    }
                }
                $out[$key] = array_slice($items, 0, $field['max'] ?? 20);
                break;

            case 'richtext':
                [$html, $htmlNotes] = clean_post_html((string) $value);
                $out[$key] = $html;
                $notes = array_merge($notes, $htmlNotes);
                break;

            case 'image':
                $src = trim((string) $value);
                if ($src !== '' && !clean_is_local_media($src)) {
                    $src = '';
                    $notes[] = 'A photo that was not from the media library was removed.';
                }
                $out[$key] = $src;
                $out[$key . '_alt'] = mb_substr(trim((string) ($posted[$key . '_alt'] ?? '')), 0, 200);
                break;

            case 'link':
                $href = trim((string) $value);
                $clean = $href === '' ? '' : (clean_internal_href($href) ?? '');
                if ($href !== '' && $clean === '') {
                    $notes[] = 'A link to another website was removed. Links can only point to pages on this site.';
                }
                $out[$key] = $clean;
                break;

            case 'select':
                $options = array_keys($field['options'] ?? []);
                $out[$key] = in_array((string) $value, $options, true) ? (string) $value : (string) ($options[0] ?? '');
                break;

            case 'lines':
                $lines = preg_split('/\R/', (string) $value) ?: [];
                $lines = array_filter(array_map('trim', $lines), static fn(string $l): bool => $l !== '');
                $out[$key] = implode("\n", array_slice(array_map(static fn(string $l): string => mb_substr($l, 0, 300), $lines), 0, 40));
                break;

            case 'textarea':
                $out[$key] = mb_substr(trim((string) $value), 0, 2000);
                break;

            default:
                $out[$key] = mb_substr(trim((string) preg_replace('/\s+/', ' ', (string) $value)), 0, 300);
        }
    }
    return $out;
}

/** A free address for a page; under $parent it becomes parent/slug. */
function admin_page_unique_slug(string $source, string $type, int $id, string $parent = ''): string
{
    $leaf = strpos($source, '/') !== false ? substr((string) strrchr($source, '/'), 1) : $source;
    $base = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($leaf)), '-');
    $base = substr($base, 0, 80) ?: 'page';
    if ($type === 'service') {
        $reserved = $parent === '' ? admin_reserved_slugs() : [];
    } else {
        $reserved = ['8-mile', 'lathrup-village'];
    }
    $prefix = $parent !== '' ? $parent . '/' : '';
    $slug = $base;
    $find = db()->prepare('SELECT 1 FROM pages WHERE type = ? AND slug = ? AND id != ?');
    for ($i = 2; ; $i++) {
        $find->execute([$type, $prefix . $slug, $id]);
        if (!$find->fetchColumn() && !in_array($slug, $reserved, true)) {
            return $prefix . $slug;
        }
        $slug = $base . '-' . $i;
    }
}

/** Top-level service pages another page can sit under, as slug => title. */
function admin_parent_options(int $id): array
{
    $stmt = db()->prepare("SELECT slug, title FROM pages WHERE type = 'service' AND status != 'trash' AND id != ? AND instr(slug, '/') = 0 ORDER BY title");
    $stmt->execute([$id]);
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

/** Whether other pages sit under this one (then it cannot move under a parent itself). */
function admin_page_has_children(array $row): bool
{
    if ($row['type'] !== 'service' || $row['slug'] === '' || strpos($row['slug'], '/') !== false) {
        return false;
    }
    $stmt = db()->prepare("SELECT 1 FROM pages WHERE type = 'service' AND slug LIKE ? LIMIT 1");
    $stmt->execute([$row['slug'] . '/%']);
    return (bool) $stmt->fetchColumn();
}

/** Keeps old links working: old address -> new address, and earlier redirects follow along. */
function admin_add_redirect(string $from, string $to, string $now): void
{
    $pdo = db();
    $pdo->prepare('INSERT INTO redirects (from_path, to_path, created_at) VALUES (?, ?, ?)
                   ON CONFLICT(from_path) DO UPDATE SET to_path = excluded.to_path')->execute([$from, $to, $now]);
    $pdo->prepare('UPDATE redirects SET to_path = ? WHERE to_path = ?')->execute([$to, $from]);
    $pdo->prepare('DELETE FROM redirects WHERE from_path = to_path')->execute();
}

function admin_save_page(array $user): void
{
    $pdo      = db();
    $id       = (int) ($_POST['id'] ?? 0);
    $existing = $id ? admin_find_page($id) : null;
    if ($id && !$existing) {
        admin_not_found($user);
    }
    $action = (string) ($_POST['action'] ?? 'save_draft'); // save_draft | publish | update | unpublish | switch_template
    $type   = admin_page_type((string) ($_POST['type'] ?? ($existing['type'] ?? 'service')));

    $tpl = template((string) ($_POST['template'] ?? '')) ?? template(template_default($type));
    if ($tpl['type'] !== $type) {
        $tpl = template(template_default($type));
    }

    $title      = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 120);
    $seoTitle   = mb_substr(trim((string) ($_POST['seo_title'] ?? '')), 0, 200);
    $descrip    = mb_substr(trim((string) preg_replace('/\s+/', ' ', (string) ($_POST['description'] ?? ''))), 0, 320);
    $menu       = empty($_POST['menu']) ? 0 : 1;
    $menuOrder  = max(0, min(999, (int) ($_POST['menu_order'] ?? 0)));
    $slugSource = trim((string) ($_POST['slug'] ?? ''));

    $notes = [];

    // a service page can sit under a top-level service page: /types-of-braces/ceramic-braces/
    $parent = $type === 'service' ? trim((string) ($_POST['parent'] ?? '')) : '';
    if ($parent !== '' && !array_key_exists($parent, admin_parent_options($id))) {
        $parent = '';
    }
    if ($parent !== '' && $existing && admin_page_has_children($existing)) {
        $parent = '';
        $notes[] = 'Other pages sit under this page, so it stays at the top level.';
    }
    $slugTyped = ($parent !== '' ? $parent . '/' : '') . $slugSource;

    $data  = admin_page_data($tpl, (array) ($_POST['f'] ?? []), $notes);

    // sections the editor left switched off
    $shown = (array) ($_POST['show'] ?? []);
    $off   = [];
    foreach ($tpl['sections'] as $key => $section) {
        if (empty($section['locked']) && empty($shown[$key])) {
            $off[] = $key;
        }
    }
    $data['_off'] = $off;

    $image = trim((string) ($_POST['image'] ?? ''));
    if ($image !== '' && !clean_is_local_media($image)) {
        $image = '';
        $notes[] = 'The share image was not from the media library, so it was removed.';
    }

    // switching layout keeps the typed content in the session and reopens the editor
    if ($action === 'switch_template') {
        admin_session();
        $_SESSION['page_form'] = compact('id', 'type', 'title', 'seoTitle', 'descrip', 'menu', 'menuOrder', 'image') + [
            'slug' => $slugTyped, 'template' => $tpl['key'], 'description' => $descrip,
            'seo_title' => $seoTitle, 'menu_order' => $menuOrder, 'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
        admin_flash('success', 'Layout switched to ' . $tpl['name'] . '. Nothing is live until you save.');
        redirect($id ? "/admin/pages/{$id}/" : '/admin/pages/new/?type=' . $type . '&template=' . $tpl['key'], 303);
    }

    $status = $existing['status'] ?? 'draft';
    if (in_array($action, ['publish', 'update'], true)) {
        $status = 'published';
    } elseif (in_array($action, ['save_draft', 'unpublish'], true) || $status === 'trash') {
        $status = 'draft';
    }

    $slug = admin_page_unique_slug($slugSource !== '' ? $slugSource : ($title !== '' ? $title : 'page-' . date('YmdHis')), $type, $id, $parent);

    $errors = [];
    if ($title === '') {
        $errors[] = $type === 'location' ? 'Add the office name before saving.' : 'Add a page title before saving.';
    }
    if ($status === 'published' && $descrip === '') {
        $errors[] = 'Add a summary before publishing. It appears in Google results and on treatment cards.';
    }
    if ($errors) {
        admin_session();
        $_SESSION['page_form'] = [
            'id' => $id, 'type' => $type, 'title' => $title, 'slug' => $slugTyped, 'template' => $tpl['key'],
            'description' => $descrip, 'seo_title' => $seoTitle, 'image' => $image, 'menu' => $menu,
            'menu_order' => $menuOrder, 'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
        foreach ($errors as $error) {
            admin_flash('error', $error);
        }
        redirect($id ? "/admin/pages/{$id}/" : '/admin/pages/new/?type=' . $type . '&template=' . $tpl['key'], 303);
    }

    $now    = date('Y-m-d H:i:s');
    $fields = [
        'type' => $type, 'slug' => $slug, 'title' => $title, 'template' => $tpl['key'],
        'description' => $descrip, 'seo_title' => $seoTitle, 'image' => $image,
        'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'menu' => $menu, 'menu_order' => $menuOrder, 'status' => $status, 'updated_at' => $now,
    ];

    $pdo->beginTransaction();
    if ($existing) {
        $sets = implode(', ', array_map(static fn(string $k): string => "{$k} = :{$k}", array_keys($fields)));
        $pdo->prepare("UPDATE pages SET {$sets} WHERE id = :id")->execute($fields + ['id' => $id]);
    } else {
        $fields += ['created_at' => $now, 'author_id' => (int) $user['id']];
        $cols = implode(', ', array_keys($fields));
        $vals = ':' . implode(', :', array_keys($fields));
        $pdo->prepare("INSERT INTO pages ({$cols}) VALUES ({$vals})")->execute($fields);
        $id = (int) $pdo->lastInsertId();
    }
    $pdo->commit();

    // a published page that moved keeps its old address working
    $wasPublished = $existing && $existing['status'] === 'published';
    $newUrl = admin_page_url(['type' => $type, 'slug' => $slug]);
    if ($wasPublished && $existing['slug'] !== $slug) {
        $oldUrl = admin_page_url(['type' => $type, 'slug' => $existing['slug']]);
        admin_add_redirect($oldUrl, $newUrl, $now);
        $notes[] = 'Visitors who follow the old address ' . $oldUrl . ' are now sent to the new one.';
    }
    // pages under this one move with it
    if ($existing && $type === 'service' && $existing['slug'] !== $slug && strpos($existing['slug'], '/') === false) {
        $children = $pdo->prepare("SELECT id, slug, status FROM pages WHERE type = 'service' AND slug LIKE ?");
        $children->execute([$existing['slug'] . '/%']);
        foreach ($children->fetchAll() as $child) {
            $childSlug = $slug . substr($child['slug'], strlen($existing['slug']));
            $pdo->prepare('UPDATE pages SET slug = ? WHERE id = ?')->execute([$childSlug, $child['id']]);
            if ($child['status'] === 'published') {
                admin_add_redirect('/' . $child['slug'] . '/', '/' . $childSlug . '/', $now);
            }
        }
    }
    $pdo->prepare('DELETE FROM redirects WHERE from_path = ?')->execute([$newUrl]);

    if ($status === 'published') {
        $message = $wasPublished ? 'Page updated.' : 'Page published at ' . $newUrl;
    } else {
        $message = $wasPublished ? 'Page switched to draft. It is no longer visible on the site.' : 'Draft saved.';
    }
    admin_flash('success', $message);
    foreach (array_unique($notes) as $note) {
        admin_flash('warning', $note);
    }
    redirect("/admin/pages/{$id}/", 303);
}

/* ============================================================
   PREVIEW, TRASH, DUPLICATE
   ============================================================ */

function admin_page_preview(int $id): void
{
    $row = admin_find_page($id);
    if (!$row) {
        admin_not_found(current_admin());
    }
    page_render(page_prepare($row), ['noindex' => true, 'preview_page' => $id, 'title' => 'Preview: ' . $row['title']]);
}

/** Preview of what is on screen in the editor, including changes that are not saved. */
function admin_page_preview_post(array $user): void
{
    $id   = (int) ($_POST['id'] ?? 0);
    $row  = $id ? admin_find_page($id) : null;
    $type = admin_page_type((string) ($_POST['type'] ?? ($row['type'] ?? 'service')));
    $tpl  = template((string) ($_POST['template'] ?? '')) ?? template(template_default($type));

    $notes = [];
    $data  = admin_page_data($tpl, (array) ($_POST['f'] ?? []), $notes);
    $shown = (array) ($_POST['show'] ?? []);
    $off   = [];
    foreach ($tpl['sections'] as $key => $section) {
        if (empty($section['locked']) && empty($shown[$key])) {
            $off[] = $key;
        }
    }
    $data['_off'] = $off;

    $draft = ($row ?? admin_blank_page($type, $tpl['key'])) + [];
    $draft['type']     = $type;
    $draft['template'] = $tpl['key'];
    $draft['title']    = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 120) ?: 'Untitled page';
    $previewParent    = $type === 'service' ? trim((string) ($_POST['parent'] ?? '')) : '';
    $draft['slug']     = ($previewParent !== '' ? $previewParent . '/' : '') . (trim((string) ($_POST['slug'] ?? '')) ?: 'preview');
    $draft['description'] = mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 320);
    $draft['seo_title']   = '';
    $draft['image']       = '';
    $draft['data']        = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $draft['updated_at']  = date('Y-m-d H:i:s');

    page_render(page_prepare($draft), ['noindex' => true, 'preview_page' => $id, 'title' => 'Preview: ' . $draft['title']]);
}

function admin_page_action(int $id, string $action): void
{
    $row = admin_find_page($id);
    if (!$row) {
        redirect('/admin/pages/', 303);
    }
    $type  = $row['type'];
    $title = $row['title'] !== '' ? '"' . $row['title'] . '"' : 'The page';
    $now   = date('Y-m-d H:i:s');

    if ($action === 'trash') {
        db()->prepare("UPDATE pages SET status = 'trash', menu = 0, updated_at = ? WHERE id = ?")->execute([$now, $id]);
        admin_flash('success', $title . ' was moved to the trash and is no longer on the site.');
        redirect('/admin/pages/?type=' . $type, 303);
    }
    if ($action === 'restore') {
        db()->prepare("UPDATE pages SET status = 'draft', updated_at = ? WHERE id = ?")->execute([$now, $id]);
        admin_flash('success', $title . ' was restored as a draft.');
        redirect('/admin/pages/?type=' . $type . '&status=trash', 303);
    }
    if ($action === 'duplicate') {
        $copy = $row;
        unset($copy['id']);
        $copy['title']      = mb_substr($row['title'] . ' (copy)', 0, 120);
        $copy['slug']       = admin_page_unique_slug($row['slug'] . '-copy', $type, 0, strpos($row['slug'], '/') !== false ? (string) strstr($row['slug'], '/', true) : '');
        $copy['status']     = 'draft';
        $copy['menu']       = 0;
        $copy['created_at'] = $copy['updated_at'] = $now;
        $cols = implode(', ', array_keys($copy));
        $vals = ':' . implode(', :', array_keys($copy));
        db()->prepare("INSERT INTO pages ({$cols}) VALUES ({$vals})")->execute($copy);
        admin_flash('success', 'Copied ' . $title . ' into a new draft.');
        redirect('/admin/pages/' . (int) db()->lastInsertId() . '/', 303);
    }
    if ($action === 'delete' && $row['status'] === 'trash') {
        db()->prepare('DELETE FROM pages WHERE id = ?')->execute([$id]);
        admin_flash('success', $title . ' was permanently deleted.');
    }
    redirect('/admin/pages/?type=' . $type . '&status=trash', 303);
}
