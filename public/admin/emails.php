<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';
$admin = Auth::requireAdmin();
$db = Database::get();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $to = trim($_POST['to'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    if (!filter_var($to, FILTER_VALIDATE_EMAIL) || $subject === '' || $body === '') {
        flash('error', 'Recipient, subject, and message are required.');
    } else {
        $mailer = new Mailer();
        $ok = $mailer->sendTemplate($to, $subject, $subject, nl2br(e($body)));
        if ($ok) {
            log_activity('admin', $admin['id'], 'email_send', "Sent email to {$to}");
            flash('success', 'Email sent successfully.');
        } else {
            flash('error', 'Failed to send: ' . $mailer->getLastError());
        }
    }
    redirect('/admin/emails.php');
}

$logs = $db->query('SELECT * FROM email_logs ORDER BY id DESC LIMIT 40')->fetchAll();
$subscribers = $db->query('SELECT * FROM newsletter_subscribers ORDER BY id DESC LIMIT 30')->fetchAll();
$prefill = $_GET['to'] ?? '';

$pageTitle = 'Send Email';
$active = 'emails';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
  <div>
    <h1>Send email</h1>
    <p>Compose messages via SMTP. Configure credentials under Settings.</p>
  </div>
  <a class="btn btn-secondary btn-sm" href="/admin/settings.php">SMTP settings</a>
</div>

<div class="panel">
  <div class="panel-head"><h2>Compose</h2></div>
  <form method="post" class="stack">
    <?= csrf_field() ?>
    <div>
      <label>To</label>
      <input type="email" name="to" required value="<?= e($prefill) ?>" placeholder="client@email.com">
    </div>
    <div>
      <label>Subject</label>
      <input type="text" name="subject" required placeholder="Your Pentagon Quest itinerary">
    </div>
    <div>
      <label>Message</label>
      <textarea name="body" required style="min-height:180px"></textarea>
    </div>
    <button class="btn" type="submit">Send email</button>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>Email log</h2></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>When</th><th>To</th><th>Subject</th><th>Status</th><th>Error</th></tr></thead>
      <tbody>
      <?php if (!$logs): ?><tr><td colspan="5" class="empty">No emails sent yet.</td></tr><?php endif; ?>
      <?php foreach ($logs as $l): ?>
        <tr>
          <td><?= e($l['created_at']) ?></td>
          <td><?= e($l['recipient']) ?></td>
          <td><?= e($l['subject']) ?></td>
          <td><?= status_badge($l['status']) ?></td>
          <td class="help"><?= e($l['error_message'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Newsletter subscribers</h2></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Name</th><th>Email</th><th>Joined</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($subscribers as $s): ?>
        <tr>
          <td><?= e($s['name'] ?: '—') ?></td>
          <td><?= e($s['email']) ?></td>
          <td><?= e($s['created_at']) ?></td>
          <td><a class="btn btn-secondary btn-sm" href="/admin/emails.php?to=<?= urlencode($s['email']) ?>">Email</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
