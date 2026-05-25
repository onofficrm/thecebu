<?php
/**
 * 세부톡방 보안·권한 점검 (정적)
 *
 * 사용: php setup/tools/eottae-talkroom-security-check-cli.php
 */
if (php_sapi_name() !== 'cli') {
    exit("CLI only\n");
}

$root = dirname(__DIR__, 2);
chdir($root);

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['REQUEST_URI'] = '/setup/tools/eottae-talkroom-security-check-cli.php';
$_SERVER['SCRIPT_NAME'] = '/setup/tools/eottae-talkroom-security-check-cli.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

define('_GNUBOARD_', true);
define('G5_PATH', $root);
define('G5_LIB_PATH', $root.'/lib');
define('G5_TABLE_PREFIX', 'g5_');

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

$checks = array();

function talkroom_sec_check($id, $label, $ok, $detail = '')
{
    global $checks;
    $checks[] = array(
        'id'     => $id,
        'label'  => $label,
        'ok'     => (bool) $ok,
        'detail' => $detail,
    );
}

$required_functions = array(
    'eottae_talkroom_can_view_posts',
    'eottae_talkroom_can_write_posts',
    'eottae_talkroom_can_manage_room',
    'eottae_talkroom_assert_manage_access',
    'eottae_talkroom_assert_talkroom_member_action',
    'eottae_talkroom_assert_write_update_access',
    'eottae_talkroom_assert_comment_update_access',
    'eottae_talkroom_guard_board_view',
    'eottae_talkroom_guard_board_list_access',
    'eottae_talkroom_user_can_delete_write',
    'eottae_talkroom_user_can_edit_write',
    'eottae_talkroom_can_submit_report',
    'eottae_talkroom_can_kick_target',
    'eottae_talkroom_verify_apply_token',
    'eottae_talkroom_verify_owner_token',
    'eottae_talkroom_verify_member_token',
    'eottae_talkroom_verify_admin_token',
    'eottae_talkroom_verify_report_token',
);

foreach ($required_functions as $fn) {
    talkroom_sec_check('fn:'.$fn, '함수 존재: '.$fn, function_exists($fn));
}

$proc_files = array(
    'proc/eottae-talkroom-apply.php',
    'proc/eottae-talkroom-admin.php',
    'proc/eottae-talkroom-owner.php',
    'proc/eottae-talkroom-member.php',
    'proc/eottae-talkroom-report.php',
    'proc/eottae-talkroom-ai-settings.php',
    'proc/eottae-talkroom-ai-admin.php',
);

foreach ($proc_files as $file) {
    $path = $root.'/'.$file;
    $content = is_file($path) ? file_get_contents($path) : '';
    talkroom_sec_check('file:'.$file, 'proc 파일: '.$file, is_file($path));
    talkroom_sec_check('post:'.$file, $file.' POST-only', strpos($content, "REQUEST_METHOD'] !== 'POST'") !== false || strpos($content, 'REQUEST_METHOD") !== "POST"') !== false);
    talkroom_sec_check('token:'.$file, $file.' CSRF 토큰 검증', strpos($content, 'verify_') !== false && strpos($content, 'token') !== false);
}

$admin_pages = glob($root.'/page/eottae-admin-talk-*.php') ?: array();
foreach ($admin_pages as $path) {
    $base = basename($path);
    $content = file_get_contents($path);
    talkroom_sec_check('admin:'.$base, $base.' super admin gate', strpos($content, "is_admin !== 'super'") !== false);
}

$page_manage = file_get_contents($root.'/page/eottae-talk-manage.php');
talkroom_sec_check('page:manage', '방장 관리 can_manage_room', strpos($page_manage, 'eottae_talkroom_can_manage_room') !== false);

$page_ai_settings = @file_get_contents($root.'/page/eottae-talk-ai-settings.php');
if ($page_ai_settings !== false) {
    talkroom_sec_check('page:ai-settings', 'AI 설정 can_view_settings', strpos($page_ai_settings, 'eottae_talkroom_ai_can_view_settings') !== false);
    talkroom_sec_check('page:ai-settings-edit', 'AI 설정 proc can_edit_settings', strpos(@file_get_contents($root.'/proc/eottae-talkroom-ai-settings.php'), 'eottae_talkroom_ai_can_edit_settings') !== false);
}

$page_reports = file_get_contents($root.'/page/eottae-talk-reports.php');
talkroom_sec_check('page:reports', '신고 관리 can_manage_room', strpos($page_reports, 'eottae_talkroom_can_manage_room') !== false);

$lib = file_get_contents(G5_LIB_PATH.'/eottae-talkroom.lib.php');
talkroom_sec_check('fix:guest-private', '비회원 비공개 글 차단 (guest login alert)', strpos($lib, '로그인 후 참여자만 열람') !== false);
talkroom_sec_check('fix:board-list', '톡방 게시판 목록 차단', strpos($lib, 'eottae_talkroom_guard_board_list_access') !== false);
talkroom_sec_check('fix:home-feed-public', '홈 피드 public only', strpos($lib, "r.visibility = 'public'") !== false);

$schema = array();
talkroom_sec_check('schema:skip', 'DB 스키마 (수동 확인)', true, '서버: php setup/tools/eottae-talkroom-install-cli.php --status');

$pass = 0;
$fail = 0;
echo "=== 세부톡방 보안·권한 점검 ===\n\n";
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
exit($fail > 0 ? 1 : 0);
