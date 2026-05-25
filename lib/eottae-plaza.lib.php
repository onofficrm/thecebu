<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_plaza_board_table')) {
    function eottae_plaza_board_table()
    {
        return defined('EOTTae_PLAZA_TABLE') ? EOTTae_PLAZA_TABLE : 'plaza';
    }
}

if (!function_exists('eottae_plaza_is_plaza_board')) {
    function eottae_plaza_is_plaza_board($bo_table = '')
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return $bo_table !== '' && $bo_table === eottae_plaza_board_table();
    }
}

if (!function_exists('eottae_plaza_type_options')) {
    /**
     * @return array<int, array{slug: string, label: string}>
     */
    function eottae_plaza_type_options()
    {
        return array(
            array('slug' => '한마디', 'label' => '한마디'),
            array('slug' => '질문', 'label' => '질문'),
            array('slug' => '정보공유', 'label' => '정보공유'),
            array('slug' => '모임제안', 'label' => '모임제안'),
            array('slug' => '홍보/거래', 'label' => '홍보/거래'),
        );
    }
}

if (!function_exists('eottae_plaza_region_options')) {
    /**
     * @return string[]
     */
    function eottae_plaza_region_options()
    {
        return array(
            '세부시티',
            '막탄',
            '라푸라푸',
            '만다우에',
            '탈람반',
            '바닐라드',
            'IT Park',
            '아얄라',
            'SM City',
            '기타',
        );
    }
}

if (!function_exists('eottae_plaza_type_badge_class')) {
    function eottae_plaza_type_badge_class($ca_name)
    {
        $map = array(
            '한마디'   => 'plaza-badge--say',
            '질문'     => 'plaza-badge--question',
            '정보공유' => 'plaza-badge--info',
            '모임제안' => 'plaza-badge--meetup',
            '홍보/거래'=> 'plaza-badge--promo',
            'AI질문'   => 'plaza-badge--ai',
        );
        $key = trim((string) $ca_name);

        return isset($map[$key]) ? $map[$key] : 'plaza-badge--default';
    }
}

if (!function_exists('eottae_plaza_relative_time')) {
    function eottae_plaza_relative_time($datetime)
    {
        if (function_exists('eottae_community_relative_time')) {
            return eottae_community_relative_time($datetime);
        }

        $datetime = trim((string) $datetime);
        if ($datetime === '' || $datetime === '0000-00-00 00:00:00') {
            return '';
        }

        $ts = strtotime($datetime);
        if (!$ts) {
            return substr($datetime, 0, 16);
        }

        $diff = time() - $ts;
        if ($diff < 60) {
            return '방금 전';
        }
        if ($diff < 3600) {
            return (int) floor($diff / 60).'분 전';
        }
        if ($diff < 86400) {
            return (int) floor($diff / 3600).'시간 전';
        }
        if ($diff < 86400 * 7) {
            return (int) floor($diff / 86400).'일 전';
        }

        return date('Y.m.d', $ts);
    }
}

if (!function_exists('eottae_plaza_snippet')) {
    function eottae_plaza_snippet($content, $len = 120)
    {
        if (function_exists('eottae_community_snippet')) {
            return eottae_community_snippet($content, $len);
        }

        $text = trim(strip_tags((string) $content));
        if ($text === '') {
            return '';
        }
        if (function_exists('cut_str')) {
            return cut_str($text, (int) $len, '…');
        }

        return mb_strlen($text, 'UTF-8') > $len
            ? mb_substr($text, 0, $len, 'UTF-8').'…'
            : $text;
    }
}

if (!function_exists('eottae_plaza_list_thumb')) {
    function eottae_plaza_list_thumb($bo_table, $wr_id)
    {
        if (function_exists('eottae_community_list_thumb')) {
            return eottae_community_list_thumb($bo_table, $wr_id);
        }

        return '';
    }
}

