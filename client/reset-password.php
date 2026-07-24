<?php
require_once __DIR__ . '/../backend/bootstrap.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$error = '';

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
        } elseif (!Auth::consumePasswordReset('client', $token, $password)) {
            $error = 'This reset link is invalid or has expired.';
        } else {
            flash('success', 'Password updated. Please sign in.');
            redirect('/client/login.php');
        }
    }
}
$pageTitle = 'Reset Password';
$client = null;
require __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width:440px;margin:20px auto">
  <h1>Choose a new password</h1>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="stack">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <div><label>New password</label><input type="password" name="password" required minlength="8"></div>
    <div><label>Confirm password</label><input type="password" name="password_confirm" required minlength="8"></div>
    <button class="btn btn-block" type="submit">Update password</button>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
