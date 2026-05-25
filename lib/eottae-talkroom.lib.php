<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_bootstrap_tables')) {
    function eottae_talkroom_bootstrap_tables()
    {
        global $g5;

        if (!isset($g5['sebu_talk_rooms_table'])) {
            $g5['sebu_talk_rooms_table'] = G5_TABLE_PREFIX.'sebu_talk_rooms';
        }
        if (!isset($g5['sebu_talk_members_table'])) {
            $g5['sebu_talk_members_table'] = G5_TABLE_PREFIX.'sebu_talk_members';
        }
        if (!isset($g5['sebu_talk_reports_table'])) {
            $g5['sebu_talk_reports_table'] = G5_TABLE_PREFIX.'sebu_talk_reports';
        }
        if (!isset($g5['sebu_talk_logs_table'])) {
            $g5['sebu_talk_logs_table'] = G5_TABLE_PREFIX.'sebu_talk_logs';
        }
        if (!isset($g5['sebu_talk_room_reads_table'])) {
            $g5['sebu_talk_room_reads_table'] = G5_TABLE_PREFIX.'sebu_talk_room_reads';
        }
        if (!isset($g5['sebu_talk_notifications_table'])) {
            $g5['sebu_talk_notifications_table'] = G5_TABLE_PREFIX.'sebu_talk_notifications';
        }
        if (!isset($g5['sebu_talk_bookmarks_table'])) {
            $g5['sebu_talk_bookmarks_table'] = G5_TABLE_PREFIX.'sebu_talk_bookmarks';
        }
    }
}

if (!function_exists('eottae_talkroom_table_names')) {
    function eottae_talkroom_table_names()
    {
        eottae_talkroom_bootstrap_tables();
        global $g5;

        return array(
            'rooms'   => $g5['sebu_talk_rooms_table'],
            'members' => $g5['sebu_talk_members_table'],
            'reports' => $g5['sebu_talk_reports_table'],
            'logs'    => $g5['sebu_talk_logs_table'],
            'reads'   => $g5['sebu_talk_room_reads_table'],
            'notifications' => $g5['sebu_talk_notifications_table'],
            'bookmarks' => $g5['sebu_talk_bookmarks_table'],
        );
    }
}

if (!function_exists('eottae_talkroom_table_exists')) {
    function eottae_talkroom_table_exists($table)
    {
        $table = preg_replace('/[^a-z0-9_]/i', '', (string) $table);
        if ($table === '') {
            return false;
        }

        $row = sql_fetch(" SHOW TABLES LIKE '{$table}' ", false);

        return !empty($row);
    }
}

if (!function_exists('eottae_talkroom_ensure_schema')) {
    /**
     * 세부톡방 테이블 생성 (CREATE TABLE IF NOT EXISTS — 기존 테이블·데이터 보존)
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_ensure_schema()
    {
        $tables = eottae_talkroom_table_names();
        $results = array();

        $ddl = array(
            'rooms' => " CREATE TABLE IF NOT EXISTS `{$tables['rooms']}` (
                `room_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `room_name` varchar(120) NOT NULL DEFAULT '',
                `room_description` varchar(500) NOT NULL DEFAULT '',
                `room_detail` text NOT NULL,
                `category` varchar(30) NOT NULL DEFAULT '',
                `emoji` varchar(16) NOT NULL DEFAULT '',
                `owner_mb_id` varchar(20) NOT NULL DEFAULT '',
                `status` varchar(20) NOT NULL DEFAULT 'pending',
                `visibility` varchar(20) NOT NULL DEFAULT 'public',
                `join_type` varchar(20) NOT NULL DEFAULT 'open',
                `rules` text NOT NULL,
                `room_notice` text NOT NULL,
                `contact` varchar(255) NOT NULL DEFAULT '',
                `apply_reason` text NOT NULL,
                `reject_reason` text NOT NULL,
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `approved_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `approved_by` varchar(20) NOT NULL DEFAULT '',
                `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`room_id`),
                KEY `idx_owner_mb_id` (`owner_mb_id`),
                KEY `idx_status` (`status`),
                KEY `idx_visibility` (`visibility`),
                KEY `idx_category` (`category`),
                KEY `idx_created_at` (`created_at`),
                KEY `idx_approved_at` (`approved_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ",

            'members' => " CREATE TABLE IF NOT EXISTS `{$tables['members']}` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `room_id` int(11) unsigned NOT NULL DEFAULT '0',
                `mb_id` varchar(20) NOT NULL DEFAULT '',
                `role` varchar(20) NOT NULL DEFAULT 'member',
                `status` varchar(20) NOT NULL DEFAULT 'active',
                `joined_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `requested_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `approved_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `approved_by` varchar(20) NOT NULL DEFAULT '',
                `kicked_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `kicked_by` varchar(20) NOT NULL DEFAULT '',
                `kicked_reason` text NOT NULL,
                `can_rejoin` tinyint(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_room_member` (`room_id`, `mb_id`),
                KEY `idx_room_id` (`room_id`),
                KEY `idx_mb_id` (`mb_id`),
                KEY `idx_role` (`role`),
                KEY `idx_status` (`status`),
                KEY `idx_kicked_at` (`kicked_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ",

            'reports' => " CREATE TABLE IF NOT EXISTS `{$tables['reports']}` (
                `report_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `room_id` int(11) unsigned NOT NULL DEFAULT '0',
                `target_type` varchar(20) NOT NULL DEFAULT '',
                `target_id` int(11) unsigned NOT NULL DEFAULT '0',
                `reporter_mb_id` varchar(20) NOT NULL DEFAULT '',
                `reason` text NOT NULL,
                `memo` text NOT NULL,
                `status` varchar(20) NOT NULL DEFAULT 'pending',
                `handled_by` varchar(20) NOT NULL DEFAULT '',
                `handled_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`report_id`),
                KEY `idx_room_id` (`room_id`),
                KEY `idx_status` (`status`),
                KEY `idx_reporter_mb_id` (`reporter_mb_id`),
                KEY `idx_target` (`target_type`, `target_id`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ",

            'logs' => " CREATE TABLE IF NOT EXISTS `{$tables['logs']}` (
                `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `room_id` int(11) unsigned NOT NULL DEFAULT '0',
                `mb_id` varchar(20) NOT NULL DEFAULT '',
                `action` varchar(50) NOT NULL DEFAULT '',
                `target_type` varchar(20) NOT NULL DEFAULT '',
                `target_id` int(11) unsigned NOT NULL DEFAULT '0',
                `memo` text NOT NULL,
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`log_id`),
                KEY `idx_room_id` (`room_id`),
                KEY `idx_mb_id` (`mb_id`),
                KEY `idx_action` (`action`),
                KEY `idx_target` (`target_type`, `target_id`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ",

            'reads' => " CREATE TABLE IF NOT EXISTS `{$tables['reads']}` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `room_id` int(11) unsigned NOT NULL DEFAULT '0',
                `mb_id` varchar(20) NOT NULL DEFAULT '',
                `last_read_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `last_read_post_id` int(11) unsigned NOT NULL DEFAULT '0',
                `last_read_comment_id` int(11) unsigned NOT NULL DEFAULT '0',
                `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_room_member_read` (`room_id`, `mb_id`),
                KEY `idx_mb_id` (`mb_id`),
                KEY `idx_last_read_at` (`last_read_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ",

            'notifications' => " CREATE TABLE IF NOT EXISTS `{$tables['notifications']}` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `mb_id` varchar(20) NOT NULL DEFAULT '',
                `room_id` int(11) unsigned NOT NULL DEFAULT '0',
                `type` varchar(40) NOT NULL DEFAULT '',
                `target_type` varchar(20) NOT NULL DEFAULT '',
                `target_id` int(11) unsigned NOT NULL DEFAULT '0',
                `title` varchar(200) NOT NULL DEFAULT '',
                `message` varchar(500) NOT NULL DEFAULT '',
                `href` varchar(500) NOT NULL DEFAULT '',
                `is_read` tinyint(1) NOT NULL DEFAULT '0',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `read_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id`),
                KEY `idx_mb_id` (`mb_id`),
                KEY `idx_mb_read` (`mb_id`, `is_read`),
                KEY `idx_room_id` (`room_id`),
                KEY `idx_type` (`type`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ",

            'bookmarks' => " CREATE TABLE IF NOT EXISTS `{$tables['bookmarks']}` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `mb_id` varchar(20) NOT NULL DEFAULT '',
                `room_id` int(11) unsigned NOT NULL DEFAULT '0',
                `post_id` int(11) unsigned NOT NULL DEFAULT '0',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_bookmark_member_post` (`mb_id`, `room_id`, `post_id`),
                KEY `idx_mb_id` (`mb_id`),
                KEY `idx_room_id` (`room_id`),
                KEY `idx_post_id` (`post_id`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ",
        );

        foreach ($ddl as $key => $sql) {
            $table = $tables[$key];
            $existed = eottae_talkroom_table_exists($table);
            $ok = (bool) sql_query($sql, false);
            $results[] = array(
                'table'   => $table,
                'key'     => $key,
                'existed' => $existed,
                'ok'      => $ok,
                'action'  => $existed ? 'exists' : 'created',
            );
        }

        eottae_talkroom_upgrade_schema();

        return $results;
    }
}

if (!function_exists('eottae_talkroom_drop_schema')) {
    /**
     * 세부톡방 테이블 삭제 (롤백용 — 운영 DB에서 신중히 사용)
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_drop_schema()
    {
        $tables = eottae_talkroom_table_names();
        $order = array('reports', 'logs', 'notifications', 'reads', 'members', 'rooms');
        $results = array();

        foreach ($order as $key) {
            $table = $tables[$key];
            if (!eottae_talkroom_table_exists($table)) {
                $results[] = array(
                    'table'  => $table,
                    'key'    => $key,
                    'ok'     => true,
                    'action' => 'missing',
                );
                continue;
            }

            $ok = (bool) sql_query(" DROP TABLE IF EXISTS `{$table}` ", false);
            $results[] = array(
                'table'  => $table,
                'key'    => $key,
                'ok'     => $ok,
                'action' => 'dropped',
            );
        }

        return $results;
    }
}

if (!function_exists('eottae_talkroom_schema_status')) {
    function eottae_talkroom_schema_status()
    {
        $tables = eottae_talkroom_table_names();
        $status = array();

        foreach ($tables as $key => $table) {
            $status[$key] = array(
                'table'  => $table,
                'exists' => eottae_talkroom_table_exists($table),
            );
        }

        return $status;
    }
}

if (!function_exists('eottae_talkroom_public_statuses')) {
    function eottae_talkroom_public_statuses()
    {
        return array('approved', 'active');
    }
}

if (!function_exists('eottae_talkroom_list_url')) {
    function eottae_talkroom_list_url()
    {
        return G5_URL.'/talk';
    }
}

if (!function_exists('eottae_talkroom_create_url')) {
    function eottae_talkroom_create_url()
    {
        return G5_URL.'/page/eottae-talk-create.php';
    }
}

if (!function_exists('eottae_talkroom_ai_landing_url')) {
    function eottae_talkroom_ai_landing_url()
    {
        return G5_URL.'/talk/ai.php';
    }
}

if (!function_exists('eottae_talkroom_my_url')) {
    function eottae_talkroom_my_url()
    {
        return G5_URL.'/page/eottae-talk-my.php';
    }
}

if (!function_exists('eottae_talkroom_apply_status_url')) {
    function eottae_talkroom_apply_status_url()
    {
        return G5_URL.'/page/eottae-talk-applies.php';
    }
}

if (!function_exists('eottae_talkroom_enter_url')) {
    function eottae_talkroom_enter_url($room_id)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1) {
            return eottae_talkroom_list_url();
        }

        return G5_URL.'/page/eottae-talk-room.php?room_id='.$room_id;
    }
}

if (!function_exists('eottae_talkroom_invite_url')) {
    function eottae_talkroom_invite_url($room_id)
    {
        return eottae_talkroom_enter_url($room_id);
    }
}

if (!function_exists('eottae_talkroom_can_share_invite')) {
    function eottae_talkroom_can_share_invite($ctx)
    {
        if (!is_array($ctx)) {
            return false;
        }

        if (!empty($ctx['can_manage'])) {
            return true;
        }

        $membership = isset($ctx['membership']) ? (string) $ctx['membership'] : 'guest';

        return in_array($membership, array('owner', 'active'), true);
    }
}

if (!function_exists('eottae_talkroom_category_label')) {
    function eottae_talkroom_category_label($code)
    {
        $map = eottae_talkroom_category_map();
        $code = trim((string) $code);

        return isset($map[$code]) ? $map[$code] : ($code !== '' ? $code : '기타');
    }
}

if (!function_exists('eottae_talkroom_category_map')) {
    function eottae_talkroom_category_map()
    {
        return array(
            'expat_life' => '교민생활',
            'parenting'  => '육아/교육',
            'sports'     => '스포츠',
            'travel'     => '여행',
            'business'   => '사업/창업',
            'used'       => '중고거래',
            'job'        => '구인구직',
            'estate'     => '부동산',
            'food'       => '맛집/카페',
            'hobby'      => '취미/모임',
            'etc'        => '기타',
            'life'       => '교민생활',
            'study'      => '육아/교육',
            'kids'       => '육아/교육',
        );
    }
}

if (!function_exists('eottae_talkroom_category_options')) {
    function eottae_talkroom_category_options()
    {
        return array(
            'expat_life' => '교민생활',
            'parenting'  => '육아/교육',
            'sports'     => '스포츠',
            'travel'     => '여행',
            'business'   => '사업/창업',
            'used'       => '중고거래',
            'job'        => '구인구직',
            'estate'     => '부동산',
            'food'       => '맛집/카페',
            'hobby'      => '취미/모임',
            'etc'        => '기타',
        );
    }
}

if (!function_exists('eottae_talkroom_status_label')) {
    function eottae_talkroom_status_label($status)
    {
        $status = trim((string) $status);
        $map = array(
            'pending'  => '승인대기',
            'approved' => '승인완료',
            'active'   => '승인완료',
            'rejected' => '반려',
            'closed'   => '운영중지',
            'stopped'  => '운영중지',
        );

        return isset($map[$status]) ? $map[$status] : $status;
    }
}

if (!function_exists('eottae_talkroom_status_class')) {
    function eottae_talkroom_status_class($status)
    {
        $status = trim((string) $status);
        $map = array(
            'pending'  => 'talk-apply-status--pending',
            'approved' => 'talk-apply-status--approved',
            'active'   => 'talk-apply-status--approved',
            'rejected' => 'talk-apply-status--rejected',
            'closed'   => 'talk-apply-status--closed',
            'stopped'  => 'talk-apply-status--closed',
        );

        return isset($map[$status]) ? $map[$status] : 'talk-apply-status--pending';
    }
}

if (!function_exists('eottae_talkroom_join_type_label')) {
    function eottae_talkroom_join_type_label($join_type)
    {
        return trim((string) $join_type) === 'approval' ? '방장 승인 후 참여' : '누구나 참여 가능';
    }
}

if (!function_exists('eottae_talkroom_upgrade_schema')) {
    function eottae_talkroom_upgrade_schema()
    {
        static $done = false;
        if ($done) {
            return;
        }

        $tables = eottae_talkroom_table_names();
        $table = $tables['rooms'];
        if (!eottae_talkroom_table_exists($table)) {
            $done = true;

            return;
        }

        $col = sql_fetch(" SHOW COLUMNS FROM `{$table}` LIKE 'apply_reason' ", false);
        if (empty($col)) {
            sql_query(" ALTER TABLE `{$table}` ADD `apply_reason` text NOT NULL AFTER `contact` ", false);
        }

        $notice_col = sql_fetch(" SHOW COLUMNS FROM `{$table}` LIKE 'room_notice' ", false);
        if (empty($notice_col)) {
            sql_query(" ALTER TABLE `{$table}` ADD `room_notice` text NOT NULL AFTER `rules` ", false);
        }

        $emoji_col = sql_fetch(" SHOW COLUMNS FROM `{$table}` LIKE 'emoji' ", false);
        if (!empty($emoji_col) && stripos((string) ($emoji_col['Type'] ?? ''), 'varchar(16)') !== false) {
            sql_query(" ALTER TABLE `{$table}` MODIFY `emoji` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' ", false);
        }

        $reports_table = $tables['reports'];
        if (eottae_talkroom_table_exists($reports_table)) {
            $idx = sql_fetch(" SHOW INDEX FROM `{$reports_table}` WHERE Key_name = 'uk_reporter_target' ", false);
            if (empty($idx)) {
                sql_query(" ALTER TABLE `{$reports_table}` ADD UNIQUE KEY `uk_reporter_target` (`reporter_mb_id`, `target_type`, `target_id`) ", false);
            }
        }

        $done = true;
    }
}

if (!function_exists('eottae_talkroom_apply_token')) {
    function eottae_talkroom_apply_token($regenerate = false)
    {
        $token = get_session('eottae_talkroom_apply_token');
        if ($regenerate || $token === '') {
            try {
                $token = bin2hex(random_bytes(16));
            } catch (Exception $e) {
                $token = md5(uniqid((string) mt_rand(), true));
            }
            set_session('eottae_talkroom_apply_token', $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_talkroom_verify_apply_token')) {
    function eottae_talkroom_verify_apply_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_talkroom_apply_token');

        return $token !== '' && $session_token !== '' && hash_equals($session_token, $token);
    }
}

if (!function_exists('eottae_talkroom_clean_text')) {
    function eottae_talkroom_clean_text($value, $max_len = 0)
    {
        $value = trim(strip_tags((string) $value));
        $value = clean_xss_tags($value, 1, 1);
        if ($max_len > 0) {
            $value = function_exists('cut_str') ? cut_str($value, $max_len) : substr($value, 0, $max_len);
        }

        return $value;
    }
}

if (!function_exists('eottae_talkroom_sanitize_emoji')) {
    function eottae_talkroom_sanitize_emoji($value)
    {
        $value = trim(strip_tags((string) $value));
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);

        return function_exists('mb_substr') ? mb_substr($value, 0, 8, 'UTF-8') : substr($value, 0, 16);
    }
}

if (!function_exists('eottae_talkroom_emoji_is_corrupted')) {
    function eottae_talkroom_emoji_is_corrupted($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return true;
        }
        if (strpos($value, '�') !== false) {
            return true;
        }
        if (preg_match('/^\?+$/u', $value)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('eottae_talkroom_resolve_emoji')) {
    function eottae_talkroom_resolve_emoji($value, $category = '')
    {
        $value = eottae_talkroom_sanitize_emoji($value);
        if (!eottae_talkroom_emoji_is_corrupted($value)) {
            return $value;
        }

        if (function_exists('eottae_talkroom_apply_ai_default_emoji')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-apply-ai.lib.php';
        }

        return function_exists('eottae_talkroom_apply_ai_default_emoji')
            ? eottae_talkroom_apply_ai_default_emoji($category)
            : '💬';
    }
}

if (!function_exists('eottae_talkroom_display_emoji')) {
    function eottae_talkroom_display_emoji($value, $category = '')
    {
        return eottae_talkroom_resolve_emoji($value, $category);
    }
}

if (!function_exists('eottae_talkroom_emoji_picker_groups')) {
    function eottae_talkroom_emoji_picker_groups()
    {
        return array(
            '대화·모임' => array('💬', '📣', '🤝', '👋', '🙌', '✨', '🌟', '❤️', '🎉', '📌'),
            '스포츠·운동' => array('⚽', '🏀', '🏐', '🏸', '🎾', '🏌️', '🏊', '🚴', '🏃', '⛳', '🥊', '🎣'),
            '육아·가족' => array('👶', '👨‍👩‍👧', '🍼', '🎒', '🧸', '📚', '🏫', '🎨', '🛝', '🧁'),
            '여행·생활' => array('✈️', '🏝️', '🌴', '🏨', '🚕', '🗺️', '📸', '☀️', '🌊', '🧳'),
            '맛집·카페' => array('🍜', '🍣', '🍕', '🍔', '☕', '🧋', '🍰', '🍺', '🥗', '🍖'),
            '사업·일' => array('💼', '🏢', '📈', '💡', '🛠️', '🧾', '🤝', '🏪', '📦', '🎯'),
            '중고·거래' => array('🛍️', '♻️', '📦', '💰', '🏷️', '📱', '🛒', '🎁', '🔖', '🧺'),
            '부동산·구인' => array('🏠', '🏢', '🔑', '🏗️', '📋', '🧑‍💼', '💼', '📝', '🏘️', '🏡'),
        );
    }
}

if (!function_exists('eottae_talkroom_render_emoji_picker')) {
    function eottae_talkroom_render_emoji_picker($selected = '💬', $input_id = 'talk_emoji')
    {
        $selected = eottae_talkroom_display_emoji($selected);
        $input_id = preg_replace('/[^a-z0-9_-]/i', '', (string) $input_id);
        if ($input_id === '') {
            $input_id = 'talk_emoji';
        }

        $groups = eottae_talkroom_emoji_picker_groups();
        ob_start();
        ?>
        <div class="talk-emoji-picker" data-talk-emoji-picker>
            <div class="talk-emoji-picker__current">
                <span class="talk-emoji-picker__preview" data-talk-emoji-preview aria-hidden="true"><?php echo $selected; ?></span>
                <input type="text" id="<?php echo $input_id; ?>" name="emoji" class="talk-apply-form__input talk-apply-form__input--emoji" maxlength="8" placeholder="💬" value="<?php echo htmlspecialchars($selected, ENT_QUOTES, 'UTF-8'); ?>" data-talk-emoji-input autocomplete="off">
            </div>
            <p class="talk-apply-form__hint">목록 카드에 표시됩니다. 아래에서 고르거나 직접 입력할 수 있습니다.</p>
            <?php foreach ($groups as $group_label => $emojis) { ?>
            <div class="talk-emoji-picker__group">
                <p class="talk-emoji-picker__group-label"><?php echo get_text($group_label); ?></p>
                <div class="talk-emoji-picker__grid">
                    <?php foreach ($emojis as $emoji) { ?>
                    <button type="button" class="talk-emoji-picker__btn<?php echo $selected === $emoji ? ' is-selected' : ''; ?>" data-talk-emoji-option="<?php echo htmlspecialchars($emoji, ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo get_text($group_label.' '.$emoji); ?>"><?php echo $emoji; ?></button>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php

        return ob_get_clean();
    }
}

if (!function_exists('eottae_talkroom_parse_apply_input')) {
    /**
     * @param array<string, mixed> $post
     * @return array<string, string>
     */
    function eottae_talkroom_parse_apply_input(array $post)
    {
        $visibility = isset($post['visibility']) ? trim((string) $post['visibility']) : 'public';
        if (!in_array($visibility, array('public', 'private'), true)) {
            $visibility = 'public';
        }

        $join_type = isset($post['join_type']) ? trim((string) $post['join_type']) : 'open';
        if (!in_array($join_type, array('open', 'approval'), true)) {
            $join_type = 'open';
        }

        $category = isset($post['category']) ? preg_replace('/[^a-z0-9_]/', '', (string) $post['category']) : '';
        $categories = eottae_talkroom_category_options();
        if (!isset($categories[$category])) {
            $category = '';
        }

        return array(
            'room_name'        => eottae_talkroom_clean_text($post['room_name'] ?? '', 120),
            'room_description' => eottae_talkroom_clean_text($post['room_description'] ?? '', 500),
            'room_detail'      => eottae_talkroom_clean_text($post['room_detail'] ?? '', 5000),
            'category'         => $category,
            'emoji'            => eottae_talkroom_resolve_emoji($post['emoji'] ?? '', $category),
            'rules'            => eottae_talkroom_clean_text($post['rules'] ?? '', 5000),
            'contact'          => eottae_talkroom_clean_text($post['contact'] ?? '', 255),
            'apply_reason'     => eottae_talkroom_clean_text($post['apply_reason'] ?? '', 2000),
            'visibility'       => $visibility,
            'join_type'        => $join_type,
        );
    }
}

