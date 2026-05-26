<?php
/**
 * 홈 공개톡 AI 관리 API (최고관리자)
 * POST /proc/eottae-public-ai-admin.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_public_ai_admin_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_public_ai_admin_json(false, '잘못된 요청입니다.');
}

if ($is_admin !== 'super' || empty($member['mb_id'])) {
    eottae_public_ai_admin_json(false, '최고관리자만 이용할 수 있습니다.');
}

$token = isset($_POST['eottae_public_ai_admin_token']) ? trim((string) $_POST['eottae_public_ai_admin_token']) : '';
if (!eottae_public_ai_verify_admin_token($token)) {
    eottae_public_ai_admin_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$mb_id = (string) $member['mb_id'];

if ($action === 'save_settings') {
    $result = eottae_public_ai_save_settings(array(
        'ai_enabled'             => !empty($_POST['ai_enabled']),
        'ai_name'                => isset($_POST['ai_name']) ? (string) $_POST['ai_name'] : '',
        'ai_persona'             => isset($_POST['ai_persona']) ? (string) $_POST['ai_persona'] : '',
        'auto_publish'           => !empty($_POST['auto_publish']),
        'require_admin_approval' => !empty($_POST['require_admin_approval']),
        'max_messages_per_day'   => isset($_POST['max_messages_per_day']) ? (int) $_POST['max_messages_per_day'] : 3,
        'min_silence_minutes'    => isset($_POST['min_silence_minutes']) ? (int) $_POST['min_silence_minutes'] : 180,
        'active_start_time'      => isset($_POST['active_start_time']) ? (string) $_POST['active_start_time'] : '',
        'active_end_time'        => isset($_POST['active_end_time']) ? (string) $_POST['active_end_time'] : '',
        'use_calendar'           => !empty($_POST['use_calendar']),
        'use_weather'            => !empty($_POST['use_weather']),
        'use_holidays'           => !empty($_POST['use_holidays']),
        'use_talk_rooms'         => !empty($_POST['use_talk_rooms']),
        'use_popular_posts'      => !empty($_POST['use_popular_posts']),
        'use_business_events'    => !empty($_POST['use_business_events']),
        'use_external_news'      => !empty($_POST['use_external_news']),
    ), $mb_id);

    if (!empty($result['ok'])) {
        eottae_public_ai_admin_token(true);
    }

    eottae_public_ai_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'save_candidate') {
    $result = eottae_public_ai_save_candidate(array(
        'candidate_id' => isset($_POST['candidate_id']) ? (int) $_POST['candidate_id'] : 0,
        'trigger_type' => isset($_POST['trigger_type']) ? (string) $_POST['trigger_type'] : '',
        'source_type'  => isset($_POST['source_type']) ? (string) $_POST['source_type'] : '',
        'source_id'    => isset($_POST['source_id']) ? (int) $_POST['source_id'] : 0,
        'title'        => isset($_POST['title']) ? (string) $_POST['title'] : '',
        'message'      => isset($_POST['message']) ? (string) $_POST['message'] : '',
        'action_label' => isset($_POST['action_label']) ? (string) $_POST['action_label'] : '',
        'action_url'   => isset($_POST['action_url']) ? (string) $_POST['action_url'] : '',
        'admin_memo'   => isset($_POST['admin_memo']) ? (string) $_POST['admin_memo'] : '',
    ), $mb_id);

    if (!empty($result['ok'])) {
        eottae_public_ai_admin_token(true);
    }

    eottae_public_ai_admin_json(!empty($result['ok']), $result['message'], array(
        'candidate_id' => (int) ($result['candidate_id'] ?? 0),
    ));
}

if ($action === 'approve_candidate') {
    $candidate_id = isset($_POST['candidate_id']) ? (int) $_POST['candidate_id'] : 0;
    $result = eottae_public_ai_set_candidate_status($candidate_id, 'approved', $mb_id);
    if (!empty($result['ok'])) {
        eottae_public_ai_admin_token(true);
    }
    eottae_public_ai_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'reject_candidate') {
    $candidate_id = isset($_POST['candidate_id']) ? (int) $_POST['candidate_id'] : 0;
    $result = eottae_public_ai_set_candidate_status($candidate_id, 'rejected', $mb_id);
    if (!empty($result['ok'])) {
        eottae_public_ai_admin_token(true);
    }
    eottae_public_ai_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'delete_candidate') {
    $candidate_id = isset($_POST['candidate_id']) ? (int) $_POST['candidate_id'] : 0;
    $result = eottae_public_ai_set_candidate_status($candidate_id, 'deleted', $mb_id);
    if (!empty($result['ok'])) {
        eottae_public_ai_admin_token(true);
    }
    eottae_public_ai_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'test_publish_candidate') {
    $candidate_id = isset($_POST['candidate_id']) ? (int) $_POST['candidate_id'] : 0;
    $result = eottae_public_ai_test_publish_candidate($candidate_id, $mb_id);
    if (!empty($result['ok'])) {
        eottae_public_ai_admin_token(true);
    }
    eottae_public_ai_admin_json(!empty($result['ok']), $result['message']);
}

eottae_public_ai_admin_json(false, '지원하지 않는 요청입니다.');
