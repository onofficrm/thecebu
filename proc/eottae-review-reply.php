<?php
/**
 * 사업자 리뷰 답변 API
 * POST /proc/eottae-review-reply.php (JSON)
 */
define('EOTTae_REVIEW_REPLY', true);

chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';

if (!defined('_GNUBOARD_')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => '접근이 올바르지 않습니다.'));
    exit;
}

include_once G5_LIB_PATH.'/eottae.lib.php';

function eottae_review_reply_json($success, $message, $redirect_url = '')
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
    eottae_review_reply_json(false, '잘못된 요청입니다.');
}

if (!$is_member || empty($member['mb_id'])) {
    eottae_review_reply_json(false, '로그인 후 답변을 작성할 수 있습니다.');
}

if (!eottae_is_business_member($member)) {
    eottae_review_reply_json(false, '사업자 회원만 답변을 작성할 수 있습니다.');
}

$token = isset($_POST['eottae_review_reply_token']) ? trim($_POST['eottae_review_reply_token']) : '';
$session_token = get_session('eottae_review_reply_token');
if ($token === '' || $session_token === '' || !hash_equals($session_token, $token)) {
    eottae_review_reply_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$review_wr_id = isset($_POST['review_wr_id']) ? (int) $_POST['review_wr_id'] : 0;
$shop_wr_id = isset($_POST['shop_wr_id']) ? (int) $_POST['shop_wr_id'] : 0;
$content = isset($_POST['content']) ? trim(strip_tags($_POST['content'])) : '';

if ($review_wr_id < 1 || $shop_wr_id < 1) {
    eottae_review_reply_json(false, '리뷰 정보가 올바르지 않습니다.');
}

if ($content === '') {
    eottae_review_reply_json(false, '답변 내용을 입력해 주세요.');
}

$len = function_exists('mb_strlen') ? mb_strlen($content, 'UTF-8') : strlen($content);
if ($len < 2) {
    eottae_review_reply_json(false, '답변 내용을 2자 이상 입력해 주세요.');
}
if ($len > 500) {
    eottae_review_reply_json(false, '답변 내용은 500자 이내로 입력해 주세요.');
}

if (!eottae_business_owns_shop($member['mb_id'], $shop_wr_id)) {
    eottae_review_reply_json(false, '본인 업체 리뷰에만 답변할 수 있습니다.');
}

$write_table = eottae_review_write_table();
$review = sql_fetch(" select * from {$write_table}
    where wr_id = '{$review_wr_id}' and wr_is_comment = 0 limit 1 ");
if (empty($review['wr_id'])) {
    eottae_review_reply_json(false, '리뷰를 찾을 수 없습니다.');
}

if ((int) $review['wr_1'] !== $shop_wr_id) {
    eottae_review_reply_json(false, '리뷰와 업체 정보가 일치하지 않습니다.');
}

if (eottae_review_has_business_reply($review_wr_id, $member['mb_id'])) {
    eottae_review_reply_json(false, '이미 답변을 작성하셨습니다.');
}

$bo_table = eottae_review_table();
$board = sql_fetch(" select * from {$g5['board_table']} where bo_table = '".sql_real_escape_string($bo_table)."' ");
if (empty($board['bo_table'])) {
    eottae_review_reply_json(false, '리뷰 게시판이 준비되지 않았습니다.');
}

$row = sql_fetch(" select max(wr_comment) as max_comment from {$write_table}
    where wr_parent = '{$review_wr_id}' and wr_is_comment = 1 ");
$tmp_comment = isset($row['max_comment']) ? ((int) $row['max_comment'] + 1) : 1;

$wr_content = sql_escape_string($content);
$wr_name = sql_escape_string($member['mb_nick']);
$mb_id = sql_escape_string($member['mb_id']);
$wr_email = sql_escape_string(isset($member['mb_email']) ? $member['mb_email'] : '');
$wr_ip = sql_escape_string(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');

$sql = " insert into {$write_table}
    set ca_name = '".sql_escape_string($review['ca_name'])."',
        wr_option = '',
        wr_num = '".sql_escape_string($review['wr_num'])."',
        wr_reply = '',
        wr_parent = '{$review_wr_id}',
        wr_is_comment = 1,
        wr_comment = '{$tmp_comment}',
        wr_comment_reply = '',
        wr_subject = '',
        wr_content = '{$wr_content}',
        mb_id = '{$mb_id}',
        wr_password = '',
        wr_name = '{$wr_name}',
        wr_email = '{$wr_email}',
        wr_homepage = '',
        wr_datetime = '".G5_TIME_YMDHIS."',
        wr_last = '',
        wr_ip = '{$wr_ip}',
        wr_1 = '', wr_2 = '', wr_3 = '', wr_4 = '', wr_5 = '',
        wr_6 = '', wr_7 = '', wr_8 = '', wr_9 = '', wr_10 = '' ";

if (!sql_query($sql, false)) {
    eottae_review_reply_json(false, '답변 저장 중 오류가 발생했습니다.');
}

$comment_id = sql_insert_id();
if (!$comment_id) {
    eottae_review_reply_json(false, '답변 저장 중 오류가 발생했습니다.');
}

sql_query(" update {$write_table} set wr_comment = wr_comment + 1, wr_last = '".G5_TIME_YMDHIS."'
    where wr_id = '{$review_wr_id}' ");
sql_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id )
    values ( '{$bo_table}', '{$comment_id}', '{$review_wr_id}', '".G5_TIME_YMDHIS."', '{$mb_id}' ) ");
sql_query(" update {$g5['board_table']} set bo_count_comment = bo_count_comment + 1
    where bo_table = '{$bo_table}' ");

set_session('eottae_review_reply_token', '');

$redirect = G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE.'&wr_id='.$shop_wr_id.'#review-'.$review_wr_id;
eottae_review_reply_json(true, '답변이 등록되었습니다.', $redirect);