if (!function_exists('eottae_talkroom_validate_apply')) {
    /**
     * @param array<string, string> $data
     * @return array<int, string>
     */
    function eottae_talkroom_validate_apply(array $data)
    {
        $errors = array();

        $name_len = function_exists('mb_strlen') ? mb_strlen($data['room_name'], 'UTF-8') : strlen($data['room_name']);
        if ($name_len < 2) {
            $errors[] = '톡방 이름을 2자 이상 입력해 주세요.';
        }
        if ($name_len > 40) {
            $errors[] = '톡방 이름은 40자 이내로 입력해 주세요.';
        }

        $desc_len = function_exists('mb_strlen') ? mb_strlen($data['room_description'], 'UTF-8') : strlen($data['room_description']);
        if ($desc_len < 5) {
            $errors[] = '톡방 한 줄 소개를 5자 이상 입력해 주세요.';
        }

        $detail_len = function_exists('mb_strlen') ? mb_strlen($data['room_detail'], 'UTF-8') : strlen($data['room_detail']);
        if ($detail_len < 10) {
            $errors[] = '톡방 상세 설명을 10자 이상 입력해 주세요.';
        }

        if ($data['category'] === '') {
            $errors[] = '카테고리를 선택해 주세요.';
        }

        $rules_len = function_exists('mb_strlen') ? mb_strlen($data['rules'], 'UTF-8') : strlen($data['rules']);
        if ($rules_len < 5) {
            $errors[] = '운영 규칙을 5자 이상 입력해 주세요.';
        }

        if ($data['contact'] === '') {
            $errors[] = '방장 연락처 또는 카카오톡 ID를 입력해 주세요.';
        }

        $reason_len = function_exists('mb_strlen') ? mb_strlen($data['apply_reason'], 'UTF-8') : strlen($data['apply_reason']);
        if ($reason_len < 10) {
            $errors[] = '신청 사유를 10자 이상 입력해 주세요.';
        }

        return $errors;
    }
}

if (!function_exists('eottae_talkroom_insert_apply')) {
    /**
     * @param string $mb_id
     * @param array<string, string> $data
     * @return int|false
     */
    function eottae_talkroom_insert_apply($mb_id, array $data)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return false;
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            eottae_talkroom_ensure_schema();
        }
        eottae_talkroom_upgrade_schema();

        $now = G5_TIME_YMDHIS;
        $sql = "
            INSERT INTO `{$tables['rooms']}` SET
                room_name = '".sql_escape_string($data['room_name'])."',
                room_description = '".sql_escape_string($data['room_description'])."',
                room_detail = '".sql_escape_string($data['room_detail'])."',
                category = '".sql_escape_string($data['category'])."',
                emoji = '".sql_escape_string($data['emoji'])."',
                owner_mb_id = '".sql_escape_string($mb_id)."',
                status = 'pending',
                visibility = '".sql_escape_string($data['visibility'])."',
                join_type = '".sql_escape_string($data['join_type'])."',
                rules = '".sql_escape_string($data['rules'])."',
                contact = '".sql_escape_string($data['contact'])."',
                apply_reason = '".sql_escape_string($data['apply_reason'])."',
                reject_reason = '',
                created_at = '{$now}',
                approved_at = '0000-00-00 00:00:00',
                approved_by = '',
                updated_at = '{$now}'
        ";

        if (!sql_query($sql, false)) {
            return false;
        }

        return (int) sql_insert_id();
    }
}

if (!function_exists('eottae_talkroom_list_my_applications')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_list_my_applications($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array();
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            return array();
        }

        $result = sql_query("
            SELECT *
            FROM `{$tables['rooms']}`
            WHERE owner_mb_id = '".sql_escape_string($mb_id)."'
            ORDER BY created_at DESC, room_id DESC
        ", false);

        $rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $rows[] = array(
                    'room_id'          => (int) $row['room_id'],
                    'emoji'            => eottae_talkroom_display_emoji($row['emoji'] ?? '', $row['category'] ?? ''),
                    'room_name'        => get_text($row['room_name'] ?? ''),
                    'room_description' => get_text($row['room_description'] ?? ''),
                    'category'         => eottae_talkroom_category_label($row['category'] ?? ''),
                    'status'           => trim((string) ($row['status'] ?? '')),
                    'status_label'     => eottae_talkroom_status_label($row['status'] ?? ''),
                    'status_class'     => eottae_talkroom_status_class($row['status'] ?? ''),
                    'visibility_label' => eottae_talkroom_visibility_label($row['visibility'] ?? ''),
                    'join_type_label'  => eottae_talkroom_join_type_label($row['join_type'] ?? ''),
                    'reject_reason'    => get_text($row['reject_reason'] ?? ''),
                    'created_at'       => trim((string) ($row['created_at'] ?? '')),
                    'created_label'    => function_exists('eottae_community_relative_time')
                        ? eottae_community_relative_time($row['created_at'] ?? '')
                        : substr((string) ($row['created_at'] ?? ''), 0, 16),
                );
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_visibility_label')) {
    function eottae_talkroom_visibility_label($visibility)
    {
        return trim((string) $visibility) === 'private' ? '비공개' : '공개';
    }
}

if (!function_exists('eottae_talkroom_board_table')) {
    function eottae_talkroom_board_table()
    {
        return defined('EOTTae_TALKROOM_TABLE') ? EOTTae_TALKROOM_TABLE : 'talkroom';
    }
}

if (!function_exists('eottae_talkroom_board_exists')) {
    function eottae_talkroom_board_exists()
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $bo = preg_replace('/[^a-z0-9_]/', '', eottae_talkroom_board_table());
        if ($bo === '') {
            $cached = false;

            return false;
        }

        $row = sql_fetch(" SELECT bo_table FROM ".G5_TABLE_PREFIX."board WHERE bo_table = '{$bo}' ", false);
        $cached = !empty($row['bo_table']);

        return $cached;
    }
}

if (!function_exists('eottae_talkroom_member_counts')) {
    /**
     * @param int[] $room_ids
     * @return array<int, int>
     */
    function eottae_talkroom_member_counts(array $room_ids)
    {
        $room_ids = array_values(array_filter(array_map('intval', $room_ids)));
        if (empty($room_ids)) {
            return array();
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['members'])) {
            return array();
        }

        $in = implode(',', $room_ids);
        $result = sql_query("
            SELECT room_id, COUNT(*) AS cnt
            FROM `{$tables['members']}`
            WHERE room_id IN ({$in})
              AND status = 'active'
            GROUP BY room_id
        ", false);

        $counts = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $counts[(int) $row['room_id']] = (int) $row['cnt'];
            }
        }

        return $counts;
    }
}

if (!function_exists('eottae_talkroom_post_counts')) {
    /**
     * @param int[] $room_ids
     * @return array<int, int>
     */
    function eottae_talkroom_post_counts(array $room_ids)
    {
        $room_ids = array_values(array_filter(array_map('intval', $room_ids)));
        if (empty($room_ids) || !eottae_talkroom_board_exists()) {
            return array();
        }

        $bo = preg_replace('/[^a-z0-9_]/', '', eottae_talkroom_board_table());
        $write_table = G5_TABLE_PREFIX.'write_'.$bo;
        if (!eottae_talkroom_table_exists($write_table)) {
            return array();
        }

        $in = implode(',', array_map('intval', $room_ids));
        $visible = eottae_talkroom_post_visible_sql();
        $result = sql_query("
            SELECT wr_1 AS room_id, COUNT(*) AS cnt
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_1 IN ({$in})
              AND {$visible}
            GROUP BY wr_1
        ", false);

        $counts = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $counts[(int) $row['room_id']] = (int) $row['cnt'];
            }
        }

        return $counts;
    }
}

if (!function_exists('eottae_talkroom_latest_post_times')) {
    /**
     * @param int[] $room_ids
     * @return array<int, string>
     */
    function eottae_talkroom_latest_post_times(array $room_ids)
    {
        $room_ids = array_values(array_filter(array_map('intval', $room_ids)));
        if (empty($room_ids) || !eottae_talkroom_board_exists()) {
            return array();
        }

        $bo = preg_replace('/[^a-z0-9_]/', '', eottae_talkroom_board_table());
        $write_table = G5_TABLE_PREFIX.'write_'.$bo;
        if (!eottae_talkroom_table_exists($write_table)) {
            return array();
        }

        $in = implode(',', $room_ids);
        $visible = eottae_talkroom_post_visible_sql();
        $result = sql_query("
            SELECT wr_1 AS room_id, MAX(wr_datetime) AS latest_at
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_1 IN ({$in})
              AND {$visible}
            GROUP BY wr_1
        ", false);

        $times = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $times[(int) $row['room_id']] = (string) $row['latest_at'];
            }
        }

        return $times;
    }
}

if (!function_exists('eottae_talkroom_resolve_updated_at')) {
    function eottae_talkroom_resolve_updated_at(array $row, $latest_post_at = '')
    {
        $candidates = array(
            isset($row['updated_at']) ? (string) $row['updated_at'] : '',
            (string) $latest_post_at,
            isset($row['approved_at']) ? (string) $row['approved_at'] : '',
            isset($row['created_at']) ? (string) $row['created_at'] : '',
        );

        foreach ($candidates as $datetime) {
            $datetime = trim($datetime);
            if ($datetime !== '' && $datetime !== '0000-00-00 00:00:00' && strtotime($datetime)) {
                return $datetime;
            }
        }

        return '';
    }
}

if (!function_exists('eottae_talkroom_format_card')) {
    function eottae_talkroom_format_card(array $row, array $stats = array())
    {
        $room_id = (int) ($row['room_id'] ?? 0);
        $latest_post_at = isset($stats['latest_post_at']) ? (string) $stats['latest_post_at'] : '';
        $updated_at = eottae_talkroom_resolve_updated_at($row, $latest_post_at);
        $emoji = eottae_talkroom_display_emoji($row['emoji'] ?? '', $row['category'] ?? '');

        $owner_nick = trim((string) ($row['owner_nick'] ?? ''));
        if ($owner_nick === '' && !empty($row['owner_mb_id'])) {
            $owner_nick = (string) $row['owner_mb_id'];
        }

        return array(
            'room_id'          => $room_id,
            'emoji'            => $emoji,
            'room_name'        => get_text((string) ($row['room_name'] ?? '')),
            'room_description' => get_text((string) ($row['room_description'] ?? '')),
            'category'         => eottae_talkroom_category_label($row['category'] ?? ''),
            'category_code'    => trim((string) ($row['category'] ?? '')),
            'owner_nick'       => get_text($owner_nick),
            'member_count'     => (int) ($stats['member_count'] ?? 0),
            'post_count'       => (int) ($stats['post_count'] ?? 0),
            'updated_at'       => $updated_at,
            'updated_label'    => function_exists('eottae_community_relative_time')
                ? eottae_community_relative_time($updated_at)
                : ($updated_at !== '' ? substr($updated_at, 0, 16) : ''),
            'visibility'       => trim((string) ($row['visibility'] ?? 'public')),
            'visibility_label' => eottae_talkroom_visibility_label($row['visibility'] ?? 'public'),
            'enter_href'       => eottae_talkroom_enter_url($room_id),
        );
    }
}

