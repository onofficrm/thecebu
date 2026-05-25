<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_dashboard_default_stats')) {
    /**
     * @return array<string, int>
     */
    function eottae_talkroom_dashboard_default_stats()
    {
        return array(
            'new_posts'       => 0,
            'new_comments'    => 0,
            'notifications'   => 0,
            'owner_tasks'     => 0,
        );
    }
}

if (!function_exists('eottae_talkroom_dashboard_enrich_room')) {
    /**
     * @param array<string, mixed> $room
     * @param array<string, int>   $unread
     * @return array<string, mixed>
     */
    function eottae_talkroom_dashboard_enrich_room(array $room, $is_owner = false, array $unread = array())
    {
        $room_id = (int) ($room['room_id'] ?? 0);
        $new_posts = isset($unread['posts'][$room_id]) ? (int) $unread['posts'][$room_id] : 0;
        $new_comments = isset($unread['comments'][$room_id]) ? (int) $unread['comments'][$room_id] : 0;

        return array_merge($room, array(
            'is_owner'         => $is_owner ? 1 : 0,
            'new_posts'        => $new_posts,
            'new_comments'     => $new_comments,
            'has_unread'       => ($new_posts + $new_comments) > 0 ? 1 : 0,
            'unread_summary'   => eottae_talkroom_dashboard_unread_summary($new_posts, $new_comments),
            'manage_href'      => $is_owner && function_exists('eottae_talkroom_owner_manage_url')
                ? eottae_talkroom_owner_manage_url($room_id)
                : '',
        ));
    }
}

if (!function_exists('eottae_talkroom_dashboard_unread_summary')) {
    function eottae_talkroom_dashboard_unread_summary($new_posts, $new_comments)
    {
        $new_posts = (int) $new_posts;
        $new_comments = (int) $new_comments;

        if ($new_posts < 1 && $new_comments < 1) {
            return '새 소식 없음';
        }

        $parts = array();
        if ($new_posts > 0) {
            $parts[] = '새 글 '.number_format($new_posts);
        }
        if ($new_comments > 0) {
            $parts[] = '새 댓글 '.number_format($new_comments);
        }

        return implode(' · ', $parts);
    }
}

if (!function_exists('eottae_talkroom_dashboard_post_view_allowed')) {
    /**
     * 대시보드 피드/공지/모임 행 — 회원 열람 권한 재검증
     */
    function eottae_talkroom_dashboard_post_view_allowed($mb_id, array $row)
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) ($row['room_id'] ?? 0);
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($room_id < 1 || $mb_id === '') {
            return false;
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return false;
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        if (function_exists('eottae_talkroom_is_room_owner')
            && eottae_talkroom_is_room_owner($room, $mb_id, $member_row)) {
            return true;
        }

        return function_exists('eottae_talkroom_can_view_posts')
            && eottae_talkroom_can_view_posts($room, $member_row);
    }
}

if (!function_exists('eottae_talkroom_dashboard_feed_default_limit')) {
    function eottae_talkroom_dashboard_feed_default_limit()
    {
        return 20;
    }
}

if (!function_exists('eottae_talkroom_dashboard_feed_room_ids_from_my')) {
    /**
     * @param array<string, array<int, array<string, mixed>>> $my
     * @return int[]
     */
    function eottae_talkroom_dashboard_feed_room_ids_from_my(array $my)
    {
        $room_ids = array();
        foreach ($my['created'] as $room) {
            $room_ids[] = (int) ($room['room_id'] ?? 0);
        }
        foreach ($my['joined'] as $room) {
            if (($room['member_status'] ?? 'active') !== 'active') {
                continue;
            }
            $room_ids[] = (int) ($room['room_id'] ?? 0);
        }

        return array_values(array_unique(array_filter($room_ids)));
    }
}

if (!function_exists('eottae_talkroom_dashboard_feed_thumbnail_url')) {
    function eottae_talkroom_dashboard_feed_thumbnail_url($wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || !function_exists('eottae_talkroom_board_table')) {
            return '';
        }

        $bo_table = preg_replace('/[^a-z0-9_]/', '', eottae_talkroom_board_table());
        if ($bo_table === '') {
            return '';
        }

        if (!function_exists('get_list_thumbnail')) {
            include_once G5_LIB_PATH.'/thumbnail.lib.php';
        }
        if (!function_exists('get_list_thumbnail')) {
            return '';
        }

        $thumb = get_list_thumbnail($bo_table, $wr_id, 160, 160, false, true);
        if (empty($thumb['src'])) {
            return '';
        }

        return function_exists('eottae_map_public_url')
            ? eottae_map_public_url($thumb['src'])
            : (string) $thumb['src'];
    }
}

if (!function_exists('eottae_talkroom_dashboard_format_feed_row')) {
    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    function eottae_talkroom_dashboard_format_feed_row(array $row)
    {
        $room_id = (int) ($row['room_id'] ?? 0);
        $wr_id = (int) ($row['wr_id'] ?? 0);
        if ($room_id < 1 || $wr_id < 1) {
            return array();
        }

        $type_label = function_exists('eottae_talkroom_post_type_label')
            ? eottae_talkroom_post_type_label($row['ca_name'] ?? '')
            : trim((string) ($row['ca_name'] ?? '일반'));
        $category_label = function_exists('eottae_talkroom_category_label')
            ? eottae_talkroom_category_label($row['category'] ?? '')
            : trim((string) ($row['category'] ?? ''));

        $last_read_at = trim((string) ($row['last_read_at'] ?? ''));
        $post_at = trim((string) ($row['wr_datetime'] ?? ''));
        $is_new = ($last_read_at === '' || $last_read_at === '0000-00-00 00:00:00' || ($post_at !== '' && $post_at > $last_read_at));

        $feed_row = array(
            'wr_id'          => $wr_id,
            'room_id'        => $room_id,
            'room_name'      => get_text($row['room_name'] ?? ''),
            'room_emoji'     => get_text(trim((string) ($row['emoji'] ?? '')) !== '' ? $row['emoji'] : '💬'),
            'category'       => get_text($category_label),
            'subject'        => get_text($row['wr_subject'] ?? ''),
            'author'         => get_text($row['wr_name'] ?? ''),
            'type_label'     => $type_label,
            'type_value'     => trim(strip_tags((string) ($row['ca_name'] ?? ''))),
            'type_class'     => function_exists('eottae_community_badge_class')
                ? eottae_community_badge_class($type_label)
                : 'community-badge--default',
            'datetime'       => $post_at,
            'time_label'     => function_exists('eottae_community_relative_time')
                ? eottae_community_relative_time($post_at)
                : ($post_at !== '' ? substr($post_at, 0, 16) : ''),
            'comment_count'  => (int) ($row['wr_comment'] ?? 0),
            'is_new'         => $is_new ? 1 : 0,
            'thumbnail'      => eottae_talkroom_dashboard_feed_thumbnail_url($wr_id),
            'href'           => function_exists('eottae_talkroom_post_view_url')
                ? eottae_talkroom_post_view_url($wr_id, $room_id)
                : '',
        );

        if (function_exists('eottae_talkroom_ai_message_enrich_post_row')) {
            include_once G5_PATH.'/components/eottae/talk-ai-message-ui.php';
            $feed_row = eottae_talkroom_ai_message_enrich_post_row($feed_row);
        }

        return $feed_row;
    }
}

