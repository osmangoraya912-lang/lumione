<?php
/**
 * LumiCore One - Add / edit a product
 */
$page_title = 'Product';
$active = 'products';
require_once __DIR__ . '/includes/header.php';

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
$product = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        flash('danger', 'Product not found.');
        redirect('products.php');
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $store = $_POST['store'] ?? 'Other';
    $price = (float) ($_POST['price'] ?? 0);
    $link = trim($_POST['product_link'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = $_POST['status'] ?? 'Live';

    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($price < 0) {
        $errors[] = 'Price cannot be negative.';
    }
    if (!in_array($store, stores(), true)) {
        $errors[] = 'Please pick a store from the list.';
    }
    if (!in_array($status, ['Live', 'Draft'], true)) {
        $errors[] = 'Please pick a status from the list.';
    }
    if ($link !== '' && !filter_var($link, FILTER_VALIDATE_URL)) {
        $errors[] = 'The product link must be a full URL starting with http:// or https://';
    }

    if (!$errors) {
        if ($product) {
            $stmt = $pdo->prepare('UPDATE products SET name = ?, store = ?, price = ?, product_link = ?, category = ?, status = ? WHERE id = ?');
            $stmt->execute([$name, $store, $price, $link, $category, $status, $id]);
            flash('success', 'Product updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (name, store, price, product_link, category, status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $store, $price, $link, $category, $status]);
            flash('success', 'Product added.');
        }
        redirect('products.php');
    }
    $product = [
        'name' => $name, 'store' => $store, 'price' => $price,
        'product_link' => $link, 'category' => $category, 'status' => $status,
    ];
}
$product = $product ?? ['name' => '', 'store' => 'goraya244', 'price' => '', 'product_link' => '', 'category' => '', 'status' => 'Live'];
?>

<h1 class="section-title mb-1"><?= $id > 0 ? 'Edit product' : 'Add product' ?></h1>
<p class="page-intro">Fill in the real details of your product. The price is in US dollars, and the link is the real page where buyers get it.</p>

<div class="card" style="max-width: 640px;">
  <div class="card-body">
    <?php foreach ($errors as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label" for="name">Product name</label>
        <input class="form-control" id="name" name="name" required value="<?= e($product['name']) ?>" placeholder="e.g. Invoice Pro">
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="store">Store</label>
          <select class="form-select" id="store" name="store">
            <?php foreach (stores() as $s): ?>
              <option value="<?= e($s) ?>"<?= $product['store'] === $s ? ' selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="price">Price (USD)</label>
          <input class="form-control" id="price" type="number" step="0.01" min="0" name="price" required value="<?= e($product['price']) ?>" placeholder="24">
          <div class="form-text">Price in US dollars, e.g. 24 means $24.00.</div>
        </div>
      </div>
      <div class="mb-3 mt-3">
        <label class="form-label" for="product_link">Product link</label>
        <input class="form-control" id="product_link" type="url" name="product_link" value="<?= e($product['product_link']) ?>" placeholder="https://goraya244.gumroad.com/l/...">
        <div class="form-text">The real Gumroad or WPBay link for this product. Leave empty if there is no link yet.</div>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="category">Category</label>
          <input class="form-control" id="category" name="category" value="<?= e($product['category']) ?>" placeholder="e.g. Business Tools">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="status">Status</label>
          <select class="form-select" id="status" name="status">
            <option value="Live"<?= $product['status'] === 'Live' ? ' selected' : '' ?>>Live</option>
            <option value="Draft"<?= $product['status'] === 'Draft' ? ' selected' : '' ?>>Draft</option>
          </select>
          <div class="form-text">Draft means not published yet.</div>
        </div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-primary"><?= $id > 0 ? 'Save changes' : 'Add product' ?></button>
        <a class="btn btn-outline-secondary" href="products.php">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
