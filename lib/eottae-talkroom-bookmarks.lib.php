<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_bookmarks_table')) {
    function eottae_talkroom_bookmarks_table()
    {
        global $g5;

        if (!isset($g5['sebu_talk_bookmarks_table'])) {
            $g5['sebu_talk_bookmarks_table'] = G5_TABLE_PREFIX.'sebu_talk_bookmarks';
        }

        return $g5['sebu_talk_bookmarks_table'];
    }
}

if (!function_exists('eottae_talkroom_bookmarks_proc_url')) {
    function eottae_talkroom_bookmarks_proc_url()
    {
        return G5_URL.'/proc/eottae-talkroom-bookmarks.php';
    }
}

if (!function_exists('eottae_talkroom_bookmark_get_post_row')) {
  /**
     * @return array<string, mixed>|null
     */
    function eottae_talkroom_bookmark_get_post_row($post_id)
    {
        $post_id = (int) $post_id;
        if ($post_id < 1 || !function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return null;
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '' || !eottae_talkroom_table_exists($write_table)) {
            return null;
        }

        $row = sql_fetch("
            SELECT wr_id, wr_subject, wr_name, wr_datetime, wr_comment, ca_name, mb_id, wr_1, wr_2, wr_is_comment
            FROM `{$write_table}`
            WHERE wr_id = '{$post_id}'
              AND wr_is_comment = 0
            LIMIT 1
        ", false);

        return is_array($row) ? $row : null;
    }
}

if (!function_exists('eottae_talkroom_bookmark_can_access_post')) {
    /**
     * @return array{ok: bool, message: string, room: array|null, post: array|null}
     */
    function eottae_talkroom_bookmark_can_access_post($mb_id, $room_id, $post_id, $is_super_admin = false)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $room_id = (int) $room_id;
        $post_id = (int) $post_id;

        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 이용해 주세요.', 'room' => null, 'post' => null);
        }
        if ($room_id < 1 || $post_id < 1) {
            return array('ok' => false, 'message' => '글 정보가 올바르지 않습니다.', 'room' => null, 'post' => null);
        }

        $post = eottae_talkroom_bookmark_get_post_row($post_id);
        if (!$post) {
            return array('ok' => false, 'message' => '글을 찾을 수 없습니다.', 'room' => null, 'post' => null);
        }

        $post_room_id = function_exists('eottae_talkroom_get_write_room_id')
            ? eottae_talkroom_get_write_room_id($post)
            : (int) ($post['wr_1'] ?? 0);
        if ($post_room_id !== $room_id) {
            return array('ok' => false, 'message' => '톡방 정보가 일치하지 않습니다.', 'room' => null, 'post' => $post);
        }

        if (function_exists('eottae_talkroom_is_post_deleted') && eottae_talkroom_is_post_deleted($post)) {
            return array('ok' => false, 'message' => '삭제된 글입니다.', 'room' => null, 'post' => $post);
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array('ok' => false, 'message' => '운영 중인 톡방을 찾을 수 없습니다.', 'room' => null, 'post' => $post);
        }

        if ($is_super_admin) {
            return array('ok' => true, 'message' => '', 'room' => $room, 'post' => $post);
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        $is_owner = eottae_talkroom_is_room_owner($room, $mb_id, $member_row);

        if (($room['visibility'] ?? 'public') === 'private') {
            if (!$is_owner && !eottae_talkroom_is_active_member($member_row)) {
                return array('ok' => false, 'message' => '비공개 톡방 글은 참여 중인 회원만 저장할 수 있습니다.', 'room' => $room, 'post' => $post);
            }
        }

        if (!$is_owner && eottae_talkroom_is_kicked_status($member_row['status'] ?? '')) {
            return array('ok' => false, 'message' => '강퇴된 톡방의 글은 저장할 수 없습니다.', 'room' => $room, 'post' => $post);
        }

        if (!function_exists('eottae_talkroom_can_view_posts') || !eottae_talkroom_can_view_posts($room, $member_row)) {
            if (!$is_owner) {
                return array('ok' => false, 'message' => '글을 열람할 권한이 없습니다.', 'room' => $room, 'post' => $post);
            }
        }

        return array('ok' => true, 'message' => '', 'room' => $room, 'post' => $post);
    }
}

