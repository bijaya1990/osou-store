<?php
/**
 * Shared opening markup for public pages.
 * Expects $pageTitle and optionally $pageDescription to be set.
 */
if (!defined('NPR_BOOTSTRAPPED')) {
    exit;
}
$pageTitle = isset($pageTitle) ? $pageTitle : NPR_SITE_NAME;
$pageDescription = isset($pageDescription) ? $pageDescription : 'Check your examination result online.';
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo e($pageTitle); ?></title>
<meta name="description" content="<?php echo e($pageDescription); ?>">
<meta name="robots" content="index, follow">
<link rel="stylesheet" href="<?php echo e(npr_url('public/assets/css/result.css')); ?>?v=1">
</head>
<body>
<header class="site-bar">
  <div class="wrap site-bar__inner">
    <a class="site-bar__brand" href="<?php echo e(npr_url()); ?>">
      <span class="site-bar__dot" aria-hidden="true"></span>
      <?php echo e(NPR_SITE_NAME); ?>
    </a>
    <nav class="site-bar__nav">
      <a href="<?php echo e(npr_url()); ?>">All Results</a>
    </nav>
  </div>
</header>
<main class="wrap page">
