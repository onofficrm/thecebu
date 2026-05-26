<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_ai_settings_table')) {
    function eottae_public_ai_settings_table()
    {
        global $g5;
        if (!isset($g5['sebu_public_ai_settings_table'])) {
            $g5['sebu_public_ai_settings_table'] = G5_TABLE_PREFIX.'sebu_public_ai_settings';
        }

        return $g5['sebu_public_ai_settings_table'];
    }
}

if (!function_exists('eottae_public_ai_candidates_table')) {
    function eottae_public_ai_candidates_table()
    {
        global $g5;
        if (!isset($g5['sebu_public_ai_candidates_table'])) {
            $g5['sebu_public_ai_candidates_table'] = G5_TABLE_PREFIX.'sebu_public_ai_candidates';
        }

        return $g5['sebu_public_ai_candidates_table'];
    }
}

if (!function_exists('eottae_public_ai_logs_table')) {
    function eottae_public_ai_logs_table()
    {
        global $g5;
        if (!isset($g5['sebu_public_ai_logs_table'])) {
            $g5['sebu_public_ai_logs_table'] = G5_TABLE_PREFIX.'sebu_public_ai_logs';
        }

        return $g5['sebu_public_ai_logs_table'];
    }
}

if (!function_exists('eottae_public_ai_trigger_types')) {
    function eottae_public_ai_trigger_types()
    {
        return array(
            'calendar_today'     => '오늘 일정',
            'calendar_tomorrow'  => '내일 일정',
            'calendar_day_after' => '모레 일정',
            'holiday'            => '공휴일',
            'weather'            => '날씨',
            'talk_room_activity' => '세부톡방 활동',
            'popular_post'       => '인기글',
            'business_event'     => '업체 이벤트',
            'quiet_chat'         => '조용한 공개톡',
            'daily_question'     => '오늘의 질문',
            'external_news'      => '외부뉴스',
            'admin_manual'       => '관리자 수동',
        );
    }
}

if (!function_exists('eottae_public_ai_source_types')) {
    function eottae_public_ai_source_types()
    {
        return array(
            'calendar'       => '캘린더',
            'weather'        => '날씨',
            'holiday'        => '공휴일',
            'talk_room'      => '세부톡방',
            'post'           => '게시글',
            'business_event' => '업체 이벤트',
            'external_news'  => '외부뉴스',
            'manual'         => '수동',
        );
    }
}

if (!function_exists('eottae_public_ai_candidate_statuses')) {
    function eottae_public_ai_candidate_statuses()
    {
        return array(
            'pending'   => '승인 대기',
            'approved'  => '승인됨',
            'rejected'  => '반려',
            'published' => '발행됨',
            'deleted'   => '삭제',
            'failed'    => '실패',
        );
    }
}

if (!function_exists('eottae_public_ai_log_publish_statuses')) {
    function eottae_public_ai_log_publish_statuses()
    {
        return array(
            'success' => '성공',
            'failed'  => '실패',
            'skipped' => '건너뜀',
        );
    }
}

if (!function_exists('eottae_public_ai_label')) {
    function eottae_public_ai_label($map, $key, $fallback = '')
    {
        $key = trim((string) $key);

        return isset($map[$key]) ? $map[$key] : ($fallback !== '' ? $fallback : $key);
    }
}

