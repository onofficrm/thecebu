<?php
include_once(dirname(__FILE__).'/../_common.php');

header('Content-Type: application/json; charset=utf-8');

include_once G5_LIB_PATH.'/eottae-app-home.lib.php';

if (!function_exists('eottae_app_json')) {
    function eottae_app_json(array $payload)
    {
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    $input = $_POST;
}

$action = preg_replace('/[^a-z0-9_.-]/i', '', (string) ($input['action'] ?? 'event'));
$event = preg_replace('/[^a-z0-9_.-]/i', '', (string) ($input['event'] ?? $action));
$label = trim((string) ($input['label'] ?? ''));
$interest = eottae_app_normalize_interest($input['interest'] ?? '');
$url = trim((string) ($input['url'] ?? ($_SERVER['HTTP_REFERER'] ?? '')));
$mb_id = !empty($is_member) ? (string) ($member['mb_id'] ?? '') : '';

if ($action === 'checkin') {
    if (empty($is_member) || $mb_id === '') {
        eottae_app_json(array('success' => false, 'message' => '로그인 후 이용해 주세요.'));
    }
    if (is_file(G5_LIB_PATH.'/eottae-promo-coupon.lib.php')) {
        include_once G5_LIB_PATH.'/eottae-promo-coupon.lib.php';
    }
    if (!function_exists('eottae_attendance_checkin')) {
        eottae_app_json(array('success' => false, 'message' => '출석 기능을 사용할 수 없습니다.'));
    }
    $result = eottae_attendance_checkin($mb_id);
    eottae_app_event_record('reward_checkin', '출석 체크', $interest, $url, $mb_id);
    eottae_app_json(array(
        'success' => !empty($result['ok']),
        'message' => (string) ($result['message'] ?? '처리되었습니다.'),
        'streak' => (int) ($result['streak'] ?? 0),
        'duplicate' => !empty($result['duplicate']),
    ));
}

if ($action === 'prefs') {
    $region = eottae_app_normalize_region($input['region'] ?? '');
    $prefs = eottae_app_normalize_notification_prefs($input['notifications'] ?? array());
    $saved = false;
    if (!empty($is_member) && $mb_id !== '') {
        $saved = eottae_app_save_preferences($mb_id, $interest, $region, $prefs);
    }

    eottae_app_event_record('prefs_save', $region, $interest, $url, $mb_id);
    eottae_app_json(array(
        'success' => true,
        'saved' => $saved,
        'interest' => $interest,
        'region' => $region,
        'notifications' => $prefs,
        'message' => $saved ? '앱 맞춤 설정을 저장했습니다.' : '이 기기에 앱 맞춤 설정을 저장했습니다.',
    ));
}

eottae_app_event_record($event, $label, $interest, $url, $mb_id);
eottae_app_json(array('success' => true));
