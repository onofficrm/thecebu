<?php
/**
 * 크론 — 공개단톡(어때봇) 정기 슬롯 발송
 *
 * 하루 4회: 아침 07~08, 점심 12~13, 저녁 18~19, 자정 23~00
 * 슬롯마다 세부어때 일정·날씨·커뮤니티 등 관련 메시지 1건을 반드시 발행합니다.
 *
 * CLI:
 *   php cron/sebu_public_ai_slot_broadcast.php
 *   php cron/sebu_public_ai_slot_broadcast.php --force
 *   php cron/sebu_public_ai_slot_broadcast.php --slot=morning
 *   php cron/sebu_public_ai_slot_broadcast.php --health
 *
 * 웹 (talkroom_ai_cron_key 또는 public_ai_cron_key):
 *   /cron/sebu_public_ai_slot_broadcast.php?key=YOUR_SECRET
 *
 * 서버 crontab 예시 (매시 정각 + 슬롯 시간대):
 *   5 7,12,18,23 * * * cd /path/to/thecebu && php cron/sebu_public_ai_slot_broadcast.php
 *   5 0 * * *     cd /path/to/thecebu && php cron/sebu_public_ai_slot_broadcast.php
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
    $_SERVER['REQUEST_URI'] = '/cron/sebu_public_ai_slot_broadcast.php';
    $_SERVER['SCRIPT_NAME'] = '/cron/sebu_public_ai_slot_broadcast.php';
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

$dry_run = false;
$force = false;
$health = false;
$slot = '';

if ($is_cli) {
    $argv = $argv ?? array();
    foreach ($argv as $arg) {
        if ($arg === '--dry-run') {
            $dry_run = true;
        }
        if ($arg === '--force') {
            $force = true;
        }
        if ($arg === '--health') {
            $health = true;
        }
        if (strpos($arg, '--slot=') === 0) {
            $slot = substr($arg, 7);
        }
    }
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $provided_key = isset($_GET['key']) ? (string) $_GET['key'] : '';
    if (!eottae_public_ai_verify_cron_key($provided_key)) {
        http_response_code(403);
        exit("Forbidden\n");
    }
    $dry_run = !empty($_GET['dry_run']);
    $force = !empty($_GET['force']);
    $health = !empty($_GET['health']);
    $slot = isset($_GET['slot']) ? (string) $_GET['slot'] : '';
}

if ($health) {
    $check = eottae_public_ai_schedule_health_check();
    echo "=== sebu_public_ai_slot_broadcast (health) ===\n";
    echo 'time: '.G5_TIME_YMDHIS."\n";
    foreach ($check as $key => $value) {
        if (is_array($value)) {
            echo $key.': '.json_encode($value, JSON_UNESCAPED_UNICODE)."\n";
        } elseif (is_bool($value)) {
            echo $key.': '.($value ? 'yes' : 'no')."\n";
        } else {
            echo $key.': '.$value."\n";
        }
    }
    echo "done\n";
    exit(0);
}

$options = array(
    'dry_run' => $dry_run,
    'force'   => $force,
);
if ($slot !== '') {
    $options['slot'] = $slot;
}

$result = eottae_public_ai_run_slot_broadcast($options);

echo "=== sebu_public_ai_slot_broadcast ===\n";
echo 'time: '.G5_TIME_YMDHIS."\n";
echo 'dry_run: '.($dry_run ? 'yes' : 'no')."\n";
echo 'force: '.($force ? 'yes' : 'no')."\n";
echo 'slot: '.($result['slot'] ?? '')."\n";
echo 'ok: '.(!empty($result['ok']) ? 'yes' : 'no')."\n";
echo 'reason: '.($result['reason'] ?? '')."\n";
echo 'published: '.(int) ($result['published'] ?? 0)."\n";
echo 'used_fallback: '.(!empty($result['used_fallback']) ? 'yes' : 'no')."\n";
if (!empty($result['candidate_id'])) {
    echo 'candidate_id: '.(int) $result['candidate_id']."\n";
}
if (!empty($result['wr_id'])) {
    echo 'wr_id: '.(int) $result['wr_id']."\n";
}
if (!empty($result['calendar_sync']) && is_array($result['calendar_sync'])) {
    $cs = $result['calendar_sync'];
    echo 'calendar_sync: '.(!empty($cs['skipped']) ? 'skipped' : (!empty($cs['ok']) ? 'ok' : 'fail'));
    if (!empty($cs['reason'])) {
        echo ' ('.$cs['reason'].')';
    }
    echo "\n";
}
echo "done\n";

$ok_reasons = array('published', 'dry_run', 'slot_already_published', 'outside_slot_window');
$exit_ok = !empty($result['ok']) || in_array($result['reason'] ?? '', $ok_reasons, true);

exit($exit_ok ? 0 : 1);
