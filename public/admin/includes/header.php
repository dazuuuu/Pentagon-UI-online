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
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="brand"><img class="brand-mark" src="assets/logo.png" alt=""><span><?= e($app) ?></span></div>
    <nav class="nav">
      <div class="section-label">Overview</div>
      <a href="index.php" class="<?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
      <a href="migrate.php" class="<?= ($active ?? '') === 'migrate' ? 'active' : '' ?>">Migrations</a>
      <div class="section-label">Catalog</div>
      <a href="travels.php" class="<?= ($active ?? '') === 'travels' ? 'active' : '' ?>">Travels</a>
      <a href="tours.php" class="<?= ($active ?? '') === 'tours' ? 'active' : '' ?>">Tours</a>
      <a href="testimonials.php" class="<?= ($active ?? '') === 'testimonials' ? 'active' : '' ?>">Testimonials</a>
      <div class="section-label">Operations</div>
      <a href="requests.php" class="<?= ($active ?? '') === 'requests' ? 'active' : '' ?>">Client Requests</a>
      <a href="bookings.php" class="<?= ($active ?? '') === 'bookings' ? 'active' : '' ?>">Bookings</a>
      <a href="clients.php" class="<?= ($active ?? '') === 'clients' ? 'active' : '' ?>">Clients</a>
      <a href="emails.php" class="<?= ($active ?? '') === 'emails' ? 'active' : '' ?>">Send Email</a>
      <div class="section-label">System</div>
      <a href="settings.php" class="<?= ($active ?? '') === 'settings' ? 'active' : '' ?>">SMTP Settings</a>
      <a href="logs.php" class="<?= ($active ?? '') === 'logs' ? 'active' : '' ?>">Activity Logs</a>
    </nav>
    <div class="sidebar-foot">
      <div>Signed in as<br><strong style="color:var(--text)"><?= e($admin['name'] ?? '') ?></strong></div>
      <a href="logout.php" class="btn btn-secondary btn-sm" style="margin-top:10px;display:inline-block;width:100%;text-align:center">Sign out</a>
    </div>
  </aside>
  <main class="main">
    <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('info')): ?><div class="alert alert-info"><?= e($msg) ?></div><?php endif; ?>
