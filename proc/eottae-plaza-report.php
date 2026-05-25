<?php
/**
 * 세부광장 신고 API
 * POST /proc/eottae-plaza-report.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-plaza-report.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_plaza_report_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_plaza_report_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_plaza_report_json(false, '로그인 후 신고할 수 있습니다.');
}

$token = isset($_POST['eottae_plaza_report_token']) ? trim((string) $_POST['eottae_plaza_report_token']) : '';
if (!eottae_plaza_verify_report_token($token)) {
    eottae_plaza_report_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$target_type = isset($_POST['target_type']) ? trim((string) $_POST['target_type']) : '';
$target_id = isset($_POST['target_id']) ? (int) $_POST['target_id'] : 0;
$reason = isset($_POST['reason']) ? trim((string) $_POST['reason']) : '';
$memo = isset($_POST['memo']) ? (string) $_POST['memo'] : '';

$result = eottae_plaza_submit_report($target_type, $target_id, $member['mb_id'], $reason, $memo);
if (!empty($result['ok'])) {
    eottae_plaza_report_token(true);
}

eottae_plaza_report_json(!empty($result['ok']), $result['message']);
