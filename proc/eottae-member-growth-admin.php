<?php
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_member_growth_admin_json($success, $message, $extra = array())
{
    echo json_encode(array_merge(array('success' => (bool) $success, 'message' => (string) $message), is_array($extra) ? $extra : array()), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $is_admin !== 'super' || empty($member['mb_id'])) {
    eottae_member_growth_admin_json(false, '권한이 없습니다.');
}

$token = isset($_POST['eottae_talkroom_admin_token']) ? trim((string) $_POST['eottae_talkroom_admin_token']) : '';
if (!eottae_talkroom_verify_admin_token($token)) {
    eottae_member_growth_admin_json(false, '보안 토큰이 만료되었습니다.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

if ($action === 'grant_badge') {
    $target_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($_POST['target_mb_id'] ?? ''));
    $badge_id = (int) ($_POST['badge_id'] ?? 0);
    $reason = trim(strip_tags((string) ($_POST['reason'] ?? '')));
    $result = eottae_member_growth_award_badge($target_mb_id, $badge_id, $member['mb_id'], $reason);
    eottae_talkroom_admin_token(true);
    eottae_member_growth_admin_json(!empty($result['ok']), !empty($result['duplicate']) ? '이미 보유한 뱃지입니다.' : '뱃지를 지급했습니다.');
}

if ($action === 'grant_score') {
    $target_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($_POST['target_mb_id'] ?? ''));
    $score = (int) ($_POST['score'] ?? 0);
    $memo = trim(strip_tags((string) ($_POST['memo'] ?? '관리자 지급')));
    if ($target_mb_id === '' || $score === 0) {
        eottae_member_growth_admin_json(false, '회원과 점수를 확인해 주세요.');
    }
    $result = eottae_member_growth_add_score($target_mb_id, 'admin_grant', $score, 'admin', 0, $memo);
    eottae_talkroom_admin_token(true);
    eottae_member_growth_admin_json(!empty($result['ok']), !empty($result['ok']) ? '점수를 반영했습니다.' : '반영하지 못했습니다. (일일 한도 등)');
}

if ($action === 'save_featured') {
    $result = eottae_member_growth_save_featured(array(
        'mb_id'             => $_POST['mb_id'] ?? '',
        'week_key'          => $_POST['week_key'] ?? '',
        'intro_text'        => $_POST['intro_text'] ?? '',
        'reason'            => $_POST['reason'] ?? '',
        'activity_summary'  => $_POST['activity_summary'] ?? '',
        'show_on_main'      => !empty($_POST['show_on_main']),
        'sort_order'        => (int) ($_POST['sort_order'] ?? 0),
    ), $member['mb_id']);
    eottae_talkroom_admin_token(true);
    eottae_member_growth_admin_json(!empty($result['ok']), !empty($result['ok']) ? '우수회원을 등록했습니다.' : ($result['message'] ?? '저장 실패'));
}

if ($action === 'delete_featured') {
    $featured_id = (int) ($_POST['featured_id'] ?? 0);
    eottae_member_growth_delete_featured($featured_id);
    eottae_talkroom_admin_token(true);
    eottae_member_growth_admin_json(true, '삭제했습니다.');
}

if ($action === 'save_member_prefs') {
    $target_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($_POST['target_mb_id'] ?? ''));
    if ($target_mb_id === '') {
        eottae_member_growth_admin_json(false, '회원 ID를 입력해 주세요.');
    }
    eottae_member_growth_save_member_prefs($target_mb_id, array(
        'exclude_ranking' => !empty($_POST['exclude_ranking']),
        'mask_nickname'   => !empty($_POST['mask_nickname']),
    ));
    eottae_talkroom_admin_token(true);
    eottae_member_growth_admin_json(true, '회원 설정을 저장했습니다.');
}

if ($action === 'badge_settings') {
    $badge_id = (int) ($_POST['badge_id'] ?? 0);
    $target_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($_POST['target_mb_id'] ?? ''));
    if ($badge_id > 0) {
        eottae_member_growth_set_badge_show_on_main($badge_id, !empty($_POST['show_on_main']));
    }
    if ($target_mb_id !== '' && $badge_id > 0 && !empty($_POST['hide_member_badge'])) {
        eottae_member_growth_set_member_badge_hidden($target_mb_id, $badge_id, true);
    }
    eottae_talkroom_admin_token(true);
    eottae_member_growth_admin_json(true, '뱃지 설정을 저장했습니다.');
}

if ($action === 'auto_featured') {
    $force = !empty($_POST['force']);
    $limit = (int) ($_POST['limit'] ?? 1);
    $result = eottae_member_growth_auto_apply_featured($member['mb_id'], $limit, $force);
    eottae_talkroom_admin_token(true);
    eottae_member_growth_admin_json(!empty($result['ok']), $result['message'] ?? '처리했습니다.', $result);
}

if ($action === 'snapshot_rankings') {
    $week_key = trim((string) ($_POST['week_key'] ?? ''));
    $result = eottae_member_growth_snapshot_week_rankings($week_key, 'week');
    eottae_talkroom_admin_token(true);
    $msg = !empty($result['skipped']) ? ($result['message'] ?? '이미 저장됨') : '주간 랭킹 '.(int) ($result['count'] ?? 0).'명을 저장했습니다.';
    eottae_member_growth_admin_json(!empty($result['ok']), $msg, $result);
}

if ($action === 'save_level') {
    $result = eottae_member_growth_save_level($_POST);
    eottae_talkroom_admin_token(true);
    eottae_member_growth_admin_json(!empty($result['ok']), !empty($result['ok']) ? '등급을 저장했습니다.' : ($result['message'] ?? '저장 실패'));
}

if ($action === 'save_badge_def') {
    $input = $_POST;
    $input['is_auto'] = !empty($_POST['is_auto']);
    $input['show_on_main'] = !empty($_POST['show_on_main']);
    $input['is_active'] = !empty($_POST['is_active']);
    $result = eottae_member_growth_save_badge($input);
    eottae_talkroom_admin_token(true);
    eottae_member_growth_admin_json(!empty($result['ok']), !empty($result['ok']) ? '뱃지를 저장했습니다.' : ($result['message'] ?? '저장 실패'));
}

if ($action === 'recalc_all_levels') {
    $result = eottae_member_growth_recalc_all_levels(500);
    eottae_talkroom_admin_token(true);
    eottae_member_growth_admin_json(true, '등급을 '.$result['count'].'명 기준으로 재계산했습니다.', $result);
}

if ($action === 'run_weekly_cron') {
    $result = eottae_member_growth_run_weekly_cron(array(
        'force_featured' => !empty($_POST['force_featured']),
        'featured_limit' => (int) ($_POST['featured_limit'] ?? 3),
        'admin_mb_id'    => $member['mb_id'],
    ));
    eottae_talkroom_admin_token(true);
    eottae_member_growth_admin_json(!empty($result['ok']), '주간 크론 작업을 실행했습니다.', $result);
}

if ($action === 'revoke_badge') {
    $target_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($_POST['target_mb_id'] ?? ''));
    $badge_id = (int) ($_POST['badge_id'] ?? 0);
    $table = eottae_member_growth_member_badges_table();
    sql_query(" DELETE FROM `{$table}` WHERE mb_id = '".sql_escape_string($target_mb_id)."' AND badge_id = '{$badge_id}' ", false);
    eottae_member_growth_clear_cache($target_mb_id);
    eottae_talkroom_admin_token(true);
    eottae_member_growth_admin_json(true, '뱃지를 회수했습니다.');
}

eottae_member_growth_admin_json(false, '지원하지 않는 요청입니다.');
