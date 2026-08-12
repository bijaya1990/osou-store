<?php
/**
 * Imported student records for one result: search, paging, single delete,
 * and "clear all".
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once NPR_BASE_PATH . '/includes/auth.php';

$admin = npr_require_admin();

$id = (int) (npr_get('id') !== '' ? npr_get('id') : npr_post('result_id'));
$result = npr_find_result($id);
if (!$result) {
    npr_flash('error', 'That result no longer exists.');
    npr_redirect(npr_url('admin/dashboard.php'));
}
if (npr_is_external($result)) {
    npr_flash('error', 'External result links do not store student records.');
    npr_redirect(npr_url('admin/edit-result.php?id=' . $id));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    npr_require_csrf();
    $action = npr_post('action');

    if ($action === 'delete_student') {
        $studentId = (int) npr_post('student_id');
        npr_query(
            'DELETE FROM `' . npr_table('result_students') . '` WHERE id = ? AND result_id = ?',
            array($studentId, $id)
        );
        npr_flash('success', 'Student record deleted.');
    } elseif ($action === 'clear_all' && npr_post('confirm') === 'DELETE') {
        npr_query('DELETE FROM `' . npr_table('result_students') . '` WHERE result_id = ?', array($id));
        npr_flash('success', 'All student records for this result were deleted.');
    } elseif ($action === 'clear_all') {
        npr_flash('error', 'Type DELETE in the confirmation box to remove all student records.');
    }

    npr_redirect(npr_url('admin/students.php?id=' . $id));
}

$search = npr_get('q');
$page = max(1, (int) npr_get('page', 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$where = 'result_id = ?';
$params = array($id);
if ($search !== '') {
    $where .= ' AND (roll_number_key LIKE ? OR student_name LIKE ? OR registration_number LIKE ?)';
    $params[] = '%' . npr_roll_key($search) . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$total = (int) npr_fetch_value(
    'SELECT COUNT(*) FROM `' . npr_table('result_students') . '` WHERE ' . $where,
    $params,
    0
);
$pages = max(1, (int) ceil($total / $perPage));

$students = npr_fetch_all(
    'SELECT * FROM `' . npr_table('result_students') . '`
      WHERE ' . $where . '
   ORDER BY roll_number_key ASC
      LIMIT ' . $perPage . ' OFFSET ' . $offset,
    $params
);

$adminTitle = 'Student Records';
$activeNav = '';
require __DIR__ . '/partials/head.php';
?>
<div class="admin-head">
  <div>
    <h1>Student Records</h1>
    <p class="admin-head__sub"><?php echo e($result['result_title']); ?> — <?php echo e($result['institution_name']); ?></p>
  </div>
  <div class="admin-head__actions">
    <a class="btn btn--ghost" href="<?php echo e(npr_url('admin/import.php?id=' . $id)); ?>">Import More</a>
    <a class="btn btn--ghost" href="<?php echo e(npr_url('admin/edit-result.php?id=' . $id)); ?>">Back to Result</a>
  </div>
</div>

<section class="card">
  <form class="search-bar" method="get">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input class="field__input" type="search" name="q" value="<?php echo e($search); ?>" placeholder="Search roll number, name or registration number">
    <button class="btn btn--ghost" type="submit">Search</button>
    <?php if ($search !== ''): ?>
      <a class="btn btn--ghost" href="<?php echo e(npr_url('admin/students.php?id=' . $id)); ?>">Clear</a>
    <?php endif; ?>
  </form>

  <p class="muted"><?php echo number_format($total); ?> record(s)<?php echo $search !== '' ? ' matching your search' : ''; ?>.</p>

  <?php if (!$students): ?>
    <div class="empty">
      <p><strong>No student records<?php echo $search !== '' ? ' matched your search' : ' yet'; ?>.</strong></p>
      <?php if ($search === ''): ?>
        <p>Upload the Excel/CSV file to import the marks for this examination.</p>
        <p><a class="btn btn--primary" href="<?php echo e(npr_url('admin/import.php?id=' . $id)); ?>">Upload Excel / CSV</a></p>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="table-scroll">
      <table class="data-table">
        <thead>
          <tr>
            <th>Roll No.</th>
            <th>Name</th>
            <th>Registration</th>
            <th class="num">Max</th>
            <th class="num">Secured</th>
            <th class="num">%</th>
            <th>Division</th>
            <th>Result</th>
            <th>Subjects</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $student): ?>
            <?php $marks = npr_decode_marks($student['marks_data']); ?>
            <tr>
              <td data-label="Roll No."><strong><?php echo e($student['roll_number']); ?></strong></td>
              <td data-label="Name"><?php echo e($student['student_name']); ?></td>
              <td data-label="Registration"><?php echo e($student['registration_number']); ?></td>
              <td class="num" data-label="Max"><?php echo e(npr_num($student['maximum_marks'])); ?></td>
              <td class="num" data-label="Secured"><?php echo e(npr_num($student['secured_marks'])); ?></td>
              <td class="num" data-label="%"><?php echo $student['percentage'] !== null ? e(number_format((float) $student['percentage'], 2)) : ''; ?></td>
              <td data-label="Division"><?php echo e($student['division']); ?></td>
              <td data-label="Result">
                <?php if (!empty($student['result_status'])): ?>
                  <span class="pill pill--<?php echo e(npr_status_class($student['result_status'])); ?>"><?php echo e($student['result_status']); ?></span>
                <?php endif; ?>
              </td>
              <td data-label="Subjects" class="muted"><?php echo count($marks); ?></td>
              <td data-label="" class="actions">
                <form method="post" class="inline-form" onsubmit="return confirm('Delete this student record?');">
                  <?php echo npr_csrf_field(); ?>
                  <input type="hidden" name="result_id" value="<?php echo $id; ?>">
                  <input type="hidden" name="action" value="delete_student">
                  <input type="hidden" name="student_id" value="<?php echo (int) $student['id']; ?>">
                  <button class="link-btn link-btn--danger" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($pages > 1): ?>
      <nav class="pager">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <?php if ($p === $page): ?>
            <span class="pager__item is-current"><?php echo $p; ?></span>
          <?php else: ?>
            <a class="pager__item" href="<?php echo e(npr_url('admin/students.php?id=' . $id . '&page=' . $p . ($search !== '' ? '&q=' . rawurlencode($search) : ''))); ?>"><?php echo $p; ?></a>
          <?php endif; ?>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</section>

<?php if ($total > 0): ?>
  <section class="card card--danger">
    <h2 class="card__title">Clear all student records</h2>
    <p>This deletes every imported student record for this result. The result itself is kept.</p>
    <form method="post" class="inline-form" onsubmit="return confirm('Delete ALL student records for this result?');">
      <?php echo npr_csrf_field(); ?>
      <input type="hidden" name="result_id" value="<?php echo $id; ?>">
      <input type="hidden" name="action" value="clear_all">
      <input class="field__input field--narrow" type="text" name="confirm" placeholder="Type DELETE to confirm" autocomplete="off">
      <button class="btn btn--danger" type="submit">Delete All Records</button>
    </form>
  </section>
<?php endif; ?>
<?php require __DIR__ . '/partials/foot.php'; ?>
