<?php
/**
 * 세부톡방 글 저장(찜) API
 * POST /proc/eottae-talkroom-bookmarks.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-bookmarks.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_talkroom_bookmarks_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_talkroom_bookmarks_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_talkroom_bookmarks_json(false, '로그인 후 이용해 주세요.');
}

$token = isset($_POST['eottae_talkroom_member_token']) ? trim((string) $_POST['eottae_talkroom_member_token']) : '';
if (!eottae_talkroom_verify_member_token($token)) {
    eottae_talkroom_bookmarks_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$room_id = isset($_POST['room_id']) ? (int) $_POST['room_id'] : 0;
$post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
$mb_id = $member['mb_id'];
$is_super = ($is_admin === 'super');

if ($room_id < 1 || $post_id < 1) {
    eottae_talkroom_bookmarks_json(false, '글 정보가 올바르지 않습니다.');
}

if ($action === 'add') {
    $result = eottae_talkroom_bookmark_add($mb_id, $room_id, $post_id, $is_super);
    if (!empty($result['ok'])) {
        eottae_talkroom_member_token(true);
    }
    eottae_talkroom_bookmarks_json(!empty($result['ok']), $result['message'], array(
        'saved'   => (int) ($result['saved'] ?? 0),
        'room_id' => $room_id,
        'post_id' => $post_id,
    ));
}

if ($action === 'remove') {
    $result = eottae_talkroom_bookmark_remove($mb_id, $room_id, $post_id);
    if (!empty($result['ok'])) {
        eottae_talkroom_member_token(true);
    }
    eottae_talkroom_bookmarks_json(!empty($result['ok']), $result['message'], array(
        'saved'   => (int) ($result['saved'] ?? 0),
        'room_id' => $room_id,
        'post_id' => $post_id,
    ));
}

if ($action === 'status') {
    $saved = eottae_talkroom_bookmark_is_saved($mb_id, $room_id, $post_id) ? 1 : 0;
    eottae_talkroom_bookmarks_json(true, '', array(
        'saved'   => $saved,
        'room_id' => $room_id,
        'post_id' => $post_id,
    ));
}

eottae_talkroom_bookmarks_json(false, '지원하지 않는 요청입니다.');
