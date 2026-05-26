<?php
/**
 * 커뮤니티 관리 API (최고관리자)
 * POST /proc/eottae-community-admin.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-community-report.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_community_admin_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_community_admin_json(false, '잘못된 요청입니다.');
}

if ($is_admin !== 'super' || empty($member['mb_id'])) {
    eottae_community_admin_json(false, '최고관리자만 이용할 수 있습니다.');
}

$token = isset($_POST['eottae_community_admin_token']) ? trim((string) $_POST['eottae_community_admin_token']) : '';
if (!eottae_community_verify_admin_token($token)) {
    eottae_community_admin_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$report_id = isset($_POST['report_id']) ? (int) $_POST['report_id'] : 0;
if ($report_id < 1) {
    eottae_community_admin_json(false, '신고 정보가 올바르지 않습니다.');
}

$action_map = array(
    'report_review'         => 'review',
    'report_dismiss'        => 'reject',
    'report_delete_content' => 'delete',
);

if (!isset($action_map[$action])) {
    eottae_community_admin_json(false, '지원하지 않는 요청입니다.');
}

$result = eottae_community_admin_handle_report($report_id, $action_map[$action], $member['mb_id']);
if (!empty($result['ok'])) {
    eottae_community_admin_token(true);
}

eottae_community_admin_json(!empty($result['ok']), $result['message']);
