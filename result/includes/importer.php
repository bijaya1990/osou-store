<?php
/**
 * Column mapping, validation and database import for uploaded result files.
 *
 * Nothing here invents data: totals and percentages are only derived when the
 * administrator explicitly asks for it on the mapping screen, and divisions are
 * never calculated — they come from the file or stay empty.
 */

/**
 * Mapping targets offered on the column-mapping screen.
 * "group" is used purely for the optgroup labels.
 */
function npr_import_fields()
{
    return array(
        'ignore'              => array('label' => '— Do not import —', 'group' => ''),
        'roll_number'         => array('label' => 'Examination Roll Number *', 'group' => 'Candidate'),
        'registration_number' => array('label' => 'Registration Number', 'group' => 'Candidate'),
        'student_name'        => array('label' => 'Student Name', 'group' => 'Candidate'),
        'father_name'         => array("label" => "Father's Name", 'group' => 'Candidate'),
        'mother_name'         => array("label" => "Mother's Name", 'group' => 'Candidate'),
        'date_of_birth'       => array('label' => 'Date of Birth', 'group' => 'Candidate'),
        'subject_secured'     => array('label' => 'Subject — Secured Marks', 'group' => 'Subject-wise marks'),
        'subject_max'         => array('label' => 'Subject — Maximum Marks', 'group' => 'Subject-wise marks'),
        'subject_grade'       => array('label' => 'Subject — Grade', 'group' => 'Subject-wise marks'),
        'maximum_marks'       => array('label' => 'Total Maximum Marks', 'group' => 'Totals'),
        'secured_marks'       => array('label' => 'Total Secured Marks', 'group' => 'Totals'),
        'total_marks'         => array('label' => 'Total (as printed)', 'group' => 'Totals'),
        'percentage'          => array('label' => 'Percentage', 'group' => 'Totals'),
        'division'            => array('label' => 'Division / Class', 'group' => 'Outcome'),
        'result_status'       => array('label' => 'Result (Pass / Fail / …)', 'group' => 'Outcome'),
        'remarks'             => array('label' => 'Remarks', 'group' => 'Outcome'),
        'extra'               => array('label' => 'Extra detail (shown as-is)', 'group' => 'Other'),
    );
}

/**
 * Fields that may only be mapped to a single column.
 */
function npr_single_use_fields()
{
    return array(
        'roll_number', 'registration_number', 'student_name', 'father_name', 'mother_name',
        'date_of_birth', 'maximum_marks', 'secured_marks', 'total_marks', 'percentage',
        'division', 'result_status', 'remarks',
    );
}

/**
 * Suggest a mapping for each column heading. The administrator can override
 * every suggestion before importing.
 */
function npr_guess_mapping(array $header)
{
    $patterns = array(
        'roll_number'         => '/^(exam(ination)?\s*)?roll\s*(no|num|number)?\.?$|^roll$|^rollno$/i',
        'registration_number' => '/^(reg(d|n|istration)?\.?\s*(no|num|number)?\.?)$|^regd?no$|^enroll?ment\s*(no|number)?$/i',
        'student_name'        => '/^(student|candidate|name of (the )?(student|candidate))\s*(name)?$|^name$/i',
        'father_name'         => '/father/i',
        'mother_name'         => '/mother/i',
        'date_of_birth'       => '/^(d\.?o\.?b\.?|date of birth)$/i',
        'maximum_marks'       => '/^(total\s*)?(max(imum)?)\s*(marks|mark)?$|^full\s*marks$/i',
        'secured_marks'       => '/^(total\s*)?(secured|obtained|obt)\.?\s*(marks|mark)?$/i',
        'total_marks'         => '/^total(\s*marks)?$|^grand\s*total$/i',
        'percentage'          => '/^(percentage|percent|%|per\s*cent|pct)$/i',
        'division'            => '/^(division|class|grade\s*division|div)$/i',
        'result_status'       => '/^(result|result\s*status|status|remark\s*result)$/i',
        'remarks'             => '/^(remarks?|note|comment)s?$/i',
    );

    $used = array();
    $mapping = array();

    foreach ($header as $index => $label) {
        $label = trim((string) $label);
        $field = 'ignore';
        $subject = '';

        foreach ($patterns as $candidate => $pattern) {
            if (isset($used[$candidate])) {
                continue;
            }
            if (preg_match($pattern, $label)) {
                $field = $candidate;
                $used[$candidate] = true;
                break;
            }
        }

        // "English Max" / "Odia (Max Marks)" style pairs.
        if ($field === 'ignore' && preg_match('/^(.*?)[\s\-_\(\[]*(max(imum)?)\s*(marks|mark)?[\)\]]?$/i', $label, $m) && trim($m[1]) !== '') {
            $field = 'subject_max';
            $subject = trim($m[1], " \t-_()[]");
        } elseif ($field === 'ignore' && preg_match('/^(.*?)[\s\-_\(\[]*(grade)[\)\]]?$/i', $label, $m) && trim($m[1]) !== '') {
            $field = 'subject_grade';
            $subject = trim($m[1], " \t-_()[]");
        } elseif ($field === 'ignore' && $label !== '') {
            // Anything left over is most likely a subject column.
            $field = 'subject_secured';
            $subject = preg_replace('/[\s\-_\(\[]*(marks?|secured|obtained|obt)\.?[\)\]]?$/i', '', $label);
            $subject = trim($subject, " \t-_()[]");
            if ($subject === '') {
                $subject = $label;
            }
        }

        $mapping[$index] = array(
            'field'   => $field,
            'subject' => $subject !== '' ? $subject : ($field === 'subject_secured' ? $label : ''),
            'max'     => '',
        );
    }

    return $mapping;
}

