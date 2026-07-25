<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';
$admin = Auth::requireAdmin();
$db = Database::get();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $clientId = (int)($_POST['client_id'] ?? 0);
        $tourId = (int)($_POST['tour_id'] ?? 0) ?: null;
        $title = trim($_POST['title'] ?? '');
        $status = $_POST['status'] ?? 'pending';
        $start = $_POST['start_date'] ?: null;
        $end = $_POST['end_date'] ?: null;
        $guests = max(1, (int)($_POST['guests'] ?? 1));
        $amount = (float)($_POST['amount'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $progress = max(0, min(100, (int)($_POST['progress_percent'] ?? 0)));
        $code = tracking_code();

        if (!$clientId || $title === '') {
            flash('error', 'Client and title are required.');
        } else {
            $travelId = null;
            if ($tourId) {
                $t = $db->prepare('SELECT travel_id, title, price FROM tours WHERE id = ?');
                $t->execute([$tourId]);
                $tour = $t->fetch();
                if ($tour) {
                    $travelId = $tour['travel_id'];
                    if ($title === '') $title = $tour['title'];
                    if ($amount <= 0) $amount = (float)$tour['price'];
                }
            }
            $db->prepare('INSERT INTO bookings (client_id, tour_id, travel_id, tracking_code, title, status, start_date, end_date, guests, amount, notes, progress_percent, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$clientId, $tourId, $travelId, $code, $title, $status, $start, $end, $guests, $amount, $notes, $progress, date('Y-m-d H:i:s')]);
            $bookingId = (int)$db->lastInsertId();
            $db->prepare('INSERT INTO booking_updates (booking_id, title, message, status, created_by_admin_id, created_at) VALUES (?,?,?,?,?,?)')
                ->execute([$bookingId, 'Booking created', 'Your tour booking has been created. Tracking code: ' . $code, $status, $admin['id'], date('Y-m-d H:i:s')]);
            log_activity('admin', $admin['id'], 'booking_create', "Created booking {$code}");

            $client = $db->prepare('SELECT email, name FROM clients WHERE id = ?');
            $client->execute([$clientId]);
            $c = $client->fetch();
            if ($c) {
                $mailer = new Mailer();
                $mailer->sendTemplate(
                    $c['email'],
                    'Your Pentagon Quest booking ' . $code,
                    'Booking confirmed',
                    '<p>Hi ' . e($c['name']) . ',</p><p>Your tour <strong>' . e($title) . '</strong> is registered. Track it anytime with code <strong>' . e($code) . '</strong>.</p>',
                    'Track your tour',
                    app_url('/client/login.php')
                );
            }
            flash('success', "Booking created with tracking code {$code}.");
            redirect('/admin/bookings.php?id=' . $bookingId);
        }
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        $progress = max(0, min(100, (int)($_POST['progress_percent'] ?? 0)));
        $adminNotes = trim($_POST['admin_notes'] ?? '');
        $start = $_POST['start_date'] ?: null;
        $end = $_POST['end_date'] ?: null;
        $amount = (float)($_POST['amount'] ?? 0);
        $db->prepare('UPDATE bookings SET status=?, progress_percent=?, admin_notes=?, start_date=?, end_date=?, amount=?, updated_at=? WHERE id=?')
            ->execute([$status, $progress, $adminNotes, $start, $end, $amount, date('Y-m-d H:i:s'), $id]);

        $updateTitle = trim($_POST['update_title'] ?? '');
        $updateMsg = trim($_POST['update_message'] ?? '');
        if ($updateTitle !== '') {
            $db->prepare('INSERT INTO booking_updates (booking_id, title, message, status, created_by_admin_id, created_at) VALUES (?,?,?,?,?,?)')
                ->execute([$id, $updateTitle, $updateMsg, $status, $admin['id'], date('Y-m-d H:i:s')]);

            $info = $db->prepare('SELECT b.tracking_code, b.title, c.email, c.name FROM bookings b JOIN clients c ON c.id = b.client_id WHERE b.id = ?');
            $info->execute([$id]);
            $row = $info->fetch();
            if ($row) {
                $mailer = new Mailer();
                $mailer->sendTemplate(
                    $row['email'],
                    'Tour update · ' . $row['tracking_code'],
                    $updateTitle,
                    '<p>Hi ' . e($row['name']) . ',</p><p>' . nl2br(e($updateMsg ?: 'Your tour status was updated to ' . $status . '.')) . '</p><p>Tracking: <strong>' . e($row['tracking_code']) . '</strong></p>',
                    'View booking',
                    app_url('/client/booking.php?code=' . urlencode($row['tracking_code']))
                );
            }
        }
        log_activity('admin', $admin['id'], 'booking_update', "Updated booking #{$id}");
        flash('success', 'Booking updated.');
        redirect('/admin/bookings.php?id=' . $id);
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM bookings WHERE id = ?')->execute([$id]);
        flash('success', 'Booking deleted.');
        redirect('/admin/bookings.php');
    }
}

$view = null;
$updates = [];
if (isset($_GET['id'])) {
    $stmt = $db->prepare('SELECT b.*, c.name AS client_name, c.email AS client_email FROM bookings b JOIN clients c ON c.id = b.client_id WHERE b.id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $view = $stmt->fetch() ?: null;
    if ($view) {
        $u = $db->prepare('SELECT * FROM booking_updates WHERE booking_id = ? ORDER BY id DESC');
        $u->execute([$view['id']]);
        $updates = $u->fetchAll();
    }
}

$clients = $db->query('SELECT id, name, email FROM clients ORDER BY name ASC')->fetchAll();
$tours = $db->query("SELECT id, title, price FROM tours WHERE status = 'published' ORDER BY title ASC")->fetchAll();
$rows = $db->query('SELECT b.*, c.name AS client_name FROM bookings b JOIN clients c ON c.id = b.client_id ORDER BY b.id DESC')->fetchAll();

$pageTitle = 'Bookings';
$active = 'bookings';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
  <div>
    <h1>Bookings</h1>
    <p>Create client tours and publish tracking updates.</p>
  </div>
</div>

<?php if ($view): ?>
<div class="panel">
  <div class="panel-head">
    <h2><?= e($view['tracking_code']) ?> · <?= e($view['title']) ?></h2>
    <a href="/admin/bookings.php">Back</a>
  </div>
  <p class="muted">Client: <strong style="color:var(--text)"><?= e($view['client_name']) ?></strong> (<?= e($view['client_email']) ?>)</p>
  <form method="post" class="form-grid" style="margin-top:14px">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?= (int)$view['id'] ?>">
    <div>
      <label>Status</label>
      <select name="status">
        <?php foreach (['pending','confirmed','in_progress','completed','cancelled'] as $s): ?>
          <option value="<?= $s ?>" <?= $view['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Progress %</label>
      <input type="number" name="progress_percent" min="0" max="100" value="<?= (int)$view['progress_percent'] ?>">
    </div>
    <div>
      <label>Start date</label>
      <input type="date" name="start_date" value="<?= e($view['start_date'] ?? '') ?>">
    </div>
    <div>
      <label>End date</label>
      <input type="date" name="end_date" value="<?= e($view['end_date'] ?? '') ?>">
    </div>
    <div>
      <label>Amount</label>
      <input type="number" step="0.01" name="amount" value="<?= e((string)$view['amount']) ?>">
    </div>
    <div class="full">
      <label>Admin notes</label>
      <textarea name="admin_notes"><?= e($view['admin_notes'] ?? '') ?></textarea>
    </div>
    <div>
      <label>Client update title (optional)</label>
      <input type="text" name="update_title" placeholder="e.g. Flights confirmed">
    </div>
    <div>
      <label>Client update message</label>
      <textarea name="update_message" placeholder="Visible to the client and emailed if SMTP is set"></textarea>
    </div>
    <div class="full"><button class="btn" type="submit">Save & notify</button></div>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>Timeline</h2></div>
  <?php if (!$updates): ?><div class="empty">No updates yet.</div><?php endif; ?>
  <?php foreach ($updates as $u): ?>
    <div style="padding:12px 0;border-bottom:1px solid var(--line)">
      <strong><?= e($u['title']) ?></strong> <?= $u['status'] ? status_badge($u['status']) : '' ?>
      <div class="help"><?= e($u['created_at']) ?></div>
      <p style="margin:.4rem 0 0"><?= nl2br(e($u['message'] ?? '')) ?></p>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="panel">
  <div class="panel-head"><h2>Create booking</h2></div>
  <form method="post" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">
    <div>
      <label>Client</label>
      <select name="client_id" required>
        <option value="">Select client…</option>
        <?php foreach ($clients as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['email']) ?>)</option>
        <?php endforeach; ?>
      </select>
      <?php if (!$clients): ?><div class="help">No clients yet — <a href="/admin/clients.php">create one</a>.</div><?php endif; ?>
    </div>
    <div>
      <label>Tour package</label>
      <select name="tour_id">
        <option value="">Custom / none</option>
        <?php foreach ($tours as $t): ?>
          <option value="<?= (int)$t['id'] ?>"><?= e($t['title']) ?> — <?= number_format((float)$t['price'], 0) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="full">
      <label>Booking title</label>
      <input type="text" name="title" required placeholder="e.g. Maasai Mara family safari">
    </div>
    <div>
      <label>Status</label>
      <select name="status">
        <?php foreach (['pending','confirmed','in_progress'] as $s): ?>
          <option value="<?= $s ?>"><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Guests</label>
      <input type="number" name="guests" min="1" value="1">
    </div>
    <div>
      <label>Start date</label>
      <input type="date" name="start_date">
    </div>
    <div>
      <label>End date</label>
      <input type="date" name="end_date">
    </div>
    <div>
      <label>Amount</label>
      <input type="number" step="0.01" name="amount" value="0">
    </div>
    <div>
      <label>Progress %</label>
      <input type="number" name="progress_percent" min="0" max="100" value="10">
    </div>
    <div class="full">
      <label>Notes</label>
      <textarea name="notes"></textarea>
    </div>
    <div class="full"><button class="btn" type="submit">Create booking</button></div>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>All bookings (<?= count($rows) ?>)</h2></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tracking</th><th>Client</th><th>Tour</th><th>Status</th><th>Progress</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><strong><?= e($r['tracking_code']) ?></strong></td>
          <td><?= e($r['client_name']) ?></td>
          <td><?= e($r['title']) ?></td>
          <td><?= status_badge($r['status']) ?></td>
          <td><?= (int)$r['progress_percent'] ?>%</td>
          <td class="actions">
            <a class="btn btn-secondary btn-sm" href="/admin/bookings.php?id=<?= (int)$r['id'] ?>">Manage</a>
            <form method="post" onsubmit="return confirm('Delete booking?')">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-danger btn-sm" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
