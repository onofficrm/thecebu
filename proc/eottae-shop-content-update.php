<?php
/**
 * 업체 상세 본문(스마트에디터) 인라인 저장
 * POST /proc/eottae-shop-content-update.php
 */
define('EOTTae_SHOP_CONTENT_UPDATE', true);

chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';

if (!defined('_GNUBOARD_')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => '접근이 올바르지 않습니다.'));
    exit;
}

include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-shop-owner.lib.php';

function eottae_shop_content_update_json($success, $message, $extra = array())
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
    eottae_shop_content_update_json(false, '잘못된 요청입니다.');
}

if (!$is_member || empty($member['mb_id'])) {
    eottae_shop_content_update_json(false, '로그인 후 수정할 수 있습니다.');
}

$token = isset($_POST['eottae_shop_content_token']) ? trim($_POST['eottae_shop_content_token']) : '';
$session_token = get_session('eottae_shop_content_token');
if ($token === '' || $session_token === '' || !hash_equals($session_token, $token)) {
    eottae_shop_content_update_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.');
}

$bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', (string) $_POST['bo_table']) : '';
$wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
if ($bo_table === '' || $wr_id < 1 || !function_exists('eottae_is_shop_board') || !eottae_is_shop_board($bo_table)) {
    eottae_shop_content_update_json(false, '업체 정보가 올바르지 않습니다.');
}

$write_table = $g5['write_prefix'].$bo_table;
$write = sql_fetch(" select * from {$write_table} where wr_id = '{$wr_id}' and wr_is_comment = 0 limit 1 ");
if (empty($write['wr_id'])) {
    eottae_shop_content_update_json(false, '업체를 찾을 수 없습니다.');
}

if (!eottae_shop_user_can_manage($write, $bo_table)) {
    eottae_shop_content_update_json(false, '본문을 수정할 권한이 없습니다.');
}

$board = sql_fetch(" select * from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' ");
if (empty($board['bo_table'])) {
    eottae_shop_content_update_json(false, '게시판 정보를 찾을 수 없습니다.');
}

$wr_content = '';
if (isset($_POST['wr_content'])) {
    $wr_content = substr(trim($_POST['wr_content']), 0, 65536);
    $wr_content = preg_replace("#[\\\]+$#", '', $wr_content);
    if (function_exists('normalize_utf8_string')) {
        $wr_content = normalize_utf8_string($wr_content);
    }
}

if ($wr_content === '') {
    eottae_shop_content_update_json(false, '본문 내용을 입력해 주세요.');
}

if (substr_count($wr_content, '&#') > 50) {
    eottae_shop_content_update_json(false, '내용에 올바르지 않은 코드가 다수 포함되어 있습니다.');
}

$wr_option = 'html1';
if (function_exists('eottae_shop_content_editor_enabled') && !eottae_shop_content_editor_enabled($board)) {
    $wr_option = '';
    $wr_content = clean_xss_tags($wr_content, 1, 1);
}

$wr_content_sql = sql_escape_string($wr_content);
$wr_option_sql = sql_escape_string($wr_option);

sql_query(" update {$write_table}
    set wr_content = '{$wr_content_sql}',
        wr_option = '{$wr_option_sql}',
        wr_last = '".G5_TIME_YMDHIS."'
    where wr_id = '{$wr_id}' ");

set_session('eottae_shop_content_token', '');

$view_content = conv_content($wr_content, 1);
if (function_exists('get_view_thumbnail')) {
    include_once G5_LIB_PATH.'/thumbnail.lib.php';
    $view_content = get_view_thumbnail($view_content);
}

eottae_shop_content_update_json(true, '본문을 저장했습니다.', array(
    'content_html' => $view_content,
));
