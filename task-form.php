<?php
/**
 * LumiCore One - Add / edit a task
 */
$page_title = 'Task';
$active = 'tasks';
require_once __DIR__ . '/includes/header.php';

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
$task = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
    $stmt->execute([$id]);
    $task = $stmt->fetch();
    if (!$task) {
        flash('danger', 'Task not found.');
        redirect('tasks.php');
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title = trim($_POST['title'] ?? '');
    $due_date = $_POST['due_date'] ?? today();
    $priority = $_POST['priority'] ?? 'Medium';

    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $due_date)) {
        $errors[] = 'Due date is not a valid date.';
    }
    if (!in_array($priority, priorities(), true)) {
        $errors[] = 'Please pick a priority from the list.';
    }

    if (!$errors) {
        if ($task) {
            $stmt = $pdo->prepare('UPDATE tasks SET title = ?, due_date = ?, priority = ? WHERE id = ?');
            $stmt->execute([$title, $due_date, $priority, $id]);
            flash('success', 'Task updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO tasks (title, due_date, priority) VALUES (?, ?, ?)');
            $stmt->execute([$title, $due_date, $priority]);
            flash('success', 'Task added.');
        }
        redirect('tasks.php');
    }
    $task = ['title' => $title, 'due_date' => $due_date, 'priority' => $priority];
}
$task = $task ?? ['title' => '', 'due_date' => today(), 'priority' => 'Medium'];
?>

<h1 class="section-title mb-1"><?= $id > 0 ? 'Edit task' : 'Add task' ?></h1>
<p class="page-intro">One small job at a time. Write what needs doing, when it is due, and how important it is.</p>

<div class="card" style="max-width: 640px;">
  <div class="card-body">
    <?php foreach ($errors as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label" for="title">Task</label>
        <input class="form-control" id="title" name="title" required value="<?= e($task['title']) ?>" placeholder="e.g. 70 images Dreamstime upload karo">
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="due_date">Due date</label>
          <input class="form-control" id="due_date" type="date" name="due_date" required value="<?= e($task['due_date']) ?>">
          <div class="form-text">The day this task should be finished.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="priority">Priority</label>
          <select class="form-select" id="priority" name="priority">
            <?php foreach (priorities() as $p): ?>
              <option value="<?= e($p) ?>"<?= $task['priority'] === $p ? ' selected' : '' ?>><?= e($p) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">High = do it first today.</div>
        </div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-primary"><?= $id > 0 ? 'Save changes' : 'Add task' ?></button>
        <a class="btn btn-outline-secondary" href="tasks.php">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
