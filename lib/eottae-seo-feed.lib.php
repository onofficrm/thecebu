<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_seo_rss_board_tables')) {
    /**
     * 네이버·검색엔진 RSS 제출 대상 게시판
     *
     * @return array<int, string>
     */
    function eottae_seo_rss_board_tables()
    {
        $tables = array(
            defined('EOTTae_COLUMN_TABLE') ? EOTTae_COLUMN_TABLE : 'column',
            defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community',
            'notice',
        );

        return array_values(array_unique(array_filter(array_map(static function ($table) {
            return preg_replace('/[^a-z0-9_]/', '', (string) $table);
        }, $tables))));
    }
}

if (!function_exists('eottae_seo_primary_rss_url')) {
    function eottae_seo_primary_rss_url()
    {
        global $site_config;

        $bo_table = '';
        if (!empty($site_config['seo_primary_rss_board'])) {
            $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $site_config['seo_primary_rss_board']);
        }
        if ($bo_table === '') {
            $bo_table = defined('EOTTae_COLUMN_TABLE') ? EOTTae_COLUMN_TABLE : 'column';
        }

        return rtrim(G5_URL, '/').'/rss/'.$bo_table;
    }
}

if (!function_exists('eottae_seo_ensure_rss_boards')) {
    /**
     * 공개 게시판 RSS 보기 자동 활성화 (비회원 읽기 가능 + bo_use_rss_view)
     */
    function eottae_seo_ensure_rss_boards()
    {
        static $ran = false;
        if ($ran) {
            return true;
        }
        $ran = true;

        global $g5;

        $tables = eottae_seo_rss_board_tables();
        if (!$tables) {
            return false;
        }

        $in = "'".implode("','", array_map('sql_escape_string', $tables))."'";
        $row = sql_fetch("
            select count(*) as cnt
            from {$g5['board_table']}
            where bo_table in ({$in})
              and bo_read_level < 2
              and bo_use_rss_view = 0
        ");
        if (empty($row['cnt'])) {
            return true;
        }

        sql_query("
            update {$g5['board_table']}
            set bo_use_rss_view = 1
            where bo_table in ({$in})
              and bo_read_level < 2
              and bo_use_rss_view = 0
        ", false);

        return true;
    }
}
