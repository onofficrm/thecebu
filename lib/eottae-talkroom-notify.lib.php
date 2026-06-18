<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_notify_table')) {
    function eottae_talkroom_notify_table()
    {
        if (!function_exists('eottae_talkroom_bootstrap_tables')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }
        eottae_talkroom_bootstrap_tables();
        global $g5;

        if (!isset($g5['sebu_talk_notifications_table'])) {
            $g5['sebu_talk_notifications_table'] = G5_TABLE_PREFIX.'sebu_talk_notifications';
        }

        return $g5['sebu_talk_notifications_table'];
    }
}

if (!function_exists('eottae_talkroom_notify_types')) {
    /**
     * @return array<string, string>
     */
    function eottae_talkroom_notify_types()
    {
        return array(
            'comment_on_my_post' => '내 글 댓글',
            'reply_to_my_comment' => '내 댓글 답글',
            'mention'             => '멘션',
            'room_approved'       => '톡방 승인',
            'room_rejected'       => '톡방 반려',
            'join_approved'       => '참여 승인',
            'join_rejected'       => '참여 거절',
            'owner_join_request'  => '참여 신청',
            'owner_report'        => '신고 접수',
            'room_notice'         => '톡방 공지',
        );
    }
}

if (!function_exists('eottae_talkroom_notify_ensure_schema')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_talkroom_notify_ensure_schema()
    {
        $table = eottae_talkroom_notify_table();
        $existed = function_exists('eottae_talkroom_table_exists') && eottae_talkroom_table_exists($table);
        $sql = " CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `room_id` int(11) unsigned NOT NULL DEFAULT '0',
            `type` varchar(40) NOT NULL DEFAULT '',
            `target_type` varchar(20) NOT NULL DEFAULT '',
            `target_id` int(11) unsigned NOT NULL DEFAULT '0',
            `title` varchar(200) NOT NULL DEFAULT '',
            `message` varchar(500) NOT NULL DEFAULT '',
            `href` varchar(500) NOT NULL DEFAULT '',
            `is_read` tinyint(1) NOT NULL DEFAULT '0',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `read_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            KEY `idx_mb_id` (`mb_id`),
            KEY `idx_mb_read` (`mb_id`, `is_read`),
            KEY `idx_room_id` (`room_id`),
            KEY `idx_type` (`type`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";
        $ok = (bool) sql_query($sql, false);

        return array(
            'table'   => $table,
            'existed' => $existed,
            'ok'      => $ok,
            'action'  => $existed ? 'exists' : 'created',
        );
    }
}

if (!function_exists('eottae_talkroom_notify_member_nick')) {
    function eottae_talkroom_notify_member_nick($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return '';
        }

        $member_table = G5_TABLE_PREFIX.'member';
        $row = sql_fetch(" SELECT mb_nick FROM `{$member_table}` WHERE mb_id = '".sql_escape_string($mb_id)."' ", false);

        return !empty($row['mb_nick']) ? get_text($row['mb_nick']) : $mb_id;
    }
}

if (!function_exists('eottae_talkroom_notify_create')) {
    /**
     * @param array<string, mixed> $payload
     * @return array{ok: bool, id?: int}
     */
    function eottae_talkroom_notify_create($mb_id, $type, array $payload = array())
    {
        eottae_talkroom_notify_ensure_schema();

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $type = trim((string) $type);
        $types = eottae_talkroom_notify_types();

        if ($mb_id === '' || !isset($types[$type])) {
            return array('ok' => false);
        }

        $room_id = max(0, (int) ($payload['room_id'] ?? 0));
        $target_type = trim(strip_tags((string) ($payload['target_type'] ?? '')));
        $target_id = max(0, (int) ($payload['target_id'] ?? 0));
        $title = eottae_talkroom_clean_text($payload['title'] ?? '', 200);
        $message = eottae_talkroom_clean_text($payload['message'] ?? '', 500);
        $href = trim(strip_tags((string) ($payload['href'] ?? '')));
        if ($href !== '' && function_exists('clean_xss_tags')) {
            $href = clean_xss_tags($href, 1, 1);
        }
        if (function_exists('eottae_talkroom_sanitize_internal_href')) {
            $href = eottae_talkroom_sanitize_internal_href($href, '');
        }

        if ($title === '') {
            $title = $types[$type];
        }

        $table = eottae_talkroom_notify_table();
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        $mb_sql = sql_escape_string($mb_id);
        $type_sql = sql_escape_string($type);
        $target_type_sql = sql_escape_string($target_type);
        $href_sql = sql_escape_string($href);

        $ok = (bool) sql_query("
            INSERT INTO `{$table}` SET
                mb_id = '{$mb_sql}',
                room_id = '{$room_id}',
                type = '{$type_sql}',
                target_type = '{$target_type_sql}',
                target_id = '{$target_id}',
                title = '".sql_escape_string($title)."',
                message = '".sql_escape_string($message)."',
                href = '{$href_sql}',
                is_read = '0',
                created_at = '{$now}',
                read_at = '0000-00-00 00:00:00'
        ", false);

        if (!$ok) {
            return array('ok' => false);
        }

        $notification_id = (int) sql_insert_id();
        if (function_exists('eottae_push_send_to_member')) {
            eottae_push_send_to_member($mb_id);
        }

        return array(
            'ok' => true,
            'id' => $notification_id,
        );
    }
}

if (!function_exists('eottae_talkroom_notify_room_name')) {
    function eottae_talkroom_notify_room_name($room_id)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1 || !function_exists('eottae_talkroom_get_room')) {
            return '';
        }

        $room = eottae_talkroom_get_room($room_id);

        return is_array($room) ? get_text($room['room_name'] ?? '') : '';
    }
}

if (!function_exists('eottae_talkroom_notify_comment_on_post')) {
    function eottae_talkroom_notify_comment_on_post($board, $parent_wr_id, $comment_id, $commenter_mb_id = '')
    {
        if (empty($board['bo_table']) || !function_exists('eottae_talkroom_is_talkroom_board')
            || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }

        $parent_wr_id = (int) $parent_wr_id;
        $comment_id = (int) $comment_id;
        if ($parent_wr_id < 1 || $comment_id < 1) {
            return;
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '') {
            return;
        }

        $parent = sql_fetch(" SELECT wr_id, wr_subject, wr_1, mb_id FROM `{$write_table}`
            WHERE wr_id = '{$parent_wr_id}' AND wr_is_comment = 0 LIMIT 1 ", false);
        if (empty($parent['wr_id'])) {
            return;
        }

        $owner_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($parent['mb_id'] ?? ''));
        $commenter_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $commenter_mb_id);
        if ($owner_mb_id === '' || ($commenter_mb_id !== '' && $owner_mb_id === $commenter_mb_id)) {
            return;
        }

        $room_id = (int) ($parent['wr_1'] ?? 0);
        $nick = $commenter_mb_id !== '' ? eottae_talkroom_notify_member_nick($commenter_mb_id) : '회원';
        $subject = get_text($parent['wr_subject'] ?? '');
        $room_name = eottae_talkroom_notify_room_name($room_id);
        $href = function_exists('eottae_talkroom_post_view_url')
            ? eottae_talkroom_post_view_url($parent_wr_id, $room_id).'#c_'.$comment_id
            : '';

        eottae_talkroom_notify_create($owner_mb_id, 'comment_on_my_post', array(
            'room_id'     => $room_id,
            'target_type' => 'comment',
            'target_id'   => $comment_id,
            'title'       => '내 글에 새 댓글',
            'message'     => ($room_name !== '' ? '['.$room_name.'] ' : '').$nick.'님이 「'.cut_str($subject, 40).'」에 댓글을 남겼습니다.',
            'href'        => $href,
        ));
    }
}

if (!function_exists('eottae_talkroom_notify_room_approved')) {
    function eottae_talkroom_notify_room_approved(array $room)
    {
        $room_id = (int) ($room['room_id'] ?? 0);
        $owner_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($room['owner_mb_id'] ?? ''));
        if ($room_id < 1 || $owner_mb_id === '') {
            return;
        }

        $room_name = get_text($room['room_name'] ?? '');
        eottae_talkroom_notify_create($owner_mb_id, 'room_approved', array(
            'room_id'     => $room_id,
            'target_type' => 'room',
            'target_id'   => $room_id,
            'title'       => '톡방 개설이 승인되었습니다',
            'message'     => '「'.$room_name.'」 톡방이 운영 승인되었습니다. 지금 바로 톡방을 이용해 보세요.',
            'href'        => function_exists('eottae_talkroom_enter_url') ? eottae_talkroom_enter_url($room_id) : '',
        ));
    }
}

if (!function_exists('eottae_talkroom_notify_room_rejected')) {
    function eottae_talkroom_notify_room_rejected(array $room, $reason = '')
    {
        $room_id = (int) ($room['room_id'] ?? 0);
        $owner_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($room['owner_mb_id'] ?? ''));
        if ($room_id < 1 || $owner_mb_id === '') {
            return;
        }

        $room_name = get_text($room['room_name'] ?? '');
        $reason = eottae_talkroom_clean_text($reason, 120);
        $message = '「'.$room_name.'」 톡방 개설 신청이 반려되었습니다.';
        if ($reason !== '') {
            $message .= ' 사유: '.$reason;
        }

        eottae_talkroom_notify_create($owner_mb_id, 'room_rejected', array(
            'room_id'     => $room_id,
            'target_type' => 'room',
            'target_id'   => $room_id,
            'title'       => '톡방 개설이 반려되었습니다',
            'message'     => $message,
            'href'        => function_exists('eottae_talkroom_apply_status_url') ? eottae_talkroom_apply_status_url() : '',
        ));
    }
}

if (!function_exists('eottae_talkroom_notify_join_approved')) {
    function eottae_talkroom_notify_join_approved($room_id, $member_mb_id)
    {
        $room_id = (int) $room_id;
        $member_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $member_mb_id);
        if ($room_id < 1 || $member_mb_id === '') {
            return;
        }

        $room_name = eottae_talkroom_notify_room_name($room_id);
        eottae_talkroom_notify_create($member_mb_id, 'join_approved', array(
            'room_id'     => $room_id,
            'target_type' => 'member',
            'target_id'   => $room_id,
            'title'       => '톡방 참여가 승인되었습니다',
            'message'     => '「'.$room_name.'」 톡방 참여 신청이 승인되었습니다.',
            'href'        => function_exists('eottae_talkroom_enter_url') ? eottae_talkroom_enter_url($room_id) : '',
        ));
    }
}

if (!function_exists('eottae_talkroom_notify_join_rejected')) {
    function eottae_talkroom_notify_join_rejected($room_id, $member_mb_id)
    {
        $room_id = (int) $room_id;
        $member_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $member_mb_id);
        if ($room_id < 1 || $member_mb_id === '') {
            return;
        }

        $room_name = eottae_talkroom_notify_room_name($room_id);
        eottae_talkroom_notify_create($member_mb_id, 'join_rejected', array(
            'room_id'     => $room_id,
            'target_type' => 'member',
            'target_id'   => $room_id,
            'title'       => '톡방 참여가 거절되었습니다',
            'message'     => '「'.$room_name.'」 톡방 참여 신청이 거절되었습니다.',
            'href'        => function_exists('eottae_mypage_talk_url') ? eottae_mypage_talk_url() : '',
        ));
    }
}

if (!function_exists('eottae_talkroom_notify_owner_join_request')) {
    function eottae_talkroom_notify_owner_join_request($room_id, $applicant_mb_id)
    {
        $room_id = (int) $room_id;
        $applicant_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $applicant_mb_id);
        if ($room_id < 1 || $applicant_mb_id === '' || !function_exists('eottae_talkroom_get_room')) {
            return;
        }

        $room = eottae_talkroom_get_room($room_id);
        if (!$room) {
            return;
        }

        $owner_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($room['owner_mb_id'] ?? ''));
        if ($owner_mb_id === '' || $owner_mb_id === $applicant_mb_id) {
            return;
        }

        $room_name = get_text($room['room_name'] ?? '');
        $nick = eottae_talkroom_notify_member_nick($applicant_mb_id);
        eottae_talkroom_notify_create($owner_mb_id, 'owner_join_request', array(
            'room_id'     => $room_id,
            'target_type' => 'member',
            'target_id'   => $room_id,
            'title'       => '새 참여 신청',
            'message'     => '「'.$room_name.'」에 '.$nick.'님의 참여 신청이 접수되었습니다.',
            'href'        => function_exists('eottae_talkroom_owner_manage_url') ? eottae_talkroom_owner_manage_url($room_id) : '',
        ));
    }
}

if (!function_exists('eottae_talkroom_notify_owner_report')) {
    function eottae_talkroom_notify_owner_report($room_id, $report_id, $target_type = '')
    {
        $room_id = (int) $room_id;
        $report_id = (int) $report_id;
        if ($room_id < 1 || $report_id < 1 || !function_exists('eottae_talkroom_get_room')) {
            return;
        }

        $room = eottae_talkroom_get_room($room_id);
        if (!$room) {
            return;
        }

        $owner_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($room['owner_mb_id'] ?? ''));
        if ($owner_mb_id === '') {
            return;
        }

        $room_name = get_text($room['room_name'] ?? '');
        $target_label = $target_type === 'comment' ? '댓글' : '게시글';
        eottae_talkroom_notify_create($owner_mb_id, 'owner_report', array(
            'room_id'     => $room_id,
            'target_type' => 'report',
            'target_id'   => $report_id,
            'title'       => '새 신고 접수',
            'message'     => '「'.$room_name.'」 톡방 '.$target_label.'에 대한 신고가 접수되었습니다.',
            'href'        => function_exists('eottae_talkroom_owner_reports_url') ? eottae_talkroom_owner_reports_url($room_id) : '',
        ));
    }
}

if (!function_exists('eottae_talkroom_notify_unread_count')) {
    function eottae_talkroom_notify_unread_count($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return 0;
        }

        eottae_talkroom_notify_ensure_schema();
        $table = eottae_talkroom_notify_table();
        if (!eottae_talkroom_table_exists($table)) {
            return 0;
        }

        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$table}`
            WHERE mb_id = '".sql_escape_string($mb_id)."'
              AND is_read = 0
        ", false);

        return !empty($row['cnt']) ? (int) $row['cnt'] : 0;
    }
}

