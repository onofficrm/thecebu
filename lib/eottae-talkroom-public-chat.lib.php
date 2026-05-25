<?php
if (!defined('_GNUBOARD_')) {
    exit;
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

        $post_row = array(
            'wr_id'      => (int) ($row['wr_id'] ?? 0),
            'wr_name'    => $row['wr_name'] ?? '',
            'mb_id'      => $row['mb_id'] ?? '',
            'wr_3'       => $row['wr_3'] ?? '',
            'author'     => get_text($row['wr_name'] ?? ''),
            'text'       => eottae_talkroom_public_group_message_text($row),
            'time_label' => function_exists('eottae_community_relative_time')
                ? eottae_community_relative_time($row['wr_datetime'] ?? '')
                : substr((string) ($row['wr_datetime'] ?? ''), 11, 5),
            'href'       => function_exists('eottae_talkroom_post_view_url')
                ? eottae_talkroom_post_view_url((int) ($row['wr_id'] ?? 0), (int) ($row['wr_1'] ?? 0))
                : '',
        );

        if (function_exists('eottae_talkroom_ai_message_enrich_post_row')) {
            $post_row = eottae_talkroom_ai_message_enrich_post_row($post_row);
        }

        $post_row['is_ai'] = !empty($post_row['is_ai']) ? 1 : 0;
        $post_row['is_mine'] = $viewer_mb_id !== '' && $viewer_mb_id === ($post_row['mb_id'] ?? '');

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
            SELECT wr_id, wr_subject, wr_content, wr_name, wr_datetime, mb_id, wr_3, wr_1
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

if (!function_exists('eottae_talkroom_public_group_chat_payload')) {
    function eottae_talkroom_public_group_chat_payload($limit = 20, $viewer_mb_id = '')
    {
        global $is_member;

        if (!function_exists('eottae_talkroom_enter_url')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room = eottae_talkroom_public_group_room();
        $room_id = $room ? (int) ($room['room_id'] ?? 0) : 0;
        $messages = array();
        $last_wr_id = 0;

        if ($room_id > 0) {
            foreach (eottae_talkroom_public_group_list_messages($room_id, $limit) as $row) {
                $message = eottae_talkroom_public_group_format_message($row, $viewer_mb_id);
                if ($message['text'] === '') {
                    continue;
                }
                $messages[] = $message;
                $last_wr_id = max($last_wr_id, (int) ($message['wr_id'] ?? 0));
            }
        }

        $member_row = null;
        if ($room_id > 0 && $viewer_mb_id !== '') {
            $member_row = eottae_talkroom_get_member_row($room_id, $viewer_mb_id);
        }

        $can_send = !empty($is_member) && $room_id > 0;

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
            'needs_join'   => ($can_send && (!$member_row || ($member_row['status'] ?? '') !== 'active')) ? 1 : 0,
            'login_href'   => function_exists('eottae_login_url') ? eottae_login_url(G5_URL) : G5_BBS_URL.'/login.php',
            'member_token' => !empty($is_member) && function_exists('eottae_talkroom_member_token')
                ? eottae_talkroom_member_token()
                : '',
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

        if (!eottae_talkroom_can_write_posts($room, $member_row)) {
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
