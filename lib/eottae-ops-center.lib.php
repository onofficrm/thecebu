<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_ops_table_exists')) {
    function eottae_ops_table_exists($table)
    {
        $table = preg_replace('/[^a-z0-9_]/i', '', (string) $table);
        if ($table === '') {
            return false;
        }
        $row = sql_fetch(" SHOW TABLES LIKE '".sql_escape_string($table)."' ", false);

        return !empty($row);
    }
}

if (!function_exists('eottae_ops_count')) {
    function eottae_ops_count($table, $where = '1=1')
    {
        $table = preg_replace('/[^a-z0-9_]/i', '', (string) $table);
        if ($table === '' || !eottae_ops_table_exists($table)) {
            return 0;
        }
        $row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$table}` WHERE {$where} ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_ops_board_defs')) {
    function eottae_ops_board_defs()
    {
        return array(
            array('key' => 'community', 'label' => '생활정보', 'bo_table' => defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community'),
            array('key' => 'free', 'label' => '자유게시판', 'bo_table' => function_exists('eottae_free_board_table') ? eottae_free_board_table() : 'free'),
            array('key' => 'review', 'label' => '업체리뷰', 'bo_table' => defined('EOTTae_REVIEW_TABLE') ? EOTTae_REVIEW_TABLE : 'review'),
            array('key' => 'event', 'label' => '이벤트', 'bo_table' => defined('EOTTae_EVENT_TABLE') ? EOTTae_EVENT_TABLE : 'event'),
            array('key' => 'market', 'label' => '중고장터', 'bo_table' => defined('EOTTae_MARKET_TABLE') ? EOTTae_MARKET_TABLE : 'market'),
            array('key' => 'estate', 'label' => '부동산', 'bo_table' => defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate'),
            array('key' => 'job', 'label' => '구인구직', 'bo_table' => defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job'),
            array('key' => 'talk', 'label' => '세부톡', 'bo_table' => defined('EOTTae_TALKROOM_TABLE') ? EOTTae_TALKROOM_TABLE : 'talk'),
        );
    }
}

if (!function_exists('eottae_ops_board_activity')) {
    function eottae_ops_board_activity($since)
    {
        global $g5;
        $posts = 0;
        $comments = 0;
        $top_boards = array();
        $since_sql = sql_escape_string($since);

        foreach (eottae_ops_board_defs() as $board) {
            $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) ($board['bo_table'] ?? ''));
            if ($bo_table === '' || empty($g5['write_prefix'])) {
                continue;
            }
            $write_table = $g5['write_prefix'].$bo_table;
            if (!eottae_ops_table_exists($write_table)) {
                continue;
            }

            $post_count = eottae_ops_count($write_table, "wr_is_comment = 0 AND wr_datetime >= '{$since_sql}'");
            $comment_count = eottae_ops_count($write_table, "wr_is_comment = 1 AND wr_datetime >= '{$since_sql}'");
            $posts += $post_count;
            $comments += $comment_count;
            if ($post_count + $comment_count > 0) {
                $top_boards[] = array(
                    'label' => (string) ($board['label'] ?? $bo_table),
                    'posts' => $post_count,
                    'comments' => $comment_count,
                    'total' => $post_count + $comment_count,
                    'href' => function_exists('eottae_board_list_url') ? eottae_board_list_url($bo_table) : G5_BBS_URL.'/board.php?bo_table='.$bo_table,
                );
            }
        }

        usort($top_boards, function ($a, $b) {
            return (int) $b['total'] <=> (int) $a['total'];
        });

        return array(
            'posts' => $posts,
            'comments' => $comments,
            'top_boards' => array_slice($top_boards, 0, 5),
        );
    }
}

if (!function_exists('eottae_ops_latest_posts')) {
    function eottae_ops_latest_posts($limit = 8)
    {
        global $g5;
        $items = array();
        $limit = max(1, min(20, (int) $limit));

        foreach (eottae_ops_board_defs() as $board) {
            $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) ($board['bo_table'] ?? ''));
            if ($bo_table === '' || empty($g5['write_prefix'])) {
                continue;
            }
            $write_table = $g5['write_prefix'].$bo_table;
            if (!eottae_ops_table_exists($write_table)) {
                continue;
            }
            $result = sql_query(" SELECT wr_id, wr_subject, wr_name, mb_id, wr_datetime, wr_hit, wr_comment
                                    FROM `{$write_table}`
                                   WHERE wr_is_comment = 0
                                   ORDER BY wr_datetime DESC, wr_id DESC
                                   LIMIT 3 ", false);
            while ($row = sql_fetch_array($result)) {
                $items[] = array(
                    'board' => (string) ($board['label'] ?? $bo_table),
                    'subject' => get_text($row['wr_subject'] ?? ''),
                    'author' => get_text($row['wr_name'] ?? $row['mb_id'] ?? ''),
                    'datetime' => (string) ($row['wr_datetime'] ?? ''),
                    'hit' => (int) ($row['wr_hit'] ?? 0),
                    'comments' => (int) ($row['wr_comment'] ?? 0),
                    'href' => function_exists('get_pretty_url') ? get_pretty_url($bo_table, (int) $row['wr_id']) : G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.(int) $row['wr_id'],
                );
            }
        }

        usort($items, function ($a, $b) {
            return strcmp((string) $b['datetime'], (string) $a['datetime']);
        });

        return array_slice($items, 0, $limit);
    }
}

if (!function_exists('eottae_ops_member_stats')) {
    function eottae_ops_member_stats($today)
    {
        global $g5;
        $member_table = $g5['member_table'];
        $login_table = $g5['login_table'];
        $today_sql = sql_escape_string($today);

        return array(
            'total' => eottae_ops_count($member_table, "mb_leave_date = ''"),
            'joined_today' => eottae_ops_count($member_table, "mb_datetime >= '{$today_sql}' AND mb_leave_date = ''"),
            'visited_today' => eottae_ops_count($member_table, "mb_today_login >= '{$today_sql}' AND mb_leave_date = ''"),
            'online' => eottae_ops_count($login_table, "lo_datetime >= '".sql_escape_string(date('Y-m-d H:i:s', G5_SERVER_TIME - 900))."'"),
        );
    }
}

if (!function_exists('eottae_ops_talk_stats')) {
    function eottae_ops_talk_stats()
    {
        $stats = array(
            'rooms_active' => 0,
            'rooms_pending' => 0,
            'join_pending' => 0,
            'reports_pending' => 0,
            'notifications_unread' => 0,
        );
        if (!function_exists('eottae_talkroom_table_names')) {
            return $stats;
        }

        $tables = eottae_talkroom_table_names();
        $stats['rooms_active'] = eottae_ops_count($tables['rooms'], "status = 'active'");
        $stats['rooms_pending'] = eottae_ops_count($tables['rooms'], "status = 'pending'");
        $stats['join_pending'] = eottae_ops_count($tables['members'], "status = 'pending'");
        $stats['reports_pending'] = eottae_ops_count($tables['reports'], "status = 'pending'");
        $stats['notifications_unread'] = eottae_ops_count($tables['notifications'], "is_read = 0");

        return $stats;
    }
}

if (!function_exists('eottae_ops_push_stats')) {
    function eottae_ops_push_stats()
    {
        $table = function_exists('eottae_push_table') ? eottae_push_table() : '';
        $configured = function_exists('eottae_push_is_configured') && eottae_push_is_configured();

        return array(
            'configured' => $configured,
            'active' => $table !== '' ? eottae_ops_count($table, "is_active = 1") : 0,
            'failed' => $table !== '' ? eottae_ops_count($table, "is_active = 1 AND last_error <> ''") : 0,
            'sent_today' => $table !== '' ? eottae_ops_count($table, "last_sent_at >= '".sql_escape_string(date('Y-m-d 00:00:00', G5_SERVER_TIME))."'") : 0,
        );
    }
}

if (!function_exists('eottae_ops_auto_comment_stats')) {
    function eottae_ops_auto_comment_stats()
    {
        $stats = array('installed' => false, 'pending' => 0, 'due' => 0, 'failed' => 0, 'inserted_today' => 0);
        $lib = G5_PLUGIN_PATH.'/auto_comment/auto_comment.lib.php';
        if (!function_exists('auto_comment_table') && is_file($lib)) {
            include_once $lib;
        }
        if (!function_exists('auto_comment_is_installed') || !auto_comment_is_installed()) {
            return $stats;
        }

        $table = auto_comment_table('queue');
        $today = date('Y-m-d 00:00:00', G5_SERVER_TIME);
        $stats['installed'] = true;
        $stats['pending'] = eottae_ops_count($table, "acq_status = 'pending'");
        $stats['due'] = eottae_ops_count($table, "acq_status = 'pending' AND acq_scheduled_at <= '".sql_escape_string(G5_TIME_YMDHIS)."'");
        $stats['failed'] = eottae_ops_count($table, "acq_status = 'failed'");
        $stats['inserted_today'] = eottae_ops_count($table, "acq_status = 'inserted' AND acq_inserted_at >= '".sql_escape_string($today)."'");

        return $stats;
    }
}

if (!function_exists('eottae_ops_pending_counts')) {
    function eottae_ops_pending_counts()
    {
        $counts = array();
        if (function_exists('eottae_column_pending_application_count')) {
            $counts['column_applications'] = eottae_column_pending_application_count();
        } else {
            $counts['column_applications'] = 0;
        }
        if (function_exists('eottae_talkroom_admin_pending_report_count')) {
            $counts['talk_reports'] = eottae_talkroom_admin_pending_report_count();
        } else {
            $counts['talk_reports'] = 0;
        }
        if (function_exists('eottae_community_admin_pending_report_count')) {
            $counts['community_reports'] = eottae_community_admin_pending_report_count();
        } else {
            $counts['community_reports'] = 0;
        }
        if (function_exists('eottae_column_list_pending_reports')) {
            $counts['column_reports'] = count(eottae_column_list_pending_reports(100));
        } else {
            $counts['column_reports'] = 0;
        }
        if (function_exists('eottae_calendar_admin_pending_report_count')) {
            $counts['calendar_reports'] = eottae_calendar_admin_pending_report_count();
        } else {
            $counts['calendar_reports'] = 0;
        }

        return $counts;
    }
}

if (!function_exists('eottae_ops_inbox_items')) {
    function eottae_ops_inbox_items(array $tasks)
    {
        $items = array();
        foreach ($tasks as $task) {
            $count = (int) ($task['count'] ?? 0);
            $items[] = array(
                'label' => (string) ($task['label'] ?? ''),
                'desc' => (string) ($task['desc'] ?? ''),
                'count' => $count,
                'href' => (string) ($task['href'] ?? '#'),
                'status' => $count > 0 ? '처리 필요' : '정상',
                'tone' => $count > 0 ? (string) ($task['tone'] ?? 'normal') : 'done',
            );
        }

        return $items;
    }
}

if (!function_exists('eottae_ops_activity_counts_for_range')) {
    function eottae_ops_activity_counts_for_range($from, $to)
    {
        global $g5;
        $from_sql = sql_escape_string($from);
        $to_sql = sql_escape_string($to);
        $posts = 0;
        $comments = 0;

        foreach (eottae_ops_board_defs() as $board) {
            $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) ($board['bo_table'] ?? ''));
            if ($bo_table === '' || empty($g5['write_prefix'])) {
                continue;
            }
            $write_table = $g5['write_prefix'].$bo_table;
            if (!eottae_ops_table_exists($write_table)) {
                continue;
            }
            $where = "wr_datetime >= '{$from_sql}' AND wr_datetime < '{$to_sql}'";
            $posts += eottae_ops_count($write_table, "wr_is_comment = 0 AND {$where}");
            $comments += eottae_ops_count($write_table, "wr_is_comment = 1 AND {$where}");
        }

        return array('posts' => $posts, 'comments' => $comments);
    }
}

if (!function_exists('eottae_ops_kpi_trend')) {
    function eottae_ops_kpi_trend($days = 7)
    {
        global $g5;
        $days = max(3, min(30, (int) $days));
        $items = array();
        $member_table = $g5['member_table'];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day_start_ts = strtotime(date('Y-m-d 00:00:00', G5_SERVER_TIME - $i * 86400));
            $from = date('Y-m-d 00:00:00', $day_start_ts);
            $to = date('Y-m-d 00:00:00', $day_start_ts + 86400);
            $activity = eottae_ops_activity_counts_for_range($from, $to);
            $joined = eottae_ops_count($member_table, "mb_datetime >= '".sql_escape_string($from)."' AND mb_datetime < '".sql_escape_string($to)."' AND mb_leave_date = ''");
            $visited = eottae_ops_count($member_table, "mb_today_login >= '".sql_escape_string($from)."' AND mb_today_login < '".sql_escape_string($to)."' AND mb_leave_date = ''");

            $items[] = array(
                'date' => date('m/d', $day_start_ts),
                'posts' => (int) $activity['posts'],
                'comments' => (int) $activity['comments'],
                'joined' => $joined,
                'visited' => $visited,
                'engagement' => (int) $activity['posts'] + (int) $activity['comments'],
            );
        }

        return $items;
    }
}

if (!function_exists('eottae_ops_build_context')) {
    function eottae_ops_build_context()
    {
        $today = date('Y-m-d 00:00:00', G5_SERVER_TIME);
        $week = date('Y-m-d 00:00:00', G5_SERVER_TIME - 6 * 86400);
        $members = eottae_ops_member_stats($today);
        $today_activity = eottae_ops_board_activity($today);
        $week_activity = eottae_ops_board_activity($week);
        $talk = eottae_ops_talk_stats();
        $push = eottae_ops_push_stats();
        $auto_comment = eottae_ops_auto_comment_stats();
        $pending = eottae_ops_pending_counts();

        $tasks = array();
        $add_task = function ($key, $label, $count, $href, $tone = 'normal', $desc = '') use (&$tasks) {
            $tasks[] = array(
                'key' => $key,
                'label' => $label,
                'count' => (int) $count,
                'href' => $href,
                'tone' => $tone,
                'desc' => $desc,
            );
        };

        $add_task('talk_rooms', '세부톡방 승인 대기', $talk['rooms_pending'], G5_URL.'/page/eottae-admin-talk-rooms.php?status=pending', 'warn', '새 톡방 신청 검토');
        $add_task('talk_join', '세부톡 참여 승인', $talk['join_pending'], G5_URL.'/page/eottae-admin-talk-rooms.php', 'warn', '방장/운영자 승인 필요');
        $add_task('talk_reports', '세부톡 신고', $talk['reports_pending'], G5_URL.'/page/eottae-admin-talk-reports.php?status=pending', 'danger', '톡방 게시글·댓글 신고');
        $add_task('community_reports', '커뮤니티 신고', $pending['community_reports'], G5_URL.'/page/eottae-admin-community-reports.php?status=pending', 'danger', '일반 게시판 신고');
        $add_task('column_reports', '컬럼 신고', $pending['column_reports'], G5_URL.'/page/eottae-admin-column.php?tab=reports', 'danger', '생활정보 컬럼 신고');
        $add_task('calendar_reports', '일정 신고', $pending['calendar_reports'], G5_URL.'/page/eottae-admin-calendar-reports.php?status=pending', 'danger', '캘린더 일정 신고');
        $add_task('columns', '칼럼니스트 신청', $pending['column_applications'], G5_URL.'/page/eottae-admin-column.php?tab=applications', 'normal', '생활정보 작성자 승인');
        $add_task('auto_comment', '자동댓글 실패/대기', $auto_comment['failed'] + $auto_comment['due'], G5_PLUGIN_URL.'/auto_comment/admin/index.php?tab=queue', $auto_comment['failed'] > 0 ? 'danger' : 'normal', '큐/worker 상태 점검');
        $add_task('push', '푸시 운영 키 설정', $push['configured'] ? 0 : 1, G5_URL.'/page/eottae-notifications.php', 'warn', 'VAPID 키 미설정 시 실제 푸시 발송 불가');

        usort($tasks, function ($a, $b) {
            if ((int) $a['count'] === (int) $b['count']) {
                return strcmp((string) $a['label'], (string) $b['label']);
            }

            return (int) $b['count'] <=> (int) $a['count'];
        });

        return array(
            'generated_at' => G5_TIME_YMDHIS,
            'members' => $members,
            'today_activity' => $today_activity,
            'week_activity' => $week_activity,
            'talk' => $talk,
            'push' => $push,
            'auto_comment' => $auto_comment,
            'pending' => $pending,
            'tasks' => $tasks,
            'inbox' => eottae_ops_inbox_items($tasks),
            'kpi_7d' => eottae_ops_kpi_trend(7),
            'kpi_30d' => eottae_ops_kpi_trend(30),
            'latest_posts' => eottae_ops_latest_posts(8),
            'quick_links' => array(
                array('label' => '그누보드 관리자', 'href' => G5_ADMIN_URL.'/'),
                array('label' => '세부톡 관리', 'href' => G5_URL.'/page/eottae-admin-talk-rooms.php'),
                array('label' => '생활정보 컬럼', 'href' => G5_URL.'/page/eottae-admin-column.php'),
                array('label' => '자동댓글 관리', 'href' => G5_PLUGIN_URL.'/auto_comment/admin/index.php'),
                array('label' => '광고 관리', 'href' => G5_URL.'/page/eottae-admin-ad-platform.php'),
                array('label' => '푸시/알림 허브', 'href' => G5_URL.'/page/eottae-notifications.php'),
            ),
        );
    }
}
