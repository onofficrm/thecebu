<?php
/**
 * CLI — SEL 아카데미(shop wr_id=8) 리뷰 23건 시드
 * php setup/tools/eottae-seed-sel-reviews-cli.php
 * php setup/tools/eottae-seed-sel-reviews-cli.php 8
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

$shop_wr_id = isset($argv[1]) ? (int) $argv[1] : 8;
if ($shop_wr_id < 1) {
    fwrite(STDERR, "Usage: php setup/tools/eottae-seed-sel-reviews-cli.php [shop_wr_id]\n");
    exit(1);
}

if (!function_exists('eottae_seed_sel_academy_reviews')) {
    fwrite(STDERR, "eottae_seed_sel_academy_reviews() missing\n");
    exit(1);
}

$logs = eottae_seed_sel_academy_reviews($shop_wr_id);
foreach ($logs as $entry) {
    $prefix = !empty($entry['ok']) ? '[OK]' : '[FAIL]';
    echo $prefix.' '.($entry['action'] ?? 'seed').': '.($entry['message'] ?? '').PHP_EOL;
}

echo "Done.\n";
