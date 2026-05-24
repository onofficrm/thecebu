<?php
/**
 * POST/GET /proc/eottae-gallery-seed.php
 * 관리자 로그인 또는 key 파라미터로 갤러리 게시판 샘플 시드
 */
include_once dirname(__DIR__).'/common.php';
include_once G5_PATH.'/setup/tools/eottae-seed.lib.php';

header('Content-Type: application/json; charset=utf-8');

$key = isset($_REQUEST['key']) ? trim((string) $_REQUEST['key']) : '';
$expected = substr(hash('sha256', G5_MYSQL_USER.G5_TABLE_PREFIX.'eottae-gallery-seed-v1'), 0, 32);

if (!$is_admin && ($key === '' || !hash_equals($expected, $key))) {
    http_response_code(403);
    echo json_encode(array('ok' => false, 'message' => 'Forbidden'), JSON_UNESCAPED_UNICODE);
    exit;
}

$logs = eottae_seed_gallery_samples_run();
$results = array();

foreach ($logs as $log) {
    $results[] = array(
        'ok'      => !empty($log['ok']),
        'action'  => isset($log['action']) ? $log['action'] : '',
        'message' => isset($log['message']) ? $log['message'] : '',
    );
}

if (function_exists('run_event')) {
    run_event('cache_delete', 'board');
}

echo json_encode(array(
    'ok'      => true,
    'count'   => count($results),
    'results' => $results,
), JSON_UNESCAPED_UNICODE);
