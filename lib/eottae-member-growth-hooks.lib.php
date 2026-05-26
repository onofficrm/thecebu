<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_member_growth_has_target_action')) {
    function eottae_member_growth_has_target_action($mb_id, $action_type, $target_type, $target_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $action_type = preg_replace('/[^a-z_]/', '', (string) $action_type);
        $target_type = preg_replace('/[^a-z_]/', '', (string) $target_type);
        $target_id = (int) $target_id;

        if ($mb_id === '' || $action_type === '' || $target_id < 1) {
            return false;
        }

        $logs = eottae_member_growth_logs_table();
        $row = sql_fetch("
            SELECT log_id
            FROM `{$logs}`
            WHERE mb_id = '".sql_escape_string($mb_id)."'
              AND action_type = '".sql_escape_string($action_type)."'
              AND target_type = '".sql_escape_string($target_type)."'
              AND target_id = '{$target_id}'
            LIMIT 1
        ", false);

        return !empty($row['log_id']);
    }
}

if (!function_exists('eottae_member_growth_guide_url')) {
    function eottae_member_growth_guide_url()
    {
        return G5_URL.'/page/eottae-member-growth-guide.php';
    }
}

if (!function_exists('eottae_member_growth_board_good_eligible')) {
    function eottae_member_growth_board_good_eligible($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        if ($bo_table === '') {
            return false;
        }

        if (function_exists('eottae_is_community_board') && eottae_is_community_board($bo_table)) {
            return true;
        }
        if (function_exists('eottae_plaza_board_table') && $bo_table === eottae_plaza_board_table()) {
            return true;
        }
        if (function_exists('eottae_talkroom_is_talkroom_board') && eottae_talkroom_is_talkroom_board($bo_table)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('eottae_member_growth_on_talkroom_join_active')) {
    function eottae_member_growth_on_talkroom_join_active($room_id, $mb_id, $memo = '')
    {
        if (!function_exists('eottae_member_growth_add_score')) {
            return;
        }

        $room_id = (int) $room_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($room_id < 1 || $mb_id === '') {
            return;
        }

        if (eottae_member_growth_has_target_action($mb_id, 'talkroom_join', 'talkroom', $room_id)) {
            return;
        }

        eottae_member_growth_add_score(
            $mb_id,
            'talkroom_join',
            0,
            'talkroom',
            $room_id,
            $memo !== '' ? $memo : '세부톡 참여'
        );
    }
}

if (!function_exists('eottae_member_growth_on_calendar_event_created')) {
    function eottae_member_growth_on_calendar_event_created($event_id, $mb_id)
    {
        if (!function_exists('eottae_member_growth_add_score')) {
            return;
        }

        $event_id = (int) $event_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($event_id < 1 || $mb_id === '') {
            return;
        }

        if (eottae_member_growth_has_target_action($mb_id, 'calendar_event', 'calendar', $event_id)) {
            return;
        }

        eottae_member_growth_add_score($mb_id, 'calendar_event', 0, 'calendar', $event_id, '일정 등록');
    }
}

if (!function_exists('eottae_member_growth_on_plaza_like_received')) {
    function eottae_member_growth_on_plaza_like_received($author_mb_id, $wr_id)
    {
        if (!function_exists('eottae_member_growth_add_score')) {
            return;
        }

        $author_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $author_mb_id);
        $wr_id = (int) $wr_id;
        if ($author_mb_id === '' || $wr_id < 1) {
            return;
        }

        eottae_member_growth_add_score($author_mb_id, 'like_received', 0, 'plaza', $wr_id, '광장 공감');
    }
}

if (!function_exists('eottae_member_growth_on_board_good')) {
    function eottae_member_growth_on_board_good($bo_table, $wr_id, $good, $href = '')
    {
        if ($good !== 'good' || !function_exists('eottae_member_growth_add_score')) {
            return;
        }

        if (!eottae_member_growth_board_good_eligible($bo_table)) {
            return;
        }

        global $g5;
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        if (!function_exists('eottae_member_growth_table_exists') || !eottae_member_growth_table_exists($write_table)) {
            return;
        }

        $row = sql_fetch("
            SELECT mb_id
            FROM `{$write_table}`
            WHERE wr_id = '{$wr_id}'
              AND wr_is_comment = 0
            LIMIT 1
        ", false);
        $author_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($row['mb_id'] ?? ''));
        if ($author_mb_id === '') {
            return;
        }

        eottae_member_growth_add_score($author_mb_id, 'like_received', 0, 'write', $wr_id, $bo_table);
    }
}

if (!function_exists('eottae_member_growth_on_challenge_like_received')) {
    function eottae_member_growth_on_challenge_like_received($author_mb_id, $entry_id)
    {
        if (!function_exists('eottae_member_growth_add_score')) {
            return;
        }

        $author_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $author_mb_id);
        $entry_id = (int) $entry_id;
        if ($author_mb_id === '' || $entry_id < 1) {
            return;
        }

        eottae_member_growth_add_score($author_mb_id, 'like_received', 0, 'challenge', $entry_id, '챌린지 공감');
    }
}

