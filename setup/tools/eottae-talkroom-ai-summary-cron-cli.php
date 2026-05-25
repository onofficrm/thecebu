<?php
/**
 * CLI — AI 톡방 요약
 *
 * dry-run: php setup/tools/eottae-talkroom-ai-summary-cron-cli.php --dry-run --room-id=3
 * 실행:   php setup/tools/eottae-talkroom-ai-summary-cron-cli.php --room-id=3
 */
if (php_sapi_name() !== 'cli') {
    exit("CLI only\n");
}

$g5_path = realpath(__DIR__.'/../..');
chdir($g5_path);

$_SERVER['SERVER_NAME'] = 'thecebu.co.kr';
$_SERVER['HTTP_HOST'] = 'thecebu.co.kr';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/setup/tools/eottae-talkroom-ai-summary-cron-cli.php';
$_SERVER['SCRIPT_NAME'] = '/setup/tools/eottae-talkroom-ai-summary-cron-cli.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-summary.lib.php';

if (!defined('_GNUBOARD_')) {
    fwrite(STDERR, "GNUBoard bootstrap failed\n");
    exit(1);
}

$argv = $argv ?? array();
$dry_run = in_array('--dry-run', $argv, true);
$room_id = 0;
foreach ($argv as $arg) {
    if (strpos($arg, '--room-id=') === 0) {
        $room_id = (int) substr($arg, 10);
    }
}

$result = eottae_talkroom_ai_run_summary_cron(array(
    'dry_run' => $dry_run,
    'room_id' => $room_id,
));

$summary = $result['summary'];
echo "=== talkroom AI summary cron ===\n";
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
}

echo "\nDone.\n";
