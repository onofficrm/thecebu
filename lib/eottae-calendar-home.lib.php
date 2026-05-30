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

if (!function_exists('eottae_home_latest_news_boards')) {
    function eottae_home_latest_news_boards()
    {
        return array(
            array('key' => 'life', 'label' => '생활정보', 'bo_table' => defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community'),
            array('key' => 'free', 'label' => '자유게시판', 'bo_table' => function_exists('eottae_free_board_table') ? eottae_free_board_table() : 'free'),
            array('key' => 'review', 'label' => '업체리뷰', 'bo_table' => defined('EOTTae_REVIEW_TABLE') ? EOTTae_REVIEW_TABLE : 'review'),
            array('key' => 'event', 'label' => '이벤트/프로모션', 'bo_table' => defined('EOTTae_EVENT_TABLE') ? EOTTae_EVENT_TABLE : 'event'),
            array('key' => 'market', 'label' => '중고장터', 'bo_table' => defined('EOTTae_MARKET_TABLE') ? EOTTae_MARKET_TABLE : 'market'),
            array('key' => 'estate', 'label' => '부동산', 'bo_table' => defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate'),
            array('key' => 'job', 'label' => '구인구직', 'bo_table' => defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job'),
        );
    }
}

if (!function_exists('eottae_home_latest_news_write_url')) {
    function eottae_home_latest_news_write_url($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            $bo_table = defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community';
        }

        return G5_BBS_URL.'/write.php?bo_table='.$bo_table;
    }
}

if (!function_exists('eottae_home_latest_news_empty_cta')) {
    function eottae_home_latest_news_empty_cta(array $board)
    {
        $label = (string) ($board['label'] ?? '게시판');
        $bo_table = (string) ($board['bo_table'] ?? '');
        $messages = array(
            'life'   => array('title' => '첫 생활정보를 남겨주세요', 'desc' => '병원, 교통, 장보기처럼 세부 생활에 도움 되는 정보를 공유해보세요.'),
            'free'   => array('title' => '첫 이야기를 시작해보세요', 'desc' => '질문, 후기, 소소한 근황까지 자유롭게 남길 수 있습니다.'),
            'review' => array('title' => '첫 업체리뷰를 남겨주세요', 'desc' => '다녀온 곳의 솔직한 경험이 다음 사람에게 큰 도움이 됩니다.'),
            'event'  => array('title' => '이벤트와 프로모션을 알려주세요', 'desc' => '세부의 할인, 행사, 오픈 소식을 빠르게 공유해보세요.'),
            'market' => array('title' => '중고 물품을 등록해보세요', 'desc' => '필요 없는 물건은 나누고, 필요한 물건은 가까이에서 찾아보세요.'),
            'estate' => array('title' => '부동산 매물을 등록해보세요', 'desc' => '렌트, 매매, 양도 정보를 찾는 교민과 여행자에게 보여주세요.'),
            'job'    => array('title' => '구인구직 정보를 등록해보세요', 'desc' => '세부에서 사람을 찾거나 일을 찾는 분들과 연결됩니다.'),
        );
        $key = (string) ($board['key'] ?? '');
        $copy = $messages[$key] ?? array(
            'title' => $label.' 첫 글을 남겨주세요',
            'desc'  => '새 글이 올라오면 홈 최신소식에 바로 노출됩니다.',
        );

        return array(
            'title'       => $copy['title'],
            'desc'        => $copy['desc'],
            'primaryText' => $label.' 글쓰기',
            'primaryUrl'  => eottae_home_latest_news_write_url($bo_table),
            'listText'    => $label.' 보기',
            'listUrl'     => G5_BBS_URL.'/board.php?bo_table='.preg_replace('/[^a-z0-9_]/', '', $bo_table),
        );
    }
}

if (!function_exists('eottae_home_latest_news_table_exists')) {
    function eottae_home_latest_news_table_exists($bo_table)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return false;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $row = sql_fetch(" SHOW TABLES LIKE '".sql_escape_string($write_table)."' ", false);

        return !empty($row);
    }
}

if (!function_exists('eottae_home_latest_news_initial')) {
    function eottae_home_latest_news_initial($title)
    {
        $text = trim(strip_tags(get_text((string) $title)));
        if ($text === '') {
            return '?';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, 1, 'UTF-8');
        }

        return substr($text, 0, 1);
    }
}

