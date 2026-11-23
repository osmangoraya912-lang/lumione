<?php
/**
 * LumiCore One - Income
 * Read-only summary: totals by platform + totals by month,
 * all counted live from the sales table. Nothing is typed here.
 */
$page_title = 'Income';
$active = 'income';
require_once __DIR__ . '/includes/header.php';

$pdo = db();

$total_income = (float) $pdo->query('SELECT COALESCE(SUM(amount), 0) FROM sales')->fetchColumn();
$total_sales = (int) $pdo->query('SELECT COUNT(*) FROM sales')->fetchColumn();

/* Totals by platform (all 6 platforms shown, even with $0) */
$by_platform = [];
foreach (income_platforms() as $pl) {
    $by_platform[$pl] = ['count' => 0, 'total' => 0.0];
}
$stmt = $pdo->query('SELECT platform, COUNT(*) AS n, SUM(amount) AS total FROM sales GROUP BY platform');
foreach ($stmt->fetchAll() as $row) {
    $by_platform[$row['platform']] = ['count' => (int) $row['n'], 'total' => (float) $row['total']];
}

/* Totals by month, newest first */
$by_month = $pdo->query(
    "SELECT DATE_FORMAT(sale_date, '%Y-%m') AS m, DATE_FORMAT(sale_date, '%M %Y') AS label, COUNT(*) AS n, SUM(amount) AS total FROM sales GROUP BY m ORDER BY m DESC"
)->fetchAll();
?>

<h1 class="section-title mb-1">Income</h1>
<p class="page-intro">All your money in one view. Totals are counted from your real sales — nothing is guessed or typed by hand here.</p>

<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="stat-card"><div class="stat-label">Total income</div><div class="stat-value"><?= money($total_income) ?></div><div class="small text-muted">all platforms, all time</div></div>
  </div>
  <div class="col-md-6">
    <div class="stat-card"><div class="stat-label">Total sales</div><div class="stat-value"><?= $total_sales ?></div><div class="small text-muted">sales recorded</div></div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">Income by platform</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>Platform</th><th class="text-end">Sales</th><th class="text-end">Amount</th></tr></thead>
            <tbody>
              <?php foreach ($by_platform as $pl => $row): ?>
                <tr>
                  <td><?= e($pl) ?></td>
                  <td class="text-end"><?= $row['count'] ?></td>
                  <td class="text-end fw-bold"><?= money($row['total']) ?></td>
                </tr>
              <?php endforeach; ?>
              <tr class="table-light">
                <td class="fw-bold">Total</td>
                <td class="text-end fw-bold"><?= $total_sales ?></td>
                <td class="text-end fw-bold"><?= money($total_income) ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">Income by month</div>
      <div class="card-body p-0">
        <?php if (!$by_month): ?>
          <div class="empty-state"><i class="bi bi-graph-up"></i>No income yet. Add your first sale on the Sales page and it will appear here, by platform and by month.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead><tr><th>Month</th><th class="text-end">Sales</th><th class="text-end">Amount</th></tr></thead>
              <tbody>
                <?php foreach ($by_month as $row): ?>
                  <tr>
                    <td><?= e($row['label']) ?></td>
                    <td class="text-end"><?= (int) $row['n'] ?></td>
                    <td class="text-end fw-bold"><?= money($row['total']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<p class="text-muted small mt-3">Sales are entered by hand on the Sales page. There is no automatic sync with Gumroad or any other platform yet — when a sale happens, add it there and this page updates.</p>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