if (!function_exists('eottae_talkroom_list_public')) {
    /**
     * 승인·운영 중인 톡방 목록
     *
     * @param array<string, mixed> $options
     * @return array{total:int, rows:array<int, array<string, mixed>>}
     */
    function eottae_talkroom_list_public(array $options = array())
    {
        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            return array('total' => 0, 'rows' => array());
        }

        $limit = isset($options['limit']) ? max(1, min(100, (int) $options['limit'])) : 50;
        $page = isset($options['page']) ? max(1, (int) $options['page']) : 1;
        $offset = ($page - 1) * $limit;
        $order = isset($options['order']) ? trim((string) $options['order']) : 'updated';

        $statuses = eottae_talkroom_public_statuses();
        $status_sql = array();
        foreach ($statuses as $status) {
            $status_sql[] = "'".sql_real_escape_string($status)."'";
        }
        $status_in = implode(',', $status_sql);

        $count_row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$tables['rooms']}`
            WHERE status IN ({$status_in})
        ", false);
        $total = (int) ($count_row['cnt'] ?? 0);
        if ($total < 1) {
            return array('total' => 0, 'rows' => array());
        }

        $member_table = G5_TABLE_PREFIX.'member';
        if ($order === 'new') {
            $order_sql = "
                CASE WHEN r.approved_at IS NULL OR r.approved_at = '0000-00-00 00:00:00' THEN 1 ELSE 0 END,
                r.approved_at DESC,
                r.created_at DESC,
                r.room_id DESC
            ";
        } else {
            $order_sql = "
                CASE WHEN r.updated_at IS NULL OR r.updated_at = '0000-00-00 00:00:00' THEN 1 ELSE 0 END,
                r.updated_at DESC,
                r.approved_at DESC,
                r.room_id DESC
            ";
        }

        $result = sql_query("
            SELECT r.*, m.mb_nick AS owner_nick
            FROM `{$tables['rooms']}` r
            LEFT JOIN `{$member_table}` m ON m.mb_id = r.owner_mb_id
            WHERE r.status IN ({$status_in})
            ORDER BY {$order_sql}
            LIMIT {$offset}, {$limit}
        ", false);

        $raw_rows = array();
        $room_ids = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $room_id = (int) $row['room_id'];
                $raw_rows[] = $row;
                $room_ids[] = $room_id;
            }
        }

        $member_counts = eottae_talkroom_member_counts($room_ids);
        $post_counts = eottae_talkroom_post_counts($room_ids);
        $latest_posts = eottae_talkroom_latest_post_times($room_ids);

        $rows = array();
        foreach ($raw_rows as $row) {
            $room_id = (int) $row['room_id'];
            $rows[] = eottae_talkroom_format_card($row, array(
                'member_count'   => isset($member_counts[$room_id]) ? $member_counts[$room_id] : 0,
                'post_count'     => isset($post_counts[$room_id]) ? $post_counts[$room_id] : 0,
                'latest_post_at' => isset($latest_posts[$room_id]) ? $latest_posts[$room_id] : '',
            ));
        }

        return array(
            'total' => $total,
            'rows'  => $rows,
        );
    }
}

if (!function_exists('eottae_talkroom_list_public_cards')) {
    /**
     * 승인·운영 중인 공개 톡방 카드 목록 (홈·API용)
     *
     * @param array<string, mixed> $options
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_list_public_cards(array $options = array())
    {
        $result = eottae_talkroom_list_public($options);

        return isset($result['rows']) && is_array($result['rows']) ? $result['rows'] : array();
    }
}

if (!function_exists('eottae_talkroom_home_hero_sort_ts')) {
    function eottae_talkroom_home_hero_sort_ts($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return 0;
        }

        $ts = strtotime($value);

        return $ts !== false ? (int) $ts : 0;
    }
}

if (!function_exists('eottae_talkroom_home_hero_hot_score')) {
    function eottae_talkroom_home_hero_hot_score(array $room)
    {
        $post_count = max(0, (int) ($room['post_count'] ?? 0));
        $member_count = max(0, (int) ($room['member_count'] ?? 0));
        $updated_ts = eottae_talkroom_home_hero_sort_ts($room['updated_at'] ?? '');
        $score = ($post_count * 4) + ($member_count * 2);

        if ($updated_ts > 0) {
            $age_hours = max(0, (time() - $updated_ts) / 3600);
            if ($age_hours <= 24) {
                $score += 24;
            } elseif ($age_hours <= 72) {
                $score += 12;
            } elseif ($age_hours <= 168) {
                $score += 6;
            }
            $score += min(10, (int) floor($updated_ts / 86400) % 10);
        }

        return $score;
    }
}

if (!function_exists('eottae_talkroom_home_hero_payload')) {
    /**
     * 홈 히어로 — 신규·화제 톡방 데이터
     *
     * @return array<string, mixed>
     */
    function eottae_talkroom_home_hero_payload($new_limit = 3, $hot_limit = 3)
    {
        $new_limit = max(1, min(6, (int) $new_limit));
        $hot_limit = max(1, min(6, (int) $hot_limit));
        $pool_limit = max(24, $new_limit + $hot_limit + 12);

        $rows = eottae_talkroom_list_public_cards(array(
            'limit' => $pool_limit,
            'page'  => 1,
        ));
        $new_rows = eottae_talkroom_list_public_cards(array(
            'limit' => $new_limit,
            'page'  => 1,
            'order' => 'new',
        ));
        $new_ids = array();
        foreach ($new_rows as $row) {
            $new_ids[(int) ($row['room_id'] ?? 0)] = true;
        }

        if (empty($rows) && empty($new_rows)) {
            return array(
                'new'        => array(),
                'hot'        => array(),
                'list_url'   => eottae_talkroom_list_url(),
                'create_url' => eottae_talkroom_create_url(),
            );
        }

        if (empty($rows)) {
            $rows = $new_rows;
        }

        $hot_pool = array();
        foreach ($rows as $row) {
            $room_id = (int) ($row['room_id'] ?? 0);
            if ($room_id < 1 || isset($new_ids[$room_id])) {
                continue;
            }
            $hot_pool[] = $row;
        }
        if (count($hot_pool) < $hot_limit) {
            $hot_pool = $rows;
        }

        usort($hot_pool, function ($a, $b) {
            $score_cmp = eottae_talkroom_home_hero_hot_score($b) <=> eottae_talkroom_home_hero_hot_score($a);
            if ($score_cmp !== 0) {
                return $score_cmp;
            }

            $post_cmp = (int) ($b['post_count'] ?? 0) <=> (int) ($a['post_count'] ?? 0);
            if ($post_cmp !== 0) {
                return $post_cmp;
            }

            return (int) ($b['member_count'] ?? 0) <=> (int) ($a['member_count'] ?? 0);
        });
        $hot_rows = array_slice($hot_pool, 0, $hot_limit);

        return array(
            'new'        => $new_rows,
            'hot'        => $hot_rows,
            'list_url'   => eottae_talkroom_list_url(),
            'create_url' => eottae_talkroom_create_url(),
        );
    }
}

if (!function_exists('eottae_talkroom_render_card')) {
    function eottae_talkroom_render_card(array $room)
    {
        if (!function_exists('eottae_talkroom_card_html')) {
            include_once G5_PATH.'/components/eottae/talk-room-card.php';
        }

        echo eottae_talkroom_card_html($room);
    }
}

if (!function_exists('eottae_talkroom_admin_applies_url')) {
    function eottae_talkroom_admin_applies_url()
    {
        return G5_URL.'/page/eottae-admin-talk-applies.php';
    }
}

if (!function_exists('eottae_talkroom_admin_rooms_url')) {
    function eottae_talkroom_admin_rooms_url()
    {
        return G5_URL.'/page/eottae-admin-talk-rooms.php';
    }
}

if (!function_exists('eottae_talkroom_admin_detail_url')) {
    function eottae_talkroom_admin_detail_url($room_id)
    {
        return G5_URL.'/page/eottae-admin-talk-detail.php?room_id='.(int) $room_id;
    }
}

if (!function_exists('eottae_talkroom_admin_token')) {
    function eottae_talkroom_admin_token($regenerate = false)
    {
        $token = get_session('eottae_talkroom_admin_token');
        if ($regenerate || $token === '' || $token === null) {
            try {
                $token = bin2hex(random_bytes(16));
            } catch (Exception $e) {
                $token = md5(uniqid((string) mt_rand(), true));
            }
            set_session('eottae_talkroom_admin_token', $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_talkroom_verify_admin_token')) {
    function eottae_talkroom_verify_admin_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_talkroom_admin_token');

        return $token !== '' && $session_token !== '' && hash_equals($session_token, $token);
    }
}

if (!function_exists('eottae_talkroom_sql_member_table')) {
    function eottae_talkroom_sql_member_table()
    {
        global $g5;

        if (!empty($g5['member_table'])) {
            return $g5['member_table'];
        }

        return G5_TABLE_PREFIX.'member';
    }
}

if (!function_exists('eottae_talkroom_admin_fetch_room_rows')) {
    /**
     * @return array<int, array<string, mixed>>|false
     */
    function eottae_talkroom_admin_fetch_room_rows($where_sql, $limit, $with_member = true)
    {
        $tables = eottae_talkroom_table_names();
        $limit = max(1, min(500, (int) $limit));
        $order_sql = 'r.created_at DESC, r.room_id DESC';

        $result = sql_query("
            SELECT r.*
            FROM `{$tables['rooms']}` r
            {$where_sql}
            ORDER BY {$order_sql}
            LIMIT {$limit}
        ", false);
        if (!$result) {
            return false;
        }

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            if (!is_array($row)) {
                continue;
            }
            if ($with_member) {
                $owner_mb_id = trim((string) ($row['owner_mb_id'] ?? ''));
                $row['owner_nick'] = '';
                $row['owner_email'] = '';
                $row['owner_name'] = '';
                if ($owner_mb_id !== '') {
                    $member_table = eottae_talkroom_sql_member_table();
                    $member = sql_fetch("
                        SELECT mb_nick, mb_email, mb_name
                        FROM `{$member_table}`
                        WHERE mb_id = '".sql_escape_string($owner_mb_id)."'
                        LIMIT 1
                    ", false);
                    if (is_array($member)) {
                        $row['owner_nick'] = (string) ($member['mb_nick'] ?? '');
                        $row['owner_email'] = (string) ($member['mb_email'] ?? '');
                        $row['owner_name'] = (string) ($member['mb_name'] ?? '');
                    }
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_pending_count')) {
    function eottae_talkroom_pending_count()
    {
        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            return 0;
        }

        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$tables['rooms']}`
            WHERE LOWER(TRIM(status)) = 'pending'
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_talkroom_get_room')) {
    function eottae_talkroom_get_room($room_id)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1) {
            return null;
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            return null;
        }

        $member_table = eottae_talkroom_sql_member_table();
        $row = sql_fetch("
            SELECT r.*, m.mb_nick AS owner_nick, m.mb_email AS owner_email, m.mb_name AS owner_name
            FROM `{$tables['rooms']}` r
            LEFT JOIN `{$member_table}` m ON m.mb_id = r.owner_mb_id
            WHERE r.room_id = '{$room_id}'
        ", false);

        if (empty($row['room_id'])) {
            return null;
        }

        return eottae_talkroom_format_admin_room($row);
    }
}

if (!function_exists('eottae_talkroom_format_admin_room')) {
    function eottae_talkroom_format_admin_room(array $row)
    {
        $owner_nick = trim((string) ($row['owner_nick'] ?? ''));
        if ($owner_nick === '' && !empty($row['owner_mb_id'])) {
            $owner_nick = (string) $row['owner_mb_id'];
        }

        return array(
            'room_id'          => (int) $row['room_id'],
            'room_name'        => get_text($row['room_name'] ?? ''),
            'room_description' => get_text($row['room_description'] ?? ''),
            'room_detail'      => get_text($row['room_detail'] ?? ''),
            'category'         => eottae_talkroom_category_label($row['category'] ?? ''),
            'category_code'    => trim((string) ($row['category'] ?? '')),
            'emoji'            => eottae_talkroom_display_emoji($row['emoji'] ?? '', $row['category'] ?? ''),
            'rules'            => get_text($row['rules'] ?? ''),
            'room_notice'      => get_text($row['room_notice'] ?? ''),
            'contact'          => get_text($row['contact'] ?? ''),
            'apply_reason'     => get_text($row['apply_reason'] ?? ''),
            'visibility'       => trim((string) ($row['visibility'] ?? 'public')),
            'visibility_label' => eottae_talkroom_visibility_label($row['visibility'] ?? 'public'),
            'join_type'        => trim((string) ($row['join_type'] ?? 'open')),
            'join_type_label'  => eottae_talkroom_join_type_label($row['join_type'] ?? 'open'),
            'owner_mb_id'      => get_text($row['owner_mb_id'] ?? ''),
            'owner_nick'       => get_text($owner_nick),
            'owner_name'       => get_text($row['owner_name'] ?? ''),
            'owner_email'      => get_text($row['owner_email'] ?? ''),
            'status'           => trim((string) ($row['status'] ?? '')),
            'status_label'     => eottae_talkroom_status_label($row['status'] ?? ''),
            'status_class'     => eottae_talkroom_status_class($row['status'] ?? ''),
            'reject_reason'    => get_text($row['reject_reason'] ?? ''),
            'created_at'       => trim((string) ($row['created_at'] ?? '')),
            'approved_at'      => trim((string) ($row['approved_at'] ?? '')),
            'approved_by'      => get_text($row['approved_by'] ?? ''),
            'updated_at'       => trim((string) ($row['updated_at'] ?? '')),
            'detail_url'       => eottae_talkroom_admin_detail_url((int) $row['room_id']),
        );
    }
}

if (!function_exists('eottae_talkroom_admin_list_applications')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_admin_list_applications($status_filter = 'pending', $limit = 200)
    {
        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            return array();
        }

        $limit = max(1, min(500, (int) $limit));
        $where = '';
        $status_filter = trim((string) $status_filter);
        if ($status_filter !== '' && $status_filter !== 'all') {
            $allowed = array('pending', 'approved', 'active', 'rejected', 'stopped', 'closed');
            if (in_array($status_filter, $allowed, true)) {
                $where = " WHERE LOWER(TRIM(r.status)) = '".sql_escape_string(strtolower($status_filter))."' ";
            }
        }

        $fetched = eottae_talkroom_admin_fetch_room_rows($where, $limit, true);
        if ($fetched === false) {
            $fetched = eottae_talkroom_admin_fetch_room_rows($where, $limit, false);
        }
        if (!is_array($fetched)) {
            return array();
        }

        $rows = array();
        foreach ($fetched as $row) {
            if (is_array($row)) {
                $rows[] = eottae_talkroom_format_admin_room($row);
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_admin_list_rooms')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_admin_list_rooms($limit = 200)
    {
        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            return array();
        }

        $limit = max(1, min(500, (int) $limit));
        $member_table = G5_TABLE_PREFIX.'member';
        $result = sql_query("
            SELECT r.*, m.mb_nick AS owner_nick, m.mb_email AS owner_email, m.mb_name AS owner_name
            FROM `{$tables['rooms']}` r
            LEFT JOIN `{$member_table}` m ON m.mb_id = r.owner_mb_id
            WHERE r.status IN ('approved', 'active', 'stopped', 'closed')
            ORDER BY
                CASE r.status WHEN 'approved' THEN 0 WHEN 'active' THEN 1 ELSE 2 END,
                r.approved_at DESC,
                r.room_id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $rows[] = eottae_talkroom_format_admin_room($row);
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_write_log')) {
    function eottae_talkroom_write_log($room_id, $mb_id, $action, $target_type = '', $target_id = 0, $memo = '')
    {
        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['logs'])) {
            return false;
        }

        $room_id = (int) $room_id;
        $target_id = (int) $target_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $action = eottae_talkroom_clean_text($action, 50);
        $target_type = eottae_talkroom_clean_text($target_type, 20);
        $memo = eottae_talkroom_clean_text($memo, 5000);
        $now = G5_TIME_YMDHIS;

        return (bool) sql_query("
            INSERT INTO `{$tables['logs']}` SET
                room_id = '{$room_id}',
                mb_id = '".sql_escape_string($mb_id)."',
                action = '".sql_escape_string($action)."',
                target_type = '".sql_escape_string($target_type)."',
                target_id = '{$target_id}',
                memo = '".sql_escape_string($memo)."',
                created_at = '{$now}'
        ", false);
    }
}

if (!function_exists('eottae_talkroom_ensure_owner_member')) {
    function eottae_talkroom_ensure_owner_member($room_id, $owner_mb_id, $admin_mb_id, $requested_at = '')
    {
        $room_id = (int) $room_id;
        $owner_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $owner_mb_id);
        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);
        if ($room_id < 1 || $owner_mb_id === '') {
            return false;
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['members'])) {
            return false;
        }

        $now = G5_TIME_YMDHIS;
        $requested_at = trim((string) $requested_at);
        if ($requested_at === '' || $requested_at === '0000-00-00 00:00:00') {
            $requested_at = $now;
        }

        $exists = sql_fetch("
            SELECT id
            FROM `{$tables['members']}`
            WHERE room_id = '{$room_id}'
              AND mb_id = '".sql_escape_string($owner_mb_id)."'
        ", false);

        if (!empty($exists['id'])) {
            return (bool) sql_query("
                UPDATE `{$tables['members']}` SET
                    role = 'owner',
                    status = 'active',
                    joined_at = '{$now}',
                    requested_at = '".sql_escape_string($requested_at)."',
                    approved_at = '{$now}',
                    approved_by = '".sql_escape_string($admin_mb_id)."',
                    kicked_at = '0000-00-00 00:00:00',
                    kicked_by = '',
                    kicked_reason = '',
                    can_rejoin = '1'
                WHERE id = '".(int) $exists['id']."'
            ", false);
        }

        return (bool) sql_query("
            INSERT INTO `{$tables['members']}` SET
                room_id = '{$room_id}',
                mb_id = '".sql_escape_string($owner_mb_id)."',
                role = 'owner',
                status = 'active',
                joined_at = '{$now}',
                requested_at = '".sql_escape_string($requested_at)."',
                approved_at = '{$now}',
                approved_by = '".sql_escape_string($admin_mb_id)."',
                kicked_at = '0000-00-00 00:00:00',
                kicked_by = '',
                kicked_reason = '',
                can_rejoin = '1'
        ", false);
    }
}

if (!function_exists('eottae_talkroom_approve_room')) {
    function eottae_talkroom_approve_room($room_id, $admin_mb_id)
    {
        $room = eottae_talkroom_get_room($room_id);
        if (!$room) {
            return array('ok' => false, 'message' => '톡방 신청을 찾을 수 없습니다.');
        }
        if ($room['status'] !== 'pending') {
            return array('ok' => false, 'message' => '승인 대기 상태의 신청만 처리할 수 있습니다.');
        }

        $tables = eottae_talkroom_table_names();
        $room_id = (int) $room_id;
        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);
        $now = G5_TIME_YMDHIS;

        $ok = (bool) sql_query("
            UPDATE `{$tables['rooms']}` SET
                status = 'approved',
                approved_at = '{$now}',
                approved_by = '".sql_escape_string($admin_mb_id)."',
                reject_reason = '',
                updated_at = '{$now}'
            WHERE room_id = '{$room_id}'
              AND status = 'pending'
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '승인 처리에 실패했습니다.');
        }

        if (!eottae_talkroom_ensure_owner_member($room_id, $room['owner_mb_id'], $admin_mb_id, $room['created_at'])) {
            return array('ok' => false, 'message' => '방장 등록에 실패했습니다. 다시 시도해 주세요.');
        }

        eottae_talkroom_write_log($room_id, $admin_mb_id, 'approve', 'room', $room_id, '톡방 개설 승인');

        if (function_exists('eottae_talkroom_notify_room_approved')) {
            eottae_talkroom_notify_room_approved($room);
        }

        return array('ok' => true, 'message' => '톡방 개설을 승인했습니다.');
    }
}

