<?php
declare(strict_types=1);

// SQLite database for the admin CMS (users, blog posts, media library).
// The file lives in the data dir, outside the web root, and is never deployed or overwritten.

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $dir = cfg('data_dir');
    if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) {
        throw new RuntimeException('Cannot create the data directory.');
    }
    $pdo = new PDO('sqlite:' . $dir . '/cms.sqlite', null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    db_migrate($pdo);
    return $pdo;
}

function db_migrate(PDO $pdo): void
{
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS settings (
    key   TEXT PRIMARY KEY,
    value TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS users (
    id            INTEGER PRIMARY KEY,
    email         TEXT NOT NULL UNIQUE COLLATE NOCASE,
    name          TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    created_at    TEXT NOT NULL,
    last_login_at TEXT
);
CREATE TABLE IF NOT EXISTS posts (
    id           INTEGER PRIMARY KEY,
    slug         TEXT NOT NULL UNIQUE,
    title        TEXT NOT NULL,
    description  TEXT NOT NULL DEFAULT '',
    category     TEXT NOT NULL DEFAULT '',
    body         TEXT NOT NULL DEFAULT '',
    faq          TEXT NOT NULL DEFAULT '[]',
    image        TEXT NOT NULL DEFAULT '',
    image_alt    TEXT NOT NULL DEFAULT '',
    featured     INTEGER NOT NULL DEFAULT 0,
    status       TEXT NOT NULL DEFAULT 'draft',   -- draft | published | trash
    published_at TEXT,                            -- local time; future = scheduled
    created_at   TEXT NOT NULL,
    updated_at   TEXT NOT NULL,
    author_id    INTEGER REFERENCES users(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS posts_status_published ON posts (status, published_at);
CREATE TABLE IF NOT EXISTS media (
    id            INTEGER PRIMARY KEY,
    path          TEXT NOT NULL UNIQUE,           -- web path, e.g. /uploads/2026/09/photo.jpg
    original_name TEXT NOT NULL DEFAULT '',
    mime          TEXT NOT NULL,
    width         INTEGER,
    height        INTEGER,
    bytes         INTEGER NOT NULL DEFAULT 0,
    alt           TEXT NOT NULL DEFAULT '',
    source_url    TEXT,                           -- set when added "from URL"
    created_at    TEXT NOT NULL,
    user_id       INTEGER REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS login_attempts (
    id           INTEGER PRIMARY KEY,
    ip_hash      TEXT NOT NULL,
    attempted_at INTEGER NOT NULL
);
SQL);

    if (db_setting($pdo, 'posts_seeded') === null) {
        db_seed_posts($pdo);
        db_set_setting($pdo, 'posts_seeded', date('c'));
    }
}

function db_setting(PDO $pdo, string $key): ?string
{
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE key = ?');
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    return $value === false ? null : (string) $value;
}

function db_set_setting(PDO $pdo, string $key, string $value): void
{
    $pdo->prepare('INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value')
        ->execute([$key, $value]);
}

/** One-time import of the original file-based articles (app/data/posts/*.php). */
function db_seed_posts(PDO $pdo): void
{
    $insert = $pdo->prepare(
        'INSERT OR IGNORE INTO posts
            (slug, title, description, category, body, faq, image, image_alt, featured, status, published_at, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach (glob(APP . '/data/posts/*.php') ?: [] as $file) {
        $p = require $file;
        $published = $p['published'] . ' 09:00:00';
        $insert->execute([
            basename($file, '.php'), $p['title'], $p['description'], $p['category'], $p['body'],
            json_encode($p['faq'] ?? [], JSON_UNESCAPED_UNICODE), $p['image'], $p['image_alt'],
            empty($p['featured']) ? 0 : 1, 'published', $published, $published, $p['updated'] . ' 09:00:00',
        ]);
    }
}
