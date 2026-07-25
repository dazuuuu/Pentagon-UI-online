<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';
$client = Auth::requireClient();
$db = Database::get();

$stmt = $db->prepare('SELECT * FROM bookings WHERE client_id = ? ORDER BY id DESC');
$stmt->execute([$client['id']]);
$bookings = $stmt->fetchAll();

$pageTitle = 'My Tours';
require __DIR__ . '/includes/header.php';
?>
<h1>My tours</h1>
<p>Hello <?= e($client['name']) ?>. Track every booking and live update from our team.</p>

<?php if (!$bookings): ?>
  <div class="card empty">
    No tours yet. When Pentagon Quest confirms a booking for your account, it will show up here.
    You can also <a href="/client/track.php">look up a tracking code</a>.
  </div>
<?php endif; ?>

<div class="grid">
<?php foreach ($bookings as $b): ?>
  <a class="card" href="/client/booking.php?code=<?= urlencode($b['tracking_code']) ?>" style="color:inherit">
    <div class="meta">
      <span class="badge"><?= e($b['tracking_code']) ?></span>
      <?= status_badge($b['status']) ?>
    </div>
    <h2 style="margin-top:10px;font-size:1.25rem"><?= e($b['title']) ?></h2>
    <p style="margin:6px 0 0">
      <?= e($b['start_date'] ?: 'Dates TBC') ?>
      <?= $b['end_date'] ? ' → ' . e($b['end_date']) : '' ?>
      · <?= (int)$b['guests'] ?> guest(s)
    </p>
    <div class="progress" title="<?= (int)$b['progress_percent'] ?>%"><span style="width:<?= (int)$b['progress_percent'] ?>%"></span></div>
    <div class="help" style="margin-top:8px"><?= (int)$b['progress_percent'] ?>% complete</div>
  </a>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
