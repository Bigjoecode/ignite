<?php
declare(strict_types=1);

// Media library storage: validated uploads and "import from URL" into public_html/uploads/YYYY/MM/.
// Images are re-encoded (when GD is available) to strip metadata and scale down very large photos,
// so everything the site shows is served from this domain.

const MEDIA_TYPES     = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
const MEDIA_MAX_BYTES = 15 * 1024 * 1024;
const MEDIA_MAX_EDGE  = 2400; // pixels on the longer side

/** @return array{0: string, 1: string} absolute directory and its web path for this month */
function media_dir(): array
{
    $sub = date('Y/m');
    $abs = WEBROOT . '/uploads/' . $sub;
    if (!is_dir($abs) && !mkdir($abs, 0755, true) && !is_dir($abs)) {
        throw new RuntimeException('Could not create the uploads folder.');
    }
    $guard = WEBROOT . '/uploads/.htaccess';
    if (!is_file($guard)) {
        // uploads are images only: never execute or render anything else from here
        file_put_contents($guard, "Options -Indexes\n<FilesMatch \"(?i)\\.(php[0-9]?|phtml|phar|pl|py|cgi|sh|html?|svg|js)$\">\n  Require all denied\n</FilesMatch>\n");
    }
    return [$abs, '/uploads/' . $sub];
}

function media_slug(string $name): string
{
    $name = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '-', $name));
    return substr(trim($name, '-'), 0, 60);
}

/** Stores an image that is already on disk (an upload's tmp file or a download). Returns the media row. */
function media_store(string $tmpPath, string $originalName, int $userId, ?string $sourceUrl = null): array
{
    $bytes = @filesize($tmpPath);
    if (!$bytes) {
        throw new InvalidArgumentException('The file is empty.');
    }
    if ($bytes > MEDIA_MAX_BYTES) {
        throw new InvalidArgumentException('Images must be 15 MB or smaller.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpPath);
    if (!isset(MEDIA_TYPES[$mime])) {
        throw new InvalidArgumentException('Only JPG, PNG, WebP and GIF images can be added.');
    }
    $size = @getimagesize($tmpPath);
    if (!$size || $size[0] < 1 || $size[1] < 1) {
        throw new InvalidArgumentException('That file is not a valid image.');
    }

    [$abs, $web] = media_dir();
    $base = media_slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'image';
    $ext  = MEDIA_TYPES[$mime];
    $name = $base . '.' . $ext;
    for ($i = 2; file_exists($abs . '/' . $name); $i++) {
        $name = $base . '-' . $i . '.' . $ext;
    }
    $dest = $abs . '/' . $name;

    // GIFs are copied untouched so animations survive
    $written = $mime !== 'image/gif' && extension_loaded('gd') && media_reencode($tmpPath, $dest, $mime, $size[0], $size[1]);
    if (!$written && !copy($tmpPath, $dest)) {
        throw new RuntimeException('Could not save the image.');
    }
    @chmod($dest, 0644);
    $final = getimagesize($dest) ?: $size;

    $row = [
        'path'          => $web . '/' . $name,
        'original_name' => mb_substr($originalName, 0, 200),
        'mime'          => $mime,
        'width'         => (int) $final[0],
        'height'        => (int) $final[1],
        'bytes'         => (int) filesize($dest),
        'alt'           => '',
        'source_url'    => $sourceUrl,
        'created_at'    => date('Y-m-d H:i:s'),
        'user_id'       => $userId,
    ];
    db()->prepare(
        'INSERT INTO media (path, original_name, mime, width, height, bytes, alt, source_url, created_at, user_id)
         VALUES (:path, :original_name, :mime, :width, :height, :bytes, :alt, :source_url, :created_at, :user_id)'
    )->execute($row);
    $row['id'] = (int) db()->lastInsertId();
    return $row;
}

function media_reencode(string $src, string $dest, string $mime, int $w, int $h): bool
{
    switch ($mime) {
        case 'image/jpeg': $img = @imagecreatefromjpeg($src); break;
        case 'image/png':  $img = @imagecreatefrompng($src); break;
        case 'image/webp': $img = @imagecreatefromwebp($src); break;
        default:           $img = false;
    }
    if (!$img) {
        return false;
    }

    // phone photos: apply the EXIF rotation, because re-encoding drops the EXIF tag
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $orientation = (int) (@exif_read_data($src)['Orientation'] ?? 1);
        $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;
        if ($angle !== 0) {
            $rotated = imagerotate($img, $angle, 0);
            if ($rotated) {
                imagedestroy($img);
                $img = $rotated;
                [$w, $h] = [imagesx($img), imagesy($img)];
            }
        }
    }

    $scale = min(1, MEDIA_MAX_EDGE / max($w, $h));
    if ($scale < 1) {
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));
        $resized = imagecreatetruecolor($nw, $nh);
        if ($mime !== 'image/jpeg') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $resized;
    } elseif ($mime !== 'image/jpeg') {
        imagealphablending($img, false);
        imagesavealpha($img, true);
    }

    switch ($mime) {
        case 'image/jpeg': $ok = imagejpeg($img, $dest, 85); break;
        case 'image/png':  $ok = imagepng($img, $dest, 6); break;
        default:           $ok = imagewebp($img, $dest, 85);
    }
    imagedestroy($img);
    return (bool) $ok;
}

