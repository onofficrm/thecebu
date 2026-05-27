<?php
/**
 * 골프조인 신청 API
 * POST /proc/eottae-golf-join-member.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-golf-join.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_golf_join_member_json($success, $message, $extra = array())
{
    echo json_encode(array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array()), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_golf_join_member_json(false, '잘못된 요청입니다.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$join_id = isset($_POST['join_id']) ? (int) $_POST['join_id'] : 0;
$token = isset($_POST['eottae_golf_join_token']) ? trim((string) $_POST['eottae_golf_join_token']) : '';

if ($join_id < 1) {
    eottae_golf_join_member_json(false, '조인 정보가 올바르지 않습니다.');
}

if (!eottae_golf_join_verify_member_token($token)) {
    eottae_golf_join_member_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    $login = function_exists('eottae_login_url')
        ? eottae_login_url(eottae_golf_join_detail_url($join_id))
        : G5_BBS_URL.'/login.php';
    eottae_golf_join_member_json(false, '로그인이 필요합니다.', array('redirect' => $login));
}

if ($action === 'apply') {
    $message = isset($_POST['message']) ? (string) $_POST['message'] : '';
    $result = eottae_golf_join_apply($join_id, $member, $message);
    if (!empty($result['ok'])) {
        eottae_golf_join_member_token(true);
    }
    eottae_golf_join_member_json(!empty($result['ok']), $result['message'] ?? '', array(
        'reload' => !empty($result['ok']),
    ));
}

if ($action === 'cancel_apply') {
    $result = eottae_golf_join_cancel_apply($join_id, $member['mb_id']);
    if (!empty($result['ok'])) {
        eottae_golf_join_member_token(true);
    }
    eottae_golf_join_member_json(!empty($result['ok']), $result['message'] ?? '', array(
        'reload' => !empty($result['ok']),
    ));
}

eottae_golf_join_member_json(false, '지원하지 않는 요청입니다.');
