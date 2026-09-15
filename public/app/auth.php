<?php
declare(strict_types=1);

// Admin authentication: session cookie scoped to /admin, CSRF tokens, login throttling.

const ADMIN_IDLE_SECONDS   = 8 * 3600;
const LOGIN_MAX_FAILURES   = 5;
const LOGIN_WINDOW_SECONDS = 900;

function admin_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $dir = cfg('data_dir') . '/sessions';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    if (is_dir($dir) && is_writable($dir)) {
        session_save_path($dir);
    }
    $https = ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    ini_set('session.use_strict_mode', '1');
    ini_set('session.gc_maxlifetime', (string) ADMIN_IDLE_SECONDS);
    session_name('ig_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/admin',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function current_admin(): ?array
{
    admin_session();
    $id = $_SESSION['uid'] ?? null;
    if (!$id) {
        return null;
    }
    if (($_SESSION['seen'] ?? 0) < time() - ADMIN_IDLE_SECONDS) {
        admin_logout();
        return null;
    }
    $_SESSION['seen'] = time();
    $stmt = db()->prepare('SELECT id, email, name FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/** The signed-in admin, or a redirect to the login page (401 JSON for API calls). */
function require_admin(bool $json = false): array
{
    $user = current_admin();
    if ($user) {
        return $user;
    }
    if ($json) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Your session has expired. Please sign in again.']);
        exit;
    }
    redirect('/admin/login/?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '/admin/'), 302);
    exit;
}

function csrf_token(): string
{
    admin_session();
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($token) && $token !== '' && hash_equals(csrf_token(), $token);
}

/** @return array|string the user row on success, or an error message */
function admin_attempt_login(string $email, string $password)
{
    $pdo = db();
    $ip  = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');

    $pdo->prepare('DELETE FROM login_attempts WHERE attempted_at < ?')->execute([time() - LOGIN_WINDOW_SECONDS]);
    $failures = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip_hash = ?');
    $failures->execute([$ip]);
    if ((int) $failures->fetchColumn() >= LOGIN_MAX_FAILURES) {
        return 'Too many failed attempts. Please wait 15 minutes and try again.';
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([trim($email)]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $pdo->prepare('INSERT INTO login_attempts (ip_hash, attempted_at) VALUES (?, ?)')->execute([$ip, time()]);
        return 'Incorrect email or password.';
    }

    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    }

    admin_session();
    session_regenerate_id(true);
    $_SESSION['uid']  = (int) $user['id'];
    $_SESSION['seen'] = time();
    unset($_SESSION['csrf']);

    $pdo->prepare('UPDATE users SET last_login_at = ? WHERE id = ?')->execute([date('Y-m-d H:i:s'), $user['id']]);
    $pdo->prepare('DELETE FROM login_attempts WHERE ip_hash = ?')->execute([$ip]);
    return $user;
}

function admin_logout(): void
{
    admin_session();
    $_SESSION = [];
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000, 'path' => $p['path'], 'domain' => $p['domain'],
        'secure' => $p['secure'], 'httponly' => $p['httponly'], 'samesite' => 'Strict',
    ]);
    session_destroy();
}
