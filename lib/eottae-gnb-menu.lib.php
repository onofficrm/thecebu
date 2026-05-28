<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_gnb_member_posts_url')) {
    function eottae_gnb_member_posts_url($mb_id = '')
    {
        global $member;

        if ($mb_id === '' && !empty($member['mb_id'])) {
            $mb_id = (string) $member['mb_id'];
        }

        if ($mb_id === '') {
            return function_exists('eottae_login_url')
                ? eottae_login_url(function_exists('eottae_mypage_url') ? eottae_mypage_url() : '')
                : G5_BBS_URL.'/login.php';
        }

        return eottae_board_list_url(
            eottae_community_board_table(),
            array('sfl' => 'mb_id,1', 'stx' => $mb_id)
        );
    }
}

if (!function_exists('eottae_gnb_member_profile_url')) {
    function eottae_gnb_member_profile_url()
    {
        global $is_member;

        if (empty($is_member)) {
            return function_exists('eottae_login_url')
                ? eottae_login_url(G5_BBS_URL.'/member_confirm.php?url='.urlencode(G5_BBS_URL.'/register_form.php'))
                : G5_BBS_URL.'/login.php';
        }

        return G5_BBS_URL.'/member_confirm.php?url='.urlencode(G5_BBS_URL.'/register_form.php');
    }
}

if (!function_exists('eottae_gnb_member_login_url')) {
    function eottae_gnb_member_login_url($return_url = '')
    {
        if ($return_url === '') {
            $return_url = function_exists('eottae_current_url') ? eottae_current_url() : G5_URL;
        }

        return function_exists('eottae_login_url') ? eottae_login_url($return_url) : G5_BBS_URL.'/login.php';
    }
}

