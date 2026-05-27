<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_bookmarks_table')) {
    function eottae_column_bookmarks_table()
    {
        global $g5;
        if (!isset($g5['sebu_column_bookmarks_table'])) {
            $g5['sebu_column_bookmarks_table'] = G5_TABLE_PREFIX.'sebu_column_bookmarks';
        }

        return $g5['sebu_column_bookmarks_table'];
    }
}

if (!function_exists('eottae_column_bookmarks_ensure_schema')) {
    function eottae_column_bookmarks_ensure_schema()
    {
        $table = eottae_column_bookmarks_table();
        if (!function_exists('eottae_column_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }
        if (eottae_column_table_exists($table)) {
            return array('ok' => true, 'action' => 'exists');
        }

        $ok = (bool) sql_query("
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `wr_id` int(11) unsigned NOT NULL DEFAULT '0',
                `mb_id` varchar(20) NOT NULL DEFAULT '',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_column_bookmark_wr_mb` (`wr_id`, `mb_id`),
                KEY `idx_column_bookmark_mb` (`mb_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", false);

        return array('ok' => $ok, 'action' => $ok ? 'created' : 'failed');
    }
}

if (!function_exists('eottae_column_member_bookmarked')) {
    function eottae_column_member_bookmarked($wr_id, $mb_id)
    {
        $wr_id = (int) $wr_id;
        $mb_id = trim((string) $mb_id);
        if ($wr_id < 1 || $mb_id === '') {
            return false;
        }

        eottae_column_bookmarks_ensure_schema();
        $table = eottae_column_bookmarks_table();
        $row = sql_fetch("
            SELECT id FROM `{$table}`
            WHERE wr_id = '{$wr_id}' AND mb_id = '".sql_escape_string($mb_id)."'
            LIMIT 1
        ", false);

        return !empty($row['id']);
    }
}

if (!function_exists('eottae_column_toggle_bookmark')) {
    function eottae_column_toggle_bookmark($wr_id, $mb_id)
    {
        $wr_id = (int) $wr_id;
        $mb_id = trim((string) $mb_id);
        if ($wr_id < 1 || $mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 저장할 수 있습니다.');
        }

        if (!function_exists('eottae_column_get_write_row')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }
        if (!eottae_column_get_write_row($wr_id)) {
            return array('ok' => false, 'message' => '컬럼을 찾을 수 없습니다.');
        }

        eottae_column_bookmarks_ensure_schema();
        $table = eottae_column_bookmarks_table();
        $mb_id_sql = sql_escape_string($mb_id);
        $existing = sql_fetch(" SELECT id FROM `{$table}` WHERE wr_id = '{$wr_id}' AND mb_id = '{$mb_id_sql}' LIMIT 1 ", false);

        if (!empty($existing['id'])) {
            sql_query(" DELETE FROM `{$table}` WHERE id = '".(int) $existing['id']."' ", false);
            $bookmarked = false;
            $message = '저장을 취소했습니다.';
        } else {
            sql_query(" INSERT INTO `{$table}` SET wr_id = '{$wr_id}', mb_id = '{$mb_id_sql}', created_at = '".G5_TIME_YMDHIS."' ", false);
            $bookmarked = true;
            $message = '저장했습니다.';
        }

        return array(
            'ok'          => true,
            'bookmarked'  => $bookmarked,
            'message'     => $message,
        );
    }
}

if (!function_exists('eottae_column_list_bookmarks')) {
    function eottae_column_list_bookmarks($mb_id, $limit = 20)
    {
        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return array();
        }

        eottae_column_bookmarks_ensure_schema();
        if (!function_exists('eottae_column_list')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }

        $table = eottae_column_bookmarks_table();
        $mb_id_sql = sql_escape_string($mb_id);
        $limit = max(1, min(50, (int) $limit));
        $result = sql_query("
            SELECT wr_id FROM `{$table}`
            WHERE mb_id = '{$mb_id_sql}'
            ORDER BY id DESC
            LIMIT {$limit}
        ", false);

        $wr_ids = array();
        while ($row = sql_fetch_array($result)) {
            $wr_ids[] = (int) $row['wr_id'];
        }
        if (empty($wr_ids)) {
            return array();
        }

        $items = array();
        foreach ($wr_ids as $wr_id) {
            $post = eottae_column_get_post($wr_id, array('skip_hit' => true, 'member_mb_id' => $mb_id));
            if ($post) {
                $items[] = $post;
            }
        }

        return $items;
    }
}
