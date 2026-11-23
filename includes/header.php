<?php
/**
 * LumiCore One - Shared layout header + app dock (sidebar) + top bar
 * Every app page sets $page_title and $active before including this file.
 * The header enforces login, so no app page is reachable without signing in.
 */
require_once __DIR__ . '/functions.php';
$user = require_login();
$active = $active ?? 'dashboard';
$nav = [
    'dashboard' => ['index.php', 'Dashboard', 'bi-speedometer2'],
    'products'  => ['products.php', 'Products', 'bi-box-seam'],
    'sales'     => ['sales.php', 'Sales', 'bi-cash-coin'],
    'income'    => ['income.php', 'Income', 'bi-graph-up-arrow'],
    'tasks'     => ['tasks.php', 'Tasks', 'bi-check2-square'],
    'posts'     => ['posts.php', 'Posts', 'bi-calendar-event'],
    'notes'     => ['notes.php', 'Notes', 'bi-journal-text'],
    'links'     => ['links.php', 'Links', 'bi-link-45deg'],
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($page_title ?? 'Dashboard') . ' - ' . SITE_NAME) ?></title>
<link rel="stylesheet" href="assets/vendor/bootstrap.min.css">
<link rel="stylesheet" href="assets/vendor/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<aside class="app-sidebar">
  <a class="sidebar-brand" href="index.php"><span class="brand-mark">L</span> LumiCore <span class="text-danger">One</span></a>
  <nav class="sidebar-nav">
    <?php foreach ($nav as $key => [$href, $label, $icon]): ?>
      <a class="sidebar-link<?= $active === $key ? ' active' : '' ?>" href="<?= e($href) ?>">
        <i class="bi <?= e($icon) ?>"></i> <span><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
    <a class="sidebar-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> <span>Log out</span></a>
  </nav>
  <div class="sidebar-user">
    <i class="bi bi-person-circle"></i> <?= e($user['name']) ?>
    <div class="small text-muted"><?= e($user['email']) ?></div>
  </div>
</aside>
<main class="app-main">
  <div class="topbar">
    <div class="topbar-app"><span class="brand-mark">L</span> <strong>LumiCore One</strong> <span class="text-muted d-none d-md-inline">— <?= e($page_title ?? 'Dashboard') ?></span></div>
    <div class="topbar-clock text-end">
      <span id="live-clock" class="clock">--:--:--</span>
      <span id="live-date" class="date d-none d-sm-inline"></span>
    </div>
  </div>
  <div class="container-fluid py-4">
    <?php flash_show(); ?>
