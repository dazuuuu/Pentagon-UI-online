<?php
require_once __DIR__ . '/../backend/bootstrap.php';

$message = '';
$error = '';
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email.';
        } else {
            try {
                $token = Auth::createPasswordReset('admin', $email);
                // Always show success to avoid email enumeration
                $sent = true;
                $message = 'If that email exists, a reset link has been sent.';
                if ($token) {
                    $link = app_url('/admin/reset-password.php?token=' . urlencode($token));
                    $mailer = new Mailer();
                    $ok = $mailer->sendTemplate(
                        $email,
                        'Reset your Pentagon Quest admin password',
                        'Password reset',
                        '<p>We received a request to reset your admin password. This link expires in 1 hour.</p>',
                        'Reset password',
                        $link
                    );
                    if (!$ok) {
                        // Fallback: show link when SMTP not configured (dev-friendly)
                        $message = 'SMTP is not configured yet. Use this reset link (valid 1 hour): ' . $link;
                        $error = $mailer->getLastError();
                    }
                }
            } catch (Throwable $e) {
                $error = 'Could not process request. Run migrations first.';
            }
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
  <title>Forgot Password · <?= e($app) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/admin/assets/admin.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="brand"><div class="brand-mark"></div><span><?= e($app) ?></span></div>
    <h1>Forgot password</h1>
    <p class="sub">Enter your admin email and we will send a reset link.</p>
    <?php if ($error && !$sent): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($sent): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($sent && $error): ?><div class="alert alert-info"><?= e($error) ?></div><?php endif; ?>
    <?php if (!$sent): ?>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <button class="btn btn-block" type="submit">Send reset link</button>
    </form>
    <?php endif; ?>
    <p style="margin-top:16px;font-size:.9rem"><a href="/admin/login.php">Back to login</a></p>
  </div>
</div>
</body>
</html>
