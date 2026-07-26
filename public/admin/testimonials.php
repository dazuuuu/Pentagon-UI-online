<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';
$admin = Auth::requireAdmin();
$db = Database::get();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $authorName = trim($_POST['author_name'] ?? '');
        $authorRole = trim($_POST['author_role'] ?? '');
        $quote = trim($_POST['quote'] ?? '');
        $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $avatarUrl = trim($_POST['avatar_url'] ?? '');
        $status = $_POST['status'] ?? 'published';
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $now = date('Y-m-d H:i:s');

        if ($authorName === '' || $quote === '') {
            flash('error', 'Name and quote are required.');
        } elseif ($id > 0) {
            $db->prepare('UPDATE testimonials SET author_name=?, author_role=?, quote=?, rating=?, avatar_url=?, status=?, sort_order=?, updated_at=? WHERE id=?')
                ->execute([$authorName, $authorRole, $quote, $rating, $avatarUrl ?: null, $status, $sortOrder, $now, $id]);
            log_activity('admin', $admin['id'], 'testimonial_update', "Updated testimonial #{$id}");
            flash('success', 'Testimonial updated.');
        } else {
            $db->prepare('INSERT INTO testimonials (author_name, author_role, quote, rating, avatar_url, status, sort_order, created_at) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$authorName, $authorRole, $quote, $rating, $avatarUrl ?: null, $status, $sortOrder, $now]);
            log_activity('admin', $admin['id'], 'testimonial_create', "Created testimonial from {$authorName}");
            flash('success', 'Testimonial created.');
        }
        redirect(base_path('/admin/testimonials.php'));
    }
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM testimonials WHERE id = ?')->execute([$id]);
        log_activity('admin', $admin['id'], 'testimonial_delete', "Deleted testimonial #{$id}");
        flash('success', 'Testimonial deleted.');
        redirect(base_path('/admin/testimonials.php'));
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM testimonials WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$rows = $db->query('SELECT * FROM testimonials ORDER BY sort_order ASC, id DESC')->fetchAll();
$pageTitle = 'Testimonials';
$active = 'testimonials';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
  <div>
    <h1>Testimonials</h1>
    <p>Shown on the homepage "What our travellers say" carousel.</p>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2><?= $edit ? 'Edit testimonial' : 'Add testimonial' ?></h2><?php if ($edit): ?><a href="testimonials.php">Cancel</a><?php endif; ?></div>
  <form method="post" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div>
      <label>Traveller name</label>
      <input type="text" name="author_name" required value="<?= e($edit['author_name'] ?? '') ?>">
    </div>
    <div>
      <label>Role / trip</label>
      <input type="text" name="author_role" value="<?= e($edit['author_role'] ?? '') ?>" placeholder="e.g. Coastal Getaway">
    </div>
    <div>
      <label>Rating</label>
      <select name="rating">
        <?php for ($i = 5; $i >= 1; $i--): ?>
          <option value="<?= $i ?>" <?= (int)($edit['rating'] ?? 5) === $i ? 'selected' : '' ?>><?= $i ?> star<?= $i === 1 ? '' : 's' ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div>
      <label>Avatar</label>
      <input type="text" name="avatar_url" id="avatar_url" value="<?= e($edit['avatar_url'] ?? '') ?>" placeholder="optional">
      <div class="pq-upload-row">
        <input type="file" accept="image/*" data-upload-target="avatar_url" data-upload-kind="image">
        <span class="pq-upload-status"></span>
      </div>
    </div>
    <div>
      <label>Status</label>
      <select name="status">
        <?php foreach (['published', 'draft'] as $s): ?>
          <option value="<?= $s ?>" <?= (($edit['status'] ?? 'published') === $s) ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Sort order</label>
      <input type="number" name="sort_order" value="<?= e((string)($edit['sort_order'] ?? 0)) ?>">
    </div>
    <div class="full">
      <label>Quote</label>
      <textarea name="quote" required style="min-height:100px"><?= e($edit['quote'] ?? '') ?></textarea>
    </div>
    <div class="full"><button class="btn" type="submit"><?= $edit ? 'Update' : 'Create' ?> testimonial</button></div>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>All testimonials (<?= count($rows) ?>)</h2></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Traveller</th><th>Quote</th><th>Rating</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php if (!$rows): ?><tr><td colspan="5" class="empty">No testimonials yet.</td></tr><?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><strong><?= e($r['author_name']) ?></strong><div class="help"><?= e($r['author_role'] ?: '—') ?></div></td>
          <td><?= e(mb_strimwidth($r['quote'], 0, 70, '…')) ?></td>
          <td><?= str_repeat('★', (int)$r['rating']) ?></td>
          <td><?= status_badge($r['status']) ?></td>
          <td class="actions">
            <a class="btn btn-secondary btn-sm" href="testimonials.php?edit=<?= (int)$r['id'] ?>">Edit</a>
            <form method="post" onsubmit="return confirm('Delete this testimonial?')">
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
