<?php
/**
 * POST/GET /proc/eottae-sel-reviews-seed.php?shop_wr_id=8&preset=sel
 * POST/GET /proc/eottae-sel-reviews-seed.php?shop_wr_id=10&preset=yonggungri
 * POST/GET /proc/eottae-sel-reviews-seed.php?shop_wr_id=9&preset=badachamchi
 * POST/GET /proc/eottae-sel-reviews-seed.php?shop_wr_id=11&preset=dawon
 * POST/GET /proc/eottae-sel-reviews-seed.php?shop_wr_id=36&preset=shiny
 * POST/GET /proc/eottae-sel-reviews-seed.php?shop_wr_id=39&preset=barocar
 * POST/GET /proc/eottae-sel-reviews-seed.php?shop_wr_id=62&preset=luckyvilla
 * POST/GET ...&preset=barocar&force=1 — brv* 시드 리뷰 삭제 후 재등록
 * POST/GET ...&preset=luckyvilla&force=1 — lvrv* 시드 리뷰 삭제 후 재등록
 * 관리자 로그인 또는 key 파라미터로 샘플 리뷰 시드 (preset: sel | yonggungri | badachamchi | dawon | shiny | barocar | luckyvilla)
 */
include_once dirname(__DIR__).'/common.php';
include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_PATH.'/setup/tools/eottae-seed.lib.php';

header('Content-Type: application/json; charset=utf-8');

$key = isset($_REQUEST['key']) ? trim((string) $_REQUEST['key']) : '';
$expected = substr(hash('sha256', G5_MYSQL_USER.G5_TABLE_PREFIX.'eottae-sel-reviews-seed-v1'), 0, 32);
$auth_ok = (bool) $is_admin;

if (!$auth_ok && $key !== '' && hash_equals($expected, $key)) {
    $auth_ok = true;
}

if (!$auth_ok && $key !== '' && is_file(G5_LIB_PATH.'/eottae-talkroom.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
    if (function_exists('eottae_talkroom_maintenance_verify_key')
        && eottae_talkroom_maintenance_verify_key($key)) {
        $auth_ok = true;
    }
}

if (!$auth_ok) {
    http_response_code(403);
    echo json_encode(array('ok' => false, 'message' => 'Forbidden'), JSON_UNESCAPED_UNICODE);
    exit;
}

$preset = isset($_REQUEST['preset']) ? strtolower(trim((string) $_REQUEST['preset'])) : 'sel';
if ($preset === '') {
    $preset = 'sel';
}

$default_shop_id = 8;
if ($preset === 'yonggungri') {
    $default_shop_id = 10;
} elseif ($preset === 'badachamchi') {
    $default_shop_id = 9;
} elseif ($preset === 'dawon') {
    $default_shop_id = 11;
} elseif ($preset === 'shiny') {
    $default_shop_id = 36;
} elseif ($preset === 'barocar') {
    $default_shop_id = 39;
} elseif ($preset === 'luckyvilla') {
    $default_shop_id = 62;
}
$shop_wr_id = isset($_REQUEST['shop_wr_id']) ? (int) $_REQUEST['shop_wr_id'] : $default_shop_id;
if ($shop_wr_id < 1) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'message' => 'shop_wr_id required'), JSON_UNESCAPED_UNICODE);
    exit;
}

$seed_fn = 'eottae_seed_sel_academy_reviews';
if ($preset === 'yonggungri') {
    $seed_fn = 'eottae_seed_yonggungri_reviews';
} elseif ($preset === 'badachamchi') {
    $seed_fn = 'eottae_seed_badachamchi_reviews';
} elseif ($preset === 'dawon') {
    $seed_fn = 'eottae_seed_dawon_reviews';
} elseif ($preset === 'shiny') {
    $seed_fn = 'eottae_seed_shiny_reviews';
} elseif ($preset === 'barocar') {
    $seed_fn = 'eottae_seed_barocar_reviews';
} elseif ($preset === 'luckyvilla') {
    $seed_fn = 'eottae_seed_luckyvilla_reviews';
}

if (!function_exists($seed_fn)) {
    http_response_code(500);
    echo json_encode(array('ok' => false, 'message' => 'Seed function missing: '.$seed_fn), JSON_UNESCAPED_UNICODE);
    exit;
}

$force = !empty($_REQUEST['force']);
if ($preset === 'barocar') {
    $logs = eottae_seed_barocar_reviews($shop_wr_id, $force);
} elseif ($preset === 'luckyvilla') {
    $logs = eottae_seed_luckyvilla_reviews($shop_wr_id, $force);
} else {
    $logs = call_user_func($seed_fn, $shop_wr_id);
}
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
    'preset'   => $preset,
    'shop_id'  => $shop_wr_id,
    'summary'  => $summary,
    'count'    => count($results),
    'results'  => $results,
), JSON_UNESCAPED_UNICODE);
