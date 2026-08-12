<?php
/**
 * The result form fields, shared by add-result.php and edit-result.php.
 * Expects $form (associative array of current values) and $isEdit (bool).
 */
if (!defined('NPR_BOOTSTRAPPED')) {
    exit;
}
$isEdit = !empty($isEdit);
?>
<fieldset class="fieldset">
  <legend>Result Type</legend>

  <div class="radio-cards" id="npr-result-type">
    <label class="radio-card">
      <input type="radio" name="result_type" value="internal" <?php echo $form['result_type'] !== 'external' ? 'checked' : ''; ?>>
      <span class="radio-card__body">
        <strong>Internal Result</strong>
        <span>You have the student-wise Excel/CSV data. Students search by roll number and see the
              full NaukriPatra marksheet.</span>
      </span>
    </label>
    <label class="radio-card">
      <input type="radio" name="result_type" value="external" <?php echo $form['result_type'] === 'external' ? 'checked' : ''; ?>>
      <span class="radio-card__body">
        <strong>External Result Link</strong>
        <span>The result is already published on an official board / university / school website.
              No Excel file, no student records — the ticker links straight to that website.</span>
      </span>
    </label>
  </div>

  <div class="field-group" data-npr-when="external">
    <label class="field">
      <span class="field__label">External Result URL <em>*</em></span>
      <input class="field__input" type="url" name="external_url" value="<?php echo e($form['external_url']); ?>"
             maxlength="500" placeholder="https://example.gov.in/results">
      <span class="field__help">Only http:// and https:// addresses are accepted.</span>
    </label>
    <label class="field">
      <span class="field__label">Button Text</span>
      <input class="field__input" type="text" name="external_button_text"
             value="<?php echo e($form['external_button_text'] !== '' ? $form['external_button_text'] : 'CHECK RESULT'); ?>" maxlength="60">
    </label>
  </div>
</fieldset>

<fieldset class="fieldset">
  <legend>Institution Information</legend>

  <label class="field">
    <span class="field__label">College / School / Institution Name <em>*</em></span>
    <input class="field__input" type="text" name="institution_name" value="<?php echo e($form['institution_name']); ?>" maxlength="190" required>
  </label>

  <label class="field">
    <span class="field__label">Institution Address <span class="field__opt">(optional)</span></span>
    <input class="field__input" type="text" name="institution_address" value="<?php echo e($form['institution_address']); ?>" maxlength="255">
  </label>

  <div class="field">
    <span class="field__label">Institution Logo <span class="field__opt">(optional, JPG/PNG/GIF/WEBP, max <?php echo (int) round(NPR_MAX_LOGO_BYTES / 1024); ?> KB)</span></span>
    <?php if ($isEdit && !empty($form['institution_logo'])): ?>
      <div class="logo-preview">
        <img src="<?php echo e(npr_logo_url($form['institution_logo'])); ?>" alt="Current logo">
        <label class="check"><input type="checkbox" name="remove_logo" value="1"> Remove current logo</label>
      </div>
    <?php endif; ?>
    <input class="field__input" type="file" name="institution_logo" accept="image/png,image/jpeg,image/gif,image/webp">
  </div>
</fieldset>

<fieldset class="fieldset">
  <legend>Examination Information</legend>

  <label class="field">
    <span class="field__label">Result Title</span>
    <input class="field__input" type="text" name="result_title" value="<?php echo e($form['result_title']); ?>" maxlength="190"
           placeholder="e.g. Annual Examination Result 2026">
    <span class="field__help">Leave blank to build it from the examination name and session.</span>
  </label>

  <label class="field">
    <span class="field__label">Examination Name <em>*</em></span>
    <input class="field__input" type="text" name="examination_name" value="<?php echo e($form['examination_name']); ?>" maxlength="190" required
           placeholder="e.g. Annual Examination 2026">
  </label>

  <div class="field-row">
    <label class="field">
      <span class="field__label">Board / University <span class="field__opt">(optional)</span></span>
      <input class="field__input" type="text" name="board_university" value="<?php echo e($form['board_university']); ?>" maxlength="190">
    </label>
    <label class="field">
      <span class="field__label">Class / Course</span>
      <input class="field__input" type="text" name="class_course" value="<?php echo e($form['class_course']); ?>" maxlength="190"
             placeholder="e.g. +3 1st Year">
    </label>
  </div>

  <div class="field-row">
    <label class="field">
      <span class="field__label">Semester / Year <span class="field__opt">(optional)</span></span>
      <input class="field__input" type="text" name="semester_year" value="<?php echo e($form['semester_year']); ?>" maxlength="120">
    </label>
    <label class="field">
      <span class="field__label">Academic Session</span>
      <input class="field__input" type="text" name="academic_session" value="<?php echo e($form['academic_session']); ?>" maxlength="60"
             placeholder="e.g. 2025-26">
    </label>
  </div>

  <div class="field-row">
    <label class="field" data-npr-when="internal">
      <span class="field__label">Examination Roll Number Label</span>
      <input class="field__input" type="text" name="roll_label" value="<?php echo e($form['roll_label']); ?>" maxlength="100">
      <span class="field__help">Shown above the search box, e.g. “Examination Roll Number”.</span>
    </label>
    <label class="field">
      <span class="field__label">Result Date</span>
      <input class="field__input" type="date" name="result_date" value="<?php echo e($form['result_date']); ?>">
    </label>
  </div>

  <label class="field">
    <span class="field__label">Result Description <span class="field__opt">(optional)</span></span>
    <textarea class="field__input" name="description" rows="3" maxlength="5000"><?php echo e($form['description']); ?></textarea>
    <span class="field__help">Shown as a notice on the result checking page.</span>
  </label>
</fieldset>

<fieldset class="fieldset">
  <legend>Publication Settings</legend>

  <label class="field">
    <span class="field__label">Result Slug</span>
    <input class="field__input" type="text" name="slug" value="<?php echo e($form['slug']); ?>" maxlength="190"
           placeholder="auto-generated from the title">
    <span class="field__help">Public address: <?php echo e(rtrim(NPR_BASE_URL, '/')); ?>/<strong>your-slug</strong>/</span>
  </label>

  <div class="field-row">
    <label class="field">
      <span class="field__label">Status</span>
      <select class="field__input" name="status">
        <option value="draft" <?php echo $form['status'] === 'draft' ? 'selected' : ''; ?>>Draft — not visible to students</option>
        <option value="published" <?php echo $form['status'] === 'published' ? 'selected' : ''; ?>>Published — publicly searchable</option>
      </select>
    </label>
    <label class="field">
      <span class="field__label">Show on Homepage Ticker</span>
      <select class="field__input" name="show_on_ticker">
        <option value="1" <?php echo (int) $form['show_on_ticker'] === 1 ? 'selected' : ''; ?>>Yes</option>
        <option value="0" <?php echo (int) $form['show_on_ticker'] === 0 ? 'selected' : ''; ?>>No</option>
      </select>
      <span class="field__help">The ticker only shows published results.</span>
    </label>
  </div>
</fieldset>
