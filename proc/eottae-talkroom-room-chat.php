<?php
/**
 * 톡방 상세 — 실시간 채팅 API
 * GET  ?room_id=1&action=poll&since_wr_id=0
 * POST action=poll|send&room_id=1
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-reads.lib.php';
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
$is_super = ($is_admin === 'super');
$can_manage_ai = eottae_talkroom_public_group_can_manage_ai($room_id, $viewer_mb_id, $is_super);

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
    $messages = eottae_talkroom_public_group_format_messages_for_viewer($rows, $viewer_mb_id, $can_manage_ai, $room_id, $is_super);
    $last_wr_id = $since_wr_id;

    foreach ($messages as $message) {
        $last_wr_id = max($last_wr_id, (int) ($message['wr_id'] ?? 0));
    }

    $poll_extras = eottae_talkroom_public_group_chat_poll_extras($room_id, $viewer_mb_id, true);

    eottae_talkroom_room_chat_json(true, '', array_merge(array(
        'room_id'    => $room_id,
        'messages'   => $messages,
        'last_wr_id' => $last_wr_id,
    ), $poll_extras));
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

    eottae_talkroom_room_chat_json(!empty($result['ok']), $result['message'] ?? '', array(
        'room_id'     => $room_id,
        'wr_id'       => (int) ($result['wr_id'] ?? 0),
        'message_row' => eottae_talkroom_public_group_enrich_message_row(
            $result['message_row'] ?? null,
            $room_id,
            $member['mb_id'],
            $is_super,
            $can_manage_ai
        ),
        'last_wr_id'  => (int) ($result['wr_id'] ?? 0),
        'member_token' => eottae_talkroom_member_token(),
    ));
}

if ($action === 'ai_speak') {
    if (!$can_manage_ai) {
        eottae_talkroom_room_chat_json(false, 'AI 말걸기 권한이 없습니다.');
    }

    $result = eottae_public_ai_run_manual_group_speak($room_id, $member['mb_id'], $is_super);

    eottae_talkroom_room_chat_json(!empty($result['ok']), $result['message'] ?? '', array(
        'room_id'      => $room_id,
        'wr_id'        => (int) ($result['wr_id'] ?? 0),
        'message_row'  => $result['message_row'] ?? null,
        'last_wr_id'   => (int) ($result['wr_id'] ?? 0),
        'member_token' => eottae_talkroom_member_token(),
    ));
}

if ($action === 'delete_message') {
    $wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
    $result = eottae_talkroom_public_group_delete_chat_message($wr_id, $member['mb_id'], $is_super);

    eottae_talkroom_room_chat_json(!empty($result['ok']), $result['message'] ?? '', array(
        'room_id'      => $room_id,
        'wr_id'        => (int) ($result['wr_id'] ?? $wr_id),
        'member_token' => eottae_talkroom_member_token(),
    ));
}

eottae_talkroom_room_chat_json(false, '지원하지 않는 요청입니다.');
