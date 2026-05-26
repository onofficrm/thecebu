<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_ai_generator_trigger_daily_limits')) {
    function eottae_public_ai_generator_trigger_daily_limits()
    {
        return array(
            'calendar_today'     => 2,
            'calendar_tomorrow'  => 2,
            'calendar_day_after' => 2,
            'holiday'            => 2,
            'talk_room_activity' => 2,
            'popular_post'       => 2,
            'business_event'     => 1,
            'quiet_chat'         => 1,
            'weather'            => 1,
            'external_news'      => 1,
        );
    }
}

if (!function_exists('eottae_public_ai_generator_load_dependencies')) {
    function eottae_public_ai_generator_load_dependencies()
    {
        include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
        if (!function_exists('eottae_calendar_list_events')) {
            include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
        }
        if (!function_exists('eottae_api_get_community_posts')) {
            include_once G5_LIB_PATH.'/eottae-api.lib.php';
        }
        if (!function_exists('eottae_get_events')) {
            include_once G5_LIB_PATH.'/eottae.lib.php';
        }
        if (!function_exists('eottae_talkroom_list_home_feed')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }
        if (!function_exists('eottae_talkroom_public_group_room_id')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-public-chat.lib.php';
        }
        if (!function_exists('eottae_talkroom_ai_bot_mb_id')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
        }
        if (!function_exists('eottae_talkroom_ai_minutes_since')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-quiet.lib.php';
        }
        if (!function_exists('eottae_promo_coupon_list')) {
            include_once G5_LIB_PATH.'/eottae-promo-coupon.lib.php';
        }
        if (is_file(G5_LIB_PATH.'/eottae-public-ai-weather.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-weather.lib.php';
        }
        if (is_file(G5_LIB_PATH.'/eottae-public-ai-news.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-news.lib.php';
        }
        if (is_file(G5_LIB_PATH.'/eottae-public-ai-guard.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-guard.lib.php';
        }
        if (is_file(G5_LIB_PATH.'/eottae-public-ai-poll.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-poll.lib.php';
        }
        if (is_file(G5_LIB_PATH.'/eottae-public-ai-openai.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-openai.lib.php';
        }
    }
}

if (!function_exists('eottae_public_ai_filter_events_on_date')) {
    function eottae_public_ai_filter_events_on_date(array $rows, $date)
    {
        $date = trim((string) $date);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return array();
        }

        $matched = array();
        foreach ($rows as $event) {
            if (!is_array($event)) {
                continue;
            }
            $start = trim((string) ($event['start_date'] ?? ''));
            $end = trim((string) ($event['end_date'] ?? $start));
            if ($start === '' || $start > $date || ($end !== '' && $end < $date)) {
                continue;
            }
            $matched[] = $event;
        }

        return $matched;
    }
}

if (!function_exists('eottae_public_ai_collect_calendar_events_for_range')) {
    function eottae_public_ai_collect_calendar_events_for_range($range_start, $range_end)
    {
        if (!function_exists('eottae_calendar_list_events')) {
            return array();
        }

        $list = eottae_calendar_list_events(array(
            'range_start' => $range_start,
            'range_end'   => $range_end,
            'limit'       => 80,
        ));

        return isset($list['rows']) && is_array($list['rows']) ? $list['rows'] : array();
    }
}

if (!function_exists('eottae_public_ai_collect_talk_room_activity')) {
    function eottae_public_ai_collect_talk_room_activity($since_days = 7, $limit = 8)
    {
        if (!function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return array('active_rooms' => array(), 'meeting_posts' => array(), 'comment_posts' => array());
        }

        $tables = eottae_talkroom_table_names();
        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table)) {
            return array('active_rooms' => array(), 'meeting_posts' => array(), 'comment_posts' => array());
        }

        $since_days = max(1, min(30, (int) $since_days));
        $limit = max(1, min(15, (int) $limit));
        $since = date('Y-m-d H:i:s', strtotime('-'.$since_days.' days'));
        $statuses = eottae_talkroom_operating_statuses();
        $status_sql = array();
        foreach ($statuses as $status) {
            $status_sql[] = "'".sql_real_escape_string($status)."'";
        }
        $status_in = implode(',', $status_sql);
        $visible = eottae_talkroom_post_visible_sql('w');

        $active_rooms = array();
        $room_result = sql_query("
            SELECT
                r.room_id,
                r.room_name,
                r.category,
                COUNT(*) AS post_count,
                MAX(w.wr_datetime) AS latest_at
            FROM `{$write_table}` w
            INNER JOIN `{$tables['rooms']}` r ON r.room_id = CAST(w.wr_1 AS UNSIGNED)
            WHERE w.wr_is_comment = 0
              AND {$visible}
              AND r.status IN ({$status_in})
              AND r.visibility = 'public'
              AND w.wr_datetime >= '".sql_real_escape_string($since)."'
            GROUP BY r.room_id, r.room_name, r.category
            ORDER BY post_count DESC, latest_at DESC
            LIMIT {$limit}
        ", false);
        if ($room_result) {
            while ($row = sql_fetch_array($room_result)) {
                $active_rooms[] = array(
                    'room_id'    => (int) ($row['room_id'] ?? 0),
                    'room_name'  => get_text($row['room_name'] ?? ''),
                    'category'   => trim((string) ($row['category'] ?? '')),
                    'post_count' => (int) ($row['post_count'] ?? 0),
                    'latest_at'  => trim((string) ($row['latest_at'] ?? '')),
                );
            }
        }

        $meeting_posts = array();
        $feed = eottae_talkroom_list_home_feed(20);
        $meeting_types = array('모임', '모임모집', '모임공지');
        foreach ($feed as $post) {
            $type_label = trim((string) ($post['type_label'] ?? ''));
            if (!in_array($type_label, $meeting_types, true)) {
                continue;
            }
            $meeting_posts[] = $post;
            if (count($meeting_posts) >= $limit) {
                break;
            }
        }

        $comment_posts = array();
        $comment_result = sql_query("
            SELECT
                w.wr_id,
                w.wr_subject,
                w.wr_comment,
                w.wr_datetime,
                w.ca_name,
                r.room_id,
                r.room_name
            FROM `{$write_table}` w
            INNER JOIN `{$tables['rooms']}` r ON r.room_id = CAST(w.wr_1 AS UNSIGNED)
            WHERE w.wr_is_comment = 0
              AND {$visible}
              AND r.status IN ({$status_in})
              AND r.visibility = 'public'
              AND w.wr_comment >= 3
              AND w.wr_datetime >= '".sql_real_escape_string($since)."'
            ORDER BY w.wr_comment DESC, w.wr_datetime DESC
            LIMIT {$limit}
        ", false);
        if ($comment_result) {
            while ($row = sql_fetch_array($comment_result)) {
                $room_id = (int) ($row['room_id'] ?? 0);
                $wr_id = (int) ($row['wr_id'] ?? 0);
                if ($room_id < 1 || $wr_id < 1) {
                    continue;
                }
                $comment_posts[] = array(
                    'wr_id'         => $wr_id,
                    'room_id'       => $room_id,
                    'room_name'     => get_text($row['room_name'] ?? ''),
                    'subject'       => get_text($row['wr_subject'] ?? ''),
                    'comment_count' => (int) ($row['wr_comment'] ?? 0),
                    'datetime'      => trim((string) ($row['wr_datetime'] ?? '')),
                    'href'          => function_exists('eottae_talkroom_post_view_url')
                        ? eottae_talkroom_post_view_url($wr_id, $room_id)
                        : '',
                );
            }
        }

        return array(
            'active_rooms'  => $active_rooms,
            'meeting_posts'   => $meeting_posts,
            'comment_posts'   => $comment_posts,
        );
    }
}

if (!function_exists('eottae_public_ai_collect_community_surge_posts')) {
    function eottae_public_ai_collect_community_surge_posts($limit = 3)
    {
        global $g5;

        if (!function_exists('eottae_api_community_board_ready') || !eottae_api_community_board_ready()) {
            return array();
        }

        $limit = max(1, min(10, (int) $limit));
        $bo_table = eottae_api_community_table();
        $write_table = $g5['write_prefix'].$bo_table;
        $since = date('Y-m-d H:i:s', strtotime('-1 day'));
        $result = sql_query("
            SELECT wr_id, ca_name, wr_subject, wr_comment, wr_hit, wr_datetime
            FROM {$write_table}
            WHERE wr_is_comment = 0
              AND wr_datetime >= '".sql_escape_string($since)."'
              AND wr_hit >= 20
            ORDER BY wr_hit DESC, wr_id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        if (!$result) {
            return $rows;
        }

        while ($row = sql_fetch_array($result)) {
            $formatted = eottae_api_format_community_row($row, false);
            if ($formatted) {
                $rows[] = $formatted;
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_public_ai_collect_business_events')) {
    function eottae_public_ai_collect_business_events($limit = 5)
    {
        $items = array();

        if (function_exists('eottae_get_events')) {
            foreach (eottae_get_events($limit) as $row) {
                $items[] = array(
                    'kind'    => 'event_board',
                    'id'      => (int) ($row['wr_id'] ?? 0),
                    'title'   => trim((string) ($row['subject'] ?? '')),
                    'href'    => trim((string) ($row['href'] ?? '')),
                );
            }
        }

        if (function_exists('eottae_promo_coupon_list')) {
            foreach (eottae_promo_coupon_list('active', $limit) as $row) {
                $promo_id = (int) ($row['promo_id'] ?? 0);
                if ($promo_id < 1) {
                    continue;
                }
                $title = trim((string) ($row['cp_title'] ?? ''));
                if ($title === '') {
                    $title = '쿠폰 프로모션';
                }
                $items[] = array(
                    'kind'  => 'promo',
                    'id'    => 100000 + $promo_id,
                    'title' => $title,
                    'href'  => G5_URL.'/page/eottae-coupon.php',
                );
            }
        }

        return $items;
    }
}

if (!function_exists('eottae_public_ai_collect_public_chat_quiet_state')) {
    function eottae_public_ai_collect_public_chat_quiet_state(array $settings, $now = null)
    {
        $now = $now ?: (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $room_id = function_exists('eottae_talkroom_public_group_room_id')
            ? (int) eottae_talkroom_public_group_room_id()
            : 0;

        if ($room_id < 1) {
            return array(
                'room_id'              => 0,
                'is_quiet'             => false,
                'minutes_since_member' => null,
                'latest_is_ai'         => false,
                'recent_ai_minutes'    => null,
            );
        }

        $last_member_at = function_exists('eottae_talkroom_ai_context_last_member_activity_at')
            ? eottae_talkroom_ai_context_last_member_activity_at($room_id)
            : '';
        $minutes_since_member = $last_member_at !== '' && function_exists('eottae_talkroom_ai_minutes_since')
            ? eottae_talkroom_ai_minutes_since($last_member_at, $now)
            : null;

        $min_silence = max(5, (int) ($settings['min_silence_minutes'] ?? 180));
        $is_quiet = $minutes_since_member === null || $minutes_since_member >= $min_silence;

        $latest_is_ai = false;
        if (function_exists('eottae_talkroom_ai_room_latest_post_row') && function_exists('eottae_talkroom_ai_is_ai_write_row')) {
            $latest = eottae_talkroom_ai_room_latest_post_row($room_id);
            $latest_is_ai = $latest && eottae_talkroom_ai_is_ai_write_row($latest);
        }

        $recent_ai_minutes = null;
        if (function_exists('eottae_talkroom_ai_room_last_ai_post_at')) {
            $last_ai_at = eottae_talkroom_ai_room_last_ai_post_at($room_id);
            if ($last_ai_at !== '' && function_exists('eottae_talkroom_ai_minutes_since')) {
                $recent_ai_minutes = eottae_talkroom_ai_minutes_since($last_ai_at, $now);
            }
        }

        return array(
            'room_id'              => $room_id,
            'is_quiet'             => $is_quiet,
            'minutes_since_member' => $minutes_since_member,
            'latest_is_ai'         => $latest_is_ai,
            'recent_ai_minutes'    => $recent_ai_minutes,
            'min_silence_minutes'  => $min_silence,
        );
    }
}

if (!function_exists('collect_public_ai_sources')) {
    /**
     * 참고 데이터 수집
     *
     * @return array<string, mixed>
     */
    function collect_public_ai_sources(array $settings = array(), $now = null)
    {
        return eottae_public_ai_collect_sources($settings, $now);
    }
}

if (!function_exists('eottae_public_ai_collect_sources')) {
    function eottae_public_ai_collect_sources(array $settings = array(), $now = null)
    {
        eottae_public_ai_generator_load_dependencies();
        eottae_public_ai_ensure_schema();

        if (!$settings) {
            $settings = eottae_public_ai_get_settings();
        }
        $now = $now ?: (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $today = substr($now, 0, 10);
        $tomorrow = date('Y-m-d', strtotime($today.' +1 day'));
        $day_after = date('Y-m-d', strtotime($today.' +2 days'));

        $range_start = $today;
        $range_end = $day_after;
        $calendar_rows = array();
        if (!empty($settings['use_calendar']) || !empty($settings['use_holidays'])) {
            $calendar_rows = eottae_public_ai_collect_calendar_events_for_range($range_start, $range_end);
        }

        $sources = array(
            'now'           => $now,
            'today'         => $today,
            'tomorrow'      => $tomorrow,
            'day_after'     => $day_after,
            'settings'      => $settings,
            'calendar'      => array(
                'today'     => eottae_public_ai_filter_events_on_date($calendar_rows, $today),
                'tomorrow'  => eottae_public_ai_filter_events_on_date($calendar_rows, $tomorrow),
                'day_after' => eottae_public_ai_filter_events_on_date($calendar_rows, $day_after),
            ),
            'holidays'      => array(
                'today'    => array(),
                'tomorrow' => array(),
            ),
            'talk_rooms'    => array('active_rooms' => array(), 'meeting_posts' => array(), 'comment_posts' => array()),
            'popular'       => array('hit' => array(), 'comment' => array(), 'surge' => array()),
            'business'      => array(),
            'quiet_chat'    => array(),
        );

        if (!empty($settings['use_holidays'])) {
            foreach ($calendar_rows as $event) {
                if (!is_array($event) || ($event['category'] ?? '') !== 'holiday') {
                    continue;
                }
                if (eottae_public_ai_filter_events_on_date(array($event), $today)) {
                    $sources['holidays']['today'][] = $event;
                }
                if (eottae_public_ai_filter_events_on_date(array($event), $tomorrow)) {
                    $sources['holidays']['tomorrow'][] = $event;
                }
            }
        }

        if (!empty($settings['use_talk_rooms'])) {
            $sources['talk_rooms'] = eottae_public_ai_collect_talk_room_activity();
        }

        if (!empty($settings['use_popular_posts'])) {
            $sources['popular']['hit'] = eottae_api_get_community_posts(5, '', 'hit');
            $sources['popular']['comment'] = eottae_api_get_community_posts(5, '', 'comment');
            $sources['popular']['surge'] = eottae_public_ai_collect_community_surge_posts(3);
        }

        if (!empty($settings['use_business_events'])) {
            $sources['business'] = eottae_public_ai_collect_business_events(5);
        }

        $sources['quiet_chat'] = eottae_public_ai_collect_public_chat_quiet_state($settings, $now);

        $sources['weather'] = array();
        if (!empty($settings['use_weather']) && function_exists('eottae_public_ai_weather_get_for_date')) {
            foreach (array($today, $tomorrow) as $d) {
                $row = eottae_public_ai_weather_get_for_date($d);
                if (!$row && function_exists('eottae_public_ai_weather_fetch_from_api')) {
                    $row = eottae_public_ai_weather_fetch_from_api($d);
                }
                if ($row) {
                    $sources['weather'][$d] = $row;
                }
            }
        }

        $sources['external_news'] = array();
        if (!empty($settings['use_external_news']) && function_exists('eottae_public_ai_external_news_list_active')) {
            $sources['external_news'] = eottae_public_ai_external_news_list_active(5);
        }

        return $sources;
    }
}

if (!function_exists('eottae_public_ai_generator_event_title')) {
    function eottae_public_ai_generator_event_title(array $event)
    {
        $title = trim(strip_tags((string) ($event['title'] ?? '')));
        if ($title === '' && !empty($event['event_id'])) {
            $title = '일정 #'.(int) $event['event_id'];
        }

        return $title;
    }
}

if (!function_exists('eottae_public_ai_generator_area_label')) {
    function eottae_public_ai_generator_area_label(array $event)
    {
        $area = trim((string) ($event['area_label'] ?? ''));
        if ($area === '') {
            $area = trim((string) ($event['area'] ?? ''));
        }

        return $area !== '' ? $area : '세부';
    }
}

if (!function_exists('eottae_public_ai_generator_build_calendar_candidates')) {
    function eottae_public_ai_generator_build_calendar_candidates(array $sources)
    {
        $candidates = array();
        $slots = array(
            'today'     => array('trigger' => 'calendar_today', 'label' => '오늘'),
            'tomorrow'  => array('trigger' => 'calendar_tomorrow', 'label' => '내일'),
            'day_after' => array('trigger' => 'calendar_day_after', 'label' => '모레'),
        );

        foreach ($slots as $key => $slot) {
            $events = isset($sources['calendar'][$key]) ? $sources['calendar'][$key] : array();
            foreach (array_slice($events, 0, 2) as $event) {
                if (!is_array($event)) {
                    continue;
                }
                $event_id = (int) ($event['event_id'] ?? 0);
                $title = eottae_public_ai_generator_event_title($event);
                if ($event_id < 1 || $title === '') {
                    continue;
                }

                $category = trim((string) ($event['category'] ?? ''));
                $area = eottae_public_ai_generator_area_label($event);
                $room_name = trim((string) ($event['related_room_name'] ?? ''));
                $action_url = trim((string) ($event['detail_href'] ?? ''));
                $message = '';
                $trigger_type = $slot['trigger'];

                if ($category === 'talk' && $room_name !== '') {
                    $message = $room_name.'에서 '.$title.' 일정이 등록되어 있어요.'."\n"
                        .'함께 참여하실 분들은 확인해보세요.';
                } elseif (in_array($category, array('festival', 'event', 'business'), true)) {
                    $message = $slot['label'].' '.$area.'에서 '.$title.' 행사가 있어요.'."\n"
                        .'다녀오시는 분들은 후기 남겨주세요 😊';
                } elseif ($key === 'today') {
                    $message = '오늘 세부어때 캘린더에 등록된 일정이 있어요.'."\n"
                        .$title.' 일정이 눈에 띄네요.'."\n"
                        .'가보실 분 계신가요?';
                } elseif ($key === 'tomorrow') {
                    $message = '내일은 '.$title.' 일정이 등록되어 있어요.'."\n"
                        .'미리 계획 잡고 계신 분 있으신가요?';
                } else {
                    $message = '모레는 '.$title.' 일정이 등록되어 있어요.'."\n"
                        .'일정 보시고 미리 준비해 보세요.';
                }

                $candidates[] = array(
                    'trigger_type' => $trigger_type,
                    'source_type'  => 'calendar',
                    'source_id'    => $event_id,
                    'title'        => $title,
                    'message'      => $message,
                    'action_label' => '일정 보기',
                    'action_url'   => $action_url,
                    'admin_memo'   => 'calendar:'.$category,
                );
            }
        }

        return $candidates;
    }
}

if (!function_exists('eottae_public_ai_generator_build_holiday_candidates')) {
    function eottae_public_ai_generator_build_holiday_candidates(array $sources)
    {
        $candidates = array();
        $holiday_slots = array(
            'today'    => '오늘',
            'tomorrow' => '내일',
        );

        foreach ($holiday_slots as $key => $day_label) {
            $events = isset($sources['holidays'][$key]) ? $sources['holidays'][$key] : array();
            if (!$events) {
                continue;
            }
            $event = $events[0];
            $event_id = (int) ($event['event_id'] ?? 0);
            $title = eottae_public_ai_generator_event_title($event);
            if ($event_id < 1) {
                continue;
            }

            $when = $key === 'today' ? '오늘' : '내일';
            $poll_options = '';
            if ($key === 'tomorrow' && function_exists('eottae_public_ai_poll_encode_options')) {
                $message = $when.'은 공휴일이네요 😊'."\n"
                    .($title !== '' ? $title.' — ' : '')
                    .'세부에 계신 분들은 어떤 계획 있으세요?';
                $poll_options = eottae_public_ai_poll_encode_options(
                    eottae_public_ai_poll_default_holiday_options()
                );
            } elseif ($key === 'today' && isset($sources['holidays']['tomorrow']) && !empty($sources['holidays']['tomorrow'])) {
                $message = '공휴일 전날이라 오늘 저녁에는 외출하시는 분들이 많을 것 같아요.'."\n"
                    .'사람 너무 붐비지 않는 장소 있으면 공유해주세요.';
            } else {
                $message = $when.'은 공휴일이네요 😊'."\n"
                    .($title !== '' ? $title.' — ' : '')
                    .'세부에 계신 분들은 어떤 계획 있으세요?'."\n"
                    .'리조트 / 맛집 / 쇼핑 / 운동 / 집콕 중에 골라보세요.';
            }

            $candidates[] = array(
                'trigger_type' => 'holiday',
                'source_type'  => 'holiday',
                'source_id'    => $event_id,
                'title'        => $title !== '' ? $title : $day_label.' 공휴일',
                'message'      => $message,
                'action_label' => '캘린더 보기',
                'action_url'   => trim((string) ($event['detail_href'] ?? G5_URL.'/page/eottae-calendar.php')),
                'admin_memo'   => 'holiday:'.$key,
                'poll_options' => $poll_options,
            );
        }

        return $candidates;
    }
}

if (!function_exists('eottae_public_ai_generator_build_talk_room_candidates')) {
    function eottae_public_ai_generator_build_talk_room_candidates(array $sources)
    {
        $candidates = array();
        $talk = isset($sources['talk_rooms']) && is_array($sources['talk_rooms']) ? $sources['talk_rooms'] : array();

        foreach (array_slice($talk['meeting_posts'] ?? array(), 0, 2) as $post) {
            $wr_id = (int) ($post['wr_id'] ?? 0);
            $room_name = trim((string) ($post['room_name'] ?? ''));
            if ($wr_id < 1 || $room_name === '') {
                continue;
            }

            $is_jokgu = (bool) preg_match('/족구/u', $room_name);
            if ($is_jokgu) {
                $message = '세부족구방에서 주말 족구 모임 이야기가 나오고 있어요.'."\n"
                    .'오랜만에 땀 흘리고 싶은 분들은 한번 참여해보세요.';
            } else {
                $message = $room_name.'에서 이번 주 모임 이야기가 나오고 있어요.'."\n"
                    .'관심 있으신 분들은 참여해보세요.';
            }

            $candidates[] = array(
                'trigger_type' => 'talk_room_activity',
                'source_type'  => 'talk_room',
                'source_id'    => $wr_id,
                'title'        => trim((string) ($post['subject'] ?? '')),
                'message'      => $message,
                'action_label' => '톡방 글 보기',
                'action_url'   => trim((string) ($post['href'] ?? '')),
                'admin_memo'   => 'meeting_post',
            );
        }

        if (count($candidates) < 2) {
            foreach (array_slice($talk['active_rooms'] ?? array(), 0, 2) as $room) {
                $room_id = (int) ($room['room_id'] ?? 0);
                $room_name = trim((string) ($room['room_name'] ?? ''));
                if ($room_id < 1 || $room_name === '') {
                    continue;
                }
                if ((int) ($room['post_count'] ?? 0) < 2) {
                    continue;
                }

                $href = function_exists('eottae_talkroom_enter_url')
                    ? eottae_talkroom_enter_url($room_id)
                    : '';
                $message = '요즘 '.$room_name.'에서 이야기가 활발해요.'."\n"
                    .'관심 있으신 분들은 한번 들러보세요.';

                $candidates[] = array(
                    'trigger_type' => 'talk_room_activity',
                    'source_type'  => 'talk_room',
                    'source_id'    => $room_id,
                    'title'        => $room_name,
                    'message'      => $message,
                    'action_label' => '톡방 입장',
                    'action_url'   => $href,
                    'admin_memo'   => 'active_room',
                );
            }
        }

        return $candidates;
    }
}

if (!function_exists('eottae_public_ai_generator_build_popular_candidates')) {
    function eottae_public_ai_generator_build_popular_candidates(array $sources)
    {
        $candidates = array();
        $popular = isset($sources['popular']) && is_array($sources['popular']) ? $sources['popular'] : array();
        $used_ids = array();

        foreach (array_slice($popular['hit'] ?? array(), 0, 1) as $post) {
            $wr_id = (int) ($post['wr_id'] ?? 0);
            $title = trim((string) ($post['title'] ?? ''));
            if ($wr_id < 1 || $title === '') {
                continue;
            }
            $used_ids[$wr_id] = true;
            $candidates[] = array(
                'trigger_type' => 'popular_post',
                'source_type'  => 'post',
                'source_id'    => $wr_id,
                'title'        => $title,
                'message'      => '오늘 세부어때에서 \''.$title.'\' 글이 많이 읽히고 있어요.'."\n"
                    .'이 주제에 대해 경험 있으신 분들은 의견 남겨주세요.',
                'action_label' => '글 보기',
                'action_url'   => trim((string) ($post['url'] ?? '')),
                'admin_memo'   => 'popular_hit',
            );
        }

        foreach (array_slice($popular['comment'] ?? array(), 0, 1) as $post) {
            $wr_id = (int) ($post['wr_id'] ?? 0);
            $title = trim((string) ($post['title'] ?? ''));
            $comments = (int) ($post['comments'] ?? 0);
            if ($wr_id < 1 || $title === '' || isset($used_ids[$wr_id])) {
                continue;
            }
            if ($comments < 3) {
                continue;
            }
            $candidates[] = array(
                'trigger_type' => 'popular_post',
                'source_type'  => 'post',
                'source_id'    => $wr_id + 500000000,
                'title'        => $title,
                'message'      => '지금 \''.$title.'\' 글에 댓글이 많이 달리고 있어요.'."\n"
                    .'다른 분들은 어떻게 생각하시나요?',
                'action_label' => '글 보기',
                'action_url'   => trim((string) ($post['url'] ?? '')),
                'admin_memo'   => 'popular_comment',
            );
        }

        foreach (array_slice($popular['surge'] ?? array(), 0, 1) as $post) {
            $wr_id = (int) ($post['wr_id'] ?? 0);
            $title = trim((string) ($post['title'] ?? ''));
            if ($wr_id < 1 || $title === '' || isset($used_ids[$wr_id])) {
                continue;
            }
            $candidates[] = array(
                'trigger_type' => 'popular_post',
                'source_type'  => 'post',
                'source_id'    => $wr_id + 600000000,
                'title'        => $title,
                'message'      => '최근 \''.$title.'\' 글 조회수가 빠르게 올라가고 있어요.'."\n"
                    .'이 주제 궁금하신 분들은 한번 읽어보세요.',
                'action_label' => '글 보기',
                'action_url'   => trim((string) ($post['url'] ?? '')),
                'admin_memo'   => 'popular_surge',
            );
        }

        return $candidates;
    }
}

if (!function_exists('eottae_public_ai_generator_build_business_candidates')) {
    function eottae_public_ai_generator_build_business_candidates(array $sources)
    {
        $candidates = array();
        $items = isset($sources['business']) && is_array($sources['business']) ? $sources['business'] : array();
        if (!$items) {
            return $candidates;
        }

        $item = $items[0];
        $id = (int) ($item['id'] ?? 0);
        $title = trim((string) ($item['title'] ?? ''));
        if ($id < 1 || $title === '') {
            return $candidates;
        }

        $candidates[] = array(
            'trigger_type' => 'business_event',
            'source_type'  => 'business_event',
            'source_id'    => $id,
            'title'        => $title,
            'message'      => '세부어때에 '.$title.' 정보가 올라왔어요.'."\n"
                .'이번 주말 계획하시는 분들은 참고해보세요.'."\n"
                .'최근 이용해보신 분 후기 있으신가요?',
            'action_label' => '자세히 보기',
            'action_url'   => trim((string) ($item['href'] ?? '')),
            'admin_memo'   => (string) ($item['kind'] ?? 'business'),
        );

        return $candidates;
    }
}

if (!function_exists('eottae_public_ai_generator_quiet_message_templates')) {
    function eottae_public_ai_generator_quiet_message_templates()
    {
        return array(
            '오늘 공개톡이 조용하네요 😊'."\n".'세부에서 요즘 자주 가는 맛집 하나씩 추천해볼까요?',
            '오늘 세부에서 궁금한 것 하나만 남겨보세요.'."\n".'여행, 맛집, 병원, 렌트, 모임 뭐든 좋습니다.',
            '공개톡이 잠시 쉬어가는 중이에요 ☕'."\n".'요즘 세부 생활에서 가장 만족했던 장소나 팁을 나눠주세요.',
        );
    }
}

if (!function_exists('eottae_public_ai_generator_build_quiet_candidates')) {
    function eottae_public_ai_generator_build_quiet_candidates(array $sources)
    {
        $candidates = array();
        $quiet = isset($sources['quiet_chat']) && is_array($sources['quiet_chat']) ? $sources['quiet_chat'] : array();
        if (empty($quiet['is_quiet'])) {
            return $candidates;
        }
        if (function_exists('eottae_public_ai_public_chat_member_is_active')
            && eottae_public_ai_public_chat_member_is_active(120, 3)) {
            return $candidates;
        }
        if (!empty($quiet['latest_is_ai'])) {
            return $candidates;
        }

        $recent_ai = $quiet['recent_ai_minutes'] ?? null;
        $min_silence = (int) ($quiet['min_silence_minutes'] ?? 180);
        if ($recent_ai !== null && $recent_ai < $min_silence) {
            return $candidates;
        }

        $templates = eottae_public_ai_generator_quiet_message_templates();
        $index = (int) date('j') % count($templates);
        $message = $templates[$index];
        $room_id = (int) ($quiet['room_id'] ?? 0);

        $candidates[] = array(
            'trigger_type' => 'quiet_chat',
            'source_type'  => 'talk_room',
            'source_id'    => $room_id > 0 ? $room_id : 1,
            'title'        => '조용한 공개톡',
            'message'      => $message,
            'action_label' => '',
            'action_url'   => G5_URL.'/',
            'admin_memo'   => 'quiet_template_'.$index,
        );

        return $candidates;
    }
}

if (!function_exists('generate_public_ai_candidates')) {
    /**
     * 수집 데이터 기반 후보 메시지 생성
     *
     * @return array<int, array<string, mixed>>
     */
    function generate_public_ai_candidates(array $sources)
    {
        return eottae_public_ai_generate_candidates($sources);
    }
}

if (!function_exists('eottae_public_ai_generate_candidates')) {
    function eottae_public_ai_generate_candidates(array $sources)
    {
        $settings = isset($sources['settings']) && is_array($sources['settings'])
            ? $sources['settings']
            : eottae_public_ai_get_settings();

        $candidates = array();

        if (!empty($settings['use_calendar'])) {
            $candidates = array_merge($candidates, eottae_public_ai_generator_build_calendar_candidates($sources));
        }

        if (!empty($settings['use_holidays'])) {
            $candidates = array_merge($candidates, eottae_public_ai_generator_build_holiday_candidates($sources));
        }

        if (!empty($settings['use_talk_rooms'])) {
            $candidates = array_merge($candidates, eottae_public_ai_generator_build_talk_room_candidates($sources));
        }

        if (!empty($settings['use_popular_posts'])) {
            $candidates = array_merge($candidates, eottae_public_ai_generator_build_popular_candidates($sources));
        }

        if (!empty($settings['use_business_events'])) {
            $candidates = array_merge($candidates, eottae_public_ai_generator_build_business_candidates($sources));
        }

        if (!empty($settings['use_weather']) && function_exists('eottae_public_ai_generator_build_weather_candidates')) {
            $candidates = array_merge($candidates, eottae_public_ai_generator_build_weather_candidates($sources));
        }

        if (!empty($settings['use_external_news']) && function_exists('eottae_public_ai_generator_build_external_news_candidates')) {
            $candidates = array_merge($candidates, eottae_public_ai_generator_build_external_news_candidates($sources));
        }

        $candidates = array_merge($candidates, eottae_public_ai_generator_build_quiet_candidates($sources));

        if (is_file(G5_LIB_PATH.'/eottae-public-ai-openai.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-openai.lib.php';
            $final = array();
            foreach ($candidates as $candidate) {
                $final[] = eottae_public_ai_generator_enhance_candidate($candidate);
            }

            return $final;
        }

        return $candidates;
    }
}

if (!function_exists('eottae_public_ai_generator_should_skip_candidate')) {
    function eottae_public_ai_generator_should_skip_candidate(array $candidate, array $options = array())
    {
        $now = isset($options['now']) ? $options['now'] : null;
        $skip_dedup = !empty($options['skip_dedup']);
        $trigger_type = trim((string) ($candidate['trigger_type'] ?? ''));
        $source_type = trim((string) ($candidate['source_type'] ?? ''));
        $source_id = max(0, (int) ($candidate['source_id'] ?? 0));
        $message = trim((string) ($candidate['message'] ?? ''));

        if (!$skip_dedup && $source_id > 0 && eottae_public_ai_candidate_exists_today($source_type, $source_id, $now)) {
            return 'duplicate_source_today';
        }

        $limits = eottae_public_ai_generator_trigger_daily_limits();
        if (!$skip_dedup && isset($limits[$trigger_type])) {
            $count = eottae_public_ai_count_candidates_today_by_trigger($trigger_type, $now);
            if ($count >= (int) $limits[$trigger_type]) {
                return 'trigger_daily_limit';
            }
        }

        if (!$skip_dedup && $trigger_type === 'business_event') {
            if (eottae_public_ai_count_candidates_today_by_trigger('business_event', $now) >= 1) {
                return 'business_event_daily_limit';
            }
        }

        if (!$skip_dedup && eottae_public_ai_has_similar_published_message($message)) {
            return 'similar_published';
        }

        return '';
    }
}

if (!function_exists('save_public_ai_candidate')) {
    /**
     * 후보 메시지 DB 저장 (중복 방지 적용)
     *
     * @return array{ok:bool, message:string, candidate_id:int, skipped_reason:string}
     */
    function save_public_ai_candidate(array $candidate, array $options = array())
    {
        return eottae_public_ai_save_candidate_from_generator($candidate, $options);
    }
}

if (!function_exists('eottae_public_ai_save_candidate_from_generator')) {
    function eottae_public_ai_save_candidate_from_generator(array $candidate, array $options = array())
    {
        eottae_public_ai_generator_load_dependencies();

        if (function_exists('eottae_public_ai_guard_apply_to_candidate')) {
            $candidate = eottae_public_ai_guard_apply_to_candidate($candidate);
        }

        $skip_reason = eottae_public_ai_generator_should_skip_candidate($candidate, $options);
        if ($skip_reason !== '') {
            return array(
                'ok'             => false,
                'message'        => $skip_reason,
                'candidate_id'   => 0,
                'skipped_reason' => $skip_reason,
            );
        }

        $result = eottae_public_ai_insert_pending_candidate($candidate);

        return array(
            'ok'             => !empty($result['ok']),
            'message'        => (string) ($result['message'] ?? ''),
            'candidate_id'   => (int) ($result['candidate_id'] ?? 0),
            'skipped_reason' => '',
        );
    }
}

if (!function_exists('run_public_ai_candidate_generator')) {
    /**
     * 후보 메시지 생성 전체 실행
     */
    function run_public_ai_candidate_generator(array $options = array())
    {
        return eottae_public_ai_run_candidate_generator($options);
    }
}

if (!function_exists('eottae_public_ai_run_candidate_generator')) {
    function eottae_public_ai_run_candidate_generator(array $options = array())
    {
        eottae_public_ai_generator_load_dependencies();
        eottae_public_ai_ensure_schema();

        $settings = eottae_public_ai_get_settings();
        $now = isset($options['now']) ? (string) $options['now'] : (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $is_test = !empty($options['is_test']);
        $force = !empty($options['force']);
        $dry_run = !empty($options['dry_run']);
        $skip_dedup = !empty($options['skip_dedup']);

        $result = array(
            'ok'              => true,
            'reason'          => '',
            'saved'           => 0,
            'skipped'         => 0,
            'candidate_ids'   => array(),
            'skip_reasons'    => array(),
            'generated_count' => 0,
        );

        if (empty($settings['ai_enabled']) && !$is_test && !$force) {
            $result['ok'] = false;
            $result['reason'] = 'ai_disabled';

            return $result;
        }

        if (!eottae_public_ai_is_within_active_hours($settings, $now) && !$is_test && !$force) {
            $result['ok'] = false;
            $result['reason'] = 'outside_active_hours';

            return $result;
        }

        $max_per_day = max(1, (int) ($settings['max_messages_per_day'] ?? 3));
        $already_today = eottae_public_ai_count_candidates_today($now);
        if ($already_today >= $max_per_day && !$is_test) {
            $result['ok'] = false;
            $result['reason'] = 'daily_candidate_cap';

            return $result;
        }

        $sources = eottae_public_ai_collect_sources($settings, $now);
        $candidates = eottae_public_ai_generate_candidates($sources);
        $result['generated_count'] = count($candidates);

        $remaining = $max_per_day - $already_today;
        if ($is_test) {
            $remaining = max($remaining, $max_per_day);
        }

        foreach ($candidates as $candidate) {
            if ($result['saved'] >= $remaining) {
                break;
            }

            if ($dry_run) {
                $skip_reason = eottae_public_ai_generator_should_skip_candidate($candidate, array(
                    'now'        => $now,
                    'skip_dedup' => $skip_dedup,
                ));
                if ($skip_reason !== '') {
                    $result['skipped']++;
                    $result['skip_reasons'][] = $skip_reason;
                    continue;
                }
                $result['saved']++;
                continue;
            }

            $save = eottae_public_ai_save_candidate_from_generator($candidate, array(
                'now'        => $now,
                'skip_dedup' => $skip_dedup,
            ));

            if (!empty($save['ok'])) {
                $result['saved']++;
                $new_id = (int) $save['candidate_id'];
                $result['candidate_ids'][] = $new_id;
                eottae_public_ai_insert_log(array(
                    'candidate_id'   => $new_id,
                    'trigger_type'   => (string) ($candidate['trigger_type'] ?? ''),
                    'message'        => (string) ($candidate['message'] ?? ''),
                    'publish_status' => 'skipped',
                    'error_message'  => $is_test ? 'admin_test_generate' : 'candidate_generated',
                ));
                if (!$dry_run && function_exists('eottae_public_ai_maybe_auto_publish_candidate')) {
                    if (!function_exists('eottae_public_ai_publish_load_dependencies')) {
                        include_once G5_LIB_PATH.'/eottae-public-ai-publish.lib.php';
                    }
                    eottae_public_ai_maybe_auto_publish_candidate($new_id, $settings);
                }
                continue;
            }

            $result['skipped']++;
            $reason = !empty($save['skipped_reason']) ? $save['skipped_reason'] : (string) ($save['message'] ?? 'unknown');
            $result['skip_reasons'][] = $reason;
        }

        if ($result['saved'] < 1 && $result['generated_count'] < 1) {
            $result['reason'] = 'no_data';
        } elseif ($result['saved'] < 1 && $result['reason'] === '') {
            $result['reason'] = 'all_skipped';
        }

        return $result;
    }
}
