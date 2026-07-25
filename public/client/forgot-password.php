<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';

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
                $token = Auth::createPasswordReset('client', $email);
                $sent = true;
                $message = 'If that email exists, a reset link has been sent.';
                if ($token) {
                    $link = app_url('/client/reset-password.php?token=' . urlencode($token));
                    $mailer = new Mailer();
                    $ok = $mailer->sendTemplate(
                        $email,
                        'Reset your Pentagon Quest password',
                        'Password reset',
                        '<p>Use the button below to choose a new password. This link expires in 1 hour.</p>',
                        'Reset password',
                        $link
                    );
                    if (!$ok) {
                        $message = 'SMTP is not configured yet. Use this reset link: ' . $link;
                        $error = $mailer->getLastError();
                    }
                }
            } catch (Throwable $e) {
                $error = 'Could not process request. Ask an admin to run migrations.';
            }
        }
    }
}
$pageTitle = 'Forgot Password';
$client = null;
require __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width:440px;margin:20px auto">
  <h1>Forgot password</h1>
  <p>We will email a secure link to reset your client account password.</p>
  <?php if ($error && !$sent): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <?php if ($sent): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
  <?php if ($sent && $error): ?><div class="alert alert-info"><?= e($error) ?></div><?php endif; ?>
  <?php if (!$sent): ?>
  <form method="post" class="stack">
    <?= csrf_field() ?>
    <div><label>Email</label><input type="email" name="email" required></div>
    <button class="btn btn-block" type="submit">Send reset link</button>
  </form>
  <?php endif; ?>
  <p style="margin-top:14px"><a href="/client/login.php">Back to login</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
