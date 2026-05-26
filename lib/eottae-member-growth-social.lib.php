<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_member_growth_social_bootstrap_tables')) {
    function eottae_member_growth_social_bootstrap_tables()
    {
        global $g5;

        $g5['sebu_featured_members_table'] = G5_TABLE_PREFIX.'sebu_featured_members';
        $g5['sebu_member_growth_prefs_table'] = G5_TABLE_PREFIX.'sebu_member_growth_prefs';
        $g5['sebu_ranking_snapshots_table'] = G5_TABLE_PREFIX.'sebu_ranking_snapshots';
        $g5['sebu_member_growth_cron_runs_table'] = G5_TABLE_PREFIX.'sebu_member_growth_cron_runs';
    }
}

if (!function_exists('eottae_member_growth_cron_runs_table')) {
    function eottae_member_growth_cron_runs_table()
    {
        eottae_member_growth_social_bootstrap_tables();
        global $g5;

        return $g5['sebu_member_growth_cron_runs_table'];
    }
}

if (!function_exists('eottae_member_growth_ranking_snapshots_table')) {
    function eottae_member_growth_ranking_snapshots_table()
    {
        eottae_member_growth_social_bootstrap_tables();
        global $g5;

        return $g5['sebu_ranking_snapshots_table'];
    }
}

if (!function_exists('eottae_member_growth_featured_table')) {
    function eottae_member_growth_featured_table()
    {
        eottae_member_growth_social_bootstrap_tables();
        global $g5;

        return $g5['sebu_featured_members_table'];
    }
}

if (!function_exists('eottae_member_growth_prefs_table')) {
    function eottae_member_growth_prefs_table()
    {
        eottae_member_growth_social_bootstrap_tables();
        global $g5;

        return $g5['sebu_member_growth_prefs_table'];
    }
}

if (!function_exists('eottae_member_growth_ensure_social_schema')) {
    function eottae_member_growth_ensure_social_schema()
    {
        eottae_member_growth_social_bootstrap_tables();

        $featured = eottae_member_growth_featured_table();
        $prefs = eottae_member_growth_prefs_table();
        $badges = eottae_member_growth_badges_table();
        $member_badges = eottae_member_growth_member_badges_table();

        sql_query(" CREATE TABLE IF NOT EXISTS `{$featured}` (
            `featured_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `week_key` varchar(10) NOT NULL DEFAULT '',
            `intro_text` varchar(255) NOT NULL DEFAULT '',
            `reason` varchar(255) NOT NULL DEFAULT '',
            `activity_summary` varchar(255) NOT NULL DEFAULT '',
            `show_on_main` tinyint(1) NOT NULL DEFAULT '1',
            `is_active` tinyint(1) NOT NULL DEFAULT '1',
            `sort_order` int(11) NOT NULL DEFAULT '0',
            `created_by` varchar(20) NOT NULL DEFAULT '',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`featured_id`),
            KEY `idx_week_main` (`week_key`, `show_on_main`, `is_active`),
            KEY `idx_mb_week` (`mb_id`, `week_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ", false);

        sql_query(" CREATE TABLE IF NOT EXISTS `{$prefs}` (
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `exclude_ranking` tinyint(1) NOT NULL DEFAULT '0',
            `mask_nickname` tinyint(1) NOT NULL DEFAULT '0',
            `public_bio` varchar(500) NOT NULL DEFAULT '',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`mb_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ", false);

        $snapshots = eottae_member_growth_ranking_snapshots_table();
        sql_query(" CREATE TABLE IF NOT EXISTS `{$snapshots}` (
            `snapshot_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `week_key` varchar(10) NOT NULL DEFAULT '',
            `ranking_type` varchar(20) NOT NULL DEFAULT 'week',
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `rank_position` int(11) NOT NULL DEFAULT '0',
            `rank_score` int(11) NOT NULL DEFAULT '0',
            `post_count` int(11) NOT NULL DEFAULT '0',
            `comment_count` int(11) NOT NULL DEFAULT '0',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`snapshot_id`),
            UNIQUE KEY `uk_week_type_mb` (`week_key`, `ranking_type`, `mb_id`),
            KEY `idx_week_type_rank` (`week_key`, `ranking_type`, `rank_position`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ", false);

        if (!sql_fetch(" SHOW COLUMNS FROM `{$prefs}` LIKE 'public_bio' ", false)) {
            sql_query(" ALTER TABLE `{$prefs}` ADD `public_bio` varchar(500) NOT NULL DEFAULT '' AFTER `mask_nickname` ", false);
        }

        if (!sql_fetch(" SHOW COLUMNS FROM `{$badges}` LIKE 'show_on_main' ", false)) {
            sql_query(" ALTER TABLE `{$badges}` ADD `show_on_main` tinyint(1) NOT NULL DEFAULT '1' AFTER `is_active` ", false);
        }
        if (!sql_fetch(" SHOW COLUMNS FROM `{$member_badges}` LIKE 'is_hidden' ", false)) {
            sql_query(" ALTER TABLE `{$member_badges}` ADD `is_hidden` tinyint(1) NOT NULL DEFAULT '0' AFTER `is_main` ", false);
        }

        $cron_runs = eottae_member_growth_cron_runs_table();
        sql_query(" CREATE TABLE IF NOT EXISTS `{$cron_runs}` (
            `run_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `task_name` varchar(40) NOT NULL DEFAULT '',
            `week_key` varchar(10) NOT NULL DEFAULT '',
            `status` varchar(20) NOT NULL DEFAULT 'ok',
            `message` varchar(255) NOT NULL DEFAULT '',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`run_id`),
            UNIQUE KEY `uk_task_week` (`task_name`, `week_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ", false);

        return true;
    }
}

if (!function_exists('eottae_member_growth_week_key')) {
    function eottae_member_growth_week_key($timestamp = null)
    {
        $timestamp = $timestamp === null ? time() : (int) $timestamp;

        return date('o', $timestamp).'-W'.date('W', $timestamp);
    }
}

if (!function_exists('eottae_member_growth_ranking_url')) {
    function eottae_member_growth_ranking_url($type = 'week')
    {
        $type = preg_replace('/[^a-z_]/', '', (string) $type);

        return G5_URL.'/ranking/?type='.$type;
    }
}

if (!function_exists('eottae_member_growth_badge_book_url')) {
    function eottae_member_growth_badge_book_url()
    {
        return G5_URL.'/badges/';
    }
}

if (!function_exists('eottae_member_growth_profile_url')) {
    function eottae_member_growth_profile_url($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return '';
        }

        return G5_URL.'/member/profile.php?mb_id='.urlencode($mb_id);
    }
}

if (!function_exists('eottae_member_growth_admin_ids')) {
    function eottae_member_growth_admin_ids()
    {
        global $config;

        $ids = array();
        if (!empty($config['cf_admin'])) {
            $ids[] = (string) $config['cf_admin'];
        }

        return array_values(array_unique(array_filter($ids)));
    }
}

if (!function_exists('eottae_member_growth_get_member_prefs')) {
    function eottae_member_growth_get_member_prefs($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $defaults = array('mb_id' => $mb_id, 'exclude_ranking' => 0, 'mask_nickname' => 0, 'public_bio' => '');

        if ($mb_id === '') {
            return $defaults;
        }

        $table = eottae_member_growth_prefs_table();
        if (!eottae_member_growth_table_exists($table)) {
            return $defaults;
        }

        $row = sql_fetch(" SELECT * FROM `{$table}` WHERE mb_id = '".sql_escape_string($mb_id)."' ", false);

        return is_array($row) && !empty($row['mb_id']) ? $row : $defaults;
    }
}

if (!function_exists('eottae_member_growth_save_member_prefs')) {
    function eottae_member_growth_save_member_prefs($mb_id, array $prefs)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return false;
        }

        eottae_member_growth_ensure_social_schema();
        $table = eottae_member_growth_prefs_table();
        $exclude = !empty($prefs['exclude_ranking']) ? 1 : 0;
        $mask = !empty($prefs['mask_nickname']) ? 1 : 0;
        $public_bio = isset($prefs['public_bio']) ? substr(strip_tags((string) $prefs['public_bio']), 0, 500) : null;
        $now = G5_TIME_YMDHIS;

        $bio_insert = '';
        if ($public_bio !== null) {
            $bio_insert = ", public_bio = '".sql_escape_string($public_bio)."'";
        }

        sql_query("
            INSERT INTO `{$table}`
            SET mb_id = '".sql_escape_string($mb_id)."',
                exclude_ranking = '{$exclude}',
                mask_nickname = '{$mask}'
                {$bio_insert},
                updated_at = '{$now}'
            ON DUPLICATE KEY UPDATE
                exclude_ranking = '{$exclude}',
                mask_nickname = '{$mask}'
                {$bio_insert},
                updated_at = '{$now}'
        ", false);

        return true;
    }
}

if (!function_exists('eottae_member_growth_is_ranking_excluded')) {
    function eottae_member_growth_is_ranking_excluded($mb_id, $mb_level = null)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return true;
        }

        if (in_array($mb_id, eottae_member_growth_admin_ids(), true)) {
            return true;
        }

        if ($mb_level !== null && (int) $mb_level >= 10) {
            return true;
        }

        $prefs = eottae_member_growth_get_member_prefs($mb_id);

        return !empty($prefs['exclude_ranking']);
    }
}

