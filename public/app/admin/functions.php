<?php
declare(strict_types=1);

// Admin CMS controllers and helpers (used by app/admin/router.php).

/* ============================================================
   RESPONSES
   ============================================================ */

function admin_wants_json(): bool
{
    return strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
}

function admin_json(int $code, array $body): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function admin_flash(string $type, string $message): void
{
    admin_session();
    $_SESSION['flash'][] = [$type, $message];
}

function admin_take_flash(): array
{
    admin_session();
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

function admin_render(string $view, array $vars, string $title, ?array $user): void
{
    extract($vars);
    ob_start();
    require APP . '/admin/views/' . $view . '.php';
    $content = ob_get_clean();
    $flash   = admin_take_flash();
    require APP . '/admin/views/layout.php';
}

function admin_not_found(?array $user): void
{
    http_response_code(404);
    admin_render('not-found', [], 'Not found', $user);
    exit;
}

function admin_asset(string $file): string
{
    $path = WEBROOT . '/admin-assets/' . $file;
    return '/admin-assets/' . $file . (is_file($path) ? '?v=' . filemtime($path) : '');
}

/* ============================================================
   POSTS
   ============================================================ */

function admin_find_post(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/** Published / Scheduled / Draft / Trash, as shown in the admin. */
function admin_post_state(array $row): string
{
    if ($row['status'] === 'trash') return 'Trash';
    if ($row['status'] !== 'published') return 'Draft';
    return $row['published_at'] > date('Y-m-d H:i:s') ? 'Scheduled' : 'Published';
}

function admin_datetime(?string $value): string
{
    return $value ? date('M j, Y \a\t g:i A', strtotime($value)) : '';
}

function admin_categories(): array
{
    return db()->query("SELECT DISTINCT category FROM posts WHERE category != '' AND status != 'trash' ORDER BY category")
        ->fetchAll(PDO::FETCH_COLUMN);
}

function admin_posts_index(array $user): void
{
    $tab = (string) ($_GET['status'] ?? 'all');
    $q   = trim((string) ($_GET['q'] ?? ''));
    $now = date('Y-m-d H:i:s');

    $where = ["status != 'trash'"];
    $args  = [];
    switch ($tab) {
        case 'published': $where = ["status = 'published'", 'published_at <= ?']; $args[] = $now; break;
        case 'scheduled': $where = ["status = 'published'", 'published_at > ?'];  $args[] = $now; break;
        case 'draft':     $where = ["status = 'draft'"]; break;
        case 'trash':     $where = ["status = 'trash'"]; break;
        default:          $tab = 'all';
    }
    if ($q !== '') {
        $where[] = '(title LIKE ? OR description LIKE ?)';
        array_push($args, '%' . $q . '%', '%' . $q . '%');
    }
    $stmt = db()->prepare(
        'SELECT id, slug, title, category, status, published_at, updated_at, featured, image FROM posts
         WHERE ' . implode(' AND ', $where) . ' ORDER BY COALESCE(published_at, updated_at) DESC, id DESC'
    );
    $stmt->execute($args);
    $posts = $stmt->fetchAll();

    $counts = db()->prepare(
        "SELECT SUM(status != 'trash') AS all_posts,
                SUM(status = 'published' AND published_at <= :a) AS published,
                SUM(status = 'published' AND published_at > :b) AS scheduled,
                SUM(status = 'draft') AS draft,
                SUM(status = 'trash') AS trash
         FROM posts"
    );
    $counts->execute([':a' => $now, ':b' => $now]);

    admin_render('posts', ['posts' => $posts, 'tab' => $tab, 'q' => $q, 'counts' => array_map('intval', $counts->fetch())], 'Posts', $user);
}

function admin_blank_post(): array
{
    return [
        'id' => 0, 'title' => '', 'slug' => '', 'body' => '', 'description' => '', 'category' => '',
        'image' => '', 'image_alt' => '', 'featured' => 0, 'status' => 'draft', 'published_at' => null,
        'faq' => '[]', 'created_at' => null, 'updated_at' => null,
    ];
}

function admin_post_editor(array $user, ?int $id): void
{
    $post = $id ? admin_find_post($id) : admin_blank_post();
    if (!$post) {
        admin_not_found($user);
    }
    // fields submitted with a validation error come back so nothing typed is lost
    admin_session();
    $old = $_SESSION['post_form'] ?? null;
    unset($_SESSION['post_form']);
    if (is_array($old) && (int) $old['id'] === (int) $post['id']) {
        $post = array_merge($post, $old);
    }
    admin_render('post-edit', ['post' => $post, 'categories' => admin_categories()], $id ? 'Edit Post' : 'Add New Post', $user);
}

function admin_unique_slug(string $source, int $id): string
{
    $base = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($source)), '-');
    $base = substr($base, 0, 80) ?: 'post';
    $slug = $base;
    $find = db()->prepare('SELECT 1 FROM posts WHERE slug = ? AND id != ?');
    for ($i = 2; ; $i++) {
        $find->execute([$slug, $id]);
        if (!$find->fetchColumn()) {
            return $slug;
        }
        $slug = $base . '-' . $i;
    }
}

