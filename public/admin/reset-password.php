<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';

$email = strtolower(trim($_GET['email'] ?? ($_POST['email'] ?? '')));
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token.';
    } else {
        $otp = trim($_POST['otp'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email.';
        } elseif ($otp === '' || !preg_match('/^\d{6}$/', $otp)) {
            $error = 'Enter the 6-digit code from your email.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (Auth::consumePasswordReset('admin', $email, $otp, $password)) {
            flash('success', 'Password updated. You can sign in now.');
            redirect(base_path('/admin/login.php'));
        } else {
            $error = 'That code is invalid or has expired. Request a new one.';
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
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="brand"><img class="brand-mark" src="assets/logo.png" alt=""><span><?= e($app) ?></span></div>
    <h1>Enter your code</h1>
    <p class="sub">Enter the 6-digit code we emailed you and choose a new password.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= e($email) ?>">
      </div>
      <div>
        <label for="otp">6-digit code</label>
        <input type="text" id="otp" name="otp" inputmode="numeric" pattern="\d{6}" maxlength="6" required
               style="letter-spacing:6px;font-size:20px;text-align:center" value="<?= e($_POST['otp'] ?? '') ?>">
      </div>
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
    <p style="margin-top:16px;font-size:.9rem"><a href="forgot-password.php">Didn't get a code? Request another</a></p>
    <p style="margin-top:8px;font-size:.9rem"><a href="login.php">Back to login</a></p>
  </div>
</div>
</body>
</html>
