<?php
/**
 * CLI — 자동댓글 플러그인 DB 설치/상태 확인
 *
 * 설치: php setup/tools/eottae-auto-comment-install-cli.php
 * 상태: php setup/tools/eottae-auto-comment-install-cli.php --status
 * 진단: php setup/tools/eottae-auto-comment-install-cli.php --diag
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
$diag_only = in_array('--diag', $argv, true);

$tables = array('setting', 'board', 'author', 'template', 'queue', 'log', 'ai_usage', 'visitor', 'post_view');

echo "Auto Comment plugin v".AUTO_COMMENT_VERSION."\n";
echo "Table prefix: ".G5_TABLE_PREFIX."\n\n";

if ($diag_only) {
    if (!auto_comment_is_installed()) {
        echo "NOT INSTALLED\n";
        exit(1);
    }

    $today = date('Y-m-d 00:00:00', G5_SERVER_TIME);
    $failure_actions = "'failed','schedule_failed','schedule_skip','strategy_skip','worker_failed','generator_fallback','ai_failed'";

    echo "enabled: ".auto_comment_get_setting('enabled', '0')."\n";
    echo "generator_mode: ".auto_comment_get_setting('generator_mode', 'ai')."\n";
    echo "auto_min_comments: ".auto_comment_get_setting('auto_min_comments', '0')."\n";
    echo "daily_limit: ".auto_comment_get_setting('daily_limit', '20')."\n";
    echo "trigger_percent: ".auto_comment_get_setting('trigger_percent', '3')."\n";
    echo "trigger_interval: ".auto_comment_get_setting('trigger_interval', '180')."s\n\n";

    foreach (array('review', 'pending', 'inserted', 'failed', 'cancelled') as $status) {
        $row = sql_fetch(" select count(*) as cnt from ".auto_comment_table('queue')." where acq_status = '".auto_comment_escape($status)."' ", false);
        echo "queue {$status}: ".(int) $row['cnt']."\n";
    }

    $due = sql_fetch(" select count(*) as cnt from ".auto_comment_table('queue')."
                       where acq_status = 'pending' and acq_scheduled_at <= '".G5_TIME_YMDHIS."' ", false);
    echo "queue pending (due now): ".(int) $due['cnt']."\n";

    $inserted_today = sql_fetch(" select count(*) as cnt from ".auto_comment_table('queue')."
                                  where acq_status = 'inserted' and acq_inserted_at >= '{$today}' ", false);
    echo "inserted today: ".(int) $inserted_today['cnt']."\n";

    $failures_today = sql_fetch(" select count(*) as cnt from ".auto_comment_table('log')."
                                   where acl_action in ({$failure_actions}) and acl_datetime >= '{$today}' ", false);
    echo "failures today: ".(int) $failures_today['cnt']."\n\n";

    echo "enabled boards:\n";
    $boards = sql_query(" select bo_table, acb_enabled, acb_auto_new_post, acb_strategy_scan, acb_midnight_schedule, acb_interval_minutes, acb_review_mode
                            from ".auto_comment_table('board')."
                           where acb_enabled = 1
                           order by bo_table asc ", false);
    while ($board = sql_fetch_array($boards)) {
        echo '  - '.$board['bo_table']
            .' new_post='.(int) $board['acb_auto_new_post']
            .' strategy='.(int) $board['acb_strategy_scan']
            .' interval='.(int) $board['acb_midnight_schedule']
            .' interval_min='.(int) $board['acb_interval_minutes']
            .' review='.(int) $board['acb_review_mode']."\n";
    }

    echo "\nrecent logs:\n";
    $logs = sql_query(" select acl_action, acl_message, acl_datetime
                          from ".auto_comment_table('log')."
                         order by acl_id desc
                         limit 12 ", false);
    while ($log = sql_fetch_array($logs)) {
        echo '  ['.$log['acl_datetime'].'] '.$log['acl_action'].': '.$log['acl_message']."\n";
    }

    $last_file = G5_DATA_PATH.'/cache/auto_comment_last_run.php';
    if (is_file($last_file)) {
        echo "\nworker last run file: ".date('Y-m-d H:i:s', filemtime($last_file))."\n";
    } else {
        echo "\nworker last run file: (none)\n";
    }

    exit(0);
}

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
