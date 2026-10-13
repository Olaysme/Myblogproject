<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
$error = '';
try {
    initialize_database();
    $hasAdmin = (int)db()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn() > 0;
} catch (Throwable $e) {
    $hasAdmin = true;
    $error = 'Could not connect to MySQL. Create a database and configure its credentials in the project .env file.';
}
if (!$error && $hasAdmin) {
    header('Location: /admin/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    if (strlen($username) < 3 || strlen($username) > 100) $error = 'Username must be between 3 and 100 characters.';
    elseif (strlen($password) < 12) $error = 'Password must be at least 12 characters.';
    elseif ($password !== $confirm) $error = 'The passwords do not match.';
    else {
        try {
            $stmt = db()->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: /admin/login.php?created=1');
            exit;
        } catch (Throwable $e) { $error = 'Could not create account. This setup page may already have been used.'; }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin setup</title><link rel="stylesheet" href="/assets/admin.css"></head>
<body><main class="auth-card"><a class="admin-brand" href="/">Olayinka Solaja</a><h1>Set up admin</h1><p>Create the first administrator account for the site.</p>
<?php if ($error): ?><div class="notice error"><?= h($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= h(admin_csrf()) ?>"><label>Username<input name="username" required minlength="3" maxlength="100" autocomplete="username"></label><label>Password<input type="password" name="password" required minlength="12" autocomplete="new-password"><small>Use at least 12 characters.</small></label><label>Confirm password<input type="password" name="confirm_password" required minlength="12" autocomplete="new-password"></label><button type="submit">Create admin account</button></form>
<a class="text-link" href="/admin/login.php">Already set up? Sign in</a></main></body></html>
