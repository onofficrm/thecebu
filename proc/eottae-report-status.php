<?php
/**
 * 세부 제보함 — 관리자 상태 변경
 * POST bo_table=report&wr_id=1&wr_8=checking&wr_9=메모&token=...
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-report.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_report_status_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    eottae_report_status_json(false, '잘못된 요청입니다.');
}

if (!check_token()) {
    eottae_report_status_json(false, '보안 토큰이 유효하지 않습니다. 새로고침 후 다시 시도해 주세요.');
}

if (empty($is_admin) || !eottae_report_is_board_admin($is_admin)) {
    eottae_report_status_json(false, '관리자만 상태를 변경할 수 있습니다.');
}

$bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', (string) $_POST['bo_table']) : '';
$wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
$status = isset($_POST['wr_8']) ? (string) $_POST['wr_8'] : '';
$memo = isset($_POST['wr_9']) ? (string) $_POST['wr_9'] : '';

$result = eottae_report_set_status($bo_table, $wr_id, $status, $memo, true);

if (!empty($result['ok'])) {
    eottae_report_set_flash($result['message'] ?? '');
}

eottae_report_status_json(!empty($result['ok']), $result['message'] ?? '', array(
    'status' => $result['status'] ?? '',
    'label'  => $result['label'] ?? '',
));