/**
 * Clean a posted mapping into the canonical shape.
 */
function npr_sanitise_mapping(array $posted, array $header)
{
    $fields = npr_import_fields();
    $mapping = array();

    foreach ($header as $index => $label) {
        $row = isset($posted[$index]) && is_array($posted[$index]) ? $posted[$index] : array();
        $field = isset($row['field']) ? (string) $row['field'] : 'ignore';
        if (!isset($fields[$field])) {
            $field = 'ignore';
        }
        $subject = isset($row['subject']) ? trim((string) $row['subject']) : '';
        if (in_array($field, array('subject_secured', 'subject_max', 'subject_grade'), true) && $subject === '') {
            $subject = trim((string) $label);
        }
        $mapping[$index] = array(
            'field'   => $field,
            'subject' => substr($subject, 0, 120),
            'max'     => isset($row['max']) ? trim((string) $row['max']) : '',
        );
    }

    return $mapping;
}

/**
 * Structural problems with a mapping (as opposed to problems with the data).
 *
 * @return array list of error strings
 */
function npr_validate_mapping(array $mapping)
{
    $errors = array();
    $counts = array();

    foreach ($mapping as $column) {
        $field = $column['field'];
        if ($field === 'ignore') {
            continue;
        }
        if (!isset($counts[$field])) {
            $counts[$field] = 0;
        }
        $counts[$field]++;
    }

    if (empty($counts['roll_number'])) {
        $errors[] = 'One column must be mapped to “Examination Roll Number”.';
    }

    $fields = npr_import_fields();
    foreach (npr_single_use_fields() as $field) {
        if (isset($counts[$field]) && $counts[$field] > 1) {
            $errors[] = 'Only one column can be mapped to “' . $fields[$field]['label'] . '”.';
        }
    }

    // Subject max/grade columns need a matching secured-marks column.
    $secured = array();
    foreach ($mapping as $column) {
        if ($column['field'] === 'subject_secured') {
            $secured[npr_subject_key($column['subject'])] = true;
        }
    }
    foreach ($mapping as $index => $column) {
        if (in_array($column['field'], array('subject_max', 'subject_grade'), true)) {
            if (!isset($secured[npr_subject_key($column['subject'])])) {
                $errors[] = 'Column ' . ($index + 1) . ' gives ' . ($column['field'] === 'subject_max' ? 'maximum marks' : 'a grade')
                    . ' for “' . $column['subject'] . '”, but no column holds that subject’s secured marks. '
                    . 'Use the same subject name on both columns.';
            }
        }
    }

    return array_values(array_unique($errors));
}

function npr_subject_key($subject)
{
    return strtolower(preg_replace('/\s+/u', ' ', trim((string) $subject)));
}

/**
 * Turn raw rows plus a mapping into student records and a validation report.
 *
 * @param array $options  derive_totals (bool), derive_percentage (bool),
 *                        duplicates ('skip'|'update'), result_id (int|0)
 * @return array{records:array, report:array}
 */
