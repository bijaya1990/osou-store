<?php
/**
 * Create a new result (internal or external).
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once NPR_BASE_PATH . '/includes/auth.php';
require_once NPR_BASE_PATH . '/includes/result-form.php';

$admin = npr_require_admin();

$form = npr_blank_result();
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    npr_require_csrf();

    $collected = npr_collect_result_input(0);
    $errors = $collected['errors'];
    $form = array_merge($form, $collected['data']);

    $logo = npr_store_logo('institution_logo');
    if ($logo['error'] !== '') {
        $errors[] = $logo['error'];
    }
    $form['institution_logo'] = $logo['file'];

    if (!$errors) {
        // An internal result has no student data yet, so it cannot go live on
        // creation — it would show an empty result page.
        $wantedPublish = ($form['status'] === 'published' && $form['result_type'] === 'internal');
        if ($wantedPublish) {
            $form['status'] = 'draft';
        }

        $id = npr_create_result($form);

        if ($wantedPublish) {
            npr_flash('error', 'The result was saved as a draft: import the student data first, then publish it.');
        }

        if ($form['result_type'] === 'external') {
            npr_flash('success', 'External result saved. It links to the official website you provided.');
            npr_redirect(npr_url('admin/edit-result.php?id=' . $id));
        }

        npr_flash('success', 'Result saved. Now upload the Excel/CSV file with the student marks.');
        npr_redirect(npr_url('admin/import.php?id=' . $id));
    }

    if ($logo['file'] !== '') {
        // Do not leave an orphan file behind when the form is redisplayed.
        npr_delete_logo($logo['file']);
        $form['institution_logo'] = '';
    }
}

$adminTitle = 'Add New Result';
$activeNav = 'add';
require __DIR__ . '/partials/head.php';
?>
<div class="admin-head">
  <h1>Add New Result</h1>
  <a class="btn btn--ghost" href="<?php echo e(npr_url('admin/dashboard.php')); ?>">Back to Dashboard</a>
</div>

<?php if ($errors): ?>
  <div class="alert alert--error">
    <strong>Please correct the following:</strong>
    <ul><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<form class="card" method="post" enctype="multipart/form-data">
  <?php echo npr_csrf_field(); ?>
  <?php $isEdit = false; require __DIR__ . '/partials/result-fields.php'; ?>

  <div class="form-actions">
    <button class="btn btn--primary" type="submit">Save Result</button>
    <a class="btn btn--ghost" href="<?php echo e(npr_url('admin/dashboard.php')); ?>">Cancel</a>
  </div>
  <p class="form-note" data-npr-when="internal">
    After saving you will be taken straight to the Excel/CSV import screen.
  </p>
</form>
<?php require __DIR__ . '/partials/foot.php'; ?>