function admin_parse_datetime(string $value): ?string
{
    $dt = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', trim($value));
    return $dt ? $dt->format('Y-m-d H:i:00') : null;
}

function admin_save_post(array $user): void
{
    $pdo      = db();
    $id       = (int) ($_POST['id'] ?? 0);
    $existing = $id ? admin_find_post($id) : null;
    if ($id && !$existing) {
        admin_not_found($user);
    }
    $action = (string) ($_POST['action'] ?? 'save_draft'); // save_draft | publish | update | unpublish

    $title       = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 200);
    $description = mb_substr(trim((string) preg_replace('/\s+/', ' ', (string) ($_POST['description'] ?? ''))), 0, 320);
    $category    = mb_substr(trim((string) ($_POST['category'] ?? '')), 0, 40);
    $imageAlt    = mb_substr(trim((string) ($_POST['image_alt'] ?? '')), 0, 200);
    $featured    = empty($_POST['featured']) ? 0 : 1;
    $when        = admin_parse_datetime((string) ($_POST['published_at'] ?? ''));
    [$body, $notes] = clean_post_html((string) ($_POST['body'] ?? ''));

    $image = trim((string) ($_POST['image'] ?? ''));
    if ($image !== '' && !clean_is_local_media($image)) {
        $image = '';
        $notes[] = 'The featured image was not from the media library, so it was removed.';
    }

    $faq = [];
    $answers = (array) ($_POST['faq_a'] ?? []);
    foreach ((array) ($_POST['faq_q'] ?? []) as $i => $question) {
        $question = trim((string) $question);
        $answer   = trim((string) ($answers[$i] ?? ''));
        if ($question !== '' && $answer !== '') {
            $faq[] = [mb_substr($question, 0, 300), mb_substr($answer, 0, 2000)];
        }
    }
    $faq = array_slice($faq, 0, 20);

    $status = $existing['status'] ?? 'draft';
    if (in_array($action, ['publish', 'update'], true)) {
        $status = 'published';
    } elseif (in_array($action, ['save_draft', 'unpublish'], true) || $status === 'trash') {
        $status = 'draft';
    }

    $slugSource = trim((string) ($_POST['slug'] ?? ''));
    $slug = admin_unique_slug($slugSource !== '' ? $slugSource : ($title !== '' ? $title : 'draft-' . date('YmdHis')), $id);

    $errors = [];
    if ($status === 'published') {
        if ($title === '')                    $errors[] = 'Add a title before publishing.';
        if (trim(strip_tags($body)) === '')   $errors[] = 'Add some content before publishing.';
        if ($description === '')              $errors[] = 'Add a summary before publishing. It appears on the blog page and in Google results.';
        if ($category === '')                 $errors[] = 'Choose a topic before publishing.';
    }
    if ($errors) {
        admin_session();
        $_SESSION['post_form'] = [
            'id' => $id, 'title' => $title, 'slug' => $slugSource, 'body' => $body, 'description' => $description,
            'category' => $category, 'image' => $image, 'image_alt' => $imageAlt, 'featured' => $featured,
            'published_at' => $when, 'faq' => json_encode($faq, JSON_UNESCAPED_UNICODE),
        ];
        foreach ($errors as $error) {
            admin_flash('error', $error);
        }
        redirect($id ? "/admin/posts/{$id}/" : '/admin/posts/new/', 303);
    }

    $now = date('Y-m-d H:i:s');
    if ($status === 'published') {
        $publishedAt = $when ?? ($existing['published_at'] ?? null) ?? $now;
    } else {
        $publishedAt = $when; // a draft remembers its planned date
    }

    $fields = [
        'title' => $title, 'slug' => $slug, 'description' => $description, 'category' => $category, 'body' => $body,
        'faq' => json_encode($faq, JSON_UNESCAPED_UNICODE), 'image' => $image, 'image_alt' => $imageAlt,
        'featured' => $featured, 'status' => $status, 'published_at' => $publishedAt, 'updated_at' => $now,
    ];

    $pdo->beginTransaction();
    if ($existing) {
        $sets = implode(', ', array_map(static fn(string $k): string => "{$k} = :{$k}", array_keys($fields)));
        $pdo->prepare("UPDATE posts SET {$sets} WHERE id = :id")->execute($fields + ['id' => $id]);
    } else {
        $fields += ['created_at' => $now, 'author_id' => (int) $user['id']];
        $cols = implode(', ', array_keys($fields));
        $vals = ':' . implode(', :', array_keys($fields));
        $pdo->prepare("INSERT INTO posts ({$cols}) VALUES ({$vals})")->execute($fields);
        $id = (int) $pdo->lastInsertId();
    }
    if ($featured) {
        $pdo->prepare('UPDATE posts SET featured = 0 WHERE id != ?')->execute([$id]);
    }
    $pdo->commit();

    $wasPublished = $existing && $existing['status'] === 'published';
    if ($status === 'published' && $publishedAt > $now) {
        $message = 'Post scheduled for ' . admin_datetime($publishedAt) . '.';
    } elseif ($status === 'published') {
        $message = $wasPublished ? 'Post updated.' : 'Post published.';
    } else {
        $message = $wasPublished ? 'Post switched to draft. It is no longer visible on the site.' : 'Draft saved.';
    }
    admin_flash('success', $message);
    foreach ($notes as $note) {
        admin_flash('warning', $note);
    }
    redirect("/admin/posts/{$id}/", 303);
}

