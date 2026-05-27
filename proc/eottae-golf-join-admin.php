<?php
/**
 * 골프조인 관리자 API
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-golf-join.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_golf_join_admin_json($success, $message, $extra = array())
{
    echo json_encode(array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array()), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_golf_join_admin_json(false, '잘못된 요청입니다.');
}

$check = eottae_golf_join_admin_assert($is_admin ?? '');
if (empty($check['ok'])) {
    eottae_golf_join_admin_json(false, $check['message']);
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$token = isset($_POST['eottae_talkroom_admin_token']) ? trim((string) $_POST['eottae_talkroom_admin_token']) : '';
if (!function_exists('eottae_talkroom_verify_admin_token') || !eottae_talkroom_verify_admin_token($token)) {
    eottae_golf_join_admin_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.');
}

$join_id = isset($_POST['join_id']) ? (int) $_POST['join_id'] : 0;
$report_id = isset($_POST['report_id']) ? (int) $_POST['report_id'] : 0;
$course_id = isset($_POST['course_id']) ? (int) $_POST['course_id'] : 0;

if ($action === 'set_status' && $join_id > 0) {
    $result = eottae_golf_join_admin_set_post_status($join_id, $_POST['status'] ?? '');
    eottae_golf_join_admin_json(!empty($result['ok']), $result['message'] ?? '', array('reload' => !empty($result['ok'])));
}

if ($action === 'hide' && $join_id > 0) {
    $result = eottae_golf_join_admin_hide_post($join_id);
    eottae_golf_join_admin_json(!empty($result['ok']), $result['message'] ?? '', array('reload' => !empty($result['ok'])));
}

if ($action === 'restore' && $join_id > 0) {
    $result = eottae_golf_join_admin_restore_post($join_id);
    eottae_golf_join_admin_json(!empty($result['ok']), $result['message'] ?? '', array('reload' => !empty($result['ok'])));
}

if ($action === 'resolve_report' && $report_id > 0) {
    $result = eottae_golf_join_admin_resolve_report($report_id, $member['mb_id'] ?? '', $_POST['admin_memo'] ?? '');
    eottae_golf_join_admin_json(!empty($result['ok']), $result['message'] ?? '', array('reload' => !empty($result['ok'])));
}

if ($action === 'save_course') {
    $result = eottae_golf_join_admin_save_course($_POST, $course_id);
    eottae_golf_join_admin_json(!empty($result['ok']), $result['message'] ?? '', array('reload' => !empty($result['ok'])));
}

eottae_golf_join_admin_json(false, '지원하지 않는 요청입니다.');
