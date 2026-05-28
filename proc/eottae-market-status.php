<?php
/**
 * 중고장터 — 거래상태 변경
 * POST bo_table=market&wr_id=1&status=selling|reserved|sold
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-market.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_market_status_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    eottae_market_status_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_market_status_json(false, '로그인 후 변경할 수 있습니다.');
}

$bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', (string) $_POST['bo_table']) : '';
$wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
$status = isset($_POST['status']) ? (string) $_POST['status'] : '';
$is_admin_user = !empty($is_admin);

$result = eottae_market_set_status($bo_table, $wr_id, $status, (string) ($member['mb_id'] ?? ''), $is_admin_user);

eottae_market_status_json(!empty($result['ok']), $result['message'] ?? '', array(
    'status' => $result['status'] ?? '',
    'label'  => $result['label'] ?? '',
));
