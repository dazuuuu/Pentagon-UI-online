<?php
require_once __DIR__ . '/../backend/bootstrap.php';
$admin = Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? 'save';
    if ($action === 'save') {
        Settings::many([
            'smtp_host' => trim($_POST['smtp_host'] ?? ''),
            'smtp_port' => trim($_POST['smtp_port'] ?? '587'),
            'smtp_encryption' => trim($_POST['smtp_encryption'] ?? 'tls'),
            'smtp_username' => trim($_POST['smtp_username'] ?? ''),
            'smtp_from_email' => trim($_POST['smtp_from_email'] ?? ''),
            'smtp_from_name' => trim($_POST['smtp_from_name'] ?? ''),
            'site_contact_email' => trim($_POST['site_contact_email'] ?? ''),
        ]);
        $pass = $_POST['smtp_password'] ?? '';
        if ($pass !== '') {
            Settings::set('smtp_password', $pass);
        }
        log_activity('admin', $admin['id'], 'settings_update', 'Updated SMTP settings');
        flash('success', 'Settings saved.');
        redirect('/admin/settings.php');
    }
    if ($action === 'test') {
        $to = trim($_POST['test_email'] ?? $admin['email']);
        $mailer = new Mailer();
        $ok = $mailer->sendTemplate(
            $to,
            'Pentagon Quest SMTP test',
            'SMTP is working',
            '<p>This is a test message from your Pentagon Quest admin panel.</p>'
        );
        if ($ok) {
            flash('success', 'Test email sent to ' . $to);
        } else {
            flash('error', 'Test failed: ' . $mailer->getLastError());
        }
        redirect('/admin/settings.php');
    }
}

$smtp = [
    'host' => Settings::get('smtp_host', config('smtp.host')),
    'port' => Settings::get('smtp_port', (string)config('smtp.port')),
    'encryption' => Settings::get('smtp_encryption', config('smtp.encryption')),
    'username' => Settings::get('smtp_username', config('smtp.username')),
    'from_email' => Settings::get('smtp_from_email', config('smtp.from_email')),
    'from_name' => Settings::get('smtp_from_name', config('smtp.from_name')),
    'site_contact_email' => Settings::get('site_contact_email', 'info@pentagonquest.com'),
];

$pageTitle = 'SMTP Settings';
$active = 'settings';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
  <div>
    <h1>SMTP settings</h1>
    <p>Configure outbound email for password resets, booking updates, and replies.</p>
  </div>
</div>

<div class="panel">
  <form method="post" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <div>
      <label>SMTP host</label>
      <input type="text" name="smtp_host" value="<?= e($smtp['host']) ?>" placeholder="smtp.gmail.com">
    </div>
    <div>
      <label>Port</label>
      <input type="number" name="smtp_port" value="<?= e($smtp['port']) ?>">
    </div>
    <div>
      <label>Encryption</label>
      <select name="smtp_encryption">
        <?php foreach (['tls','ssl','none'] as $enc): ?>
          <option value="<?= $enc ?>" <?= $smtp['encryption'] === $enc ? 'selected' : '' ?>><?= strtoupper($enc) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Username</label>
      <input type="text" name="smtp_username" value="<?= e($smtp['username']) ?>" autocomplete="off">
    </div>
    <div>
      <label>Password (leave blank to keep)</label>
      <input type="password" name="smtp_password" autocomplete="new-password">
    </div>
    <div>
      <label>From email</label>
      <input type="email" name="smtp_from_email" value="<?= e($smtp['from_email']) ?>">
    </div>
    <div>
      <label>From name</label>
      <input type="text" name="smtp_from_name" value="<?= e($smtp['from_name']) ?>">
    </div>
    <div>
      <label>Site contact email (receives form inquiries)</label>
      <input type="email" name="site_contact_email" value="<?= e($smtp['site_contact_email']) ?>">
    </div>
    <div class="full"><button class="btn" type="submit">Save settings</button></div>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>Send test email</h2></div>
  <form method="post" class="inline-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="test">
    <div style="flex:1;min-width:220px">
      <label>Test recipient</label>
      <input type="email" name="test_email" value="<?= e($admin['email']) ?>" required>
    </div>
    <button class="btn btn-secondary" type="submit">Send test</button>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
