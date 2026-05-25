<?php
/**
 * CLI — 세부광장 게시판(plaza) 설치/상태 확인
 *
 * 설치: php setup/tools/eottae-plaza-install-cli.php
 * 상태: php setup/tools/eottae-plaza-install-cli.php --status
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
$_SERVER['REQUEST_URI'] = '/setup/tools/eottae-plaza-install-cli.php';
$_SERVER['SCRIPT_NAME'] = '/setup/tools/eottae-plaza-install-cli.php';

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
include_once G5_PATH.'/setup/tools/eottae-install.lib.php';

if (!defined('_GNUBOARD_')) {
    fwrite(STDERR, "GNUBoard bootstrap failed\n");
    exit(1);
}

$status_only = in_array('--status', $argv ?? array(), true);

echo "=== 세부광장(plaza) 설치 ===\n\n";

$bo_table = eottae_plaza_board_table();
$write_table = $g5['write_prefix'].$bo_table;
$board_row = sql_fetch(" SELECT bo_table, bo_subject, bo_skin FROM {$g5['board_table']} WHERE bo_table = '".sql_escape_string($bo_table)."' ", false);
$write_exists = sql_fetch(" SHOW TABLES LIKE '".sql_escape_string($write_table)."' ", false);

echo "bo_table      : {$bo_table}\n";
echo "board config  : ".(!empty($board_row['bo_table']) ? 'YES ('.$board_row['bo_subject'].')' : 'NO')."\n";
echo "write table   : ".(!empty($write_exists) ? 'YES ('.$write_table.')' : 'NO')."\n";
echo "skin          : ".(!empty($board_row['bo_skin']) ? $board_row['bo_skin'] : 'eottae-plaza')."\n\n";

if ($status_only) {
    exit(0);
}

if (!empty($board_row['bo_table']) && !empty($write_exists)) {
    echo "Already installed.\n";
} else {
    eottae_install_ensure_group('community', '커뮤니티');
    $result = eottae_install_create_board(eottae_install_plaza_board_def());
    echo "Result: ".($result['action'] ?? 'unknown')." — ".($result['message'] ?? '')."\n";
    if (empty($result['ok'])) {
        exit(1);
    }
}

include_once G5_LIB_PATH.'/eottae-plaza-likes.lib.php';
include_once G5_LIB_PATH.'/eottae-plaza-report.lib.php';
$likes_schema = eottae_plaza_likes_ensure_schema();
$reports_schema = eottae_plaza_reports_ensure_schema();
echo "likes table   : ".($likes_schema['action'] ?? 'unknown')."\n";
echo "reports table : ".($reports_schema['action'] ?? 'unknown')."\n";

include_once G5_LIB_PATH.'/eottae-plaza-ai.lib.php';
$ai_schema = eottae_plaza_ai_ensure_schema();
echo "ai settings/logs: ".(!empty($ai_schema['ok']) ? 'ok' : 'failed')."\n";
exit(0);
