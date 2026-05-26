<?php
/**
 * 리뷰 수정 API
 * POST /proc/eottae-review-update.php
 */
define('EOTTae_REVIEW_UPDATE', true);

chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';

if (!defined('_GNUBOARD_')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => '접근이 올바르지 않습니다.'));
    exit;
}

include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-review-manage.lib.php';

function eottae_review_update_json($success, $message, $redirect_url = '')
{
    $payload = array(
        'success' => (bool) $success,
        'message' => (string) $message,
    );
    if ($redirect_url !== '') {
        $payload['redirect_url'] = (string) $redirect_url;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_review_update_json(false, '잘못된 요청입니다.');
}

if (!$is_member || empty($member['mb_id'])) {
    eottae_review_update_json(false, '로그인이 필요합니다.');
}

$token = isset($_POST['eottae_review_token']) ? trim((string) $_POST['eottae_review_token']) : '';
$session_token = get_session('eottae_review_token');
if ($token === '' || $session_token === '' || !hash_equals($session_token, $token)) {
    eottae_review_update_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$review_wr_id = isset($_POST['review_wr_id']) ? (int) $_POST['review_wr_id'] : 0;
$shop_wr_id = isset($_POST['shop_wr_id']) ? (int) $_POST['shop_wr_id'] : 0;
$rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
$content = isset($_POST['content']) ? (string) $_POST['content'] : '';

$result = eottae_review_update_execute(
    $review_wr_id,
    $rating,
    $content,
    $member['mb_id'],
    $is_admin
);

if (empty($result['ok'])) {
    eottae_review_update_json(false, $result['message']);
}

$shop_wr_id = (int) ($result['shop_wr_id'] ?? $shop_wr_id);
$redirect = $shop_wr_id > 0
    ? G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE.'&wr_id='.$shop_wr_id.'#review-'.$review_wr_id
    : '';

eottae_review_update_json(true, $result['message'], $redirect);
