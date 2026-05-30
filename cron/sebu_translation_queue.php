<?php
/**
 * 크론 — 자동 번역 작업 큐 처리
 *
 * CLI:
 *   php cron/sebu_translation_queue.php --limit=5
 *
 * 웹:
 *   /cron/sebu_translation_queue.php?key=YOUR_SECRET&limit=5
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
    $_SERVER['REQUEST_URI'] = '/cron/sebu_translation_queue.php';
    $_SERVER['SCRIPT_NAME'] = '/cron/sebu_translation_queue.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
}

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-translation.lib.php';

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

$limit = 5;
if ($is_cli) {
    $argv = $argv ?? array();
    foreach ($argv as $arg) {
        if (strpos($arg, '--limit=') === 0) {
            $limit = (int) substr($arg, 8);
        }
    }
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $provided_key = isset($_GET['key']) ? (string) $_GET['key'] : '';
    if (!eottae_translation_verify_cron_key($provided_key)) {
        http_response_code(403);
        exit("Forbidden\n");
    }
    if (isset($_GET['limit'])) {
        $limit = (int) $_GET['limit'];
    }
}

$summary = eottae_translation_run_queue($limit);
echo 'processed='.$summary['processed'].' succeeded='.$summary['succeeded'].' failed='.$summary['failed'].PHP_EOL;