if (!function_exists('eottae_talkroom_dashboard_list_feed')) {
    /**
     * 가입 톡방 통합 피드 — room_id 선별 후 단일 JOIN 쿼리
     *
     * 권장 인덱스 (write_talkroom):
     *   KEY idx_talkroom_room_feed (wr_1, wr_is_comment, wr_datetime)
     *
     * @param array<string, mixed> $options room_id, type, limit, offset
     * @return array<string, mixed>
     */
    function eottae_talkroom_dashboard_list_feed($mb_id, array $room_ids, array $options = array())
    {
        if (!function_exists('eottae_talkroom_board_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $room_ids = array_values(array_unique(array_filter(array_map('intval', $room_ids))));

        $limit = isset($options['limit']) ? (int) $options['limit'] : eottae_talkroom_dashboard_feed_default_limit();
        $limit = max(1, min(50, $limit));
        $offset = max(0, (int) ($options['offset'] ?? 0));
        $filter_room_id = max(0, (int) ($options['room_id'] ?? 0));
        $filter_type = trim(strip_tags((string) ($options['type'] ?? '')));

        $empty = array(
            'items'        => array(),
            'has_more'     => false,
            'next_offset'  => $offset,
            'limit'        => $limit,
            'offset'       => $offset,
            'room_id'      => $filter_room_id,
            'type'         => $filter_type,
        );

        if ($mb_id === '' || empty($room_ids) || !eottae_talkroom_board_exists()) {
            return $empty;
        }

        if ($filter_room_id > 0 && !in_array($filter_room_id, $room_ids, true)) {
            return $empty;
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            return $empty;
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table)) {
            return $empty;
        }

        $reads_table = function_exists('eottae_talkroom_reads_table') ? eottae_talkroom_reads_table() : '';
        $has_reads = $reads_table !== '' && eottae_talkroom_table_exists($reads_table);

        $statuses = eottae_talkroom_operating_statuses();
        $status_sql = array();
        foreach ($statuses as $status) {
            $status_sql[] = "'".sql_real_escape_string($status)."'";
        }
        $status_in = implode(',', $status_sql);

        $room_in = implode(',', $room_ids);
        $visible = eottae_talkroom_post_visible_sql('w');
        $mb_sql = sql_escape_string($mb_id);

        $where = array(
            'w.wr_is_comment = 0',
            $visible,
            "r.status IN ({$status_in})",
            "r.room_id IN ({$room_in})",
        );

        if ($filter_room_id > 0) {
            $where[] = "r.room_id = '{$filter_room_id}'";
        }

        if ($filter_type !== '') {
            $where[] = "w.ca_name = '".sql_escape_string($filter_type)."'";
        }

        $where_sql = implode(' AND ', $where);
        $fetch_limit = $limit + 1;

        $reads_join = '';
        $reads_select = "NULL AS last_read_at";
        if ($has_reads) {
            $reads_join = "LEFT JOIN `{$reads_table}` rd ON rd.room_id = r.room_id AND rd.mb_id = '{$mb_sql}'";
            $reads_select = 'rd.last_read_at';
        }

        $result = sql_query("
            SELECT
                w.wr_id,
                w.wr_subject,
                w.wr_name,
                w.wr_datetime,
                w.wr_comment,
                w.ca_name,
                w.mb_id,
                w.wr_3,
                r.room_id,
                r.room_name,
                r.category,
                r.emoji,
                {$reads_select}
            FROM `{$write_table}` w
            INNER JOIN `{$tables['rooms']}` r
                ON r.room_id = CAST(w.wr_1 AS UNSIGNED)
            {$reads_join}
            WHERE {$where_sql}
            ORDER BY w.wr_datetime DESC, w.wr_id DESC
            LIMIT {$offset}, {$fetch_limit}
        ", false);

        $items = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                if (!eottae_talkroom_dashboard_post_view_allowed($mb_id, $row)) {
                    continue;
                }
                $formatted = eottae_talkroom_dashboard_format_feed_row($row);
                if (!empty($formatted['href'])) {
                    $items[] = $formatted;
                }
            }
        }

        $has_more = count($items) > $limit;
        if ($has_more) {
            $items = array_slice($items, 0, $limit);
        }

        return array(
            'items'       => $items,
            'has_more'    => $has_more,
            'next_offset' => $offset + count($items),
            'limit'       => $limit,
            'offset'      => $offset,
            'room_id'     => $filter_room_id,
            'type'        => $filter_type,
        );
    }
}

