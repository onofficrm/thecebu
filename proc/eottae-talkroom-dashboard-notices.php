<?php
/**
 * 마이페이지 — 내 톡방 공지 API
 * POST /proc/eottae-talkroom-dashboard-notices.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-reads.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-dashboard.lib.php';
include_once G5_PATH.'/components/eottae/talk-mypage-dashboard.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_talkroom_dashboard_notices_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_talkroom_dashboard_notices_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_talkroom_dashboard_notices_json(false, '로그인 후 이용해 주세요.');
}

$token = isset($_POST['eottae_talkroom_member_token']) ? trim((string) $_POST['eottae_talkroom_member_token']) : '';
if (!eottae_talkroom_verify_member_token($token)) {
    eottae_talkroom_dashboard_notices_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$mb_id = $member['mb_id'];
$my = eottae_talkroom_list_my_rooms($mb_id);
$room_ids = eottae_talkroom_dashboard_feed_room_ids_from_my($my);

$limit = isset($_POST['limit']) ? (int) $_POST['limit'] : eottae_talkroom_dashboard_notices_default_limit();
$offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0;

$notices = eottae_talkroom_dashboard_list_notices($mb_id, $room_ids, array(
    'limit'  => $limit,
    'offset' => $offset,
));

$html = eottae_talkroom_dashboard_notice_items_html($notices['items']);

eottae_talkroom_member_token(true);

eottae_talkroom_dashboard_notices_json(true, '', array(
    'items'       => $notices['items'],
    'html'        => $html,
    'has_more'    => !empty($notices['has_more']),
    'next_offset' => (int) ($notices['next_offset'] ?? 0),
    'offset'      => (int) ($notices['offset'] ?? 0),
    'limit'       => (int) ($notices['limit'] ?? $limit),
));
