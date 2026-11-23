<?php
/**
 * LumiCore One - Log in
 * Single-user app: the account is created by install.php.
 */
require_once __DIR__ . '/includes/functions.php';

if (current_user()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($pass, $user['password'])) {
        login_user($user);
        redirect('index.php');
    }
    $error = 'Wrong email or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Log in - <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="assets/vendor/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container py-5" style="max-width: 420px;">
  <div class="text-center mb-4">
    <span class="brand-mark" style="width:48px;height:48px;font-size:1.5rem;">L</span>
    <h1 class="h3 fw-bold mt-2 mb-0">LumiCore <span class="text-danger">One</span></h1>
    <p class="text-muted"><?= e(SITE_TAGLINE) ?></p>
  </div>
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h2 class="h5 mb-3">Log in</h2>
      <?php flash_show(); ?>
      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" name="password" required>
        </div>
        <button class="btn btn-primary w-100">Log in</button>
      </form>
      <p class="text-muted small mt-3 mb-0">No account yet? Run <code>install.php</code> once to create your admin account.</p>
    </div>
  </div>
</div>
</body>
</html>