if (!function_exists('eottae_talkroom_dashboard_feed_type_options')) {
    /**
     * @param int[] $room_ids
     * @return array<int, array<string, string>>
     */
    function eottae_talkroom_dashboard_feed_type_options(array $room_ids)
    {
        $room_ids = array_values(array_unique(array_filter(array_map('intval', $room_ids))));
        if (empty($room_ids) || !function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return array();
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table)) {
            return array();
        }

        $in = implode(',', $room_ids);
        $visible = eottae_talkroom_post_visible_sql('w');
        $result = sql_query("
            SELECT DISTINCT w.ca_name
            FROM `{$write_table}` w
            WHERE w.wr_is_comment = 0
              AND {$visible}
              AND CAST(w.wr_1 AS UNSIGNED) IN ({$in})
              AND TRIM(w.ca_name) <> ''
            ORDER BY w.ca_name ASC
            LIMIT 30
        ", false);

        $options = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $value = trim(strip_tags((string) ($row['ca_name'] ?? '')));
                if ($value === '') {
                    continue;
                }
                $options[] = array(
                    'value' => $value,
                    'label' => function_exists('eottae_talkroom_post_type_label')
                        ? eottae_talkroom_post_type_label($value)
                        : $value,
                );
            }
        }

        return $options;
    }
}

if (!function_exists('eottae_talkroom_dashboard_feed_proc_url')) {
    function eottae_talkroom_dashboard_feed_proc_url()
    {
        return G5_URL.'/proc/eottae-talkroom-dashboard-feed.php';
    }
}

if (!function_exists('eottae_talkroom_dashboard_notices_proc_url')) {
    function eottae_talkroom_dashboard_notices_proc_url()
    {
        return G5_URL.'/proc/eottae-talkroom-dashboard-notices.php';
    }
}

if (!function_exists('eottae_talkroom_dashboard_notices_default_limit')) {
    function eottae_talkroom_dashboard_notices_default_limit()
    {
        return 5;
    }
}

if (!function_exists('eottae_talkroom_dashboard_meetups_default_limit')) {
    function eottae_talkroom_dashboard_meetups_default_limit()
    {
        return 10;
    }
}

if (!function_exists('eottae_talkroom_dashboard_notice_where_sql')) {
    function eottae_talkroom_dashboard_notice_where_sql($alias = 'w')
    {
        $prefix = $alias !== '' ? $alias.'.' : '';

        return " ({$prefix}ca_name = '공지' OR {$prefix}wr_num < 0) ";
    }
}

if (!function_exists('eottae_talkroom_dashboard_meetup_where_sql')) {
    function eottae_talkroom_dashboard_meetup_where_sql($alias = 'w')
    {
        $prefix = $alias !== '' ? $alias.'.' : '';

        return " (
            {$prefix}ca_name LIKE '%모임%'
            OR {$prefix}wr_subject LIKE '%모임%'
            OR {$prefix}wr_3 = 'ai:meetup_suggest'
            OR {$prefix}ca_name = '모임공지'
        ) ";
    }
}

if (!function_exists('eottae_talkroom_dashboard_parse_meetup_meta')) {
    /**
     * @return array{date_label: string, time_label: string, location: string, sort_date: string, has_date: bool}
     */
    function eottae_talkroom_dashboard_parse_meetup_meta($subject, $content)
    {
        $text = trim(strip_tags((string) $subject.' '.(string) $content));
        $meta = array(
            'date_label' => '',
            'time_label' => '',
            'location'   => '',
            'sort_date'  => '',
            'has_date'   => false,
        );

        if ($text === '') {
            return $meta;
        }

        if (preg_match('/(\d{4})[\.\-\/](\d{1,2})[\.\-\/](\d{1,2})/u', $text, $m)) {
            $meta['date_label'] = sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
            $meta['sort_date'] = $meta['date_label'];
            $meta['has_date'] = true;
        } elseif (preg_match('/(\d{1,2})월\s*(\d{1,2})일/u', $text, $m)) {
            $year = defined('G5_TIME_YMD') ? (int) substr(G5_TIME_YMD, 0, 4) : (int) date('Y');
            $meta['date_label'] = sprintf('%d월 %d일', (int) $m[1], (int) $m[2]);
            $meta['sort_date'] = sprintf('%04d-%02d-%02d', $year, (int) $m[1], (int) $m[2]);
            $meta['has_date'] = true;
        } elseif (preg_match('/이번\s*주\s*(월|화|수|목|금|토|일)요일/u', $text, $m)) {
            $meta['date_label'] = '이번 주 '.$m[1].'요일';
            $meta['has_date'] = false;
        }

        if (preg_match('/(오전|오후)\s*(\d{1,2})\s*시(?:\s*(\d{1,2})\s*분)?/u', $text, $tm)) {
            $meta['time_label'] = $tm[1].' '.(int) $tm[2].'시';
            if (!empty($tm[3])) {
                $meta['time_label'] .= ' '.(int) $tm[3].'분';
            }
        } elseif (preg_match('/(\d{1,2}):(\d{2})/u', $text, $tm)) {
            $meta['time_label'] = sprintf('%02d:%02d', (int) $tm[1], (int) $tm[2]);
        }

        if (preg_match('/장소\s*[:：]\s*([^\n\r\.]+)/u', $text, $lm)) {
            $meta['location'] = trim($lm[1]);
        } elseif (preg_match('/(?:📍|@)\s*([^\n\r\.]+)/u', $text, $lm)) {
            $meta['location'] = trim($lm[1]);
        }

        if ($meta['location'] !== '' && function_exists('cut_str')) {
            $meta['location'] = cut_str($meta['location'], 40, '…');
        }

        return $meta;
    }
}

if (!function_exists('eottae_talkroom_dashboard_filter_meetup_rows')) {
    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_dashboard_filter_meetup_rows(array $rows, $limit = 10)
    {
        $limit = max(1, min(30, (int) $limit));
        $today = defined('G5_TIME_YMD') ? G5_TIME_YMD : date('Y-m-d');
        $recent_cutoff = date('Y-m-d H:i:s', strtotime('-60 days'));

        $dated_upcoming = array();
        $undated_recent = array();

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $subject = (string) ($row['wr_subject'] ?? '');
            $content = (string) ($row['wr_content'] ?? '');
            $meta = eottae_talkroom_dashboard_parse_meetup_meta($subject, $content);
            $row['_meetup_meta'] = $meta;

            if (!empty($meta['has_date']) && $meta['sort_date'] !== '') {
                if ($meta['sort_date'] < $today) {
                    continue;
                }
                $row['_meetup_sort'] = $meta['sort_date'].' 00:00:00';
                $dated_upcoming[] = $row;
                continue;
            }

            $post_at = trim((string) ($row['wr_datetime'] ?? ''));
            if ($post_at === '' || $post_at < $recent_cutoff) {
                continue;
            }
            $row['_meetup_sort'] = $post_at;
            $undated_recent[] = $row;
        }

        usort($dated_upcoming, function ($a, $b) {
            return strcmp((string) ($a['_meetup_sort'] ?? ''), (string) ($b['_meetup_sort'] ?? ''));
        });
        usort($undated_recent, function ($a, $b) {
            return strcmp((string) ($b['_meetup_sort'] ?? ''), (string) ($a['_meetup_sort'] ?? ''));
        });

        $merged = array_merge($dated_upcoming, $undated_recent);

        return array_slice($merged, 0, $limit);
    }
}

