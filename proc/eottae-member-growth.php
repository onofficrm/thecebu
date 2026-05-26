<?php
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_member_growth_json($success, $message, $extra = array())
{
    echo json_encode(array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array()), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_member_growth_json(false, '잘못된 요청입니다.');
}

if (!$is_member || empty($member['mb_id'])) {
    eottae_member_growth_json(false, '로그인이 필요합니다.');
}

$token = isset($_POST['eottae_member_growth_token']) ? trim((string) $_POST['eottae_member_growth_token']) : '';
if (!eottae_member_growth_verify_member_token($token)) {
    eottae_member_growth_json(false, '보안 토큰이 만료되었습니다. 새로고침해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

if ($action === 'set_main_badge') {
    $badge_id = isset($_POST['badge_id']) ? (int) $_POST['badge_id'] : 0;
    $result = eottae_member_growth_set_main_badge($member['mb_id'], $badge_id);
    if (!empty($result['ok'])) {
        eottae_member_growth_member_token(true);
    }
    eottae_member_growth_json(!empty($result['ok']), $result['message'] ?? '');
}

if ($action === 'save_public_bio') {
    $prefs = eottae_member_growth_get_member_prefs($member['mb_id']);
    $prefs['public_bio'] = trim(strip_tags((string) ($_POST['public_bio'] ?? '')));
    eottae_member_growth_save_member_prefs($member['mb_id'], $prefs);
    eottae_member_growth_member_token(true);
    eottae_member_growth_json(true, '프로필 소개를 저장했습니다.');
}

eottae_member_growth_json(false, '지원하지 않는 요청입니다.');