if (!function_exists('eottae_member_growth_should_mask_nick')) {
    function eottae_member_growth_should_mask_nick($mb_id, $mb_open = null)
    {
        $prefs = eottae_member_growth_get_member_prefs($mb_id);
        if (!empty($prefs['mask_nickname'])) {
            return true;
        }
        if ($mb_open !== null && (string) $mb_open !== '1') {
            return true;
        }

        return false;
    }
}

if (!function_exists('eottae_member_growth_mask_nick')) {
    function eottae_member_growth_mask_nick($nick)
    {
        $nick = trim(strip_tags((string) $nick));
        $len = mb_strlen($nick, 'UTF-8');
        if ($len <= 2) {
            return mb_substr($nick, 0, 1, 'UTF-8').'*';
        }
        if ($len === 3) {
            return mb_substr($nick, 0, 1, 'UTF-8').'*'.mb_substr($nick, 2, 1, 'UTF-8');
        }

        return mb_substr($nick, 0, 1, 'UTF-8').str_repeat('*', min(3, $len - 2)).mb_substr($nick, -1, 1, 'UTF-8');
    }
}

if (!function_exists('eottae_member_growth_ranking_exclude_sql')) {
    function eottae_member_growth_ranking_exclude_sql($member_alias = 'm')
    {
        $member_alias = preg_replace('/[^a-z_]/', '', (string) $member_alias);
        if ($member_alias === '') {
            $member_alias = 'm';
        }

        $parts = array(" {$member_alias}.mb_level < 10 ");
        $admin_ids = eottae_member_growth_admin_ids();
        foreach ($admin_ids as $admin_id) {
            $parts[] = " {$member_alias}.mb_id <> '".sql_escape_string($admin_id)."' ";
        }

        $prefs = eottae_member_growth_prefs_table();
        if (eottae_member_growth_table_exists($prefs)) {
            $parts[] = " NOT EXISTS (
                SELECT 1 FROM `{$prefs}` p
                WHERE p.mb_id = {$member_alias}.mb_id AND p.exclude_ranking = 1
            ) ";
        }

        return '('.implode(' AND ', $parts).')';
    }
}

if (!function_exists('eottae_member_growth_period_bounds')) {
    function eottae_member_growth_period_bounds($period)
    {
        $period = preg_replace('/[^a-z]/', '', (string) $period);
        $now = time();

        if ($period === 'month') {
            return array(
                'start' => date('Y-m-01 00:00:00', $now),
                'end'   => date('Y-m-t 23:59:59', $now),
            );
        }

        if ($period === 'week') {
            $dow = (int) date('N', $now);

            return array(
                'start' => date('Y-m-d 00:00:00', strtotime('-'.($dow - 1).' days', $now)),
                'end'   => date('Y-m-d 23:59:59', strtotime('+'.(7 - $dow).' days', $now)),
            );
        }

        return array('start' => '', 'end' => '');
    }
}

if (!function_exists('eottae_member_growth_week_bounds_for_key')) {
    function eottae_member_growth_week_bounds_for_key($week_key)
    {
        $week_key = trim((string) $week_key);
        if (preg_match('/^(\d{4})-W(\d{2})$/', $week_key, $matches)) {
            $dt = new DateTime();
            $dt->setISODate((int) $matches[1], (int) $matches[2]);

            return array(
                'start' => $dt->format('Y-m-d 00:00:00'),
                'end'   => $dt->modify('+6 days')->format('Y-m-d 23:59:59'),
            );
        }

        return eottae_member_growth_period_bounds('week');
    }
}

