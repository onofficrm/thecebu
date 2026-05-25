<?php
/**
 * 세부톡방 AI 도우미 — 기능·보안 정적 감사
 *
 * 사용: php setup/tools/eottae-talkroom-ai-audit-cli.php
 */
if (php_sapi_name() !== 'cli') {
    exit("CLI only\n");
}

$root = dirname(__DIR__, 2);
chdir($root);

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['REQUEST_URI'] = '/setup/tools/eottae-talkroom-ai-audit-cli.php';
$_SERVER['SCRIPT_NAME'] = '/setup/tools/eottae-talkroom-ai-audit-cli.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

define('_GNUBOARD_', true);
define('G5_PATH', $root);
define('G5_LIB_PATH', $root.'/lib');
define('G5_TABLE_PREFIX', 'g5_');
define('G5_TIME_YMD', date('Y-m-d'));
define('G5_TIME_YMDHIS', date('Y-m-d H:i:s'));
define('G5_SERVER_TIME', time());

include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-guard.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-safety.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-meetup.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-reaction.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-admin.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-summary.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-quiet.lib.php';

$checks = array();

function ai_audit($id, $label, $ok, $detail = '')
{
    global $checks;
    $checks[] = array(
        'id'     => $id,
        'label'  => $label,
        'ok'     => (bool) $ok,
        'detail' => $detail,
    );
}

function ai_audit_file_contains($path, $needles)
{
    if (!is_file($path)) {
        return false;
    }
    $content = file_get_contents($path);
    foreach ((array) $needles as $needle) {
        if (strpos($content, $needle) === false) {
            return false;
        }
    }

    return true;
}

// ── 1. 최고관리자 / 2. 방장 / 3. 일반회원 (페이지·proc 게이트) ──
$admin_pages = array(
    'page/eottae-admin-talk-ai.php',
    'page/eottae-admin-talk-ai-logs.php',
);
foreach ($admin_pages as $file) {
    ai_audit('admin:'.$file, $file.' super gate', ai_audit_file_contains($root.'/'.$file, "is_admin !== 'super'"));
}

ai_audit(
    'page:ai-settings',
    '방 AI 설정 can_view_settings',
    ai_audit_file_contains($root.'/page/eottae-talk-ai-settings.php', 'eottae_talkroom_ai_can_view_settings')
);

ai_audit(
    'proc:save-settings-edit',
    'save_settings can_edit_settings 검증',
    ai_audit_file_contains($root.'/proc/eottae-talkroom-ai-settings.php', 'eottae_talkroom_ai_can_edit_settings')
);

ai_audit(
    'proc:admin-super',
    'AI admin proc super gate',
    ai_audit_file_contains($root.'/proc/eottae-talkroom-ai-admin.php', array("is_admin !== 'super'", 'eottae_talkroom_verify_admin_token'))
);

ai_audit(
    'lib:assert-edit',
    'save_settings assert_edit_access',
    ai_audit_file_contains($root.'/lib/eottae-talkroom-ai.lib.php', 'eottae_talkroom_ai_assert_edit_access')
);

ai_audit(
    'lib:force-off',
    '강제 OFF assert 차단',
    ai_audit_file_contains($root.'/lib/eottae-talkroom-ai.lib.php', 'admin_force_disabled')
);

// ── 4~9. 트리거 가드 (DB 없이 가능한 순수 함수) ──
ai_audit(
    'guard:shared-fn',
    '공통 한도 evaluate_shared_limits 함수',
    function_exists('eottae_talkroom_ai_evaluate_shared_limits')
);

ai_audit(
    'guard:consecutive-fn',
    '연속 AI 글 차단 room_latest_post_is_ai',
    function_exists('eottae_talkroom_ai_room_latest_post_is_ai')
);

ai_audit(
    'guard:test-no-daily-inc',
    'is_test 시 daily count 증가 안 함',
    eottae_talkroom_ai_increment_daily_count(99999, G5_TIME_YMDHIS, array('is_test' => true)) === 0
);

ai_audit(
    'meetup:sports-only',
    '모임 제안 — 스포츠 카테고리 허용',
    eottae_talkroom_ai_meetup_is_eligible_category('sports')
);
ai_audit(
    'meetup:trade-block',
    '모임 제안 — 중고거래 카테고리 차단',
    !eottae_talkroom_ai_meetup_is_eligible_category('trade')
);

$sensitive = eottae_talkroom_ai_detect_sensitive_content('이 사람 사기꾼이에요 고소할게요');
ai_audit(
    'reaction:sensitive-skip',
    '민감 키워드 감지',
    !empty($sensitive['hit']),
    $sensitive['category'] ?? ''
);

