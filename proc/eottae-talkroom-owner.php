<?php
/**
 * 세부톡방 방장 관리 API
 * POST /proc/eottae-talkroom-owner.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_talkroom_owner_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_talkroom_owner_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_talkroom_owner_json(false, '로그인 후 이용해 주세요.');
}

$is_super = ($is_admin === 'super');
$token = isset($_POST['eottae_talkroom_owner_token']) ? trim((string) $_POST['eottae_talkroom_owner_token']) : '';
if (!eottae_talkroom_verify_owner_token($token)) {
    eottae_talkroom_owner_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$room_id = isset($_POST['room_id']) ? (int) $_POST['room_id'] : 0;

if ($room_id < 1) {
    eottae_talkroom_owner_json(false, '톡방 정보가 올바르지 않습니다.');
}

if ($action === 'delete') {
    $result = eottae_talkroom_delete_room($room_id, $member['mb_id'], $is_super);
    if (!empty($result['ok'])) {
        eottae_talkroom_owner_token(true);
    }
    eottae_talkroom_owner_json(!empty($result['ok']), $result['message'], array(
        'redirect_url' => eottae_talkroom_list_url(),
    ));
}

$access = eottae_talkroom_assert_manage_access($room_id, $member['mb_id'], $is_super);
if (empty($access['ok'])) {
    eottae_talkroom_owner_json(false, $access['message']);
}

if ($action === 'approve_member') {
    $member_id = isset($_POST['member_id']) ? (int) $_POST['member_id'] : 0;
    $result = eottae_talkroom_approve_member($room_id, $member_id, $member['mb_id'], $is_super);
    if (!empty($result['ok'])) {
        eottae_talkroom_owner_token(true);
    }
    eottae_talkroom_owner_json(!empty($result['ok']), $result['message']);
}

if ($action === 'reject_member') {
    $member_id = isset($_POST['member_id']) ? (int) $_POST['member_id'] : 0;
    $result = eottae_talkroom_reject_member($room_id, $member_id, $member['mb_id'], $is_super);
    if (!empty($result['ok'])) {
        eottae_talkroom_owner_token(true);
    }
    eottae_talkroom_owner_json(!empty($result['ok']), $result['message']);
}

if ($action === 'update_room') {
    $data = eottae_talkroom_parse_owner_update_input($_POST);
    $result = eottae_talkroom_update_room_by_owner($room_id, $data, $member['mb_id'], $is_super);
    if (!empty($result['ok'])) {
        eottae_talkroom_owner_token(true);
    }
    eottae_talkroom_owner_json(!empty($result['ok']), $result['message'], array(
        'redirect_url' => eottae_talkroom_owner_manage_url($room_id),
    ));
}

if ($action === 'save_notice') {
    $notice = isset($_POST['room_notice']) ? (string) $_POST['room_notice'] : '';
    $result = eottae_talkroom_save_room_notice($room_id, $notice, $member['mb_id'], $is_super);
    if (!empty($result['ok'])) {
        eottae_talkroom_owner_token(true);
    }
    eottae_talkroom_owner_json(!empty($result['ok']), $result['message']);
}

if ($action === 'kick_member') {
    $member_id = isset($_POST['member_id']) ? (int) $_POST['member_id'] : 0;
    $reason = isset($_POST['kicked_reason']) ? (string) $_POST['kicked_reason'] : '';
    $can_rejoin = isset($_POST['can_rejoin']) ? (int) $_POST['can_rejoin'] : 0;
    $result = eottae_talkroom_kick_member($room_id, $member_id, $member['mb_id'], $is_super, $reason, $can_rejoin);
    if (!empty($result['ok'])) {
        eottae_talkroom_owner_token(true);
    }
    eottae_talkroom_owner_json(!empty($result['ok']), $result['message']);
}

if ($action === 'stop') {
    $result = eottae_talkroom_stop_room($room_id, $member['mb_id'], $is_super);
    if (!empty($result['ok'])) {
        eottae_talkroom_owner_token(true);
    }
    eottae_talkroom_owner_json(!empty($result['ok']), $result['message'], array(
        'redirect_url' => eottae_talkroom_list_url(),
    ));
}

$report_id = isset($_POST['report_id']) ? (int) $_POST['report_id'] : 0;
if ($report_id > 0 && in_array($action, array('report_review', 'report_dismiss', 'report_delete_content', 'report_kick_member'), true)) {
    $report_row = eottae_talkroom_get_report($report_id);
    if (!$report_row || (int) ($report_row['room_id'] ?? 0) !== $room_id) {
        eottae_talkroom_owner_json(false, '신고 정보가 올바르지 않습니다.');
    }
    $extra = array();
    if ($action === 'report_kick_member') {
        $extra['kicked_reason'] = isset($_POST['kicked_reason']) ? (string) $_POST['kicked_reason'] : '';
        $extra['can_rejoin'] = isset($_POST['can_rejoin']) ? (int) $_POST['can_rejoin'] : 0;
    }
    $result = eottae_talkroom_handle_report($report_id, $action, $member['mb_id'], $is_super, $extra);
    if (!empty($result['ok'])) {
        eottae_talkroom_owner_token(true);
    }
    eottae_talkroom_owner_json(!empty($result['ok']), $result['message']);
}

eottae_talkroom_owner_json(false, '지원하지 않는 요청입니다.');
