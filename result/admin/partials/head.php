<?php
/**
 * Admin chrome (top). Expects $admin and optionally $adminTitle, $activeNav.
 */
if (!defined('NPR_BOOTSTRAPPED')) {
    exit;
}
$adminTitle = isset($adminTitle) ? $adminTitle : 'Dashboard';
$activeNav = isset($activeNav) ? $activeNav : '';
$flashes = npr_take_flashes();
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo e($adminTitle); ?> — Result Admin</title>
<link rel="stylesheet" href="<?php echo e(npr_url('public/assets/css/admin.css')); ?>?v=1">
</head>
<body class="admin">
<header class="admin-bar">
  <div class="admin-bar__inner">
    <a class="admin-bar__brand" href="<?php echo e(npr_url('admin/dashboard.php')); ?>">Result Management</a>
    <nav class="admin-bar__nav">
      <a class="<?php echo $activeNav === 'dashboard' ? 'is-active' : ''; ?>" href="<?php echo e(npr_url('admin/dashboard.php')); ?>">Dashboard</a>
      <a class="<?php echo $activeNav === 'add' ? 'is-active' : ''; ?>" href="<?php echo e(npr_url('admin/add-result.php')); ?>">Add New Result</a>
      <a href="<?php echo e(npr_url()); ?>" target="_blank" rel="noopener">View Site</a>
    </nav>
    <div class="admin-bar__user">
      <a href="<?php echo e(npr_url('admin/account.php')); ?>"><?php echo e(isset($admin['username']) ? $admin['username'] : ''); ?></a>
      <a href="<?php echo e(npr_url('admin/logout.php')); ?>">Log out</a>
    </div>
  </div>
</header>
<main class="admin-main">
<?php foreach ($flashes as $flash): ?>
  <div class="alert alert--<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
<?php endforeach; ?>
