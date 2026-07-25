<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';
$admin = Auth::requireAdmin();
$db = Database::get();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($title);
        $travelId = (int)($_POST['travel_id'] ?? 0) ?: null;
        $description = trim($_POST['description'] ?? '');
        $durationDays = (int)($_POST['duration_days'] ?? 0) ?: null;
        $durationLabel = trim($_POST['duration_label'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $currency = trim($_POST['currency'] ?? 'KES') ?: 'KES';
        $location = trim($_POST['location'] ?? '');
        $inclusions = trim($_POST['inclusions'] ?? '');
        $exclusions = trim($_POST['exclusions'] ?? '');
        $itinerary = trim($_POST['itinerary'] ?? '');
        $status = $_POST['status'] ?? 'published';
        $featured = isset($_POST['featured']) ? 1 : 0;
        $now = date('Y-m-d H:i:s');

        if ($title === '') {
            flash('error', 'Title is required.');
        } elseif ($id > 0) {
            $db->prepare('UPDATE tours SET travel_id=?, title=?, slug=?, description=?, duration_days=?, duration_label=?, price=?, currency=?, location=?, inclusions=?, exclusions=?, itinerary=?, status=?, featured=?, updated_at=? WHERE id=?')
                ->execute([$travelId, $title, $slug, $description, $durationDays, $durationLabel, $price, $currency, $location, $inclusions, $exclusions, $itinerary, $status, $featured, $now, $id]);
            log_activity('admin', $admin['id'], 'tour_update', "Updated tour #{$id}");
            flash('success', 'Tour updated.');
        } else {
            $db->prepare('INSERT INTO tours (travel_id, title, slug, description, duration_days, duration_label, price, currency, location, inclusions, exclusions, itinerary, status, featured, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$travelId, $title, $slug, $description, $durationDays, $durationLabel, $price, $currency, $location, $inclusions, $exclusions, $itinerary, $status, $featured, $now]);
            log_activity('admin', $admin['id'], 'tour_create', "Created tour {$title}");
            flash('success', 'Tour created.');

            if ($status === 'published') {
                notify_subscribers(
                    'New tour: ' . $title . ' · Pentagon Quest',
                    'A new tour just went live',
                    '<p>' . e($title) . ($location ? ' — ' . e($location) : '') . '</p><p>' . nl2br(e(mb_strimwidth($description, 0, 300, '…'))) . '</p>',
                    'View tours',
                    app_url('/packages/')
                );
            }
        }
        redirect('/admin/tours.php');
    }
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM tours WHERE id = ?')->execute([$id]);
        log_activity('admin', $admin['id'], 'tour_delete', "Deleted tour #{$id}");
        flash('success', 'Tour deleted.');
        redirect('/admin/tours.php');
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM tours WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$travels = $db->query('SELECT id, title FROM travels ORDER BY title ASC')->fetchAll();
$rows = $db->query('SELECT o.*, t.title AS travel_title FROM tours o LEFT JOIN travels t ON t.id = o.travel_id ORDER BY o.id DESC')->fetchAll();
$pageTitle = 'Tours';
$active = 'tours';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
  <div>
    <h1>Tours & packages</h1>
    <p>Manage priced packages clients can book and track.</p>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2><?= $edit ? 'Edit tour' : 'Add tour' ?></h2><?php if ($edit): ?><a href="/admin/tours.php">Cancel</a><?php endif; ?></div>
  <form method="post" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div class="full">
      <label>Title</label>
      <input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>">
    </div>
    <div>
      <label>Slug</label>
      <input type="text" name="slug" value="<?= e($edit['slug'] ?? '') ?>">
    </div>
    <div>
      <label>Travel / destination</label>
      <select name="travel_id">
        <option value="">— None —</option>
        <?php foreach ($travels as $t): ?>
          <option value="<?= (int)$t['id'] ?>" <?= ((int)($edit['travel_id'] ?? 0) === (int)$t['id']) ? 'selected' : '' ?>><?= e($t['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Duration (days)</label>
      <input type="number" name="duration_days" min="1" value="<?= e((string)($edit['duration_days'] ?? '')) ?>">
    </div>
    <div>
      <label>Duration label</label>
      <input type="text" name="duration_label" value="<?= e($edit['duration_label'] ?? '') ?>" placeholder="5 Days / 4 Nights">
    </div>
    <div>
      <label>Price</label>
      <input type="number" step="0.01" name="price" value="<?= e((string)($edit['price'] ?? '0')) ?>">
    </div>
    <div>
      <label>Currency</label>
      <input type="text" name="currency" value="<?= e($edit['currency'] ?? 'KES') ?>">
    </div>
    <div>
      <label>Location</label>
      <input type="text" name="location" value="<?= e($edit['location'] ?? '') ?>">
    </div>
    <div>
      <label>Status</label>
      <select name="status">
        <?php foreach (['published','draft','inactive'] as $s): ?>
          <option value="<?= $s ?>" <?= (($edit['status'] ?? 'published') === $s) ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="full">
      <label>Description</label>
      <textarea name="description"><?= e($edit['description'] ?? '') ?></textarea>
    </div>
    <div>
      <label>Inclusions</label>
      <textarea name="inclusions"><?= e($edit['inclusions'] ?? '') ?></textarea>
    </div>
    <div>
      <label>Exclusions</label>
      <textarea name="exclusions"><?= e($edit['exclusions'] ?? '') ?></textarea>
    </div>
    <div class="full">
      <label>Itinerary</label>
      <textarea name="itinerary" style="min-height:140px"><?= e($edit['itinerary'] ?? '') ?></textarea>
    </div>
    <div class="full">
      <label><input type="checkbox" name="featured" value="1" <?= !empty($edit['featured']) ? 'checked' : '' ?>> Featured</label>
    </div>
    <div class="full"><button class="btn" type="submit"><?= $edit ? 'Update' : 'Create' ?> tour</button></div>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>All tours (<?= count($rows) ?>)</h2></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tour</th><th>Travel</th><th>Price</th><th>Duration</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><strong><?= e($r['title']) ?></strong><div class="help"><?= e($r['location']) ?></div></td>
          <td><?= e($r['travel_title'] ?: '—') ?></td>
          <td><?= e($r['currency']) ?> <?= number_format((float)$r['price'], 0) ?></td>
          <td><?= e($r['duration_label'] ?: (($r['duration_days'] ? $r['duration_days'] . ' days' : '—'))) ?></td>
          <td><?= status_badge($r['status']) ?></td>
          <td class="actions">
            <a class="btn btn-secondary btn-sm" href="/admin/tours.php?edit=<?= (int)$r['id'] ?>">Edit</a>
            <form method="post" onsubmit="return confirm('Delete this tour?')">
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
