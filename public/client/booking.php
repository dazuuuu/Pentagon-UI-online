<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';
$client = Auth::requireClient();
$db = Database::get();

$code = trim($_GET['code'] ?? '');
$stmt = $db->prepare('SELECT * FROM bookings WHERE tracking_code = ? AND client_id = ? LIMIT 1');
$stmt->execute([$code, $client['id']]);
$booking = $stmt->fetch();

if (!$booking) {
    flash('error', 'Booking not found for your account.');
    redirect('/client/index.php');
}

$u = $db->prepare('SELECT * FROM booking_updates WHERE booking_id = ? ORDER BY id DESC');
$u->execute([$booking['id']]);
$updates = $u->fetchAll();

$pageTitle = $booking['tracking_code'];
require __DIR__ . '/includes/header.php';
?>
<p><a href="<?= e(url('client/index.php')) ?>">← Back to my tours</a></p>
<div class="card">
  <div class="meta">
    <span class="badge"><?= e($booking['tracking_code']) ?></span>
    <?= status_badge($booking['status']) ?>
  </div>
  <h1 style="margin-top:10px"><?= e($booking['title']) ?></h1>
  <p>
    <?= e($booking['start_date'] ?: 'Start date TBC') ?>
    <?= $booking['end_date'] ? ' → ' . e($booking['end_date']) : '' ?>
    · <?= (int)$booking['guests'] ?> guest(s)
    <?php if ((float)$booking['amount'] > 0): ?>
      · <?= e($booking['currency']) ?> <?= number_format((float)$booking['amount'], 0) ?>
    <?php endif; ?>
  </p>
  <div class="progress"><span style="width:<?= (int)$booking['progress_percent'] ?>%"></span></div>
  <div class="help" style="margin-top:8px"><?= (int)$booking['progress_percent'] ?>% complete</div>
  <?php if ($booking['notes']): ?>
    <p style="margin-top:14px"><strong>Notes:</strong> <?= nl2br(e($booking['notes'])) ?></p>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Updates</h2>
  <?php if (!$updates): ?><div class="empty">No updates posted yet.</div><?php endif; ?>
  <div class="timeline" style="margin-top:14px">
    <?php foreach ($updates as $item): ?>
      <div class="item">
        <strong><?= e($item['title']) ?></strong>
        <?= $item['status'] ? status_badge($item['status']) : '' ?>
        <div class="help"><?= e($item['created_at']) ?></div>
        <p><?= nl2br(e($item['message'] ?? '')) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