if (!function_exists('eottae_talkroom_notify_list')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_notify_list($mb_id, $limit = 30, $offset = 0)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array();
        }

        eottae_talkroom_notify_ensure_schema();
        $table = eottae_talkroom_notify_table();
        if (!eottae_talkroom_table_exists($table)) {
            return array();
        }

        $limit = max(1, min(100, (int) $limit));
        $offset = max(0, (int) $offset);
        $mb_sql = sql_escape_string($mb_id);
        $rooms_table = function_exists('eottae_talkroom_table_names') ? eottae_talkroom_table_names()['rooms'] : '';

        $join = '';
        $room_select = "'' AS room_name";
        if ($rooms_table !== '' && eottae_talkroom_table_exists($rooms_table)) {
            $join = "LEFT JOIN `{$rooms_table}` r ON r.room_id = n.room_id";
            $room_select = 'r.room_name';
        }

        $result = sql_query("
            SELECT n.*, {$room_select}
            FROM `{$table}` n
            {$join}
            WHERE n.mb_id = '{$mb_sql}'
            ORDER BY n.created_at DESC, n.id DESC
            LIMIT {$offset}, {$limit}
        ", false);

        $rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $rows[] = eottae_talkroom_notify_format_row($row);
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_notify_format_row')) {
    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    function eottae_talkroom_notify_format_row(array $row)
    {
        $types = eottae_talkroom_notify_types();
        $type = trim((string) ($row['type'] ?? ''));
        $created_at = trim((string) ($row['created_at'] ?? ''));

        return array(
            'id'          => (int) ($row['id'] ?? 0),
            'type'        => $type,
            'type_label'  => isset($types[$type]) ? $types[$type] : $type,
            'room_id'     => (int) ($row['room_id'] ?? 0),
            'room_name'   => get_text($row['room_name'] ?? ''),
            'target_type' => trim((string) ($row['target_type'] ?? '')),
            'target_id'   => (int) ($row['target_id'] ?? 0),
            'title'       => get_text($row['title'] ?? ''),
            'message'     => get_text($row['message'] ?? ''),
            'href'        => function_exists('eottae_talkroom_sanitize_internal_href')
                ? eottae_talkroom_sanitize_internal_href($row['href'] ?? '', '')
                : trim((string) ($row['href'] ?? '')),
            'is_read'     => !empty($row['is_read']) ? 1 : 0,
            'created_at'  => $created_at,
            'time_label'  => function_exists('eottae_community_relative_time')
                ? eottae_community_relative_time($created_at)
                : ($created_at !== '' ? substr($created_at, 0, 16) : ''),
        );
    }
}

