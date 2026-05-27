<?php
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_LIB_PATH.'/eottae-column-report.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

if ($is_admin !== 'super') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => '권한이 없습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$token = isset($_POST['admin_token']) ? trim((string) $_POST['admin_token']) : '';
if (!function_exists('eottae_talkroom_verify_admin_token') || !eottae_talkroom_verify_admin_token($token)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => '관리자 토큰이 유효하지 않습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

function eottae_column_admin_json($ok, $message = '', $extra = array())
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(array('success' => (bool) $ok, 'message' => (string) $message), $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'save_author') {
    $result = eottae_column_save_author($_POST, true);
    eottae_column_admin_json(!empty($result['ok']), $result['message'] ?? '');
}

if ($action === 'save_monthly') {
    $result = eottae_column_save_monthly_award($_POST, $member['mb_id'] ?? '');
    eottae_column_admin_json(!empty($result['ok']), $result['message'] ?? '');
}

if ($action === 'set_flags') {
    $wr_id = (int) ($_POST['wr_id'] ?? 0);
    $flags = array();
    if (isset($_POST['is_featured'])) {
        $flags['is_featured'] = !empty($_POST['is_featured']);
    }
    if (isset($_POST['is_recommended'])) {
        $flags['is_recommended'] = !empty($_POST['is_recommended']);
    }
    if (isset($_POST['status'])) {
        $flags['status'] = (string) $_POST['status'];
    }
    $result = eottae_column_admin_set_flags($wr_id, $flags);
    eottae_column_admin_json(!empty($result['ok']), $result['message'] ?? '');
}

if ($action === 'handle_report') {
    $report_id = (int) ($_POST['report_id'] ?? 0);
    $report_action = (string) ($_POST['report_action'] ?? '');
    $result = eottae_column_handle_report($report_id, $report_action, $member['mb_id'] ?? '');
    eottae_column_admin_json(!empty($result['ok']), $result['message'] ?? '');
}

if ($action === 'review_application') {
    $application_id = (int) ($_POST['application_id'] ?? 0);
    $decision = (string) ($_POST['decision'] ?? '');
    $memo = (string) ($_POST['review_memo'] ?? '');
    $result = eottae_column_review_application($application_id, $decision, $member['mb_id'] ?? '', $memo);
    eottae_column_admin_json(!empty($result['ok']), $result['message'] ?? '');
}

eottae_column_admin_json(false, '알 수 없는 요청입니다.');
