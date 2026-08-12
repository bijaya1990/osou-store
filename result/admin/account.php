<?php
/**
 * Change the signed-in administrator's password.
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once NPR_BASE_PATH . '/includes/auth.php';

$admin = npr_require_admin();

$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    npr_require_csrf();

    $current = isset($_POST['current_password']) ? (string) $_POST['current_password'] : '';
    $new = isset($_POST['new_password']) ? (string) $_POST['new_password'] : '';
    $repeat = isset($_POST['repeat_password']) ? (string) $_POST['repeat_password'] : '';

    if (!password_verify($current, $admin['password_hash'])) {
        $errors[] = 'Your current password is not correct.';
    }
    if (strlen($new) < 10) {
        $errors[] = 'The new password must be at least 10 characters long.';
    }
    if ($new !== $repeat) {
        $errors[] = 'The two new passwords do not match.';
    }
    if ($new !== '' && $new === $current) {
        $errors[] = 'The new password must be different from the current one.';
    }

    if (!$errors) {
        npr_query(
            'UPDATE `' . npr_table('admins') . '` SET password_hash = ?, updated_at = ? WHERE id = ?',
            array(password_hash($new, PASSWORD_DEFAULT), npr_now(), (int) $admin['id'])
        );
        npr_flash('success', 'Your password has been changed.');
        npr_redirect(npr_url('admin/account.php'));
    }
}

$adminTitle = 'My Account';
$activeNav = '';
require __DIR__ . '/partials/head.php';
?>
<div class="admin-head">
  <div>
    <h1>My Account</h1>
    <p class="admin-head__sub">Signed in as <?php echo e($admin['username']); ?></p>
  </div>
  <a class="btn btn--ghost" href="<?php echo e(npr_url('admin/dashboard.php')); ?>">Back to Dashboard</a>
</div>

<?php if ($errors): ?>
  <div class="alert alert--error">
    <strong>Please correct the following:</strong>
    <ul><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<form class="card" method="post" autocomplete="off">
  <?php echo npr_csrf_field(); ?>
  <h2 class="card__title">Change Password</h2>

  <label class="field field--narrow">
    <span class="field__label">Current password</span>
    <input class="field__input" type="password" name="current_password" required>
  </label>
  <label class="field field--narrow">
    <span class="field__label">New password (at least 10 characters)</span>
    <input class="field__input" type="password" name="new_password" required>
  </label>
  <label class="field field--narrow">
    <span class="field__label">Repeat new password</span>
    <input class="field__input" type="password" name="repeat_password" required>
  </label>

  <div class="form-actions">
    <button class="btn btn--primary" type="submit">Change Password</button>
  </div>
  <p class="form-note">
    Forgotten your password and locked out? See “Resetting a forgotten password” in README.md —
    it uses <code>tools/reset-password.php</code> and requires access to your hosting file manager.
  </p>
</form>

<section class="card">
  <h2 class="card__title">Account Details</h2>
  <div class="summary-grid">
    <div><span class="summary-grid__label">Username</span><strong><?php echo e($admin['username']); ?></strong></div>
    <div><span class="summary-grid__label">Email</span><strong><?php echo e($admin['email'] !== null && $admin['email'] !== '' ? $admin['email'] : '—'); ?></strong></div>
    <div><span class="summary-grid__label">Last login</span><strong><?php echo e(npr_date($admin['last_login_at'], 'd M Y H:i')); ?></strong></div>
  </div>
</section>
<?php require __DIR__ . '/partials/foot.php'; ?>
