<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_plaza_reports_table')) {
    function eottae_plaza_reports_table()
    {
        global $g5;
        if (!isset($g5['sebu_plaza_reports_table'])) {
            $g5['sebu_plaza_reports_table'] = G5_TABLE_PREFIX.'sebu_plaza_reports';
        }

        return $g5['sebu_plaza_reports_table'];
    }
}

if (!function_exists('eottae_plaza_report_reasons')) {
    function eottae_plaza_report_reasons()
    {
        return array(
            'ad_spam'   => '광고/도배',
            'abuse'     => '욕설/비방',
            'privacy'   => '개인정보 노출',
            'scam'      => '사기 의심',
            'illegal'   => '음란/불법',
            'politics'  => '정치/종교 분쟁',
            'off_topic' => '주제와 무관',
            'etc'       => '기타',
        );
    }
}

if (!function_exists('eottae_plaza_report_token')) {
    function eottae_plaza_report_token($regenerate = false)
    {
        $token = get_session('eottae_plaza_report_token');
        if ($regenerate || $token === '') {
            $token = bin2hex(random_bytes(16));
            set_session('eottae_plaza_report_token', $token);
        }

        return (string) $token;
    }
}

if (!function_exists('eottae_plaza_verify_report_token')) {
    function eottae_plaza_verify_report_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_plaza_report_token');

        return $token !== '' && $session_token !== '' && hash_equals((string) $session_token, $token);
    }
}

