<?php
/**
 * CLI — 승인 대기 톡방 개설 일괄 승인
 *
 *   php setup/tools/eottae-talkroom-approve-pending-cli.php
 *   php setup/tools/eottae-talkroom-approve-pending-cli.php --dry-run
 */
if (php_sapi_name() !== 'cli') {
    exit("CLI only\n");
}

$script = realpath(__DIR__.'/../../cron/sebu_talk_admin_approve_pending.php');
if (!$script || !is_file($script)) {
    fwrite(STDERR, "cron script not found\n");
    exit(1);
}

$argv = $argv ?? array();
$args = array($script);
foreach ($argv as $index => $arg) {
    if ($index === 0) {
        continue;
    }
    $args[] = $arg;
}

passthru('php '.implode(' ', array_map('escapeshellarg', $args)), $exit_code);
exit((int) $exit_code);
