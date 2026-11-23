<?php
/**
 * LumiCore One - Notes
 * Simple notes: add, edit, delete. Add/edit lives in note-form.php.
 */
$page_title = 'Notes';
$active = 'notes';
require_once __DIR__ . '/includes/header.php';

$pdo = db();

/* Delete (POST + CSRF) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $stmt = $pdo->prepare('DELETE FROM notes WHERE id = ?');
    $stmt->execute([(int) ($_POST['id'] ?? 0)]);
    flash('success', 'Note deleted.');
    redirect('notes.php');
}

$notes = $pdo->query('SELECT * FROM notes ORDER BY updated_at DESC, id DESC')->fetchAll();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
  <h1 class="section-title mb-0">Notes</h1>
  <a class="btn btn-primary" href="note-form.php"><i class="bi bi-plus-lg"></i> Add note</a>
</div>
<p class="page-intro">Quick notes for yourself. Rules, ideas and reminders — anything you do not want to forget.</p>

<?php if (!$notes): ?>
  <div class="card"><div class="card-body">
    <div class="empty-state"><i class="bi bi-journal-text"></i>No notes yet. Click "Add note" to write your first one.</div>
  </div></div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($notes as $n): ?>
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h2 class="h5"><?= e($n['title']) ?></h2>
            <p class="mb-2" style="white-space: pre-wrap;"><?= e($n['body']) ?></p>
            <p class="text-muted small mb-3">Updated <?= e(date('d M Y, H:i', strtotime($n['updated_at']))) ?></p>
            <a class="btn btn-outline-primary btn-sm" href="note-form.php?id=<?= (int) $n['id'] ?>">Edit</a>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this note?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
              <button class="btn btn-outline-danger btn-sm">Delete</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
