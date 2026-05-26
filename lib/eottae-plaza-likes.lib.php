<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_plaza_likes_table')) {
    function eottae_plaza_likes_table()
    {
        global $g5;
        if (!isset($g5['sebu_plaza_likes_table'])) {
            $g5['sebu_plaza_likes_table'] = G5_TABLE_PREFIX.'sebu_plaza_likes';
        }

        return $g5['sebu_plaza_likes_table'];
    }
}

if (!function_exists('eottae_plaza_likes_ensure_schema')) {
    function eottae_plaza_likes_ensure_schema()
    {
        $table = eottae_plaza_likes_table();
        if (!function_exists('eottae_talkroom_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }
        if (eottae_talkroom_table_exists($table)) {
            return array('ok' => true, 'action' => 'exists');
        }

        $ok = (bool) sql_query("
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `wr_id` int(11) unsigned NOT NULL DEFAULT '0',
                `mb_id` varchar(20) NOT NULL DEFAULT '',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_plaza_like_wr_mb` (`wr_id`, `mb_id`),
                KEY `idx_plaza_like_wr` (`wr_id`),
                KEY `idx_plaza_like_mb` (`mb_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", false);

        return array('ok' => $ok, 'action' => $ok ? 'created' : 'failed');
    }
}

if (!function_exists('eottae_plaza_member_token')) {
    function eottae_plaza_member_token($regenerate = false)
    {
        $token = get_session('eottae_plaza_member_token');
        if ($regenerate || $token === '') {
            $token = bin2hex(random_bytes(16));
            set_session('eottae_plaza_member_token', $token);
        }

        return (string) $token;
    }
}

if (!function_exists('eottae_plaza_verify_member_token')) {
    function eottae_plaza_verify_member_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_plaza_member_token');

        return $token !== '' && $session_token !== '' && hash_equals((string) $session_token, $token);
    }
}

if (!function_exists('eottae_plaza_get_post_row')) {
    function eottae_plaza_get_post_row($wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || !function_exists('eottae_plaza_board_table')) {
            return null;
        }

        include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
        global $g5;
        $write_table = $g5['write_prefix'].eottae_plaza_board_table();
        $row = sql_fetch("
            SELECT *
            FROM `{$write_table}`
            WHERE wr_id = '{$wr_id}'
              AND wr_is_comment = 0
            LIMIT 1
        ", false);

        return is_array($row) && !empty($row['wr_id']) ? $row : null;
    }
}

if (!function_exists('eottae_plaza_like_count')) {
    function eottae_plaza_like_count($wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return 0;
        }

        eottae_plaza_likes_ensure_schema();
        $table = eottae_plaza_likes_table();
        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$table}`
            WHERE wr_id = '{$wr_id}'
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_plaza_like_counts_batch')) {
    /**
     * @param int[] $wr_ids
     * @return array<int, int>
     */
    function eottae_plaza_like_counts_batch(array $wr_ids)
    {
        $wr_ids = array_values(array_unique(array_filter(array_map('intval', $wr_ids))));
        if (empty($wr_ids)) {
            return array();
        }

        eottae_plaza_likes_ensure_schema();
        $table = eottae_plaza_likes_table();
        $in = implode(',', $wr_ids);
        $counts = array();
        $result = sql_query("
            SELECT wr_id, COUNT(*) AS cnt
            FROM `{$table}`
            WHERE wr_id IN ({$in})
            GROUP BY wr_id
        ", false);
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $counts[(int) ($row['wr_id'] ?? 0)] = (int) ($row['cnt'] ?? 0);
            }
        }

        return $counts;
    }
}

if (!function_exists('eottae_plaza_user_liked_batch')) {
    /**
     * @param int[] $wr_ids
     * @return array<int, bool>
     */
    function eottae_plaza_user_liked_batch($mb_id, array $wr_ids)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $wr_ids = array_values(array_unique(array_filter(array_map('intval', $wr_ids))));
        if ($mb_id === '' || empty($wr_ids)) {
            return array();
        }

        eottae_plaza_likes_ensure_schema();
        $table = eottae_plaza_likes_table();
        $in = implode(',', $wr_ids);
        $liked = array();
        $result = sql_query("
            SELECT wr_id
            FROM `{$table}`
            WHERE mb_id = '".sql_escape_string($mb_id)."'
              AND wr_id IN ({$in})
        ", false);
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $liked[(int) ($row['wr_id'] ?? 0)] = true;
            }
        }

        return $liked;
    }
}

if (!function_exists('eottae_plaza_toggle_like')) {
    /**
     * @return array{ok: bool, message: string, liked: int, count: int}
     */
    function eottae_plaza_toggle_like($wr_id, $mb_id)
    {
        $wr_id = (int) $wr_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($wr_id < 1 || $mb_id === '') {
            return array('ok' => false, 'message' => '잘못된 요청입니다.', 'liked' => 0, 'count' => 0);
        }

        $post = eottae_plaza_get_post_row($wr_id);
        if (!$post) {
            return array('ok' => false, 'message' => '글을 찾을 수 없습니다.', 'liked' => 0, 'count' => 0);
        }

        if (!function_exists('eottae_plaza_is_post_visible') || !eottae_plaza_is_post_visible($post, false)) {
            return array('ok' => false, 'message' => '공감할 수 없는 글입니다.', 'liked' => 0, 'count' => 0);
        }

        if (($post['mb_id'] ?? '') === $mb_id) {
            return array('ok' => false, 'message' => '본인 글에는 공감할 수 없습니다.', 'liked' => 0, 'count' => 0);
        }

        eottae_plaza_likes_ensure_schema();
        $table = eottae_plaza_likes_table();
        $mb_sql = sql_escape_string($mb_id);
        $existing = sql_fetch("
            SELECT id
            FROM `{$table}`
            WHERE wr_id = '{$wr_id}'
              AND mb_id = '{$mb_sql}'
            LIMIT 1
        ", false);

        if (!empty($existing['id'])) {
            sql_query("
                DELETE FROM `{$table}`
                WHERE id = '".(int) $existing['id']."'
                  AND mb_id = '{$mb_sql}'
            ", false);
            $liked = 0;
        } else {
            $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
            $ok = (bool) sql_query("
                INSERT INTO `{$table}` SET
                    wr_id = '{$wr_id}',
                    mb_id = '{$mb_sql}',
                    created_at = '{$now}'
            ", false);
            if (!$ok) {
                return array('ok' => false, 'message' => '공감 처리에 실패했습니다.', 'liked' => 0, 'count' => eottae_plaza_like_count($wr_id));
            }
            $liked = 1;
            if (function_exists('eottae_member_growth_on_plaza_like_received') && !empty($post['mb_id'])) {
                eottae_member_growth_on_plaza_like_received($post['mb_id'], $wr_id);
            }
        }

        return array(
            'ok'      => true,
            'message' => $liked ? '공감했습니다.' : '공감을 취소했습니다.',
            'liked'   => $liked,
            'count'   => eottae_plaza_like_count($wr_id),
        );
    }
}
