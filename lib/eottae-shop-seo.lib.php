<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_shop_seo_table')) {
    function eottae_shop_seo_table()
    {
        return G5_TABLE_PREFIX.'eottae_shop_seo';
    }
}

if (!function_exists('eottae_shop_seo_ensure_table')) {
    function eottae_shop_seo_ensure_table()
    {
        $table = eottae_shop_seo_table();
        $exists = sql_fetch(" show tables like '{$table}' ");
        if (!empty($exists)) {
            return true;
        }

        $sql = " create table if not exists `{$table}` (
            `seo_id` int unsigned not null auto_increment,
            `bo_table` varchar(20) not null default '',
            `wr_id` int unsigned not null default 0,
            `meta_title` varchar(255) not null default '',
            `meta_intro` varchar(500) not null default '',
            `meta_description` varchar(500) not null default '',
            `focus_keyword` varchar(255) not null default '',
            `updated_at` datetime not null default '0000-00-00 00:00:00',
            primary key (`seo_id`),
            unique key `uk_shop` (`bo_table`, `wr_id`),
            key `idx_wr_id` (`wr_id`)
        ) engine=InnoDB default charset=utf8 ";

        return sql_query($sql, false) !== false;
    }
}

if (!function_exists('eottae_shop_seo_get')) {
    function eottae_shop_seo_get($bo_table, $wr_id)
    {
        $bo_table = (string) $bo_table;
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return array();
        }

        eottae_shop_seo_ensure_table();

        $table = eottae_shop_seo_table();
        $row = sql_fetch(" select * from `{$table}` where bo_table = '".sql_escape_string($bo_table)."' and wr_id = '{$wr_id}' limit 1 ");

        return is_array($row) ? $row : array();
    }
}

if (!function_exists('eottae_shop_seo_delete')) {
    function eottae_shop_seo_delete($bo_table, $wr_id)
    {
        $bo_table = (string) $bo_table;
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return false;
        }

        eottae_shop_seo_ensure_table();

        $table = eottae_shop_seo_table();
        sql_query(" delete from `{$table}` where bo_table = '".sql_escape_string($bo_table)."' and wr_id = '{$wr_id}' ");

        return true;
    }
}

if (!function_exists('eottae_shop_seo_save')) {
    function eottae_shop_seo_save($bo_table, $wr_id, $data = array())
    {
        $bo_table = (string) $bo_table;
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return false;
        }

        eottae_shop_seo_ensure_table();

        $meta_title = isset($data['meta_title']) ? trim(strip_tags((string) $data['meta_title'])) : '';
        $meta_intro = isset($data['meta_intro']) ? trim(strip_tags((string) $data['meta_intro'])) : '';
        $meta_description = isset($data['meta_description']) ? trim(strip_tags((string) $data['meta_description'])) : '';
        $focus_keyword = isset($data['focus_keyword']) ? trim(strip_tags((string) $data['focus_keyword'])) : '';

        if (function_exists('cut_str')) {
            $meta_title = cut_str($meta_title, 255, '');
            $meta_intro = cut_str($meta_intro, 500, '');
            $meta_description = cut_str($meta_description, 500, '');
            $focus_keyword = cut_str($focus_keyword, 255, '');
        }

        $table = eottae_shop_seo_table();
        $exists = eottae_shop_seo_get($bo_table, $wr_id);
        $now = G5_TIME_YMDHIS;

        if (empty($exists)) {
            if ($meta_title === '' && $meta_intro === '' && $meta_description === '' && $focus_keyword === '') {
                return true;
            }

            $sql = " insert into `{$table}` set
                bo_table = '".sql_escape_string($bo_table)."',
                wr_id = '{$wr_id}',
                meta_title = '".sql_escape_string($meta_title)."',
                meta_intro = '".sql_escape_string($meta_intro)."',
                meta_description = '".sql_escape_string($meta_description)."',
                focus_keyword = '".sql_escape_string($focus_keyword)."',
                updated_at = '{$now}' ";

            return sql_query($sql) !== false;
        }

        $sql = " update `{$table}` set
            meta_title = '".sql_escape_string($meta_title)."',
            meta_intro = '".sql_escape_string($meta_intro)."',
            meta_description = '".sql_escape_string($meta_description)."',
            focus_keyword = '".sql_escape_string($focus_keyword)."',
            updated_at = '{$now}'
            where bo_table = '".sql_escape_string($bo_table)."' and wr_id = '{$wr_id}' ";

        return sql_query($sql) !== false;
    }
}

if (!function_exists('eottae_shop_seo_from_post')) {
    function eottae_shop_seo_from_post()
    {
        return array(
            'meta_title'       => isset($_POST['eottae_seo_title']) ? (string) $_POST['eottae_seo_title'] : '',
            'meta_intro'       => isset($_POST['eottae_seo_intro']) ? (string) $_POST['eottae_seo_intro'] : '',
            'meta_description' => isset($_POST['eottae_seo_description']) ? (string) $_POST['eottae_seo_description'] : '',
            'focus_keyword'    => isset($_POST['eottae_seo_keyword']) ? (string) $_POST['eottae_seo_keyword'] : '',
        );
    }
}

