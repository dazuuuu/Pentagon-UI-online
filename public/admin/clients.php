<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';
$admin = Auth::requireAdmin();
$db = Database::get();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $active = isset($_POST['is_active']) ? 1 : 0;
        $now = date('Y-m-d H:i:s');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Valid name and email are required.');
        } elseif ($id > 0) {
            if ($password !== '') {
                $db->prepare('UPDATE clients SET name=?, email=?, phone=?, password_hash=?, is_active=?, updated_at=? WHERE id=?')
                    ->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $active, $now, $id]);
            } else {
                $db->prepare('UPDATE clients SET name=?, email=?, phone=?, is_active=?, updated_at=? WHERE id=?')
                    ->execute([$name, $email, $phone, $active, $now, $id]);
            }
            flash('success', 'Client updated.');
        } else {
            if (strlen($password) < 8) {
                flash('error', 'Password must be at least 8 characters for new clients.');
                redirect('/admin/clients.php');
            }
            $db->prepare('INSERT INTO clients (name, email, phone, password_hash, is_active, created_at) VALUES (?,?,?,?,?,?)')
                ->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $active, $now]);
            log_activity('admin', $admin['id'], 'client_create', "Created client {$email}");
            flash('success', 'Client created. They can sign in at /client/login.php');
        }
        redirect('/admin/clients.php');
    }
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM clients WHERE id = ?')->execute([$id]);
        flash('success', 'Client deleted.');
        redirect('/admin/clients.php');
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM clients WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$rows = $db->query('SELECT c.*, (SELECT COUNT(*) FROM bookings WHERE client_id = c.id) AS booking_count FROM clients c ORDER BY c.id DESC')->fetchAll();
$pageTitle = 'Clients';
$active = 'clients';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
  <div>
    <h1>Clients</h1>
    <p>Accounts used to track tours on the client portal.</p>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2><?= $edit ? 'Edit client' : 'Add client' ?></h2><?php if ($edit): ?><a href="/admin/clients.php">Cancel</a><?php endif; ?></div>
  <form method="post" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div>
      <label>Name</label>
      <input type="text" name="name" required value="<?= e($edit['name'] ?? '') ?>">
    </div>
    <div>
      <label>Email</label>
      <input type="email" name="email" required value="<?= e($edit['email'] ?? '') ?>">
    </div>
    <div>
      <label>Phone</label>
      <input type="text" name="phone" value="<?= e($edit['phone'] ?? '') ?>">
    </div>
    <div>
      <label>Password <?= $edit ? '(leave blank to keep)' : '' ?></label>
      <input type="password" name="password" <?= $edit ? '' : 'required minlength="8"' ?>>
    </div>
    <div class="full">
      <label><input type="checkbox" name="is_active" value="1" <?= (($edit['is_active'] ?? 1) ? 'checked' : '') ?>> Active</label>
    </div>
    <div class="full"><button class="btn" type="submit"><?= $edit ? 'Update' : 'Create' ?> client</button></div>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>All clients (<?= count($rows) ?>)</h2></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Name</th><th>Contact</th><th>Bookings</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><strong><?= e($r['name']) ?></strong></td>
          <td><?= e($r['email']) ?><div class="help"><?= e($r['phone'] ?: '') ?></div></td>
          <td><?= (int)$r['booking_count'] ?></td>
          <td><?= status_badge($r['is_active'] ? 'active' : 'inactive') ?></td>
          <td class="actions">
            <a class="btn btn-secondary btn-sm" href="/admin/clients.php?edit=<?= (int)$r['id'] ?>">Edit</a>
            <form method="post" onsubmit="return confirm('Delete client and their bookings?')">
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
