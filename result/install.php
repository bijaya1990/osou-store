<?php
/**
 * Guided installer for shared hosting.
 *
 * Collects database credentials and the first administrator account, writes
 * config.php, creates the tables and then asks you to delete this file.
 * It refuses to run once an administrator account exists.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$root = __DIR__;
$configPath = $root . '/config.php';
$schemaPath = $root . '/schema.sql';

$errors = array();
$done = false;
$configWritten = false;
$configContents = '';

/* --- Environment checks -------------------------------------------------- */

$checks = array(
    'PHP 7.0 or newer'            => version_compare(PHP_VERSION, '7.0.0', '>='),
    'PDO MySQL extension'         => extension_loaded('pdo_mysql'),
    'mbstring or iconv (optional)' => function_exists('mb_convert_encoding') || function_exists('iconv'),
    'zip extension (.xlsx import)' => class_exists('ZipArchive'),
    'XML extension (.xlsx import)' => class_exists('XMLReader'),
    'uploads/ is writable'        => is_writable($root . '/uploads'),
    'Base folder is writable (to write config.php)' => is_writable($root),
);
$fatal = !$checks['PHP 7.0 or newer'] || !$checks['PDO MySQL extension'];

/* --- Already installed? -------------------------------------------------- */

$alreadyInstalled = false;
if (is_file($configPath)) {
    require_once $configPath;
    if (defined('NPR_DB_HOST')) {
        try {
            $probe = new PDO(
                sprintf('mysql:host=%s;dbname=%s;charset=%s', NPR_DB_HOST, NPR_DB_NAME, NPR_DB_CHARSET),
                NPR_DB_USER,
                NPR_DB_PASS,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            $count = $probe->query('SELECT COUNT(*) FROM `' . NPR_TABLE_PREFIX . 'admins`')->fetchColumn();
            $alreadyInstalled = ((int) $count > 0);
        } catch (Exception $e) {
            $alreadyInstalled = false; // config exists but tables/credentials are not usable yet
        }
    }
}

/* --- Handle the form ----------------------------------------------------- */

if (!$alreadyInstalled && !$fatal && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $post = function ($key, $default = '') {
        return isset($_POST[$key]) && !is_array($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
    };

    $dbHost = $post('db_host', 'localhost');
    $dbName = $post('db_name');
    $dbUser = $post('db_user');
    $dbPass = isset($_POST['db_pass']) ? (string) $_POST['db_pass'] : '';
    $prefix = $post('prefix', 'np_res_');
    $baseUrl = rtrim($post('base_url'), '/');
    $siteName = $post('site_name', 'NaukriPatra Results');
    $adminUser = $post('admin_user');
    $adminPass = isset($_POST['admin_pass']) ? (string) $_POST['admin_pass'] : '';
    $adminPass2 = isset($_POST['admin_pass2']) ? (string) $_POST['admin_pass2'] : '';
    $adminEmail = $post('admin_email');

    if ($dbName === '' || $dbUser === '') {
        $errors[] = 'Database name and database user are required.';
    }
    if (!preg_match('/^[a-z0-9_]{1,20}$/i', $prefix)) {
        $errors[] = 'The table prefix may contain only letters, numbers and underscores.';
    }
    if ($baseUrl === '' || !preg_match('~^https?://~i', $baseUrl)) {
        $errors[] = 'The result system URL must start with http:// or https://.';
    }
    if (!preg_match('/^[A-Za-z0-9_.\-]{3,64}$/', $adminUser)) {
        $errors[] = 'The admin username must be 3–64 characters (letters, numbers, dot, dash, underscore).';
    }
    if (strlen($adminPass) < 10) {
        $errors[] = 'The admin password must be at least 10 characters long.';
    }
    if ($adminPass !== $adminPass2) {
        $errors[] = 'The two admin passwords do not match.';
    }
    if ($adminEmail !== '' && !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'The admin email address is not valid.';
    }

    $pdo = null;
    if (!$errors) {
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbName),
                $dbUser,
                $dbPass,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch (PDOException $e) {
            $errors[] = 'Could not connect to the database: ' . $e->getMessage();
        }
    }

    if (!$errors && $pdo) {
        $sql = @file_get_contents($schemaPath);
        if ($sql === false) {
            $errors[] = 'schema.sql could not be read. Re-upload it next to install.php.';
        } else {
            $sql = str_replace('{{PREFIX}}', $prefix, $sql);
            $sql = preg_replace('/^\s*--.*$/m', '', $sql); // drop comment lines
            try {
                foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
                    if ($statement === '') {
                        continue;
                    }
                    $pdo->exec($statement);
                }
            } catch (PDOException $e) {
                $errors[] = 'The tables could not be created: ' . $e->getMessage();
            }
        }
    }

    if (!$errors && $pdo) {
        try {
            $exists = $pdo->prepare('SELECT id FROM `' . $prefix . 'admins` WHERE username = ? LIMIT 1');
            $exists->execute(array($adminUser));
            if ($exists->fetchColumn() === false) {
                $insert = $pdo->prepare(
                    'INSERT INTO `' . $prefix . 'admins` (username, password_hash, full_name, email, is_active, created_at, updated_at)
                     VALUES (?,?,?,?,1,NOW(),NOW())'
                );
                $insert->execute(array($adminUser, password_hash($adminPass, PASSWORD_DEFAULT), $adminUser, $adminEmail !== '' ? $adminEmail : null));
            }
        } catch (PDOException $e) {
            $errors[] = 'The administrator account could not be created: ' . $e->getMessage();
        }
    }

    if (!$errors) {
        $configContents = npr_build_config(array(
            'db_host'   => $dbHost,
            'db_name'   => $dbName,
            'db_user'   => $dbUser,
            'db_pass'   => $dbPass,
            'prefix'    => $prefix,
            'base_url'  => $baseUrl,
            'site_name' => $siteName,
        ));

        $configWritten = (@file_put_contents($configPath, $configContents, LOCK_EX) !== false);
        if ($configWritten) {
            @chmod($configPath, 0640);
        }
        $done = true;
    }
}

