<?php
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_LIB_PATH.'/eottae-column-report.lib.php';
include_once G5_LIB_PATH.'/eottae-column-admin-authors.lib.php';
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

if ($action === 'send_author_memo') {
    $scope = isset($_POST['memo_scope']) ? preg_replace('/[^a-z_]/', '', (string) $_POST['memo_scope']) : 'selected';
    $memo_body = isset($_POST['memo_body']) ? (string) $_POST['memo_body'] : '';
    $mb_ids = array();
    if (!empty($_POST['mb_ids']) && is_array($_POST['mb_ids'])) {
        $mb_ids = $_POST['mb_ids'];
    } elseif (!empty($_POST['mb_ids']) && is_string($_POST['mb_ids'])) {
        $mb_ids = explode(',', $_POST['mb_ids']);
    }

    $recv_mb_ids = eottae_column_admin_author_mb_ids_for_scope($scope, array(
        'mb_ids' => $mb_ids,
        'search' => isset($_POST['list_search']) ? (string) $_POST['list_search'] : '',
    ));

    $result = eottae_column_admin_send_memo($member['mb_id'] ?? '', $recv_mb_ids, $memo_body);
    eottae_column_admin_json(!empty($result['ok']), $result['message'] ?? '', array(
        'sent'     => (int) ($result['sent'] ?? 0),
        'skipped'  => (int) ($result['skipped'] ?? 0),
    ));
}

if ($action === 'toggle_author_flag') {
    $mb_id = isset($_POST['mb_id']) ? preg_replace('/[^a-z0-9_]/i', '', (string) $_POST['mb_id']) : '';
    $field = isset($_POST['field']) ? preg_replace('/[^a-z_]/', '', (string) $_POST['field']) : '';
    $value = isset($_POST['value']) ? (int) $_POST['value'] : 0;
    $result = eottae_column_admin_toggle_author_flag($mb_id, $field, $value);
    eottae_column_admin_json(!empty($result['ok']), $result['message'] ?? '', array(
        'field' => $result['field'] ?? $field,
        'value' => (int) ($result['value'] ?? $value),
    ));
}

eottae_column_admin_json(false, '알 수 없는 요청입니다.');
