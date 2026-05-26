<?php
/**
 * 크론 — 회원 등급/뱃지 주간 작업
 *
 * - 지난 주 활동 랭킹 스냅샷 저장
 * - 이번 주 우수회원 자동 선정 (TOP 3, 이미 있으면 건너뜀)
 *
 * CLI:
 *   php cron/sebu_member_growth_weekly.php
 *   php cron/sebu_member_growth_weekly.php --dry-run
 *   php cron/sebu_member_growth_weekly.php --force-featured
 *
 * 웹 (member_growth_cron_key 또는 talkroom_ai_cron_key 설정 시):
 *   /cron/sebu_member_growth_weekly.php?key=YOUR_SECRET
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
    $_SERVER['REQUEST_URI'] = '/cron/sebu_member_growth_weekly.php';
    $_SERVER['SCRIPT_NAME'] = '/cron/sebu_member_growth_weekly.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
}

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';

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

$dry_run = false;
$force_featured = false;
$featured_limit = 3;

if ($is_cli) {
    $argv = $argv ?? array();
    foreach ($argv as $arg) {
        if ($arg === '--dry-run') {
            $dry_run = true;
        }
        if ($arg === '--force-featured') {
            $force_featured = true;
        }
        if (strpos($arg, '--limit=') === 0) {
            $featured_limit = (int) substr($arg, 8);
        }
    }
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $provided_key = isset($_GET['key']) ? (string) $_GET['key'] : '';
    if (!eottae_member_growth_verify_cron_key($provided_key)) {
        http_response_code(403);
        exit("Forbidden\n");
    }
    $dry_run = !empty($_GET['dry_run']);
    $force_featured = !empty($_GET['force_featured']);
    if (isset($_GET['limit'])) {
        $featured_limit = (int) $_GET['limit'];
    }
}

$result = eottae_member_growth_run_weekly_cron(array(
    'dry_run'        => $dry_run,
    'force_featured' => $force_featured,
    'featured_limit' => $featured_limit,
    'admin_mb_id'    => 'cron',
));

echo "=== sebu_member_growth_weekly ===\n";
echo 'time: '.G5_TIME_YMDHIS."\n";
echo 'dry_run: '.($dry_run ? 'yes' : 'no')."\n";
echo 'previous_week: '.($result['previous_week'] ?? '')."\n";
echo 'current_week: '.($result['current_week'] ?? '')."\n";

if (!empty($result['snapshot'])) {
    $snap = $result['snapshot'];
    echo 'snapshot: '.json_encode($snap, JSON_UNESCAPED_UNICODE)."\n";
}
if (!empty($result['featured'])) {
    $feat = $result['featured'];
    echo 'featured: '.json_encode($feat, JSON_UNESCAPED_UNICODE)."\n";
}

echo "\nDone.\n";
