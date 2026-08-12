<?php
/**
 * Validation and persistence for the "result" record itself
 * (shared by add-result.php and edit-result.php).
 */

/**
 * An empty result row, used to prefill the create form.
 */
function npr_blank_result()
{
    return array(
        'id'                   => 0,
        'result_type'          => 'internal',
        'external_url'         => '',
        'external_button_text' => 'CHECK RESULT',
        'institution_name'    => '',
        'institution_address' => '',
        'institution_logo'    => '',
        'result_title'        => '',
        'examination_name'    => '',
        'board_university'    => '',
        'class_course'        => '',
        'semester_year'       => '',
        'academic_session'    => '',
        'roll_label'          => 'Examination Roll Number',
        'result_date'         => date('Y-m-d'),
        'description'         => '',
        'slug'                => '',
        'status'              => 'draft',
        'show_on_ticker'      => 1,
    );
}

/**
 * Read and validate the posted result form.
 *
 * @return array{data:array, errors:array}
 */
function npr_collect_result_input($existingId = 0)
{
    $errors = array();

    $data = array(
        'result_type'         => npr_post('result_type') === 'external' ? 'external' : 'internal',
        'external_url'        => npr_post('external_url'),
        'external_button_text' => substr(npr_post('external_button_text'), 0, 60),
        'institution_name'    => substr(npr_post('institution_name'), 0, 190),
        'institution_address' => substr(npr_post('institution_address'), 0, 255),
        'result_title'        => substr(npr_post('result_title'), 0, 190),
        'examination_name'    => substr(npr_post('examination_name'), 0, 190),
        'board_university'    => substr(npr_post('board_university'), 0, 190),
        'class_course'        => substr(npr_post('class_course'), 0, 190),
        'semester_year'       => substr(npr_post('semester_year'), 0, 120),
        'academic_session'    => substr(npr_post('academic_session'), 0, 60),
        'roll_label'          => substr(npr_post('roll_label'), 0, 100),
        'result_date'         => npr_post('result_date'),
        'description'         => substr(npr_post('description'), 0, 5000),
        'slug'                => npr_post('slug'),
        'status'              => npr_post('status') === 'published' ? 'published' : 'draft',
        'show_on_ticker'      => npr_post('show_on_ticker') === '1' ? 1 : 0,
    );

    if ($data['institution_name'] === '') {
        $errors[] = 'College / School / Institution name is required.';
    }

    if ($data['result_type'] === 'external') {
        $link = npr_validate_external_url($data['external_url']);
        if (!$link['ok']) {
            $errors[] = $link['error'];
            $data['external_url'] = $data['external_url']; // keep what was typed, for the form
        } else {
            $data['external_url'] = $link['url'];
        }
        if ($data['external_button_text'] === '') {
            $data['external_button_text'] = 'CHECK RESULT';
        }
    } else {
        $data['external_url'] = null;
        $data['external_button_text'] = 'CHECK RESULT';
    }
    if ($data['examination_name'] === '') {
        $errors[] = 'Examination name is required.';
    }
    if ($data['result_title'] === '') {
        // Compose a sensible title rather than rejecting the form.
        $data['result_title'] = trim($data['examination_name'] . ' Result' . ($data['academic_session'] !== '' ? ' ' . $data['academic_session'] : ''));
        if ($data['result_title'] === 'Result') {
            $errors[] = 'Result title is required.';
        }
    }
    if ($data['roll_label'] === '') {
        $data['roll_label'] = 'Examination Roll Number';
    }

    if ($data['result_date'] !== '') {
        $timestamp = strtotime($data['result_date']);
        if ($timestamp === false) {
            $errors[] = 'Result date is not a valid date.';
            $data['result_date'] = null;
        } else {
            $data['result_date'] = date('Y-m-d', $timestamp);
        }
    } else {
        $data['result_date'] = null;
    }

    $slugSource = $data['slug'] !== '' ? $data['slug'] : $data['institution_name'] . '-' . $data['result_title'];
    $data['slug'] = npr_unique_slug($slugSource, $existingId);

    return array('data' => $data, 'errors' => $errors);
}