if (!function_exists('eottae_shop_seo_excerpt')) {
    function eottae_shop_seo_excerpt($html, $length = 160)
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $html)));
        if ($text === '') {
            return '';
        }
        if (function_exists('cut_str')) {
            return cut_str($text, (int) $length, '…');
        }
        if (mb_strlen($text, 'UTF-8') > $length) {
            return mb_substr($text, 0, $length, 'UTF-8').'…';
        }

        return $text;
    }
}

if (!function_exists('eottae_shop_seo_resolve_for_write')) {
    function eottae_shop_seo_resolve_for_write($write, $seo = null)
    {
        if (!is_array($write)) {
            $write = array();
        }
        if (!is_array($seo)) {
            $seo = array();
        }

        $subject = isset($write['wr_subject']) ? get_text($write['wr_subject']) : '';
        $site_name = '';
        if (function_exists('g5site_cfg')) {
            $site_name = g5site_cfg('site_name', '');
        }
        global $config;
        if ($site_name === '' && isset($config['cf_title'])) {
            $site_name = trim(strip_tags($config['cf_title']));
        }

        $meta_title = isset($seo['meta_title']) ? trim(get_text($seo['meta_title'])) : '';
        $meta_intro = isset($seo['meta_intro']) ? trim(get_text($seo['meta_intro'])) : '';
        $meta_description = isset($seo['meta_description']) ? trim(get_text($seo['meta_description'])) : '';
        $focus_keyword = isset($seo['focus_keyword']) ? trim(get_text($seo['focus_keyword'])) : '';

        if ($meta_title === '' && $subject !== '') {
            $meta_title = $site_name !== '' ? $subject.' | '.$site_name : $subject;
        }
        if ($meta_intro === '' && !empty($write['wr_content'])) {
            $meta_intro = eottae_shop_seo_excerpt($write['wr_content'], 120);
        }
        if ($meta_description === '' && !empty($write['wr_content'])) {
            $meta_description = eottae_shop_seo_excerpt($write['wr_content'], 160);
        }

        return array(
            'meta_title'       => $meta_title,
            'meta_intro'       => $meta_intro,
            'meta_description' => $meta_description,
            'focus_keyword'    => $focus_keyword,
        );
    }
}

if (!function_exists('eottae_shop_seo_apply_page')) {
    function eottae_shop_seo_apply_page($board, $write)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_is_shop_board') || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }
        if (!is_array($write) || empty($write['wr_id'])) {
            return;
        }

        global $page_title, $page_description, $page_keywords, $page_canonical, $page_og_image, $page_schema_type, $g5;

        $seo = eottae_shop_seo_get($board['bo_table'], (int) $write['wr_id']);
        $resolved = eottae_shop_seo_resolve_for_write($write, $seo);

        if ($resolved['meta_title'] !== '') {
            $page_title = $resolved['meta_title'];
            $g5['title'] = strip_tags($resolved['meta_title']);
        }

        if ($resolved['meta_description'] !== '') {
            $page_description = $resolved['meta_description'];
        } elseif ($resolved['meta_intro'] !== '') {
            $page_description = $resolved['meta_intro'];
        }

        if ($resolved['focus_keyword'] !== '') {
            $page_keywords = $resolved['focus_keyword'];
        }

        if (function_exists('eottae_shop_view_url') && eottae_is_shop_board($board['bo_table'])) {
            $page_canonical = eottae_shop_view_url((int) $write['wr_id'], $board['bo_table']);
        } elseif (function_exists('get_pretty_url')) {
            $page_canonical = get_pretty_url($board['bo_table'], (int) $write['wr_id']);
        } else {
            $page_canonical = G5_BBS_URL.'/board.php?bo_table='.$board['bo_table'].'&wr_id='.(int) $write['wr_id'];
        }

        if (!function_exists('get_list_thumbnail') && is_file(G5_LIB_PATH.'/thumbnail.lib.php')) {
            include_once G5_LIB_PATH.'/thumbnail.lib.php';
        }
        if (function_exists('eottae_shop_map_thumb_get')) {
            $map_thumb = eottae_shop_map_thumb_get($board['bo_table'], (int) $write['wr_id']);
            if (!empty($map_thumb['url'])) {
                $page_og_image = $map_thumb['url'];
            }
        }
        if ($page_og_image === '' && function_exists('get_list_thumbnail')) {
            $thumb = get_list_thumbnail($board['bo_table'], (int) $write['wr_id'], 1200, 630, false, true);
            if (!empty($thumb['src'])) {
                $page_og_image = $thumb['src'];
            }
        }

        $page_schema_type = 'LocalBusiness';

        if (is_file(G5_PATH.'/components/seo-meta.php')) {
            include_once G5_PATH.'/components/seo-meta.php';
        }
        if (function_exists('g5b_seo_init')) {
            g5b_seo_init();
        }
    }
}
