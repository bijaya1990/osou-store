<?php
/**
 * Shared helpers: URLs, formatting, slugs, result queries, flash messages.
 */

/**
 * Build an absolute URL inside the result system.
 */
function npr_url($path = '')
{
    $path = ltrim((string) $path, '/');
    return rtrim(NPR_BASE_URL, '/') . ($path === '' ? '/' : '/' . $path);
}

/**
 * Public URL of a single result page.
 */
function npr_result_url($slug)
{
    return npr_url(rawurlencode((string) $slug) . '/');
}

/**
 * Public URL of an uploaded institution logo.
 */
function npr_logo_url($file)
{
    return npr_url('uploads/logos/' . rawurlencode((string) $file));
}

/**
 * Redirect and stop.
 */
function npr_redirect($url)
{
    if (!headers_sent()) {
        header('Location: ' . $url);
    }
    echo '<p>Continue to <a href="' . e($url) . '">' . e($url) . '</a></p>';
    exit;
}

/**
 * Current timestamp in MySQL DATETIME format.
 */
function npr_now()
{
    return date('Y-m-d H:i:s');
}

/**
 * Turn any string into a URL-safe slug.
 */
function npr_slugify($text)
{
    $text = (string) $text;
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        if ($converted !== false) {
            $text = $converted;
        }
    }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim((string) $text, '-');
    $text = preg_replace('/-{2,}/', '-', $text);
    return substr((string) $text, 0, 180);
}

/**
 * Make a slug unique within the results table.
 */
function npr_unique_slug($slug, $ignoreId = 0)
{
    $slug = npr_slugify($slug);
    if ($slug === '') {
        $slug = 'result-' . date('Y-m-d');
    }

    $base = $slug;
    $suffix = 1;
    while (true) {
        $exists = npr_fetch_value(
            'SELECT id FROM `' . npr_table('results') . '` WHERE slug = ? AND id <> ? LIMIT 1',
            array($slug, (int) $ignoreId)
        );
        if ($exists === null) {
            return $slug;
        }
        $suffix++;
        $slug = substr($base, 0, 170) . '-' . $suffix;
    }
}

/**
 * Normalise a roll number for lookup: trim, uppercase, strip spaces/dashes.
 * The original value is stored separately and shown to the student as typed
 * by the institution.
 */
function npr_roll_key($roll)
{
    $roll = strtoupper(trim((string) $roll));
    $roll = preg_replace('/[\s\-\/\.]+/', '', $roll);
    return substr((string) $roll, 0, 64);
}

/**
 * Recognised result statuses.
 */
function npr_result_statuses()
{
    return array('PASS', 'FAIL', 'ABSENT', 'WITHHELD', 'COMPARTMENT', 'OTHER');
}

/**
 * Map free-text status from a spreadsheet onto a known status.
 * Anything unrecognised is preserved verbatim rather than guessed at.
 */
function npr_normalise_status($value)
{
    $raw = trim((string) $value);
    if ($raw === '') {
        return '';
    }

    $upper = strtoupper($raw);
    $compact = preg_replace('/[^A-Z]/', '', $upper);

    $aliases = array(
        'PASS'        => array('PASS', 'PASSED', 'P', 'QUALIFIED', 'PROMOTED'),
        'FAIL'        => array('FAIL', 'FAILED', 'F', 'NOTQUALIFIED', 'UNSUCCESSFUL'),
        'ABSENT'      => array('ABSENT', 'AB', 'A', 'NOTAPPEARED'),
        'WITHHELD'    => array('WITHHELD', 'WITHELD', 'HELD', 'RESULTWITHHELD'),
        'COMPARTMENT' => array('COMPARTMENT', 'COMPT', 'COMP', 'BACK', 'SUPPLEMENTARY', 'SUPPLE', 'BACKPAPER'),
    );

    foreach ($aliases as $status => $variants) {
        if (in_array($compact, $variants, true)) {
            return $status;
        }
    }

    return $upper;
}

/**
 * CSS modifier for a result status badge.
 */
function npr_status_class($status)
{
    switch (strtoupper((string) $status)) {
        case 'PASS':
            return 'is-pass';
        case 'FAIL':
            return 'is-fail';
        case 'ABSENT':
            return 'is-absent';
        case 'WITHHELD':
            return 'is-withheld';
        case 'COMPARTMENT':
            return 'is-compartment';
        default:
            return 'is-other';
    }
}

/**
 * Format a number for display: drop trailing ".00", keep real decimals.
 */