$exclude_post = eottae_talkroom_ai_reaction_should_exclude_post(array(
    'wr_content' => '이 사람은 사기꾼입니다 고소하겠습니다',
    'wr_subject' => '주의',
    'wr_comment' => 0,
    'ca_name'    => '일반',
    'mb_id'      => 'user1',
    'wr_1'       => '1',
));
ai_audit(
    'reaction:exclude-sensitive',
    '민감 글 리액션 제외',
    !empty($exclude_post['exclude']),
    $exclude_post['reason'] ?? ''
);

ai_audit(
    'reaction:classify-fn',
    '리액션 classify_post_for_reaction 함수',
    function_exists('eottae_talkroom_ai_classify_post_for_reaction')
);

ai_audit(
    'reaction:hourly-limit',
    '리액션 시간당 한도 상수',
    function_exists('eottae_talkroom_ai_reaction_hourly_limit')
        && eottae_talkroom_ai_reaction_hourly_limit() === 2
);

ai_audit(
    'reaction:exclude-fn',
    '리액션 should_exclude_post 함수',
    function_exists('eottae_talkroom_ai_reaction_should_exclude_post')
);

ai_audit(
    'daily:once-per-day',
    '오늘의 질문 has_success_log_on_date 검사',
    ai_audit_file_contains($root.'/lib/eottae-talkroom-ai-daily-question.lib.php', 'has_success_log_on_date')
);

ai_audit(
    'quiet:silence-check',
    '조용한 방 min_silence_minutes 검사',
    ai_audit_file_contains($root.'/lib/eottae-talkroom-ai-quiet.lib.php', 'min_silence_minutes')
);

ai_audit(
    'summary:redact',
    '요약 개인정보 redact 함수',
    function_exists('eottae_talkroom_ai_redact_summary_text')
);

ai_audit(
    'welcome:active-only',
    '환영 should_welcome_member active 검사',
    ai_audit_file_contains($root.'/lib/eottae-talkroom-ai-welcome.lib.php', "'active'")
);

// ── 10. 보안 ──
$ai_procs = array(
    'proc/eottae-talkroom-ai-settings.php',
    'proc/eottae-talkroom-ai-admin.php',
);
foreach ($ai_procs as $file) {
    ai_audit('post:'.$file, $file.' POST-only', ai_audit_file_contains($root.'/'.$file, "REQUEST_METHOD'] !== 'POST'"));
    ai_audit('csrf:'.$file, $file.' CSRF 토큰', ai_audit_file_contains($root.'/'.$file, 'verify_') && ai_audit_file_contains($root.'/'.$file, 'token'));
}

ai_audit(
    'xss:admin-ai',
    '관리 AI 목록 get_text 출력',
    ai_audit_file_contains($root.'/page/eottae-admin-talk-ai.php', "get_text(\$item['room_name'])")
);

ai_audit(
    'xss:logs',
    'AI 로그 response_text get_text',
    ai_audit_file_contains($root.'/page/eottae-admin-talk-ai-logs.php', "get_text(\$row['response_text'])")
);

ai_audit(
    'sql:insert-escape',
    'AI insert sql_escape_string',
    ai_audit_file_contains($root.'/lib/eottae-talkroom-ai.lib.php', 'sql_escape_string($subject)')
);

ai_audit(
    'delete:ai-only',
    'AI 삭제 ai_write_row 검증',
    ai_audit_file_contains($root.'/lib/eottae-talkroom-ai-admin.lib.php', 'eottae_talkroom_ai_is_ai_write_row')
);

ai_audit(
    'test:limit',
    '테스트 작성 일일 한도',
    function_exists('eottae_talkroom_ai_assert_admin_test_write_allowed')
        && eottae_talkroom_ai_max_test_writes_per_day() === 5
);

$cron_files = glob($root.'/cron/sebu_talk_ai_*.php') ?: array();
ai_audit('cron:files', 'AI cron 스크립트 '.count($cron_files).'개', count($cron_files) >= 5, implode(', ', array_map('basename', $cron_files)));

ai_audit(
    'cron:key-guard',
    'cron key 검증 함수',
    function_exists('eottae_talkroom_ai_verify_cron_key')
);

// ── 결과 ──
$pass = 0;
$fail = 0;
echo "=== 세부톡방 AI 도우미 감사 ===\n\n";
foreach ($checks as $c) {
    $mark = $c['ok'] ? 'PASS' : 'FAIL';
    if ($c['ok']) {
        $pass++;
    } else {
        $fail++;
    }
    echo '['.$mark.'] '.$c['label'];
    if ($c['detail'] !== '') {
        echo ' — '.$c['detail'];
    }
    echo "\n";
}

echo "\n합계: PASS {$pass}, FAIL {$fail}\n";
echo "\nDB 연동 테스트: php setup/tools/eottae-talkroom-ai-install-cli.php --status\n";
echo "크론 dry-run: php setup/tools/eottae-talkroom-ai-quiet-cron-cli.php --dry-run\n";

exit($fail > 0 ? 1 : 0);
