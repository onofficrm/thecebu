<?php
/**
 * CLI — 세부어때 캘린더 DB 테이블 설치/상태 확인
 *
 * 설치: php setup/tools/eottae-calendar-install-cli.php
 * 상태: php setup/tools/eottae-calendar-install-cli.php --status
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
$_SERVER['REQUEST_URI'] = '/setup/tools/eottae-calendar-install-cli.php';
$_SERVER['SCRIPT_NAME'] = '/setup/tools/eottae-calendar-install-cli.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-calendar.lib.php';

if (!defined('_GNUBOARD_')) {
    fwrite(STDERR, "GNUBoard bootstrap failed\n");
    exit(1);
}

$argv = $argv ?? array();
$status_only = in_array('--status', $argv, true);

echo "Table prefix: ".G5_TABLE_PREFIX."\n\n";

if ($status_only) {
    foreach (eottae_calendar_schema_status() as $key => $row) {
        $flag = !empty($row['exists']) ? 'YES' : 'NO';
        echo sprintf("[%s] %s (%s)\n", $flag, $row['table'], $key);
    }
    exit(0);
}

$results = eottae_calendar_ensure_schema();
foreach ($results as $row) {
    $flag = !empty($row['ok']) ? 'OK' : 'FAIL';
    echo sprintf("[%s] %s\n", $flag, $row['table'] ?? '');
}

echo "\nDone.\n";
