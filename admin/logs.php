<?php
require_once __DIR__ . '/../backend/bootstrap.php';
$admin = Auth::requireAdmin();
$db = Database::get();
$logs = $db->query('SELECT * FROM activity_logs ORDER BY id DESC LIMIT 200')->fetchAll();
$pageTitle = 'Activity Logs';
$active = 'logs';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
  <div>
    <h1>Activity logs</h1>
    <p>Authentication and management events across admin and clients.</p>
  </div>
</div>
<div class="panel">
  <div class="table-wrap">
    <table>
      <thead><tr><th>When</th><th>Actor</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
      <tbody>
      <?php foreach ($logs as $l): ?>
        <tr>
          <td><?= e($l['created_at']) ?></td>
          <td><?= e($l['actor_type']) ?> #<?= e((string)$l['actor_id']) ?></td>
          <td><?= e($l['action']) ?></td>
          <td><?= e($l['details']) ?></td>
          <td><?= e($l['ip_address']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
