<?php
/**
 * CLI — 샤인카세차 SHINY(shop wr_id=36) 소개 갱신 + 리뷰 39건 시드
 * php setup/tools/eottae-seed-shiny-reviews-cli.php
 * php setup/tools/eottae-seed-shiny-reviews-cli.php 36
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

$shop_wr_id = isset($argv[1]) ? (int) $argv[1] : 36;
if ($shop_wr_id < 1) {
    fwrite(STDERR, "Usage: php setup/tools/eottae-seed-shiny-reviews-cli.php [shop_wr_id]\n");
    exit(1);
}

if (!function_exists('eottae_seed_shiny_reviews')) {
    fwrite(STDERR, "eottae_seed_shiny_reviews() missing\n");
    exit(1);
}

$logs = eottae_seed_shiny_reviews($shop_wr_id);
$inserted = 0;
$skipped = 0;
$failed = 0;

foreach ($logs as $entry) {
    $prefix = !empty($entry['ok']) ? '[OK]' : '[FAIL]';
    $msg = ($entry['action'] ?? 'seed').': '.($entry['message'] ?? '');
    echo $prefix.' '.$msg.PHP_EOL;
    if (!empty($entry['ok'])) {
        if (strpos($msg, 'seeded') !== false) {
            $inserted++;
        } elseif (strpos($msg, 'exists') !== false) {
            $skipped++;
        }
    } else {
        $failed++;
    }
}

if (function_exists('eottae_get_shop_review_summary')) {
    $summary = eottae_get_shop_review_summary($shop_wr_id);
    echo 'Summary: ★ '.($summary['average'] ?? 0).' / '.($summary['count'] ?? 0).' reviews'.PHP_EOL;
}

echo "Done. inserted={$inserted} skipped={$skipped} failed={$failed}\n";
