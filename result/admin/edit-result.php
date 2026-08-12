<?php
/**
 * Edit a result, publish / unpublish it, toggle the ticker, or delete it.
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once NPR_BASE_PATH . '/includes/auth.php';
require_once NPR_BASE_PATH . '/includes/result-form.php';

$admin = npr_require_admin();

$id = (int) npr_get('id');
$result = npr_find_result($id);
if (!$result) {
    npr_flash('error', 'That result no longer exists.');
    npr_redirect(npr_url('admin/dashboard.php'));
}

$errors = array();
$form = array_merge(npr_blank_result(), $result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    npr_require_csrf();
    $action = npr_post('action', 'save');

    if ($action === 'publish' || $action === 'unpublish') {
        $status = $action === 'publish' ? 'published' : 'draft';

        if ($status === 'published' && $result['result_type'] === 'internal' && npr_student_count($id) === 0) {
            npr_flash('error', 'This internal result has no student records yet. Import the Excel/CSV file before publishing.');
            npr_redirect(npr_url('admin/edit-result.php?id=' . $id));
        }

        npr_query(
            'UPDATE `' . npr_table('results') . '`
                SET status = ?, published_at = COALESCE(published_at, ?), updated_at = ?
              WHERE id = ?',
            array($status, $status === 'published' ? npr_now() : null, npr_now(), $id)
        );
        npr_touch_ticker_revision();
        npr_flash('success', $status === 'published'
            ? 'Result published. It is now live and appears in the homepage ticker (if enabled).'
            : 'Result unpublished. It is no longer visible to students.');
        npr_redirect(npr_url('admin/edit-result.php?id=' . $id));
    }

    if ($action === 'toggle_ticker') {
        $new = (int) $result['show_on_ticker'] === 1 ? 0 : 1;
        npr_query(
            'UPDATE `' . npr_table('results') . '` SET show_on_ticker = ?, updated_at = ? WHERE id = ?',
            array($new, npr_now(), $id)
        );
        npr_touch_ticker_revision();
        npr_flash('success', $new ? 'This result will appear in the homepage ticker.' : 'This result was removed from the homepage ticker.');
        npr_redirect(npr_url('admin/edit-result.php?id=' . $id));
    }

    // Default: save the form.
    $collected = npr_collect_result_input($id);
    $errors = $collected['errors'];
    $form = array_merge($form, $collected['data']);
    $form['institution_logo'] = $result['institution_logo'];

    $logo = npr_store_logo('institution_logo');
    if ($logo['error'] !== '') {
        $errors[] = $logo['error'];
    }

    if (!$errors) {
        if ($logo['file'] !== '') {
            npr_delete_logo($result['institution_logo']);
            $form['institution_logo'] = $logo['file'];
        } elseif (npr_post('remove_logo') === '1') {
            npr_delete_logo($result['institution_logo']);
            $form['institution_logo'] = null;
        }

        if ($form['status'] === 'published' && $form['result_type'] === 'internal' && npr_student_count($id) === 0) {
            $form['status'] = 'draft';
            npr_flash('error', 'The result was saved as a draft: an internal result cannot be published before its student data is imported.');
        }

        npr_update_result($id, $form, $result);
        npr_touch_ticker_revision();
        npr_flash('success', 'Result updated.');
        npr_redirect(npr_url('admin/edit-result.php?id=' . $id));
    } elseif ($logo['file'] !== '') {
        npr_delete_logo($logo['file']);
    }
}

$studentCount = npr_student_count($id);
$isExternal = npr_is_external($result);

$adminTitle = 'Edit Result';
$activeNav = '';
require __DIR__ . '/partials/head.php';
?>
<div class="admin-head">
  <h1>Edit Result</h1>
  <a class="btn btn--ghost" href="<?php echo e(npr_url('admin/dashboard.php')); ?>">Back to Dashboard</a>
</div>

<?php if ($errors): ?>
  <div class="alert alert--error">
    <strong>Please correct the following:</strong>
    <ul><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<section class="card card--summary">
  <div class="summary-grid">
    <div>
      <span class="summary-grid__label">Type</span>
      <strong><?php echo $isExternal ? 'External Link' : 'Internal'; ?></strong>
    </div>
    <div>
      <span class="summary-grid__label">Status</span>
      <strong><span class="pill pill--<?php echo $result['status'] === 'published' ? 'live' : 'draft'; ?>"><?php echo e(ucfirst($result['status'])); ?></span></strong>
    </div>
    <div>
      <span class="summary-grid__label"><?php echo $isExternal ? 'Destination' : 'Students imported'; ?></span>
      <strong>
        <?php if ($isExternal): ?>
          <a href="<?php echo e((string) $result['external_url']); ?>" target="_blank" rel="noopener nofollow external"><?php echo e(parse_url((string) $result['external_url'], PHP_URL_HOST)); ?></a>
        <?php else: ?>
          <?php echo $studentCount; ?>
        <?php endif; ?>
      </strong>
    </div>
    <div>
      <span class="summary-grid__label">Homepage ticker</span>
      <strong><?php echo ((int) $result['show_on_ticker'] === 1) ? 'Yes' : 'No'; ?></strong>
    </div>
  </div>

  <div class="summary-actions">
    <form method="post" class="inline-form">
      <?php echo npr_csrf_field(); ?>
      <?php if ($result['status'] === 'published'): ?>
        <button class="btn btn--ghost" type="submit" name="action" value="unpublish">Unpublish</button>
      <?php else: ?>
        <button class="btn btn--primary" type="submit" name="action" value="publish">Publish Result</button>
      <?php endif; ?>
      <button class="btn btn--ghost" type="submit" name="action" value="toggle_ticker">
        <?php echo ((int) $result['show_on_ticker'] === 1) ? 'Remove from ticker' : 'Show on ticker'; ?>
      </button>
    </form>

    <?php if (!$isExternal): ?>
      <a class="btn btn--ghost" href="<?php echo e(npr_url('admin/import.php?id=' . $id)); ?>">Upload / Import Data</a>
      <a class="btn btn--ghost" href="<?php echo e(npr_url('admin/students.php?id=' . $id)); ?>">View Students (<?php echo $studentCount; ?>)</a>
    <?php endif; ?>

    <a class="btn btn--ghost" href="<?php echo e(npr_result_link($result)); ?>" target="_blank" rel="noopener">Open Result Page</a>
    <a class="btn btn--danger" href="<?php echo e(npr_url('admin/delete-result.php?id=' . $id)); ?>">Delete</a>
  </div>

  <?php if ($result['status'] === 'draft'): ?>
    <p class="form-note">Draft results are not searchable and never appear in the homepage ticker.</p>
  <?php endif; ?>
</section>

<form class="card" method="post" enctype="multipart/form-data">
  <?php echo npr_csrf_field(); ?>
  <input type="hidden" name="action" value="save">
  <?php $isEdit = true; require __DIR__ . '/partials/result-fields.php'; ?>

  <div class="form-actions">
    <button class="btn btn--primary" type="submit">Save Changes</button>
    <a class="btn btn--ghost" href="<?php echo e(npr_url('admin/dashboard.php')); ?>">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/partials/foot.php'; ?>
