<?php
/**
 * 세부광장 공감 API
 * POST /proc/eottae-plaza-likes.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-plaza-likes.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_plaza_likes_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_plaza_likes_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_plaza_likes_json(false, '로그인 후 공감할 수 있습니다.');
}

$token = isset($_POST['eottae_plaza_member_token']) ? trim((string) $_POST['eottae_plaza_member_token']) : '';
if (!eottae_plaza_verify_member_token($token)) {
    eottae_plaza_likes_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
if ($wr_id < 1) {
    eottae_plaza_likes_json(false, '글 정보가 올바르지 않습니다.');
}

$result = eottae_plaza_toggle_like($wr_id, $member['mb_id']);
if (!empty($result['ok'])) {
    eottae_plaza_member_token(true);
}

eottae_plaza_likes_json(
    !empty($result['ok']),
    $result['message'],
    array(
        'liked' => (int) ($result['liked'] ?? 0),
        'count' => (int) ($result['count'] ?? 0),
    )
);
