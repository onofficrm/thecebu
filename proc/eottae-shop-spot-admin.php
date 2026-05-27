<?php
/**
 * 최우수 업체 노출 — 관리자 설정 API
 */
include_once dirname(__DIR__).'/common.php';
include_once G5_LIB_PATH.'/eottae-shop-spot.lib.php';

header('Content-Type: application/json; charset=utf-8');

if ($is_admin !== 'super' || empty($member['mb_id'])) {
    echo json_encode(array('ok' => false, 'message' => '최고관리자만 이용할 수 있습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('ok' => false, 'message' => '잘못된 요청입니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

eottae_shop_spot_ensure_schema();

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

if ($action === 'save_slot') {
    $spot_slot = isset($_POST['spot_slot']) ? (int) $_POST['spot_slot'] : 0;
    $points = isset($_POST['points_required']) ? (int) $_POST['points_required'] : 0;
    $days = isset($_POST['days_duration']) ? (int) $_POST['days_duration'] : 0;
    $enabled = !empty($_POST['is_enabled']);

    $result = eottae_shop_spot_save_config($spot_slot, $points, $days, $enabled, $member['mb_id']);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'save_all') {
    $logs = array();
    for ($slot = 1; $slot <= eottae_shop_spot_slot_count(); $slot++) {
        $points = isset($_POST['points_'.$slot]) ? (int) $_POST['points_'.$slot] : 0;
        $days = isset($_POST['days_'.$slot]) ? (int) $_POST['days_'.$slot] : 0;
        $enabled = !empty($_POST['enabled_'.$slot]);
        $logs[] = eottae_shop_spot_save_config($slot, $points, $days, $enabled, $member['mb_id']);
    }
    echo json_encode(array('ok' => true, 'message' => '저장되었습니다.', 'slots' => $logs), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array('ok' => false, 'message' => '잘못된 요청입니다.'), JSON_UNESCAPED_UNICODE);
