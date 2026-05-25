<?php
/**
 * 세부톡방 알림 API
 * POST /proc/eottae-talkroom-notifications.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-notify.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_talkroom_notify_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_talkroom_notify_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_talkroom_notify_json(false, '로그인 후 이용해 주세요.');
}

$token = isset($_POST['eottae_talkroom_member_token']) ? trim((string) $_POST['eottae_talkroom_member_token']) : '';
if (!eottae_talkroom_verify_member_token($token)) {
    eottae_talkroom_notify_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$mb_id = $member['mb_id'];

if ($action === 'mark_all') {
    $result = eottae_talkroom_notify_mark_all_read($mb_id);
    if (!empty($result['ok'])) {
        eottae_talkroom_member_token(true);
    }
    eottae_talkroom_notify_json(!empty($result['ok']), $result['message'], array(
        'updated' => (int) ($result['updated'] ?? 0),
    ));
}

$notification_id = isset($_POST['notification_id']) ? (int) $_POST['notification_id'] : 0;
if ($notification_id < 1) {
    eottae_talkroom_notify_json(false, '알림 정보가 올바르지 않습니다.');
}

if ($action === 'mark_read') {
    $owned = eottae_talkroom_notify_get_owned($notification_id, $mb_id);
    if (!$owned) {
        eottae_talkroom_notify_json(false, '접근할 수 없는 알림입니다.');
    }

    $result = eottae_talkroom_notify_mark_read($notification_id, $mb_id);
    if (!empty($result['ok'])) {
        eottae_talkroom_member_token(true);
    }
    eottae_talkroom_notify_json(!empty($result['ok']), $result['message'], array(
        'notification_id' => $notification_id,
        'href'            => function_exists('eottae_talkroom_sanitize_internal_href')
            ? eottae_talkroom_sanitize_internal_href($owned['href'] ?? '', '')
            : trim((string) ($owned['href'] ?? '')),
    ));
}

eottae_talkroom_notify_json(false, '지원하지 않는 요청입니다.');
