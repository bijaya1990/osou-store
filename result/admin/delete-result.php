<?php
/**
 * Delete a result together with its student records (with confirmation).
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once NPR_BASE_PATH . '/includes/auth.php';
require_once NPR_BASE_PATH . '/includes/result-form.php';

$admin = npr_require_admin();

$id = (int) (npr_get('id') !== '' ? npr_get('id') : npr_post('result_id'));
$result = npr_find_result($id);
if (!$result) {
    npr_flash('error', 'That result no longer exists.');
    npr_redirect(npr_url('admin/dashboard.php'));
}

$studentCount = npr_is_external($result) ? 0 : npr_student_count($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    npr_require_csrf();

    if (npr_post('confirm') !== 'DELETE') {
        npr_flash('error', 'Type DELETE to confirm.');
        npr_redirect(npr_url('admin/delete-result.php?id=' . $id));
    }

    $pdo = npr_db();
    $pdo->beginTransaction();
    try {
        npr_query('DELETE FROM `' . npr_table('result_students') . '` WHERE result_id = ?', array($id));
        npr_query('DELETE FROM `' . npr_table('import_logs') . '` WHERE result_id = ?', array($id));
        npr_query('DELETE FROM `' . npr_table('results') . '` WHERE id = ?', array($id));
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('[NaukriPatra Result] delete failed: ' . $e->getMessage());
        npr_flash('error', 'The result could not be deleted.');
        npr_redirect(npr_url('admin/edit-result.php?id=' . $id));
    }

    npr_delete_logo($result['institution_logo']);
    npr_touch_ticker_revision();
    npr_flash('success', 'Result deleted.');
    npr_redirect(npr_url('admin/dashboard.php'));
}

$adminTitle = 'Delete Result';
$activeNav = '';
require __DIR__ . '/partials/head.php';
?>
<div class="admin-head">
  <h1>Delete Result</h1>
  <a class="btn btn--ghost" href="<?php echo e(npr_url('admin/edit-result.php?id=' . $id)); ?>">Cancel</a>
</div>

<section class="card card--danger">
  <h2 class="card__title">This cannot be undone</h2>
  <p>
    You are about to delete <strong><?php echo e($result['result_title']); ?></strong>
    (<?php echo e($result['institution_name']); ?>)
    <?php if (!npr_is_external($result)): ?>
      and its <strong><?php echo $studentCount; ?></strong> student record(s)
    <?php endif; ?>.
    The result will disappear from the homepage ticker immediately.
  </p>

  <form method="post">
    <?php echo npr_csrf_field(); ?>
    <input type="hidden" name="result_id" value="<?php echo $id; ?>">
    <label class="field field--narrow">
      <span class="field__label">Type DELETE to confirm</span>
      <input class="field__input" type="text" name="confirm" autocomplete="off" required>
    </label>
    <div class="form-actions">
      <button class="btn btn--danger" type="submit">Delete Permanently</button>
      <a class="btn btn--ghost" href="<?php echo e(npr_url('admin/edit-result.php?id=' . $id)); ?>">Keep it</a>
    </div>
  </form>
</section>
<?php require __DIR__ . '/partials/foot.php'; ?>
