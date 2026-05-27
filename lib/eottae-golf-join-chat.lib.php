<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_golf_join_can_access_chat')) {
    /**
     * @return array{ok: bool, message: string}
     */
    function eottae_golf_join_can_access_chat($join_id, $mb_id)
    {
        $join_id = (int) $join_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($join_id < 1 || $mb_id === '') {
            return array('ok' => false, 'message' => '로그인이 필요합니다.');
        }

        $post = eottae_golf_join_fetch_post_row($join_id);
        if (!$post) {
            return array('ok' => false, 'message' => '조인방을 찾을 수 없습니다.');
        }

        if (function_exists('eottae_golf_join_is_post_deleted') && eottae_golf_join_is_post_deleted($post)) {
            return array('ok' => false, 'message' => '종료된 조인방입니다.');
        }

        if ((string) ($post['status'] ?? '') === 'cancelled') {
            return array('ok' => false, 'message' => '취소된 조인방입니다.');
        }

        $host_id = (string) ($post['user_id'] ?? '');
        if ($mb_id === $host_id) {
            return array('ok' => true, 'message' => '');
        }

        $member = eottae_golf_join_fetch_member_row($join_id, $mb_id);
        if ($member && (string) ($member['status'] ?? '') === 'approved') {
            return array('ok' => true, 'message' => '');
        }

        return array('ok' => false, 'message' => '승인된 멤버만 채팅방에 입장할 수 있습니다.');
    }
}

if (!function_exists('eottae_golf_join_ensure_chat_room')) {
    /**
     * @return array<string, mixed>|null
     */
    function eottae_golf_join_ensure_chat_room($join_id)
    {
        $join_id = (int) $join_id;
        if ($join_id < 1) {
            return null;
        }

        eottae_golf_join_ensure_schema();
        $tables = eottae_golf_join_table_names();
        if (!eottae_golf_join_table_exists($tables['chat_rooms'])) {
            return null;
        }

        $row = sql_fetch("
            SELECT *
            FROM `{$tables['chat_rooms']}`
            WHERE post_id = '{$join_id}'
            LIMIT 1
        ", false);

        if (!empty($row['id'])) {
            return $row;
        }

        $now = G5_TIME_YMDHIS;
        sql_query("
            INSERT INTO `{$tables['chat_rooms']}` SET
                post_id = '{$join_id}',
                status = 'active',
                created_at = '{$now}',
                updated_at = '{$now}'
        ", false);

        $room_id = (int) sql_insert_id();
        if ($room_id < 1) {
            return null;
        }

        return sql_fetch(" SELECT * FROM `{$tables['chat_rooms']}` WHERE id = '{$room_id}' LIMIT 1 ", false);
    }
}

if (!function_exists('eottae_golf_join_chat_list_messages')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_golf_join_chat_list_messages($chat_room_id, $since_id = 0, $limit = 50)
    {
        $chat_room_id = (int) $chat_room_id;
        $since_id = (int) $since_id;
        $limit = max(1, min(100, (int) $limit));

        $tables = eottae_golf_join_table_names();
        $member_table = eottae_golf_join_member_table();
        $where = " m.chat_room_id = '{$chat_room_id}' ";
        if ($since_id > 0) {
            $where .= " AND m.id > '{$since_id}' ";
        }

        $result = sql_query("
            SELECT m.*, mb.mb_nick AS member_nick
            FROM `{$tables['chat_messages']}` m
            LEFT JOIN `{$member_table}` mb ON mb.mb_id = m.user_id
            WHERE {$where}
            ORDER BY m.id ASC
            LIMIT {$limit}
        ", false);

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $nick = trim((string) ($row['member_nick'] ?? ''));
            if ($nick === '') {
                $nick = (string) ($row['user_id'] ?? '회원');
            }
            $rows[] = array(
                'id'         => (int) ($row['id'] ?? 0),
                'user_id'    => (string) ($row['user_id'] ?? ''),
                'nickname'   => $nick,
                'message'    => (string) ($row['message'] ?? ''),
                'is_system'  => !empty($row['is_system']),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'time_label' => eottae_golf_join_chat_time_label($row['created_at'] ?? ''),
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_golf_join_chat_time_label')) {
    function eottae_golf_join_chat_time_label($datetime)
    {
        $datetime = (string) $datetime;
        if ($datetime === '' || $datetime === '0000-00-00 00:00:00') {
            return '';
        }

        $ts = strtotime($datetime);
        if (!$ts) {
            return $datetime;
        }

        if (date('Y-m-d', $ts) === date('Y-m-d')) {
            return date('H:i', $ts);
        }

        return date('n/j H:i', $ts);
    }
}

if (!function_exists('eottae_golf_join_chat_send_message')) {
    /**
     * @param array<string, mixed> $member
     * @return array{ok: bool, message: string, message_id?: int}
     */
    function eottae_golf_join_chat_send_message($join_id, array $member, $text)
    {
        $join_id = (int) $join_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($member['mb_id'] ?? ''));
        $text = trim((string) $text);

        if ($join_id < 1 || $mb_id === '') {
            return array('ok' => false, 'message' => '잘못된 요청입니다.');
        }

        if ($text === '') {
            return array('ok' => false, 'message' => '메시지를 입력해 주세요.');
        }

        if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > 500) {
            return array('ok' => false, 'message' => '메시지는 500자 이내로 입력해 주세요.');
        }

        $access = eottae_golf_join_can_access_chat($join_id, $mb_id);
        if (empty($access['ok'])) {
            return $access;
        }

        $room = eottae_golf_join_ensure_chat_room($join_id);
        if (!$room || empty($room['id'])) {
            return array('ok' => false, 'message' => '채팅방을 열 수 없습니다.');
        }

        $tables = eottae_golf_join_table_names();
        $now = G5_TIME_YMDHIS;
        $ok = sql_query("
            INSERT INTO `{$tables['chat_messages']}` SET
                chat_room_id = '".(int) $room['id']."',
                user_id = '".sql_escape_string($mb_id)."',
                message = '".sql_escape_string($text)."',
                is_system = '0',
                created_at = '{$now}'
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '메시지 전송에 실패했습니다.');
        }

        sql_query("
            UPDATE `{$tables['chat_rooms']}` SET updated_at = '{$now}'
            WHERE id = '".(int) $room['id']."'
            LIMIT 1
        ", false);

        return array(
            'ok'         => true,
            'message'    => '전송되었습니다.',
            'message_id' => (int) sql_insert_id(),
        );
    }
}

if (!function_exists('eottae_golf_join_chat_build_context')) {
    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    function eottae_golf_join_chat_build_context($join_id, $viewer_mb_id, array $post = array())
    {
        $join_id = (int) $join_id;
        $viewer_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $viewer_mb_id);
        if (!$post) {
            $post = eottae_golf_join_fetch_post_row($join_id) ?: array();
        }

        $access = eottae_golf_join_can_access_chat($join_id, $viewer_mb_id);
        $room = $access['ok'] ? eottae_golf_join_ensure_chat_room($join_id) : null;

        return array(
            'join_id'      => $join_id,
            'post'         => $post,
            'can_access'   => !empty($access['ok']),
            'access_message' => $access['message'] ?? '',
            'chat_room_id' => (int) ($room['id'] ?? 0),
            'title'        => (string) ($post['golf_course_name'] ?? '골프조인 채팅'),
        );
    }
}
