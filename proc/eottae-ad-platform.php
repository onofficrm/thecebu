<?php
include_once dirname(__DIR__).'/common.php';
include_once G5_LIB_PATH.'/eottae-ad-platform.lib.php';

header('Content-Type: application/json; charset=utf-8');
eottae_ad_platform_ensure_schema();

if (empty($is_member) || empty($member['mb_id'])) {
    echo json_encode(array('ok' => false, 'message' => '로그인 후 이용해 주세요.'), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!eottae_ad_platform_can_manage($member)) {
    echo json_encode(array('ok' => false, 'message' => '사업자회원 또는 최고관리자만 이용할 수 있습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('ok' => false, 'message' => '잘못된 요청입니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : 'apply';
$is_super = ($is_admin === 'super');

if ($action === 'quote') {
    $slot = eottae_ad_platform_get_slot_by_code(isset($_POST['slot_code']) ? $_POST['slot_code'] : '');
    $days = max(1, (int) ($_POST['days'] ?? 0));
    $bid_bonus = max(0, min(100000, (int) ($_POST['bid_bonus'] ?? 0)));
    if (!$slot) {
        echo json_encode(array('ok' => false, 'message' => '광고 위치를 찾을 수 없습니다.'), JSON_UNESCAPED_UNICODE);
        exit;
    }
    $base_points = eottae_ad_platform_calc_points($slot, $days, 0);
    echo json_encode(array(
        'ok'           => true,
        'total_points' => eottae_ad_platform_calc_points($slot, $days, $bid_bonus),
        'base_points'  => $base_points,
        'bid_bonus'    => $bid_bonus,
        'point_per_day'=> (int) $slot['point_per_day'],
        'min_days'     => (int) $slot['min_days'],
        'max_days'     => (int) $slot['max_days'],
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'extend_quote') {
    $ad_id = (int) ($_POST['ad_id'] ?? 0);
    $extra_days = max(1, (int) ($_POST['extra_days'] ?? 0));
    $campaign = eottae_ad_platform_get_campaign($ad_id);
    if (!$campaign || !eottae_ad_platform_member_owns_campaign($campaign, $member['mb_id'], $is_super)) {
        echo json_encode(array('ok' => false, 'message' => '광고를 찾을 수 없습니다.'), JSON_UNESCAPED_UNICODE);
        exit;
    }
    $slot = eottae_ad_platform_get_slot_by_id((int) $campaign['slot_id']);
    if (!$slot) {
        echo json_encode(array('ok' => false, 'message' => '광고 위치 정보가 없습니다.'), JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo json_encode(array(
        'ok'           => true,
        'extra_points' => eottae_ad_platform_calc_points($slot, $extra_days),
        'point_per_day'=> (int) $slot['point_per_day'],
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$image_url = isset($_POST['image_url']) ? trim((string) $_POST['image_url']) : '';
if (!empty($_FILES['image_file']['tmp_name'])) {
    $upload = eottae_ad_platform_upload_image($_FILES['image_file']);
    if (empty($upload['ok'])) {
        echo json_encode($upload, JSON_UNESCAPED_UNICODE);
        exit;
    }
    $image_url = (string) $upload['url'];
}

if ($action === 'apply') {
    $result = eottae_ad_platform_apply($member['mb_id'], array(
        'slot_code'       => isset($_POST['slot_code']) ? $_POST['slot_code'] : '',
        'start_date'      => isset($_POST['start_date']) ? $_POST['start_date'] : '',
        'days'            => isset($_POST['days']) ? $_POST['days'] : 0,
        'title'           => isset($_POST['title']) ? $_POST['title'] : '',
        'description'     => isset($_POST['description']) ? $_POST['description'] : '',
        'button_text'     => isset($_POST['button_text']) ? $_POST['button_text'] : '',
        'link_url'        => isset($_POST['link_url']) ? $_POST['link_url'] : '',
        'image_url'       => $image_url,
        'shop_bo_table'   => isset($_POST['shop_bo_table']) ? $_POST['shop_bo_table'] : '',
        'shop_wr_id'      => isset($_POST['shop_wr_id']) ? $_POST['shop_wr_id'] : 0,
        'target_category' => isset($_POST['target_category']) ? $_POST['target_category'] : '',
        'target_region'   => isset($_POST['target_region']) ? $_POST['target_region'] : '',
        'bid_bonus'       => isset($_POST['bid_bonus']) ? $_POST['bid_bonus'] : 0,
    ));
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'update') {
    $result = eottae_ad_platform_update_campaign((int) ($_POST['ad_id'] ?? 0), $member['mb_id'], array(
        'start_date'      => isset($_POST['start_date']) ? $_POST['start_date'] : '',
        'days'            => isset($_POST['days']) ? $_POST['days'] : 0,
        'title'           => isset($_POST['title']) ? $_POST['title'] : '',
        'description'     => isset($_POST['description']) ? $_POST['description'] : '',
        'button_text'     => isset($_POST['button_text']) ? $_POST['button_text'] : '',
        'link_url'        => isset($_POST['link_url']) ? $_POST['link_url'] : '',
        'image_url'       => $image_url,
        'target_category' => isset($_POST['target_category']) ? $_POST['target_category'] : '',
        'target_region'   => isset($_POST['target_region']) ? $_POST['target_region'] : '',
        'bid_bonus'       => isset($_POST['bid_bonus']) ? $_POST['bid_bonus'] : 0,
    ), $is_super);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'extend') {
    $result = eottae_ad_platform_extend_campaign(
        (int) ($_POST['ad_id'] ?? 0),
        $member['mb_id'],
        (int) ($_POST['extra_days'] ?? 0),
        $is_super
    );
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'cancel') {
    $result = eottae_ad_platform_member_cancel((int) ($_POST['ad_id'] ?? 0), $member['mb_id'], $is_super);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array('ok' => false, 'message' => '잘못된 요청입니다.'), JSON_UNESCAPED_UNICODE);
