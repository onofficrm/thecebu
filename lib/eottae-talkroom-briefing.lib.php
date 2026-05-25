<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_dashboard_today_start_sql')) {
    include_once G5_LIB_PATH.'/eottae-talkroom-dashboard.lib.php';
}
if (!function_exists('eottae_talkroom_list_my_rooms')) {
    include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
}

if (!function_exists('eottae_talkroom_briefing_bot_label')) {
    function eottae_talkroom_briefing_bot_label()
    {
        return '어때봇 브리핑';
    }
}

if (!function_exists('eottae_talkroom_briefing_member_nick')) {
    function eottae_talkroom_briefing_member_nick($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return '회원';
        }

        $member = get_member($mb_id);
        $nick = trim((string) ($member['mb_nick'] ?? ''));
        if ($nick === '') {
            $nick = $mb_id;
        }

        return get_text($nick);
    }
}

if (!function_exists('eottae_talkroom_briefing_room_ids_for_member')) {
    /**
     * active 참여 + 방장 운영중 톡방 ID (강퇴/승인대기 제외)
     *
     * @return int[]
     */
    function eottae_talkroom_briefing_room_ids_for_member($mb_id)
    {
        if (!function_exists('eottae_talkroom_list_my_rooms')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }
        if (!function_exists('eottae_talkroom_dashboard_feed_room_ids_from_my')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-dashboard.lib.php';
        }

        $my = eottae_talkroom_list_my_rooms($mb_id);

        return eottae_talkroom_dashboard_feed_room_ids_from_my($my);
    }
}

