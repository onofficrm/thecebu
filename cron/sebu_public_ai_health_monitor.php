<?php
/**
 * 크론 — 공개단톡 AI 운영 점검 (슬롯 누락·AI 비활성 알림)
 *
 * CLI:
 *   php cron/sebu_public_ai_health_monitor.php
 *   php cron/sebu_public_ai_health_monitor.php --no-notify
 *
 * 웹:
 *   /cron/sebu_public_ai_health_monitor.php?key=YOUR_SECRET
 *
 * crontab 예시 (슬롯 종료 직후 점검):
 *   10 9,14,20,1 * * * cd /path/to/thecebu && php cron/sebu_public_ai_health_monitor.php
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
    $_SERVER['REQUEST_URI'] = '/cron/sebu_public_ai_health_monitor.php';
    $_SERVER['SCRIPT_NAME'] = '/cron/sebu_public_ai_health_monitor.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
}

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-schedule.lib.php';

if (!defined('_GNUBOARD_')) {
    if ($is_cli) {
        fwrite(STDERR, "GNUBoard bootstrap failed\n");
        exit(1);
    }
    http_response_code(500);
    exit('GNUBoard bootstrap failed');
}

$notify = true;

if ($is_cli) {
    $argv = $argv ?? array();
    if (in_array('--no-notify', $argv, true)) {
        $notify = false;
    }
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $provided_key = isset($_GET['key']) ? (string) $_GET['key'] : '';
    if (!eottae_public_ai_verify_cron_key($provided_key)) {
        http_response_code(403);
        exit("Forbidden\n");
    }
    $notify = empty($_GET['no_notify']);
}

$result = eottae_public_ai_run_health_monitor(array(
    'notify' => $notify,
));

echo "=== sebu_public_ai_health_monitor ===\n";
echo 'time: '.G5_TIME_YMDHIS."\n";
echo 'notify: '.($notify ? 'yes' : 'no')."\n";
echo 'ok: '.(!empty($result['ok']) ? 'yes' : 'no')."\n";
if (!empty($result['issues'])) {
    echo 'issues: '.implode(', ', $result['issues'])."\n";
}
if (!empty($result['slot_stats'])) {
    $ss = $result['slot_stats'];
    echo 'slots_published: '.(int) ($ss['published_count'] ?? 0).' / '.(int) ($ss['total_slots'] ?? 4)."\n";
    echo 'slots_missed: '.(int) ($ss['missed_count'] ?? 0)."\n";
}
echo "done\n";

exit(!empty($result['ok']) ? 0 : 1);
