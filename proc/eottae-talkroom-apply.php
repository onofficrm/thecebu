<?php
/**
 * 세부톡방 개설 신청 처리
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alert('잘못된 접근입니다.', eottae_talkroom_create_url());
}

if (empty($is_member) || empty($member['mb_id'])) {
    alert('로그인 후 톡방 개설을 신청할 수 있습니다.', eottae_login_url(eottae_talkroom_create_url()));
}

$token = isset($_POST['eottae_talkroom_token']) ? trim((string) $_POST['eottae_talkroom_token']) : '';
if (!eottae_talkroom_verify_apply_token($token)) {
    alert('보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.', eottae_talkroom_create_url());
}

$data = eottae_talkroom_parse_apply_input($_POST);
$errors = eottae_talkroom_validate_apply($data);
if (!empty($errors)) {
    alert($errors[0], eottae_talkroom_create_url());
}

$room_id = eottae_talkroom_insert_apply($member['mb_id'], $data);
if (!$room_id) {
    alert('톡방 저장에 실패했습니다. 잠시 후 다시 시도해 주세요.', eottae_talkroom_create_url());
}

$approve = eottae_talkroom_approve_room($room_id, $member['mb_id']);
if (empty($approve['ok'])) {
    alert($approve['message'] ?? '톡방을 공개하는 데 실패했습니다. 고객센터로 문의해 주세요.', eottae_talkroom_apply_status_url());
}

eottae_talkroom_apply_token(true);

goto_url(eottae_talkroom_enter_url($room_id).'?created=1');
