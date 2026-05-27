<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_golf_join_admin_assert')) {
    function eottae_golf_join_admin_assert($is_admin)
    {
        if ($is_admin !== 'super') {
            return array('ok' => false, 'message' => '최고관리자만 이용할 수 있습니다.');
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_golf_join_admin_list_posts')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_golf_join_admin_list_posts($status = 'all', $limit = 200)
    {
        $tables = eottae_golf_join_table_names();
        $member_table = eottae_golf_join_member_table();
        if (!eottae_golf_join_table_exists($tables['posts'])) {
            return array();
        }

        $limit = max(1, min(500, (int) $limit));
        $where = ' 1=1 ';
        $status = preg_replace('/[^a-z]/', '', (string) $status);
        if ($status === 'hidden') {
            $where .= " AND deleted_at != '0000-00-00 00:00:00' ";
        } elseif ($status !== 'all' && $status !== '') {
            $where .= " AND deleted_at = '0000-00-00 00:00:00' AND status = '".sql_escape_string($status)."' ";
        } else {
            $where .= " AND deleted_at = '0000-00-00 00:00:00' ";
        }

        $result = sql_query("
            SELECT p.*, mb.mb_nick AS host_nickname
            FROM `{$tables['posts']}` p
            LEFT JOIN `{$member_table}` mb ON mb.mb_id = p.user_id
            WHERE {$where}
            ORDER BY p.id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        $status_labels = eottae_golf_join_post_status_options();
        while ($row = sql_fetch_array($result)) {
            $st = (string) ($row['status'] ?? '');
            $rows[] = array(
                'id'              => (int) ($row['id'] ?? 0),
                'title'           => (string) ($row['title'] ?? ''),
                'golf_course_name'=> (string) ($row['golf_course_name'] ?? ''),
                'region_label'    => eottae_golf_join_region_label($row['region'] ?? ''),
                'host_nickname'   => (string) ($row['host_nickname'] ?? $row['user_id'] ?? ''),
                'user_id'         => (string) ($row['user_id'] ?? ''),
                'status'          => $st,
                'status_label'    => $status_labels[$st] ?? $st,
                'current_count'   => (int) ($row['current_count'] ?? 0),
                'recruit_count'   => (int) ($row['recruit_count'] ?? 0),
                'report_count'    => (int) ($row['report_count'] ?? 0),
                'round_date'      => (string) ($row['round_date'] ?? ''),
                'created_at'      => (string) ($row['created_at'] ?? ''),
                'detail_url'      => eottae_golf_join_detail_url((int) ($row['id'] ?? 0)),
                'is_hidden'       => function_exists('eottae_golf_join_is_post_deleted')
                    ? eottae_golf_join_is_post_deleted($row)
                    : false,
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_golf_join_admin_set_post_status')) {
    function eottae_golf_join_admin_set_post_status($join_id, $status)
    {
        $join_id = (int) $join_id;
        $status = preg_replace('/[^a-z]/', '', (string) $status);
        if (!isset(eottae_golf_join_post_status_options()[$status])) {
            return array('ok' => false, 'message' => '올바른 상태를 선택해 주세요.');
        }

        $tables = eottae_golf_join_table_names();
        $now = G5_TIME_YMDHIS;
        $ok = sql_query("
            UPDATE `{$tables['posts']}` SET
                status = '".sql_escape_string($status)."',
                updated_at = '{$now}'
            WHERE id = '{$join_id}'
            LIMIT 1
        ", false);

        return array('ok' => (bool) $ok, 'message' => $ok ? '상태가 변경되었습니다.' : '변경에 실패했습니다.');
    }
}

if (!function_exists('eottae_golf_join_admin_hide_post')) {
    function eottae_golf_join_admin_hide_post($join_id)
    {
        $join_id = (int) $join_id;
        $tables = eottae_golf_join_table_names();
        $now = G5_TIME_YMDHIS;
        $ok = sql_query("
            UPDATE `{$tables['posts']}` SET
                deleted_at = '{$now}',
                updated_at = '{$now}'
            WHERE id = '{$join_id}'
            LIMIT 1
        ", false);

        return array('ok' => (bool) $ok, 'message' => $ok ? '숨김 처리되었습니다.' : '처리에 실패했습니다.');
    }
}

if (!function_exists('eottae_golf_join_admin_restore_post')) {
    function eottae_golf_join_admin_restore_post($join_id)
    {
        $join_id = (int) $join_id;
        $tables = eottae_golf_join_table_names();
        $now = G5_TIME_YMDHIS;
        $ok = sql_query("
            UPDATE `{$tables['posts']}` SET
                deleted_at = '0000-00-00 00:00:00',
                updated_at = '{$now}'
            WHERE id = '{$join_id}'
            LIMIT 1
        ", false);

        return array('ok' => (bool) $ok, 'message' => $ok ? '복구되었습니다.' : '복구에 실패했습니다.');
    }
}

if (!function_exists('eottae_golf_join_admin_list_reports')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_golf_join_admin_list_reports($status = 'pending', $limit = 200)
    {
        $tables = eottae_golf_join_table_names();
        $member_table = eottae_golf_join_member_table();
        if (!eottae_golf_join_table_exists($tables['reports'])) {
            return array();
        }

        $limit = max(1, min(500, (int) $limit));
        $where = ' 1=1 ';
        $status = preg_replace('/[^a-z]/', '', (string) $status);
        if ($status !== '' && $status !== 'all') {
            $where .= " AND r.status = '".sql_escape_string($status)."' ";
        }

        $result = sql_query("
            SELECT r.*, p.title, p.golf_course_name, mb.mb_nick AS reporter_nick
            FROM `{$tables['reports']}` r
            LEFT JOIN `{$tables['posts']}` p ON p.id = r.post_id
            LEFT JOIN `{$member_table}` mb ON mb.mb_id = r.reporter_user_id
            WHERE {$where}
            ORDER BY r.id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = array(
                'id'           => (int) ($row['id'] ?? 0),
                'post_id'      => (int) ($row['post_id'] ?? 0),
                'post_title'   => (string) ($row['title'] ?? $row['golf_course_name'] ?? ''),
                'reason'       => (string) ($row['reason'] ?? ''),
                'memo'         => (string) ($row['memo'] ?? ''),
                'status'       => (string) ($row['status'] ?? ''),
                'reporter_nick'=> (string) ($row['reporter_nick'] ?? $row['reporter_user_id'] ?? ''),
                'created_at'   => (string) ($row['created_at'] ?? ''),
                'detail_url'   => eottae_golf_join_detail_url((int) ($row['post_id'] ?? 0)),
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_golf_join_admin_resolve_report')) {
    function eottae_golf_join_admin_resolve_report($report_id, $admin_mb_id, $admin_memo = '')
    {
        $report_id = (int) $report_id;
        $tables = eottae_golf_join_table_names();
        $now = G5_TIME_YMDHIS;
        $ok = sql_query("
            UPDATE `{$tables['reports']}` SET
                status = 'resolved',
                admin_memo = '".sql_escape_string(trim((string) $admin_memo))."',
                handled_by = '".sql_escape_string((string) $admin_mb_id)."',
                resolved_at = '{$now}'
            WHERE id = '{$report_id}'
            LIMIT 1
        ", false);

        return array('ok' => (bool) $ok, 'message' => $ok ? '신고 처리가 완료되었습니다.' : '처리에 실패했습니다.');
    }
}

if (!function_exists('eottae_golf_join_admin_save_course')) {
    /**
     * @param array<string, mixed> $input
     */
    function eottae_golf_join_admin_save_course(array $input, $course_id = 0)
    {
        $course_id = (int) $course_id;
        $name = trim((string) ($input['name'] ?? ''));
        $region = preg_replace('/[^a-z_]/', '', (string) ($input['region'] ?? ''));

        if ($name === '') {
            return array('ok' => false, 'message' => '골프장명을 입력해 주세요.');
        }
        if (!isset(eottae_golf_join_region_options()[$region])) {
            return array('ok' => false, 'message' => '지역을 선택해 주세요.');
        }

        $tables = eottae_golf_join_table_names();
        $now = G5_TIME_YMDHIS;
        $fields = "
            region = '".sql_escape_string($region)."',
            name = '".sql_escape_string($name)."',
            address = '".sql_escape_string(trim((string) ($input['address'] ?? '')))."',
            map_url = '".sql_escape_string(trim((string) ($input['map_url'] ?? '')))."',
            phone = '".sql_escape_string(trim((string) ($input['phone'] ?? '')))."',
            price_info = '".sql_escape_string(trim((string) ($input['price_info'] ?? '')))."',
            description = '".sql_escape_string(trim((string) ($input['description'] ?? '')))."',
            is_active = '".(!empty($input['is_active']) ? '1' : '0')."',
            updated_at = '{$now}'
        ";

        if ($course_id > 0) {
            $ok = sql_query(" UPDATE `{$tables['courses']}` SET {$fields} WHERE id = '{$course_id}' LIMIT 1 ", false);
        } else {
            $ok = sql_query(" INSERT INTO `{$tables['courses']}` SET {$fields}, created_at = '{$now}' ", false);
        }

        return array('ok' => (bool) $ok, 'message' => $ok ? '저장되었습니다.' : '저장에 실패했습니다.');
    }
}

if (!function_exists('eottae_golf_join_admin_list_courses_all')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_golf_join_admin_list_courses_all()
    {
        $table = eottae_golf_join_table_names()['courses'];
        if (!eottae_golf_join_table_exists($table)) {
            return array();
        }

        $result = sql_query(" SELECT * FROM `{$table}` ORDER BY is_active DESC, region ASC, name ASC ", false);
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = array_merge($row, array(
                'region_label' => eottae_golf_join_region_label($row['region'] ?? ''),
                'is_active_label' => !empty($row['is_active']) ? '노출' : '비노출',
            ));
        }

        return $rows;
    }
}