if (!function_exists('eottae_talkroom_dashboard_batch_meetup_participation')) {
    /**
     * @param int[] $wr_ids
     * @return array<int, bool>
     */
    function eottae_talkroom_dashboard_batch_meetup_participation($mb_id, array $wr_ids)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $wr_ids = array_values(array_unique(array_filter(array_map('intval', $wr_ids))));
        if ($mb_id === '' || empty($wr_ids) || !function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return array();
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table)) {
            return array();
        }

        $in = implode(',', $wr_ids);
        $mb_sql = sql_escape_string($mb_id);
        $visible = eottae_talkroom_post_visible_sql('c');
        $found = array();

        $result = sql_query("
            SELECT DISTINCT c.wr_parent
            FROM `{$write_table}` c
            WHERE c.wr_is_comment = 1
              AND c.mb_id = '{$mb_sql}'
              AND c.wr_parent IN ({$in})
              AND {$visible}
              AND (
                    c.wr_content LIKE '%참여%'
                 OR c.wr_content LIKE '%참석%'
                 OR c.wr_content LIKE '%갈게%'
                 OR c.wr_content LIKE '%참가%'
                 OR c.wr_content LIKE '%갈래%'
                 OR c.wr_content LIKE '%함께%'
                 OR c.wr_content LIKE '%가요%'
              )
        ", false);

        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $found[(int) ($row['wr_parent'] ?? 0)] = true;
            }
        }

        return $found;
    }
}

if (!function_exists('eottae_talkroom_dashboard_format_notice_row')) {
    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    function eottae_talkroom_dashboard_format_notice_row(array $row)
    {
        $room_id = (int) ($row['room_id'] ?? 0);
        $wr_id = (int) ($row['wr_id'] ?? 0);
        if ($room_id < 1 || $wr_id < 1) {
            return array();
        }

        $last_read_at = trim((string) ($row['last_read_at'] ?? ''));
        $post_at = trim((string) ($row['wr_datetime'] ?? ''));
        $is_unread = ($last_read_at === '' || $last_read_at === '0000-00-00 00:00:00' || ($post_at !== '' && $post_at > $last_read_at));

        return array(
            'wr_id'          => $wr_id,
            'room_id'        => $room_id,
            'room_name'      => get_text($row['room_name'] ?? ''),
            'room_emoji'     => get_text(trim((string) ($row['emoji'] ?? '')) !== '' ? $row['emoji'] : '💬'),
            'subject'        => get_text($row['wr_subject'] ?? ''),
            'author'         => get_text($row['wr_name'] ?? ''),
            'datetime'       => $post_at,
            'time_label'     => function_exists('eottae_community_relative_time')
                ? eottae_community_relative_time($post_at)
                : ($post_at !== '' ? substr($post_at, 0, 16) : ''),
            'is_unread'      => $is_unread ? 1 : 0,
            'is_confirmed'   => $is_unread ? 0 : 1,
            'href'           => function_exists('eottae_talkroom_post_view_url')
                ? eottae_talkroom_post_view_url($wr_id, $room_id)
                : '',
        );
    }
}

if (!function_exists('eottae_talkroom_dashboard_format_meetup_row')) {
    /**
     * @param array<string, mixed> $row
     * @param array<int, bool>     $participation
     * @return array<string, mixed>
     */
    function eottae_talkroom_dashboard_format_meetup_row(array $row, array $participation = array())
    {
        $room_id = (int) ($row['room_id'] ?? 0);
        $wr_id = (int) ($row['wr_id'] ?? 0);
        if ($room_id < 1 || $wr_id < 1) {
            return array();
        }

        $meta = isset($row['_meetup_meta']) && is_array($row['_meetup_meta'])
            ? $row['_meetup_meta']
            : eottae_talkroom_dashboard_parse_meetup_meta($row['wr_subject'] ?? '', $row['wr_content'] ?? '');

        $date_label = trim((string) ($meta['date_label'] ?? ''));
        if ($date_label === '') {
            $date_label = '일정 미정';
        }

        $time_label = trim((string) ($meta['time_label'] ?? ''));
        if ($time_label === '') {
            $time_label = '-';
        }

        $location = trim((string) ($meta['location'] ?? ''));
        if ($location === '') {
            $location = '-';
        }

        $has_participation = !empty($participation[$wr_id]);

        return array(
            'wr_id'               => $wr_id,
            'room_id'             => $room_id,
            'room_name'           => get_text($row['room_name'] ?? ''),
            'room_emoji'          => get_text(trim((string) ($row['emoji'] ?? '')) !== '' ? $row['emoji'] : '💬'),
            'subject'             => get_text($row['wr_subject'] ?? ''),
            'date_label'          => $date_label,
            'time_label'          => $time_label,
            'location'            => get_text($location),
            'comment_count'       => (int) ($row['wr_comment'] ?? 0),
            'has_participation'   => $has_participation ? 1 : 0,
            'participation_label' => $has_participation ? '참여 의사 있음' : '',
            'href'                => function_exists('eottae_talkroom_post_view_url')
                ? eottae_talkroom_post_view_url($wr_id, $room_id)
                : '',
        );
    }
}

