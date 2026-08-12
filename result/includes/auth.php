<?php
/**
 * Admin authentication: login, logout, session guard, throttling.
 */

/**
 * The signed-in admin row, or null.
 */
function npr_current_admin()
{
    npr_session_start();

    if (empty($_SESSION['npr_admin_id'])) {
        return null;
    }

    // Idle timeout.
    $last = isset($_SESSION['npr_last_activity']) ? (int) $_SESSION['npr_last_activity'] : 0;
    if ($last > 0 && (time() - $last) > NPR_SESSION_IDLE_TIMEOUT) {
        npr_logout();
        return null;
    }

    // Bind the session to the browser fingerprint to blunt cookie theft.
    $fingerprint = npr_session_fingerprint();
    if (!empty($_SESSION['npr_fingerprint']) && !hash_equals($_SESSION['npr_fingerprint'], $fingerprint)) {
        npr_logout();
        return null;
    }

    $admin = npr_fetch_one(
        'SELECT * FROM `' . npr_table('admins') . '` WHERE id = ? AND is_active = 1 LIMIT 1',
        array((int) $_SESSION['npr_admin_id'])
    );

    if (!$admin) {
        npr_logout();
        return null;
    }

    $_SESSION['npr_last_activity'] = time();
    return $admin;
}

function npr_session_fingerprint()
{
    $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    return hash('sha256', $agent . '|' . NPR_SESSION_NAME);
}

/**
 * Require a signed-in admin, or bounce to the login screen.
 */
function npr_require_admin()
{
    $admin = npr_current_admin();
    if (!$admin) {
        $target = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        npr_redirect(npr_url('admin/login.php') . ($target !== '' ? '?next=' . rawurlencode($target) : ''));
    }
    return $admin;
}

/**
 * Attempt a login.
 *
 * @return array{ok:bool, error:string}
 */
function npr_attempt_login($username, $password)
{
    $ip = npr_client_ip();

    if (npr_login_locked($ip)) {
        return array('ok' => false, 'error' => 'Too many failed attempts. Please try again in a few minutes.');
    }

    $username = trim((string) $username);
    $admin = null;

    if ($username !== '') {
        $admin = npr_fetch_one(
            'SELECT * FROM `' . npr_table('admins') . '` WHERE username = ? LIMIT 1',
            array($username)
        );
    }

    $valid = false;
    if ($admin && (int) $admin['is_active'] === 1) {
        $valid = password_verify((string) $password, $admin['password_hash']);
    } else {
        // Constant-ish work factor for unknown users, so timing does not leak.
        password_verify((string) $password, '$2y$10$usesomesillystringforsalt0000000000000000000000000000000000');
    }

    npr_record_login_attempt($ip, $username, $valid);

    if (!$valid) {
        return array('ok' => false, 'error' => 'Invalid username or password.');
    }

    if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
        npr_query(
            'UPDATE `' . npr_table('admins') . '` SET password_hash = ?, updated_at = ? WHERE id = ?',
            array(password_hash((string) $password, PASSWORD_DEFAULT), npr_now(), (int) $admin['id'])
        );
    }

    npr_session_start();
    session_regenerate_id(true);
    $_SESSION['npr_admin_id'] = (int) $admin['id'];
    $_SESSION['npr_admin_name'] = (string) $admin['username'];
    $_SESSION['npr_last_activity'] = time();
    $_SESSION['npr_fingerprint'] = npr_session_fingerprint();
    $_SESSION['npr_csrf'] = bin2hex(random_bytes(32));

    npr_query(
        'UPDATE `' . npr_table('admins') . '` SET last_login_at = ? WHERE id = ?',
        array(npr_now(), (int) $admin['id'])
    );

    return array('ok' => true, 'error' => '');
}

function npr_record_login_attempt($ip, $username, $success)
{
    npr_query(
        'INSERT INTO `' . npr_table('login_attempts') . '` (ip_address, username, attempted_at, success) VALUES (?, ?, ?, ?)',
        array($ip, substr((string) $username, 0, 64), npr_now(), $success ? 1 : 0)
    );

    // Opportunistic cleanup of old rows.
    if (mt_rand(1, 20) === 1) {
        npr_query(
            'DELETE FROM `' . npr_table('login_attempts') . '` WHERE attempted_at < ?',
            array(date('Y-m-d H:i:s', time() - 86400))
        );
    }
}

function npr_login_locked($ip)
{
    $since = date('Y-m-d H:i:s', time() - NPR_LOGIN_WINDOW_SECONDS);
    $failures = (int) npr_fetch_value(
        'SELECT COUNT(*) FROM `' . npr_table('login_attempts') . '`
          WHERE ip_address = ? AND success = 0 AND attempted_at > ?',
        array($ip, $since),
        0
    );
    return $failures >= NPR_LOGIN_MAX_ATTEMPTS;
}

/**
 * Destroy the admin session.
 */
function npr_logout()
{
    npr_session_start();
    $_SESSION = array();
    if (ini_get('session.use_cookies') && !headers_sent()) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], !empty($params['secure']), !empty($params['httponly']));
    }
    session_destroy();
}