/**
 * Render config.php from the collected values.
 */
function npr_build_config(array $values)
{
    $q = function ($value) {
        return "'" . str_replace(array('\\', "'"), array('\\\\', "\\'"), (string) $value) . "'";
    };

    return "<?php\n"
        . "/**\n * NaukriPatra Result Management System — configuration.\n"
        . " * Generated by install.php on " . date('d M Y H:i') . ".\n */\n\n"
        . "define('NPR_DB_HOST', " . $q($values['db_host']) . ");\n"
        . "define('NPR_DB_NAME', " . $q($values['db_name']) . ");\n"
        . "define('NPR_DB_USER', " . $q($values['db_user']) . ");\n"
        . "define('NPR_DB_PASS', " . $q($values['db_pass']) . ");\n"
        . "define('NPR_DB_CHARSET', 'utf8mb4');\n\n"
        . "define('NPR_TABLE_PREFIX', " . $q($values['prefix']) . ");\n\n"
        . "define('NPR_BASE_URL', " . $q($values['base_url']) . ");\n"
        . "define('NPR_BASE_PATH', __DIR__);\n"
        . "define('NPR_UPLOAD_PATH', NPR_BASE_PATH . '/uploads');\n\n"
        . "define('NPR_MAX_UPLOAD_BYTES', 8 * 1024 * 1024);\n"
        . "define('NPR_MAX_LOGO_BYTES', 1024 * 1024);\n\n"
        . "define('NPR_SESSION_NAME', 'npr_admin_session');\n"
        . "define('NPR_SESSION_IDLE_TIMEOUT', 3600);\n"
        . "define('NPR_LOGIN_MAX_ATTEMPTS', 8);\n"
        . "define('NPR_LOGIN_WINDOW_SECONDS', 900);\n\n"
        . "define('NPR_SITE_NAME', " . $q($values['site_name']) . ");\n"
        . "define('NPR_TIMEZONE', 'Asia/Kolkata');\n"
        . "define('NPR_DEBUG', false);\n"
        . "define('NPR_INSTALLED', true);\n";
}

