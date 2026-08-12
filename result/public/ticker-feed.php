<?php
/**
 * JSON feed of published results for the homepage ticker.
 * Reached at /result/ticker.json (or /result/index.php?npr_route=ticker.json).
 */
if (!defined('NPR_BOOTSTRAPPED')) {
    require_once dirname(__DIR__) . '/includes/bootstrap.php';
}

header('Content-Type: application/json; charset=utf-8');
// Kept short on purpose: this feed is what lets a homepage sitting behind a
// page cache notice a newly published result quickly.
header('Cache-Control: public, max-age=15');
header('Access-Control-Allow-Origin: *');

$items = array();
foreach (npr_ticker_results(12) as $row) {
    $items[] = array(
        'title'       => $row['result_title'],
        'institution' => $row['institution_name'],
        'examination' => $row['examination_name'],
        'session'     => $row['academic_session'],
        'date'        => npr_date($row['result_date']),
        'type'        => npr_is_external($row) ? 'external' : 'internal',
        'url'         => npr_result_link($row),
        'button'      => npr_result_button_text($row),
    );
}

echo json_encode(array(
    'label'    => 'LIVE RESULTS',
    'empty'    => count($items) === 0,
    'message'  => count($items) === 0 ? 'Coming Soon' : '',
    'revision' => npr_ticker_revision(),
    'items'    => $items,
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
