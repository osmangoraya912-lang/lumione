<?php
/**
 * LumiCore One - Add / edit a link
 */
$page_title = 'Link';
$active = 'links';
require_once __DIR__ . '/includes/header.php';

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
$link = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM links WHERE id = ?');
    $stmt->execute([$id]);
    $link = $stmt->fetch();
    if (!$link) {
        flash('danger', 'Link not found.');
        redirect('links.php');
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title = trim($_POST['title'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $category = $_POST['category'] ?? 'Work';

    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $errors[] = 'The URL must be a full link starting with http:// or https://';
    }
    if (!in_array($category, link_categories(), true)) {
        $errors[] = 'Please pick a category from the list.';
    }

    if (!$errors) {
        if ($link) {
            $stmt = $pdo->prepare('UPDATE links SET title = ?, url = ?, category = ? WHERE id = ?');
            $stmt->execute([$title, $url, $category, $id]);
            flash('success', 'Link updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO links (title, url, category) VALUES (?, ?, ?)');
            $stmt->execute([$title, $url, $category]);
            flash('success', 'Link added.');
        }
        redirect('links.php');
    }
    $link = ['title' => $title, 'url' => $url, 'category' => $category];
}
$link = $link ?? ['title' => '', 'url' => '', 'category' => 'Work'];
?>

<h1 class="section-title mb-1"><?= $id > 0 ? 'Edit link' : 'Add link' ?></h1>
<p class="page-intro">Save a link you open often — your store, a profile, or a tool you use every day.</p>

<div class="card" style="max-width: 640px;">
  <div class="card-body">
    <?php foreach ($errors as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label" for="title">Title</label>
        <input class="form-control" id="title" name="title" required value="<?= e($link['title']) ?>" placeholder="e.g. goraya244 Gumroad store">
      </div>
      <div class="mb-3">
        <label class="form-label" for="url">URL</label>
        <input class="form-control" id="url" type="url" name="url" required value="<?= e($link['url']) ?>" placeholder="https://...">
        <div class="form-text">The full link, starting with https://</div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="category">Category</label>
        <select class="form-select" id="category" name="category">
          <?php foreach (link_categories() as $c): ?>
            <option value="<?= e($c) ?>"<?= $link['category'] === $c ? ' selected' : '' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Store = your shops. Social = your profiles. Work = tools you use for work. Learning = study links.</div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-primary"><?= $id > 0 ? 'Save changes' : 'Add link' ?></button>
        <a class="btn btn-outline-secondary" href="links.php">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
