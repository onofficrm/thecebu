<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_business_snippet_table')) {
    function eottae_business_snippet_table()
    {
        return G5_TABLE_PREFIX.'eottae_business_snippet';
    }
}

if (!function_exists('eottae_business_snippet_ensure_table')) {
    function eottae_business_snippet_ensure_table()
    {
        $table = eottae_business_snippet_table();
        $exists = sql_fetch(" show tables like '{$table}' ");
        if (!empty($exists)) {
            return true;
        }

        $sql = " create table if not exists `{$table}` (
            `snippet_id` int unsigned not null auto_increment,
            `mb_id` varchar(20) not null default '',
            `label` varchar(100) not null default '',
            `wr_subject` varchar(255) not null default '',
            `wr_content` text not null,
            `sort_order` int not null default 0,
            `created_at` datetime not null default '0000-00-00 00:00:00',
            `updated_at` datetime not null default '0000-00-00 00:00:00',
            primary key (`snippet_id`),
            key `idx_mb_id` (`mb_id`, `sort_order`)
        ) engine=InnoDB default charset=utf8 ";

        return sql_query($sql, false) !== false;
    }
}

if (!function_exists('eottae_business_snippet_max_count')) {
    function eottae_business_snippet_max_count()
    {
        return 30;
    }
}

if (!function_exists('eottae_business_primary_shop')) {
    function eottae_business_primary_shop($mb_id)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        if ($mb_id === '') {
            return array();
        }

        if (function_exists('eottae_shop_board_tables')) {
            foreach (eottae_shop_board_tables() as $bo_table) {
                $shop_table = $g5['write_prefix'].$bo_table;
                $row = sql_fetch(" select * from {$shop_table}
                    where mb_id = '{$mb_id}' and wr_is_comment = 0
                    order by wr_id desc limit 1 ");
                if (!empty($row['wr_id']) && function_exists('eottae_shop_from_write')) {
                    return eottae_shop_from_write($row);
                }
            }
        }

        return array();
    }
}

if (!function_exists('eottae_business_snippet_list')) {
    function eottae_business_snippet_list($mb_id)
    {
        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return array();
        }

        eottae_business_snippet_ensure_table();
        $table = eottae_business_snippet_table();
        $result = sql_query(" select * from `{$table}`
            where mb_id = '".sql_escape_string($mb_id)."'
            order by sort_order asc, snippet_id desc ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = array(
                'snippet_id' => (int) $row['snippet_id'],
                'label' => get_text($row['label']),
                'wr_subject' => get_text($row['wr_subject']),
                'wr_content' => $row['wr_content'],
                'updated_at' => $row['updated_at'],
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_business_snippet_get')) {
    function eottae_business_snippet_get($mb_id, $snippet_id)
    {
        $mb_id = trim((string) $mb_id);
        $snippet_id = (int) $snippet_id;
        if ($mb_id === '' || $snippet_id < 1) {
            return array();
        }

        eottae_business_snippet_ensure_table();
        $table = eottae_business_snippet_table();
        $row = sql_fetch(" select * from `{$table}`
            where snippet_id = '{$snippet_id}' and mb_id = '".sql_escape_string($mb_id)."' limit 1 ");

        if (empty($row['snippet_id'])) {
            return array();
        }

        return array(
            'snippet_id' => (int) $row['snippet_id'],
            'label' => get_text($row['label']),
            'wr_subject' => get_text($row['wr_subject']),
            'wr_content' => $row['wr_content'],
        );
    }
}

if (!function_exists('eottae_business_snippet_save')) {
    function eottae_business_snippet_save($mb_id, $data = array())
    {
        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return false;
        }

        eottae_business_snippet_ensure_table();
        $table = eottae_business_snippet_table();

        $snippet_id = isset($data['snippet_id']) ? (int) $data['snippet_id'] : 0;
        $label = isset($data['label']) ? trim(strip_tags((string) $data['label'])) : '';
        $wr_subject = isset($data['wr_subject']) ? trim(strip_tags((string) $data['wr_subject'])) : '';
        $wr_content = isset($data['wr_content']) ? trim((string) $data['wr_content']) : '';

        if ($label === '') {
            $label = $wr_subject !== '' ? $wr_subject : '홍보 문구';
        }
        if ($wr_content === '') {
            return false;
        }

        if (function_exists('cut_str')) {
            $label = cut_str($label, 100, '');
            $wr_subject = cut_str($wr_subject, 255, '');
        }

        $now = G5_TIME_YMDHIS;

        if ($snippet_id > 0) {
            $exists = eottae_business_snippet_get($mb_id, $snippet_id);
            if (empty($exists)) {
                return false;
            }

            sql_query(" update `{$table}` set
                label = '".sql_escape_string($label)."',
                wr_subject = '".sql_escape_string($wr_subject)."',
                wr_content = '".sql_escape_string($wr_content)."',
                updated_at = '{$now}'
                where snippet_id = '{$snippet_id}' and mb_id = '".sql_escape_string($mb_id)."' ");

            return $snippet_id;
        }

        $count_row = sql_fetch(" select count(*) as cnt from `{$table}` where mb_id = '".sql_escape_string($mb_id)."' ");
        if ((int) $count_row['cnt'] >= eottae_business_snippet_max_count()) {
            return false;
        }

        sql_query(" insert into `{$table}` set
            mb_id = '".sql_escape_string($mb_id)."',
            label = '".sql_escape_string($label)."',
            wr_subject = '".sql_escape_string($wr_subject)."',
            wr_content = '".sql_escape_string($wr_content)."',
            sort_order = 0,
            created_at = '{$now}',
            updated_at = '{$now}' ");

        return (int) sql_insert_id();
    }
}

if (!function_exists('eottae_business_snippet_delete')) {
    function eottae_business_snippet_delete($mb_id, $snippet_id)
    {
        $mb_id = trim((string) $mb_id);
        $snippet_id = (int) $snippet_id;
        if ($mb_id === '' || $snippet_id < 1) {
            return false;
        }

        eottae_business_snippet_ensure_table();
        $table = eottae_business_snippet_table();
        sql_query(" delete from `{$table}`
            where snippet_id = '{$snippet_id}' and mb_id = '".sql_escape_string($mb_id)."' ");

        return true;
    }
}
