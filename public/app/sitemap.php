<?php
declare(strict_types=1);

header('Content-Type: application/xml; charset=utf-8');

// [path, lastmod or null]
$urls = [['/', null], ['/locations/', null], ['/braces-for-kids/', null]];
foreach (locations() as $l) {
    $urls[] = ['/locations/' . $l['slug'] . '/', null];
}
foreach (page_rows('service') as $row) {
    $urls[] = ['/' . $row['slug'] . '/', substr((string) $row['updated_at'], 0, 10)];
}
foreach (pages() as $slug => $p) {
    if (empty($p['draft'])) $urls[] = ['/' . $slug . '/', null];
}
$posts = posts();
$urls[] = ['/blog/', $posts ? max(array_column($posts, 'updated')) : null];
foreach ($posts as $post) {
    $urls[] = ['/blog/' . $post['slug'] . '/', $post['updated']];
}

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach ($urls as [$path, $lastmod]) {
    echo '  <url><loc>', e(cfg('base_url') . $path), '</loc>', $lastmod ? '<lastmod>' . e($lastmod) . '</lastmod>' : '', "</url>\n";
}
echo "</urlset>\n";
