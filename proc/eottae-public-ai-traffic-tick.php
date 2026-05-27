<?php
/**
 * 공개단톡 AI — 방문·외부 웹크론 트리거 (일반 호스팅용)
 *
 * GET /proc/eottae-public-ai-traffic-tick.php
 * GET /proc/eottae-public-ai-traffic-tick.php?key=YOUR_SECRET  (talkroom_ai_cron_key)
 *
 * cron-job.org 등 무료 외부 스케줄러에서 5~15분 간격 호출 가능.
 * 홈 공개톡 폴링에서도 자동 호출됩니다.
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-schedule.lib.php';

header('Content-Type: application/json; charset=utf-8');

$key = isset($_GET['key']) ? (string) $_GET['key'] : '';
$require_key = false;
if (function_exists('g5site_cfg')) {
    $secret = trim((string) g5site_cfg('public_ai_cron_key', ''));
    if ($secret === '') {
        $secret = trim((string) g5site_cfg('talkroom_ai_cron_key', ''));
    }
    $require_key = ($secret !== '');
}

if ($require_key && !eottae_public_ai_verify_cron_key($key)) {
    http_response_code(403);
    echo json_encode(array(
        'success' => false,
        'message' => 'Forbidden',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$dry_run = !empty($_GET['dry_run']);
$result = eottae_public_ai_maybe_run_traffic_slot_broadcast(array(
    'source'  => 'web_tick',
    'dry_run' => $dry_run,
));

echo json_encode(array(
    'success'   => !empty($result['ok']),
    'ran'       => !empty($result['ran']),
    'reason'    => (string) ($result['reason'] ?? ''),
    'slot'      => (string) ($result['slot'] ?? ''),
    'published' => (int) ($result['published'] ?? 0),
    'time'      => defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'),
), JSON_UNESCAPED_UNICODE);
