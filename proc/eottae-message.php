<?php
/**
 * 세부어때 메시지 API
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-message.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_message_json($success, $message, $extra = array())
{
    echo json_encode(array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array()), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_message_json(false, '잘못된 요청입니다.');
}

if (empty($is_member) || empty($member['mb_id'])) {
    $login = function_exists('eottae_login_url')
        ? eottae_login_url(eottae_message_url())
        : G5_BBS_URL.'/login.php';
    eottae_message_json(false, '로그인이 필요합니다.', array('redirect' => $login));
}

$token = isset($_POST['eottae_message_token']) ? trim((string) $_POST['eottae_message_token']) : '';
if (!eottae_message_verify_token($token)) {
    eottae_message_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

if ($action === 'send') {
    $receiver = isset($_POST['receiver']) ? trim((string) $_POST['receiver']) : '';
    $body = isset($_POST['body']) ? (string) $_POST['body'] : '';
    $result = eottae_message_send($member, $receiver, $body);
    if (!empty($result['ok'])) {
        eottae_message_token(true);
    }
    eottae_message_json(!empty($result['ok']), $result['message'] ?? '', array(
        'thread_id' => (int) ($result['thread_id'] ?? 0),
        'redirect' => !empty($result['thread_id']) ? eottae_message_url(array('thread_id' => (int) $result['thread_id'])) : '',
    ));
}

if ($action === 'shop_inquiry') {
    $inquiry_code = isset($_POST['inquiry_code']) ? trim((string) $_POST['inquiry_code']) : '';
    $body = isset($_POST['body']) ? (string) $_POST['body'] : '';
    $result = eottae_message_send_shop_inquiry($member, $inquiry_code, $body);
    if (!empty($result['ok'])) {
        eottae_message_token(true);
    }
    eottae_message_json(!empty($result['ok']), $result['message'] ?? '', array(
        'thread_id' => (int) ($result['thread_id'] ?? 0),
        'redirect' => !empty($result['thread_id']) ? eottae_message_url(array('thread_id' => (int) $result['thread_id'])) : '',
    ));
}

if ($action === 'operator_inquiry') {
    $body = isset($_POST['body']) ? (string) $_POST['body'] : '';
    $result = eottae_message_send_operator($member, $body);
    if (!empty($result['ok'])) {
        eottae_message_token(true);
    }
    eottae_message_json(!empty($result['ok']), $result['message'] ?? '', array(
        'thread_id' => (int) ($result['thread_id'] ?? 0),
        'redirect' => !empty($result['thread_id']) ? eottae_message_url(array('thread_id' => (int) $result['thread_id'])) : '',
    ));
}

if ($action === 'golf_join_inquiry') {
    $join_id = isset($_POST['join_id']) ? (int) $_POST['join_id'] : 0;
    $body = isset($_POST['body']) ? (string) $_POST['body'] : '';
    $result = eottae_message_send_golf_join_inquiry($member, $join_id, $body);
    if (!empty($result['ok'])) {
        eottae_message_token(true);
    }
    eottae_message_json(!empty($result['ok']), $result['message'] ?? '', array(
        'thread_id' => (int) ($result['thread_id'] ?? 0),
        'redirect' => !empty($result['thread_id']) ? eottae_message_url(array('thread_id' => (int) $result['thread_id'])) : '',
    ));
}

if ($action === 'report_reply') {
    include_once G5_LIB_PATH.'/eottae-report.lib.php';

    if (empty($is_admin) || !eottae_report_is_board_admin($is_admin)) {
        eottae_message_json(false, '관리자만 제보 답변을 보낼 수 있습니다.');
    }

    $bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', (string) $_POST['bo_table']) : '';
    $wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
    $body = isset($_POST['body']) ? (string) $_POST['body'] : '';
    $result = eottae_message_send_report_reply($member, $bo_table, $wr_id, $body);
    if (!empty($result['ok'])) {
        eottae_message_token(true);
    }
    eottae_message_json(!empty($result['ok']), $result['message'] ?? '', array(
        'thread_id' => (int) ($result['thread_id'] ?? 0),
        'redirect' => !empty($result['thread_id']) ? eottae_message_url(array('thread_id' => (int) $result['thread_id'])) : '',
    ));
}

if ($action === 'reply') {
    $thread_id = isset($_POST['thread_id']) ? (int) $_POST['thread_id'] : 0;
    $body = isset($_POST['body']) ? (string) $_POST['body'] : '';
    $result = eottae_message_reply($thread_id, $member, $body);
    if (!empty($result['ok'])) {
        eottae_message_token(true);
    }
    eottae_message_json(!empty($result['ok']), $result['message'] ?? '', array(
        'thread_id' => (int) ($result['thread_id'] ?? $thread_id),
        'message_id' => (int) ($result['message_id'] ?? 0),
        'redirect' => eottae_message_url(array('thread_id' => $thread_id)),
    ));
}

if ($action === 'archive') {
    $thread_id = isset($_POST['thread_id']) ? (int) $_POST['thread_id'] : 0;
    if (eottae_message_archive_thread($thread_id, $member['mb_id'])) {
        eottae_message_json(true, '대화를 보관했습니다.', array('redirect' => eottae_message_url()));
    }
    eottae_message_json(false, '대화를 찾을 수 없습니다.');
}

eottae_message_json(false, '지원하지 않는 요청입니다.');
