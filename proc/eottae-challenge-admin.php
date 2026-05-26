<?php
/**
 * 세부어때 챌린지 관리 API (최고관리자)
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
include_once G5_LIB_PATH.'/eottae-challenge-report.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_challenge_admin_json($success, $message, $extra = array())
{
    echo json_encode(array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array()), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_challenge_admin_json(false, '잘못된 요청입니다.');
}

if ($is_admin !== 'super' || empty($member['mb_id'])) {
    eottae_challenge_admin_json(false, '최고관리자만 이용할 수 있습니다.');
}

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

$token = isset($_POST['eottae_talkroom_admin_token']) ? trim((string) $_POST['eottae_talkroom_admin_token']) : '';
if (!eottae_talkroom_verify_admin_token($token)) {
    eottae_challenge_admin_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

if ($action === 'save') {
    $challenge_id = isset($_POST['challenge_id']) ? (int) $_POST['challenge_id'] : 0;
    $result = eottae_challenge_admin_save($_POST, $member['mb_id'], $challenge_id);
    if (!empty($result['ok'])) {
        eottae_talkroom_admin_token(true);
    }
    eottae_challenge_admin_json(!empty($result['ok']), $result['message'] ?? '', array(
        'challenge_id' => (int) ($result['challenge_id'] ?? 0),
    ));
}

if ($action === 'delete') {
    $challenge_id = isset($_POST['challenge_id']) ? (int) $_POST['challenge_id'] : 0;
    $result = eottae_challenge_admin_delete($challenge_id);
    if (!empty($result['ok'])) {
        eottae_talkroom_admin_token(true);
    }
    eottae_challenge_admin_json(!empty($result['ok']), $result['message'] ?? '');
}

if ($action === 'set_best') {
    $entry_id = isset($_POST['entry_id']) ? (int) $_POST['entry_id'] : 0;
    $is_best = !empty($_POST['is_best']);
    $result = eottae_challenge_set_entry_best($entry_id, $is_best, $member['mb_id']);
    if (!empty($result['ok'])) {
        eottae_talkroom_admin_token(true);
    }
    eottae_challenge_admin_json(!empty($result['ok']), $result['message'] ?? '');
}

if ($action === 'hide_entry') {
    $entry_id = isset($_POST['entry_id']) ? (int) $_POST['entry_id'] : 0;
    $entries = eottae_challenge_entries_table();
    sql_query(" UPDATE `{$entries}` SET status = 'hidden', updated_at = '".G5_TIME_YMDHIS."' WHERE entry_id = '".(int) $entry_id."' ", false);
    eottae_talkroom_admin_token(true);
    eottae_challenge_admin_json(true, '참여글을 숨김 처리했습니다.');
}

if ($action === 'handle_report') {
    $report_id = isset($_POST['report_id']) ? (int) $_POST['report_id'] : 0;
    $report_action = isset($_POST['report_action']) ? trim((string) $_POST['report_action']) : 'review';
    $result = eottae_challenge_handle_report($report_id, $report_action, $member['mb_id']);
    if (!empty($result['ok'])) {
        eottae_talkroom_admin_token(true);
    }
    eottae_challenge_admin_json(!empty($result['ok']), $result['message'] ?? '');
}

eottae_challenge_admin_json(false, '지원하지 않는 요청입니다.');
