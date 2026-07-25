<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';

$adminCount = 0;
try {
    $adminCount = Auth::adminCount();
} catch (Throwable $e) {
    flash('error', 'Database not ready. Run migrations first.');
    redirect('/admin/migrate.php');
}

$open = config('allow_open_admin_register', true) && $adminCount === 0;
$current = Auth::admin();

if (!$open && !$current) {
    flash('error', 'Admin registration is closed. Sign in as an existing admin to add more.');
    redirect('/admin/login.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        if ($name === '' || $email === '' || $password === '') {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                Database::get()->prepare(
                    'INSERT INTO admins (name, email, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, 1, ?)'
                )->execute([$name, $email, $hash, $open ? 'superadmin' : 'admin', date('Y-m-d H:i:s')]);
                $id = (int)Database::get()->lastInsertId();
                log_activity('admin', $current['id'] ?? $id, 'admin_register', "Registered admin {$email}");
                if (!$current) {
                    Auth::attemptAdmin($email, $password);
                }
                flash('success', 'Admin account created successfully.');
                redirect('/admin/index.php');
            } catch (PDOException $e) {
                $error = str_contains($e->getMessage(), 'UNIQUE')
                    ? 'An admin with that email already exists.'
                    : 'Could not create admin: ' . $e->getMessage();
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
  <title>Register Admin · <?= e($app) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('admin/assets/admin.css')) ?>">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="brand"><div class="brand-mark"></div><span><?= e($app) ?></span></div>
    <h1><?= $open ? 'Create first admin' : 'Register admin' ?></h1>
    <p class="sub"><?= $open ? 'No admins exist yet. Create the first account to unlock the dashboard.' : 'Add another administrator account.' ?></p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <div>
        <label for="name">Full name</label>
        <input type="text" id="name" name="name" required value="<?= e($_POST['name'] ?? '') ?>">
      </div>
      <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8">
      </div>
      <div>
        <label for="password_confirm">Confirm password</label>
        <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
      </div>
      <button class="btn btn-block" type="submit">Create admin</button>
    </form>
    <p style="margin-top:16px;font-size:.9rem"><a href="<?= e(url('admin/login.php')) ?>">Back to login</a></p>
  </div>
</div>
</body>
</html>
