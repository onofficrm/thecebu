<?php
include_once dirname(__DIR__).'/common.php';
include_once G5_LIB_PATH.'/eottae-ad-platform.lib.php';

header('Content-Type: application/json; charset=utf-8');
eottae_ad_platform_ensure_schema();

if ($is_admin !== 'super') {
    echo json_encode(array('ok' => false, 'message' => '최고관리자만 이용할 수 있습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('ok' => false, 'message' => '잘못된 요청입니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$admin_mb_id = !empty($member['mb_id']) ? $member['mb_id'] : 'admin';

if ($action === 'save_slot') {
    $slot_id = (int) ($_POST['slot_id'] ?? 0);
    $result = eottae_ad_platform_save_slot($slot_id, array(
        'point_per_day'   => $_POST['point_per_day'] ?? 0,
        'min_days'        => $_POST['min_days'] ?? 3,
        'max_days'        => $_POST['max_days'] ?? 30,
        'max_active_ads'  => $_POST['max_active_ads'] ?? 1,
        'requires_review' => !empty($_POST['requires_review']),
        'requires_image'  => !empty($_POST['requires_image']),
        'is_active'       => !empty($_POST['is_active']),
    ), $admin_mb_id);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

$ad_id = (int) ($_POST['ad_id'] ?? 0);
$message = isset($_POST['message']) ? trim((string) $_POST['message']) : '';

if ($action === 'approve') {
    echo json_encode(eottae_ad_platform_admin_approve($ad_id, $admin_mb_id), JSON_UNESCAPED_UNICODE);
    exit;
}
if ($action === 'reject') {
    echo json_encode(eottae_ad_platform_admin_reject($ad_id, $admin_mb_id, $message), JSON_UNESCAPED_UNICODE);
    exit;
}
if ($action === 'cancel') {
    echo json_encode(eottae_ad_platform_admin_cancel($ad_id, $admin_mb_id, $message), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array('ok' => false, 'message' => '잘못된 요청입니다.'), JSON_UNESCAPED_UNICODE);
