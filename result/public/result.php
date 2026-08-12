<?php
/**
 * Result checking page for a single published result.
 * Expects $result (a row from the results table).
 */
if (!defined('NPR_BOOTSTRAPPED') || empty($result)) {
    exit;
}

npr_session_start();

$rollInput = '';
$student = null;
$searchError = '';
$searched = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['roll_number'])) {
    $searched = true;
    $rollInput = trim((string) $_POST['roll_number']);

    if ($rollInput === '') {
        $searchError = 'Please enter your ' . strtolower($result['roll_label']) . '.';
    } elseif (strlen($rollInput) > 64) {
        $searchError = 'The roll number you entered is too long.';
    } elseif (npr_search_throttled()) {
        $searchError = 'Too many attempts. Please wait a minute and try again.';
    } else {
        $student = npr_find_student($result['id'], $rollInput);
        if (!$student) {
            $searchError = 'Please check your ' . strtolower($result['roll_label']) . ' and try again.';
        }
    }
}

$pageTitle = $result['result_title'] . ' — ' . $result['institution_name'];
$pageDescription = 'Check ' . $result['examination_name'] . ' result of ' . $result['institution_name'] . ' online.';
require __DIR__ . '/layout-top.php';
?>

<section class="exam-head">
  <div class="exam-head__top">
    <?php if (!empty($result['institution_logo'])): ?>
      <img class="exam-head__logo" src="<?php echo e(npr_logo_url($result['institution_logo'])); ?>" alt="">
    <?php endif; ?>
    <div>
      <h1 class="exam-head__institution"><?php echo e($result['institution_name']); ?></h1>
      <?php if (!empty($result['institution_address'])): ?>
        <p class="exam-head__address"><?php echo e($result['institution_address']); ?></p>
      <?php endif; ?>
      <?php if (!empty($result['board_university'])): ?>
        <p class="exam-head__board"><?php echo e($result['board_university']); ?></p>
      <?php endif; ?>
    </div>
  </div>
  <div class="exam-head__bar">
    <p class="exam-head__exam"><?php echo e($result['examination_name']); ?></p>
    <p class="exam-head__title"><?php echo e($result['result_title']); ?></p>
    <p class="exam-head__meta">
      <?php if (!empty($result['class_course'])): ?><span><?php echo e($result['class_course']); ?></span><?php endif; ?>
      <?php if (!empty($result['semester_year'])): ?><span><?php echo e($result['semester_year']); ?></span><?php endif; ?>
      <?php if (!empty($result['academic_session'])): ?><span>Session <?php echo e($result['academic_session']); ?></span><?php endif; ?>
      <?php if (!empty($result['result_date'])): ?><span>Declared on <?php echo e(npr_date($result['result_date'])); ?></span><?php endif; ?>
    </p>
  </div>
</section>

<?php if (!empty($result['description'])): ?>
  <div class="notice"><?php echo nl2br(e($result['description'])); ?></div>
<?php endif; ?>

<?php if ($student): ?>
  <?php require __DIR__ . '/marksheet.php'; ?>

  <div class="after-result no-print">
    <a class="btn btn--ghost" href="<?php echo e(npr_result_url($result['slug'])); ?>">Check another roll number</a>
  </div>
<?php else: ?>
  <section class="search-card" id="check">
    <h2 class="search-card__title">Check Your Result</h2>
    <p class="search-card__hint">Enter your <?php echo e($result['roll_label']); ?> exactly as printed on your admit card.</p>

    <?php if ($searchError !== ''): ?>
      <div class="alert alert--error" role="alert">
        <strong><?php echo $searched && $rollInput !== '' ? 'Result Not Found' : 'Please try again'; ?></strong>
        <span><?php echo e($searchError); ?></span>
      </div>
    <?php endif; ?>

    <form method="post" action="<?php echo e(npr_result_url($result['slug'])); ?>#check" class="search-form" autocomplete="off">
      <label class="search-form__label" for="roll_number"><?php echo e($result['roll_label']); ?></label>
      <input
        class="search-form__input"
        type="text"
        id="roll_number"
        name="roll_number"
        value="<?php echo e($rollInput); ?>"
        inputmode="text"
        maxlength="64"
        autocapitalize="characters"
        spellcheck="false"
        required>
      <button class="btn btn--primary btn--block" type="submit">View Result</button>
    </form>
  </section>
<?php endif; ?>

<?php require __DIR__ . '/layout-bottom.php'; ?>
