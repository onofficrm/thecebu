<?php
/**
 * CLI — 자동댓글 플러그인 DB 설치/상태 확인
 *
 * 설치: php setup/tools/eottae-auto-comment-install-cli.php
 * 상태: php setup/tools/eottae-auto-comment-install-cli.php --status
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
$_SERVER['REQUEST_URI'] = '/setup/tools/eottae-auto-comment-install-cli.php';
$_SERVER['SCRIPT_NAME'] = '/setup/tools/eottae-auto-comment-install-cli.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

include_once $g5_path.'/common.php';
include_once G5_PLUGIN_PATH.'/auto_comment/auto_comment.lib.php';

if (!defined('_GNUBOARD_')) {
    fwrite(STDERR, "GNUBoard bootstrap failed\n");
    exit(1);
}

$argv = $argv ?? array();
$status_only = in_array('--status', $argv, true);

$tables = array('setting', 'board', 'author', 'template', 'queue', 'log', 'ai_usage', 'visitor', 'post_view');

echo "Auto Comment plugin v".AUTO_COMMENT_VERSION."\n";
echo "Table prefix: ".G5_TABLE_PREFIX."\n\n";

if ($status_only) {
    foreach ($tables as $name) {
        $table = auto_comment_table($name);
        $exists = auto_comment_table_exists($table, true) ? 'YES' : 'NO';
        echo sprintf("[%s] %s\n", $exists, $table);
    }
    echo "\nenabled: ".auto_comment_get_setting('enabled', '0')."\n";
    echo "installed: ".(auto_comment_is_installed() ? 'yes' : 'no')."\n";
    exit(0);
}

if (!is_file(G5_PATH.'/extend/auto_comment.extend.php')) {
    fwrite(STDERR, "Missing extend/auto_comment.extend.php\n");
    exit(1);
}

if (auto_comment_is_installed()) {
    echo "Already installed — running update...\n";
    auto_comment_update();
} else {
    echo "Running install...\n";
    auto_comment_install();
}

foreach ($tables as $name) {
    $table = auto_comment_table($name);
    $exists = auto_comment_table_exists($table, true) ? 'OK' : 'FAIL';
    echo sprintf("[%s] %s\n", $exists, $table);
}

echo "\nModule enabled: ".auto_comment_get_setting('enabled', '0')." (0 = OFF, safe default)\n";
echo "Admin: ".G5_PLUGIN_URL."/auto_comment/admin/index.php\n";
echo "Done.\n";
