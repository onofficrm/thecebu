<?php
/**
 * 세부톡방 AI 관리자 API
 * POST /proc/eottae-talkroom-ai-admin.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-admin.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_talkroom_ai_admin_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_talkroom_ai_admin_json(false, '잘못된 요청입니다.');
}

if ($is_admin !== 'super') {
    eottae_talkroom_ai_admin_json(false, '최고관리자만 이용할 수 있습니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_talkroom_ai_admin_json(false, '로그인 후 이용해 주세요.');
}

$token = isset($_POST['eottae_talkroom_admin_token']) ? trim((string) $_POST['eottae_talkroom_admin_token']) : '';
if (!eottae_talkroom_verify_admin_token($token)) {
    eottae_talkroom_ai_admin_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

if ($action === 'save_global_policy') {
    $data = array(
        'site_ai_enabled'      => !empty($_POST['site_ai_enabled']) ? 1 : 0,
        'owner_config_allowed' => !empty($_POST['owner_ai_allowed']) ? 1 : 0,
        'site_daily_limit'     => isset($_POST['site_daily_limit']) ? (int) $_POST['site_daily_limit'] : eottae_talkroom_ai_default_site_daily_limit(),
    );
    $result = eottae_talkroom_ai_save_global_policy($data, $member['mb_id'], true);
    if (!empty($result['ok'])) {
        eottae_talkroom_admin_token(true);
    }
    eottae_talkroom_ai_admin_json(!empty($result['ok']), $result['message'], array(
        'redirect_url' => eottae_talkroom_ai_admin_url().'?saved=1',
    ));
}

$room_id = isset($_POST['room_id']) ? (int) $_POST['room_id'] : 0;

if ($action === 'toggle_room_force_off') {
    if ($room_id < 1) {
        eottae_talkroom_ai_admin_json(false, '톡방 정보가 올바르지 않습니다.');
    }

    $disabled = !empty($_POST['force_disabled']) ? 1 : 0;
    $result = eottae_talkroom_ai_set_room_force_disabled($room_id, $disabled, $member['mb_id'], true);
    if (!empty($result['ok'])) {
        eottae_talkroom_admin_token(true);
    }
    eottae_talkroom_ai_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'delete_ai_content') {
    $wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
    if ($wr_id < 1) {
        eottae_talkroom_ai_admin_json(false, '삭제할 글/댓글 ID가 필요합니다.');
    }

    $result = eottae_talkroom_ai_delete_content($wr_id, $member['mb_id'], true);
    if (!empty($result['ok'])) {
        eottae_talkroom_admin_token(true);
    }
    eottae_talkroom_ai_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'test_ai_trigger') {
    if ($room_id < 1) {
        eottae_talkroom_ai_admin_json(false, '톡방 정보가 올바르지 않습니다.');
    }

    $trigger = isset($_POST['trigger']) ? trim((string) $_POST['trigger']) : '';
    $dry_run = !empty($_POST['dry_run']);
    $result = eottae_talkroom_ai_run_admin_trigger_test($room_id, $trigger, array(
        'dry_run' => $dry_run,
        'force'   => true,
    ));

    if (!empty($result['ok']) || in_array($result['status'] ?? '', array('success', 'dry_run'), true)) {
        eottae_talkroom_admin_token(true);
    }

    eottae_talkroom_ai_admin_json(
        in_array($result['status'] ?? '', array('success', 'dry_run'), true),
        (string) ($result['message'] ?? '처리에 실패했습니다.'),
        array('result' => $result)
    );
}

eottae_talkroom_ai_admin_json(false, '지원하지 않는 요청입니다.');
