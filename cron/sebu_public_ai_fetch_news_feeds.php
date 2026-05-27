<?php
/**
 * 크론 — RSS/뉴스 피드 자동 수집 → 외부뉴스 테이블 적재
 *
 * CLI:
 *   php cron/sebu_public_ai_fetch_news_feeds.php
 *   php cron/sebu_public_ai_fetch_news_feeds.php --force
 *   php cron/sebu_public_ai_fetch_news_feeds.php --dry-run
 *
 * 웹 (public_ai_cron_key):
 *   /cron/sebu_public_ai_fetch_news_feeds.php?key=YOUR_SECRET
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
    $_SERVER['REQUEST_URI'] = '/cron/sebu_public_ai_fetch_news_feeds.php';
    $_SERVER['SCRIPT_NAME'] = '/cron/sebu_public_ai_fetch_news_feeds.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
}

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-news.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-news-feed.lib.php';

if (!defined('_GNUBOARD_')) {
    if ($is_cli) {
        fwrite(STDERR, "GNUBoard bootstrap failed\n");
        exit(1);
    }
    http_response_code(500);
    exit('GNUBoard bootstrap failed');
}

$force = false;
$dry_run = false;

if ($is_cli) {
    $argv = $argv ?? array();
    foreach ($argv as $arg) {
        if ($arg === '--force') {
            $force = true;
        }
        if ($arg === '--dry-run') {
            $dry_run = true;
        }
    }
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $provided_key = isset($_GET['key']) ? (string) $_GET['key'] : '';
    if (!function_exists('eottae_public_ai_verify_cron_key') || !eottae_public_ai_verify_cron_key($provided_key)) {
        http_response_code(403);
        exit("Forbidden\n");
    }
    $force = !empty($_GET['force']);
    $dry_run = !empty($_GET['dry_run']);
}

$feed_id = isset($_GET['feed_id']) ? (int) $_GET['feed_id'] : 0;
if ($is_cli && !empty($argv)) {
    foreach ($argv as $arg) {
        if (preg_match('/^--feed-id=(\d+)$/', $arg, $m)) {
            $feed_id = (int) $m[1];
        }
    }
}

eottae_public_ai_news_feed_ensure_schema();

if ($feed_id > 0) {
    $result = eottae_public_ai_news_feed_fetch_one($feed_id, array(
        'force'   => $force,
        'dry_run' => $dry_run,
    ));
    echo "=== sebu_public_ai_fetch_news_feeds (single) ===\n";
    echo 'feed_id: '.$feed_id."\n";
    echo 'ok: '.(!empty($result['ok']) ? 'yes' : 'no')."\n";
    echo 'message: '.($result['message'] ?? '')."\n";
    echo 'inserted: '.(int) ($result['inserted'] ?? 0)."\n";
    exit(!empty($result['ok']) ? 0 : 1);
}

$summary = eottae_public_ai_news_feed_run_all(array(
    'force'   => $force,
    'dry_run' => $dry_run,
));

echo "=== sebu_public_ai_fetch_news_feeds ===\n";
echo 'time: '.G5_TIME_YMDHIS."\n";
echo 'dry_run: '.($dry_run ? 'yes' : 'no')."\n";
echo 'force: '.($force ? 'yes' : 'no')."\n";
echo 'feeds_checked: '.(int) ($summary['feeds'] ?? 0)."\n";
echo 'inserted: '.(int) ($summary['inserted'] ?? 0)."\n";
if (!empty($summary['errors'])) {
    echo 'errors: '.implode('; ', $summary['errors'])."\n";
}
echo "done\n";

exit(!empty($summary['ok']) ? 0 : 1);
