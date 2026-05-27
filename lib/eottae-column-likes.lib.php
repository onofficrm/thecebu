<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_likes_table')) {
    function eottae_column_likes_table()
    {
        global $g5;
        if (!isset($g5['sebu_column_likes_table'])) {
            $g5['sebu_column_likes_table'] = G5_TABLE_PREFIX.'sebu_column_likes';
        }

        return $g5['sebu_column_likes_table'];
    }
}

if (!function_exists('eottae_column_likes_ensure_schema')) {
    function eottae_column_likes_ensure_schema()
    {
        $table = eottae_column_likes_table();
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
                UNIQUE KEY `uk_column_like_wr_mb` (`wr_id`, `mb_id`),
                KEY `idx_column_like_wr` (`wr_id`),
                KEY `idx_column_like_mb` (`mb_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", false);

        return array('ok' => $ok, 'action' => $ok ? 'created' : 'failed');
    }
}

if (!function_exists('eottae_column_like_count')) {
    function eottae_column_like_count($wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return 0;
        }

        eottae_column_likes_ensure_schema();
        $table = eottae_column_likes_table();
        $row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$table}` WHERE wr_id = '{$wr_id}' ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_column_member_liked')) {
    function eottae_column_member_liked($wr_id, $mb_id)
    {
        $wr_id = (int) $wr_id;
        $mb_id = trim((string) $mb_id);
        if ($wr_id < 1 || $mb_id === '') {
            return false;
        }

        eottae_column_likes_ensure_schema();
        $table = eottae_column_likes_table();
        $row = sql_fetch("
            SELECT id FROM `{$table}`
            WHERE wr_id = '{$wr_id}' AND mb_id = '".sql_escape_string($mb_id)."'
            LIMIT 1
        ", false);

        return !empty($row['id']);
    }
}

if (!function_exists('eottae_column_author_total_likes')) {
    function eottae_column_author_total_likes($mb_id)
    {
        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return 0;
        }

        if (!function_exists('eottae_column_write_table')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }

        eottae_column_likes_ensure_schema();
        $likes_table = eottae_column_likes_table();
        $write_table = eottae_column_write_table();
        $mb_id_sql = sql_escape_string($mb_id);

        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$likes_table}` l
            INNER JOIN `{$write_table}` w ON w.wr_id = l.wr_id AND w.wr_is_comment = 0
            WHERE w.mb_id = '{$mb_id_sql}'
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_column_toggle_like')) {
    function eottae_column_toggle_like($wr_id, $mb_id)
    {
        $wr_id = (int) $wr_id;
        $mb_id = trim((string) $mb_id);
        if ($wr_id < 1 || $mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 공감할 수 있습니다.');
        }

        if (!function_exists('eottae_column_get_write_row')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }
        $post = eottae_column_get_write_row($wr_id);
        if (!$post) {
            return array('ok' => false, 'message' => '컬럼을 찾을 수 없습니다.');
        }

        eottae_column_likes_ensure_schema();
        $table = eottae_column_likes_table();
        $mb_id_sql = sql_escape_string($mb_id);
        $existing = sql_fetch(" SELECT id FROM `{$table}` WHERE wr_id = '{$wr_id}' AND mb_id = '{$mb_id_sql}' LIMIT 1 ", false);

        if (!empty($existing['id'])) {
            sql_query(" DELETE FROM `{$table}` WHERE id = '".(int) $existing['id']."' ", false);
            $liked = false;
            $message = '공감을 취소했습니다.';
        } else {
            sql_query(" INSERT INTO `{$table}` SET wr_id = '{$wr_id}', mb_id = '{$mb_id_sql}', created_at = '".G5_TIME_YMDHIS."' ", false);
            $liked = true;
            $message = '공감했습니다.';
            if (function_exists('eottae_member_growth_on_plaza_like')) {
                include_once G5_LIB_PATH.'/eottae-member-growth-hooks.lib.php';
            }
            if (function_exists('eottae_member_growth_add_score') && !empty($post['mb_id']) && $post['mb_id'] !== $mb_id) {
                include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
                eottae_member_growth_add_score($post['mb_id'], 'like_received', 0, 'column', $wr_id, '컬럼 공감');
            }
        }

        return array(
            'ok'         => true,
            'liked'      => $liked,
            'like_count' => eottae_column_like_count($wr_id),
            'message'    => $message,
        );
    }
}