function admin_post_status(int $id, string $action): void
{
    $post = admin_find_post($id);
    if (!$post) {
        redirect('/admin/posts/', 303);
    }
    $title = $post['title'] !== '' ? '"' . $post['title'] . '"' : 'The post';
    if ($action === 'trash') {
        db()->prepare("UPDATE posts SET status = 'trash', featured = 0, updated_at = ? WHERE id = ?")->execute([date('Y-m-d H:i:s'), $id]);
        admin_flash('success', $title . ' was moved to the trash.');
        redirect('/admin/posts/', 303);
    }
    if ($action === 'restore') {
        db()->prepare("UPDATE posts SET status = 'draft', updated_at = ? WHERE id = ?")->execute([date('Y-m-d H:i:s'), $id]);
        admin_flash('success', $title . ' was restored as a draft.');
        redirect('/admin/posts/?status=trash', 303);
    }
    if ($action === 'delete' && $post['status'] === 'trash') {
        db()->prepare('DELETE FROM posts WHERE id = ?')->execute([$id]);
        admin_flash('success', $title . ' was permanently deleted.');
    }
    redirect('/admin/posts/?status=trash', 303);
}

function admin_preview(int $id): void
{
    $row = admin_find_post($id);
    if (!$row) {
        admin_not_found(current_admin());
    }
    $post = post_prepare($row);
    render('blog-post', ['post' => $post], [
        'path'        => '/blog/' . $post['slug'] . '/',
        'title'       => 'Preview: ' . $post['title'],
        'description' => $post['description'],
        'css'         => ['home.css', 'page.css', 'blog.css'],
        'js'          => ['blog.js'],
        'noindex'     => true,
        'preview_id'  => $id,
    ]);
}

/* ============================================================
   MEDIA
   ============================================================ */

function admin_media_public(array $row): array
{
    return [
        'id'        => (int) $row['id'],
        'url'       => $row['path'],
        'name'      => $row['original_name'] !== '' ? $row['original_name'] : basename($row['path']),
        'alt'       => $row['alt'],
        'width'     => (int) $row['width'],
        'height'    => (int) $row['height'],
        'bytes'     => (int) $row['bytes'],
        'date'      => substr($row['created_at'], 0, 10),
        'deletable' => strncmp($row['path'], '/uploads/', 9) === 0,
    ];
}

