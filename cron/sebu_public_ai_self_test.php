<?php
/**
 * 공개톡 AI 자체 점검 (최고관리자/CLI)
 *
 * php cron/sebu_public_ai_self_test.php
 */
$g5_path = realpath(__DIR__.'/..');
chdir($g5_path);

$is_cli = (php_sapi_name() === 'cli');
if ($is_cli) {
    $_SERVER['SERVER_NAME'] = 'thecebu.co.kr';
    $_SERVER['HTTP_HOST'] = 'thecebu.co.kr';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}

include_once $g5_path.'/common.php';
include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-generator.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-publish.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-guard.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-weather.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-news.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-news-feed.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-poll.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-openai.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-schedule.lib.php';

if (!defined('_GNUBOARD_')) {
    fwrite(STDERR, "bootstrap failed\n");
    exit(1);
}

$pass = 0;
$fail = 0;

function public_ai_test_line($name, $ok, $detail = '')
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo "[PASS] {$name}\n";
    } else {
        $fail++;
        echo "[FAIL] {$name}".($detail !== '' ? " — {$detail}" : '')."\n";
    }
}

eottae_public_ai_ensure_schema();

public_ai_test_line('schema_settings', eottae_talkroom_table_exists(eottae_public_ai_settings_table()));
public_ai_test_line('schema_candidates', eottae_talkroom_table_exists(eottae_public_ai_candidates_table()));
public_ai_test_line('schema_weather', eottae_talkroom_table_exists(eottae_public_ai_weather_table()));
public_ai_test_line('schema_external_news', eottae_talkroom_table_exists(eottae_public_ai_external_news_table()));
public_ai_test_line('schema_news_feeds', eottae_talkroom_table_exists(eottae_public_ai_news_feed_table()));
public_ai_test_line('fn_news_feed_parse', function_exists('eottae_public_ai_news_feed_parse_xml'));
public_ai_test_line('schema_openai_logs', eottae_talkroom_table_exists(eottae_public_ai_openai_logs_table()));

$rss_sample = '<?xml version="1.0"?><rss version="2.0"><channel><item><title>Test</title><link>https://example.com/a</link><description>Hello</description><guid>1</guid></item></channel></rss>';
$rss_parsed = eottae_public_ai_news_feed_parse_xml($rss_sample);
public_ai_test_line('rss_parse_sample', !empty($rss_parsed['ok']) && count($rss_parsed['items']) === 1);
public_ai_test_line('fn_build_public_ai_prompt', function_exists('build_public_ai_prompt'));
public_ai_test_line('fn_generate_public_ai_message', function_exists('generate_public_ai_message'));

$scan = eottae_public_ai_guard_scan_text('정치 선거 관련 이야기');
public_ai_test_line('guard_sensitive_politics', !empty($scan['is_sensitive']));

$scan_ok = eottae_public_ai_guard_scan_text('맛집 추천 부탁해요');
public_ai_test_line('guard_safe_message', empty($scan_ok['is_sensitive']));

$blocked = eottae_public_ai_guard_news_is_blocked(array(
    'title' => '살인 사건',
    'summary' => '지역 뉴스',
    'category' => 'crime',
    'is_sensitive' => 0,
));
public_ai_test_line('guard_news_blocked', $blocked);

$poll = eottae_public_ai_poll_append_to_message('내일 계획은?', eottae_public_ai_poll_encode_options(array('리조트', '맛집')));
public_ai_test_line('poll_append', strpos($poll, '1. 리조트') !== false);

$sources = eottae_public_ai_collect_sources(eottae_public_ai_get_settings());
public_ai_test_line('collect_sources', is_array($sources) && isset($sources['calendar']));

$candidates = eottae_public_ai_generate_candidates($sources);
public_ai_test_line('generate_candidates_array', is_array($candidates));

$ext = array(
    'trigger_type' => 'external_news',
    'source_type'  => 'external_news',
    'source_id'    => 1,
    'message'      => '테스트 외부뉴스 후보',
    'force_admin_approval' => 1,
);
public_ai_test_line('guard_external_no_auto', !eottae_public_ai_guard_can_auto_publish($ext));

$dry = eottae_public_ai_run_candidate_generator(array('dry_run' => true, 'is_test' => true, 'force' => true));
public_ai_test_line('generator_dry_run', is_array($dry));

$elig = eottae_public_ai_evaluate_publish_eligibility(array(
    'status' => 'pending',
    'trigger_type' => 'external_news',
    'message' => '공식 안내 확인',
    'force_admin_approval' => 1,
), eottae_public_ai_get_settings(), array());
public_ai_test_line('publish_block_external_cron', empty($elig['ok']) && ($elig['reason'] ?? '') === 'external_news_admin_only');

$slots = eottae_public_ai_publish_slots();
public_ai_test_line('schedule_slots_defined', count($slots) === 4);
public_ai_test_line('schedule_detect_morning', eottae_public_ai_detect_publish_slot('2026-05-27 07:30:00') === 'morning');
public_ai_test_line('schedule_detect_noon', eottae_public_ai_detect_publish_slot('2026-05-27 12:15:00') === 'noon');
public_ai_test_line('schedule_detect_midnight', eottae_public_ai_detect_publish_slot('2026-05-27 23:45:00') === 'midnight');

$fallback = eottae_public_ai_build_slot_fallback_candidate('noon', $sources);
public_ai_test_line('schedule_fallback_message', is_array($fallback) && strpos($fallback['message'] ?? '', '세부어때') !== false);

$health = eottae_public_ai_schedule_health_check();
public_ai_test_line('schedule_health_check', is_array($health) && array_key_exists('ai_enabled', $health));

$monitor = eottae_public_ai_run_health_monitor(array('notify' => false));
public_ai_test_line('schedule_health_monitor', is_array($monitor) && isset($monitor['issues']));

$traffic_dry = eottae_public_ai_maybe_run_traffic_slot_broadcast(array(
    'source'  => 'self_test',
    'dry_run' => true,
    'now'     => '2026-05-27 12:30:00',
));
public_ai_test_line('traffic_tick_dry_run', !empty($traffic_dry['ran']) && ($traffic_dry['slot'] ?? '') === 'noon');

$slot_dry = eottae_public_ai_run_slot_broadcast(array(
    'dry_run' => true,
    'force'   => true,
    'slot'    => 'noon',
    'now'     => '2026-05-27 12:30:00',
));
public_ai_test_line('schedule_slot_dry_run', !empty($slot_dry['published']) || ($slot_dry['reason'] ?? '') === 'dry_run');

echo "\n=== summary: pass={$pass} fail={$fail} ===\n";
exit($fail > 0 ? 1 : 0);
