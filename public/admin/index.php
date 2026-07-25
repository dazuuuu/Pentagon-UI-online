<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';
$admin = Auth::requireAdmin();
$db = Database::get();

$stats = [
    'travels' => (int)$db->query('SELECT COUNT(*) FROM travels')->fetchColumn(),
    'tours' => (int)$db->query('SELECT COUNT(*) FROM tours')->fetchColumn(),
    'requests' => (int)$db->query("SELECT COUNT(*) FROM client_requests WHERE status IN ('new','pending','in_progress')")->fetchColumn(),
    'bookings' => (int)$db->query('SELECT COUNT(*) FROM bookings')->fetchColumn(),
    'clients' => (int)$db->query('SELECT COUNT(*) FROM clients')->fetchColumn(),
];

$recentRequests = $db->query('SELECT * FROM client_requests ORDER BY id DESC LIMIT 6')->fetchAll();
$recentBookings = $db->query('SELECT b.*, c.name AS client_name FROM bookings b JOIN clients c ON c.id = b.client_id ORDER BY b.id DESC LIMIT 6')->fetchAll();
$logs = $db->query('SELECT * FROM activity_logs ORDER BY id DESC LIMIT 8')->fetchAll();

$pageTitle = 'Dashboard';
$active = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
  <div>
    <h1>Dashboard</h1>
    <p>Overview of catalog, requests, and client bookings.</p>
  </div>
  <div class="actions">
    <a class="btn btn-secondary btn-sm" href="travels.php">Add travel</a>
    <a class="btn btn-sm" href="emails.php">Send email</a>
  </div>
</div>

<div class="stats">
  <div class="stat"><div class="label">Travels</div><div class="value"><?= $stats['travels'] ?></div></div>
  <div class="stat"><div class="label">Tours</div><div class="value"><?= $stats['tours'] ?></div></div>
  <div class="stat"><div class="label">Open requests</div><div class="value"><?= $stats['requests'] ?></div></div>
  <div class="stat"><div class="label">Bookings</div><div class="value"><?= $stats['bookings'] ?></div></div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Recent client requests</h2><a href="requests.php">View all</a></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Name</th><th>Subject</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
      <?php if (!$recentRequests): ?><tr><td colspan="4" class="empty">No requests yet.</td></tr><?php endif; ?>
      <?php foreach ($recentRequests as $r): ?>
        <tr>
          <td><a href="requests.php?id=<?= (int)$r['id'] ?>"><?= e($r['name']) ?></a><div class="help"><?= e($r['email']) ?></div></td>
          <td><?= e($r['subject'] ?: '—') ?></td>
          <td><?= status_badge($r['status']) ?></td>
          <td><?= e($r['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Recent bookings</h2><a href="bookings.php">Manage</a></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tracking</th><th>Client</th><th>Tour</th><th>Status</th><th>Progress</th></tr></thead>
      <tbody>
      <?php if (!$recentBookings): ?><tr><td colspan="5" class="empty">No bookings yet.</td></tr><?php endif; ?>
      <?php foreach ($recentBookings as $b): ?>
        <tr>
          <td><a href="bookings.php?id=<?= (int)$b['id'] ?>"><?= e($b['tracking_code']) ?></a></td>
          <td><?= e($b['client_name']) ?></td>
          <td><?= e($b['title']) ?></td>
          <td><?= status_badge($b['status']) ?></td>
          <td><?= (int)$b['progress_percent'] ?>%</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Activity</h2><a href="logs.php">Full log</a></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>When</th><th>Actor</th><th>Action</th><th>Details</th></tr></thead>
      <tbody>
      <?php foreach ($logs as $l): ?>
        <tr>
          <td><?= e($l['created_at']) ?></td>
          <td><?= e($l['actor_type']) ?> #<?= e((string)$l['actor_id']) ?></td>
          <td><?= e($l['action']) ?></td>
          <td><?= e($l['details']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
