<?php
/**
 * Public front controller.
 *
 *   /result/                     → list of published results
 *   /result/<slug>/              → result checking page for one result
 *   /result/ticker.json          → published results as JSON (ticker feed)
 *
 * Pretty URLs come from .htaccess; ?slug=<slug> works as a fallback when
 * mod_rewrite is unavailable.
 */

require_once __DIR__ . '/includes/bootstrap.php';

$route = npr_get('npr_route');
if ($route === '' && isset($_SERVER['PATH_INFO'])) {
    $route = trim((string) $_SERVER['PATH_INFO'], '/');
}
if ($route === '') {
    $route = npr_get('slug');
}
$route = trim((string) $route, '/');

if ($route === 'ticker.json') {
    require __DIR__ . '/public/ticker-feed.php';
    exit;
}

if ($route === '') {
    require __DIR__ . '/public/home.php';
    exit;
}

// Anything else is treated as a result slug.
$slug = npr_slugify($route);
$result = $slug !== '' ? npr_find_published_result($slug) : null;

if (!$result) {
    http_response_code(404);
    require __DIR__ . '/public/not-found.php';
    exit;
}

// An external result has no student data of its own: send the visitor to the
// official website that published it. The destination comes from the database
// and was validated when the administrator saved it, so this is not an open
// redirect.
if (npr_is_external($result)) {
    $check = npr_validate_external_url((string) $result['external_url']);
    if (!$check['ok']) {
        http_response_code(404);
        require __DIR__ . '/public/not-found.php';
        exit;
    }
    header('Location: ' . $check['url'], true, 302);
    header('Referrer-Policy: no-referrer');
    $target = $check['url'];
    require __DIR__ . '/public/external-redirect.php';
    exit;
}

require __DIR__ . '/public/result.php';
