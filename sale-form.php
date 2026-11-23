<?php
/**
 * LumiCore One - Add / edit a sale
 */
$page_title = 'Sale';
$active = 'sales';
require_once __DIR__ . '/includes/header.php';

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
$sale = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM sales WHERE id = ?');
    $stmt->execute([$id]);
    $sale = $stmt->fetch();
    if (!$sale) {
        flash('danger', 'Sale not found.');
        redirect('sales.php');
    }
}

$products = $pdo->query('SELECT id, name, price FROM products ORDER BY name')->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $product_id = (int) ($_POST['product_id'] ?? 0);
    $platform = $_POST['platform'] ?? 'Other';
    $amount = (float) ($_POST['amount'] ?? 0);
    $sale_date = $_POST['sale_date'] ?? today();
    $buyer_note = trim($_POST['buyer_note'] ?? '');

    if ($amount <= 0) {
        $errors[] = 'Amount must be more than 0.';
    }
    if (!in_array($platform, income_platforms(), true)) {
        $errors[] = 'Please pick a platform from the list.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sale_date)) {
        $errors[] = 'Sale date is not a valid date.';
    }
    if ($product_id > 0) {
        $check = $pdo->prepare('SELECT COUNT(*) FROM products WHERE id = ?');
        $check->execute([$product_id]);
        if ((int) $check->fetchColumn() === 0) {
            $errors[] = 'That product does not exist.';
        }
    }

    if (!$errors) {
        $pid = $product_id > 0 ? $product_id : null;
        if ($sale) {
            $stmt = $pdo->prepare('UPDATE sales SET product_id = ?, platform = ?, amount = ?, sale_date = ?, buyer_note = ? WHERE id = ?');
            $stmt->execute([$pid, $platform, $amount, $sale_date, $buyer_note, $id]);
            flash('success', 'Sale updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO sales (product_id, platform, amount, sale_date, buyer_note) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$pid, $platform, $amount, $sale_date, $buyer_note]);
            flash('success', 'Sale added. Your income totals are updated.');
        }
        redirect('sales.php');
    }
    $sale = ['product_id' => $product_id, 'platform' => $platform, 'amount' => $amount, 'sale_date' => $sale_date, 'buyer_note' => $buyer_note];
}
$sale = $sale ?? ['product_id' => 0, 'platform' => 'Gumroad goraya244', 'amount' => '', 'sale_date' => today(), 'buyer_note' => ''];
?>

<h1 class="section-title mb-1"><?= $id > 0 ? 'Edit sale' : 'Add sale' ?></h1>
<p class="page-intro">Record a real sale here — the day it happened, the platform it came from, and the amount you received.</p>

<div class="card" style="max-width: 640px;">
  <div class="card-body">
    <?php foreach ($errors as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label" for="product_id">Product</label>
        <select class="form-select" id="product_id" name="product_id">
          <option value="0">— No product (e.g. Fiverr work or stock images) —</option>
          <?php foreach ($products as $p): ?>
            <option value="<?= (int) $p['id'] ?>"<?= (int) $sale['product_id'] === (int) $p['id'] ? ' selected' : '' ?>><?= e($p['name']) ?> (<?= money($p['price']) ?>)</option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Pick the product that was sold, or leave it blank for work that is not a product.</div>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="platform">Platform</label>
          <select class="form-select" id="platform" name="platform">
            <?php foreach (income_platforms() as $pl): ?>
              <option value="<?= e($pl) ?>"<?= $sale['platform'] === $pl ? ' selected' : '' ?>><?= e($pl) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="amount">Amount (USD)</label>
          <input class="form-control" id="amount" type="number" step="0.01" min="0.01" name="amount" required value="<?= e($sale['amount']) ?>" placeholder="24">
          <div class="form-text">The money you actually received, in US dollars.</div>
        </div>
      </div>
      <div class="row g-3 mt-1">
        <div class="col-md-6">
          <label class="form-label" for="sale_date">Sale date</label>
          <input class="form-control" id="sale_date" type="date" name="sale_date" required value="<?= e($sale['sale_date']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="buyer_note">Buyer note (optional)</label>
          <input class="form-control" id="buyer_note" name="buyer_note" value="<?= e($sale['buyer_note']) ?>" placeholder="e.g. buyer name">
          <div class="form-text">Optional — a short note about this sale.</div>
        </div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-primary"><?= $id > 0 ? 'Save changes' : 'Add sale' ?></button>
        <a class="btn btn-outline-secondary" href="sales.php">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