if (!function_exists('eottae_talkroom_dashboard_list_notices')) {
    /**
     * @param int[] $room_ids
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    function eottae_talkroom_dashboard_list_notices($mb_id, array $room_ids, array $options = array())
    {
        if (!function_exists('eottae_talkroom_board_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $room_ids = array_values(array_unique(array_filter(array_map('intval', $room_ids))));

        $limit = isset($options['limit']) ? (int) $options['limit'] : eottae_talkroom_dashboard_notices_default_limit();
        $limit = max(1, min(30, $limit));
        $offset = max(0, (int) ($options['offset'] ?? 0));

        $empty = array(
            'items'       => array(),
            'has_more'    => false,
            'next_offset' => $offset,
            'limit'       => $limit,
            'offset'      => $offset,
        );

        if ($mb_id === '' || empty($room_ids) || !eottae_talkroom_board_exists()) {
            return $empty;
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            return $empty;
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table)) {
            return $empty;
        }

        $reads_table = function_exists('eottae_talkroom_reads_table') ? eottae_talkroom_reads_table() : '';
        $has_reads = $reads_table !== '' && eottae_talkroom_table_exists($reads_table);

        $statuses = eottae_talkroom_operating_statuses();
        $status_sql = array();
        foreach ($statuses as $status) {
            $status_sql[] = "'".sql_real_escape_string($status)."'";
        }
        $status_in = implode(',', $status_sql);

        $room_in = implode(',', $room_ids);
        $visible = eottae_talkroom_post_visible_sql('w');
        $notice_where = eottae_talkroom_dashboard_notice_where_sql('w');
        $mb_sql = sql_escape_string($mb_id);
        $fetch_limit = $limit + 1;

        $reads_join = '';
        $reads_select = "NULL AS last_read_at";
        if ($has_reads) {
            $reads_join = "LEFT JOIN `{$reads_table}` rd ON rd.room_id = r.room_id AND rd.mb_id = '{$mb_sql}'";
            $reads_select = 'rd.last_read_at';
        }

        $result = sql_query("
            SELECT
                w.wr_id,
                w.wr_subject,
                w.wr_name,
                w.wr_datetime,
                w.wr_num,
                w.ca_name,
                r.room_id,
                r.room_name,
                r.emoji,
                {$reads_select}
            FROM `{$write_table}` w
            INNER JOIN `{$tables['rooms']}` r
                ON r.room_id = CAST(w.wr_1 AS UNSIGNED)
            {$reads_join}
            WHERE w.wr_is_comment = 0
              AND {$visible}
              AND {$notice_where}
              AND r.status IN ({$status_in})
              AND r.room_id IN ({$room_in})
            ORDER BY w.wr_datetime DESC, w.wr_id DESC
            LIMIT {$offset}, {$fetch_limit}
        ", false);

        $items = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                if (!eottae_talkroom_dashboard_post_view_allowed($mb_id, $row)) {
                    continue;
                }
                $formatted = eottae_talkroom_dashboard_format_notice_row($row);
                if (!empty($formatted['href'])) {
                    $items[] = $formatted;
                }
            }
        }

        $has_more = count($items) > $limit;
        if ($has_more) {
            $items = array_slice($items, 0, $limit);
        }

        return array(
            'items'       => $items,
            'has_more'    => $has_more,
            'next_offset' => $offset + count($items),
            'limit'       => $limit,
            'offset'      => $offset,
        );
    }
}

if (!function_exists('eottae_talkroom_dashboard_list_meetups')) {
    /**
     * @param int[] $room_ids
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    function eottae_talkroom_dashboard_list_meetups($mb_id, array $room_ids, array $options = array())
    {
        if (!function_exists('eottae_talkroom_board_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $room_ids = array_values(array_unique(array_filter(array_map('intval', $room_ids))));

        $limit = isset($options['limit']) ? (int) $options['limit'] : eottae_talkroom_dashboard_meetups_default_limit();
        $limit = max(1, min(30, (int) $limit));

        $empty = array(
            'items' => array(),
            'limit' => $limit,
        );

        if ($mb_id === '' || empty($room_ids) || !eottae_talkroom_board_exists()) {
            return $empty;
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            return $empty;
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table)) {
            return $empty;
        }

        $statuses = eottae_talkroom_operating_statuses();
        $status_sql = array();
        foreach ($statuses as $status) {
            $status_sql[] = "'".sql_real_escape_string($status)."'";
        }
        $status_in = implode(',', $status_sql);

        $room_in = implode(',', $room_ids);
        $visible = eottae_talkroom_post_visible_sql('w');
        $meetup_where = eottae_talkroom_dashboard_meetup_where_sql('w');
        $fetch_limit = max(50, $limit * 5);

        $result = sql_query("
            SELECT
                w.wr_id,
                w.wr_subject,
                w.wr_content,
                w.wr_datetime,
                w.wr_comment,
                w.ca_name,
                w.wr_3,
                r.room_id,
                r.room_name,
                r.emoji
            FROM `{$write_table}` w
            INNER JOIN `{$tables['rooms']}` r
                ON r.room_id = CAST(w.wr_1 AS UNSIGNED)
            WHERE w.wr_is_comment = 0
              AND {$visible}
              AND {$meetup_where}
              AND r.status IN ({$status_in})
              AND r.room_id IN ({$room_in})
            ORDER BY w.wr_datetime DESC, w.wr_id DESC
            LIMIT {$fetch_limit}
        ", false);

        $raw_rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                if (!eottae_talkroom_dashboard_post_view_allowed($mb_id, $row)) {
                    continue;
                }
                $raw_rows[] = $row;
            }
        }

        $filtered = eottae_talkroom_dashboard_filter_meetup_rows($raw_rows, $limit);
        $wr_ids = array();
        foreach ($filtered as $row) {
            $wr_ids[] = (int) ($row['wr_id'] ?? 0);
        }

        $participation = eottae_talkroom_dashboard_batch_meetup_participation($mb_id, $wr_ids);
        $items = array();
        foreach ($filtered as $row) {
            $formatted = eottae_talkroom_dashboard_format_meetup_row($row, $participation);
            if (!empty($formatted['href'])) {
                $items[] = $formatted;
            }
        }

        return array(
            'items' => $items,
            'limit' => $limit,
        );
    }
}

if (!function_exists('eottae_talkroom_dashboard_today_start_sql')) {
    function eottae_talkroom_dashboard_today_start_sql()
    {
        $today = defined('G5_TIME_YMD') ? G5_TIME_YMD : date('Y-m-d');

        return $today.' 00:00:00';
    }
}

if (!function_exists('eottae_talkroom_dashboard_owner_manage_links')) {
    /**
     * @return array<string, string>
     */
    function eottae_talkroom_dashboard_owner_manage_links($room_id)
    {
        $room_id = (int) $room_id;
        $manage = function_exists('eottae_talkroom_owner_manage_url')
            ? eottae_talkroom_owner_manage_url($room_id)
            : G5_URL.'/page/eottae-talk-manage.php?room_id='.$room_id;

        $links = array(
            'manage'  => $manage,
            'pending' => $manage.'#talk-manage-pending',
            'reports' => function_exists('eottae_talkroom_owner_reports_url')
                ? eottae_talkroom_owner_reports_url($room_id)
                : $manage,
            'notice'  => $manage.'#talk-manage-notice',
            'members' => $manage.'#talk-manage-members',
        );

        if (function_exists('eottae_talkroom_ai_settings_url')) {
            $links['ai'] = eottae_talkroom_ai_settings_url($room_id);
        }

        return $links;
    }
}

