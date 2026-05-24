<?php
/**
 * CLI — 커뮤니티 사이드바 광고 시드 (업체 + 광고 테이블)
 * php setup/tools/eottae-seed-ads-cli.php
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
include_once G5_LIB_PATH.'/eottae-ad.lib.php';

if (!function_exists('eottae_ad_seed_defaults')) {
    fwrite(STDERR, "eottae-ad.lib.php missing\n");
    exit(1);
}

eottae_ad_ensure_table();
$logs = eottae_ad_seed_defaults();

foreach ($logs as $line) {
    echo $line.PHP_EOL;
}

echo "Done.\n";
