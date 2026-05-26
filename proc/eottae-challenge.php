<?php
/**
 * 세부어때 챌린지 API
 * POST /proc/eottae-challenge.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
include_once G5_LIB_PATH.'/eottae-challenge-likes.lib.php';
include_once G5_LIB_PATH.'/eottae-challenge-report.lib.php';

$is_json = isset($_POST['response']) && $_POST['response'] === 'json';

function eottae_challenge_proc_json($success, $message, $extra = array())
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array()), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($is_json) {
        eottae_challenge_proc_json(false, '잘못된 요청입니다.');
    }
    alert('잘못된 요청입니다.', eottae_challenge_list_url());
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$token = isset($_POST['eottae_challenge_token']) ? trim((string) $_POST['eottae_challenge_token']) : '';

if (!eottae_challenge_verify_member_token($token)) {
    if ($is_json) {
        eottae_challenge_proc_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.');
    }
    alert('보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.', eottae_challenge_list_url());
}

if ($action === 'create_entry') {
    if (!$is_member || empty($member['mb_id'])) {
        alert('로그인 후 참여할 수 있습니다.', function_exists('eottae_login_url') ? eottae_login_url(eottae_challenge_list_url()) : G5_BBS_URL.'/login.php');
    }

    $challenge_id = isset($_POST['challenge_id']) ? (int) $_POST['challenge_id'] : 0;
    $writer = array(
        'mb_id'   => $member['mb_id'],
        'mb_nick' => $member['mb_nick'] ?? '',
        'mb_name' => $member['mb_name'] ?? '',
    );

    $result = eottae_challenge_create_entry($challenge_id, $_POST, $writer);
    eottae_challenge_member_token(true);

    if (!empty($result['ok'])) {
        $entry_id = (int) ($result['entry_id'] ?? 0);
        $msg = (string) ($result['message'] ?? '참여 완료');
        $reward = $result['reward'] ?? array();
        if (!empty($reward['point'])) {
            $msg .= ' (+'.number_format((int) $reward['point']).'P)';
        }
        goto_url(eottae_challenge_entry_url($entry_id).'?joined=1&msg='.urlencode($msg));
    }

    alert($result['message'] ?? '참여에 실패했습니다.', eottae_challenge_write_url($challenge_id));
}

if ($action === 'delete_entry') {
    if (!$is_member || empty($member['mb_id'])) {
        eottae_challenge_proc_json(false, '로그인이 필요합니다.');
    }

    $entry_id = isset($_POST['entry_id']) ? (int) $_POST['entry_id'] : 0;
    $result = eottae_challenge_delete_entry($entry_id, $member['mb_id'], $is_admin === 'super');
    eottae_challenge_member_token(true);
    eottae_challenge_proc_json(!empty($result['ok']), $result['message'] ?? '', array(
        'redirect' => eottae_challenge_mypage_url(),
    ));
}

if ($action === 'toggle_like') {
    if (!$is_member || empty($member['mb_id'])) {
        eottae_challenge_proc_json(false, '로그인 후 공감할 수 있습니다.');
    }

    $entry_id = isset($_POST['entry_id']) ? (int) $_POST['entry_id'] : 0;
    $result = eottae_challenge_toggle_like($entry_id, $member['mb_id']);
    eottae_challenge_member_token(true);
    eottae_challenge_proc_json(!empty($result['ok']), $result['message'] ?? '', array(
        'liked'      => !empty($result['liked']),
        'like_count' => (int) ($result['like_count'] ?? 0),
    ));
}

if ($action === 'add_comment') {
    if (!$is_member || empty($member['mb_id'])) {
        eottae_challenge_proc_json(false, '로그인 후 댓글을 작성할 수 있습니다.');
    }

    $entry_id = isset($_POST['entry_id']) ? (int) $_POST['entry_id'] : 0;
    $content = isset($_POST['content']) ? (string) $_POST['content'] : '';
    $result = eottae_challenge_add_comment($entry_id, $member['mb_id'], $member['mb_nick'] ?? '', $content);
    eottae_challenge_member_token(true);
    eottae_challenge_proc_json(!empty($result['ok']), $result['message'] ?? '');
}

if ($action === 'report') {
    if (!$is_member || empty($member['mb_id'])) {
        eottae_challenge_proc_json(false, '로그인 후 신고할 수 있습니다.');
    }

    $report_token = isset($_POST['eottae_challenge_report_token']) ? trim((string) $_POST['eottae_challenge_report_token']) : '';
    if (!eottae_challenge_verify_report_token($report_token)) {
        eottae_challenge_proc_json(false, '보안 토큰이 만료되었습니다.');
    }

    $entry_id = isset($_POST['entry_id']) ? (int) $_POST['entry_id'] : 0;
    $reason = isset($_POST['reason']) ? (string) $_POST['reason'] : '';
    $memo = isset($_POST['memo']) ? (string) $_POST['memo'] : '';
    $result = eottae_challenge_submit_report($entry_id, $member['mb_id'], $reason, $memo);
    eottae_challenge_report_token(true);
    eottae_challenge_member_token(true);
    eottae_challenge_proc_json(!empty($result['ok']), $result['message'] ?? '');
}

if ($is_json) {
    eottae_challenge_proc_json(false, '지원하지 않는 요청입니다.');
}

alert('지원하지 않는 요청입니다.', eottae_challenge_list_url());
