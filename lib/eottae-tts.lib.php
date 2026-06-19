<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_tts_table_exists')) {
    function eottae_tts_table_exists($table)
    {
        $table = preg_replace('/[^a-z0-9_]/i', '', (string) $table);
        if ($table === '') {
            return false;
        }
        $row = sql_fetch(" SHOW TABLES LIKE '".sql_escape_string($table)."' ", false);

        return !empty($row);
    }
}

if (!function_exists('eottae_tts_settings_table')) {
    function eottae_tts_settings_table()
    {
        global $g5;
        if (!isset($g5['eottae_tts_settings_table'])) {
            $g5['eottae_tts_settings_table'] = G5_TABLE_PREFIX.'eottae_tts_settings';
        }

        return $g5['eottae_tts_settings_table'];
    }
}

if (!function_exists('eottae_tts_audio_table')) {
    function eottae_tts_audio_table()
    {
        global $g5;
        if (!isset($g5['eottae_tts_audio_table'])) {
            $g5['eottae_tts_audio_table'] = G5_TABLE_PREFIX.'eottae_tts_audio';
        }

        return $g5['eottae_tts_audio_table'];
    }
}

if (!function_exists('eottae_tts_voice_options')) {
    function eottae_tts_voice_options()
    {
        return array(
            'alloy' => 'Alloy - 균형 잡힌 기본 음성',
            'echo' => 'Echo - 또렷한 남성형 음성',
            'fable' => 'Fable - 부드러운 스토리텔링',
            'nova' => 'Nova - 밝고 자연스러운 여성형 음성',
            'onyx' => 'Onyx - 낮고 안정적인 음성',
            'shimmer' => 'Shimmer - 선명하고 가벼운 음성',
        );
    }
}

