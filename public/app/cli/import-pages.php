<?php
/**
 * Imports service pages written for the "Treatment Guide" layout.
 *
 *   php app/cli/import-pages.php app/data/content/treatment-pages-2026-09.php [--dry-run] [--force]
 *
 * Each page is saved through the same parser as the admin editor, so text is
 * cleaned, links are checked and every field is stored explicitly. An existing
 * page with the same address is updated in place (its id, menu setting and
 * order are kept). A page that has been edited in the dashboard since it was
 * created is skipped unless --force is given, so nobody's work is overwritten.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap.php';
require_once APP . '/sanitize.php';
require_once APP . '/admin/pages.php';

$file   = $argv[1] ?? '';
$dryRun = in_array('--dry-run', $argv, true);
$force  = in_array('--force', $argv, true);
if ($file === '' || !is_file($file)) {
    fwrite(STDERR, "usage: php app/cli/import-pages.php CONTENT_FILE [--dry-run] [--force]\n");
    exit(1);
}

$content = require $file;
$tpl     = template('service-guide');
$pdo     = db();
$now     = date('Y-m-d H:i:s');
$find    = $pdo->prepare("SELECT * FROM pages WHERE type = 'service' AND slug = ?");
$failed  = false;

echo $dryRun ? "DRY RUN — nothing will be saved\n" : '';

$pdo->beginTransaction();
foreach ($content['pages'] as $page) {
    $parent = (string) ($page['parent'] ?? '');
    $slug   = ($parent !== '' ? $parent . '/' : '') . $page['slug'];
    $url    = '/' . $slug . '/';

    if ($parent !== '') {
        $find->execute([$parent]);
        if (!$find->fetch()) {
            echo "SKIP  {$url}  parent page /{$parent}/ does not exist\n";
            $failed = true;
            continue;
        }
    }

    $find->execute([$slug]);
    $existing = $find->fetch() ?: null;
    $movedFrom = null;
    if (!$existing && !empty($page['from'])) {
        $find->execute([$page['from']]);
        $existing  = $find->fetch() ?: null;
        $movedFrom = $existing ? '/' . $page['from'] . '/' : null;
    }

    if ($existing && !$force && $existing['updated_at'] > $existing['created_at']) {
        echo "SKIP  {$url}  edited in the dashboard on {$existing['updated_at']} (use --force to replace it)\n";
        $failed = true;
        continue;
    }

    // exactly what the editor would post
    $notes  = [];
    $posted = [
        'hero'    => $page['hero'],
        'content' => ['blocks' => $page['blocks']],
        'offices' => [],
        'consult' => ['image' => '/assets/img/IMG_20260814_125716.jpg'],
    ];
    $data = admin_page_data($tpl, $posted, $notes);
    $data['_off'] = ['consult']; // each page ends with its own call to action, then the office list

    $row = [
        'type'        => 'service',
        'slug'        => $slug,
        'title'       => $page['title'],
        'template'    => $tpl['key'],
        'description' => mb_substr($page['description'], 0, 320),
        'seo_title'   => $page['seo_title'] ?? '',
        'image'       => $page['hero']['image'],
        'data'        => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'status'      => 'published',
        'updated_at'  => $now,
    ];

    $blocks = count($data['content']['blocks']);
    if ($existing) {
        $sets = implode(', ', array_map(static fn(string $k): string => "{$k} = :{$k}", array_keys($row)));
        $pdo->prepare("UPDATE pages SET {$sets} WHERE id = :id")->execute($row + ['id' => $existing['id']]);
        // created_at follows, so the next run can still tell an import from a later edit
        $pdo->prepare('UPDATE pages SET created_at = ? WHERE id = ?')->execute([$now, $existing['id']]);
        echo 'UPDATE ' . $url . "  ({$blocks} blocks" . ($movedFrom ? ", moved from {$movedFrom}" : '') . ")\n";
    } else {
        $row += ['menu' => (int) ($page['menu'] ?? 0), 'menu_order' => (int) ($page['menu_order'] ?? 0), 'created_at' => $now];
        $cols = implode(', ', array_keys($row));
        $vals = ':' . implode(', :', array_keys($row));
        $pdo->prepare("INSERT INTO pages ({$cols}) VALUES ({$vals})")->execute($row);
        echo "CREATE {$url}  ({$blocks} blocks)\n";
    }

    if ($movedFrom && $existing['status'] === 'published') {
        admin_add_redirect($movedFrom, $url, $now);
        echo "       redirect {$movedFrom} -> {$url}\n";
    }
    $pdo->prepare('DELETE FROM redirects WHERE from_path = ?')->execute([$url]);

    foreach (array_unique($notes) as $note) {
        echo "       note: {$note}\n";
    }
}

// internal links that pointed at moved pages
$rewrites = $content['rewrite_links'] ?? [];
if ($rewrites) {
    $replace = [];
    foreach ($rewrites as $from => $to) {
        $replace['"' . $from . '"'] = '"' . $to . '"';
    }
    foreach ($pdo->query("SELECT id, type, slug, data FROM pages WHERE status != 'trash'")->fetchAll() as $other) {
        $updated = strtr($other['data'], $replace);
        if ($updated !== $other['data']) {
            $pdo->prepare('UPDATE pages SET data = ? WHERE id = ?')->execute([$updated, $other['id']]);
            echo 'LINKS ' . admin_page_url($other) . "  internal links updated\n";
        }
    }
}

if ($dryRun) {
    $pdo->rollBack();
    echo "rolled back (dry run)\n";
} else {
    $pdo->commit();
    echo "saved\n";
}
exit($failed ? 2 : 0);
