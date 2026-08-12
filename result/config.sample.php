<?php
/**
 * NaukriPatra Result Management System — configuration sample.
 *
 * Copy this file to config.php and fill in your own values, or let install.php
 * write it for you. Never commit the real config.php to a public repository.
 */

// --- Database -------------------------------------------------------------
define('NPR_DB_HOST', 'localhost');
define('NPR_DB_NAME', 'your_database');
define('NPR_DB_USER', 'your_database_user');
define('NPR_DB_PASS', 'your_database_password');
define('NPR_DB_CHARSET', 'utf8mb4');

// Prefix for this system's tables. Keeps them separate from WordPress tables.
define('NPR_TABLE_PREFIX', 'np_res_');

// --- URLs -----------------------------------------------------------------
// Public base URL of the result system, no trailing slash.
define('NPR_BASE_URL', 'https://naukripatra.in/result');

// --- Paths ----------------------------------------------------------------
define('NPR_BASE_PATH', __DIR__);
define('NPR_UPLOAD_PATH', NPR_BASE_PATH . '/uploads');

// --- Uploads --------------------------------------------------------------
define('NPR_MAX_UPLOAD_BYTES', 8 * 1024 * 1024); // 8 MB spreadsheet limit
define('NPR_MAX_LOGO_BYTES', 1024 * 1024);       // 1 MB logo limit

// --- Session / security ---------------------------------------------------
define('NPR_SESSION_NAME', 'npr_admin_session');
// Idle timeout for admin sessions, in seconds.
define('NPR_SESSION_IDLE_TIMEOUT', 3600);
// Login throttling: max failed attempts per IP inside the window.
define('NPR_LOGIN_MAX_ATTEMPTS', 8);
define('NPR_LOGIN_WINDOW_SECONDS', 900);

// Site display name, used in page titles.
define('NPR_SITE_NAME', 'NaukriPatra Results');

// Set to false once you are live to hide detailed PHP errors.
define('NPR_DEBUG', false);

// Marks the installation as complete. install.php refuses to run when true.
define('NPR_INSTALLED', true);
