<?php
/**
 * PDO connection handling and table-name helpers.
 */

if (!defined('NPR_DB_HOST')) {
    http_response_code(500);
    exit('Configuration not loaded.');
}

/**
 * Shared PDO connection (lazily created).
 */
function npr_db()
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        NPR_DB_HOST,
        NPR_DB_NAME,
        NPR_DB_CHARSET
    );

    try {
        $pdo = new PDO($dsn, NPR_DB_USER, NPR_DB_PASS, array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ));
    } catch (PDOException $e) {
        error_log('[NaukriPatra Result] DB connection failed: ' . $e->getMessage());
        http_response_code(503);
        if (defined('NPR_DEBUG') && NPR_DEBUG) {
            exit('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
        exit('The result service is temporarily unavailable. Please try again later.');
    }

    return $pdo;
}

/**
 * Prefixed table name. The prefix is developer-controlled, never user input.
 */
function npr_table($name)
{
    return NPR_TABLE_PREFIX . $name;
}

/**
 * Run a prepared statement and return the statement handle.
 */
function npr_query($sql, array $params = array())
{
    $stmt = npr_db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch a single row, or null.
 */
function npr_fetch_one($sql, array $params = array())
{
    $row = npr_query($sql, $params)->fetch();
    return $row === false ? null : $row;
}

/**
 * Fetch all rows.
 */
function npr_fetch_all($sql, array $params = array())
{
    return npr_query($sql, $params)->fetchAll();
}

/**
 * Fetch the first column of the first row.
 */
function npr_fetch_value($sql, array $params = array(), $default = null)
{
    $value = npr_query($sql, $params)->fetchColumn();
    return $value === false ? $default : $value;
}