if (!function_exists('eottae_talkroom_reject_room')) {
    function eottae_talkroom_reject_room($room_id, $admin_mb_id, $reason)
    {
        $room = eottae_talkroom_get_room($room_id);
        if (!$room) {
            return array('ok' => false, 'message' => '톡방 신청을 찾을 수 없습니다.');
        }
        if ($room['status'] !== 'pending') {
            return array('ok' => false, 'message' => '승인 대기 상태의 신청만 반려할 수 있습니다.');
        }

        $reason = eottae_talkroom_clean_text($reason, 2000);
        $reason_len = function_exists('mb_strlen') ? mb_strlen($reason, 'UTF-8') : strlen($reason);
        if ($reason_len < 2) {
            return array('ok' => false, 'message' => '반려 사유를 2자 이상 입력해 주세요.');
        }

        $tables = eottae_talkroom_table_names();
        $room_id = (int) $room_id;
        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);
        $now = G5_TIME_YMDHIS;

        $ok = (bool) sql_query("
            UPDATE `{$tables['rooms']}` SET
                status = 'rejected',
                reject_reason = '".sql_escape_string($reason)."',
                updated_at = '{$now}'
            WHERE room_id = '{$room_id}'
              AND status = 'pending'
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '반려 처리에 실패했습니다.');
        }

        eottae_talkroom_write_log($room_id, $admin_mb_id, 'reject', 'room', $room_id, $reason);

        if (function_exists('eottae_talkroom_notify_room_rejected')) {
            eottae_talkroom_notify_room_rejected($room, $reason);
        }

        return array('ok' => true, 'message' => '톡방 개설 신청을 반려했습니다.');
    }
}

if (!function_exists('eottae_talkroom_stop_room')) {
    function eottae_talkroom_stop_room($room_id, $admin_mb_id)
    {
        $room = eottae_talkroom_get_room($room_id);
        if (!$room) {
            return array('ok' => false, 'message' => '톡방을 찾을 수 없습니다.');
        }
        if (!in_array($room['status'], array('approved', 'active'), true)) {
            return array('ok' => false, 'message' => '운영 중인 톡방만 중지할 수 있습니다.');
        }

        $tables = eottae_talkroom_table_names();
        $room_id = (int) $room_id;
        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);
        $now = G5_TIME_YMDHIS;

        $ok = (bool) sql_query("
            UPDATE `{$tables['rooms']}` SET
                status = 'stopped',
                updated_at = '{$now}'
            WHERE room_id = '{$room_id}'
              AND status IN ('approved', 'active')
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '운영중지 처리에 실패했습니다.');
        }

        eottae_talkroom_write_log($room_id, $admin_mb_id, 'stop', 'room', $room_id, '톡방 운영중지');

        return array('ok' => true, 'message' => '톡방 운영을 중지했습니다.');
    }
}

if (!function_exists('eottae_talkroom_operating_statuses')) {
    function eottae_talkroom_operating_statuses()
    {
        return array('approved', 'active');
    }
}

if (!function_exists('eottae_talkroom_is_operating_room')) {
    function eottae_talkroom_is_operating_room($status)
    {
        return in_array(trim((string) $status), eottae_talkroom_operating_statuses(), true);
    }
}

if (!function_exists('eottae_talkroom_is_kicked_status')) {
    function eottae_talkroom_is_kicked_status($status)
    {
        $status = trim((string) $status);

        return in_array($status, array('kicked', 'banned'), true);
    }
}

if (!function_exists('eottae_talkroom_get_operating_room')) {
    function eottae_talkroom_get_operating_room($room_id)
    {
        $room = eottae_talkroom_get_room($room_id);
        if (!$room || !eottae_talkroom_is_operating_room($room['status'])) {
            return null;
        }

        return $room;
    }
}

if (!function_exists('eottae_talkroom_member_token')) {
    function eottae_talkroom_member_token($regenerate = false)
    {
        $token = get_session('eottae_talkroom_member_token');
        if ($regenerate || $token === '' || $token === null) {
            try {
                $token = bin2hex(random_bytes(16));
            } catch (Exception $e) {
                $token = md5(uniqid((string) mt_rand(), true));
            }
            set_session('eottae_talkroom_member_token', $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_talkroom_verify_member_token')) {
    function eottae_talkroom_verify_member_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_talkroom_member_token');

        return $token !== '' && $session_token !== '' && hash_equals($session_token, $token);
    }
}

if (!function_exists('eottae_talkroom_get_member_row')) {
    function eottae_talkroom_get_member_row($room_id, $mb_id)
    {
        $room_id = (int) $room_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($room_id < 1 || $mb_id === '') {
            return null;
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['members'])) {
            return null;
        }

        $row = sql_fetch("
            SELECT *
            FROM `{$tables['members']}`
            WHERE room_id = '{$room_id}'
              AND mb_id = '".sql_escape_string($mb_id)."'
        ", false);

        return !empty($row['id']) ? $row : null;
    }
}

if (!function_exists('eottae_talkroom_can_rejoin_room')) {
    function eottae_talkroom_can_rejoin_room($member_row)
    {
        if (!$member_row) {
            return true;
        }

        $status = trim((string) ($member_row['status'] ?? ''));
        if (eottae_talkroom_is_kicked_status($status)) {
            return false;
        }

        if ((int) ($member_row['can_rejoin'] ?? 1) === 0) {
            return false;
        }

        if ($status === 'left' || $status === 'rejected') {
            return true;
        }

        return !in_array($status, array('active', 'pending'), true);
    }
}

if (!function_exists('eottae_talkroom_is_active_member')) {
    function eottae_talkroom_is_active_member($member_row)
    {
        return is_array($member_row) && trim((string) ($member_row['status'] ?? '')) === 'active';
    }
}

if (!function_exists('eottae_talkroom_sanitize_internal_href')) {
    /**
     * 내부 링크만 허용 (XSS·오픈 리다이렉트 방지)
     */
    function eottae_talkroom_sanitize_internal_href($href, $fallback = '#')
    {
        $href = trim(strip_tags((string) $href));
        if ($href === '' || $href === '#') {
            return $fallback;
        }

        if (function_exists('clean_xss_tags')) {
            $href = clean_xss_tags($href, 1, 1);
        }

        $lower = strtolower($href);
        if (preg_match('/^(javascript|data|vbscript):/i', $lower)) {
            return $fallback;
        }

        if ($href[0] === '/' && ($href[1] ?? '') !== '/') {
            return $href;
        }

        $base = defined('G5_URL') ? rtrim((string) G5_URL, '/') : '';
        if ($base !== '' && strpos($href, $base.'/') === 0) {
            return $href;
        }
        if ($base !== '' && $href === $base) {
            return $href;
        }

        return $fallback;
    }
}

if (!function_exists('eottae_talkroom_can_view_posts')) {
    function eottae_talkroom_can_view_posts(array $room, $member_row = null)
    {
        if (($room['visibility'] ?? 'public') === 'private') {
            return eottae_talkroom_is_active_member($member_row);
        }

        return true;
    }
}

if (!function_exists('eottae_talkroom_can_view_full_detail')) {
    function eottae_talkroom_can_view_full_detail(array $room, $member_row = null)
    {
        if (($room['visibility'] ?? 'public') === 'private') {
            return eottae_talkroom_is_active_member($member_row);
        }

        return true;
    }
}

if (!function_exists('eottae_talkroom_can_write_posts')) {
    function eottae_talkroom_can_write_posts(array $room, $member_row = null)
    {
        return eottae_talkroom_is_active_member($member_row)
            && eottae_talkroom_is_operating_room($room['status'] ?? '');
    }
}

if (!function_exists('eottae_talkroom_membership_state')) {
    function eottae_talkroom_membership_state(array $room, $member_row = null, $mb_id = '')
    {
        if ($member_row && eottae_talkroom_is_kicked_status($member_row['status'] ?? '')) {
            return 'kicked';
        }

        if ($member_row && ($member_row['status'] ?? '') === 'pending') {
            return 'pending';
        }

        if ($member_row && ($member_row['status'] ?? '') === 'active') {
            if (($member_row['role'] ?? '') === 'owner' || ($mb_id !== '' && $mb_id === ($room['owner_mb_id'] ?? ''))) {
                return 'owner';
            }

            return 'active';
        }

        if ($member_row && ($member_row['status'] ?? '') === 'left') {
            return 'left';
        }

        if ($member_row && ($member_row['status'] ?? '') === 'rejected') {
            return 'left';
        }

        return 'guest';
    }
}

if (!function_exists('eottae_talkroom_room_stats')) {
    function eottae_talkroom_room_stats($room_id)
    {
        $room_id = (int) $room_id;
        $member_counts = eottae_talkroom_member_counts(array($room_id));
        $post_counts = eottae_talkroom_post_counts(array($room_id));
        $latest_posts = eottae_talkroom_latest_post_times(array($room_id));

        return array(
            'member_count'   => isset($member_counts[$room_id]) ? $member_counts[$room_id] : 0,
            'post_count'     => isset($post_counts[$room_id]) ? $post_counts[$room_id] : 0,
            'latest_post_at' => isset($latest_posts[$room_id]) ? $latest_posts[$room_id] : '',
        );
    }
}

if (!function_exists('eottae_talkroom_format_detail')) {
    function eottae_talkroom_format_detail(array $room, array $stats = array())
    {
        $room_id = (int) ($room['room_id'] ?? 0);
        $updated_at = eottae_talkroom_resolve_updated_at($room, $stats['latest_post_at'] ?? '');

        return array_merge($room, array(
            'member_count'     => (int) ($stats['member_count'] ?? 0),
            'post_count'       => (int) ($stats['post_count'] ?? 0),
            'updated_at'       => $updated_at,
            'updated_label'    => function_exists('eottae_community_relative_time')
                ? eottae_community_relative_time($updated_at)
                : ($updated_at !== '' ? substr($updated_at, 0, 16) : ''),
            'join_type'        => trim((string) ($room['join_type'] ?? 'open')),
            'join_type_label'  => eottae_talkroom_join_type_label($room['join_type'] ?? 'open'),
            'rules'            => get_text($room['rules'] ?? ''),
            'room_notice'      => get_text($room['room_notice'] ?? ''),
            'enter_href'       => eottae_talkroom_enter_url($room_id),
            'invite_href'      => eottae_talkroom_invite_url($room_id),
            'manage_href'      => eottae_talkroom_owner_manage_url($room_id),
            'write_href'       => eottae_talkroom_write_url($room_id),
        ));
    }
}

if (!function_exists('eottae_talkroom_write_url')) {
    function eottae_talkroom_write_url($room_id)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1 || !eottae_talkroom_board_exists()) {
            return '';
        }

        $bo = preg_replace('/[^a-z0-9_]/', '', eottae_talkroom_board_table());

        return G5_BBS_URL.'/write.php?bo_table='.$bo.'&wr_1='.$room_id;
    }
}

if (!function_exists('eottae_talkroom_post_view_url')) {
    function eottae_talkroom_post_view_url($wr_id, $room_id = 0)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || !eottae_talkroom_board_exists()) {
            return '';
        }

        $bo = preg_replace('/[^a-z0-9_]/', '', eottae_talkroom_board_table());
        $url = G5_BBS_URL.'/board.php?bo_table='.$bo.'&wr_id='.$wr_id;
        if ($room_id > 0) {
            $url .= '&room_id='.(int) $room_id;
        }

        return $url;
    }
}

if (!function_exists('eottae_talkroom_list_room_posts')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_list_room_posts($room_id, $limit = 20)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1 || !eottae_talkroom_board_exists()) {
            return array();
        }

        $bo = preg_replace('/[^a-z0-9_]/', '', eottae_talkroom_board_table());
        $write_table = G5_TABLE_PREFIX.'write_'.$bo;
        if (!eottae_talkroom_table_exists($write_table)) {
            return array();
        }

        $limit = max(1, min(50, (int) $limit));
        $visible = eottae_talkroom_post_visible_sql();
        $result = sql_query("
            SELECT wr_id, wr_subject, wr_name, wr_datetime, wr_content, wr_comment, mb_id, wr_3, ca_name
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_1 = '{$room_id}'
              AND {$visible}
            ORDER BY wr_id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $content = isset($row['wr_content']) ? (string) $row['wr_content'] : '';
                $snippet = function_exists('eottae_community_snippet')
                    ? eottae_community_snippet($content, 120)
                    : cut_str(strip_tags($content), 120);

                $post_row = array(
                    'wr_id'      => (int) $row['wr_id'],
                    'wr_name'    => $row['wr_name'] ?? '',
                    'mb_id'      => $row['mb_id'] ?? '',
                    'wr_3'       => $row['wr_3'] ?? '',
                    'subject'    => get_text($row['wr_subject'] ?? ''),
                    'author'     => get_text($row['wr_name'] ?? ''),
                    'datetime'   => trim((string) ($row['wr_datetime'] ?? '')),
                    'time_label' => function_exists('eottae_community_relative_time')
                        ? eottae_community_relative_time($row['wr_datetime'] ?? '')
                        : substr((string) ($row['wr_datetime'] ?? ''), 0, 16),
                    'snippet'    => $snippet,
                    'comment_count' => (int) ($row['wr_comment'] ?? 0),
                    'href'       => eottae_talkroom_post_view_url((int) $row['wr_id'], $room_id),
                );
                if (function_exists('eottae_talkroom_ai_message_enrich_post_row')) {
                    include_once G5_PATH.'/components/eottae/talk-ai-message-ui.php';
                    $post_row = eottae_talkroom_ai_message_enrich_post_row($post_row);
                }
                $rows[] = $post_row;
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_post_type_label')) {
    function eottae_talkroom_post_type_label($ca_name)
    {
        $ca_name = trim(strip_tags((string) $ca_name));

        return $ca_name !== '' ? $ca_name : '일반';
    }
}

