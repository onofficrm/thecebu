<?php
/**
 * 게시글 AI 음성 읽기 API
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-tts.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_tts_json($success, $message = '', $extra = array())
{
    echo json_encode(array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$action = isset($_REQUEST['action']) ? trim((string) $_REQUEST['action']) : 'audio';
$bo_table = isset($_REQUEST['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', (string) $_REQUEST['bo_table']) : '';
$wr_id = isset($_REQUEST['wr_id']) ? (int) $_REQUEST['wr_id'] : 0;

if ($action !== 'audio') {
    eottae_tts_json(false, '지원하지 않는 요청입니다.');
}
if ($bo_table === '' || $wr_id < 1) {
    eottae_tts_json(false, '글 정보가 올바르지 않습니다.');
}

$result = eottae_tts_get_or_create_audio($bo_table, $wr_id);
eottae_tts_json(!empty($result['ok']), (string) ($result['message'] ?? ''), array(
    'audio_url' => (string) ($result['audio_url'] ?? ''),
    'cached' => !empty($result['cached']),
));
