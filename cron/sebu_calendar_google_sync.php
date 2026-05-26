<?php
/**
 * 크론 — Google Calendar iCal → 세부어때 캘린더 동기화
 *
 * CLI:
 *   php cron/sebu_calendar_google_sync.php
 *   php cron/sebu_calendar_google_sync.php --dry-run
 *
 * 웹 (calendar_sync_cron_key 설정 시):
 *   /cron/sebu_calendar_google_sync.php?key=YOUR_SECRET
 *   /cron/sebu_calendar_google_sync.php?key=YOUR_SECRET&dry_run=1
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
    $_SERVER['REQUEST_URI'] = '/cron/sebu_calendar_google_sync.php';
    $_SERVER['SCRIPT_NAME'] = '/cron/sebu_calendar_google_sync.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
}

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
include_once G5_LIB_PATH.'/eottae-calendar-google.lib.php';

if (!function_exists('g5site_cfg') && defined('G5_PATH') && is_file(G5_PATH.'/_site.config.php')) {
    include_once G5_PATH.'/_site.config.php';
}

if (!defined('_GNUBOARD_')) {
    if ($is_cli) {
        fwrite(STDERR, "GNUBoard bootstrap failed\n");
        exit(1);
    }
    http_response_code(500);
    exit("GNUBoard bootstrap failed\n");
}

if (!function_exists('eottae_calendar_verify_sync_cron_key')) {
    function eottae_calendar_verify_sync_cron_key($provided)
    {
        $provided = trim((string) $provided);
        if ($provided === '') {
            return false;
        }

        $keys = array();
        if (function_exists('g5site_cfg')) {
            $cfg = trim((string) g5site_cfg('calendar_sync_cron_key', ''));
            if ($cfg !== '') {
                $keys[] = $cfg;
            }
            $talk_key = trim((string) g5site_cfg('talkroom_ai_cron_key', ''));
            if ($talk_key !== '') {
                $keys[] = $talk_key;
            }
        }

        foreach ($keys as $key) {
            if ($key !== '' && hash_equals($key, $provided)) {
                return true;
            }
        }

        return false;
    }
}

if ($is_cli) {
    $argv = $argv ?? array();
    $dry_run = in_array('--dry-run', $argv, true);
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $provided_key = isset($_GET['key']) ? (string) $_GET['key'] : '';
    if (!eottae_calendar_verify_sync_cron_key($provided_key)) {
        http_response_code(403);
        exit("Forbidden\n");
    }
    $dry_run = !empty($_GET['dry_run']);
}

echo "=== sebu_calendar_google_sync ===\n";
echo 'time: '.G5_TIME_YMDHIS."\n";
echo 'calendar_id: '.eottae_calendar_google_calendar_id()."\n";
echo 'dry_run: '.($dry_run ? 'yes' : 'no')."\n\n";

$result = eottae_calendar_google_sync(array(
    'dry_run' => $dry_run,
));

if (empty($result['ok'])) {
    echo 'status: error'."\n";
    echo 'message: '.($result['message'] ?? 'sync failed')."\n";
    exit(1);
}

echo 'status: ok'."\n";
echo 'message: '.($result['message'] ?? 'sync complete')."\n";
echo 'fetched: '.(int) ($result['fetched_count'] ?? 0)."\n";
echo 'inserted: '.(int) ($result['inserted_count'] ?? 0)."\n";
echo 'updated: '.(int) ($result['updated_count'] ?? 0)."\n";
echo 'hidden: '.(int) ($result['hidden_count'] ?? 0)."\n";
exit(0);