/**
 * Move an uploaded logo into uploads/logos and return its filename,
 * or '' when no new logo was supplied.
 *
 * @return array{file:string, error:string}
 */
function npr_store_logo($fileField)
{
    $file = isset($_FILES[$fileField]) ? $_FILES[$fileField] : null;
    $check = npr_validate_logo_upload($file);

    if (!$check['ok']) {
        return array('file' => '', 'error' => $check['error']);
    }

    $dir = NPR_UPLOAD_PATH . '/logos';
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return array('file' => '', 'error' => 'The uploads/logos folder could not be created.');
    }

    $name = 'logo-' . date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $check['ext'];
    if (!@move_uploaded_file($check['tmp'], $dir . '/' . $name)) {
        return array('file' => '', 'error' => 'The logo could not be saved.');
    }
    @chmod($dir . '/' . $name, 0644);

    return array('file' => $name, 'error' => '');
}

/**
 * Delete a stored logo file.
 */
function npr_delete_logo($file)
{
    $file = basename((string) $file);
    if ($file === '' || !preg_match('/^logo-[0-9a-z\-]+\.(jpg|png|gif|webp)$/i', $file)) {
        return;
    }
    $path = NPR_UPLOAD_PATH . '/logos/' . $file;
    if (is_file($path)) {
        @unlink($path);
    }
}

/**
 * Insert a new result row and return its id.
 */
function npr_create_result(array $data)
{
    $now = npr_now();
    npr_query(
        'INSERT INTO `' . npr_table('results') . '`
         (result_type, external_url, external_button_text,
          institution_name, institution_address, institution_logo, result_title, examination_name,
          board_university, class_course, semester_year, academic_session, roll_label, result_date,
          description, slug, status, show_on_ticker, published_at, created_at, updated_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
        array(
            $data['result_type'],
            $data['external_url'],
            $data['external_button_text'],
            $data['institution_name'],
            $data['institution_address'],
            isset($data['institution_logo']) ? $data['institution_logo'] : null,
            $data['result_title'],
            $data['examination_name'],
            $data['board_university'],
            $data['class_course'],
            $data['semester_year'],
            $data['academic_session'],
            $data['roll_label'],
            $data['result_date'],
            $data['description'],
            $data['slug'],
            $data['status'],
            $data['show_on_ticker'],
            $data['status'] === 'published' ? $now : null,
            $now,
            $now,
        )
    );

    return (int) npr_db()->lastInsertId();
}

/**
 * Update an existing result row.
 */
function npr_update_result($id, array $data, array $existing)
{
    $id = (int) $id;
    $now = npr_now();

    // Stamp published_at the first time a result goes live.
    $publishedAt = $existing['published_at'];
    if ($data['status'] === 'published' && empty($publishedAt)) {
        $publishedAt = $now;
    }

    npr_query(
        'UPDATE `' . npr_table('results') . '` SET
            result_type = ?, external_url = ?, external_button_text = ?,
            institution_name = ?, institution_address = ?, institution_logo = ?, result_title = ?,
            examination_name = ?, board_university = ?, class_course = ?, semester_year = ?,
            academic_session = ?, roll_label = ?, result_date = ?, description = ?, slug = ?,
            status = ?, show_on_ticker = ?, published_at = ?, updated_at = ?
         WHERE id = ?',
        array(
            $data['result_type'],
            $data['external_url'],
            $data['external_button_text'],
            $data['institution_name'],
            $data['institution_address'],
            isset($data['institution_logo']) ? $data['institution_logo'] : null,
            $data['result_title'],
            $data['examination_name'],
            $data['board_university'],
            $data['class_course'],
            $data['semester_year'],
            $data['academic_session'],
            $data['roll_label'],
            $data['result_date'],
            $data['description'],
            $data['slug'],
            $data['status'],
            $data['show_on_ticker'],
            $publishedAt,
            $now,
            $id,
        )
    );
}
