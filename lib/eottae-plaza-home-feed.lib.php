<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_plaza_home_feed_community_table')) {
    function eottae_plaza_home_feed_community_table()
    {
        return function_exists('eottae_community_board_table')
            ? eottae_community_board_table()
            : (defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community');
    }
}

if (!function_exists('eottae_plaza_list_home_feed_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_plaza_list_home_feed_rows($bo_table, $limit = 5)
    {
        global $g5;

        if (!function_exists('eottae_plaza_board_table')) {
            include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
        }

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return array();
        }

        $limit = max(1, min(10, (int) $limit));
        $write_table = $g5['write_prefix'].$bo_table;
        $exists = sql_fetch(" SHOW TABLES LIKE '".sql_escape_string($write_table)."' ", false);
        if (empty($exists)) {
            return array();
        }

        $is_plaza = $bo_table === eottae_plaza_board_table();
        $visible = $is_plaza
            ? eottae_plaza_post_visible_sql()
            : ' wr_is_comment = 0 ';
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

        $like_counts = array();
        if ($is_plaza) {
            include_once G5_LIB_PATH.'/eottae-plaza-likes.lib.php';
            include_once G5_LIB_PATH.'/eottae-plaza-ai.lib.php';
            $like_counts = eottae_plaza_like_counts_batch($wr_ids);
        }

        $items = array();
        foreach ($rows as $row) {
            $wr_id = (int) ($row['wr_id'] ?? 0);
            $ca_name = get_text($row['ca_name'] ?? '');
            $is_ai = $is_plaza
                && function_exists('eottae_plaza_ai_is_ai_write_row')
                && eottae_plaza_ai_is_ai_write_row($row);
            if ($is_plaza) {
                $type_class = $is_ai ? 'plaza-badge--ai' : eottae_plaza_type_badge_class($ca_name);
                $badge_kind = 'plaza';
            } else {
                $type_class = function_exists('eottae_community_badge_class')
                    ? eottae_community_badge_class($ca_name)
                    : 'community-badge--default';
                $badge_kind = 'community';
            }

            $href = get_pretty_url($bo_table, $wr_id);
            if ($href === '' || $href === false) {
                $href = G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id;
            }

            $items[] = array(
                'wr_id'         => $wr_id,
                'ca_name'       => $ca_name,
                'type_label'    => $is_ai ? '[AI질문]' : ($ca_name !== '' ? '['.$ca_name.']' : ''),
                'type_class'    => $type_class,
                'badge_kind'    => $badge_kind,
                'subject'       => get_text($row['wr_subject'] ?? ''),
                'comment_count' => (int) ($row['wr_comment'] ?? 0),
                'like_count'    => (int) ($like_counts[$wr_id] ?? 0),
                'time_label'    => eottae_plaza_relative_time($row['wr_datetime'] ?? ''),
                'href'          => $href,
                'is_ai'         => $is_ai ? 1 : 0,
            );
        }

        return $items;
    }
}

if (!function_exists('eottae_plaza_home_feed_context')) {
    /**
     * @return array{posts: array<int, array<string, mixed>>, list_url: string, list_label: string, source: string}
     */
    function eottae_plaza_home_feed_context($limit = 5)
    {
        if (!function_exists('eottae_plaza_board_table')) {
            include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
        }

        $posts = eottae_plaza_list_home_feed_rows(eottae_plaza_board_table(), $limit);
        if (!empty($posts)) {
            return array(
                'posts'      => $posts,
                'list_url'   => eottae_plaza_list_url(),
                'list_label' => '세부광장으로 이동',
                'source'     => 'plaza',
            );
        }

        $community_table = eottae_plaza_home_feed_community_table();
        $posts = eottae_plaza_list_home_feed_rows($community_table, $limit);

        return array(
            'posts'      => $posts,
            'list_url'   => function_exists('eottae_community_list_url')
                ? eottae_community_list_url()
                : G5_BBS_URL.'/board.php?bo_table='.$community_table,
            'list_label' => '커뮤니티 더보기',
            'source'     => 'community',
        );
    }
}

if (!function_exists('eottae_plaza_list_home_feed')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_plaza_list_home_feed($limit = 5)
    {
        $context = eottae_plaza_home_feed_context($limit);

        return $context['posts'];
    }
}
