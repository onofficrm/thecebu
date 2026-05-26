<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_calendar_reports_table')) {
    function eottae_calendar_reports_table()
    {
        return G5_TABLE_PREFIX.'sebu_calendar_reports';
    }
}

if (!function_exists('eottae_calendar_report_reasons')) {
    function eottae_calendar_report_reasons()
    {
        return array(
            'wrong_datetime' => '날짜/시간이 잘못됨',
            'wrong_location' => '장소가 잘못됨',
            'ended'          => '이미 종료된 일정',
            'duplicate'      => '중복 일정',
            'false_info'     => '허위 정보',
            'inappropriate'  => '부적절한 내용',
            'etc'            => '기타',
        );
    }
}

if (!function_exists('eottae_calendar_report_reason_label')) {
    function eottae_calendar_report_reason_label($reason)
    {
        $reasons = eottae_calendar_report_reasons();

        return isset($reasons[$reason]) ? $reasons[$reason] : '기타';
    }
}

if (!function_exists('eottae_calendar_report_status_label')) {
    function eottae_calendar_report_status_label($status)
    {
        $map = array(
            'pending'  => '접수',
            'reviewed' => '검토중',
            'deleted'  => '일정 처리',
            'rejected' => '기각',
        );

        return isset($map[$status]) ? $map[$status] : $status;
    }
}

if (!function_exists('eottae_calendar_report_status_class')) {
    function eottae_calendar_report_status_class($status)
    {
        $map = array(
            'pending'  => 'talk-apply-status--pending',
            'reviewed' => 'talk-apply-status--approved',
            'deleted'  => 'talk-apply-status--rejected',
            'rejected' => 'talk-apply-status--closed',
        );

        return isset($map[$status]) ? $map[$status] : 'talk-apply-status--pending';
    }
}

