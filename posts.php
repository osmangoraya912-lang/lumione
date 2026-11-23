<?php
/**
 * LumiCore One - Posts (content calendar)
 * List, toggle Posted/Planned, delete. Add/edit lives in post-form.php.
 */
$page_title = 'Posts';
$active = 'posts';
require_once __DIR__ . '/includes/header.php';

$pdo = db();

/* Toggle status / delete (POST + CSRF) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'toggle') {
        $stmt = $pdo->prepare("UPDATE posts SET status = IF(status = 'Posted', 'Planned', 'Posted') WHERE id = ?");
        $stmt->execute([$id]);
        flash('success', 'Post status updated.');
        redirect('posts.php');
    }
    if (($_POST['action'] ?? '') === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Post deleted.');
        redirect('posts.php');
    }
}

$platform_filter = $_GET['platform'] ?? '';
$sql = 'SELECT * FROM posts WHERE 1 = 1';
$params = [];
if (in_array($platform_filter, post_platforms(), true)) {
    $sql .= ' AND platform = ?';
    $params[] = $platform_filter;
}
$sql .= ' ORDER BY scheduled_at ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
  <h1 class="section-title mb-0">Posts</h1>
  <a class="btn btn-primary" href="post-form.php"><i class="bi bi-plus-lg"></i> Plan a post</a>
</div>
<p class="page-intro">Your content calendar. Plan a post, set its date and time, and mark it Posted when it goes live.</p>

<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label" for="platform">Platform</label>
        <select class="form-select" id="platform" name="platform">
          <option value="">All platforms</option>
          <?php foreach (post_platforms() as $pl): ?>
            <option value="<?= e($pl) ?>"<?= $platform_filter === $pl ? ' selected' : '' ?>><?= e($pl) ?></option>
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
    <?php if (!$posts): ?>
      <div class="empty-state"><i class="bi bi-calendar-event"></i>No posts planned yet. Click "Plan a post" to add your first one.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Scheduled</th><th>Post</th><th>Platform</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            <?php foreach ($posts as $p): ?>
              <tr>
                <td><?= e(date('d M Y, H:i', strtotime($p['scheduled_at']))) ?></td>
                <td>
                  <?php if ($p['link'] !== ''): ?>
                    <a href="<?= e($p['link']) ?>" target="_blank" rel="noopener"><?= e($p['title']) ?> <i class="bi bi-box-arrow-up-right small"></i></a>
                  <?php else: ?>
                    <?= e($p['title']) ?>
                  <?php endif; ?>
                </td>
                <td><?= e($p['platform']) ?></td>
                <td><?= status_badge($p['status']) ?></td>
                <td class="text-end text-nowrap">
                  <form method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                    <button class="btn btn-outline-success btn-sm"><?= $p['status'] === 'Posted' ? 'Mark Planned' : 'Mark Posted' ?></button>
                  </form>
                  <a class="btn btn-outline-primary btn-sm" href="post-form.php?id=<?= (int) $p['id'] ?>">Edit</a>
                  <form method="post" class="d-inline" onsubmit="return confirm('Delete this post?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
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
