<?php
/**
 * 세부어때 캘린더 관리 API (최고관리자)
 * POST /proc/eottae-calendar-admin.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
include_once G5_LIB_PATH.'/eottae-calendar-report.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_calendar_admin_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_calendar_admin_json(false, '잘못된 요청입니다.');
}

if ($is_admin !== 'super' || empty($member['mb_id'])) {
    eottae_calendar_admin_json(false, '최고관리자만 이용할 수 있습니다.');
}

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

$token = isset($_POST['eottae_talkroom_admin_token']) ? trim((string) $_POST['eottae_talkroom_admin_token']) : '';
if (!eottae_talkroom_verify_admin_token($token)) {
    eottae_calendar_admin_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$report_id = isset($_POST['report_id']) ? (int) $_POST['report_id'] : 0;

if ($report_id < 1) {
    eottae_calendar_admin_json(false, '신고 정보가 올바르지 않습니다.');
}

$allowed = array('review', 'reject', 'hide_event', 'delete_event');
if (!in_array($action, $allowed, true)) {
    eottae_calendar_admin_json(false, '지원하지 않는 요청입니다.');
}

$result = eottae_calendar_handle_report($report_id, $action, $member['mb_id'], true);
if (!empty($result['ok'])) {
    eottae_talkroom_admin_token(true);
}

eottae_calendar_admin_json(!empty($result['ok']), (string) ($result['message'] ?? ''));
