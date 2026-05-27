<?php
/**
 * CLI — 럭키풀빌라(shop wr_id=62) 리뷰 35건 시드
 * php setup/tools/eottae-seed-luckyvilla-reviews-cli.php
 * php setup/tools/eottae-seed-luckyvilla-reviews-cli.php 62 --force
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

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae.lib.php';
include_once __DIR__.'/eottae-seed.lib.php';

$shop_wr_id = isset($argv[1]) ? (int) $argv[1] : 62;
$force = in_array('--force', $argv, true) || in_array('-f', $argv, true);

if ($shop_wr_id < 1) {
    fwrite(STDERR, "Usage: php setup/tools/eottae-seed-luckyvilla-reviews-cli.php [shop_wr_id] [--force]\n");
    exit(1);
}

if (!function_exists('eottae_seed_luckyvilla_reviews')) {
    fwrite(STDERR, "eottae_seed_luckyvilla_reviews() missing\n");
    exit(1);
}

$logs = eottae_seed_luckyvilla_reviews($shop_wr_id, $force);
foreach ($logs as $entry) {
    $prefix = !empty($entry['ok']) ? '[OK]' : '[FAIL]';
    echo $prefix.' '.($entry['action'] ?? 'seed').': '.($entry['message'] ?? '').PHP_EOL;
}

if (function_exists('eottae_get_shop_review_summary')) {
    $summary = eottae_get_shop_review_summary($shop_wr_id);
    echo 'Summary: ★ '.$summary['average'].' / '.$summary['count']." reviews\n";
}

echo "Done.\n";
