<?php
/**
 * 세부 생활정보 컬럼 API
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_LIB_PATH.'/eottae-column-likes.lib.php';
include_once G5_LIB_PATH.'/eottae-column-bookmarks.lib.php';
include_once G5_LIB_PATH.'/eottae-column-report.lib.php';

global $is_member, $member, $is_admin;

$is_json = isset($_POST['response']) && $_POST['response'] === 'json';

function eottae_column_proc_json($success, $message, $extra = array())
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array()), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_column_proc_json(false, '잘못된 요청입니다.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$token = isset($_POST['eottae_column_token']) ? trim((string) $_POST['eottae_column_token']) : '';

if ($action === 'delete') {
    if (!eottae_column_verify_member_token($token)) {
        alert('보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.', eottae_column_list_url());
    }
    if (!$is_member) {
        alert('로그인이 필요합니다.', eottae_column_list_url());
    }

    $wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
    $result = eottae_column_delete_post($wr_id, $member['mb_id'] ?? '', $is_admin === 'super');
    eottae_column_member_token(true);

    if (!empty($result['ok'])) {
        goto_url($result['list_url'] ?? eottae_column_list_url());
    }
    alert($result['message'] ?? '삭제에 실패했습니다.', eottae_column_view_url($wr_id));
}

if ($action === 'save') {
    if (!eottae_column_verify_member_token($token)) {
        alert('보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.', eottae_column_write_url());
    }
    if (!$is_member) {
        alert('로그인이 필요합니다.', eottae_column_list_url());
    }

    $result = eottae_column_save_post($_POST, $member, $is_admin === 'super');
    eottae_column_member_token(true);

    if (!empty($result['ok'])) {
        goto_url($result['view_url'] ?? eottae_column_view_url($result['wr_id'] ?? 0));
    }
    alert($result['message'] ?? '저장에 실패했습니다.', eottae_column_write_url((int) ($_POST['wr_id'] ?? 0)));
}

if ($action === 'apply_columnist') {
    if (!eottae_column_verify_member_token($token)) {
        alert('보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.', eottae_column_apply_url());
    }
    if (!$is_member) {
        alert('로그인이 필요합니다.', eottae_column_apply_url());
    }

    $result = eottae_column_submit_application($_POST, $member);
    eottae_column_member_token(true);

    if (!empty($result['ok'])) {
        alert($result['message'] ?? '신청이 접수되었습니다.', eottae_column_mypage_url());
    }
    alert($result['message'] ?? '신청 접수에 실패했습니다.', eottae_column_apply_url());
}

if (!eottae_column_verify_member_token($token)) {
    eottae_column_proc_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.');
}

if (!$is_member) {
    eottae_column_proc_json(false, '로그인이 필요합니다.');
}

if ($action === 'toggle_like') {
    $wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
    $result = eottae_column_toggle_like($wr_id, $member['mb_id']);
    eottae_column_proc_json(!empty($result['ok']), $result['message'] ?? '', array(
        'liked'         => !empty($result['liked']),
        'like_count'    => (int) ($result['like_count'] ?? 0),
        'column_token'  => eottae_column_member_token(false),
    ));
}

if ($action === 'toggle_bookmark') {
    $wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
    $result = eottae_column_toggle_bookmark($wr_id, $member['mb_id']);
    eottae_column_proc_json(!empty($result['ok']), $result['message'] ?? '', array(
        'bookmarked'   => !empty($result['bookmarked']),
        'column_token' => eottae_column_member_token(false),
    ));
}

if ($action === 'report') {
    $report_token = isset($_POST['eottae_column_report_token']) ? trim((string) $_POST['eottae_column_report_token']) : $token;
    if (!eottae_column_verify_report_token($report_token)) {
        eottae_column_proc_json(false, '보안 토큰이 만료되었습니다.');
    }
    $wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
    $reason = isset($_POST['reason']) ? (string) $_POST['reason'] : '';
    $memo = isset($_POST['memo']) ? (string) $_POST['memo'] : '';
    $result = eottae_column_submit_report($wr_id, $member['mb_id'], $reason, $memo);
    eottae_column_report_token(true);
    eottae_column_proc_json(!empty($result['ok']), $result['message'] ?? '');
}

eottae_column_proc_json(false, '알 수 없는 요청입니다.');
