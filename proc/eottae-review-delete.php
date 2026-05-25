<?php
/**
 * 리뷰 삭제 API
 * POST /proc/eottae-review-delete.php
 * - action=super_delete (최고관리자 즉시 삭제)
 * - action=request (업체관리자 삭제 요청)
 * - action=approve / reject (최고관리자 승인·반려)
 */
define('EOTTae_REVIEW_DELETE', true);

chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';

if (!defined('_GNUBOARD_')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => '접근이 올바르지 않습니다.'));
    exit;
}

include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-review-delete.lib.php';
eottae_review_delete_ensure_schema();

function eottae_review_delete_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_review_delete_json(false, '잘못된 요청입니다.');
}

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($action === 'approve' || $action === 'reject') {
    if ($is_admin !== 'super' || empty($member['mb_id'])) {
        eottae_review_delete_json(false, '최고관리자만 처리할 수 있습니다.');
    }

    $rdr_id = isset($_POST['rdr_id']) ? (int) $_POST['rdr_id'] : 0;
    if ($rdr_id < 1) {
        eottae_review_delete_json(false, '요청 정보가 올바르지 않습니다.');
    }

    if ($action === 'approve') {
        $result = eottae_review_delete_request_approve($rdr_id, $member['mb_id']);
    } else {
        $note = isset($_POST['process_note']) ? trim($_POST['process_note']) : '';
        $result = eottae_review_delete_request_reject($rdr_id, $member['mb_id'], $note);
    }

    eottae_review_delete_json(!empty($result['ok']), $result['message']);
}

if (!$is_member || empty($member['mb_id'])) {
    eottae_review_delete_json(false, '로그인이 필요합니다.');
}

$review_wr_id = isset($_POST['review_wr_id']) ? (int) $_POST['review_wr_id'] : 0;
$shop_wr_id = isset($_POST['shop_wr_id']) ? (int) $_POST['shop_wr_id'] : 0;

if ($review_wr_id < 1 || $shop_wr_id < 1) {
    eottae_review_delete_json(false, '리뷰 정보가 올바르지 않습니다.');
}

if ($action === 'super_delete') {
    if ($is_admin !== 'super') {
        eottae_review_delete_json(false, '최고관리자만 리뷰를 삭제할 수 있습니다.');
    }

    $token = isset($_POST['eottae_review_delete_token']) ? trim($_POST['eottae_review_delete_token']) : '';
    $session_token = get_session('eottae_review_delete_token');
    if ($token === '' || $session_token === '' || !hash_equals($session_token, $token)) {
        eottae_review_delete_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
    }

    $result = eottae_review_delete_execute($review_wr_id);
    if (!empty($result['ok'])) {
        set_session('eottae_review_delete_token', '');
    }

    eottae_review_delete_json(
        !empty($result['ok']),
        $result['message'],
        array('shop_wr_id' => isset($result['shop_wr_id']) ? (int) $result['shop_wr_id'] : $shop_wr_id)
    );
}

if ($action === 'request') {
    if (!eottae_is_business_member($member)) {
        eottae_review_delete_json(false, '업체 관리자만 삭제를 요청할 수 있습니다.');
    }

    $token = isset($_POST['eottae_review_delete_token']) ? trim($_POST['eottae_review_delete_token']) : '';
    $session_token = get_session('eottae_review_delete_token');
    if ($token === '' || $session_token === '' || !hash_equals($session_token, $token)) {
        eottae_review_delete_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
    }

    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    $result = eottae_review_delete_request_create($review_wr_id, $shop_wr_id, $member['mb_id'], $reason);
    if (!empty($result['ok'])) {
        set_session('eottae_review_delete_token', '');
    }

    eottae_review_delete_json(!empty($result['ok']), $result['message']);
}

eottae_review_delete_json(false, '지원하지 않는 요청입니다.');
