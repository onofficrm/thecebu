<?php
/**
 * 프로모션 쿠폰 — 관리자·회원 API
 */
include_once dirname(__DIR__).'/common.php';
include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-promo-coupon.lib.php';

header('Content-Type: application/json; charset=utf-8');

eottae_promo_coupon_ensure_schema();

$action = isset($_REQUEST['action']) ? trim((string) $_REQUEST['action']) : '';

$admin_actions = array('list', 'create', 'status', 'grant', 'best_comment', 'awards');
$member_actions = array('visible', 'claim', 'quiz', 'attendance', 'my_awards');

if (in_array($action, $admin_actions, true)) {
    if ($is_admin !== 'super') {
        echo json_encode(array('success' => false, 'message' => '최고관리자만 이용할 수 있습니다.'), JSON_UNESCAPED_UNICODE);
        exit;
    }
    $actor_mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';
} elseif (in_array($action, $member_actions, true)) {
    if (empty($is_member) || empty($member['mb_id'])) {
        echo json_encode(array('success' => false, 'message' => '로그인 후 이용해 주세요.'), JSON_UNESCAPED_UNICODE);
        exit;
    }
    $actor_mb_id = $member['mb_id'];
} else {
    echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'list') {
    $status = isset($_REQUEST['status']) ? trim((string) $_REQUEST['status']) : '';
    echo json_encode(array(
        'success' => true,
        'data' => eottae_promo_coupon_list($status),
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'create') {
    $trigger_type = isset($_POST['trigger_type']) ? trim((string) $_POST['trigger_type']) : '';
    $config = array();

    if ($trigger_type === 'quiz') {
        $config = array(
            'question' => isset($_POST['quiz_question']) ? $_POST['quiz_question'] : '',
            'answer' => isset($_POST['quiz_answer']) ? $_POST['quiz_answer'] : '',
            'hint' => isset($_POST['quiz_hint']) ? $_POST['quiz_hint'] : '',
        );
    } elseif ($trigger_type === 'post_count') {
        $config = array(
            'min_posts' => isset($_POST['min_posts']) ? (int) $_POST['min_posts'] : 0,
            'bo_tables' => eottae_promo_community_boards(),
        );
    } elseif ($trigger_type === 'post_views') {
        $config = array(
            'min_views' => isset($_POST['min_views']) ? (int) $_POST['min_views'] : 0,
            'bo_tables' => eottae_promo_community_boards(),
        );
    } elseif ($trigger_type === 'attendance_streak') {
        $config = array(
            'days' => isset($_POST['attendance_days']) ? (int) $_POST['attendance_days'] : 0,
        );
    } else {
        $config = array(
            'guide' => isset($_POST['promo_guide']) ? $_POST['promo_guide'] : '',
        );
    }

    $result = eottae_promo_coupon_create($actor_mb_id, array(
        'promo_title' => isset($_POST['promo_title']) ? $_POST['promo_title'] : '',
        'promo_desc' => isset($_POST['promo_desc']) ? $_POST['promo_desc'] : '',
        'trigger_type' => $trigger_type,
        'trigger_config' => $config,
        'promo_max_total' => isset($_POST['promo_max_total']) ? (int) $_POST['promo_max_total'] : 0,
        'promo_max_per_member' => isset($_POST['promo_max_per_member']) ? (int) $_POST['promo_max_per_member'] : 1,
        'promo_starts_at' => isset($_POST['promo_starts_at']) ? $_POST['promo_starts_at'] : '',
        'promo_ends_at' => isset($_POST['promo_ends_at']) ? $_POST['promo_ends_at'] : '',
        'cp_expires_at' => isset($_POST['cp_expires_at']) ? $_POST['cp_expires_at'] : '',
    ));

    echo json_encode(array(
        'success' => !empty($result['ok']),
        'message' => $result['message'],
        'promo_id' => isset($result['promo_id']) ? (int) $result['promo_id'] : 0,
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'status') {
    $promo_id = isset($_POST['promo_id']) ? (int) $_POST['promo_id'] : 0;
    $status = isset($_POST['status']) ? trim((string) $_POST['status']) : 'paused';
    $result = eottae_promo_coupon_update_status($promo_id, $status);
    echo json_encode(array(
        'success' => !empty($result['ok']),
        'message' => $result['message'],
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'grant') {
    $promo_id = isset($_POST['promo_id']) ? (int) $_POST['promo_id'] : 0;
    $target_mb_id = isset($_POST['target_mb_id']) ? trim((string) $_POST['target_mb_id']) : '';
    $result = eottae_promo_admin_grant($promo_id, $target_mb_id, $actor_mb_id);
    echo json_encode(array(
        'success' => !empty($result['ok']),
        'message' => $result['message'],
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'best_comment') {
    $promo_id = isset($_POST['promo_id']) ? (int) $_POST['promo_id'] : 0;
    $bo_table = isset($_POST['bo_table']) ? trim((string) $_POST['bo_table']) : '';
    $wr_id = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
    $comment_wr_id = isset($_POST['comment_wr_id']) ? (int) $_POST['comment_wr_id'] : 0;
    $result = eottae_promo_admin_best_comment($promo_id, $bo_table, $wr_id, $comment_wr_id, $actor_mb_id);
    echo json_encode(array(
        'success' => !empty($result['ok']),
        'message' => $result['message'],
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'awards') {
    $promo_id = isset($_REQUEST['promo_id']) ? (int) $_REQUEST['promo_id'] : 0;
    global $g5;
    eottae_promo_coupon_bootstrap_tables();
    $where = $promo_id > 0 ? " where a.promo_id = '{$promo_id}' " : '';
    $result = sql_query(" select a.*, p.promo_title, m.mb_nick
        from {$g5['eottae_coupon_promo_award_table']} a
        inner join {$g5['eottae_coupon_promo_table']} p on p.promo_id = a.promo_id
        left join {$g5['member_table']} m on m.mb_id = a.mb_id
        {$where}
        order by a.award_id desc
        limit 200 ");
    $rows = array();
    while ($row = sql_fetch_array($result)) {
        $rows[] = $row;
    }
    echo json_encode(array('success' => true, 'data' => $rows), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'visible') {
    echo json_encode(array(
        'success' => true,
        'data' => eottae_promo_member_visible_list($actor_mb_id),
        'streak' => eottae_attendance_get_streak($actor_mb_id),
        'checked_today' => eottae_attendance_checked_today($actor_mb_id),
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'claim') {
    $promo_id = isset($_POST['promo_id']) ? (int) $_POST['promo_id'] : 0;
    $result = eottae_promo_member_claim($promo_id, $actor_mb_id);
    echo json_encode(array(
        'success' => !empty($result['ok']),
        'message' => $result['message'],
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'quiz') {
    $promo_id = isset($_POST['promo_id']) ? (int) $_POST['promo_id'] : 0;
    $answer = isset($_POST['answer']) ? $_POST['answer'] : '';
    $result = eottae_promo_member_quiz($promo_id, $actor_mb_id, $answer);
    echo json_encode(array(
        'success' => !empty($result['ok']),
        'message' => $result['message'],
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'attendance') {
    $result = eottae_attendance_checkin($actor_mb_id);
    echo json_encode(array(
        'success' => !empty($result['ok']),
        'message' => $result['message'],
        'streak' => isset($result['streak']) ? (int) $result['streak'] : 0,
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'my_awards') {
    echo json_encode(array(
        'success' => true,
        'data' => eottae_promo_awards_for_member($actor_mb_id),
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array('success' => false, 'message' => '처리할 수 없는 요청입니다.'), JSON_UNESCAPED_UNICODE);