if (!function_exists('eottae_gnb_nav_menu')) {
    /**
     * GNB 메뉴 트리 (상위 + children)
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_gnb_nav_menu()
    {
        global $is_member, $member;

        $shop_table = eottae_shop_table();
        $community_table = eottae_community_board_table();
        $free_table = function_exists('eottae_free_board_table') ? eottae_free_board_table() : 'free';
        $review_table = defined('EOTTae_REVIEW_TABLE') ? EOTTae_REVIEW_TABLE : 'review';
        $people_table = defined('EOTTae_PEOPLE_TABLE') ? EOTTae_PEOPLE_TABLE : 'people';
        $event_table = defined('EOTTae_EVENT_TABLE') ? EOTTae_EVENT_TABLE : 'event';
        $report_table = defined('EOTTae_REPORT_TABLE') ? EOTTae_REPORT_TABLE : 'report';
        $news_list_url = function_exists('eottae_news_list_url') ? eottae_news_list_url() : eottae_board_list_url('news');
        $market_table = defined('EOTTae_MARKET_TABLE') ? EOTTae_MARKET_TABLE : 'market';
        $job_table = defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job';
        $estate_table = defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate';
        $gallery_table = defined('EOTTae_GALLERY_TABLE') ? EOTTae_GALLERY_TABLE : 'gallery';
        $youtube_table = defined('EOTTae_YOUTUBE_TABLE') ? EOTTae_YOUTUBE_TABLE : 'youtube';
        $cebu_map_url = G5_URL.'/cebu-map/';
        $golf_list_url = function_exists('eottae_golf_join_list_url') ? eottae_golf_join_list_url() : G5_URL.'/golf-join/';
        $golf_create_url = function_exists('eottae_golf_join_create_url') ? eottae_golf_join_create_url() : G5_URL.'/golf-join/create';
        $column_list_url = function_exists('eottae_column_list_url') ? eottae_column_list_url() : G5_URL.'/column/';
        $columnist_recruit_url = function_exists('eottae_columnist_recruit_url')
            ? eottae_columnist_recruit_url()
            : G5_URL.'/columnist/';
        $column_apply_url = function_exists('eottae_column_apply_url')
            ? eottae_column_apply_url()
            : G5_URL.'/page/eottae-column-apply.php';
        $mypage_url = function_exists('eottae_mypage_url') ? eottae_mypage_url() : G5_URL.'/page/eottae-mypage.php';
        $message_url = function_exists('eottae_message_url') ? eottae_message_url() : G5_URL.'/page/eottae-messages.php';
        $message_label = '쪽지';
        if (!empty($is_member) && !empty($member['mb_id']) && is_file(G5_LIB_PATH.'/eottae-message.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-message.lib.php';
            $message_unread_count = function_exists('eottae_message_unread_count') ? eottae_message_unread_count($member['mb_id']) : 0;
            if ($message_unread_count > 0) {
                $message_label .= ' ('.number_format($message_unread_count).')';
            }
        }
        $talk_applies_url = function_exists('eottae_talkroom_apply_status_url')
            ? eottae_talkroom_apply_status_url()
            : G5_URL.'/page/eottae-talk-applies.php';

        return array(
            array(
                'key'   => 'home',
                'label' => '홈',
                'href'  => G5_URL.'/',
            ),
            array(
                'key'      => 'shop',
                'label'    => '내주변',
                'href'     => eottae_board_list_url($shop_table),
                'children' => array(
                    array('key' => 'shop_food', 'label' => '내 주변 맛집', 'href' => eottae_shop_category_url('맛집')),
                    array('key' => 'shop_all', 'label' => '내 주변 업체', 'href' => eottae_board_list_url($shop_table)),
                    array('key' => 'shop_hospital', 'label' => '내 주변 병원', 'href' => eottae_shop_category_url('병원')),
                    array('key' => 'shop_convenience', 'label' => '내 주변 생활편의', 'href' => eottae_shop_category_url('마트')),
                ),
            ),
            array(
                'key'      => 'community',
                'label'    => '커뮤니티',
                'href'     => eottae_community_list_url(),
                'children' => array(
                    array('key' => 'community_news', 'label' => '필리핀뉴스', 'href' => $news_list_url),
                    array('key' => 'community_life', 'label' => '생활정보', 'href' => eottae_board_list_url($community_table)),
                    array('key' => 'community_free', 'label' => '자유게시판', 'href' => eottae_board_list_url($free_table)),
                    array('key' => 'community_review', 'label' => '업체리뷰', 'href' => eottae_board_list_url($review_table)),
                    array('key' => 'community_people', 'label' => '사람찾기', 'href' => eottae_board_list_url($people_table)),
                    array('key' => 'community_event', 'label' => '이벤트/프로모션', 'href' => eottae_board_list_url($event_table)),
                    array('key' => 'community_report', 'label' => '제보함', 'href' => eottae_board_list_url($report_table)),
                ),
            ),
            array(
                'key'      => 'cebu_map',
                'label'    => '생활지도',
                'href'     => $cebu_map_url,
                'emphasis' => 'accent',
                'children' => array(
                    array('key' => 'cebu_map_all', 'label' => '전체지도', 'href' => $cebu_map_url),
                    array('key' => 'cebu_map_job', 'label' => '구인구직', 'href' => $cebu_map_url.'?type=job'),
                    array('key' => 'cebu_map_market', 'label' => '중고장터', 'href' => $cebu_map_url.'?type=market'),
                    array('key' => 'cebu_map_estate', 'label' => '부동산', 'href' => $cebu_map_url.'?type=estate'),
                ),
            ),
            array(
                'key'      => 'golf_join',
                'label'    => '골프조인',
                'href'     => $golf_list_url,
                'children' => array(
                    array('key' => 'golf_join_create', 'label' => '조인 모집', 'href' => $golf_create_url),
                    array('key' => 'golf_join_open', 'label' => '모집중', 'href' => $golf_list_url.'?exclude_full=1'),
                    array('key' => 'golf_join_closed', 'label' => '마감', 'href' => $golf_list_url.'?status=closed'),
                    array('key' => 'golf_join_course', 'label' => '골프장 정보', 'href' => eottae_shop_category_url('골프')),
                ),
            ),
            array(
                'key'      => 'column',
                'label'    => function_exists('eottae_column_menu_label') ? eottae_column_menu_label() : '컬럼',
                'href'     => $column_list_url,
                'children' => array(
                    array('key' => 'column_all', 'label' => '전체 컬럼', 'href' => $column_list_url),
                    array('key' => 'column_recruit', 'label' => '컬럼리스트 소개', 'href' => $columnist_recruit_url),
                    array('key' => 'column_apply', 'label' => '컬럼리스트 신청', 'href' => $column_apply_url),
                ),
            ),
            array(
                'key'      => 'media',
                'label'    => '미디어',
                'href'     => eottae_board_list_url($gallery_table),
                'children' => array(
                    array('key' => 'media_gallery', 'label' => '갤러리', 'href' => eottae_board_list_url($gallery_table)),
                    array('key' => 'media_youtube', 'label' => '유튜브', 'href' => eottae_board_list_url($youtube_table)),
                ),
            ),
            array(
                'key'      => 'mypage',
                'label'    => 'MY',
                'href'     => $mypage_url,
                'children' => array(
                    array('key' => 'mypage_profile', 'label' => '내 프로필', 'href' => eottae_gnb_member_profile_url()),
                    array('key' => 'mypage_posts', 'label' => '내가 쓴 글', 'href' => eottae_gnb_member_posts_url()),
                    array('key' => 'mypage_shop', 'label' => '내 업체', 'href' => $mypage_url, 'login_required' => true),
                    array('key' => 'mypage_applies', 'label' => '내 신청내역', 'href' => $talk_applies_url, 'login_required' => true),
                    array('key' => 'mypage_memo', 'label' => $message_label, 'href' => $message_url, 'login_required' => true),
                ),
            ),
        );
    }
}

if (!function_exists('eottae_gnb_nav_item_active')) {
    function eottae_gnb_nav_item_active(array $item)
    {
        $key = isset($item['key']) ? (string) $item['key'] : '';
        if ($key === '') {
            return false;
        }

        if (function_exists('eottae_gnb_link_is_active') && eottae_gnb_link_is_active($key)) {
            return true;
        }

        if (!empty($item['children']) && is_array($item['children'])) {
            foreach ($item['children'] as $child) {
                if (is_array($child) && eottae_gnb_nav_item_active($child)) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (!function_exists('eottae_gnb_nav_item_href')) {
    function eottae_gnb_nav_item_href(array $item)
    {
        global $is_member;

        $href = isset($item['href']) ? (string) $item['href'] : '#';
        if (!empty($item['login_required']) && empty($is_member)) {
            return eottae_gnb_member_login_url($href);
        }

        return $href;
    }
}

if (!function_exists('eottae_home_gnb_mobile_menu_payload')) {
    /**
     * 홈(빌더) — 전체메뉴 드로어용 GNB 트리 JSON
     *
     * @return array<string, mixed>
     */
    function eottae_home_gnb_mobile_menu_payload()
    {
        if (!function_exists('eottae_gnb_nav_item_label') && is_file(G5_PATH.'/components/eottae/gnb-nav-items.php')) {
            include_once G5_PATH.'/components/eottae/gnb-nav-items.php';
        }

        $skip_keys = array('community_free', 'people');
        $items = array();

        foreach (eottae_gnb_nav_menu() as $item) {
            if (!is_array($item) || !empty($item['desktop_action'])) {
                continue;
            }

            $key = isset($item['key']) ? (string) $item['key'] : '';
            if ($key !== '' && in_array($key, $skip_keys, true)) {
                continue;
            }

            $children = array();
            if (!empty($item['children']) && is_array($item['children'])) {
                foreach ($item['children'] as $child) {
                    if (!is_array($child)) {
                        continue;
                    }
                    $child_key = isset($child['key']) ? (string) $child['key'] : '';
                    if ($child_key !== '' && in_array($child_key, $skip_keys, true)) {
                        continue;
                    }
                    $children[] = array(
                        'key'   => $child_key,
                        'label' => eottae_gnb_nav_item_label($child['label'] ?? ''),
                        'href'  => eottae_gnb_nav_item_href($child),
                    );
                }
            }

            $items[] = array(
                'key'      => $key,
                'label'    => eottae_gnb_nav_item_label($item['label'] ?? ''),
                'href'     => eottae_gnb_nav_item_href($item),
                'children' => $children,
            );
        }

        $auth = function_exists('eottae_auth_context') ? eottae_auth_context() : array('is_member' => false);
        $is_member = !empty($auth['is_member']);

        return array(
            'title'        => '전체메뉴',
            'items'        => $items,
            'is_member'    => $is_member,
            'login_url'    => function_exists('eottae_login_url') ? eottae_login_url(G5_URL.'/') : G5_BBS_URL.'/login.php',
            'logout_url'   => G5_BBS_URL.'/logout.php',
            'register_url' => function_exists('eottae_register_url') ? eottae_register_url() : G5_BBS_URL.'/register.php',
            'mypage_url'   => function_exists('eottae_mypage_url') ? eottae_mypage_url() : G5_URL.'/page/eottae-mypage.php',
        );
    }
}
