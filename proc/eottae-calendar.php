<?php
/**
 * 세부어때 캘린더 API
 * POST /proc/eottae-calendar.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-calendar.lib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alert('잘못된 요청입니다.', eottae_calendar_list_url());
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : 'create';
$token = isset($_POST['eottae_calendar_token']) ? trim((string) $_POST['eottae_calendar_token']) : '';

if (!eottae_calendar_verify_member_token($token)) {
    alert('보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.', eottae_calendar_list_url());
}

if (!$is_member || empty($member['mb_id'])) {
    alert('로그인 후 이용해 주세요.', function_exists('eottae_login_url') ? eottae_login_url(eottae_calendar_list_url()) : G5_BBS_URL.'/login.php');
}

$is_super = ($is_admin === 'super');
$writer = array(
    'mb_id'   => $member['mb_id'],
    'mb_nick' => $member['mb_nick'] ?? '',
    'mb_name' => $member['mb_name'] ?? '',
);

if ($action === 'create') {
    $room_id = isset($_POST['related_room_id']) ? (int) $_POST['related_room_id'] : 0;
    $from = isset($_POST['from']) ? preg_replace('/[^a-z_]/', '', (string) $_POST['from']) : '';
    if ($room_id < 1 && !empty($_POST['prefill_room_id'])) {
        $room_id = (int) $_POST['prefill_room_id'];
        $_POST['related_room_id'] = $room_id;
    }
    if ($room_id > 0 && in_array($from, array('talk', 'talk_post'), true)) {
        if (!eottae_calendar_can_create_from_talk($room_id, $member['mb_id'], $is_super)) {
            alert('해당 톡방 참여자만 일정을 등록할 수 있습니다.', eottae_calendar_list_url());
        }
    }

    $result = eottae_calendar_create_event($_POST, $writer);
    if (!empty($result['ok'])) {
        eottae_calendar_member_token(true);
        $event_id = (int) ($result['event_id'] ?? 0);
        goto_url($event_id > 0 ? eottae_calendar_event_url($event_id) : eottae_calendar_list_url());
    }
    alert($result['message'] ?? '일정 등록에 실패했습니다.', eottae_calendar_create_url());
}

if ($action === 'update') {
    $event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
    $result = eottae_calendar_update_event($event_id, $_POST, $member['mb_id'], $is_super);
    if (!empty($result['ok'])) {
        eottae_calendar_member_token(true);
        goto_url(eottae_calendar_event_url($event_id));
    }
    alert($result['message'] ?? '일정 수정에 실패했습니다.', eottae_calendar_edit_url($event_id));
}

if ($action === 'delete') {
    $event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
    $result = eottae_calendar_delete_event($event_id, $member['mb_id'], $is_super);
    if (!empty($result['ok'])) {
        eottae_calendar_member_token(true);
        goto_url(eottae_calendar_list_url(array('deleted' => 1)));
    }
    alert($result['message'] ?? '일정 삭제에 실패했습니다.', eottae_calendar_event_url($event_id));
}

alert('지원하지 않는 요청입니다.', eottae_calendar_list_url());
