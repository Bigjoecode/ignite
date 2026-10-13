<?php
declare(strict_types=1);

// Creates a dashboard login, or resets the password of an existing one, and prints a generated
// password once. With --office=SLUG the login belongs to that office: it sees only that
// office's bookings and nothing else in the dashboard.
//
//   php app/cli/create-admin.php you@example.com "Your Name"
//   php app/cli/create-admin.php flint@igniteorthodontics.com "Flint Front Desk" --office=flint

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap.php';

$email  = strtolower(trim($argv[1] ?? ''));
$name   = trim($argv[2] ?? '');
$office = '';
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--office=')) {
        $office = substr($arg, 9);
    }
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '') {
    fwrite(STDERR, "usage: php app/cli/create-admin.php email@example.com \"Full Name\" [--office=SLUG]\n");
    exit(1);
}
if ($office !== '' && !isset(locations()[$office])) {
    fwrite(STDERR, "no office with the address /locations/{$office}/. The offices are: " . implode(', ', array_keys(locations())) . "\n");
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
    $pdo->prepare('UPDATE users SET name = ?, password_hash = ?, office = ? WHERE id = ?')
        ->execute([$name, $hash, $office, $id]);
    echo "Password reset for {$email}\n";
} else {
    $pdo->prepare('INSERT INTO users (email, name, password_hash, office, created_at) VALUES (?, ?, ?, ?, ?)')
        ->execute([$email, $name, $hash, $office, date('Y-m-d H:i:s')]);
    echo "Created login {$email}\n";
}
echo 'Sees: ', $office !== '' ? locations()[$office]['name'] . " bookings only\n" : "everything\n";
echo "Password: {$password}\n";
