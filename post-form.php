<?php
/**
 * LumiCore One - Plan / edit a post
 */
$page_title = 'Post';
$active = 'posts';
require_once __DIR__ . '/includes/header.php';

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
$post = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) {
        flash('danger', 'Post not found.');
        redirect('posts.php');
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title = trim($_POST['title'] ?? '');
    $platform = $_POST['platform'] ?? 'Threads';
    $scheduled_at = trim($_POST['scheduled_at'] ?? '');
    $status = $_POST['status'] ?? 'Planned';
    $link = trim($_POST['link'] ?? '');

    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if (!in_array($platform, post_platforms(), true)) {
        $errors[] = 'Please pick a platform from the list.';
    }
    $ts = strtotime($scheduled_at);
    if ($ts === false) {
        $errors[] = 'Scheduled date and time is not valid.';
    } else {
        $scheduled_at = date('Y-m-d H:i:s', $ts);
    }
    if (!in_array($status, ['Planned', 'Posted'], true)) {
        $errors[] = 'Please pick a status from the list.';
    }
    if ($link !== '' && !filter_var($link, FILTER_VALIDATE_URL)) {
        $errors[] = 'The link must be a full URL starting with http:// or https://';
    }

    if (!$errors) {
        if ($post) {
            $stmt = $pdo->prepare('UPDATE posts SET title = ?, platform = ?, scheduled_at = ?, status = ?, link = ? WHERE id = ?');
            $stmt->execute([$title, $platform, $scheduled_at, $status, $link, $id]);
            flash('success', 'Post updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO posts (title, platform, scheduled_at, status, link) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$title, $platform, $scheduled_at, $status, $link]);
            flash('success', 'Post planned.');
        }
        redirect('posts.php');
    }
    $post = ['title' => $title, 'platform' => $platform, 'scheduled_at' => $scheduled_at, 'status' => $status, 'link' => $link];
}
if ($post) {
    $ts = strtotime($post['scheduled_at']);
    $post['scheduled_at'] = $ts !== false ? date('Y-m-d\TH:i', $ts) : '';
} else {
    $post = ['title' => '', 'platform' => 'Threads', 'scheduled_at' => date('Y-m-d\TH:i', strtotime('+1 hour')), 'status' => 'Planned', 'link' => ''];
}
?>

<h1 class="section-title mb-1"><?= $id > 0 ? 'Edit post' : 'Plan a post' ?></h1>
<p class="page-intro">Plan what to post, where, and when. When the post goes live, mark it Posted.</p>

<div class="card" style="max-width: 640px;">
  <div class="card-body">
    <?php foreach ($errors as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label" for="title">Post title</label>
        <input class="form-control" id="title" name="title" required value="<?= e($post['title']) ?>" placeholder="e.g. M-B01 Messaging Trio - Threads + Bluesky">
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="platform">Platform</label>
          <select class="form-select" id="platform" name="platform">
            <?php foreach (post_platforms() as $pl): ?>
              <option value="<?= e($pl) ?>"<?= $post['platform'] === $pl ? ' selected' : '' ?>><?= e($pl) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="status">Status</label>
          <select class="form-select" id="status" name="status">
            <option value="Planned"<?= $post['status'] === 'Planned' ? ' selected' : '' ?>>Planned</option>
            <option value="Posted"<?= $post['status'] === 'Posted' ? ' selected' : '' ?>>Posted</option>
          </select>
          <div class="form-text">Planned = not posted yet.</div>
        </div>
      </div>
      <div class="mb-3 mt-3">
        <label class="form-label" for="scheduled_at">Scheduled date &amp; time</label>
        <input class="form-control" id="scheduled_at" type="datetime-local" name="scheduled_at" required value="<?= e($post['scheduled_at']) ?>">
        <div class="form-text">When should this post go live?</div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="link">Link</label>
        <input class="form-control" id="link" type="url" name="link" value="<?= e($post['link']) ?>" placeholder="https://...">
        <div class="form-text">The product or page link this post points to. Leave empty if there is none yet.</div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-primary"><?= $id > 0 ? 'Save changes' : 'Plan post' ?></button>
        <a class="btn btn-outline-secondary" href="posts.php">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