if (!function_exists('eottae_talkroom_dashboard_owner_ai_status')) {
    /**
     * @return array{available: bool, label: string, enabled: int, class: string}
     */
    function eottae_talkroom_dashboard_owner_ai_status($room_id)
    {
        $room_id = (int) $room_id;
        $default = array(
            'available' => false,
            'label'     => '미설정',
            'enabled'   => 0,
            'class'     => 'my-talk-badge--muted',
        );

        if (!function_exists('eottae_talkroom_ai_table_names')) {
            return $default;
        }

        $tables = eottae_talkroom_ai_table_names();
        if (!function_exists('eottae_talkroom_ai_table_exists')
            || !eottae_talkroom_ai_table_exists($tables['settings'])) {
            return $default;
        }

        $default['available'] = true;
        $row = function_exists('eottae_talkroom_ai_get_settings_row')
            ? eottae_talkroom_ai_get_settings_row($room_id)
            : null;

        if (!$row) {
            return array_merge($default, array(
                'label' => 'OFF',
                'class' => 'my-talk-badge--muted',
            ));
        }

        if (!empty($row['admin_force_disabled'])) {
            return array_merge($default, array(
                'label'   => '관리자 OFF',
                'enabled' => 0,
                'class'   => 'my-talk-badge--muted',
            ));
        }

        if (!empty($row['ai_enabled'])) {
            return array_merge($default, array(
                'label'   => 'ON',
                'enabled' => 1,
                'class'   => 'my-talk-badge--owner',
            ));
        }

        return array_merge($default, array(
            'label'   => 'OFF',
            'enabled' => 0,
            'class'   => 'my-talk-badge--muted',
        ));
    }
}

if (!function_exists('eottae_talkroom_dashboard_batch_owner_member_counts')) {
    /**
     * @param int[] $room_ids
     * @return array{pending: array<int, int>, kicked: array<int, int>}
     */
    function eottae_talkroom_dashboard_batch_owner_member_counts(array $room_ids)
    {
        $room_ids = array_values(array_unique(array_filter(array_map('intval', $room_ids))));
        $empty = array('pending' => array(), 'kicked' => array());
        if (empty($room_ids)) {
            return $empty;
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['members'])) {
            return $empty;
        }

        $in = implode(',', $room_ids);
        $pending = array();
        $kicked = array();

        $result = sql_query("
            SELECT room_id, status, COUNT(*) AS cnt
            FROM `{$tables['members']}`
            WHERE room_id IN ({$in})
              AND status IN ('pending', 'kicked', 'banned')
            GROUP BY room_id, status
        ", false);

        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $room_id = (int) ($row['room_id'] ?? 0);
                $status = trim((string) ($row['status'] ?? ''));
                $cnt = (int) ($row['cnt'] ?? 0);
                if ($room_id < 1 || $cnt < 1) {
                    continue;
                }
                if ($status === 'pending') {
                    $pending[$room_id] = $cnt;
                } elseif (in_array($status, array('kicked', 'banned'), true)) {
                    $kicked[$room_id] = ($kicked[$room_id] ?? 0) + $cnt;
                }
            }
        }

        return array(
            'pending' => $pending,
            'kicked'  => $kicked,
        );
    }
}

if (!function_exists('eottae_talkroom_dashboard_batch_owner_report_counts')) {
    /**
     * @param int[] $room_ids
     * @return array<int, int>
     */
    function eottae_talkroom_dashboard_batch_owner_report_counts(array $room_ids)
    {
        $room_ids = array_values(array_unique(array_filter(array_map('intval', $room_ids))));
        if (empty($room_ids)) {
            return array();
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['reports'])) {
            return array();
        }

        $in = implode(',', $room_ids);
        $counts = array();
        $result = sql_query("
            SELECT room_id, COUNT(*) AS cnt
            FROM `{$tables['reports']}`
            WHERE room_id IN ({$in})
              AND status = 'pending'
            GROUP BY room_id
        ", false);

        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $counts[(int) $row['room_id']] = (int) $row['cnt'];
            }
        }

        return $counts;
    }
}

