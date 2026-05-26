<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_challenge_reports_table')) {
    function eottae_challenge_reports_table()
    {
        global $g5;
        if (!isset($g5['sebu_challenge_reports_table'])) {
            $g5['sebu_challenge_reports_table'] = G5_TABLE_PREFIX.'sebu_challenge_reports';
        }

        return $g5['sebu_challenge_reports_table'];
    }
}

if (!function_exists('eottae_challenge_reports_ensure_schema')) {
    function eottae_challenge_reports_ensure_schema()
    {
        if (!function_exists('eottae_challenge_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
        }

        $table = eottae_challenge_reports_table();
        if (eottae_challenge_table_exists($table)) {
            return array('ok' => true, 'action' => 'exists');
        }

        $ok = (bool) sql_query("
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `report_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `entry_id` int(11) unsigned NOT NULL DEFAULT '0',
                `reporter_mb_id` varchar(20) NOT NULL DEFAULT '',
                `reason` varchar(30) NOT NULL DEFAULT '',
                `memo` varchar(1000) NOT NULL DEFAULT '',
                `status` varchar(20) NOT NULL DEFAULT 'pending',
                `handled_by` varchar(20) NOT NULL DEFAULT '',
                `handled_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`report_id`),
                UNIQUE KEY `uniq_entry_reporter` (`entry_id`, `reporter_mb_id`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", false);

        return array('ok' => $ok, 'action' => $ok ? 'created' : 'failed');
    }
}

if (!function_exists('eottae_challenge_report_reasons')) {
    function eottae_challenge_report_reasons()
    {
        return array(
            'spam'      => '스팸/광고',
            'fake'      => '허위 인증',
            'abuse'     => '욕설/비방',
            'privacy'   => '개인정보 노출',
            'copyright' => '저작권 침해',
            'other'     => '기타',
        );
    }
}

if (!function_exists('eottae_challenge_report_token')) {
    function eottae_challenge_report_token($regenerate = false)
    {
        $token = get_session('eottae_challenge_report_token');
        if ($regenerate || $token === '') {
            $token = bin2hex(random_bytes(16));
            set_session('eottae_challenge_report_token', $token);
        }

        return (string) $token;
    }
}

if (!function_exists('eottae_challenge_verify_report_token')) {
    function eottae_challenge_verify_report_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_challenge_report_token');

        return $token !== '' && $session_token !== '' && hash_equals((string) $session_token, $token);
    }
}

if (!function_exists('eottae_challenge_submit_report')) {
    function eottae_challenge_submit_report($entry_id, $reporter_mb_id, $reason, $memo = '')
    {
        if (!function_exists('eottae_challenge_get_entry')) {
            include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
        }

        $entry = eottae_challenge_get_entry($entry_id, true);
        if (!$entry) {
            return array('ok' => false, 'message' => '참여글을 찾을 수 없습니다.');
        }

        $reporter_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $reporter_mb_id);
        if ($reporter_mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 신고할 수 있습니다.');
        }

        if ($reporter_mb_id === ($entry['mb_id'] ?? '')) {
            return array('ok' => false, 'message' => '본인 글은 신고할 수 없습니다.');
        }

        $reasons = eottae_challenge_report_reasons();
        $reason = preg_replace('/[^a-z_]/', '', (string) $reason);
        if (!isset($reasons[$reason])) {
            return array('ok' => false, 'message' => '신고 사유를 선택해 주세요.');
        }

        eottae_challenge_reports_ensure_schema();
        $table = eottae_challenge_reports_table();
        $entry_id = (int) $entry_id;
        $memo = trim(strip_tags((string) $memo));

        $exists = sql_fetch("
            SELECT report_id FROM `{$table}`
            WHERE entry_id = '{$entry_id}' AND reporter_mb_id = '".sql_escape_string($reporter_mb_id)."'
            LIMIT 1
        ", false);
        if (!empty($exists['report_id'])) {
            return array('ok' => false, 'message' => '이미 신고한 글입니다.');
        }

        sql_query("
            INSERT INTO `{$table}`
            SET
                entry_id = '{$entry_id}',
                reporter_mb_id = '".sql_escape_string($reporter_mb_id)."',
                reason = '".sql_escape_string($reason)."',
                memo = '".sql_escape_string(substr($memo, 0, 1000))."',
                status = 'pending',
                created_at = '".G5_TIME_YMDHIS."'
        ", false);

        return array('ok' => true, 'message' => '신고가 접수되었습니다.');
    }
}

if (!function_exists('eottae_challenge_list_pending_reports')) {
    function eottae_challenge_list_pending_reports($limit = 50)
    {
        eottae_challenge_reports_ensure_schema();
        $table = eottae_challenge_reports_table();
        $entries = eottae_challenge_entries_table();
        $limit = max(1, min(100, (int) $limit));
        $items = array();

        $result = sql_query("
            SELECT r.*, e.title AS entry_title, e.challenge_id
            FROM `{$table}` r
            INNER JOIN `{$entries}` e ON e.entry_id = r.entry_id
            WHERE r.status = 'pending'
            ORDER BY r.created_at DESC
            LIMIT {$limit}
        ", false);

        while ($row = sql_fetch_array($result)) {
            if (!is_array($row)) {
                continue;
            }
            $reasons = eottae_challenge_report_reasons();
            $items[] = array(
                'report_id'      => (int) ($row['report_id'] ?? 0),
                'entry_id'       => (int) ($row['entry_id'] ?? 0),
                'entry_title'    => get_text($row['entry_title'] ?? ''),
                'challenge_id'   => (int) ($row['challenge_id'] ?? 0),
                'reporter_mb_id' => get_text($row['reporter_mb_id'] ?? ''),
                'reason_label'   => isset($reasons[$row['reason'] ?? '']) ? $reasons[$row['reason']] : '',
                'memo'           => get_text($row['memo'] ?? ''),
                'created_at'     => (string) ($row['created_at'] ?? ''),
            );
        }

        return $items;
    }
}

if (!function_exists('eottae_challenge_handle_report')) {
    function eottae_challenge_handle_report($report_id, $action, $admin_mb_id)
    {
        eottae_challenge_reports_ensure_schema();
        $table = eottae_challenge_reports_table();
        $report_id = (int) $report_id;
        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);

        $row = sql_fetch(" SELECT * FROM `{$table}` WHERE report_id = '{$report_id}' LIMIT 1 ", false);
        if (empty($row['report_id'])) {
            return array('ok' => false, 'message' => '신고를 찾을 수 없습니다.');
        }

        $status = 'reviewed';
        if ($action === 'hide_entry') {
            $entries = eottae_challenge_entries_table();
            sql_query("
                UPDATE `{$entries}`
                SET status = 'hidden', updated_at = '".G5_TIME_YMDHIS."'
                WHERE entry_id = '".(int) $row['entry_id']."'
            ", false);
            $status = 'deleted';
        } elseif ($action === 'reject') {
            $status = 'rejected';
        }

        sql_query("
            UPDATE `{$table}`
            SET status = '".sql_escape_string($status)."',
                handled_by = '".sql_escape_string($admin_mb_id)."',
                handled_at = '".G5_TIME_YMDHIS."'
            WHERE report_id = '{$report_id}'
        ", false);

        if ($status === 'deleted' && function_exists('eottae_member_growth_on_report_confirmed') && !empty($row['reporter_mb_id'])) {
            eottae_member_growth_on_report_confirmed($row['reporter_mb_id'], $report_id, 'report_challenge', '챌린지 신고 처리');
        }

        return array('ok' => true, 'message' => '처리되었습니다.');
    }
}
