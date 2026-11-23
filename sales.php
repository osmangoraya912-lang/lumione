<?php
/**
 * LumiCore One - Sales
 * List, filter and delete sales. Add/edit lives in sale-form.php.
 */
$page_title = 'Sales';
$active = 'sales';
require_once __DIR__ . '/includes/header.php';

$pdo = db();

/* Delete (POST + CSRF) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $stmt = $pdo->prepare('DELETE FROM sales WHERE id = ?');
    $stmt->execute([(int) ($_POST['id'] ?? 0)]);
    flash('success', 'Sale deleted.');
    redirect('sales.php');
}

$platform_filter = $_GET['platform'] ?? '';
$sql = 'SELECT s.*, p.name AS product_name FROM sales s LEFT JOIN products p ON p.id = s.product_id WHERE 1 = 1';
$params = [];
if (in_array($platform_filter, income_platforms(), true)) {
    $sql .= ' AND s.platform = ?';
    $params[] = $platform_filter;
}
$sql .= ' ORDER BY s.sale_date DESC, s.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll();
$total = 0.0;
foreach ($sales as $s) {
    $total += (float) $s['amount'];
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
  <h1 class="section-title mb-0">Sales</h1>
  <a class="btn btn-primary" href="sale-form.php"><i class="bi bi-plus-lg"></i> Add sale</a>
</div>
<p class="page-intro">Every sale you make, recorded here. Add a sale when it happens and your income totals update automatically.</p>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="stat-card"><div class="stat-label">Total sales</div><div class="stat-value"><?= count($sales) ?></div></div>
  </div>
  <div class="col-md-4">
    <div class="stat-card"><div class="stat-label">Total amount</div><div class="stat-value"><?= money($total) ?></div></div>
  </div>
  <div class="col-md-4">
    <div class="card h-100"><div class="card-body d-flex align-items-center">
      <form method="get" class="d-flex gap-2 w-100">
        <select class="form-select" name="platform">
          <option value="">All platforms</option>
          <?php foreach (income_platforms() as $pl): ?>
            <option value="<?= e($pl) ?>"<?= $platform_filter === $pl ? ' selected' : '' ?>><?= e($pl) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-primary">Filter</button>
      </form>
    </div></div>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <?php if (!$sales): ?>
      <div class="empty-state"><i class="bi bi-receipt"></i>No sales yet. Your first sale will show up here — add it with "Add sale" when it happens.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Date</th><th>Product</th><th>Platform</th><th>Buyer note</th><th class="text-end">Amount</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            <?php foreach ($sales as $s): ?>
              <tr>
                <td><?= e($s['sale_date']) ?></td>
                <td><?= e($s['product_name'] ?? '—') ?></td>
                <td><?= e($s['platform']) ?></td>
                <td><?= e($s['buyer_note']) ?></td>
                <td class="text-end fw-bold"><?= money($s['amount']) ?></td>
                <td class="text-end text-nowrap">
                  <a class="btn btn-outline-primary btn-sm" href="sale-form.php?id=<?= (int) $s['id'] ?>">Edit</a>
                  <form method="post" class="d-inline" onsubmit="return confirm('Delete this sale?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
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