if (!function_exists('eottae_home_latest_news_format_row')) {
    function eottae_home_latest_news_format_row(array $row, array $board)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) ($board['bo_table'] ?? ''));
        $wr_id = (int) ($row['wr_id'] ?? 0);
        if ($bo_table === '' || $wr_id < 1) {
            return null;
        }

        $datetime = (string) ($row['wr_datetime'] ?? '');
        $ts = strtotime($datetime);
        $comments = (int) ($row['wr_comment'] ?? 0);
        $views = (int) ($row['wr_hit'] ?? 0);
        $title = get_text($row['wr_subject'] ?? '');
        $thumb = '';

        if (function_exists('eottae_community_list_thumb')) {
            $thumb = (string) eottae_community_list_thumb($bo_table, $wr_id);
        }

        return array(
            'id'       => $wr_id,
            'boardKey' => (string) ($board['key'] ?? ''),
            'board'    => (string) ($board['label'] ?? $bo_table),
            'title'    => $title,
            'comments' => $comments,
            'views'    => $views,
            'time'     => function_exists('eottae_api_relative_time_label') ? eottae_api_relative_time_label($datetime) : '',
            'datetime' => $datetime,
            'is_new'   => $ts ? (G5_SERVER_TIME - $ts) < 86400 : false,
            'is_hot'   => $views >= 100 || $comments >= 10,
            'url'      => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id,
            'thumb'    => $thumb,
            'initial'  => eottae_home_latest_news_initial($title),
        );
    }
}

if (!function_exists('eottae_home_latest_news_board_payload')) {
    function eottae_home_latest_news_board_payload(array $board, $limit = 6)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) ($board['bo_table'] ?? ''));
        $limit = max(1, min(12, (int) $limit));
        if ($bo_table === '' || !eottae_home_latest_news_table_exists($bo_table)) {
            return array();
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $result = sql_query("
            SELECT wr_id, wr_subject, wr_comment, wr_hit, wr_datetime
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
            ORDER BY wr_datetime DESC, wr_id DESC
            LIMIT {$limit}
        ", false);

        $items = array();
        while ($row = sql_fetch_array($result)) {
            $item = eottae_home_latest_news_format_row($row, $board);
            if ($item) {
                $items[] = $item;
            }
        }

        return $items;
    }
}

if (!function_exists('eottae_home_latest_news_today_counts')) {
    function eottae_home_latest_news_today_counts(array $boards)
    {
        global $g5;

        $today = date('Y-m-d').' 00:00:00';
        $post_count = 0;
        $comment_count = 0;
        $news_count = 0;
        $news_keys = array('event' => true, 'market' => true, 'estate' => true, 'job' => true);

        foreach ($boards as $board) {
            $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) ($board['bo_table'] ?? ''));
            if ($bo_table === '' || !eottae_home_latest_news_table_exists($bo_table)) {
                continue;
            }

            $write_table = $g5['write_prefix'].$bo_table;
            $post_row = sql_fetch("
                SELECT COUNT(*) AS cnt
                FROM `{$write_table}`
                WHERE wr_is_comment = 0
                  AND wr_datetime >= '".sql_escape_string($today)."'
            ", false);
            $comment_row = sql_fetch("
                SELECT COUNT(*) AS cnt
                FROM `{$write_table}`
                WHERE wr_is_comment = 1
                  AND wr_datetime >= '".sql_escape_string($today)."'
            ", false);

            $posts = (int) ($post_row['cnt'] ?? 0);
            $post_count += $posts;
            $comment_count += (int) ($comment_row['cnt'] ?? 0);
            if (!empty($news_keys[(string) ($board['key'] ?? '')])) {
                $news_count += $posts;
            }
        }

        return array(
            'posts'    => $post_count,
            'comments' => $comment_count,
            'news'     => $news_count,
        );
    }
}

