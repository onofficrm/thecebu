<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 커뮤니티 허브 — 생활정보(community)·자유(free)·리뷰(review)·사람찾기(people)·이벤트(event)
 * 분류(ca_name) 대신 독립 게시판 + 공통 eottae-community 스킨 UI
 *
 * 기존 community 게시판 ca_name 데이터는 DB에 그대로 두고 화면에서만 분류 UI를 숨깁니다.
 * 분류별 글을 새 게시판으로 옮기려면 별도 마이그레이션 스크립트가 필요합니다(자동 실행하지 않음).
 *
 * --- 관리자 게시판 생성 체크리스트 (없을 때만) ---
 * 그누보드 관리자 > 게시판관리 > 게시판추가. 그룹: community(커뮤니티)
 *
 * | bo_table   | 게시판 제목        | PC/모바일 스킨      | 분류 | 비고 |
 * |------------|-------------------|---------------------|------|------|
 * | community  | 생활정보          | eottae-community    | 없음 | 기존 커뮤니티 게시판 재사용 가능 |
 * | free       | 자유게시판        | eottae-community    | 없음 | |
 * | review     | 업체리뷰          | eottae-community    | 없음 | 업체 연동 리뷰(wr_1~5)와 공존 시 스킨·필드 확인 |
 * | people     | 사람찾기          | eottae-community    | 없음 | |
 * | event      | 이벤트/프로모션   | eottae-community    | 없음 | |
 *
 * 권한(권장): 목록/읽기 1(비회원), 쓰기 2(회원) — 사이트 정책에 맞게 조정.
 * 메뉴: 환경설정 > 메뉴설정 — 상위 「커뮤니티」 하위에 위 5개 링크 (/bbs/board.php?bo_table=…)
 */

if (!function_exists('eottae_community_hub_board_tables')) {
    /**
     * @return array<int, string>
     */
    function eottae_community_hub_board_tables()
    {
        return array('community', 'free', 'review', 'people', 'event');
    }
}

if (!function_exists('eottae_community_hub_board_defs')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_community_hub_board_defs()
    {
        return array(
            array(
                'bo_table' => defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community',
                'label'    => '생활정보',
                'kicker'   => '세부 커뮤니티',
                'title'    => '세부 생활정보',
                'desc'     => '세부 교민과 여행자가 함께 나누는 생생한 로컬 생활정보 게시판입니다.',
                'image'    => '1555881400-0d2f29490987',
            ),
            array(
                'bo_table' => function_exists('eottae_free_board_table') ? eottae_free_board_table() : 'free',
                'label'    => '자유게시판',
                'kicker'   => '자유게시판',
                'title'    => '자유게시판',
                'desc'     => '세부 생활, 일상, 질문, 소통을 자유롭게 나누는 게시판입니다.',
                'image'    => '1555881400-0d2f29490987',
            ),
            array(
                'bo_table' => defined('EOTTae_REVIEW_TABLE') ? EOTTae_REVIEW_TABLE : 'review',
                'label'    => '업체리뷰',
                'kicker'   => '업체리뷰',
                'title'    => '업체리뷰',
                'desc'     => '세부의 식당, 마사지, 병원, 업체 이용후기를 공유하는 게시판입니다.',
                'image'    => '1414235077428-338989a43e79',
            ),
            array(
                'bo_table' => defined('EOTTae_PEOPLE_TABLE') ? EOTTae_PEOPLE_TABLE : 'people',
                'label'    => '사람찾기',
                'kicker'   => '사람찾기',
                'title'    => '사람찾기',
                'desc'     => '세부에서 사람을 찾거나 연락을 연결할 수 있는 게시판입니다.',
                'image'    => '1507525428034-b723cf961d3e',
            ),
            array(
                'bo_table' => defined('EOTTae_EVENT_TABLE') ? EOTTae_EVENT_TABLE : 'event',
                'label'    => '이벤트/프로모션',
                'kicker'   => '이벤트/프로모션',
                'title'    => '이벤트/프로모션',
                'desc'     => '세부 지역 이벤트, 할인, 프로모션 정보를 공유하는 게시판입니다.',
                'image'    => '1511795409834-ef04bbd61622',
            ),
        );
    }
}

