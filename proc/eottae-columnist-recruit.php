<?php
/**
 * 컬럼리스트 모집 랜딩 신청
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-columnist-recruit.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_columnist_recruit_json($success, $message, $redirect = '')
{
    echo json_encode(array(
        'success'  => (bool) $success,
        'message'  => (string) $message,
        'redirect' => (string) $redirect,
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_columnist_recruit_json(false, '잘못된 요청입니다.');
}

$token = isset($_POST['eottae_columnist_recruit_token']) ? trim((string) $_POST['eottae_columnist_recruit_token']) : '';
if (!eottae_columnist_recruit_verify_token($token)) {
    eottae_columnist_recruit_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$last_key = 'eottae_columnist_recruit_last_submit';
$last_time = (int) get_session($last_key);
if ($last_time > 0 && G5_SERVER_TIME - $last_time < 30) {
    eottae_columnist_recruit_json(false, '잠시 후 다시 시도해 주세요.');
}

global $is_member, $member;
$member_row = ($is_member && is_array($member)) ? $member : array();

$_POST['referer'] = isset($_POST['referer']) ? $_POST['referer'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');

$result = eottae_columnist_recruit_submit($_POST, $member_row);
eottae_columnist_recruit_token(true);

if (empty($result['ok'])) {
    eottae_columnist_recruit_json(false, $result['message'] ?? '신청 접수에 실패했습니다.');
}

set_session($last_key, G5_SERVER_TIME);
$redirect = eottae_columnist_recruit_url(array('submitted' => '1'));
eottae_columnist_recruit_json(true, $result['message'] ?? '신청이 접수되었습니다.', $redirect);
