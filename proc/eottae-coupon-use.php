<?php
/**
 * 쿠폰 사용 처리
 * POST /proc/eottae-coupon-use.php (JSON)
 */
define('EOTTae_COUPON_USE', true);

include_once dirname(__FILE__).'/../_common.php';

if (!defined('_GNUBOARD_')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => '접근이 올바르지 않습니다.'));
    exit;
}

include_once G5_LIB_PATH.'/eottae-coupon.lib.php';

function eottae_coupon_use_json($success, $message)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_coupon_use_json(false, '잘못된 요청입니다.');
}

if (!$is_member || empty($member['mb_id'])) {
    eottae_coupon_use_json(false, '로그인 후 이용해 주세요.');
}

$ci_id = isset($_POST['ci_id']) ? (int) $_POST['ci_id'] : 0;
$result = eottae_coupon_use($member['mb_id'], $ci_id);
eottae_coupon_use_json(!empty($result['ok']), $result['message']);
