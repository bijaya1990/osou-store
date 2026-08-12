<?php
/**
 * Excel/CSV import: upload → column mapping → validation report → import.
 *
 * The uploaded file is staged in uploads/tmp and referenced by an opaque
 * token, so the mapping and validation steps never trust a client-supplied
 * path.
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once NPR_BASE_PATH . '/includes/auth.php';
require_once NPR_BASE_PATH . '/includes/spreadsheet.php';
require_once NPR_BASE_PATH . '/includes/importer.php';

$admin = npr_require_admin();

$id = (int) (npr_get('id') !== '' ? npr_get('id') : npr_post('result_id'));
$result = npr_find_result($id);
if (!$result) {
    npr_flash('error', 'That result no longer exists.');
    npr_redirect(npr_url('admin/dashboard.php'));
}

if (npr_is_external($result)) {
    npr_flash('error', 'This is an external result link, so there is no student data to import.');
    npr_redirect(npr_url('admin/edit-result.php?id=' . $id));
}

$step = 'upload';
$errors = array();
$notices = array();

$token = '';
$originalName = '';
$header = array();
$rows = array();
$preview = array();
$mapping = array();
$report = null;
$records = array();
$options = array(
    'derive_totals'     => true,
    'derive_percentage' => true,
    'duplicates'        => 'update',
);
$truncated = false;
$confirmed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    npr_require_csrf();
    $action = npr_post('action', 'upload');

    if ($action === 'upload') {
        $check = npr_validate_spreadsheet_upload(isset($_FILES['result_file']) ? $_FILES['result_file'] : null);
        if (!$check['ok']) {
            $errors[] = $check['error'];
        } else {
            $originalName = $check['name'];
            $token = npr_stage_upload($check['tmp'], $check['ext']);
            if ($token === '') {
                $errors[] = 'The uploaded file could not be stored. Check that uploads/tmp is writable.';
            }
        }
    } else {
        $token = npr_post('token');
        $originalName = substr(npr_post('original_name'), 0, 255);
    }

    if (!$errors && $token !== '') {
        $path = npr_staged_path($token);
        if ($path === '') {
            $errors[] = 'The uploaded file has expired. Please upload it again.';
            $token = '';
        } else {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $parsed = npr_read_spreadsheet($path, $ext);

            if (!$parsed['ok']) {
                $errors[] = $parsed['error'];
                @unlink($path);
                $token = '';
            } else {
                $header = $parsed['header'];
                $rows = $parsed['rows'];
                $truncated = $parsed['truncated'];
                $preview = array_slice($rows, 0, 5);
                $step = 'map';

                if ($truncated) {
                    $notices[] = 'Only the first ' . number_format(NPR_MAX_IMPORT_ROWS) . ' rows were read from this file.';
                }

                if ($action === 'upload') {
                    $mapping = npr_guess_mapping($header);
                } else {
                    $postedMapping = isset($_POST['map']) && is_array($_POST['map']) ? $_POST['map'] : array();
                    $mapping = npr_sanitise_mapping($postedMapping, $header);
                    $options['derive_totals'] = npr_post('derive_totals') === '1';
                    $options['derive_percentage'] = npr_post('derive_percentage') === '1';
                    $duplicates = npr_post('duplicates', 'update');
                    $options['duplicates'] = in_array($duplicates, array('update', 'skip', 'replace'), true) ? $duplicates : 'update';
                    $confirmed = npr_post('confirm_partial') === '1';

                    $mappingErrors = npr_validate_mapping($mapping);
                    if ($mappingErrors) {
                        $errors = array_merge($errors, $mappingErrors);
                    } else {
                        $prepared = npr_prepare_import($header, $rows, $mapping, array(
                            'derive_totals'     => $options['derive_totals'],
                            'derive_percentage' => $options['derive_percentage'],
                            'result_id'         => $id,
                        ));
                        $records = $prepared['records'];
                        $report = $prepared['report'];
                        $step = 'review';

                        if ($action === 'import') {
                            if (!$records) {
                                $errors[] = 'There are no valid rows to import.';
                            } elseif ($report['invalid_rows'] > 0 && !$confirmed) {
                                $errors[] = 'This file contains ' . $report['invalid_rows'] . ' invalid row(s). '
                                    . 'Fix the file and upload it again, or tick the confirmation box to import only the valid rows.';
                            } else {
                                try {
                                    $mode = $options['duplicates'] === 'replace' ? 'replace' : $options['duplicates'];
                                    $summary = npr_commit_import($id, $records, $mode);

                                    npr_query(
                                        'INSERT INTO `' . npr_table('import_logs') . '`
                                         (result_id, admin_id, original_filename, total_rows, imported_rows, updated_rows, skipped_rows, mode, notes, created_at)
                                         VALUES (?,?,?,?,?,?,?,?,?,?)',
                                        array(
                                            $id,
                                            (int) $admin['id'],
                                            $originalName,
                                            (int) $report['total_rows'],
                                            (int) $summary['inserted'],
                                            (int) $summary['updated'],
                                            (int) ($summary['skipped'] + $report['invalid_rows']),
                                            $mode,
                                            $report['invalid_rows'] > 0 ? $report['invalid_rows'] . ' invalid row(s) were not imported.' : null,
                                            npr_now(),
                                        )
                                    );

                                    @unlink($path);

                                    npr_flash('success', sprintf(
                                        'Import finished: %d added, %d updated, %d skipped.',
                                        $summary['inserted'],
                                        $summary['updated'],
                                        $summary['skipped'] + $report['invalid_rows']
                                    ));
                                    npr_redirect(npr_url('admin/students.php?id=' . $id));
                                } catch (Exception $e) {
                                    error_log('[NaukriPatra Result] import failed: ' . $e->getMessage());
                                    $errors[] = 'The import could not be completed. No records were changed.';
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

$studentCount = npr_student_count($id);
$fields = npr_import_fields();
$history = npr_fetch_all(
    'SELECT * FROM `' . npr_table('import_logs') . '` WHERE result_id = ? ORDER BY id DESC LIMIT 5',
    array($id)
);

$adminTitle = 'Import Result Data';
$activeNav = '';
require __DIR__ . '/partials/head.php';
?>
<div class="admin-head">
  <div>
    <h1>Import Result Data</h1>
    <p class="admin-head__sub"><?php echo e($result['result_title']); ?> — <?php echo e($result['institution_name']); ?></p>
  </div>
  <a class="btn btn--ghost" href="<?php echo e(npr_url('admin/edit-result.php?id=' . $id)); ?>">Back to Result</a>
</div>

<ol class="steps">
  <li class="<?php echo $step === 'upload' ? 'is-active' : 'is-done'; ?>">1. Upload file</li>
  <li class="<?php echo $step === 'map' ? 'is-active' : ($step === 'review' ? 'is-done' : ''); ?>">2. Map columns</li>
  <li class="<?php echo $step === 'review' ? 'is-active' : ''; ?>">3. Validate &amp; import</li>
</ol>

<?php foreach ($notices as $notice): ?>
  <div class="alert alert--info"><?php echo e($notice); ?></div>
<?php endforeach; ?>

<?php if ($errors): ?>
  <div class="alert alert--error">
    <strong>Please check the following:</strong>
    <ul><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<?php if ($step === 'upload'): ?>

  <section class="card">
    <h2 class="card__title">Upload Excel / CSV</h2>
    <p>This result currently has <strong><?php echo $studentCount; ?></strong> student record(s).</p>

    <form method="post" enctype="multipart/form-data">
      <?php echo npr_csrf_field(); ?>
      <input type="hidden" name="action" value="upload">
      <input type="hidden" name="result_id" value="<?php echo $id; ?>">

      <label class="field">
        <span class="field__label">Result file <em>*</em></span>
        <input class="field__input" type="file" name="result_file" accept=".csv,.xlsx,text/csv" required>
        <span class="field__help">
          Accepted formats: .xlsx and .csv (maximum <?php echo (int) round(NPR_MAX_UPLOAD_BYTES / 1048576); ?> MB,
          up to <?php echo number_format(NPR_MAX_IMPORT_ROWS); ?> rows). Old .xls files must be re-saved as .xlsx or .csv first.
        </span>
      </label>

      <div class="form-actions">
        <button class="btn btn--primary" type="submit">Upload &amp; Continue</button>
      </div>
    </form>

    <div class="hint-box">
      <h3>How the file should look</h3>
      <p>The first row must contain column headings. Every following row is one student. For example:</p>
      <div class="table-scroll">
        <table class="data-table data-table--sample">
          <thead>
            <tr><th>Exam Roll No</th><th>Registration No</th><th>Student Name</th><th>English</th><th>Odia</th><th>Total</th><th>Division</th><th>Result</th></tr>
          </thead>
          <tbody>
            <tr><td colspan="8" class="muted">Your own columns — subject names, extra details and their order are all up to you.</td></tr>
          </tbody>
        </table>
      </div>
      <p class="muted">Only the roll-number column is required. Everything else is optional, and you decide on the next
         screen what each column means.</p>
    </div>
  </section>

  <?php if ($history): ?>
    <section class="card">
      <h2 class="card__title">Recent imports</h2>
      <div class="table-scroll">
        <table class="data-table">
          <thead><tr><th>Date</th><th>File</th><th class="num">Rows</th><th class="num">Added</th><th class="num">Updated</th><th class="num">Skipped</th><th>Mode</th></tr></thead>
          <tbody>
            <?php foreach ($history as $log): ?>
              <tr>
                <td data-label="Date"><?php echo e(npr_date($log['created_at'], 'd M Y H:i')); ?></td>
                <td data-label="File"><?php echo e($log['original_filename']); ?></td>
                <td class="num" data-label="Rows"><?php echo (int) $log['total_rows']; ?></td>
                <td class="num" data-label="Added"><?php echo (int) $log['imported_rows']; ?></td>
                <td class="num" data-label="Updated"><?php echo (int) $log['updated_rows']; ?></td>
                <td class="num" data-label="Skipped"><?php echo (int) $log['skipped_rows']; ?></td>
                <td data-label="Mode"><?php echo e($log['mode']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  <?php endif; ?>

<?php else: ?>

  <form method="post">
    <?php echo npr_csrf_field(); ?>
    <input type="hidden" name="result_id" value="<?php echo $id; ?>">
    <input type="hidden" name="token" value="<?php echo e($token); ?>">
    <input type="hidden" name="original_name" value="<?php echo e($originalName); ?>">

    <?php if ($report !== null): ?>
      <section class="card">
        <h2 class="card__title">Validation report</h2>
        <div class="stat-grid stat-grid--compact">
          <div class="stat"><span class="stat__label">Total rows</span><span class="stat__value"><?php echo (int) $report['total_rows']; ?></span></div>
          <div class="stat stat--ok"><span class="stat__label">Valid rows</span><span class="stat__value"><?php echo (int) $report['valid_rows']; ?></span></div>
          <div class="stat <?php echo $report['invalid_rows'] > 0 ? 'stat--bad' : ''; ?>"><span class="stat__label">Invalid rows</span><span class="stat__value"><?php echo (int) $report['invalid_rows']; ?></span></div>
          <div class="stat"><span class="stat__label">Missing roll no.</span><span class="stat__value"><?php echo (int) $report['missing_roll']; ?></span></div>
          <div class="stat"><span class="stat__label">Duplicates in file</span><span class="stat__value"><?php echo (int) $report['duplicate_in_file']; ?></span></div>
          <div class="stat"><span class="stat__label">Already in database</span><span class="stat__value"><?php echo (int) $report['existing_in_db']; ?></span></div>
        </div>

        <?php if ($report['errors']): ?>
          <h3 class="card__subtitle">Rows that will not be imported</h3>
          <ul class="issue-list">
            <?php foreach ($report['errors'] as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?>
          </ul>
          <?php if ($report['invalid_rows'] > count($report['errors'])): ?>
            <p class="muted">…and <?php echo (int) $report['invalid_rows'] - count($report['errors']); ?> more.</p>
          <?php endif; ?>
        <?php endif; ?>

        <?php if ($report['warnings']): ?>
          <h3 class="card__subtitle">Warnings</h3>
          <ul class="issue-list issue-list--warn">
            <?php foreach ($report['warnings'] as $warning): ?><li><?php echo e($warning); ?></li><?php endforeach; ?>
          </ul>
          <p class="muted">Warnings do not block the import — the values are imported exactly as they appear in your file.</p>
        <?php endif; ?>

        <?php if ($report['valid_rows'] > 0): ?>
          <div class="import-confirm">
            <?php if ($report['invalid_rows'] > 0): ?>
              <label class="check">
                <input type="checkbox" name="confirm_partial" value="1" <?php echo $confirmed ? 'checked' : ''; ?>>
                Import the <?php echo (int) $report['valid_rows']; ?> valid row(s) and skip the <?php echo (int) $report['invalid_rows']; ?> invalid one(s).
              </label>
            <?php endif; ?>
            <button class="btn btn--primary" type="submit" name="action" value="import">
              Import <?php echo (int) $report['valid_rows']; ?> Student Record(s)
            </button>
          </div>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <section class="card">
      <h2 class="card__title">Column mapping</h2>
      <p class="muted">
        File: <strong><?php echo e($originalName); ?></strong> — <?php echo count($rows); ?> data row(s), <?php echo count($header); ?> column(s).
        Tell the system what each column contains. Columns set to “Do not import” are ignored.
      </p>

      <div class="table-scroll">
        <table class="data-table map-table">
          <thead>
            <tr>
              <th>Column in your file</th>
              <th>Sample values</th>
              <th>Import as</th>
              <th>Subject name / Max marks</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($header as $index => $label): ?>
              <?php
                $current = isset($mapping[$index]) ? $mapping[$index] : array('field' => 'ignore', 'subject' => '', 'max' => '');
                $samples = array();
                foreach ($preview as $previewRow) {
                    $value = isset($previewRow[$index]) ? trim((string) $previewRow[$index]) : '';
                    if ($value !== '') {
                        $samples[] = $value;
                    }
                }
                $samples = array_slice(array_unique($samples), 0, 3);
              ?>
              <tr>
                <td data-label="Column"><strong><?php echo e($label); ?></strong></td>
                <td data-label="Sample values" class="muted"><?php echo $samples ? e(implode(', ', $samples)) : '<span class="muted">(empty)</span>'; ?></td>
                <td data-label="Import as">
                  <select class="field__input map-table__select" name="map[<?php echo $index; ?>][field]">
                    <?php
                      $lastGroup = null;
                      foreach ($fields as $key => $field):
                        if ($field['group'] !== $lastGroup):
                          if ($lastGroup !== null && $lastGroup !== '') { echo '</optgroup>'; }
                          if ($field['group'] !== '') { echo '<optgroup label="' . e($field['group']) . '">'; }
                          $lastGroup = $field['group'];
                        endif;
                    ?>
                      <option value="<?php echo e($key); ?>" <?php echo $current['field'] === $key ? 'selected' : ''; ?>><?php echo e($field['label']); ?></option>
                    <?php endforeach; ?>
                    <?php if ($lastGroup !== null && $lastGroup !== '') { echo '</optgroup>'; } ?>
                  </select>
                </td>
                <td data-label="Subject / Max">
                  <input class="field__input map-table__subject" type="text" name="map[<?php echo $index; ?>][subject]"
                         value="<?php echo e($current['subject']); ?>" placeholder="Subject name" maxlength="120">
                  <input class="field__input map-table__max" type="text" name="map[<?php echo $index; ?>][max]"
                         value="<?php echo e($current['max']); ?>" placeholder="Max marks" maxlength="10">
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <p class="muted">
        For subject columns, the “Max marks” box sets the same maximum for every student
        (leave it empty if a separate column already holds the maximum marks).
      </p>
    </section>

    <section class="card">
      <h2 class="card__title">Import options</h2>

      <label class="check">
        <input type="checkbox" name="derive_totals" value="1" <?php echo $options['derive_totals'] ? 'checked' : ''; ?>>
        Add up the subject columns when the file has no total maximum / secured marks column.
      </label>
      <label class="check">
        <input type="checkbox" name="derive_percentage" value="1" <?php echo $options['derive_percentage'] ? 'checked' : ''; ?>>
        Calculate the percentage when the file does not contain one (secured ÷ maximum × 100).
      </label>
      <p class="muted">Division is never calculated — it is imported only when your file provides it.</p>

      <label class="field field--narrow">
        <span class="field__label">If a roll number already exists in this result</span>
        <select class="field__input" name="duplicates">
          <option value="update" <?php echo $options['duplicates'] === 'update' ? 'selected' : ''; ?>>Update the existing record</option>
          <option value="skip" <?php echo $options['duplicates'] === 'skip' ? 'selected' : ''; ?>>Keep the existing record and skip the row</option>
          <option value="replace" <?php echo $options['duplicates'] === 'replace' ? 'selected' : ''; ?>>Delete all <?php echo $studentCount; ?> existing record(s) first, then import</option>
        </select>
      </label>

      <div class="form-actions">
        <button class="btn btn--primary" type="submit" name="action" value="validate">Check File</button>
        <a class="btn btn--ghost" href="<?php echo e(npr_url('admin/import.php?id=' . $id)); ?>">Start over</a>
      </div>
    </section>
  </form>

<?php endif; ?>
<?php require __DIR__ . '/partials/foot.php'; ?>
