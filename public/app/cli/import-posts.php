<?php
/**
 * Publishes written articles into the blog.
 *
 *   php app/cli/import-posts.php app/data/content/blog-2026-10.php [--dry-run] [--force]
 *
 * Each article is cleaned exactly as the dashboard editor would clean it, so links
 * and images pointing at other websites are stripped and only the allowed markup
 * survives. An article with the same address is updated in place. One that has been
 * edited in the dashboard since it was imported is skipped unless --force is given.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap.php';
require_once APP . '/sanitize.php';

$file   = $argv[1] ?? '';
$dryRun = in_array('--dry-run', $argv, true);
$force  = in_array('--force', $argv, true);
if ($file === '' || !is_file($file)) {
    fwrite(STDERR, "usage: php app/cli/import-posts.php CONTENT_FILE [--dry-run] [--force]\n");
    exit(1);
}

$content = require $file;
$pdo     = db();
$now     = date('Y-m-d H:i:s');
$find    = $pdo->prepare('SELECT * FROM posts WHERE slug = ?');
$failed  = false;

echo $dryRun ? "DRY RUN — nothing will be saved\n" : '';

$pdo->beginTransaction();
foreach ($content['posts'] as $post) {
    $slug = $post['slug'];
    $find->execute([$slug]);
    $existing = $find->fetch() ?: null;

    if ($existing && !$force && $existing['updated_at'] > $existing['created_at']) {
        echo "SKIP  /blog/{$slug}/  edited in the dashboard on {$existing['updated_at']} (use --force to replace it)\n";
        $failed = true;
        continue;
    }

    [$body, $notes] = clean_post_html($post['body']);

    $faq = [];
    foreach ($post['faq'] ?? [] as [$question, $answer]) {
        $faq[] = [mb_substr($question, 0, 300), mb_substr($answer, 0, 2000)];
    }

    $row = [
        'slug'         => $slug,
        'title'        => $post['title'],
        'description'  => mb_substr($post['description'], 0, 320),
        'category'     => $post['category'] ?? '',
        'body'         => $body,
        'faq'          => json_encode($faq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'image'        => $post['image'] ?? '',
        'image_alt'    => $post['image_alt'] ?? '',
        'featured'     => (int) ($post['featured'] ?? 0),
        'status'       => 'published',
        'published_at' => $post['published'] ?? $now,
        'updated_at'   => $now,
    ];

    $words = str_word_count(strip_tags($body));
    if ($existing) {
        $sets = implode(', ', array_map(static fn(string $k): string => "{$k} = :{$k}", array_keys($row)));
        $pdo->prepare("UPDATE posts SET {$sets} WHERE id = :id")->execute($row + ['id' => $existing['id']]);
        // created_at follows, so the next run can still tell an import from a later edit
        $pdo->prepare('UPDATE posts SET created_at = ? WHERE id = ?')->execute([$now, $existing['id']]);
        echo "UPDATE /blog/{$slug}/  ({$words} words)\n";
    } else {
        $row += ['created_at' => $now];
        $cols = implode(', ', array_keys($row));
        $vals = ':' . implode(', :', array_keys($row));
        $pdo->prepare("INSERT INTO posts ({$cols}) VALUES ({$vals})")->execute($row);
        echo "CREATE /blog/{$slug}/  ({$words} words)\n";
    }

    foreach (array_unique($notes) as $note) {
        echo "       note: {$note}\n";
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
