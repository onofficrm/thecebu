<?php
/**
 * 광고방 — 연동 쿠폰 받기(다운로드)
 */
include_once dirname(__DIR__).'/common.php';
include_once G5_LIB_PATH.'/eottae-adroom.lib.php';
include_once G5_LIB_PATH.'/eottae-business-coupon.lib.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($is_member) || empty($member['mb_id'])) {
    echo json_encode(array('success' => false, 'message' => '로그인 후 쿠폰을 받을 수 있습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$action = isset($_REQUEST['action']) ? trim((string) $_REQUEST['action']) : '';
if ($action !== 'claim') {
    echo json_encode(array('success' => false, 'message' => '올바르지 않은 요청입니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
$result = eottae_adroom_member_coupon_issue($wr_id, $member['mb_id']);

echo json_encode(array(
    'success' => !empty($result['ok']),
    'message' => $result['message'] ?? '',
    'coupons_url' => $result['coupons_url'] ?? G5_URL.'/page/eottae-coupons.php',
), JSON_UNESCAPED_UNICODE);
