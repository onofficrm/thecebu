<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_reads_table')) {
    function eottae_talkroom_reads_table()
    {
        if (!function_exists('eottae_talkroom_bootstrap_tables')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }
        eottae_talkroom_bootstrap_tables();
        global $g5;

        if (!isset($g5['sebu_talk_room_reads_table'])) {
            $g5['sebu_talk_room_reads_table'] = G5_TABLE_PREFIX.'sebu_talk_room_reads';
        }

        return $g5['sebu_talk_room_reads_table'];
    }
}

if (!function_exists('eottae_talkroom_reads_ensure_schema')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_talkroom_reads_ensure_schema()
    {
        $table = eottae_talkroom_reads_table();
        $existed = function_exists('eottae_talkroom_table_exists') && eottae_talkroom_table_exists($table);
        $sql = " CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `room_id` int(11) unsigned NOT NULL DEFAULT '0',
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `last_read_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `last_read_post_id` int(11) unsigned NOT NULL DEFAULT '0',
            `last_read_comment_id` int(11) unsigned NOT NULL DEFAULT '0',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_room_member_read` (`room_id`, `mb_id`),
            KEY `idx_mb_id` (`mb_id`),
            KEY `idx_last_read_at` (`last_read_at`)
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

if (!function_exists('eottae_talkroom_reads_can_mark')) {
    function eottae_talkroom_reads_can_mark($room_id, $mb_id)
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($room_id < 1 || $mb_id === '') {
            return false;
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return false;
        }

        if ($mb_id !== '' && $mb_id === ($room['owner_mb_id'] ?? '')) {
            return true;
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        $membership = eottae_talkroom_membership_state($room, $member_row, $mb_id);
        if (!in_array($membership, array('owner', 'active'), true)) {
            return false;
        }

        return eottae_talkroom_can_view_posts($room, $member_row);
    }
}

if (!function_exists('eottae_talkroom_reads_snapshot_ids')) {
    /**
     * @return array{post_id: int, comment_id: int}
     */
    function eottae_talkroom_reads_snapshot_ids($room_id)
    {
        $room_id = (int) $room_id;
        $post_id = 0;
        $comment_id = 0;

        if ($room_id < 1 || !function_exists('eottae_talkroom_write_table')) {
            return array('post_id' => 0, 'comment_id' => 0);
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table)) {
            return array('post_id' => 0, 'comment_id' => 0);
        }

        $visible = eottae_talkroom_post_visible_sql();
        $post_row = sql_fetch("
            SELECT MAX(wr_id) AS max_id
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_1 = '{$room_id}'
              AND {$visible}
        ", false);
        if (!empty($post_row['max_id'])) {
            $post_id = (int) $post_row['max_id'];
        }

        $comment_row = sql_fetch("
            SELECT MAX(c.wr_id) AS max_id
            FROM `{$write_table}` c
            INNER JOIN `{$write_table}` p
                ON p.wr_id = c.wr_parent AND p.wr_is_comment = 0
            WHERE c.wr_is_comment = 1
              AND p.wr_1 = '{$room_id}'
              AND {$visible}
              AND ".eottae_talkroom_post_visible_sql('c')."
        ", false);
        if (!empty($comment_row['max_id'])) {
            $comment_id = (int) $comment_row['max_id'];
        }

        return array(
            'post_id'    => $post_id,
            'comment_id' => $comment_id,
        );
    }
}

if (!function_exists('eottae_talkroom_mark_room_read')) {
    /**
     * @return array{ok: bool, message: string}
     */
    function eottae_talkroom_mark_room_read($room_id, $mb_id)
    {
        eottae_talkroom_reads_ensure_schema();

        $room_id = (int) $room_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($room_id < 1 || $mb_id === '') {
            return array('ok' => false, 'message' => '잘못된 요청입니다.');
        }

        if (!eottae_talkroom_reads_can_mark($room_id, $mb_id)) {
            return array('ok' => false, 'message' => '읽음 처리할 수 없는 톡방입니다.');
        }

        $table = eottae_talkroom_reads_table();
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        $ids = eottae_talkroom_reads_snapshot_ids($room_id);
        $post_id = (int) $ids['post_id'];
        $comment_id = (int) $ids['comment_id'];
        $mb_sql = sql_escape_string($mb_id);

        $ok = (bool) sql_query("
            INSERT INTO `{$table}`
                (`room_id`, `mb_id`, `last_read_at`, `last_read_post_id`, `last_read_comment_id`, `updated_at`)
            VALUES
                ('{$room_id}', '{$mb_sql}', '{$now}', '{$post_id}', '{$comment_id}', '{$now}')
            ON DUPLICATE KEY UPDATE
                `last_read_at` = VALUES(`last_read_at`),
                `last_read_post_id` = VALUES(`last_read_post_id`),
                `last_read_comment_id` = VALUES(`last_read_comment_id`),
                `updated_at` = VALUES(`updated_at`)
        ", false);

        return array(
            'ok'      => $ok,
            'message' => $ok ? '읽음 처리되었습니다.' : '읽음 처리에 실패했습니다.',
        );
    }
}

if (!function_exists('eottae_talkroom_mark_all_rooms_read')) {
    /**
     * @param int[] $room_ids
     * @return array{ok: bool, message: string, updated: int}
     */
    function eottae_talkroom_mark_all_rooms_read($mb_id, array $room_ids)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $room_ids = array_values(array_unique(array_filter(array_map('intval', $room_ids))));
        if ($mb_id === '' || empty($room_ids)) {
            return array('ok' => false, 'message' => '처리할 톡방이 없습니다.', 'updated' => 0);
        }

        $updated = 0;
        foreach ($room_ids as $room_id) {
            $result = eottae_talkroom_mark_room_read($room_id, $mb_id);
            if (!empty($result['ok'])) {
                $updated++;
            }
        }

        if ($updated < 1) {
            return array('ok' => false, 'message' => '읽음 처리할 톡방이 없습니다.', 'updated' => 0);
        }

        return array(
            'ok'      => true,
            'message' => '모든 톡방을 읽음 처리했습니다.',
            'updated' => $updated,
        );
    }
}

if (!function_exists('eottae_talkroom_unread_counts_for_rooms')) {
    /**
     * @param int[] $room_ids
     * @return array{posts: array<int, int>, comments: array<int, int>}
     */
    function eottae_talkroom_unread_counts_for_rooms($mb_id, array $room_ids)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $room_ids = array_values(array_unique(array_filter(array_map('intval', $room_ids))));
        $empty = array('posts' => array(), 'comments' => array());

        if ($mb_id === '' || empty($room_ids)) {
            return $empty;
        }

        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        eottae_talkroom_reads_ensure_schema();

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table)) {
            return $empty;
        }

        $reads_table = eottae_talkroom_reads_table();
        if (!eottae_talkroom_table_exists($reads_table)) {
            return $empty;
        }

        $in = implode(',', $room_ids);
        $mb_sql = sql_escape_string($mb_id);
        $visible = eottae_talkroom_post_visible_sql('w');
        $comment_visible = eottae_talkroom_post_visible_sql('c');
        $parent_visible = eottae_talkroom_post_visible_sql('p');
        $read_cutoff = "IFNULL(r.last_read_at, '0000-00-00 00:00:00')";

        $posts = array();
        $result = sql_query("
            SELECT w.wr_1 AS room_id, COUNT(*) AS cnt
            FROM `{$write_table}` w
            LEFT JOIN `{$reads_table}` r
                ON r.room_id = w.wr_1 AND r.mb_id = '{$mb_sql}'
            WHERE w.wr_is_comment = 0
              AND w.wr_1 IN ({$in})
              AND {$visible}
              AND w.wr_datetime > {$read_cutoff}
            GROUP BY w.wr_1
        ", false);
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $posts[(int) $row['room_id']] = (int) $row['cnt'];
            }
        }

        $comments = array();
        $result = sql_query("
            SELECT p.wr_1 AS room_id, COUNT(*) AS cnt
            FROM `{$write_table}` c
            INNER JOIN `{$write_table}` p
                ON p.wr_id = c.wr_parent AND p.wr_is_comment = 0
            LEFT JOIN `{$reads_table}` r
                ON r.room_id = p.wr_1 AND r.mb_id = '{$mb_sql}'
            WHERE c.wr_is_comment = 1
              AND p.wr_1 IN ({$in})
              AND {$comment_visible}
              AND {$parent_visible}
              AND c.wr_datetime > {$read_cutoff}
            GROUP BY p.wr_1
        ", false);
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $comments[(int) $row['room_id']] = (int) $row['cnt'];
            }
        }

        return array(
            'posts'    => $posts,
            'comments' => $comments,
        );
    }
}

