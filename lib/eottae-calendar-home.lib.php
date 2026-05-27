<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_calendar_home_weekday_label')) {
    function eottae_calendar_home_weekday_label($date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
            return '';
        }

        $labels = array('일', '월', '화', '수', '목', '금', '토');
        $idx = (int) date('w', strtotime($date));

        return isset($labels[$idx]) ? $labels[$idx] : '';
    }
}

if (!function_exists('eottae_calendar_home_format_event')) {
    function eottae_calendar_home_format_event(array $event)
    {
        return array(
            'event_id'       => (int) ($event['event_id'] ?? 0),
            'title'          => (string) ($event['title'] ?? ''),
            'category'       => (string) ($event['category'] ?? ''),
            'category_label' => (string) ($event['category_label'] ?? ''),
            'category_class' => (string) ($event['category_class'] ?? ''),
            'badge_label'    => (string) ($event['badge_label'] ?? ''),
            'badge_class'    => (string) ($event['badge_class'] ?? ''),
            'time_label'     => (string) ($event['time_label'] ?? ''),
            'location'       => (string) ($event['location'] ?? ''),
            'writer_display' => (string) ($event['writer_display'] ?? $event['writer_name'] ?? ''),
            'source_label'   => (string) ($event['source_label'] ?? ''),
            'is_google'      => !empty($event['is_google']) ? 1 : 0,
            'detail_href'    => (string) ($event['detail_href'] ?? ''),
        );
    }
}

if (!function_exists('eottae_calendar_home_summary_payload')) {
    function eottae_calendar_home_summary_payload($limit_per_day = 5)
    {
        if (!function_exists('eottae_calendar_summary_days')) {
            include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
        }

        $limit_per_day = max(1, min(10, (int) $limit_per_day));
        $summary = eottae_calendar_summary_days(date('Y-m-d'), '');
        $days = array();
        $talk_events = array();

        foreach ($summary as $day) {
            $events = array();
            foreach (($day['events'] ?? array()) as $event) {
                if (!is_array($event)) {
                    continue;
                }
                $formatted = eottae_calendar_home_format_event($event);
                $events[] = $formatted;
                if (($formatted['category'] ?? '') === 'talk' && count($talk_events) < 6) {
                    $talk_events[] = array_merge($formatted, array(
                        'day_label' => (string) ($day['label'] ?? ''),
                        'date'      => (string) ($day['date'] ?? ''),
                    ));
                }
                if (count($events) >= $limit_per_day) {
                    break;
                }
            }

            $days[] = array(
                'label'   => (string) ($day['label'] ?? ''),
                'date'    => (string) ($day['date'] ?? ''),
                'weekday' => eottae_calendar_home_weekday_label($day['date'] ?? ''),
                'count'   => (int) ($day['count'] ?? 0),
                'events'  => $events,
            );
        }

        global $is_member;

        $member_state = !empty($is_member);
        if (!$member_state && function_exists('eottae_auth_context')) {
            $member_state = !empty(eottae_auth_context()['is_member']);
        }

        return array(
            'title'        => '이번 3일 세부 일정',
            'days'         => $days,
            'talk_events'  => $talk_events,
            'calendar_url' => eottae_calendar_list_url(),
            'create_url'   => eottae_calendar_create_url(),
            'is_member'    => $member_state,
            'login_url'    => function_exists('eottae_login_url')
                ? eottae_login_url(eottae_calendar_create_url())
                : G5_BBS_URL.'/login.php',
        );
    }
}

if (!function_exists('eottae_home_popular_payload')) {
    function eottae_home_popular_payload($limit = 5)
    {
        if (!function_exists('eottae_api_get_community_posts')) {
            include_once G5_LIB_PATH.'/eottae-api.lib.php';
        }

        $limit = max(1, min(10, (int) $limit));

        return array(
            'latest'  => eottae_api_get_community_posts($limit, '', 'latest'),
            'hit'     => eottae_api_get_community_posts($limit, '', 'hit'),
            'comment' => eottae_api_get_community_posts($limit, '', 'comment'),
        );
    }
}

if (!function_exists('eottae_home_public_talkrooms_payload')) {
    /**
     * 홈 커뮤니티 영역 — 공개 세부톡방 롤링 캐러셀 (최대 5건)
     *
     * @return array<string, mixed>
     */
    function eottae_home_public_talkrooms_payload($limit = 5)
    {
        if (!function_exists('eottae_talkroom_list_public_cards')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }
        if (!function_exists('eottae_talkroom_home_hero_hot_score')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $limit = max(1, min(10, (int) $limit));
        $pool = eottae_talkroom_list_public_cards(array(
            'limit' => max(24, $limit),
            'page'  => 1,
        ));

        if (!empty($pool)) {
            usort($pool, function ($a, $b) {
                $score_cmp = eottae_talkroom_home_hero_hot_score($b) <=> eottae_talkroom_home_hero_hot_score($a);
                if ($score_cmp !== 0) {
                    return $score_cmp;
                }

                return (int) ($b['post_count'] ?? 0) <=> (int) ($a['post_count'] ?? 0);
            });
        }

        $rooms = array_slice($pool, 0, $limit);

        return array(
            'title'      => '공개 세부톡방',
            'desc'       => '관심 주제별 공개 톡방에 참여하고 세부 생활 정보를 나눠 보세요.',
            'rooms'      => $rooms,
            'list_url'   => function_exists('eottae_talkroom_list_url') ? eottae_talkroom_list_url() : G5_URL.'/talk',
            'create_url' => function_exists('eottae_talkroom_create_url') ? eottae_talkroom_create_url() : G5_URL.'/page/eottae-talk-create.php',
            'total'      => count($rooms),
        );
    }
}

if (!function_exists('eottae_home_main_section_payload')) {
    function eottae_home_main_section_payload()
    {
        if (!function_exists('eottae_api_community_table')) {
            include_once G5_LIB_PATH.'/eottae-api.lib.php';
        }

        $community_table = eottae_api_community_table();

        return array(
            'calendar'      => eottae_calendar_home_summary_payload(5),
            'popular'       => eottae_home_popular_payload(5),
            'talk_rooms'    => eottae_home_public_talkrooms_payload(5),
            'community_url' => G5_BBS_URL.'/board.php?bo_table='.$community_table,
        );
    }
}