if (!function_exists('eottae_talkroom_list_home_feed')) {
    /**
     * 메인 — 공개·운영 중 톡방 최신글 (단일 JOIN 쿼리)
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_list_home_feed($limit = 8)
    {
        if (!eottae_talkroom_board_exists()) {
            return array();
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            return array();
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table)) {
            return array();
        }

        $limit = max(1, min(20, (int) $limit));
        $statuses = eottae_talkroom_operating_statuses();
        $status_sql = array();
        foreach ($statuses as $status) {
            $status_sql[] = "'".sql_real_escape_string($status)."'";
        }
        $status_in = implode(',', $status_sql);
        $visible = eottae_talkroom_post_visible_sql('w');

        $result = sql_query("
            SELECT
                w.wr_id,
                w.wr_subject,
                w.wr_name,
                w.wr_datetime,
                w.wr_comment,
                w.ca_name,
                w.mb_id,
                w.wr_3,
                r.room_id,
                r.room_name
            FROM `{$write_table}` w
            INNER JOIN `{$tables['rooms']}` r
                ON r.room_id = CAST(w.wr_1 AS UNSIGNED)
            WHERE w.wr_is_comment = 0
              AND {$visible}
              AND r.status IN ({$status_in})
              AND r.visibility = 'public'
            ORDER BY w.wr_datetime DESC, w.wr_id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        if (!$result) {
            return $rows;
        }

        while ($row = sql_fetch_array($result)) {
            $room_id = (int) ($row['room_id'] ?? 0);
            $wr_id = (int) ($row['wr_id'] ?? 0);
            if ($room_id < 1 || $wr_id < 1) {
                continue;
            }

            $type_label = eottae_talkroom_post_type_label($row['ca_name'] ?? '');
            $feed_row = array(
                'wr_id'         => $wr_id,
                'room_id'       => $room_id,
                'room_name'     => get_text($row['room_name'] ?? ''),
                'wr_name'       => $row['wr_name'] ?? '',
                'mb_id'         => $row['mb_id'] ?? '',
                'wr_3'          => $row['wr_3'] ?? '',
                'subject'       => get_text($row['wr_subject'] ?? ''),
                'author'        => get_text($row['wr_name'] ?? ''),
                'type_label'    => $type_label,
                'type_class'    => function_exists('eottae_community_badge_class')
                    ? eottae_community_badge_class($type_label)
                    : 'community-badge--default',
                'datetime'      => trim((string) ($row['wr_datetime'] ?? '')),
                'time_label'    => function_exists('eottae_community_relative_time')
                    ? eottae_community_relative_time($row['wr_datetime'] ?? '')
                    : substr((string) ($row['wr_datetime'] ?? ''), 0, 16),
                'comment_count' => (int) ($row['wr_comment'] ?? 0),
                'href'          => eottae_talkroom_post_view_url($wr_id, $room_id),
            );
            if (function_exists('eottae_talkroom_ai_message_enrich_post_row')) {
                include_once G5_PATH.'/components/eottae/talk-ai-message-ui.php';
                $feed_row = eottae_talkroom_ai_message_enrich_post_row($feed_row);
            }
            $rows[] = $feed_row;
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_build_detail_context')) {
    function eottae_talkroom_build_detail_context($room_id, $mb_id = '')
    {
        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return null;
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $member_row = $mb_id !== '' ? eottae_talkroom_get_member_row($room_id, $mb_id) : null;
        $stats = eottae_talkroom_room_stats($room_id);
        $detail = eottae_talkroom_format_detail($room, $stats);

        $membership = eottae_talkroom_membership_state($room, $member_row, $mb_id);
        $can_view_posts = eottae_talkroom_can_view_posts($room, $member_row);
        $can_view_full = eottae_talkroom_can_view_full_detail($room, $member_row);
        $can_write = eottae_talkroom_can_write_posts($room, $member_row);
        $can_join = false;
        $can_leave = false;
        $join_blocked_reason = '';

        if ($mb_id === '') {
            $can_join = false;
        } elseif ($membership === 'kicked') {
            $can_rejoin = (int) ($member_row['can_rejoin'] ?? 1);
            $join_blocked_reason = $can_rejoin === 0
                ? '재참여가 허용되지 않는 톡방입니다.'
                : '강퇴된 톡방입니다. 다시 참여할 수 없습니다.';
        } elseif (in_array($membership, array('guest', 'left'), true)) {
            $can_join = eottae_talkroom_can_rejoin_room($member_row);
            if (!$can_join) {
                $join_blocked_reason = '다시 참여할 수 없는 톡방입니다.';
            }
        } elseif ($membership === 'pending') {
            $join_blocked_reason = '참여 승인을 기다리는 중입니다.';
        } elseif ($membership === 'owner' || $membership === 'active') {
            $can_leave = ($membership !== 'owner');
            if ($membership === 'owner') {
                $join_blocked_reason = '방장은 톡방을 탈퇴할 수 없습니다. 방장 변경 후 탈퇴해 주세요.';
            }
        }

        $posts = $can_view_posts ? eottae_talkroom_list_room_posts($room_id, 20) : array();

        global $is_admin;
        $is_super = (isset($is_admin) && $is_admin === 'super');
        $can_manage = eottae_talkroom_can_manage_room($room_id, $mb_id, $is_super);
        $can_view_notice = ($room['visibility'] ?? 'public') === 'public' || $can_view_full;
        $can_share_invite = eottae_talkroom_can_share_invite(array(
            'membership'  => $membership,
            'can_manage'  => $can_manage,
        ));

        return array(
            'room'                => $detail,
            'membership'          => $membership,
            'member_row'          => $member_row,
            'can_view_posts'      => $can_view_posts,
            'can_view_full'       => $can_view_full,
            'can_view_notice'     => $can_view_notice,
            'can_write'           => $can_write,
            'can_join'            => $can_join,
            'can_leave'           => $can_leave,
            'can_manage'          => $can_manage,
            'can_share_invite'    => $can_share_invite,
            'join_blocked_reason' => $join_blocked_reason,
            'posts'               => $posts,
        );
    }
}

if (!function_exists('eottae_talkroom_join_room')) {
    function eottae_talkroom_join_room($room_id, $mb_id)
    {
        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array('ok' => false, 'message' => '참여할 수 없는 톡방입니다.');
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인이 필요합니다.');
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        if ($member_row && eottae_talkroom_is_kicked_status($member_row['status'] ?? '')) {
            $can_rejoin = (int) ($member_row['can_rejoin'] ?? 1);
            $message = $can_rejoin === 0
                ? '재참여가 허용되지 않는 톡방입니다.'
                : '강퇴된 톡방입니다. 다시 참여할 수 없습니다.';

            return array('ok' => false, 'message' => $message);
        }

        if ($member_row && !eottae_talkroom_can_rejoin_room($member_row)) {
            return array('ok' => false, 'message' => '다시 참여할 수 없는 톡방입니다.');
        }

        if ($member_row && ($member_row['status'] ?? '') === 'active') {
            return array('ok' => false, 'message' => '이미 참여 중인 톡방입니다.');
        }

        if ($member_row && ($member_row['status'] ?? '') === 'pending') {
            return array('ok' => false, 'message' => '이미 참여 승인을 기다리고 있습니다.');
        }

        $tables = eottae_talkroom_table_names();
        $room_id = (int) $room_id;
        $now = G5_TIME_YMDHIS;
        $join_type = trim((string) ($room['join_type'] ?? 'open'));
        $new_status = ($join_type === 'approval') ? 'pending' : 'active';
        $is_owner = ($mb_id === ($room['owner_mb_id'] ?? ''));

        if ($is_owner) {
            return array('ok' => false, 'message' => '방장은 이미 이 톡방의 운영자입니다.');
        }

        if ($member_row && !empty($member_row['id'])) {
            $ok = (bool) sql_query("
                UPDATE `{$tables['members']}` SET
                    role = 'member',
                    status = '".sql_escape_string($new_status)."',
                    requested_at = '{$now}',
                    joined_at = '".($new_status === 'active' ? $now : '0000-00-00 00:00:00')."',
                    approved_at = '".($new_status === 'active' ? $now : '0000-00-00 00:00:00')."',
                    approved_by = '',
                    kicked_at = '0000-00-00 00:00:00',
                    kicked_by = '',
                    kicked_reason = '',
                    can_rejoin = '1'
                WHERE id = '".(int) $member_row['id']."'
            ", false);
        } else {
            $ok = (bool) sql_query("
                INSERT INTO `{$tables['members']}` SET
                    room_id = '{$room_id}',
                    mb_id = '".sql_escape_string($mb_id)."',
                    role = 'member',
                    status = '".sql_escape_string($new_status)."',
                    joined_at = '".($new_status === 'active' ? $now : '0000-00-00 00:00:00')."',
                    requested_at = '{$now}',
                    approved_at = '".($new_status === 'active' ? $now : '0000-00-00 00:00:00')."',
                    approved_by = '',
                    kicked_at = '0000-00-00 00:00:00',
                    kicked_by = '',
                    kicked_reason = '',
                    can_rejoin = '1'
            ", false);
        }

        if (!$ok) {
            return array('ok' => false, 'message' => '참여 처리에 실패했습니다.');
        }

        eottae_talkroom_write_log($room_id, $mb_id, 'join', 'member', $room_id, $new_status);

        if ($new_status === 'active' && function_exists('eottae_talkroom_ai_schedule_welcome')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-welcome.lib.php';
            eottae_talkroom_ai_schedule_welcome($room_id, $mb_id);
        }

        if ($new_status === 'pending') {
            if (function_exists('eottae_talkroom_notify_owner_join_request')) {
                eottae_talkroom_notify_owner_join_request($room_id, $mb_id);
            }

            return array('ok' => true, 'message' => '참여 신청이 접수되었습니다. 방장 승인 후 글쓰기가 가능합니다.', 'status' => 'pending');
        }

        return array('ok' => true, 'message' => '톡방에 참여했습니다.', 'status' => 'active');
    }
}

if (!function_exists('eottae_talkroom_leave_room')) {
    function eottae_talkroom_leave_room($room_id, $mb_id)
    {
        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array('ok' => false, 'message' => '탈퇴할 수 없는 톡방입니다.');
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        if (!$member_row || ($member_row['status'] ?? '') !== 'active') {
            return array('ok' => false, 'message' => '참여 중인 톡방이 아닙니다.');
        }

        if (($member_row['role'] ?? '') === 'owner' || $mb_id === ($room['owner_mb_id'] ?? '')) {
            return array('ok' => false, 'message' => '방장은 톡방을 탈퇴할 수 없습니다. 방장 변경 후 탈퇴해 주세요.');
        }

        $tables = eottae_talkroom_table_names();
        $now = G5_TIME_YMDHIS;
        $ok = (bool) sql_query("
            UPDATE `{$tables['members']}` SET
                status = 'left',
                joined_at = '0000-00-00 00:00:00'
            WHERE id = '".(int) $member_row['id']."'
              AND status = 'active'
              AND role <> 'owner'
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '탈퇴 처리에 실패했습니다.');
        }

        eottae_talkroom_write_log($room_id, $mb_id, 'leave', 'member', (int) $member_row['id'], '자진 탈퇴');

        return array('ok' => true, 'message' => '톡방에서 탈퇴했습니다.');
    }
}

if (!function_exists('eottae_talkroom_list_my_rooms')) {
    function eottae_talkroom_list_my_rooms($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array('created' => array(), 'joined' => array(), 'pending' => array());
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            return array('created' => array(), 'joined' => array(), 'pending' => array());
        }

        $status_in = "'approved','active'";
        $member_table = G5_TABLE_PREFIX.'member';

        $created = array();
        $result = sql_query("
            SELECT r.*, m.mb_nick AS owner_nick
            FROM `{$tables['rooms']}` r
            LEFT JOIN `{$member_table}` m ON m.mb_id = r.owner_mb_id
            WHERE r.owner_mb_id = '".sql_escape_string($mb_id)."'
              AND r.status IN ({$status_in})
            ORDER BY r.updated_at DESC, r.room_id DESC
        ", false);
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $stats = eottae_talkroom_room_stats((int) $row['room_id']);
                $created[] = eottae_talkroom_format_card($row, $stats);
            }
        }

        $joined = array();
        $pending = array();
        if (eottae_talkroom_table_exists($tables['members'])) {
            $result = sql_query("
                SELECT r.*, m.mb_nick AS owner_nick, tm.status AS member_status, tm.role AS member_role
                FROM `{$tables['members']}` tm
                INNER JOIN `{$tables['rooms']}` r ON r.room_id = tm.room_id
                LEFT JOIN `{$member_table}` m ON m.mb_id = r.owner_mb_id
                WHERE tm.mb_id = '".sql_escape_string($mb_id)."'
                  AND r.status IN ({$status_in})
                  AND tm.status IN ('active', 'pending')
                ORDER BY tm.status ASC, r.updated_at DESC, r.room_id DESC
            ", false);
            if ($result) {
                while ($row = sql_fetch_array($result)) {
                    $stats = eottae_talkroom_room_stats((int) $row['room_id']);
                    $card = eottae_talkroom_format_card($row, $stats);
                    $card['member_status'] = trim((string) ($row['member_status'] ?? ''));
                    $card['member_role'] = trim((string) ($row['member_role'] ?? ''));

                    if (($row['member_status'] ?? '') === 'pending') {
                        $pending[] = $card;
                        continue;
                    }

                    $member_role = trim((string) ($row['member_role'] ?? ''));
                    if ($member_role === 'member' || $member_role === 'owner') {
                        if (($row['owner_mb_id'] ?? '') !== $mb_id) {
                            $joined[] = $card;
                        }
                    }
                }
            }
        }

        return array(
            'created' => $created,
            'joined'  => $joined,
            'pending' => $pending,
        );
    }
}

if (!function_exists('eottae_talkroom_owner_manage_url')) {
    function eottae_talkroom_owner_manage_url($room_id)
    {
        return G5_URL.'/page/eottae-talk-manage.php?room_id='.(int) $room_id;
    }
}

if (!function_exists('eottae_talkroom_is_room_owner')) {
    function eottae_talkroom_is_room_owner(array $room, $mb_id, $member_row = null)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return false;
        }

        if ($mb_id === ($room['owner_mb_id'] ?? '')) {
            return true;
        }

        if ($member_row === null && !empty($room['room_id'])) {
            $member_row = eottae_talkroom_get_member_row((int) $room['room_id'], $mb_id);
        }

        return is_array($member_row)
            && ($member_row['role'] ?? '') === 'owner'
            && ($member_row['status'] ?? '') === 'active';
    }
}

if (!function_exists('eottae_talkroom_can_manage_room')) {
    function eottae_talkroom_can_manage_room($room_id, $mb_id, $is_super_admin = false)
    {
        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return false;
        }

        if ($is_super_admin) {
            return true;
        }

        return eottae_talkroom_is_room_owner($room, $mb_id);
    }
}

if (!function_exists('eottae_talkroom_owner_token')) {
    function eottae_talkroom_owner_token($regenerate = false)
    {
        $token = get_session('eottae_talkroom_owner_token');
        if ($regenerate || $token === '' || $token === null) {
            try {
                $token = bin2hex(random_bytes(16));
            } catch (Exception $e) {
                $token = md5(uniqid((string) mt_rand(), true));
            }
            set_session('eottae_talkroom_owner_token', $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_talkroom_verify_owner_token')) {
    function eottae_talkroom_verify_owner_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_talkroom_owner_token');

        return $token !== '' && $session_token !== '' && hash_equals($session_token, $token);
    }
}

if (!function_exists('eottae_talkroom_assert_manage_access')) {
    function eottae_talkroom_assert_manage_access($room_id, $mb_id, $is_super_admin = false)
    {
        if (!eottae_talkroom_can_manage_room($room_id, $mb_id, $is_super_admin)) {
            return array('ok' => false, 'message' => '톡방 관리 권한이 없습니다.');
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_talkroom_list_room_members')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_list_room_members($room_id, $status = 'active', $limit = 100)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1) {
            return array();
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['members'])) {
            return array();
        }

        $limit = max(1, min(200, (int) $limit));
        $status = trim((string) $status);
        $where = '';
        if ($status !== '' && $status !== 'all') {
            if ($status === 'kicked') {
                $where = " AND tm.status IN ('kicked', 'banned') ";
            } else {
                $where = " AND tm.status = '".sql_escape_string($status)."' ";
            }
        }

        $member_table = G5_TABLE_PREFIX.'member';
        $result = sql_query("
            SELECT tm.*, m.mb_nick
            FROM `{$tables['members']}` tm
            LEFT JOIN `{$member_table}` m ON m.mb_id = tm.mb_id
            WHERE tm.room_id = '{$room_id}'
            {$where}
            ORDER BY
                CASE tm.role WHEN 'owner' THEN 0 ELSE 1 END,
                tm.joined_at DESC,
                tm.requested_at DESC,
                tm.id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $nick = trim((string) ($row['mb_nick'] ?? ''));
                if ($nick === '') {
                    $nick = (string) ($row['mb_id'] ?? '');
                }

                $rows[] = array(
                    'id'            => (int) $row['id'],
                    'mb_id'         => get_text($row['mb_id'] ?? ''),
                    'mb_nick'       => get_text($nick),
                    'role'          => trim((string) ($row['role'] ?? '')),
                    'status'        => trim((string) ($row['status'] ?? '')),
                    'requested_at'  => trim((string) ($row['requested_at'] ?? '')),
                    'joined_at'     => trim((string) ($row['joined_at'] ?? '')),
                    'approved_at'   => trim((string) ($row['approved_at'] ?? '')),
                    'kicked_at'     => trim((string) ($row['kicked_at'] ?? '')),
                    'kicked_by'     => get_text($row['kicked_by'] ?? ''),
                    'kicked_reason' => get_text($row['kicked_reason'] ?? ''),
                    'can_rejoin'    => (int) ($row['can_rejoin'] ?? 1),
                );
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_approve_member')) {
    function eottae_talkroom_approve_member($room_id, $member_id, $manager_mb_id, $is_super_admin = false)
    {
        $check = eottae_talkroom_assert_manage_access($room_id, $manager_mb_id, $is_super_admin);
        if (empty($check['ok'])) {
            return $check;
        }

        $room_id = (int) $room_id;
        $member_id = (int) $member_id;
        $tables = eottae_talkroom_table_names();
        $row = sql_fetch("
            SELECT *
            FROM `{$tables['members']}`
            WHERE id = '{$member_id}'
              AND room_id = '{$room_id}'
              AND status = 'pending'
              AND role = 'member'
        ", false);

        if (empty($row['id'])) {
            return array('ok' => false, 'message' => '승인할 참여 신청을 찾을 수 없습니다.');
        }

        $now = G5_TIME_YMDHIS;
        $manager_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $manager_mb_id);
        $ok = (bool) sql_query("
            UPDATE `{$tables['members']}` SET
                status = 'active',
                joined_at = '{$now}',
                approved_at = '{$now}',
                approved_by = '".sql_escape_string($manager_mb_id)."'
            WHERE id = '{$member_id}'
              AND room_id = '{$room_id}'
              AND status = 'pending'
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '참여 승인에 실패했습니다.');
        }

        eottae_talkroom_write_log($room_id, $manager_mb_id, 'approve_member', 'member', $member_id, $row['mb_id']);

        if (function_exists('eottae_talkroom_ai_schedule_welcome')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-welcome.lib.php';
            eottae_talkroom_ai_schedule_welcome($room_id, $row['mb_id']);
        }

        if (function_exists('eottae_talkroom_notify_join_approved')) {
            eottae_talkroom_notify_join_approved($room_id, $row['mb_id']);
        }

        return array('ok' => true, 'message' => '참여 신청을 승인했습니다.');
    }
}

if (!function_exists('eottae_talkroom_reject_member')) {
    function eottae_talkroom_reject_member($room_id, $member_id, $manager_mb_id, $is_super_admin = false)
    {
        $check = eottae_talkroom_assert_manage_access($room_id, $manager_mb_id, $is_super_admin);
        if (empty($check['ok'])) {
            return $check;
        }

        $room_id = (int) $room_id;
        $member_id = (int) $member_id;
        $tables = eottae_talkroom_table_names();
        $row = sql_fetch("
            SELECT *
            FROM `{$tables['members']}`
            WHERE id = '{$member_id}'
              AND room_id = '{$room_id}'
              AND status = 'pending'
              AND role = 'member'
        ", false);

        if (empty($row['id'])) {
            return array('ok' => false, 'message' => '거절할 참여 신청을 찾을 수 없습니다.');
        }

        $ok = (bool) sql_query("
            UPDATE `{$tables['members']}` SET
                status = 'rejected',
                joined_at = '0000-00-00 00:00:00',
                approved_at = '0000-00-00 00:00:00',
                approved_by = ''
            WHERE id = '{$member_id}'
              AND room_id = '{$room_id}'
              AND status = 'pending'
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '참여 거절에 실패했습니다.');
        }

        eottae_talkroom_write_log($room_id, $manager_mb_id, 'reject_member', 'member', $member_id, $row['mb_id']);

        if (function_exists('eottae_talkroom_notify_join_rejected')) {
            eottae_talkroom_notify_join_rejected($room_id, $row['mb_id']);
        }

        return array('ok' => true, 'message' => '참여 신청을 거절했습니다.');
    }
}

if (!function_exists('eottae_talkroom_parse_owner_update_input')) {
    function eottae_talkroom_parse_owner_update_input(array $post)
    {
        $join_type = isset($post['join_type']) ? trim((string) $post['join_type']) : 'open';
        if (!in_array($join_type, array('open', 'approval'), true)) {
            $join_type = 'open';
        }

        return array(
            'room_description' => eottae_talkroom_clean_text($post['room_description'] ?? '', 500),
            'room_detail'      => eottae_talkroom_clean_text($post['room_detail'] ?? '', 5000),
            'emoji'            => eottae_talkroom_resolve_emoji($post['emoji'] ?? '', isset($post['category']) ? (string) $post['category'] : ''),
            'rules'            => eottae_talkroom_clean_text($post['rules'] ?? '', 5000),
            'contact'          => eottae_talkroom_clean_text($post['contact'] ?? '', 255),
            'join_type'        => $join_type,
            'room_notice'      => eottae_talkroom_clean_text($post['room_notice'] ?? '', 2000),
        );
    }
}

if (!function_exists('eottae_talkroom_validate_owner_update')) {
    function eottae_talkroom_validate_owner_update(array $data)
    {
        $errors = array();

        $desc_len = function_exists('mb_strlen') ? mb_strlen($data['room_description'], 'UTF-8') : strlen($data['room_description']);
        if ($desc_len < 5) {
            $errors[] = '톡방 한 줄 소개를 5자 이상 입력해 주세요.';
        }

        $detail_len = function_exists('mb_strlen') ? mb_strlen($data['room_detail'], 'UTF-8') : strlen($data['room_detail']);
        if ($detail_len < 10) {
            $errors[] = '톡방 상세 설명을 10자 이상 입력해 주세요.';
        }

        $rules_len = function_exists('mb_strlen') ? mb_strlen($data['rules'], 'UTF-8') : strlen($data['rules']);
        if ($rules_len < 5) {
            $errors[] = '운영 규칙을 5자 이상 입력해 주세요.';
        }

        if ($data['contact'] === '') {
            $errors[] = '연락처를 입력해 주세요.';
        }

        return $errors;
    }
}

if (!function_exists('eottae_talkroom_update_room_by_owner')) {
    function eottae_talkroom_update_room_by_owner($room_id, array $data, $manager_mb_id, $is_super_admin = false)
    {
        $check = eottae_talkroom_assert_manage_access($room_id, $manager_mb_id, $is_super_admin);
        if (empty($check['ok'])) {
            return $check;
        }

        $errors = eottae_talkroom_validate_owner_update($data);
        if (!empty($errors)) {
            return array('ok' => false, 'message' => $errors[0]);
        }

        $tables = eottae_talkroom_table_names();
        $room_id = (int) $room_id;
        $now = G5_TIME_YMDHIS;

        $ok = (bool) sql_query("
            UPDATE `{$tables['rooms']}` SET
                room_description = '".sql_escape_string($data['room_description'])."',
                room_detail = '".sql_escape_string($data['room_detail'])."',
                emoji = '".sql_escape_string($data['emoji'])."',
                rules = '".sql_escape_string($data['rules'])."',
                contact = '".sql_escape_string($data['contact'])."',
                join_type = '".sql_escape_string($data['join_type'])."',
                room_notice = '".sql_escape_string($data['room_notice'])."',
                updated_at = '{$now}'
            WHERE room_id = '{$room_id}'
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '톡방 정보 저장에 실패했습니다.');
        }

        eottae_talkroom_write_log($room_id, $manager_mb_id, 'update_room', 'room', $room_id, 'owner_update');

        return array('ok' => true, 'message' => '톡방 정보를 저장했습니다.');
    }
}

if (!function_exists('eottae_talkroom_save_room_notice')) {
    function eottae_talkroom_save_room_notice($room_id, $notice, $manager_mb_id, $is_super_admin = false)
    {
        $check = eottae_talkroom_assert_manage_access($room_id, $manager_mb_id, $is_super_admin);
        if (empty($check['ok'])) {
            return $check;
        }

        $notice = eottae_talkroom_clean_text($notice, 2000);
        $tables = eottae_talkroom_table_names();
        $room_id = (int) $room_id;
        $now = G5_TIME_YMDHIS;

        $ok = (bool) sql_query("
            UPDATE `{$tables['rooms']}` SET
                room_notice = '".sql_escape_string($notice)."',
                updated_at = '{$now}'
            WHERE room_id = '{$room_id}'
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '공지 저장에 실패했습니다.');
        }

        eottae_talkroom_write_log($room_id, $manager_mb_id, 'save_notice', 'room', $room_id, '');

        return array('ok' => true, 'message' => $notice === '' ? '공지를 삭제했습니다.' : '공지를 저장했습니다.');
    }
}

if (!function_exists('eottae_talkroom_is_super_member')) {
    function eottae_talkroom_is_super_member($mb_id)
    {
        global $config;

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return false;
        }

        if ($mb_id === ($config['cf_admin'] ?? '')) {
            return true;
        }

        if (!function_exists('get_member')) {
            return false;
        }

        $mb = get_member($mb_id, 'mb_level');
        if (!is_array($mb) || empty($mb['mb_id'])) {
            return false;
        }

        return (int) ($mb['mb_level'] ?? 0) >= 10;
    }
}

if (!function_exists('eottae_talkroom_can_kick_target')) {
    function eottae_talkroom_can_kick_target(array $room, array $target_row, $manager_mb_id, $is_super_admin = false)
    {
        $manager_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $manager_mb_id);
        $target_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($target_row['mb_id'] ?? ''));

        if ($manager_mb_id === '' || $target_mb_id === '') {
            return array('ok' => false, 'message' => '회원 정보가 올바르지 않습니다.');
        }

        if ($manager_mb_id === $target_mb_id) {
            return array('ok' => false, 'message' => '자기 자신은 강퇴할 수 없습니다.');
        }

        if (($target_row['role'] ?? '') === 'owner' || $target_mb_id === ($room['owner_mb_id'] ?? '')) {
            return array('ok' => false, 'message' => '방장은 강퇴할 수 없습니다.');
        }

        if (!$is_super_admin && eottae_talkroom_is_super_member($target_mb_id)) {
            return array('ok' => false, 'message' => '최고관리자는 강퇴할 수 없습니다.');
        }

        $target_status = trim((string) ($target_row['status'] ?? ''));
        if (!in_array($target_status, array('active', 'pending'), true)) {
            return array('ok' => false, 'message' => '강퇴할 수 없는 회원 상태입니다.');
        }

        if (!$is_super_admin && ($target_row['role'] ?? '') !== 'member') {
            return array('ok' => false, 'message' => '일반 참여자만 강퇴할 수 있습니다.');
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_talkroom_kick_member')) {
    function eottae_talkroom_kick_member($room_id, $member_id, $manager_mb_id, $is_super_admin, $reason, $can_rejoin = 1)
    {
        $check = eottae_talkroom_assert_manage_access($room_id, $manager_mb_id, $is_super_admin);
        if (empty($check['ok'])) {
            return $check;
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array('ok' => false, 'message' => '운영 중인 톡방을 찾을 수 없습니다.');
        }

        $room_id = (int) $room_id;
        $member_id = (int) $member_id;
        $tables = eottae_talkroom_table_names();
        $row = sql_fetch("
            SELECT *
            FROM `{$tables['members']}`
            WHERE id = '{$member_id}'
              AND room_id = '{$room_id}'
        ", false);

        if (empty($row['id'])) {
            return array('ok' => false, 'message' => '강퇴할 회원을 찾을 수 없습니다.');
        }

        $target_check = eottae_talkroom_can_kick_target($room, $row, $manager_mb_id, $is_super_admin);
        if (empty($target_check['ok'])) {
            return $target_check;
        }

        $reason = eottae_talkroom_clean_text($reason, 1000);
        if ($reason === '') {
            return array('ok' => false, 'message' => '강퇴 사유를 입력해 주세요.');
        }

        $can_rejoin = ((int) $can_rejoin === 1) ? 1 : 0;
        $now = G5_TIME_YMDHIS;
        $manager_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $manager_mb_id);

        $ok = (bool) sql_query("
            UPDATE `{$tables['members']}` SET
                status = 'kicked',
                kicked_at = '{$now}',
                kicked_by = '".sql_escape_string($manager_mb_id)."',
                kicked_reason = '".sql_escape_string($reason)."',
                can_rejoin = '{$can_rejoin}'
            WHERE id = '{$member_id}'
              AND room_id = '{$room_id}'
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '강퇴 처리에 실패했습니다.');
        }

        eottae_talkroom_write_log($room_id, $manager_mb_id, 'kick_member', 'member', $member_id, $row['mb_id'].'|rejoin:'.$can_rejoin);

        return array('ok' => true, 'message' => '회원을 강퇴했습니다.');
    }
}

if (!function_exists('eottae_talkroom_unkick_member')) {
    function eottae_talkroom_unkick_member($room_id, $member_id, $admin_mb_id, $status_after = 'left')
    {
        global $is_admin;
        if ($is_admin !== 'super') {
            return array('ok' => false, 'message' => '최고관리자만 강퇴 해제할 수 있습니다.');
        }

        $room_id = (int) $room_id;
        $member_id = (int) $member_id;
        $status_after = trim((string) $status_after);
        if (!in_array($status_after, array('left', 'active'), true)) {
            $status_after = 'left';
        }

        $tables = eottae_talkroom_table_names();
        $row = sql_fetch("
            SELECT *
            FROM `{$tables['members']}`
            WHERE id = '{$member_id}'
              AND room_id = '{$room_id}'
              AND status IN ('kicked', 'banned')
        ", false);

        if (empty($row['id'])) {
            return array('ok' => false, 'message' => '강퇴 해제할 회원을 찾을 수 없습니다.');
        }

        $now = G5_TIME_YMDHIS;
        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);
        $joined_at = ($status_after === 'active') ? $now : '0000-00-00 00:00:00';

        $ok = (bool) sql_query("
            UPDATE `{$tables['members']}` SET
                status = '".sql_escape_string($status_after)."',
                joined_at = '{$joined_at}',
                kicked_at = '0000-00-00 00:00:00',
                kicked_by = '',
                kicked_reason = '',
                can_rejoin = '1'
            WHERE id = '{$member_id}'
              AND room_id = '{$room_id}'
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '강퇴 해제에 실패했습니다.');
        }

        eottae_talkroom_write_log($room_id, $admin_mb_id, 'unkick_member', 'member', $member_id, $status_after);

        if ($status_after === 'active' && function_exists('eottae_talkroom_ai_schedule_welcome')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-welcome.lib.php';
            eottae_talkroom_ai_schedule_welcome($room_id, $row['mb_id']);
        }

        $message = ($status_after === 'active')
            ? '강퇴를 해제하고 참여 상태로 복구했습니다.'
            : '강퇴를 해제했습니다. 회원이 다시 참여 신청할 수 있습니다.';

        return array('ok' => true, 'message' => $message);
    }
}

if (!function_exists('eottae_talkroom_admin_kicked_url')) {
    function eottae_talkroom_admin_kicked_url()
    {
        return G5_URL.'/page/eottae-admin-talk-kicked.php';
    }
}

if (!function_exists('eottae_talkroom_admin_kicked_count')) {
    function eottae_talkroom_admin_kicked_count()
    {
        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['members'])) {
            return 0;
        }

        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$tables['members']}`
            WHERE status IN ('kicked', 'banned')
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_talkroom_admin_fetch_kicked_rows')) {
    /**
     * @return resource|false
     */
    function eottae_talkroom_admin_fetch_kicked_rows($limit, $with_member = true)
    {
        $tables = eottae_talkroom_table_names();
        $limit = max(1, min(300, (int) $limit));

        if ($with_member) {
            $member_table = eottae_talkroom_sql_member_table();
            $sql = "
                SELECT tm.*, m.mb_nick AS target_nick, kb.mb_nick AS kicked_by_nick,
                       r.room_name, r.emoji, r.status AS room_status
                FROM `{$tables['members']}` tm
                INNER JOIN `{$tables['rooms']}` r ON r.room_id = tm.room_id
                LEFT JOIN `{$member_table}` m ON m.mb_id = tm.mb_id
                LEFT JOIN `{$member_table}` kb ON kb.mb_id = tm.kicked_by
                WHERE tm.status IN ('kicked', 'banned')
                ORDER BY tm.kicked_at DESC, tm.id DESC
                LIMIT {$limit}
            ";
        } else {
            $sql = "
                SELECT tm.*, '' AS target_nick, '' AS kicked_by_nick,
                       r.room_name, r.emoji, r.status AS room_status
                FROM `{$tables['members']}` tm
                INNER JOIN `{$tables['rooms']}` r ON r.room_id = tm.room_id
                WHERE tm.status IN ('kicked', 'banned')
                ORDER BY tm.kicked_at DESC, tm.id DESC
                LIMIT {$limit}
            ";
        }

        return sql_query($sql, false);
    }
}

