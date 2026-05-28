<?php
/**
 * 이벤트/프로모션 — 기간 없음 이벤트 수동 종료
 * POST bo_table=event&wr_id=1
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-event.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_event_close_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    eottae_event_close_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_event_close_json(false, '로그인 후 종료할 수 있습니다.');
}

$bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', (string) $_POST['bo_table']) : '';
$wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
$is_super = ($is_admin === 'super');

if (!eottae_is_event_board($bo_table)) {
    eottae_event_close_json(false, '이벤트 게시판이 아닙니다.');
}

$result = eottae_event_set_manual_closed($bo_table, $wr_id, $member['mb_id'], $is_super);

eottae_event_close_json(!empty($result['ok']), $result['message'] ?? '', array(
    'status' => $result['status'] ?? '',
    'label'  => $result['label'] ?? '',
));
