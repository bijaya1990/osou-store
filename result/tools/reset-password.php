<?php
/**
 * Emergency password reset for a locked-out administrator.
 *
 * This script is deliberately disabled by default. To use it you must prove
 * that you control the server's filesystem:
 *
 *   1. In cPanel File Manager (or FTP), create an empty file called
 *      reset-allowed.txt inside the result/ folder.
 *   2. Open https://your-site.com/result/tools/reset-password.php
 *   3. Set the new password for your admin username.
 *   4. DELETE reset-allowed.txt immediately afterwards.
 *
 * Without that file the script refuses to do anything, so leaving it on the
 * server does not expose your admin account. Deleting the whole tools/ folder
 * once you no longer need it is also perfectly fine.
 */

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once NPR_BASE_PATH . '/includes/auth.php';

npr_session_start();

$unlockFile = NPR_BASE_PATH . '/reset-allowed.txt';
$unlocked = is_file($unlockFile);

$errors = array();
$done = false;

if ($unlocked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    npr_require_csrf();

    $username = npr_post('username');
    $new = isset($_POST['new_password']) ? (string) $_POST['new_password'] : '';
    $repeat = isset($_POST['repeat_password']) ? (string) $_POST['repeat_password'] : '';

    $admin = $username !== '' ? npr_fetch_one(
        'SELECT id FROM `' . npr_table('admins') . '` WHERE username = ? LIMIT 1',
        array($username)
    ) : null;

    if (!$admin) {
        $errors[] = 'No administrator account with that username exists.';
    }
    if (strlen($new) < 10) {
        $errors[] = 'The new password must be at least 10 characters long.';
    }
    if ($new !== $repeat) {
        $errors[] = 'The two passwords do not match.';
    }

    if (!$errors) {
        npr_query(
            'UPDATE `' . npr_table('admins') . '` SET password_hash = ?, is_active = 1, updated_at = ? WHERE id = ?',
            array(password_hash($new, PASSWORD_DEFAULT), npr_now(), (int) $admin['id'])
        );
        // Clear the login throttle so you can sign in straight away.
        npr_query('DELETE FROM `' . npr_table('login_attempts') . '` WHERE username = ?', array($username));
        $done = true;
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Reset Administrator Password</title>
<link rel="stylesheet" href="<?php echo e(npr_url('public/assets/css/admin.css')); ?>">
</head>
<body class="admin admin--login">
<div class="login-card">
  <h1 class="login-card__title">Reset Password</h1>
  <p class="login-card__sub">Emergency access for a locked-out administrator</p>

  <?php if (!$unlocked): ?>

    <div class="alert alert--error">
      <strong>This tool is locked.</strong>
      To unlock it, create an empty file named <code>reset-allowed.txt</code> in the
      <code>result/</code> folder using your hosting File Manager or FTP, then reload this page.
    </div>
    <p class="muted">This step proves you control the server, so the tool cannot be misused by anyone else.</p>

  <?php elseif ($done): ?>

    <div class="alert alert--success">
      <strong>Password changed.</strong> You can sign in now.
    </div>
    <div class="alert alert--info">
      <strong>Delete <code>reset-allowed.txt</code> now</strong> to lock this tool again.
    </div>
    <p><a class="btn btn--primary btn--block" href="<?php echo e(npr_url('admin/login.php')); ?>">Go to admin login</a></p>

  <?php else: ?>

    <?php if ($errors): ?>
      <div class="alert alert--error">
        <ul><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <?php echo npr_csrf_field(); ?>
      <label class="field">
        <span class="field__label">Administrator username</span>
        <input class="field__input" type="text" name="username" value="<?php echo e(npr_post('username')); ?>" required autofocus>
      </label>
      <label class="field">
        <span class="field__label">New password (at least 10 characters)</span>
        <input class="field__input" type="password" name="new_password" required>
      </label>
      <label class="field">
        <span class="field__label">Repeat new password</span>
        <input class="field__input" type="password" name="repeat_password" required>
      </label>
      <button class="btn btn--primary btn--block" type="submit">Set New Password</button>
    </form>

  <?php endif; ?>
</div>
</body>
</html>
