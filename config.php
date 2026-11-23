<?php
/**
 * LumiCore One - Configuration
 * After running install.php this file is filled in automatically.
 * You can also edit the values below by hand.
 */
define('SITE_NAME', 'LumiCore One');
define('SITE_TAGLINE', 'Your whole business in one place');
define('CURRENCY', '$ ');

// Database - fill these in (install.php does it for you)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'lumione');
define('DB_USER', 'root');
define('DB_PASS', '');

define('UPLOAD_DIR', __DIR__ . '/uploads');
define('UPLOAD_URL', 'uploads');

session_start();
