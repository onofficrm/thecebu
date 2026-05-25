<?php
/**
 * 세부톡방 참여/탈퇴 API
 * POST /proc/eottae-talkroom-member.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_talkroom_member_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_talkroom_member_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_talkroom_member_json(false, '로그인 후 이용해 주세요.');
}

$token = isset($_POST['eottae_talkroom_member_token']) ? trim((string) $_POST['eottae_talkroom_member_token']) : '';
if (!eottae_talkroom_verify_member_token($token)) {
    eottae_talkroom_member_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$room_id = isset($_POST['room_id']) ? (int) $_POST['room_id'] : 0;

if ($room_id < 1) {
    eottae_talkroom_member_json(false, '톡방 정보가 올바르지 않습니다.');
}

if ($action === 'join') {
    $result = eottae_talkroom_join_room($room_id, $member['mb_id']);
    if (!empty($result['ok'])) {
        eottae_talkroom_member_token(true);
    }
    eottae_talkroom_member_json(!empty($result['ok']), $result['message'], array(
        'redirect_url' => eottae_talkroom_enter_url($room_id),
        'status'       => isset($result['status']) ? $result['status'] : '',
    ));
}

if ($action === 'leave') {
    $result = eottae_talkroom_leave_room($room_id, $member['mb_id']);
    if (!empty($result['ok'])) {
        eottae_talkroom_member_token(true);
    }
    eottae_talkroom_member_json(!empty($result['ok']), $result['message'], array(
        'redirect_url' => eottae_talkroom_enter_url($room_id),
    ));
}

eottae_talkroom_member_json(false, '지원하지 않는 요청입니다.');
