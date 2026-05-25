<?php
/**
 * CLI — 세부톡방 DB 테이블 설치/상태 확인
 *
 * 설치:   php setup/tools/eottae-talkroom-install-cli.php
 * 상태:   php setup/tools/eottae-talkroom-install-cli.php --status
 * 롤백:   php setup/tools/eottae-talkroom-install-cli.php --rollback --confirm
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
$_SERVER['REQUEST_URI'] = '/setup/tools/eottae-talkroom-install-cli.php';
$_SERVER['SCRIPT_NAME'] = '/setup/tools/eottae-talkroom-install-cli.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

if (!defined('_GNUBOARD_')) {
    fwrite(STDERR, "GNUBoard bootstrap failed\n");
    exit(1);
}

$argv = $argv ?? array();
$status_only = in_array('--status', $argv, true);
$rollback = in_array('--rollback', $argv, true);
$confirm = in_array('--confirm', $argv, true);

echo "Table prefix: ".G5_TABLE_PREFIX."\n\n";

if ($status_only) {
    foreach (eottae_talkroom_schema_status() as $key => $row) {
        $flag = !empty($row['exists']) ? 'YES' : 'NO';
        echo sprintf("[%s] %s (%s)\n", $flag, $row['table'], $key);
    }
    exit(0);
}

if ($rollback) {
    if (!$confirm) {
        fwrite(STDERR, "Rollback requires --confirm. Example:\n");
        fwrite(STDERR, "  php setup/tools/eottae-talkroom-install-cli.php --rollback --confirm\n");
        exit(1);
    }

    echo "Rolling back sebu_talk tables...\n";
    foreach (eottae_talkroom_drop_schema() as $entry) {
        $status = !empty($entry['ok']) ? 'OK' : 'FAIL';
        echo sprintf("[%s] %s (%s) %s\n", $status, $entry['table'], $entry['key'], $entry['action']);
    }
    exit(0);
}

echo "Installing sebu_talk tables (CREATE TABLE IF NOT EXISTS)...\n";
foreach (eottae_talkroom_ensure_schema() as $entry) {
    $status = !empty($entry['ok']) ? 'OK' : 'FAIL';
    echo sprintf("[%s] %s (%s) %s\n", $status, $entry['table'], $entry['key'], $entry['action']);
}

echo "\nDone.\n";
