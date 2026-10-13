<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
if (!empty($_SESSION['admin_id'])) { header('Location: /admin/'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    try {
        initialize_database();
        $stmt = db()->prepare('SELECT id, password_hash FROM admin_users WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$admin['id'];
            $_SESSION['admin_username'] = $username;
            header('Location: /admin/');
            exit;
        }
        $error = 'The username or password is incorrect.';
    } catch (Throwable $e) {
        $error = 'Could not connect to MySQL. Configure the project .env file and run admin setup.';
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin login</title><link rel="stylesheet" href="/assets/admin.css"></head>
<body><main class="auth-card"><a class="admin-brand" href="/">Olayinka Solaja</a><h1>Admin sign in</h1><p>Manage your site content.</p>
<?php if (isset($_GET['created'])): ?><div class="notice success">Admin account created. Sign in to continue.</div><?php endif; ?>
<?php if ($error): ?><div class="notice error"><?= h($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= h(admin_csrf()) ?>"><label>Username<input name="username" required autocomplete="username"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button type="submit">Sign in</button></form>
<a class="text-link" href="/admin/setup.php">First time? Set up admin</a></main></body></html>
