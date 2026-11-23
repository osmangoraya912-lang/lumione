<?php
/**
 * LumiCore One - Products
 * List, search, filter and delete products. Add/edit lives in product-form.php.
 */
$page_title = 'Products';
$active = 'products';
require_once __DIR__ . '/includes/header.php';

$pdo = db();

/* Delete (POST + CSRF) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([(int) ($_POST['id'] ?? 0)]);
    flash('success', 'Product deleted.');
    redirect('products.php');
}

$q = trim($_GET['q'] ?? '');
$store_filter = $_GET['store'] ?? '';
$sql = 'SELECT * FROM products WHERE 1 = 1';
$params = [];
if ($q !== '') {
    $sql .= ' AND (name LIKE ? OR category LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if (in_array($store_filter, stores(), true)) {
    $sql .= ' AND store = ?';
    $params[] = $store_filter;
}
$sql .= ' ORDER BY name';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
  <h1 class="section-title mb-0">Products</h1>
  <a class="btn btn-primary" href="product-form.php"><i class="bi bi-plus-lg"></i> Add product</a>
</div>
<p class="page-intro">All your products in one place. Add a product once and it shows up in Sales too. Click a product name to open its real store link.</p>

<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-6">
        <label class="form-label" for="q">Search</label>
        <input class="form-control" id="q" type="search" name="q" placeholder="Search by name or category..." value="<?= e($q) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="store">Store</label>
        <select class="form-select" id="store" name="store">
          <option value="">All stores</option>
          <?php foreach (stores() as $s): ?>
            <option value="<?= e($s) ?>"<?= $store_filter === $s ? ' selected' : '' ?>><?= e($s) ?></option>
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
    <?php if (!$products): ?>
      <div class="empty-state"><i class="bi bi-box-seam"></i>No products yet. Click "Add product" to add your first one.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Product</th><th>Store</th><th>Category</th><th class="text-end">Price</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            <?php foreach ($products as $p): ?>
              <tr>
                <td>
                  <?php if ($p['product_link'] !== ''): ?>
                    <a href="<?= e($p['product_link']) ?>" target="_blank" rel="noopener"><?= e($p['name']) ?> <i class="bi bi-box-arrow-up-right small"></i></a>
                  <?php else: ?>
                    <?= e($p['name']) ?>
                  <?php endif; ?>
                </td>
                <td><?= e($p['store']) ?></td>
                <td><?= e($p['category']) ?></td>
                <td class="text-end fw-bold"><?= money($p['price']) ?></td>
                <td><?= status_badge($p['status']) ?></td>
                <td class="text-end text-nowrap">
                  <a class="btn btn-outline-primary btn-sm" href="product-form.php?id=<?= (int) $p['id'] ?>">Edit</a>
                  <form method="post" class="d-inline" onsubmit="return confirm('Delete this product? Sales linked to it will keep their record.');">
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
  <div class="card-footer text-muted small"><?= count($products) ?> product(s) shown</div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
