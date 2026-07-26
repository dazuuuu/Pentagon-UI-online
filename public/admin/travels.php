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
        $location = trim($_POST['location'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'published';
        $featured = isset($_POST['featured']) ? 1 : 0;
        $now = date('Y-m-d H:i:s');

        if ($title === '') {
            flash('error', 'Title is required.');
        } elseif ($id > 0) {
            $prev = $db->prepare('SELECT status FROM travels WHERE id = ?');
            $prev->execute([$id]);
            $prevStatus = (string)($prev->fetchColumn() ?: '');

            $db->prepare('UPDATE travels SET title=?, slug=?, location=?, country=?, summary=?, description=?, status=?, featured=?, updated_at=? WHERE id=?')
                ->execute([$title, $slug, $location, $country, $summary, $description, $status, $featured, $now, $id]);
            log_activity('admin', $admin['id'], 'travel_update', "Updated travel #{$id}");

            $alert = null;
            if ($status === 'published' && $prevStatus !== 'published') {
                $alert = notify_subscribers(
                    'New destination: ' . $title . ' · Pentagon Quest',
                    'A new destination just went live',
                    '<p>' . e($title) . ($country ? ' — ' . e($country) : '') . '</p><p>' . nl2br(e($summary)) . '</p>',
                    'View destinations',
                    app_url('/destinations/')
                );
            }
            if ($alert) {
                flash('success', 'Travel updated. Subscriber alert: ' . (int)$alert['sent'] . ' sent'
                    . ($alert['failed'] ? ', ' . (int)$alert['failed'] . ' failed' : '') . '.');
            } else {
                flash('success', 'Travel updated.');
            }
        } else {
            $db->prepare('INSERT INTO travels (title, slug, location, country, summary, description, status, featured, created_at) VALUES (?,?,?,?,?,?,?,?,?)')
                ->execute([$title, $slug, $location, $country, $summary, $description, $status, $featured, $now]);
            log_activity('admin', $admin['id'], 'travel_create', "Created travel {$title}");

            if ($status === 'published') {
                $alert = notify_subscribers(
                    'New destination: ' . $title . ' · Pentagon Quest',
                    'A new destination just went live',
                    '<p>' . e($title) . ($country ? ' — ' . e($country) : '') . '</p><p>' . nl2br(e($summary)) . '</p>',
                    'View destinations',
                    app_url('/destinations/')
                );
                flash('success', 'Travel created. Subscriber alert: ' . (int)$alert['sent'] . ' sent'
                    . ($alert['failed'] ? ', ' . (int)$alert['failed'] . ' failed' : '') . '.');
            } else {
                flash('success', 'Travel created.');
            }
        }
        redirect(base_path('/admin/travels.php'));
    }
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM travels WHERE id = ?')->execute([$id]);
        log_activity('admin', $admin['id'], 'travel_delete', "Deleted travel #{$id}");
        flash('success', 'Travel deleted.');
        redirect(base_path('/admin/travels.php'));
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM travels WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$rows = $db->query('SELECT t.*, (SELECT COUNT(*) FROM tours WHERE travel_id = t.id) AS tour_count FROM travels t ORDER BY t.id DESC')->fetchAll();
$pageTitle = 'Travels';
$active = 'travels';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
  <div>
    <h1>Travels</h1>
    <p>Destinations and travel experiences featured on the site.</p>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2><?= $edit ? 'Edit travel' : 'Add travel' ?></h2><?php if ($edit): ?><a href="travels.php">Cancel</a><?php endif; ?></div>
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
      <input type="text" name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="auto from title">
    </div>
    <div>
      <label>Status</label>
      <select name="status">
        <?php foreach (['published','draft','inactive'] as $s): ?>
          <option value="<?= $s ?>" <?= (($edit['status'] ?? 'published') === $s) ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Location</label>
      <input type="text" name="location" value="<?= e($edit['location'] ?? '') ?>">
    </div>
    <div>
      <label>Country</label>
      <input type="text" name="country" value="<?= e($edit['country'] ?? '') ?>">
    </div>
    <div class="full">
      <label>Summary</label>
      <textarea name="summary"><?= e($edit['summary'] ?? '') ?></textarea>
    </div>
    <div class="full">
      <label>Description</label>
      <textarea name="description" style="min-height:160px"><?= e($edit['description'] ?? '') ?></textarea>
    </div>
    <div class="full">
      <label><input type="checkbox" name="featured" value="1" <?= !empty($edit['featured']) ? 'checked' : '' ?>> Featured</label>
    </div>
    <div class="full"><button class="btn" type="submit"><?= $edit ? 'Update' : 'Create' ?> travel</button></div>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>All travels (<?= count($rows) ?>)</h2></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Title</th><th>Location</th><th>Tours</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><strong><?= e($r['title']) ?></strong><div class="help"><?= e($r['slug']) ?></div></td>
          <td><?= e(trim(($r['location'] ?? '') . ', ' . ($r['country'] ?? ''), ', ')) ?></td>
          <td><?= (int)$r['tour_count'] ?></td>
          <td><?= status_badge($r['status']) ?><?= $r['featured'] ? ' · featured' : '' ?></td>
          <td class="actions">
            <a class="btn btn-secondary btn-sm" href="travels.php?edit=<?= (int)$r['id'] ?>">Edit</a>
            <form method="post" onsubmit="return confirm('Delete this travel?')">
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
