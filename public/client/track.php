<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';

$booking = null;
$error = '';
$code = trim($_GET['code'] ?? ($_POST['code'] ?? ''));

if ($code !== '') {
    try {
        $stmt = Database::get()->prepare(
            'SELECT b.tracking_code, b.title, b.status, b.progress_percent, b.start_date, b.end_date, b.guests, c.name AS client_name
             FROM bookings b JOIN clients c ON c.id = b.client_id
             WHERE b.tracking_code = ? LIMIT 1'
        );
        $stmt->execute([strtoupper(trim($code))]);
        $booking = $stmt->fetch() ?: null;
        if (!$booking) {
            // Also try exact case
            $stmt->execute([trim($code)]);
            $booking = $stmt->fetch() ?: null;
        }
        if (!$booking) {
            $error = 'No booking found for that tracking code.';
        }
    } catch (Throwable $e) {
        $error = 'Tracking is unavailable until migrations are run.';
    }
}

$pageTitle = 'Track Tour';
$client = Auth::client();
require __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width:560px;margin:0 auto 18px">
  <h1>Track by code</h1>
  <p>Enter the tracking code from your confirmation email (e.g. PQ-A1B2C3D4).</p>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="get" class="stack">
    <div>
      <label>Tracking code</label>
      <input type="text" name="code" required value="<?= e($code) ?>" placeholder="PQ-XXXXXXXX">
    </div>
    <button class="btn" type="submit">Look up</button>
  </form>
</div>

<?php if ($booking): ?>
<div class="card" style="max-width:560px;margin:0 auto">
  <div class="meta">
    <span class="badge"><?= e($booking['tracking_code']) ?></span>
    <?= status_badge($booking['status']) ?>
  </div>
  <h2 style="margin-top:10px"><?= e($booking['title']) ?></h2>
  <p>Guest: <?= e($booking['client_name']) ?></p>
  <p>
    <?= e($booking['start_date'] ?: 'Dates TBC') ?>
    <?= $booking['end_date'] ? ' → ' . e($booking['end_date']) : '' ?>
    · <?= (int)$booking['guests'] ?> guest(s)
  </p>
  <div class="progress"><span style="width:<?= (int)$booking['progress_percent'] ?>%"></span></div>
  <div class="help" style="margin-top:8px"><?= (int)$booking['progress_percent'] ?>% complete</div>
  <?php if ($client): ?>
    <p style="margin-top:14px"><a class="btn" href="booking.php?code=<?= urlencode($booking['tracking_code']) ?>">Open full timeline</a></p>
  <?php else: ?>
    <p style="margin-top:14px" class="help">Sign in to see the full update timeline for your bookings.</p>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
