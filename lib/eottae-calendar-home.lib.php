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

if (!function_exists('eottae_home_main_section_payload')) {
    function eottae_home_main_section_payload()
    {
        return array(
            'calendar' => eottae_calendar_home_summary_payload(5),
            'popular'  => eottae_home_popular_payload(5),
        );
    }
}
