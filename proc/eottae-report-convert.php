<?php
/**
 * 세부 제보함 — 관리자 게시판 복사 전환
 * POST bo_table=report&wr_id=1&target_bo_table=community&token=...
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-report.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_report_convert_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    eottae_report_convert_json(false, '잘못된 요청입니다.');
}

if (!check_token()) {
    eottae_report_convert_json(false, '보안 토큰이 유효하지 않습니다. 새로고침 후 다시 시도해 주세요.');
}

if (empty($is_admin) || !eottae_report_is_board_admin($is_admin)) {
    eottae_report_convert_json(false, '관리자만 전환할 수 있습니다.');
}

$bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', (string) $_POST['bo_table']) : '';
$wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
$target_bo_table = isset($_POST['target_bo_table']) ? preg_replace('/[^a-z0-9_]/', '', (string) $_POST['target_bo_table']) : '';

$result = eottae_report_copy_to_board($bo_table, $wr_id, $target_bo_table, true);

if (!empty($result['ok'])) {
    eottae_report_set_flash($result['message'] ?? '');
}

eottae_report_convert_json(!empty($result['ok']), $result['message'] ?? '', array(
    'target_bo_table' => $result['target_bo_table'] ?? '',
    'target_wr_id'    => isset($result['target_wr_id']) ? (int) $result['target_wr_id'] : 0,
    'view_url'        => $result['view_url'] ?? '',
));
