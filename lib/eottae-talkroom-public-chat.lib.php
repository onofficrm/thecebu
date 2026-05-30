<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_public_group_super_mb_id')) {
    function eottae_talkroom_public_group_super_mb_id()
    {
        global $config;

        $admin_id = isset($config['cf_admin']) ? preg_replace('/[^a-z0-9_@.-]/i', '', (string) $config['cf_admin']) : '';
        if ($admin_id !== '') {
            return $admin_id;
        }

        global $g5;
        if (!empty($g5['member_table'])) {
            $row = sql_fetch("
                SELECT mb_id
                FROM {$g5['member_table']}
                WHERE mb_level = '10'
                ORDER BY mb_datetime ASC
                LIMIT 1
            ", false);
            if (!empty($row['mb_id'])) {
                return preg_replace('/[^a-z0-9_@.-]/i', '', (string) $row['mb_id']);
            }
        }

        return '';
    }
}

if (!function_exists('eottae_talkroom_public_group_insert_approved_room')) {
    /**
     * 최고관리자 명의로 공개 단체톡방을 즉시 승인 상태로 생성
     *
     * @return int|false
     */
    function eottae_talkroom_public_group_insert_approved_room($owner_mb_id, $admin_mb_id)
    {
        if (!function_exists('eottae_talkroom_table_names')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        eottae_talkroom_ensure_schema();
        eottae_talkroom_upgrade_schema();

        $owner_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $owner_mb_id);
        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);
        if ($owner_mb_id === '') {
            return false;
        }
        if ($admin_mb_id === '') {
            $admin_mb_id = $owner_mb_id;
        }

        $tables = eottae_talkroom_table_names();
        $now = G5_TIME_YMDHIS;
        $name = sql_escape_string(eottae_talkroom_public_group_room_name());

        $ok = sql_query("
            INSERT INTO `{$tables['rooms']}` SET
                room_name = '{$name}',
                room_description = '세부어때 홈 히어로 공개 단체 채팅방입니다.',
                room_detail = '회원 누구나 참여하는 실시간 공개 단체 대화입니다. AI 도우미가 환영·질문·리액션으로 대화를 돕습니다.',
                category = 'general',
                emoji = '💬',
                owner_mb_id = '".sql_escape_string($owner_mb_id)."',
                status = 'approved',
                visibility = 'public',
                join_type = 'open',
                rules = '예의를 지켜 주세요. 광고·욕설·개인정보 공유는 제한될 수 있습니다.',
                room_notice = '세부어때 공개 단체톡방에 오신 것을 환영합니다!',
                contact = '',
                apply_reason = '홈 히어로 공개 단체톡방 (시스템 자동 개설)',
                reject_reason = '',
                created_at = '{$now}',
                approved_at = '{$now}',
                approved_by = '".sql_escape_string($admin_mb_id)."',
                updated_at = '{$now}'
        ", false);

        if (!$ok) {
            return false;
        }

        $room_id = (int) sql_insert_id();
        if ($room_id < 1) {
            return false;
        }

        eottae_talkroom_ensure_owner_member($room_id, $owner_mb_id, $admin_mb_id, $now);
        eottae_talkroom_write_log($room_id, $admin_mb_id, 'approve', 'room', $room_id, '홈 공개 단체톡방 자동 개설');

        return $room_id;
    }
}

if (!function_exists('eottae_talkroom_public_group_ensure_ai')) {
    function eottae_talkroom_public_group_ensure_ai($room_id, $admin_mb_id = '')
    {
        $room_id = (int) $room_id;
        if ($room_id < 1) {
            return false;
        }

        if (!function_exists('eottae_talkroom_ai_ensure_schema')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
        }
        if (!function_exists('eottae_talkroom_ai_save_global_policy')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-admin.lib.php';
        }

        eottae_talkroom_ai_ensure_schema();
        eottae_talkroom_ai_ensure_bot_member('어때봇');

        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);
        if ($admin_mb_id === '') {
            $admin_mb_id = eottae_talkroom_public_group_super_mb_id();
        }

        $global = eottae_talkroom_ai_get_global_policy();
        if (empty($global['site_ai_enabled']) || empty($global['owner_config_allowed'])) {
            eottae_talkroom_ai_save_global_policy(array(
                'site_ai_enabled'      => 1,
                'owner_config_allowed' => 1,
                'site_daily_limit'     => max(50, (int) ($global['site_daily_limit'] ?? 50)),
            ), $admin_mb_id, true);
        }

        $settings = array(
            'ai_enabled'               => 1,
            'ai_name'                  => '어때봇',
            'ai_persona'               => 'community_manager',
            'ai_tone'                  => 'friendly',
            'quiet_trigger_enabled'    => 1,
            'daily_question_enabled'   => 1,
            'welcome_enabled'          => 1,
            'meetup_suggest_enabled'   => 0,
            'summary_enabled'          => 1,
            'reaction_enabled'         => 1,
            'max_messages_per_day'     => 8,
            'min_silence_minutes'      => 60,
            'active_start_time'        => '08:00:00',
            'active_end_time'          => '23:00:00',
            'admin_force_disabled'     => 0,
        );

        $current = eottae_talkroom_ai_get_settings($room_id);
        if (empty($current['ai_enabled'])) {
            eottae_talkroom_ai_save_settings($room_id, $settings, $admin_mb_id, true);
        }

        if (function_exists('eottae_talkroom_ai_ensure_welcome_hub_post')) {
            eottae_talkroom_ai_ensure_welcome_hub_post($room_id, '어때봇');
        }

        return true;
    }
}

