<?php
declare(strict_types=1);

header('Content-Type: application/xml; charset=utf-8');

$urls = ['/', '/locations/', '/braces-for-kids/'];
foreach (locations() as $l) {
    $urls[] = '/locations/' . $l['slug'] . '/';
}
foreach (pages() as $slug => $p) {
    if (empty($p['draft'])) $urls[] = '/' . $slug . '/';
}

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach ($urls as $u) {
    echo '  <url><loc>', e(cfg('base_url') . $u), "</loc></url>\n";
}
echo "</urlset>\n";
