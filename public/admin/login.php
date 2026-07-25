<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';

if (Auth::admin()) {
    redirect('/admin/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($email === '' || $password === '') {
            $error = 'Email and password are required.';
        } elseif (Auth::attemptAdmin($email, $password)) {
            flash('success', 'Welcome back.');
            redirect('/admin/index.php');
        } else {
            $error = 'Invalid credentials or inactive account.';
        }
    }
}
$app = config('app_name', 'Pentagon Quest');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login · <?= e($app) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('admin/assets/admin.css')) ?>">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="brand"><div class="brand-mark"></div><span><?= e($app) ?></span></div>
    <h1>Admin sign in</h1>
    <p class="sub">Manage travels, tours, bookings, and client requests.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" autocomplete="username">
      </div>
      <div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
      </div>
      <button class="btn btn-block" type="submit">Sign in</button>
    </form>
    <p style="margin-top:16px;font-size:.9rem">
      <a href="<?= e(url('admin/forgot-password.php')) ?>">Forgot password?</a>
      · <a href="<?= e(url('admin/migrate.php')) ?>">Run migrations</a>
    </p>
  </div>
</div>
</body>
</html>