if (!function_exists('eottae_talkroom_unread_totals_for_rooms')) {
    /**
     * @param int[] $room_ids
     * @return array{new_posts: int, new_comments: int}
     */
    function eottae_talkroom_unread_totals_for_rooms($mb_id, array $room_ids)
    {
        $counts = eottae_talkroom_unread_counts_for_rooms($mb_id, $room_ids);

        return array(
            'new_posts'    => array_sum($counts['posts']),
            'new_comments' => array_sum($counts['comments']),
        );
    }
}

if (!function_exists('eottae_talkroom_reads_proc_url')) {
    function eottae_talkroom_reads_proc_url()
    {
        return G5_URL.'/proc/eottae-talkroom-reads.php';
    }
}

if (!function_exists('eottae_talkroom_chat_unread_counts_for_messages')) {
    /**
     * 내 메시지별 아직 읽지 않은 참여자 수 (카카오톡 미읽음 숫자)
     *
     * @param array<int, array{wr_id?:int, mb_id?:string}|int> $specs
     * @return array<int, int> wr_id => unread_count
     */
    function eottae_talkroom_chat_unread_counts_for_messages($room_id, array $specs)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1 || empty($specs)) {
            return array();
        }

        if (!function_exists('eottae_talkroom_list_room_members')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $by_wr = array();
        foreach ($specs as $spec) {
            if (is_int($spec) || (is_string($spec) && ctype_digit($spec))) {
                $wr_id = (int) $spec;
                $sender = '';
            } elseif (is_array($spec)) {
                $wr_id = (int) ($spec['wr_id'] ?? 0);
                $sender = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($spec['mb_id'] ?? ''));
            } else {
                continue;
            }
            if ($wr_id < 1) {
                continue;
            }
            $by_wr[$wr_id] = $sender;
        }

        if (empty($by_wr)) {
            return array();
        }

        $need_sender = array();
        foreach ($by_wr as $wr_id => $sender) {
            if ($sender === '') {
                $need_sender[] = $wr_id;
            }
        }

        if (!empty($need_sender) && function_exists('eottae_talkroom_write_table')) {
            $write_table = eottae_talkroom_write_table();
            if ($write_table !== '') {
                $id_list = implode(',', array_map('intval', $need_sender));
                $result = sql_query("
                    SELECT wr_id, mb_id
                    FROM `{$write_table}`
                    WHERE wr_id IN ({$id_list})
                      AND wr_is_comment = 0
                ", false);
                if ($result) {
                    while ($row = sql_fetch_array($result)) {
                        $wr_id = (int) ($row['wr_id'] ?? 0);
                        if ($wr_id > 0) {
                            $by_wr[$wr_id] = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($row['mb_id'] ?? ''));
                        }
                    }
                }
            }
        }

        $bot_mb_id = '';
        if (is_file(G5_LIB_PATH.'/eottae-talkroom-ai.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
            if (function_exists('eottae_talkroom_ai_get_bot_member')) {
                $bot = eottae_talkroom_ai_get_bot_member();
                $bot_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($bot['mb_id'] ?? ''));
            }
        }

        $recipient_ids = array();
        $members = eottae_talkroom_list_room_members($room_id, 'active', 200);
        foreach ($members as $member_row) {
            $mbid = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($member_row['mb_id'] ?? ''));
            if ($mbid === '' || ($bot_mb_id !== '' && $mbid === $bot_mb_id)) {
                continue;
            }
            $recipient_ids[$mbid] = true;
        }

        $read_map = array();
        $reads_table = eottae_talkroom_reads_table();
        $read_result = sql_query("
            SELECT mb_id, last_read_post_id
            FROM `{$reads_table}`
            WHERE room_id = '{$room_id}'
        ", false);
        if ($read_result) {
            while ($read_row = sql_fetch_array($read_result)) {
                $mbid = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($read_row['mb_id'] ?? ''));
                if ($mbid === '') {
                    continue;
                }
                $read_map[$mbid] = max(0, (int) ($read_row['last_read_post_id'] ?? 0));
            }
        }

        $out = array();
        foreach ($by_wr as $wr_id => $sender) {
            $count = 0;
            foreach (array_keys($recipient_ids) as $mbid) {
                if ($sender !== '' && $mbid === $sender) {
                    continue;
                }
                $last_read = (int) ($read_map[$mbid] ?? 0);
                if ($last_read < $wr_id) {
                    $count++;
                }
            }
            $out[$wr_id] = $count;
        }

        return $out;
    }
}

if (!function_exists('eottae_talkroom_reads_auto_mark_on_view')) {
    function eottae_talkroom_reads_auto_mark_on_view($room_id, $mb_id, array $ctx = array())
    {
        if ($mb_id === '') {
            return;
        }

        $membership = isset($ctx['membership']) ? (string) $ctx['membership'] : '';
        $can_view_posts = !empty($ctx['can_view_posts']);
        if (!$can_view_posts || !in_array($membership, array('owner', 'active'), true)) {
            return;
        }

        eottae_talkroom_mark_room_read((int) $room_id, $mb_id);
    }
}
