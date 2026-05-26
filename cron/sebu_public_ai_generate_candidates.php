<?php
/**
 * 크론 — 홈 공개톡 AI 후보 메시지 생성 (템플릿 기반, 발행 없음)
 *
 * CLI:
 *   php cron/sebu_public_ai_generate_candidates.php
 *   php cron/sebu_public_ai_generate_candidates.php --dry-run
 *   php cron/sebu_public_ai_generate_candidates.php --test
 *
 * 웹 (public_ai_cron_key 또는 talkroom_ai_cron_key 설정 시):
 *   /cron/sebu_public_ai_generate_candidates.php?key=YOUR_SECRET
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
    $_SERVER['REQUEST_URI'] = '/cron/sebu_public_ai_generate_candidates.php';
    $_SERVER['SCRIPT_NAME'] = '/cron/sebu_public_ai_generate_candidates.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
}

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-generator.lib.php';

if (!defined('_GNUBOARD_')) {
    if ($is_cli) {
        fwrite(STDERR, "GNUBoard bootstrap failed\n");
        exit(1);
    }
    http_response_code(500);
    exit('GNUBoard bootstrap failed');
}

$dry_run = false;
$is_test = false;
$force = false;

if ($is_cli) {
    $argv = $argv ?? array();
    foreach ($argv as $arg) {
        if ($arg === '--dry-run') {
            $dry_run = true;
        }
        if ($arg === '--test') {
            $is_test = true;
            $force = true;
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
    $is_test = !empty($_GET['test']);
    $force = !empty($_GET['force']);
}

$result = eottae_public_ai_run_candidate_generator(array(
    'dry_run' => $dry_run,
    'is_test' => $is_test,
    'force'   => $force,
));

echo "=== sebu_public_ai_generate_candidates ===\n";
echo 'time: '.G5_TIME_YMDHIS."\n";
echo 'dry_run: '.($dry_run ? 'yes' : 'no')."\n";
echo 'test: '.($is_test ? 'yes' : 'no')."\n";
echo 'ok: '.(!empty($result['ok']) ? 'yes' : 'no')."\n";
echo 'reason: '.($result['reason'] ?? '')."\n";
echo 'generated: '.(int) ($result['generated_count'] ?? 0)."\n";
echo 'saved: '.(int) ($result['saved'] ?? 0)."\n";
echo 'skipped: '.(int) ($result['skipped'] ?? 0)."\n";
if (!empty($result['candidate_ids'])) {
    echo 'candidate_ids: '.implode(',', $result['candidate_ids'])."\n";
}
if (!empty($result['skip_reasons'])) {
    echo 'skip_reasons: '.implode(', ', array_unique($result['skip_reasons']))."\n";
}
echo "done\n";

exit(!empty($result['ok']) || (int) ($result['saved'] ?? 0) > 0 ? 0 : 1);
