<?php
/**
 * 업체 찜(스크랩) 토글 API
 * POST /proc/eottae-shop-save.php
 */
define('EOTTae_SHOP_SAVE', true);

include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae.lib.php';

function eottae_shop_save_json($success, $message, $saved = null)
{
    $payload = array(
        'success' => (bool) $success,
        'message' => (string) $message,
    );
    if ($saved !== null) {
        $payload['saved'] = (bool) $saved;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_shop_save_json(false, '잘못된 요청입니다.');
}

if (!$is_member || empty($member['mb_id'])) {
    eottae_shop_save_json(false, '로그인 후 이용해 주세요.');
}

$token = isset($_POST['eottae_shop_save_token']) ? trim($_POST['eottae_shop_save_token']) : '';
$session_token = get_session('eottae_shop_save_token');
if ($token === '' || $session_token === '' || !hash_equals($session_token, $token)) {
    eottae_shop_save_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.');
}

$shop_wr_id = isset($_POST['shop_wr_id']) ? (int) $_POST['shop_wr_id'] : 0;
if ($shop_wr_id < 1) {
    eottae_shop_save_json(false, '업체 정보가 올바르지 않습니다.');
}

$shop_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
$row = sql_fetch(" select wr_id from {$shop_table} where wr_id = '{$shop_wr_id}' and wr_is_comment = 0 ");
if (empty($row['wr_id'])) {
    eottae_shop_save_json(false, '업체를 찾을 수 없습니다.');
}

$bo_table = EOTTae_SHOP_TABLE;
$mb_id = sql_escape_string($member['mb_id']);

if (eottae_is_shop_saved($member['mb_id'], $shop_wr_id)) {
    sql_query(" delete from {$g5['scrap_table']}
        where mb_id = '{$mb_id}' and bo_table = '{$bo_table}' and wr_id = '{$shop_wr_id}' ");
    set_session('eottae_shop_save_token', '');
    eottae_shop_save_json(true, '찜 목록에서 제거했습니다.', false);
}

sql_query(" insert into {$g5['scrap_table']} ( mb_id, bo_table, wr_id, ms_datetime )
    values ( '{$mb_id}', '{$bo_table}', '{$shop_wr_id}', '".G5_TIME_YMDHIS."' ) ");
set_session('eottae_shop_save_token', '');
eottae_shop_save_json(true, '찜 목록에 저장했습니다.', true);
