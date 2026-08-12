<?php
/**
 * Single entry point for configuration and shared includes.
 * Every PHP file in this system starts by requiring this file.
 */

if (defined('NPR_BOOTSTRAPPED')) {
    return;
}
define('NPR_BOOTSTRAPPED', true);

$nprRoot = dirname(__DIR__);

if (!is_file($nprRoot . '/config.php')) {
    http_response_code(503);
    $installer = is_file($nprRoot . '/install.php');
    echo '<!doctype html><meta charset="utf-8"><title>Setup required</title>';
    echo '<div style="font:16px/1.6 system-ui,sans-serif;max-width:640px;margin:12vh auto;padding:0 20px">';
    echo '<h1 style="font-size:22px">Result system not configured</h1>';
    echo '<p><code>config.php</code> was not found. Copy <code>config.sample.php</code> to <code>config.php</code> and fill in your database details';
    if ($installer) {
        echo ', or run the <a href="install.php">installer</a>';
    }
    echo '.</p></div>';
    exit;
}

require_once $nprRoot . '/config.php';

if (defined('NPR_DEBUG') && NPR_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}

if (!defined('NPR_BASE_PATH')) {
    define('NPR_BASE_PATH', $nprRoot);
}
if (!defined('NPR_UPLOAD_PATH')) {
    define('NPR_UPLOAD_PATH', NPR_BASE_PATH . '/uploads');
}

date_default_timezone_set(defined('NPR_TIMEZONE') ? NPR_TIMEZONE : 'Asia/Kolkata');

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

npr_security_headers();
