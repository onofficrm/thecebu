<?php
/**
 * 골프조인 채팅 API (polling)
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-golf-join.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_golf_join_chat_json($success, $message, $extra = array())
{
    echo json_encode(array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array()), JSON_UNESCAPED_UNICODE);
    exit;
}

$join_id = isset($_REQUEST['join_id']) ? (int) $_REQUEST['join_id'] : 0;
$action = isset($_REQUEST['action']) ? trim((string) $_REQUEST['action']) : 'poll';
$since_id = isset($_REQUEST['since_id']) ? (int) $_REQUEST['since_id'] : 0;

if ($join_id < 1) {
    eottae_golf_join_chat_json(false, '조인 정보가 올바르지 않습니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    $login = function_exists('eottae_login_url')
        ? eottae_login_url(eottae_golf_join_chat_url($join_id))
        : G5_BBS_URL.'/login.php';
    eottae_golf_join_chat_json(false, '로그인이 필요합니다.', array('redirect' => $login));
}

$access = eottae_golf_join_can_access_chat($join_id, $member['mb_id']);
if (empty($access['ok'])) {
    eottae_golf_join_chat_json(false, $access['message'] ?? '입장할 수 없습니다.');
}

$room = eottae_golf_join_ensure_chat_room($join_id);
if (!$room || empty($room['id'])) {
    eottae_golf_join_chat_json(false, '채팅방을 열 수 없습니다.');
}

$chat_room_id = (int) $room['id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'poll') {
    $messages = eottae_golf_join_chat_list_messages($chat_room_id, $since_id, 50);
    $last_id = $since_id;
    foreach ($messages as $msg) {
        $last_id = max($last_id, (int) ($msg['id'] ?? 0));
    }

    eottae_golf_join_chat_json(true, '', array(
        'messages'   => $messages,
        'last_id'    => $last_id,
        'viewer_id'  => (string) $member['mb_id'],
    ));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_golf_join_chat_json(false, '잘못된 요청입니다.');
}

$token = isset($_POST['eottae_golf_join_token']) ? trim((string) $_POST['eottae_golf_join_token']) : '';
if (!eottae_golf_join_verify_member_token($token)) {
    eottae_golf_join_chat_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.');
}

if ($action === 'send') {
    $text = isset($_POST['message']) ? (string) $_POST['message'] : '';
    $result = eottae_golf_join_chat_send_message($join_id, $member, $text);
    if (!empty($result['ok'])) {
        eottae_golf_join_member_token(true);
    }
    eottae_golf_join_chat_json(!empty($result['ok']), $result['message'] ?? '', array(
        'message_id' => (int) ($result['message_id'] ?? 0),
    ));
}

eottae_golf_join_chat_json(false, '지원하지 않는 요청입니다.');
