<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_reports_table')) {
    function eottae_column_reports_table()
    {
        global $g5;
        if (!isset($g5['sebu_column_reports_table'])) {
            $g5['sebu_column_reports_table'] = G5_TABLE_PREFIX.'sebu_column_reports';
        }

        return $g5['sebu_column_reports_table'];
    }
}

if (!function_exists('eottae_column_reports_ensure_schema')) {
    function eottae_column_reports_ensure_schema()
    {
        $table = eottae_column_reports_table();
        if (!function_exists('eottae_column_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }
        if (eottae_column_table_exists($table)) {
            return array('ok' => true, 'action' => 'exists');
        }

        $ok = (bool) sql_query("
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `report_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `wr_id` int(11) unsigned NOT NULL DEFAULT '0',
                `reporter_mb_id` varchar(20) NOT NULL DEFAULT '',
                `reason` varchar(40) NOT NULL DEFAULT '',
                `memo` varchar(500) NOT NULL DEFAULT '',
                `status` varchar(20) NOT NULL DEFAULT 'pending',
                `handled_by` varchar(20) NOT NULL DEFAULT '',
                `handled_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`report_id`),
                KEY `idx_column_report_wr` (`wr_id`),
                KEY `idx_column_report_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", false);

        return array('ok' => $ok, 'action' => $ok ? 'created' : 'failed');
    }
}

if (!function_exists('eottae_column_report_reasons')) {
    function eottae_column_report_reasons()
    {
        return array(
            'wrong_info'  => '잘못된 정보',
            'ad'          => '광고/홍보',
            'privacy'     => '개인정보 노출',
            'abuse'       => '비방/분쟁',
            'other'       => '기타',
        );
    }
}

if (!function_exists('eottae_column_report_token')) {
    function eottae_column_report_token($regenerate = false)
    {
        $token = get_session('eottae_column_report_token');
        if ($regenerate || $token === '') {
            $token = bin2hex(random_bytes(16));
            set_session('eottae_column_report_token', $token);
        }

        return (string) $token;
    }
}

if (!function_exists('eottae_column_verify_report_token')) {
    function eottae_column_verify_report_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_column_report_token');

        return $token !== '' && $session_token !== '' && hash_equals((string) $session_token, $token);
    }
}

if (!function_exists('eottae_column_submit_report')) {
    function eottae_column_submit_report($wr_id, $mb_id, $reason, $memo = '')
    {
        $wr_id = (int) $wr_id;
        $mb_id = trim((string) $mb_id);
        $reason = preg_replace('/[^a-z_]/', '', (string) $reason);
        $reasons = eottae_column_report_reasons();

        if ($wr_id < 1 || $mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 신고할 수 있습니다.');
        }
        if (!isset($reasons[$reason])) {
            return array('ok' => false, 'message' => '신고 사유를 선택해 주세요.');
        }

        if (!function_exists('eottae_column_get_write_row')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }
        if (!eottae_column_get_write_row($wr_id)) {
            return array('ok' => false, 'message' => '컬럼을 찾을 수 없습니다.');
        }

        eottae_column_reports_ensure_schema();
        $table = eottae_column_reports_table();
        $mb_id_sql = sql_escape_string($mb_id);

        $dup = sql_fetch("
            SELECT report_id FROM `{$table}`
            WHERE wr_id = '{$wr_id}' AND reporter_mb_id = '{$mb_id_sql}' AND status = 'pending'
            LIMIT 1
        ", false);
        if (!empty($dup['report_id'])) {
            return array('ok' => false, 'message' => '이미 신고 접수된 컬럼입니다.');
        }

        sql_query(" INSERT INTO `{$table}` SET
            wr_id = '{$wr_id}',
            reporter_mb_id = '{$mb_id_sql}',
            reason = '".sql_escape_string($reason)."',
            memo = '".sql_escape_string(trim(strip_tags((string) $memo)))."',
            status = 'pending',
            created_at = '".G5_TIME_YMDHIS."'
        ", false);

        return array('ok' => true, 'message' => '신고가 접수되었습니다.');
    }
}

if (!function_exists('eottae_column_list_pending_reports')) {
    function eottae_column_list_pending_reports($limit = 50)
    {
        eottae_column_reports_ensure_schema();
        if (!function_exists('eottae_column_write_table')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }

        global $g5;
        $table = eottae_column_reports_table();
        $write_table = eottae_column_write_table();
        $limit = max(1, min(100, (int) $limit));

        $result = sql_query("
            SELECT r.*, w.wr_subject, w.mb_id AS author_mb_id
            FROM `{$table}` r
            LEFT JOIN `{$write_table}` w ON w.wr_id = r.wr_id
            WHERE r.status = 'pending'
            ORDER BY r.report_id DESC
            LIMIT {$limit}
        ", false);

        $items = array();
        $reasons = eottae_column_report_reasons();
        while ($row = sql_fetch_array($result)) {
            $row['reason_label'] = $reasons[$row['reason']] ?? $row['reason'];
            $row['view_url'] = eottae_column_view_url($row['wr_id']);
            $items[] = $row;
        }

        return $items;
    }
}

if (!function_exists('eottae_column_handle_report')) {
    function eottae_column_handle_report($report_id, $action, $admin_mb_id)
    {
        $report_id = (int) $report_id;
        $action = preg_replace('/[^a-z_]/', '', (string) $action);
        if ($report_id < 1 || !in_array($action, array('dismiss', 'hide', 'delete'), true)) {
            return array('ok' => false, 'message' => '잘못된 요청입니다.');
        }

        eottae_column_reports_ensure_schema();
        if (!function_exists('eottae_column_admin_set_flags')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }

        $table = eottae_column_reports_table();
        $report = sql_fetch(" SELECT * FROM `{$table}` WHERE report_id = '{$report_id}' LIMIT 1 ", false);
        if (empty($report['report_id'])) {
            return array('ok' => false, 'message' => '신고를 찾을 수 없습니다.');
        }

        $wr_id = (int) $report['wr_id'];
        if ($action === 'hide') {
            eottae_column_admin_set_flags($wr_id, array('status' => 'hidden'));
        } elseif ($action === 'delete') {
            eottae_column_admin_set_flags($wr_id, array('status' => 'hidden'));
        }

        $status = $action === 'dismiss' ? 'dismissed' : 'handled';
        sql_query(" UPDATE `{$table}` SET
            status = '".sql_escape_string($status)."',
            handled_by = '".sql_escape_string($admin_mb_id)."',
            handled_at = '".G5_TIME_YMDHIS."'
            WHERE report_id = '{$report_id}'
        ", false);

        if ($action !== 'dismiss' && function_exists('eottae_member_growth_add_score') && !empty($report['reporter_mb_id'])) {
            include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
            eottae_member_growth_add_score($report['reporter_mb_id'], 'report_confirmed', 0, 'column', $wr_id, '컬럼 신고 처리');
        }

        return array('ok' => true, 'message' => '처리되었습니다.');
    }
}
