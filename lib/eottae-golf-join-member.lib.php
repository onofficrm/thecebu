<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_golf_join_owner_token')) {
    function eottae_golf_join_owner_token($regenerate = false)
    {
        $token = get_session('eottae_golf_join_owner_token');
        if ($regenerate || $token === '') {
            $token = bin2hex(random_bytes(16));
            set_session('eottae_golf_join_owner_token', $token);
        }

        return (string) $token;
    }
}

if (!function_exists('eottae_golf_join_verify_owner_token')) {
    function eottae_golf_join_verify_owner_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_golf_join_owner_token');

        return $token !== '' && $session_token !== '' && hash_equals((string) $session_token, $token);
    }
}

if (!function_exists('eottae_golf_join_fetch_post_row')) {
    /**
     * @return array<string, mixed>|null
     */
    function eottae_golf_join_fetch_post_row($join_id)
    {
        $join_id = (int) $join_id;
        if ($join_id < 1) {
            return null;
        }

        $tables = eottae_golf_join_table_names();
        if (!eottae_golf_join_table_exists($tables['posts'])) {
            return null;
        }

        return sql_fetch("
            SELECT *
            FROM `{$tables['posts']}`
            WHERE id = '{$join_id}'
              AND deleted_at = '0000-00-00 00:00:00'
            LIMIT 1
        ", false);
    }
}

if (!function_exists('eottae_golf_join_fetch_member_row')) {
    /**
     * @return array<string, mixed>|null
     */
    function eottae_golf_join_fetch_member_row($join_id, $user_id)
    {
        $join_id = (int) $join_id;
        $user_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $user_id);
        if ($join_id < 1 || $user_id === '') {
            return null;
        }

        $tables = eottae_golf_join_table_names();
        if (!eottae_golf_join_table_exists($tables['members'])) {
            return null;
        }

        return sql_fetch("
            SELECT *
            FROM `{$tables['members']}`
            WHERE post_id = '{$join_id}'
              AND user_id = '".sql_escape_string($user_id)."'
            LIMIT 1
        ", false);
    }
}

if (!function_exists('eottae_golf_join_fetch_member_by_id')) {
    /**
     * @return array<string, mixed>|null
     */
    function eottae_golf_join_fetch_member_by_id($member_id, $join_id = 0)
    {
        $member_id = (int) $member_id;
        $join_id = (int) $join_id;
        if ($member_id < 1) {
            return null;
        }

        $tables = eottae_golf_join_table_names();
        if (!eottae_golf_join_table_exists($tables['members'])) {
            return null;
        }

        $where = " id = '{$member_id}' ";
        if ($join_id > 0) {
            $where .= " AND post_id = '{$join_id}' ";
        }

        return sql_fetch(" SELECT * FROM `{$tables['members']}` WHERE {$where} LIMIT 1 ", false);
    }
}

if (!function_exists('eottae_golf_join_member_gender_label')) {
    function eottae_golf_join_member_gender_label($gender)
    {
        $gender = strtoupper(substr((string) $gender, 0, 1));
        $map = array('M' => '남성', 'F' => '여성');

        return $map[$gender] ?? '미입력';
    }
}

if (!function_exists('eottae_golf_join_member_age_label')) {
    function eottae_golf_join_member_age_label($code)
    {
        $code = preg_replace('/[^a-z0-9_]/', '', (string) $code);
        $options = eottae_golf_join_age_preference_options();

        return isset($options[$code]) ? $options[$code] : (($code !== '') ? $code : '미입력');
    }
}

if (!function_exists('eottae_golf_join_member_score_label')) {
    function eottae_golf_join_member_score_label($code)
    {
        $code = preg_replace('/[^a-z0-9_]/', '', (string) $code);
        $options = eottae_golf_join_score_preference_options();

        return isset($options[$code]) ? $options[$code] : (($code !== '') ? $code : '미입력');
    }
}

if (!function_exists('eottae_golf_join_format_member_display')) {
    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    function eottae_golf_join_format_member_display(array $row)
    {
        $created = (string) ($row['created_at'] ?? '');
        $created_label = '';
        if ($created !== '' && $created !== '0000-00-00 00:00:00') {
            $ts = strtotime($created);
            $created_label = $ts ? date('n월 j일 H:i', $ts) : $created;
        }

        return array_merge($row, array(
            'gender_label'    => eottae_golf_join_member_gender_label($row['gender'] ?? ''),
            'age_group_label' => eottae_golf_join_member_age_label($row['age_group'] ?? ''),
            'score_range_label' => eottae_golf_join_member_score_label($row['score_range'] ?? ''),
            'created_at_label'  => $created_label,
        ));
    }
}

if (!function_exists('eottae_golf_join_count_approved_members')) {
    function eottae_golf_join_count_approved_members($join_id)
    {
        $join_id = (int) $join_id;
        $tables = eottae_golf_join_table_names();
        if ($join_id < 1 || !eottae_golf_join_table_exists($tables['members'])) {
            return 0;
        }

        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$tables['members']}`
            WHERE post_id = '{$join_id}'
              AND status = 'approved'
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_golf_join_refresh_post_counts')) {
    /**
     * 승인 인원 기준 current_count·status 동기화
     *
     * @return array{current_count: int, status: string}
     */
    function eottae_golf_join_refresh_post_counts($join_id)
    {
        $join_id = (int) $join_id;
        $post = eottae_golf_join_fetch_post_row($join_id);
        if (!$post) {
            return array('current_count' => 0, 'status' => '');
        }

        $approved = eottae_golf_join_count_approved_members($join_id);
        $recruit = max(1, (int) ($post['recruit_count'] ?? 1));
        $status = (string) ($post['status'] ?? 'recruiting');

        if ($status === 'recruiting' && $approved >= $recruit) {
            $status = 'full';
        } elseif ($status === 'full' && $approved < $recruit) {
            $status = 'recruiting';
        }

        $tables = eottae_golf_join_table_names();
        $now = G5_TIME_YMDHIS;
        sql_query("
            UPDATE `{$tables['posts']}` SET
                current_count = '{$approved}',
                status = '".sql_escape_string($status)."',
                updated_at = '{$now}'
            WHERE id = '{$join_id}'
            LIMIT 1
        ", false);

        return array(
            'current_count' => $approved,
            'status'        => $status,
        );
    }
}

if (!function_exists('eottae_golf_join_has_seat_available')) {
    function eottae_golf_join_has_seat_available(array $post)
    {
        $current = (int) ($post['current_count'] ?? 0);
        $recruit = max(1, (int) ($post['recruit_count'] ?? 1));

        return $current < $recruit;
    }
}

if (!function_exists('eottae_golf_join_can_apply_check')) {
    /**
     * @param array<string, mixed> $post
     * @return array{ok: bool, message: string}
     */
    function eottae_golf_join_can_apply_check(array $post, $viewer_mb_id)
    {
        $viewer_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $viewer_mb_id);
        if ($viewer_mb_id === '') {
            return array('ok' => false, 'message' => '로그인이 필요합니다.');
        }

        $host_id = (string) ($post['user_id'] ?? '');
        if ($viewer_mb_id === $host_id) {
            return array('ok' => false, 'message' => '방장은 조인을 신청할 수 없습니다.');
        }

        $status = (string) ($post['status'] ?? '');
        if ($status !== 'recruiting') {
            return array('ok' => false, 'message' => '모집이 마감되어 신청할 수 없습니다.');
        }

        if (!eottae_golf_join_has_seat_available($post)) {
            return array('ok' => false, 'message' => '모집 인원이 모두 찼습니다.');
        }

        $member_row = eottae_golf_join_fetch_member_row((int) ($post['id'] ?? 0), $viewer_mb_id);
        if ($member_row) {
            $m_status = (string) ($member_row['status'] ?? '');
            if ($m_status === 'pending') {
                return array('ok' => false, 'message' => '이미 신청한 조인입니다.');
            }
            if ($m_status === 'approved') {
                return array('ok' => false, 'message' => '이미 참여 확정된 조인입니다.');
            }
            if ($m_status === 'rejected') {
                return array('ok' => false, 'message' => '이미 처리된 신청입니다. 다시 신청할 수 없습니다.');
            }
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_golf_join_apply')) {
    /**
     * @param array<string, mixed> $member
     * @return array{ok: bool, message: string}
     */
    function eottae_golf_join_apply($join_id, array $member, $message = '')
    {
        $join_id = (int) $join_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($member['mb_id'] ?? ''));
        if ($join_id < 1 || $mb_id === '') {
            return array('ok' => false, 'message' => '잘못된 요청입니다.');
        }

        $post = eottae_golf_join_fetch_post_row($join_id);
        if (!$post) {
            return array('ok' => false, 'message' => '조인방을 찾을 수 없습니다.');
        }

        $check = eottae_golf_join_can_apply_check($post, $mb_id);
        if (empty($check['ok'])) {
            return $check;
        }

        $profile = eottae_golf_join_host_profile_from_member($member);
        $message = trim((string) $message);
        if (function_exists('mb_substr')) {
            $message = mb_substr($message, 0, 500, 'UTF-8');
        } else {
            $message = substr($message, 0, 500);
        }

        $tables = eottae_golf_join_table_names();
        $now = G5_TIME_YMDHIS;
        $existing = eottae_golf_join_fetch_member_row($join_id, $mb_id);

        if ($existing && !empty($existing['id'])) {
            $member_id = (int) $existing['id'];
            $prev_status = (string) ($existing['status'] ?? '');
            if (!in_array($prev_status, array('cancelled'), true)) {
                return array('ok' => false, 'message' => '이미 신청한 조인입니다.');
            }

            $ok = sql_query("
                UPDATE `{$tables['members']}` SET
                    status = 'pending',
                    role = 'member',
                    nickname = '".sql_escape_string($profile['nickname'])."',
                    gender = '".sql_escape_string($profile['gender'])."',
                    age_group = '".sql_escape_string($profile['age_group'])."',
                    score_range = '".sql_escape_string($profile['score_range'])."',
                    message = '".sql_escape_string($message)."',
                    updated_at = '{$now}'
                WHERE id = '{$member_id}'
                  AND post_id = '{$join_id}'
                  AND user_id = '".sql_escape_string($mb_id)."'
                LIMIT 1
            ", false);
        } else {
            $ok = sql_query("
                INSERT INTO `{$tables['members']}` SET
                    post_id = '{$join_id}',
                    user_id = '".sql_escape_string($mb_id)."',
                    role = 'member',
                    status = 'pending',
                    nickname = '".sql_escape_string($profile['nickname'])."',
                    gender = '".sql_escape_string($profile['gender'])."',
                    age_group = '".sql_escape_string($profile['age_group'])."',
                    score_range = '".sql_escape_string($profile['score_range'])."',
                    message = '".sql_escape_string($message)."',
                    created_at = '{$now}',
                    updated_at = '{$now}'
            ", false);
        }

        if (!$ok) {
            return array('ok' => false, 'message' => '신청 처리에 실패했습니다.');
        }

        return array('ok' => true, 'message' => '조인 신청이 완료되었습니다. 방장의 승인을 기다려 주세요.');
    }
}

if (!function_exists('eottae_golf_join_cancel_apply')) {
    /**
     * @return array{ok: bool, message: string}
     */
    function eottae_golf_join_cancel_apply($join_id, $mb_id)
    {
        $join_id = (int) $join_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($join_id < 1 || $mb_id === '') {
            return array('ok' => false, 'message' => '잘못된 요청입니다.');
        }

        $row = eottae_golf_join_fetch_member_row($join_id, $mb_id);
        if (!$row) {
            return array('ok' => false, 'message' => '신청 내역을 찾을 수 없습니다.');
        }

        if ((string) ($row['status'] ?? '') !== 'pending') {
            return array('ok' => false, 'message' => '승인 대기 중인 신청만 취소할 수 있습니다.');
        }

        $tables = eottae_golf_join_table_names();
        $now = G5_TIME_YMDHIS;
        $ok = sql_query("
            UPDATE `{$tables['members']}` SET
                status = 'cancelled',
                updated_at = '{$now}'
            WHERE id = '".(int) ($row['id'] ?? 0)."'
              AND post_id = '{$join_id}'
              AND user_id = '".sql_escape_string($mb_id)."'
              AND status = 'pending'
            LIMIT 1
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '신청 취소에 실패했습니다.');
        }

        return array('ok' => true, 'message' => '조인 신청을 취소했습니다.');
    }
}

if (!function_exists('eottae_golf_join_assert_host')) {
    /**
     * @return array{ok: bool, message: string, post?: array<string, mixed>}
     */
    function eottae_golf_join_assert_host($join_id, $mb_id)
    {
        $join_id = (int) $join_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($join_id < 1 || $mb_id === '') {
            return array('ok' => false, 'message' => '권한이 없습니다.');
        }

        $post = eottae_golf_join_fetch_post_row($join_id);
        if (!$post) {
            return array('ok' => false, 'message' => '조인방을 찾을 수 없습니다.');
        }

        if ((string) ($post['user_id'] ?? '') !== $mb_id) {
            return array('ok' => false, 'message' => '방장만 이용할 수 있습니다.');
        }

        return array('ok' => true, 'message' => '', 'post' => $post);
    }
}

if (!function_exists('eottae_golf_join_list_pending_members')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_golf_join_list_pending_members($join_id)
    {
        $join_id = (int) $join_id;
        $tables = eottae_golf_join_table_names();
        if ($join_id < 1 || !eottae_golf_join_table_exists($tables['members'])) {
            return array();
        }

        $result = sql_query("
            SELECT *
            FROM `{$tables['members']}`
            WHERE post_id = '{$join_id}'
              AND status = 'pending'
              AND role != 'host'
            ORDER BY created_at ASC
        ", false);

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = eottae_golf_join_format_member_display($row);
        }

        return $rows;
    }
}

if (!function_exists('eottae_golf_join_approve_member')) {
    /**
     * @return array{ok: bool, message: string}
     */
    function eottae_golf_join_approve_member($join_id, $member_id, $host_mb_id)
    {
        $join_id = (int) $join_id;
        $member_id = (int) $member_id;
        $access = eottae_golf_join_assert_host($join_id, $host_mb_id);
        if (empty($access['ok'])) {
            return array('ok' => false, 'message' => $access['message']);
        }

        $post = $access['post'];
        if ((string) ($post['status'] ?? '') !== 'recruiting') {
            return array('ok' => false, 'message' => '모집 중인 조인만 승인할 수 있습니다.');
        }

        $approved = eottae_golf_join_count_approved_members($join_id);
        $recruit = max(1, (int) ($post['recruit_count'] ?? 1));
        if ($approved >= $recruit) {
            eottae_golf_join_refresh_post_counts($join_id);

            return array('ok' => false, 'message' => '모집 인원이 가득 찼습니다. 더 이상 승인할 수 없습니다.');
        }

        $member_row = eottae_golf_join_fetch_member_by_id($member_id, $join_id);
        if (!$member_row) {
            return array('ok' => false, 'message' => '신청자를 찾을 수 없습니다.');
        }

        if ((string) ($member_row['status'] ?? '') !== 'pending') {
            return array('ok' => false, 'message' => '승인 대기 중인 신청만 처리할 수 있습니다.');
        }

        if ((string) ($member_row['role'] ?? '') === 'host') {
            return array('ok' => false, 'message' => '방장은 승인 대상이 아닙니다.');
        }

        $tables = eottae_golf_join_table_names();
        $now = G5_TIME_YMDHIS;
        $ok = sql_query("
            UPDATE `{$tables['members']}` SET
                status = 'approved',
                updated_at = '{$now}'
            WHERE id = '{$member_id}'
              AND post_id = '{$join_id}'
              AND status = 'pending'
            LIMIT 1
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '승인 처리에 실패했습니다.');
        }

        $counts = eottae_golf_join_refresh_post_counts($join_id);
        $msg = '참여를 승인했습니다.';
        if (($counts['status'] ?? '') === 'full') {
            $msg .= ' 모집이 완료되었습니다.';
        }

        return array('ok' => true, 'message' => $msg);
    }
}

if (!function_exists('eottae_golf_join_reject_member')) {
    /**
     * @return array{ok: bool, message: string}
     */
    function eottae_golf_join_reject_member($join_id, $member_id, $host_mb_id)
    {
        $join_id = (int) $join_id;
        $member_id = (int) $member_id;
        $access = eottae_golf_join_assert_host($join_id, $host_mb_id);
        if (empty($access['ok'])) {
            return array('ok' => false, 'message' => $access['message']);
        }

        $member_row = eottae_golf_join_fetch_member_by_id($member_id, $join_id);
        if (!$member_row) {
            return array('ok' => false, 'message' => '신청자를 찾을 수 없습니다.');
        }

        if ((string) ($member_row['status'] ?? '') !== 'pending') {
            return array('ok' => false, 'message' => '승인 대기 중인 신청만 거절할 수 있습니다.');
        }

        $tables = eottae_golf_join_table_names();
        $now = G5_TIME_YMDHIS;
        $ok = sql_query("
            UPDATE `{$tables['members']}` SET
                status = 'rejected',
                updated_at = '{$now}'
            WHERE id = '{$member_id}'
              AND post_id = '{$join_id}'
              AND status = 'pending'
            LIMIT 1
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '거절 처리에 실패했습니다.');
        }

        return array('ok' => true, 'message' => '신청을 거절했습니다.');
    }
}

if (!function_exists('eottae_golf_join_close_post')) {
    /**
     * @return array{ok: bool, message: string}
     */
    function eottae_golf_join_close_post($join_id, $host_mb_id)
    {
        $join_id = (int) $join_id;
        $access = eottae_golf_join_assert_host($join_id, $host_mb_id);
        if (empty($access['ok'])) {
            return array('ok' => false, 'message' => $access['message']);
        }

        $post = $access['post'];
        $status = (string) ($post['status'] ?? '');
        if (in_array($status, array('closed', 'cancelled'), true)) {
            return array('ok' => false, 'message' => '이미 마감된 조인입니다.');
        }

        $tables = eottae_golf_join_table_names();
        $now = G5_TIME_YMDHIS;
        $ok = sql_query("
            UPDATE `{$tables['posts']}` SET
                status = 'closed',
                updated_at = '{$now}'
            WHERE id = '{$join_id}'
            LIMIT 1
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '모집 마감에 실패했습니다.');
        }

        return array('ok' => true, 'message' => '모집을 마감했습니다.');
    }
}
