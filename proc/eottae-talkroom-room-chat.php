<?php
/**
 * 톡방 상세 — 실시간 채팅 API
 * GET  ?room_id=1&action=poll&since_wr_id=0
 * POST action=poll|send&room_id=1
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-public-chat.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_talkroom_room_chat_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$room_id = isset($_REQUEST['room_id']) ? (int) $_REQUEST['room_id'] : 0;
if ($room_id < 1) {
    eottae_talkroom_room_chat_json(false, '톡방 정보가 올바르지 않습니다.', array(
        'room_id'  => 0,
        'messages' => array(),
    ));
}

$room = eottae_talkroom_get_operating_room($room_id);
if (!$room) {
    eottae_talkroom_room_chat_json(false, '운영 중인 톡방을 찾을 수 없습니다.', array(
        'room_id'  => $room_id,
        'messages' => array(),
    ));
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$action = isset($_REQUEST['action']) ? trim((string) $_REQUEST['action']) : 'poll';
$since_wr_id = isset($_REQUEST['since_wr_id']) ? (int) $_REQUEST['since_wr_id'] : 0;
$viewer_mb_id = !empty($member['mb_id']) ? (string) $member['mb_id'] : '';

if ($method === 'GET' || $action === 'poll') {
    $ctx = eottae_talkroom_build_detail_context($room_id, $viewer_mb_id);
    if (!$ctx || empty($ctx['can_view_posts'])) {
        eottae_talkroom_room_chat_json(false, '대화를 볼 수 없습니다.', array(
            'room_id'    => $room_id,
            'messages'   => array(),
            'last_wr_id' => $since_wr_id,
        ));
    }

    $rows = eottae_talkroom_public_group_list_messages($room_id, 30, $since_wr_id);
    $messages = array();
    $last_wr_id = $since_wr_id;

    foreach ($rows as $row) {
        $message = eottae_talkroom_public_group_format_message($row, $viewer_mb_id);
        if ($message['text'] === '') {
            continue;
        }
        $messages[] = $message;
        $last_wr_id = max($last_wr_id, (int) ($message['wr_id'] ?? 0));
    }

    eottae_talkroom_room_chat_json(true, '', array(
        'room_id'    => $room_id,
        'messages'   => $messages,
        'last_wr_id' => $last_wr_id,
    ));
}

if ($method !== 'POST') {
    eottae_talkroom_room_chat_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    $return = function_exists('eottae_talkroom_enter_url') ? eottae_talkroom_enter_url($room_id) : G5_URL;
    eottae_talkroom_room_chat_json(false, '회원가입 또는 로그인 후 메시지를 보낼 수 있습니다.', array(
        'auth_required' => true,
        'login_url'     => function_exists('eottae_login_url') ? eottae_login_url($return) : G5_BBS_URL.'/login.php?url='.urlencode($return),
        'register_url'  => function_exists('eottae_register_url') ? eottae_register_url() : G5_BBS_URL.'/register.php',
    ));
}

$token = isset($_POST['eottae_talkroom_member_token']) ? trim((string) $_POST['eottae_talkroom_member_token']) : '';
if (!eottae_talkroom_verify_member_token($token)) {
    eottae_talkroom_room_chat_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

if ($action === 'send') {
    $text = isset($_POST['message']) ? (string) $_POST['message'] : '';
    $result = eottae_talkroom_room_send_message($room_id, $member['mb_id'], $text);
    if (!empty($result['ok'])) {
        eottae_talkroom_member_token(true);
    }

    eottae_talkroom_room_chat_json(!empty($result['ok']), $result['message'] ?? '', array(
        'room_id'     => $room_id,
        'wr_id'       => (int) ($result['wr_id'] ?? 0),
        'message_row' => $result['message_row'] ?? null,
        'last_wr_id'  => (int) ($result['wr_id'] ?? 0),
    ));
}

eottae_talkroom_room_chat_json(false, '지원하지 않는 요청입니다.');
