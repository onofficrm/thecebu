<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_logs_url')) {
    function eottae_talkroom_ai_logs_url(array $params = array())
    {
        $url = G5_URL.'/page/eottae-admin-talk-ai-logs.php';
        if (empty($params)) {
            return $url;
        }

        return $url.'?'.http_build_query($params);
    }
}

if (!function_exists('eottae_talkroom_ai_admin_list_logs')) {
    /**
     * @return array{rows:array, total:int, page:int, per_page:int}
     */
    function eottae_talkroom_ai_admin_list_logs(array $filters = array(), $page = 1, $per_page = 30)
    {
        if (!function_exists('eottae_talkroom_table_names')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $tables = eottae_talkroom_ai_table_names();
        $talk_tables = eottae_talkroom_table_names();
        if (!eottae_talkroom_ai_table_exists($tables['logs'])) {
            return array('rows' => array(), 'total' => 0, 'page' => 1, 'per_page' => $per_page);
        }

        $page = max(1, (int) $page);
        $per_page = max(10, min(100, (int) $per_page));
        $offset = ($page - 1) * $per_page;

        $where = array('1=1');
        if (!empty($filters['room_id'])) {
            $where[] = "l.room_id = '".(int) $filters['room_id']."'";
        }
        if (!empty($filters['trigger_type'])) {
            $where[] = "l.trigger_type = '".sql_escape_string((string) $filters['trigger_type'])."'";
        }
        if (!empty($filters['status'])) {
            $where[] = "l.status = '".sql_escape_string((string) $filters['status'])."'";
        }
        if (!empty($filters['date_from'])) {
            $where[] = "l.created_at >= '".sql_escape_string((string) $filters['date_from'])." 00:00:00'";
        }
        if (!empty($filters['date_to'])) {
            $where[] = "l.created_at <= '".sql_escape_string((string) $filters['date_to'])." 23:59:59'";
        }

        $where_sql = implode(' AND ', $where);
        $join = '';
        $room_name_select = "'' AS room_name, '' AS room_emoji";
        if (eottae_talkroom_table_exists($talk_tables['rooms'])) {
            $join = " LEFT JOIN `{$talk_tables['rooms']}` r ON r.room_id = l.room_id ";
            $room_name_select = "IFNULL(r.room_name, '') AS room_name, IFNULL(r.emoji, '') AS room_emoji";
        }

        $count_row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$tables['logs']}` l
            WHERE {$where_sql}
        ", false);
        $total = (int) ($count_row['cnt'] ?? 0);

        $result = sql_query("
            SELECT l.*, {$room_name_select}
            FROM `{$tables['logs']}` l
            {$join}
            WHERE {$where_sql}
            ORDER BY l.log_id DESC
            LIMIT {$offset}, {$per_page}
        ", false);

        $trigger_labels = eottae_talkroom_ai_trigger_types();
        $status_labels = eottae_talkroom_ai_log_statuses();
        $rows = array();

        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $trigger = (string) ($row['trigger_type'] ?? '');
                $status = (string) ($row['status'] ?? '');
                $post_id = (int) ($row['post_id'] ?? 0);
                $comment_id = (int) ($row['comment_id'] ?? 0);
                $room_id = (int) ($row['room_id'] ?? 0);
                $content_link = '';

                if ($post_id > 0 && function_exists('eottae_talkroom_post_view_url')) {
                    $content_link = eottae_talkroom_post_view_url($post_id, $room_id);
                }

                $rows[] = array(
                    'log_id'         => (int) ($row['log_id'] ?? 0),
                    'room_id'        => $room_id,
                    'room_name'      => get_text($row['room_name'] ?? ''),
                    'room_emoji'     => get_text($row['room_emoji'] ?? ''),
                    'trigger_type'   => $trigger,
                    'trigger_label'  => isset($trigger_labels[$trigger]) ? $trigger_labels[$trigger] : $trigger,
                    'response_text'  => cut_str(strip_tags((string) ($row['response_text'] ?? '')), 180, '…'),
                    'response_full'  => (string) ($row['response_text'] ?? ''),
                    'post_id'        => $post_id,
                    'comment_id'     => $comment_id,
                    'status'         => $status,
                    'status_label'   => isset($status_labels[$status]) ? $status_labels[$status] : $status,
                    'error_message'  => get_text($row['error_message'] ?? ''),
                    'created_at'     => (string) ($row['created_at'] ?? ''),
                    'content_link'   => $content_link,
                );
            }
        }

        return array(
            'rows'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $per_page,
        );
    }
}

if (!function_exists('eottae_talkroom_ai_get_write_row_by_id')) {
    function eottae_talkroom_ai_get_write_row_by_id($wr_id)
    {
        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $wr_id = (int) $wr_id;
        $write_table = eottae_talkroom_write_table();
        if ($wr_id < 1 || $write_table === '') {
            return null;
        }

        return sql_fetch(" SELECT * FROM `{$write_table}` WHERE wr_id = '{$wr_id}' LIMIT 1 ", false);
    }
}

if (!function_exists('eottae_talkroom_ai_delete_content')) {
    /**
     * @return array{ok:bool, message:string}
     */
    function eottae_talkroom_ai_delete_content($wr_id, $mb_id, $is_super_admin = false)
    {
        if (!$is_super_admin) {
            return array('ok' => false, 'message' => '최고관리자만 AI 콘텐츠를 삭제할 수 있습니다.');
        }

        if (!function_exists('eottae_talkroom_soft_delete_write')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $wr_id = (int) $wr_id;
        $write = eottae_talkroom_ai_get_write_row_by_id($wr_id);
        if (!$write || empty($write['wr_id'])) {
            return array('ok' => false, 'message' => '글/댓글을 찾을 수 없습니다.');
        }

        if (!eottae_talkroom_ai_is_ai_write_row($write)) {
            return array('ok' => false, 'message' => 'AI가 작성한 글/댓글만 삭제할 수 있습니다.');
        }

        $board = array('bo_table' => eottae_talkroom_board_table());
        $result = eottae_talkroom_soft_delete_write($write, $board, $mb_id, 'AI 관리자 삭제');

        return array(
            'ok'      => !empty($result['ok']),
            'message' => (string) ($result['message'] ?? '삭제 처리에 실패했습니다.'),
        );
    }
}

if (!function_exists('eottae_talkroom_ai_set_room_force_disabled')) {
    function eottae_talkroom_ai_set_room_force_disabled($room_id, $disabled, $mb_id, $is_super_admin = false)
    {
        if (!$is_super_admin) {
            return array('ok' => false, 'message' => '최고관리자만 방 AI 강제 OFF를 설정할 수 있습니다.');
        }

        $room_id = (int) $room_id;
        if ($room_id < 1) {
            return array('ok' => false, 'message' => '톡방 정보가 올바르지 않습니다.');
        }

        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }
        if (!eottae_talkroom_get_operating_room($room_id)) {
            return array('ok' => false, 'message' => '운영 중인 톡방을 찾을 수 없습니다.');
        }

        eottae_talkroom_ai_ensure_schema();

        $disabled = !empty($disabled) ? 1 : 0;
        $existing = eottae_talkroom_ai_get_settings_row($room_id);
        $now = G5_TIME_YMDHIS;
        $tables = eottae_talkroom_ai_table_names();

        if ($existing) {
            $ok = (bool) sql_query("
                UPDATE `{$tables['settings']}` SET
                    admin_force_disabled = '{$disabled}',
                    updated_at = '{$now}'
                WHERE room_id = '{$room_id}'
            ", false);
        } else {
            $defaults = eottae_talkroom_ai_default_settings();
            $ok = (bool) sql_query("
                INSERT INTO `{$tables['settings']}` SET
                    room_id = '{$room_id}',
                    ai_enabled = '0',
                    admin_force_disabled = '{$disabled}',
                    ai_name = '".sql_escape_string($defaults['ai_name'])."',
                    ai_persona = '".sql_escape_string($defaults['ai_persona'])."',
                    ai_tone = '".sql_escape_string($defaults['ai_tone'])."',
                    quiet_trigger_enabled = '".(int) $defaults['quiet_trigger_enabled']."',
                    daily_question_enabled = '".(int) $defaults['daily_question_enabled']."',
                    welcome_enabled = '".(int) $defaults['welcome_enabled']."',
                    meetup_suggest_enabled = '".(int) $defaults['meetup_suggest_enabled']."',
                    summary_enabled = '".(int) $defaults['summary_enabled']."',
                    reaction_enabled = '".(int) $defaults['reaction_enabled']."',
                    max_messages_per_day = '".(int) $defaults['max_messages_per_day']."',
                    min_silence_minutes = '".(int) $defaults['min_silence_minutes']."',
                    active_start_time = '".sql_escape_string($defaults['active_start_time'])."',
                    active_end_time = '".sql_escape_string($defaults['active_end_time'])."',
                    created_at = '{$now}',
                    updated_at = '{$now}'
            ", false);
        }

        eottae_talkroom_ai_write_log($room_id, 'admin_test', array(
            'status'        => $ok ? 'success' : 'failed',
            'prompt_text'   => 'admin_force_disabled:'.($disabled ? '1' : '0'),
            'response_text' => '',
            'error_message' => $ok ? '' : 'force_off_save_failed',
        ));

        return array(
            'ok'      => $ok,
            'message' => $ok
                ? ($disabled ? '해당 톡방 AI를 강제 OFF 했습니다.' : '해당 톡방 AI 강제 OFF를 해제했습니다.')
                : '저장에 실패했습니다.',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_save_global_policy')) {
    function eottae_talkroom_ai_save_global_policy(array $data, $mb_id, $is_super_admin = false)
    {
        if (!$is_super_admin) {
            return array('ok' => false, 'message' => '최고관리자만 전역 AI 정책을 변경할 수 있습니다.');
        }

        eottae_talkroom_ai_ensure_schema();

        $site_ai_enabled = !empty($data['site_ai_enabled']) ? 1 : 0;
        $owner_config_allowed = !empty($data['owner_config_allowed']) ? 1 : 0;
        $site_daily_limit = max(1, min(10000, (int) ($data['site_daily_limit'] ?? eottae_talkroom_ai_default_site_daily_limit())));

        $tables = eottae_talkroom_ai_table_names();
        $now = G5_TIME_YMDHIS;
        $existing = eottae_talkroom_ai_get_settings_row(0);
        $defaults = eottae_talkroom_ai_default_settings();

        if ($existing) {
            $set_site = eottae_talkroom_ai_column_exists($tables['settings'], 'site_ai_enabled')
                ? "site_ai_enabled = '{$site_ai_enabled}',"
                : '';
            $ok = (bool) sql_query("
                UPDATE `{$tables['settings']}` SET
                    {$set_site}
                    ai_enabled = '{$owner_config_allowed}',
                    max_messages_per_day = '{$site_daily_limit}',
                    updated_at = '{$now}'
                WHERE room_id = '0'
            ", false);
        } else {
            $site_col = eottae_talkroom_ai_column_exists($tables['settings'], 'site_ai_enabled')
                ? "site_ai_enabled = '{$site_ai_enabled}',"
                : '';
            $ok = (bool) sql_query("
                INSERT INTO `{$tables['settings']}` SET
                    room_id = '0',
                    {$site_col}
                    ai_enabled = '{$owner_config_allowed}',
                    max_messages_per_day = '{$site_daily_limit}',
                    ai_name = '".sql_escape_string($defaults['ai_name'])."',
                    ai_persona = '".sql_escape_string($defaults['ai_persona'])."',
                    ai_tone = '".sql_escape_string($defaults['ai_tone'])."',
                    quiet_trigger_enabled = '1',
                    daily_question_enabled = '1',
                    welcome_enabled = '1',
                    meetup_suggest_enabled = '0',
                    summary_enabled = '1',
                    reaction_enabled = '0',
                    min_silence_minutes = '360',
                    active_start_time = '09:00:00',
                    active_end_time = '22:00:00',
                    created_at = '{$now}',
                    updated_at = '{$now}'
            ", false);
        }

        return array(
            'ok'      => $ok,
            'message' => $ok ? '전역 AI 정책이 저장되었습니다.' : '저장에 실패했습니다.',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_max_test_writes_per_day')) {
    function eottae_talkroom_ai_max_test_writes_per_day()
    {
        return 5;
    }
}

if (!function_exists('eottae_talkroom_ai_get_today_admin_test_count')) {
    function eottae_talkroom_ai_get_today_admin_test_count($room_id, $target_date = null)
    {
        $room_id = (int) $room_id;
        $target_date = $target_date ?: G5_TIME_YMD;
        $tables = eottae_talkroom_ai_table_names();

        if (!eottae_talkroom_ai_table_exists($tables['logs'])) {
            return 0;
        }

        $row = sql_fetch("
            SELECT COUNT(*) AS total_count
            FROM `{$tables['logs']}`
            WHERE room_id = '{$room_id}'
              AND trigger_type = 'admin_test'
              AND status = 'success'
              AND post_id > 0
              AND DATE(created_at) = '".sql_escape_string($target_date)."'
        ", false);

        return (int) ($row['total_count'] ?? 0);
    }
}

if (!function_exists('eottae_talkroom_ai_assert_admin_test_write_allowed')) {
    /**
     * @return array{ok:bool, message:string}
     */
    function eottae_talkroom_ai_assert_admin_test_write_allowed($room_id)
    {
        $room_id = (int) $room_id;
        $limit = eottae_talkroom_ai_max_test_writes_per_day();
        $count = eottae_talkroom_ai_get_today_admin_test_count($room_id);

        if ($count >= $limit) {
            return array(
                'ok'      => false,
                'message' => '하루 테스트 작성 한도('.$limit.'회)를 초과했습니다. 내일 다시 시도하거나 최고관리자에게 문의해 주세요.',
            );
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_talkroom_ai_run_admin_trigger_test')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_run_admin_trigger_test($room_id, $trigger, array $options = array())
    {
        $room_id = (int) $room_id;
        $trigger = trim((string) $trigger);
        $dry_run = !empty($options['dry_run']);
        $force = !empty($options['force']);
        $is_test = true;

        if (!$dry_run) {
            $test_limit = eottae_talkroom_ai_assert_admin_test_write_allowed($room_id);
            if (empty($test_limit['ok'])) {
                return array(
                    'room_id' => $room_id,
                    'status'  => 'skipped',
                    'reason'  => 'test_limit_reached',
                    'message' => $test_limit['message'],
                );
            }
        }

        switch ($trigger) {
            case 'quiet_room':
                if (!function_exists('eottae_talkroom_ai_run_quiet_trigger')) {
                    include_once G5_LIB_PATH.'/eottae-talkroom-ai-quiet.lib.php';
                }

                return eottae_talkroom_ai_run_quiet_trigger($room_id, $dry_run, $options['now'] ?? null, array(
                    'force'   => $force ?: true,
                    'is_test' => $is_test,
                ));

            case 'daily_question':
                if (!function_exists('eottae_talkroom_ai_run_daily_question_trigger')) {
                    include_once G5_LIB_PATH.'/eottae-talkroom-ai-daily-question.lib.php';
                }

                return eottae_talkroom_ai_run_daily_question_trigger($room_id, array(
                    'dry_run' => $dry_run,
                    'force'   => $force ?: true,
                    'is_test' => $is_test,
                    'now'     => $options['now'] ?? null,
                ));

            case 'meetup_suggest':
                if (!function_exists('eottae_talkroom_ai_run_meetup_suggest_trigger')) {
                    include_once G5_LIB_PATH.'/eottae-talkroom-ai-meetup.lib.php';
                }

                return eottae_talkroom_ai_run_meetup_suggest_trigger($room_id, $dry_run, $options['now'] ?? null, array(
                    'force'   => $force ?: true,
                    'is_test' => $is_test,
                ));

            case 'summary':
                if (!function_exists('eottae_talkroom_ai_run_summary_trigger')) {
                    include_once G5_LIB_PATH.'/eottae-talkroom-ai-summary.lib.php';
                }

                return eottae_talkroom_ai_run_summary_trigger($room_id, $dry_run, $options['now'] ?? null, array(
                    'force'   => $force ?: true,
                    'is_test' => $is_test,
                ));

            default:
                return array(
                    'room_id' => $room_id,
                    'status'  => 'failed',
                    'reason'  => 'unknown_trigger',
                    'message' => '지원하지 않는 테스트 트리거입니다.',
                );
        }
    }
}