if (!function_exists('eottae_talkroom_notify_get_owned')) {
    /**
     * @return array<string, mixed>|null
     */
    function eottae_talkroom_notify_get_owned($notification_id, $mb_id)
    {
        $notification_id = (int) $notification_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($notification_id < 1 || $mb_id === '') {
            return null;
        }

        eottae_talkroom_notify_ensure_schema();
        $table = eottae_talkroom_notify_table();
        if (!eottae_talkroom_table_exists($table)) {
            return null;
        }

        $row = sql_fetch("
            SELECT *
            FROM `{$table}`
            WHERE id = '{$notification_id}'
              AND mb_id = '".sql_escape_string($mb_id)."'
            LIMIT 1
        ", false);

        return !empty($row['id']) ? $row : null;
    }
}

if (!function_exists('eottae_talkroom_notify_mark_read')) {
    function eottae_talkroom_notify_mark_read($notification_id, $mb_id)
    {
        $row = eottae_talkroom_notify_get_owned($notification_id, $mb_id);
        if (!$row) {
            return array('ok' => false, 'message' => '알림을 찾을 수 없습니다.');
        }

        if (!empty($row['is_read'])) {
            return array('ok' => true, 'message' => '이미 읽은 알림입니다.');
        }

        $table = eottae_talkroom_notify_table();
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        $ok = (bool) sql_query("
            UPDATE `{$table}` SET
                is_read = 1,
                read_at = '{$now}'
            WHERE id = '".(int) $notification_id."'
              AND mb_id = '".sql_escape_string($mb_id)."'
              AND is_read = 0
        ", false);

        return array(
            'ok'      => $ok,
            'message' => $ok ? '읽음 처리되었습니다.' : '읽음 처리에 실패했습니다.',
        );
    }
}

if (!function_exists('eottae_talkroom_notify_mark_all_read')) {
    function eottae_talkroom_notify_mark_all_read($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '잘못된 요청입니다.', 'updated' => 0);
        }

        eottae_talkroom_notify_ensure_schema();
        $table = eottae_talkroom_notify_table();
        if (!eottae_talkroom_table_exists($table)) {
            return array('ok' => false, 'message' => '알림 테이블이 없습니다.', 'updated' => 0);
        }

        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        $mb_sql = sql_escape_string($mb_id);
        sql_query("
            UPDATE `{$table}` SET
                is_read = 1,
                read_at = '{$now}'
            WHERE mb_id = '{$mb_sql}'
              AND is_read = 0
        ", false);

        $updated = 0;
        if (function_exists('sql_affected_rows')) {
            $updated = (int) sql_affected_rows();
        }

        return array(
            'ok'      => true,
            'message' => '모든 알림을 읽음 처리했습니다.',
            'updated' => $updated,
        );
    }
}

if (!function_exists('eottae_talkroom_notify_proc_url')) {
    function eottae_talkroom_notify_proc_url()
    {
        return G5_URL.'/proc/eottae-talkroom-notifications.php';
    }
}
