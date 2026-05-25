<?php
/**
 * 세부톡방 관리 API (최고관리자)
 * POST /proc/eottae-talkroom-admin.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_talkroom_admin_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_talkroom_admin_json(false, '잘못된 요청입니다.');
}

if ($is_admin !== 'super' || empty($member['mb_id'])) {
    eottae_talkroom_admin_json(false, '최고관리자만 이용할 수 있습니다.');
}

$token = isset($_POST['eottae_talkroom_admin_token']) ? trim((string) $_POST['eottae_talkroom_admin_token']) : '';
if (!eottae_talkroom_verify_admin_token($token)) {
    eottae_talkroom_admin_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$room_id = isset($_POST['room_id']) ? (int) $_POST['room_id'] : 0;

if ($room_id < 1) {
    eottae_talkroom_admin_json(false, '톡방 정보가 올바르지 않습니다.');
}

if ($action === 'approve') {
    $result = eottae_talkroom_approve_room($room_id, $member['mb_id']);
    if (!empty($result['ok'])) {
        eottae_talkroom_admin_token(true);
    }
    eottae_talkroom_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'reject') {
    $reason = isset($_POST['reject_reason']) ? (string) $_POST['reject_reason'] : '';
    $result = eottae_talkroom_reject_room($room_id, $member['mb_id'], $reason);
    if (!empty($result['ok'])) {
        eottae_talkroom_admin_token(true);
    }
    eottae_talkroom_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'stop') {
    $result = eottae_talkroom_stop_room($room_id, $member['mb_id']);
    if (!empty($result['ok'])) {
        eottae_talkroom_admin_token(true);
    }
    eottae_talkroom_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'unkick_member') {
    $member_id = isset($_POST['member_id']) ? (int) $_POST['member_id'] : 0;
    $status_after = isset($_POST['status_after']) ? trim((string) $_POST['status_after']) : 'left';
    $result = eottae_talkroom_unkick_member($room_id, $member_id, $member['mb_id'], $status_after);
    if (!empty($result['ok'])) {
        eottae_talkroom_admin_token(true);
    }
    eottae_talkroom_admin_json(!empty($result['ok']), $result['message']);
}

$report_id = isset($_POST['report_id']) ? (int) $_POST['report_id'] : 0;
if ($report_id > 0 && in_array($action, array('report_review', 'report_dismiss', 'report_delete_content', 'report_kick_member'), true)) {
    $report_row = eottae_talkroom_get_report($report_id);
    if (!$report_row || (int) ($report_row['room_id'] ?? 0) !== $room_id) {
        eottae_talkroom_admin_json(false, '신고 정보가 올바르지 않습니다.');
    }
    $extra = array();
    if ($action === 'report_kick_member') {
        $extra['kicked_reason'] = isset($_POST['kicked_reason']) ? (string) $_POST['kicked_reason'] : '';
        $extra['can_rejoin'] = isset($_POST['can_rejoin']) ? (int) $_POST['can_rejoin'] : 0;
    }
    $result = eottae_talkroom_handle_report($report_id, $action, $member['mb_id'], true, $extra);
    if (!empty($result['ok'])) {
        eottae_talkroom_admin_token(true);
    }
    eottae_talkroom_admin_json(!empty($result['ok']), $result['message']);
}

eottae_talkroom_admin_json(false, '지원하지 않는 요청입니다.');