if (!function_exists('eottae_talkroom_admin_list_kicked_members')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_admin_list_kicked_members($limit = 200)
    {
        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['members']) || !eottae_talkroom_table_exists($tables['rooms'])) {
            return array();
        }

        $limit = max(1, min(300, (int) $limit));
        $result = eottae_talkroom_admin_fetch_kicked_rows($limit, true);
        if (!$result) {
            $result = eottae_talkroom_admin_fetch_kicked_rows($limit, false);
        }

        $rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $target_nick = trim((string) ($row['target_nick'] ?? ''));
                if ($target_nick === '') {
                    $target_nick = (string) ($row['mb_id'] ?? '');
                }

                $kicked_by_nick = trim((string) ($row['kicked_by_nick'] ?? ''));
                if ($kicked_by_nick === '') {
                    $kicked_by_nick = (string) ($row['kicked_by'] ?? '');
                }

                $rows[] = array(
                    'id'             => (int) $row['id'],
                    'room_id'        => (int) $row['room_id'],
                    'room_name'      => get_text($row['room_name'] ?? ''),
                    'emoji'          => eottae_talkroom_display_emoji($row['emoji'] ?? ''),
                    'room_status'    => trim((string) ($row['room_status'] ?? '')),
                    'mb_id'          => get_text($row['mb_id'] ?? ''),
                    'mb_nick'        => get_text($target_nick),
                    'kicked_at'      => trim((string) ($row['kicked_at'] ?? '')),
                    'kicked_by'      => get_text($row['kicked_by'] ?? ''),
                    'kicked_by_nick' => get_text($kicked_by_nick),
                    'kicked_reason'  => get_text($row['kicked_reason'] ?? ''),
                    'can_rejoin'     => (int) ($row['can_rejoin'] ?? 1),
                    'can_rejoin_label' => ((int) ($row['can_rejoin'] ?? 1) === 1) ? '허용' : '불허',
                    'manage_href'    => eottae_talkroom_owner_manage_url((int) $row['room_id']),
                    'enter_href'     => eottae_talkroom_enter_url((int) $row['room_id']),
                );
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_assert_talkroom_member_action')) {
    function eottae_talkroom_assert_talkroom_member_action(array $board, $room_id, $mb_id, $is_super_admin = false)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1) {
            return array('ok' => true, 'message' => '');
        }

        if ($is_super_admin) {
            return array('ok' => true, 'message' => '');
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array('ok' => false, 'message' => '운영 중인 톡방이 아닙니다.', 'redirect' => eottae_talkroom_list_url());
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        if ($member_row && eottae_talkroom_is_kicked_status($member_row['status'] ?? '')) {
            return array('ok' => false, 'message' => '강퇴된 톡방에서는 글쓰기와 댓글 작성이 불가합니다.', 'redirect' => eottae_talkroom_enter_url($room_id));
        }

        if (!eottae_talkroom_can_write_posts($room, $member_row)) {
            return array('ok' => false, 'message' => '톡방 참여자만 이용할 수 있습니다.', 'redirect' => eottae_talkroom_enter_url($room_id));
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_talkroom_soft_delete_status')) {
    function eottae_talkroom_soft_delete_status()
    {
        return 'deleted';
    }
}

if (!function_exists('eottae_talkroom_post_visible_sql')) {
    function eottae_talkroom_post_visible_sql($alias = '')
    {
        $prefix = $alias !== '' ? $alias.'.' : '';

        return " ({$prefix}wr_2 = '' OR {$prefix}wr_2 IS NULL) ";
    }
}

if (!function_exists('eottae_talkroom_is_post_deleted')) {
    function eottae_talkroom_is_post_deleted($write_row)
    {
        if (!is_array($write_row)) {
            return false;
        }

        return trim((string) ($write_row['wr_2'] ?? '')) === eottae_talkroom_soft_delete_status();
    }
}

if (!function_exists('eottae_talkroom_get_write_room_id')) {
    function eottae_talkroom_get_write_room_id($write_row)
    {
        if (!is_array($write_row)) {
            return 0;
        }

        return (int) ($write_row['wr_1'] ?? 0);
    }
}

if (!function_exists('eottae_talkroom_write_table')) {
    function eottae_talkroom_write_table()
    {
        if (!eottae_talkroom_board_exists()) {
            return '';
        }

        $bo = preg_replace('/[^a-z0-9_]/', '', eottae_talkroom_board_table());

        return G5_TABLE_PREFIX.'write_'.$bo;
    }
}

if (!function_exists('eottae_talkroom_user_can_delete_write')) {
    function eottae_talkroom_user_can_delete_write($write, $board, $mb_id = '', $is_super_admin = false)
    {
        if (!is_array($write) || empty($write['wr_id']) || !is_array($board)) {
            return false;
        }

        if (empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return false;
        }

        $room_id = eottae_talkroom_get_write_room_id($write);
        if ($room_id < 1) {
            return false;
        }

        if ($is_super_admin) {
            return true;
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return false;
        }

        if ($mb_id === ($write['mb_id'] ?? '')) {
            return true;
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return false;
        }

        return eottae_talkroom_is_room_owner($room, $mb_id);
    }
}

if (!function_exists('eottae_talkroom_user_can_edit_write')) {
    function eottae_talkroom_user_can_edit_write($write, $board, $mb_id = '', $is_super_admin = false)
    {
        if (!is_array($write) || empty($write['wr_id']) || !is_array($board)) {
            return false;
        }

        if (empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return false;
        }

        if ($is_super_admin) {
            return true;
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return false;
        }

        $room_id = eottae_talkroom_get_write_room_id($write);
        if ($room_id < 1) {
            return false;
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return false;
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        if (!eottae_talkroom_can_write_posts($room, $member_row)) {
            return false;
        }

        return $mb_id === ($write['mb_id'] ?? '');
    }
}

if (!function_exists('eottae_talkroom_can_comment_on_post')) {
    function eottae_talkroom_can_comment_on_post($board, $parent_write, $mb_id = '', $is_super_admin = false)
    {
        if (!is_array($board) || empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return null;
        }

        if (!is_array($parent_write) || empty($parent_write['wr_id'])) {
            return false;
        }

        if (eottae_talkroom_is_post_deleted($parent_write)) {
            return false;
        }

        if ($is_super_admin) {
            return true;
        }

        $room_id = eottae_talkroom_get_write_room_id($parent_write);
        if ($room_id < 1) {
            return false;
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return false;
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);

        return eottae_talkroom_can_write_posts($room, $member_row);
    }
}

if (!function_exists('eottae_talkroom_soft_delete_write')) {
    function eottae_talkroom_soft_delete_write($write, $board, $deleter_mb_id, $reason = '')
    {
        if (!is_array($write) || empty($write['wr_id']) || !is_array($board)) {
            return array('ok' => false, 'message' => '삭제할 글을 찾을 수 없습니다.');
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table)) {
            return array('ok' => false, 'message' => '게시판을 찾을 수 없습니다.');
        }

        if (eottae_talkroom_is_post_deleted($write)) {
            return array('ok' => false, 'message' => '이미 삭제된 글입니다.');
        }

        $wr_id = (int) $write['wr_id'];
        $room_id = eottae_talkroom_get_write_room_id($write);
        $deleter_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $deleter_mb_id);
        $reason = eottae_talkroom_clean_text($reason, 500);
        $now = G5_TIME_YMDHIS;
        $deleted_status = eottae_talkroom_soft_delete_status();
        $is_comment = !empty($write['wr_is_comment']);

        $ok = (bool) sql_query("
            UPDATE `{$write_table}` SET
                wr_2 = '".sql_escape_string($deleted_status)."',
                wr_3 = '".sql_escape_string($deleter_mb_id)."',
                wr_4 = '".sql_escape_string($now)."',
                wr_5 = '".sql_escape_string($reason)."'
            WHERE wr_id = '{$wr_id}'
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '삭제 처리에 실패했습니다.');
        }

        global $g5;
        $bo_table = preg_replace('/[^a-z0-9_]/', '', eottae_talkroom_board_table());
        sql_query(" DELETE FROM {$g5['board_new_table']} WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}' ", false);

        if ($is_comment) {
            $parent_id = (int) ($write['wr_parent'] ?? 0);
            if ($parent_id > 0) {
                sql_query(" UPDATE `{$write_table}` SET wr_comment = GREATEST(wr_comment - 1, 0) WHERE wr_id = '{$parent_id}' ", false);
            }
            sql_query(" UPDATE {$g5['board_table']} SET bo_count_comment = GREATEST(bo_count_comment - 1, 0) WHERE bo_table = '{$bo_table}' ", false);
        } else {
            sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = GREATEST(bo_count_write - 1, 0) WHERE bo_table = '{$bo_table}' ", false);
        }

        if ($room_id > 0) {
            eottae_talkroom_write_log(
                $room_id,
                $deleter_mb_id,
                $is_comment ? 'delete_comment' : 'delete_post',
                $is_comment ? 'comment' : 'post',
                $wr_id,
                $reason
            );
        }

        delete_cache_latest($bo_table);

        return array(
            'ok'        => true,
            'message'   => $is_comment ? '댓글을 삭제했습니다.' : '게시글을 삭제했습니다.',
            'room_id'   => $room_id,
            'wr_id'     => $wr_id,
            'parent_id' => $is_comment ? (int) ($write['wr_parent'] ?? 0) : $wr_id,
        );
    }
}