function npr_prepare_import(array $header, array $rows, array $mapping, array $options)
{
    $deriveTotals = !empty($options['derive_totals']);
    $derivePercentage = !empty($options['derive_percentage']);
    $resultId = isset($options['result_id']) ? (int) $options['result_id'] : 0;

    $report = array(
        'total_rows'        => count($rows),
        'valid_rows'        => 0,
        'invalid_rows'      => 0,
        'missing_roll'      => 0,
        'duplicate_in_file' => 0,
        'existing_in_db'    => 0,
        'errors'            => array(),
        'warnings'          => array(),
    );

    $existing = array();
    if ($resultId > 0) {
        $stmt = npr_query(
            'SELECT roll_number_key FROM `' . npr_table('result_students') . '` WHERE result_id = ?',
            array($resultId)
        );
        while (($key = $stmt->fetchColumn()) !== false) {
            $existing[$key] = true;
        }
    }

    $records = array();
    $seen = array();

    foreach ($rows as $offset => $row) {
        $lineNumber = $offset + 2; // +1 for the header, +1 for 1-based numbering
        $rowErrors = array();

        $record = array(
            'roll_number'         => '',
            'registration_number' => '',
            'student_name'        => '',
            'father_name'         => '',
            'mother_name'         => '',
            'date_of_birth'       => '',
            'maximum_marks'       => null,
            'secured_marks'       => null,
            'total_marks'         => null,
            'percentage'          => null,
            'division'            => '',
            'result_status'       => '',
            'remarks'             => '',
        );

        $subjects = array();   // subject key => [subject, secured, max, grade]
        $extras = array();

        foreach ($mapping as $index => $column) {
            $field = $column['field'];
            if ($field === 'ignore') {
                continue;
            }
            $value = isset($row[$index]) ? trim((string) $row[$index]) : '';

            switch ($field) {
                case 'subject_secured':
                case 'subject_max':
                case 'subject_grade':
                    $key = npr_subject_key($column['subject']);
                    if ($key === '') {
                        break;
                    }
                    if (!isset($subjects[$key])) {
                        $subjects[$key] = array(
                            'subject' => $column['subject'],
                            'secured' => null,
                            'max'     => null,
                            'grade'   => '',
                            'raw'     => '',
                        );
                    }
                    if ($field === 'subject_secured') {
                        $number = npr_parse_number($value);
                        $subjects[$key]['secured'] = $number;
                        $subjects[$key]['raw'] = $value;
                        if ($number === null && $value !== '') {
                            // Keep non-numeric markers such as "AB" visible on the marksheet.
                            $subjects[$key]['grade'] = $subjects[$key]['grade'] !== '' ? $subjects[$key]['grade'] : $value;
                        }
                        if ($subjects[$key]['max'] === null && $column['max'] !== '') {
                            $subjects[$key]['max'] = npr_parse_number($column['max']);
                        }
                    } elseif ($field === 'subject_max') {
                        $subjects[$key]['max'] = npr_parse_number($value);
                    } else {
                        $subjects[$key]['grade'] = $value;
                    }
                    break;

                case 'maximum_marks':
                case 'secured_marks':
                case 'total_marks':
                case 'percentage':
                    $number = npr_parse_number($value);
                    if ($number === null && $value !== '') {
                        $rowErrors[] = '“' . $header[$index] . '” is not a number (“' . $value . '”)';
                    }
                    $record[$field] = $number;
                    break;

                case 'result_status':
                    $record['result_status'] = npr_normalise_status($value);
                    break;

                case 'extra':
                    if ($value !== '') {
                        $extras[$header[$index]] = $value;
                    }
                    break;

                default:
                    $record[$field] = $value;
                    break;
            }
        }

        // Static per-subject maximum marks typed on the mapping screen.
        foreach ($mapping as $column) {
            if ($column['field'] !== 'subject_secured' || $column['max'] === '') {
                continue;
            }
            $key = npr_subject_key($column['subject']);
            if (isset($subjects[$key]) && $subjects[$key]['max'] === null) {
                $subjects[$key]['max'] = npr_parse_number($column['max']);
            }
        }

        $record['roll_number'] = substr(trim($record['roll_number']), 0, 64);
        $rollKey = npr_roll_key($record['roll_number']);

        if ($rollKey === '') {
            $report['missing_roll']++;
            $rowErrors[] = 'roll number is missing';
        } elseif (isset($seen[$rollKey])) {
            $report['duplicate_in_file']++;
            $rowErrors[] = 'duplicate roll number “' . $record['roll_number'] . '” (also on row ' . $seen[$rollKey] . ')';
        }

        if ($rollKey !== '' && isset($existing[$rollKey])) {
            $report['existing_in_db']++;
        }

        $marks = array();
        foreach ($subjects as $subject) {
            if ($subject['secured'] === null && $subject['max'] === null && $subject['grade'] === '') {
                continue;
            }
            $marks[] = array(
                'subject' => $subject['subject'],
                'max'     => $subject['max'],
                'secured' => $subject['secured'] !== null ? $subject['secured'] : ($subject['raw'] !== '' && !is_numeric($subject['raw']) ? $subject['raw'] : null),
                'grade'   => $subject['grade'],
            );
        }

        // Derived totals — only when the administrator asked for them.
        if ($deriveTotals && $marks) {
            $sumSecured = null;
            $sumMax = null;
            foreach ($marks as $mark) {
                if (is_numeric($mark['secured'])) {
                    $sumSecured = ($sumSecured === null ? 0 : $sumSecured) + (float) $mark['secured'];
                }
                if (is_numeric($mark['max'])) {
                    $sumMax = ($sumMax === null ? 0 : $sumMax) + (float) $mark['max'];
                }
            }
            if ($record['secured_marks'] === null && $sumSecured !== null) {
                $record['secured_marks'] = $sumSecured;
            }
            if ($record['maximum_marks'] === null && $sumMax !== null) {
                $record['maximum_marks'] = $sumMax;
            }
        }

        if ($record['total_marks'] === null && $record['secured_marks'] !== null) {
            $record['total_marks'] = $record['secured_marks'];
        }
        if ($record['secured_marks'] === null && $record['total_marks'] !== null) {
            $record['secured_marks'] = $record['total_marks'];
        }

        if ($derivePercentage
            && $record['percentage'] === null
            && $record['secured_marks'] !== null
            && $record['maximum_marks'] !== null
            && (float) $record['maximum_marks'] > 0
        ) {
            $record['percentage'] = round(((float) $record['secured_marks'] / (float) $record['maximum_marks']) * 100, 2);
        }

        if ($record['percentage'] !== null && ($record['percentage'] < 0 || $record['percentage'] > 100)) {
            $report['warnings'][] = 'Row ' . $lineNumber . ': percentage of ' . npr_num($record['percentage']) . '% looks unusual.';
        }
        if ($record['secured_marks'] !== null && $record['maximum_marks'] !== null
            && (float) $record['secured_marks'] > (float) $record['maximum_marks']
        ) {
            $report['warnings'][] = 'Row ' . $lineNumber . ': secured marks are higher than maximum marks.';
        }

        if ($rowErrors) {
            $report['invalid_rows']++;
            if (count($report['errors']) < 100) {
                $report['errors'][] = 'Row ' . $lineNumber . ': ' . implode('; ', $rowErrors) . '.';
            }
            continue;
        }

        $seen[$rollKey] = $lineNumber;
        $report['valid_rows']++;

        $record['roll_number_key'] = $rollKey;
        $record['marks_data'] = $marks ? json_encode($marks, JSON_UNESCAPED_UNICODE) : null;
        $record['extra_data'] = $extras ? json_encode($extras, JSON_UNESCAPED_UNICODE) : null;
        $record['line'] = $lineNumber;

        $records[] = $record;
    }

    if (count($report['warnings']) > 25) {
        $extra = count($report['warnings']) - 25;
        $report['warnings'] = array_slice($report['warnings'], 0, 25);
        $report['warnings'][] = '…and ' . $extra . ' more similar warnings.';
    }

    return array('records' => $records, 'report' => $report);
}

