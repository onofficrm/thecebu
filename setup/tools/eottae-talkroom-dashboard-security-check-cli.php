<?php
/**
 * 마이페이지 내 세부톡방 대시보드 — 보안·권한 정적 점검
 *
 * 사용: php setup/tools/eottae-talkroom-dashboard-security-check-cli.php
 */
if (php_sapi_name() !== 'cli') {
    exit("CLI only\n");
}

$root = dirname(__DIR__, 2);
chdir($root);

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['REQUEST_URI'] = '/setup/tools/eottae-talkroom-dashboard-security-check-cli.php';
$_SERVER['SCRIPT_NAME'] = '/setup/tools/eottae-talkroom-dashboard-security-check-cli.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

define('_GNUBOARD_', true);
define('G5_PATH', $root);
define('G5_LIB_PATH', $root.'/lib');
define('G5_TABLE_PREFIX', 'g5_');

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-dashboard.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-bookmarks.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-notify.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-reads.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-briefing.lib.php';

$checks = array();

function dash_sec_check($label, $ok, $detail = '')
{
    global $checks;
    $checks[] = array('label' => $label, 'ok' => (bool) $ok, 'detail' => $detail);
}

$required_functions = array(
    'eottae_talkroom_dashboard_post_view_allowed',
    'eottae_talkroom_dashboard_feed_room_ids_from_my',
    'eottae_talkroom_dashboard_build_context',
    'eottae_talkroom_bookmark_can_access_post',
    'eottae_talkroom_notify_get_owned',
    'eottae_talkroom_reads_can_mark',
    'eottae_talkroom_sanitize_internal_href',
    'collect_my_talk_briefing_data',
);

foreach ($required_functions as $fn) {
    dash_sec_check('함수: '.$fn, function_exists($fn));
}

$page_talk = file_get_contents($root.'/page/eottae-mypage-talk.php');
dash_sec_check('페이지 로그인 게이트', strpos($page_talk, 'if (!$is_member)') !== false);

$dashboard_lib = file_get_contents(G5_LIB_PATH.'/eottae-talkroom-dashboard.lib.php');
dash_sec_check('피드 room_id 화이트리스트', strpos($dashboard_lib, '!in_array($filter_room_id, $room_ids') !== false);
dash_sec_check('피드 행 열람 재검증', strpos($dashboard_lib, 'eottae_talkroom_dashboard_post_view_allowed') !== false);
dash_sec_check('방장 요약 can_manage_room', strpos($dashboard_lib, 'eottae_talkroom_can_manage_room') !== false);

$bookmarks_lib = file_get_contents(G5_LIB_PATH.'/eottae-talkroom-bookmarks.lib.php');
dash_sec_check('저장글 mb_id 스코프', strpos($bookmarks_lib, "WHERE b.mb_id = '{\$mb_sql}'") !== false);
dash_sec_check('저장글 접근 제한 메타 마스킹', strpos($bookmarks_lib, '접근 제한된 글입니다') !== false);

$notify_lib = file_get_contents(G5_LIB_PATH.'/eottae-talkroom-notify.lib.php');
dash_sec_check('알림 소유권 조회', strpos($notify_lib, 'eottae_talkroom_notify_get_owned') !== false);
dash_sec_check('알림 href sanitize', strpos($notify_lib, 'eottae_talkroom_sanitize_internal_href') !== false);

$reads_lib = file_get_contents(G5_LIB_PATH.'/eottae-talkroom-reads.lib.php');
dash_sec_check('읽음 처리 owner_mb_id 허용', strpos($reads_lib, "owner_mb_id") !== false && strpos($reads_lib, 'reads_can_mark') !== false);

$talkroom_lib = file_get_contents(G5_LIB_PATH.'/eottae-talkroom.lib.php');
dash_sec_check('공동방장 joined 포함', strpos($talkroom_lib, "member_role === 'owner'") !== false);

$component = file_get_contents($root.'/components/eottae/talk-mypage-dashboard.php');
dash_sec_check('알림 href htmlspecialchars', strpos($component, 'htmlspecialchars($href') !== false);

$post_procs = array(
    'proc/eottae-talkroom-dashboard-feed.php',
    'proc/eottae-talkroom-dashboard-notices.php',
    'proc/eottae-talkroom-reads.php',
    'proc/eottae-talkroom-notifications.php',
    'proc/eottae-talkroom-bookmarks.php',
);

foreach ($post_procs as $file) {
    $path = $root.'/'.$file;
    $content = is_file($path) ? file_get_contents($path) : '';
    dash_sec_check($file.' POST-only', is_file($path) && strpos($content, "REQUEST_METHOD'] !== 'POST'") !== false);
    dash_sec_check($file.' CSRF 토큰', is_file($path) && strpos($content, 'verify_member_token') !== false);
    dash_sec_check($file.' 세션 mb_id', is_file($path) && strpos($content, "\$member['mb_id']") !== false);
}

$notify_proc = file_get_contents($root.'/proc/eottae-talkroom-notifications.php');
dash_sec_check('알림 mark_read IDOR 방어', strpos($notify_proc, 'notify_get_owned') !== false);

$reads_proc = file_get_contents($root.'/proc/eottae-talkroom-reads.php');
dash_sec_check('읽음 mark_all active room_ids', strpos($reads_proc, 'feed_room_ids_from_my') !== false);

// href sanitize unit checks
if (function_exists('eottae_talkroom_sanitize_internal_href')) {
    dash_sec_check('href: javascript 차단', eottae_talkroom_sanitize_internal_href('javascript:alert(1)', '#') === '#');
    dash_sec_check('href: 상대경로 허용', eottae_talkroom_sanitize_internal_href('/page/test.php', '#') === '/page/test.php');
}

// feed room_id whitelist — 정적 패턴 확인 (DB 없이)
dash_sec_check('피드 room_id 화이트리스트 로직', strpos($dashboard_lib, '!in_array($filter_room_id, $room_ids') !== false);

$pass = 0;
$fail = 0;
echo "=== 내 세부톡방 대시보드 보안·권한 점검 ===\n\n";
foreach ($checks as $c) {
    $mark = $c['ok'] ? 'PASS' : 'FAIL';
    if ($c['ok']) {
        $pass++;
    } else {
        $fail++;
    }
    echo '['.$mark.'] '.$c['label'];
    if (!empty($c['detail'])) {
        echo ' — '.$c['detail'];
    }
    echo "\n";
}

echo "\n합계: PASS {$pass}, FAIL {$fail}\n";
exit($fail > 0 ? 1 : 0);
