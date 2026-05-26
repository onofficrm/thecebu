<?php
/**
 * 크론 — 승인된 공개톡 AI 후보 메시지 자동 발행
 *
 * auto_publish=1 이고 require_admin_approval=0 일 때는 후보 생성 시 즉시 발행.
 * 기본(승인 필요) 운영: approved 상태 후보만 이 크론에서 발행.
 *
 * CLI:
 *   php cron/sebu_public_ai_publish_approved.php
 *   php cron/sebu_public_ai_publish_approved.php --dry-run
 *
 * 웹:
 *   /cron/sebu_public_ai_publish_approved.php?key=YOUR_SECRET
 */
$g5_path = realpath(__DIR__.'/..');
chdir($g5_path);

$is_cli = (php_sapi_name() === 'cli');

if ($is_cli) {
    $_SERVER['SERVER_NAME'] = 'thecebu.co.kr';
    $_SERVER['HTTP_HOST'] = 'thecebu.co.kr';
    $_SERVER['SERVER_PORT'] = '443';
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['REQUEST_URI'] = '/cron/sebu_public_ai_publish_approved.php';
    $_SERVER['SCRIPT_NAME'] = '/cron/sebu_public_ai_publish_approved.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
}

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-publish.lib.php';

if (!defined('_GNUBOARD_')) {
    if ($is_cli) {
        fwrite(STDERR, "GNUBoard bootstrap failed\n");
        exit(1);
    }
    http_response_code(500);
    exit('GNUBoard bootstrap failed');
}

$dry_run = false;
$force = false;

if ($is_cli) {
    $argv = $argv ?? array();
    foreach ($argv as $arg) {
        if ($arg === '--dry-run') {
            $dry_run = true;
        }
        if ($arg === '--force') {
            $force = true;
        }
    }
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $provided_key = isset($_GET['key']) ? (string) $_GET['key'] : '';
    if (!eottae_public_ai_verify_cron_key($provided_key)) {
        http_response_code(403);
        exit("Forbidden\n");
    }
    $dry_run = !empty($_GET['dry_run']);
    $force = !empty($_GET['force']);
}

$result = eottae_public_ai_run_publish_approved(array(
    'dry_run' => $dry_run,
    'force'   => $force,
));

echo "=== sebu_public_ai_publish_approved ===\n";
echo 'time: '.G5_TIME_YMDHIS."\n";
echo 'dry_run: '.($dry_run ? 'yes' : 'no')."\n";
echo 'ok: '.(!empty($result['ok']) ? 'yes' : 'no')."\n";
echo 'reason: '.($result['reason'] ?? '')."\n";
echo 'published: '.(int) ($result['published'] ?? 0)."\n";
echo 'skipped: '.(int) ($result['skipped'] ?? 0)."\n";
if (!empty($result['candidate_ids'])) {
    echo 'candidate_ids: '.implode(',', $result['candidate_ids'])."\n";
}
if (!empty($result['wr_ids'])) {
    echo 'wr_ids: '.implode(',', $result['wr_ids'])."\n";
}
echo "done\n";

exit((int) ($result['published'] ?? 0) > 0 ? 0 : 1);