if (!function_exists('eottae_talkroom_bookmark_is_saved')) {
    function eottae_talkroom_bookmark_is_saved($mb_id, $room_id, $post_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $room_id = (int) $room_id;
        $post_id = (int) $post_id;
        if ($mb_id === '' || $room_id < 1 || $post_id < 1) {
            return false;
        }

        $table = eottae_talkroom_bookmarks_table();
        if (!eottae_talkroom_table_exists($table)) {
            return false;
        }

        $row = sql_fetch("
            SELECT id
            FROM `{$table}`
            WHERE mb_id = '".sql_escape_string($mb_id)."'
              AND room_id = '{$room_id}'
              AND post_id = '{$post_id}'
            LIMIT 1
        ", false);

        return !empty($row['id']);
    }
}

if (!function_exists('eottae_talkroom_bookmark_add')) {
    /**
     * @return array{ok: bool, message: string, saved: int}
     */
    function eottae_talkroom_bookmark_add($mb_id, $room_id, $post_id, $is_super_admin = false)
    {
        $access = eottae_talkroom_bookmark_can_access_post($mb_id, $room_id, $post_id, $is_super_admin);
        if (empty($access['ok'])) {
            return array('ok' => false, 'message' => $access['message'], 'saved' => 0);
        }

        if (eottae_talkroom_bookmark_is_saved($mb_id, $room_id, $post_id)) {
            return array('ok' => true, 'message' => '이미 저장된 글입니다.', 'saved' => 1);
        }

        $table = eottae_talkroom_bookmarks_table();
        if (!eottae_talkroom_table_exists($table)) {
            return array('ok' => false, 'message' => '저장 기능을 사용할 수 없습니다.', 'saved' => 0);
        }

        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        $mb_sql = sql_escape_string($mb_id);
        $room_id = (int) $room_id;
        $post_id = (int) $post_id;

        $ok = sql_query("
            INSERT INTO `{$table}`
                (mb_id, room_id, post_id, created_at)
            VALUES
                ('{$mb_sql}', '{$room_id}', '{$post_id}', '".sql_escape_string($now)."')
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '글 저장에 실패했습니다.', 'saved' => 0);
        }

        return array('ok' => true, 'message' => '글을 저장했습니다.', 'saved' => 1);
    }
}

