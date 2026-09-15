<?php
declare(strict_types=1);

// Creates an admin user, or resets the password of an existing one, and prints a generated password once.
//
//   ssh ignite 'cd ~/domains/igniteorthodontics.com/public_html && php app/cli/create-admin.php you@example.com "Your Name"'

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap.php';

$email = strtolower(trim($argv[1] ?? ''));
$name  = trim($argv[2] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '') {
    fwrite(STDERR, "usage: php app/cli/create-admin.php email@example.com \"Full Name\"\n");
    exit(1);
}

// 20 characters without look-alikes (0/O, 1/l/I)
$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
$password = '';
for ($i = 0; $i < 20; $i++) {
    $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
}
$hash = password_hash($password, PASSWORD_DEFAULT);

$pdo  = db();
$find = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$find->execute([$email]);
$id = $find->fetchColumn();

if ($id) {
    $pdo->prepare('UPDATE users SET name = ?, password_hash = ? WHERE id = ?')->execute([$name, $hash, $id]);
    echo "Password reset for {$email}\n";
} else {
    $pdo->prepare('INSERT INTO users (email, name, password_hash, created_at) VALUES (?, ?, ?, ?)')
        ->execute([$email, $name, $hash, date('Y-m-d H:i:s')]);
    echo "Created admin {$email}\n";
}
echo "Password: {$password}\n";
