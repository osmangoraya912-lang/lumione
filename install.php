<?php
/**
 * LumiCore One - Browser installer
 * 1. Fill in your MySQL details + choose an admin account.
 * 2. It creates the database, imports schema.sql and creates the admin.
 * 3. DELETE this file after a successful install.
 */
$errors = [];
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['db_host'] ?? '127.0.0.1');
    $port = trim($_POST['db_port'] ?? '3306');
    $name = trim($_POST['db_name'] ?? 'lumione');
    $user = trim($_POST['db_user'] ?? 'root');
    $pass = $_POST['db_pass'] ?? '';
    $admin_name = trim($_POST['admin_name'] ?? '');
    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_pass = $_POST['admin_pass'] ?? '';

    if ($admin_name === '' || $admin_email === '' || strlen($admin_pass) < 6) {
        $errors[] = 'Please fill in the admin name, a valid email and a password of at least 6 characters.';
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Admin email is not valid.';
    } else {
        try {
            $pdo = new PDO(
                "mysql:host=$host;port=$port;charset=utf8mb4",
                $user, $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$name`");
            $schema = file_get_contents(__DIR__ . '/schema.sql');
            if ($schema === false) {
                throw new RuntimeException('schema.sql not found.');
            }
            $pdo->exec($schema);
            $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE name = VALUES(name), password = VALUES(password)'
            );
            $stmt->execute([$admin_name, $admin_email, $hash]);

            // Write the config file
            $config = "<?php\n"
                . "define('SITE_NAME', 'LumiCore One');\n"
                . "define('SITE_TAGLINE', 'Your whole business in one place');\n"
                . "define('CURRENCY', '\$ ');\n"
                . "define('DB_HOST', '" . addslashes($host) . "');\n"
                . "define('DB_PORT', '" . addslashes($port) . "');\n"
                . "define('DB_NAME', '" . addslashes($name) . "');\n"
                . "define('DB_USER', '" . addslashes($user) . "');\n"
                . "define('DB_PASS', '" . addslashes($pass) . "');\n"
                . "define('UPLOAD_DIR', __DIR__ . '/uploads');\n"
                . "define('UPLOAD_URL', 'uploads');\n"
                . "session_start();\n";
            file_put_contents(__DIR__ . '/config.php', $config);
            $done = true;
        } catch (Throwable $ex) {
            $errors[] = 'Install failed: ' . $ex->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Install LumiCore One</title>
<link rel="stylesheet" href="assets/vendor/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container py-5" style="max-width: 560px;">
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h1 class="h4 mb-1">Install LumiCore One</h1>
      <p class="text-muted">Enter your MySQL details. The installer creates the database, tables, your real products, links, tasks and notes, and your admin account.</p>
      <?php if ($done): ?>
        <div class="alert alert-success">Installed successfully! <strong>Delete install.php now</strong>, then
        <a href="login.php">log in</a> to open your dashboard.</div>
      <?php else: ?>
        <?php foreach ($errors as $err): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
        <form method="post">
          <h2 class="h6 mt-3">Database</h2>
          <div class="row g-2">
            <div class="col-8"><label class="form-label">Host</label><input class="form-control" name="db_host" value="127.0.0.1" required></div>
            <div class="col-4"><label class="form-label">Port</label><input class="form-control" name="db_port" value="3306" required></div>
            <div class="col-6"><label class="form-label">Database name</label><input class="form-control" name="db_name" value="lumione" required></div>
            <div class="col-6"><label class="form-label">Username</label><input class="form-control" name="db_user" value="root" required></div>
            <div class="col-12"><label class="form-label">Password</label><input type="password" class="form-control" name="db_pass" placeholder="Leave empty if none"></div>
          </div>
          <h2 class="h6 mt-4">Admin account</h2>
          <div class="mb-2"><label class="form-label">Name</label><input class="form-control" name="admin_name" required></div>
          <div class="mb-2"><label class="form-label">Email</label><input type="email" class="form-control" name="admin_email" required></div>
          <div class="mb-3"><label class="form-label">Password (min 6 characters)</label><input type="password" class="form-control" name="admin_pass" required></div>
          <button class="btn btn-primary w-100">Install Now</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <p class="text-center text-muted small mt-3">PHP 8.1+ and MySQL 8 / MariaDB required. Works on XAMPP and InfinityFree.</p>
</div>
</body>
</html>