if (!function_exists('eottae_talkroom_public_group_ensure_provisioned')) {
    /**
     * 홈 히어로 공개 단체톡방 + AI 설정 자동 준비 (최고관리자 명의)
     */
    function eottae_talkroom_public_group_ensure_provisioned()
    {
        static $done = false;
        if ($done) {
            return;
        }

        if (!function_exists('eottae_talkroom_table_names')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        if (!function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return;
        }

        $done = true;

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            eottae_talkroom_ensure_schema();
        }

        $admin_mb_id = eottae_talkroom_public_group_super_mb_id();
        if ($admin_mb_id === '') {
            return;
        }

        $room_id = 0;

        if (defined('EOTTae_PUBLIC_GROUP_TALK_ROOM_ID') && (int) EOTTae_PUBLIC_GROUP_TALK_ROOM_ID > 0) {
            $room_id = (int) EOTTae_PUBLIC_GROUP_TALK_ROOM_ID;
            $room = eottae_talkroom_get_operating_room($room_id);
            if (!$room || ($room['visibility'] ?? '') !== 'public') {
                $room_id = 0;
            }
        }

        if ($room_id < 1) {
            $name = sql_escape_string(eottae_talkroom_public_group_room_name());
            $statuses = eottae_talkroom_operating_statuses();
            $status_sql = array();
            foreach ($statuses as $status) {
                $status_sql[] = "'".sql_real_escape_string($status)."'";
            }
            $row = sql_fetch("
                SELECT room_id
                FROM `{$tables['rooms']}`
                WHERE room_name = '{$name}'
                  AND visibility = 'public'
                  AND status IN (".implode(',', $status_sql).")
                ORDER BY room_id ASC
                LIMIT 1
            ", false);
            $room_id = !empty($row['room_id']) ? (int) $row['room_id'] : 0;
        }

        if ($room_id < 1) {
            $room_id = (int) eottae_talkroom_public_group_insert_approved_room($admin_mb_id, $admin_mb_id);
        }

        if ($room_id > 0) {
            eottae_talkroom_public_group_ensure_ai($room_id, $admin_mb_id);
        }
    }
}

if (!function_exists('eottae_talkroom_public_group_room_name')) {
    function eottae_talkroom_public_group_room_name()
    {
        return defined('EOTTae_PUBLIC_GROUP_TALK_ROOM_NAME')
            ? (string) EOTTae_PUBLIC_GROUP_TALK_ROOM_NAME
            : '세부공개단체톡';
    }
}

if (!function_exists('eottae_talkroom_public_group_room_id')) {
    function eottae_talkroom_public_group_room_id()
    {
        static $resolved = null;
        if ($resolved !== null) {
            return $resolved;
        }

        eottae_talkroom_public_group_ensure_provisioned();

        if (defined('EOTTae_PUBLIC_GROUP_TALK_ROOM_ID') && (int) EOTTae_PUBLIC_GROUP_TALK_ROOM_ID > 0) {
            $resolved = (int) EOTTae_PUBLIC_GROUP_TALK_ROOM_ID;

            return $resolved;
        }

        if (!function_exists('eottae_talkroom_table_names')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            $resolved = 0;

            return $resolved;
        }

        $name = sql_escape_string(eottae_talkroom_public_group_room_name());
        $statuses = eottae_talkroom_operating_statuses();
        $status_sql = array();
        foreach ($statuses as $status) {
            $status_sql[] = "'".sql_real_escape_string($status)."'";
        }
        $status_in = implode(',', $status_sql);

        $row = sql_fetch("
            SELECT room_id
            FROM `{$tables['rooms']}`
            WHERE room_name = '{$name}'
              AND visibility = 'public'
              AND status IN ({$status_in})
            ORDER BY room_id ASC
            LIMIT 1
        ", false);

        if (empty($row['room_id'])) {
            $row = sql_fetch("
                SELECT room_id
                FROM `{$tables['rooms']}`
                WHERE visibility = 'public'
                  AND join_type = 'open'
                  AND status IN ({$status_in})
                ORDER BY room_id ASC
                LIMIT 1
            ", false);
        }

        $resolved = !empty($row['room_id']) ? (int) $row['room_id'] : 0;

        return $resolved;
    }
}

if (!function_exists('eottae_talkroom_public_group_room')) {
    function eottae_talkroom_public_group_room()
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = eottae_talkroom_public_group_room_id();
        if ($room_id < 1) {
            return null;
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room || ($room['visibility'] ?? 'public') !== 'public') {
            return null;
        }

        return $room;
    }
}

if (!function_exists('eottae_talkroom_public_group_message_text')) {
    function eottae_talkroom_public_group_message_text(array $row)
    {
        $content = trim(strip_tags((string) ($row['wr_content'] ?? '')));
        if ($content !== '') {
            return $content;
        }

        return trim(strip_tags((string) ($row['wr_subject'] ?? '')));
    }
}

if (!function_exists('eottae_talkroom_public_group_format_message')) {
    function eottae_talkroom_public_group_format_message(array $row, $viewer_mb_id = '')
    {
        if (!function_exists('eottae_talkroom_ai_message_enrich_post_row')) {
            include_once G5_PATH.'/components/eottae/talk-ai-message-ui.php';
        }

        $action_url = trim((string) ($row['wr_link1'] ?? ''));
        if ($action_url !== '' && !preg_match('#^https?://#i', $action_url)) {
            $action_url = '';
        }

        $calendar_event_id = max(0, (int) ($row['wr_5'] ?? 0));
        if ($calendar_event_id < 1 && $action_url !== '') {
            if (!function_exists('eottae_calendar_event_id_from_url')) {
                include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
            }
            $calendar_event_id = eottae_calendar_event_id_from_url($action_url);
        }

        $post_row = array(
            'wr_id'        => (int) ($row['wr_id'] ?? 0),
            'wr_name'      => $row['wr_name'] ?? '',
            'mb_id'        => $row['mb_id'] ?? '',
            'wr_3'         => $row['wr_3'] ?? '',
            'author'       => get_text($row['wr_name'] ?? ''),
            'text'         => eottae_talkroom_public_group_message_text($row),
            'time_label'   => function_exists('eottae_community_relative_time')
                ? eottae_community_relative_time($row['wr_datetime'] ?? '')
                : substr((string) ($row['wr_datetime'] ?? ''), 11, 5),
            'href'         => function_exists('eottae_talkroom_post_view_url')
                ? eottae_talkroom_post_view_url((int) ($row['wr_id'] ?? 0), (int) ($row['wr_1'] ?? 0))
                : '',
            'action_label'      => get_text($row['wr_link2'] ?? ''),
            'action_url'        => $action_url,
            'calendar_event_id' => $calendar_event_id,
        );

        if (function_exists('eottae_talkroom_ai_message_enrich_post_row')) {
            $post_row = eottae_talkroom_ai_message_enrich_post_row($post_row);
        }

        $post_row['is_ai'] = !empty($post_row['is_ai']) ? 1 : 0;
        $post_row['is_mine'] = $viewer_mb_id !== '' && $viewer_mb_id === ($post_row['mb_id'] ?? '');
        if (!empty($post_row['is_ai'])) {
            $post_row['author'] = !empty($post_row['ai_display_name'])
                ? get_text($post_row['ai_display_name'])
                : get_text($post_row['author'] ?? '어때봇 · AI 도우미');
            $post_row['ai_display_name'] = $post_row['author'];
        } elseif (!empty($post_row['mb_id']) && function_exists('eottae_member_growth_author_badge_text')) {
            include_once G5_PATH.'/components/eottae/member-growth-display.php';
            $author_badge = eottae_member_growth_author_badge_text($post_row['mb_id']);
            if ($author_badge !== '') {
                $post_row['author_badge'] = $author_badge;
                $post_row['author_display'] = get_text($post_row['author']).' · '.$author_badge;
            }
        }

        if (!function_exists('eottae_public_group_chat_attach_profile_fields')) {
            include_once G5_PATH.'/components/eottae/public-group-chat-message.php';
        }
        if (function_exists('eottae_public_group_chat_attach_profile_fields')) {
            $post_row = eottae_public_group_chat_attach_profile_fields($post_row);
        }

        if (strpos((string) ($post_row['text'] ?? ''), "\n\n1.") !== false) {
            $parts = preg_split("/\n\n(?=\d+\.)/u", (string) $post_row['text'], 2);
            if (is_array($parts) && count($parts) === 2) {
                $post_row['text'] = $parts[0];
                $poll_lines = preg_split('/\n/u', trim($parts[1]));
                $opts = array();
                foreach ($poll_lines as $line) {
                    if (preg_match('/^\d+\.\s*(.+)$/u', trim($line), $m)) {
                        $opts[] = trim($m[1]);
                    }
                }
                if ($opts && function_exists('eottae_public_ai_poll_encode_options')) {
                    include_once G5_LIB_PATH.'/eottae-public-ai-poll.lib.php';
                    $post_row['poll_options'] = eottae_public_ai_poll_encode_options($opts);
                }
            }
        }

        return $post_row;
    }
}

if (!function_exists('eottae_talkroom_public_group_list_messages')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_public_group_list_messages($room_id, $limit = 20, $since_wr_id = 0)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1 || !function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $write_table = eottae_talkroom_write_table();
        if ($room_id < 1 || $write_table === '') {
            return array();
        }

        $limit = max(1, min(50, (int) $limit));
        $since_wr_id = max(0, (int) $since_wr_id);
        $visible = eottae_talkroom_post_visible_sql();
        $where_since = $since_wr_id > 0 ? " AND wr_id > '{$since_wr_id}' " : '';

        $result = sql_query("
            SELECT wr_id, wr_subject, wr_content, wr_name, wr_datetime, mb_id, wr_3, wr_1, wr_link1, wr_link2, wr_5
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_1 = '{$room_id}'
              AND {$visible}
              {$where_since}
            ORDER BY wr_id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $text = eottae_talkroom_public_group_message_text($row);
                if ($text === '') {
                    continue;
                }
                $rows[] = $row;
            }
        }

        if ($since_wr_id < 1) {
            $rows = array_reverse($rows);
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_public_group_list_messages_before')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_public_group_list_messages_before($room_id, $before_wr_id, $limit = 10)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1 || !function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $write_table = eottae_talkroom_write_table();
        $before_wr_id = (int) $before_wr_id;
        if ($room_id < 1 || $write_table === '' || $before_wr_id < 1) {
            return array();
        }

        $limit = max(1, min(20, (int) $limit));
        $visible = eottae_talkroom_post_visible_sql();

        $result = sql_query("
            SELECT wr_id, wr_subject, wr_content, wr_name, wr_datetime, mb_id, wr_3, wr_1, wr_link1, wr_link2, wr_5
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_1 = '{$room_id}'
              AND {$visible}
              AND wr_id < '{$before_wr_id}'
            ORDER BY wr_id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $text = eottae_talkroom_public_group_message_text($row);
                if ($text === '') {
                    continue;
                }
                $rows[] = $row;
            }
        }

        return array_reverse($rows);
    }
}