if (!function_exists('eottae_member_growth_on_report_confirmed')) {
    function eottae_member_growth_on_report_confirmed($reporter_mb_id, $report_id, $source = 'report', $memo = '')
    {
        if (!function_exists('eottae_member_growth_add_score')) {
            return;
        }

        $reporter_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $reporter_mb_id);
        $report_id = (int) $report_id;
        $source = preg_replace('/[^a-z_]/', '', (string) $source);
        if ($reporter_mb_id === '' || $report_id < 1 || $source === '') {
            return;
        }

        if (eottae_member_growth_has_target_action($reporter_mb_id, 'report_confirmed', $source, $report_id)) {
            return;
        }

        eottae_member_growth_add_score(
            $reporter_mb_id,
            'report_confirmed',
            0,
            $source,
            $report_id,
            $memo !== '' ? $memo : '유효 신고'
        );
    }
}

if (!function_exists('eottae_member_growth_on_best_post')) {
    function eottae_member_growth_on_best_post($author_mb_id, $entry_id, $source = 'challenge_entry', $memo = '')
    {
        if (!function_exists('eottae_member_growth_add_score')) {
            return;
        }

        $author_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $author_mb_id);
        $entry_id = (int) $entry_id;
        $source = preg_replace('/[^a-z_]/', '', (string) $source);
        if ($author_mb_id === '' || $entry_id < 1 || $source === '') {
            return;
        }

        if (eottae_member_growth_has_target_action($author_mb_id, 'best_post', $source, $entry_id)) {
            return;
        }

        eottae_member_growth_add_score(
            $author_mb_id,
            'best_post',
            0,
            $source,
            $entry_id,
            $memo !== '' ? $memo : '우수글 선정'
        );
    }
}

if (!function_exists('eottae_member_growth_list_score_logs')) {
    function eottae_member_growth_list_score_logs(array $opts = array())
    {
        $limit = min(100, max(1, (int) ($opts['limit'] ?? 50)));
        $offset = max(0, (int) ($opts['offset'] ?? 0));
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($opts['mb_id'] ?? ''));
        $action_type = preg_replace('/[^a-z_]/', '', (string) ($opts['action_type'] ?? ''));

        $logs = eottae_member_growth_logs_table();
        $where = ' WHERE 1=1 ';
        if ($mb_id !== '') {
            $where .= " AND l.mb_id = '".sql_escape_string($mb_id)."' ";
        }
        if ($action_type !== '') {
            $where .= " AND l.action_type = '".sql_escape_string($action_type)."' ";
        }

        $member_table = G5_TABLE_PREFIX.'member';
        $total_row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$logs}` l {$where} ", false);
        $total = (int) ($total_row['cnt'] ?? 0);

        $rows = array();
        $result = sql_query("
            SELECT l.*, m.mb_nick
            FROM `{$logs}` l
            LEFT JOIN `{$member_table}` m ON m.mb_id = l.mb_id
            {$where}
            ORDER BY l.log_id DESC
            LIMIT {$offset}, {$limit}
        ", false);

        while ($row = sql_fetch_array($result)) {
            if (is_array($row)) {
                $rows[] = $row;
            }
        }

        return array(
            'rows'   => $rows,
            'total'  => $total,
            'limit'  => $limit,
            'offset' => $offset,
        );
    }
}

if (!function_exists('eottae_member_growth_action_type_label')) {
    function eottae_member_growth_action_type_label($action_type)
    {
        $labels = array(
            'register'         => '회원가입',
            'first_post'       => '첫 글',
            'post_write'       => '글 작성',
            'comment_write'    => '댓글',
            'talkroom_join'    => '세부톡 참여',
            'talkroom_post'    => '세부톡 글',
            'calendar_event'   => '일정 등록',
            'challenge_entry'  => '챌린지 참여',
            'used_goods_post'  => '중고거래 글',
            'life_info_post'   => '생활정보 글',
            'like_received'    => '공감 받음',
            'report_confirmed' => '신고 처리',
            'best_post'        => '우수 글',
            'admin_grant'      => '관리자 지급',
        );

        $action_type = preg_replace('/[^a-z_]/', '', (string) $action_type);

        return isset($labels[$action_type]) ? $labels[$action_type] : $action_type;
    }
}

if (function_exists('add_event')) {
    add_event('bbs_increase_good_json', 'eottae_member_growth_on_board_good', 10, 3);
    add_event('bbs_increase_good_html', 'eottae_member_growth_on_board_good', 10, 4);
}
