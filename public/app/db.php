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
CREATE TABLE IF NOT EXISTS pages (
    id          INTEGER PRIMARY KEY,
    type        TEXT NOT NULL,                  -- service | location
    slug        TEXT NOT NULL,
    title       TEXT NOT NULL,
    template    TEXT NOT NULL,                  -- key from app/templates.php
    description TEXT NOT NULL DEFAULT '',
    seo_title   TEXT NOT NULL DEFAULT '',
    image       TEXT NOT NULL DEFAULT '',       -- share image
    data        TEXT NOT NULL DEFAULT '{}',     -- section content as JSON
    menu        INTEGER NOT NULL DEFAULT 0,     -- show in the Treatments menu
    menu_order  INTEGER NOT NULL DEFAULT 0,
    status      TEXT NOT NULL DEFAULT 'draft',  -- draft | published | trash
    created_at  TEXT NOT NULL,
    updated_at  TEXT NOT NULL,
    author_id   INTEGER REFERENCES users(id) ON DELETE SET NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS pages_type_slug ON pages (type, slug);
CREATE TABLE IF NOT EXISTS redirects (
    from_path  TEXT PRIMARY KEY,                -- old address, e.g. /old-slug/
    to_path    TEXT NOT NULL,
    created_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS bookings (
    id          TEXT PRIMARY KEY,               -- the id written to bookings.jsonl
    created_at  TEXT NOT NULL,                  -- UTC, when the request came in
    status      TEXT NOT NULL DEFAULT 'new',    -- new | contacted | booked | closed
    office      TEXT NOT NULL DEFAULT '',
    patient     TEXT NOT NULL DEFAULT '',
    treatment   TEXT NOT NULL DEFAULT '',
    date        TEXT NOT NULL DEFAULT '',       -- preferred day, or "first"
    time        TEXT NOT NULL DEFAULT '',
    first_name  TEXT NOT NULL DEFAULT '',
    last_name   TEXT NOT NULL DEFAULT '',
    phone       TEXT NOT NULL DEFAULT '',
    email       TEXT NOT NULL DEFAULT '',
    notes       TEXT NOT NULL DEFAULT '',
    source      TEXT NOT NULL DEFAULT '',       -- the page the visitor came from
    staff_note  TEXT NOT NULL DEFAULT '',       -- what the practice wrote about it
    trashed_at  TEXT,                           -- in the trash, still recoverable
    kind        TEXT NOT NULL DEFAULT 'office', -- office | virtual (a video consultation)
    start_at    TEXT,                           -- a virtual consultation's agreed time
    meet_url    TEXT,                           -- its Google Meet link
    event_id    TEXT,                           -- its Google Calendar event
    updated_at  TEXT,
    updated_by  INTEGER REFERENCES users(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS bookings_created ON bookings (created_at DESC);
SQL);

    // columns added after a table first shipped
    $bookingCols = $pdo->query('PRAGMA table_info(bookings)')->fetchAll(PDO::FETCH_COLUMN, 1);
    $laterCols   = [
        'trashed_at' => 'TEXT',                          // in the trash, still recoverable
        'kind'       => "TEXT NOT NULL DEFAULT 'office'", // office | virtual (a video consultation)
        'start_at'   => 'TEXT',                          // a virtual consultation's agreed time
        'meet_url'   => 'TEXT',                          // its Google Meet link
        'event_id'   => 'TEXT',                          // its Google Calendar event
    ];
    foreach ($laterCols as $column => $type) {
        if (!in_array($column, $bookingCols, true)) {
            $pdo->exec("ALTER TABLE bookings ADD COLUMN {$column} {$type}");
        }
    }

    if (db_setting($pdo, 'posts_seeded') === null) {
        db_seed_posts($pdo);
        db_set_setting($pdo, 'posts_seeded', date('c'));
    }
    if (db_setting($pdo, 'pages_seeded') === null) {
        db_seed_pages($pdo);
        db_set_setting($pdo, 'pages_seeded', date('c'));
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

/** One-time import of the offices and treatment pages (app/data/seed-pages.php). */
function db_seed_pages(PDO $pdo): void
{
    $seed   = require APP . '/data/seed-pages.php';
    $now    = date('Y-m-d H:i:s');
    $insert = $pdo->prepare(
        'INSERT OR IGNORE INTO pages
            (type, slug, title, template, description, seo_title, image, data, menu, menu_order, status, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach (['location' => $seed['locations'], 'service' => $seed['services']] as $type => $rows) {
        foreach ($rows as $p) {
            $insert->execute([
                $type, $p['slug'], $p['title'], $p['template'], $p['description'] ?? '', $p['seo_title'] ?? '',
                $p['image'] ?? '', json_encode($p['data'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                $p['menu'] ?? 0, $p['menu_order'] ?? 0, 'published', $now, $now,
            ]);
        }
    }
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