if (!function_exists('eottae_talkroom_briefing_count_today_notices')) {
    /**
     * @param int[] $room_ids
     */
    function eottae_talkroom_briefing_count_today_notices(array $room_ids)
    {
        $room_ids = array_values(array_unique(array_filter(array_map('intval', $room_ids))));
        if (empty($room_ids) || !function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return 0;
        }

        $write_table = eottae_talkroom_write_table();
        $tables = eottae_talkroom_table_names();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table) || !eottae_talkroom_table_exists($tables['rooms'])) {
            return 0;
        }

        $in = implode(',', $room_ids);
        $visible = eottae_talkroom_post_visible_sql('w');
        $notice_where = eottae_talkroom_dashboard_notice_where_sql('w');
        $today_sql = sql_escape_string(eottae_talkroom_dashboard_today_start_sql());

        $statuses = eottae_talkroom_operating_statuses();
        $status_sql = array();
        foreach ($statuses as $status) {
            $status_sql[] = "'".sql_real_escape_string($status)."'";
        }
        $status_in = implode(',', $status_sql);

        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$write_table}` w
            INNER JOIN `{$tables['rooms']}` r ON r.room_id = CAST(w.wr_1 AS UNSIGNED)
            WHERE w.wr_is_comment = 0
              AND {$visible}
              AND {$notice_where}
              AND w.wr_datetime >= '{$today_sql}'
              AND r.status IN ({$status_in})
              AND r.room_id IN ({$in})
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_talkroom_briefing_count_today_meetups')) {
    /**
     * @param int[] $room_ids
     */
    function eottae_talkroom_briefing_count_today_meetups(array $room_ids)
    {
        $room_ids = array_values(array_unique(array_filter(array_map('intval', $room_ids))));
        if (empty($room_ids) || !function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return 0;
        }

        $write_table = eottae_talkroom_write_table();
        $tables = eottae_talkroom_table_names();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table) || !eottae_talkroom_table_exists($tables['rooms'])) {
            return 0;
        }

        $in = implode(',', $room_ids);
        $visible = eottae_talkroom_post_visible_sql('w');
        $meetup_where = eottae_talkroom_dashboard_meetup_where_sql('w');
        $today_sql = sql_escape_string(eottae_talkroom_dashboard_today_start_sql());

        $statuses = eottae_talkroom_operating_statuses();
        $status_sql = array();
        foreach ($statuses as $status) {
            $status_sql[] = "'".sql_real_escape_string($status)."'";
        }
        $status_in = implode(',', $status_sql);

        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$write_table}` w
            INNER JOIN `{$tables['rooms']}` r ON r.room_id = CAST(w.wr_1 AS UNSIGNED)
            WHERE w.wr_is_comment = 0
              AND {$visible}
              AND {$meetup_where}
              AND w.wr_datetime >= '{$today_sql}'
              AND r.status IN ({$status_in})
              AND r.room_id IN ({$in})
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_talkroom_briefing_top_commented_posts')) {
    /**
     * @param int[] $room_ids
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_briefing_top_commented_posts(array $room_ids, $limit = 3)
    {
        $room_ids = array_values(array_unique(array_filter(array_map('intval', $room_ids))));
        $limit = max(1, min(10, (int) $limit));
        if (empty($room_ids) || !function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return array();
        }

        $write_table = eottae_talkroom_write_table();
        $tables = eottae_talkroom_table_names();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table) || !eottae_talkroom_table_exists($tables['rooms'])) {
            return array();
        }

        $in = implode(',', $room_ids);
        $visible = eottae_talkroom_post_visible_sql('w');
        $today_sql = sql_escape_string(eottae_talkroom_dashboard_today_start_sql());

        $statuses = eottae_talkroom_operating_statuses();
        $status_sql = array();
        foreach ($statuses as $status) {
            $status_sql[] = "'".sql_real_escape_string($status)."'";
        }
        $status_in = implode(',', $status_sql);

        $rows = array();
        $result = sql_query("
            SELECT
                w.wr_id,
                w.wr_subject,
                w.wr_comment,
                w.wr_datetime,
                w.ca_name,
                w.mb_id,
                r.room_id,
                r.room_name,
                r.emoji
            FROM `{$write_table}` w
            INNER JOIN `{$tables['rooms']}` r ON r.room_id = CAST(w.wr_1 AS UNSIGNED)
            WHERE w.wr_is_comment = 0
              AND {$visible}
              AND w.wr_datetime >= '{$today_sql}'
              AND r.status IN ({$status_in})
              AND r.room_id IN ({$in})
            ORDER BY w.wr_comment DESC, w.wr_datetime DESC, w.wr_id DESC
            LIMIT {$limit}
        ", false);

        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $rows[] = eottae_talkroom_briefing_format_post_row($row, 'hot_comments');
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_briefing_top_active_rooms')) {
    /**
     * @param int[] $room_ids
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_briefing_top_active_rooms(array $room_ids, array $my, $limit = 3)
    {
        $room_ids = array_values(array_unique(array_filter(array_map('intval', $room_ids))));
        $limit = max(1, min(10, (int) $limit));
        if (empty($room_ids)) {
            return array();
        }

        if (!function_exists('eottae_talkroom_dashboard_batch_owner_today_activity')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-dashboard.lib.php';
        }

        $activity = eottae_talkroom_dashboard_batch_owner_today_activity($room_ids);
        $room_map = array();
        foreach ($my['created'] ?? array() as $room) {
            $room_map[(int) ($room['room_id'] ?? 0)] = $room;
        }
        foreach ($my['joined'] ?? array() as $room) {
            if (($room['member_status'] ?? 'active') !== 'active') {
                continue;
            }
            $room_map[(int) ($room['room_id'] ?? 0)] = $room;
        }

        $scored = array();
        foreach ($room_ids as $room_id) {
            if ($room_id < 1 || !isset($room_map[$room_id])) {
                continue;
            }
            $posts = (int) ($activity['posts'][$room_id] ?? 0);
            $comments = (int) ($activity['comments'][$room_id] ?? 0);
            $score = $posts + $comments;
            if ($score < 1) {
                continue;
            }

            $room = $room_map[$room_id];
            $highlight = eottae_talkroom_briefing_room_highlight_post($room_id);
            $scored[] = array(
                'room_id'           => $room_id,
                'room_name'         => get_text($room['room_name'] ?? ''),
                'emoji'             => get_text(trim((string) ($room['emoji'] ?? '')) !== '' ? $room['emoji'] : '💬'),
                'activity_score'    => $score,
                'today_posts'       => $posts,
                'today_comments'    => $comments,
                'highlight_subject' => $highlight['subject'] ?? '',
                'highlight_type'    => $highlight['type_label'] ?? '',
            );
        }

        usort($scored, function ($a, $b) {
            return (int) ($b['activity_score'] ?? 0) <=> (int) ($a['activity_score'] ?? 0);
        });

        return array_slice($scored, 0, $limit);
    }
}

if (!function_exists('eottae_talkroom_briefing_room_highlight_post')) {
    /**
     * @return array{subject: string, type_label: string}
     */
    function eottae_talkroom_briefing_room_highlight_post($room_id)
    {
        $room_id = (int) $room_id;
        $empty = array('subject' => '', 'type_label' => '');
        if ($room_id < 1 || !function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return $empty;
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table)) {
            return $empty;
        }

        $visible = eottae_talkroom_post_visible_sql('w');
        $today_sql = sql_escape_string(eottae_talkroom_dashboard_today_start_sql());

        $row = sql_fetch("
            SELECT wr_subject, ca_name, wr_comment
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_1 = '{$room_id}'
              AND {$visible}
              AND wr_datetime >= '{$today_sql}'
            ORDER BY wr_comment DESC, wr_datetime DESC, wr_id DESC
            LIMIT 1
        ", false);

        if (!$row) {
            return $empty;
        }

        $subject = get_text($row['wr_subject'] ?? '');
        if (function_exists('cut_str') && $subject !== '') {
            $subject = cut_str(strip_tags($subject), 28, '…');
        }

        $type_label = function_exists('eottae_talkroom_post_type_label')
            ? eottae_talkroom_post_type_label($row['ca_name'] ?? '')
            : trim((string) ($row['ca_name'] ?? ''));

        return array(
            'subject'    => $subject,
            'type_label' => $type_label,
        );
    }
}

