<?php
/**
 * 골프조인 방장 API
 * POST /proc/eottae-golf-join-owner.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-golf-join.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_golf_join_owner_json($success, $message, $extra = array())
{
    echo json_encode(array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array()), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_golf_join_owner_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_golf_join_owner_json(false, '로그인이 필요합니다.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$join_id = isset($_POST['join_id']) ? (int) $_POST['join_id'] : 0;
$token = isset($_POST['eottae_golf_join_owner_token']) ? trim((string) $_POST['eottae_golf_join_owner_token']) : '';

if ($join_id < 1) {
    eottae_golf_join_owner_json(false, '조인 정보가 올바르지 않습니다.');
}

if (!eottae_golf_join_verify_owner_token($token)) {
    eottae_golf_join_owner_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.');
}

if ($action === 'approve') {
    $member_id = isset($_POST['member_id']) ? (int) $_POST['member_id'] : 0;
    $result = eottae_golf_join_approve_member($join_id, $member_id, $member['mb_id']);
    if (!empty($result['ok'])) {
        eottae_golf_join_owner_token(true);
    }
    eottae_golf_join_owner_json(!empty($result['ok']), $result['message'] ?? '', array(
        'reload' => !empty($result['ok']),
    ));
}

if ($action === 'reject') {
    $member_id = isset($_POST['member_id']) ? (int) $_POST['member_id'] : 0;
    $result = eottae_golf_join_reject_member($join_id, $member_id, $member['mb_id']);
    if (!empty($result['ok'])) {
        eottae_golf_join_owner_token(true);
    }
    eottae_golf_join_owner_json(!empty($result['ok']), $result['message'] ?? '', array(
        'reload' => !empty($result['ok']),
    ));
}

if ($action === 'close') {
    $result = eottae_golf_join_close_post($join_id, $member['mb_id']);
    if (!empty($result['ok'])) {
        eottae_golf_join_owner_token(true);
    }
    eottae_golf_join_owner_json(!empty($result['ok']), $result['message'] ?? '', array(
        'reload' => !empty($result['ok']),
    ));
}

eottae_golf_join_owner_json(false, '지원하지 않는 요청입니다.');
