<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_sitemap_xml_escape')) {
    function eottae_sitemap_xml_escape($value)
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('eottae_sitemap_pretty_board_url')) {
    function eottae_sitemap_pretty_board_url($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return '';
        }

        if ($bo_table === (defined('EOTTae_COLUMN_TABLE') ? EOTTae_COLUMN_TABLE : 'column')
            && function_exists('eottae_column_list_url')
        ) {
            return eottae_column_list_url();
        }

        return rtrim(G5_URL, '/').'/'.$bo_table;
    }
}

if (!function_exists('eottae_sitemap_static_entries')) {
    /**
     * @return array<int, array<string, string>>
     */
    function eottae_sitemap_static_entries()
    {
        $base = rtrim(G5_URL, '/');
        $entries = array(
            array('loc' => $base.'/', 'changefreq' => 'daily', 'priority' => '1.0'),
            array('loc' => eottae_sitemap_pretty_board_url(eottae_shop_table()), 'changefreq' => 'daily', 'priority' => '0.9'),
            array('loc' => function_exists('eottae_community_hub_all_url') ? eottae_community_hub_all_url() : eottae_sitemap_pretty_board_url(eottae_community_board_table()), 'changefreq' => 'daily', 'priority' => '0.8'),
            array('loc' => eottae_sitemap_pretty_board_url(defined('EOTTae_COLUMN_TABLE') ? EOTTae_COLUMN_TABLE : 'column'), 'changefreq' => 'daily', 'priority' => '0.8'),
            array('loc' => $base.'/cebu-map/', 'changefreq' => 'weekly', 'priority' => '0.8'),
            array('loc' => $base.'/cost-calculator/', 'changefreq' => 'monthly', 'priority' => '0.6'),
            array('loc' => $base.'/golf-join/', 'changefreq' => 'weekly', 'priority' => '0.6'),
            array('loc' => $base.'/page/privacy.php', 'changefreq' => 'yearly', 'priority' => '0.3'),
        );

        $optional = array(
            array('loc' => eottae_sitemap_pretty_board_url('notice'), 'changefreq' => 'weekly', 'priority' => '0.7'),
            array('loc' => function_exists('eottae_free_list_url') ? eottae_free_list_url() : eottae_sitemap_pretty_board_url(function_exists('eottae_free_board_table') ? eottae_free_board_table() : 'free'), 'changefreq' => 'daily', 'priority' => '0.7'),
            array('loc' => function_exists('eottae_news_list_url') ? eottae_news_list_url() : eottae_sitemap_pretty_board_url('news'), 'changefreq' => 'daily', 'priority' => '0.7'),
            array('loc' => function_exists('eottae_talkroom_list_url') ? eottae_talkroom_list_url() : eottae_sitemap_pretty_board_url(defined('EOTTae_TALKROOM_TABLE') ? EOTTae_TALKROOM_TABLE : 'talkroom'), 'changefreq' => 'daily', 'priority' => '0.6'),
            array('loc' => function_exists('eottae_calendar_list_url') ? eottae_calendar_list_url() : $base.'/calendar/', 'changefreq' => 'weekly', 'priority' => '0.6'),
            array('loc' => function_exists('eottae_challenge_list_url') ? eottae_challenge_list_url() : $base.'/challenge/', 'changefreq' => 'weekly', 'priority' => '0.5'),
        );

        foreach ($optional as $entry) {
            if (!empty($entry['loc'])) {
                $entries[] = $entry;
            }
        }

        $seen = array();
        $unique = array();
        foreach ($entries as $entry) {
            $loc = trim((string) ($entry['loc'] ?? ''));
            if ($loc === '' || isset($seen[$loc])) {
                continue;
            }
            $seen[$loc] = true;
            $entry['loc'] = $loc;
            $unique[] = $entry;
        }

        return $unique;
    }
}

if (!function_exists('eottae_sitemap_post_board_tables')) {
    /**
     * @return array<int, string>
     */
    function eottae_sitemap_post_board_tables()
    {
        $tables = array(
            eottae_shop_table(),
            defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community',
            defined('EOTTae_COLUMN_TABLE') ? EOTTae_COLUMN_TABLE : 'column',
            'notice',
            function_exists('eottae_free_board_table') ? eottae_free_board_table() : 'free',
            'news',
            defined('EOTTae_REVIEW_TABLE') ? EOTTae_REVIEW_TABLE : 'review',
            defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job',
            defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate',
            defined('EOTTae_MARKET_TABLE') ? EOTTae_MARKET_TABLE : 'market',
        );

        return array_values(array_unique(array_filter(array_map(static function ($table) {
            return preg_replace('/[^a-z0-9_]/', '', (string) $table);
        }, $tables))));
    }
}

