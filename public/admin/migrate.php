<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';

$results = [];
$error = '';
$canRun = true;

try {
    $migrator = new Migrator();
} catch (Throwable $e) {
    $canRun = false;
    $error = 'Could not connect to database: ' . $e->getMessage();
    $migrator = null;
}

if ($canRun && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token.';
    } else {
        $results = $migrator->runAll();
        $failed = array_filter($results, fn($r) => ($r['status'] ?? '') === 'error');
        if ($failed) {
            flash('error', 'Some migrations failed. Check details below.');
        } else {
            flash('success', 'Migrations completed successfully.');
        }
    }
}

$pending = $canRun ? $migrator->pending() : [];
$ran = $canRun ? $migrator->ran() : [];
$admin = Auth::admin();
$app = config('app_name', 'Pentagon Quest');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Migrations · <?= e($app) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('admin/assets/admin.css')) ?>">
</head>
<body>
<?php if ($admin): $pageTitle = 'Migrations'; $active = 'migrate'; require __DIR__ . '/includes/header.php'; else: ?>
<div class="auth-wrap"><div class="auth-card" style="width:min(720px,100%)">
  <div class="brand"><div class="brand-mark"></div><span><?= e($app) ?></span></div>
  <h1>Database migrations</h1>
  <p class="sub">Run this once after deployment to create tables. Then register your first admin.</p>
<?php endif; ?>

<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>

<?php if (!$admin): ?><?php else: ?>
<div class="topbar">
  <div>
    <h1>Migrations</h1>
    <p>Apply pending schema changes and seed sample catalog data.</p>
  </div>
</div>
<?php endif; ?>

<div class="panel">
  <div class="panel-head">
    <h2>Pending (<?= count($pending) ?>)</h2>
    <?php if ($canRun): ?>
    <form method="post">
      <?= csrf_field() ?>
      <button class="btn" type="submit" <?= $pending ? '' : 'disabled' ?>>Run migrations</button>
    </form>
    <?php endif; ?>
  </div>
  <?php if ($pending): ?>
    <ul>
      <?php foreach ($pending as $p): ?><li class="muted"><?= e($p) ?></li><?php endforeach; ?>
    </ul>
  <?php else: ?>
    <div class="empty">No pending migrations.</div>
  <?php endif; ?>
</div>

<?php if ($results): ?>
<div class="panel">
  <div class="panel-head"><h2>Last run results</h2></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Migration</th><th>Status</th><th>Message</th></tr></thead>
      <tbody>
      <?php foreach ($results as $r): ?>
        <tr>
          <td><?= e($r['migration'] ?? '—') ?></td>
          <td><?= status_badge($r['status'] === 'ok' ? 'completed' : 'failed') ?></td>
          <td><?= e($r['message'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="panel">
  <div class="panel-head"><h2>Applied (<?= count($ran) ?>)</h2></div>
  <?php if ($ran): ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Migration</th><th>Batch</th><th>Ran at</th></tr></thead>
      <tbody>
      <?php foreach ($ran as $r): ?>
        <tr>
          <td><?= e($r['migration']) ?></td>
          <td><?= e((string)$r['batch']) ?></td>
          <td><?= e($r['ran_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <div class="empty">No migrations have been applied yet.</div>
  <?php endif; ?>
</div>

<p class="muted" style="margin-top:8px">
  Next: open <a href="<?= e(url('devs/register.php')) ?>"><code>devs/register.php</code></a> to create the first admin · <a href="<?= e(url('admin/login.php')) ?>">Login</a> · Driver: <strong><?= e(Database::driver()) ?></strong>
</p>

<?php if ($admin): require __DIR__ . '/includes/footer.php'; else: ?>
</div></div>
</body></html>
<?php endif; ?>
