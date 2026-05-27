<?php
/**
 * 업체 최우수 노출 — 포인트 신청
 */
include_once dirname(__DIR__).'/common.php';
include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-shop-spot.lib.php';

header('Content-Type: application/json; charset=utf-8');

eottae_shop_spot_ensure_schema();

if (empty($is_member) || empty($member['mb_id'])) {
    echo json_encode(array('ok' => false, 'message' => '로그인 후 이용해 주세요.'), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('ok' => false, 'message' => '잘못된 요청입니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : 'apply';
if ($action !== 'apply') {
    echo json_encode(array('ok' => false, 'message' => '잘못된 요청입니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$list_bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', (string) $_POST['bo_table']) : '';
$shop_bo_table = isset($_POST['shop_bo_table']) ? preg_replace('/[^a-z0-9_]/', '', (string) $_POST['shop_bo_table']) : $list_bo_table;
$shop_wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
$spot_slot = isset($_POST['spot_slot']) ? (int) $_POST['spot_slot'] : 0;

$result = eottae_shop_spot_apply($member['mb_id'], $list_bo_table, $shop_bo_table, $shop_wr_id, $spot_slot);

if (!empty($result['ok']) && function_exists('run_event')) {
    run_event('cache_delete', 'board');
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
