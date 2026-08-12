<?php
/**
 * The printed marksheet. Expects $result and $student.
 * Every value here comes from the imported data — nothing is invented.
 */
if (!defined('NPR_BOOTSTRAPPED') || empty($result) || empty($student)) {
    exit;
}

$marks = npr_decode_marks($student['marks_data']);
$extra = npr_decode_extra($student['extra_data']);

$hasMaxColumn = false;
$hasGradeColumn = false;
$sumMax = null;
$sumSecured = null;

foreach ($marks as $mark) {
    if ($mark['max'] !== null && $mark['max'] !== '') {
        $hasMaxColumn = true;
        if (is_numeric($mark['max'])) {
            $sumMax = ($sumMax === null ? 0 : $sumMax) + (float) $mark['max'];
        }
    }
    if ($mark['grade'] !== '') {
        $hasGradeColumn = true;
    }
    if (is_numeric($mark['secured'])) {
        $sumSecured = ($sumSecured === null ? 0 : $sumSecured) + (float) $mark['secured'];
    }
}

$totalMax = $student['maximum_marks'] !== null && $student['maximum_marks'] !== '' ? $student['maximum_marks'] : $sumMax;
$totalSecured = $student['secured_marks'] !== null && $student['secured_marks'] !== ''
    ? $student['secured_marks']
    : ($student['total_marks'] !== null && $student['total_marks'] !== '' ? $student['total_marks'] : $sumSecured);

$status = strtoupper((string) $student['result_status']);
$statusClass = npr_status_class($status);

$candidateRows = array(
    'Student Name'            => $student['student_name'],
    $result['roll_label']     => $student['roll_number'],
    'Registration No.'        => $student['registration_number'],
    "Father's Name"           => $student['father_name'],
    "Mother's Name"           => $student['mother_name'],
    'Date of Birth'           => $student['date_of_birth'],
    'Class / Course'          => $result['class_course'],
    'Semester / Year'         => $result['semester_year'],
    'Academic Session'        => $result['academic_session'],
);
foreach ($extra as $label => $value) {
    $candidateRows[(string) $label] = $value;
}
$candidateRows = array_filter($candidateRows, function ($value) {
    return trim((string) $value) !== '';
});
?>
<article class="marksheet" id="marksheet">

  <div class="marksheet__print-head print-only">
    <?php if (!empty($result['institution_logo'])): ?>
      <img class="marksheet__print-logo" src="<?php echo e(npr_logo_url($result['institution_logo'])); ?>" alt="">
    <?php endif; ?>
    <h2><?php echo e($result['institution_name']); ?></h2>
    <?php if (!empty($result['institution_address'])): ?>
      <p><?php echo e($result['institution_address']); ?></p>
    <?php endif; ?>
    <p class="marksheet__print-exam"><?php echo e($result['examination_name']); ?></p>
    <p class="marksheet__print-title"><?php echo e($result['result_title']); ?></p>
  </div>

  <section class="panel">
    <h2 class="panel__title">Candidate Information</h2>
    <dl class="candidate">
      <?php foreach ($candidateRows as $label => $value): ?>
        <div class="candidate__row">
          <dt><?php echo e($label); ?></dt>
          <dd><?php echo e($value); ?></dd>
        </div>
      <?php endforeach; ?>
    </dl>
  </section>

  <?php if ($marks): ?>
    <section class="panel">
      <h2 class="panel__title">Subject-wise Marks</h2>
      <div class="table-scroll">
        <table class="marks-table<?php echo $hasGradeColumn ? ' marks-table--wide' : ''; ?>">
          <thead>
            <tr>
              <th scope="col" class="marks-table__subject">Subject</th>
              <?php if ($hasMaxColumn): ?><th scope="col" class="num">Maximum Marks</th><?php endif; ?>
              <th scope="col" class="num">Secured Marks</th>
              <?php if ($hasGradeColumn): ?><th scope="col" class="num">Grade</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($marks as $mark): ?>
              <tr>
                <td class="marks-table__subject" data-label="Subject"><?php echo e($mark['subject']); ?></td>
                <?php if ($hasMaxColumn): ?>
                  <td class="num" data-label="Maximum Marks"><?php echo $mark['max'] === null ? '&ndash;' : e(npr_num($mark['max'])); ?></td>
                <?php endif; ?>
                <td class="num" data-label="Secured Marks"><?php echo $mark['secured'] === null || $mark['secured'] === '' ? '&ndash;' : e(npr_num($mark['secured'])); ?></td>
                <?php if ($hasGradeColumn): ?>
                  <td class="num" data-label="Grade"><?php echo $mark['grade'] === '' ? '&ndash;' : e($mark['grade']); ?></td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <?php if ($totalMax !== null || $totalSecured !== null): ?>
            <tfoot>
              <tr>
                <th scope="row" class="marks-table__subject">Total</th>
                <?php if ($hasMaxColumn): ?>
                  <td class="num" data-label="Maximum Marks"><?php echo $totalMax === null ? '&ndash;' : e(npr_num($totalMax)); ?></td>
                <?php endif; ?>
                <td class="num" data-label="Secured Marks"><?php echo $totalSecured === null ? '&ndash;' : e(npr_num($totalSecured)); ?></td>
                <?php if ($hasGradeColumn): ?><td class="num"></td><?php endif; ?>
              </tr>
            </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </section>
  <?php endif; ?>

  <section class="panel">
    <h2 class="panel__title">Result Summary</h2>
    <div class="summary">
      <ul class="summary__figures">
        <?php if ($totalMax !== null && $totalMax !== ''): ?>
          <li><span>Maximum Marks</span><strong><?php echo e(npr_num($totalMax)); ?></strong></li>
        <?php endif; ?>
        <?php if ($totalSecured !== null && $totalSecured !== ''): ?>
          <li><span>Total Secured</span><strong><?php echo e(npr_num($totalSecured)); ?></strong></li>
        <?php endif; ?>
        <?php if ($student['percentage'] !== null && $student['percentage'] !== ''): ?>
          <li><span>Percentage</span><strong><?php echo e(number_format((float) $student['percentage'], 2)); ?>%</strong></li>
        <?php endif; ?>
        <?php if (!empty($student['division'])): ?>
          <li><span>Division</span><strong><?php echo e(strtoupper($student['division'])); ?></strong></li>
        <?php endif; ?>
      </ul>

      <?php if ($status !== ''): ?>
        <div class="verdict verdict--<?php echo e($statusClass); ?>">
          <span class="verdict__label">Result</span>
          <span class="verdict__value"><?php echo e($status); ?></span>
          <?php if (!empty($student['division'])): ?>
            <span class="verdict__division"><?php echo e(strtoupper($student['division'])); ?></span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($student['remarks'])): ?>
      <p class="summary__remarks"><strong>Remarks:</strong> <?php echo e($student['remarks']); ?></p>
    <?php endif; ?>
  </section>

  <p class="marksheet__disclaimer">
    This is a computer-generated statement of marks published online and does not require a signature.
    It is provisional; the original marksheet issued by
    <?php echo e($result['board_university'] !== '' && $result['board_university'] !== null ? $result['board_university'] : $result['institution_name']); ?>
    is the authoritative document.
  </p>
</article>

<div class="marksheet-actions no-print">
  <button type="button" class="btn btn--primary" id="npr-print">Print Result</button>
  <button type="button" class="btn btn--ghost" id="npr-save">Save as PDF</button>
</div>

<script src="<?php echo e(npr_url('public/assets/js/result.js')); ?>?v=1" defer></script>