if (!function_exists('eottae_talkroom_public_group_has_older_messages')) {
    function eottae_talkroom_public_group_has_older_messages($room_id, $before_wr_id)
    {
        $room_id = (int) $room_id;
        $before_wr_id = (int) $before_wr_id;
        if ($room_id < 1 || $before_wr_id < 2) {
            return false;
        }

        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '') {
            return false;
        }

        $visible = eottae_talkroom_post_visible_sql();
        $row = sql_fetch("
            SELECT wr_id
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_1 = '{$room_id}'
              AND {$visible}
              AND wr_id < '{$before_wr_id}'
            ORDER BY wr_id DESC
            LIMIT 1
        ", false);

        return is_array($row) && !empty($row['wr_id']);
    }
}

if (!function_exists('eottae_talkroom_public_group_chat_payload')) {
    function eottae_talkroom_public_group_chat_payload($limit = 20, $viewer_mb_id = '')
    {
        global $is_member, $is_admin;

        if (!function_exists('eottae_talkroom_enter_url')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room = eottae_talkroom_public_group_room();
        $room_id = $room ? (int) ($room['room_id'] ?? 0) : 0;
        $messages = array();
        $last_wr_id = 0;
        $is_super = ($is_admin === 'super');
        $can_manage_ai = $room_id > 0 && eottae_talkroom_public_group_can_manage_ai($room_id, $viewer_mb_id, $is_super);

        if ($room_id > 0) {
            $rows = eottae_talkroom_public_group_list_messages($room_id, $limit);
            $messages = eottae_talkroom_public_group_format_messages_for_viewer(
                $rows,
                $viewer_mb_id,
                $can_manage_ai,
                $room_id,
                $is_super
            );
            foreach ($messages as $message) {
                $last_wr_id = max($last_wr_id, (int) ($message['wr_id'] ?? 0));
            }
        }

        $member_row = null;
        if ($room_id > 0 && $viewer_mb_id !== '') {
            $member_row = eottae_talkroom_get_member_row($room_id, $viewer_mb_id);
        }

        $can_send = !empty($is_member) && $room_id > 0 && eottae_talkroom_is_active_member($member_row);
        $needs_join = !empty($is_member)
            && $room_id > 0
            && $viewer_mb_id !== ''
            && (!$member_row || ($member_row['status'] ?? '') !== 'active')
            && (!$member_row || !eottae_talkroom_is_kicked_status($member_row['status'] ?? ''));

        return array(
            'room_id'      => $room_id,
            'room_name'    => $room ? get_text($room['room_name'] ?? eottae_talkroom_public_group_room_name()) : eottae_talkroom_public_group_room_name(),
            'room_emoji'   => $room ? get_text($room['emoji'] ?? '💬') : '💬',
            'enter_href'   => $room_id > 0 ? eottae_talkroom_enter_url($room_id) : eottae_talkroom_list_url(),
            'list_href'    => eottae_talkroom_list_url(),
            'messages'     => $messages,
            'last_wr_id'   => $last_wr_id,
            'is_member'    => !empty($is_member) ? 1 : 0,
            'can_send'     => $can_send ? 1 : 0,
            'needs_join'   => ($needs_join && !$can_send) ? 1 : 0,
            'login_href'    => function_exists('eottae_login_url') ? eottae_login_url(G5_URL) : G5_BBS_URL.'/login.php',
            'register_href' => function_exists('eottae_register_url') ? eottae_register_url() : G5_BBS_URL.'/register.php',
            'member_token' => !empty($is_member) && function_exists('eottae_talkroom_member_token')
                ? eottae_talkroom_member_token()
                : '',
            'can_manage_ai' => $can_manage_ai ? 1 : 0,
        );
    }
}

if (!function_exists('eottae_talkroom_public_group_send_message')) {
    function eottae_talkroom_public_group_send_message($room_id, $mb_id, $message)
    {
        global $g5, $member;

        if (!function_exists('eottae_talkroom_join_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $public_room_id = eottae_talkroom_public_group_room_id();
        if ($room_id < 1 || $room_id !== $public_room_id) {
            return array('ok' => false, 'message' => '공개 단체톡방만 이용할 수 있습니다.');
        }

        $room = eottae_talkroom_public_group_room();
        if (!$room) {
            return array('ok' => false, 'message' => '운영 중인 공개 단체톡방이 없습니다.');
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인이 필요합니다.');
        }

        $message = trim(strip_tags((string) $message));
        if ($message === '') {
            return array('ok' => false, 'message' => '메시지를 입력해 주세요.');
        }

        if (function_exists('mb_strlen') && mb_strlen($message, 'UTF-8') > 500) {
            return array('ok' => false, 'message' => '메시지는 500자 이내로 작성해 주세요.');
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        if (!$member_row || ($member_row['status'] ?? '') !== 'active') {
            if (($room['join_type'] ?? 'open') !== 'open') {
                return array('ok' => false, 'message' => '참여 승인 후 메시지를 보낼 수 있습니다.');
            }

            $join = eottae_talkroom_join_room($room_id, $mb_id);
            if (empty($join['ok']) && ($join['message'] ?? '') !== '이미 참여 중인 톡방입니다.') {
                return array('ok' => false, 'message' => $join['message'] ?? '참여에 실패했습니다.');
            }

            $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        }

        if (!eottae_talkroom_can_write_posts($room, $member_row, $mb_id)) {
            return array('ok' => false, 'message' => '메시지를 보낼 수 없습니다.');
        }

        $write_table = eottae_talkroom_write_table();
        $bo_table = preg_replace('/[^a-z0-9_]/', '', eottae_talkroom_board_table());
        if ($write_table === '' || $bo_table === '') {
            return array('ok' => false, 'message' => '게시판 설정을 찾을 수 없습니다.');
        }

        $mb = is_array($member) ? $member : array();
        $wr_name = get_text($mb['mb_nick'] ?? ($mb['mb_name'] ?? $mb_id));
        if ($wr_name === '') {
            $wr_name = $mb_id;
        }

        $subject = function_exists('cut_str') ? cut_str($message, 40, '…') : mb_substr($message, 0, 40, 'UTF-8');
        $subject_sql = sql_escape_string($subject);
        $content_sql = sql_escape_string($message);
        $mb_id_sql = sql_escape_string($mb_id);
        $wr_name_sql = sql_escape_string($wr_name);
        $wr_email_sql = sql_escape_string($mb['mb_email'] ?? ($mb_id.'@local'));
        $seo = sql_escape_string(preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($subject)));

        sql_query(" INSERT INTO `{$write_table}` SET
            wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM `{$write_table}` AS sq),
            wr_reply = '',
            wr_comment = 0,
            ca_name = '한마디',
            wr_option = '',
            wr_subject = '{$subject_sql}',
            wr_content = '{$content_sql}',
            wr_seo_title = '{$seo}',
            wr_link1 = '',
            wr_link2 = '',
            wr_link1_hit = 0,
            wr_link2_hit = 0,
            wr_hit = 0,
            wr_good = 0,
            wr_nogood = 0,
            mb_id = '{$mb_id_sql}',
            wr_password = '',
            wr_name = '{$wr_name_sql}',
            wr_email = '{$wr_email_sql}',
            wr_homepage = '',
            wr_datetime = '".G5_TIME_YMDHIS."',
            wr_last = '".G5_TIME_YMDHIS."',
            wr_ip = '".sql_escape_string($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1')."',
            wr_1 = '{$room_id}',
            wr_2 = '',
            wr_3 = 'web:public_chat',
            wr_4 = '',
            wr_5 = '',
            wr_6 = '',
            wr_7 = '',
            wr_8 = '',
            wr_9 = '',
            wr_10 = '' ", false);

        $wr_id = (int) sql_insert_id();
        if ($wr_id < 1) {
            return array('ok' => false, 'message' => '메시지 전송에 실패했습니다.');
        }

        sql_query(" UPDATE `{$write_table}` SET wr_parent = '{$wr_id}' WHERE wr_id = '{$wr_id}' ", false);
        sql_query(" INSERT INTO {$g5['board_new_table']}
            (bo_table, wr_id, wr_parent, bn_datetime, mb_id)
            VALUES ('{$bo_table}', '{$wr_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', '{$mb_id_sql}') ", false);
        sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '{$bo_table}' ", false);

        if (function_exists('delete_cache_latest')) {
            delete_cache_latest($bo_table);
        }

        if (function_exists('eottae_talkroom_ai_schedule_reaction_for_post')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-reaction.lib.php';
            eottae_talkroom_ai_schedule_reaction_for_post($wr_id);
        }

        $row = sql_fetch(" SELECT wr_id, wr_subject, wr_content, wr_name, wr_datetime, mb_id, wr_3, wr_1
            FROM `{$write_table}` WHERE wr_id = '{$wr_id}' LIMIT 1 ");

        return array(
            'ok'      => true,
            'message' => '전송되었습니다.',
            'wr_id'   => $wr_id,
            'message_row' => $row ? eottae_talkroom_public_group_format_message($row, $mb_id) : null,
        );
    }
}

if (!function_exists('eottae_talkroom_room_send_message')) {
    /**
     * 톡방 상세 — 실시간 채팅 메시지 전송 (공개·비공개 모든 운영 톡방)
     *
     * @return array{ok:bool, message:string, wr_id?:int, message_row?:array<string, mixed>|null}
     */
    function eottae_talkroom_room_send_message($room_id, $mb_id, $message)
    {
        global $g5, $member;

        if (!function_exists('eottae_talkroom_join_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array('ok' => false, 'message' => '운영 중인 톡방을 찾을 수 없습니다.');
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인이 필요합니다.');
        }

        $message = trim(strip_tags((string) $message));
        if ($message === '') {
            return array('ok' => false, 'message' => '메시지를 입력해 주세요.');
        }

        if (function_exists('mb_strlen') && mb_strlen($message, 'UTF-8') > 500) {
            return array('ok' => false, 'message' => '메시지는 500자 이내로 작성해 주세요.');
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        if (!$member_row || ($member_row['status'] ?? '') !== 'active') {
            if (($room['join_type'] ?? 'open') !== 'open') {
                return array('ok' => false, 'message' => '참여 승인 후 메시지를 보낼 수 있습니다.');
            }

            $join = eottae_talkroom_join_room($room_id, $mb_id);
            if (empty($join['ok']) && ($join['message'] ?? '') !== '이미 참여 중인 톡방입니다.') {
                return array('ok' => false, 'message' => $join['message'] ?? '참여에 실패했습니다.');
            }

            $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        }

        if (!eottae_talkroom_can_write_posts($room, $member_row, $mb_id)) {
            return array('ok' => false, 'message' => '메시지를 보낼 수 없습니다.');
        }

        $write_table = eottae_talkroom_write_table();
        $bo_table = preg_replace('/[^a-z0-9_]/', '', eottae_talkroom_board_table());
        if ($write_table === '' || $bo_table === '') {
            return array('ok' => false, 'message' => '게시판 설정을 찾을 수 없습니다.');
        }

        $mb = is_array($member) ? $member : array();
        $wr_name = get_text($mb['mb_nick'] ?? ($mb['mb_name'] ?? $mb_id));
        if ($wr_name === '') {
            $wr_name = $mb_id;
        }

        $subject = function_exists('cut_str') ? cut_str($message, 40, '…') : mb_substr($message, 0, 40, 'UTF-8');
        $subject_sql = sql_escape_string($subject);
        $content_sql = sql_escape_string($message);
        $mb_id_sql = sql_escape_string($mb_id);
        $wr_name_sql = sql_escape_string($wr_name);
        $wr_email_sql = sql_escape_string($mb['mb_email'] ?? ($mb_id.'@local'));
        $seo = sql_escape_string(preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($subject)));

        sql_query(" INSERT INTO `{$write_table}` SET
            wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM `{$write_table}` AS sq),
            wr_reply = '',
            wr_comment = 0,
            ca_name = '한마디',
            wr_option = '',
            wr_subject = '{$subject_sql}',
            wr_content = '{$content_sql}',
            wr_seo_title = '{$seo}',
            wr_link1 = '',
            wr_link2 = '',
            wr_link1_hit = 0,
            wr_link2_hit = 0,
            wr_hit = 0,
            wr_good = 0,
            wr_nogood = 0,
            mb_id = '{$mb_id_sql}',
            wr_password = '',
            wr_name = '{$wr_name_sql}',
            wr_email = '{$wr_email_sql}',
            wr_homepage = '',
            wr_datetime = '".G5_TIME_YMDHIS."',
            wr_last = '".G5_TIME_YMDHIS."',
            wr_ip = '".sql_escape_string($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1')."',
            wr_1 = '{$room_id}',
            wr_2 = '',
            wr_3 = 'web:room_chat',
            wr_4 = '',
            wr_5 = '',
            wr_6 = '',
            wr_7 = '',
            wr_8 = '',
            wr_9 = '',
            wr_10 = '' ", false);

        $wr_id = (int) sql_insert_id();
        if ($wr_id < 1) {
            return array('ok' => false, 'message' => '메시지 전송에 실패했습니다.');
        }

        sql_query(" UPDATE `{$write_table}` SET wr_parent = '{$wr_id}' WHERE wr_id = '{$wr_id}' ", false);
        sql_query(" INSERT INTO {$g5['board_new_table']}
            (bo_table, wr_id, wr_parent, bn_datetime, mb_id)
            VALUES ('{$bo_table}', '{$wr_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', '{$mb_id_sql}') ", false);
        sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '{$bo_table}' ", false);

        if (function_exists('delete_cache_latest')) {
            delete_cache_latest($bo_table);
        }

        if (function_exists('eottae_talkroom_ai_schedule_reaction_for_post')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-reaction.lib.php';
            eottae_talkroom_ai_schedule_reaction_for_post($wr_id);
        }

        $row = sql_fetch(" SELECT wr_id, wr_subject, wr_content, wr_name, wr_datetime, mb_id, wr_3, wr_1
            FROM `{$write_table}` WHERE wr_id = '{$wr_id}' LIMIT 1 ");

        return array(
            'ok'          => true,
            'message'     => '전송되었습니다.',
            'wr_id'       => $wr_id,
            'message_row' => $row ? eottae_talkroom_public_group_format_message($row, $mb_id) : null,
        );
    }
}

if (!function_exists('eottae_talkroom_room_chat_payload')) {
    /**
     * 톡방 상세 페이지 — 채팅 UI 초기 데이터
     *
     * @param array<string, mixed>|null $ctx eottae_talkroom_build_detail_context()
     * @return array<string, mixed>
     */
    function eottae_talkroom_room_chat_payload($room_id, $viewer_mb_id = '', ?array $ctx = null, $initial_limit = 25)
    {
        global $is_member, $is_admin;

        if (!function_exists('eottae_talkroom_build_detail_context')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $viewer_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $viewer_mb_id);
        $initial_limit = max(1, min(50, (int) $initial_limit));

        if ($ctx === null) {
            $ctx = eottae_talkroom_build_detail_context($room_id, $viewer_mb_id);
        }

        if (!$ctx || empty($ctx['room'])) {
            return array(
                'room_id'    => 0,
                'messages'   => array(),
                'last_wr_id' => 0,
                'first_wr_id'=> 0,
                'has_older'  => 0,
            );
        }

        $room = $ctx['room'];
        $messages = array();
        $last_wr_id = 0;
        $first_wr_id = 0;
        $has_older = 0;
        $is_super = ($is_admin === 'super');
        $can_manage_ai = eottae_talkroom_public_group_can_manage_ai($room_id, $viewer_mb_id, $is_super);

        if (!empty($ctx['can_view_posts'])) {
            $rows = eottae_talkroom_public_group_list_messages($room_id, $initial_limit);
            $messages = eottae_talkroom_public_group_format_messages_for_viewer(
                $rows,
                $viewer_mb_id,
                $can_manage_ai,
                $room_id,
                $is_super
            );
            foreach ($messages as $message) {
                $wr_id = (int) ($message['wr_id'] ?? 0);
                if ($wr_id < 1) {
                    continue;
                }
                if ($first_wr_id < 1 || $wr_id < $first_wr_id) {
                    $first_wr_id = $wr_id;
                }
                $last_wr_id = max($last_wr_id, $wr_id);
            }
            if ($first_wr_id > 0) {
                $has_older = eottae_talkroom_public_group_has_older_messages($room_id, $first_wr_id) ? 1 : 0;
            }
        }

        $can_send = !empty($is_member) && !empty($ctx['can_write']);
        $membership = isset($ctx['membership']) ? (string) $ctx['membership'] : 'guest';
        $can_join = !empty($ctx['can_join']);
        $needs_join = !empty($is_member)
            && $viewer_mb_id !== ''
            && $can_join
            && in_array($membership, array('guest', 'left'), true);
        $membership_pending = !empty($is_member) && $membership === 'pending';
        $join_blocked = !empty($is_member)
            && !$needs_join
            && !$membership_pending
            && !empty($ctx['can_view_posts'])
            && empty($ctx['can_write']);

        $return_url = function_exists('eottae_talkroom_enter_url')
            ? eottae_talkroom_enter_url($room_id)
            : G5_URL;

        return array(
            'room_id'       => $room_id,
            'room_name'     => get_text($room['room_name'] ?? ''),
            'room_emoji'    => get_text($room['emoji'] ?? '💬'),
            'room_desc'     => get_text($room['room_description'] ?? ''),
            'messages'      => $messages,
            'last_wr_id'    => $last_wr_id,
            'first_wr_id'   => $first_wr_id,
            'has_older'     => $has_older,
            'initial_limit' => $initial_limit,
            'is_member'     => !empty($is_member) ? 1 : 0,
            'can_send'      => $can_send ? 1 : 0,
            'can_view'      => !empty($ctx['can_view_posts']) ? 1 : 0,
            'needs_join'    => $needs_join ? 1 : 0,
            'membership_pending' => $membership_pending ? 1 : 0,
            'join_blocked'  => $join_blocked ? 1 : 0,
            'login_href'    => function_exists('eottae_login_url') ? eottae_login_url($return_url) : G5_BBS_URL.'/login.php?url='.urlencode($return_url),
            'register_href' => function_exists('eottae_register_url') ? eottae_register_url() : G5_BBS_URL.'/register.php',
            'member_token'  => !empty($is_member) && function_exists('eottae_talkroom_member_token')
                ? eottae_talkroom_member_token()
                : '',
            'join_hint'     => get_text($ctx['join_blocked_reason'] ?? ''),
            'can_manage_ai' => $can_manage_ai ? 1 : 0,
        );
    }
}

if (!function_exists('eottae_talkroom_public_group_can_manage_ai')) {
    /**
     * 공개 단체톡 — 최고관리자 또는 방장(AI 수동 발화·삭제)
     */
    function eottae_talkroom_public_group_can_manage_ai($room_id, $mb_id, $is_super_admin = false)
    {
        if (!function_exists('eottae_talkroom_can_manage_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        if ($room_id < 1) {
            return false;
        }

        $public_id = (int) eottae_talkroom_public_group_room_id();
        if ($public_id < 1 || $room_id !== $public_id) {
            return false;
        }

        return eottae_talkroom_can_manage_room($room_id, $mb_id, $is_super_admin);
    }
}

if (!function_exists('eottae_talkroom_public_group_can_delete_message')) {
    /**
     * 공개·톡방 채팅 메시지 삭제 권한 — 최고관리자 또는 작성자
     */
    function eottae_talkroom_public_group_can_delete_message(array $write, $mb_id, $is_super_admin = false)
    {
        if (!is_array($write) || empty($write['wr_id'])) {
            return false;
        }

        if ($is_super_admin) {
            return true;
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return false;
        }

        $author_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($write['mb_id'] ?? ''));

        return $author_mb_id !== '' && $author_mb_id === $mb_id;
    }
}

if (!function_exists('eottae_talkroom_public_group_enrich_message_for_manager')) {
    /**
     * @param array<string, mixed> $message
     * @return array<string, mixed>
     */
    function eottae_talkroom_public_group_enrich_message_for_manager(
        array $message,
        $can_manage_ai = false,
        $viewer_mb_id = '',
        $is_super_admin = false
    ) {
        $message['can_delete'] = 0;

        if ($is_super_admin) {
            $message['can_delete'] = 1;

            return $message;
        }

        if ($viewer_mb_id !== '' && !empty($message['is_mine'])) {
            $message['can_delete'] = 1;

            return $message;
        }

        if ($can_manage_ai && !empty($message['is_ai'])) {
            $message['can_delete'] = 1;
        }

        return $message;
    }
}

if (!function_exists('eottae_talkroom_public_group_attach_message_unread_counts')) {
    /**
     * @param array<int, array<string, mixed>> $messages
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_public_group_attach_message_unread_counts(array $messages, $room_id, $viewer_mb_id)
    {
        $room_id = (int) $room_id;
        $viewer_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $viewer_mb_id);
        if ($room_id < 1 || $viewer_mb_id === '' || empty($messages)) {
            return $messages;
        }

        $specs = array();
        foreach ($messages as $message) {
            if (empty($message['is_mine'])) {
                continue;
            }
            $wr_id = (int) ($message['wr_id'] ?? 0);
            if ($wr_id < 1) {
                continue;
            }
            $specs[] = array(
                'wr_id' => $wr_id,
                'mb_id' => $viewer_mb_id,
            );
        }

        if (empty($specs)) {
            return $messages;
        }

        if (!function_exists('eottae_talkroom_chat_unread_counts_for_messages')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-reads.lib.php';
        }

        $counts = eottae_talkroom_chat_unread_counts_for_messages($room_id, $specs);
        foreach ($messages as $idx => $message) {
            if (empty($message['is_mine'])) {
                continue;
            }
            $wr_id = (int) ($message['wr_id'] ?? 0);
            $messages[$idx]['unread_count'] = isset($counts[$wr_id]) ? (int) $counts[$wr_id] : 0;
        }

        return $messages;
    }
}

if (!function_exists('eottae_talkroom_public_group_chat_poll_extras')) {
    /**
     * 폴링 시 읽음 처리 + 내 메시지 미읽음 수 갱신
     *
     * @return array{unread_updates: array<string, int>}
     */
    function eottae_talkroom_public_group_chat_poll_extras($room_id, $viewer_mb_id, $mark_read = true)
    {
        $room_id = (int) $room_id;
        $viewer_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $viewer_mb_id);
        $extras = array('unread_updates' => array());

        if ($mark_read && $viewer_mb_id !== '') {
            if (!function_exists('eottae_talkroom_reads_can_mark')) {
                include_once G5_LIB_PATH.'/eottae-talkroom-reads.lib.php';
            }
            if (eottae_talkroom_reads_can_mark($room_id, $viewer_mb_id)) {
                eottae_talkroom_mark_room_read($room_id, $viewer_mb_id);
            }
        }

        $raw = isset($_REQUEST['read_check_wr_ids']) ? trim((string) $_REQUEST['read_check_wr_ids']) : '';
        if ($viewer_mb_id === '' || $raw === '') {
            return $extras;
        }

        $wr_ids = array();
        foreach (explode(',', $raw) as $part) {
            $wr_id = (int) trim($part);
            if ($wr_id > 0) {
                $wr_ids[$wr_id] = $wr_id;
            }
        }
        $wr_ids = array_values($wr_ids);
        if (empty($wr_ids)) {
            return $extras;
        }

        if (!function_exists('eottae_talkroom_chat_unread_counts_for_messages')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-reads.lib.php';
        }

        $specs = array();
        foreach ($wr_ids as $wr_id) {
            $specs[] = array(
                'wr_id' => $wr_id,
                'mb_id' => $viewer_mb_id,
            );
        }

        $counts = eottae_talkroom_chat_unread_counts_for_messages($room_id, $specs);
        foreach ($counts as $wr_id => $count) {
            $extras['unread_updates'][(string) $wr_id] = (int) $count;
        }

        return $extras;
    }
}

if (!function_exists('eottae_talkroom_public_group_enrich_message_row')) {
    /**
     * @param array<string, mixed>|null $message_row
     * @return array<string, mixed>|null
     */
    function eottae_talkroom_public_group_enrich_message_row(
        $message_row,
        $room_id,
        $viewer_mb_id,
        $is_super_admin = false,
        $can_manage_ai = false
    ) {
        if (!is_array($message_row) || empty($message_row)) {
            return $message_row;
        }

        $rows = eottae_talkroom_public_group_attach_message_unread_counts(
            array($message_row),
            (int) $room_id,
            (string) $viewer_mb_id
        );

        $message = $rows[0] ?? $message_row;

        return eottae_talkroom_public_group_enrich_message_for_manager(
            $message,
            $can_manage_ai,
            $viewer_mb_id,
            $is_super_admin
        );
    }
}

if (!function_exists('eottae_talkroom_public_group_format_messages_for_viewer')) {
    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_public_group_format_messages_for_viewer(
        array $rows,
        $viewer_mb_id,
        $can_manage_ai,
        $room_id = 0,
        $is_super_admin = false
    ) {
        $messages = array();

        foreach ($rows as $row) {
            $message = eottae_talkroom_public_group_format_message($row, $viewer_mb_id);
            if (($message['text'] ?? '') === '') {
                continue;
            }
            $messages[] = eottae_talkroom_public_group_enrich_message_for_manager(
                $message,
                $can_manage_ai,
                $viewer_mb_id,
                $is_super_admin
            );
        }

        if ((int) $room_id > 0) {
            $messages = eottae_talkroom_public_group_attach_message_unread_counts($messages, (int) $room_id, (string) $viewer_mb_id);
        }

        return $messages;
    }
}

if (!function_exists('eottae_public_ai_run_manual_group_speak')) {
    /**
     * 공개 단체톡 — 관리자/방장 수동 AI 말걸기
     *
     * @return array{ok:bool, message:string, wr_id?:int, message_row?:array|null}
     */
    function eottae_public_ai_run_manual_group_speak($room_id, $actor_mb_id, $is_super_admin = false)
    {
        $room_id = (int) $room_id;
        if (!eottae_talkroom_public_group_can_manage_ai($room_id, $actor_mb_id, $is_super_admin)) {
            return array('ok' => false, 'message' => 'AI 말걸기 권한이 없습니다.');
        }

        if (!is_file(G5_LIB_PATH.'/eottae-public-ai-schedule.lib.php')) {
            return array('ok' => false, 'message' => 'AI 모듈을 불러올 수 없습니다.');
        }

        include_once G5_LIB_PATH.'/eottae-public-ai-schedule.lib.php';
        include_once G5_LIB_PATH.'/eottae-public-ai-generator.lib.php';
        include_once G5_LIB_PATH.'/eottae-public-ai-publish.lib.php';

        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');

        if (function_exists('eottae_public_ai_sync_calendar_before_slot')) {
            eottae_public_ai_sync_calendar_before_slot();
        }
        if (function_exists('eottae_public_ai_sync_weather_for_ai')) {
            eottae_public_ai_sync_weather_for_ai($now);
        }

        $settings = eottae_public_ai_get_settings();
        $slot_key = function_exists('eottae_public_ai_detect_publish_slot')
            ? eottae_public_ai_detect_publish_slot($now)
            : '';
        if ($slot_key === '') {
            $slot_key = 'noon';
        }

        $slot_settings = $settings;
        $slot_settings['use_weather'] = 1;
        $sources = eottae_public_ai_collect_sources($slot_settings, $now);
        $candidates = eottae_public_ai_generate_candidates($sources);
        if (function_exists('eottae_public_ai_sort_candidates_for_slot')) {
            $candidates = eottae_public_ai_sort_candidates_for_slot($candidates, $slot_key);
        }

        $message = '';
        $action_label = '';
        $action_url = '';
        $trigger_type = 'admin_manual';
        $calendar_event_id = 0;

        foreach ($candidates as $candidate) {
            $candidate_message = trim((string) ($candidate['message'] ?? ''));
            if ($candidate_message === '') {
                continue;
            }
            $message = $candidate_message;
            $action_label = trim((string) ($candidate['action_label'] ?? ''));
            $action_url = trim((string) ($candidate['action_url'] ?? ''));
            $trigger_type = trim((string) ($candidate['trigger_type'] ?? 'admin_manual'));
            if (($candidate['source_type'] ?? '') === 'calendar') {
                $calendar_event_id = max(0, (int) ($candidate['source_id'] ?? 0));
            }
            break;
        }

        if ($message === '' && function_exists('eottae_public_ai_build_slot_fallback_candidate')) {
            $fallback = eottae_public_ai_build_slot_fallback_candidate($slot_key, $sources);
            $message = trim((string) ($fallback['message'] ?? ''));
            $action_label = trim((string) ($fallback['action_label'] ?? ''));
            $action_url = trim((string) ($fallback['action_url'] ?? ''));
        }

        if ($message === '') {
            return array('ok' => false, 'message' => '보낼 AI 메시지를 만들지 못했습니다.');
        }

        if ($calendar_event_id < 1 && $action_url !== '') {
            if (!function_exists('eottae_calendar_event_id_from_url')) {
                include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
            }
            $calendar_event_id = eottae_calendar_event_id_from_url($action_url);
        }

        $send = eottae_talkroom_public_group_send_ai_message($room_id, $message, array(
            'trigger_type'      => preg_replace('/[^a-z0-9_]/', '', $trigger_type) ?: 'admin_manual',
            'action_label'      => $action_label,
            'action_url'        => $action_url,
            'calendar_event_id' => $calendar_event_id,
            'settings'          => $settings,
        ));

        if (empty($send['ok'])) {
            return $send;
        }

        $wr_id = (int) ($send['wr_id'] ?? 0);
        $write_table = function_exists('eottae_talkroom_write_table') ? eottae_talkroom_write_table() : '';
        $row = null;
        if ($wr_id > 0 && $write_table !== '') {
            $row = sql_fetch("
                SELECT wr_id, wr_subject, wr_content, wr_name, wr_datetime, mb_id, wr_3, wr_1, wr_link1, wr_link2, wr_5
                FROM `{$write_table}`
                WHERE wr_id = '{$wr_id}'
                LIMIT 1
            ");
        }

        $message_row = $row
            ? eottae_talkroom_public_group_enrich_message_for_manager(
                eottae_talkroom_public_group_format_message($row, $actor_mb_id),
                true,
                $actor_mb_id,
                $is_super_admin
            )
            : ($send['message_row'] ?? null);

        return array(
            'ok'          => true,
            'message'     => 'AI가 메시지를 보냈습니다.',
            'wr_id'       => $wr_id,
            'message_row' => $message_row,
        );
    }
}

if (!function_exists('eottae_talkroom_public_group_delete_chat_message')) {
    /**
     * 공개 단체톡·톡방 채팅 — 메시지 삭제 (최고관리자·작성자)
     *
     * @return array{ok:bool, message:string, wr_id?:int}
     */
    function eottae_talkroom_public_group_delete_chat_message($wr_id, $mb_id, $is_super_admin = false)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return array('ok' => false, 'message' => '삭제할 메시지가 없습니다.');
        }

        if (!function_exists('eottae_talkroom_soft_delete_write')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '') {
            return array('ok' => false, 'message' => '게시판 설정을 찾을 수 없습니다.');
        }

        $write = sql_fetch("
            SELECT *
            FROM `{$write_table}`
            WHERE wr_id = '{$wr_id}'
              AND wr_is_comment = 0
            LIMIT 1
        ");
        if (!$write) {
            return array('ok' => false, 'message' => '메시지를 찾을 수 없습니다.');
        }

        if (!eottae_talkroom_public_group_can_delete_message($write, $mb_id, $is_super_admin)) {
            return array('ok' => false, 'message' => '삭제 권한이 없습니다.');
        }

        if (function_exists('eottae_talkroom_is_post_deleted') && eottae_talkroom_is_post_deleted($write)) {
            return array('ok' => true, 'message' => '이미 삭제된 메시지입니다.', 'wr_id' => $wr_id);
        }

        $board = array('bo_table' => eottae_talkroom_board_table());
        $reason = $is_super_admin ? '공개톡 최고관리자 삭제' : '작성자 삭제';
        $result = eottae_talkroom_soft_delete_write($write, $board, $mb_id, $reason);
        if (empty($result['ok'])) {
            return array(
                'ok'      => false,
                'message' => $result['message'] ?? '삭제에 실패했습니다.',
            );
        }

        return array(
            'ok'      => true,
            'message' => '메시지를 삭제했습니다.',
            'wr_id'   => $wr_id,
        );
    }
}
