<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_challenge_likes_table')) {
    function eottae_challenge_likes_table()
    {
        global $g5;
        if (!isset($g5['sebu_challenge_likes_table'])) {
            $g5['sebu_challenge_likes_table'] = G5_TABLE_PREFIX.'sebu_challenge_likes';
        }

        return $g5['sebu_challenge_likes_table'];
    }
}

if (!function_exists('eottae_challenge_likes_ensure_schema')) {
    function eottae_challenge_likes_ensure_schema()
    {
        if (!function_exists('eottae_challenge_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
        }

        $table = eottae_challenge_likes_table();
        if (eottae_challenge_table_exists($table)) {
            return array('ok' => true, 'action' => 'exists');
        }

        $ok = (bool) sql_query("
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `entry_id` int(11) unsigned NOT NULL DEFAULT '0',
                `mb_id` varchar(20) NOT NULL DEFAULT '',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_entry_mb` (`entry_id`, `mb_id`),
                KEY `idx_entry` (`entry_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", false);

        return array('ok' => $ok, 'action' => $ok ? 'created' : 'failed');
    }
}

if (!function_exists('eottae_challenge_like_count')) {
    function eottae_challenge_like_count($entry_id)
    {
        $entry_id = (int) $entry_id;
        if ($entry_id < 1) {
            return 0;
        }

        eottae_challenge_likes_ensure_schema();
        $table = eottae_challenge_likes_table();
        $row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$table}` WHERE entry_id = '{$entry_id}' ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_challenge_member_liked')) {
    function eottae_challenge_member_liked($entry_id, $mb_id)
    {
        $entry_id = (int) $entry_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($entry_id < 1 || $mb_id === '') {
            return false;
        }

        eottae_challenge_likes_ensure_schema();
        $table = eottae_challenge_likes_table();
        $row = sql_fetch("
            SELECT id FROM `{$table}`
            WHERE entry_id = '{$entry_id}' AND mb_id = '".sql_escape_string($mb_id)."'
            LIMIT 1
        ", false);

        return !empty($row['id']);
    }
}

if (!function_exists('eottae_challenge_toggle_like')) {
    function eottae_challenge_toggle_like($entry_id, $mb_id)
    {
        if (!function_exists('eottae_challenge_get_entry')) {
            include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
        }

        $entry = eottae_challenge_get_entry($entry_id, true);
        if (!$entry) {
            return array('ok' => false, 'message' => '참여글을 찾을 수 없습니다.');
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 공감할 수 있습니다.');
        }

        eottae_challenge_likes_ensure_schema();
        $table = eottae_challenge_likes_table();
        $entry_id = (int) $entry_id;

        if (eottae_challenge_member_liked($entry_id, $mb_id)) {
            sql_query("
                DELETE FROM `{$table}`
                WHERE entry_id = '{$entry_id}' AND mb_id = '".sql_escape_string($mb_id)."'
            ", false);
            $liked = false;
        } else {
            sql_query("
                INSERT INTO `{$table}`
                SET entry_id = '{$entry_id}',
                    mb_id = '".sql_escape_string($mb_id)."',
                    created_at = '".G5_TIME_YMDHIS."'
            ", false);
            $liked = true;
            if (function_exists('eottae_member_growth_on_challenge_like_received') && !empty($entry['mb_id']) && $entry['mb_id'] !== $mb_id) {
                eottae_member_growth_on_challenge_like_received($entry['mb_id'], $entry_id);
            }
        }

        return array(
            'ok'         => true,
            'liked'      => $liked,
            'like_count' => eottae_challenge_like_count($entry_id),
        );
    }
}
