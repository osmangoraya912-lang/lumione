<?php
/**
 * LumiCore One - Tasks
 * To-do list: add, toggle done, delete. Add/edit lives in task-form.php.
 */
$page_title = 'Tasks';
$active = 'tasks';
require_once __DIR__ . '/includes/header.php';

$pdo = db();

/* Toggle done / delete (POST + CSRF) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'toggle') {
        $stmt = $pdo->prepare('UPDATE tasks SET done = 1 - done WHERE id = ?');
        $stmt->execute([$id]);
        redirect('tasks.php' . (($_GET['f'] ?? '') !== '' ? '?f=' . urlencode($_GET['f']) : ''));
    }
    if (($_POST['action'] ?? '') === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Task deleted.');
        redirect('tasks.php');
    }
}

$filter = $_GET['f'] ?? 'all';
$sql = 'SELECT * FROM tasks WHERE 1 = 1';
$params = [];
if ($filter === 'today') {
    $sql .= ' AND due_date = CURDATE() AND done = 0';
} elseif ($filter === 'done') {
    $sql .= ' AND done = 1';
} elseif ($filter === 'open') {
    $sql .= ' AND done = 0';
}
$sql .= " ORDER BY done ASC, due_date ASC, FIELD(priority, 'High', 'Medium', 'Low'), id";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();
$open_count = (int) $pdo->query('SELECT COUNT(*) FROM tasks WHERE done = 0')->fetchColumn();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
  <h1 class="section-title mb-0">Tasks</h1>
  <a class="btn btn-primary" href="task-form.php"><i class="bi bi-plus-lg"></i> Add task</a>
</div>
<p class="page-intro">Your daily work list. Add a task, set its due date, and tick it done when finished. Overdue tasks turn red.</p>

<div class="btn-group mb-3" role="group" aria-label="Filter tasks">
  <a class="btn btn-outline-primary<?= $filter === 'all' ? ' active' : '' ?>" href="tasks.php?f=all">All</a>
  <a class="btn btn-outline-primary<?= $filter === 'today' ? ' active' : '' ?>" href="tasks.php?f=today">Today</a>
  <a class="btn btn-outline-primary<?= $filter === 'open' ? ' active' : '' ?>" href="tasks.php?f=open">Open (<?= $open_count ?>)</a>
  <a class="btn btn-outline-primary<?= $filter === 'done' ? ' active' : '' ?>" href="tasks.php?f=done">Done</a>
</div>

<div class="card">
  <div class="card-body p-0">
    <?php if (!$tasks): ?>
      <div class="empty-state"><i class="bi bi-check2-square"></i>No tasks here. Click "Add task" to add your first one.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th style="width:44px;"></th><th>Task</th><th>Due date</th><th>Priority</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            <?php foreach ($tasks as $t): ?>
              <?php $overdue = !$t['done'] && $t['due_date'] < today(); ?>
              <tr class="<?= $overdue ? 'overdue' : '' ?>">
                <td>
                  <form method="post" action="tasks.php<?= $filter !== 'all' ? '?f=' . urlencode($filter) : '' ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                    <button class="btn btn-sm <?= $t['done'] ? 'btn-success' : 'btn-outline-secondary' ?>" title="<?= $t['done'] ? 'Mark as not done' : 'Mark as done' ?>">
                      <i class="bi <?= $t['done'] ? 'bi-check-lg' : 'bi-square' ?>"></i>
                    </button>
                  </form>
                </td>
                <td style="<?= $t['done'] ? 'text-decoration: line-through; color: var(--lc-muted);' : '' ?>"><?= e($t['title']) ?><?= $overdue ? ' <span class="badge bg-danger">Overdue</span>' : '' ?></td>
                <td><?= e($t['due_date']) ?></td>
                <td><?= priority_badge($t['priority']) ?></td>
                <td class="text-end text-nowrap">
                  <a class="btn btn-outline-primary btn-sm" href="task-form.php?id=<?= (int) $t['id'] ?>">Edit</a>
                  <form method="post" class="d-inline" onsubmit="return confirm('Delete this task?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
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