if (!function_exists('eottae_member_growth_previous_week_key')) {
    function eottae_member_growth_previous_week_key($timestamp = null)
    {
        $timestamp = $timestamp === null ? time() : (int) $timestamp;

        return eottae_member_growth_week_key(strtotime('-7 days', $timestamp));
    }
}

if (!function_exists('eottae_member_growth_ranking_types')) {
    function eottae_member_growth_ranking_types()
    {
        return array(
            'week'      => array('label' => '이번 주 활동', 'period' => 'week'),
            'month'     => array('label' => '이번 달 활동', 'period' => 'month'),
            'all'       => array('label' => '전체 활동', 'period' => 'all'),
            'info'      => array('label' => '정보공유', 'period' => 'month'),
            'comment'   => array('label' => '댓글', 'period' => 'month'),
            'challenge' => array('label' => '챌린지', 'period' => 'month'),
        );
    }
}

if (!function_exists('eottae_member_growth_ranking_list')) {
    function eottae_member_growth_ranking_list($type = 'week', $limit = 30, array $options = array())
    {
        $types = eottae_member_growth_ranking_types();
        $type = isset($types[$type]) ? $type : 'week';
        $limit = max(1, min(50, (int) $limit));

        global $g5;
        $member_table = $g5['member_table'];
        $scores_table = eottae_member_growth_scores_table();
        $logs_table = eottae_member_growth_logs_table();
        $exclude_sql = eottae_member_growth_ranking_exclude_sql('m');
        $items = array();
        $bounds_override = isset($options['bounds']) && is_array($options['bounds']) ? $options['bounds'] : null;

        if ($type === 'all') {
            $result = sql_query("
                SELECT m.mb_id, m.mb_nick, m.mb_open, m.mb_level,
                       s.total_score AS rank_score
                FROM `{$scores_table}` s
                INNER JOIN `{$member_table}` m ON m.mb_id = s.mb_id
                WHERE {$exclude_sql}
                ORDER BY s.total_score DESC, s.updated_at ASC
                LIMIT {$limit}
            ", false);
        } else {
            $meta = $types[$type];
            $period = $meta['period'] === 'all' ? 'month' : $meta['period'];
            $bounds = $bounds_override ?: eottae_member_growth_period_bounds($period);
            $action_filter = '';

            if ($type === 'info') {
                $action_filter = " AND l.action_type IN ('life_info_post', 'post_write') ";
            } elseif ($type === 'comment') {
                $action_filter = " AND l.action_type = 'comment_write' ";
            } elseif ($type === 'challenge') {
                $action_filter = " AND l.action_type = 'challenge_entry' ";
            }

            $result = sql_query("
                SELECT m.mb_id, m.mb_nick, m.mb_open, m.mb_level,
                       SUM(l.score) AS rank_score
                FROM `{$logs_table}` l
                INNER JOIN `{$member_table}` m ON m.mb_id = l.mb_id
                WHERE l.created_at >= '".sql_escape_string($bounds['start'])."'
                  AND l.created_at <= '".sql_escape_string($bounds['end'])."'
                  AND l.score > 0
                  {$action_filter}
                  AND {$exclude_sql}
                GROUP BY m.mb_id
                ORDER BY rank_score DESC
                LIMIT {$limit}
            ", false);
        }

        $rank = 0;
        while ($row = sql_fetch_array($result)) {
            if (!is_array($row) || empty($row['mb_id'])) {
                continue;
            }
            $rank++;
            $row['rank'] = $rank;
            $row['display_nick'] = eottae_member_growth_display_nick($row['mb_id'], $row['mb_nick'], $row['mb_open']);
            $items[] = $row;
        }

        if ($items) {
            $mb_ids = array_column($items, 'mb_id');
            eottae_member_growth_prefetch_members($mb_ids);
            foreach ($items as $i => $row) {
                $stats = eottae_member_growth_member_stats($row['mb_id']);
                $profile = eottae_member_growth_get_profile($row['mb_id']);
                $items[$i]['stats'] = $stats;
                $items[$i]['profile'] = $profile;
            }
        }

        return $items;
    }
}

if (!function_exists('eottae_member_growth_display_nick')) {
    function eottae_member_growth_display_nick($mb_id, $nick, $mb_open = null)
    {
        if (eottae_member_growth_should_mask_nick($mb_id, $mb_open)) {
            return eottae_member_growth_mask_nick($nick);
        }

        return get_text(strip_tags((string) $nick));
    }
}

if (!function_exists('eottae_member_growth_save_featured')) {
    function eottae_member_growth_save_featured(array $input, $admin_mb_id = '')
    {
        eottae_member_growth_ensure_social_schema();

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($input['mb_id'] ?? ''));
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '회원 ID를 입력해 주세요.');
        }

        $week_key = trim((string) ($input['week_key'] ?? ''));
        if ($week_key === '') {
            $week_key = eottae_member_growth_week_key();
        }

        $table = eottae_member_growth_featured_table();
        $featured_id = (int) ($input['featured_id'] ?? 0);
        $intro = substr(strip_tags((string) ($input['intro_text'] ?? '')), 0, 255);
        $reason = substr(strip_tags((string) ($input['reason'] ?? '')), 0, 255);
        $summary = substr(strip_tags((string) ($input['activity_summary'] ?? '')), 0, 255);
        $show_on_main = !empty($input['show_on_main']) ? 1 : 0;
        $is_active = !isset($input['is_active']) || !empty($input['is_active']) ? 1 : 0;
        $sort_order = (int) ($input['sort_order'] ?? 0);
        $now = G5_TIME_YMDHIS;
        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);

        if ($featured_id > 0) {
            sql_query("
                UPDATE `{$table}`
                SET mb_id = '".sql_escape_string($mb_id)."',
                    week_key = '".sql_escape_string($week_key)."',
                    intro_text = '".sql_escape_string($intro)."',
                    reason = '".sql_escape_string($reason)."',
                    activity_summary = '".sql_escape_string($summary)."',
                    show_on_main = '{$show_on_main}',
                    is_active = '{$is_active}',
                    sort_order = '{$sort_order}',
                    updated_at = '{$now}'
                WHERE featured_id = '{$featured_id}'
            ", false);

            return array('ok' => true, 'featured_id' => $featured_id);
        }

        sql_query("
            INSERT INTO `{$table}`
            SET mb_id = '".sql_escape_string($mb_id)."',
                week_key = '".sql_escape_string($week_key)."',
                intro_text = '".sql_escape_string($intro)."',
                reason = '".sql_escape_string($reason)."',
                activity_summary = '".sql_escape_string($summary)."',
                show_on_main = '{$show_on_main}',
                is_active = '{$is_active}',
                sort_order = '{$sort_order}',
                created_by = '".sql_escape_string($admin_mb_id)."',
                created_at = '{$now}',
                updated_at = '{$now}'
        ", false);

        return array('ok' => true, 'featured_id' => (int) sql_insert_id());
    }
}

