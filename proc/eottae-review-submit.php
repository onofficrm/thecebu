<?php
/**
 * 업체 리뷰 등록 API
 * POST /proc/eottae-review-submit.php (JSON 응답)
 */
define('EOTTae_REVIEW_SUBMIT', true);

include_once dirname(__FILE__).'/../_common.php';

if (!defined('_GNUBOARD_')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => '접근이 올바르지 않습니다.'));
    exit;
}

include_once G5_LIB_PATH.'/eottae.lib.php';

function eottae_review_json($success, $message, $redirect_url = '')
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
    eottae_review_json(false, '잘못된 요청입니다.');
}

if (!$is_member || empty($member['mb_id'])) {
    eottae_review_json(false, '로그인 후 리뷰를 작성할 수 있습니다.');
}

if (eottae_is_business_member($member)) {
    eottae_review_json(false, '사업자 회원은 리뷰를 작성할 수 없습니다.');
}

$token = isset($_POST['eottae_review_token']) ? trim($_POST['eottae_review_token']) : '';
$session_token = get_session('eottae_review_token');
if ($token === '' || $session_token === '' || !hash_equals($session_token, $token)) {
    eottae_review_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$shop_wr_id = isset($_POST['shop_wr_id']) ? (int) $_POST['shop_wr_id'] : 0;
$shop_name = isset($_POST['shop_name']) ? trim(strip_tags($_POST['shop_name'])) : '';
$rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
$content = isset($_POST['content']) ? trim(strip_tags($_POST['content'])) : '';

if ($shop_wr_id < 1) {
    eottae_review_json(false, '업체 정보가 올바르지 않습니다.');
}
if ($rating < 1 || $rating > 5) {
    eottae_review_json(false, '별점을 1~5점 사이로 선택해 주세요.');
}
if ($content === '') {
    eottae_review_json(false, '리뷰 내용을 입력해 주세요.');
}
$len = function_exists('mb_strlen') ? mb_strlen($content, 'UTF-8') : strlen($content);
if ($len < 10) {
    eottae_review_json(false, '리뷰 내용을 10자 이상 입력해 주세요.');
}
if ($len > 1000) {
    eottae_review_json(false, '리뷰 내용은 1000자 이내로 입력해 주세요.');
}

$shop_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
$shop_row = sql_fetch(" select wr_id, wr_subject from {$shop_table} where wr_id = '{$shop_wr_id}' ");
if (empty($shop_row['wr_id'])) {
    eottae_review_json(false, '업체를 찾을 수 없습니다.');
}

if ($shop_name === '') {
    $shop_name = get_text($shop_row['wr_subject']);
}

if (eottae_user_reviewed_shop($member['mb_id'], $shop_wr_id)) {
    eottae_review_json(false, '이미 이 업체에 리뷰를 작성하셨습니다.');
}

$bo_table = eottae_review_table();
$board = sql_fetch(" select * from {$g5['board_table']} where bo_table = '".sql_real_escape_string($bo_table)."' ");
if (empty($board['bo_table'])) {
    eottae_review_json(false, '리뷰 게시판이 준비되지 않았습니다. 관리자에게 문의해 주세요.');
}

$write_table = eottae_review_write_table();
$wr_subject_raw = '['.$rating.'점] '.$shop_name.' 리뷰';
$wr_subject = sql_escape_string(function_exists('cut_str') ? cut_str($wr_subject_raw, 255) : substr($wr_subject_raw, 0, 255));
$wr_content = sql_escape_string($content);
$wr_name = sql_escape_string($member['mb_nick']);
$mb_id = sql_escape_string($member['mb_id']);
$wr_email = sql_escape_string(isset($member['mb_email']) ? $member['mb_email'] : '');
$wr_ip = sql_escape_string(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
$wr_1 = sql_escape_string((string) $shop_wr_id);
$wr_2 = sql_escape_string((string) $rating);
$wr_3 = sql_escape_string($shop_name);
$wr_4 = sql_escape_string('visible');
$wr_5 = '0';

$sql = " insert into {$write_table} set
    wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM {$write_table} as sq),
    wr_reply = '',
    wr_comment = 0,
    ca_name = '',
    wr_option = '',
    wr_subject = '{$wr_subject}',
    wr_content = '{$wr_content}',
    wr_seo_title = '',
    wr_link1 = '',
    wr_link2 = '',
    mb_id = '{$mb_id}',
    wr_password = '',
    wr_name = '{$wr_name}',
    wr_email = '{$wr_email}',
    wr_datetime = '".G5_TIME_YMDHIS."',
    wr_last = '".G5_TIME_YMDHIS."',
    wr_ip = '{$wr_ip}',
    wr_1 = '{$wr_1}',
    wr_2 = '{$wr_2}',
    wr_3 = '{$wr_3}',
    wr_4 = '{$wr_4}',
    wr_5 = '{$wr_5}',
    wr_6 = '', wr_7 = '', wr_8 = '', wr_9 = '', wr_10 = '' ";

if (!sql_query($sql, false)) {
    eottae_review_json(false, '리뷰 저장 중 오류가 발생했습니다.');
}

$wr_id = sql_insert_id();
if (!$wr_id) {
    eottae_review_json(false, '리뷰 저장 중 오류가 발생했습니다.');
}

sql_query(" update {$write_table} set wr_parent = '{$wr_id}' where wr_id = '{$wr_id}' ");
sql_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id )
    values ( '{$bo_table}', '{$wr_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', '{$mb_id}' ) ");
sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write + 1 where bo_table = '{$bo_table}' ");

$photo_count = 0;
if (!empty($_FILES['photo']['name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
    $allowed = array('image/jpeg', 'image/png', 'image/webp');
    $mime = isset($_FILES['photo']['type']) ? $_FILES['photo']['type'] : '';
    if (in_array($mime, $allowed, true) && (int) $_FILES['photo']['size'] <= 5242880) {
        $dest_dir = G5_DATA_PATH.'/file/'.$bo_table;
        if (!is_dir($dest_dir)) {
            @mkdir($dest_dir, G5_DIR_PERMISSION, true);
        }
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        if ($ext === '') {
            $ext = 'jpg';
        }
        $bf_file = md5(uniqid((string) mt_rand(), true)).'.'.strtolower($ext);
        if (@move_uploaded_file($_FILES['photo']['tmp_name'], $dest_dir.'/'.$bf_file)) {
            $photo_count = 1;
            $size = (int) filesize($dest_dir.'/'.$bf_file);
            $info = @getimagesize($dest_dir.'/'.$bf_file);
            $bf_width = isset($info[0]) ? (int) $info[0] : 0;
            $bf_height = isset($info[1]) ? (int) $info[1] : 0;
            $bf_type = isset($info[2]) ? (int) $info[2] : 0;
            $bf_source = sql_escape_string(basename($_FILES['photo']['name']));

            sql_query(" insert into {$g5['board_file_table']} set
                bo_table = '{$bo_table}',
                wr_id = '{$wr_id}',
                bf_no = '0',
                bf_source = '{$bf_source}',
                bf_file = '{$bf_file}',
                bf_content = '',
                bf_fileurl = '',
                bf_thumburl = '',
                bf_storage = '',
                bf_download = 0,
                bf_filesize = '{$size}',
                bf_width = '{$bf_width}',
                bf_height = '{$bf_height}',
                bf_type = '{$bf_type}',
                bf_datetime = '".G5_TIME_YMDHIS."' ");
            sql_query(" update {$write_table} set wr_file = wr_file + 1, wr_5 = '1' where wr_id = '{$wr_id}' ");
        }
    }
}

set_session('eottae_review_token', '');

include_once G5_LIB_PATH.'/eottae-coupon.lib.php';

$points = eottae_grant_review_points($member['mb_id'], $wr_id, $shop_name, $photo_count > 0);

$message = '리뷰가 등록되었습니다.';
if ($points['total'] > 0) {
    $message .= ' (+'.number_format($points['total']).'P)';
}

$review_count = count(eottae_get_member_reviews($member['mb_id'], 100));
if ($review_count === 1) {
    $coupon_result = eottae_coupon_ensure_review_bonus($member['mb_id']);
    if (!empty($coupon_result['ok']) && empty($coupon_result['duplicate'])) {
        $message .= ' 첫 리뷰 감사 쿠폰이 발급되었습니다.';
    }
}

$redirect = G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE.'&wr_id='.$shop_wr_id.'#shop-reviews';
eottae_review_json(true, $message, $redirect);
