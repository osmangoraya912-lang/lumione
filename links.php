<?php
/**
 * LumiCore One - Links (link hub)
 * List, filter and delete links. Add/edit lives in link-form.php.
 */
$page_title = 'Links';
$active = 'links';
require_once __DIR__ . '/includes/header.php';

$pdo = db();

/* Delete (POST + CSRF) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $stmt = $pdo->prepare('DELETE FROM links WHERE id = ?');
    $stmt->execute([(int) ($_POST['id'] ?? 0)]);
    flash('success', 'Link deleted.');
    redirect('links.php');
}

$category_filter = $_GET['category'] ?? '';
$sql = 'SELECT * FROM links WHERE 1 = 1';
$params = [];
if (in_array($category_filter, link_categories(), true)) {
    $sql .= ' AND category = ?';
    $params[] = $category_filter;
}
$sql .= ' ORDER BY category, title';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$links = $stmt->fetchAll();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
  <h1 class="section-title mb-0">Links</h1>
  <a class="btn btn-primary" href="link-form.php"><i class="bi bi-plus-lg"></i> Add link</a>
</div>
<p class="page-intro">All your important links in one place. Your stores, profiles and tools — one click to open any of them.</p>

<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label" for="category">Category</label>
        <select class="form-select" id="category" name="category">
          <option value="">All categories</option>
          <?php foreach (link_categories() as $c): ?>
            <option value="<?= e($c) ?>"<?= $category_filter === $c ? ' selected' : '' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-grid">
        <button class="btn btn-primary">Filter</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <?php if (!$links): ?>
      <div class="empty-state"><i class="bi bi-link-45deg"></i>No links yet. Click "Add link" to save your first one.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Title</th><th>Link</th><th>Category</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            <?php foreach ($links as $l): ?>
              <tr>
                <td><?= e($l['title']) ?></td>
                <td><a href="<?= e($l['url']) ?>" target="_blank" rel="noopener"><?= e($l['url']) ?> <i class="bi bi-box-arrow-up-right small"></i></a></td>
                <td><span class="badge bg-secondary"><?= e($l['category']) ?></span></td>
                <td class="text-end text-nowrap">
                  <a class="btn btn-outline-primary btn-sm" href="link-form.php?id=<?= (int) $l['id'] ?>">Edit</a>
                  <form method="post" class="d-inline" onsubmit="return confirm('Delete this link?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                    <button class="btn btn-outline-danger btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