if (!function_exists('eottae_member_growth_list_featured')) {
    function eottae_member_growth_list_featured($week_key = '', $main_only = false, $limit = 5)
    {
        eottae_member_growth_ensure_social_schema();

        if ($week_key === '') {
            $week_key = eottae_member_growth_week_key();
        }

        $table = eottae_member_growth_featured_table();
        global $g5;
        $member_table = $g5['member_table'];
        $where = " f.week_key = '".sql_escape_string($week_key)."' AND f.is_active = 1 ";
        if ($main_only) {
            $where .= ' AND f.show_on_main = 1 ';
        }

        $limit = max(1, min(10, (int) $limit));
        $result = sql_query("
            SELECT f.*, m.mb_nick, m.mb_open
            FROM `{$table}` f
            INNER JOIN `{$member_table}` m ON m.mb_id = f.mb_id
            WHERE {$where}
            ORDER BY f.sort_order ASC, f.featured_id DESC
            LIMIT {$limit}
        ", false);

        $items = array();
        while ($row = sql_fetch_array($result)) {
            if (!is_array($row)) {
                continue;
            }
            $mb_id = $row['mb_id'];
            eottae_member_growth_get_profile($mb_id);
            $row['display_nick'] = eottae_member_growth_display_nick($mb_id, $row['mb_nick'], $row['mb_open']);
            $row['profile'] = eottae_member_growth_get_profile($mb_id);
            $row['stats'] = eottae_member_growth_member_stats($mb_id);
            $bounds = eottae_member_growth_period_bounds('week');
            $logs = eottae_member_growth_logs_table();
            $wr = sql_fetch("
                SELECT COALESCE(SUM(score), 0) AS week_score
                FROM `{$logs}`
                WHERE mb_id = '".sql_escape_string($mb_id)."'
                  AND created_at >= '".sql_escape_string($bounds['start'])."'
                  AND created_at <= '".sql_escape_string($bounds['end'])."'
            ", false);
            $row['week_score'] = (int) ($wr['week_score'] ?? 0);
            $row['profile_url'] = eottae_member_growth_profile_url($mb_id);
            $items[] = $row;
        }

        return $items;
    }
}

if (!function_exists('eottae_member_growth_delete_featured')) {
    function eottae_member_growth_delete_featured($featured_id)
    {
        $featured_id = (int) $featured_id;
        if ($featured_id < 1) {
            return false;
        }

        $table = eottae_member_growth_featured_table();
        sql_query(" DELETE FROM `{$table}` WHERE featured_id = '{$featured_id}' ", false);

        return true;
    }
}

if (!function_exists('eottae_member_growth_recent_badge_feed')) {
    function eottae_member_growth_recent_badge_feed($limit = 5)
    {
        $limit = max(1, min(10, (int) $limit));
        $mb_badges = eottae_member_growth_member_badges_table();
        $badges = eottae_member_growth_badges_table();
        global $g5;
        $member_table = $g5['member_table'];

        $result = sql_query("
            SELECT mb.id, mb.mb_id, mb.badge_id, mb.created_at,
                   b.badge_name, b.badge_icon, b.badge_color, b.badge_type,
                   m.mb_nick, m.mb_open
            FROM `{$mb_badges}` mb
            INNER JOIN `{$badges}` b ON b.badge_id = mb.badge_id AND b.is_active = 1
            INNER JOIN `{$member_table}` m ON m.mb_id = mb.mb_id
            WHERE mb.is_hidden = 0
              AND (b.show_on_main = 1 OR b.show_on_main IS NULL)
              AND ".eottae_member_growth_ranking_exclude_sql('m')."
            ORDER BY mb.created_at DESC
            LIMIT {$limit}
        ", false);

        $items = array();
        while ($row = sql_fetch_array($result)) {
            if (!is_array($row)) {
                continue;
            }
            $row['display_nick'] = eottae_member_growth_display_nick($row['mb_id'], $row['mb_nick'], $row['mb_open']);
            $row['profile_url'] = eottae_member_growth_profile_url($row['mb_id']);
            $items[] = $row;
        }

        return $items;
    }
}

if (!function_exists('eottae_member_growth_badge_condition_label')) {
    function eottae_member_growth_badge_condition_label(array $badge)
    {
        $type = (string) ($badge['condition_type'] ?? '');
        $val = (int) ($badge['condition_value'] ?? 0);

        if ($type === 'manual' || (int) ($badge['is_auto'] ?? 0) === 0) {
            return '관리자 인증 필요';
        }

        $map = array(
            'first_post'      => '첫 글 작성',
            'first_comment'   => '첫 댓글 작성',
            'post_count'      => '글 '.$val.'개 작성',
            'comment_count'   => '댓글 '.$val.'개 작성',
            'score_min'       => '활동 점수 '.number_format($val).'점 이상',
            'challenge_once'  => '챌린지 1회 이상 참여',
            'days_active'     => '활동 '.$val.'일 이상',
        );

        return $map[$type] ?? ($type !== '' ? $type : '조건 미설정');
    }
}

if (!function_exists('eottae_member_growth_badge_book')) {
    function eottae_member_growth_badge_book($viewer_mb_id = '')
    {
        $viewer_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $viewer_mb_id);
        $badges_table = eottae_member_growth_badges_table();
        $owned = array();

        if ($viewer_mb_id !== '') {
            foreach (eottae_member_growth_list_member_badges($viewer_mb_id) as $row) {
                $owned[(int) $row['badge_id']] = $row;
            }
        }

        $result = sql_query("
            SELECT * FROM `{$badges_table}`
            WHERE is_active = 1
            ORDER BY sort_order ASC, badge_id ASC
        ", false);

        $items = array();
        while ($badge = sql_fetch_array($result)) {
            if (!is_array($badge)) {
                continue;
            }
            $bid = (int) $badge['badge_id'];
            $badge['owned'] = isset($owned[$bid]);
            $badge['is_main'] = !empty($owned[$bid]['is_main']);
            $badge['condition_label'] = eottae_member_growth_badge_condition_label($badge);

            $items[] = $badge;
        }

        return $items;
    }
}

if (!function_exists('eottae_member_growth_public_profile')) {
    function eottae_member_growth_public_profile($mb_id, $viewer_mb_id = '')
    {
        global $g5;

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $viewer_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $viewer_mb_id);

        if ($mb_id === '') {
            return null;
        }

        $member_table = $g5['member_table'];
        $mb = sql_fetch("
            SELECT mb_id, mb_nick, mb_open, mb_level, mb_datetime, mb_profile
            FROM `{$member_table}`
            WHERE mb_id = '".sql_escape_string($mb_id)."'
        ", false);

        if (empty($mb['mb_id'])) {
            return null;
        }

        $is_self = $viewer_mb_id !== '' && $viewer_mb_id === $mb_id;
        if (!$is_self && (string) ($mb['mb_open'] ?? '') !== '1') {
            return array(
                'mb_id'         => $mb_id,
                'is_private'    => true,
                'display_nick'  => eottae_member_growth_display_nick($mb_id, $mb['mb_nick'], $mb['mb_open']),
            );
        }

        $profile = eottae_member_growth_get_profile($mb_id);
        $stats = eottae_member_growth_member_stats($mb_id);
        $badges = eottae_member_growth_list_member_badges($mb_id);
        $visible_badges = array();
        foreach ($badges as $badge) {
            if (empty($badge['is_hidden'])) {
                $visible_badges[] = $badge;
            }
        }

        $recent_badges = array_slice($visible_badges, 0, 5);

        $member_prefs = eottae_member_growth_get_member_prefs($mb_id);
        $public_bio = trim((string) ($member_prefs['public_bio'] ?? ''));
        if ($public_bio === '' && $is_self) {
            $public_bio = trim(strip_tags((string) ($mb['mb_profile'] ?? '')));
        }

        return array(
            'mb_id'          => $mb_id,
            'is_private'     => false,
            'is_self'        => $is_self,
            'display_nick'   => eottae_member_growth_display_nick($mb_id, $mb['mb_nick'], $mb['mb_open']),
            'public_bio'     => $public_bio !== '' ? get_text($public_bio) : '',
            'mb_profile'     => $is_self ? get_text(strip_tags((string) $mb['mb_profile'])) : '',
            'profile'        => $profile,
            'stats'          => $stats,
            'badges'         => $visible_badges,
            'badge_count'    => count($visible_badges),
            'recent_badges'  => $recent_badges,
            'profile_url'    => eottae_member_growth_profile_url($mb_id),
        );
    }
}

if (!function_exists('eottae_member_growth_set_member_badge_hidden')) {
    function eottae_member_growth_set_member_badge_hidden($mb_id, $badge_id, $hidden = true)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $badge_id = (int) $badge_id;
        if ($mb_id === '' || $badge_id < 1) {
            return false;
        }

        $table = eottae_member_growth_member_badges_table();
        sql_query("
            UPDATE `{$table}`
            SET is_hidden = '".($hidden ? 1 : 0)."'
            WHERE mb_id = '".sql_escape_string($mb_id)."' AND badge_id = '{$badge_id}'
        ", false);
        eottae_member_growth_clear_cache($mb_id);

        return true;
    }
}

if (!function_exists('eottae_member_growth_set_badge_show_on_main')) {
    function eottae_member_growth_set_badge_show_on_main($badge_id, $show = true)
    {
        $badge_id = (int) $badge_id;
        if ($badge_id < 1) {
            return false;
        }

        $table = eottae_member_growth_badges_table();
        sql_query(" UPDATE `{$table}` SET show_on_main = '".($show ? 1 : 0)."' WHERE badge_id = '{$badge_id}' ", false);

        return true;
    }
}

if (!function_exists('eottae_member_growth_snapshot_week_rankings')) {
    function eottae_member_growth_snapshot_week_rankings($week_key = '', $ranking_type = 'week')
    {
        eottae_member_growth_ensure_social_schema();

        if ($week_key === '') {
            $week_key = eottae_member_growth_week_key();
        }
        $ranking_type = preg_replace('/[^a-z_]/', '', (string) $ranking_type);
        if ($ranking_type === '') {
            $ranking_type = 'week';
        }

        $table = eottae_member_growth_ranking_snapshots_table();
        $exists = sql_fetch("
            SELECT COUNT(*) AS cnt FROM `{$table}`
            WHERE week_key = '".sql_escape_string($week_key)."'
              AND ranking_type = '".sql_escape_string($ranking_type)."'
        ", false);
        if ((int) ($exists['cnt'] ?? 0) > 0) {
            return array('ok' => true, 'skipped' => true, 'message' => '이미 저장된 주간 랭킹입니다.');
        }

        $bounds = eottae_member_growth_week_bounds_for_key($week_key);
        $ranking = eottae_member_growth_ranking_list($ranking_type, 30, array('bounds' => $bounds));
        $now = G5_TIME_YMDHIS;
        $count = 0;

        foreach ($ranking as $row) {
            $stats = $row['stats'] ?? array();
            sql_query("
                INSERT INTO `{$table}`
                SET week_key = '".sql_escape_string($week_key)."',
                    ranking_type = '".sql_escape_string($ranking_type)."',
                    mb_id = '".sql_escape_string($row['mb_id'])."',
                    rank_position = '".(int) ($row['rank'] ?? 0)."',
                    rank_score = '".(int) ($row['rank_score'] ?? 0)."',
                    post_count = '".(int) ($stats['post_count'] ?? 0)."',
                    comment_count = '".(int) ($stats['comment_count'] ?? 0)."',
                    created_at = '{$now}'
            ", false);
            $count++;
        }

        return array('ok' => true, 'count' => $count, 'week_key' => $week_key);
    }
}

if (!function_exists('eottae_member_growth_list_ranking_history')) {
    function eottae_member_growth_list_ranking_history($week_key = '', $ranking_type = 'week', $limit = 10)
    {
        eottae_member_growth_ensure_social_schema();

        $table = eottae_member_growth_ranking_snapshots_table();
        global $g5;
        $member_table = $g5['member_table'];
        $ranking_type = preg_replace('/[^a-z_]/', '', (string) $ranking_type);

        if ($week_key === '') {
            $row = sql_fetch("
                SELECT week_key FROM `{$table}`
                WHERE ranking_type = '".sql_escape_string($ranking_type)."'
                ORDER BY week_key DESC
                LIMIT 1
            ", false);
            $week_key = (string) ($row['week_key'] ?? '');
        }

        if ($week_key === '') {
            return array('week_key' => '', 'items' => array());
        }

        $limit = max(1, min(30, (int) $limit));
        $result = sql_query("
            SELECT s.*, m.mb_nick, m.mb_open
            FROM `{$table}` s
            INNER JOIN `{$member_table}` m ON m.mb_id = s.mb_id
            WHERE s.week_key = '".sql_escape_string($week_key)."'
              AND s.ranking_type = '".sql_escape_string($ranking_type)."'
            ORDER BY s.rank_position ASC
            LIMIT {$limit}
        ", false);

        $items = array();
        while ($row = sql_fetch_array($result)) {
            if (!is_array($row)) {
                continue;
            }
            $row['display_nick'] = eottae_member_growth_display_nick($row['mb_id'], $row['mb_nick'], $row['mb_open']);
            $row['profile'] = eottae_member_growth_get_profile($row['mb_id']);
            $row['profile_url'] = eottae_member_growth_profile_url($row['mb_id']);
            $items[] = $row;
        }

        return array('week_key' => $week_key, 'items' => $items);
    }
}

if (!function_exists('eottae_member_growth_list_ranking_week_keys')) {
    function eottae_member_growth_list_ranking_week_keys($ranking_type = 'week', $limit = 8)
    {
        $table = eottae_member_growth_ranking_snapshots_table();
        if (!eottae_member_growth_table_exists($table)) {
            return array();
        }

        $limit = max(1, min(20, (int) $limit));
        $result = sql_query("
            SELECT DISTINCT week_key FROM `{$table}`
            WHERE ranking_type = '".sql_escape_string(preg_replace('/[^a-z_]/', '', (string) $ranking_type))."'
            ORDER BY week_key DESC
            LIMIT {$limit}
        ", false);

        $keys = array();
        while ($row = sql_fetch_array($result)) {
            if (!empty($row['week_key'])) {
                $keys[] = $row['week_key'];
            }
        }

        return $keys;
    }
}

if (!function_exists('eottae_member_growth_build_featured_summary')) {
    function eottae_member_growth_build_featured_summary($mb_id)
    {
        $stats = eottae_member_growth_member_stats($mb_id);
        $bounds = eottae_member_growth_period_bounds('week');
        $logs = eottae_member_growth_logs_table();
        $wr = sql_fetch("
            SELECT COALESCE(SUM(score), 0) AS week_score
            FROM `{$logs}`
            WHERE mb_id = '".sql_escape_string($mb_id)."'
              AND created_at >= '".sql_escape_string($bounds['start'])."'
              AND created_at <= '".sql_escape_string($bounds['end'])."'
        ", false);

        $parts = array('이번 주 '.number_format((int) ($wr['week_score'] ?? 0)).'점');
        if ((int) ($stats['post_count'] ?? 0) > 0) {
            $parts[] = '글 '.number_format((int) $stats['post_count']);
        }
        if ((int) ($stats['comment_count'] ?? 0) > 0) {
            $parts[] = '댓글 '.number_format((int) $stats['comment_count']);
        }
        if ((int) ($stats['challenge_count'] ?? 0) > 0) {
            $parts[] = '챌린지 '.number_format((int) $stats['challenge_count']);
        }

        return implode(' · ', $parts);
    }
}

if (!function_exists('eottae_member_growth_build_featured_intro')) {
    function eottae_member_growth_build_featured_intro($mb_id)
    {
        $prefs = eottae_member_growth_get_member_prefs($mb_id);
        if (!empty($prefs['public_bio'])) {
            return substr(strip_tags((string) $prefs['public_bio']), 0, 255);
        }

        $stats = eottae_member_growth_member_stats($mb_id);
        if ((int) ($stats['challenge_count'] ?? 0) > 0 && (int) ($stats['post_count'] ?? 0) > 0) {
            return '챌린지와 생활정보를 꾸준히 공유해 주셨어요.';
        }
        if ((int) ($stats['post_count'] ?? 0) >= 3) {
            return '유용한 생활 정보를 나눠 주셨어요.';
        }
        if ((int) ($stats['comment_count'] ?? 0) >= 10) {
            return '댓글로 이웃들과 따뜻하게 소통해 주셨어요.';
        }

        return '이번 주 커뮤니티 활동이 돋보였어요.';
    }
}

if (!function_exists('eottae_member_growth_delete_featured_for_week')) {
    function eottae_member_growth_delete_featured_for_week($week_key)
    {
        $week_key = trim((string) $week_key);
        if ($week_key === '') {
            return false;
        }

        $table = eottae_member_growth_featured_table();
        sql_query(" DELETE FROM `{$table}` WHERE week_key = '".sql_escape_string($week_key)."' ", false);

        return true;
    }
}

if (!function_exists('eottae_member_growth_auto_apply_featured')) {
    function eottae_member_growth_auto_apply_featured($admin_mb_id = '', $limit = 1, $force = false)
    {
        $week_key = eottae_member_growth_week_key();
        $existing = eottae_member_growth_list_featured($week_key, false, 1);

        if (!$force && !empty($existing)) {
            return array('ok' => false, 'message' => '이미 이번 주 우수회원이 등록되어 있습니다.');
        }

        if ($force && !empty($existing)) {
            eottae_member_growth_delete_featured_for_week($week_key);
        }

        $limit = max(1, min(5, (int) $limit));
        $ranking = eottae_member_growth_ranking_list('week', $limit);
        if (empty($ranking)) {
            return array('ok' => false, 'message' => '자동 선정할 활동 회원이 없습니다.');
        }

        $applied = 0;
        $sort = 0;
        foreach ($ranking as $row) {
            $result = eottae_member_growth_save_featured(array(
                'mb_id'            => $row['mb_id'],
                'week_key'         => $week_key,
                'intro_text'       => eottae_member_growth_build_featured_intro($row['mb_id']),
                'reason'           => '주간 활동 랭킹 자동 선정',
                'activity_summary' => eottae_member_growth_build_featured_summary($row['mb_id']),
                'show_on_main'     => ($sort === 0),
                'sort_order'       => $sort,
            ), $admin_mb_id);
            if (!empty($result['ok'])) {
                $applied++;
                $sort++;
            }
        }

        return array('ok' => $applied > 0, 'count' => $applied, 'message' => $applied.'명을 우수회원으로 등록했습니다.');
    }
}

if (!function_exists('eottae_member_growth_weekly_spotlight_member')) {
    function eottae_member_growth_weekly_spotlight_member()
    {
        $featured = eottae_member_growth_list_featured('', true, 1);
        if (!empty($featured)) {
            return $featured[0];
        }

        $ranking = eottae_member_growth_ranking_list('week', 1);
        if (empty($ranking)) {
            return null;
        }

        $row = $ranking[0];
        $mb_id = $row['mb_id'];

        return array(
            'mb_id'             => $mb_id,
            'display_nick'      => $row['display_nick'],
            'profile'           => $row['profile'] ?? eottae_member_growth_get_profile($mb_id),
            'profile_url'       => eottae_member_growth_profile_url($mb_id),
            'intro_text'        => eottae_member_growth_build_featured_intro($mb_id),
            'activity_summary'  => eottae_member_growth_build_featured_summary($mb_id),
            'week_score'        => (int) ($row['rank_score'] ?? 0),
            'stats'             => $row['stats'] ?? eottae_member_growth_member_stats($mb_id),
            'is_auto_suggested' => true,
        );
    }
}

if (!function_exists('eottae_member_growth_challenge_spotlight_list')) {
    function eottae_member_growth_challenge_spotlight_list($limit = 3)
    {
        if (!function_exists('eottae_challenge_entries_table')) {
            include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
        }

        eottae_challenge_ensure_schema();
        $entries = eottae_challenge_entries_table();
        if (!eottae_member_growth_table_exists($entries)) {
            return array();
        }

        $bounds = eottae_member_growth_period_bounds('week');
        global $g5;
        $member_table = $g5['member_table'];
        $exclude_sql = eottae_member_growth_ranking_exclude_sql('m');
        $limit = max(1, min(5, (int) $limit));

        $result = sql_query("
            SELECT e.mb_id, COUNT(*) AS entry_count, MAX(e.created_at) AS last_entry,
                   m.mb_nick, m.mb_open
            FROM `{$entries}` e
            INNER JOIN `{$member_table}` m ON m.mb_id = e.mb_id
            WHERE e.status = 'active'
              AND e.created_at >= '".sql_escape_string($bounds['start'])."'
              AND e.created_at <= '".sql_escape_string($bounds['end'])."'
              AND {$exclude_sql}
            GROUP BY e.mb_id
            ORDER BY entry_count DESC, last_entry DESC
            LIMIT {$limit}
        ", false);

        $items = array();
        while ($row = sql_fetch_array($result)) {
            if (!is_array($row) || empty($row['mb_id'])) {
                continue;
            }
            $row['display_nick'] = eottae_member_growth_display_nick($row['mb_id'], $row['mb_nick'], $row['mb_open']);
            $row['profile'] = eottae_member_growth_get_profile($row['mb_id']);
            $row['profile_url'] = eottae_member_growth_profile_url($row['mb_id']);
            $items[] = $row;
        }

        return $items;
    }
}

if (!function_exists('eottae_member_growth_save_level')) {
    function eottae_member_growth_save_level(array $input)
    {
        $level_id = (int) ($input['level_id'] ?? 0);
        $level_name = substr(strip_tags((string) ($input['level_name'] ?? '')), 0, 60);
        $level_description = substr(strip_tags((string) ($input['level_description'] ?? '')), 0, 255);
        $min_score = max(0, (int) ($input['min_score'] ?? 0));
        $icon = substr((string) ($input['icon'] ?? ''), 0, 20);
        $color = preg_replace('/[^a-z_]/', '', (string) ($input['color'] ?? 'default'));
        $sort_order = (int) ($input['sort_order'] ?? 0);
        $is_active = !isset($input['is_active']) || !empty($input['is_active']) ? 1 : 0;
        $now = G5_TIME_YMDHIS;

        if ($level_name === '') {
            return array('ok' => false, 'message' => '등급명을 입력해 주세요.');
        }

        $table = eottae_member_growth_levels_table();

        if ($level_id > 0) {
            sql_query("
                UPDATE `{$table}`
                SET level_name = '".sql_escape_string($level_name)."',
                    level_description = '".sql_escape_string($level_description)."',
                    min_score = '{$min_score}',
                    icon = '".sql_escape_string($icon)."',
                    color = '".sql_escape_string($color)."',
                    sort_order = '{$sort_order}',
                    is_active = '{$is_active}',
                    updated_at = '{$now}'
                WHERE level_id = '{$level_id}'
            ", false);

            return array('ok' => true, 'level_id' => $level_id);
        }

        sql_query("
            INSERT INTO `{$table}`
            SET level_name = '".sql_escape_string($level_name)."',
                level_description = '".sql_escape_string($level_description)."',
                min_score = '{$min_score}',
                icon = '".sql_escape_string($icon)."',
                color = '".sql_escape_string($color)."',
                sort_order = '{$sort_order}',
                is_active = '{$is_active}',
                created_at = '{$now}',
                updated_at = '{$now}'
        ", false);

        return array('ok' => true, 'level_id' => (int) sql_insert_id());
    }
}

if (!function_exists('eottae_member_growth_save_badge')) {
    function eottae_member_growth_save_badge(array $input)
    {
        $badge_id = (int) ($input['badge_id'] ?? 0);
        $badge_name = substr(strip_tags((string) ($input['badge_name'] ?? '')), 0, 80);
        $badge_description = substr(strip_tags((string) ($input['badge_description'] ?? '')), 0, 255);
        $badge_icon = substr((string) ($input['badge_icon'] ?? ''), 0, 20);
        $badge_color = preg_replace('/[^a-z_]/', '', (string) ($input['badge_color'] ?? 'default'));
        $badge_type = preg_replace('/[^a-z_]/', '', (string) ($input['badge_type'] ?? 'activity'));
        $condition_type = preg_replace('/[^a-z_]/', '', (string) ($input['condition_type'] ?? 'manual'));
        $condition_value = max(0, (int) ($input['condition_value'] ?? 0));
        $is_auto = !empty($input['is_auto']) ? 1 : 0;
        $is_active = !isset($input['is_active']) || !empty($input['is_active']) ? 1 : 0;
        $show_on_main = !isset($input['show_on_main']) || !empty($input['show_on_main']) ? 1 : 0;
        $sort_order = (int) ($input['sort_order'] ?? 0);
        $now = G5_TIME_YMDHIS;

        if ($badge_name === '') {
            return array('ok' => false, 'message' => '뱃지명을 입력해 주세요.');
        }

        $table = eottae_member_growth_badges_table();

        if ($badge_id > 0) {
            sql_query("
                UPDATE `{$table}`
                SET badge_name = '".sql_escape_string($badge_name)."',
                    badge_description = '".sql_escape_string($badge_description)."',
                    badge_icon = '".sql_escape_string($badge_icon)."',
                    badge_color = '".sql_escape_string($badge_color)."',
                    badge_type = '".sql_escape_string($badge_type)."',
                    condition_type = '".sql_escape_string($condition_type)."',
                    condition_value = '{$condition_value}',
                    is_auto = '{$is_auto}',
                    is_active = '{$is_active}',
                    show_on_main = '{$show_on_main}',
                    sort_order = '{$sort_order}',
                    updated_at = '{$now}'
                WHERE badge_id = '{$badge_id}'
            ", false);

            return array('ok' => true, 'badge_id' => $badge_id);
        }

        sql_query("
            INSERT INTO `{$table}`
            SET badge_name = '".sql_escape_string($badge_name)."',
                badge_description = '".sql_escape_string($badge_description)."',
                badge_icon = '".sql_escape_string($badge_icon)."',
                badge_color = '".sql_escape_string($badge_color)."',
                badge_type = '".sql_escape_string($badge_type)."',
                condition_type = '".sql_escape_string($condition_type)."',
                condition_value = '{$condition_value}',
                is_auto = '{$is_auto}',
                is_active = '{$is_active}',
                show_on_main = '{$show_on_main}',
                sort_order = '{$sort_order}',
                created_at = '{$now}',
                updated_at = '{$now}'
        ", false);

        return array('ok' => true, 'badge_id' => (int) sql_insert_id());
    }
}

if (!function_exists('eottae_member_growth_verify_cron_key')) {
    function eottae_member_growth_verify_cron_key($provided_key)
    {
        if (!function_exists('g5site_cfg') && defined('G5_PATH') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }

        $keys = array();
        if (function_exists('g5site_cfg')) {
            foreach (array('member_growth_cron_key', 'talkroom_ai_cron_key') as $cfg_key) {
                $val = trim((string) g5site_cfg($cfg_key, ''));
                if ($val !== '') {
                    $keys[] = $val;
                }
            }
        }

        if (empty($keys)) {
            return php_sapi_name() === 'cli';
        }

        $provided_key = trim((string) $provided_key);

        foreach ($keys as $key) {
            if ($provided_key !== '' && hash_equals($key, $provided_key)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('eottae_member_growth_cron_run_exists')) {
    function eottae_member_growth_cron_run_exists($task_name, $week_key)
    {
        $table = eottae_member_growth_cron_runs_table();
        if (!eottae_member_growth_table_exists($table)) {
            return false;
        }

        $task_name = preg_replace('/[^a-z_]/', '', (string) $task_name);
        $row = sql_fetch("
            SELECT run_id FROM `{$table}`
            WHERE task_name = '".sql_escape_string($task_name)."'
              AND week_key = '".sql_escape_string((string) $week_key)."'
            LIMIT 1
        ", false);

        return !empty($row['run_id']);
    }
}

if (!function_exists('eottae_member_growth_log_cron_run')) {
    function eottae_member_growth_log_cron_run($task_name, $week_key, $status, $message = '')
    {
        eottae_member_growth_ensure_social_schema();
        $table = eottae_member_growth_cron_runs_table();
        $task_name = preg_replace('/[^a-z_]/', '', (string) $task_name);
        $status = preg_replace('/[^a-z_]/', '', (string) $status);
        if ($status === '') {
            $status = 'ok';
        }

        sql_query("
            INSERT INTO `{$table}`
            SET task_name = '".sql_escape_string($task_name)."',
                week_key = '".sql_escape_string((string) $week_key)."',
                status = '".sql_escape_string($status)."',
                message = '".sql_escape_string(substr(strip_tags((string) $message), 0, 255))."',
                created_at = '".G5_TIME_YMDHIS."'
            ON DUPLICATE KEY UPDATE
                status = '".sql_escape_string($status)."',
                message = '".sql_escape_string(substr(strip_tags((string) $message), 0, 255))."',
                created_at = '".G5_TIME_YMDHIS."'
        ", false);

        return true;
    }
}

if (!function_exists('eottae_member_growth_run_weekly_cron')) {
    function eottae_member_growth_run_weekly_cron(array $options = array())
    {
        eottae_member_growth_ensure_schema();

        $dry_run = !empty($options['dry_run']);
        $force_featured = !empty($options['force_featured']);
        $featured_limit = max(1, min(5, (int) ($options['featured_limit'] ?? 3)));
        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($options['admin_mb_id'] ?? 'system'));

        $current_week = eottae_member_growth_week_key();
        $previous_week = eottae_member_growth_previous_week_key();
        $summary = array(
            'dry_run'       => $dry_run,
            'current_week'  => $current_week,
            'previous_week' => $previous_week,
            'snapshot'      => null,
            'featured'      => null,
        );

        if (!$dry_run && !eottae_member_growth_cron_run_exists('weekly_snapshot', $previous_week)) {
            $summary['snapshot'] = eottae_member_growth_snapshot_week_rankings($previous_week, 'week');
            $snap = $summary['snapshot'];
            $msg = !empty($snap['skipped']) ? 'snapshot skipped' : 'snapshot saved '.(int) ($snap['count'] ?? 0);
            eottae_member_growth_log_cron_run('weekly_snapshot', $previous_week, 'ok', $msg);
        } elseif ($dry_run) {
            $summary['snapshot'] = array('ok' => true, 'dry_run' => true, 'week_key' => $previous_week);
        } else {
            $summary['snapshot'] = array('ok' => true, 'skipped' => true, 'week_key' => $previous_week);
        }

        $should_feature = $force_featured || !eottae_member_growth_cron_run_exists('weekly_featured', $current_week);
        if (!$dry_run && $should_feature) {
            $summary['featured'] = eottae_member_growth_auto_apply_featured($admin_mb_id, $featured_limit, $force_featured);
            $feat = $summary['featured'];
            eottae_member_growth_log_cron_run(
                'weekly_featured',
                $current_week,
                !empty($feat['ok']) ? 'ok' : 'skip',
                (string) ($feat['message'] ?? '')
            );
        } elseif ($dry_run) {
            $summary['featured'] = array('ok' => true, 'dry_run' => true, 'limit' => $featured_limit);
        } else {
            $summary['featured'] = array('ok' => true, 'skipped' => true);
        }

        $summary['ok'] = true;

        return $summary;
    }
}