if (!function_exists('eottae_plaza_category_tabs')) {
    /**
     * @param array<string, mixed> $board
     * @return array<int, array<string, mixed>>
     */
    function eottae_plaza_category_tabs($board)
    {
        $tabs = array(
            array('slug' => '', 'label' => '전체', 'count' => 0),
        );
        foreach (eottae_plaza_type_options() as $opt) {
            $tabs[] = array(
                'slug'  => $opt['slug'],
                'label' => $opt['label'],
                'count' => 0,
            );
        }

        if (!function_exists('eottae_community_board_table')) {
            return $tabs;
        }

        $bo_table = eottae_plaza_board_table();
        $write_table = $GLOBALS['g5']['write_prefix'].$bo_table;
        $row = sql_fetch(" SHOW TABLES LIKE '".sql_real_escape_string($write_table)."' ", false);
        if (empty($row)) {
            return $tabs;
        }

        $visible_sql = " wr_is_comment = 0 ";
        if (function_exists('eottae_plaza_post_visible_sql')) {
            $visible_sql = eottae_plaza_post_visible_sql();
        }

        $total_row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$write_table}`
            WHERE {$visible_sql}
        ", false);
        $tabs[0]['count'] = (int) ($total_row['cnt'] ?? 0);

        $result = sql_query("
            SELECT ca_name, COUNT(*) AS cnt
            FROM `{$write_table}`
            WHERE {$visible_sql}
              AND TRIM(ca_name) <> ''
            GROUP BY ca_name
        ", false);
        if ($result) {
            $counts = array();
            while ($row = sql_fetch_array($result)) {
                $counts[trim((string) ($row['ca_name'] ?? ''))] = (int) ($row['cnt'] ?? 0);
            }
            foreach ($tabs as $idx => $tab) {
                if ($tab['slug'] === '') {
                    continue;
                }
                $tabs[$idx]['count'] = (int) ($counts[$tab['slug']] ?? 0);
            }
        }

        return $tabs;
    }
}

if (!function_exists('eottae_plaza_post_visible_sql')) {
    function eottae_plaza_post_visible_sql($alias = '')
    {
        $prefix = $alias !== '' ? $alias.'.' : '';
        $hidden = sql_escape_string('hidden');

        return " ({$prefix}wr_is_comment = 0 AND ({$prefix}wr_2 = '' OR {$prefix}wr_2 IS NULL OR {$prefix}wr_2 <> '{$hidden}')) ";
    }
}

if (!function_exists('eottae_plaza_list_url')) {
    function eottae_plaza_list_url($params = array())
    {
        $base = G5_URL.'/plaza/';
        if (empty($params)) {
            return $base;
        }

        return $base.'?'.http_build_query($params);
    }
}

if (!function_exists('eottae_plaza_write_url')) {
    function eottae_plaza_write_url($params = array())
    {
        $base = G5_BBS_URL.'/write.php?bo_table='.eottae_plaza_board_table();
        if (empty($params)) {
            return $base;
        }

        return $base.'&'.http_build_query($params);
    }
}

if (!function_exists('eottae_plaza_talk_list_url')) {
    function eottae_plaza_talk_list_url()
    {
        return function_exists('eottae_talkroom_list_url')
            ? eottae_talkroom_list_url()
            : G5_URL.'/talk';
    }
}

if (!function_exists('eottae_plaza_login_url')) {
    function eottae_plaza_login_url($return_url = '')
    {
        if (function_exists('eottae_login_url')) {
            return eottae_login_url($return_url !== '' ? $return_url : eottae_plaza_list_url());
        }

        return G5_BBS_URL.'/login.php?url='.urlencode($return_url !== '' ? $return_url : eottae_plaza_list_url());
    }
}

if (!function_exists('eottae_plaza_hero_data')) {
    function eottae_plaza_hero_data()
    {
        return array(
            'kicker' => '세부어때 공개 공간',
            'title'  => '세부광장',
            'desc'   => '세부 교민, 여행자, 사업자가 가볍게 한마디 나누는 공개 공간입니다. 질문, 정보공유, 모임제안, 오늘의 세부 이야기를 자유롭게 남겨보세요.',
        );
    }
}

if (!function_exists('eottae_plaza_validate_write_input')) {
    /**
     * @return array{ok: bool, message: string}
     */
    function eottae_plaza_validate_write_input()
    {
        $ca_name = isset($_POST['ca_name']) ? trim(strip_tags((string) $_POST['ca_name'])) : '';
        $region = isset($_POST['wr_1']) ? trim(strip_tags((string) $_POST['wr_1'])) : '';
        $subject = isset($_POST['wr_subject']) ? trim(strip_tags((string) $_POST['wr_subject'])) : '';
        $content = isset($_POST['wr_content']) ? trim((string) $_POST['wr_content']) : '';

        $allowed_types = array();
        foreach (eottae_plaza_type_options() as $opt) {
            $allowed_types[] = $opt['slug'];
        }
        if ($ca_name === '' || !in_array($ca_name, $allowed_types, true)) {
            return array('ok' => false, 'message' => '글 유형을 선택해 주세요.');
        }

        if ($ca_name === 'AI질문') {
            return array('ok' => false, 'message' => '선택할 수 없는 글 유형입니다.');
        }

        if ($region === '' || !in_array($region, eottae_plaza_region_options(), true)) {
            return array('ok' => false, 'message' => '지역을 선택해 주세요.');
        }

        if ($subject === '' && $content === '') {
            return array('ok' => false, 'message' => '제목 또는 내용을 입력해 주세요.');
        }

        if (mb_strlen(strip_tags($content), 'UTF-8') > 2000) {
            return array('ok' => false, 'message' => '내용은 2000자 이내로 작성해 주세요.');
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_plaza_apply_write_defaults')) {
    function eottae_plaza_apply_write_defaults($w)
    {
        if ($w === 'u') {
            return;
        }

        $_POST['wr_2'] = 'visible';
        if (empty($_POST['wr_3'])) {
            $_POST['wr_3'] = 'web';
        }
        if (trim((string) ($_POST['wr_subject'] ?? '')) === '' && trim((string) ($_POST['wr_content'] ?? '')) !== '') {
            $_POST['wr_subject'] = function_exists('cut_str')
                ? cut_str(strip_tags((string) $_POST['wr_content']), 40, '…')
                : mb_substr(strip_tags((string) $_POST['wr_content']), 0, 40, 'UTF-8');
        }
    }
}

if (!function_exists('eottae_plaza_user_can_manage_post')) {
    function eottae_plaza_user_can_manage_post(array $write)
    {
        global $is_admin, $member, $is_member;

        if ($is_admin === 'super') {
            return true;
        }

        if (function_exists('eottae_plaza_ai_is_ai_write_row') && eottae_plaza_ai_is_ai_write_row($write)) {
            return false;
        }

        if (empty($is_member) || empty($member['mb_id'])) {
            return false;
        }

        return !empty($write['mb_id']) && $write['mb_id'] === $member['mb_id'];
    }
}

if (!function_exists('eottae_plaza_sort_options')) {
    function eottae_plaza_sort_options($current_sst = '', $current_sod = 'desc')
    {
        if (function_exists('eottae_community_sort_options')) {
            return eottae_community_sort_options($current_sst, $current_sod);
        }

        return array(
            array('sst' => '', 'sod' => 'desc', 'label' => '최신순', 'active' => $current_sst === ''),
            array('sst' => 'wr_hit', 'sod' => 'desc', 'label' => '조회순', 'active' => $current_sst === 'wr_hit'),
            array('sst' => 'wr_comment', 'sod' => 'desc', 'label' => '댓글순', 'active' => $current_sst === 'wr_comment'),
        );
    }
}

if (!function_exists('eottae_plaza_today_count')) {
    function eottae_plaza_today_count()
    {
        if (function_exists('eottae_community_today_count')) {
            return eottae_community_today_count(eottae_plaza_board_table());
        }

        return 0;
    }
}

if (!function_exists('eottae_plaza_load_assets')) {
    function eottae_plaza_load_assets()
    {
        if (!function_exists('add_stylesheet')) {
            return;
        }

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-plaza.css">', 25);
    }
}

if (!function_exists('eottae_plaza_is_post_visible')) {
    function eottae_plaza_is_post_visible(array $write, $is_super_admin = false)
    {
        if ($is_super_admin) {
            return true;
        }

        $status = trim((string) ($write['wr_2'] ?? ''));

        return $status === '' || $status === 'visible';
    }
}

if (!function_exists('eottae_plaza_is_comment_visible')) {
    function eottae_plaza_is_comment_visible(array $comment, $is_super_admin = false)
    {
        if ($is_super_admin) {
            return true;
        }

        $status = trim((string) ($comment['wr_2'] ?? ''));

        return $status === '' || $status === 'visible';
    }
}

if (!function_exists('eottae_plaza_is_target_visible')) {
    function eottae_plaza_is_target_visible(array $write, $target_type, $is_super_admin = false)
    {
        if (!empty($write['wr_is_comment'])) {
            return eottae_plaza_is_comment_visible($write, $is_super_admin);
        }

        return eottae_plaza_is_post_visible($write, $is_super_admin);
    }
}

if (!function_exists('eottae_plaza_hide_target')) {
    function eottae_plaza_hide_target($target_type, $target_id, $admin_mb_id = '')
    {
        global $g5;
        $target_type = trim((string) $target_type);
        $target_id = (int) $target_id;
        if ($target_id < 1 || !in_array($target_type, array('post', 'comment'), true)) {
            return array('ok' => false, 'message' => '대상 정보가 올바르지 않습니다.');
        }

        $write_table = $g5['write_prefix'].eottae_plaza_board_table();
        $is_comment = $target_type === 'comment' ? 1 : 0;
        $hidden = $target_type === 'comment' ? 'deleted' : 'hidden';

        $ok = (bool) sql_query("
            UPDATE `{$write_table}` SET
                wr_2 = '".sql_escape_string($hidden)."'
            WHERE wr_id = '{$target_id}'
              AND wr_is_comment = '{$is_comment}'
        ", false);

        return array(
            'ok'      => $ok,
            'message' => $ok ? '삭제 처리했습니다.' : '삭제 처리에 실패했습니다.',
        );
    }
}

if (!function_exists('eottae_plaza_admin_posts_url')) {
    function eottae_plaza_admin_posts_url($params = array())
    {
        $base = G5_URL.'/page/eottae-admin-plaza-posts.php';
        if (empty($params)) {
            return $base;
        }

        return $base.'?'.http_build_query($params);
    }
}

if (!function_exists('eottae_plaza_admin_reports_url')) {
    function eottae_plaza_admin_reports_url($status = 'pending')
    {
        return G5_URL.'/page/eottae-admin-plaza-reports.php?status='.urlencode((string) $status);
    }
}

if (!function_exists('eottae_plaza_admin_list_posts')) {
    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    function eottae_plaza_admin_list_posts(array $filters = array(), $limit = 100, $offset = 0)
    {
        global $g5;
        include_once G5_LIB_PATH.'/eottae-plaza-likes.lib.php';
        include_once G5_LIB_PATH.'/eottae-plaza-report.lib.php';

        $limit = max(1, min(200, (int) $limit));
        $offset = max(0, (int) $offset);
        $write_table = $g5['write_prefix'].eottae_plaza_board_table();
        $member_table = G5_TABLE_PREFIX.'member';

        $where = array('w.wr_is_comment = 0');
        $ca_name = trim(strip_tags((string) ($filters['ca_name'] ?? '')));
        $region = trim(strip_tags((string) ($filters['region'] ?? '')));
        $author = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($filters['mb_id'] ?? ''));
        $reported_only = !empty($filters['reported_only']);

        if ($ca_name !== '') {
            $where[] = "w.ca_name = '".sql_escape_string($ca_name)."'";
        }
        if ($region !== '') {
            $where[] = "w.wr_1 = '".sql_escape_string($region)."'";
        }
        if ($author !== '') {
            $where[] = "w.mb_id = '".sql_escape_string($author)."'";
        }

        $where_sql = implode(' AND ', $where);
        $result = sql_query("
            SELECT w.*, m.mb_nick
            FROM `{$write_table}` w
            LEFT JOIN `{$member_table}` m ON m.mb_id = w.mb_id
            WHERE {$where_sql}
            ORDER BY w.wr_datetime DESC, w.wr_id DESC
            LIMIT {$offset}, {$limit}
        ", false);

        $items = array();
        $wr_ids = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $wr_ids[] = (int) ($row['wr_id'] ?? 0);
                $items[] = $row;
            }
        }

        $like_counts = eottae_plaza_like_counts_batch($wr_ids);
        $report_counts = eottae_plaza_report_counts_batch($wr_ids);

        $formatted = array();
        foreach ($items as $row) {
            $wr_id = (int) ($row['wr_id'] ?? 0);
            $report_cnt = (int) ($report_counts[$wr_id] ?? 0);
            if ($reported_only && $report_cnt < 1) {
                continue;
            }

            $formatted[] = array(
                'wr_id'        => $wr_id,
                'ca_name'      => get_text($row['ca_name'] ?? ''),
                'region'       => get_text($row['wr_1'] ?? ''),
                'subject'      => get_text($row['wr_subject'] ?? ''),
                'author'       => get_text($row['mb_nick'] ?? $row['wr_name'] ?? ''),
                'mb_id'        => get_text($row['mb_id'] ?? ''),
                'comment_count'=> (int) ($row['wr_comment'] ?? 0),
                'like_count'   => (int) ($like_counts[$wr_id] ?? 0),
                'report_count' => $report_cnt,
                'hit'          => (int) ($row['wr_hit'] ?? 0),
                'status'       => trim((string) ($row['wr_2'] ?? '')),
                'is_hidden'    => trim((string) ($row['wr_2'] ?? '')) === 'hidden' ? 1 : 0,
                'datetime'     => trim((string) ($row['wr_datetime'] ?? '')),
                'href'         => get_pretty_url(eottae_plaza_board_table(), $wr_id),
            );
        }

        return $formatted;
    }
}

if (!function_exists('eottae_plaza_talk_create_url')) {
    function eottae_plaza_talk_create_url()
    {
        return function_exists('eottae_talkroom_create_url')
            ? eottae_talkroom_create_url()
            : G5_URL.'/page/eottae-talk-create.php';
    }
}

if (!function_exists('eottae_plaza_rules_items')) {
    /**
     * @return string[]
     */
    function eottae_plaza_rules_items()
    {
        return array(
            '개인정보를 공개하지 마세요.',
            '욕설, 비방, 분쟁 유도 글은 삭제될 수 있습니다.',
            '반복 홍보글은 제한될 수 있습니다.',
            '중고거래, 구인구직, 부동산 글은 관련 톡방이나 게시판으로 이동될 수 있습니다.',
            '불법/유해 정보는 즉시 삭제될 수 있습니다.',
        );
    }
}

if (!function_exists('eottae_plaza_related_room_type_categories')) {
    /**
     * 글 유형 → 톡방 category slug 우선순위
     *
     * @return array<string, string[]>
     */
    function eottae_plaza_related_room_type_categories()
    {
        return array(
            '모임제안' => array('sports', 'hobby'),
            '질문'     => array('travel', 'expat_life', 'food'),
            '홍보/거래'=> array('used', 'business', 'job'),
            '정보공유' => array('food', 'expat_life', 'travel'),
            '한마디'   => array('expat_life', 'hobby', 'etc'),
        );
    }
}

if (!function_exists('eottae_plaza_related_room_keyword_rules')) {
    /**
     * @return array<int, array{keywords: string[], categories: string[]}>
     */
    function eottae_plaza_related_room_keyword_rules()
    {
        return array(
            array(
                'keywords'   => array('축구', '족구', '골프', '탁구', '배드민턴'),
                'categories' => array('sports', 'hobby'),
            ),
            array(
                'keywords'   => array('아이', '학교', '병원', '영어캠프', '학원', '맘수다', '육아'),
                'categories' => array('parenting'),
            ),
            array(
                'keywords'   => array('렌트', '콘도', '집', '부동산', '월세'),
                'categories' => array('estate'),
            ),
            array(
                'keywords'   => array('직원', '사업', '창업', '마케팅', '세금'),
                'categories' => array('business'),
            ),
            array(
                'keywords'   => array('공항픽업', '호핑', '마사지', '환전', '숙소'),
                'categories' => array('travel'),
            ),
            array(
                'keywords'   => array('냉장고', '오토바이', '가구', '팝니다', '삽니다'),
                'categories' => array('used'),
            ),
        );
    }
}

if (!function_exists('eottae_plaza_guess_related_room_categories')) {
    /**
     * @return string[]
     */
    function eottae_plaza_guess_related_room_categories($ca_name, $plain_text)
    {
        $categories = array();
        $ca_name = trim((string) $ca_name);
        $plain_text = trim((string) $plain_text);

        $type_map = eottae_plaza_related_room_type_categories();
        if ($ca_name !== '' && isset($type_map[$ca_name])) {
            $categories = array_merge($categories, $type_map[$ca_name]);
        }

        foreach (eottae_plaza_related_room_keyword_rules() as $rule) {
            foreach ($rule['keywords'] as $keyword) {
                if ($keyword !== '' && mb_strpos($plain_text, $keyword, 0, 'UTF-8') !== false) {
                    $categories = array_merge($categories, $rule['categories']);
                    break;
                }
            }
        }

        $deduped = array();
        foreach ($categories as $code) {
            $code = preg_replace('/[^a-z0-9_]/', '', (string) $code);
            if ($code !== '' && !in_array($code, $deduped, true)) {
                $deduped[] = $code;
            }
        }

        return $deduped;
    }
}

if (!function_exists('eottae_plaza_fetch_public_rooms_by_categories')) {
    /**
     * @param string[] $categories
     * @return array<int, array<string, mixed>>
     */
    function eottae_plaza_fetch_public_rooms_by_categories(array $categories, $limit = 3)
    {
        include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

        $limit = max(1, min(3, (int) $limit));
        $categories = array_values(array_unique(array_filter(array_map(function ($code) {
            return preg_replace('/[^a-z0-9_]/', '', (string) $code);
        }, $categories))));

        if (empty($categories)) {
            return array();
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            return array();
        }

        $statuses = eottae_talkroom_public_statuses();
        $status_sql = array();
        foreach ($statuses as $status) {
            $status_sql[] = "'".sql_escape_string($status)."'";
        }
        $status_in = implode(',', $status_sql);

        $cat_sql = array();
        foreach ($categories as $code) {
            $cat_sql[] = "'".sql_escape_string($code)."'";
        }
        $cat_in = implode(',', $cat_sql);

        $member_table = G5_TABLE_PREFIX.'member';
        $result = sql_query("
            SELECT r.*, m.mb_nick AS owner_nick
            FROM `{$tables['rooms']}` r
            LEFT JOIN `{$member_table}` m ON m.mb_id = r.owner_mb_id
            WHERE r.status IN ({$status_in})
              AND r.visibility = 'public'
              AND r.category IN ({$cat_in})
            ORDER BY
                CASE WHEN r.updated_at IS NULL OR r.updated_at = '0000-00-00 00:00:00' THEN 1 ELSE 0 END,
                r.updated_at DESC,
                r.approved_at DESC,
                r.room_id DESC
            LIMIT {$limit}
        ", false);

        $raw_rows = array();
        $room_ids = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $room_ids[] = (int) ($row['room_id'] ?? 0);
                $raw_rows[] = $row;
            }
        }

        if (empty($raw_rows)) {
            return array();
        }

        $member_counts = eottae_talkroom_member_counts($room_ids);
        $post_counts = eottae_talkroom_post_counts($room_ids);
        $latest_posts = eottae_talkroom_latest_post_times($room_ids);

        $rooms = array();
        foreach ($raw_rows as $row) {
            $room_id = (int) ($row['room_id'] ?? 0);
            $rooms[] = eottae_talkroom_format_card($row, array(
                'member_count'   => isset($member_counts[$room_id]) ? $member_counts[$room_id] : 0,
                'post_count'     => isset($post_counts[$room_id]) ? $post_counts[$room_id] : 0,
                'latest_post_at' => isset($latest_posts[$room_id]) ? $latest_posts[$room_id] : '',
            ));
        }

        return $rooms;
    }
}

if (!function_exists('eottae_plaza_related_rooms')) {
    /**
     * 규칙 기반 관련 톡방 추천 (AI 교체 가능)
     *
     * @param array<string, mixed> $post
     * @return array<int, array<string, mixed>>
     */
    function eottae_plaza_related_rooms(array $post, $limit = 3)
    {
        $limit = max(1, min(3, (int) $limit));
        $plain = trim(strip_tags(
            (string) ($post['wr_subject'] ?? '').' '.(string) ($post['wr_content'] ?? '')
        ));
        $categories = eottae_plaza_guess_related_room_categories(
            trim((string) ($post['ca_name'] ?? '')),
            $plain
        );

        $rooms = eottae_plaza_fetch_public_rooms_by_categories($categories, $limit);
        if (!empty($rooms)) {
            return $rooms;
        }

        include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        $fallback = eottae_talkroom_list_public(array('limit' => $limit));
        $rows = isset($fallback['rows']) && is_array($fallback['rows']) ? $fallback['rows'] : array();
        $public_rows = array();
        foreach ($rows as $row) {
            if (($row['visibility'] ?? 'public') !== 'public') {
                continue;
            }
            $public_rows[] = $row;
            if (count($public_rows) >= $limit) {
                break;
            }
        }

        return $public_rows;
    }
}

if (!function_exists('get_plaza_related_rooms')) {
    function get_plaza_related_rooms(array $post, $limit = 3)
    {
        return eottae_plaza_related_rooms($post, $limit);
    }
}

if (!function_exists('eottae_plaza_list_home_feed')) {
    /**
     * 메인 노출용 최신 글 (가벼운 조회)
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_plaza_list_home_feed($limit = 5)
    {
        global $g5;

        $limit = max(1, min(10, (int) $limit));
        $write_table = $g5['write_prefix'].eottae_plaza_board_table();
        $exists = sql_fetch(" SHOW TABLES LIKE '".sql_escape_string($write_table)."' ", false);
        if (empty($exists)) {
            return array();
        }

        $visible = eottae_plaza_post_visible_sql();
        $result = sql_query("
            SELECT wr_id, ca_name, wr_subject, wr_comment, wr_datetime, mb_id, wr_3, wr_name
            FROM `{$write_table}`
            WHERE {$visible}
            ORDER BY wr_datetime DESC, wr_id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        $wr_ids = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $wr_ids[] = (int) ($row['wr_id'] ?? 0);
                $rows[] = $row;
            }
        }

        if (empty($rows)) {
            return array();
        }

        include_once G5_LIB_PATH.'/eottae-plaza-likes.lib.php';
        include_once G5_LIB_PATH.'/eottae-plaza-ai.lib.php';
        $like_counts = eottae_plaza_like_counts_batch($wr_ids);

        $items = array();
        foreach ($rows as $row) {
            $wr_id = (int) ($row['wr_id'] ?? 0);
            $ca_name = get_text($row['ca_name'] ?? '');
            $is_ai = function_exists('eottae_plaza_ai_is_ai_write_row') && eottae_plaza_ai_is_ai_write_row($row);
            $items[] = array(
                'wr_id'         => $wr_id,
                'ca_name'       => $ca_name,
                'type_label'    => $is_ai ? '[AI질문]' : ($ca_name !== '' ? '['.$ca_name.']' : ''),
                'type_class'    => $is_ai ? 'plaza-badge--ai' : eottae_plaza_type_badge_class($ca_name),
                'subject'       => get_text($row['wr_subject'] ?? ''),
                'comment_count' => (int) ($row['wr_comment'] ?? 0),
                'like_count'    => (int) ($like_counts[$wr_id] ?? 0),
                'time_label'    => eottae_plaza_relative_time($row['wr_datetime'] ?? ''),
                'href'          => get_pretty_url(eottae_plaza_board_table(), $wr_id),
                'is_ai'         => $is_ai ? 1 : 0,
            );
        }

        return $items;
    }
}

if (!function_exists('eottae_plaza_home_hero_recent_posts')) {
    /**
     * 홈 히어로 — 광장 글 기반 관련 톡방 추천용 최근 글
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_plaza_home_hero_recent_posts($limit = 6)
    {
        global $g5;

        $limit = max(1, min(10, (int) $limit));
        $write_table = $g5['write_prefix'].eottae_plaza_board_table();
        $exists = sql_fetch(" SHOW TABLES LIKE '".sql_escape_string($write_table)."' ", false);
        if (empty($exists)) {
            return array();
        }

        $visible = eottae_plaza_post_visible_sql();
        $result = sql_query("
            SELECT wr_id, ca_name, wr_subject, wr_content
            FROM `{$write_table}`
            WHERE {$visible}
            ORDER BY wr_datetime DESC, wr_id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_plaza_home_hero_payload')) {
    /**
     * 홈 히어로 — 세부광장 연결·인기 톡방
     *
     * @return array<string, mixed>
     */
    function eottae_plaza_home_hero_payload($linked_limit = 3, $hot_limit = 3)
    {
        include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

        $linked_limit = max(1, min(6, (int) $linked_limit));
        $hot_limit = max(1, min(6, (int) $hot_limit));
        $linked_rows = array();
        $seen = array();

        foreach (eottae_plaza_home_hero_recent_posts(6) as $post) {
            foreach (eottae_plaza_related_rooms($post, 2) as $room) {
                $room_id = (int) ($room['room_id'] ?? 0);
                if ($room_id < 1 || isset($seen[$room_id])) {
                    continue;
                }
                $seen[$room_id] = true;
                $linked_rows[] = $room;
                if (count($linked_rows) >= $linked_limit) {
                    break 2;
                }
            }
        }

        $pool = eottae_talkroom_list_public_cards(array(
            'limit' => max(24, $hot_limit + count($linked_rows) + 8),
            'page'  => 1,
        ));
        $hot_pool = array();
        foreach ($pool as $row) {
            $room_id = (int) ($row['room_id'] ?? 0);
            if ($room_id < 1 || isset($seen[$room_id])) {
                continue;
            }
            $hot_pool[] = $row;
        }

        usort($hot_pool, function ($a, $b) {
            $score_cmp = eottae_talkroom_home_hero_hot_score($b) <=> eottae_talkroom_home_hero_hot_score($a);
            if ($score_cmp !== 0) {
                return $score_cmp;
            }

            return (int) ($b['post_count'] ?? 0) <=> (int) ($a['post_count'] ?? 0);
        });
        $hot_rows = array_slice($hot_pool, 0, $hot_limit);

        return array(
            'variant'    => 'plaza',
            'new'        => $linked_rows,
            'hot'        => $hot_rows,
            'list_url'   => eottae_plaza_talk_list_url(),
            'create_url' => eottae_talkroom_create_url(),
            'plaza_url'  => eottae_plaza_list_url(),
        );
    }
}
