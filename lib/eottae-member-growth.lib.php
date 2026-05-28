<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_member_growth_bootstrap_tables')) {
    function eottae_member_growth_bootstrap_tables()
    {
        global $g5;

        $g5['sebu_member_levels_table'] = G5_TABLE_PREFIX.'sebu_member_levels';
        $g5['sebu_member_activity_scores_table'] = G5_TABLE_PREFIX.'sebu_member_activity_scores';
        $g5['sebu_member_score_logs_table'] = G5_TABLE_PREFIX.'sebu_member_score_logs';
        $g5['sebu_badges_table'] = G5_TABLE_PREFIX.'sebu_badges';
        $g5['sebu_member_badges_table'] = G5_TABLE_PREFIX.'sebu_member_badges';
    }
}

if (!function_exists('eottae_member_growth_table_exists')) {
    function eottae_member_growth_table_exists($table)
    {
        $table = preg_replace('/[^a-z0-9_]/i', '', (string) $table);
        if ($table === '') {
            return false;
        }
        if (!function_exists('eottae_talkroom_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        return eottae_talkroom_table_exists($table);
    }
}

if (!function_exists('eottae_member_growth_levels_table')) {
    function eottae_member_growth_levels_table()
    {
        eottae_member_growth_bootstrap_tables();
        global $g5;

        return $g5['sebu_member_levels_table'];
    }
}

if (!function_exists('eottae_member_growth_scores_table')) {
    function eottae_member_growth_scores_table()
    {
        eottae_member_growth_bootstrap_tables();
        global $g5;

        return $g5['sebu_member_activity_scores_table'];
    }
}

if (!function_exists('eottae_member_growth_logs_table')) {
    function eottae_member_growth_logs_table()
    {
        eottae_member_growth_bootstrap_tables();
        global $g5;

        return $g5['sebu_member_score_logs_table'];
    }
}

if (!function_exists('eottae_member_growth_badges_table')) {
    function eottae_member_growth_badges_table()
    {
        eottae_member_growth_bootstrap_tables();
        global $g5;

        return $g5['sebu_badges_table'];
    }
}

if (!function_exists('eottae_member_growth_member_badges_table')) {
    function eottae_member_growth_member_badges_table()
    {
        eottae_member_growth_bootstrap_tables();
        global $g5;

        return $g5['sebu_member_badges_table'];
    }
}

if (!function_exists('eottae_member_growth_score_rules')) {
    function eottae_member_growth_score_rules()
    {
        return array(
            'register'         => array('score' => 100, 'daily_max' => 0, 'once' => true),
            'first_post'       => array('score' => 50, 'daily_max' => 0, 'once' => true),
            'post_write'       => array('score' => 20, 'daily_max' => 100),
            'comment_write'    => array('score' => 5, 'daily_max' => 50),
            'talkroom_join'    => array('score' => 10, 'daily_max' => 30),
            'talkroom_post'    => array('score' => 20, 'daily_max' => 100),
            'calendar_event'   => array('score' => 30, 'daily_max' => 60),
            'challenge_entry'  => array('score' => 50, 'daily_max' => 100),
            'used_goods_post'  => array('score' => 20, 'daily_max' => 80),
            'life_info_post'   => array('score' => 30, 'daily_max' => 90),
            'like_received'    => array('score' => 5, 'daily_max' => 25),
            'report_confirmed' => array('score' => 20, 'daily_max' => 40),
            'best_post'        => array('score' => 100, 'daily_max' => 0),
            'admin_grant'      => array('score' => 0, 'daily_max' => 0),
        );
    }
}

if (!function_exists('eottae_member_growth_daily_total_cap')) {
    function eottae_member_growth_daily_total_cap()
    {
        return 500;
    }
}

if (!function_exists('eottae_member_growth_ensure_schema')) {
    function eottae_member_growth_ensure_schema()
    {
        eottae_member_growth_bootstrap_tables();

        $levels = eottae_member_growth_levels_table();
        $scores = eottae_member_growth_scores_table();
        $logs = eottae_member_growth_logs_table();
        $badges = eottae_member_growth_badges_table();
        $member_badges = eottae_member_growth_member_badges_table();

        sql_query(" CREATE TABLE IF NOT EXISTS `{$levels}` (
            `level_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `level_name` varchar(60) NOT NULL DEFAULT '',
            `level_description` varchar(255) NOT NULL DEFAULT '',
            `min_score` int(11) NOT NULL DEFAULT '0',
            `icon` varchar(20) NOT NULL DEFAULT '',
            `color` varchar(30) NOT NULL DEFAULT 'default',
            `sort_order` int(11) NOT NULL DEFAULT '0',
            `is_active` tinyint(1) NOT NULL DEFAULT '1',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`level_id`),
            KEY `idx_active_sort` (`is_active`, `sort_order`),
            KEY `idx_min_score` (`min_score`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ", false);

        sql_query(" CREATE TABLE IF NOT EXISTS `{$scores}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `total_score` int(11) NOT NULL DEFAULT '0',
            `today_score` int(11) NOT NULL DEFAULT '0',
            `last_score_date` date DEFAULT NULL,
            `current_level_id` int(11) unsigned NOT NULL DEFAULT '0',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_mb_id` (`mb_id`),
            KEY `idx_total_score` (`total_score`),
            KEY `idx_level` (`current_level_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ", false);

        sql_query(" CREATE TABLE IF NOT EXISTS `{$logs}` (
            `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `action_type` varchar(40) NOT NULL DEFAULT '',
            `score` int(11) NOT NULL DEFAULT '0',
            `target_type` varchar(30) NOT NULL DEFAULT '',
            `target_id` int(11) unsigned NOT NULL DEFAULT '0',
            `memo` varchar(255) NOT NULL DEFAULT '',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`log_id`),
            KEY `idx_mb_created` (`mb_id`, `created_at`),
            KEY `idx_action` (`action_type`),
            KEY `idx_target` (`target_type`, `target_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ", false);

        sql_query(" CREATE TABLE IF NOT EXISTS `{$badges}` (
            `badge_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `badge_name` varchar(80) NOT NULL DEFAULT '',
            `badge_description` varchar(255) NOT NULL DEFAULT '',
            `badge_icon` varchar(20) NOT NULL DEFAULT '',
            `badge_color` varchar(30) NOT NULL DEFAULT 'default',
            `badge_type` varchar(20) NOT NULL DEFAULT 'activity',
            `condition_type` varchar(30) NOT NULL DEFAULT '',
            `condition_value` int(11) NOT NULL DEFAULT '0',
            `is_auto` tinyint(1) NOT NULL DEFAULT '1',
            `is_active` tinyint(1) NOT NULL DEFAULT '1',
            `sort_order` int(11) NOT NULL DEFAULT '0',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`badge_id`),
            KEY `idx_type_active` (`badge_type`, `is_active`),
            KEY `idx_condition` (`condition_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ", false);

        sql_query(" CREATE TABLE IF NOT EXISTS `{$member_badges}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `badge_id` int(11) unsigned NOT NULL DEFAULT '0',
            `is_main` tinyint(1) NOT NULL DEFAULT '0',
            `awarded_by` varchar(20) NOT NULL DEFAULT '',
            `awarded_reason` varchar(255) NOT NULL DEFAULT '',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_mb_badge` (`mb_id`, `badge_id`),
            KEY `idx_mb_main` (`mb_id`, `is_main`),
            KEY `idx_badge` (`badge_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ", false);

        eottae_member_growth_seed_defaults();
        eottae_member_growth_seed_extra_badges();

        if (function_exists('eottae_member_growth_ensure_social_schema')) {
            eottae_member_growth_ensure_social_schema();
        }

        return true;
    }
}

if (!function_exists('eottae_member_growth_seed_extra_badges')) {
    function eottae_member_growth_seed_extra_badges()
    {
        $badges_table = eottae_member_growth_badges_table();
        $now = G5_TIME_YMDHIS;
        $badges = array(
            array('가입 30일', '가입 후 30일이 지난 회원', '📅', 'activity', 'days_active', 30, 35),
            array('가입 100일', '가입 후 100일이 지난 회원', '🗓', 'activity', 'days_active', 100, 36),
            array('정보공유왕', '글 50개 작성', '📚', 'community', 'post_count', 50, 45),
            array('세부 정착러', '활동 점수 5000점 달성', '🏠', 'community', 'score_min', 5000, 55),
            array('세부톡 방장', '세부톡 방장으로 운영 중', '🎙', 'community', 'manual', 0, 52),
        );

        foreach ($badges as $badge) {
            $name = (string) ($badge[0] ?? '');
            if ($name === '') {
                continue;
            }
            $exists = sql_fetch("
                SELECT badge_id
                FROM `{$badges_table}`
                WHERE badge_name = '".sql_escape_string($name)."'
                LIMIT 1
            ", false);
            if (!empty($exists['badge_id'])) {
                continue;
            }

            $condition_type = (string) ($badge[4] ?? '');
            sql_query("
                INSERT INTO `{$badges_table}`
                SET badge_name = '".sql_escape_string($name)."',
                    badge_description = '".sql_escape_string($badge[1])."',
                    badge_icon = '".sql_escape_string($badge[2])."',
                    badge_color = 'default',
                    badge_type = '".sql_escape_string($badge[3])."',
                    condition_type = '".sql_escape_string($condition_type)."',
                    condition_value = '".(int) ($badge[5] ?? 0)."',
                    is_auto = '".($condition_type === 'manual' ? 0 : 1)."',
                    is_active = 1,
                    sort_order = '".(int) ($badge[6] ?? 0)."',
                    created_at = '{$now}',
                    updated_at = '{$now}'
            ", false);
        }
    }
}

if (!function_exists('eottae_member_growth_seed_defaults')) {
    function eottae_member_growth_seed_defaults()
    {
        $levels_table = eottae_member_growth_levels_table();
        $row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$levels_table}` ", false);
        if ((int) ($row['cnt'] ?? 0) > 0) {
            return;
        }

        $now = G5_TIME_YMDHIS;
        $levels = array(
            array('새싹회원', '가입 직후 기본 등급', 0, '🌱', 'default', 10),
            array('세부 입문자', '첫 글 또는 댓글 활동 시작', 100, '🏝', 'life', 20),
            array('세부 이웃', '꾸준한 커뮤니티 참여', 500, '🤝', 'life', 30),
            array('세부 생활러', '생활정보·톡방 활동이 활발', 1500, '⭐', 'life', 40),
            array('세부 고수', '유용한 정보 공유가 많음', 4000, '💡', 'food', 50),
            array('세부 로컬', '장기 활동 회원', 8000, '🏠', 'meetup', 60),
            array('세부어때 VIP', '우수 활동 또는 운영 선정', 10000, '👑', 'vip', 70),
        );

        foreach ($levels as $level) {
            sql_query("
                INSERT INTO `{$levels_table}`
                SET level_name = '".sql_escape_string($level[0])."',
                    level_description = '".sql_escape_string($level[1])."',
                    min_score = '".(int) $level[2]."',
                    icon = '".sql_escape_string($level[3])."',
                    color = '".sql_escape_string($level[4])."',
                    sort_order = '".(int) $level[5]."',
                    is_active = 1,
                    created_at = '{$now}',
                    updated_at = '{$now}'
            ", false);
        }

        $badges_table = eottae_member_growth_badges_table();
        $badges = array(
            array('첫 글 작성', '첫 커뮤니티 글을 작성했습니다', '✍️', 'activity', 'first_post', 1, 10),
            array('첫 댓글 작성', '첫 댓글을 남겼습니다', '💬', 'activity', 'first_comment', 1, 20),
            array('댓글 10개', '댓글 10개 달성', '💬', 'activity', 'comment_count', 10, 30),
            array('글 10개', '글 10개 작성', '📝', 'activity', 'post_count', 10, 40),
            array('맛집 탐험가', '맛집 관련 활동 우수', '🍽', 'community', 'manual', 0, 50),
            array('세부 생활고수', '생활정보 공유 우수', '💡', 'community', 'score_min', 1500, 60),
            array('챌린지 참여자', '챌린지에 참여했습니다', '🏆', 'community', 'challenge_once', 1, 70),
            array('교민 인증', '운영자 인증 교민 회원', '✅', 'official', 'manual', 0, 80),
            array('VIP', '우수 회원', '👑', 'official', 'score_min', 10000, 90),
        );

        foreach ($badges as $badge) {
            $condition_type = (string) ($badge[4] ?? '');
            sql_query("
                INSERT INTO `{$badges_table}`
                SET badge_name = '".sql_escape_string($badge[0])."',
                    badge_description = '".sql_escape_string($badge[1])."',
                    badge_icon = '".sql_escape_string($badge[2])."',
                    badge_color = 'default',
                    badge_type = '".sql_escape_string($badge[3])."',
                    condition_type = '".sql_escape_string($condition_type)."',
                    condition_value = '".(int) ($badge[5] ?? 0)."',
                    is_auto = '".($condition_type === 'manual' ? 0 : 1)."',
                    is_active = 1,
                    sort_order = '".(int) ($badge[6] ?? 0)."',
                    created_at = '{$now}',
                    updated_at = '{$now}'
            ", false);
        }
    }
}

if (!function_exists('eottae_member_growth_ensure_member_row')) {
    function eottae_member_growth_ensure_member_row($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return null;
        }

        $table = eottae_member_growth_scores_table();
        $row = sql_fetch(" SELECT * FROM `{$table}` WHERE mb_id = '".sql_escape_string($mb_id)."' LIMIT 1 ", false);
        if (!empty($row['id'])) {
            return $row;
        }

        $today = date('Y-m-d');
        sql_query("
            INSERT INTO `{$table}`
            SET mb_id = '".sql_escape_string($mb_id)."',
                total_score = 0,
                today_score = 0,
                last_score_date = '{$today}',
                current_level_id = 0,
                updated_at = '".G5_TIME_YMDHIS."'
        ", false);

        eottae_member_growth_recalc_level($mb_id);

        return sql_fetch(" SELECT * FROM `{$table}` WHERE mb_id = '".sql_escape_string($mb_id)."' LIMIT 1 ", false);
    }
}

if (!function_exists('eottae_member_growth_today_action_score')) {
    function eottae_member_growth_today_action_score($mb_id, $action_type)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $action_type = preg_replace('/[^a-z_]/', '', (string) $action_type);
        $today = date('Y-m-d');
        $logs = eottae_member_growth_logs_table();
        $row = sql_fetch("
            SELECT COALESCE(SUM(score), 0) AS s
            FROM `{$logs}`
            WHERE mb_id = '".sql_escape_string($mb_id)."'
              AND action_type = '".sql_escape_string($action_type)."'
              AND DATE(created_at) = '{$today}'
              AND score > 0
        ", false);

        return (int) ($row['s'] ?? 0);
    }
}

if (!function_exists('eottae_member_growth_has_once_action')) {
    function eottae_member_growth_has_once_action($mb_id, $action_type)
    {
        $logs = eottae_member_growth_logs_table();
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $action_type = preg_replace('/[^a-z_]/', '', (string) $action_type);
        $row = sql_fetch("
            SELECT log_id FROM `{$logs}`
            WHERE mb_id = '".sql_escape_string($mb_id)."'
              AND action_type = '".sql_escape_string($action_type)."'
            LIMIT 1
        ", false);

        return !empty($row['log_id']);
    }
}

if (!function_exists('eottae_member_growth_add_score')) {
    function eottae_member_growth_add_score($mb_id, $action_type, $score = 0, $target_type = '', $target_id = 0, $memo = '')
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $action_type = preg_replace('/[^a-z_]/', '', (string) $action_type);
        if ($mb_id === '' || $action_type === '') {
            return array('ok' => false, 'message' => 'invalid');
        }

        eottae_member_growth_ensure_member_row($mb_id);
        $rules = eottae_member_growth_score_rules();
        $rule = isset($rules[$action_type]) ? $rules[$action_type] : null;

        $is_admin_grant = ($action_type === 'admin_grant');
        if ($is_admin_grant) {
            $score = (int) $score;
            if ($score === 0) {
                return array('ok' => false, 'message' => 'zero');
            }
        } elseif ($rule) {
            if (!empty($rule['once']) && eottae_member_growth_has_once_action($mb_id, $action_type)) {
                return array('ok' => false, 'message' => 'already');
            }
            if ($score === 0) {
                $score = (int) ($rule['score'] ?? 0);
            }
            if ($score < 1) {
                return array('ok' => false, 'message' => 'zero');
            }
            $daily_max = (int) ($rule['daily_max'] ?? 0);
            if ($daily_max > 0) {
                $today_action = eottae_member_growth_today_action_score($mb_id, $action_type);
                if ($today_action >= $daily_max) {
                    return array('ok' => false, 'message' => 'daily_action_cap');
                }
                $score = min($score, $daily_max - $today_action);
            }
        } else {
            return array('ok' => false, 'message' => 'unknown_action');
        }

        if (!$is_admin_grant && $score < 1) {
            return array('ok' => false, 'message' => 'capped');
        }

        $scores_table = eottae_member_growth_scores_table();
        $row = sql_fetch(" SELECT * FROM `{$scores_table}` WHERE mb_id = '".sql_escape_string($mb_id)."' ", false);
        $today = date('Y-m-d');
        $today_score = (int) ($row['today_score'] ?? 0);
        if (($row['last_score_date'] ?? '') !== $today) {
            $today_score = 0;
        }

        if (!$is_admin_grant) {
            $daily_cap = eottae_member_growth_daily_total_cap();
            if ($daily_cap > 0 && $today_score >= $daily_cap) {
                return array('ok' => false, 'message' => 'daily_total_cap');
            }
            if ($daily_cap > 0) {
                $score = min($score, $daily_cap - $today_score);
            }
            if ($score < 1) {
                return array('ok' => false, 'message' => 'daily_total_cap');
            }
        }

        $total = (int) ($row['total_score'] ?? 0) + $score;
        if ($total < 0) {
            $total = 0;
        }
        if (!$is_admin_grant || $score > 0) {
            $today_score += $score;
            if ($today_score < 0) {
                $today_score = 0;
            }
        }

        sql_query("
            UPDATE `{$scores_table}`
            SET total_score = '{$total}',
                today_score = '{$today_score}',
                last_score_date = '{$today}',
                updated_at = '".G5_TIME_YMDHIS."'
            WHERE mb_id = '".sql_escape_string($mb_id)."'
        ", false);

        $logs = eottae_member_growth_logs_table();
        sql_query("
            INSERT INTO `{$logs}`
            SET mb_id = '".sql_escape_string($mb_id)."',
                action_type = '".sql_escape_string($action_type)."',
                score = '{$score}',
                target_type = '".sql_escape_string(preg_replace('/[^a-z_]/', '', (string) $target_type))."',
                target_id = '".(int) $target_id."',
                memo = '".sql_escape_string(substr(strip_tags((string) $memo), 0, 255))."',
                created_at = '".G5_TIME_YMDHIS."'
        ", false);

        eottae_member_growth_recalc_level($mb_id);
        eottae_member_growth_check_auto_badges($mb_id);

        if ($action_type === 'post_write' && !eottae_member_growth_has_once_action($mb_id, 'first_post')) {
            eottae_member_growth_add_score($mb_id, 'first_post', 0, $target_type, $target_id, '첫 글 보너스');
        }

        return array('ok' => true, 'score' => $score, 'total' => $total);
    }
}

if (!function_exists('eottae_member_growth_recalc_level')) {
    function eottae_member_growth_recalc_level($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return 0;
        }

        $scores_table = eottae_member_growth_scores_table();
        $levels_table = eottae_member_growth_levels_table();
        $row = sql_fetch(" SELECT total_score FROM `{$scores_table}` WHERE mb_id = '".sql_escape_string($mb_id)."' ", false);
        $total = (int) ($row['total_score'] ?? 0);

        $level = sql_fetch("
            SELECT level_id
            FROM `{$levels_table}`
            WHERE is_active = 1 AND min_score <= '{$total}'
            ORDER BY min_score DESC, sort_order DESC
            LIMIT 1
        ", false);

        $level_id = (int) ($level['level_id'] ?? 0);
        sql_query("
            UPDATE `{$scores_table}`
            SET current_level_id = '{$level_id}', updated_at = '".G5_TIME_YMDHIS."'
            WHERE mb_id = '".sql_escape_string($mb_id)."'
        ", false);

        return $level_id;
    }
}

if (!function_exists('eottae_member_growth_recalc_all_levels')) {
    function eottae_member_growth_recalc_all_levels($batch_size = 500)
    {
        $batch_size = max(1, min(5000, (int) $batch_size));
        $scores_table = eottae_member_growth_scores_table();
        $result = sql_query(" SELECT mb_id FROM `{$scores_table}` ORDER BY updated_at DESC LIMIT {$batch_size} ", false);
        $count = 0;

        while ($row = sql_fetch_array($result)) {
            if (empty($row['mb_id'])) {
                continue;
            }
            eottae_member_growth_recalc_level($row['mb_id']);
            eottae_member_growth_clear_cache($row['mb_id']);
            $count++;
        }

        return array('ok' => true, 'count' => $count);
    }
}

if (!function_exists('eottae_member_growth_get_level')) {
    function eottae_member_growth_get_level($level_id)
    {
        $level_id = (int) $level_id;
        if ($level_id < 1) {
            return null;
        }

        $table = eottae_member_growth_levels_table();
        $row = sql_fetch(" SELECT * FROM `{$table}` WHERE level_id = '{$level_id}' AND is_active = 1 LIMIT 1 ", false);

        return is_array($row) && !empty($row['level_id']) ? $row : null;
    }
}

if (!function_exists('eottae_member_growth_get_lowest_level')) {
    function eottae_member_growth_get_lowest_level()
    {
        static $cached = null;
        if (is_array($cached)) {
            return $cached;
        }

        $table = eottae_member_growth_levels_table();
        if (!eottae_member_growth_table_exists($table)) {
            return null;
        }

        $cached = sql_fetch("
            SELECT *
            FROM `{$table}`
            WHERE is_active = 1
            ORDER BY min_score ASC, sort_order ASC, level_id ASC
            LIMIT 1
        ", false);

        return is_array($cached) && !empty($cached['level_id']) ? $cached : null;
    }
}

if (!function_exists('eottae_member_growth_super_admin_level')) {
    function eottae_member_growth_super_admin_level()
    {
        return array(
            'level_id'          => 0,
            'level_name'        => '최고관리자',
            'level_description' => '사이트 최고관리자',
            'min_score'         => 0,
            'icon'              => '👑',
            'color'             => 'official',
            'sort_order'        => 999,
            'is_active'         => 1,
        );
    }
}

if (!function_exists('eottae_member_growth_is_super_member')) {
    function eottae_member_growth_is_super_member($member = null)
    {
        if ($member === null) {
            global $member;
        }
        if (!is_array($member) || empty($member['mb_id'])) {
            return false;
        }

        global $is_admin;
        if ($is_admin === 'super') {
            return true;
        }

        return (int) ($member['mb_level'] ?? 0) >= 10;
    }
}

if (!function_exists('eottae_member_growth_get_login_display_profile')) {
    /**
     * 로그인 박스 등 — 활동 등급 배지용 (업적 뱃지보다 등급 우선)
     *
     * @param array<string, mixed>|null $member
     * @return array<string, mixed>|null
     */
    function eottae_member_growth_get_login_display_profile($member = null)
    {
        if ($member === null) {
            global $member;
        }
        if (!is_array($member) || empty($member['mb_id'])) {
            return null;
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $member['mb_id']);
        if ($mb_id === '') {
            return null;
        }

        if (eottae_member_growth_is_super_member($member)) {
            $profile = eottae_member_growth_get_profile($mb_id);
            if (!is_array($profile)) {
                $profile = array(
                    'mb_id'       => $mb_id,
                    'total_score' => 0,
                    'main_badge'  => null,
                    'next_level'  => null,
                );
            }
            $profile['level'] = eottae_member_growth_super_admin_level();
            $profile['main_badge'] = null;

            return $profile;
        }

        $profile = eottae_member_growth_get_profile($mb_id);
        if (!is_array($profile)) {
            return null;
        }

        if (empty($profile['level']['level_name'])) {
            $profile['level'] = eottae_member_growth_get_lowest_level();
        }
        $profile['main_badge'] = null;

        return $profile;
    }
}

if (!function_exists('eottae_member_growth_member_stats')) {
    function eottae_member_growth_member_stats($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $stats = array('post_count' => 0, 'comment_count' => 0, 'challenge_count' => 0, 'days_active' => 0);

        if ($mb_id === '') {
            return $stats;
        }

        global $g5;
        $member_table = G5_TABLE_PREFIX.'member';
        $m = sql_fetch(" SELECT mb_datetime FROM `{$member_table}` WHERE mb_id = '".sql_escape_string($mb_id)."' ", false);
        if (!empty($m['mb_datetime']) && $m['mb_datetime'] !== '0000-00-00 00:00:00') {
            $stats['days_active'] = max(0, (int) floor((time() - strtotime($m['mb_datetime'])) / 86400));
        }

        if (defined('EOTTae_COMMUNITY_TABLE')) {
            $wt = $g5['write_prefix'].EOTTae_COMMUNITY_TABLE;
            $r = sql_fetch(" SELECT COUNT(*) AS c FROM `{$wt}` WHERE mb_id = '".sql_escape_string($mb_id)."' AND wr_is_comment = 0 ", false);
            $stats['post_count'] += (int) ($r['c'] ?? 0);
            $r = sql_fetch(" SELECT COUNT(*) AS c FROM `{$wt}` WHERE mb_id = '".sql_escape_string($mb_id)."' AND wr_is_comment = 1 ", false);
            $stats['comment_count'] += (int) ($r['c'] ?? 0);
        }

        if (function_exists('eottae_plaza_board_table')) {
            include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
            $wt = $g5['write_prefix'].eottae_plaza_board_table();
            if (eottae_member_growth_table_exists($wt)) {
                $r = sql_fetch(" SELECT COUNT(*) AS c FROM `{$wt}` WHERE mb_id = '".sql_escape_string($mb_id)."' AND wr_is_comment = 0 ", false);
                $stats['post_count'] += (int) ($r['c'] ?? 0);
                $r = sql_fetch(" SELECT COUNT(*) AS c FROM `{$wt}` WHERE mb_id = '".sql_escape_string($mb_id)."' AND wr_is_comment = 1 ", false);
                $stats['comment_count'] += (int) ($r['c'] ?? 0);
            }
        }

        if (function_exists('eottae_challenge_entries_table') && eottae_member_growth_table_exists(eottae_challenge_entries_table())) {
            include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
            $et = eottae_challenge_entries_table();
            $r = sql_fetch(" SELECT COUNT(*) AS c FROM `{$et}` WHERE mb_id = '".sql_escape_string($mb_id)."' AND status = 'active' ", false);
            $stats['challenge_count'] = (int) ($r['c'] ?? 0);
        }

        return $stats;
    }
}

if (!function_exists('eottae_member_growth_award_badge')) {
    function eottae_member_growth_award_badge($mb_id, $badge_id, $awarded_by = '', $reason = '', $set_main = false)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $badge_id = (int) $badge_id;
        if ($mb_id === '' || $badge_id < 1) {
            return array('ok' => false);
        }

        $badges = eottae_member_growth_badges_table();
        $badge = sql_fetch(" SELECT * FROM `{$badges}` WHERE badge_id = '{$badge_id}' AND is_active = 1 ", false);
        if (empty($badge['badge_id'])) {
            return array('ok' => false);
        }

        $mb_badges = eottae_member_growth_member_badges_table();
        $exists = sql_fetch("
            SELECT id FROM `{$mb_badges}`
            WHERE mb_id = '".sql_escape_string($mb_id)."' AND badge_id = '{$badge_id}'
        ", false);
        if (!empty($exists['id'])) {
            return array('ok' => true, 'duplicate' => true);
        }

        if ($set_main) {
            sql_query(" UPDATE `{$mb_badges}` SET is_main = 0 WHERE mb_id = '".sql_escape_string($mb_id)."' ", false);
        }

        sql_query("
            INSERT INTO `{$mb_badges}`
            SET mb_id = '".sql_escape_string($mb_id)."',
                badge_id = '{$badge_id}',
                is_main = '".($set_main ? 1 : 0)."',
                awarded_by = '".sql_escape_string(preg_replace('/[^a-z0-9_@.-]/i', '', (string) $awarded_by))."',
                awarded_reason = '".sql_escape_string(substr(strip_tags((string) $reason), 0, 255))."',
                created_at = '".G5_TIME_YMDHIS."'
        ", false);

        eottae_member_growth_clear_cache($mb_id);

        return array('ok' => true);
    }
}

if (!function_exists('eottae_member_growth_check_auto_badges')) {
    function eottae_member_growth_check_auto_badges($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return;
        }

        eottae_member_growth_ensure_member_row($mb_id);
        $scores_table = eottae_member_growth_scores_table();
        $score_row = sql_fetch(" SELECT total_score FROM `{$scores_table}` WHERE mb_id = '".sql_escape_string($mb_id)."' ", false);
        $total = (int) ($score_row['total_score'] ?? 0);
        $stats = eottae_member_growth_member_stats($mb_id);

        $badges_table = eottae_member_growth_badges_table();
        $result = sql_query(" SELECT * FROM `{$badges_table}` WHERE is_active = 1 AND is_auto = 1 ", false);

        while ($badge = sql_fetch_array($result)) {
            if (!is_array($badge)) {
                continue;
            }
            $type = (string) ($badge['condition_type'] ?? '');
            $val = (int) ($badge['condition_value'] ?? 0);
            $pass = false;

            switch ($type) {
                case 'score_min':
                    $pass = $total >= $val;
                    break;
                case 'post_count':
                    $pass = $stats['post_count'] >= $val;
                    break;
                case 'comment_count':
                    $pass = $stats['comment_count'] >= $val;
                    break;
                case 'first_post':
                    $pass = $stats['post_count'] >= 1;
                    break;
                case 'first_comment':
                    $pass = $stats['comment_count'] >= 1;
                    break;
                case 'challenge_once':
                    $pass = $stats['challenge_count'] >= 1;
                    break;
                case 'days_active':
                    $pass = $stats['days_active'] >= $val;
                    break;
            }

            if ($pass) {
                eottae_member_growth_award_badge($mb_id, (int) $badge['badge_id'], 'system', '자동 지급');
            }
        }
    }
}

if (!function_exists('eottae_member_growth_clear_cache')) {
    function eottae_member_growth_clear_cache($mb_id = '')
    {
        if (!isset($GLOBALS['eottae_member_growth_cache'])) {
            return;
        }
        if ($mb_id === '') {
            unset($GLOBALS['eottae_member_growth_cache']);
            return;
        }
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        unset($GLOBALS['eottae_member_growth_cache'][$mb_id]);
    }
}

if (!function_exists('eottae_member_growth_get_profile')) {
    function eottae_member_growth_get_profile($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return null;
        }

        if (!empty($GLOBALS['eottae_member_growth_cache'][$mb_id])) {
            return $GLOBALS['eottae_member_growth_cache'][$mb_id];
        }

        eottae_member_growth_ensure_member_row($mb_id);
        $scores_table = eottae_member_growth_scores_table();
        $row = sql_fetch(" SELECT * FROM `{$scores_table}` WHERE mb_id = '".sql_escape_string($mb_id)."' ", false);

        $level = eottae_member_growth_get_level((int) ($row['current_level_id'] ?? 0));
        if (!$level) {
            $level = eottae_member_growth_get_lowest_level();
        }

        $main_badge = eottae_member_growth_get_main_badge($mb_id);
        $next = eottae_member_growth_next_level((int) ($row['total_score'] ?? 0));

        $profile = array(
            'mb_id'        => $mb_id,
            'total_score'  => (int) ($row['total_score'] ?? 0),
            'level'        => $level,
            'main_badge'   => $main_badge,
            'next_level'   => $next,
        );

        $GLOBALS['eottae_member_growth_cache'][$mb_id] = $profile;

        return $profile;
    }
}

if (!function_exists('eottae_member_growth_next_level')) {
    function eottae_member_growth_next_level($total_score)
    {
        $levels_table = eottae_member_growth_levels_table();
        $total_score = (int) $total_score;

        return sql_fetch("
            SELECT level_id, level_name, min_score, icon, color
            FROM `{$levels_table}`
            WHERE is_active = 1 AND min_score > '{$total_score}'
            ORDER BY min_score ASC
            LIMIT 1
        ", false);
    }
}

if (!function_exists('eottae_member_growth_get_main_badge')) {
    function eottae_member_growth_get_main_badge($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return null;
        }

        $mb_badges = eottae_member_growth_member_badges_table();
        $badges = eottae_member_growth_badges_table();

        $hidden_sql = '';
        if (sql_fetch(" SHOW COLUMNS FROM `{$mb_badges}` LIKE 'is_hidden' ", false)) {
            $hidden_sql = ' AND mb.is_hidden = 0 ';
        }

        $row = sql_fetch("
            SELECT b.*, mb.is_main
            FROM `{$mb_badges}` mb
            INNER JOIN `{$badges}` b ON b.badge_id = mb.badge_id AND b.is_active = 1
            WHERE mb.mb_id = '".sql_escape_string($mb_id)."'
            {$hidden_sql}
            ORDER BY mb.is_main DESC, mb.created_at DESC
            LIMIT 1
        ", false);

        return is_array($row) && !empty($row['badge_id']) ? $row : null;
    }
}

if (!function_exists('eottae_member_growth_list_member_badges')) {
    function eottae_member_growth_list_member_badges($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $items = array();
        if ($mb_id === '') {
            return $items;
        }

        $mb_badges = eottae_member_growth_member_badges_table();
        $badges = eottae_member_growth_badges_table();
        $result = sql_query("
            SELECT b.*, mb.is_main, mb.is_hidden, mb.awarded_reason, mb.created_at AS earned_at
            FROM `{$mb_badges}` mb
            INNER JOIN `{$badges}` b ON b.badge_id = mb.badge_id
            WHERE mb.mb_id = '".sql_escape_string($mb_id)."'
            ORDER BY mb.is_main DESC, mb.created_at DESC
        ", false);

        while ($row = sql_fetch_array($result)) {
            if (is_array($row)) {
                $items[] = $row;
            }
        }

        return $items;
    }
}

if (!function_exists('eottae_member_growth_set_main_badge')) {
    function eottae_member_growth_set_main_badge($mb_id, $badge_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $badge_id = (int) $badge_id;

        $mb_badges = eottae_member_growth_member_badges_table();
        $owned = sql_fetch("
            SELECT id FROM `{$mb_badges}`
            WHERE mb_id = '".sql_escape_string($mb_id)."' AND badge_id = '{$badge_id}'
        ", false);
        if (empty($owned['id'])) {
            return array('ok' => false, 'message' => '보유하지 않은 뱃지입니다.');
        }

        sql_query(" UPDATE `{$mb_badges}` SET is_main = 0 WHERE mb_id = '".sql_escape_string($mb_id)."' ", false);
        sql_query(" UPDATE `{$mb_badges}` SET is_main = 1 WHERE mb_id = '".sql_escape_string($mb_id)."' AND badge_id = '{$badge_id}' ", false);
        eottae_member_growth_clear_cache($mb_id);

        return array('ok' => true);
    }
}

if (!function_exists('eottae_member_growth_recent_logs')) {
    function eottae_member_growth_recent_logs($mb_id, $limit = 10)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $limit = max(1, min(50, (int) $limit));
        $logs = eottae_member_growth_logs_table();
        $items = array();
        $result = sql_query("
            SELECT * FROM `{$logs}`
            WHERE mb_id = '".sql_escape_string($mb_id)."'
            ORDER BY created_at DESC
            LIMIT {$limit}
        ", false);

        while ($row = sql_fetch_array($result)) {
            if (is_array($row)) {
                $items[] = $row;
            }
        }

        return $items;
    }
}

if (!function_exists('eottae_member_growth_prefetch_members')) {
    function eottae_member_growth_prefetch_members(array $mb_ids)
    {
        foreach ($mb_ids as $mb_id) {
            $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
            if ($mb_id !== '') {
                eottae_member_growth_get_profile($mb_id);
            }
        }
    }
}

if (!function_exists('eottae_member_growth_mypage_url')) {
    function eottae_member_growth_mypage_url()
    {
        return G5_URL.'/mypage/badges.php';
    }
}

if (!function_exists('eottae_member_growth_member_token')) {
    function eottae_member_growth_member_token($regenerate = false)
    {
        $token = get_session('eottae_member_growth_token');
        if ($regenerate || $token === '') {
            $token = bin2hex(random_bytes(16));
            set_session('eottae_member_growth_token', $token);
        }

        return (string) $token;
    }
}

if (!function_exists('eottae_member_growth_verify_member_token')) {
    function eottae_member_growth_verify_member_token($token)
    {
        $token = trim((string) $token);
        $session = get_session('eottae_member_growth_token');

        return $token !== '' && $session !== '' && hash_equals((string) $session, $token);
    }
}

if (!function_exists('eottae_member_growth_proc_url')) {
    function eottae_member_growth_proc_url()
    {
        return G5_URL.'/proc/eottae-member-growth.php';
    }
}

if (!function_exists('eottae_member_growth_admin_url')) {
    function eottae_member_growth_admin_url()
    {
        return G5_URL.'/page/eottae-admin-member-growth.php';
    }
}

if (!function_exists('eottae_member_growth_on_register')) {
    function eottae_member_growth_on_register($mb_id)
    {
        eottae_member_growth_add_score($mb_id, 'register', 0, 'member', 0, '회원가입');
        eottae_member_growth_check_auto_badges($mb_id);
    }
}

if (!function_exists('eottae_member_growth_on_write')) {
    function eottae_member_growth_on_write($board, $wr_id, $w)
    {
        global $member;

        if ($w !== '' || empty($member['mb_id']) || empty($board['bo_table'])) {
            return;
        }

        $mb_id = $member['mb_id'];
        $bo_table = $board['bo_table'];
        $action = 'post_write';

        if (function_exists('eottae_is_community_board') && eottae_is_community_board($bo_table)) {
            $action = 'post_write';
        } elseif (function_exists('eottae_plaza_board_table') && $bo_table === eottae_plaza_board_table()) {
            $action = 'life_info_post';
        } elseif (function_exists('eottae_talkroom_is_talkroom_board') && eottae_talkroom_is_talkroom_board($bo_table)) {
            $action = 'talkroom_post';
        } elseif (defined('EOTTae_JOB_TABLE') && $bo_table === EOTTae_JOB_TABLE) {
            $action = 'used_goods_post';
        } else {
            return;
        }

        eottae_member_growth_add_score($mb_id, $action, 0, 'write', (int) $wr_id);
    }
}

if (!function_exists('eottae_member_growth_on_comment')) {
    function eottae_member_growth_on_comment($board, $wr_id, $w, $comment_id, $reply_array)
    {
        global $member;

        if ($w !== 'c' || empty($member['mb_id'])) {
            return;
        }

        eottae_member_growth_add_score($member['mb_id'], 'comment_write', 0, 'comment', (int) $comment_id);
    }
}

if (is_file(__DIR__.'/eottae-member-growth-hooks.lib.php')) {
    include_once __DIR__.'/eottae-member-growth-hooks.lib.php';
}
if (is_file(__DIR__.'/eottae-member-growth-social.lib.php')) {
    include_once __DIR__.'/eottae-member-growth-social.lib.php';
}