/**
 * Write prepared records into the database inside a transaction.
 *
 * @param string $mode 'replace' | 'update' | 'skip'
 * @return array{inserted:int, updated:int, skipped:int}
 */
function npr_commit_import($resultId, array $records, $mode)
{
    $resultId = (int) $resultId;
    $pdo = npr_db();
    $table = npr_table('result_students');
    $now = npr_now();

    $summary = array('inserted' => 0, 'updated' => 0, 'skipped' => 0);

    $pdo->beginTransaction();
    try {
        if ($mode === 'replace') {
            npr_query('DELETE FROM `' . $table . '` WHERE result_id = ?', array($resultId));
        }

        $existing = array();
        if ($mode !== 'replace') {
            $stmt = npr_query('SELECT roll_number_key, id FROM `' . $table . '` WHERE result_id = ?', array($resultId));
            foreach ($stmt->fetchAll() as $row) {
                $existing[$row['roll_number_key']] = (int) $row['id'];
            }
        }

        $insert = $pdo->prepare(
            'INSERT INTO `' . $table . '`
             (result_id, roll_number, roll_number_key, registration_number, student_name, father_name, mother_name,
              date_of_birth, marks_data, extra_data, maximum_marks, secured_marks, total_marks, percentage,
              division, result_status, remarks, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );

        $update = $pdo->prepare(
            'UPDATE `' . $table . '` SET
                roll_number = ?, registration_number = ?, student_name = ?, father_name = ?, mother_name = ?,
                date_of_birth = ?, marks_data = ?, extra_data = ?, maximum_marks = ?, secured_marks = ?,
                total_marks = ?, percentage = ?, division = ?, result_status = ?, remarks = ?, updated_at = ?
             WHERE id = ?'
        );

        foreach ($records as $record) {
            $values = array(
                $record['registration_number'] !== '' ? substr($record['registration_number'], 0, 64) : null,
                $record['student_name'] !== '' ? substr($record['student_name'], 0, 190) : null,
                $record['father_name'] !== '' ? substr($record['father_name'], 0, 190) : null,
                $record['mother_name'] !== '' ? substr($record['mother_name'], 0, 190) : null,
                $record['date_of_birth'] !== '' ? substr($record['date_of_birth'], 0, 40) : null,
                $record['marks_data'],
                $record['extra_data'],
                $record['maximum_marks'],
                $record['secured_marks'],
                $record['total_marks'],
                $record['percentage'],
                $record['division'] !== '' ? substr($record['division'], 0, 60) : null,
                $record['result_status'] !== '' ? substr($record['result_status'], 0, 40) : null,
                $record['remarks'] !== '' ? substr($record['remarks'], 0, 255) : null,
            );

            $key = $record['roll_number_key'];

            if (isset($existing[$key])) {
                if ($mode === 'skip') {
                    $summary['skipped']++;
                    continue;
                }
                $params = array_merge(array($record['roll_number']), $values, array($now, $existing[$key]));
                $update->execute($params);
                $summary['updated']++;
                continue;
            }

            $params = array_merge(
                array($resultId, $record['roll_number'], $key),
                $values,
                array($now, $now)
            );
            $insert->execute($params);
            $existing[$key] = (int) $pdo->lastInsertId();
            $summary['inserted']++;
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

    return $summary;
}

/**
 * Store an uploaded spreadsheet in a private staging folder and return its token.
 */
function npr_stage_upload($tmpPath, $ext)
{
    $dir = NPR_UPLOAD_PATH . '/tmp';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $token = bin2hex(random_bytes(16));
    $target = $dir . '/' . $token . '.' . ($ext === 'xlsx' ? 'xlsx' : 'csv');

    if (!@move_uploaded_file($tmpPath, $target)) {
        return '';
    }
    @chmod($target, 0644);
    npr_cleanup_staged_uploads();

    return basename($target);
}

/**
 * Resolve a staged file token to an absolute path, or '' when unknown.
 */
function npr_staged_path($token)
{
    $token = (string) $token;
    if (!preg_match('/^[a-f0-9]{32}\.(csv|xlsx)$/', $token)) {
        return '';
    }
    $path = NPR_UPLOAD_PATH . '/tmp/' . $token;
    return is_file($path) ? $path : '';
}

/**
 * Remove staged uploads older than six hours.
 */
function npr_cleanup_staged_uploads()
{
    $dir = NPR_UPLOAD_PATH . '/tmp';
    if (!is_dir($dir)) {
        return;
    }
    $cutoff = time() - 21600;
    foreach ((array) glob($dir . '/*') as $file) {
        if (is_file($file) && basename($file) !== '.htaccess' && filemtime($file) < $cutoff) {
            @unlink($file);
        }
    }
}
