<?php
/**
 * 세부어때 캘린더 일정 신고 API
 * POST /proc/eottae-calendar-report.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
include_once G5_LIB_PATH.'/eottae-calendar-report.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_calendar_report_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_calendar_report_json(false, '잘못된 요청입니다.');
}

if (!$is_member || empty($member['mb_id'])) {
    eottae_calendar_report_json(false, '로그인 후 신고할 수 있습니다.');
}

$token = isset($_POST['eottae_calendar_report_token']) ? trim((string) $_POST['eottae_calendar_report_token']) : '';
if (!eottae_calendar_verify_report_token($token)) {
    eottae_calendar_report_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
$reason = isset($_POST['reason']) ? trim((string) $_POST['reason']) : '';
$memo = isset($_POST['memo']) ? trim((string) $_POST['memo']) : '';

$result = eottae_calendar_submit_report($event_id, $member['mb_id'], $reason, $memo);
if (!empty($result['ok'])) {
    eottae_calendar_report_token(true);
}

eottae_calendar_report_json(!empty($result['ok']), (string) ($result['message'] ?? ''));
