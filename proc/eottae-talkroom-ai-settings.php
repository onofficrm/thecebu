<?php
/**
 * 세부톡방 AI 설정 API
 * POST /proc/eottae-talkroom-ai-settings.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-daily-question.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-admin.lib.php';

eottae_talkroom_ensure_schema();
eottae_talkroom_ai_ensure_schema();
eottae_talkroom_ensure_board();

header('Content-Type: application/json; charset=utf-8');

function eottae_talkroom_ai_settings_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_talkroom_ai_settings_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_talkroom_ai_settings_json(false, '로그인 후 이용해 주세요.');
}

$is_super = ($is_admin === 'super');
$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$room_id = isset($_POST['room_id']) ? (int) $_POST['room_id'] : -1;

if ($action === 'save_global_policy') {
    if (!$is_super) {
        eottae_talkroom_ai_settings_json(false, '최고관리자만 이용할 수 있습니다.');
    }

    $token = isset($_POST['eottae_talkroom_admin_token']) ? trim((string) $_POST['eottae_talkroom_admin_token']) : '';
    if (!eottae_talkroom_verify_admin_token($token)) {
        eottae_talkroom_ai_settings_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
    }

    $data = array(
        'site_ai_enabled'      => !empty($_POST['site_ai_enabled']) ? 1 : 0,
        'owner_config_allowed' => !empty($_POST['owner_ai_allowed']) ? 1 : 0,
        'site_daily_limit'     => isset($_POST['site_daily_limit']) ? (int) $_POST['site_daily_limit'] : eottae_talkroom_ai_default_site_daily_limit(),
    );
    $result = eottae_talkroom_ai_save_global_policy($data, $member['mb_id'], true);
    if (!empty($result['ok'])) {
        eottae_talkroom_admin_token(true);
    }
    eottae_talkroom_ai_settings_json(!empty($result['ok']), $result['message'], array(
        'redirect_url' => eottae_talkroom_ai_admin_url().'?saved=1',
    ));
}

if ($action === 'test_daily_question') {
    $_POST['trigger'] = 'daily_question';
    $action = 'test_ai_trigger';
}

if ($action === 'test_ai_trigger') {
    if ($room_id < 1) {
        eottae_talkroom_ai_settings_json(false, '톡방 정보가 올바르지 않습니다.');
    }

    if (!eottae_talkroom_ai_can_edit_settings($room_id, $member['mb_id'], $is_super)) {
        eottae_talkroom_ai_settings_json(false, 'AI 설정 변경 권한이 없습니다.');
    }

    if ($is_super) {
        $token = isset($_POST['eottae_talkroom_admin_token']) ? trim((string) $_POST['eottae_talkroom_admin_token']) : '';
        if ($token === '' || !eottae_talkroom_verify_admin_token($token)) {
            $token = isset($_POST['eottae_talkroom_owner_token']) ? trim((string) $_POST['eottae_talkroom_owner_token']) : '';
            if (!eottae_talkroom_verify_owner_token($token)) {
                eottae_talkroom_ai_settings_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
            }
        }
    } else {
        $token = isset($_POST['eottae_talkroom_owner_token']) ? trim((string) $_POST['eottae_talkroom_owner_token']) : '';
        if (!eottae_talkroom_verify_owner_token($token)) {
            eottae_talkroom_ai_settings_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
        }
    }

    $trigger = isset($_POST['trigger']) ? trim((string) $_POST['trigger']) : 'daily_question';
    $dry_run = !empty($_POST['dry_run']);
    $result = eottae_talkroom_ai_run_admin_trigger_test($room_id, $trigger, array(
        'dry_run' => $dry_run,
        'force'   => true,
    ));

    if (in_array($result['status'] ?? '', array('success', 'dry_run'), true)) {
        if ($is_super) {
            eottae_talkroom_admin_token(true);
        }
        eottae_talkroom_owner_token(true);
    }

    eottae_talkroom_ai_settings_json(
        in_array($result['status'] ?? '', array('success', 'dry_run'), true),
        (string) ($result['message'] ?? '처리에 실패했습니다.'),
        array(
            'result'       => $result,
            'post_id'      => (int) ($result['post_id'] ?? 0),
            'redirect_url' => eottae_talkroom_ai_settings_url($room_id),
        )
    );
}

if ($room_id < 1) {
    eottae_talkroom_ai_settings_json(false, '톡방 정보가 올바르지 않습니다.');
}

if ($action !== 'save_settings') {
    eottae_talkroom_ai_settings_json(false, '지원하지 않는 요청입니다.');
}

if (!eottae_talkroom_ai_can_edit_settings($room_id, $member['mb_id'], $is_super)) {
    eottae_talkroom_ai_settings_json(false, 'AI 설정 변경 권한이 없습니다.');
}

if ($is_super) {
    $token = isset($_POST['eottae_talkroom_admin_token']) ? trim((string) $_POST['eottae_talkroom_admin_token']) : '';
    if ($token === '' || !eottae_talkroom_verify_admin_token($token)) {
        $token = isset($_POST['eottae_talkroom_owner_token']) ? trim((string) $_POST['eottae_talkroom_owner_token']) : '';
        if (!eottae_talkroom_verify_owner_token($token)) {
            eottae_talkroom_ai_settings_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
        }
    }
} else {
    $token = isset($_POST['eottae_talkroom_owner_token']) ? trim((string) $_POST['eottae_talkroom_owner_token']) : '';
    if (!eottae_talkroom_verify_owner_token($token)) {
        eottae_talkroom_ai_settings_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
    }
}

$data = eottae_talkroom_ai_parse_settings_input($_POST);
$result = eottae_talkroom_ai_save_settings($room_id, $data, $member['mb_id'], $is_super);

if (!empty($result['ok'])) {
    if ($is_super) {
        eottae_talkroom_admin_token(true);
    }
    eottae_talkroom_owner_token(true);
}

eottae_talkroom_ai_settings_json(!empty($result['ok']), $result['message'], array(
    'redirect_url' => eottae_talkroom_ai_settings_url($room_id).'?saved=1',
));
