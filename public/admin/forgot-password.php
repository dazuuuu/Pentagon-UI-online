<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';

$message = '';
$error = '';
$sent = false;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email.';
        } else {
            try {
                $otp = Auth::createPasswordReset('admin', $email);
                // Always show success to avoid email enumeration
                $sent = true;
                $message = 'If that email exists, a 6-digit code has been sent. It expires in 15 minutes.';
                if ($otp) {
                    $mailer = new Mailer();
                    $ok = $mailer->sendTemplate(
                        $email,
                        'Your Pentagon Quest admin password reset code',
                        'Password reset code',
                        '<p>We received a request to reset your admin password. Enter this code to continue. It expires in 15 minutes.</p>'
                        . '<p style="font-size:32px;font-weight:700;letter-spacing:6px;text-align:center;margin:20px 0">' . e($otp) . '</p>'
                        . '<p>If you did not request this, you can ignore this email.</p>'
                    );
                    if (!$ok) {
                        // Fallback: show the code when SMTP not configured (dev-friendly)
                        $message = 'SMTP is not configured yet. Your reset code (valid 15 minutes): ' . e($otp);
                        $error = $mailer->getLastError();
                    }
                }
            } catch (Throwable $e) {
                $error = 'Could not process request. Run migrations first.';
                $sent = false;
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
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="brand"><img class="brand-mark" src="assets/logo.png" alt=""><span><?= e($app) ?></span></div>
    <h1>Forgot password</h1>
    <p class="sub">Enter your admin email and we will send a 6-digit reset code.</p>
    <?php if ($error && !$sent): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($sent): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($sent && $error): ?><div class="alert alert-info"><?= e($error) ?></div><?php endif; ?>
    <?php if (!$sent): ?>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= e($email) ?>">
      </div>
      <button class="btn btn-block" type="submit">Send code</button>
    </form>
    <?php else: ?>
    <p style="margin-top:16px"><a class="btn btn-block" href="reset-password.php?email=<?= urlencode($email) ?>">I have my code</a></p>
    <?php endif; ?>
    <p style="margin-top:16px;font-size:.9rem"><a href="login.php">Back to login</a></p>
  </div>
</div>
</body>
</html>
