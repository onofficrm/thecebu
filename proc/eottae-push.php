<?php
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-push.lib.php';

header('Content-Type: application/json; charset=utf-8');

global $is_member, $member;

function eottae_push_json($success, $message = '', $extra = array())
{
    echo json_encode(array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$action = isset($_REQUEST['action']) ? trim((string) $_REQUEST['action']) : 'state';
$logged_in = !empty($is_member) && !empty($member['mb_id']);
$mb_id = $logged_in ? (string) $member['mb_id'] : '';

if ($action === 'state') {
    $summary = array('total' => 0);
    if ($logged_in && is_file(G5_LIB_PATH.'/eottae-notification.lib.php')) {
        include_once G5_LIB_PATH.'/eottae-notification.lib.php';
        if (function_exists('eottae_mypage_notification_summary')) {
            $summary = eottae_mypage_notification_summary($mb_id);
        }
    }

    eottae_push_json(true, '', array(
        'logged_in' => $logged_in,
        'login_url' => function_exists('eottae_login_url') ? eottae_login_url(G5_URL.'/page/eottae-notifications.php') : G5_BBS_URL.'/login.php',
        'enabled' => eottae_push_enabled(),
        'configured' => eottae_push_is_configured(),
        'public_key' => eottae_push_public_key(),
        'token' => $logged_in ? eottae_push_token(false) : '',
        'sw_version' => is_file(G5_PATH.'/eottae-service-worker.js') ? (string) filemtime(G5_PATH.'/eottae-service-worker.js') : '',
        'subscription_count' => $logged_in ? eottae_push_member_subscription_count($mb_id) : 0,
        'unread_total' => (int) ($summary['total'] ?? 0),
        'app_only' => function_exists('g5site_cfg') ? (bool) g5site_cfg('web_push_prompt_app_only', true) : true,
    ));
}

if ($action === 'latest') {
    if (!$logged_in) {
        eottae_push_json(false, '로그인이 필요합니다.', array('logged_in' => false));
    }
    eottae_push_json(true, '', eottae_push_latest_payload($mb_id));
}

if (!$logged_in) {
    eottae_push_json(false, '로그인 후 이용해 주세요.', array('logged_in' => false));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_push_json(false, '잘못된 요청입니다.');
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$token = isset($payload['token']) ? trim((string) $payload['token']) : (isset($_POST['token']) ? trim((string) $_POST['token']) : '');
if (!eottae_push_verify_token($token)) {
    eottae_push_json(false, '보안 토큰이 만료되었습니다.');
}

if ($action === 'subscribe') {
    $subscription = isset($payload['subscription']) && is_array($payload['subscription']) ? $payload['subscription'] : array();
    $result = eottae_push_register_subscription($mb_id, $subscription);
    eottae_push_json(!empty($result['ok']), $result['message'] ?? '', array(
        'subscription_count' => eottae_push_member_subscription_count($mb_id),
    ));
}

if ($action === 'unsubscribe') {
    $endpoint = isset($payload['endpoint']) ? trim((string) $payload['endpoint']) : '';
    $result = eottae_push_unregister_subscription($mb_id, $endpoint);
    eottae_push_json(!empty($result['ok']), $result['message'] ?? '', array(
        'subscription_count' => eottae_push_member_subscription_count($mb_id),
    ));
}

eottae_push_json(false, '지원하지 않는 요청입니다.');
