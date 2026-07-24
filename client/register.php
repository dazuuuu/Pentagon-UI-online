<?php
require_once __DIR__ . '/../backend/bootstrap.php';

if (Auth::client()) {
    redirect('/client/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Valid name and email are required.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            try {
                Database::get()->prepare(
                    'INSERT INTO clients (name, email, phone, password_hash, is_active, created_at) VALUES (?,?,?,?,1,?)'
                )->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT), date('Y-m-d H:i:s')]);
                Auth::attemptClient($email, $password);
                flash('success', 'Account created. Your tours will appear here once booked.');
                redirect('/client/index.php');
            } catch (PDOException $e) {
                $error = str_contains($e->getMessage(), 'UNIQUE')
                    ? 'An account with that email already exists.'
                    : 'Could not create account. Run migrations first.';
            }
        }
    }
}
$pageTitle = 'Register';
$client = null;
require __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width:480px;margin:20px auto">
  <h1>Create client account</h1>
  <p>Register to track your Pentagon Quest bookings and receive updates.</p>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="stack">
    <?= csrf_field() ?>
    <div><label>Full name</label><input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>"></div>
    <div><label>Email</label><input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></div>
    <div><label>Phone</label><input type="text" name="phone" value="<?= e($_POST['phone'] ?? '') ?>"></div>
    <div><label>Password</label><input type="password" name="password" required minlength="8"></div>
    <div><label>Confirm password</label><input type="password" name="password_confirm" required minlength="8"></div>
    <button class="btn btn-block" type="submit">Register</button>
  </form>
  <p style="margin-top:14px"><a href="/client/login.php">Already have an account? Sign in</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
