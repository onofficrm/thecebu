<?php
/**
 * 세부광장 관리 API (최고관리자)
 * POST /proc/eottae-plaza-admin.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
include_once G5_LIB_PATH.'/eottae-plaza-report.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_plaza_admin_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_plaza_admin_json(false, '잘못된 요청입니다.');
}

if ($is_admin !== 'super' || empty($member['mb_id'])) {
    eottae_plaza_admin_json(false, '최고관리자만 이용할 수 있습니다.');
}

$token = isset($_POST['eottae_plaza_admin_token']) ? trim((string) $_POST['eottae_plaza_admin_token']) : '';
if (!eottae_plaza_verify_admin_token($token)) {
    eottae_plaza_admin_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

if ($action === 'save_ai_settings') {
    include_once G5_LIB_PATH.'/eottae-plaza-ai.lib.php';
    $result = eottae_plaza_ai_save_settings(array(
        'daily_question_enabled' => !empty($_POST['daily_question_enabled']),
        'ai_name'                => isset($_POST['ai_name']) ? (string) $_POST['ai_name'] : '',
    ), $member['mb_id']);
    if (!empty($result['ok'])) {
        eottae_plaza_admin_token(true);
    }
    eottae_plaza_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'test_daily_question') {
    include_once G5_LIB_PATH.'/eottae-plaza-ai.lib.php';
    $result = eottae_plaza_ai_run_daily_question(array(
        'is_test' => true,
        'force'   => true,
    ));
    if (!empty($result['ok']) || ($result['status'] ?? '') === 'posted') {
        eottae_plaza_admin_token(true);
        eottae_plaza_admin_json(true, $result['message'], array(
            'post_id' => (int) ($result['post_id'] ?? 0),
        ));
    }
    eottae_plaza_admin_json(false, $result['message'] ?? '작성에 실패했습니다.');
}

if ($action === 'hide_post') {
    $wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
    if ($wr_id < 1) {
        eottae_plaza_admin_json(false, '글 정보가 올바르지 않습니다.');
    }

    include_once G5_LIB_PATH.'/eottae-plaza-likes.lib.php';
    $post = eottae_plaza_get_post_row($wr_id);
    if (!$post) {
        eottae_plaza_admin_json(false, '글을 찾을 수 없습니다.');
    }

    $result = eottae_plaza_hide_target('post', $wr_id, $member['mb_id']);
    if (!empty($result['ok'])) {
        eottae_plaza_admin_token(true);
    }
    eottae_plaza_admin_json(!empty($result['ok']), $result['message']);
}

$report_id = isset($_POST['report_id']) ? (int) $_POST['report_id'] : 0;
if ($report_id < 1) {
    eottae_plaza_admin_json(false, '지원하지 않는 요청입니다.');
}

$report = eottae_plaza_get_report($report_id);
if (!$report) {
    eottae_plaza_admin_json(false, '신고 정보가 올바르지 않습니다.');
}

$action_map = array(
    'report_review'         => 'review',
    'report_dismiss'        => 'reject',
    'report_delete_content' => 'delete',
);

if (!isset($action_map[$action])) {
    eottae_plaza_admin_json(false, '지원하지 않는 요청입니다.');
}

$result = eottae_plaza_admin_handle_report($report_id, $action_map[$action], $member['mb_id']);
if (!empty($result['ok'])) {
    eottae_plaza_admin_token(true);
}

eottae_plaza_admin_json(!empty($result['ok']), $result['message']);
