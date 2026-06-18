<?php
/**
 * 세부어때 운영센터 API (최고관리자)
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-push.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_ops_center_json($success, $message, $extra = array())
{
    echo json_encode(array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_ops_center_json(false, '잘못된 요청입니다.');
}

if ($is_admin !== 'super' || empty($member['mb_id'])) {
    eottae_ops_center_json(false, '최고관리자만 이용할 수 있습니다.');
}

$token = isset($_POST['eottae_ops_token']) ? trim((string) $_POST['eottae_ops_token']) : '';
if (!eottae_talkroom_verify_admin_token($token)) {
    eottae_ops_center_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

if ($action === 'send_push_campaign') {
    $title = isset($_POST['title']) ? (string) $_POST['title'] : '';
    $body = isset($_POST['body']) ? (string) $_POST['body'] : '';
    $url = isset($_POST['url']) ? (string) $_POST['url'] : '';
    $limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 500;

    $result = eottae_push_send_campaign($title, $body, $url, $member['mb_id'], $limit);
    eottae_ops_center_json(!empty($result['ok']), (string) ($result['message'] ?? ''), array(
        'campaign_id' => (int) ($result['campaign_id'] ?? 0),
        'sent' => (int) ($result['sent'] ?? 0),
    ));
}

eottae_ops_center_json(false, '지원하지 않는 요청입니다.');
