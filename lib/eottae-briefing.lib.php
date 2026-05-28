<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_briefing_today_start_sql')) {
    function eottae_briefing_today_start_sql()
    {
        if (function_exists('eottae_talkroom_dashboard_today_start_sql')) {
            return eottae_talkroom_dashboard_today_start_sql();
        }

        $today = defined('G5_TIME_YMD') ? G5_TIME_YMD : date('Y-m-d');

        return $today.' 00:00:00';
    }
}

if (!function_exists('eottae_briefing_tomorrow_end_sql')) {
    function eottae_briefing_tomorrow_end_sql()
    {
        $base = defined('G5_TIME_YMD') ? G5_TIME_YMD : date('Y-m-d');

        return date('Y-m-d', strtotime($base.' +2 day')).' 00:00:00';
    }
}

if (!function_exists('eottae_briefing_admin_notice')) {
    function eottae_briefing_admin_notice()
    {
        if (!function_exists('g5site_cfg')) {
            return '';
        }

        return trim(strip_tags((string) g5site_cfg('sebu_briefing_notice', '')));
    }
}

if (!function_exists('eottae_briefing_board_today_count')) {
    function eottae_briefing_board_today_count($bo_table)
    {
        if (function_exists('eottae_community_today_count')) {
            return (int) eottae_community_today_count($bo_table);
        }

        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return 0;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $today = sql_escape_string(eottae_briefing_today_start_sql());
        $row = sql_fetch(" select count(*) as cnt from {$write_table}
            where wr_is_comment = 0 and wr_datetime >= '{$today}' ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_briefing_talkroom_today_stats')) {
    /**
     * @return array{posts: int, comments: int}
     */
    function eottae_briefing_talkroom_today_stats()
    {
        $empty = array('posts' => 0, 'comments' => 0);

        if (!function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return $empty;
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || !function_exists('eottae_talkroom_table_exists') || !eottae_talkroom_table_exists($write_table)) {
            return $empty;
        }

        $today = sql_escape_string(eottae_briefing_today_start_sql());
        $visible = eottae_talkroom_post_visible_sql('w');

        $row = sql_fetch("
            SELECT
                SUM(CASE WHEN w.wr_is_comment = 0 THEN 1 ELSE 0 END) AS posts,
                SUM(CASE WHEN w.wr_is_comment = 1 THEN 1 ELSE 0 END) AS comments
            FROM `{$write_table}` w
            WHERE w.wr_datetime >= '{$today}'
              AND {$visible}
        ", false);

        return array(
            'posts'    => (int) ($row['posts'] ?? 0),
            'comments' => (int) ($row['comments'] ?? 0),
        );
    }
}

if (!function_exists('eottae_briefing_used_today_count')) {
    function eottae_briefing_used_today_count()
    {
        if (!function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return 0;
        }

        $write_table = eottae_talkroom_write_table();
        $tables = eottae_talkroom_table_names();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table) || !eottae_talkroom_table_exists($tables['rooms'])) {
            return 0;
        }

        $today = sql_escape_string(eottae_briefing_today_start_sql());
        $visible = eottae_talkroom_post_visible_sql('w');
        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$write_table}` w
            INNER JOIN `{$tables['rooms']}` r ON r.room_id = CAST(w.wr_1 AS UNSIGNED)
            WHERE w.wr_is_comment = 0
              AND w.wr_datetime >= '{$today}'
              AND {$visible}
              AND r.category = 'used'
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_briefing_used_today_highlights')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_briefing_used_today_highlights($limit = 2)
    {
        $limit = max(1, min(5, (int) $limit));
        $rows = array();

        if (!function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return $rows;
        }

        $write_table = eottae_talkroom_write_table();
        $tables = eottae_talkroom_table_names();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table) || !eottae_talkroom_table_exists($tables['rooms'])) {
            return $rows;
        }

        $today = sql_escape_string(eottae_briefing_today_start_sql());
        $visible = eottae_talkroom_post_visible_sql('w');
        $result = sql_query("
            SELECT w.wr_id, w.wr_subject, r.room_id, r.room_name
            FROM `{$write_table}` w
            INNER JOIN `{$tables['rooms']}` r ON r.room_id = CAST(w.wr_1 AS UNSIGNED)
            WHERE w.wr_is_comment = 0
              AND w.wr_datetime >= '{$today}'
              AND {$visible}
              AND r.category = 'used'
            ORDER BY w.wr_id DESC
            LIMIT {$limit}
        ", false);

        if (!$result) {
            return $rows;
        }

        while ($row = sql_fetch_array($result)) {
            $room_id = (int) ($row['room_id'] ?? 0);
            $wr_id = (int) ($row['wr_id'] ?? 0);
            $rows[] = array(
                'title'     => get_text($row['wr_subject'] ?? ''),
                'room_name' => get_text($row['room_name'] ?? ''),
                'url'       => function_exists('eottae_talkroom_post_view_url')
                    ? eottae_talkroom_post_view_url($wr_id, $room_id)
                    : '',
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_briefing_talkroom_active_rooms_today')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_briefing_talkroom_active_rooms_today($limit = 3)
    {
        $limit = max(1, min(5, (int) $limit));
        $rows = array();

        if (!function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return $rows;
        }

        $write_table = eottae_talkroom_write_table();
        $tables = eottae_talkroom_table_names();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table) || !eottae_talkroom_table_exists($tables['rooms'])) {
            return $rows;
        }

        $today = sql_escape_string(eottae_briefing_today_start_sql());
        $visible = eottae_talkroom_post_visible_sql('w');
        $statuses = eottae_talkroom_operating_statuses();
        $status_sql = array();
        foreach ($statuses as $status) {
            $status_sql[] = "'".sql_real_escape_string($status)."'";
        }
        $status_in = implode(',', $status_sql);

        $result = sql_query("
            SELECT r.room_id, r.room_name, r.emoji, r.category,
                   COUNT(*) AS activity,
                   MAX(w.wr_subject) AS latest_subject
            FROM `{$write_table}` w
            INNER JOIN `{$tables['rooms']}` r ON r.room_id = CAST(w.wr_1 AS UNSIGNED)
            WHERE w.wr_datetime >= '{$today}'
              AND {$visible}
              AND r.status IN ({$status_in})
              AND r.visibility = 'public'
            GROUP BY r.room_id, r.room_name, r.emoji, r.category
            ORDER BY activity DESC, r.room_id DESC
            LIMIT {$limit}
        ", false);

        if (!$result) {
            return $rows;
        }

        while ($row = sql_fetch_array($result)) {
            $subject = get_text($row['latest_subject'] ?? '');
            if (function_exists('cut_str') && $subject !== '') {
                $subject = cut_str(strip_tags($subject), 24, '…');
            }
            $rows[] = array(
                'room_id'   => (int) ($row['room_id'] ?? 0),
                'room_name' => get_text($row['room_name'] ?? ''),
                'emoji'     => function_exists('eottae_talkroom_display_emoji')
                    ? eottae_talkroom_display_emoji($row['emoji'] ?? '', $row['category'] ?? '')
                    : '',
                'activity'  => (int) ($row['activity'] ?? 0),
                'subject'   => $subject,
                'url'       => function_exists('eottae_talkroom_enter_url')
                    ? eottae_talkroom_enter_url((int) ($row['room_id'] ?? 0))
                    : (function_exists('eottae_talkroom_list_url') ? eottae_talkroom_list_url() : G5_URL.'/talk/'),
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_briefing_event_count_today')) {
    function eottae_briefing_event_count_today()
    {
        $count = 0;

        if (function_exists('eottae_get_events')) {
            $today = defined('G5_TIME_YMD') ? G5_TIME_YMD : date('Y-m-d');
            foreach (eottae_get_events(30) as $event) {
                $dt = isset($event['datetime']) ? substr((string) $event['datetime'], 0, 10) : '';
                if ($dt === $today) {
                    $count++;
                }
            }
        }

        if ($count < 1 && defined('EOTTae_EVENT_TABLE')) {
            $count = eottae_briefing_board_today_count(EOTTae_EVENT_TABLE);
        }

        return $count;
    }
}

if (!function_exists('eottae_briefing_calendar_counts')) {
    /**
     * @return array{today: int, tomorrow: int, today_events: array<int, array<string, mixed>>}
     */
    function eottae_briefing_calendar_counts()
    {
        if (!function_exists('eottae_calendar_summary_days')) {
            include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
        }

        $summary = eottae_calendar_summary_days(date('Y-m-d'), '');
        $today = 0;
        $tomorrow = 0;
        $today_events = array();

        foreach ($summary as $idx => $day) {
            if ($idx === 0) {
                $today = (int) ($day['count'] ?? 0);
                foreach (($day['events'] ?? array()) as $event) {
                    if (!is_array($event)) {
                        continue;
                    }
                    $today_events[] = array(
                        'title'    => get_text($event['title'] ?? ''),
                        'location' => get_text($event['location'] ?? ''),
                        'url'      => get_text($event['detail_href'] ?? ''),
                    );
                    if (count($today_events) >= 3) {
                        break;
                    }
                }
            } elseif ($idx === 1) {
                $tomorrow = (int) ($day['count'] ?? 0);
            }
        }

        return array(
            'today'        => $today,
            'tomorrow'     => $tomorrow,
            'today_events' => $today_events,
        );
    }
}

if (!function_exists('eottae_briefing_popular_posts')) {
    /**
     * @return array{top: array<int, array<string, mixed>>, hit: array<int, array<string, mixed>>, comment: array<int, array<string, mixed>>, surge: array<int, array<string, mixed>>}
     */
    function eottae_briefing_popular_posts($limit = 3)
    {
        if (!function_exists('eottae_api_get_community_posts')) {
            include_once G5_LIB_PATH.'/eottae-api.lib.php';
        }

        $limit = max(1, min(5, (int) $limit));

        $surge = array();
        if (!function_exists('eottae_public_ai_collect_community_surge_posts') && is_file(G5_LIB_PATH.'/eottae-public-ai-generator.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-generator.lib.php';
        }
        if (function_exists('eottae_public_ai_collect_community_surge_posts')) {
            $surge = eottae_public_ai_collect_community_surge_posts($limit);
        }

        return array(
            'top'     => eottae_api_get_community_posts($limit, '', 'hit'),
            'hit'     => eottae_api_get_community_posts($limit, '', 'hit'),
            'comment' => eottae_api_get_community_posts($limit, '', 'comment'),
            'surge'   => $surge,
        );
    }
}

if (!function_exists('eottae_briefing_urls')) {
    /**
     * @return array<string, string>
     */
    function eottae_briefing_urls()
    {
        $job_table = defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job';
        $plaza_table = function_exists('eottae_plaza_board_table') ? eottae_plaza_board_table() : 'plaza';
        $community_table = function_exists('eottae_api_community_table') ? eottae_api_community_table() : 'community';

        return array(
            'calendar'  => function_exists('eottae_calendar_list_url') ? eottae_calendar_list_url() : G5_URL.'/calendar/',
            'talk'      => function_exists('eottae_talkroom_list_url') ? eottae_talkroom_list_url() : G5_URL.'/talk/',
            'plaza'     => function_exists('eottae_plaza_list_url') ? eottae_plaza_list_url() : G5_BBS_URL.'/board.php?bo_table='.$plaza_table,
            'community' => G5_BBS_URL.'/board.php?bo_table='.$community_table,
            'job'       => G5_BBS_URL.'/board.php?bo_table='.$job_table,
            'events'    => G5_URL.'/page/eottae-events.php',
            'used'      => function_exists('eottae_talkroom_list_url') ? eottae_talkroom_list_url() : G5_URL.'/talk/',
        );
    }
}

if (!function_exists('collect_today_sebu_briefing_data')) {
    function collect_today_sebu_briefing_data()
    {
        $calendar = eottae_briefing_calendar_counts();
        $talk = eottae_briefing_talkroom_today_stats();
        $plaza_table = function_exists('eottae_plaza_board_table') ? eottae_plaza_board_table() : 'plaza';
        $job_table = defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job';
        $popular = eottae_briefing_popular_posts(3);

        return array(
            'generated_at' => defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'),
            'counts'       => array(
                'calendar_today'    => (int) ($calendar['today'] ?? 0),
                'calendar_tomorrow' => (int) ($calendar['tomorrow'] ?? 0),
                'talk_posts'        => (int) ($talk['posts'] ?? 0),
                'talk_comments'     => (int) ($talk['comments'] ?? 0),
                'plaza_posts'       => eottae_briefing_board_today_count($plaza_table),
                'used_posts'        => eottae_briefing_used_today_count(),
                'job_posts'         => eottae_briefing_board_today_count($job_table),
                'events'            => eottae_briefing_event_count_today(),
            ),
            'calendar_events' => $calendar['today_events'] ?? array(),
            'talk_rooms'      => eottae_briefing_talkroom_active_rooms_today(3),
            'used_highlights' => eottae_briefing_used_today_highlights(2),
            'popular'         => $popular,
            'admin_notice'    => eottae_briefing_admin_notice(),
            'urls'            => eottae_briefing_urls(),
        );
    }
}

if (!function_exists('eottae_briefing_generate_via_openai')) {
    /**
     * TODO: OpenAI API 연결 위치 — 자연어 브리핑 생성
     *
     * @param array<string, mixed> $data
     * @param string               $scope today|my
     * @return string[]|string|null
     */
    function eottae_briefing_generate_via_openai(array $data, $scope = 'today')
    {
        return null;
    }
}

if (!function_exists('eottae_briefing_generate_template_text_today')) {
    /**
     * TODO: 브리핑 문장 생성 고도화 위치 — 규칙/템플릿 엔진
     *
     * @param array<string, mixed> $data
     * @return string[]
     */
    function eottae_briefing_generate_template_text_today(array $data)
    {
        $counts = isset($data['counts']) && is_array($data['counts']) ? $data['counts'] : array();
        $lines = array();

        $schedule = (int) ($counts['calendar_today'] ?? 0);
        $talk_posts = (int) ($counts['talk_posts'] ?? 0);
        $talk_comments = (int) ($counts['talk_comments'] ?? 0);

        if ($schedule + $talk_posts + $talk_comments > 0) {
            $lines[] = sprintf(
                '오늘 세부어때에는 새 일정 %d개, 세부톡 새 글 %d개, 댓글 %d개가 올라왔습니다.',
                $schedule,
                $talk_posts,
                $talk_comments
            );
        } else {
            $lines[] = '오늘 세부어때에서 확인할 커뮤니티 소식을 모았습니다.';
        }

        $rooms = isset($data['talk_rooms']) && is_array($data['talk_rooms']) ? $data['talk_rooms'] : array();
        if (!empty($rooms[0]['room_name'])) {
            $room_line = get_text($rooms[0]['room_name']);
            if (!empty($rooms[0]['subject'])) {
                $lines[] = sprintf('%s에서는 %s 이야기가 진행 중입니다.', $room_line, get_text($rooms[0]['subject']));
            } else {
                $lines[] = sprintf('%s에서 오늘 활동이 활발합니다.', $room_line);
            }
        }

        if (!empty($rooms[1]['room_name'])) {
            $lines[] = sprintf('%s에서도 새 글이 이어지고 있습니다.', get_text($rooms[1]['room_name']));
        }

        $used = (int) ($counts['used_posts'] ?? 0);
        if ($used > 0) {
            $used_highlights = isset($data['used_highlights']) && is_array($data['used_highlights'])
                ? $data['used_highlights']
                : array();
            if (!empty($used_highlights[0]['title'])) {
                $lines[] = sprintf(
                    '중고거래방에는 %s 등 새 판매글이 %d개 올라왔습니다.',
                    get_text($used_highlights[0]['title']),
                    $used
                );
            } else {
                $lines[] = sprintf('중고거래방에 새 글이 %d개 올라왔습니다.', $used);
            }
        }

        $job = (int) ($counts['job_posts'] ?? 0);
        if ($job > 0) {
            $lines[] = sprintf('구인구직 게시판에 새 글이 %d개 등록되었습니다.', $job);
        }

        $events = (int) ($counts['events'] ?? 0);
        if ($events > 0) {
            $lines[] = sprintf('업체 이벤트·기획전 %d건을 확인해보세요.', $events);
        }

        $calendar_events = isset($data['calendar_events']) && is_array($data['calendar_events'])
            ? $data['calendar_events']
            : array();
        if (!empty($calendar_events[0]['title'])) {
            $event_title = get_text($calendar_events[0]['title']);
            $lines[] = sprintf('오늘 일정으로 %s이(가) 눈에 띕니다.', $event_title);
        }

        if (!empty($data['admin_notice'])) {
            $lines[] = get_text($data['admin_notice']);
        }

        return array_slice($lines, 0, 6);
    }
}

if (!function_exists('generate_today_sebu_briefing_text')) {
    /**
     * @param array<string, mixed> $data
     * @return string[]
     */
    function generate_today_sebu_briefing_text(array $data)
    {
        $ai = eottae_briefing_generate_via_openai($data, 'today');
        if (is_array($ai) && !empty($ai)) {
            return array_slice(array_values(array_filter(array_map('trim', $ai))), 0, 6);
        }
        if (is_string($ai) && trim($ai) !== '') {
            return array_slice(preg_split('/\R+/u', trim($ai)), 0, 6);
        }

        return eottae_briefing_generate_template_text_today($data);
    }
}

if (!function_exists('eottae_briefing_summary_line_today')) {
    function eottae_briefing_summary_line_today(array $data)
    {
        $lines = generate_today_sebu_briefing_text($data);
        $highlights = array();
        $rooms = isset($data['talk_rooms']) && is_array($data['talk_rooms']) ? $data['talk_rooms'] : array();
        $calendar_events = isset($data['calendar_events']) && is_array($data['calendar_events']) ? $data['calendar_events'] : array();

        if (!empty($rooms[0]['room_name'])) {
            $highlights[] = get_text($rooms[0]['room_name']).' 모임';
        }
        if (!empty($calendar_events[0]['title'])) {
            $highlights[] = get_text($calendar_events[0]['title']);
        }
        if (!empty($calendar_events[1]['title'])) {
            $highlights[] = get_text($calendar_events[1]['title']).' 이벤트';
        }

        if (!empty($highlights)) {
            return '오늘은 '.implode('과 ', array_slice($highlights, 0, 2)).'가 눈에 띕니다.';
        }

        return !empty($lines[1]) ? $lines[1] : (!empty($lines[0]) ? $lines[0] : '오늘 세부 커뮤니티 소식을 확인해보세요.');
    }
}

if (!function_exists('eottae_briefing_stat_cards_today')) {
    /**
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    function eottae_briefing_stat_cards_today(array $data)
    {
        $counts = isset($data['counts']) && is_array($data['counts']) ? $data['counts'] : array();
        $urls = isset($data['urls']) && is_array($data['urls']) ? $data['urls'] : eottae_briefing_urls();

        return array(
            array('key' => 'calendar_today', 'icon' => 'calendar', 'label' => '오늘 일정', 'value' => (int) ($counts['calendar_today'] ?? 0), 'url' => $urls['calendar'] ?? '#'),
            array('key' => 'calendar_tomorrow', 'icon' => 'calendar', 'label' => '내일 일정', 'value' => (int) ($counts['calendar_tomorrow'] ?? 0), 'url' => $urls['calendar'] ?? '#'),
            array('key' => 'talk_posts', 'icon' => 'talk', 'label' => '세부톡 새 글', 'value' => (int) ($counts['talk_posts'] ?? 0), 'url' => $urls['talk'] ?? '#'),
            array('key' => 'plaza_posts', 'icon' => 'plaza', 'label' => '세부광장', 'value' => (int) ($counts['plaza_posts'] ?? 0), 'url' => $urls['plaza'] ?? '#'),
            array('key' => 'used_posts', 'icon' => 'used', 'label' => '중고거래', 'value' => (int) ($counts['used_posts'] ?? 0), 'url' => $urls['used'] ?? '#'),
            array('key' => 'job_posts', 'icon' => 'job', 'label' => '구인구직', 'value' => (int) ($counts['job_posts'] ?? 0), 'url' => $urls['job'] ?? '#'),
            array('key' => 'events', 'icon' => 'event', 'label' => '업체 이벤트', 'value' => (int) ($counts['events'] ?? 0), 'url' => $urls['events'] ?? '#'),
        );
    }
}

if (!function_exists('render_today_sebu_briefing')) {
    /**
     * @param array<string, mixed>|null $data
     */
    function render_today_sebu_briefing($data = null)
    {
        if (!is_array($data)) {
            $data = collect_today_sebu_briefing_data();
        }

        $lines = generate_today_sebu_briefing_text($data);
        $summary = eottae_briefing_summary_line_today($data);
        $cards = eottae_briefing_stat_cards_today($data);
        $popular = isset($data['popular']['top']) && is_array($data['popular']['top']) ? $data['popular']['top'] : array();

        include G5_PATH.'/components/eottae/sebu-briefing-main.php';
    }
}

if (!function_exists('eottae_briefing_my_post_comments_today')) {
    function eottae_briefing_my_post_comments_today($mb_id)
    {
        global $g5;

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return 0;
        }

        $today = sql_escape_string(eottae_briefing_today_start_sql());
        $mb_sql = sql_escape_string($mb_id);
        $total = 0;

        if (function_exists('eottae_talkroom_board_exists') && eottae_talkroom_board_exists()) {
            $write_table = eottae_talkroom_write_table();
            if ($write_table !== '' && eottae_talkroom_table_exists($write_table)) {
                $visible = eottae_talkroom_post_visible_sql('c');
                $parent_visible = eottae_talkroom_post_visible_sql('p');
                $row = sql_fetch("
                    SELECT COUNT(*) AS cnt
                    FROM `{$write_table}` c
                    INNER JOIN `{$write_table}` p ON p.wr_id = c.wr_parent
                    WHERE c.wr_is_comment = 1
                      AND c.wr_datetime >= '{$today}'
                      AND {$visible}
                      AND p.mb_id = '{$mb_sql}'
                      AND p.wr_is_comment = 0
                      AND {$parent_visible}
                ", false);
                $total += (int) ($row['cnt'] ?? 0);
            }
        }

        $community_table = function_exists('eottae_api_community_table') ? eottae_api_community_table() : 'community';
        $community_write = $g5['write_prefix'].$community_table;
        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM {$community_write} c
            INNER JOIN {$community_write} p ON p.wr_id = c.wr_parent
            WHERE c.wr_is_comment = 1
              AND c.wr_datetime >= '{$today}'
              AND p.mb_id = '{$mb_sql}'
              AND p.wr_is_comment = 0
        ", false);
        $total += (int) ($row['cnt'] ?? 0);

        return $total;
    }
}

if (!function_exists('eottae_briefing_my_saved_count')) {
    function eottae_briefing_my_saved_count($mb_id)
    {
        global $g5;

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return 0;
        }

        $mb_sql = sql_escape_string($mb_id);
        $row = sql_fetch(" select count(*) as cnt from {$g5['scrap_table']} where mb_id = '{$mb_sql}' ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('collect_my_sebu_briefing_data')) {
    function collect_my_sebu_briefing_data($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array(
                'mb_id'        => '',
                'member_nick'  => '',
                'is_empty'     => true,
                'generated_at' => defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'),
            );
        }

        if (!function_exists('collect_my_talk_briefing_data')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-briefing.lib.php';
        }

        $talk = collect_my_talk_briefing_data($mb_id);
        $bookmarks = array();
        if (function_exists('eottae_talkroom_bookmark_list')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-bookmarks.lib.php';
            $bookmark_result = eottae_talkroom_bookmark_list($mb_id, 5, 0, false);
            $bookmarks = isset($bookmark_result['items']) && is_array($bookmark_result['items'])
                ? $bookmark_result['items']
                : array();
        }

        $member = get_member($mb_id);
        $nick = trim((string) ($member['mb_nick'] ?? ''));
        if ($nick === '') {
            $nick = $mb_id;
        }

        $my_comments = eottae_briefing_my_post_comments_today($mb_id);
        $saved_count = eottae_briefing_my_saved_count($mb_id);

        return array_merge($talk, array(
            'member_nick'           => get_text($nick),
            'my_post_comments'      => $my_comments,
            'saved_count'           => $saved_count,
            'saved_bookmarks'       => $bookmarks,
            'owner_pending_total' => (int) ($talk['owner_pending_join'] ?? 0),
            'meetup_count'          => (int) ($talk['meetup_count'] ?? 0),
            'urls'                  => eottae_briefing_urls(),
            'mypage_talk_url'       => function_exists('eottae_mypage_talk_url')
                ? eottae_mypage_talk_url()
                : G5_URL.'/mypage/talk.php',
        ));
    }
}

if (!function_exists('eottae_briefing_generate_template_text_my')) {
    /**
     * @param array<string, mixed> $data
     * @return string[]
     */
    function eottae_briefing_generate_template_text_my(array $data)
    {
        if (!empty($data['is_empty']) || (int) ($data['room_count'] ?? 0) < 1) {
            return array(
                '아직 참여 중인 톡방이 없습니다.',
                '관심 있는 세부톡방에 참여하면 개인 브리핑이 제공됩니다.',
            );
        }

        $nick = get_text($data['member_nick'] ?? '회원');
        $lines = array();

        $lines[] = sprintf(
            '%s님이 가입한 %d개 톡방에서 오늘 새 글 %d개, 댓글 %d개가 올라왔습니다.',
            $nick,
            (int) ($data['room_count'] ?? 0),
            (int) ($data['today_posts'] ?? 0),
            (int) ($data['today_comments'] ?? 0)
        );

        $top_rooms = isset($data['top_rooms']) && is_array($data['top_rooms']) ? $data['top_rooms'] : array();
        foreach (array_slice($top_rooms, 0, 2) as $room) {
            $room_name = get_text($room['room_name'] ?? '');
            $subject = get_text($room['highlight_subject'] ?? '');
            if ($room_name === '') {
                continue;
            }
            if ($subject !== '') {
                $lines[] = sprintf('%s에서는 %s 이야기가 있습니다.', $room_name, $subject);
            } else {
                $lines[] = sprintf('%s에서 활동이 이어지고 있습니다.', $room_name);
            }
        }

        if ((int) ($data['my_post_comments'] ?? 0) > 0) {
            $lines[] = sprintf('내 글에 달린 오늘의 댓글이 %d개 있습니다.', (int) $data['my_post_comments']);
        }

        if ((int) ($data['owner_pending_join'] ?? 0) > 0) {
            $lines[] = sprintf('방장으로 승인 대기 중인 참여 신청이 %d건 있습니다.', (int) $data['owner_pending_join']);
        }

        if ((int) ($data['meetup_count'] ?? 0) > 0) {
            $lines[] = sprintf('참여 중인 모임 일정 관련 글이 %d개 올라왔습니다.', (int) $data['meetup_count']);
        }

        if ((int) ($data['saved_count'] ?? 0) > 0 && count($lines) < 5) {
            $lines[] = sprintf('저장한 글 %d개를 다시 확인해보세요.', (int) $data['saved_count']);
        }

        return array_slice($lines, 0, 5);
    }
}

if (!function_exists('generate_my_sebu_briefing_text')) {
    /**
     * @param array<string, mixed> $data
     * @return string[]
     */
    function generate_my_sebu_briefing_text(array $data)
    {
        $ai = eottae_briefing_generate_via_openai($data, 'my');
        if (is_array($ai) && !empty($ai)) {
            return array_slice(array_values(array_filter(array_map('trim', $ai))), 0, 5);
        }
        if (is_string($ai) && trim($ai) !== '') {
            return array_slice(preg_split('/\R+/u', trim($ai)), 0, 5);
        }

        return eottae_briefing_generate_template_text_my($data);
    }
}

if (!function_exists('eottae_briefing_stat_cards_my')) {
    /**
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    function eottae_briefing_stat_cards_my(array $data)
    {
        $urls = isset($data['urls']) && is_array($data['urls']) ? $data['urls'] : eottae_briefing_urls();
        $talk_url = $data['mypage_talk_url'] ?? ($urls['talk'] ?? '#');

        return array(
            array('key' => 'talk_posts', 'icon' => 'talk', 'label' => '내 톡방 새 글', 'value' => (int) ($data['today_posts'] ?? 0), 'url' => $talk_url),
            array('key' => 'talk_comments', 'icon' => 'comment', 'label' => '내 톡방 새 댓글', 'value' => (int) ($data['today_comments'] ?? 0), 'url' => $talk_url),
            array('key' => 'my_post_comments', 'icon' => 'reply', 'label' => '내 글 댓글', 'value' => (int) ($data['my_post_comments'] ?? 0), 'url' => $urls['community'] ?? '#'),
            array('key' => 'owner_pending', 'icon' => 'pending', 'label' => '승인 대기', 'value' => (int) ($data['owner_pending_join'] ?? 0), 'url' => $talk_url),
            array('key' => 'meetup', 'icon' => 'meetup', 'label' => '모임 일정', 'value' => (int) ($data['meetup_count'] ?? 0), 'url' => $urls['calendar'] ?? '#'),
            array('key' => 'saved', 'icon' => 'saved', 'label' => '저장한 글', 'value' => (int) ($data['saved_count'] ?? 0), 'url' => G5_URL.'/page/eottae-saved-shops.php'),
        );
    }
}

if (!function_exists('render_my_sebu_briefing')) {
    /**
     * @param array<string, mixed>|null $data
     */
    function render_my_sebu_briefing($data = null)
    {
        global $member;

        if (!is_array($data)) {
            $mb_id = isset($member['mb_id']) ? (string) $member['mb_id'] : '';
            $data = collect_my_sebu_briefing_data($mb_id);
        }

        $lines = generate_my_sebu_briefing_text($data);
        $cards = eottae_briefing_stat_cards_my($data);

        include G5_PATH.'/components/eottae/sebu-briefing-mypage.php';
    }
}

if (!function_exists('eottae_briefing_url')) {
    function eottae_briefing_url()
    {
        return G5_URL.'/briefing/';
    }
}

if (!function_exists('eottae_briefing_teaser_stats')) {
    /**
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    function eottae_briefing_teaser_stats(array $data)
    {
        $counts = isset($data['counts']) && is_array($data['counts']) ? $data['counts'] : array();
        $urls = isset($data['urls']) && is_array($data['urls']) ? $data['urls'] : eottae_briefing_urls();

        return array(
            array('label' => '오늘 일정', 'value' => (int) ($counts['calendar_today'] ?? 0), 'url' => $urls['calendar'] ?? '#'),
            array('label' => '세부톡 글', 'value' => (int) ($counts['talk_posts'] ?? 0), 'url' => $urls['talk'] ?? '#'),
            array('label' => '세부광장', 'value' => (int) ($counts['plaza_posts'] ?? 0), 'url' => $urls['plaza'] ?? '#'),
        );
    }
}

if (!function_exists('eottae_briefing_teaser_line')) {
    function eottae_briefing_teaser_line(array $data)
    {
        $lines = generate_today_sebu_briefing_text($data);
        if (!empty($lines[0])) {
            return get_text($lines[0]);
        }

        return '오늘 세부 커뮤니티 소식을 확인해보세요.';
    }
}

if (!function_exists('render_today_sebu_briefing_teaser')) {
    /**
     * @param array<string, mixed>|null $data
     */
    function render_today_sebu_briefing_teaser($data = null)
    {
        if (!is_array($data)) {
            $data = collect_today_sebu_briefing_data();
        }

        $teaser_title = '오늘의 세부 체크';
        $teaser_summary = eottae_briefing_summary_line_today($data);
        $teaser_line = eottae_briefing_teaser_line($data);
        $teaser_stats = eottae_briefing_teaser_stats($data);
        $teaser_url = eottae_briefing_url();
        $teaser_cta = '브리핑 보기';

        include G5_PATH.'/components/eottae/sebu-briefing-teaser.php';
    }
}

if (!function_exists('eottae_briefing_home_payload')) {
    function eottae_briefing_home_payload()
    {
        $data = collect_today_sebu_briefing_data();

        return array(
            'mode'       => 'teaser',
            'title'      => '오늘의 세부 체크',
            'summary'    => eottae_briefing_summary_line_today($data),
            'line'       => eottae_briefing_teaser_line($data),
            'stats'      => eottae_briefing_teaser_stats($data),
            'briefing_url' => eottae_briefing_url(),
            'cta'        => '브리핑 보기',
            'generated_at'=> $data['generated_at'] ?? '',
        );
    }
}

if (!function_exists('eottae_briefing_load_assets')) {
    function eottae_briefing_load_assets()
    {
        if (!function_exists('add_stylesheet')) {
            return;
        }

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-briefing.css?v=2">', 28);
    }
}
