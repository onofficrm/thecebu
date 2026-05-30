<?php
/**
 * 번역 큐 — 방문·외부 웹크론 트리거 (일반 호스팅용)
 *
 * GET /proc/eottae-translation-traffic-tick.php
 * GET /proc/eottae-translation-traffic-tick.php?key=YOUR_SECRET
 *
 * cron-job.org 등에서 5~10분 간격 호출 가능.
 * 사이트 방문 시 common_header shutdown 훅에서도 자동 처리됩니다.
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-translation.lib.php';

header('Content-Type: application/json; charset=utf-8');

$key = isset($_GET['key']) ? (string) $_GET['key'] : '';
$require_key = eottae_translation_cron_key() !== '';

if ($require_key && !eottae_translation_verify_cron_key($key)) {
    http_response_code(403);
    echo json_encode(array(
        'success' => false,
        'message' => 'Forbidden',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$dry_run = !empty($_GET['dry_run']);
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 0;
$options = array(
    'force' => true,
    'dry_run' => $dry_run,
);
if ($limit > 0) {
    $options['limit'] = $limit;
}

$result = eottae_translation_maybe_run_queue_tick($options);

echo json_encode(array(
    'success' => !empty($result['ok']),
    'ran' => !empty($result['ran']),
    'reason' => (string) ($result['reason'] ?? ''),
    'processed' => (int) ($result['processed'] ?? 0),
    'succeeded' => (int) ($result['succeeded'] ?? 0),
    'failed' => (int) ($result['failed'] ?? 0),
    'time' => defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'),
), JSON_UNESCAPED_UNICODE);