if (!function_exists('eottae_talkroom_handle_soft_delete_post')) {
    function eottae_talkroom_handle_soft_delete_post($write, $board)
    {
        global $member, $is_admin, $page, $qstr;

        if (!is_array($write) || empty($write['wr_id']) || !is_array($board)) {
            return false;
        }

        if (empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table']) || !empty($write['wr_is_comment'])) {
            return false;
        }

        $is_super = ($is_admin === 'super');
        $mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';
        if (!eottae_talkroom_user_can_delete_write($write, $board, $mb_id, $is_super)) {
            alert('게시글을 삭제할 권한이 없습니다.');
        }

        $result = eottae_talkroom_soft_delete_write($write, $board, $mb_id, '');
        if (empty($result['ok'])) {
            alert($result['message']);
        }

        $bo_table = preg_replace('/[^a-z0-9_]/', '', $board['bo_table']);
        $room_id = (int) ($result['room_id'] ?? 0);
        $redirect = $room_id > 0
            ? eottae_talkroom_enter_url($room_id)
            : G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&page='.$page.$qstr;

        goto_url(short_url_clean($redirect));
    }
}

if (!function_exists('eottae_talkroom_handle_soft_delete_comment')) {
    function eottae_talkroom_handle_soft_delete_comment($write, $board)
    {
        global $member, $is_admin, $page, $qstr;

        if (!is_array($write) || empty($write['wr_id']) || !is_array($board)) {
            return false;
        }

        if (empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table']) || empty($write['wr_is_comment'])) {
            return false;
        }

        $is_super = ($is_admin === 'super');
        $mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';
        if (!eottae_talkroom_user_can_delete_write($write, $board, $mb_id, $is_super)) {
            alert('댓글을 삭제할 권한이 없습니다.');
        }

        $result = eottae_talkroom_soft_delete_write($write, $board, $mb_id, '');
        if (empty($result['ok'])) {
            alert($result['message']);
        }

        $bo_table = preg_replace('/[^a-z0-9_]/', '', $board['bo_table']);
        $parent_id = (int) ($result['parent_id'] ?? 0);
        goto_url(short_url_clean(G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$parent_id.'&page='.$page.$qstr));
    }
}

if (!function_exists('eottae_talkroom_apply_view_links')) {
    function eottae_talkroom_apply_view_links($board, $write, $member, $is_admin, $bo_table, $wr_id, $page, $qstr, &$update_href, &$delete_href)
    {
        if (!is_array($board) || !is_array($write) || empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }

        if (!empty($write['wr_is_comment'])) {
            return;
        }

        $is_super = ($is_admin === 'super');
        $mb_id = is_array($member) ? ($member['mb_id'] ?? '') : '';
        $update_href = '';
        $delete_href = '';

        if (eottae_talkroom_user_can_edit_write($write, $board, $mb_id, $is_super)) {
            $update_href = short_url_clean(G5_BBS_URL.'/write.php?w=u&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;page='.$page.$qstr);
        }

        if (eottae_talkroom_user_can_delete_write($write, $board, $mb_id, $is_super)) {
            set_session('ss_delete_token', $token = uniqid((string) time()));
            $delete_href = G5_BBS_URL.'/delete.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;token='.$token.'&amp;page='.$page.urldecode($qstr);
        }
    }
}

if (!function_exists('eottae_talkroom_comment_delete_link')) {
    function eottae_talkroom_comment_delete_link($comment_row, $board, $parent_write, $member, $is_admin, $page, $qstr)
    {
        if (!is_array($comment_row) || !is_array($board) || empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return '';
        }

        $is_super = ($is_admin === 'super');
        $mb_id = is_array($member) ? ($member['mb_id'] ?? '') : '';
        if (!eottae_talkroom_user_can_delete_write($comment_row, $board, $mb_id, $is_super)) {
            return '';
        }

        set_session('ss_delete_comment_'.$comment_row['wr_id'].'_token', $token = uniqid((string) time()));
        $bo_table = preg_replace('/[^a-z0-9_]/', '', $board['bo_table']);

        return G5_BBS_URL.'/delete_comment.php?bo_table='.$bo_table.'&amp;comment_id='.$comment_row['wr_id'].'&amp;token='.$token.'&amp;page='.$page.$qstr;
    }
}

if (!function_exists('eottae_talkroom_format_deleted_comment_content')) {
    function eottae_talkroom_format_deleted_comment_content($comment_row, $is_super_admin = false)
    {
        if (!eottae_talkroom_is_post_deleted($comment_row)) {
            return null;
        }

        if ($is_super_admin) {
            $by = get_text($comment_row['wr_3'] ?? '');
            $at = trim((string) ($comment_row['wr_4'] ?? ''));
            $meta = trim($by.($at !== '' ? ' · '.$at : ''));

            return array(
                'content'  => '[관리자 확인] 삭제된 댓글'.($meta !== '' ? ' ('.$meta.')' : ''),
                'content1' => $comment_row['wr_content'] ?? '',
            );
        }

        return array(
            'content'  => '삭제된 댓글입니다.',
            'content1' => '삭제된 댓글입니다.',
        );
    }
}

if (!function_exists('eottae_talkroom_assert_write_update_access')) {
    function eottae_talkroom_assert_write_update_access($board, $wr_id, $w)
    {
        global $is_member, $member, $is_admin, $write;

        if (empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }

        if (!$is_member || empty($member['mb_id'])) {
            alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.$_SERVER['REQUEST_URI']));
        }

        $is_super = ($is_admin === 'super');
        $target = null;
        $room_id = 0;

        if ($w === 'u' || $w === 'r') {
            $target = is_array($write) && !empty($write['wr_id']) ? $write : null;
            if (!$target && $wr_id > 0) {
                $write_table = eottae_talkroom_write_table();
                $target = sql_fetch(" SELECT * FROM `{$write_table}` WHERE wr_id = '".(int) $wr_id."' ", false);
            }
            if (!$target || empty($target['wr_id'])) {
                alert('글이 존재하지 않습니다.');
            }
            if (eottae_talkroom_is_post_deleted($target) && !$is_super) {
                alert('삭제된 글은 수정할 수 없습니다.');
            }
            $room_id = eottae_talkroom_get_write_room_id($target);
        } else {
            $room_id = isset($_POST['wr_1']) ? (int) $_POST['wr_1'] : (int) ($_REQUEST['wr_1'] ?? 0);
        }

        if ($room_id < 1) {
            alert('톡방 정보가 없습니다. 톡방 상세 페이지에서 글쓰기를 이용해 주세요.', eottae_talkroom_list_url());
        }

        if ($w === 'u') {
            if (!eottae_talkroom_user_can_edit_write($target, $board, $member['mb_id'], $is_super)) {
                alert('글을 수정할 권한이 없습니다.', eottae_talkroom_enter_url($room_id));
            }

            return;
        }

        if ($w === 'r') {
            alert('톡방 게시판에서는 답글을 사용할 수 없습니다.', eottae_talkroom_enter_url($room_id));
        }

        $check = eottae_talkroom_assert_talkroom_member_action($board, $room_id, $member['mb_id'], $is_super);
        if (empty($check['ok'])) {
            $redirect = !empty($check['redirect']) ? $check['redirect'] : eottae_talkroom_enter_url($room_id);
            alert($check['message'], $redirect);
        }
    }
}

if (!function_exists('eottae_talkroom_assert_comment_update_access')) {
    function eottae_talkroom_assert_comment_update_access($board, $wr, $wr_id, $w)
    {
        global $is_member, $member, $is_admin;

        if (empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }

        if (!$is_member || empty($member['mb_id'])) {
            alert('로그인 후 댓글을 작성할 수 있습니다.');
        }

        $is_super = ($is_admin === 'super');
        $room_id = eottae_talkroom_get_write_room_id($wr);

        if ($w === 'cu') {
            $write_table = eottae_talkroom_write_table();
            $comment_id = isset($_POST['comment_id']) ? (int) $_POST['comment_id'] : 0;
            $comment = $comment_id > 0
                ? sql_fetch(" SELECT * FROM `{$write_table}` WHERE wr_id = '{$comment_id}' ", false)
                : null;
            if (!$comment || empty($comment['wr_id'])) {
                alert('댓글을 찾을 수 없습니다.');
            }
            if (eottae_talkroom_is_post_deleted($comment)) {
                alert('삭제된 댓글은 수정할 수 없습니다.');
            }
            if (!$is_super && ($member['mb_id'] ?? '') !== ($comment['mb_id'] ?? '')) {
                alert('자신의 댓글만 수정할 수 있습니다.');
            }

            $check = eottae_talkroom_assert_talkroom_member_action($board, $room_id, $member['mb_id'], $is_super);
            if (empty($check['ok'])) {
                alert($check['message'], !empty($check['redirect']) ? $check['redirect'] : eottae_talkroom_enter_url($room_id));
            }

            return;
        }

        if ($w !== 'c') {
            return;
        }

        if (eottae_talkroom_is_post_deleted($wr)) {
            alert('삭제된 게시글에는 댓글을 작성할 수 없습니다.');
        }

        $check = eottae_talkroom_assert_talkroom_member_action($board, $room_id, $member['mb_id'], $is_super);
        if (empty($check['ok'])) {
            alert($check['message'], !empty($check['redirect']) ? $check['redirect'] : eottae_talkroom_enter_url($room_id));
        }
    }
}

if (!function_exists('eottae_talkroom_guard_board_list_access')) {
    function eottae_talkroom_guard_board_list_access($board, $wr_id)
    {
        global $is_admin, $board;

        if (!is_array($board) || empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }

        if ($is_admin === 'super') {
            return;
        }

        $board['bo_use_list_view'] = false;

        $wr_id = (int) $wr_id;
        if ($wr_id > 0) {
            return;
        }

        $room_id = isset($_GET['room_id']) ? (int) $_GET['room_id'] : 0;
        if ($room_id > 0) {
            goto_url(eottae_talkroom_enter_url($room_id));
        }

        alert('톡방 게시글은 각 톡방 페이지에서 확인해 주세요.', eottae_talkroom_list_url());
    }
}

if (!function_exists('eottae_talkroom_guard_board_view')) {
    function eottae_talkroom_guard_board_view($board, $write, $wr_id)
    {
        global $is_admin;

        if (empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }

        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || !is_array($write) || empty($write['wr_id'])) {
            return;
        }

        $is_super = ($is_admin === 'super');
        if (eottae_talkroom_is_post_deleted($write)) {
            if (!$is_super) {
                $room_id = eottae_talkroom_get_write_room_id($write);
                alert('삭제된 게시글입니다.', $room_id > 0 ? eottae_talkroom_enter_url($room_id) : eottae_talkroom_list_url());
            }

            return;
        }

        eottae_talkroom_on_board_head_private_view($board, $write, $wr_id);
    }
}

if (!function_exists('eottae_talkroom_on_board_head_private_view')) {
    function eottae_talkroom_on_board_head_private_view($board, $write, $wr_id)
    {
        global $is_member, $member, $is_admin;

        if (empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }

        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || !is_array($write) || empty($write['wr_id'])) {
            return;
        }

        if ($is_admin === 'super') {
            return;
        }

        $room_id = eottae_talkroom_get_write_room_id($write);
        if ($room_id < 1) {
            return;
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return;
        }

        $member_row = null;
        if ($is_member && !empty($member['mb_id'])) {
            $member_row = eottae_talkroom_get_member_row($room_id, $member['mb_id']);
        }

        if (($room['visibility'] ?? 'public') === 'private' && !eottae_talkroom_can_view_posts($room, $member_row)) {
            if (!$is_member || empty($member['mb_id'])) {
                $return_url = G5_BBS_URL.'/board.php?bo_table='.preg_replace('/[^a-z0-9_]/', '', $board['bo_table']).'&wr_id='.$wr_id;
                $login_url = function_exists('eottae_login_url')
                    ? eottae_login_url($return_url)
                    : G5_BBS_URL.'/login.php?url='.urlencode($return_url);
                alert('비공개 톡방 게시글은 로그인 후 참여자만 열람할 수 있습니다.', $login_url);
            }

            alert('비공개 톡방 게시글은 참여자만 열람할 수 있습니다.', eottae_talkroom_enter_url($room_id));
        }
    }
}

if (!function_exists('eottae_talkroom_report_reasons')) {
    function eottae_talkroom_report_reasons()
    {
        return array(
            'ad_spam'   => '광고/도배',
            'abuse'     => '욕설/비방',
            'privacy'   => '개인정보 노출',
            'scam'      => '사기 의심',
            'illegal'   => '음란/불법',
            'off_topic' => '톡방 주제와 무관',
            'etc'       => '기타',
        );
    }
}

if (!function_exists('eottae_talkroom_report_reason_label')) {
    function eottae_talkroom_report_reason_label($reason)
    {
        $reasons = eottae_talkroom_report_reasons();

        return isset($reasons[$reason]) ? $reasons[$reason] : get_text($reason);
    }
}

if (!function_exists('eottae_talkroom_report_status_label')) {
    function eottae_talkroom_report_status_label($status)
    {
        $map = array(
            'pending'   => '접수',
            'reviewed'  => '확인',
            'dismissed' => '기각',
            'resolved'  => '처리완료',
        );

        return isset($map[$status]) ? $map[$status] : get_text($status);
    }
}

if (!function_exists('eottae_talkroom_report_status_class')) {
    function eottae_talkroom_report_status_class($status)
    {
        $map = array(
            'pending'   => 'talk-apply-status--pending',
            'reviewed'  => 'talk-apply-status--approved',
            'dismissed' => 'talk-apply-status--rejected',
            'resolved'  => 'talk-apply-status--approved',
        );

        return isset($map[$status]) ? $map[$status] : 'talk-apply-status--pending';
    }
}

