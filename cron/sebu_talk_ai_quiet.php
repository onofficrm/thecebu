<?php
/**
 * 크론 — 조용한 방 AI 화제 던지기
 *
 * CLI:
 *   php cron/sebu_talk_ai_quiet.php
 *   php cron/sebu_talk_ai_quiet.php --dry-run
 *   php cron/sebu_talk_ai_quiet.php --room-id=3
 *
 * 웹 (talkroom_ai_cron_key 설정 시):
 *   /cron/sebu_talk_ai_quiet.php?key=YOUR_SECRET
 *   /cron/sebu_talk_ai_quiet.php?key=YOUR_SECRET&dry_run=1&room_id=3
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
    $_SERVER['REQUEST_URI'] = '/cron/sebu_talk_ai_quiet.php';
    $_SERVER['SCRIPT_NAME'] = '/cron/sebu_talk_ai_quiet.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
}

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-quiet.lib.php';

if (!defined('_GNUBOARD_')) {
    if ($is_cli) {
        fwrite(STDERR, "GNUBoard bootstrap failed\n");
        exit(1);
    }
    http_response_code(500);
    exit('GNUBoard bootstrap failed');
}

$provided_key = '';
if ($is_cli) {
  $argv = $argv ?? array();
  foreach ($argv as $arg) {
    if (strpos($arg, '--room-id=') === 0) {
      $_GET['room_id'] = (int) substr($arg, 10);
    }
    if ($arg === '--dry-run') {
      $_GET['dry_run'] = '1';
    }
  }
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $provided_key = isset($_GET['key']) ? (string) $_GET['key'] : '';
    if (!eottae_talkroom_ai_verify_cron_key($provided_key)) {
        http_response_code(403);
        exit("Forbidden\n");
    }
}

$dry_run = !empty($_GET['dry_run']);
$room_id = isset($_GET['room_id']) ? (int) $_GET['room_id'] : 0;

$result = eottae_talkroom_ai_run_quiet_cron(array(
    'dry_run' => $dry_run,
    'room_id' => $room_id,
));

$summary = $result['summary'];
echo "=== sebu_talk_ai_quiet ===\n";
echo 'time: '.G5_TIME_YMDHIS."\n";
echo 'dry_run: '.(!empty($summary['dry_run']) ? 'yes' : 'no')."\n";
echo 'checked: '.(int) $summary['checked']."\n";
echo 'posted: '.(int) $summary['posted']."\n";
echo 'skipped: '.(int) $summary['skipped']."\n";
echo 'failed: '.(int) $summary['failed']."\n\n";

foreach ($result['results'] as $row) {
    echo sprintf(
        "[%s] room #%d %s — %s (%s)\n",
        strtoupper((string) ($row['status'] ?? 'unknown')),
        (int) ($row['room_id'] ?? 0),
        (string) ($row['room_name'] ?? ''),
        (string) ($row['message'] ?? ''),
        (string) ($row['reason'] ?? '')
    );
    if (!empty($row['subject'])) {
        echo '  subject: '.$row['subject']."\n";
    }
    if (!empty($row['content'])) {
        echo '  content: '.$row['content']."\n";
    }
    if (!empty($row['post_id'])) {
        echo '  post_id: '.(int) $row['post_id']."\n";
    }
}

echo "\nDone.\n";
