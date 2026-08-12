<?php
/**
 * Admin dashboard: counters plus the list of results.
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once NPR_BASE_PATH . '/includes/auth.php';

$admin = npr_require_admin();

$stats = npr_fetch_one(
    'SELECT
        COUNT(*) AS total,
        SUM(status = \'published\') AS published,
        SUM(status = \'draft\') AS draft
     FROM `' . npr_table('results') . '`'
);
$totalStudents = (int) npr_fetch_value('SELECT COUNT(*) FROM `' . npr_table('result_students') . '`', array(), 0);

$results = npr_fetch_all(
    'SELECT r.*, (SELECT COUNT(*) FROM `' . npr_table('result_students') . '` s WHERE s.result_id = r.id) AS student_count
       FROM `' . npr_table('results') . '` r
   ORDER BY r.created_at DESC, r.id DESC'
);

$adminTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/partials/head.php';
?>
<div class="admin-head">
  <h1>Result Management</h1>
  <a class="btn btn--primary" href="<?php echo e(npr_url('admin/add-result.php')); ?>">+ Add New Result</a>
</div>

<div class="stat-grid">
  <div class="stat"><span class="stat__label">Total Results</span><span class="stat__value"><?php echo (int) $stats['total']; ?></span></div>
  <div class="stat"><span class="stat__label">Published</span><span class="stat__value"><?php echo (int) $stats['published']; ?></span></div>
  <div class="stat"><span class="stat__label">Draft</span><span class="stat__value"><?php echo (int) $stats['draft']; ?></span></div>
  <div class="stat"><span class="stat__label">Total Students</span><span class="stat__value"><?php echo $totalStudents; ?></span></div>
</div>

<section class="card">
  <h2 class="card__title">Recent Results</h2>

  <?php if (!$results): ?>
    <div class="empty">
      <p><strong>No results published yet.</strong></p>
      <p>Create a result, upload the Excel/CSV file of marks, then publish it. The homepage ticker shows
         “LIVE RESULTS → Coming Soon” until the first result is published.</p>
      <p><a class="btn btn--primary" href="<?php echo e(npr_url('admin/add-result.php')); ?>">+ Add New Result</a></p>
    </div>
  <?php else: ?>
    <div class="table-scroll">
      <table class="data-table">
        <thead>
          <tr>
            <th>Result</th>
            <th>Institution</th>
            <th>Type</th>
            <th class="num">Students</th>
            <th>Status</th>
            <th>Ticker</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($results as $row): ?>
            <?php $rowIsExternal = npr_is_external($row); ?>
            <tr>
              <td data-label="Result">
                <a class="data-table__strong" href="<?php echo e(npr_url('admin/edit-result.php?id=' . (int) $row['id'])); ?>"><?php echo e($row['result_title']); ?></a>
                <div class="data-table__sub"><?php echo e($row['examination_name']); ?><?php echo $row['class_course'] !== '' ? ' · ' . e($row['class_course']) : ''; ?></div>
              </td>
              <td data-label="Institution"><?php echo e($row['institution_name']); ?></td>
              <td data-label="Type">
                <span class="pill pill--<?php echo $rowIsExternal ? 'external' : 'internal'; ?>"><?php echo $rowIsExternal ? 'External Link' : 'Internal'; ?></span>
              </td>
              <td class="num" data-label="Students"><?php echo $rowIsExternal ? '&ndash;' : (int) $row['student_count']; ?></td>
              <td data-label="Status">
                <span class="pill pill--<?php echo $row['status'] === 'published' ? 'live' : 'draft'; ?>"><?php echo e(ucfirst($row['status'])); ?></span>
              </td>
              <td data-label="Ticker"><?php echo ((int) $row['show_on_ticker'] === 1) ? 'Yes' : 'No'; ?></td>
              <td data-label="Date"><?php echo e(npr_date($row['result_date'], 'd M Y')); ?></td>
              <td data-label="Actions" class="actions">
                <a href="<?php echo e(npr_url('admin/edit-result.php?id=' . (int) $row['id'])); ?>">Edit</a>
                <?php if (!$rowIsExternal): ?>
                  <a href="<?php echo e(npr_url('admin/import.php?id=' . (int) $row['id'])); ?>">Import</a>
                  <a href="<?php echo e(npr_url('admin/students.php?id=' . (int) $row['id'])); ?>">Students</a>
                <?php endif; ?>
                <?php if ($row['status'] === 'published'): ?>
                  <a href="<?php echo e(npr_result_link($row)); ?>" target="_blank" rel="noopener<?php echo $rowIsExternal ? ' nofollow external' : ''; ?>">View</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/partials/foot.php'; ?>
