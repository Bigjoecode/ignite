<?php
/**
 * Deletes smile photos older than the retention period set in the dashboard.
 *
 *   php app/cli/purge-smile-photos.php [--days=90] [--dry-run]
 *
 * The requests stay in Bookings; only the photographs go. Worth running daily:
 *   0 3 * * * cd ~/domains/igniteorthodontics.com/public_html && php app/cli/purge-smile-photos.php
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap.php';
require_once APP . '/smile.php';
require_once APP . '/smile-ai.php';

$days = SMILE_KEEP_DAYS;
foreach ($argv as $given) {
    if (str_starts_with($given, '--days=')) {
        $days = max(1, (int) substr($given, 7));
    }
}
if (!in_array('--days=' . $days, $argv, true)) {
    $days = smile_ai_settings()['keep_days'];     // what the dashboard says, unless told otherwise
}

if (in_array('--dry-run', $argv, true)) {
    $root  = cfg('data_dir') . '/smile';
    $count = 0;
    if (is_dir($root)) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile() && $file->getMTime() < time() - $days * 86400) {
                $count++;
            }
        }
    }
    echo "would delete {$count} photo(s) older than {$days} days\n";
    exit(0);
}

echo 'deleted ', smile_purge($days), " photo(s) older than {$days} days\n";
