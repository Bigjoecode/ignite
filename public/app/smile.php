<?php
declare(strict_types=1);

// "See Your Smile": a visitor sends a photo of their smile and gets a preview of
// what treatment could look like, plus a real opinion from the team.
//
// A patient's photo is health information, so it is never put in the media library
// or anywhere the web can reach. It is written to ignite-data/smile/, outside the
// web root, and only a signed-in admin can fetch it, through the dashboard.

require_once APP . '/media.php';     // for the image checks and re-encoding
require_once APP . '/bookings.php';  // a smile request is a booking of kind "smile"

const SMILE_MAX_BYTES = 12 * 1024 * 1024;
const SMILE_MAX_EDGE  = 1600;        // plenty for a preview, and keeps files small
const SMILE_KEEP_DAYS = 90;          // photos are deleted after this many days

/** What is bothering them, as offered on the page and shown in the dashboard. */
function smile_concerns(): array
{
    return [
        'crowded'  => 'Crowded or overlapping teeth',
        'gaps'     => 'Gaps between my teeth',
        'overbite' => 'My top teeth stick out',
        'underbite'=> 'My bottom teeth sit in front',
        'crooked'  => 'Crooked or rotated teeth',
        'unsure'   => 'Not sure, I just want a straighter smile',
    ];
}

/** The folder photos live in, created on first use and never served by the web server. */
function smile_dir(): string
{
    $dir = cfg('data_dir') . '/smile/' . date('Y/m');
    if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
        throw new RuntimeException('Cannot create the photo folder.');
    }
    return $dir;
}

/**
 * Checks an uploaded photo and stores it privately.
 * Returns the path to keep, or a message to show the visitor.
 */
function smile_store_photo(array $file, string $id): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
        return [null, 'Please choose a photo of your smile.'];
    }
    if (($file['size'] ?? 0) > SMILE_MAX_BYTES) {
        return [null, 'That photo is too large. Please use one under 12MB.'];
    }
    $size = @getimagesize($file['tmp_name']);
    if (!$size || !in_array($size['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
        return [null, 'That file is not a photo we can read. Please use a JPG or PNG.'];
    }

    $dest = smile_dir() . '/' . $id . '.jpg';
    if (!smile_reencode($file['tmp_name'], $dest, $size['mime'], (int) $size[0], (int) $size[1])) {
        return [null, 'We could not read that photo. Please try another one.'];
    }
    @chmod($dest, 0600);

    // the path is stored relative to the data folder, so moving the site does not break it
    return [str_replace(cfg('data_dir') . '/', '', $dest), null];
}

/** Re-encodes to JPEG at a sensible size, which also strips the camera's EXIF data. */
function smile_reencode(string $src, string $dest, string $mime, int $w, int $h): bool
{
    switch ($mime) {
        case 'image/jpeg': $img = @imagecreatefromjpeg($src); break;
        case 'image/png':  $img = @imagecreatefrompng($src); break;
        case 'image/webp': $img = @imagecreatefromwebp($src); break;
        default:           return false;
    }
    if (!$img) {
        return false;
    }
    // phones record the rotation in EXIF, which re-encoding drops, so apply it first
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $orientation = (int) (@exif_read_data($src)['Orientation'] ?? 1);
        $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;
        if ($angle !== 0 && ($rotated = imagerotate($img, $angle, 0))) {
            imagedestroy($img);
            $img = $rotated;
            [$w, $h] = [imagesx($img), imagesy($img)];
        }
    }
    $scale = min(1, SMILE_MAX_EDGE / max($w, $h));
    if ($scale < 1) {
        $resized = imagecreatetruecolor((int) round($w * $scale), (int) round($h * $scale));
        imagecopyresampled($resized, $img, 0, 0, 0, 0, imagesx($resized), imagesy($resized), $w, $h);
        imagedestroy($img);
        $img = $resized;
    }
    $ok = imagejpeg($img, $dest, 86);
    imagedestroy($img);
    return (bool) $ok;
}

/** The full path of a stored photo, or null when it is gone. */
function smile_photo_path(string $stored): ?string
{
    if ($stored === '' || str_contains($stored, '..')) {
        return null;
    }
    $path = cfg('data_dir') . '/' . $stored;
    return is_file($path) ? $path : null;
}

/**
 * A link that lets the person who just sent a photo see it, and nobody else.
 * The photos are outside the web root, so each link is signed and expires; staff
 * see the same images through the dashboard, which checks the login instead.
 */
function smile_image_url(string $id, string $which, int $minutes = 60): string
{
    $expires = time() + $minutes * 60;
    return '/see-your-smile/image/?ref=' . rawurlencode($id) . '&which=' . rawurlencode($which)
        . '&exp=' . $expires . '&sig=' . smile_image_signature($id, $which, $expires);
}

function smile_image_signature(string $id, string $which, int $expires): string
{
    return hash_hmac('sha256', $id . '|' . $which . '|' . $expires, smile_secret());
}

/** True when a link is one we made, and has not run out. */
function smile_image_link_valid(string $id, string $which, int $expires, string $signature): bool
{
    return $expires > time()
        && hash_equals(smile_image_signature($id, $which, $expires), $signature);
}

/** The key the links are signed with, made once and kept with the settings. */
function smile_secret(): string
{
    $secret = db_setting(db(), 'smile_secret');
    if (!$secret) {
        $secret = bin2hex(random_bytes(32));
        db_set_setting(db(), 'smile_secret', $secret);
    }
    return (string) $secret;
}

/** Sends a stored photo to the browser. Used by the signed link and by the dashboard. */
function smile_send_image(string $stored): void
{
    $path = smile_photo_path($stored);
    if (!$path) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: image/jpeg');
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: private, no-store');
    header('X-Robots-Tag: noindex, nofollow');
    header('Content-Disposition: inline; filename="smile.jpg"');
    readfile($path);
    exit;
}

/** Deletes photos older than the retention period. Returns how many went. */
function smile_purge(int $days = SMILE_KEEP_DAYS): int
{
    $root = cfg('data_dir') . '/smile';
    if (!is_dir($root)) {
        return 0;
    }
    $cutoff = time() - $days * 86400;
    $gone   = 0;
    $files  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($files as $file) {
        if ($file->isFile() && $file->getMTime() < $cutoff && @unlink($file->getPathname())) {
            $gone++;
        }
    }
    if ($gone) {
        // the request stays in the dashboard; only the photo is removed
        db()->prepare("UPDATE bookings SET photo_path = '', result_path = '' WHERE kind = 'smile' AND created_at < ?")
            ->execute([gmdate('c', $cutoff)]);
    }
    return $gone;
}
