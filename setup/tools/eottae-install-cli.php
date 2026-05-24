<?php
/**
 * CLI 전용 — 세부어때 설치 (로컬에서 운영 DB 연결 시)
 * php setup/tools/eottae-install-cli.php
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
$_SERVER['REQUEST_URI'] = '/setup/tools/eottae-install-cli.php';
$_SERVER['SCRIPT_NAME'] = '/setup/tools/eottae-install-cli.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

include_once $g5_path.'/common.php';
include_once __DIR__.'/eottae-install.lib.php';

if (!defined('_GNUBOARD_')) {
    fwrite(STDERR, "GNUBoard bootstrap failed\n");
    exit(1);
}

$update_only = in_array('--update', $argv ?? array(), true);
$logs = $update_only ? eottae_install_update_existing_boards() : eottae_install_run();

foreach ($logs as $entry) {
    $status = $entry['ok'] ? 'OK' : 'FAIL';
    echo sprintf("[%s] %s: %s\n", $status, $entry['action'], $entry['message']);
}

echo "\nDone.\n";
