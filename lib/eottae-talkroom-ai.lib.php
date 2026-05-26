<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_bootstrap_tables')) {
    function eottae_talkroom_ai_bootstrap_tables()
    {
        global $g5;

        if (!isset($g5['sebu_talk_ai_settings_table'])) {
            $g5['sebu_talk_ai_settings_table'] = G5_TABLE_PREFIX.'sebu_talk_ai_settings';
        }
        if (!isset($g5['sebu_talk_ai_logs_table'])) {
            $g5['sebu_talk_ai_logs_table'] = G5_TABLE_PREFIX.'sebu_talk_ai_logs';
        }
        if (!isset($g5['sebu_talk_ai_daily_limits_table'])) {
            $g5['sebu_talk_ai_daily_limits_table'] = G5_TABLE_PREFIX.'sebu_talk_ai_daily_limits';
        }
    }
}

if (!function_exists('eottae_talkroom_ai_table_names')) {
    function eottae_talkroom_ai_table_names()
    {
        eottae_talkroom_ai_bootstrap_tables();
        global $g5;

        return array(
            'settings'     => $g5['sebu_talk_ai_settings_table'],
            'logs'         => $g5['sebu_talk_ai_logs_table'],
            'daily_limits' => $g5['sebu_talk_ai_daily_limits_table'],
        );
    }
}

if (!function_exists('eottae_talkroom_ai_table_exists')) {
    function eottae_talkroom_ai_table_exists($table)
    {
        if (function_exists('eottae_talkroom_table_exists')) {
            return eottae_talkroom_table_exists($table);
        }

        $table = preg_replace('/[^a-z0-9_]/', '', (string) $table);
        if ($table === '') {
            return false;
        }

        $row = sql_fetch(" SHOW TABLES LIKE '".sql_escape_string($table)."' ", false);

        return !empty($row);
    }
}

include_once G5_LIB_PATH.'/eottae-talkroom-ai-guard.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-safety.lib.php';

