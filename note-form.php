<?php
/**
 * LumiCore One - Add / edit a note
 */
$page_title = 'Note';
$active = 'notes';
require_once __DIR__ . '/includes/header.php';

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
$note = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ?');
    $stmt->execute([$id]);
    $note = $stmt->fetch();
    if (!$note) {
        flash('danger', 'Note not found.');
        redirect('notes.php');
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');

    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if ($body === '') {
        $errors[] = 'Write something in the note.';
    }

    if (!$errors) {
        if ($note) {
            $stmt = $pdo->prepare('UPDATE notes SET title = ?, body = ? WHERE id = ?');
            $stmt->execute([$title, $body, $id]);
            flash('success', 'Note updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO notes (title, body) VALUES (?, ?)');
            $stmt->execute([$title, $body]);
            flash('success', 'Note added.');
        }
        redirect('notes.php');
    }
    $note = ['title' => $title, 'body' => $body];
}
$note = $note ?? ['title' => '', 'body' => ''];
?>

<h1 class="section-title mb-1"><?= $id > 0 ? 'Edit note' : 'Add note' ?></h1>
<p class="page-intro">Write it down so you do not forget it. A rule, an idea, a reminder — anything.</p>

<div class="card" style="max-width: 640px;">
  <div class="card-body">
    <?php foreach ($errors as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label" for="title">Title</label>
        <input class="form-control" id="title" name="title" required value="<?= e($note['title']) ?>" placeholder="e.g. Discount codes">
      </div>
      <div class="mb-3">
        <label class="form-label" for="body">Note</label>
        <textarea class="form-control" id="body" name="body" rows="6" required placeholder="Write your note here..."><?= e($note['body']) ?></textarea>
        <div class="form-text">Plain text. Line breaks are kept as you write them.</div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-primary"><?= $id > 0 ? 'Save changes' : 'Add note' ?></button>
        <a class="btn btn-outline-secondary" href="notes.php">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
