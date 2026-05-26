<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_ai_openai_logs_table')) {
    function eottae_public_ai_openai_logs_table()
    {
        global $g5;
        if (!isset($g5['sebu_public_ai_openai_logs_table'])) {
            $g5['sebu_public_ai_openai_logs_table'] = G5_TABLE_PREFIX.'sebu_public_ai_openai_logs';
        }

        return $g5['sebu_public_ai_openai_logs_table'];
    }
}

if (!function_exists('eottae_public_ai_openai_ensure_schema')) {
    function eottae_public_ai_openai_ensure_schema()
    {
        if (!function_exists('eottae_talkroom_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $table = eottae_public_ai_openai_logs_table();
        if (!eottae_talkroom_table_exists($table)) {
            sql_query("
                CREATE TABLE IF NOT EXISTS `{$table}` (
                    `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `prompt_hash` char(40) NOT NULL DEFAULT '',
                    `trigger_type` varchar(40) NOT NULL DEFAULT '',
                    `source_type` varchar(40) NOT NULL DEFAULT '',
                    `source_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `model` varchar(40) NOT NULL DEFAULT '',
                    `prompt_tokens` int(11) unsigned NOT NULL DEFAULT '0',
                    `completion_tokens` int(11) unsigned NOT NULL DEFAULT '0',
                    `total_tokens` int(11) unsigned NOT NULL DEFAULT '0',
                    `status` varchar(20) NOT NULL DEFAULT '',
                    `error_message` varchar(500) NOT NULL DEFAULT '',
                    `is_test` tinyint(1) NOT NULL DEFAULT '0',
                    `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`log_id`),
                    KEY `idx_public_ai_openai_created` (`created_at`),
                    KEY `idx_public_ai_openai_source` (`source_type`, `source_id`, `created_at`),
                    KEY `idx_public_ai_openai_status` (`status`, `created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ", false);
        }

        if (function_exists('eottae_public_ai_upgrade_settings_openai_columns')) {
            eottae_public_ai_upgrade_settings_openai_columns();
        }
    }
}

if (!function_exists('eottae_public_ai_upgrade_settings_openai_columns')) {
    function eottae_public_ai_upgrade_settings_openai_columns()
    {
        if (!function_exists('eottae_talkroom_table_exists')) {
            return;
        }

        $table = eottae_public_ai_settings_table();
        if (!eottae_talkroom_table_exists($table)) {
            return;
        }

        $columns = array(
            'openai_enabled'            => "ADD `openai_enabled` tinyint(1) NOT NULL DEFAULT '0'",
            'openai_model'              => "ADD `openai_model` varchar(40) NOT NULL DEFAULT 'gpt-4o-mini'",
            'openai_api_key'            => "ADD `openai_api_key` varchar(255) NOT NULL DEFAULT ''",
            'openai_max_calls_per_day'  => "ADD `openai_max_calls_per_day` smallint(5) unsigned NOT NULL DEFAULT '20'",
            'openai_max_message_length' => "ADD `openai_max_message_length` smallint(5) unsigned NOT NULL DEFAULT '400'",
            'openai_fallback_template'  => "ADD `openai_fallback_template` tinyint(1) NOT NULL DEFAULT '1'",
        );

        foreach ($columns as $col => $ddl) {
            $row = sql_fetch(" SHOW COLUMNS FROM `{$table}` LIKE '{$col}' ", false);
            if (empty($row['Field'])) {
                sql_query(" ALTER TABLE `{$table}` {$ddl} ", false);
            }
        }
    }
}

if (!function_exists('eottae_public_ai_openai_bootstrap_config')) {
    function eottae_public_ai_openai_bootstrap_config()
    {
        if (!function_exists('eottae_public_ai_get_settings')) {
            include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
        }

        eottae_public_ai_openai_ensure_schema();
        $settings = eottae_public_ai_get_settings();

        return array(
            'enabled'              => !empty($settings['openai_enabled']),
            'model'                => $settings['openai_model'] ?? 'gpt-4o-mini',
            'max_calls_per_day'    => (int) ($settings['openai_max_calls_per_day'] ?? 20),
            'max_message_length'   => (int) ($settings['openai_max_message_length'] ?? 400),
            'fallback_template'    => !empty($settings['openai_fallback_template']),
            'api_key_configured'   => eottae_public_ai_openai_api_key_configured(),
            'api_key_source'       => eottae_public_ai_openai_api_key_source(),
        );
    }
}

if (!function_exists('eottae_public_ai_openai_read_db_api_key')) {
    function eottae_public_ai_openai_read_db_api_key()
    {
        if (!function_exists('eottae_public_ai_settings_table')) {
            include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
        }
        eottae_public_ai_openai_ensure_schema();
        $table = eottae_public_ai_settings_table();
        $row = sql_fetch(" SELECT openai_api_key FROM `{$table}` WHERE id = 1 LIMIT 1 ", false);

        return trim((string) ($row['openai_api_key'] ?? ''));
    }
}

if (!function_exists('eottae_public_ai_openai_api_key_source')) {
    function eottae_public_ai_openai_api_key_source()
    {
        $env = getenv('PUBLIC_AI_OPENAI_API_KEY');
        if (is_string($env) && trim($env) !== '') {
            return 'env';
        }

        if (!function_exists('g5site_cfg') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }
        if (function_exists('g5site_cfg')) {
            $cfg = trim((string) g5site_cfg('public_ai_openai_api_key', ''));
            if ($cfg === '') {
                $cfg = trim((string) g5site_cfg('openai_api_key', ''));
            }
            if ($cfg !== '') {
                return 'site_config';
            }
        }

        if (eottae_public_ai_openai_read_db_api_key() !== '') {
            return 'database';
        }

        return '';
    }
}

if (!function_exists('eottae_public_ai_openai_resolve_api_key')) {
    function eottae_public_ai_openai_resolve_api_key()
    {
        $env = getenv('PUBLIC_AI_OPENAI_API_KEY');
        if (is_string($env) && trim($env) !== '') {
            return trim($env);
        }

        if (!function_exists('g5site_cfg') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }
        if (function_exists('g5site_cfg')) {
            $key = trim((string) g5site_cfg('public_ai_openai_api_key', ''));
            if ($key === '') {
                $key = trim((string) g5site_cfg('openai_api_key', ''));
            }
            if ($key !== '') {
                return $key;
            }
        }

        return eottae_public_ai_openai_read_db_api_key();
    }
}

if (!function_exists('eottae_public_ai_openai_api_key_configured')) {
    function eottae_public_ai_openai_api_key_configured()
    {
        return eottae_public_ai_openai_resolve_api_key() !== '';
    }
}

if (!function_exists('eottae_public_ai_openai_mask_api_key')) {
    function eottae_public_ai_openai_mask_api_key($key)
    {
        $key = trim((string) $key);
        if ($key === '') {
            return '';
        }
        if (strlen($key) <= 8) {
            return '********';
        }

        return substr($key, 0, 4).'…'.substr($key, -4);
    }
}

if (!function_exists('eottae_public_ai_openai_count_calls_today')) {
    function eottae_public_ai_openai_count_calls_today($success_only = true)
    {
        eottae_public_ai_openai_ensure_schema();
        $table = eottae_public_ai_openai_logs_table();
        $day_start = eottae_public_ai_day_start_datetime(eottae_public_ai_today_ymd());
        $where = " created_at >= '".sql_escape_string($day_start)."' AND is_test = 0 ";
        if ($success_only) {
            $where .= " AND status = 'success' ";
        }

        $row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$table}` WHERE {$where} ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_public_ai_openai_source_called_today')) {
    function eottae_public_ai_openai_source_called_today($source_type, $source_id, $now = null)
    {
        eottae_public_ai_openai_ensure_schema();
        $source_type = trim((string) $source_type);
        $source_id = max(0, (int) $source_id);
        if ($source_type === '' || $source_id < 1) {
            return false;
        }

        $table = eottae_public_ai_openai_logs_table();
        $day_start = eottae_public_ai_day_start_datetime(eottae_public_ai_today_ymd($now));
        $row = sql_fetch("
            SELECT log_id
            FROM `{$table}`
            WHERE source_type = '".sql_escape_string($source_type)."'
              AND source_id = '{$source_id}'
              AND status = 'success'
              AND is_test = 0
              AND created_at >= '".sql_escape_string($day_start)."'
            LIMIT 1
        ", false);

        return !empty($row['log_id']);
    }
}

if (!function_exists('eottae_public_ai_openai_insert_log')) {
    function eottae_public_ai_openai_insert_log(array $data)
    {
        eottae_public_ai_openai_ensure_schema();
        $table = eottae_public_ai_openai_logs_table();
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');

        $ok = (bool) sql_query("
            INSERT INTO `{$table}` SET
                prompt_hash = '".sql_escape_string((string) ($data['prompt_hash'] ?? ''))."',
                trigger_type = '".sql_escape_string((string) ($data['trigger_type'] ?? ''))."',
                source_type = '".sql_escape_string((string) ($data['source_type'] ?? ''))."',
                source_id = '".max(0, (int) ($data['source_id'] ?? 0))."',
                model = '".sql_escape_string((string) ($data['model'] ?? ''))."',
                prompt_tokens = '".max(0, (int) ($data['prompt_tokens'] ?? 0))."',
                completion_tokens = '".max(0, (int) ($data['completion_tokens'] ?? 0))."',
                total_tokens = '".max(0, (int) ($data['total_tokens'] ?? 0))."',
                status = '".sql_escape_string((string) ($data['status'] ?? 'failed'))."',
                error_message = '".sql_escape_string((string) ($data['error_message'] ?? ''))."',
                is_test = '".(!empty($data['is_test']) ? 1 : 0)."',
                created_at = '{$now}'
        ", false);

        return array(
            'ok'     => $ok,
            'log_id' => (int) sql_insert_id(),
        );
    }
}

if (!function_exists('eottae_public_ai_openai_can_call')) {
    /**
     * @return array{ok:bool, reason:string}
     */
    function eottae_public_ai_openai_can_call(array $source_data, array $settings = array(), array $options = array())
    {
        if (!function_exists('eottae_public_ai_get_settings')) {
            include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
        }
        if (!$settings) {
            $settings = eottae_public_ai_get_settings();
        }

        $force = !empty($options['force_test']);
        if (empty($settings['openai_enabled']) && !$force) {
            return array('ok' => false, 'reason' => 'openai_disabled');
        }

        if (!eottae_public_ai_openai_api_key_configured()) {
            return array('ok' => false, 'reason' => 'no_api_key');
        }

        if (!function_exists('curl_init')) {
            return array('ok' => false, 'reason' => 'no_curl');
        }

        $max_calls = max(1, (int) ($settings['openai_max_calls_per_day'] ?? 20));
        if (!$force && eottae_public_ai_openai_count_calls_today(true) >= $max_calls) {
            return array('ok' => false, 'reason' => 'daily_api_limit');
        }

        $source_type = trim((string) ($source_data['source_type'] ?? ''));
        $source_id = max(0, (int) ($source_data['source_id'] ?? 0));
        if (!$force && $source_id > 0 && eottae_public_ai_openai_source_called_today($source_type, $source_id)) {
            return array('ok' => false, 'reason' => 'duplicate_source_api_today');
        }

        $trigger = trim((string) ($source_data['trigger_type'] ?? ''));
        if ($trigger === 'external_news' && empty($options['allow_external_news'])) {
            return array('ok' => false, 'reason' => 'external_news_skip_openai');
        }

        return array('ok' => true, 'reason' => 'eligible');
    }
}

if (!function_exists('build_public_ai_prompt')) {
    function build_public_ai_prompt(array $source_data, $trigger_type, array $settings = array())
    {
        return eottae_public_ai_build_prompt($source_data, $trigger_type, $settings);
    }
}

if (!function_exists('eottae_public_ai_build_prompt')) {
    function eottae_public_ai_build_prompt(array $source_data, $trigger_type, array $settings = array())
    {
        if (!$settings && function_exists('eottae_public_ai_get_settings')) {
            $settings = eottae_public_ai_get_settings();
        }

        $ai_name = trim((string) ($settings['ai_name'] ?? '어때봇'));
        $persona = trim((string) ($settings['ai_persona'] ?? '세부어때 공개톡 분위기 메이커'));
        $max_len = max(80, (int) ($settings['openai_max_message_length'] ?? 400));

        $rules = "규칙:\n"
            ."- AI 이름: {$ai_name}\n"
            ."- 역할: {$persona}\n"
            ."- 말투: 친근하고 짧게 (2~4문장, 메시지 {$max_len}자 이내)\n"
            ."- 목적: 회원이 댓글을 남기고 싶게 질문형으로 마무리\n"
            ."- 금지: 단정적 의료·법률·투자 조언, 정치·종교·분쟁, 광고처럼 보이는 문장\n"
            ."- 권장: 선택지 제시, 세부·막탄 등 지역명 자연스럽게\n"
            ."- 권장: 확인이 필요한 정보는 '확인해보세요' 형태\n"
            ."- 출력은 반드시 JSON만: {\"title\":\"\",\"message\":\"\",\"action_label\":\"\",\"action_url\":\"\"}\n";

        $context_lines = array();
        if (!empty($source_data['context']) && is_array($source_data['context'])) {
            foreach ($source_data['context'] as $k => $v) {
                if (is_scalar($v) && trim((string) $v) !== '') {
                    $context_lines[] = $k.': '.trim((string) $v);
                }
            }
        }
        if (!empty($source_data['template']['message'])) {
            $context_lines[] = '템플릿 참고(그대로 복사 금지): '.trim((string) $source_data['template']['message']);
        }

        $task = '아래 정보를 참고해 홈 공개톡에 올릴 짧은 대화 유도 메시지를 만들어주세요.';
        switch ($trigger_type) {
            case 'calendar_today':
            case 'calendar_tomorrow':
            case 'calendar_day_after':
                $task = '아래 세부어때 캘린더 일정을 참고해서 홈 공개톡에 올릴 짧은 대화 유도 메시지를 만들어주세요. 회원들이 댓글을 남기고 싶게 질문형으로 끝내주세요.';
                break;
            case 'holiday':
                $task = '아래 공휴일 정보를 참고해 세부에 계신 분들의 주말·휴일 계획을 묻는 공개톡 메시지를 만들어주세요. 선택지를 자연스럽게 넣어주세요.';
                break;
            case 'weather':
                $task = '아래 세부 날씨 정보를 참고해서 공개톡에 올릴 생활형 안내 메시지를 만들어주세요. 날씨는 틀릴 수 있으니 단정하지 말고 부드럽게 표현해주세요.';
                break;
            case 'business_event':
                $task = '아래 업체 이벤트 정보를 참고해서 광고처럼 보이지 않게 자연스러운 대화 소재로 만들어주세요.';
                break;
            case 'external_news':
                $task = '아래 지역 소식 요약을 참고해 뉴스 본문을 복사하지 말고, 공식 안내 확인을 권하는 짧은 대화 메시지를 만들어주세요.';
                break;
            case 'popular_post':
                $task = '아래 인기 게시글 주제를 참고해 경험담·의견을 나누고 싶게 만드는 공개톡 메시지를 만들어주세요.';
                break;
            case 'talk_room_activity':
                $task = '아래 세부톡방 활동 정보를 참고해 관심 있는 분들이 참여하고 싶게 만드는 메시지를 만들어주세요.';
                break;
            case 'quiet_chat':
                $task = '공개톡이 조용할 때 분위기를 살리는 가벼운 질문 메시지를 만들어주세요. 맛집·생활·여행 등 세부 관련 주제로.';
                break;
        }

        return $task."\n\n".$rules."\n참고 정보:\n".($context_lines ? implode("\n", $context_lines) : '(없음)');
    }
}

if (!function_exists('eottae_public_ai_openai_parse_response')) {
    function eottae_public_ai_openai_parse_response($content, array $settings = array())
    {
        $max_len = max(80, (int) ($settings['openai_max_message_length'] ?? 400));
        $content = trim((string) $content);
        if ($content === '') {
            return null;
        }

        if (preg_match('/\{[\s\S]*\}/u', $content, $m)) {
            $content = $m[0];
        }

        $parsed = json_decode($content, true);
        if (!is_array($parsed)) {
            $lines = preg_split('/\r\n|\r|\n/u', $content);
            $message = trim(implode("\n", array_slice($lines, 0, 6)));

            return array(
                'title'        => '',
                'message'      => function_exists('cut_str') ? cut_str($message, $max_len, '…') : substr($message, 0, $max_len),
                'action_label' => '',
                'action_url'   => '',
            );
        }

        $message = trim(strip_tags((string) ($parsed['message'] ?? '')));
        if ($message === '' && !empty($parsed['content'])) {
            $message = trim(strip_tags((string) $parsed['content']));
        }
        if ($message === '') {
            return null;
        }

        if (function_exists('mb_strlen') && mb_strlen($message, 'UTF-8') > $max_len) {
            $message = mb_substr($message, 0, $max_len, 'UTF-8').'…';
        }

        $action_url = trim((string) ($parsed['action_url'] ?? ''));
        if ($action_url !== '' && !preg_match('#^https?://#i', $action_url)) {
            $action_url = '';
        }

        return array(
            'title'        => trim(strip_tags((string) ($parsed['title'] ?? ''))),
            'message'      => $message,
            'action_label' => trim(strip_tags((string) ($parsed['action_label'] ?? ''))),
            'action_url'   => $action_url,
        );
    }
}

if (!function_exists('call_openai_for_public_message')) {
    /**
     * @return array{ok:bool, parsed:array|null, usage:array, error:string, raw:string}
     */
    function call_openai_for_public_message($prompt, array $options = array())
    {
        return eottae_public_ai_call_openai($prompt, $options);
    }
}

if (!function_exists('eottae_public_ai_call_openai')) {
    function eottae_public_ai_call_openai($prompt, array $options = array())
    {
        $settings = isset($options['settings']) && is_array($options['settings'])
            ? $options['settings']
            : eottae_public_ai_get_settings();

        $api_key = eottae_public_ai_openai_resolve_api_key();
        if ($api_key === '') {
            return array('ok' => false, 'parsed' => null, 'usage' => array(), 'error' => 'no_api_key', 'raw' => '');
        }

        $model = trim((string) ($options['model'] ?? $settings['openai_model'] ?? 'gpt-4o-mini'));
        if ($model === '') {
            $model = 'gpt-4o-mini';
        }

        $payload = array(
            'model'    => $model,
            'messages' => array(
                array(
                    'role'    => 'system',
                    'content' => 'You write Korean public group chat messages for Cebu community site 세부어때. Return strict JSON only with keys title, message, action_label, action_url.',
                ),
                array('role' => 'user', 'content' => (string) $prompt),
            ),
            'temperature' => 0.7,
            'max_tokens'  => 500,
            'response_format' => array('type' => 'json_object'),
        );

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$api_key,
            ),
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT    => 35,
        ));

        $raw = curl_exec($ch);
        $curl_err = curl_error($ch);
        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $raw === '') {
            return array(
                'ok'     => false,
                'parsed' => null,
                'usage'  => array(),
                'error'  => $curl_err !== '' ? $curl_err : 'empty_response',
                'raw'    => '',
                'model'  => $model,
            );
        }

        $decoded = json_decode($raw, true);
        if ($http_code < 200 || $http_code >= 300) {
            $err = isset($decoded['error']['message']) ? (string) $decoded['error']['message'] : 'http_'.$http_code;

            return array(
                'ok'     => false,
                'parsed' => null,
                'usage'  => array(),
                'error'  => $err,
                'raw'    => '',
                'model'  => $model,
            );
        }

        $content = isset($decoded['choices'][0]['message']['content'])
            ? trim((string) $decoded['choices'][0]['message']['content'])
            : '';
        $usage = isset($decoded['usage']) && is_array($decoded['usage']) ? $decoded['usage'] : array();
        $parsed = eottae_public_ai_openai_parse_response($content, $settings);

        if (!$parsed || trim($parsed['message']) === '') {
            return array(
                'ok'     => false,
                'parsed' => null,
                'usage'  => $usage,
                'error'  => 'parse_failed',
                'raw'    => $content,
                'model'  => $model,
            );
        }

        return array(
            'ok'     => true,
            'parsed' => $parsed,
            'usage'  => $usage,
            'error'  => '',
            'raw'    => $content,
            'model'  => $model,
        );
    }
}

if (!function_exists('generate_public_ai_message')) {
    /**
     * @param array<string, mixed> $source_data
     * @param array<string, mixed>|null $template_candidate
     * @return array{candidate:array, source:string, error:string}
     */
    function generate_public_ai_message(array $source_data, $trigger_type, $template_candidate = null, array $settings = array())
    {
        return eottae_public_ai_generate_message($source_data, $trigger_type, $template_candidate, $settings);
    }
}

if (!function_exists('eottae_public_ai_generate_message')) {
    function eottae_public_ai_generate_message(array $source_data, $trigger_type, $template_candidate = null, array $settings = array(), array $options = array())
    {
        if (!function_exists('eottae_public_ai_get_settings')) {
            include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
        }
        if (!function_exists('eottae_public_ai_guard_apply_to_candidate')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-guard.lib.php';
        }

        if (!$settings) {
            $settings = eottae_public_ai_get_settings();
        }

        $trigger_type = trim((string) $trigger_type);
        $template = is_array($template_candidate) ? $template_candidate : array();

        if (!isset($source_data['trigger_type']) || $source_data['trigger_type'] === '') {
            $source_data['trigger_type'] = $trigger_type;
        }
        if (!isset($source_data['source_type']) && !empty($template['source_type'])) {
            $source_data['source_type'] = $template['source_type'];
        }
        if (!isset($source_data['source_id']) && !empty($template['source_id'])) {
            $source_data['source_id'] = (int) $template['source_id'];
        }
        if (empty($source_data['template']) && $template) {
            $source_data['template'] = $template;
        }

        $can = eottae_public_ai_openai_can_call($source_data, $settings, $options);
        if (empty($can['ok'])) {
            if (!empty($settings['openai_fallback_template']) && $template) {
                return array('candidate' => $template, 'source' => 'template', 'error' => (string) ($can['reason'] ?? ''));
            }

            return array('candidate' => null, 'source' => 'none', 'error' => (string) ($can['reason'] ?? ''));
        }

        $prompt = eottae_public_ai_build_prompt($source_data, $trigger_type, $settings);
        $prompt_hash = sha1($prompt);
        $call = eottae_public_ai_call_openai($prompt, array('settings' => $settings));

        $usage = isset($call['usage']) && is_array($call['usage']) ? $call['usage'] : array();
        eottae_public_ai_openai_insert_log(array(
            'prompt_hash'       => $prompt_hash,
            'trigger_type'      => $trigger_type,
            'source_type'       => (string) ($source_data['source_type'] ?? ''),
            'source_id'         => (int) ($source_data['source_id'] ?? 0),
            'model'             => (string) ($call['model'] ?? ''),
            'prompt_tokens'     => (int) ($usage['prompt_tokens'] ?? 0),
            'completion_tokens' => (int) ($usage['completion_tokens'] ?? 0),
            'total_tokens'      => (int) ($usage['total_tokens'] ?? 0),
            'status'            => !empty($call['ok']) ? 'success' : 'failed',
            'error_message'     => (string) ($call['error'] ?? ''),
            'is_test'           => !empty($options['force_test']) ? 1 : 0,
        ));

        if (empty($call['ok']) || empty($call['parsed'])) {
            if (!empty($settings['openai_fallback_template']) && $template) {
                return array('candidate' => $template, 'source' => 'template_fallback', 'error' => (string) ($call['error'] ?? ''));
            }

            return array('candidate' => null, 'source' => 'failed', 'error' => (string) ($call['error'] ?? ''));
        }

        $parsed = $call['parsed'];
        $candidate = array_merge($template, array(
            'trigger_type' => $trigger_type,
            'title'        => $parsed['title'] !== '' ? $parsed['title'] : ($template['title'] ?? ''),
            'message'      => $parsed['message'],
            'action_label' => $parsed['action_label'] !== '' ? $parsed['action_label'] : ($template['action_label'] ?? ''),
            'action_url'   => $parsed['action_url'] !== '' ? $parsed['action_url'] : ($template['action_url'] ?? ''),
            'admin_memo'   => trim((string) ($template['admin_memo'] ?? '')).'|openai',
        ));

        $candidate = eottae_public_ai_guard_apply_to_candidate($candidate);

        return array(
            'candidate' => $candidate,
            'source'    => 'openai',
            'error'     => '',
        );
    }
}

if (!function_exists('eottae_public_ai_generator_enhance_candidate')) {
    /**
     * 템플릿 후보를 OpenAI로 개선 (실패 시 템플릿 유지)
     *
     * @return array<string, mixed>
     */
    function eottae_public_ai_generator_enhance_candidate(array $template_candidate, array $options = array())
    {
        if (!function_exists('eottae_public_ai_get_settings')) {
            include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
        }

        $settings = eottae_public_ai_get_settings();
        $trigger_type = trim((string) ($template_candidate['trigger_type'] ?? ''));

        $source_data = array(
            'trigger_type' => $trigger_type,
            'source_type'  => trim((string) ($template_candidate['source_type'] ?? '')),
            'source_id'    => (int) ($template_candidate['source_id'] ?? 0),
            'template'     => $template_candidate,
            'context'      => isset($options['context']) && is_array($options['context']) ? $options['context'] : array(),
        );

        if ($trigger_type === 'external_news') {
            $options['allow_external_news'] = false;

            return $template_candidate;
        }

        $gen = eottae_public_ai_generate_message($source_data, $trigger_type, $template_candidate, $settings, $options);
        if (!empty($gen['candidate']) && is_array($gen['candidate'])) {
            return $gen['candidate'];
        }

        return $template_candidate;
    }
}

if (!function_exists('eottae_public_ai_openai_build_context_from_candidate')) {
    function eottae_public_ai_openai_build_context_from_candidate(array $candidate)
    {
        $ctx = array();
        if (!empty($candidate['title'])) {
            $ctx['제목'] = $candidate['title'];
        }
        if (!empty($candidate['message'])) {
            $ctx['템플릿 메시지'] = $candidate['message'];
        }
        if (!empty($candidate['admin_memo'])) {
            $ctx['메모'] = $candidate['admin_memo'];
        }

        return $ctx;
    }
}

if (!function_exists('eottae_public_ai_openai_admin_list_logs')) {
    function eottae_public_ai_openai_admin_list_logs($limit = 50)
    {
        eottae_public_ai_openai_ensure_schema();
        $table = eottae_public_ai_openai_logs_table();
        $limit = max(1, min(200, (int) $limit));
        $rows = array();
        $result = sql_query("
            SELECT *
            FROM `{$table}`
            ORDER BY log_id DESC
            LIMIT {$limit}
        ", false);
        while ($result && ($row = sql_fetch_array($result))) {
            $rows[] = array(
                'log_id'            => (int) ($row['log_id'] ?? 0),
                'prompt_hash'       => substr((string) ($row['prompt_hash'] ?? ''), 0, 12),
                'trigger_type'      => (string) ($row['trigger_type'] ?? ''),
                'source_type'       => (string) ($row['source_type'] ?? ''),
                'source_id'         => (int) ($row['source_id'] ?? 0),
                'model'             => (string) ($row['model'] ?? ''),
                'total_tokens'      => (int) ($row['total_tokens'] ?? 0),
                'status'            => (string) ($row['status'] ?? ''),
                'error_message'     => get_text((string) ($row['error_message'] ?? '')),
                'is_test'           => (int) !empty($row['is_test']),
                'created_at'        => (string) ($row['created_at'] ?? ''),
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_public_ai_openai_admin_test_fixture')) {
    /**
     * 관리자 OpenAI 테스트용 소스·템플릿 후보
     *
     * @return array{ok:bool, message:string, trigger_type:string, source_data:array, template:array}
     */
    function eottae_public_ai_openai_admin_test_fixture($test_type, $custom_text = '')
    {
        if (!function_exists('eottae_public_ai_generator_load_dependencies')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-generator.lib.php';
        }
        eottae_public_ai_generator_load_dependencies();

        $settings = eottae_public_ai_get_settings();
        $test_type = trim((string) $test_type);
        $custom_text = trim(strip_tags((string) $custom_text));
        if (function_exists('mb_substr') && mb_strlen($custom_text, 'UTF-8') > 800) {
            $custom_text = mb_substr($custom_text, 0, 800, 'UTF-8');
        }

        if ($test_type === 'custom') {
            if ($custom_text === '') {
                return array('ok' => false, 'message' => '직접 입력 내용을 적어주세요.', 'trigger_type' => '', 'source_data' => array(), 'template' => array());
            }
            $template = array(
                'trigger_type' => 'quiet_chat',
                'source_type'  => 'manual_test',
                'source_id'    => 0,
                'title'        => '',
                'message'      => '세부 생활 이야기 나눠볼까요?'."\n".$custom_text,
                'action_label' => '',
                'action_url'   => '',
                'admin_memo'   => 'admin_test:custom',
            );

            return array(
                'ok'            => true,
                'message'       => '',
                'trigger_type'  => 'quiet_chat',
                'source_data'   => array(
                    'trigger_type' => 'quiet_chat',
                    'source_type'  => 'manual_test',
                    'source_id'    => 0,
                    'context'      => array('관리자 입력' => $custom_text),
                    'template'     => $template,
                ),
                'template'      => $template,
            );
        }

        $sources = eottae_public_ai_collect_sources($settings);
        $candidates = array();

        if ($test_type === 'calendar') {
            $candidates = eottae_public_ai_generator_build_calendar_candidates($sources);
            if (!$candidates) {
                $candidates = eottae_public_ai_generator_build_holiday_candidates($sources);
            }
        } elseif ($test_type === 'weather') {
            if (is_file(G5_LIB_PATH.'/eottae-public-ai-weather.lib.php')) {
                include_once G5_LIB_PATH.'/eottae-public-ai-weather.lib.php';
            }
            $today = substr((string) ($sources['today'] ?? date('Y-m-d')), 0, 10);
            if (function_exists('eottae_public_ai_weather_get_for_date')) {
                $row = eottae_public_ai_weather_get_for_date($today);
                if ($row) {
                    $sources['weather'][$today] = $row;
                }
            }
            if (function_exists('eottae_public_ai_generator_build_weather_candidates')) {
                $candidates = eottae_public_ai_generator_build_weather_candidates($sources);
            }
        } elseif ($test_type === 'popular') {
            $candidates = eottae_public_ai_generator_build_popular_candidates($sources);
        } else {
            return array('ok' => false, 'message' => '지원하지 않는 테스트 유형입니다.', 'trigger_type' => '', 'source_data' => array(), 'template' => array());
        }

        if (!$candidates) {
            $hints = array(
                'calendar' => '캘린더 일정 또는 공휴일 데이터가 없습니다.',
                'weather'  => '오늘 날씨 데이터가 없습니다. 날씨 데이터 메뉴에서 등록해 주세요.',
                'popular'  => '인기 게시글 데이터가 없습니다.',
            );

            return array(
                'ok'           => false,
                'message'      => $hints[$test_type] ?? '테스트용 데이터가 없습니다.',
                'trigger_type' => '',
                'source_data'  => array(),
                'template'     => array(),
            );
        }

        $template = $candidates[0];
        $trigger_type = trim((string) ($template['trigger_type'] ?? 'quiet_chat'));
        $source_data = array(
            'trigger_type' => $trigger_type,
            'source_type'  => trim((string) ($template['source_type'] ?? '')),
            'source_id'    => (int) ($template['source_id'] ?? 0),
            'context'      => eottae_public_ai_openai_build_context_from_candidate($template),
            'template'     => $template,
        );

        return array(
            'ok'           => true,
            'message'      => '',
            'trigger_type' => $trigger_type,
            'source_data'  => $source_data,
            'template'     => $template,
        );
    }
}
