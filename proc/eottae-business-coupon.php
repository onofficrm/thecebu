<?php
/**
 * 사업자 쿠폰 — 생성·발행·사용처리
 */
include_once dirname(__DIR__).'/common.php';
include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-business-coupon.lib.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($is_member) || empty($member['mb_id'])) {
    echo json_encode(array('success' => false, 'message' => '로그인 후 이용해 주세요.'), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!function_exists('eottae_is_business_member') || !eottae_is_business_member($member)) {
    echo json_encode(array('success' => false, 'message' => '사업자 회원만 이용할 수 있습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$owner_mb_id = $member['mb_id'];
$action = isset($_REQUEST['action']) ? trim((string) $_REQUEST['action']) : '';

if ($action === 'campaigns') {
    echo json_encode(array(
        'success' => true,
        'data' => eottae_business_coupon_campaigns($owner_mb_id),
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'issues') {
    $status = isset($_REQUEST['status']) ? trim((string) $_REQUEST['status']) : '';
    echo json_encode(array(
        'success' => true,
        'data' => eottae_business_coupon_issues($owner_mb_id, $status),
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'create') {
    $result = eottae_business_coupon_create($owner_mb_id, array(
        'cp_benefit_type' => isset($_POST['cp_benefit_type']) ? $_POST['cp_benefit_type'] : '',
        'cp_percent' => isset($_POST['cp_percent']) ? $_POST['cp_percent'] : 0,
        'cp_free_item' => isset($_POST['cp_free_item']) ? $_POST['cp_free_item'] : '',
        'cp_min_amount' => isset($_POST['cp_min_amount']) ? $_POST['cp_min_amount'] : '',
        'cp_condition_menu' => isset($_POST['cp_condition_menu']) ? $_POST['cp_condition_menu'] : '',
        'cp_order_benefit' => isset($_POST['cp_order_benefit']) ? $_POST['cp_order_benefit'] : 'percent',
        'cp_max_issue' => isset($_POST['cp_max_issue']) ? $_POST['cp_max_issue'] : 100,
        'cp_expires_at' => isset($_POST['cp_expires_at']) ? $_POST['cp_expires_at'] : '',
        'cp_title' => isset($_POST['cp_title']) ? $_POST['cp_title'] : '',
    ));
    echo json_encode(array(
        'success' => !empty($result['ok']),
        'message' => $result['message'],
        'cp_id' => isset($result['cp_id']) ? (int) $result['cp_id'] : 0,
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'issue') {
    $cp_id = isset($_POST['cp_id']) ? (int) $_POST['cp_id'] : 0;
    $target_mb_id = isset($_POST['target_mb_id']) ? trim((string) $_POST['target_mb_id']) : '';
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;
    $result = eottae_business_coupon_issue_many($owner_mb_id, $cp_id, $target_mb_id, $quantity);
    echo json_encode(array(
        'success' => !empty($result['ok']),
        'message' => $result['message'],
        'issued' => isset($result['issued']) ? (int) $result['issued'] : 0,
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'redeem') {
    $ci_id = isset($_POST['ci_id']) ? (int) $_POST['ci_id'] : 0;
    $lookup = isset($_POST['lookup']) ? trim((string) $_POST['lookup']) : '';
    if ($ci_id > 0) {
        $result = eottae_business_coupon_redeem($owner_mb_id, $ci_id);
    } else {
        $result = eottae_business_coupon_redeem_by_lookup($owner_mb_id, $lookup);
    }
    echo json_encode(array(
        'success' => !empty($result['ok']),
        'message' => $result['message'],
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array('success' => false, 'message' => '올바르지 않은 요청입니다.'), JSON_UNESCAPED_UNICODE);
