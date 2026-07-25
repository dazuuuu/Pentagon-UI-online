<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';

if (Auth::client()) {
    redirect('/client/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (Auth::attemptClient($email, $password)) {
            flash('success', 'Welcome back.');
            redirect('/client/index.php');
        }
        $error = 'Invalid email or password.';
    }
}
$pageTitle = 'Client Login';
$client = null;
require __DIR__ . '/includes/header.php';
?>
<div class="auth" style="min-height:auto;padding:0;display:block">
  <div class="card" style="max-width:440px;margin:40px auto">
    <h1>Track your tours</h1>
    <p>Sign in to see booking status, progress, and updates from Pentagon Quest.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <div>
        <label>Email</label>
        <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div>
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button class="btn btn-block" type="submit">Sign in</button>
    </form>
    <p style="margin-top:14px;font-size:.9rem">
      <a href="/client/forgot-password.php">Forgot password?</a> ·
      <a href="/client/register.php">Create account</a> ·
      <a href="/client/track.php">Track by code</a>
    </p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