if (!function_exists('eottae_talkroom_briefing_format_post_row')) {
    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    function eottae_talkroom_briefing_format_post_row(array $row, $reason = '')
    {
        $room_id = (int) ($row['room_id'] ?? 0);
        $wr_id = (int) ($row['wr_id'] ?? 0);
        if ($room_id < 1 || $wr_id < 1) {
            return array();
        }

        $type_label = function_exists('eottae_talkroom_post_type_label')
            ? eottae_talkroom_post_type_label($row['ca_name'] ?? '')
            : trim((string) ($row['ca_name'] ?? '일반'));

        return array(
            'wr_id'         => $wr_id,
            'room_id'       => $room_id,
            'room_name'     => get_text($row['room_name'] ?? ''),
            'room_emoji'    => get_text(trim((string) ($row['emoji'] ?? '')) !== '' ? $row['emoji'] : '💬'),
            'subject'       => get_text($row['wr_subject'] ?? ''),
            'author'        => get_text($row['wr_name'] ?? ($row['mb_id'] ?? '')),
            'type_label'    => $type_label,
            'comment_count' => (int) ($row['wr_comment'] ?? 0),
            'reason'        => (string) $reason,
            'reason_label'  => eottae_talkroom_briefing_reason_label($reason),
            'href'          => function_exists('eottae_talkroom_post_view_url')
                ? eottae_talkroom_post_view_url($wr_id, $room_id)
                : '',
        );
    }
}

if (!function_exists('eottae_talkroom_briefing_reason_label')) {
    function eottae_talkroom_briefing_reason_label($reason)
    {
        $map = array(
            'my_comment'   => '내 글 댓글',
            'meetup'       => '모임공지',
            'notice'       => '공지',
            'hot_comments' => '댓글 많음',
        );

        return $map[$reason] ?? '';
    }
}