if (!function_exists('eottae_talkroom_bookmark_remove')) {
    /**
     * @return array{ok: bool, message: string, saved: int}
     */
    function eottae_talkroom_bookmark_remove($mb_id, $room_id, $post_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $room_id = (int) $room_id;
        $post_id = (int) $post_id;

        if ($mb_id === '' || $room_id < 1 || $post_id < 1) {
            return array('ok' => false, 'message' => '글 정보가 올바르지 않습니다.', 'saved' => 0);
        }

        $table = eottae_talkroom_bookmarks_table();
        if (!eottae_talkroom_table_exists($table)) {
            return array('ok' => false, 'message' => '저장 기능을 사용할 수 없습니다.', 'saved' => 0);
        }

        sql_query("
            DELETE FROM `{$table}`
            WHERE mb_id = '".sql_escape_string($mb_id)."'
              AND room_id = '{$room_id}'
              AND post_id = '{$post_id}'
            LIMIT 1
        ", false);

        return array('ok' => true, 'message' => '저장을 취소했습니다.', 'saved' => 0);
    }
}

if (!function_exists('eottae_talkroom_bookmark_resolve_access_state')) {
    /**
     * @param array<string, mixed> $room
     * @param array<string, mixed>|null $post
     * @param array<string, mixed>|null $member_row
     */
    function eottae_talkroom_bookmark_resolve_access_state($mb_id, array $room, $post, $member_row, $is_super_admin = false)
    {
        if (!$post) {
            return 'missing';
        }

        if (function_exists('eottae_talkroom_is_post_deleted') && eottae_talkroom_is_post_deleted($post)) {
            return 'deleted';
        }

        if ($is_super_admin) {
            return 'ok';
        }

        $room_id = (int) ($room['room_id'] ?? 0);
        $is_owner = eottae_talkroom_is_room_owner($room, $mb_id, $member_row);

        if (!eottae_talkroom_is_operating_room($room['status'] ?? '')) {
            return 'restricted';
        }

        if (eottae_talkroom_is_kicked_status($member_row['status'] ?? '')) {
            return 'restricted';
        }

        if (($room['visibility'] ?? 'public') === 'private' && !$is_owner && !eottae_talkroom_is_active_member($member_row)) {
            return 'restricted';
        }

        if (!function_exists('eottae_talkroom_can_view_posts') || !eottae_talkroom_can_view_posts($room, $member_row)) {
            if (!$is_owner) {
                return 'restricted';
            }
        }

        $post_room_id = function_exists('eottae_talkroom_get_write_room_id')
            ? eottae_talkroom_get_write_room_id($post)
            : (int) ($post['wr_1'] ?? 0);
        if ($post_room_id !== $room_id) {
            return 'restricted';
        }

        return 'ok';
    }
}

if (!function_exists('eottae_talkroom_bookmark_format_row')) {
    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    function eottae_talkroom_bookmark_format_row(array $row, $mb_id, $is_super_admin = false)
    {
        $bookmark_id = (int) ($row['id'] ?? 0);
        $room_id = (int) ($row['room_id'] ?? 0);
        $post_id = (int) ($row['post_id'] ?? 0);
        if ($bookmark_id < 1 || $room_id < 1 || $post_id < 1) {
            return array();
        }

        $room = array(
            'room_id'    => $room_id,
            'room_name'  => get_text($row['room_name'] ?? ''),
            'emoji'      => eottae_talkroom_display_emoji($row['emoji'] ?? '', $row['category'] ?? ''),
            'visibility' => trim((string) ($row['visibility'] ?? 'public')),
            'status'     => trim((string) ($row['status'] ?? '')),
            'owner_mb_id'=> trim((string) ($row['owner_mb_id'] ?? '')),
        );

        $post = null;
        if (!empty($row['wr_id'])) {
            $post = array(
                'wr_id'       => (int) $row['wr_id'],
                'wr_subject'  => $row['wr_subject'] ?? '',
                'wr_name'     => $row['wr_name'] ?? '',
                'wr_comment'  => (int) ($row['wr_comment'] ?? 0),
                'ca_name'     => $row['ca_name'] ?? '',
                'wr_1'        => $row['wr_1'] ?? '',
                'wr_2'        => $row['wr_2'] ?? '',
            );
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        $access_state = eottae_talkroom_bookmark_resolve_access_state($mb_id, $room, $post, $member_row, $is_super_admin);

        $type_label = function_exists('eottae_talkroom_post_type_label')
            ? eottae_talkroom_post_type_label($post['ca_name'] ?? '')
            : trim((string) ($post['ca_name'] ?? '일반'));

        $created_at = trim((string) ($row['created_at'] ?? ''));
        $saved_label = function_exists('eottae_community_relative_time')
            ? eottae_community_relative_time($created_at)
            : ($created_at !== '' ? substr($created_at, 0, 16) : '');

        $item = array(
            'bookmark_id'   => $bookmark_id,
            'room_id'       => $room_id,
            'post_id'       => $post_id,
            'room_name'     => $room['room_name'],
            'room_emoji'    => $room['emoji'],
            'subject'       => get_text($post['wr_subject'] ?? ''),
            'author'        => get_text($post['wr_name'] ?? ''),
            'type_label'    => $type_label,
            'comment_count' => (int) ($post['wr_comment'] ?? 0),
            'saved_at'      => $created_at,
            'saved_label'   => $saved_label,
            'access_state'  => $access_state,
            'href'          => '',
            'status_label'  => '',
            'can_open'      => 0,
        );

        if ($access_state === 'deleted') {
            $item['subject'] = '삭제된 글입니다';
            $item['status_label'] = '삭제됨';
        } elseif ($access_state === 'missing') {
            $item['subject'] = '삭제된 글입니다';
            $item['status_label'] = '삭제됨';
        } elseif ($access_state === 'restricted') {
            $item['subject'] = '접근 제한된 글입니다';
            $item['author'] = '';
            $item['room_name'] = '톡방';
            $item['type_label'] = '';
            $item['comment_count'] = 0;
            $item['status_label'] = '접근 제한';
        } elseif ($access_state === 'ok') {
            $item['href'] = function_exists('eottae_talkroom_post_view_url')
                ? eottae_talkroom_post_view_url($post_id, $room_id)
                : '';
            $item['can_open'] = $item['href'] !== '' ? 1 : 0;
        }

        return $item;
    }
}

if (!function_exists('eottae_talkroom_bookmark_list')) {
    /**
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    function eottae_talkroom_bookmark_list($mb_id, $limit = 20, $offset = 0, $is_super_admin = false)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $limit = max(1, min(50, (int) $limit));
        $offset = max(0, (int) $offset);

        $empty = array('items' => array(), 'total' => 0);
        if ($mb_id === '') {
            return $empty;
        }

        $table = eottae_talkroom_bookmarks_table();
        if (!eottae_talkroom_table_exists($table)) {
            return $empty;
        }

        if (!function_exists('eottae_talkroom_board_exists') || !eottae_talkroom_board_exists()) {
            return $empty;
        }

        $write_table = eottae_talkroom_write_table();
        $tables = eottae_talkroom_table_names();
        if ($write_table === '' || !eottae_talkroom_table_exists($tables['rooms'])) {
            return $empty;
        }

        $mb_sql = sql_escape_string($mb_id);
        $count_row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$table}` b
            WHERE b.mb_id = '{$mb_sql}'
        ", false);
        $total = (int) ($count_row['cnt'] ?? 0);

        $result = sql_query("
            SELECT
                b.id,
                b.room_id,
                b.post_id,
                b.created_at,
                r.room_name,
                r.emoji,
                r.visibility,
                r.status,
                r.owner_mb_id,
                w.wr_id,
                w.wr_subject,
                w.wr_name,
                w.wr_comment,
                w.ca_name,
                w.wr_1,
                w.wr_2
            FROM `{$table}` b
            INNER JOIN `{$tables['rooms']}` r ON r.room_id = b.room_id
            LEFT JOIN `{$write_table}` w ON w.wr_id = b.post_id AND w.wr_is_comment = 0
            WHERE b.mb_id = '{$mb_sql}'
            ORDER BY b.created_at DESC, b.id DESC
            LIMIT {$offset}, {$limit}
        ", false);

        $items = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $formatted = eottae_talkroom_bookmark_format_row($row, $mb_id, $is_super_admin);
                if (!empty($formatted['bookmark_id'])) {
                    $items[] = $formatted;
                }
            }
        }

        return array(
            'items' => $items,
            'total' => $total,
        );
    }
}
