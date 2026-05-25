<?php
/**
 * 크론 — 세부광장 AI 오늘의 질문
 *
 * CLI:
 *   php cron/sebu_plaza_ai_daily_question.php
 *   php cron/sebu_plaza_ai_daily_question.php --dry-run
 *   php cron/sebu_plaza_ai_daily_question.php --test
 *
 * 웹 (plaza_ai_cron_key 또는 talkroom_ai_cron_key 설정 시):
 *   /cron/sebu_plaza_ai_daily_question.php?key=YOUR_SECRET
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
    $_SERVER['REQUEST_URI'] = '/cron/sebu_plaza_ai_daily_question.php';
    $_SERVER['SCRIPT_NAME'] = '/cron/sebu_plaza_ai_daily_question.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
}

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
include_once G5_LIB_PATH.'/eottae-plaza-ai.lib.php';

if (!defined('_GNUBOARD_')) {
    if ($is_cli) {
        fwrite(STDERR, "GNUBoard bootstrap failed\n");
        exit(1);
    }
    http_response_code(500);
    exit('GNUBoard bootstrap failed');
}

$dry_run = false;
$is_test = false;
$force = false;

if ($is_cli) {
    $argv = $argv ?? array();
    foreach ($argv as $arg) {
        if ($arg === '--dry-run') {
            $dry_run = true;
        }
        if ($arg === '--test') {
            $is_test = true;
            $force = true;
        }
        if ($arg === '--force') {
            $force = true;
        }
    }
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $provided_key = isset($_GET['key']) ? (string) $_GET['key'] : '';
    if (!eottae_plaza_ai_verify_cron_key($provided_key)) {
        http_response_code(403);
        exit("Forbidden\n");
    }
    $dry_run = !empty($_GET['dry_run']);
    $is_test = !empty($_GET['test']);
    $force = !empty($_GET['force']);
}

$result = eottae_plaza_ai_run_daily_question(array(
    'dry_run' => $dry_run,
    'is_test' => $is_test,
    'force'   => $force,
));

echo "=== sebu_plaza_ai_daily_question ===\n";
echo 'time: '.G5_TIME_YMDHIS."\n";
echo 'dry_run: '.($dry_run ? 'yes' : 'no')."\n";
echo 'test: '.($is_test ? 'yes' : 'no')."\n";
echo 'status: '.($result['status'] ?? 'unknown')."\n";
echo 'reason: '.($result['reason'] ?? '')."\n";
echo 'message: '.($result['message'] ?? '')."\n";
if (!empty($result['post_id'])) {
    echo 'post_id: '.(int) $result['post_id']."\n";
}
if (!empty($result['subject'])) {
    echo 'subject: '.$result['subject']."\n";
}
if (!empty($result['content'])) {
    echo 'content: '.str_replace("\n", ' / ', $result['content'])."\n";
}
echo "\nDone.\n";

exit(($result['status'] ?? '') === 'failed' ? 1 : 0);
