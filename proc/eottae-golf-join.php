<?php
/**
 * 골프조인 API
 * POST /proc/eottae-golf-join.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-golf-join.lib.php';

if (!defined('_GNUBOARD_')) {
    exit;
}

$is_json = isset($_POST['response']) && $_POST['response'] === 'json';

function eottae_golf_join_proc_json($success, $message, $extra = array())
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array()), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($is_json) {
        eottae_golf_join_proc_json(false, '잘못된 요청입니다.');
    }
    alert('잘못된 요청입니다.', eottae_golf_join_list_url());
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$token = isset($_POST['eottae_golf_join_token']) ? trim((string) $_POST['eottae_golf_join_token']) : '';

if (!eottae_golf_join_verify_member_token($token)) {
    if ($is_json) {
        eottae_golf_join_proc_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.');
    }
    alert('보안 토큰이 만료되었습니다. 페이지를 새로고침해 주세요.', eottae_golf_join_create_url());
}

if ($action === 'create') {
    if (!$is_member || empty($member['mb_id'])) {
        $login = function_exists('eottae_login_url') ? eottae_login_url(eottae_golf_join_create_url()) : G5_BBS_URL.'/login.php';
        if ($is_json) {
            eottae_golf_join_proc_json(false, '로그인이 필요합니다.', array('redirect' => $login));
        }
        alert('로그인 후 등록할 수 있습니다.', $login);
    }

    $mood_tags = array();
    if (isset($_POST['mood_tags']) && is_array($_POST['mood_tags'])) {
        $mood_tags = $_POST['mood_tags'];
    } elseif (isset($_POST['mood_tags']) && is_string($_POST['mood_tags'])) {
        $mood_tags = array_filter(array_map('trim', explode(',', $_POST['mood_tags'])));
    }

    $input = array(
        'register_mode'      => $_POST['register_mode'] ?? '',
        'round_date'         => $_POST['round_date'] ?? '',
        'schedule_slot'      => $_POST['schedule_slot'] ?? '',
        'tee_time'           => $_POST['tee_time'] ?? '',
        'golf_course_id'     => $_POST['golf_course_id'] ?? 0,
        'golf_course_name'   => $_POST['golf_course_name'] ?? '',
        'golf_course_custom' => $_POST['golf_course_custom'] ?? '',
        'region'             => $_POST['region'] ?? '',
        'recruit_slots'      => $_POST['recruit_slots'] ?? 0,
        'gender_preference'  => $_POST['gender_preference'] ?? '',
        'age_preference'     => $_POST['age_preference'] ?? '',
        'score_preference'   => $_POST['score_preference'] ?? '',
        'title'              => $_POST['title'] ?? '',
        'description'        => $_POST['description'] ?? '',
        'mood_tags'          => $mood_tags,
        'host_nickname'      => $_POST['host_nickname'] ?? '',
        'host_gender'        => $_POST['host_gender'] ?? '',
        'host_age_group'     => $_POST['host_age_group'] ?? '',
        'host_score_range'   => $_POST['host_score_range'] ?? '',
    );

    $result = eottae_golf_join_create_post($input, $member);
    eottae_golf_join_member_token(true);

    if (!empty($result['ok'])) {
        $join_id = (int) ($result['join_id'] ?? 0);
        $redirect = eottae_golf_join_detail_url($join_id).'?created=1';
        if ($is_json) {
            eottae_golf_join_proc_json(true, $result['message'] ?? '등록되었습니다.', array(
                'join_id'  => $join_id,
                'redirect' => $redirect,
            ));
        }
        goto_url($redirect);
    }

    if ($is_json) {
        eottae_golf_join_proc_json(false, $result['message'] ?? '등록에 실패했습니다.');
    }
    alert($result['message'] ?? '등록에 실패했습니다.', eottae_golf_join_create_url());
}

if ($is_json) {
    eottae_golf_join_proc_json(false, '지원하지 않는 요청입니다.');
}
alert('지원하지 않는 요청입니다.', eottae_golf_join_list_url());