if (!function_exists('eottae_tts_ensure_schema')) {
    function eottae_tts_ensure_schema()
    {
        $settings_table = eottae_tts_settings_table();
        sql_query(" CREATE TABLE IF NOT EXISTS `{$settings_table}` (
            `id` tinyint(1) unsigned NOT NULL DEFAULT '1',
            `enabled` tinyint(1) NOT NULL DEFAULT '1',
            `model` varchar(40) NOT NULL DEFAULT 'tts-1',
            `voice` varchar(30) NOT NULL DEFAULT 'nova',
            `speed` decimal(4,2) NOT NULL DEFAULT '1.00',
            `board_list` text NOT NULL,
            `max_chars` smallint(5) unsigned NOT NULL DEFAULT '4000',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ", false);

        $audio_table = eottae_tts_audio_table();
        sql_query(" CREATE TABLE IF NOT EXISTS `{$audio_table}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `bo_table` varchar(30) NOT NULL DEFAULT '',
            `wr_id` int(11) unsigned NOT NULL DEFAULT '0',
            `content_hash` char(40) NOT NULL DEFAULT '',
            `model` varchar(40) NOT NULL DEFAULT '',
            `voice` varchar(30) NOT NULL DEFAULT '',
            `speed` decimal(4,2) NOT NULL DEFAULT '1.00',
            `file_path` varchar(500) NOT NULL DEFAULT '',
            `file_url` varchar(500) NOT NULL DEFAULT '',
            `status` varchar(20) NOT NULL DEFAULT 'ready',
            `error_message` varchar(500) NOT NULL DEFAULT '',
            `generated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_tts_audio` (`bo_table`, `wr_id`, `content_hash`, `model`, `voice`, `speed`),
            KEY `idx_post` (`bo_table`, `wr_id`),
            KEY `idx_generated_at` (`generated_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ", false);

        $row = sql_fetch(" SELECT id FROM `{$settings_table}` WHERE id = 1 ", false);
        if (empty($row['id'])) {
            $default_boards = array();
            foreach (eottae_tts_board_options() as $board) {
                $default_boards[] = $board['bo_table'];
            }
            sql_query(" INSERT INTO `{$settings_table}` SET
                id = 1,
                enabled = '1',
                model = 'tts-1',
                voice = 'nova',
                speed = '1.00',
                board_list = '".sql_escape_string(implode(',', $default_boards))."',
                max_chars = '4000',
                updated_at = '".G5_TIME_YMDHIS."' ", false);
        }
    }
}

if (!function_exists('eottae_tts_board_options')) {
    function eottae_tts_board_options()
    {
        global $g5;
        $boards = array();
        if (empty($g5['board_table']) || !eottae_tts_table_exists($g5['board_table'])) {
            return $boards;
        }

        $result = sql_query(" SELECT bo_table, bo_subject FROM `{$g5['board_table']}` ORDER BY gr_id, bo_order, bo_table ", false);
        while ($row = sql_fetch_array($result)) {
            $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) ($row['bo_table'] ?? ''));
            if ($bo_table === '') {
                continue;
            }
            $boards[] = array(
                'bo_table' => $bo_table,
                'label' => get_text($row['bo_subject'] ?? $bo_table),
            );
        }

        return $boards;
    }
}

if (!function_exists('eottae_tts_get_settings')) {
    function eottae_tts_get_settings()
    {
        eottae_tts_ensure_schema();
        $table = eottae_tts_settings_table();
        $row = sql_fetch(" SELECT * FROM `{$table}` WHERE id = 1 ", false);
        $boards = array_filter(array_map('trim', explode(',', (string) ($row['board_list'] ?? ''))));
        $available_boards = array();
        foreach (eottae_tts_board_options() as $board) {
            if (!empty($board['bo_table'])) {
                $available_boards[] = $board['bo_table'];
            }
        }
        $column_bo_table = function_exists('eottae_column_board_table') ? eottae_column_board_table() : 'column';
        if (
            $column_bo_table !== ''
            && in_array($column_bo_table, $available_boards, true)
            && !in_array($column_bo_table, $boards, true)
            && count($boards) >= max(1, count($available_boards) - 1)
        ) {
            $boards[] = $column_bo_table;
        }
        $voice = (string) ($row['voice'] ?? 'nova');
        if (!isset(eottae_tts_voice_options()[$voice])) {
            $voice = 'nova';
        }

        return array(
            'enabled' => !empty($row['enabled']),
            'model' => trim((string) ($row['model'] ?? 'tts-1')) ?: 'tts-1',
            'voice' => $voice,
            'speed' => max(0.25, min(4.0, (float) ($row['speed'] ?? 1.0))),
            'board_list' => $boards,
            'max_chars' => max(500, min(4000, (int) ($row['max_chars'] ?? 4000))),
        );
    }
}

if (!function_exists('eottae_tts_save_settings')) {
    function eottae_tts_save_settings(array $data)
    {
        eottae_tts_ensure_schema();
        $voices = eottae_tts_voice_options();
        $enabled = !empty($data['enabled']) ? 1 : 0;
        $model = preg_replace('/[^a-z0-9_.-]/i', '', (string) ($data['model'] ?? 'tts-1'));
        if ($model === '') {
            $model = 'tts-1';
        }
        $voice = preg_replace('/[^a-z0-9_-]/i', '', (string) ($data['voice'] ?? 'nova'));
        if (!isset($voices[$voice])) {
            $voice = 'nova';
        }
        $speed = max(0.25, min(4.0, (float) ($data['speed'] ?? 1.0)));
        $max_chars = max(500, min(4000, (int) ($data['max_chars'] ?? 4000)));
        $boards = array();
        if (!empty($data['boards']) && is_array($data['boards'])) {
            foreach ($data['boards'] as $bo_table) {
                $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
                if ($bo_table !== '') {
                    $boards[$bo_table] = $bo_table;
                }
            }
        }

        $table = eottae_tts_settings_table();
        sql_query(" UPDATE `{$table}` SET
            enabled = '{$enabled}',
            model = '".sql_escape_string($model)."',
            voice = '".sql_escape_string($voice)."',
            speed = '".sql_escape_string(number_format($speed, 2, '.', ''))."',
            board_list = '".sql_escape_string(implode(',', array_values($boards)))."',
            max_chars = '{$max_chars}',
            updated_at = '".G5_TIME_YMDHIS."'
            WHERE id = 1 ", false);

        return array('ok' => true, 'message' => '음성 읽기 설정을 저장했습니다.');
    }
}

if (!function_exists('eottae_tts_board_enabled')) {
    function eottae_tts_board_enabled($bo_table, $settings = null)
    {
        if (!is_array($settings)) {
            $settings = null;
        }
        $settings = $settings ?: eottae_tts_get_settings();
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);

        return !empty($settings['enabled'])
            && $bo_table !== ''
            && in_array($bo_table, $settings['board_list'], true);
    }
}

if (!function_exists('eottae_tts_api_key')) {
    function eottae_tts_mask_api_key($key)
    {
        $key = trim((string) $key);
        if ($key === '') {
            return '';
        }
        if (strlen($key) <= 10) {
            return '********';
        }

        return substr($key, 0, 7).'…'.substr($key, -4);
    }

    function eottae_tts_api_key_info()
    {
        if (!function_exists('eottae_secrets_load') && is_file(G5_LIB_PATH.'/eottae-secrets.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-secrets.lib.php';
        }
        if (function_exists('eottae_secrets_load')) {
            eottae_secrets_load();
        }

        $sources = array(
            'tts_openai_api_key' => 'TTS 전용 키',
            'openai_api_key' => '공통 OpenAI 키',
            'ai_generate_api_key' => 'AI 자동생성 키',
        );
        if (function_exists('g5site_cfg')) {
            foreach ($sources as $key_name => $label) {
                $value = trim((string) g5site_cfg($key_name, ''));
                if ($value !== '') {
                    return array(
                        'key' => $value,
                        'source' => $key_name,
                        'source_label' => $label,
                        'masked' => eottae_tts_mask_api_key($value),
                    );
                }
            }
        }

        foreach (array('OPENAI_API_KEY', 'EOTTAE_OPENAI_API_KEY') as $env_key) {
            $env_val = getenv($env_key);
            if ($env_val !== false && trim((string) $env_val) !== '') {
                $value = trim((string) $env_val);
                return array(
                    'key' => $value,
                    'source' => $env_key,
                    'source_label' => '환경변수 '.$env_key,
                    'masked' => eottae_tts_mask_api_key($value),
                );
            }
        }

        return array(
            'key' => '',
            'source' => '',
            'source_label' => '',
            'masked' => '',
        );
    }

    function eottae_tts_api_key()
    {
        $info = eottae_tts_api_key_info();

        return (string) ($info['key'] ?? '');
    }
}

if (!function_exists('eottae_tts_extract_text')) {
    function eottae_tts_extract_text(array $write, $max_chars = 4000)
    {
        $subject = trim((string) ($write['wr_subject'] ?? ''));
        $content = (string) ($write['wr_content'] ?? '');
        $content = preg_replace('/\{이미지\:[^}]+\}/u', ' ', $content);
        $content = strip_tags($content);
        $text = html_entity_decode(trim($subject."\n\n".$content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim((string) $text);
        $max_chars = max(500, min(4000, (int) $max_chars));
        if (function_exists('mb_substr')) {
            $text = mb_substr($text, 0, $max_chars, 'UTF-8');
        } else {
            $text = substr($text, 0, $max_chars);
        }

        return trim($text);
    }
}

if (!function_exists('eottae_tts_load_write')) {
    function eottae_tts_load_write($bo_table, $wr_id)
    {
        global $g5;
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1 || empty($g5['write_prefix'])) {
            return null;
        }
        $write_table = $g5['write_prefix'].$bo_table;
        if (!eottae_tts_table_exists($write_table)) {
            return null;
        }

        return sql_fetch(" SELECT * FROM `{$write_table}` WHERE wr_id = '{$wr_id}' AND wr_is_comment = 0 ", false);
    }
}

if (!function_exists('eottae_tts_can_read_write')) {
    function eottae_tts_can_read_write($bo_table, array $write)
    {
        global $g5, $member, $is_admin;
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        if ($bo_table === '' || empty($write['wr_id']) || empty($g5['board_table'])) {
            return false;
        }

        $board = sql_fetch(" SELECT bo_read_level FROM `{$g5['board_table']}` WHERE bo_table = '".sql_escape_string($bo_table)."' ", false);
        $member_level = (int) ($member['mb_level'] ?? 1);
        if (($is_admin ?? '') !== 'super' && $member_level < (int) ($board['bo_read_level'] ?? 1)) {
            return false;
        }

        if (strpos((string) ($write['wr_option'] ?? ''), 'secret') !== false) {
            $mb_id = (string) ($member['mb_id'] ?? '');
            if (($is_admin ?? '') === 'super' || ($mb_id !== '' && $mb_id === (string) ($write['mb_id'] ?? ''))) {
                return true;
            }
            $session_key = 'ss_secret_'.$bo_table.'_'.($write['wr_num'] ?? '');

            return (bool) get_session($session_key);
        }

        return true;
    }
}

if (!function_exists('eottae_tts_audio_path_url')) {
    function eottae_tts_audio_path_url($bo_table, $wr_id, $hash)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        $hash = preg_replace('/[^a-f0-9]/i', '', (string) $hash);
        $dir = G5_DATA_PATH.'/eottae-tts/'.$bo_table;
        if (!is_dir($dir)) {
            @mkdir($dir, G5_DIR_PERMISSION, true);
        }
        @chmod($dir, G5_DIR_PERMISSION);
        $filename = $wr_id.'-'.$hash.'.mp3';

        return array(
            'path' => $dir.'/'.$filename,
            'url' => G5_DATA_URL.'/eottae-tts/'.$bo_table.'/'.$filename,
        );
    }
}

if (!function_exists('eottae_tts_openai_speech')) {
    function eottae_tts_openai_error_message($http_code, $decoded, $curl_error = '')
    {
        $http_code = (int) $http_code;
        $error = is_array($decoded) && isset($decoded['error']) && is_array($decoded['error']) ? $decoded['error'] : array();
        $type = strtolower((string) ($error['type'] ?? ''));
        $code = strtolower((string) ($error['code'] ?? ''));
        $message = strtolower((string) ($error['message'] ?? ''));

        if ($http_code === 401) {
            return 'AI 음성 API 키를 확인해 주세요.';
        }
        if ($http_code === 429 && ($code === 'insufficient_quota' || strpos($message, 'quota') !== false || strpos($message, 'billing') !== false)) {
            return 'AI 음성 생성 한도가 소진되었습니다. 관리자에게 문의해 주세요.';
        }
        if ($http_code === 429 || $type === 'rate_limit_error') {
            return 'AI 음성 요청이 많습니다. 잠시 후 다시 시도해 주세요.';
        }
        if ($http_code === 400) {
            return 'AI 음성 설정을 확인해 주세요.';
        }
        if ($http_code >= 500) {
            return 'AI 음성 서버가 일시적으로 불안정합니다. 잠시 후 다시 시도해 주세요.';
        }
        if ($curl_error !== '') {
            return 'AI 음성 서버에 연결할 수 없습니다. 잠시 후 다시 시도해 주세요.';
        }

        return 'AI 음성을 생성할 수 없습니다. 잠시 후 다시 시도해 주세요.';
    }

    function eottae_tts_openai_speech($text, $model, $voice, $speed)
    {
        $api_key = eottae_tts_api_key();
        if ($api_key === '') {
            return array('ok' => false, 'message' => 'AI API 키가 설정되지 않았습니다.');
        }
        if (!function_exists('curl_init')) {
            return array('ok' => false, 'message' => '서버 PHP cURL 확장이 필요합니다.');
        }

        if (function_exists('eottae_ai_release_session_lock')) {
            eottae_ai_release_session_lock();
        }
        @set_time_limit(90);

        $payload = array(
            'model' => $model,
            'voice' => $voice,
            'input' => $text,
            'response_format' => 'mp3',
            'speed' => (float) $speed,
        );
        $ch = curl_init('https://api.openai.com/v1/audio/speech');
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$api_key,
            ),
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 80,
        ));
        $raw = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code >= 200 && $http_code < 300 && is_string($raw) && $raw !== '') {
            return array('ok' => true, 'audio' => $raw);
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : null;

        return array('ok' => false, 'message' => eottae_tts_openai_error_message($http_code, $decoded, $curl_error));
    }
}

if (!function_exists('eottae_tts_get_or_create_audio')) {
    function eottae_tts_get_or_create_audio($bo_table, $wr_id)
    {
        $settings = eottae_tts_get_settings();
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if (!eottae_tts_board_enabled($bo_table, $settings)) {
            return array('ok' => false, 'message' => '이 게시판은 음성 읽기를 사용하지 않습니다.');
        }

        $write = eottae_tts_load_write($bo_table, $wr_id);
        if (!$write || !eottae_tts_can_read_write($bo_table, $write)) {
            return array('ok' => false, 'message' => '글을 읽을 권한이 없습니다.');
        }

        $text = eottae_tts_extract_text($write, $settings['max_chars']);
        if ($text === '') {
            return array('ok' => false, 'message' => '읽을 본문이 없습니다.');
        }

        $model = $settings['model'];
        $voice = $settings['voice'];
        $speed = number_format((float) $settings['speed'], 2, '.', '');
        $hash = sha1($text.'|'.$model.'|'.$voice.'|'.$speed);
        $audio = eottae_tts_audio_path_url($bo_table, $wr_id, $hash);
        $table = eottae_tts_audio_table();
        $cached = sql_fetch(" SELECT * FROM `{$table}`
            WHERE bo_table = '".sql_escape_string($bo_table)."'
              AND wr_id = '{$wr_id}'
              AND content_hash = '{$hash}'
              AND model = '".sql_escape_string($model)."'
              AND voice = '".sql_escape_string($voice)."'
              AND speed = '".sql_escape_string($speed)."'
            LIMIT 1 ", false);
        if (!empty($cached['file_path']) && is_file($cached['file_path'])) {
            return array('ok' => true, 'audio_url' => (string) $cached['file_url'], 'cached' => true);
        }
        if (is_file($audio['path'])) {
            return array('ok' => true, 'audio_url' => $audio['url'], 'cached' => true);
        }

        $result = eottae_tts_openai_speech($text, $model, $voice, (float) $speed);
        if (empty($result['ok'])) {
            return array('ok' => false, 'message' => (string) ($result['message'] ?? 'AI 음성 생성 실패'));
        }

        if (@file_put_contents($audio['path'], $result['audio']) === false) {
            return array('ok' => false, 'message' => '음성 파일을 저장할 수 없습니다.');
        }
        @chmod($audio['path'], G5_FILE_PERMISSION);

        sql_query(" INSERT INTO `{$table}` SET
                bo_table = '".sql_escape_string($bo_table)."',
                wr_id = '{$wr_id}',
                content_hash = '{$hash}',
                model = '".sql_escape_string($model)."',
                voice = '".sql_escape_string($voice)."',
                speed = '".sql_escape_string($speed)."',
                file_path = '".sql_escape_string($audio['path'])."',
                file_url = '".sql_escape_string($audio['url'])."',
                status = 'ready',
                generated_at = '".G5_TIME_YMDHIS."'
            ON DUPLICATE KEY UPDATE
                file_path = VALUES(file_path),
                file_url = VALUES(file_url),
                status = 'ready',
                error_message = '',
                generated_at = VALUES(generated_at) ", false);

        return array('ok' => true, 'audio_url' => $audio['url'], 'cached' => false);
    }
}

if (!function_exists('eottae_tts_estimated_minutes')) {
    function eottae_tts_estimated_minutes($text)
    {
        $len = function_exists('mb_strlen') ? mb_strlen((string) $text, 'UTF-8') : strlen((string) $text);

        return max(1, (int) ceil($len / 650));
    }
}

if (!function_exists('eottae_tts_render_board_player')) {
    function eottae_tts_render_board_player()
    {
        global $bo_table, $wr_id, $write, $view, $is_admin;
        if (defined('G5_IS_ADMIN') || empty($bo_table) || empty($wr_id)) {
            return;
        }

        $settings = eottae_tts_get_settings();
        if (!eottae_tts_board_enabled($bo_table, $settings)) {
            return;
        }

        $row = is_array($write ?? null) && !empty($write['wr_id']) ? $write : (is_array($view ?? null) ? $view : array());
        if (empty($row['wr_id'])) {
            return;
        }
        $plain = eottae_tts_extract_text($row, $settings['max_chars']);
        if ($plain === '') {
            return;
        }

        $estimate = eottae_tts_estimated_minutes($plain);
        $endpoint = G5_URL.'/proc/eottae-tts.php';
        $css_ver = is_file(G5_PATH.'/css/eottae-tts.css') ? '?ver='.(int) filemtime(G5_PATH.'/css/eottae-tts.css') : '';
        $js_ver = is_file(G5_PATH.'/js/eottae-tts.js') ? '?ver='.(int) filemtime(G5_PATH.'/js/eottae-tts.js') : '';
        $css_url = G5_CSS_URL.'/eottae-tts.css'.$css_ver;
        $js_url = G5_JS_URL.'/eottae-tts.js'.$js_ver;
        ?>
<link rel="stylesheet" href="<?php echo $css_url; ?>">
<div class="eottae-tts-player" data-eottae-tts-player data-endpoint="<?php echo get_text($endpoint); ?>" data-bo-table="<?php echo get_text($bo_table); ?>" data-wr-id="<?php echo (int) $wr_id; ?>" data-default-speed="<?php echo get_text(number_format((float) $settings['speed'], 2, '.', '')); ?>">
    <button type="button" class="eottae-tts-player__play" data-tts-play aria-label="게시글 읽어주기"><span>▶</span></button>
    <div class="eottae-tts-player__main">
        <strong>세부어때 음성읽기</strong>
        <span data-tts-status>이 글을 AI 음성으로 읽어드릴게요.</span>
    </div>
    <label class="eottae-tts-player__speed">
        <span>속도</span>
        <select data-tts-speed>
            <?php foreach (array('0.75', '1.00', '1.25', '1.50', '1.75', '2.00') as $speed) { ?>
            <option value="<?php echo $speed; ?>"<?php echo abs((float) $speed - (float) $settings['speed']) < 0.01 ? ' selected' : ''; ?>><?php echo rtrim(rtrim($speed, '0'), '.'); ?>x</option>
            <?php } ?>
        </select>
    </label>
    <span class="eottae-tts-player__time">약 <?php echo number_format($estimate); ?>분</span>
    <audio preload="none" data-tts-audio></audio>
</div>
<script src="<?php echo $js_url; ?>" defer></script>
        <?php
    }
}
