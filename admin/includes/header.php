<?php
/** @var string $pageTitle */
/** @var string $active */
/** @var array $admin */
$app = config('app_name', 'Pentagon Quest');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'Admin') ?> · <?= e($app) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,500;8..60,600;8..60,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/admin/assets/admin.css">
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="brand"><div class="brand-mark"></div><span><?= e($app) ?></span></div>
    <nav class="nav">
      <div class="section-label">Overview</div>
      <a href="/admin/index.php" class="<?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
      <a href="/admin/migrate.php" class="<?= ($active ?? '') === 'migrate' ? 'active' : '' ?>">Migrations</a>
      <div class="section-label">Catalog</div>
      <a href="/admin/travels.php" class="<?= ($active ?? '') === 'travels' ? 'active' : '' ?>">Travels</a>
      <a href="/admin/tours.php" class="<?= ($active ?? '') === 'tours' ? 'active' : '' ?>">Tours</a>
      <div class="section-label">Operations</div>
      <a href="/admin/requests.php" class="<?= ($active ?? '') === 'requests' ? 'active' : '' ?>">Client Requests</a>
      <a href="/admin/bookings.php" class="<?= ($active ?? '') === 'bookings' ? 'active' : '' ?>">Bookings</a>
      <a href="/admin/clients.php" class="<?= ($active ?? '') === 'clients' ? 'active' : '' ?>">Clients</a>
      <a href="/admin/emails.php" class="<?= ($active ?? '') === 'emails' ? 'active' : '' ?>">Send Email</a>
      <div class="section-label">System</div>
      <a href="/admin/settings.php" class="<?= ($active ?? '') === 'settings' ? 'active' : '' ?>">SMTP Settings</a>
      <a href="/admin/logs.php" class="<?= ($active ?? '') === 'logs' ? 'active' : '' ?>">Activity Logs</a>
      <a href="/admin/logout.php">Sign out</a>
    </nav>
    <div class="sidebar-foot">
      Signed in as<br><strong style="color:var(--text)"><?= e($admin['name'] ?? '') ?></strong>
    </div>
  </aside>
  <main class="main">
    <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('info')): ?><div class="alert alert-info"><?= e($msg) ?></div><?php endif; ?>
