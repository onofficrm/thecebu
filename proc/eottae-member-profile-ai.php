<?php
/**
 * 회원가입/정보수정 — AI 프로필 사진 생성
 */
require_once dirname(__FILE__).'/_eottae_json_bootstrap.php';

include_once G5_LIB_PATH.'/eottae-member-profile.lib.php';

$token = isset($_POST['eottae_profile_ai_token']) ? trim((string) $_POST['eottae_profile_ai_token']) : '';
$session_token = function_exists('get_session') ? (string) get_session('ss_token') : '';
if ($token === '' || $session_token === '' || !hash_equals($session_token, $token)) {
    eottae_json_send(array('success' => false, 'message' => '요청이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.'));
}

$w = isset($_POST['w']) ? preg_replace('/[^a-z]/', '', (string) $_POST['w']) : '';
if ($w === 'u') {
    if (empty($is_member)) {
        eottae_json_send(array('success' => false, 'message' => '로그인 후 이용해 주세요.'));
    }
}

$nick = isset($_POST['mb_nick']) ? trim(strip_tags((string) $_POST['mb_nick'])) : '';
if ($nick === '' && !empty($member['mb_nick'])) {
    $nick = get_text($member['mb_nick']);
}
if ($nick === '') {
    eottae_json_send(array('success' => false, 'message' => '닉네임을 먼저 입력해 주세요.'));
}

$audience = isset($_POST['mb_2']) ? preg_replace('/[^a-z]/', '', (string) $_POST['mb_2']) : '';
$role = isset($_POST['mb_1']) ? preg_replace('/[^a-z]/', '', (string) $_POST['mb_1']) : '';
if ($role === '' && $audience !== 'tourist' && function_exists('eottae_normalize_member_type_fields')) {
    list($role, $audience) = eottae_normalize_member_type_fields($role, $audience);
}

$result = eottae_member_profile_generate_ai_image($nick, $audience, $role);
if (empty($result['ok'])) {
    eottae_json_send(array(
        'success' => false,
        'message' => $result['message'] ?? 'AI 프로필 생성에 실패했습니다.',
    ));
}

eottae_json_send(array(
    'success' => true,
    'data'    => array(
        'tmp' => $result['tmp'] ?? '',
        'url' => $result['url'] ?? '',
    ),
));
