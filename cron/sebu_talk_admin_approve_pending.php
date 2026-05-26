<?php
/**
 * 크론 — 승인 대기 톡방 개설 일괄 승인
 *
 * CLI:
 *   php cron/sebu_talk_admin_approve_pending.php
 *   php cron/sebu_talk_admin_approve_pending.php --dry-run
 *
 * 웹 (talkroom_ai_cron_key 설정 시):
 *   /cron/sebu_talk_admin_approve_pending.php?key=YOUR_SECRET
 *   /cron/sebu_talk_admin_approve_pending.php?key=YOUR_SECRET&dry_run=1
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
    $_SERVER['REQUEST_URI'] = '/cron/sebu_talk_admin_approve_pending.php';
    $_SERVER['SCRIPT_NAME'] = '/cron/sebu_talk_admin_approve_pending.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
}

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

if (!function_exists('g5site_cfg') && defined('G5_PATH') && is_file(G5_PATH.'/_site.config.php')) {
    include_once G5_PATH.'/_site.config.php';
}

if (!defined('_GNUBOARD_')) {
    if ($is_cli) {
        fwrite(STDERR, "GNUBoard bootstrap failed\n");
        exit(1);
    }
    http_response_code(500);
    exit("GNUBoard bootstrap failed\n");
}

if ($is_cli) {
    $argv = $argv ?? array();
    $dry_run = in_array('--dry-run', $argv, true);
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $provided_key = isset($_GET['key']) ? (string) $_GET['key'] : '';
    if (!eottae_talkroom_maintenance_verify_key($provided_key)) {
        if (!function_exists('eottae_talkroom_ai_verify_cron_key')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-quiet.lib.php';
        }
        if (!eottae_talkroom_ai_verify_cron_key($provided_key)) {
            http_response_code(403);
            exit("Forbidden\n");
        }
    }
    $dry_run = !empty($_GET['dry_run']);
}

$admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($config['cf_admin'] ?? 'admin'));
$pending_count = eottae_talkroom_pending_count();

echo "=== sebu_talk_admin_approve_pending ===\n";
echo 'time: '.G5_TIME_YMDHIS."\n";
echo 'admin: '.$admin_mb_id."\n";
echo 'pending_count: '.$pending_count."\n";
echo 'dry_run: '.($dry_run ? 'yes' : 'no')."\n\n";

if ($pending_count < 1) {
    echo "No pending room applications.\n";
    exit(0);
}

if ($dry_run) {
    $applications = eottae_talkroom_admin_resolve_applications('pending', 500);
    foreach ($applications as $application) {
        echo sprintf(
            "[DRY-RUN] room #%d — %s (owner=%s)\n",
            (int) ($application['room_id'] ?? 0),
            (string) ($application['room_name'] ?? ''),
            (string) ($application['owner_mb_id'] ?? '')
        );
    }
    echo "\nDone.\n";
    exit(0);
}

$result = eottae_talkroom_approve_all_pending_rooms($admin_mb_id);
echo (string) ($result['message'] ?? '')."\n\n";

foreach ($result['items'] as $item) {
    echo sprintf(
        "[%s] room #%d — %s (%s)\n",
        !empty($item['ok']) ? 'OK' : 'FAIL',
        (int) ($item['room_id'] ?? 0),
        (string) ($item['room_name'] ?? ''),
        (string) ($item['message'] ?? '')
    );
}

echo "\nDone.\n";
