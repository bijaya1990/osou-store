<?php
/**
 * Shown only if the browser does not follow the 302 for an external result.
 * Expects $result and $target (an already-validated http/https URL).
 */
if (!defined('NPR_BOOTSTRAPPED') || empty($result) || empty($target)) {
    exit;
}
$host = parse_url($target, PHP_URL_HOST);
$pageTitle = $result['result_title'] . ' — ' . NPR_SITE_NAME;
require __DIR__ . '/layout-top.php';
?>
<div class="empty-state">
  <p class="empty-state__title"><?php echo e($result['result_title']); ?></p>
  <p>This result is published on the official website<?php echo $host ? ' (' . e($host) . ')' : ''; ?>.
     You are being redirected there now.</p>
  <p>
    <a class="btn btn--primary" href="<?php echo e($target); ?>" rel="noopener nofollow external">
      <?php echo e(npr_result_button_text($result)); ?>
    </a>
  </p>
</div>
<?php require __DIR__ . '/layout-bottom.php'; ?>