if (!function_exists('eottae_talkroom_report_token')) {
    function eottae_talkroom_report_token($regenerate = false)
    {
        $token = get_session('eottae_talkroom_report_token');
        if ($regenerate || $token === '' || $token === null) {
            try {
                $token = bin2hex(random_bytes(16));
            } catch (Exception $e) {
                $token = md5(uniqid((string) mt_rand(), true));
            }
            set_session('eottae_talkroom_report_token', $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_talkroom_verify_report_token')) {
    function eottae_talkroom_verify_report_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_talkroom_report_token');

        return $token !== '' && $session_token !== '' && hash_equals($session_token, $token);
    }
}

if (!function_exists('eottae_talkroom_owner_reports_url')) {
    function eottae_talkroom_owner_reports_url($room_id)
    {
        return G5_URL.'/page/eottae-talk-reports.php?room_id='.(int) $room_id;
    }
}

if (!function_exists('eottae_talkroom_admin_reports_url')) {
    function eottae_talkroom_admin_reports_url($status = 'pending')
    {
        $url = G5_URL.'/page/eottae-admin-talk-reports.php';
        if ($status !== '' && $status !== 'all') {
            $url .= '?status='.urlencode($status);
        }

        return $url;
    }
}

if (!function_exists('eottae_talkroom_get_report_target_write')) {
    function eottae_talkroom_get_report_target_write($target_type, $target_id)
    {
        $target_type = trim((string) $target_type);
        $target_id = (int) $target_id;
        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || $target_id < 1 || !in_array($target_type, array('post', 'comment'), true)) {
            return null;
        }

        $is_comment = ($target_type === 'comment') ? 1 : 0;
        $row = sql_fetch("
            SELECT *
            FROM `{$write_table}`
            WHERE wr_id = '{$target_id}'
              AND wr_is_comment = '{$is_comment}'
        ", false);

        return !empty($row['wr_id']) ? $row : null;
    }
}

if (!function_exists('eottae_talkroom_has_reported_target')) {
    function eottae_talkroom_has_reported_target($reporter_mb_id, $target_type, $target_id)
    {
        $reporter_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $reporter_mb_id);
        $target_type = trim((string) $target_type);
        $target_id = (int) $target_id;
        if ($reporter_mb_id === '' || $target_id < 1) {
            return false;
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['reports'])) {
            return false;
        }

        $row = sql_fetch("
            SELECT report_id
            FROM `{$tables['reports']}`
            WHERE reporter_mb_id = '".sql_escape_string($reporter_mb_id)."'
              AND target_type = '".sql_escape_string($target_type)."'
              AND target_id = '{$target_id}'
            LIMIT 1
        ", false);

        return !empty($row['report_id']);
    }
}

if (!function_exists('eottae_talkroom_can_submit_report')) {
    function eottae_talkroom_can_submit_report($board, $write, $reporter_mb_id, $target_type, $target_id, $parent_write = null, $is_super_admin = false)
    {
        if ($is_super_admin) {
            return array('ok' => false, 'message' => '관리자는 신고할 수 없습니다.');
        }

        $reporter_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $reporter_mb_id);
        if ($reporter_mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 신고할 수 있습니다.');
        }

        if (empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return array('ok' => false, 'message' => '톡방 게시글만 신고할 수 있습니다.');
        }

        $target = eottae_talkroom_get_report_target_write($target_type, $target_id);
        if (!$target) {
            return array('ok' => false, 'message' => '신고 대상을 찾을 수 없습니다.');
        }

        if (eottae_talkroom_is_post_deleted($target)) {
            return array('ok' => false, 'message' => '삭제된 글은 신고할 수 없습니다.');
        }

        $room_id = 0;
        if ($target_type === 'comment' && is_array($parent_write)) {
            $room_id = eottae_talkroom_get_write_room_id($parent_write);
        } else {
            $room_id = eottae_talkroom_get_write_room_id($target);
        }
        if ($room_id < 1) {
            return array('ok' => false, 'message' => '톡방 정보가 없습니다.');
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array('ok' => false, 'message' => '운영 중인 톡방이 아닙니다.');
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $reporter_mb_id);
        if (!eottae_talkroom_can_write_posts($room, $member_row)) {
            return array('ok' => false, 'message' => '톡방 참여자만 신고할 수 있습니다.');
        }

        if ($reporter_mb_id === ($target['mb_id'] ?? '')) {
            return array('ok' => false, 'message' => '본인 글/댓글은 신고할 수 없습니다.');
        }

        if (eottae_talkroom_has_reported_target($reporter_mb_id, $target_type, $target_id)) {
            return array('ok' => false, 'message' => '이미 신고한 대상입니다.');
        }

        return array('ok' => true, 'message' => '', 'room_id' => $room_id);
    }
}

if (!function_exists('eottae_talkroom_submit_report')) {
    function eottae_talkroom_submit_report($room_id, $target_type, $target_id, $reporter_mb_id, $reason, $memo = '')
    {
        global $board;

        $room_id = (int) $room_id;
        $target_type = trim((string) $target_type);
        $target_id = (int) $target_id;
        $reasons = eottae_talkroom_report_reasons();
        if (!isset($reasons[$reason])) {
            return array('ok' => false, 'message' => '신고 사유를 선택해 주세요.');
        }

        $memo = eottae_talkroom_clean_text($memo, 500);
        if ($reason === 'etc' && $memo === '') {
            return array('ok' => false, 'message' => '기타 사유를 입력해 주세요.');
        }

        $target = eottae_talkroom_get_report_target_write($target_type, $target_id);
        $parent = null;
        if ($target_type === 'comment' && $target) {
            $write_table = eottae_talkroom_write_table();
            $parent = sql_fetch(" SELECT * FROM `{$write_table}` WHERE wr_id = '".(int) ($target['wr_parent'] ?? 0)."' ", false);
        }

        $board_row = is_array($board) ? $board : array('bo_table' => eottae_talkroom_board_table());
        $check = eottae_talkroom_can_submit_report($board_row, $target, $reporter_mb_id, $target_type, $target_id, $parent, false);
        if (empty($check['ok'])) {
            return $check;
        }

        if ((int) ($check['room_id'] ?? 0) !== $room_id) {
            return array('ok' => false, 'message' => '톡방 정보가 일치하지 않습니다.');
        }

        $tables = eottae_talkroom_table_names();
        $reporter_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $reporter_mb_id);
        $now = G5_TIME_YMDHIS;

        $ok = (bool) sql_query("
            INSERT INTO `{$tables['reports']}` SET
                room_id = '{$room_id}',
                target_type = '".sql_escape_string($target_type)."',
                target_id = '{$target_id}',
                reporter_mb_id = '".sql_escape_string($reporter_mb_id)."',
                reason = '".sql_escape_string($reason)."',
                memo = '".sql_escape_string($memo)."',
                status = 'pending',
                handled_by = '',
                handled_at = '0000-00-00 00:00:00',
                created_at = '{$now}'
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '신고 저장에 실패했습니다. 이미 신고했을 수 있습니다.');
        }

        $report_id = sql_insert_id();
        eottae_talkroom_write_log($room_id, $reporter_mb_id, 'report', $target_type, $target_id, $reason);

        if (function_exists('eottae_talkroom_notify_owner_report')) {
            eottae_talkroom_notify_owner_report($room_id, (int) $report_id, $target_type);
        }

        return array('ok' => true, 'message' => '신고가 접수되었습니다.', 'report_id' => (int) $report_id);
    }
}

if (!function_exists('eottae_talkroom_get_report')) {
    function eottae_talkroom_get_report($report_id)
    {
        $report_id = (int) $report_id;
        if ($report_id < 1) {
            return null;
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['reports'])) {
            return null;
        }

        $row = sql_fetch(" SELECT * FROM `{$tables['reports']}` WHERE report_id = '{$report_id}' ", false);

        return !empty($row['report_id']) ? $row : null;
    }
}

if (!function_exists('eottae_talkroom_format_report_row')) {
    function eottae_talkroom_format_report_row(array $row)
    {
        $member_table = G5_TABLE_PREFIX.'member';
        $reporter = sql_fetch(" SELECT mb_nick FROM `{$member_table}` WHERE mb_id = '".sql_escape_string($row['reporter_mb_id'] ?? '')."' ", false);
        $handler = sql_fetch(" SELECT mb_nick FROM `{$member_table}` WHERE mb_id = '".sql_escape_string($row['handled_by'] ?? '')."' ", false);
        $target = eottae_talkroom_get_report_target_write($row['target_type'] ?? '', $row['target_id'] ?? 0);
        $target_mb_id = $target['mb_id'] ?? '';
        $target_author = '';
        if ($target_mb_id !== '') {
            $author = sql_fetch(" SELECT mb_nick FROM `{$member_table}` WHERE mb_id = '".sql_escape_string($target_mb_id)."' ", false);
            $target_author = get_text($author['mb_nick'] ?? $target_mb_id);
        }

        $room_id = (int) ($row['room_id'] ?? 0);
        $room = eottae_talkroom_get_room($room_id);
        $target_label = ($row['target_type'] ?? '') === 'comment' ? '댓글' : '게시글';
        $target_preview = '';
        if ($target) {
            $target_preview = ($row['target_type'] ?? '') === 'comment'
                ? cut_str(strip_tags((string) ($target['wr_content'] ?? '')), 80)
                : get_text($target['wr_subject'] ?? cut_str(strip_tags((string) ($target['wr_content'] ?? '')), 80));
        }

        return array(
            'report_id'        => (int) $row['report_id'],
            'room_id'          => $room_id,
            'room_name'        => get_text($room['room_name'] ?? ''),
            'emoji'            => eottae_talkroom_display_emoji($room['emoji'] ?? ''),
            'target_type'      => trim((string) ($row['target_type'] ?? '')),
            'target_type_label'=> $target_label,
            'target_id'        => (int) ($row['target_id'] ?? 0),
            'target_preview'   => $target_preview,
            'target_mb_id'     => get_text($target_mb_id),
            'target_author'    => $target_author,
            'target_href'      => ($target && ($row['target_type'] ?? '') === 'post')
                ? eottae_talkroom_post_view_url((int) $row['target_id'], $room_id)
                : (($target && ($row['target_type'] ?? '') === 'comment')
                    ? eottae_talkroom_post_view_url((int) ($target['wr_parent'] ?? 0), $room_id).'#c_'.(int) $row['target_id']
                    : ''),
            'reporter_mb_id'   => get_text($row['reporter_mb_id'] ?? ''),
            'reporter_nick'    => get_text($reporter['mb_nick'] ?? ($row['reporter_mb_id'] ?? '')),
            'reason'           => trim((string) ($row['reason'] ?? '')),
            'reason_label'     => eottae_talkroom_report_reason_label($row['reason'] ?? ''),
            'memo'             => get_text($row['memo'] ?? ''),
            'status'           => trim((string) ($row['status'] ?? 'pending')),
            'status_label'     => eottae_talkroom_report_status_label($row['status'] ?? 'pending'),
            'status_class'     => eottae_talkroom_report_status_class($row['status'] ?? 'pending'),
            'handled_by'       => get_text($row['handled_by'] ?? ''),
            'handled_by_nick'  => get_text($handler['mb_nick'] ?? ($row['handled_by'] ?? '')),
            'handled_at'       => trim((string) ($row['handled_at'] ?? '')),
            'created_at'       => trim((string) ($row['created_at'] ?? '')),
            'is_pending'       => ($row['status'] ?? '') === 'pending',
        );
    }
}

if (!function_exists('eottae_talkroom_list_room_reports')) {
    function eottae_talkroom_list_room_reports($room_id, $status = 'all', $limit = 100)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1) {
            return array();
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['reports'])) {
            return array();
        }

        $limit = max(1, min(200, (int) $limit));
        $where = " WHERE room_id = '{$room_id}' ";
        $status = trim((string) $status);
        if ($status !== '' && $status !== 'all') {
            $where .= " AND status = '".sql_escape_string($status)."' ";
        }

        $result = sql_query("
            SELECT *
            FROM `{$tables['reports']}`
            {$where}
            ORDER BY
                CASE status WHEN 'pending' THEN 0 WHEN 'reviewed' THEN 1 ELSE 2 END,
                created_at DESC,
                report_id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $rows[] = eottae_talkroom_format_report_row($row);
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_admin_list_reports')) {
    function eottae_talkroom_admin_list_reports($status = 'pending', $limit = 200)
    {
        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['reports'])) {
            return array();
        }

        $limit = max(1, min(300, (int) $limit));
        $where = '';
        $status = trim((string) $status);
        if ($status !== '' && $status !== 'all') {
            $where = " WHERE status = '".sql_escape_string($status)."' ";
        }

        $result = sql_query("
            SELECT *
            FROM `{$tables['reports']}`
            {$where}
            ORDER BY
                CASE status WHEN 'pending' THEN 0 WHEN 'reviewed' THEN 1 ELSE 2 END,
                created_at DESC,
                report_id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $rows[] = eottae_talkroom_format_report_row($row);
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_pending_report_count')) {
    function eottae_talkroom_pending_report_count($room_id)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1) {
            return 0;
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['reports'])) {
            return 0;
        }

        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$tables['reports']}`
            WHERE room_id = '{$room_id}'
              AND status = 'pending'
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_talkroom_admin_pending_report_count')) {
    function eottae_talkroom_admin_pending_report_count()
    {
        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['reports'])) {
            return 0;
        }

        $row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$tables['reports']}` WHERE status = 'pending' ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_talkroom_update_report_status')) {
    function eottae_talkroom_update_report_status($report_id, $status, $handler_mb_id)
    {
        $report_id = (int) $report_id;
        $status = trim((string) $status);
        $handler_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $handler_mb_id);
        $tables = eottae_talkroom_table_names();
        $now = G5_TIME_YMDHIS;

        return (bool) sql_query("
            UPDATE `{$tables['reports']}` SET
                status = '".sql_escape_string($status)."',
                handled_by = '".sql_escape_string($handler_mb_id)."',
                handled_at = '{$now}'
            WHERE report_id = '{$report_id}'
        ", false);
    }
}

if (!function_exists('eottae_talkroom_handle_report')) {
    function eottae_talkroom_handle_report($report_id, $action, $handler_mb_id, $is_super_admin = false, $extra = array())
    {
        $report = eottae_talkroom_get_report($report_id);
        if (!$report) {
            return array('ok' => false, 'message' => '신고 내역을 찾을 수 없습니다.');
        }

        $room_id = (int) ($report['room_id'] ?? 0);
        if ($room_id < 1) {
            return array('ok' => false, 'message' => '신고 정보가 올바르지 않습니다.');
        }

        if (!$is_super_admin && !eottae_talkroom_can_manage_room($room_id, $handler_mb_id, false)) {
            return array('ok' => false, 'message' => '신고 처리 권한이 없습니다.');
        }

        $handler_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $handler_mb_id);
        $action = trim((string) $action);

        if ($action === 'report_review') {
            if (!eottae_talkroom_update_report_status($report_id, 'reviewed', $handler_mb_id)) {
                return array('ok' => false, 'message' => '확인 처리에 실패했습니다.');
            }

            return array('ok' => true, 'message' => '신고를 확인 처리했습니다.');
        }

        if ($action === 'report_dismiss') {
            if (!eottae_talkroom_update_report_status($report_id, 'dismissed', $handler_mb_id)) {
                return array('ok' => false, 'message' => '기각 처리에 실패했습니다.');
            }

            return array('ok' => true, 'message' => '신고를 기각했습니다.');
        }

        if ($action === 'report_delete_content') {
            $target = eottae_talkroom_get_report_target_write($report['target_type'] ?? '', $report['target_id'] ?? 0);
            if (!$target) {
                return array('ok' => false, 'message' => '삭제할 대상을 찾을 수 없습니다.');
            }

            $board = array('bo_table' => eottae_talkroom_board_table());
            $reason = '신고 처리: '.eottae_talkroom_report_reason_label($report['reason'] ?? '');
            if (!empty($report['memo'])) {
                $reason .= ' / '.$report['memo'];
            }

            if (!eottae_talkroom_is_post_deleted($target)) {
                $delete = eottae_talkroom_soft_delete_write($target, $board, $handler_mb_id, $reason);
                if (empty($delete['ok'])) {
                    return $delete;
                }
            }

            if (!eottae_talkroom_update_report_status($report_id, 'resolved', $handler_mb_id)) {
                return array('ok' => false, 'message' => '신고 상태 업데이트에 실패했습니다.');
            }

            return array('ok' => true, 'message' => '게시글/댓글을 삭제 처리했습니다.');
        }

        if ($action === 'report_kick_member') {
            $target = eottae_talkroom_get_report_target_write($report['target_type'] ?? '', $report['target_id'] ?? 0);
            if (!$target || empty($target['mb_id'])) {
                return array('ok' => false, 'message' => '강퇴할 회원을 찾을 수 없습니다.');
            }

            $tables = eottae_talkroom_table_names();
            $member_row = sql_fetch("
                SELECT *
                FROM `{$tables['members']}`
                WHERE room_id = '{$room_id}'
                  AND mb_id = '".sql_escape_string($target['mb_id'])."'
            ", false);

            if (empty($member_row['id'])) {
                return array('ok' => false, 'message' => '톡방 참여자 정보를 찾을 수 없습니다.');
            }

            $kick_reason = isset($extra['kicked_reason']) ? (string) $extra['kicked_reason'] : '신고 처리에 따른 강퇴';
            $can_rejoin = isset($extra['can_rejoin']) ? (int) $extra['can_rejoin'] : 0;
            $kick = eottae_talkroom_kick_member($room_id, (int) $member_row['id'], $handler_mb_id, $is_super_admin, $kick_reason, $can_rejoin);
            if (empty($kick['ok'])) {
                return $kick;
            }

            if (!eottae_talkroom_update_report_status($report_id, 'resolved', $handler_mb_id)) {
                return array('ok' => false, 'message' => '신고 상태 업데이트에 실패했습니다.');
            }

            return array('ok' => true, 'message' => '회원을 강퇴하고 신고를 처리했습니다.');
        }

        return array('ok' => false, 'message' => '지원하지 않는 처리입니다.');
    }
}