function npr_num($value)
{
    if ($value === null || $value === '') {
        return '';
    }
    if (!is_numeric($value)) {
        return (string) $value;
    }
    $float = (float) $value;
    if (abs($float - round($float)) < 0.00001) {
        return (string) (int) round($float);
    }
    return rtrim(rtrim(number_format($float, 2, '.', ''), '0'), '.');
}

/**
 * Format a date for display, tolerating empty values.
 */
function npr_date($date, $format = 'd F Y')
{
    if (empty($date) || $date === '0000-00-00') {
        return '';
    }
    $ts = strtotime((string) $date);
    return $ts ? date($format, $ts) : (string) $date;
}

/**
 * Parse a value from a spreadsheet cell into a float, or null when not numeric.
 * Handles "78", "78.5", " 78 ", "78/100" (takes 78) and returns null for
 * markers such as "AB", "-", "NA".
 */
function npr_parse_number($value)
{
    if ($value === null) {
        return null;
    }
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }
    if (strpos($value, '/') !== false) {
        $value = trim(substr($value, 0, strpos($value, '/')));
    }
    $value = str_replace(array(',', ' '), '', $value);
    if (!is_numeric($value)) {
        return null;
    }
    return (float) $value;
}

/**
 * Decode the JSON marks payload into a list of subject rows.
 */
function npr_decode_marks($json)
{
    if (empty($json)) {
        return array();
    }
    $data = json_decode((string) $json, true);
    if (!is_array($data)) {
        return array();
    }
    $rows = array();
    foreach ($data as $row) {
        if (!is_array($row) || !isset($row['subject'])) {
            continue;
        }
        $rows[] = array(
            'subject' => (string) $row['subject'],
            'max'     => isset($row['max']) && $row['max'] !== '' ? $row['max'] : null,
            'secured' => isset($row['secured']) && $row['secured'] !== '' ? $row['secured'] : null,
            'grade'   => isset($row['grade']) ? (string) $row['grade'] : '',
            'remarks' => isset($row['remarks']) ? (string) $row['remarks'] : '',
        );
    }
    return $rows;
}

/**
 * Decode the free-form extra columns payload (label => value).
 */
function npr_decode_extra($json)
{
    if (empty($json)) {
        return array();
    }
    $data = json_decode((string) $json, true);
    return is_array($data) ? $data : array();
}

/**
 * Validate an administrator-supplied external result URL.
 * Only plain http(s) links to a real host are accepted — javascript:, data:,
 * file: and malformed values are rejected outright.
 *
 * @return array{ok:bool, url:string, error:string}
 */
function npr_validate_external_url($url)
{
    $url = trim((string) $url);

    if ($url === '') {
        return array('ok' => false, 'url' => '', 'error' => 'The external result URL is required for an external result.');
    }
    if (strlen($url) > 500) {
        return array('ok' => false, 'url' => '', 'error' => 'The external result URL is too long (maximum 500 characters).');
    }
    // Control characters and whitespace are never legitimate inside a URL.
    if (preg_match('/[\x00-\x20\x7F<>"\'\\\\]/', $url)) {
        return array('ok' => false, 'url' => '', 'error' => 'The external result URL contains characters that are not allowed.');
    }
    // Add a scheme when the administrator typed a bare domain.
    if (!preg_match('~^[a-z][a-z0-9+.\-]*://~i', $url)) {
        if (preg_match('~^[a-z][a-z0-9+.\-]*:~i', $url)) {
            return array('ok' => false, 'url' => '', 'error' => 'Only http:// and https:// links are allowed.');
        }
        $url = 'https://' . $url;
    }

    $parts = parse_url($url);
    if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
        return array('ok' => false, 'url' => '', 'error' => 'That does not look like a valid web address.');
    }

    $scheme = strtolower($parts['scheme']);
    if ($scheme !== 'http' && $scheme !== 'https') {
        return array('ok' => false, 'url' => '', 'error' => 'Only http:// and https:// links are allowed.');
    }

    $host = $parts['host'];
    if (!preg_match('/^(\[[0-9a-f:]+\]|[a-z0-9\-._~%]+)$/i', $host) || strpos($host, '.') === false) {
        return array('ok' => false, 'url' => '', 'error' => 'The external result URL must point at a full domain name.');
    }

    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        return array('ok' => false, 'url' => '', 'error' => 'That does not look like a valid web address.');
    }

    return array('ok' => true, 'url' => $url, 'error' => '');
}

/**
 * True when a result row points at an external website.
 */
function npr_is_external($result)
{
    return isset($result['result_type']) && $result['result_type'] === 'external';
}

/**
 * The link a student should follow for a result: the internal result page,
 * or the configured external website.
 */
