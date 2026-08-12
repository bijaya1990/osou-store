<?php
/**
 * Escaping, CSRF, session bootstrap and upload validation.
 */

/**
 * HTML-escape a value for output.
 */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Escape for use inside a URL query string.
 */
function eu($value)
{
    return rawurlencode((string) $value);
}

/**
 * Start a hardened session once per request.
 */
function npr_session_start()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params(array(
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ));
    } else {
        session_set_cookie_params(0, '/', '', $secure, true);
    }

    session_name(NPR_SESSION_NAME);
    session_start();
}

/**
 * Current CSRF token, created on first use.
 */
function npr_csrf_token()
{
    npr_session_start();
    if (empty($_SESSION['npr_csrf'])) {
        $_SESSION['npr_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['npr_csrf'];
}

/**
 * Hidden CSRF input for admin forms.
 */
function npr_csrf_field()
{
    return '<input type="hidden" name="npr_csrf" value="' . e(npr_csrf_token()) . '">';
}

/**
 * Validate the CSRF token of the current POST request. Aborts on failure.
 */
function npr_require_csrf()
{
    npr_session_start();
    $sent = isset($_POST['npr_csrf']) ? (string) $_POST['npr_csrf'] : '';
    $known = isset($_SESSION['npr_csrf']) ? (string) $_SESSION['npr_csrf'] : '';

    if ($known === '' || $sent === '' || !hash_equals($known, $sent)) {
        http_response_code(400);
        exit('Security check failed. Please reload the page and try again.');
    }
}

/**
 * Send baseline security headers.
 */
function npr_security_headers()
{
    if (headers_sent()) {
        return;
    }
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

/**
 * Client IP address, best effort.
 */
function npr_client_ip()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    return substr($ip, 0, 45);
}

/**
 * Allowed spreadsheet extensions and the MIME types hosts commonly report.
 */
function npr_allowed_spreadsheet_types()
{
    return array(
        'csv'  => array('text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel', 'application/octet-stream'),
        'txt'  => array('text/plain', 'application/octet-stream'),
        'xlsx' => array(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
            'application/octet-stream',
        ),
    );
}

/**
 * Validate an uploaded spreadsheet.
 *
 * @return array{ok:bool, error:string, ext:string, tmp:string, name:string}
 */
function npr_validate_spreadsheet_upload($file)
{
    $fail = function ($message) {
        return array('ok' => false, 'error' => $message, 'ext' => '', 'tmp' => '', 'name' => '');
    };

    if (!is_array($file) || !isset($file['error'])) {
        return $fail('No file was received.');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return $fail('Please choose a file to upload.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return $fail('The file is larger than the server upload limit.');
        default:
            return $fail('The file could not be uploaded. Please try again.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return $fail('Invalid upload.');
    }

    if ($file['size'] <= 0) {
        return $fail('The uploaded file is empty.');
    }

    if ($file['size'] > NPR_MAX_UPLOAD_BYTES) {
        return $fail('The file is too large. Maximum size is ' . round(NPR_MAX_UPLOAD_BYTES / 1048576, 1) . ' MB.');
    }

    $name = (string) $file['name'];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed = npr_allowed_spreadsheet_types();

    if (!isset($allowed[$ext])) {
        return $fail('Unsupported file type. Please upload a .csv or .xlsx file. (Old .xls files must be re-saved as .csv or .xlsx.)');
    }

    // Reject anything that even looks like a script, regardless of extension.
    if (preg_match('/\.(php\d?|phtml|phar|inc|cgi|pl|py|sh|htaccess)(\.|$)/i', $name)) {
        return $fail('That filename is not allowed.');
    }

    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = (string) finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
    }
    if ($mime !== '' && !in_array($mime, $allowed[$ext], true)) {
        return $fail('The file content does not match a ' . strtoupper($ext) . ' spreadsheet.');
    }

    // xlsx must really be a ZIP container.
    if ($ext === 'xlsx') {
        $handle = fopen($file['tmp_name'], 'rb');
        $magic = $handle ? fread($handle, 2) : '';
        if ($handle) {
            fclose($handle);
        }
        if ($magic !== 'PK') {
            return $fail('This does not look like a valid .xlsx workbook.');
        }
    }

    return array(
        'ok'    => true,
        'error' => '',
        'ext'   => $ext,
        'tmp'   => $file['tmp_name'],
        'name'  => $name,
    );
}

/**
 * Validate an uploaded institution logo.
 */
function npr_validate_logo_upload($file)
{
    $fail = function ($message) {
        return array('ok' => false, 'error' => $message, 'ext' => '', 'tmp' => '');
    };

    if (!is_array($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return array('ok' => false, 'error' => '', 'ext' => '', 'tmp' => ''); // nothing supplied
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return $fail('The logo could not be uploaded.');
    }
    if ($file['size'] > NPR_MAX_LOGO_BYTES) {
        return $fail('The logo is too large. Maximum size is ' . round(NPR_MAX_LOGO_BYTES / 1024) . ' KB.');
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        return $fail('The logo must be a valid image file.');
    }

    $map = array(
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_WEBP => 'webp',
    );
    if (!isset($map[$info[2]])) {
        return $fail('Only JPG, PNG, GIF or WEBP logos are supported.');
    }

    return array('ok' => true, 'error' => '', 'ext' => $map[$info[2]], 'tmp' => $file['tmp_name']);
}
