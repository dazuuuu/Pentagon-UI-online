<?php
/** @var string $pageTitle */
/** @var array|null $client */
$app = config('app_name', 'Pentagon Quest');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'My Tours') ?> · <?= e($app) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/client.css">
</head>
<body>
<div class="shell">
  <div class="top">
    <div class="brand"><i></i> <?= e($app) ?></div>
    <div class="nav">
      <?php if (!empty($client)): ?>
        <a href="index.php">My tours</a>
        <a href="track.php">Track by code</a>
        <a href="logout.php">Sign out</a>
      <?php else: ?>
        <a href="../">Website</a>
        <a href="login.php">Sign in</a>
        <a href="register.php">Register</a>
      <?php endif; ?>
    </div>
  </div>
  <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
  <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
  <?php if ($msg = flash('info')): ?><div class="alert alert-info"><?= e($msg) ?></div><?php endif; ?>