if (!function_exists('eottae_talkroom_briefing_collect_priority_posts')) {
    /**
     * @param int[] $room_ids
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_briefing_collect_priority_posts($mb_id, array $room_ids, $limit = 5)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $room_ids = array_values(array_unique(array_filter(array_map('intval', $room_ids))));
        $limit = max(1, min(10, (int) $limit));
        if ($mb_id === '' || empty($room_ids) || !function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return array();
        }

        $write_table = eottae_talkroom_write_table();
        $tables = eottae_talkroom_table_names();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table) || !eottae_talkroom_table_exists($tables['rooms'])) {
            return array();
        }

        $candidates = array();
        $in = implode(',', $room_ids);
        $mb_sql = sql_escape_string($mb_id);
        $visible = eottae_talkroom_post_visible_sql('w');
        $comment_visible = eottae_talkroom_post_visible_sql('c');
        $today_sql = sql_escape_string(eottae_talkroom_dashboard_today_start_sql());
        $notice_where = eottae_talkroom_dashboard_notice_where_sql('w');
        $meetup_where = eottae_talkroom_dashboard_meetup_where_sql('w');

        $statuses = eottae_talkroom_operating_statuses();
        $status_sql = array();
        foreach ($statuses as $status) {
            $status_sql[] = "'".sql_real_escape_string($status)."'";
        }
        $status_in = implode(',', $status_sql);

        $add_candidate = function (array $row, $reason, $score) use (&$candidates, $mb_id) {
            if (function_exists('eottae_talkroom_dashboard_post_view_allowed')
                && !eottae_talkroom_dashboard_post_view_allowed($mb_id, $row)) {
                return;
            }
            $formatted = eottae_talkroom_briefing_format_post_row($row, $reason);
            if (empty($formatted['wr_id']) || empty($formatted['href'])) {
                return;
            }
            $wr_id = (int) $formatted['wr_id'];
            if (!isset($candidates[$wr_id]) || $score > (int) ($candidates[$wr_id]['_score'] ?? 0)) {
                $formatted['_score'] = $score;
                $candidates[$wr_id] = $formatted;
            }
        };

        $result = sql_query("
            SELECT
                p.wr_id, p.wr_subject, p.wr_comment, p.wr_datetime, p.ca_name, p.mb_id, p.wr_name,
                r.room_id, r.room_name, r.emoji,
                COUNT(c.wr_id) AS new_comment_cnt
            FROM `{$write_table}` p
            INNER JOIN `{$tables['rooms']}` r ON r.room_id = CAST(p.wr_1 AS UNSIGNED)
            INNER JOIN `{$write_table}` c
                ON c.wr_parent = p.wr_id AND c.wr_is_comment = 1
            WHERE p.wr_is_comment = 0
              AND p.mb_id = '{$mb_sql}'
              AND CAST(p.wr_1 AS UNSIGNED) IN ({$in})
              AND {$visible}
              AND {$comment_visible}
              AND c.wr_datetime >= '{$today_sql}'
              AND r.status IN ({$status_in})
            GROUP BY p.wr_id
            ORDER BY new_comment_cnt DESC, p.wr_datetime DESC
            LIMIT 5
        ", false);
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $score = 100 + (int) ($row['new_comment_cnt'] ?? 0);
                $add_candidate($row, 'my_comment', $score);
            }
        }

        $result = sql_query("
            SELECT w.wr_id, w.wr_subject, w.wr_comment, w.wr_datetime, w.ca_name, w.mb_id, w.wr_name,
                   r.room_id, r.room_name, r.emoji
            FROM `{$write_table}` w
            INNER JOIN `{$tables['rooms']}` r ON r.room_id = CAST(w.wr_1 AS UNSIGNED)
            WHERE w.wr_is_comment = 0
              AND CAST(w.wr_1 AS UNSIGNED) IN ({$in})
              AND {$visible}
              AND {$meetup_where}
              AND w.wr_datetime >= '{$today_sql}'
              AND r.status IN ({$status_in})
            ORDER BY w.wr_datetime DESC, w.wr_id DESC
            LIMIT 5
        ", false);
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $add_candidate($row, 'meetup', 80);
            }
        }

        $result = sql_query("
            SELECT w.wr_id, w.wr_subject, w.wr_comment, w.wr_datetime, w.ca_name, w.mb_id, w.wr_name,
                   r.room_id, r.room_name, r.emoji
            FROM `{$write_table}` w
            INNER JOIN `{$tables['rooms']}` r ON r.room_id = CAST(w.wr_1 AS UNSIGNED)
            WHERE w.wr_is_comment = 0
              AND CAST(w.wr_1 AS UNSIGNED) IN ({$in})
              AND {$visible}
              AND {$notice_where}
              AND w.wr_datetime >= '{$today_sql}'
              AND r.status IN ({$status_in})
            ORDER BY w.wr_datetime DESC, w.wr_id DESC
            LIMIT 5
        ", false);
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $add_candidate($row, 'notice', 70);
            }
        }

        foreach (eottae_talkroom_briefing_top_commented_posts($room_ids, 5) as $row) {
            if (empty($row['wr_id'])) {
                continue;
            }
            $score = 50 + (int) ($row['comment_count'] ?? 0);
            $add_candidate(array_merge($row, array(
                'wr_name' => $row['author'] ?? '',
            )), 'hot_comments', $score);
        }

        $items = array_values($candidates);
        usort($items, function ($a, $b) {
            return (int) ($b['_score'] ?? 0) <=> (int) ($a['_score'] ?? 0);
        });

        $items = array_slice($items, 0, $limit);
        foreach ($items as &$item) {
            unset($item['_score']);
        }
        unset($item);

        return $items;
    }
}

if (!function_exists('collect_my_talk_briefing_data')) {
    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    function collect_my_talk_briefing_data($mb_id, array $options = array())
    {
        if (!function_exists('eottae_talkroom_list_my_rooms')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }
        if (!function_exists('eottae_talkroom_unread_totals_for_rooms')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-reads.lib.php';
        }
        if (!function_exists('eottae_talkroom_notify_unread_count')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-notify.lib.php';
        }
        if (!function_exists('eottae_talkroom_dashboard_build_owner_summaries')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-dashboard.lib.php';
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array(
                'mb_id'        => '',
                'member_nick'  => '',
                'room_count'   => 0,
                'is_empty'     => true,
                'generated_at' => defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'),
            );
        }

        $my = isset($options['my']) && is_array($options['my'])
            ? $options['my']
            : eottae_talkroom_list_my_rooms($mb_id);
        $room_ids = isset($options['room_ids']) && is_array($options['room_ids'])
            ? array_values(array_unique(array_filter(array_map('intval', $options['room_ids']))))
            : eottae_talkroom_briefing_room_ids_for_member($mb_id);

        $today_activity = !empty($room_ids)
            ? eottae_talkroom_dashboard_batch_owner_today_activity($room_ids)
            : array('posts' => array(), 'comments' => array());

        $today_posts = 0;
        $today_comments = 0;
        foreach ($room_ids as $room_id) {
            $today_posts += (int) ($today_activity['posts'][$room_id] ?? 0);
            $today_comments += (int) ($today_activity['comments'][$room_id] ?? 0);
        }

        $unread_totals = !empty($room_ids)
            ? eottae_talkroom_unread_totals_for_rooms($mb_id, $room_ids)
            : array('new_posts' => 0, 'new_comments' => 0);

        $owner_summaries = !empty($my['created']) || !empty($my['joined'])
            ? eottae_talkroom_dashboard_build_owner_summaries($mb_id, $my)
            : array();

        $pending_join = 0;
        $pending_reports = 0;
        foreach ($owner_summaries as $row) {
            $pending_join += (int) ($row['pending_members'] ?? 0);
            $pending_reports += (int) ($row['pending_reports'] ?? 0);
        }

        $top_rooms = eottae_talkroom_briefing_top_active_rooms($room_ids, $my, 3);
        $top_commented = eottae_talkroom_briefing_top_commented_posts($room_ids, 3);
        $priority_posts = eottae_talkroom_briefing_collect_priority_posts($mb_id, $room_ids, 5);

        $notice_count = eottae_talkroom_briefing_count_today_notices($room_ids);
        $meetup_count = eottae_talkroom_briefing_count_today_meetups($room_ids);
        $notification_count = eottae_talkroom_notify_unread_count($mb_id);

        $is_empty = (
            count($room_ids) < 1
            || (
                $today_posts < 1
                && $today_comments < 1
                && (int) ($unread_totals['new_posts'] ?? 0) < 1
                && (int) ($unread_totals['new_comments'] ?? 0) < 1
                && $notification_count < 1
                && $notice_count < 1
                && $meetup_count < 1
                && $pending_join < 1
                && $pending_reports < 1
                && empty($priority_posts)
            )
        );

        return array(
            'mb_id'               => $mb_id,
            'member_nick'         => eottae_talkroom_briefing_member_nick($mb_id),
            'room_count'          => count($room_ids),
            'today_posts'         => $today_posts,
            'today_comments'      => $today_comments,
            'unread_posts'        => (int) ($unread_totals['new_posts'] ?? 0),
            'unread_comments'     => (int) ($unread_totals['new_comments'] ?? 0),
            'notification_count'  => $notification_count,
            'notice_count'        => $notice_count,
            'meetup_count'        => $meetup_count,
            'owner_pending_join'  => $pending_join,
            'owner_pending_reports' => $pending_reports,
            'owner_tasks'         => $pending_join + $pending_reports,
            'top_rooms'           => $top_rooms,
            'top_commented_posts' => $top_commented,
            'priority_posts'      => $priority_posts,
            'is_empty'            => $is_empty,
            'generated_at'        => defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'),
            'bot_label'           => eottae_talkroom_briefing_bot_label(),
        );
    }
}

if (!function_exists('eottae_talkroom_briefing_generate_template_text')) {
    /**
     * @param array<string, mixed> $data
     * @return string[]
     */
    function eottae_talkroom_briefing_generate_template_text(array $data)
    {
        if (!empty($data['is_empty']) || (int) ($data['room_count'] ?? 0) < 1) {
            return array(
                '아직 오늘의 새 소식이 많지 않습니다.',
                '관심 있는 톡방에 참여하고 대화를 시작해보세요.',
            );
        }

        $nick = get_text($data['member_nick'] ?? '회원');
        $lines = array();

        if ((int) ($data['today_posts'] ?? 0) > 0 || (int) ($data['today_comments'] ?? 0) > 0) {
            $lines[] = sprintf(
                '%s님이 가입한 %d개 톡방에서 오늘 새 글 %d개, 댓글 %d개가 올라왔습니다.',
                $nick,
                (int) ($data['room_count'] ?? 0),
                (int) ($data['today_posts'] ?? 0),
                (int) ($data['today_comments'] ?? 0)
            );
        } else {
            $lines[] = sprintf(
                '%s님이 참여 중인 %d개 톡방을 확인했습니다.',
                $nick,
                (int) ($data['room_count'] ?? 0)
            );
        }

        $top_rooms = isset($data['top_rooms']) && is_array($data['top_rooms']) ? $data['top_rooms'] : array();
        foreach (array_slice($top_rooms, 0, 2) as $room) {
            $room_name = get_text($room['room_name'] ?? '');
            $subject = get_text($room['highlight_subject'] ?? '');
            if ($room_name === '') {
                continue;
            }
            if ($subject !== '') {
                $lines[] = sprintf('%s에서는 %s 이야기가 진행 중입니다.', $room_name, $subject);
            } else {
                $lines[] = sprintf('%s에서 오늘 활동이 이어지고 있습니다.', $room_name);
            }
        }

        if ((int) ($data['meetup_count'] ?? 0) > 0) {
            $lines[] = sprintf('오늘 모임 관련 글이 %d개 올라왔습니다.', (int) $data['meetup_count']);
        }

        if ((int) ($data['notice_count'] ?? 0) > 0) {
            $lines[] = sprintf('새 공지 %d개를 확인해보세요.', (int) $data['notice_count']);
        }

        if ((int) ($data['notification_count'] ?? 0) > 0) {
            $lines[] = sprintf('먼저 확인하면 좋은 알림이 %d개 있습니다.', (int) $data['notification_count']);
        }

        if ((int) ($data['owner_pending_join'] ?? 0) > 0) {
            $lines[] = sprintf('방장으로 관리해야 할 참여 신청이 %d건 있습니다.', (int) $data['owner_pending_join']);
        } elseif ((int) ($data['owner_pending_reports'] ?? 0) > 0) {
            $lines[] = sprintf('처리할 신고가 %d건 있습니다.', (int) $data['owner_pending_reports']);
        }

        if ((int) ($data['unread_posts'] ?? 0) + (int) ($data['unread_comments'] ?? 0) > 0 && count($lines) < 4) {
            $lines[] = sprintf(
                '아직 읽지 않은 글 %d개, 댓글 %d개가 있습니다.',
                (int) ($data['unread_posts'] ?? 0),
                (int) ($data['unread_comments'] ?? 0)
            );
        }

        return array_slice($lines, 0, 5);
    }
}

