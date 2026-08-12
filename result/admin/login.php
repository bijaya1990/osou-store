<?php
/**
 * Admin login.
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once NPR_BASE_PATH . '/includes/auth.php';

npr_session_start();

if (npr_current_admin()) {
    npr_redirect(npr_url('admin/dashboard.php'));
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    npr_require_csrf();
    $username = npr_post('username');
    $attempt = npr_attempt_login($username, isset($_POST['password']) ? (string) $_POST['password'] : '');

    if ($attempt['ok']) {
        $next = npr_get('next');
        // Only allow relative redirects back into this system.
        if ($next !== '' && strpos($next, '//') === false && strpos($next, ':') === false && $next[0] === '/') {
            npr_redirect($next);
        }
        npr_redirect(npr_url('admin/dashboard.php'));
    }
    $error = $attempt['error'];
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Admin Login — Result Management</title>
<link rel="stylesheet" href="<?php echo e(npr_url('public/assets/css/admin.css')); ?>?v=1">
</head>
<body class="admin admin--login">
<div class="login-card">
  <h1 class="login-card__title">Result Management</h1>
  <p class="login-card__sub">Administrator sign in</p>

  <?php if ($error !== ''): ?>
    <div class="alert alert--error"><?php echo e($error); ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <?php echo npr_csrf_field(); ?>
    <label class="field">
      <span class="field__label">Username</span>
      <input class="field__input" type="text" name="username" value="<?php echo e($username); ?>" required autofocus>
    </label>
    <label class="field">
      <span class="field__label">Password</span>
      <input class="field__input" type="password" name="password" required>
    </label>
    <button class="btn btn--primary btn--block" type="submit">Sign In</button>
  </form>
</div>
</body>
</html>
