<?php
/**
 * 크론 — 광고 플랫폼 유지보수
 *
 * - 만료 처리 / 예약 활성화 / 연장 알림 쪽지
 * - 이벤트 dedupe 정리
 *
 * CLI:
 *   php cron/sebu_ad_platform_maintain.php
 *   php cron/sebu_ad_platform_maintain.php --dry-run
 *
 * 웹 (ad_platform_cron_key 또는 talkroom_ai_cron_key 설정 시):
 *   /cron/sebu_ad_platform_maintain.php?key=YOUR_SECRET
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
    $_SERVER['REQUEST_URI'] = '/cron/sebu_ad_platform_maintain.php';
    $_SERVER['SCRIPT_NAME'] = '/cron/sebu_ad_platform_maintain.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
}

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-ad-platform.lib.php';

if (!defined('_GNUBOARD_')) {
    if ($is_cli) {
        fwrite(STDERR, "GNUBoard bootstrap failed\n");
        exit(1);
    }
    http_response_code(500);
    exit("GNUBoard bootstrap failed\n");
}

$dry_run = false;

if ($is_cli) {
    $argv = $argv ?? array();
    foreach ($argv as $arg) {
        if ($arg === '--dry-run') {
            $dry_run = true;
        }
    }
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $provided_key = isset($_GET['key']) ? (string) $_GET['key'] : '';
    if (!eottae_ad_platform_verify_cron_key($provided_key)) {
        http_response_code(403);
        exit("Forbidden\n");
    }
    $dry_run = !empty($_GET['dry_run']);
}

$result = eottae_ad_platform_run_maintain_cron(array(
    'dry_run' => $dry_run,
));

echo "=== sebu_ad_platform_maintain ===\n";
echo 'time: '.G5_TIME_YMDHIS."\n";
echo 'dry_run: '.($dry_run ? 'yes' : 'no')."\n";
echo 'ok: '.(!empty($result['ok']) ? 'yes' : 'no')."\n";
echo 'message: '.($result['message'] ?? '')."\n";
if (!empty($result['ran_at'])) {
    echo 'ran_at: '.$result['ran_at']."\n";
}
echo "done\n";
