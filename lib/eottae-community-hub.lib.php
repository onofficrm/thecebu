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

if (!function_exists('eottae_community_hub_list_url')) {
    function eottae_community_hub_list_url($bo_table, $params = array())
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            $bo_table = defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community';
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

        $community_table = defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community';

        // 전체: 허브 합계 글 수 — 링크는 생활정보(community) 게시판 (통합 목록 페이지 미구현)
        $tabs[] = array(
            'slug'   => '_all',
            'label'  => '전체',
            'count'  => eottae_community_hub_total_count(),
            'href'   => eottae_community_hub_list_url($community_table),
            'active' => false,
        );

        foreach (eottae_community_hub_board_defs() as $def) {
            $bo_table = (string) ($def['bo_table'] ?? '');
            if ($bo_table === '') {
                continue;
            }

            $tabs[] = array(
                'slug'   => $bo_table,
                'label'  => (string) ($def['label'] ?? $bo_table),
                'count'  => eottae_community_hub_board_count($bo_table),
                'href'   => eottae_community_hub_list_url($bo_table),
                'active' => $current_bo_table !== '' && $current_bo_table === $bo_table,
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

if (!function_exists('eottae_community_hub_apply_runtime')) {
    function eottae_community_hub_apply_runtime($bo_table)
    {
        if (!eottae_is_community_hub_board($bo_table)) {
            return;
        }

        eottae_community_hub_ensure_board_skin($bo_table);

        if (function_exists('eottae_ensure_free_board_skin') && function_exists('eottae_free_board_table')
            && $bo_table === eottae_free_board_table()) {
            eottae_ensure_free_board_skin();
        }
    }
}