if (!function_exists('eottae_talkroom_ai_trigger_types')) {
    /**
     * @return array<string, string>
     */
    function eottae_talkroom_ai_trigger_types()
    {
        return array(
            'quiet_room'      => '조용한 방 화제',
            'daily_question'  => '오늘의 질문',
            'welcome'         => '신규 회원 환영',
            'meetup_suggest'  => '모임 제안',
            'summary'         => '방 요약',
            'reaction'        => '반응/리액션',
            'admin_test'      => '관리자 테스트',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_log_statuses')) {
    /**
     * @return array<string, string>
     */
    function eottae_talkroom_ai_log_statuses()
    {
        return array(
            'pending' => '대기',
            'success' => '성공',
            'failed'  => '실패',
            'skipped' => '건너뜀',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_default_settings')) {
    /**
     * 방별 AI 설정 기본값 (DB 미등록 시 코드 fallback용)
     *
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_default_settings()
    {
        return array(
            'room_id'                  => 0,
            'ai_enabled'               => 0,
            'ai_name'                  => '어때봇',
            'ai_persona'               => 'community_manager',
            'ai_tone'                  => 'friendly',
            'quiet_trigger_enabled'    => 1,
            'daily_question_enabled'   => 1,
            'welcome_enabled'          => 1,
            'meetup_suggest_enabled'   => 0,
            'summary_enabled'          => 1,
            'reaction_enabled'         => 0,
            'admin_force_disabled'     => 0,
            'max_messages_per_day'     => 2,
            'min_silence_minutes'      => 360,
            'active_start_time'        => '09:00:00',
            'active_end_time'          => '22:00:00',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_name_options')) {
    /**
     * @return array<string, string>
     */
    function eottae_talkroom_ai_name_options()
    {
        return array(
            '어때봇'       => '어때봇',
            '세부톡 도우미' => '세부톡 도우미',
            '세부친구'     => '세부친구',
            '세부생활봇'   => '세부생활봇',
            '세부모임봇'   => '세부모임봇',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_persona_options')) {
    /**
     * @return array<string, string>
     */
    function eottae_talkroom_ai_persona_options()
    {
        return array(
            'community_manager' => '친근한 커뮤니티 매니저',
            'meetup_helper'     => '활발한 모임 도우미',
            'life_info'         => '세부 생활정보 도우미',
            'mom_talk'          => '맘수다방 도우미',
            'business_partner'  => '사업자방 파트너',
            'travel_guide'      => '여행자 가이드',
            'used_goods'        => '중고거래 도우미',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_tone_options')) {
    /**
     * @return array<string, string>
     */
    function eottae_talkroom_ai_tone_options()
    {
        return array(
            'friendly'    => '친근하게',
            'calm'        => '차분하게',
            'lively'      => '활발하게',
            'informative' => '정보 중심으로',
            'brief'       => '짧고 간단하게',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_feature_fields')) {
    /**
     * @return array<string, string>
     */
    function eottae_talkroom_ai_feature_fields()
    {
        return array(
            'quiet_trigger_enabled'  => '조용한 방 화제 던지기',
            'daily_question_enabled' => '오늘의 질문',
            'welcome_enabled'        => '신규회원 환영',
            'meetup_suggest_enabled' => '모임 제안',
            'summary_enabled'        => '방 요약',
            'reaction_enabled'       => 'AI 리액션',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_settings_url')) {
    function eottae_talkroom_ai_settings_url($room_id)
    {
        return G5_URL.'/page/eottae-talk-ai-settings.php?room_id='.(int) $room_id;
    }
}

if (!function_exists('eottae_talkroom_ai_admin_url')) {
    function eottae_talkroom_ai_admin_url()
    {
        return G5_URL.'/page/eottae-admin-talk-ai.php';
    }
}

if (!function_exists('eottae_talkroom_ai_global_room_id')) {
    function eottae_talkroom_ai_global_room_id()
    {
        return 0;
    }
}

if (!function_exists('eottae_talkroom_ai_get_settings_row')) {
    function eottae_talkroom_ai_get_settings_row($room_id)
    {
        $room_id = (int) $room_id;
        if ($room_id < 0) {
            return null;
        }

        $tables = eottae_talkroom_ai_table_names();
        if (!eottae_talkroom_ai_table_exists($tables['settings'])) {
            return null;
        }

        return sql_fetch(" SELECT * FROM `{$tables['settings']}` WHERE room_id = '{$room_id}' LIMIT 1 ");
    }
}

if (!function_exists('eottae_talkroom_ai_get_settings')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_get_settings($room_id)
    {
        $room_id = (int) $room_id;
        $defaults = eottae_talkroom_ai_default_settings();
        $defaults['room_id'] = $room_id;

        $row = eottae_talkroom_ai_get_settings_row($room_id);
        if (!$row) {
            return $defaults;
        }

        $merged = array_merge($defaults, $row);
        $merged['room_id'] = $room_id;
        $merged['admin_force_disabled'] = (int) !empty($row['admin_force_disabled']);
        if (function_exists('eottae_talkroom_ai_normalize_max_messages_per_day')) {
            $merged['max_messages_per_day'] = eottae_talkroom_ai_normalize_max_messages_per_day($merged['max_messages_per_day'] ?? 2);
        }

        return $merged;
    }
}

if (!function_exists('eottae_talkroom_ai_column_exists')) {
    function eottae_talkroom_ai_column_exists($table, $column)
    {
        $table = preg_replace('/[^a-z0-9_]/', '', (string) $table);
        $column = preg_replace('/[^a-z0-9_]/', '', (string) $column);
        if ($table === '' || $column === '') {
            return false;
        }

        $row = sql_fetch(" SHOW COLUMNS FROM `{$table}` LIKE '".sql_escape_string($column)."' ", false);

        return !empty($row['Field']);
    }
}

if (!function_exists('eottae_talkroom_ai_can_edit_settings')) {
    function eottae_talkroom_ai_can_edit_settings($room_id, $mb_id, $is_super_admin = false)
    {
        $room_id = (int) $room_id;

        if ($room_id === eottae_talkroom_ai_global_room_id()) {
            return $is_super_admin;
        }

        if (!function_exists('eottae_talkroom_can_manage_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        if (!eottae_talkroom_can_manage_room($room_id, $mb_id, $is_super_admin)) {
            return false;
        }

        if ($is_super_admin) {
            return true;
        }

        if (!eottae_talkroom_ai_is_site_ai_enabled()) {
            return false;
        }

        return eottae_talkroom_ai_is_owner_config_allowed();
    }
}

if (!function_exists('eottae_talkroom_ai_assert_edit_access')) {
    function eottae_talkroom_ai_assert_edit_access($room_id, $mb_id, $is_super_admin = false)
    {
        $room_id = (int) $room_id;

        if ($room_id === eottae_talkroom_ai_global_room_id()) {
            if (!$is_super_admin) {
                return array('ok' => false, 'message' => '전역 AI 정책은 최고관리자만 변경할 수 있습니다.');
            }

            return array('ok' => true, 'message' => '');
        }

        if (!function_exists('eottae_talkroom_assert_manage_access')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $access = eottae_talkroom_assert_manage_access($room_id, $mb_id, $is_super_admin);
        if (empty($access['ok'])) {
            return $access;
        }

        if (!$is_super_admin && !eottae_talkroom_ai_is_owner_config_allowed()) {
            return array('ok' => false, 'message' => '최고관리자가 톡방 AI 설정을 일시 중지했습니다. 설정을 변경할 수 없습니다.');
        }

        if (!$is_super_admin && !eottae_talkroom_ai_is_site_ai_enabled()) {
            return array('ok' => false, 'message' => '사이트 전체 AI 사용이 중지되어 설정을 변경할 수 없습니다.');
        }

        if (!$is_super_admin && eottae_talkroom_ai_is_room_force_disabled($room_id)) {
            return array('ok' => false, 'message' => '최고관리자가 이 톡방 AI를 강제 OFF 했습니다. 설정을 변경할 수 없습니다.');
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_talkroom_ai_can_view_settings')) {
    function eottae_talkroom_ai_can_view_settings($room_id, $mb_id, $is_super_admin = false)
    {
        $room_id = (int) $room_id;

        if ($room_id === eottae_talkroom_ai_global_room_id()) {
            return $is_super_admin;
        }

        if (!function_exists('eottae_talkroom_can_manage_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        return eottae_talkroom_can_manage_room($room_id, $mb_id, $is_super_admin);
    }
}

if (!function_exists('eottae_talkroom_ai_normalize_time')) {
    function eottae_talkroom_ai_normalize_time($value, $fallback = '09:00:00')
    {
        $value = trim((string) $value);
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $value, $m)) {
            $hour = max(0, min(23, (int) $m[1]));
            $min = max(0, min(59, (int) $m[2]));
            $sec = isset($m[3]) ? max(0, min(59, (int) $m[3])) : 0;

            return sprintf('%02d:%02d:%02d', $hour, $min, $sec);
        }

        return eottae_talkroom_ai_normalize_time($fallback, '09:00:00');
    }
}

if (!function_exists('eottae_talkroom_ai_time_for_input')) {
    function eottae_talkroom_ai_time_for_input($value)
    {
        $value = eottae_talkroom_ai_normalize_time($value);

        return substr($value, 0, 5);
    }
}

if (!function_exists('eottae_talkroom_ai_parse_settings_input')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_parse_settings_input(array $post)
    {
        $data = array(
            'ai_enabled'               => !empty($post['ai_enabled']) ? 1 : 0,
            'ai_name'                  => isset($post['ai_name']) ? trim((string) $post['ai_name']) : '',
            'ai_persona'               => isset($post['ai_persona']) ? trim((string) $post['ai_persona']) : '',
            'ai_tone'                  => isset($post['ai_tone']) ? trim((string) $post['ai_tone']) : '',
            'quiet_trigger_enabled'    => !empty($post['quiet_trigger_enabled']) ? 1 : 0,
            'daily_question_enabled'   => !empty($post['daily_question_enabled']) ? 1 : 0,
            'welcome_enabled'          => !empty($post['welcome_enabled']) ? 1 : 0,
            'meetup_suggest_enabled'   => !empty($post['meetup_suggest_enabled']) ? 1 : 0,
            'summary_enabled'          => !empty($post['summary_enabled']) ? 1 : 0,
            'reaction_enabled'         => !empty($post['reaction_enabled']) ? 1 : 0,
            'max_messages_per_day'     => eottae_talkroom_ai_normalize_max_messages_per_day(isset($post['max_messages_per_day']) ? (int) $post['max_messages_per_day'] : 2),
            'min_silence_minutes'      => isset($post['min_silence_minutes']) ? (int) $post['min_silence_minutes'] : 360,
            'active_start_time'        => isset($post['active_start_time']) ? trim((string) $post['active_start_time']) : '09:00',
            'active_end_time'          => isset($post['active_end_time']) ? trim((string) $post['active_end_time']) : '22:00',
        );

        $data['active_start_time'] = eottae_talkroom_ai_normalize_time($data['active_start_time'], '09:00:00');
        $data['active_end_time'] = eottae_talkroom_ai_normalize_time($data['active_end_time'], '22:00:00');

        return $data;
    }
}

if (!function_exists('eottae_talkroom_ai_validate_settings')) {
    /**
     * @return array<int, string>
     */
    function eottae_talkroom_ai_validate_settings(array $data, $room_id = 0)
    {
        $errors = array();
        $room_id = (int) $room_id;
        $names = eottae_talkroom_ai_name_options();
        $personas = eottae_talkroom_ai_persona_options();
        $tones = eottae_talkroom_ai_tone_options();

        if ($room_id !== eottae_talkroom_ai_global_room_id()) {
            if ($data['ai_name'] === '' || !isset($names[$data['ai_name']])) {
                $errors[] = 'AI 이름을 선택해 주세요.';
            }
            if ($data['ai_persona'] === '' || !isset($personas[$data['ai_persona']])) {
                $errors[] = 'AI 캐릭터/역할을 선택해 주세요.';
            }
            if ($data['ai_tone'] === '' || !isset($tones[$data['ai_tone']])) {
                $errors[] = 'AI 말투를 선택해 주세요.';
            }
            if ($data['max_messages_per_day'] < eottae_talkroom_ai_min_messages_per_day()
                || $data['max_messages_per_day'] > eottae_talkroom_ai_max_messages_per_day_cap()) {
                $errors[] = '하루 최대 AI 발언 수는 '.eottae_talkroom_ai_min_messages_per_day().'~'.eottae_talkroom_ai_max_messages_per_day_cap().' 사이로 설정해 주세요.';
            }
            if ($data['min_silence_minutes'] < 30 || $data['min_silence_minutes'] > 10080) {
                $errors[] = '조용한 방 판단 기준은 30분~7일(10080분) 사이로 설정해 주세요.';
            }
            if ($data['active_start_time'] === $data['active_end_time']) {
                $errors[] = 'AI 활동 시작·종료 시간이 같을 수 없습니다.';
            }
        }

        return $errors;
    }
}

if (!function_exists('eottae_talkroom_ai_save_settings')) {
    function eottae_talkroom_ai_save_settings($room_id, array $data, $mb_id, $is_super_admin = false)
    {
        $room_id = (int) $room_id;
        $access = eottae_talkroom_ai_assert_edit_access($room_id, $mb_id, $is_super_admin);
        if (empty($access['ok'])) {
            return array('ok' => false, 'message' => $access['message']);
        }

        $errors = eottae_talkroom_ai_validate_settings($data, $room_id);
        if (!empty($errors)) {
            return array('ok' => false, 'message' => $errors[0]);
        }

        if ($room_id > 0) {
            if (!function_exists('eottae_talkroom_get_operating_room')) {
                include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
            }
            if (!eottae_talkroom_get_operating_room($room_id)) {
                return array('ok' => false, 'message' => '운영 중인 톡방을 찾을 수 없습니다.');
            }
        }

        $tables = eottae_talkroom_ai_table_names();
        if (!eottae_talkroom_ai_table_exists($tables['settings'])) {
            eottae_talkroom_ai_ensure_schema();
        }

        $now = G5_TIME_YMDHIS;
        $existing = eottae_talkroom_ai_get_settings_row($room_id);

        if ($room_id === eottae_talkroom_ai_global_room_id()) {
            $ai_enabled = !empty($data['ai_enabled']) ? 1 : 0;
            $defaults = eottae_talkroom_ai_default_settings();

            if ($existing) {
                $sql = " UPDATE `{$tables['settings']}` SET
                    ai_enabled = '{$ai_enabled}',
                    updated_at = '{$now}'
                    WHERE room_id = '0' ";
            } else {
                $sql = " INSERT INTO `{$tables['settings']}` SET
                    room_id = '0',
                    ai_enabled = '{$ai_enabled}',
                    ai_name = '".sql_escape_string($defaults['ai_name'])."',
                    ai_persona = '".sql_escape_string($defaults['ai_persona'])."',
                    ai_tone = '".sql_escape_string($defaults['ai_tone'])."',
                    quiet_trigger_enabled = '1',
                    daily_question_enabled = '1',
                    welcome_enabled = '1',
                    meetup_suggest_enabled = '0',
                    summary_enabled = '1',
                    reaction_enabled = '0',
                    max_messages_per_day = '2',
                    min_silence_minutes = '360',
                    active_start_time = '09:00:00',
                    active_end_time = '22:00:00',
                    created_at = '{$now}',
                    updated_at = '{$now}' ";
            }

            $ok = (bool) sql_query($sql, false);

            return array(
                'ok'      => $ok,
                'message' => $ok ? '전역 AI 정책이 저장되었습니다.' : '저장에 실패했습니다.',
            );
        }

        $fields = array(
            'ai_enabled'               => (int) !empty($data['ai_enabled']),
            'ai_name'                  => sql_escape_string($data['ai_name']),
            'ai_persona'               => sql_escape_string($data['ai_persona']),
            'ai_tone'                  => sql_escape_string($data['ai_tone']),
            'quiet_trigger_enabled'    => (int) !empty($data['quiet_trigger_enabled']),
            'daily_question_enabled'   => (int) !empty($data['daily_question_enabled']),
            'welcome_enabled'          => (int) !empty($data['welcome_enabled']),
            'meetup_suggest_enabled'   => (int) !empty($data['meetup_suggest_enabled']),
            'summary_enabled'          => (int) !empty($data['summary_enabled']),
            'reaction_enabled'         => (int) !empty($data['reaction_enabled']),
            'max_messages_per_day'     => (int) $data['max_messages_per_day'],
            'min_silence_minutes'      => (int) $data['min_silence_minutes'],
            'active_start_time'        => sql_escape_string($data['active_start_time']),
            'active_end_time'          => sql_escape_string($data['active_end_time']),
        );

        if ($existing) {
            $sql = " UPDATE `{$tables['settings']}` SET
                ai_enabled = '{$fields['ai_enabled']}',
                ai_name = '{$fields['ai_name']}',
                ai_persona = '{$fields['ai_persona']}',
                ai_tone = '{$fields['ai_tone']}',
                quiet_trigger_enabled = '{$fields['quiet_trigger_enabled']}',
                daily_question_enabled = '{$fields['daily_question_enabled']}',
                welcome_enabled = '{$fields['welcome_enabled']}',
                meetup_suggest_enabled = '{$fields['meetup_suggest_enabled']}',
                summary_enabled = '{$fields['summary_enabled']}',
                reaction_enabled = '{$fields['reaction_enabled']}',
                max_messages_per_day = '{$fields['max_messages_per_day']}',
                min_silence_minutes = '{$fields['min_silence_minutes']}',
                active_start_time = '{$fields['active_start_time']}',
                active_end_time = '{$fields['active_end_time']}',
                updated_at = '{$now}'
                WHERE room_id = '{$room_id}' ";
        } else {
            $sql = " INSERT INTO `{$tables['settings']}` SET
                room_id = '{$room_id}',
                ai_enabled = '{$fields['ai_enabled']}',
                ai_name = '{$fields['ai_name']}',
                ai_persona = '{$fields['ai_persona']}',
                ai_tone = '{$fields['ai_tone']}',
                quiet_trigger_enabled = '{$fields['quiet_trigger_enabled']}',
                daily_question_enabled = '{$fields['daily_question_enabled']}',
                welcome_enabled = '{$fields['welcome_enabled']}',
                meetup_suggest_enabled = '{$fields['meetup_suggest_enabled']}',
                summary_enabled = '{$fields['summary_enabled']}',
                reaction_enabled = '{$fields['reaction_enabled']}',
                max_messages_per_day = '{$fields['max_messages_per_day']}',
                min_silence_minutes = '{$fields['min_silence_minutes']}',
                active_start_time = '{$fields['active_start_time']}',
                active_end_time = '{$fields['active_end_time']}',
                created_at = '{$now}',
                updated_at = '{$now}' ";
        }

        $ok = (bool) sql_query($sql, false);

        return array(
            'ok'      => $ok,
            'message' => $ok ? 'AI 도우미 설정이 저장되었습니다.' : '저장에 실패했습니다.',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_admin_list_rooms')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_ai_admin_list_rooms($limit = 200)
    {
        if (!function_exists('eottae_talkroom_table_names')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $tables = eottae_talkroom_table_names();
        $ai_tables = eottae_talkroom_ai_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms'])) {
            return array();
        }

        $limit = max(1, min(500, (int) $limit));
        $join = '';
        $select_ai = "0 AS ai_enabled, 0 AS admin_force_disabled, '' AS ai_name, '0000-00-00 00:00:00' AS ai_updated_at";
        if (eottae_talkroom_ai_table_exists($ai_tables['settings'])) {
            $join = " LEFT JOIN `{$ai_tables['settings']}` s ON s.room_id = r.room_id ";
            $force_col = eottae_talkroom_ai_column_exists($ai_tables['settings'], 'admin_force_disabled')
                ? 'IFNULL(s.admin_force_disabled, 0) AS admin_force_disabled'
                : '0 AS admin_force_disabled';
            $select_ai = 'IFNULL(s.ai_enabled, 0) AS ai_enabled, '.$force_col.', IFNULL(s.ai_name, \'\') AS ai_name, IFNULL(s.updated_at, \'0000-00-00 00:00:00\') AS ai_updated_at';
        }

        $sql = " SELECT r.room_id, r.room_name, r.emoji, r.owner_mb_id, r.status, r.approved_at,
                        {$select_ai}
                 FROM `{$tables['rooms']}` r
                 {$join}
                 WHERE r.status IN ('approved', 'active')
                 ORDER BY r.approved_at DESC, r.room_id DESC
                 LIMIT {$limit} ";
        $result = sql_query($sql);
        $rows = array();

        while ($row = sql_fetch_array($result)) {
            $owner_nick = $row['owner_mb_id'];
            if ($row['owner_mb_id'] !== '') {
                $mb = get_member($row['owner_mb_id'], 'mb_nick');
                if (!empty($mb['mb_nick'])) {
                    $owner_nick = $mb['mb_nick'];
                }
            }

            $rows[] = array(
                'room_id'        => (int) $row['room_id'],
                'room_name'      => get_text($row['room_name']),
                'emoji'          => get_text($row['emoji']),
                'owner_mb_id'    => get_text($row['owner_mb_id']),
                'owner_nick'     => get_text($owner_nick),
                'status'         => $row['status'],
                'ai_enabled'     => (int) $row['ai_enabled'],
                'admin_force_disabled' => (int) ($row['admin_force_disabled'] ?? 0),
                'ai_effective'   => eottae_talkroom_ai_is_room_ai_effective((int) $row['room_id']) ? 1 : 0,
                'ai_name'        => get_text($row['ai_name']),
                'ai_updated_at'  => $row['ai_updated_at'],
                'settings_url'   => eottae_talkroom_ai_settings_url((int) $row['room_id']),
                'manage_url'     => function_exists('eottae_talkroom_owner_manage_url')
                    ? eottae_talkroom_owner_manage_url((int) $row['room_id'])
                    : '',
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_ai_upgrade_schema')) {
    function eottae_talkroom_ai_upgrade_schema()
    {
        static $done = false;
        if ($done) {
            return;
        }

        $tables = eottae_talkroom_ai_table_names();
        if (!eottae_talkroom_ai_table_exists($tables['settings'])) {
            $done = true;

            return;
        }

        // 향후 컬럼 추가는 이 함수에서 ALTER 처리
        if (!eottae_talkroom_ai_column_exists($tables['settings'], 'site_ai_enabled')) {
            sql_query(" ALTER TABLE `{$tables['settings']}` ADD COLUMN `site_ai_enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `ai_enabled` ", false);
        }
        if (!eottae_talkroom_ai_column_exists($tables['settings'], 'admin_force_disabled')) {
            sql_query(" ALTER TABLE `{$tables['settings']}` ADD COLUMN `admin_force_disabled` tinyint(1) NOT NULL DEFAULT '0' AFTER `site_ai_enabled` ", false);
        }

        $done = true;
    }
}

if (!function_exists('eottae_talkroom_ai_ensure_schema')) {
    /**
     * 세부톡 AI 테이블 생성 (CREATE TABLE IF NOT EXISTS)
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_ai_ensure_schema()
    {
        $tables = eottae_talkroom_ai_table_names();
        $results = array();

        $ddl = array(
            'settings' => " CREATE TABLE IF NOT EXISTS `{$tables['settings']}` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `room_id` int(11) unsigned NOT NULL DEFAULT '0',
                `ai_enabled` tinyint(1) NOT NULL DEFAULT '0',
                `ai_name` varchar(60) NOT NULL DEFAULT '세부AI',
                `ai_persona` text NOT NULL,
                `ai_tone` varchar(30) NOT NULL DEFAULT 'friendly',
                `quiet_trigger_enabled` tinyint(1) NOT NULL DEFAULT '1',
                `daily_question_enabled` tinyint(1) NOT NULL DEFAULT '1',
                `welcome_enabled` tinyint(1) NOT NULL DEFAULT '1',
                `meetup_suggest_enabled` tinyint(1) NOT NULL DEFAULT '0',
                `summary_enabled` tinyint(1) NOT NULL DEFAULT '1',
                `reaction_enabled` tinyint(1) NOT NULL DEFAULT '0',
                `max_messages_per_day` int(11) unsigned NOT NULL DEFAULT '2',
                `min_silence_minutes` int(11) unsigned NOT NULL DEFAULT '2880',
                `active_start_time` time NOT NULL DEFAULT '08:00:00',
                `active_end_time` time NOT NULL DEFAULT '22:00:00',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_room_id` (`room_id`),
                KEY `idx_ai_enabled` (`ai_enabled`),
                KEY `idx_updated_at` (`updated_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ",

            'logs' => " CREATE TABLE IF NOT EXISTS `{$tables['logs']}` (
                `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `room_id` int(11) unsigned NOT NULL DEFAULT '0',
                `trigger_type` varchar(30) NOT NULL DEFAULT '',
                `prompt_text` mediumtext NOT NULL,
                `response_text` mediumtext NOT NULL,
                `post_id` int(11) unsigned NOT NULL DEFAULT '0',
                `comment_id` int(11) unsigned NOT NULL DEFAULT '0',
                `status` varchar(20) NOT NULL DEFAULT 'pending',
                `error_message` text NOT NULL,
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`log_id`),
                KEY `idx_room_id` (`room_id`),
                KEY `idx_trigger_type` (`trigger_type`),
                KEY `idx_status` (`status`),
                KEY `idx_created_at` (`created_at`),
                KEY `idx_room_created` (`room_id`, `created_at`),
                KEY `idx_post_id` (`post_id`),
                KEY `idx_comment_id` (`comment_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ",

            'daily_limits' => " CREATE TABLE IF NOT EXISTS `{$tables['daily_limits']}` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `room_id` int(11) unsigned NOT NULL DEFAULT '0',
                `target_date` date NOT NULL,
                `message_count` int(11) unsigned NOT NULL DEFAULT '0',
                `last_message_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_room_date` (`room_id`, `target_date`),
                KEY `idx_target_date` (`target_date`),
                KEY `idx_last_message_at` (`last_message_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ",
        );

        foreach ($ddl as $key => $sql) {
            $table = $tables[$key];
            $existed = eottae_talkroom_ai_table_exists($table);
            $ok = (bool) sql_query($sql, false);
            $results[] = array(
                'table'   => $table,
                'key'     => $key,
                'existed' => $existed,
                'ok'      => $ok,
                'action'  => $existed ? 'exists' : 'created',
            );
        }

        eottae_talkroom_ai_upgrade_schema();

        return $results;
    }
}

if (!function_exists('eottae_talkroom_ai_drop_schema')) {
    /**
     * 세부톡 AI 테이블 삭제 (롤백용)
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_ai_drop_schema()
    {
        $tables = eottae_talkroom_ai_table_names();
        $order = array('logs', 'daily_limits', 'settings');
        $results = array();

        foreach ($order as $key) {
            $table = $tables[$key];
            if (!eottae_talkroom_ai_table_exists($table)) {
                $results[] = array(
                    'table'  => $table,
                    'key'    => $key,
                    'ok'     => true,
                    'action' => 'missing',
                );
                continue;
            }

            $ok = (bool) sql_query(" DROP TABLE IF EXISTS `{$table}` ", false);
            $results[] = array(
                'table'  => $table,
                'key'    => $key,
                'ok'     => $ok,
                'action' => 'dropped',
            );
        }

        return $results;
    }
}

if (!function_exists('eottae_talkroom_ai_schema_status')) {
    /**
     * @return array<string, array<string, mixed>>
     */
    function eottae_talkroom_ai_schema_status()
    {
        $tables = eottae_talkroom_ai_table_names();
        $status = array();

        foreach ($tables as $key => $table) {
            $status[$key] = array(
                'table'  => $table,
                'exists' => eottae_talkroom_ai_table_exists($table),
            );
        }

        return $status;
    }
}

if (!function_exists('eottae_talkroom_ai_bot_mb_id')) {
    function eottae_talkroom_ai_bot_mb_id()
    {
        if (!function_exists('g5site_cfg') && defined('G5_PATH') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }

        $id = function_exists('g5site_cfg')
            ? trim((string) g5site_cfg('talkroom_ai_bot_mb_id', 'sebu_ai'))
            : 'sebu_ai';
        $id = preg_replace('/[^a-z0-9_]/', '', $id);

        return $id !== '' ? $id : 'sebu_ai';
    }
}

if (!function_exists('eottae_talkroom_ai_get_bot_member')) {
    function eottae_talkroom_ai_get_bot_member()
    {
        global $g5;

        $mb_id = eottae_talkroom_ai_bot_mb_id();
        if ($mb_id === '') {
            return null;
        }

        return sql_fetch(" SELECT * FROM {$g5['member_table']} WHERE mb_id = '".sql_escape_string($mb_id)."' LIMIT 1 ");
    }
}

if (!function_exists('eottae_talkroom_ai_ensure_bot_member')) {
    function eottae_talkroom_ai_ensure_bot_member($display_name = '어때봇')
    {
        global $g5;

        $existing = eottae_talkroom_ai_get_bot_member();
        if (!empty($existing['mb_id'])) {
            return array('ok' => true, 'message' => 'exists', 'mb_id' => $existing['mb_id']);
        }

        $mb_id = eottae_talkroom_ai_bot_mb_id();
        if ($mb_id === '') {
            return array('ok' => false, 'message' => 'AI 계정 ID가 올바르지 않습니다.');
        }

        $nick = sql_escape_string(cut_str(strip_tags((string) $display_name), 20, ''));
        if ($nick === '') {
            $nick = '어때봇';
        }

        try {
            $plain = bin2hex(random_bytes(16));
        } catch (Exception $e) {
            $plain = md5(uniqid((string) mt_rand(), true));
        }

        $password = function_exists('get_encrypt_string')
            ? get_encrypt_string($plain)
            : sql_password($plain);

        $sql = " INSERT INTO {$g5['member_table']} SET
            mb_id = '".sql_escape_string($mb_id)."',
            mb_password = '".sql_escape_string($password)."',
            mb_name = '{$nick}',
            mb_nick = '{$nick}',
            mb_nick_date = '".G5_TIME_YMD."',
            mb_email = '".sql_escape_string($mb_id.'@ai.local')."',
            mb_level = '2',
            mb_mailling = '0',
            mb_sms = '0',
            mb_open = '0',
            mb_point = '0',
            mb_datetime = '".G5_TIME_YMDHIS."',
            mb_ip = '127.0.0.1',
            mb_email_certify = '".G5_TIME_YMDHIS."' ";
        $ok = (bool) sql_query($sql, false);

        return array(
            'ok'      => $ok,
            'message' => $ok ? 'created' : 'AI 계정 생성에 실패했습니다.',
            'mb_id'   => $mb_id,
        );
    }
}

if (!function_exists('eottae_talkroom_ai_is_ai_write_row')) {
    function eottae_talkroom_ai_is_ai_write_row($write_row)
    {
        if (!is_array($write_row)) {
            return false;
        }

        $bot_id = eottae_talkroom_ai_bot_mb_id();
        if ($bot_id !== '' && ($write_row['mb_id'] ?? '') === $bot_id) {
            return true;
        }

        $marker = trim((string) ($write_row['wr_3'] ?? ''));

        return strpos($marker, 'ai:') === 0;
    }
}

if (!function_exists('eottae_talkroom_ai_is_within_active_hours')) {
    function eottae_talkroom_ai_is_within_active_hours(array $settings, $now = null)
    {
        $now = $now ?: G5_TIME_YMDHIS;
        $current = date('H:i:s', strtotime($now));
        $start = eottae_talkroom_ai_normalize_time($settings['active_start_time'] ?? '09:00:00');
        $end = eottae_talkroom_ai_normalize_time($settings['active_end_time'] ?? '22:00:00');

        if ($start <= $end) {
            return $current >= $start && $current <= $end;
        }

        return $current >= $start || $current <= $end;
    }
}

if (!function_exists('eottae_talkroom_ai_room_last_activity_at')) {
    function eottae_talkroom_ai_room_last_activity_at($room_id)
    {
        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $write_table = eottae_talkroom_write_table();
        if ($room_id < 1 || $write_table === '' || !eottae_talkroom_table_exists($write_table)) {
            return '';
        }

        $visible_p = eottae_talkroom_post_visible_sql('p');
        $visible_c = eottae_talkroom_post_visible_sql('c');
        $row = sql_fetch("
            SELECT MAX(t.activity_at) AS latest_at
            FROM (
                SELECT p.wr_datetime AS activity_at
                FROM `{$write_table}` p
                WHERE p.wr_is_comment = 0
                  AND p.wr_1 = '{$room_id}'
                  AND {$visible_p}
                UNION ALL
                SELECT c.wr_datetime AS activity_at
                FROM `{$write_table}` c
                INNER JOIN `{$write_table}` p
                    ON c.wr_parent = p.wr_id AND c.wr_is_comment = 1
                WHERE p.wr_1 = '{$room_id}'
                  AND {$visible_p}
                  AND {$visible_c}
            ) t
        ", false);

        return trim((string) ($row['latest_at'] ?? ''));
    }
}

if (!function_exists('eottae_talkroom_ai_room_latest_post_row')) {
    function eottae_talkroom_ai_room_latest_post_row($room_id)
    {
        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $write_table = eottae_talkroom_write_table();
        if ($room_id < 1 || $write_table === '') {
            return null;
        }

        $visible = eottae_talkroom_post_visible_sql();

        return sql_fetch("
            SELECT *
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_1 = '{$room_id}'
              AND {$visible}
            ORDER BY wr_id DESC
            LIMIT 1
        ", false);
    }
}

if (!function_exists('eottae_talkroom_ai_room_last_ai_post_at')) {
    function eottae_talkroom_ai_room_last_ai_post_at($room_id)
    {
        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $write_table = eottae_talkroom_write_table();
        $bot_id = eottae_talkroom_ai_bot_mb_id();
        if ($room_id < 1 || $write_table === '' || $bot_id === '') {
            return '';
        }

        $visible = eottae_talkroom_post_visible_sql();
        $row = sql_fetch("
            SELECT wr_datetime
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_1 = '{$room_id}'
              AND {$visible}
              AND (mb_id = '".sql_escape_string($bot_id)."' OR wr_3 LIKE 'ai:%')
            ORDER BY wr_id DESC
            LIMIT 1
        ", false);

        return trim((string) ($row['wr_datetime'] ?? ''));
    }
}

if (!function_exists('eottae_talkroom_ai_get_today_message_count')) {
    function eottae_talkroom_ai_get_today_message_count($room_id, $target_date = null)
    {
        $room_id = (int) $room_id;
        $target_date = $target_date ?: G5_TIME_YMD;
        $tables = eottae_talkroom_ai_table_names();

        if (!eottae_talkroom_ai_table_exists($tables['daily_limits'])) {
            return 0;
        }

        $row = sql_fetch("
            SELECT message_count
            FROM `{$tables['daily_limits']}`
            WHERE room_id = '{$room_id}'
              AND target_date = '".sql_escape_string($target_date)."'
            LIMIT 1
        ", false);

        return (int) ($row['message_count'] ?? 0);
    }
}

if (!function_exists('eottae_talkroom_ai_increment_daily_count')) {
    function eottae_talkroom_ai_increment_daily_count($room_id, $now = null, array $options = array())
    {
        if (!empty($options['is_test'])) {
            return 0;
        }

        $room_id = (int) $room_id;
        $now = $now ?: G5_TIME_YMDHIS;
        $target_date = substr($now, 0, 10);
        $tables = eottae_talkroom_ai_table_names();

        if (!eottae_talkroom_ai_table_exists($tables['daily_limits'])) {
            eottae_talkroom_ai_ensure_schema();
        }

        $existing = sql_fetch("
            SELECT id, message_count
            FROM `{$tables['daily_limits']}`
            WHERE room_id = '{$room_id}'
              AND target_date = '".sql_escape_string($target_date)."'
            LIMIT 1
        ", false);

        if (!empty($existing['id'])) {
            sql_query("
                UPDATE `{$tables['daily_limits']}` SET
                    message_count = message_count + 1,
                    last_message_at = '".sql_escape_string($now)."'
                WHERE id = '".(int) $existing['id']."'
            ", false);

            return (int) $existing['message_count'] + 1;
        }

        sql_query("
            INSERT INTO `{$tables['daily_limits']}` SET
                room_id = '{$room_id}',
                target_date = '".sql_escape_string($target_date)."',
                message_count = 1,
                last_message_at = '".sql_escape_string($now)."'
        ", false);

        return 1;
    }
}

if (!function_exists('eottae_talkroom_ai_has_success_log_on_date')) {
    function eottae_talkroom_ai_has_success_log_on_date($room_id, $trigger_type, $target_date = null)
    {
        $room_id = (int) $room_id;
        $target_date = $target_date ?: G5_TIME_YMD;
        $tables = eottae_talkroom_ai_table_names();

        if (!eottae_talkroom_ai_table_exists($tables['logs'])) {
            return false;
        }

        $row = sql_fetch("
            SELECT log_id
            FROM `{$tables['logs']}`
            WHERE room_id = '{$room_id}'
              AND trigger_type = '".sql_escape_string((string) $trigger_type)."'
              AND status = 'success'
              AND created_at >= '".sql_escape_string($target_date)." 00:00:00'
              AND created_at <= '".sql_escape_string($target_date)." 23:59:59'
            LIMIT 1
        ", false);

        return !empty($row['log_id']);
    }
}

if (!function_exists('eottae_talkroom_ai_has_success_log_within_days')) {
    function eottae_talkroom_ai_has_success_log_within_days($room_id, $trigger_type, $days = 7)
    {
        $room_id = (int) $room_id;
        $days = max(1, (int) $days);
        $tables = eottae_talkroom_ai_table_names();

        if (!eottae_talkroom_ai_table_exists($tables['logs'])) {
            return false;
        }

        $since = date('Y-m-d H:i:s', G5_SERVER_TIME - ($days * 86400));
        $row = sql_fetch("
            SELECT log_id
            FROM `{$tables['logs']}`
            WHERE room_id = '{$room_id}'
              AND trigger_type = '".sql_escape_string((string) $trigger_type)."'
              AND status = 'success'
              AND created_at >= '".sql_escape_string($since)."'
            LIMIT 1
        ", false);

        return !empty($row['log_id']);
    }
}

if (!function_exists('eottae_talkroom_ai_write_log')) {
    function eottae_talkroom_ai_write_log($room_id, $trigger_type, array $data = array())
    {
        $room_id = (int) $room_id;
        $tables = eottae_talkroom_ai_table_names();

        if (!eottae_talkroom_ai_table_exists($tables['logs'])) {
            eottae_talkroom_ai_ensure_schema();
        }

        $statuses = eottae_talkroom_ai_log_statuses();
        $status = isset($data['status']) ? trim((string) $data['status']) : 'pending';
        if (!isset($statuses[$status])) {
            $status = 'pending';
        }

        $sql = " INSERT INTO `{$tables['logs']}` SET
            room_id = '{$room_id}',
            trigger_type = '".sql_escape_string((string) $trigger_type)."',
            prompt_text = '".sql_escape_string((string) ($data['prompt_text'] ?? ''))."',
            response_text = '".sql_escape_string((string) ($data['response_text'] ?? ''))."',
            post_id = '".(int) ($data['post_id'] ?? 0)."',
            comment_id = '".(int) ($data['comment_id'] ?? 0)."',
            status = '".sql_escape_string($status)."',
            error_message = '".sql_escape_string((string) ($data['error_message'] ?? ''))."',
            created_at = '".G5_TIME_YMDHIS."' ";
        $ok = (bool) sql_query($sql, false);

        return array(
            'ok'     => $ok,
            'log_id' => $ok ? (int) sql_insert_id() : 0,
        );
    }
}

if (!function_exists('eottae_talkroom_ai_insert_post')) {
    function eottae_talkroom_ai_insert_post($room_id, $subject, $content, array $options = array())
    {
        global $g5;

        if (!function_exists('eottae_talkroom_board_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $board_check = eottae_talkroom_require_board();
        if ($room_id < 1 || empty($board_check['ok'])) {
            return array('ok' => false, 'message' => $board_check['message'] ?? '톡방 게시판을 찾을 수 없습니다.');
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array('ok' => false, 'message' => '운영 중인 톡방이 아닙니다.');
        }

        $ai_name = trim((string) ($options['ai_name'] ?? '어때봇'));
        if ($ai_name === '') {
            $ai_name = '어때봇';
        }

        $bot = eottae_talkroom_ai_ensure_bot_member($ai_name);
        if (empty($bot['ok'])) {
            return array('ok' => false, 'message' => $bot['message']);
        }

        $member = eottae_talkroom_ai_get_bot_member();
        if (!$member) {
            return array('ok' => false, 'message' => 'AI 계정을 찾을 수 없습니다.');
        }

        $trigger_type = trim((string) ($options['trigger_type'] ?? 'quiet_room'));
        $ca_name = trim((string) ($options['ca_name'] ?? 'AI·화제'));
        $bo_table = preg_replace('/[^a-z0-9_]/', '', eottae_talkroom_board_table());
        $write_table = G5_TABLE_PREFIX.'write_'.$bo_table;

        $subject = strip_tags((string) $subject);
        $content = (string) $content;
        if ($subject === '' || $content === '') {
            return array('ok' => false, 'message' => '제목과 내용이 필요합니다.');
        }

        $subject_sql = sql_escape_string($subject);
        $content_sql = sql_escape_string($content);
        $ca_name_sql = sql_escape_string($ca_name);
        $mb_id = sql_escape_string($member['mb_id']);
        $wr_name = sql_escape_string($ai_name);
        $wr_email = sql_escape_string($member['mb_email'] ?? ($member['mb_id'].'@ai.local'));
        $wr_3 = sql_escape_string('ai:'.$trigger_type);
        $seo = sql_escape_string(preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($subject)));

        sql_query(" INSERT INTO `{$write_table}` SET
            wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM `{$write_table}` AS sq),
            wr_reply = '',
            wr_comment = 0,
            ca_name = '{$ca_name_sql}',
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
            mb_id = '{$mb_id}',
            wr_password = '',
            wr_name = '{$wr_name}',
            wr_email = '{$wr_email}',
            wr_homepage = '',
            wr_datetime = '".G5_TIME_YMDHIS."',
            wr_last = '".G5_TIME_YMDHIS."',
            wr_ip = '127.0.0.1',
            wr_1 = '{$room_id}',
            wr_2 = '',
            wr_3 = '{$wr_3}',
            wr_4 = '',
            wr_5 = '',
            wr_6 = '',
            wr_7 = '',
            wr_8 = '',
            wr_9 = '',
            wr_10 = '' ", false);

        $wr_id = (int) sql_insert_id();
        if ($wr_id < 1) {
            return array('ok' => false, 'message' => '게시글 등록에 실패했습니다.');
        }

        sql_query(" UPDATE `{$write_table}` SET wr_parent = '{$wr_id}' WHERE wr_id = '{$wr_id}' ", false);
        sql_query(" INSERT INTO {$g5['board_new_table']}
            (bo_table, wr_id, wr_parent, bn_datetime, mb_id)
            VALUES ('{$bo_table}', '{$wr_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', '{$mb_id}') ", false);
        sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '{$bo_table}' ", false);

        if (function_exists('delete_cache_latest')) {
            delete_cache_latest($bo_table);
        }

        return array(
            'ok'      => true,
            'message' => '게시글이 등록되었습니다.',
            'wr_id'   => $wr_id,
            'subject' => $subject,
            'content' => $content,
        );
    }
}

if (!function_exists('eottae_talkroom_ai_get_welcome_hub_post')) {
    function eottae_talkroom_ai_get_welcome_hub_post($room_id)
    {
        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $write_table = eottae_talkroom_write_table();
        if ($room_id < 1 || $write_table === '') {
            return null;
        }

        $visible = eottae_talkroom_post_visible_sql();

        return sql_fetch("
            SELECT *
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_1 = '{$room_id}'
              AND wr_3 = 'ai:welcome_hub'
              AND {$visible}
            ORDER BY wr_id ASC
            LIMIT 1
        ", false);
    }
}

if (!function_exists('eottae_talkroom_ai_ensure_welcome_hub_post')) {
    function eottae_talkroom_ai_ensure_welcome_hub_post($room_id, $ai_name = '어때봇')
    {
        $existing = eottae_talkroom_ai_get_welcome_hub_post($room_id);
        if (!empty($existing['wr_id'])) {
            return array(
                'ok'    => true,
                'wr_id' => (int) $existing['wr_id'],
                'post'  => $existing,
            );
        }

        $subject = '['.$ai_name.'] 새 회원 환영';
        $content = '새로 오신 분들을 환영합니다. 아래에서 AI 도우미의 환영 메시지를 확인할 수 있습니다.';

        return eottae_talkroom_ai_insert_post($room_id, $subject, $content, array(
            'ai_name'      => $ai_name,
            'trigger_type' => 'welcome_hub',
            'ca_name'      => 'AI·환영',
        ));
    }
}

if (!function_exists('eottae_talkroom_ai_insert_comment')) {
    function eottae_talkroom_ai_insert_comment($room_id, $parent_wr_id, $content, array $options = array())
    {
        global $g5;

        if (!function_exists('eottae_talkroom_board_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $parent_wr_id = (int) $parent_wr_id;
        $board_check = eottae_talkroom_require_board();
        if ($room_id < 1 || $parent_wr_id < 1 || empty($board_check['ok'])) {
            return array('ok' => false, 'message' => $board_check['message'] ?? '댓글을 등록할 게시글 정보가 올바르지 않습니다.');
        }

        $write_table = eottae_talkroom_write_table();
        $parent = sql_fetch("
            SELECT *
            FROM `{$write_table}`
            WHERE wr_id = '{$parent_wr_id}'
              AND wr_is_comment = 0
              AND wr_1 = '{$room_id}'
            LIMIT 1
        ", false);

        if (empty($parent['wr_id'])) {
            return array('ok' => false, 'message' => '환영 게시글을 찾을 수 없습니다.');
        }

        $ai_name = trim((string) ($options['ai_name'] ?? '어때봇'));
        if ($ai_name === '') {
            $ai_name = '어때봇';
        }

        $display_name = trim((string) ($options['display_name'] ?? ''));
        if ($display_name === '' && !empty($options['show_ai_helper_label'])) {
            $display_name = $ai_name.' · AI 도우미';
        }

        $bot = eottae_talkroom_ai_ensure_bot_member($ai_name);
        if (empty($bot['ok'])) {
            return array('ok' => false, 'message' => $bot['message']);
        }

        $member = eottae_talkroom_ai_get_bot_member();
        if (!$member) {
            return array('ok' => false, 'message' => 'AI 계정을 찾을 수 없습니다.');
        }

        $content = trim(strip_tags((string) $content));
        if ($content === '') {
            return array('ok' => false, 'message' => '댓글 내용이 비어 있습니다.');
        }

        $trigger_type = trim((string) ($options['trigger_type'] ?? 'welcome'));
        $target_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($options['target_mb_id'] ?? ''));
        $bump_parent_last = !empty($options['bump_parent_last']);

        $row = sql_fetch("
            SELECT MAX(wr_comment) AS max_comment
            FROM `{$write_table}`
            WHERE wr_parent = '{$parent_wr_id}'
              AND wr_is_comment = 1
        ", false);
        $tmp_comment = isset($row['max_comment']) ? ((int) $row['max_comment'] + 1) : 1;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', eottae_talkroom_board_table());
        $mb_id = sql_escape_string($member['mb_id']);
        $wr_name = sql_escape_string($display_name !== '' ? $display_name : $ai_name);
        $wr_email = sql_escape_string($member['mb_email'] ?? ($member['mb_id'].'@ai.local'));
        $content_sql = sql_escape_string($content);
        $ca_name_sql = sql_escape_string($parent['ca_name'] ?? 'AI·환영');
        $wr_num = sql_escape_string($parent['wr_num'] ?? '0');
        $wr_3 = sql_escape_string('ai:'.$trigger_type);
        $wr_4 = sql_escape_string($target_mb_id);

        sql_query(" INSERT INTO `{$write_table}` SET
            ca_name = '{$ca_name_sql}',
            wr_option = '',
            wr_num = '{$wr_num}',
            wr_reply = '',
            wr_parent = '{$parent_wr_id}',
            wr_is_comment = 1,
            wr_comment = '{$tmp_comment}',
            wr_comment_reply = '',
            wr_subject = '',
            wr_content = '{$content_sql}',
            mb_id = '{$mb_id}',
            wr_password = '',
            wr_name = '{$wr_name}',
            wr_email = '{$wr_email}',
            wr_homepage = '',
            wr_datetime = '".G5_TIME_YMDHIS."',
            wr_last = '',
            wr_ip = '127.0.0.1',
            wr_1 = '{$room_id}',
            wr_2 = '',
            wr_3 = '{$wr_3}',
            wr_4 = '{$wr_4}',
            wr_5 = '',
            wr_6 = '',
            wr_7 = '',
            wr_8 = '',
            wr_9 = '',
            wr_10 = '' ", false);

        $comment_id = (int) sql_insert_id();
        if ($comment_id < 1) {
            return array('ok' => false, 'message' => '댓글 등록에 실패했습니다.');
        }

        if ($bump_parent_last) {
            sql_query("
                UPDATE `{$write_table}` SET
                    wr_comment = wr_comment + 1,
                    wr_last = '".G5_TIME_YMDHIS."'
                WHERE wr_id = '{$parent_wr_id}'
            ", false);
        } else {
            sql_query("
                UPDATE `{$write_table}` SET wr_comment = wr_comment + 1
                WHERE wr_id = '{$parent_wr_id}'
            ", false);
        }

        sql_query("
            INSERT INTO {$g5['board_new_table']}
            (bo_table, wr_id, wr_parent, bn_datetime, mb_id)
            VALUES ('{$bo_table}', '{$comment_id}', '{$parent_wr_id}', '".G5_TIME_YMDHIS."', '{$mb_id}')
        ", false);
        sql_query("
            UPDATE {$g5['board_table']} SET bo_count_comment = bo_count_comment + 1
            WHERE bo_table = '{$bo_table}'
        ", false);

        return array(
            'ok'         => true,
            'message'    => '댓글이 등록되었습니다.',
            'comment_id' => $comment_id,
            'post_id'    => $parent_wr_id,
            'content'    => $content,
        );
    }
}
