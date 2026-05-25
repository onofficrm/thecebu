<?php
/**
 * 마이페이지 — 내 톡방 통합 피드 API
 * POST /proc/eottae-talkroom-dashboard-feed.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-reads.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-dashboard.lib.php';
include_once G5_PATH.'/components/eottae/talk-mypage-dashboard.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_talkroom_dashboard_feed_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_talkroom_dashboard_feed_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_talkroom_dashboard_feed_json(false, '로그인 후 이용해 주세요.');
}

$token = isset($_POST['eottae_talkroom_member_token']) ? trim((string) $_POST['eottae_talkroom_member_token']) : '';
if (!eottae_talkroom_verify_member_token($token)) {
    eottae_talkroom_dashboard_feed_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$mb_id = $member['mb_id'];
$my = eottae_talkroom_list_my_rooms($mb_id);
$room_ids = eottae_talkroom_dashboard_feed_room_ids_from_my($my);

$limit = isset($_POST['limit']) ? (int) $_POST['limit'] : eottae_talkroom_dashboard_feed_default_limit();
$offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0;
$room_id = isset($_POST['room_id']) ? (int) $_POST['room_id'] : 0;
$type = isset($_POST['type']) ? trim(strip_tags((string) $_POST['type'])) : '';

$feed = eottae_talkroom_dashboard_list_feed($mb_id, $room_ids, array(
    'limit'   => $limit,
    'offset'  => $offset,
    'room_id' => $room_id,
    'type'    => $type,
));

$html = eottae_talkroom_dashboard_feed_items_html($feed['items']);

eottae_talkroom_member_token(true);

eottae_talkroom_dashboard_feed_json(true, '', array(
    'items'       => $feed['items'],
    'html'        => $html,
    'has_more'    => !empty($feed['has_more']),
    'next_offset' => (int) ($feed['next_offset'] ?? 0),
    'offset'      => (int) ($feed['offset'] ?? 0),
    'limit'       => (int) ($feed['limit'] ?? $limit),
));