function npr_result_link($result)
{
    if (npr_is_external($result) && !empty($result['external_url'])) {
        return (string) $result['external_url'];
    }
    return npr_result_url($result['slug']);
}

/**
 * The label of the call-to-action button for a result.
 */
function npr_result_button_text($result)
{
    $text = isset($result['external_button_text']) ? trim((string) $result['external_button_text']) : '';
    if (npr_is_external($result) && $text !== '') {
        return $text;
    }
    return 'Check Result';
}

/**
 * Published results for the homepage ticker, newest first.
 */
function npr_ticker_results($limit = 12)
{
    $limit = max(1, min(50, (int) $limit));
    return npr_fetch_all(
        'SELECT id, result_title, institution_name, examination_name, academic_session, slug, result_date,
                published_at, result_type, external_url, external_button_text
           FROM `' . npr_table('results') . '`
          WHERE status = \'published\' AND show_on_ticker = 1
       ORDER BY COALESCE(published_at, created_at) DESC, id DESC
          LIMIT ' . $limit
    );
}

/**
 * All published results, newest first (used by the result system landing page).
 */
function npr_published_results($limit = 50)
{
    $limit = max(1, min(200, (int) $limit));
    return npr_fetch_all(
        'SELECT * FROM `' . npr_table('results') . '`
          WHERE status = \'published\'
       ORDER BY COALESCE(published_at, created_at) DESC, id DESC
          LIMIT ' . $limit
    );
}

/**
 * Load one published result by slug, or null.
 */
function npr_find_published_result($slug)
{
    return npr_fetch_one(
        'SELECT * FROM `' . npr_table('results') . '` WHERE slug = ? AND status = \'published\' LIMIT 1',
        array((string) $slug)
    );
}

/**
 * Load a result by id regardless of status (admin side).
 */
function npr_find_result($id)
{
    return npr_fetch_one(
        'SELECT * FROM `' . npr_table('results') . '` WHERE id = ? LIMIT 1',
        array((int) $id)
    );
}

/**
 * Number of imported students for a result.
 */
function npr_student_count($resultId)
{
    return (int) npr_fetch_value(
        'SELECT COUNT(*) FROM `' . npr_table('result_students') . '` WHERE result_id = ?',
        array((int) $resultId),
        0
    );
}

/**
 * Find one student inside one result by roll number.
 */
function npr_find_student($resultId, $roll)
{
    $key = npr_roll_key($roll);
    if ($key === '') {
        return null;
    }
    return npr_fetch_one(
        'SELECT * FROM `' . npr_table('result_students') . '`
          WHERE result_id = ? AND roll_number_key = ? LIMIT 1',
        array((int) $resultId, $key)
    );
}

/**
 * Light per-visitor throttle on result lookups, so the roll-number form cannot
 * be used to walk the whole database. Allows 40 lookups per 10 minutes.
 */
function npr_search_throttled($limit = 40, $window = 600)
{
    npr_session_start();
    $now = time();
    $hits = isset($_SESSION['npr_lookups']) && is_array($_SESSION['npr_lookups']) ? $_SESSION['npr_lookups'] : array();

    $hits = array_values(array_filter($hits, function ($time) use ($now, $window) {
        return ($now - (int) $time) < $window;
    }));

    if (count($hits) >= $limit) {
        $_SESSION['npr_lookups'] = $hits;
        return true;
    }

    $hits[] = $now;
    $_SESSION['npr_lookups'] = $hits;
    return false;
}

/* ---------------------------------------------------------------------------
 * Flash messages
 * ------------------------------------------------------------------------ */

function npr_flash($type, $message)
{
    npr_session_start();
    if (!isset($_SESSION['npr_flash']) || !is_array($_SESSION['npr_flash'])) {
        $_SESSION['npr_flash'] = array();
    }
    $_SESSION['npr_flash'][] = array('type' => $type, 'message' => $message);
}

function npr_take_flashes()
{
    npr_session_start();
    $flashes = isset($_SESSION['npr_flash']) && is_array($_SESSION['npr_flash']) ? $_SESSION['npr_flash'] : array();
    unset($_SESSION['npr_flash']);
    return $flashes;
}

/**
 * Read a trimmed POST string.
 */
function npr_post($key, $default = '')
{
    if (!isset($_POST[$key]) || is_array($_POST[$key])) {
        return $default;
    }
    return trim((string) $_POST[$key]);
}

/**
 * Read a trimmed GET string.
 */
function npr_get($key, $default = '')
{
    if (!isset($_GET[$key]) || is_array($_GET[$key])) {
        return $default;
    }
    return trim((string) $_GET[$key]);
}
