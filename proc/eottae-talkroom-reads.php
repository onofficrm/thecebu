<?php
/**
 * 세부톡방 읽음 처리 API
 * POST /proc/eottae-talkroom-reads.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-reads.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-dashboard.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_talkroom_reads_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_talkroom_reads_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_talkroom_reads_json(false, '로그인 후 이용해 주세요.');
}

$token = isset($_POST['eottae_talkroom_member_token']) ? trim((string) $_POST['eottae_talkroom_member_token']) : '';
if (!eottae_talkroom_verify_member_token($token)) {
    eottae_talkroom_reads_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$mb_id = $member['mb_id'];

if ($action === 'mark_all') {
    $room_ids = array();
    if (isset($_POST['room_ids']) && is_array($_POST['room_ids'])) {
        $room_ids = $_POST['room_ids'];
    } elseif (isset($_POST['room_ids']) && is_string($_POST['room_ids'])) {
        $decoded = json_decode($_POST['room_ids'], true);
        if (is_array($decoded)) {
            $room_ids = $decoded;
        }
    }

    if (empty($room_ids)) {
        $my = eottae_talkroom_list_my_rooms($mb_id);
        $room_ids = eottae_talkroom_dashboard_feed_room_ids_from_my($my);
    }

    $result = eottae_talkroom_mark_all_rooms_read($mb_id, $room_ids);
    if (!empty($result['ok'])) {
        eottae_talkroom_member_token(true);
    }
    eottae_talkroom_reads_json(!empty($result['ok']), $result['message'], array(
        'updated' => (int) ($result['updated'] ?? 0),
    ));
}

$room_id = isset($_POST['room_id']) ? (int) $_POST['room_id'] : 0;
if ($room_id < 1) {
    eottae_talkroom_reads_json(false, '톡방 정보가 올바르지 않습니다.');
}

if ($action === 'mark_room') {
    $result = eottae_talkroom_mark_room_read($room_id, $mb_id);
    if (!empty($result['ok'])) {
        eottae_talkroom_member_token(true);
    }
    eottae_talkroom_reads_json(!empty($result['ok']), $result['message'], array(
        'room_id' => $room_id,
    ));
}

eottae_talkroom_reads_json(false, '지원하지 않는 요청입니다.');
