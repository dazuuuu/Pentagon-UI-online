<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';
$admin = Auth::requireAdmin();
$db = Database::get();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'update' && $id) {
        $status = $_POST['status'] ?? 'new';
        $notes = trim($_POST['admin_notes'] ?? '');

        $current = $db->prepare('SELECT status, name, email, subject FROM client_requests WHERE id = ?');
        $current->execute([$id]);
        $before = $current->fetch();

        $db->prepare('UPDATE client_requests SET status=?, admin_notes=?, assigned_admin_id=?, updated_at=? WHERE id=?')
            ->execute([$status, $notes, $admin['id'], date('Y-m-d H:i:s'), $id]);
        log_activity('admin', $admin['id'], 'request_update', "Updated request #{$id} to {$status}");

        if ($before && $status === 'accepted' && $before['status'] !== 'accepted') {
            $mailer = new Mailer();
            $mailer->sendTemplate(
                $before['email'],
                'Your request has been accepted · Pentagon Quest',
                'Good news, ' . $before['name'] . '!',
                '<p>Your inquiry' . ($before['subject'] ? ' — "' . e($before['subject']) . '"' : '') . ' has been accepted. Our team will be in touch shortly with next steps.</p>'
            );
        }

        flash('success', 'Request updated.');
        redirect('/admin/requests.php?id=' . $id);
    }

    if ($action === 'email' && $id) {
        $stmt = $db->prepare('SELECT * FROM client_requests WHERE id = ?');
        $stmt->execute([$id]);
        $req = $stmt->fetch();
        if ($req) {
            $subject = trim($_POST['email_subject'] ?? '');
            $body = trim($_POST['email_body'] ?? '');
            if ($subject && $body) {
                $mailer = new Mailer();
                $ok = $mailer->sendTemplate($req['email'], $subject, $subject, nl2br(e($body)));
                if ($ok) {
                    flash('success', 'Email sent to ' . $req['email']);
                } else {
                    flash('error', 'Email failed: ' . $mailer->getLastError());
                }
            }
        }
        redirect('/admin/requests.php?id=' . $id);
    }

    if ($action === 'delete' && $id) {
        $db->prepare('DELETE FROM client_requests WHERE id = ?')->execute([$id]);
        flash('success', 'Request deleted.');
        redirect('/admin/requests.php');
    }
}

$view = null;
if (isset($_GET['id'])) {
    $stmt = $db->prepare('SELECT * FROM client_requests WHERE id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $view = $stmt->fetch() ?: null;
}

$filter = $_GET['status'] ?? '';
if ($filter) {
    $stmt = $db->prepare('SELECT * FROM client_requests WHERE status = ? ORDER BY id DESC');
    $stmt->execute([$filter]);
    $rows = $stmt->fetchAll();
} else {
    $rows = $db->query('SELECT * FROM client_requests ORDER BY id DESC')->fetchAll();
}

$pageTitle = 'Client Requests';
$active = 'requests';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
  <div>
    <h1>Client requests</h1>
    <p>Inquiries from the contact form and newsletter leads.</p>
  </div>
  <div class="actions">
    <a class="btn btn-secondary btn-sm" href="/admin/requests.php">All</a>
    <a class="btn btn-secondary btn-sm" href="/admin/requests.php?status=new">New</a>
    <a class="btn btn-secondary btn-sm" href="/admin/requests.php?status=in_progress">In progress</a>
  </div>
</div>

<?php if ($view): ?>
<div class="panel">
  <div class="panel-head">
    <h2>Request #<?= (int)$view['id'] ?> · <?= e($view['name']) ?></h2>
    <a href="/admin/requests.php">Back to list</a>
  </div>
  <div class="form-grid">
    <div><label>Email</label><div><?= e($view['email']) ?></div></div>
    <div><label>Phone</label><div><?= e($view['phone'] ?: '—') ?></div></div>
    <div><label>Subject</label><div><?= e($view['subject'] ?: '—') ?></div></div>
    <div><label>Tour interest</label><div><?= e($view['tour_interest'] ?: '—') ?></div></div>
    <div class="full"><label>Message</label><div class="panel" style="background:var(--bg-soft)"><?= nl2br(e($view['message'])) ?></div></div>
  </div>
  <hr style="border:0;border-top:1px solid var(--line);margin:18px 0">
  <form method="post" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?= (int)$view['id'] ?>">
    <div>
      <label>Status</label>
      <select name="status">
        <?php foreach (['new','pending','accepted','in_progress','completed','rejected'] as $s): ?>
          <option value="<?= $s ?>" <?= $view['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="full">
      <label>Admin notes</label>
      <textarea name="admin_notes"><?= e($view['admin_notes'] ?? '') ?></textarea>
    </div>
    <div class="full"><button class="btn" type="submit">Save request</button></div>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>Reply by email</h2></div>
  <form method="post" class="stack">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="email">
    <input type="hidden" name="id" value="<?= (int)$view['id'] ?>">
    <div>
      <label>Subject</label>
      <input type="text" name="email_subject" value="Re: <?= e($view['subject'] ?: 'Your Pentagon Quest inquiry') ?>" required>
    </div>
    <div>
      <label>Message</label>
      <textarea name="email_body" required placeholder="Hi <?= e($view['name']) ?>,&#10;&#10;Thank you for reaching out..."></textarea>
    </div>
    <button class="btn" type="submit">Send email</button>
  </form>
</div>
<?php endif; ?>

<div class="panel">
  <div class="panel-head"><h2>All requests (<?= count($rows) ?>)</h2></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Client</th><th>Subject</th><th>Source</th><th>Status</th><th>Date</th><th></th></tr></thead>
      <tbody>
      <?php if (!$rows): ?><tr><td colspan="6" class="empty">No client requests yet.</td></tr><?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><strong><?= e($r['name']) ?></strong><div class="help"><?= e($r['email']) ?></div></td>
          <td><?= e($r['subject'] ?: mb_strimwidth($r['message'], 0, 60, '…')) ?></td>
          <td><?= e($r['source']) ?></td>
          <td><?= status_badge($r['status']) ?></td>
          <td><?= e($r['created_at']) ?></td>
          <td class="actions">
            <a class="btn btn-secondary btn-sm" href="/admin/requests.php?id=<?= (int)$r['id'] ?>">Open</a>
            <form method="post" onsubmit="return confirm('Delete request?')">
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
