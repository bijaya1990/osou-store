<?php
if (!defined('NPR_BOOTSTRAPPED')) {
    exit;
}
$pageTitle = 'Result not available — ' . NPR_SITE_NAME;
require __DIR__ . '/layout-top.php';
?>
<div class="empty-state">
  <p class="empty-state__title">Result not available</p>
  <p>The page you are looking for is not available. The result may not have been published yet.</p>
  <p><a class="btn btn--primary" href="<?php echo e(npr_url()); ?>">View published results</a></p>
</div>
<?php require __DIR__ . '/layout-bottom.php'; ?>