$guess = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
    . '://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'example.com')
    . rtrim(str_replace('/install.php', '', isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/result'), '/');

$h = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Install — Result Management System</title>
<link rel="stylesheet" href="public/assets/css/admin.css">
</head>
<body class="admin">
<header class="admin-bar"><div class="admin-bar__inner"><span class="admin-bar__brand">Result Management — Installation</span></div></header>
<main class="admin-main">

<?php if ($alreadyInstalled): ?>

  <div class="alert alert--success"><strong>Already installed.</strong> An administrator account already exists.</div>
  <section class="card">
    <h2 class="card__title">Next step</h2>
    <p><strong>Delete install.php from the server now.</strong> Leaving it in place is a security risk.</p>
    <p><a class="btn btn--primary" href="admin/login.php">Go to admin login</a></p>
  </section>

<?php elseif ($done): ?>

  <div class="alert alert--success"><strong>Installation complete.</strong> The database tables were created and your administrator account is ready.</div>

  <?php if (!$configWritten): ?>
    <div class="alert alert--error">
      <strong>config.php could not be written automatically.</strong>
      Create a file called <code>config.php</code> in this folder and paste the contents below into it.
    </div>
    <section class="card">
      <h2 class="card__title">config.php</h2>
      <textarea class="field__input" rows="26" readonly onclick="this.select()"><?php echo $h($configContents); ?></textarea>
    </section>
  <?php endif; ?>

  <section class="card">
    <h2 class="card__title">Finish up</h2>
    <ol>
      <li><strong>Delete install.php</strong> from the server.</li>
      <li>Sign in at <a href="admin/login.php">admin/login.php</a> with the account you just created.</li>
      <li>Add your first result, upload the Excel/CSV file, then publish.</li>
      <li>Install the WordPress plugin from <code>wordpress-plugin/</code> and add the ticker to your homepage.</li>
    </ol>
    <p class="muted">The database starts empty: no results, no students. The homepage ticker shows
       “LIVE RESULTS → Coming Soon” until you publish your first result.</p>
    <p><a class="btn btn--primary" href="admin/login.php">Go to admin login</a></p>
  </section>

<?php else: ?>

  <section class="card">
    <h2 class="card__title">Server check</h2>
    <table class="data-table">
      <tbody>
      <?php foreach ($checks as $label => $ok): ?>
        <tr>
          <td data-label="Requirement"><?php echo $h($label); ?></td>
          <td data-label="Status"><span class="pill pill--<?php echo $ok ? 'live' : 'draft'; ?>"><?php echo $ok ? 'OK' : 'Missing'; ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php if ($fatal): ?>
      <div class="alert alert--error" style="margin-top:14px">
        PHP 7.0+ and the PDO MySQL extension are required. Please ask your host to enable them.
      </div>
    <?php elseif (!$checks['zip extension (.xlsx import)'] || !$checks['XML extension (.xlsx import)']): ?>
      <div class="alert alert--info" style="margin-top:14px">
        .xlsx import needs the zip and XML extensions. Without them you can still import CSV files.
      </div>
    <?php endif; ?>
  </section>

  <?php if ($errors): ?>
    <div class="alert alert--error">
      <strong>Please correct the following:</strong>
      <ul><?php foreach ($errors as $error): ?><li><?php echo $h($error); ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <?php if (!$fatal): ?>
  <form class="card" method="post" autocomplete="off">
    <fieldset class="fieldset">
      <legend>Database</legend>
      <div class="field-row">
        <label class="field"><span class="field__label">Database host</span>
          <input class="field__input" type="text" name="db_host" value="<?php echo $h(isset($_POST['db_host']) ? $_POST['db_host'] : 'localhost'); ?>" required></label>
        <label class="field"><span class="field__label">Database name</span>
          <input class="field__input" type="text" name="db_name" value="<?php echo $h(isset($_POST['db_name']) ? $_POST['db_name'] : ''); ?>" required></label>
      </div>
      <div class="field-row">
        <label class="field"><span class="field__label">Database user</span>
          <input class="field__input" type="text" name="db_user" value="<?php echo $h(isset($_POST['db_user']) ? $_POST['db_user'] : ''); ?>" required></label>
        <label class="field"><span class="field__label">Database password</span>
          <input class="field__input" type="password" name="db_pass"></label>
      </div>
      <label class="field field--narrow"><span class="field__label">Table prefix</span>
        <input class="field__input" type="text" name="prefix" value="<?php echo $h(isset($_POST['prefix']) ? $_POST['prefix'] : 'np_res_'); ?>" required>
        <span class="field__help">Keeps these tables separate from your WordPress tables. You may use the same database as WordPress.</span>
      </label>
    </fieldset>

    <fieldset class="fieldset">
      <legend>Site</legend>
      <label class="field"><span class="field__label">Result system URL</span>
        <input class="field__input" type="url" name="base_url" value="<?php echo $h(isset($_POST['base_url']) ? $_POST['base_url'] : $guess); ?>" required>
        <span class="field__help">The public address of this folder, without a trailing slash.</span>
      </label>
      <label class="field"><span class="field__label">Site name</span>
        <input class="field__input" type="text" name="site_name" value="<?php echo $h(isset($_POST['site_name']) ? $_POST['site_name'] : 'NaukriPatra Results'); ?>"></label>
    </fieldset>

    <fieldset class="fieldset">
      <legend>Administrator account</legend>
      <div class="field-row">
        <label class="field"><span class="field__label">Username</span>
          <input class="field__input" type="text" name="admin_user" value="<?php echo $h(isset($_POST['admin_user']) ? $_POST['admin_user'] : ''); ?>" required></label>
        <label class="field"><span class="field__label">Email (optional)</span>
          <input class="field__input" type="email" name="admin_email" value="<?php echo $h(isset($_POST['admin_email']) ? $_POST['admin_email'] : ''); ?>"></label>
      </div>
      <div class="field-row">
        <label class="field"><span class="field__label">Password (min. 10 characters)</span>
          <input class="field__input" type="password" name="admin_pass" required></label>
        <label class="field"><span class="field__label">Repeat password</span>
          <input class="field__input" type="password" name="admin_pass2" required></label>
      </div>
    </fieldset>

    <div class="form-actions">
      <button class="btn btn--primary" type="submit">Install</button>
    </div>
  </form>
  <?php endif; ?>

<?php endif; ?>
</main>
</body>
</html>
