<?php
/**
 * LumiCore One - Dashboard
 * Every number on this page is computed live from the database.
 */
$page_title = 'Dashboard';
$active = 'dashboard';
require_once __DIR__ . '/includes/header.php';

$pdo = db();
$total_products = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$total_sales = (int) $pdo->query('SELECT COUNT(*) FROM sales')->fetchColumn();
$total_income = (float) $pdo->query('SELECT COALESCE(SUM(amount), 0) FROM sales')->fetchColumn();
$tasks_today = (int) $pdo->query("SELECT COUNT(*) FROM tasks WHERE due_date = CURDATE() AND done = 0")->fetchColumn();
$posts_week = (int) $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'Planned' AND scheduled_at >= NOW() AND scheduled_at < DATE_ADD(NOW(), INTERVAL 7 DAY)")->fetchColumn();

/* Income by month, last 6 months (for the CSS bar chart) */
$months = [];
$dt = new DateTime('first day of this month');
for ($i = 5; $i >= 0; $i--) {
    $m = (clone $dt)->modify("-$i months");
    $months[$m->format('Y-m')] = ['label' => $m->format('M'), 'total' => 0.0];
}
$stmt = $pdo->query("SELECT DATE_FORMAT(sale_date, '%Y-%m') AS m, SUM(amount) AS total FROM sales WHERE sale_date >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01') GROUP BY m");
foreach ($stmt->fetchAll() as $row) {
    if (isset($months[$row['m']])) {
        $months[$row['m']]['total'] = (float) $row['total'];
    }
}
$max_month = 0.0;
foreach ($months as $m) {
    $max_month = max($max_month, $m['total']);
}

/* Recent sales (latest 5) */
$recent = $pdo->query(
    'SELECT s.*, p.name AS product_name FROM sales s LEFT JOIN products p ON p.id = s.product_id ORDER BY s.sale_date DESC, s.id DESC LIMIT 5'
)->fetchAll();

/* Tasks due today */
$due_today = $pdo->query(
    "SELECT * FROM tasks WHERE due_date = CURDATE() AND done = 0 ORDER BY FIELD(priority, 'High', 'Medium', 'Low'), id"
)->fetchAll();
?>

<h1 class="section-title mb-1">Dashboard</h1>
<p class="page-intro">Your whole business at a glance. See your products, sales, income, tasks and posts here — everything updates by itself.</p>

<div class="row g-3 mb-4">
  <div class="col-6 col-xl-2 col-lg-4">
    <div class="stat-card"><div class="stat-label"><i class="bi bi-box-seam stat-icon float-end"></i>Products</div><div class="stat-value"><?= $total_products ?></div><div class="small text-muted">in your catalog</div></div>
  </div>
  <div class="col-6 col-xl-2 col-lg-4">
    <div class="stat-card"><div class="stat-label"><i class="bi bi-cash-coin stat-icon float-end"></i>Sales</div><div class="stat-value"><?= $total_sales ?></div><div class="small text-muted">total orders</div></div>
  </div>
  <div class="col-6 col-xl-3 col-lg-4">
    <div class="stat-card"><div class="stat-label"><i class="bi bi-graph-up-arrow stat-icon float-end"></i>Total income</div><div class="stat-value"><?= money($total_income) ?></div><div class="small text-muted">all platforms, all time</div></div>
  </div>
  <div class="col-6 col-xl-2 col-lg-4">
    <div class="stat-card"><div class="stat-label"><i class="bi bi-check2-square stat-icon float-end"></i>Tasks today</div><div class="stat-value"><?= $tasks_today ?></div><div class="small text-muted">due today, not done</div></div>
  </div>
  <div class="col-6 col-xl-3 col-lg-4">
    <div class="stat-card"><div class="stat-label"><i class="bi bi-calendar-event stat-icon float-end"></i>Posts this week</div><div class="stat-value"><?= $posts_week ?></div><div class="small text-muted">planned, next 7 days</div></div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header">Income by month (last 6 months)</div>
      <div class="card-body">
        <?php if ($total_income <= 0): ?>
          <div class="empty-state"><i class="bi bi-graph-up"></i>No income yet. When your first sale comes in, add it on the Sales page and this chart will fill in.</div>
        <?php else: ?>
          <div class="bar-chart">
            <?php foreach ($months as $m): ?>
              <div class="bar-col">
                <div class="bar-value"><?= $m['total'] > 0 ? money($m['total']) : '' ?></div>
                <div class="bar" style="height: <?= $max_month > 0 ? max(2, round($m['total'] / $max_month * 100)) : 2 ?>%"></div>
                <div class="bar-label"><?= e($m['label']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header">Tasks due today</div>
      <div class="card-body">
        <?php if (!$due_today): ?>
          <div class="empty-state"><i class="bi bi-check-circle"></i>Nothing due today. Enjoy the quiet day.</div>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($due_today as $t): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><?= e($t['title']) ?></span>
                <?= priority_badge($t['priority']) ?>
              </li>
            <?php endforeach; ?>
          </ul>
          <a class="btn btn-outline-primary btn-sm mt-3" href="tasks.php">Open tasks</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Recent sales</span>
    <a class="btn btn-primary btn-sm" href="sales.php">All sales</a>
  </div>
  <div class="card-body p-0">
    <?php if (!$recent): ?>
      <div class="empty-state"><i class="bi bi-receipt"></i>No sales yet. Your first sale will show up here.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Date</th><th>Product</th><th>Platform</th><th class="text-end">Amount</th></tr></thead>
          <tbody>
            <?php foreach ($recent as $s): ?>
              <tr>
                <td><?= e($s['sale_date']) ?></td>
                <td><?= e($s['product_name'] ?? '—') ?></td>
                <td><?= e($s['platform']) ?></td>
                <td class="text-end fw-bold"><?= money($s['amount']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
