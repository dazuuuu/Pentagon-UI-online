<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';
        if (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif ($token === '') {
            $error = 'Missing reset token.';
        } elseif (Auth::consumePasswordReset('admin', $token, $password)) {
            flash('success', 'Password updated. You can sign in now.');
            redirect('/admin/login.php');
        } else {
            $error = 'This reset link is invalid or has expired.';
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
  <title>Reset Password · <?= e($app) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('admin/assets/admin.css')) ?>">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="brand"><div class="brand-mark"></div><span><?= e($app) ?></span></div>
    <h1>Set new password</h1>
    <p class="sub">Choose a strong password for your admin account.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <div>
        <label for="password">New password</label>
        <input type="password" id="password" name="password" required minlength="8">
      </div>
      <div>
        <label for="password_confirm">Confirm password</label>
        <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
      </div>
      <button class="btn btn-block" type="submit">Update password</button>
    </form>
    <p style="margin-top:16px;font-size:.9rem"><a href="<?= e(url('admin/login.php')) ?>">Back to login</a></p>
  </div>
</div>
</body>
</html>
