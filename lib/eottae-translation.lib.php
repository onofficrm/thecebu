<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_translation_table')) {
    function eottae_translation_table()
    {
        return 'post_translations';
    }
}

if (!function_exists('eottae_translation_supported_languages')) {
    function eottae_translation_supported_languages()
    {
        return array(
            'ko' => '한국어',
            'en' => 'English',
            'ja' => '日本語',
            'zh' => '中文',
        );
    }
}

if (!function_exists('eottae_translation_normalize_language')) {
    function eottae_translation_normalize_language($language, $default = 'ko')
    {
        $language = strtolower(trim((string) $language));
        if (strpos($language, 'ko') === 0) {
            return 'ko';
        }
        if (strpos($language, 'en') === 0) {
            return 'en';
        }
        if (strpos($language, 'ja') === 0) {
            return 'ja';
        }
        if ($language === 'zh' || strpos($language, 'zh-') === 0) {
            return 'zh';
        }

        if ($default === '') {
            return '';
        }

        $supported = eottae_translation_supported_languages();
        return isset($supported[$default]) ? $default : 'ko';
    }
}

if (!function_exists('eottae_translation_language_label')) {
    function eottae_translation_language_label($language)
    {
        $supported = eottae_translation_supported_languages();
        $language = eottae_translation_normalize_language($language);

        return isset($supported[$language]) ? $supported[$language] : $supported['ko'];
    }
}