/** The site's existing photos appear in the library too (once). */
function admin_media_seed_site_images(): void
{
    $pdo = db();
    if (db_setting($pdo, 'media_seeded') !== null) {
        return;
    }
    $insert = $pdo->prepare('INSERT OR IGNORE INTO media (path, original_name, mime, width, height, bytes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $finfo  = new finfo(FILEINFO_MIME_TYPE);
    foreach (glob(WEBROOT . '/assets/img/*') ?: [] as $file) {
        $mime = $finfo->file($file);
        $size = @getimagesize($file);
        if (!isset(MEDIA_TYPES[$mime]) || !$size) {
            continue;
        }
        $insert->execute(['/assets/img/' . basename($file), basename($file), $mime, $size[0], $size[1], filesize($file), date('Y-m-d H:i:s', filemtime($file))]);
    }
    db_set_setting($pdo, 'media_seeded', date('c'));
}

function admin_media_list(): void
{
    admin_media_seed_site_images();
    $q    = trim((string) ($_GET['q'] ?? ''));
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $per  = 48;
    $sql  = 'SELECT * FROM media';
    $args = [];
    if ($q !== '') {
        $sql .= ' WHERE original_name LIKE ? OR alt LIKE ? OR path LIKE ?';
        $args = array_fill(0, 3, '%' . $q . '%');
    }
    $sql .= ' ORDER BY id DESC LIMIT ' . ($per + 1) . ' OFFSET ' . (($page - 1) * $per);
    $stmt = db()->prepare($sql);
    $stmt->execute($args);
    $rows = $stmt->fetchAll();
    admin_json(200, [
        'ok'       => true,
        'items'    => array_map('admin_media_public', array_slice($rows, 0, $per)),
        'has_more' => count($rows) > $per,
    ]);
}

function admin_normalize_files($files): array
{
    if (!is_array($files) || !isset($files['name'])) {
        return [];
    }
    if (!is_array($files['name'])) {
        return [$files];
    }
    $out = [];
    foreach (array_keys($files['name']) as $i) {
        $out[] = ['name' => $files['name'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
    }
    return $out;
}

function admin_media_upload(array $user): void
{
    $files = admin_normalize_files($_FILES['files'] ?? ($_FILES['file'] ?? null));
    if (!$files) {
        admin_json(400, ['ok' => false, 'error' => 'No file was received.']);
    }
    $messages = [
        UPLOAD_ERR_INI_SIZE => 'the file is too large.', UPLOAD_ERR_FORM_SIZE => 'the file is too large.',
        UPLOAD_ERR_PARTIAL => 'the upload was interrupted.', UPLOAD_ERR_NO_FILE => 'no file was chosen.',
    ];
    $items  = [];
    $errors = [];
    foreach ($files as $f) {
        $name = (string) $f['name'];
        if ((int) $f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
            $errors[] = $name . ': ' . ($messages[(int) $f['error']] ?? 'the upload failed.');
            continue;
        }
        try {
            $items[] = admin_media_public(media_store($f['tmp_name'], $name, (int) $user['id']));
        } catch (InvalidArgumentException $e) {
            $errors[] = $name . ': ' . $e->getMessage();
        } catch (Throwable $e) {
            error_log('media upload: ' . $e->getMessage());
            $errors[] = $name . ': the image could not be saved.';
        }
    }
    admin_json($items ? 200 : 422, [
        'ok'       => (bool) $items,
        'items'    => $items,
        'errors'   => $errors,
        'error'    => $errors ? implode(' ', $errors) : null,
        'location' => $items[0]['url'] ?? null, // TinyMCE paste/drop upload handler
    ]);
}

function admin_media_url(array $user): void
{
    try {
        $row = media_from_url((string) ($_POST['url'] ?? ''), (int) $user['id']);
        admin_json(200, ['ok' => true, 'item' => admin_media_public($row)]);
    } catch (InvalidArgumentException $e) {
        admin_json(422, ['ok' => false, 'error' => $e->getMessage()]);
    } catch (Throwable $e) {
        error_log('media url import: ' . $e->getMessage());
        admin_json(500, ['ok' => false, 'error' => 'The image could not be imported from that address.']);
    }
}

function admin_media_update(int $id): void
{
    if (!media_find($id)) {
        admin_json(404, ['ok' => false, 'error' => 'That image no longer exists.']);
    }
    $alt = mb_substr(trim((string) ($_POST['alt'] ?? '')), 0, 200);
    db()->prepare('UPDATE media SET alt = ? WHERE id = ?')->execute([$alt, $id]);
    admin_json(200, ['ok' => true, 'item' => admin_media_public(media_find($id))]);
}

/* ============================================================
   ACCOUNT
   ============================================================ */

function admin_account(array $user, string $method): void
{
    $errors = [];
    if ($method === 'POST') {
        $name    = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 80);
        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        $hash = (string) $stmt->fetchColumn();

        if ($name === '') {
            $errors[] = 'Enter your name.';
        }
        $changing = $new !== '' || $confirm !== '';
        if ($changing) {
            if (!password_verify($current, $hash)) $errors[] = 'Your current password is incorrect.';
            if (mb_strlen($new) < 12)              $errors[] = 'Use at least 12 characters for your new password.';
            if ($new !== $confirm)                 $errors[] = 'The new passwords do not match.';
        }
        if (!$errors) {
            db()->prepare('UPDATE users SET name = ? WHERE id = ?')->execute([$name, $user['id']]);
            if ($changing) {
                db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
                session_regenerate_id(true);
            }
            admin_flash('success', $changing ? 'Your details and password were updated.' : 'Your details were updated.');
            redirect('/admin/account/', 303);
        }
        $user['name'] = $name;
    }
    admin_render('account', ['errors' => $errors], 'Your Account', $user);
}