if (!function_exists('eottae_sitemap_board_exists')) {
    function eottae_sitemap_board_exists($bo_table)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return false;
        }

        $row = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = '{$bo_table}' ");

        return !empty($row['cnt']);
    }
}

if (!function_exists('eottae_sitemap_post_view_url')) {
    function eottae_sitemap_post_view_url($bo_table, $wr_id, $wr_seo_title = '')
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return '';
        }

        if ($bo_table === (defined('EOTTae_COLUMN_TABLE') ? EOTTae_COLUMN_TABLE : 'column')
            && function_exists('eottae_column_view_url')
        ) {
            return eottae_column_view_url($wr_id);
        }

        if (function_exists('eottae_icrm_build_final_url')) {
            return eottae_icrm_build_final_url($bo_table, $wr_seo_title, $wr_id);
        }

        return G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id;
    }
}

if (!function_exists('eottae_sitemap_board_post_entries')) {
    /**
     * @return array<int, array<string, string>>
     */
    function eottae_sitemap_board_post_entries($bo_table, $limit = 300)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $limit = max(1, min(1000, (int) $limit));
        if ($bo_table === '' || !eottae_sitemap_board_exists($bo_table)) {
            return array();
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $exists = sql_fetch(" SHOW TABLES LIKE '".sql_escape_string($write_table)."' ");
        if (empty($exists)) {
            return array();
        }

        $result = sql_query("
            select wr_id, wr_seo_title, wr_datetime
            from `{$write_table}`
            where wr_is_comment = 0
              and wr_option not like '%secret%'
            order by wr_id desc
            limit {$limit}
        ");

        $entries = array();
        while ($row = sql_fetch_array($result)) {
            $loc = eottae_sitemap_post_view_url($bo_table, (int) $row['wr_id'], (string) ($row['wr_seo_title'] ?? ''));
            if ($loc === '') {
                continue;
            }

            $entry = array(
                'loc' => $loc,
                'changefreq' => 'weekly',
                'priority' => '0.5',
            );

            $lastmod = trim((string) ($row['wr_datetime'] ?? ''));
            if ($lastmod !== '' && $lastmod !== '0000-00-00 00:00:00') {
                $entry['lastmod'] = date('c', strtotime($lastmod));
            }

            $entries[] = $entry;
        }

        return $entries;
    }
}

if (!function_exists('eottae_sitemap_entries')) {
    /**
     * @return array<int, array<string, string>>
     */
    function eottae_sitemap_entries($post_limit_per_board = 300)
    {
        $entries = eottae_sitemap_static_entries();

        foreach (eottae_sitemap_post_board_tables() as $bo_table) {
            $entries = array_merge($entries, eottae_sitemap_board_post_entries($bo_table, $post_limit_per_board));
        }

        $seen = array();
        $unique = array();
        foreach ($entries as $entry) {
            $loc = trim((string) ($entry['loc'] ?? ''));
            if ($loc === '' || isset($seen[$loc])) {
                continue;
            }
            $seen[$loc] = true;
            $entry['loc'] = $loc;
            $unique[] = $entry;
        }

        return $unique;
    }
}

if (!function_exists('eottae_sitemap_render_xml')) {
    function eottae_sitemap_render_xml(array $entries)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($entries as $entry) {
            $loc = trim((string) ($entry['loc'] ?? ''));
            if ($loc === '') {
                continue;
            }

            $xml .= "  <url>\n";
            $xml .= '    <loc>'.eottae_sitemap_xml_escape($loc)."</loc>\n";

            if (!empty($entry['lastmod'])) {
                $xml .= '    <lastmod>'.eottae_sitemap_xml_escape($entry['lastmod'])."</lastmod>\n";
            }
            if (!empty($entry['changefreq'])) {
                $xml .= '    <changefreq>'.eottae_sitemap_xml_escape($entry['changefreq'])."</changefreq>\n";
            }
            if (!empty($entry['priority'])) {
                $xml .= '    <priority>'.eottae_sitemap_xml_escape($entry['priority'])."</priority>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>\n";

        return $xml;
    }
}