if (!function_exists('eottae_translation_ensure_schema')) {
    function eottae_translation_ensure_schema()
    {
        $table = eottae_translation_table();

        sql_query(" CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `post_id` int(11) NOT NULL DEFAULT '0',
            `board_type` varchar(50) NOT NULL DEFAULT '',
            `source_language` varchar(10) NOT NULL DEFAULT 'ko',
            `target_language` varchar(10) NOT NULL DEFAULT '',
            `translated_title` text NOT NULL,
            `translated_content` mediumtext NOT NULL,
            `provider` varchar(30) NOT NULL DEFAULT '',
            `source_updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            UNIQUE KEY `post_board_target` (`post_id`, `board_type`, `target_language`),
            KEY `board_post` (`board_type`, `post_id`),
            KEY `target_language` (`target_language`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

        return true;
    }
}

if (!function_exists('eottae_translation_secret')) {
    function eottae_translation_secret($config_key, $env_keys = array(), $default = '')
    {
        if (function_exists('eottae_secrets_load')) {
            eottae_secrets_load();
        }

        if (function_exists('eottae_secrets_get')) {
            $value = trim((string) eottae_secrets_get($config_key, ''));
            if ($value !== '') {
                return $value;
            }
        } elseif (function_exists('g5site_cfg')) {
            $value = trim((string) g5site_cfg($config_key, ''));
            if ($value !== '') {
                return $value;
            }
        }

        foreach ((array) $env_keys as $env_key) {
            $env_value = getenv($env_key);
            if ($env_value !== false && trim((string) $env_value) !== '') {
                return trim((string) $env_value);
            }
        }

        return $default;
    }
}

if (!function_exists('eottae_translation_provider')) {
    function eottae_translation_provider()
    {
        $provider = strtolower(eottae_translation_secret('translation_provider', array('TRANSLATION_PROVIDER'), 'openai'));
        if (!in_array($provider, array('openai', 'google', 'deepl'), true)) {
            $provider = 'openai';
        }

        return $provider;
    }
}

if (!function_exists('eottae_translation_api_key')) {
    function eottae_translation_api_key($provider)
    {
        $provider = strtolower((string) $provider);
        if ($provider === 'google') {
            return eottae_translation_secret('google_translate_api_key', array('GOOGLE_TRANSLATE_API_KEY'), '');
        }
        if ($provider === 'deepl') {
            return eottae_translation_secret('deepl_api_key', array('DEEPL_API_KEY'), '');
        }

        $key = eottae_translation_secret('openai_api_key', array('OPENAI_API_KEY', 'EOTTAE_OPENAI_API_KEY'), '');
        if ($key !== '') {
            return $key;
        }

        return function_exists('eottae_ai_openai_api_key') ? eottae_ai_openai_api_key() : '';
    }
}

if (!function_exists('eottae_translation_source_updated_at')) {
    function eottae_translation_source_updated_at(array $write)
    {
        $candidates = array(
            isset($write['wr_last']) ? (string) $write['wr_last'] : '',
            isset($write['wr_datetime']) ? (string) $write['wr_datetime'] : '',
        );

        foreach ($candidates as $value) {
            if ($value !== '' && $value !== '0000-00-00 00:00:00') {
                return $value;
            }
        }

        return G5_TIME_YMDHIS;
    }
}

if (!function_exists('eottae_translation_cache_get')) {
    function eottae_translation_cache_get($bo_table, $wr_id, $target_language, $source_updated_at)
    {
        eottae_translation_ensure_schema();

        $table = eottae_translation_table();
        $bo_table_sql = sql_escape_string($bo_table);
        $target_sql = sql_escape_string($target_language);
        $wr_id = (int) $wr_id;
        $source_sql = sql_escape_string($source_updated_at);

        $row = sql_fetch(" select * from `{$table}`
            where post_id = '{$wr_id}'
              and board_type = '{$bo_table_sql}'
              and target_language = '{$target_sql}'
              and source_updated_at >= '{$source_sql}'
            limit 1 ");

        return !empty($row['id']) ? $row : null;
    }
}

if (!function_exists('eottae_translation_cache_save')) {
    function eottae_translation_cache_save($bo_table, $wr_id, $source_language, $target_language, $title, $content, $provider, $source_updated_at)
    {
        eottae_translation_ensure_schema();

        $table = eottae_translation_table();
        $bo_table_sql = sql_escape_string($bo_table);
        $source_sql = sql_escape_string($source_language);
        $target_sql = sql_escape_string($target_language);
        $title_sql = sql_escape_string($title);
        $content_sql = sql_escape_string($content);
        $provider_sql = sql_escape_string($provider);
        $source_updated_sql = sql_escape_string($source_updated_at);
        $wr_id = (int) $wr_id;

        sql_query(" insert into `{$table}`
            set post_id = '{$wr_id}',
                board_type = '{$bo_table_sql}',
                source_language = '{$source_sql}',
                target_language = '{$target_sql}',
                translated_title = '{$title_sql}',
                translated_content = '{$content_sql}',
                provider = '{$provider_sql}',
                source_updated_at = '{$source_updated_sql}',
                created_at = '".G5_TIME_YMDHIS."',
                updated_at = '".G5_TIME_YMDHIS."'
            on duplicate key update
                source_language = values(source_language),
                translated_title = values(translated_title),
                translated_content = values(translated_content),
                provider = values(provider),
                source_updated_at = values(source_updated_at),
                updated_at = values(updated_at) ", false);
    }
}

if (!function_exists('eottae_translation_cache_delete')) {
    function eottae_translation_cache_delete($bo_table, $wr_id)
    {
        eottae_translation_ensure_schema();

        $table = eottae_translation_table();
        $bo_table_sql = sql_escape_string($bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table_sql === '' || $wr_id < 1) {
            return;
        }

        sql_query(" delete from `{$table}` where board_type = '{$bo_table_sql}' and post_id = '{$wr_id}' ", false);
    }
}

if (!function_exists('eottae_translation_on_write_update_after')) {
    function eottae_translation_on_write_update_after($board, $wr_id, $w, $qstr = '', $redirect_url = '')
    {
        if ($w !== 'u' || empty($board['bo_table']) || (int) $wr_id < 1) {
            return;
        }

        eottae_translation_cache_delete($board['bo_table'], (int) $wr_id);
    }
}

if (!function_exists('eottae_translation_extract_json')) {
    function eottae_translation_extract_json($text)
    {
        $text = trim((string) $text);
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/is', $text, $matches)) {
            $decoded = json_decode(trim($matches[1]), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        if (preg_match('/(\{.*\})/s', $text, $matches)) {
            $decoded = json_decode(trim($matches[1]), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}

if (!function_exists('eottae_translation_openai_translate_post')) {
    function eottae_translation_openai_translate_post(array $args)
    {
        $api_key = eottae_translation_api_key('openai');
        if ($api_key === '' || !function_exists('curl_init')) {
            return array('success' => false, 'message' => $api_key === '' ? 'no_api_key' : 'no_curl');
        }

        if (function_exists('eottae_ai_release_session_lock')) {
            eottae_ai_release_session_lock();
        } elseif (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $target_language = eottae_translation_language_label($args['targetLanguage'] ?? 'en');
        $title = (string) ($args['title'] ?? '');
        $content = (string) ($args['content'] ?? '');
        $model = eottae_translation_secret('translation_openai_model', array('TRANSLATION_OPENAI_MODEL'), 'gpt-4o-mini');
        if ($model === '') {
            $model = 'gpt-4o-mini';
        }

        $prompt = "너는 세부 지역 커뮤니티 사이트의 전문 번역가다.\n"
            ."아래 게시글을 {$target_language}로 자연스럽게 번역해라.\n"
            ."원문의 의미를 생략하거나 요약하지 말고 유지해라.\n"
            ."HTML 태그가 있다면 태그 구조는 유지하고 텍스트만 번역해라.\n"
            ."지역명, 업체명, 사람 이름, 전화번호, 주소, 가격, 날짜는 임의로 바꾸지 마라.\n"
            ."욕설, 광고, 사용자 작성 글의 뉘앙스를 과도하게 바꾸지 마라.\n\n"
            ."제목:\n{$title}\n\n"
            ."본문:\n{$content}\n\n"
            ."반환 형식은 JSON으로만 해라.\n\n"
            ."{\n  \"translatedTitle\": \"\",\n  \"translatedContent\": \"\"\n}";

        $payload = array(
            'model' => $model,
            'messages' => array(
                array('role' => 'user', 'content' => $prompt),
            ),
            'temperature' => 0.2,
            'response_format' => array('type' => 'json_object'),
        );

        @set_time_limit(90);
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$api_key,
            ),
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 70,
        ));
        $raw = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
        if ($raw === false || $http_code < 200 || $http_code >= 300 || !is_array($decoded)) {
            return array(
                'success' => false,
                'message' => $curl_error !== '' ? $curl_error : 'openai_http_'.$http_code,
            );
        }

        $json_text = trim((string) ($decoded['choices'][0]['message']['content'] ?? ''));
        $translated = eottae_translation_extract_json($json_text);
        if (!is_array($translated)) {
            return array('success' => false, 'message' => 'invalid_translation_json');
        }

        $translated_title = trim((string) ($translated['translatedTitle'] ?? ''));
        $translated_content = trim((string) ($translated['translatedContent'] ?? ''));
        if ($translated_title === '' && $translated_content === '') {
            return array('success' => false, 'message' => 'empty_translation');
        }

        return array(
            'success' => true,
            'translatedTitle' => $translated_title,
            'translatedContent' => $translated_content,
            'provider' => 'openai',
        );
    }
}

if (!function_exists('eottae_translate_post')) {
    function eottae_translate_post(array $args)
    {
        $provider = eottae_translation_provider();
        if ($provider === 'openai') {
            return eottae_translation_openai_translate_post($args);
        }

        return array(
            'success' => false,
            'message' => $provider.'_provider_not_implemented',
            'provider' => $provider,
        );
    }
}

if (!function_exists('eottae_translation_sanitize_title')) {
    function eottae_translation_sanitize_title($title)
    {
        return trim(clean_xss_tags((string) $title, 1, 1));
    }
}

if (!function_exists('eottae_translation_sanitize_content')) {
    function eottae_translation_sanitize_content($content, $html)
    {
        $content = trim((string) $content);
        if ((int) $html > 0) {
            return function_exists('html_purifier') ? html_purifier($content) : $content;
        }

        return clean_xss_tags($content, 1, 1);
    }
}

if (!function_exists('eottae_translation_render_content')) {
    function eottae_translation_render_content($content, $html)
    {
        $rendered = conv_content($content, (int) $html);
        if (function_exists('get_view_thumbnail')) {
            include_once G5_LIB_PATH.'/thumbnail.lib.php';
            $rendered = get_view_thumbnail($rendered);
        }

        return $rendered;
    }
}

if (!function_exists('eottae_translation_token')) {
    function eottae_translation_token($regenerate = false)
    {
        $key = 'eottae_post_translation_token';
        $token = get_session($key);
        if ($regenerate || $token === '' || $token === null) {
            $token = md5(uniqid((string) mt_rand(), true));
            set_session($key, $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_post_translation_enqueue_assets')) {
    function eottae_post_translation_enqueue_assets()
    {
        static $enqueued = false;
        if ($enqueued) {
            return;
        }
        $enqueued = true;

        $css = G5_PATH.'/css/eottae-post-translation.css';
        if (is_file($css)) {
            add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-post-translation.css?ver='.(int) filemtime($css).'">', 31);
        }

        $js = G5_PATH.'/js/eottae-post-translation.js';
        if (is_file($js)) {
            add_javascript('<script src="'.G5_JS_URL.'/eottae-post-translation.js?ver='.(int) filemtime($js).'" defer></script>', 31);
        }
    }
}

if (!function_exists('eottae_post_translation_panel_html')) {
    function eottae_post_translation_panel_html($bo_table, array $view, $source_language = 'ko')
    {
        if (empty($view['wr_id'])) {
            return '';
        }

        eottae_post_translation_enqueue_assets();

        $source_language = eottae_translation_normalize_language($source_language, 'ko');
        $targets = array(
            'en' => 'English',
            'ja' => '日本語',
            'zh' => '中文',
        );

        ob_start();
        ?>
        <div class="post-translation" data-post-translation
             data-bo-table="<?php echo get_text($bo_table); ?>"
             data-wr-id="<?php echo (int) $view['wr_id']; ?>"
             data-source-language="<?php echo get_text($source_language); ?>"
             data-token="<?php echo get_text(eottae_translation_token()); ?>"
             data-endpoint="<?php echo G5_URL; ?>/proc/eottae-post-translate.php">
            <div class="post-translation__buttons" role="group" aria-label="게시글 번역">
                <button type="button" class="post-translation__btn is-active" data-translation-original data-i18n="translation.view_original">원문 보기</button>
                <?php foreach ($targets as $lang => $label) { ?>
                <button type="button" class="post-translation__btn" data-translation-target="<?php echo $lang; ?>" data-i18n="translation.view_<?php echo $lang; ?>"><?php echo $label; ?>로 보기</button>
                <?php } ?>
            </div>
            <p class="post-translation__status" data-translation-status role="status" aria-live="polite" hidden></p>
            <p class="post-translation__notice" data-translation-notice data-i18n="translation.notice" hidden>이 번역은 자동 번역으로 제공됩니다. 일부 표현이 자연스럽지 않을 수 있습니다.</p>
        </div>
        <?php
        return trim(ob_get_clean());
    }
}