if (!function_exists('generate_my_talk_briefing_text')) {
    /**
     * OpenAI API 교체 지점 — eottae_talkroom_briefing_generate_via_openai() 훅
     *
     * @param array<string, mixed> $data
     * @return string[]
     */
    function generate_my_talk_briefing_text(array $data)
    {
        if (function_exists('eottae_talkroom_briefing_generate_via_openai')) {
            $ai_lines = eottae_talkroom_briefing_generate_via_openai($data);
            if (is_array($ai_lines) && !empty($ai_lines)) {
                return array_slice(array_values(array_filter(array_map('trim', $ai_lines))), 0, 5);
            }
            if (is_string($ai_lines) && trim($ai_lines) !== '') {
                return array_slice(preg_split('/\R+/u', trim($ai_lines)), 0, 5);
            }
        }

        return eottae_talkroom_briefing_generate_template_text($data);
    }
}

if (!function_exists('render_my_talk_briefing')) {
    /**
     * @param array<string, mixed> $data
     */
    function render_my_talk_briefing(array $data)
    {
        $lines = generate_my_talk_briefing_text($data);
        $priority_posts = isset($data['priority_posts']) && is_array($data['priority_posts'])
            ? $data['priority_posts']
            : array();
        $bot_label = get_text($data['bot_label'] ?? eottae_talkroom_briefing_bot_label());
        $is_empty = !empty($data['is_empty']);

        ob_start();
        ?>
        <section class="my-talk-briefing" aria-labelledby="my-talk-briefing-title">
            <div class="my-talk-briefing__inner">
                <span class="my-talk-briefing__icon" aria-hidden="true">🤖</span>
                <div class="my-talk-briefing__body">
                    <div class="my-talk-briefing__head">
                        <h2 class="my-talk-briefing__title" id="my-talk-briefing-title">오늘의 세부톡 브리핑</h2>
                        <span class="my-talk-briefing__badge"><?php echo $bot_label; ?></span>
                    </div>
                    <p class="my-talk-briefing__auto-note">자동 요약 · 실시간 활동 기준</p>
                    <div class="my-talk-briefing__text-block">
                        <?php foreach ($lines as $line) {
                            if (trim((string) $line) === '') {
                                continue;
                            } ?>
                        <p class="my-talk-briefing__text"><?php echo get_text($line); ?></p>
                        <?php } ?>
                    </div>
                    <?php if (!$is_empty && !empty($priority_posts)) { ?>
                    <div class="my-talk-briefing__priority">
                        <h3 class="my-talk-briefing__priority-title">먼저 확인하면 좋은 글</h3>
                        <ul class="my-talk-briefing__priority-list">
                            <?php foreach ($priority_posts as $post) {
                                if (empty($post['href'])) {
                                    continue;
                                } ?>
                            <li class="my-talk-briefing__priority-item">
                                <a href="<?php echo $post['href']; ?>" class="my-talk-briefing__priority-link">
                                    <span class="my-talk-room-badge my-talk-briefing__priority-room"><?php echo get_text($post['room_emoji'] ?? '💬'); ?> <?php echo get_text($post['room_name'] ?? ''); ?></span>
                                    <?php if (!empty($post['reason_label'])) { ?>
                                    <span class="my-talk-badge my-talk-badge--muted my-talk-briefing__priority-reason"><?php echo get_text($post['reason_label']); ?></span>
                                    <?php } ?>
                                    <strong class="my-talk-briefing__priority-subject my-talk-title-clamp"><?php echo get_text($post['subject'] ?? ''); ?></strong>
                                    <?php if ((int) ($post['comment_count'] ?? 0) > 0) { ?>
                                    <span class="my-talk-briefing__priority-meta">댓글 <?php echo number_format((int) $post['comment_count']); ?></span>
                                    <?php } ?>
                                </a>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