if (!function_exists('eottae_calendar_report_token')) {
    function eottae_calendar_report_token($regenerate = false)
    {
        $token = get_session('eottae_calendar_report_token');
        if ($regenerate || $token === '' || $token === null) {
            try {
                $token = bin2hex(random_bytes(16));
            } catch (Exception $e) {
                $token = md5(uniqid((string) mt_rand(), true));
            }
            set_session('eottae_calendar_report_token', $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_calendar_verify_report_token')) {
    function eottae_calendar_verify_report_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_calendar_report_token');

        return $token !== '' && $session_token !== '' && hash_equals($session_token, $token);
    }
}

if (!function_exists('eottae_calendar_admin_reports_url')) {
    function eottae_calendar_admin_reports_url($status = 'pending')
    {
        $url = G5_URL.'/page/eottae-admin-calendar-reports.php';
        if ($status !== '' && $status !== 'all') {
            $url .= '?status='.urlencode($status);
        }

        return $url;
    }
}

if (!function_exists('eottae_calendar_has_reported_event')) {
    function eottae_calendar_has_reported_event($reporter_mb_id, $event_id)
    {
        $table = eottae_calendar_reports_table();
        if (!eottae_calendar_table_exists()) {
            return false;
        }

        $reporter_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $reporter_mb_id);
        $event_id = (int) $event_id;
        if ($reporter_mb_id === '' || $event_id < 1) {
            return false;
        }

        $row = sql_fetch("
            SELECT report_id
            FROM `{$table}`
            WHERE event_id = {$event_id}
              AND reporter_mb_id = '".sql_real_escape_string($reporter_mb_id)."'
            LIMIT 1
        ", false);

        return !empty($row['report_id']);
    }
}

if (!function_exists('eottae_calendar_can_report_event')) {
    function eottae_calendar_can_report_event(array $event, $reporter_mb_id, $is_super = false)
    {
        if (!empty($is_super)) {
            return array('ok' => false, 'message' => '관리자는 신고할 수 없습니다.');
        }

        $reporter_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $reporter_mb_id);
        if ($reporter_mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 신고할 수 있습니다.');
        }

        if (function_exists('eottae_calendar_can_edit_event') && eottae_calendar_can_edit_event($event, $reporter_mb_id, false)) {
            return array('ok' => false, 'message' => '본인 일정은 신고할 수 없습니다.');
        }

        if (eottae_calendar_has_reported_event($reporter_mb_id, (int) ($event['event_id'] ?? 0))) {
            return array('ok' => false, 'message' => '이미 신고한 일정입니다.');
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_calendar_submit_report')) {
    function eottae_calendar_submit_report($event_id, $reporter_mb_id, $reason, $memo = '')
    {
        $event = eottae_calendar_get_event($event_id);
        if (!$event) {
            return array('ok' => false, 'message' => '일정을 찾을 수 없습니다.');
        }

        $check = eottae_calendar_can_report_event($event, $reporter_mb_id, false);
        if (empty($check['ok'])) {
            return $check;
        }

        $reasons = eottae_calendar_report_reasons();
        $reason = preg_replace('/[^a-z_]/', '', (string) $reason);
        if (!isset($reasons[$reason])) {
            return array('ok' => false, 'message' => '신고 사유를 선택해 주세요.');
        }

        $memo = eottae_calendar_clean_text($memo, 1000);
        $table = eottae_calendar_reports_table();
        $now = G5_TIME_YMDHIS;
        $reporter_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $reporter_mb_id);
        $event_id = (int) $event_id;

        $ok = sql_query("
            INSERT INTO `{$table}` SET
                event_id = {$event_id},
                reporter_mb_id = '".sql_real_escape_string($reporter_mb_id)."',
                reason = '".sql_real_escape_string($reason)."',
                memo = '".sql_real_escape_string($memo)."',
                status = 'pending',
                created_at = '{$now}'
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '신고 접수에 실패했습니다.');
        }

        return array('ok' => true, 'message' => '신고가 접수되었습니다. 검토 후 조치하겠습니다.');
    }
}

if (!function_exists('eottae_calendar_format_report_row')) {
    function eottae_calendar_format_report_row(array $row)
    {
        $event_id = (int) ($row['event_id'] ?? 0);
        $event = eottae_calendar_get_event($event_id, true);
        $reporter = get_member($row['reporter_mb_id'] ?? '', 'mb_id,mb_nick');
        $handler = !empty($row['handled_by']) ? get_member($row['handled_by'], 'mb_id,mb_nick') : null;

        return array_merge($row, array(
            'report_id'        => (int) ($row['report_id'] ?? 0),
            'event_id'         => $event_id,
            'event_title'      => $event ? (string) ($event['title'] ?? '') : '(삭제/숨김 일정)',
            'event_href'       => $event ? eottae_calendar_event_url($event_id) : '',
            'event_source'     => $event ? (string) ($event['source_type'] ?? 'local') : '',
            'reason_label'     => eottae_calendar_report_reason_label($row['reason'] ?? ''),
            'status_label'     => eottae_calendar_report_status_label($row['status'] ?? 'pending'),
            'status_class'     => eottae_calendar_report_status_class($row['status'] ?? 'pending'),
            'reporter_nick'    => !empty($reporter['mb_nick']) ? get_text($reporter['mb_nick']) : (string) ($row['reporter_mb_id'] ?? ''),
            'handled_by_nick'  => is_array($handler) && !empty($handler['mb_nick']) ? get_text($handler['mb_nick']) : '',
            'memo'             => get_text((string) ($row['memo'] ?? '')),
        ));
    }
}

if (!function_exists('eottae_calendar_admin_list_reports')) {
    function eottae_calendar_admin_list_reports($status = 'pending', $limit = 200)
    {
        $table = eottae_calendar_reports_table();
        if (!eottae_calendar_table_exists()) {
            return array();
        }

        $limit = max(1, min(500, (int) $limit));
        $where = '1=1';
        if ($status !== 'all') {
            $status = preg_replace('/[^a-z_]/', '', (string) $status);
            $where = " status = '".sql_real_escape_string($status)."' ";
        }

        $result = sql_query("
            SELECT *
            FROM `{$table}`
            WHERE {$where}
            ORDER BY report_id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = eottae_calendar_format_report_row($row);
        }

        return $rows;
    }
}

if (!function_exists('eottae_calendar_admin_pending_report_count')) {
    function eottae_calendar_admin_pending_report_count()
    {
        $table = eottae_calendar_reports_table();
        if (!eottae_calendar_table_exists()) {
            return 0;
        }

        $row = sql_fetch("SELECT COUNT(*) AS cnt FROM `{$table}` WHERE status = 'pending'", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_calendar_get_report')) {
    function eottae_calendar_get_report($report_id)
    {
        $table = eottae_calendar_reports_table();
        $report_id = (int) $report_id;
        if ($report_id < 1) {
            return null;
        }

        $row = sql_fetch("SELECT * FROM `{$table}` WHERE report_id = {$report_id} LIMIT 1", false);
        if (!$row) {
            return null;
        }

        return eottae_calendar_format_report_row($row);
    }
}

if (!function_exists('eottae_calendar_update_report_status')) {
    function eottae_calendar_update_report_status($report_id, $status, $handler_mb_id)
    {
        $table = eottae_calendar_reports_table();
        $report_id = (int) $report_id;
        $status = preg_replace('/[^a-z_]/', '', (string) $status);
        $handler_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $handler_mb_id);
        $now = G5_TIME_YMDHIS;

        return (bool) sql_query("
            UPDATE `{$table}` SET
                status = '".sql_real_escape_string($status)."',
                handled_by = '".sql_real_escape_string($handler_mb_id)."',
                handled_at = '{$now}'
            WHERE report_id = {$report_id}
            LIMIT 1
        ", false);
    }
}

if (!function_exists('eottae_calendar_handle_report')) {
    function eottae_calendar_handle_report($report_id, $action, $handler_mb_id, $is_super = false)
    {
        if (!$is_super) {
            return array('ok' => false, 'message' => '최고관리자만 처리할 수 있습니다.');
        }

        $report = eottae_calendar_get_report($report_id);
        if (!$report) {
            return array('ok' => false, 'message' => '신고를 찾을 수 없습니다.');
        }

        $event_id = (int) ($report['event_id'] ?? 0);

        if ($action === 'review') {
            if (!eottae_calendar_update_report_status($report_id, 'reviewed', $handler_mb_id)) {
                return array('ok' => false, 'message' => '상태 변경에 실패했습니다.');
            }

            return array('ok' => true, 'message' => '신고를 검토 중으로 표시했습니다.');
        }

        if ($action === 'reject') {
            if (!eottae_calendar_update_report_status($report_id, 'rejected', $handler_mb_id)) {
                return array('ok' => false, 'message' => '기각 처리에 실패했습니다.');
            }

            return array('ok' => true, 'message' => '신고를 기각했습니다.');
        }

        if ($action === 'hide_event') {
            $hide = eottae_calendar_hide_event($event_id, $handler_mb_id, $is_super);
            if (empty($hide['ok'])) {
                return $hide;
            }
            if (!eottae_calendar_update_report_status($report_id, 'deleted', $handler_mb_id)) {
                return array('ok' => false, 'message' => '신고 상태 변경에 실패했습니다.');
            }

            return array('ok' => true, 'message' => '일정을 숨김 처리했습니다.');
        }

        if ($action === 'delete_event') {
            $delete = eottae_calendar_delete_event($event_id, $handler_mb_id, $is_super);
            if (empty($delete['ok'])) {
                return $delete;
            }
            if (!eottae_calendar_update_report_status($report_id, 'deleted', $handler_mb_id)) {
                return array('ok' => false, 'message' => '신고 상태 변경에 실패했습니다.');
            }

            return array('ok' => true, 'message' => '일정을 삭제(숨김) 처리했습니다.');
        }

        return array('ok' => false, 'message' => '지원하지 않는 처리입니다.');
    }
}