if (!function_exists('eottae_plaza_reports_ensure_schema')) {
    function eottae_plaza_reports_ensure_schema()
    {
        $table = eottae_plaza_reports_table();
        if (!function_exists('eottae_talkroom_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }
        if (eottae_talkroom_table_exists($table)) {
            return array('ok' => true, 'action' => 'exists');
        }

        $ok = (bool) sql_query("
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `report_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `target_type` varchar(20) NOT NULL DEFAULT '',
                `target_id` int(11) unsigned NOT NULL DEFAULT '0',
                `reporter_mb_id` varchar(20) NOT NULL DEFAULT '',
                `reason` varchar(30) NOT NULL DEFAULT '',
                `memo` varchar(500) NOT NULL DEFAULT '',
                `status` varchar(20) NOT NULL DEFAULT 'pending',
                `handled_by` varchar(20) NOT NULL DEFAULT '',
                `handled_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`report_id`),
                UNIQUE KEY `uk_plaza_report_reporter_target` (`reporter_mb_id`, `target_type`, `target_id`),
                KEY `idx_plaza_report_status` (`status`),
                KEY `idx_plaza_report_target` (`target_type`, `target_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", false);

        return array('ok' => $ok, 'action' => $ok ? 'created' : 'failed');
    }
}

if (!function_exists('eottae_plaza_get_report_target_write')) {
    function eottae_plaza_get_report_target_write($target_type, $target_id)
    {
        $target_id = (int) $target_id;
        if ($target_id < 1) {
            return null;
        }

        include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
        global $g5;
        $write_table = $g5['write_prefix'].eottae_plaza_board_table();

        if ($target_type === 'comment') {
            $row = sql_fetch("
                SELECT *
                FROM `{$write_table}`
                WHERE wr_id = '{$target_id}'
                  AND wr_is_comment = 1
                LIMIT 1
            ", false);
        } else {
            $row = sql_fetch("
                SELECT *
                FROM `{$write_table}`
                WHERE wr_id = '{$target_id}'
                  AND wr_is_comment = 0
                LIMIT 1
            ", false);
        }

        return is_array($row) && !empty($row['wr_id']) ? $row : null;
    }
}

if (!function_exists('eottae_plaza_has_reported_target')) {
    function eottae_plaza_has_reported_target($reporter_mb_id, $target_type, $target_id)
    {
        eottae_plaza_reports_ensure_schema();
        $table = eottae_plaza_reports_table();
        $reporter_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $reporter_mb_id);
        $target_type = trim((string) $target_type);
        $target_id = (int) $target_id;
        if ($reporter_mb_id === '' || $target_id < 1) {
            return false;
        }

        $row = sql_fetch("
            SELECT report_id
            FROM `{$table}`
            WHERE reporter_mb_id = '".sql_escape_string($reporter_mb_id)."'
              AND target_type = '".sql_escape_string($target_type)."'
              AND target_id = '{$target_id}'
            LIMIT 1
        ", false);

        return !empty($row['report_id']);
    }
}

if (!function_exists('eottae_plaza_can_submit_report')) {
    function eottae_plaza_can_submit_report($target_type, $target_id, $reporter_mb_id, $is_super_admin = false)
    {
        if ($is_super_admin) {
            return array('ok' => false, 'message' => '관리자는 신고할 수 없습니다.');
        }

        $reporter_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $reporter_mb_id);
        if ($reporter_mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 신고할 수 있습니다.');
        }

        if (!in_array($target_type, array('post', 'comment'), true)) {
            return array('ok' => false, 'message' => '신고 대상이 올바르지 않습니다.');
        }

        $target_id = (int) $target_id;
        if ($target_id < 1) {
            return array('ok' => false, 'message' => '신고 대상 정보가 올바르지 않습니다.');
        }

        $target = eottae_plaza_get_report_target_write($target_type, $target_id);
        if (!$target) {
            return array('ok' => false, 'message' => '신고 대상을 찾을 수 없습니다.');
        }

        include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
        if (!eottae_plaza_is_target_visible($target, $target_type, false)) {
            return array('ok' => false, 'message' => '삭제된 글/댓글은 신고할 수 없습니다.');
        }

        if ($reporter_mb_id === ($target['mb_id'] ?? '')) {
            return array('ok' => false, 'message' => '본인 글/댓글은 신고할 수 없습니다.');
        }

        if (eottae_plaza_has_reported_target($reporter_mb_id, $target_type, $target_id)) {
            return array('ok' => false, 'message' => '이미 신고한 대상입니다.');
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_plaza_submit_report')) {
    function eottae_plaza_submit_report($target_type, $target_id, $reporter_mb_id, $reason, $memo = '')
    {
        $target_type = trim((string) $target_type);
        $target_id = (int) $target_id;
        $reasons = eottae_plaza_report_reasons();
        if (!isset($reasons[$reason])) {
            return array('ok' => false, 'message' => '신고 사유를 선택해 주세요.');
        }

        $memo = function_exists('eottae_talkroom_clean_text')
            ? eottae_talkroom_clean_text($memo, 500)
            : cut_str(strip_tags((string) $memo), 500, '');
        if ($reason === 'etc' && $memo === '') {
            return array('ok' => false, 'message' => '기타 사유를 입력해 주세요.');
        }

        $check = eottae_plaza_can_submit_report($target_type, $target_id, $reporter_mb_id, false);
        if (empty($check['ok'])) {
            return $check;
        }

        eottae_plaza_reports_ensure_schema();
        $table = eottae_plaza_reports_table();
        $reporter_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $reporter_mb_id);
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');

        $ok = (bool) sql_query("
            INSERT INTO `{$table}` SET
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

        return array(
            'ok'        => true,
            'message'   => '신고가 접수되었습니다.',
            'report_id' => (int) sql_insert_id(),
        );
    }
}

if (!function_exists('eottae_plaza_report_target_preview')) {
    function eottae_plaza_report_target_preview($target_type, $target_id)
    {
        $target = eottae_plaza_get_report_target_write($target_type, $target_id);
        if (!$target) {
            return array(
                'subject' => '삭제됨',
                'preview' => '',
                'author'  => '',
                'href'    => '',
            );
        }

        $text = $target_type === 'comment'
            ? strip_tags((string) ($target['wr_content'] ?? ''))
            : get_text($target['wr_subject'] ?? '');
        if ($text === '' && !empty($target['wr_content'])) {
            $text = strip_tags((string) $target['wr_content']);
        }

        $preview = function_exists('cut_str') ? cut_str($text, 80, '…') : mb_substr($text, 0, 80, 'UTF-8');
        $wr_id = $target_type === 'comment' ? (int) ($target['wr_parent'] ?? 0) : (int) ($target['wr_id'] ?? 0);
        $href = $wr_id > 0 ? get_pretty_url(eottae_plaza_board_table(), $wr_id) : '';

        return array(
            'subject' => get_text($target['wr_subject'] ?? $preview),
            'preview' => get_text($preview),
            'author'  => get_text($target['wr_name'] ?? ''),
            'href'    => $href,
        );
    }
}

if (!function_exists('eottae_plaza_admin_token')) {
    function eottae_plaza_admin_token($regenerate = false)
    {
        $token = get_session('eottae_plaza_admin_token');
        if ($regenerate || $token === '') {
            $token = bin2hex(random_bytes(16));
            set_session('eottae_plaza_admin_token', $token);
        }

        return (string) $token;
    }
}

if (!function_exists('eottae_plaza_verify_admin_token')) {
    function eottae_plaza_verify_admin_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_plaza_admin_token');

        return $token !== '' && $session_token !== '' && hash_equals((string) $session_token, $token);
    }
}

if (!function_exists('eottae_plaza_admin_list_reports')) {
    function eottae_plaza_admin_list_reports($status = 'pending', $limit = 200)
    {
        eottae_plaza_reports_ensure_schema();
        $table = eottae_plaza_reports_table();
        $limit = max(1, min(500, (int) $limit));
        $reasons = eottae_plaza_report_reasons();
        $member_table = G5_TABLE_PREFIX.'member';

        $where = '1=1';
        if ($status !== 'all') {
            $where = "r.status = '".sql_escape_string(trim((string) $status))."'";
        }

        $result = sql_query("
            SELECT r.*, m.mb_nick AS reporter_nick, h.mb_nick AS handler_nick
            FROM `{$table}` r
            LEFT JOIN `{$member_table}` m ON m.mb_id = r.reporter_mb_id
            LEFT JOIN `{$member_table}` h ON h.mb_id = r.handled_by
            WHERE {$where}
            ORDER BY r.created_at DESC, r.report_id DESC
            LIMIT {$limit}
        ", false);

        $items = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $target_type = trim((string) ($row['target_type'] ?? ''));
                $target_id = (int) ($row['target_id'] ?? 0);
                $preview = eottae_plaza_report_target_preview($target_type, $target_id);
                $reason = trim((string) ($row['reason'] ?? ''));
                $status_val = trim((string) ($row['status'] ?? ''));

                $status_labels = array(
                    'pending'  => '접수',
                    'reviewed' => '확인',
                    'deleted'  => '삭제처리',
                    'rejected' => '기각',
                );

                $items[] = array(
                    'report_id'         => (int) ($row['report_id'] ?? 0),
                    'target_type'       => $target_type,
                    'target_type_label' => $target_type === 'comment' ? '댓글' : '글',
                    'target_id'         => $target_id,
                    'target_preview'    => $preview['preview'],
                    'target_subject'    => $preview['subject'],
                    'target_author'     => $preview['author'],
                    'target_href'       => $preview['href'],
                    'reason'            => $reason,
                    'reason_label'      => isset($reasons[$reason]) ? $reasons[$reason] : $reason,
                    'memo'              => get_text($row['memo'] ?? ''),
                    'status'            => $status_val,
                    'status_label'      => isset($status_labels[$status_val]) ? $status_labels[$status_val] : $status_val,
                    'reporter_mb_id'    => get_text($row['reporter_mb_id'] ?? ''),
                    'reporter_nick'     => get_text($row['reporter_nick'] ?? $row['reporter_mb_id'] ?? ''),
                    'handled_by_nick'   => get_text($row['handler_nick'] ?? ''),
                    'created_at'        => trim((string) ($row['created_at'] ?? '')),
                    'handled_at'        => trim((string) ($row['handled_at'] ?? '')),
                );
            }
        }

        return $items;
    }
}

if (!function_exists('eottae_plaza_admin_pending_report_count')) {
    function eottae_plaza_admin_pending_report_count()
    {
        eottae_plaza_reports_ensure_schema();
        $table = eottae_plaza_reports_table();
        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$table}`
            WHERE status = 'pending'
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_plaza_get_report')) {
    function eottae_plaza_get_report($report_id)
    {
        $report_id = (int) $report_id;
        if ($report_id < 1) {
            return null;
        }

        eottae_plaza_reports_ensure_schema();
        $table = eottae_plaza_reports_table();
        $row = sql_fetch("
            SELECT *
            FROM `{$table}`
            WHERE report_id = '{$report_id}'
            LIMIT 1
        ", false);

        return is_array($row) && !empty($row['report_id']) ? $row : null;
    }
}

if (!function_exists('eottae_plaza_admin_handle_report')) {
    function eottae_plaza_admin_handle_report($report_id, $action, $admin_mb_id)
    {
        global $is_admin;

        if ($is_admin !== 'super') {
            return array('ok' => false, 'message' => '권한이 없습니다.');
        }

        $report = eottae_plaza_get_report($report_id);
        if (!$report) {
            return array('ok' => false, 'message' => '신고를 찾을 수 없습니다.');
        }

        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        $table = eottae_plaza_reports_table();
        $report_id = (int) $report_id;

        if ($action === 'review') {
            sql_query("
                UPDATE `{$table}` SET
                    status = 'reviewed',
                    handled_by = '".sql_escape_string($admin_mb_id)."',
                    handled_at = '{$now}'
                WHERE report_id = '{$report_id}'
            ", false);

            return array('ok' => true, 'message' => '신고를 확인했습니다.');
        }

        if ($action === 'reject') {
            sql_query("
                UPDATE `{$table}` SET
                    status = 'rejected',
                    handled_by = '".sql_escape_string($admin_mb_id)."',
                    handled_at = '{$now}'
                WHERE report_id = '{$report_id}'
            ", false);

            return array('ok' => true, 'message' => '신고를 기각했습니다.');
        }

        if ($action === 'delete') {
            include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
            $target_type = trim((string) ($report['target_type'] ?? ''));
            $target_id = (int) ($report['target_id'] ?? 0);
            $hide = eottae_plaza_hide_target($target_type, $target_id, $admin_mb_id);
            if (empty($hide['ok'])) {
                return $hide;
            }

            sql_query("
                UPDATE `{$table}` SET
                    status = 'deleted',
                    handled_by = '".sql_escape_string($admin_mb_id)."',
                    handled_at = '{$now}'
                WHERE report_id = '{$report_id}'
            ", false);

            if (function_exists('eottae_member_growth_on_report_confirmed') && !empty($report['reporter_mb_id'])) {
                eottae_member_growth_on_report_confirmed($report['reporter_mb_id'], $report_id, 'report_plaza', '광장 신고 처리');
            }

            return array('ok' => true, 'message' => '신고 대상을 삭제 처리했습니다.');
        }

        return array('ok' => false, 'message' => '지원하지 않는 처리입니다.');
    }
}

if (!function_exists('eottae_plaza_report_counts_batch')) {
    /**
     * @param int[] $wr_ids post wr_ids
     * @return array<int, int>
     */
    function eottae_plaza_report_counts_batch(array $wr_ids)
    {
        $wr_ids = array_values(array_unique(array_filter(array_map('intval', $wr_ids))));
        if (empty($wr_ids)) {
            return array();
        }

        eottae_plaza_reports_ensure_schema();
        $table = eottae_plaza_reports_table();
        $in = implode(',', $wr_ids);
        $counts = array();
        $result = sql_query("
            SELECT target_id, COUNT(*) AS cnt
            FROM `{$table}`
            WHERE target_type = 'post'
              AND target_id IN ({$in})
              AND status IN ('pending', 'reviewed')
            GROUP BY target_id
        ", false);
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $counts[(int) ($row['target_id'] ?? 0)] = (int) ($row['cnt'] ?? 0);
            }
        }

        return $counts;
    }
}