if (!function_exists('eottae_talkroom_dashboard_batch_owner_today_activity')) {
    /**
     * @param int[] $room_ids
     * @return array{posts: array<int, int>, comments: array<int, int>}
     */
    function eottae_talkroom_dashboard_batch_owner_today_activity(array $room_ids)
    {
        $room_ids = array_values(array_unique(array_filter(array_map('intval', $room_ids))));
        $empty = array('posts' => array(), 'comments' => array());
        if (empty($room_ids) || !function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return $empty;
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table)) {
            return $empty;
        }

        $in = implode(',', $room_ids);
        $today_start = eottae_talkroom_dashboard_today_start_sql();
        $today_sql = sql_escape_string($today_start);
        $visible = eottae_talkroom_post_visible_sql('w');
        $comment_visible = eottae_talkroom_post_visible_sql('c');
        $parent_visible = eottae_talkroom_post_visible_sql('p');

        $posts = array();
        $result = sql_query("
            SELECT w.wr_1 AS room_id, COUNT(*) AS cnt
            FROM `{$write_table}` w
            WHERE w.wr_is_comment = 0
              AND CAST(w.wr_1 AS UNSIGNED) IN ({$in})
              AND {$visible}
              AND w.wr_datetime >= '{$today_sql}'
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
            WHERE c.wr_is_comment = 1
              AND CAST(p.wr_1 AS UNSIGNED) IN ({$in})
              AND {$comment_visible}
              AND {$parent_visible}
              AND c.wr_datetime >= '{$today_sql}'
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

if (!function_exists('eottae_talkroom_dashboard_collect_owner_rooms')) {
    /**
     * owner_mb_id 또는 members.role=owner 기준 운영 중 톡방 카드 목록
     *
     * @param array<string, array<int, array<string, mixed>>> $my
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_dashboard_collect_owner_rooms($mb_id, array $my)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array();
        }

        $rooms_by_id = array();
        foreach ($my['created'] ?? array() as $room) {
            if (!is_array($room)) {
                continue;
            }
            $room_id = (int) ($room['room_id'] ?? 0);
            if ($room_id > 0) {
                $rooms_by_id[$room_id] = $room;
            }
        }

        if (!function_exists('eottae_talkroom_table_names')) {
            return array_values($rooms_by_id);
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['members']) || !eottae_talkroom_table_exists($tables['rooms'])) {
            return array_values($rooms_by_id);
        }

        $exclude_sql = '';
        if (!empty($rooms_by_id)) {
            $exclude_sql = ' AND r.room_id NOT IN ('.implode(',', array_map('intval', array_keys($rooms_by_id))).')';
        }

        $member_table = G5_TABLE_PREFIX.'member';
        $status_in = "'approved','active'";
        $result = sql_query("
            SELECT r.*, m.mb_nick AS owner_nick
            FROM `{$tables['members']}` tm
            INNER JOIN `{$tables['rooms']}` r ON r.room_id = tm.room_id
            LEFT JOIN `{$member_table}` m ON m.mb_id = r.owner_mb_id
            WHERE tm.mb_id = '".sql_escape_string($mb_id)."'
              AND tm.role = 'owner'
              AND tm.status = 'active'
              AND r.status IN ({$status_in})
              {$exclude_sql}
            ORDER BY r.updated_at DESC, r.room_id DESC
        ", false);

        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $room_id = (int) ($row['room_id'] ?? 0);
                if ($room_id < 1 || isset($rooms_by_id[$room_id])) {
                    continue;
                }
                $stats = function_exists('eottae_talkroom_room_stats')
                    ? eottae_talkroom_room_stats($room_id)
                    : array();
                $rooms_by_id[$room_id] = function_exists('eottae_talkroom_format_card')
                    ? eottae_talkroom_format_card($row, $stats)
                    : $row;
            }
        }

        return array_values($rooms_by_id);
    }
}

if (!function_exists('eottae_talkroom_dashboard_build_owner_summaries')) {
    /**
     * @param array<string, array<int, array<string, mixed>>> $my
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_dashboard_build_owner_summaries($mb_id, array $my)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array();
        }

        $owner_rooms = eottae_talkroom_dashboard_collect_owner_rooms($mb_id, $my);
        if (empty($owner_rooms)) {
            return array();
        }

        $rooms = array();
        $room_ids = array();
        foreach ($owner_rooms as $room) {
            $room_id = (int) ($room['room_id'] ?? 0);
            if ($room_id < 1) {
                continue;
            }
            if (!function_exists('eottae_talkroom_can_manage_room')
                || !eottae_talkroom_can_manage_room($room_id, $mb_id, false)) {
                continue;
            }
            $rooms[] = $room;
            $room_ids[] = $room_id;
        }

        if (empty($room_ids)) {
            return array();
        }

        $member_counts = eottae_talkroom_dashboard_batch_owner_member_counts($room_ids);
        $report_counts = eottae_talkroom_dashboard_batch_owner_report_counts($room_ids);
        $today_activity = eottae_talkroom_dashboard_batch_owner_today_activity($room_ids);

        $summaries = array();
        foreach ($rooms as $room) {
            $room_id = (int) ($room['room_id'] ?? 0);
            $pending = (int) ($member_counts['pending'][$room_id] ?? 0);
            $kicked = (int) ($member_counts['kicked'][$room_id] ?? 0);
            $pending_reports = (int) ($report_counts[$room_id] ?? 0);
            $today_posts = (int) ($today_activity['posts'][$room_id] ?? 0);
            $today_comments = (int) ($today_activity['comments'][$room_id] ?? 0);
            $ai = eottae_talkroom_dashboard_owner_ai_status($room_id);
            $links = eottae_talkroom_dashboard_owner_manage_links($room_id);
            $tasks = $pending + $pending_reports;

            $summaries[] = array_merge($room, array(
                'pending_members'  => $pending,
                'pending_reports'  => $pending_reports,
                'kicked_members'   => $kicked,
                'today_posts'      => $today_posts,
                'today_comments'   => $today_comments,
                'owner_tasks'      => $tasks,
                'ai'               => $ai,
                'manage_links'     => $links,
                'has_tasks'        => $tasks > 0 ? 1 : 0,
                'category_label'   => get_text($room['category'] ?? ''),
                'updated_label'    => get_text($room['updated_label'] ?? ''),
            ));
        }

        return $summaries;
    }
}

if (!function_exists('eottae_talkroom_dashboard_owner_tasks_total')) {
    /**
     * @param array<int, array<string, mixed>> $owner_summaries
     */
    function eottae_talkroom_dashboard_owner_tasks_total(array $owner_summaries)
    {
        $total = 0;
        foreach ($owner_summaries as $row) {
            $total += (int) ($row['owner_tasks'] ?? 0);
        }

        return $total;
    }
}

if (!function_exists('eottae_talkroom_dashboard_build_context')) {
    /**
     * @param array<string, mixed> $feed_options
     * @return array<string, mixed>
     */
    function eottae_talkroom_dashboard_build_context($mb_id, array $feed_options = array())
    {
        if (!function_exists('eottae_talkroom_list_my_rooms')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }
        if (!function_exists('eottae_talkroom_unread_counts_for_rooms')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-reads.lib.php';
        }
        if (!function_exists('eottae_talkroom_notify_list')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-notify.lib.php';
        }
        if (!function_exists('eottae_talkroom_bookmark_list')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-bookmarks.lib.php';
        }
        if (!function_exists('collect_my_talk_briefing_data')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-briefing.lib.php';
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $my = $mb_id !== '' ? eottae_talkroom_list_my_rooms($mb_id) : array(
            'created' => array(),
            'joined'  => array(),
            'pending' => array(),
        );

        $room_ids = eottae_talkroom_dashboard_feed_room_ids_from_my($my);

        $unread = $mb_id !== '' && !empty($room_ids)
            ? eottae_talkroom_unread_counts_for_rooms($mb_id, $room_ids)
            : array('posts' => array(), 'comments' => array());

        $totals = eottae_talkroom_unread_totals_for_rooms($mb_id, $room_ids);
        $stats = eottae_talkroom_dashboard_default_stats();
        $stats['new_posts'] = (int) ($totals['new_posts'] ?? 0);
        $stats['new_comments'] = (int) ($totals['new_comments'] ?? 0);
        $stats['notifications'] = $mb_id !== '' ? eottae_talkroom_notify_unread_count($mb_id) : 0;

        $news_rooms = array();
        foreach ($my['created'] as $room) {
            $news_rooms[] = eottae_talkroom_dashboard_enrich_room($room, true, $unread);
        }
        foreach ($my['joined'] as $room) {
            if (($room['member_status'] ?? 'active') !== 'active') {
                continue;
            }
            $news_rooms[] = eottae_talkroom_dashboard_enrich_room($room, false, $unread);
        }

        $owner_summaries = $mb_id !== ''
            ? eottae_talkroom_dashboard_build_owner_summaries($mb_id, $my)
            : array();
        $stats['owner_tasks'] = eottae_talkroom_dashboard_owner_tasks_total($owner_summaries);

        $feed_limit = isset($feed_options['limit']) ? (int) $feed_options['limit'] : eottae_talkroom_dashboard_feed_default_limit();
        $feed_room_id = max(0, (int) ($feed_options['room_id'] ?? 0));
        $feed_type = trim(strip_tags((string) ($feed_options['type'] ?? '')));
        $feed_offset = max(0, (int) ($feed_options['offset'] ?? 0));

        $feed = $mb_id !== ''
            ? eottae_talkroom_dashboard_list_feed($mb_id, $room_ids, array(
                'limit'   => $feed_limit,
                'offset'  => $feed_offset,
                'room_id' => $feed_room_id,
                'type'    => $feed_type,
            ))
            : array('items' => array(), 'has_more' => false, 'next_offset' => 0);

        $notices = $mb_id !== '' && !empty($room_ids)
            ? eottae_talkroom_dashboard_list_notices($mb_id, $room_ids, array(
                'limit'  => eottae_talkroom_dashboard_notices_default_limit(),
                'offset' => 0,
            ))
            : array('items' => array(), 'has_more' => false, 'next_offset' => 0);

        $meetups = $mb_id !== '' && !empty($room_ids)
            ? eottae_talkroom_dashboard_list_meetups($mb_id, $room_ids)
            : array('items' => array());

        $is_super = false;
        if (isset($GLOBALS['is_admin']) && $GLOBALS['is_admin'] === 'super') {
            $is_super = true;
        }
        $bookmarks = $mb_id !== ''
            ? eottae_talkroom_bookmark_list($mb_id, 30, 0, $is_super)
            : array('items' => array(), 'total' => 0);

        $briefing = $mb_id !== ''
            ? collect_my_talk_briefing_data($mb_id, array(
                'my'       => $my,
                'room_ids' => $room_ids,
            ))
            : array('is_empty' => true, 'priority_posts' => array());

        $feed_room_options = array();
        foreach ($news_rooms as $room) {
            $feed_room_options[] = array(
                'room_id'   => (int) ($room['room_id'] ?? 0),
                'room_name' => get_text($room['room_name'] ?? ''),
            );
        }

        $read_token = function_exists('eottae_talkroom_member_token') ? eottae_talkroom_member_token() : '';
        $notifications = $mb_id !== '' ? eottae_talkroom_notify_list($mb_id, 30, 0) : array();

        return array(
            'stats'              => $stats,
            'news_rooms'         => $news_rooms,
            'owner_rooms'        => $owner_summaries,
            'owner_summaries'    => $owner_summaries,
            'feed_items'         => isset($feed['items']) ? $feed['items'] : array(),
            'feed'               => $feed,
            'notice_items'       => isset($notices['items']) ? $notices['items'] : array(),
            'notices'            => $notices,
            'meetup_items'       => isset($meetups['items']) ? $meetups['items'] : array(),
            'meetups'            => $meetups,
            'bookmark_items'     => isset($bookmarks['items']) ? $bookmarks['items'] : array(),
            'bookmarks'          => $bookmarks,
            'bookmarks_proc_url' => function_exists('eottae_talkroom_bookmarks_proc_url')
                ? eottae_talkroom_bookmarks_proc_url()
                : G5_URL.'/proc/eottae-talkroom-bookmarks.php',
            'briefing'           => $briefing,
            'feed_room_options'  => $feed_room_options,
            'feed_type_options'  => eottae_talkroom_dashboard_feed_type_options($room_ids),
            'feed_filters'       => array(
                'room_id' => $feed_room_id,
                'type'    => $feed_type,
            ),
            'notifications'      => $notifications,
            'notify_unread'      => (int) ($stats['notifications'] ?? 0),
            'has_owner'          => !empty($owner_summaries),
            'room_count'         => count($news_rooms),
            'room_ids'           => $room_ids,
            'read_token'         => $read_token,
            'reads_proc_url'     => function_exists('eottae_talkroom_reads_proc_url')
                ? eottae_talkroom_reads_proc_url()
                : G5_URL.'/proc/eottae-talkroom-reads.php',
            'feed_proc_url'      => eottae_talkroom_dashboard_feed_proc_url(),
            'notices_proc_url'   => eottae_talkroom_dashboard_notices_proc_url(),
            'notify_proc_url'    => function_exists('eottae_talkroom_notify_proc_url')
                ? eottae_talkroom_notify_proc_url()
                : G5_URL.'/proc/eottae-talkroom-notifications.php',
            'mypage_talk_url'    => function_exists('eottae_mypage_talk_url')
                ? eottae_mypage_talk_url()
                : G5_URL.'/mypage/talk.php',
        );
    }
}