if (!function_exists('eottae_public_ai_ensure_schema')) {
    function eottae_public_ai_ensure_schema()
    {
        if (!function_exists('eottae_talkroom_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $settings_table = eottae_public_ai_settings_table();
        $candidates_table = eottae_public_ai_candidates_table();
        $logs_table = eottae_public_ai_logs_table();
        $ok = true;

        if (!eottae_talkroom_table_exists($settings_table)) {
            $ok = $ok && (bool) sql_query("
                CREATE TABLE IF NOT EXISTS `{$settings_table}` (
                    `id` tinyint(3) unsigned NOT NULL DEFAULT '1',
                    `ai_enabled` tinyint(1) NOT NULL DEFAULT '0',
                    `ai_name` varchar(50) NOT NULL DEFAULT '어때봇',
                    `ai_persona` varchar(255) NOT NULL DEFAULT '',
                    `auto_publish` tinyint(1) NOT NULL DEFAULT '0',
                    `require_admin_approval` tinyint(1) NOT NULL DEFAULT '1',
                    `max_messages_per_day` smallint(5) unsigned NOT NULL DEFAULT '3',
                    `min_silence_minutes` smallint(5) unsigned NOT NULL DEFAULT '180',
                    `active_start_time` time NOT NULL DEFAULT '08:00:00',
                    `active_end_time` time NOT NULL DEFAULT '22:00:00',
                    `use_calendar` tinyint(1) NOT NULL DEFAULT '1',
                    `use_weather` tinyint(1) NOT NULL DEFAULT '0',
                    `use_holidays` tinyint(1) NOT NULL DEFAULT '1',
                    `use_talk_rooms` tinyint(1) NOT NULL DEFAULT '1',
                    `use_popular_posts` tinyint(1) NOT NULL DEFAULT '1',
                    `use_business_events` tinyint(1) NOT NULL DEFAULT '1',
                    `use_external_news` tinyint(1) NOT NULL DEFAULT '0',
                    `updated_by` varchar(20) NOT NULL DEFAULT '',
                    `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ", false);
        }

        if (!eottae_talkroom_table_exists($candidates_table)) {
            $ok = $ok && (bool) sql_query("
                CREATE TABLE IF NOT EXISTS `{$candidates_table}` (
                    `candidate_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `trigger_type` varchar(40) NOT NULL DEFAULT '',
                    `source_type` varchar(40) NOT NULL DEFAULT '',
                    `source_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `title` varchar(120) NOT NULL DEFAULT '',
                    `message` text NOT NULL,
                    `action_label` varchar(80) NOT NULL DEFAULT '',
                    `action_url` varchar(255) NOT NULL DEFAULT '',
                    `status` varchar(20) NOT NULL DEFAULT 'pending',
                    `admin_memo` varchar(500) NOT NULL DEFAULT '',
                    `approved_by` varchar(20) NOT NULL DEFAULT '',
                    `approved_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `published_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`candidate_id`),
                    KEY `idx_public_ai_candidate_status` (`status`, `created_at`),
                    KEY `idx_public_ai_candidate_trigger` (`trigger_type`, `created_at`),
                    KEY `idx_public_ai_candidate_source` (`source_type`, `source_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ", false);
        }

        if (is_file(G5_LIB_PATH.'/eottae-public-ai-weather.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-weather.lib.php';
            if (function_exists('eottae_public_ai_weather_ensure_schema')) {
                eottae_public_ai_weather_ensure_schema();
            }
        }
        if (is_file(G5_LIB_PATH.'/eottae-public-ai-news.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-news.lib.php';
            if (function_exists('eottae_public_ai_external_news_ensure_schema')) {
                eottae_public_ai_external_news_ensure_schema();
            }
        }

        eottae_public_ai_upgrade_candidate_columns();

        if (is_file(G5_LIB_PATH.'/eottae-public-ai-openai.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-openai.lib.php';
            if (function_exists('eottae_public_ai_openai_ensure_schema')) {
                eottae_public_ai_openai_ensure_schema();
            }
        }

        if (!eottae_talkroom_table_exists($logs_table)) {
            $ok = $ok && (bool) sql_query("
                CREATE TABLE IF NOT EXISTS `{$logs_table}` (
                    `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `candidate_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `trigger_type` varchar(40) NOT NULL DEFAULT '',
                    `message` varchar(500) NOT NULL DEFAULT '',
                    `publish_status` varchar(20) NOT NULL DEFAULT '',
                    `chat_message_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `error_message` varchar(500) NOT NULL DEFAULT '',
                    `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`log_id`),
                    KEY `idx_public_ai_log_status` (`publish_status`, `created_at`),
                    KEY `idx_public_ai_log_candidate` (`candidate_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ", false);
        }

        $defaults = eottae_public_ai_default_settings();
        $row = sql_fetch(" SELECT id FROM `{$settings_table}` WHERE id = 1 LIMIT 1 ", false);
        if (empty($row['id'])) {
            $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
            sql_query("
                INSERT INTO `{$settings_table}` SET
                    id = 1,
                    ai_enabled = '".(int) $defaults['ai_enabled']."',
                    ai_name = '".sql_escape_string($defaults['ai_name'])."',
                    ai_persona = '".sql_escape_string($defaults['ai_persona'])."',
                    auto_publish = '".(int) $defaults['auto_publish']."',
                    require_admin_approval = '".(int) $defaults['require_admin_approval']."',
                    max_messages_per_day = '".(int) $defaults['max_messages_per_day']."',
                    min_silence_minutes = '".(int) $defaults['min_silence_minutes']."',
                    active_start_time = '".sql_escape_string($defaults['active_start_time'])."',
                    active_end_time = '".sql_escape_string($defaults['active_end_time'])."',
                    use_calendar = '".(int) $defaults['use_calendar']."',
                    use_weather = '".(int) $defaults['use_weather']."',
                    use_holidays = '".(int) $defaults['use_holidays']."',
                    use_talk_rooms = '".(int) $defaults['use_talk_rooms']."',
                    use_popular_posts = '".(int) $defaults['use_popular_posts']."',
                    use_business_events = '".(int) $defaults['use_business_events']."',
                    use_external_news = '".(int) $defaults['use_external_news']."',
                    created_at = '{$now}',
                    updated_at = '{$now}'
            ", false);
        }

        return array('ok' => $ok);
    }
}

if (!function_exists('eottae_public_ai_upgrade_candidate_columns')) {
    function eottae_public_ai_upgrade_candidate_columns()
    {
        if (!function_exists('eottae_talkroom_table_exists')) {
            return;
        }

        $table = eottae_public_ai_candidates_table();
        if (!eottae_talkroom_table_exists($table)) {
            return;
        }

        $columns = array(
            'is_sensitive'          => "ADD `is_sensitive` tinyint(1) NOT NULL DEFAULT '0'",
            'poll_options'          => "ADD `poll_options` text NOT NULL",
            'force_admin_approval'  => "ADD `force_admin_approval` tinyint(1) NOT NULL DEFAULT '0'",
        );

        foreach ($columns as $col => $ddl) {
            $row = sql_fetch(" SHOW COLUMNS FROM `{$table}` LIKE '{$col}' ", false);
            if (empty($row['Field'])) {
                sql_query(" ALTER TABLE `{$table}` {$ddl} ", false);
            }
        }
    }
}

if (!function_exists('eottae_public_ai_default_settings')) {
    function eottae_public_ai_default_settings()
    {
        return array(
            'ai_enabled'             => 0,
            'ai_name'                => '어때봇',
            'ai_persona'             => '세부어때 공개톡 분위기 메이커',
            'auto_publish'           => 0,
            'require_admin_approval' => 1,
            'max_messages_per_day'   => 3,
            'min_silence_minutes'    => 180,
            'active_start_time'      => '08:00:00',
            'active_end_time'        => '22:00:00',
            'use_calendar'           => 1,
            'use_weather'            => 0,
            'use_holidays'           => 1,
            'use_talk_rooms'         => 1,
            'use_popular_posts'      => 1,
            'use_business_events'    => 1,
            'use_external_news'      => 0,
            'openai_enabled'            => 0,
            'openai_model'              => 'gpt-4o-mini',
            'openai_api_key'            => '',
            'openai_api_key_masked'     => '',
            'openai_api_key_source'     => '',
            'openai_max_calls_per_day'  => 20,
            'openai_max_message_length' => 400,
            'openai_fallback_template'  => 1,
        );
    }
}

if (!function_exists('eottae_public_ai_normalize_time')) {
    function eottae_public_ai_normalize_time($value, $default = '08:00:00')
    {
        $value = trim((string) $value);
        if (preg_match('/^\d{2}:\d{2}$/', $value)) {
            return $value.':00';
        }
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
            return $value;
        }

        return $default;
    }
}

if (!function_exists('eottae_public_ai_get_settings')) {
    function eottae_public_ai_get_settings()
    {
        eottae_public_ai_ensure_schema();
        $table = eottae_public_ai_settings_table();
        $defaults = eottae_public_ai_default_settings();
        $row = sql_fetch(" SELECT * FROM `{$table}` WHERE id = 1 LIMIT 1 ", false);
        if (empty($row)) {
            return $defaults;
        }

        $settings = array(
            'ai_enabled'             => (int) !empty($row['ai_enabled']),
            'ai_name'                => trim((string) ($row['ai_name'] ?? $defaults['ai_name'])) ?: $defaults['ai_name'],
            'ai_persona'             => trim((string) ($row['ai_persona'] ?? $defaults['ai_persona'])),
            'auto_publish'           => (int) !empty($row['auto_publish']),
            'require_admin_approval' => (int) !empty($row['require_admin_approval']),
            'max_messages_per_day'   => max(1, min(50, (int) ($row['max_messages_per_day'] ?? $defaults['max_messages_per_day']))),
            'min_silence_minutes'    => max(5, min(1440, (int) ($row['min_silence_minutes'] ?? $defaults['min_silence_minutes']))),
            'active_start_time'      => eottae_public_ai_normalize_time($row['active_start_time'] ?? '', $defaults['active_start_time']),
            'active_end_time'        => eottae_public_ai_normalize_time($row['active_end_time'] ?? '', $defaults['active_end_time']),
            'use_calendar'           => (int) !empty($row['use_calendar']),
            'use_weather'            => (int) !empty($row['use_weather']),
            'use_holidays'           => (int) !empty($row['use_holidays']),
            'use_talk_rooms'         => (int) !empty($row['use_talk_rooms']),
            'use_popular_posts'      => (int) !empty($row['use_popular_posts']),
            'use_business_events'    => (int) !empty($row['use_business_events']),
            'use_external_news'      => (int) !empty($row['use_external_news']),
            'updated_by'             => trim((string) ($row['updated_by'] ?? '')),
            'created_at'             => trim((string) ($row['created_at'] ?? '')),
            'updated_at'             => trim((string) ($row['updated_at'] ?? '')),
            'openai_enabled'            => (int) !empty($row['openai_enabled']),
            'openai_model'              => trim((string) ($row['openai_model'] ?? 'gpt-4o-mini')) ?: 'gpt-4o-mini',
            'openai_api_key'            => '',
            'openai_api_key_masked'     => '',
            'openai_api_key_source'     => '',
            'openai_max_calls_per_day'  => max(1, min(200, (int) ($row['openai_max_calls_per_day'] ?? 20))),
            'openai_max_message_length' => max(80, min(1000, (int) ($row['openai_max_message_length'] ?? 400))),
            'openai_fallback_template'  => (int) !empty($row['openai_fallback_template'] ?? 1),
        );

        if (is_file(G5_LIB_PATH.'/eottae-public-ai-openai.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-openai.lib.php';
            $key = eottae_public_ai_openai_resolve_api_key();
            $settings['openai_api_key_masked'] = eottae_public_ai_openai_mask_api_key($key);
            $settings['openai_api_key_source'] = eottae_public_ai_openai_api_key_source();
        }

        return $settings;
    }
}

if (!function_exists('eottae_public_ai_save_settings')) {
    function eottae_public_ai_save_settings(array $data, $admin_mb_id = '')
    {
        global $is_admin;

        if ($is_admin !== 'super') {
            return array('ok' => false, 'message' => '권한이 없습니다.');
        }

        eottae_public_ai_ensure_schema();
        $defaults = eottae_public_ai_default_settings();
        $table = eottae_public_ai_settings_table();
        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');

        $ai_name = trim(strip_tags((string) ($data['ai_name'] ?? $defaults['ai_name'])));
        if ($ai_name === '') {
            $ai_name = $defaults['ai_name'];
        }
        if (function_exists('mb_substr')) {
            $ai_name = mb_substr($ai_name, 0, 50, 'UTF-8');
        }

        $ai_persona = trim(strip_tags((string) ($data['ai_persona'] ?? $defaults['ai_persona'])));
        if (function_exists('mb_substr')) {
            $ai_persona = mb_substr($ai_persona, 0, 255, 'UTF-8');
        }

        $openai_model = trim(strip_tags((string) ($data['openai_model'] ?? 'gpt-4o-mini')));
        if ($openai_model === '') {
            $openai_model = 'gpt-4o-mini';
        }
        if (function_exists('mb_substr')) {
            $openai_model = mb_substr($openai_model, 0, 40, 'UTF-8');
        }

        $openai_key_sql = '';
        if (is_file(G5_LIB_PATH.'/eottae-public-ai-openai.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-openai.lib.php';
            $new_key = trim((string) ($data['openai_api_key'] ?? ''));
            $is_masked = $new_key === '' || preg_match('/^[•*.\s]+$/u', $new_key) || strpos($new_key, '…') !== false;
            if (!$is_masked && $new_key !== '') {
                $openai_key_sql = " openai_api_key = '".sql_escape_string($new_key)."', ";
            }
        }

        $ok = (bool) sql_query("
            INSERT INTO `{$table}` SET
                id = 1,
                ai_enabled = '".(!empty($data['ai_enabled']) ? 1 : 0)."',
                ai_name = '".sql_escape_string($ai_name)."',
                ai_persona = '".sql_escape_string($ai_persona)."',
                auto_publish = '".(!empty($data['auto_publish']) ? 1 : 0)."',
                require_admin_approval = '".(!empty($data['require_admin_approval']) ? 1 : 0)."',
                max_messages_per_day = '".max(1, min(50, (int) ($data['max_messages_per_day'] ?? $defaults['max_messages_per_day'])))."',
                min_silence_minutes = '".max(5, min(1440, (int) ($data['min_silence_minutes'] ?? $defaults['min_silence_minutes'])))."',
                active_start_time = '".sql_escape_string(eottae_public_ai_normalize_time($data['active_start_time'] ?? '', $defaults['active_start_time']))."',
                active_end_time = '".sql_escape_string(eottae_public_ai_normalize_time($data['active_end_time'] ?? '', $defaults['active_end_time']))."',
                use_calendar = '".(!empty($data['use_calendar']) ? 1 : 0)."',
                use_weather = '".(!empty($data['use_weather']) ? 1 : 0)."',
                use_holidays = '".(!empty($data['use_holidays']) ? 1 : 0)."',
                use_talk_rooms = '".(!empty($data['use_talk_rooms']) ? 1 : 0)."',
                use_popular_posts = '".(!empty($data['use_popular_posts']) ? 1 : 0)."',
                use_business_events = '".(!empty($data['use_business_events']) ? 1 : 0)."',
                use_external_news = '".(!empty($data['use_external_news']) ? 1 : 0)."',
                openai_enabled = '".(!empty($data['openai_enabled']) ? 1 : 0)."',
                openai_model = '".sql_escape_string($openai_model)."',
                openai_max_calls_per_day = '".max(1, min(200, (int) ($data['openai_max_calls_per_day'] ?? 20)))."',
                openai_max_message_length = '".max(80, min(1000, (int) ($data['openai_max_message_length'] ?? 400)))."',
                openai_fallback_template = '".(!empty($data['openai_fallback_template']) ? 1 : 0)."',
                updated_by = '".sql_escape_string($admin_mb_id)."',
                created_at = '{$now}',
                updated_at = '{$now}'
            ON DUPLICATE KEY UPDATE
                ai_enabled = VALUES(ai_enabled),
                ai_name = VALUES(ai_name),
                ai_persona = VALUES(ai_persona),
                auto_publish = VALUES(auto_publish),
                require_admin_approval = VALUES(require_admin_approval),
                max_messages_per_day = VALUES(max_messages_per_day),
                min_silence_minutes = VALUES(min_silence_minutes),
                active_start_time = VALUES(active_start_time),
                active_end_time = VALUES(active_end_time),
                use_calendar = VALUES(use_calendar),
                use_weather = VALUES(use_weather),
                use_holidays = VALUES(use_holidays),
                use_talk_rooms = VALUES(use_talk_rooms),
                use_popular_posts = VALUES(use_popular_posts),
                use_business_events = VALUES(use_business_events),
                use_external_news = VALUES(use_external_news),
                openai_enabled = VALUES(openai_enabled),
                openai_model = VALUES(openai_model),
                openai_max_calls_per_day = VALUES(openai_max_calls_per_day),
                openai_max_message_length = VALUES(openai_max_message_length),
                openai_fallback_template = VALUES(openai_fallback_template),
                {$openai_key_sql}
                updated_by = VALUES(updated_by),
                updated_at = VALUES(updated_at)
        ", false);

        return array(
            'ok'      => $ok,
            'message' => $ok ? '설정을 저장했습니다.' : '설정 저장에 실패했습니다.',
        );
    }
}

if (!function_exists('eottae_public_ai_format_candidate_row')) {
    function eottae_public_ai_format_candidate_row(array $row)
    {
        $trigger_map = eottae_public_ai_trigger_types();
        $source_map = eottae_public_ai_source_types();
        $status_map = eottae_public_ai_candidate_statuses();
        $message = trim((string) ($row['message'] ?? ''));
        $preview = $message;
        if (function_exists('cut_str')) {
            $preview = cut_str($message, 80, '…');
        } elseif (function_exists('mb_substr') && mb_strlen($message, 'UTF-8') > 80) {
            $preview = mb_substr($message, 0, 80, 'UTF-8').'…';
        }

        return array(
            'candidate_id'   => (int) ($row['candidate_id'] ?? 0),
            'trigger_type'   => trim((string) ($row['trigger_type'] ?? '')),
            'trigger_label'  => eottae_public_ai_label($trigger_map, $row['trigger_type'] ?? ''),
            'source_type'    => trim((string) ($row['source_type'] ?? '')),
            'source_label'   => eottae_public_ai_label($source_map, $row['source_type'] ?? ''),
            'source_id'      => (int) ($row['source_id'] ?? 0),
            'title'          => get_text($row['title'] ?? ''),
            'message'        => $message,
            'message_preview'=> get_text($preview),
            'action_label'   => get_text($row['action_label'] ?? ''),
            'action_url'     => get_text($row['action_url'] ?? ''),
            'status'         => trim((string) ($row['status'] ?? 'pending')),
            'status_label'   => eottae_public_ai_label($status_map, $row['status'] ?? ''),
            'admin_memo'     => get_text($row['admin_memo'] ?? ''),
            'approved_by'    => get_text($row['approved_by'] ?? ''),
            'approved_at'    => trim((string) ($row['approved_at'] ?? '')),
            'published_at'   => trim((string) ($row['published_at'] ?? '')),
            'created_at'     => trim((string) ($row['created_at'] ?? '')),
            'updated_at'     => trim((string) ($row['updated_at'] ?? '')),
            'is_sensitive'   => (int) !empty($row['is_sensitive']),
            'poll_options'   => trim((string) ($row['poll_options'] ?? '')),
            'force_admin_approval' => (int) !empty($row['force_admin_approval']),
        );
    }
}

if (!function_exists('eottae_public_ai_get_candidate')) {
    function eottae_public_ai_get_candidate($candidate_id)
    {
        $candidate_id = (int) $candidate_id;
        if ($candidate_id < 1) {
            return null;
        }

        eottae_public_ai_ensure_schema();
        $table = eottae_public_ai_candidates_table();
        $row = sql_fetch(" SELECT * FROM `{$table}` WHERE candidate_id = '{$candidate_id}' LIMIT 1 ", false);
        if (empty($row['candidate_id'])) {
            return null;
        }

        return eottae_public_ai_format_candidate_row($row);
    }
}

if (!function_exists('eottae_public_ai_save_candidate')) {
    function eottae_public_ai_save_candidate(array $data, $admin_mb_id = '')
    {
        global $is_admin;

        if ($is_admin !== 'super') {
            return array('ok' => false, 'message' => '권한이 없습니다.');
        }

        eottae_public_ai_ensure_schema();
        $table = eottae_public_ai_candidates_table();
        $trigger_types = array_keys(eottae_public_ai_trigger_types());
        $source_types = array_keys(eottae_public_ai_source_types());

        $candidate_id = (int) ($data['candidate_id'] ?? 0);
        $trigger_type = trim((string) ($data['trigger_type'] ?? 'admin_manual'));
        if (!in_array($trigger_type, $trigger_types, true)) {
            $trigger_type = 'admin_manual';
        }

        $source_type = trim((string) ($data['source_type'] ?? 'manual'));
        if (!in_array($source_type, $source_types, true)) {
            $source_type = 'manual';
        }

        $title = trim(strip_tags((string) ($data['title'] ?? '')));
        $message = trim(strip_tags((string) ($data['message'] ?? '')));
        if ($message === '') {
            return array('ok' => false, 'message' => '메시지 내용을 입력해 주세요.');
        }
        if (function_exists('mb_strlen') && mb_strlen($message, 'UTF-8') > 2000) {
            return array('ok' => false, 'message' => '메시지는 2000자 이내로 작성해 주세요.');
        }

        $action_label = trim(strip_tags((string) ($data['action_label'] ?? '')));
        $action_url = trim((string) ($data['action_url'] ?? ''));
        $admin_memo = trim(strip_tags((string) ($data['admin_memo'] ?? '')));
        $source_id = max(0, (int) ($data['source_id'] ?? 0));
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');

        if ($candidate_id > 0) {
            $existing = eottae_public_ai_get_candidate($candidate_id);
            if (!$existing) {
                return array('ok' => false, 'message' => '후보 메시지를 찾을 수 없습니다.');
            }
            if (in_array($existing['status'], array('published', 'deleted'), true)) {
                return array('ok' => false, 'message' => '발행·삭제된 메시지는 수정할 수 없습니다.');
            }

            $ok = (bool) sql_query("
                UPDATE `{$table}` SET
                    trigger_type = '".sql_escape_string($trigger_type)."',
                    source_type = '".sql_escape_string($source_type)."',
                    source_id = '{$source_id}',
                    title = '".sql_escape_string($title)."',
                    message = '".sql_escape_string($message)."',
                    action_label = '".sql_escape_string($action_label)."',
                    action_url = '".sql_escape_string($action_url)."',
                    admin_memo = '".sql_escape_string($admin_memo)."',
                    updated_at = '{$now}'
                WHERE candidate_id = '{$candidate_id}'
                LIMIT 1
            ", false);

            return array(
                'ok'           => $ok,
                'message'      => $ok ? '후보 메시지를 수정했습니다.' : '수정에 실패했습니다.',
                'candidate_id' => $candidate_id,
            );
        }

        $ok = (bool) sql_query("
            INSERT INTO `{$table}` SET
                trigger_type = '".sql_escape_string($trigger_type)."',
                source_type = '".sql_escape_string($source_type)."',
                source_id = '{$source_id}',
                title = '".sql_escape_string($title)."',
                message = '".sql_escape_string($message)."',
                action_label = '".sql_escape_string($action_label)."',
                action_url = '".sql_escape_string($action_url)."',
                status = 'pending',
                admin_memo = '".sql_escape_string($admin_memo)."',
                created_at = '{$now}',
                updated_at = '{$now}'
        ", false);

        $new_id = (int) sql_insert_id();

        return array(
            'ok'           => $ok && $new_id > 0,
            'message'      => ($ok && $new_id > 0) ? '후보 메시지를 등록했습니다.' : '등록에 실패했습니다.',
            'candidate_id' => $new_id,
        );
    }
}

if (!function_exists('eottae_public_ai_set_candidate_status')) {
    function eottae_public_ai_set_candidate_status($candidate_id, $status, $admin_mb_id = '')
    {
        global $is_admin;

        if ($is_admin !== 'super') {
            return array('ok' => false, 'message' => '권한이 없습니다.');
        }

        $candidate_id = (int) $candidate_id;
        $status = trim((string) $status);
        $allowed = array_keys(eottae_public_ai_candidate_statuses());
        if ($candidate_id < 1 || !in_array($status, $allowed, true)) {
            return array('ok' => false, 'message' => '요청이 올바르지 않습니다.');
        }

        $candidate = eottae_public_ai_get_candidate($candidate_id);
        if (!$candidate) {
            return array('ok' => false, 'message' => '후보 메시지를 찾을 수 없습니다.');
        }

        $table = eottae_public_ai_candidates_table();
        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        $approved_sql = '';
        if ($status === 'approved') {
            $approved_sql = ", approved_by = '".sql_escape_string($admin_mb_id)."', approved_at = '{$now}' ";
        }

        $ok = (bool) sql_query("
            UPDATE `{$table}` SET
                status = '".sql_escape_string($status)."',
                updated_at = '{$now}'
                {$approved_sql}
            WHERE candidate_id = '{$candidate_id}'
            LIMIT 1
        ", false);

        return array(
            'ok'      => $ok,
            'message' => $ok ? '상태를 변경했습니다.' : '상태 변경에 실패했습니다.',
        );
    }
}

if (!function_exists('eottae_public_ai_admin_list_candidates')) {
    function eottae_public_ai_admin_list_candidates($status = '', $limit = 50, $offset = 0)
    {
        eottae_public_ai_ensure_schema();
        $table = eottae_public_ai_candidates_table();
        $limit = max(1, min(200, (int) $limit));
        $offset = max(0, (int) $offset);
        $status = trim((string) $status);
        $where = " WHERE 1=1 ";
        if ($status !== '' && $status !== 'all') {
            $where .= " AND status = '".sql_escape_string($status)."' ";
        } else {
            $where .= " AND status <> 'deleted' ";
        }

        $rows = array();
        $result = sql_query("
            SELECT *
            FROM `{$table}`
            {$where}
            ORDER BY candidate_id DESC
            LIMIT {$offset}, {$limit}
        ", false);

        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $rows[] = eottae_public_ai_format_candidate_row($row);
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_public_ai_admin_count_candidates')) {
    function eottae_public_ai_admin_count_candidates($status = '')
    {
        eottae_public_ai_ensure_schema();
        $table = eottae_public_ai_candidates_table();
        $status = trim((string) $status);
        $where = " WHERE 1=1 ";
        if ($status !== '' && $status !== 'all') {
            $where .= " AND status = '".sql_escape_string($status)."' ";
        } else {
            $where .= " AND status <> 'deleted' ";
        }

        $row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$table}` {$where} ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_public_ai_insert_log')) {
    function eottae_public_ai_insert_log(array $data)
    {
        eottae_public_ai_ensure_schema();
        $table = eottae_public_ai_logs_table();
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');

        $candidate_id = max(0, (int) ($data['candidate_id'] ?? 0));
        $trigger_type = trim((string) ($data['trigger_type'] ?? ''));
        $message = trim((string) ($data['message'] ?? ''));
        if (function_exists('cut_str') && $message !== '') {
            $message = cut_str($message, 500, '…');
        }
        $publish_status = trim((string) ($data['publish_status'] ?? 'skipped'));
        $allowed_status = array_keys(eottae_public_ai_log_publish_statuses());
        if (!in_array($publish_status, $allowed_status, true)) {
            $publish_status = 'skipped';
        }

        $chat_message_id = max(0, (int) ($data['chat_message_id'] ?? 0));
        $error_message = trim((string) ($data['error_message'] ?? ''));
        if (function_exists('cut_str') && $error_message !== '') {
            $error_message = cut_str($error_message, 500, '…');
        }

        $ok = (bool) sql_query("
            INSERT INTO `{$table}` SET
                candidate_id = '{$candidate_id}',
                trigger_type = '".sql_escape_string($trigger_type)."',
                message = '".sql_escape_string($message)."',
                publish_status = '".sql_escape_string($publish_status)."',
                chat_message_id = '{$chat_message_id}',
                error_message = '".sql_escape_string($error_message)."',
                created_at = '{$now}'
        ", false);

        return array(
            'ok'     => $ok,
            'log_id' => (int) sql_insert_id(),
        );
    }
}

if (!function_exists('eottae_public_ai_admin_list_logs')) {
    function eottae_public_ai_admin_list_logs($limit = 50, $offset = 0)
    {
        eottae_public_ai_ensure_schema();
        $table = eottae_public_ai_logs_table();
        $limit = max(1, min(200, (int) $limit));
        $offset = max(0, (int) $offset);
        $trigger_map = eottae_public_ai_trigger_types();
        $status_map = eottae_public_ai_log_publish_statuses();
        $rows = array();

        $result = sql_query("
            SELECT *
            FROM `{$table}`
            ORDER BY log_id DESC
            LIMIT {$offset}, {$limit}
        ", false);

        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $message = trim((string) ($row['message'] ?? ''));
                $rows[] = array(
                    'log_id'           => (int) ($row['log_id'] ?? 0),
                    'candidate_id'     => (int) ($row['candidate_id'] ?? 0),
                    'trigger_type'     => trim((string) ($row['trigger_type'] ?? '')),
                    'trigger_label'    => eottae_public_ai_label($trigger_map, $row['trigger_type'] ?? ''),
                    'message'          => get_text($message),
                    'publish_status'   => trim((string) ($row['publish_status'] ?? '')),
                    'publish_label'    => eottae_public_ai_label($status_map, $row['publish_status'] ?? ''),
                    'chat_message_id'  => (int) ($row['chat_message_id'] ?? 0),
                    'error_message'    => get_text($row['error_message'] ?? ''),
                    'created_at'       => trim((string) ($row['created_at'] ?? '')),
                );
            }
        }

        return $rows;
    }
}


if (!function_exists('eottae_public_ai_mypage_admin_url')) {
    function eottae_public_ai_mypage_admin_url()
    {
        return G5_URL.'/page/eottae-mypage.php#sebu-public-ai-admin';
    }
}

if (!function_exists('eottae_public_ai_admin_settings_url')) {
    function eottae_public_ai_admin_settings_url()
    {
        return G5_URL.'/page/eottae-admin-public-ai.php';
    }
}

if (!function_exists('eottae_public_ai_admin_candidates_url')) {
    function eottae_public_ai_admin_candidates_url($status = '')
    {
        $url = G5_URL.'/page/eottae-admin-public-ai-candidates.php';
        $status = trim((string) $status);
        if ($status !== '') {
            $url .= '?status='.rawurlencode($status);
        }

        return $url;
    }
}

if (!function_exists('eottae_public_ai_admin_logs_url')) {
    function eottae_public_ai_admin_logs_url()
    {
        return G5_URL.'/page/eottae-admin-public-ai-logs.php';
    }
}

if (!function_exists('eottae_public_ai_admin_token')) {
    function eottae_public_ai_admin_token($regenerate = false)
    {
        $token = get_session('eottae_public_ai_admin_token');
        if ($regenerate || $token === '' || !is_string($token)) {
            try {
                $token = bin2hex(random_bytes(16));
            } catch (Exception $e) {
                $token = md5(uniqid((string) mt_rand(), true));
            }
            set_session('eottae_public_ai_admin_token', $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_public_ai_verify_admin_token')) {
    function eottae_public_ai_verify_admin_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_public_ai_admin_token');

        return $token !== '' && is_string($session_token) && hash_equals($session_token, $token);
    }
}

if (!function_exists('eottae_public_ai_pending_count')) {
    function eottae_public_ai_pending_count()
    {
        return eottae_public_ai_admin_count_candidates('pending');
    }
}

if (!function_exists('eottae_public_ai_mypage_dashboard_stats')) {
    /**
     * 마이페이지 공개단톡 AI 운영 지표
     *
     * @return array<string, mixed>
     */
    function eottae_public_ai_mypage_dashboard_stats($now = null)
    {
        eottae_public_ai_ensure_schema();
        $settings = eottae_public_ai_get_settings();
        $now = $now ?: (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));

        $published_today = 0;
        if (is_file(G5_LIB_PATH.'/eottae-public-ai-publish.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-publish.lib.php';
            if (function_exists('eottae_public_ai_count_public_chat_published_today')) {
                $published_today = eottae_public_ai_count_public_chat_published_today($now);
            }
        }

        $openai_success = 0;
        $openai_total = 0;
        if (is_file(G5_LIB_PATH.'/eottae-public-ai-openai.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-openai.lib.php';
            if (function_exists('eottae_public_ai_openai_count_calls_today')) {
                $openai_success = eottae_public_ai_openai_count_calls_today(true);
                $openai_total = eottae_public_ai_openai_count_calls_today(false);
            }
        }

        $candidates_table = eottae_public_ai_candidates_table();
        $last_candidate = sql_fetch("
            SELECT MAX(created_at) AS last_at
            FROM `{$candidates_table}`
        ", false);
        $last_published = sql_fetch("
            SELECT MAX(published_at) AS last_at
            FROM `{$candidates_table}`
            WHERE status = 'published' AND published_at > '0000-00-00 00:00:00'
        ", false);

        $last_openai_at = '';
        if (function_exists('eottae_public_ai_openai_logs_table')) {
            $openai_table = eottae_public_ai_openai_logs_table();
            $last_openai = sql_fetch("
                SELECT MAX(created_at) AS last_at
                FROM `{$openai_table}`
                WHERE is_test = 0
            ", false);
            $last_openai_at = trim((string) ($last_openai['last_at'] ?? ''));
        }

        return array(
            'published_today'          => $published_today,
            'max_messages_per_day'     => max(1, (int) ($settings['max_messages_per_day'] ?? 10)),
            'openai_calls_success'     => $openai_success,
            'openai_calls_total'       => $openai_total,
            'openai_max_calls_per_day' => max(1, (int) ($settings['openai_max_calls_per_day'] ?? 20)),
            'openai_enabled'           => !empty($settings['openai_enabled']),
            'last_candidate_at'        => trim((string) ($last_candidate['last_at'] ?? '')),
            'last_published_at'        => trim((string) ($last_published['last_at'] ?? '')),
            'last_openai_at'           => $last_openai_at,
        );
    }
}

if (!function_exists('eottae_public_ai_format_dashboard_datetime')) {
    function eottae_public_ai_format_dashboard_datetime($datetime)
    {
        $datetime = trim((string) $datetime);
        if ($datetime === '' || strpos($datetime, '0000-00-00') === 0) {
            return '—';
        }

        $ts = strtotime($datetime);
        if ($ts === false) {
            return '—';
        }

        $today = defined('G5_TIME_YMD') ? G5_TIME_YMD : date('Y-m-d');
        if (substr($datetime, 0, 10) === $today) {
            return '오늘 '.date('H:i', $ts);
        }

        return date('m/d H:i', $ts);
    }
}

if (!function_exists('eottae_public_ai_today_ymd')) {
    function eottae_public_ai_today_ymd($now = null)
    {
        $now = $now ?: (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));

        return substr($now, 0, 10);
    }
}

if (!function_exists('eottae_public_ai_day_start_datetime')) {
    function eottae_public_ai_day_start_datetime($ymd = '')
    {
        $ymd = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $ymd)
            ? (string) $ymd
            : eottae_public_ai_today_ymd();

        return $ymd.' 00:00:00';
    }
}

if (!function_exists('eottae_public_ai_is_within_active_hours')) {
    function eottae_public_ai_is_within_active_hours(array $settings, $now = null)
    {
        $now = $now ?: (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $current = date('H:i:s', strtotime($now));
        $start = eottae_public_ai_normalize_time($settings['active_start_time'] ?? '08:00:00');
        $end = eottae_public_ai_normalize_time($settings['active_end_time'] ?? '22:00:00');

        if ($start <= $end) {
            return $current >= $start && $current <= $end;
        }

        return $current >= $start || $current <= $end;
    }
}

if (!function_exists('eottae_public_ai_candidate_exists_today')) {
    function eottae_public_ai_candidate_exists_today($source_type, $source_id, $now = null)
    {
        eottae_public_ai_ensure_schema();
        $source_type = trim((string) $source_type);
        $source_id = max(0, (int) $source_id);
        if ($source_type === '' || $source_id < 1) {
            return false;
        }

        $table = eottae_public_ai_candidates_table();
        $day_start = eottae_public_ai_day_start_datetime(eottae_public_ai_today_ymd($now));
        $row = sql_fetch("
            SELECT candidate_id
            FROM `{$table}`
            WHERE source_type = '".sql_escape_string($source_type)."'
              AND source_id = '{$source_id}'
              AND created_at >= '".sql_escape_string($day_start)."'
              AND status NOT IN ('rejected', 'deleted')
            LIMIT 1
        ", false);

        return !empty($row['candidate_id']);
    }
}

if (!function_exists('eottae_public_ai_count_candidates_today_by_trigger')) {
    function eottae_public_ai_count_candidates_today_by_trigger($trigger_type, $now = null)
    {
        eottae_public_ai_ensure_schema();
        $trigger_type = trim((string) $trigger_type);
        if ($trigger_type === '') {
            return 0;
        }

        $table = eottae_public_ai_candidates_table();
        $day_start = eottae_public_ai_day_start_datetime(eottae_public_ai_today_ymd($now));
        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$table}`
            WHERE trigger_type = '".sql_escape_string($trigger_type)."'
              AND created_at >= '".sql_escape_string($day_start)."'
              AND status NOT IN ('rejected', 'deleted')
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_public_ai_count_candidates_today')) {
    function eottae_public_ai_count_candidates_today($now = null)
    {
        eottae_public_ai_ensure_schema();
        $table = eottae_public_ai_candidates_table();
        $day_start = eottae_public_ai_day_start_datetime(eottae_public_ai_today_ymd($now));
        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$table}`
            WHERE created_at >= '".sql_escape_string($day_start)."'
              AND status NOT IN ('rejected', 'deleted')
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_public_ai_normalize_message_compare')) {
    function eottae_public_ai_normalize_message_compare($message)
    {
        $message = trim(strip_tags((string) $message));
        $message = preg_replace('/\s+/u', ' ', $message);
        if (function_exists('mb_strtolower')) {
            $message = mb_strtolower($message, 'UTF-8');
        } else {
            $message = strtolower($message);
        }

        return $message;
    }
}

if (!function_exists('eottae_public_ai_messages_are_similar')) {
    function eottae_public_ai_messages_are_similar($a, $b)
    {
        $a = eottae_public_ai_normalize_message_compare($a);
        $b = eottae_public_ai_normalize_message_compare($b);
        if ($a === '' || $b === '') {
            return false;
        }
        if ($a === $b) {
            return true;
        }

        $len_a = function_exists('mb_strlen') ? mb_strlen($a, 'UTF-8') : strlen($a);
        $len_b = function_exists('mb_strlen') ? mb_strlen($b, 'UTF-8') : strlen($b);
        $prefix_len = min(40, $len_a, $len_b);
        if ($prefix_len > 0) {
            $prefix_a = function_exists('mb_substr') ? mb_substr($a, 0, $prefix_len, 'UTF-8') : substr($a, 0, $prefix_len);
            $prefix_b = function_exists('mb_substr') ? mb_substr($b, 0, $prefix_len, 'UTF-8') : substr($b, 0, $prefix_len);
            if ($prefix_a === $prefix_b) {
                return true;
            }
        }

        similar_text($a, $b, $percent);

        return $percent >= 82.0;
    }
}

if (!function_exists('eottae_public_ai_has_similar_published_message')) {
    function eottae_public_ai_has_similar_published_message($message, $days = 14)
    {
        eottae_public_ai_ensure_schema();
        $message = trim((string) $message);
        if ($message === '') {
            return false;
        }

        $table = eottae_public_ai_candidates_table();
        $since = date('Y-m-d H:i:s', strtotime('-'.max(1, (int) $days).' days'));
        $result = sql_query("
            SELECT message
            FROM `{$table}`
            WHERE status = 'published'
              AND published_at >= '".sql_escape_string($since)."'
            ORDER BY published_at DESC
            LIMIT 30
        ", false);

        if (!$result) {
            return false;
        }

        while ($row = sql_fetch_array($result)) {
            if (eottae_public_ai_messages_are_similar($message, $row['message'] ?? '')) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('eottae_public_ai_insert_pending_candidate')) {
    function eottae_public_ai_insert_pending_candidate(array $candidate)
    {
        eottae_public_ai_ensure_schema();
        $table = eottae_public_ai_candidates_table();
        $trigger_types = array_keys(eottae_public_ai_trigger_types());
        $source_types = array_keys(eottae_public_ai_source_types());

        $trigger_type = trim((string) ($candidate['trigger_type'] ?? ''));
        if (!in_array($trigger_type, $trigger_types, true)) {
            return array('ok' => false, 'message' => 'invalid_trigger', 'candidate_id' => 0);
        }

        $source_type = trim((string) ($candidate['source_type'] ?? ''));
        if (!in_array($source_type, $source_types, true)) {
            return array('ok' => false, 'message' => 'invalid_source', 'candidate_id' => 0);
        }

        $message = trim(strip_tags((string) ($candidate['message'] ?? '')));
        if ($message === '') {
            return array('ok' => false, 'message' => 'empty_message', 'candidate_id' => 0);
        }

        $title = trim(strip_tags((string) ($candidate['title'] ?? '')));
        $action_label = trim(strip_tags((string) ($candidate['action_label'] ?? '')));
        $action_url = trim((string) ($candidate['action_url'] ?? ''));
        $admin_memo = trim(strip_tags((string) ($candidate['admin_memo'] ?? '')));
        $source_id = max(0, (int) ($candidate['source_id'] ?? 0));
        $is_sensitive = !empty($candidate['is_sensitive']) ? 1 : 0;
        $force_admin = !empty($candidate['force_admin_approval']) ? 1 : 0;
        $poll_options = trim((string) ($candidate['poll_options'] ?? ''));
        if ($poll_options !== '' && function_exists('eottae_public_ai_poll_decode_options')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-poll.lib.php';
            $decoded = eottae_public_ai_poll_decode_options($poll_options);
            $poll_options = eottae_public_ai_poll_encode_options($decoded);
        }
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');

        $ok = (bool) sql_query("
            INSERT INTO `{$table}` SET
                trigger_type = '".sql_escape_string($trigger_type)."',
                source_type = '".sql_escape_string($source_type)."',
                source_id = '{$source_id}',
                title = '".sql_escape_string($title)."',
                message = '".sql_escape_string($message)."',
                action_label = '".sql_escape_string($action_label)."',
                action_url = '".sql_escape_string($action_url)."',
                status = 'pending',
                admin_memo = '".sql_escape_string($admin_memo)."',
                is_sensitive = '{$is_sensitive}',
                poll_options = '".sql_escape_string($poll_options)."',
                force_admin_approval = '{$force_admin}',
                created_at = '{$now}',
                updated_at = '{$now}'
        ", false);

        $new_id = (int) sql_insert_id();

        return array(
            'ok'           => $ok && $new_id > 0,
            'message'      => ($ok && $new_id > 0) ? 'saved' : 'insert_failed',
            'candidate_id' => $new_id,
        );
    }
}

if (!function_exists('eottae_public_ai_verify_cron_key')) {
    function eottae_public_ai_verify_cron_key($provided_key)
    {
        if (function_exists('eottae_talkroom_ai_verify_cron_key')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
            if (eottae_talkroom_ai_verify_cron_key($provided_key)) {
                return true;
            }
        }

        if (!function_exists('g5site_cfg') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }

        $secret = function_exists('g5site_cfg') ? trim((string) g5site_cfg('public_ai_cron_key', '')) : '';
        if ($secret === '' && function_exists('g5site_cfg')) {
            $secret = trim((string) g5site_cfg('talkroom_ai_cron_key', ''));
        }

        if ($secret === '') {
            return php_sapi_name() === 'cli';
        }

        return is_string($provided_key) && hash_equals($secret, (string) $provided_key);
    }
}