if (!function_exists('eottae_home_latest_news_payload')) {
    function eottae_home_latest_news_payload($limit_per_board = 6)
    {
        if (!function_exists('eottae_api_relative_time_label')) {
            include_once G5_LIB_PATH.'/eottae-api.lib.php';
        }

        $boards = eottae_home_latest_news_boards();
        $tabs = array();
        $all = array();
        foreach ($boards as $board) {
            $items = eottae_home_latest_news_board_payload($board, $limit_per_board);
            $tabs[] = array(
                'key'      => (string) $board['key'],
                'label'    => (string) $board['label'],
                'bo_table' => (string) $board['bo_table'],
                'url'      => G5_BBS_URL.'/board.php?bo_table='.(string) $board['bo_table'],
                'items'    => $items,
                'count'    => count($items),
                'emptyCta' => eottae_home_latest_news_empty_cta($board),
            );
            foreach ($items as $item) {
                $all[] = $item;
            }
        }

        usort($all, function ($a, $b) {
            return strcmp((string) ($b['datetime'] ?? ''), (string) ($a['datetime'] ?? ''));
        });
        $all = array_slice($all, 0, max(6, min(12, (int) $limit_per_board)));

        array_unshift($tabs, array(
            'key'   => 'all',
            'label' => '전체',
            'url'   => G5_BBS_URL.'/board.php?bo_table='.(defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community'),
            'items' => $all,
            'count' => count($all),
            'emptyCta' => array(
                'title'       => '세부의 첫 소식을 남겨주세요',
                'desc'        => '생활정보, 중고장터, 부동산, 구인구직까지 필요한 정보를 직접 등록할 수 있습니다.',
                'primaryText' => '생활정보 글쓰기',
                'primaryUrl'  => eottae_home_latest_news_write_url(defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community'),
                'listText'    => '커뮤니티 보기',
                'listUrl'     => G5_BBS_URL.'/board.php?bo_table='.(defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community'),
            ),
        ));

        return array(
            'title'  => '세부 최신소식',
            'desc'   => '커뮤니티와 생활지도에 올라온 새 글을 한곳에서 확인하세요.',
            'tabs'   => $tabs,
            'today'  => eottae_home_latest_news_today_counts($boards),
        );
    }
}

if (!function_exists('eottae_home_contribution_banner_payload')) {
    function eottae_home_contribution_banner_payload()
    {
        $shop_table = function_exists('eottae_shop_table') ? eottae_shop_table() : (defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop');

        return array(
            'eyebrow' => '함께 채우는 세부 생활지도',
            'title'   => '업체·중고·부동산·구인 정보를 직접 등록해보세요',
            'desc'    => '홈 최신소식과 생활지도에 노출되어 필요한 사람에게 더 빨리 닿습니다.',
            'actions' => array(
                array('label' => '업체 등록', 'url' => eottae_home_latest_news_write_url($shop_table), 'tone' => 'primary'),
                array('label' => '중고장터', 'url' => eottae_home_latest_news_write_url(defined('EOTTae_MARKET_TABLE') ? EOTTae_MARKET_TABLE : 'market'), 'tone' => 'market'),
                array('label' => '부동산', 'url' => eottae_home_latest_news_write_url(defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate'), 'tone' => 'estate'),
                array('label' => '구인구직', 'url' => eottae_home_latest_news_write_url(defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job'), 'tone' => 'job'),
            ),
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

        $visible_count = max(1, min(8, (int) $visible_count));
        $pool = eottae_talkroom_list_public_cards(array(
            'limit' => max(24, $visible_count + 12),
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

        return array(
            'title'         => '공개 세부톡방',
            'desc'          => '관심 주제별 공개 톡방에 참여하고 세부 생활 정보를 나눠 보세요.',
            'rooms'         => $pool,
            'visible_count' => $visible_count,
            'list_url'      => function_exists('eottae_talkroom_list_url') ? eottae_talkroom_list_url() : G5_URL.'/talk',
            'create_url'    => function_exists('eottae_talkroom_create_url') ? eottae_talkroom_create_url() : G5_URL.'/page/eottae-talk-create.php',
            'total'         => count($pool),
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
            'latest_news'   => eottae_home_latest_news_payload(6),
            'contribution_banner' => eottae_home_contribution_banner_payload(),
            'talk_rooms'    => eottae_home_public_talkrooms_payload(6),
            'community_url' => G5_BBS_URL.'/board.php?bo_table='.$community_table,
        );
    }
}
