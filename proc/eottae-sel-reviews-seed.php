<?php
/**
 * POST/GET /proc/eottae-sel-reviews-seed.php?shop_wr_id=8
 * 관리자 로그인 또는 key 파라미터로 SEL 아카데미 샘플 리뷰 시드
 */
include_once dirname(__DIR__).'/common.php';
include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_PATH.'/setup/tools/eottae-seed.lib.php';

header('Content-Type: application/json; charset=utf-8');

$key = isset($_REQUEST['key']) ? trim((string) $_REQUEST['key']) : '';
$expected = substr(hash('sha256', G5_MYSQL_USER.G5_TABLE_PREFIX.'eottae-sel-reviews-seed-v1'), 0, 32);

if (!$is_admin && ($key === '' || !hash_equals($expected, $key))) {
    http_response_code(403);
    echo json_encode(array('ok' => false, 'message' => 'Forbidden'), JSON_UNESCAPED_UNICODE);
    exit;
}

$shop_wr_id = isset($_REQUEST['shop_wr_id']) ? (int) $_REQUEST['shop_wr_id'] : 8;
if ($shop_wr_id < 1) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'message' => 'shop_wr_id required'), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!function_exists('eottae_seed_sel_academy_reviews')) {
    http_response_code(500);
    echo json_encode(array('ok' => false, 'message' => 'Seed function missing'), JSON_UNESCAPED_UNICODE);
    exit;
}

$logs = eottae_seed_sel_academy_reviews($shop_wr_id);
$results = array();
$failed = 0;

foreach ($logs as $log) {
    if (empty($log['ok'])) {
        $failed++;
    }
    $results[] = array(
        'ok'      => !empty($log['ok']),
        'action'  => isset($log['action']) ? $log['action'] : '',
        'message' => isset($log['message']) ? $log['message'] : '',
    );
}

if (function_exists('run_event')) {
    run_event('cache_delete', 'board');
}

$summary = function_exists('eottae_get_shop_review_summary')
    ? eottae_get_shop_review_summary($shop_wr_id)
    : array('count' => 0, 'average' => 0);

echo json_encode(array(
    'ok'       => $failed === 0,
    'shop_id'  => $shop_wr_id,
    'summary'  => $summary,
    'count'    => count($results),
    'results'  => $results,
), JSON_UNESCAPED_UNICODE);