/** Downloads an image from a public web address into the library (never hotlinks it). */
function media_from_url(string $url, int $userId): array
{
    $original = trim($url);
    $url = $original;
    $tmp = tempnam(sys_get_temp_dir(), 'igmedia');
    try {
        for ($hop = 0; $hop <= 3; $hop++) {
            [$host, $port, $ip] = media_public_target($url);
            $fh = fopen($tmp, 'wb');
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_FILE             => $fh,
                CURLOPT_FOLLOWLOCATION   => false,          // each redirect is re-checked below
                CURLOPT_RESOLVE          => [$host . ':' . $port . ':' . $ip],
                CURLOPT_CONNECTTIMEOUT   => 10,
                CURLOPT_TIMEOUT          => 30,
                CURLOPT_USERAGENT        => 'IgniteOrthodonticsCMS/1.0',
                CURLOPT_NOPROGRESS       => false,
                CURLOPT_PROGRESSFUNCTION => static fn($ch, $total, $done): int => $done > MEDIA_MAX_BYTES ? 1 : 0,
            ]);
            if (defined('CURLOPT_PROTOCOLS_STR')) {
                curl_setopt($ch, CURLOPT_PROTOCOLS_STR, 'http,https');
            }
            $ok    = curl_exec($ch);
            $code  = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $next  = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
            $error = curl_error($ch);
            curl_close($ch);
            fclose($fh);

            if ($ok === false) {
                throw new InvalidArgumentException(stripos($error, 'callback') !== false
                    ? 'Images must be 15 MB or smaller.'
                    : 'The image could not be downloaded from that address.');
            }
            if ($code >= 300 && $code < 400 && $next !== '') {
                $url = $next;
                continue;
            }
            if ($code !== 200) {
                throw new InvalidArgumentException('The image could not be downloaded (the website answered ' . $code . ').');
            }
            $name = basename((string) parse_url($url, PHP_URL_PATH)) ?: 'image';
            return media_store($tmp, $name, $userId, $original);
        }
        throw new InvalidArgumentException('That address redirects too many times.');
    } finally {
        @unlink($tmp);
    }
}

/** @return array{0: string, 1: int, 2: string} host, port and a public IPv4 address to connect to */
function media_public_target(string $url): array
{
    $parts  = parse_url($url);
    $scheme = strtolower($parts['scheme'] ?? '');
    $host   = $parts['host'] ?? '';
    if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
        throw new InvalidArgumentException('Enter the full image address, starting with https://');
    }
    $ips = gethostbynamel($host) ?: [];
    if (!$ips) {
        throw new InvalidArgumentException('That website could not be found.');
    }
    foreach ($ips as $ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw new InvalidArgumentException('That address is not allowed.');
        }
    }
    return [$host, (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80)), $ips[0]];
}

function media_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM media WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function media_delete(int $id): void
{
    $row = media_find($id);
    if (!$row) {
        return;
    }
    if (preg_match('#^/uploads/\d{4}/\d{2}/[a-z0-9-]+\.(jpg|png|webp|gif)$#', $row['path'])) {
        @unlink(WEBROOT . $row['path']);
    }
    db()->prepare('DELETE FROM media WHERE id = ?')->execute([$id]);
}
