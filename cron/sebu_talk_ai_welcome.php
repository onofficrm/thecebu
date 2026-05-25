<?php
/**
 * 크론 — AI 신규회원 환영 (대기 큐 처리)
 *
 * CLI:
 *   php cron/sebu_talk_ai_welcome.php
 *   php cron/sebu_talk_ai_welcome.php --room-id=3
 *
 * 웹 (talkroom_ai_cron_key 설정 시):
 *   /cron/sebu_talk_ai_welcome.php?key=YOUR_SECRET
 */
$g5_path = realpath(__DIR__.'/..');
chdir($g5_path);

$is_cli = (php_sapi_name() === 'cli');

if ($is_cli) {
    $_SERVER['SERVER_NAME'] = 'thecebu.co.kr';
    $_SERVER['HTTP_HOST'] = 'thecebu.co.kr';
    $_SERVER['SERVER_PORT'] = '443';
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['REQUEST_URI'] = '/cron/sebu_talk_ai_welcome.php';
    $_SERVER['SCRIPT_NAME'] = '/cron/sebu_talk_ai_welcome.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
}

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-welcome.lib.php';

if (!defined('_GNUBOARD_')) {
    if ($is_cli) {
        fwrite(STDERR, "GNUBoard bootstrap failed\n");
        exit(1);
    }
    http_response_code(500);
    exit('GNUBoard bootstrap failed');
}

if ($is_cli) {
    $argv = $argv ?? array();
    foreach ($argv as $arg) {
        if (strpos($arg, '--room-id=') === 0) {
            $_GET['room_id'] = (int) substr($arg, 10);
        }
    }
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $provided_key = isset($_GET['key']) ? (string) $_GET['key'] : '';
    if (!function_exists('eottae_talkroom_ai_verify_cron_key')) {
        include_once G5_LIB_PATH.'/eottae-talkroom-ai-quiet.lib.php';
    }
    if (!eottae_talkroom_ai_verify_cron_key($provided_key)) {
        http_response_code(403);
        exit("Forbidden\n");
    }
}

$room_id = isset($_GET['room_id']) ? (int) $_GET['room_id'] : 0;

$result = eottae_talkroom_ai_run_welcome_cron(array(
    'room_id' => $room_id,
    'limit'   => 20,
));

$summary = $result['summary'];
echo "=== sebu_talk_ai_welcome ===\n";
echo 'time: '.G5_TIME_YMDHIS."\n";
echo 'processed: '.(int) $summary['processed']."\n";
echo 'success: '.(int) $summary['success']."\n";
echo 'skipped: '.(int) $summary['skipped']."\n";
echo 'failed: '.(int) $summary['failed']."\n\n";

foreach ($result['results'] as $row) {
    echo sprintf(
        "[%s] room #%d mb=%s — %s (%s)\n",
        strtoupper((string) ($row['status'] ?? 'unknown')),
        (int) ($row['room_id'] ?? 0),
        (string) ($row['mb_id'] ?? ''),
        (string) ($row['message'] ?? ''),
        (string) ($row['reason'] ?? '')
    );
}

echo "\nDone.\n";