if (!function_exists('eottae_community_hub_board_def')) {
    function eottae_community_hub_board_def($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        foreach (eottae_community_hub_board_defs() as $def) {
            if (($def['bo_table'] ?? '') === $bo_table) {
                return $def;
            }
        }

        return null;
    }
}

if (!function_exists('eottae_is_community_hub_board')) {
    function eottae_is_community_hub_board($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return $bo_table !== '' && in_array($bo_table, eottae_community_hub_board_tables(), true);
    }
}

if (!function_exists('eottae_community_hub_board_count')) {
    function eottae_community_hub_board_count($bo_table)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '' || empty($g5['write_prefix'])) {
            return 0;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        if (!sql_query(" DESCRIBE `{$write_table}` ", false)) {
            return 0;
        }

        $row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$write_table}` WHERE wr_is_comment = 0 ");

        return isset($row['cnt']) ? (int) $row['cnt'] : 0;
    }
}

if (!function_exists('eottae_community_hub_total_count')) {
    function eottae_community_hub_total_count()
    {
        $total = 0;
        foreach (eottae_community_hub_board_tables() as $bo_table) {
            $total += eottae_community_hub_board_count($bo_table);
        }

        return $total;
    }
}

if (!function_exists('eottae_community_hub_primary_table')) {
    function eottae_community_hub_primary_table()
    {
        return defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community';
    }
}

if (!function_exists('eottae_community_hub_list_url')) {
    function eottae_community_hub_list_url($bo_table, $params = array())
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            $bo_table = eottae_community_hub_primary_table();
        }

        $primary = eottae_community_hub_primary_table();
        if ($bo_table === $primary && !isset($params['hub'])) {
            $params['hub'] = 'community';
        }

        if (function_exists('eottae_board_list_url')) {
            return eottae_board_list_url($bo_table, $params);
        }

        $url = G5_BBS_URL.'/board.php?bo_table='.$bo_table;
        if (!empty($params)) {
            $url .= '&'.http_build_query($params);
        }

        return $url;
    }
}

if (!function_exists('eottae_community_hub_all_url')) {
    function eottae_community_hub_all_url($params = array())
    {
        $params['hub'] = 'all';

        return eottae_community_hub_list_url(eottae_community_hub_primary_table(), $params);
    }
}

if (!function_exists('eottae_community_hub_is_all_view')) {
    function eottae_community_hub_is_all_view($bo_table = '')
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            $bo_table = eottae_community_hub_primary_table();
        }

        if ($bo_table !== eottae_community_hub_primary_table()) {
            return false;
        }

        return isset($_GET['hub']) && trim((string) $_GET['hub']) === 'all';
    }
}

if (!function_exists('eottae_community_hub_board_label')) {
    function eottae_community_hub_board_label($bo_table)
    {
        $def = eottae_community_hub_board_def($bo_table);

        return $def ? (string) ($def['label'] ?? $bo_table) : $bo_table;
    }
}

if (!function_exists('eottae_community_hub_get_board')) {
    function eottae_community_hub_get_board($bo_table)
    {
        static $cache = array();

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return array();
        }

        if (isset($cache[$bo_table])) {
            return $cache[$bo_table];
        }

        global $g5;
        $cache[$bo_table] = array();
        if (!empty($g5['board_table'])) {
            $cache[$bo_table] = sql_fetch("
                SELECT * FROM {$g5['board_table']}
                WHERE bo_table = '".sql_escape_string($bo_table)."'
            ");
        }
        if (!is_array($cache[$bo_table])) {
            $cache[$bo_table] = array();
        }

        return $cache[$bo_table];
    }
}

if (!function_exists('eottae_community_hub_write_table_exists')) {
    function eottae_community_hub_write_table_exists($bo_table)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '' || empty($g5['write_prefix'])) {
            return false;
        }

        $write_table = $g5['write_prefix'].$bo_table;

        return (bool) sql_query(" DESCRIBE `{$write_table}` ", false);
    }
}

if (!function_exists('eottae_community_hub_today_count')) {
    function eottae_community_hub_today_count()
    {
        global $g5;

        $today = G5_TIME_YMD.' 00:00:00';
        $total = 0;

        foreach (eottae_community_hub_board_tables() as $bo_table) {
            if (!eottae_community_hub_write_table_exists($bo_table)) {
                continue;
            }
            $write_table = $g5['write_prefix'].$bo_table;
            $row = sql_fetch("
                SELECT COUNT(*) AS cnt FROM `{$write_table}`
                WHERE wr_is_comment = 0 AND wr_datetime >= '{$today}'
            ");
            $total += isset($row['cnt']) ? (int) $row['cnt'] : 0;
        }

        return $total;
    }
}

if (!function_exists('eottae_community_hub_union_search_sql')) {
    function eottae_community_hub_union_search_sql($stx, $sfl, $sop)
    {
        $stx = trim((string) $stx);
        if ($stx === '' && $stx !== '0') {
            return '';
        }

        if (!function_exists('get_sql_search')) {
            return '';
        }

        $sql = get_sql_search('', $sfl, $stx, $sop);
        if ($sql === '' || $sql === '0') {
            return '';
        }

        return ' AND '.$sql;
    }
}

if (!function_exists('eottae_community_hub_union_order_sql')) {
    function eottae_community_hub_union_order_sql($sst, $sod)
    {
        $sst = preg_replace('/[^a-z_]/', '', (string) $sst);
        if (!in_array($sst, array('wr_datetime', 'wr_hit', 'wr_comment'), true)) {
            $sst = 'wr_datetime';
        }

        $sod = strtolower((string) $sod) === 'asc' ? 'ASC' : 'DESC';

        return " ORDER BY `{$sst}` {$sod}, hub_bo_table ASC, wr_id DESC ";
    }
}

if (!function_exists('eottae_community_hub_build_union_sql')) {
    /**
     * @return array{sql:string, tables:array<int, string>}
     */
    function eottae_community_hub_build_union_sql($search_sql = '')
    {
        global $g5;

        $parts = array();
        $tables = array();

        foreach (eottae_community_hub_board_tables() as $bo_table) {
            if (!eottae_community_hub_write_table_exists($bo_table)) {
                continue;
            }

            $write_table = $g5['write_prefix'].$bo_table;
            $tables[] = $bo_table;
            $parts[] = "
                SELECT '".sql_escape_string($bo_table)."' AS hub_bo_table, w.*
                FROM `{$write_table}` w
                WHERE w.wr_is_comment = 0 {$search_sql}
            ";
        }

        return array(
            'sql'    => $parts ? implode(' UNION ALL ', $parts) : '',
            'tables' => $tables,
        );
    }
}

if (!function_exists('eottae_community_hub_apply_merged_list')) {
    /**
     * 커뮤니티 허브 전체 보기 — list.php 목록을 통합 게시판 글으로 교체
     *
     * @param array<string, mixed> $ctx page, sst, sod, stx, sfl, sop, qstr
     * @return array<string, mixed>|null
     */
    function eottae_community_hub_apply_merged_list($bo_table, $board, $board_skin_url, array $ctx = array())
    {
        if (!eottae_community_hub_is_all_view($bo_table)) {
            return null;
        }

        global $config, $qstr;

        $page = max(1, (int) ($ctx['page'] ?? 1));
        $sst = isset($ctx['sst']) ? trim((string) $ctx['sst']) : '';
        $sod = isset($ctx['sod']) ? trim((string) $ctx['sod']) : 'desc';
        $stx = isset($ctx['stx']) ? trim((string) $ctx['stx']) : '';
        $sfl = isset($ctx['sfl']) ? trim((string) $ctx['sfl']) : '';
        $sop = isset($ctx['sop']) ? trim((string) $ctx['sop']) : 'and';

        $search_sql = eottae_community_hub_union_search_sql($stx, $sfl, $sop);
        $union = eottae_community_hub_build_union_sql($search_sql);
        if ($union['sql'] === '') {
            return array(
                'list'         => array(),
                'total_count'  => 0,
                'total_page'   => 0,
                'write_pages'  => '',
                'today_count'  => 0,
            );
        }

        $page_rows = G5_IS_MOBILE
            ? (int) ($board['bo_mobile_page_rows'] ?? 15)
            : (int) ($board['bo_page_rows'] ?? 15);
        if ($page_rows < 1) {
            $page_rows = 15;
        }

        $count_row = sql_fetch(' SELECT COUNT(*) AS cnt FROM ( '.$union['sql'].' ) hub ');
        $total_count = isset($count_row['cnt']) ? (int) $count_row['cnt'] : 0;
        $total_page = $total_count > 0 ? (int) ceil($total_count / $page_rows) : 0;
        $from_record = ($page - 1) * $page_rows;

        $order_sql = eottae_community_hub_union_order_sql($sst, $sod);
        $sql = ' SELECT * FROM ( '.$union['sql'].' ) hub '.$order_sql.' LIMIT '.(int) $from_record.', '.(int) $page_rows.' ';
        $result = sql_query($sql);

        $subject_len = G5_IS_MOBILE
            ? (int) ($board['bo_mobile_subject_len'] ?? 60)
            : (int) ($board['bo_subject_len'] ?? 60);

        $list = array();
        $num = $total_count - $from_record;
        while ($row = sql_fetch_array($result)) {
            $item_bo_table = preg_replace('/[^a-z0-9_]/', '', (string) ($row['hub_bo_table'] ?? ''));
            if ($item_bo_table === '') {
                continue;
            }

            $item_board = eottae_community_hub_get_board($item_bo_table);
            if (!$item_board) {
                $item_board = $board;
                $item_board['bo_table'] = $item_bo_table;
            }

            $item = get_list($row, $item_board, $board_skin_url, $subject_len);
            $item['hub_bo_table'] = $item_bo_table;
            $item['href'] = get_pretty_url($item_bo_table, (int) $row['wr_id'], $qstr);
            $item['comment_href'] = $item['href'];
            $item['is_notice'] = false;
            $item['num'] = $num--;
            $item['list_content'] = $item['wr_content'] ?? '';

            if (strstr($item['wr_option'] ?? '', 'secret')) {
                $item['wr_content'] = '';
            }

            if ($stx !== '' && function_exists('search_font') && strpos($sfl, 'subject') !== false) {
                $item['subject'] = search_font($stx, $item['subject']);
            }

            $list[] = $item;
        }

        $list_qstr = 'hub=all';
        if ($stx !== '') {
            $list_qstr .= '&amp;stx='.urlencode($stx).'&amp;sfl='.urlencode($sfl).'&amp;sop='.urlencode($sop);
        }
        if ($sst !== '') {
            $list_qstr .= '&amp;sst='.urlencode($sst).'&amp;sod='.urlencode($sod);
        }

        $write_pages = get_paging(
            G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'],
            $page,
            $total_page,
            get_pretty_url($bo_table, '', $list_qstr.'&amp;page=')
        );

        return array(
            'list'         => $list,
            'total_count'  => $total_count,
            'total_page'   => $total_page,
            'write_pages'  => $write_pages,
            'today_count'  => eottae_community_hub_today_count(),
        );
    }
}

if (!function_exists('eottae_community_hub_redirect_legacy_list')) {
    function eottae_community_hub_redirect_legacy_list($board, $write, $wr_id)
    {
        if ((int) $wr_id > 0 || !is_array($board) || empty($board['bo_table'])) {
            return;
        }

        $w = isset($_GET['w']) ? trim((string) $_GET['w']) : '';
        if ($w !== '') {
            return;
        }

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $board['bo_table']);
        if ($bo_table !== eottae_community_hub_primary_table()) {
            return;
        }

        if (isset($_GET['hub'])) {
            return;
        }

        goto_url(eottae_community_hub_all_url());
    }
}

if (!function_exists('eottae_community_hub_tabs')) {
    /**
     * 커뮤니티 허브 네비 탭 (독립 게시판 링크)
     *
     * @return array<int, array{slug:string, label:string, count:int, href:string, active:bool}>
     */
    function eottae_community_hub_tabs($current_bo_table = '')
    {
        $current_bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $current_bo_table);
        $tabs = array();

        $community_table = eottae_community_hub_primary_table();
        $is_all_view = eottae_community_hub_is_all_view($current_bo_table);

        $tabs[] = array(
            'slug'   => '_all',
            'label'  => '전체',
            'count'  => eottae_community_hub_total_count(),
            'href'   => eottae_community_hub_all_url(),
            'active' => $is_all_view,
        );

        foreach (eottae_community_hub_board_defs() as $def) {
            $bo_table = (string) ($def['bo_table'] ?? '');
            if ($bo_table === '') {
                continue;
            }

            $tab_active = $current_bo_table === $bo_table && !$is_all_view;
            if ($bo_table === $community_table) {
                $tab_active = $current_bo_table === $community_table
                    && !$is_all_view
                    && (!isset($_GET['hub']) || trim((string) $_GET['hub']) === 'community');
            }

            $tabs[] = array(
                'slug'   => $bo_table,
                'label'  => (string) ($def['label'] ?? $bo_table),
                'count'  => eottae_community_hub_board_count($bo_table),
                'href'   => eottae_community_hub_list_url($bo_table),
                'active' => $tab_active,
            );
        }

        return $tabs;
    }
}

if (!function_exists('eottae_community_hub_hero')) {
    function eottae_community_hub_hero($board, $sca = '')
    {
        $bo_table = '';
        if (is_array($board) && !empty($board['bo_table'])) {
            $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $board['bo_table']);
        }

        if (eottae_community_hub_is_all_view($bo_table)) {
            $cebu_img = 'https://images.unsplash.com/photo-%s?auto=format&fit=crop&w=1600&q=85';

            return array(
                'kicker' => '세부 커뮤니티',
                'title'  => '세부 커뮤니티 전체',
                'desc'   => '생활정보·자유게시판·업체리뷰·사람찾기·이벤트 글을 한곳에서 모아 봅니다.',
                'image'  => sprintf($cebu_img, '1518509562904-7fc873a70436'),
            );
        }

        $def = eottae_community_hub_board_def($bo_table);
        $cebu_img = 'https://images.unsplash.com/photo-%s?auto=format&fit=crop&w=1600&q=85';

        if ($def) {
            return array(
                'kicker' => get_text($def['kicker'] ?? ''),
                'title'  => get_text($def['title'] ?? ''),
                'desc'   => get_text($def['desc'] ?? ''),
                'image'  => sprintf($cebu_img, $def['image'] ?? '1518509562904-7fc873a70436'),
            );
        }

        if (function_exists('eottae_community_board_hero')) {
            return eottae_community_board_hero($board, '');
        }

        $subject = is_array($board) && isset($board['bo_subject']) ? get_text($board['bo_subject']) : '게시판';

        return array(
            'kicker' => $subject,
            'title'  => $subject,
            'desc'   => '',
            'image'  => sprintf($cebu_img, '1518509562904-7fc873a70436'),
        );
    }
}

if (!function_exists('eottae_community_hub_ensure_board_skin')) {
    /**
     * 허브 게시판 스킨·첨부 설정 (eottae-community)
     */
    function eottae_community_hub_ensure_board_skin($bo_table)
    {
        static $done = array();

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '' || !eottae_is_community_hub_board($bo_table) || isset($done[$bo_table])) {
            return;
        }
        $done[$bo_table] = true;

        global $g5, $board;

        if (empty($g5['board_table'])) {
            return;
        }

        $row = sql_fetch("
            SELECT bo_skin, bo_mobile_skin, bo_upload_count, bo_use_category
            FROM {$g5['board_table']}
            WHERE bo_table = '".sql_escape_string($bo_table)."'
        ");
        if (!$row) {
            return;
        }

        $skin = 'eottae-community';
        $limit = function_exists('eottae_community_photo_limit') ? eottae_community_photo_limit() : 7;
        $upload = max($limit, (int) ($row['bo_upload_count'] ?? 0));

        $needs_update = (string) ($row['bo_skin'] ?? '') !== $skin
            || (string) ($row['bo_mobile_skin'] ?? '') !== $skin
            || (int) ($row['bo_upload_count'] ?? 0) < $limit
            || (int) ($row['bo_use_category'] ?? 0) === 1;

        if (!$needs_update) {
            return;
        }

        sql_query("
            UPDATE {$g5['board_table']} SET
                bo_skin = '".sql_escape_string($skin)."',
                bo_mobile_skin = '".sql_escape_string($skin)."',
                bo_upload_count = '{$upload}',
                bo_use_category = 0
            WHERE bo_table = '".sql_escape_string($bo_table)."'
        ", false);

        if (is_array($board) && isset($board['bo_table']) && $board['bo_table'] === $bo_table) {
            $board['bo_skin'] = $skin;
            $board['bo_mobile_skin'] = $skin;
            $board['bo_upload_count'] = $upload;
            $board['bo_use_category'] = 0;
        }
    }
}

if (!function_exists('eottae_community_hub_write_href')) {
    /**
     * 허브 게시판 글쓰기 URL (해당 bo_table 권한 기준)
     */
    function eottae_community_hub_write_href($bo_table)
    {
        global $member;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '' || !eottae_is_community_hub_board($bo_table)) {
            return '';
        }

        if (eottae_community_hub_is_all_view(eottae_community_hub_primary_table())) {
            return '';
        }

        $board = eottae_community_hub_get_board($bo_table);
        if (empty($board['bo_table'])) {
            return '';
        }

        $mb_level = (int) ($member['mb_level'] ?? 0);
        if ($mb_level < (int) ($board['bo_write_level'] ?? 2)) {
            return '';
        }

        return function_exists('short_url_clean')
            ? short_url_clean(G5_BBS_URL.'/write.php?bo_table='.$bo_table)
            : G5_BBS_URL.'/write.php?bo_table='.$bo_table;
    }
}

if (!function_exists('eottae_community_hub_prepare_list_context')) {
    /**
     * 목록 스킨 — 권한·스킨 보정 후 write_href 확정
     */
    function eottae_community_hub_prepare_list_context($bo_table)
    {
        global $board, $write_href;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '' || !eottae_is_community_hub_board($bo_table)) {
            return;
        }

        eottae_community_hub_apply_runtime($bo_table);

        if (function_exists('eottae_event_ensure_board_permissions') && function_exists('eottae_is_event_board')
            && eottae_is_event_board($bo_table)) {
            eottae_event_ensure_board_permissions();
        }

        $fresh = eottae_community_hub_get_board($bo_table);
        if (is_array($fresh) && !empty($fresh['bo_table'])) {
            if (!is_array($board)) {
                $board = $fresh;
            } else {
                $board = array_merge($board, $fresh);
            }
        }

        $href = eottae_community_hub_write_href($bo_table);
        if ($href !== '') {
            $write_href = $href;
        }
    }
}

if (!function_exists('eottae_community_hub_apply_runtime')) {
    function eottae_community_hub_apply_runtime($bo_table)
    {
        if (!eottae_is_community_hub_board($bo_table)) {
            return;
        }

        eottae_community_hub_ensure_board_skin($bo_table);

        if (function_exists('eottae_event_ensure_board_permissions') && function_exists('eottae_is_event_board')
            && eottae_is_event_board($bo_table)) {
            eottae_event_ensure_board_permissions();
        }

        if (function_exists('eottae_ensure_free_board_skin') && function_exists('eottae_free_board_table')
            && $bo_table === eottae_free_board_table()) {
            eottae_ensure_free_board_skin();
        }
    }
}
