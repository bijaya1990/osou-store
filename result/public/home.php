<?php
/**
 * Landing page: every published result.
 */
if (!defined('NPR_BOOTSTRAPPED')) {
    exit;
}

$results = npr_published_results(50);

$pageTitle = 'Online Results — ' . NPR_SITE_NAME;
$pageDescription = 'Check school, college, university and board examination results online.';
require __DIR__ . '/layout-top.php';
?>
<section class="page-head">
  <h1>Online Result Portal</h1>
  <p>Select an examination below and enter your roll number to view your result.</p>
</section>

<?php if (!$results): ?>
  <div class="empty-state">
    <p class="empty-state__title">No results have been published yet.</p>
    <p>Results will appear here as soon as they are declared. Please check back later.</p>
  </div>
<?php else: ?>
  <ul class="result-list">
    <?php foreach ($results as $row): ?>
      <?php $isExternal = npr_is_external($row); ?>
      <li class="result-list__item">
        <div class="result-list__body">
          <h2 class="result-list__title"><?php echo e($row['result_title']); ?></h2>
          <p class="result-list__meta">
            <span><?php echo e($row['institution_name']); ?></span>
            <?php if ($isExternal): ?><span class="tag">Official website</span><?php endif; ?>
            <?php if (!empty($row['class_course'])): ?><span><?php echo e($row['class_course']); ?></span><?php endif; ?>
            <?php if (!empty($row['academic_session'])): ?><span>Session <?php echo e($row['academic_session']); ?></span><?php endif; ?>
            <?php if (!empty($row['result_date'])): ?><span>Declared <?php echo e(npr_date($row['result_date'])); ?></span><?php endif; ?>
          </p>
        </div>
        <a class="btn btn--primary"
           href="<?php echo e(npr_result_link($row)); ?>"
           <?php echo $isExternal ? 'target="_blank" rel="noopener nofollow external"' : ''; ?>>
          <?php echo e(npr_result_button_text($row)); ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
<?php require __DIR__ . '/layout-bottom.php'; ?>
