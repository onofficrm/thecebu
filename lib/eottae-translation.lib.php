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

if (!function_exists('eottae_translation_job_table')) {
    function eottae_translation_job_table()
    {
        return 'post_translation_jobs';
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
        $job_table = eottae_translation_job_table();

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

        sql_query(" CREATE TABLE IF NOT EXISTS `{$job_table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `post_id` int(11) NOT NULL DEFAULT '0',
            `board_type` varchar(50) NOT NULL DEFAULT '',
            `source_language` varchar(10) NOT NULL DEFAULT 'ko',
            `target_language` varchar(10) NOT NULL DEFAULT '',
            `source_updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `status` varchar(20) NOT NULL DEFAULT 'queued',
            `attempts` int(11) NOT NULL DEFAULT '0',
            `last_error` varchar(255) NOT NULL DEFAULT '',
            `locked_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            UNIQUE KEY `post_board_target` (`post_id`, `board_type`, `target_language`),
            KEY `status_updated` (`status`, `updated_at`),
            KEY `board_post` (`board_type`, `post_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

        $review_status = sql_fetch(" show columns from `{$table}` like 'review_status' ", false);
        if (empty($review_status)) {
            sql_query(" alter table `{$table}`
                add `review_status` varchar(20) not null default 'auto' after `provider`,
                add `reviewed_by` varchar(20) not null default '' after `review_status`,
                add `reviewed_at` datetime not null default '0000-00-00 00:00:00' after `reviewed_by`,
                add key `review_status` (`review_status`) ", false);
        }

        $translated_extras = sql_fetch(" show columns from `{$table}` like 'translated_extras' ", false);
        if (empty($translated_extras)) {
            sql_query(" alter table `{$table}` add `translated_extras` text not null after `translated_content` ", false);
        }

        return true;
    }
}

if (!function_exists('eottae_translation_review_status_label')) {
    function eottae_translation_review_status_label($status)
    {
        $status = strtolower(trim((string) $status));
        if ($status === 'reviewed') {
            return '검수완료';
        }

        return '자동번역';
    }
}

if (!function_exists('eottae_translation_cache_get_by_id')) {
    function eottae_translation_cache_get_by_id($id)
    {
        eottae_translation_ensure_schema();

        $id = (int) $id;
        if ($id < 1) {
            return null;
        }

        $table = eottae_translation_table();
        $row = sql_fetch(" select * from `{$table}` where id = '{$id}' limit 1 ");

        return !empty($row['id']) ? $row : null;
    }
}

if (!function_exists('eottae_translation_post_fetch')) {
    function eottae_translation_post_fetch($bo_table, $wr_id)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return null;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $write = sql_fetch(" select * from `{$write_table}` where wr_id = '{$wr_id}' and wr_is_comment = 0 limit 1 ");
        if (empty($write['wr_id'])) {
            return null;
        }

        return $write;
    }
}

if (!function_exists('eottae_translation_extra_labels')) {
    function eottae_translation_extra_labels($bo_table = '')
    {
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        if (function_exists('eottae_is_event_board') && eottae_is_event_board($bo_table)) {
            return array(
                'display_name' => '업체명/작성자명',
                'benefit' => '혜택 요약',
                'contact' => '문의 방법',
            );
        }
        if (function_exists('eottae_is_job_board') && eottae_is_job_board($bo_table)
            && function_exists('eottae_job_translation_extra_labels')) {
            return eottae_job_translation_extra_labels();
        }

        return array(
            'intro' => '한 줄 소개',
            'events' => '이벤트/프로모션',
            'coupons' => '쿠폰 안내',
            'hours' => '영업시간',
            'closed' => '휴무일',
            'status' => '영업상태',
        );
    }
}

if (!function_exists('eottae_translation_extra_allowed_keys')) {
    function eottae_translation_extra_allowed_keys($bo_table = '')
    {
        return array_keys(eottae_translation_extra_labels($bo_table));
    }
}

if (!function_exists('eottae_translation_shop_events_text')) {
    function eottae_translation_shop_events_text($bo_table, $wr_id)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return '';
        }

        if (!function_exists('eottae_event_posts_for_shop') && is_file(G5_LIB_PATH.'/eottae-event.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-event.lib.php';
        }
        if (!function_exists('eottae_event_posts_for_shop')) {
            return '';
        }

        $events = eottae_event_posts_for_shop($bo_table, $wr_id, true, 5);
        if (!$events) {
            return '';
        }

        $lines = array();
        foreach ($events as $event) {
            $parts = array();
            $title = trim(get_text($event['wr_subject'] ?? ''));
            if ($title !== '') {
                $parts[] = $title;
            }
            $benefit = trim(get_text($event['wr_7'] ?? ''));
            if ($benefit !== '') {
                $parts[] = $benefit;
            }
            $contact = trim(get_text($event['wr_8'] ?? ''));
            if ($contact !== '') {
                $parts[] = $contact;
            }
            if ($parts) {
                $lines[] = implode(' — ', $parts);
            }
        }

        return implode("\n", $lines);
    }
}

if (!function_exists('eottae_translation_shop_coupons_text')) {
    function eottae_translation_shop_coupons_text(array $write, $bo_table = '')
    {
        if (!function_exists('eottae_shop_owner_mb_id_from_write')) {
            include_once G5_LIB_PATH.'/eottae-shop-owner.lib.php';
        }
        $owner_mb_id = eottae_shop_owner_mb_id_from_write($write);
        if ($owner_mb_id === '') {
            return '';
        }

        if (!function_exists('eottae_business_coupon_campaigns') && is_file(G5_LIB_PATH.'/eottae-business-coupon.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-business-coupon.lib.php';
        }
        if (!function_exists('eottae_business_coupon_campaigns')) {
            return '';
        }

        $campaigns = eottae_business_coupon_campaigns($owner_mb_id, 5);
        if (!$campaigns) {
            return '';
        }

        $lines = array();
        foreach ($campaigns as $coupon) {
            $title = trim(get_text($coupon['cp_title'] ?? ''));
            $desc = trim(get_text($coupon['cp_desc'] ?? ''));
            if ($desc === '' && function_exists('eottae_business_coupon_build_copy')) {
                $copy = eottae_business_coupon_build_copy($coupon);
                if ($title === '' && !empty($copy['title'])) {
                    $title = trim((string) $copy['title']);
                }
                if (!empty($copy['desc'])) {
                    $desc = trim((string) $copy['desc']);
                }
            }
            if ($title === '' && $desc === '') {
                continue;
            }
            $lines[] = $desc !== '' ? ($title !== '' ? $title.' — '.$desc : $desc) : $title;
        }

        return implode("\n", $lines);
    }
}

if (!function_exists('eottae_translation_shop_extras_from_write')) {
    function eottae_translation_shop_extras_from_write(array $write, $bo_table = '')
    {
        if ($bo_table === '' && !empty($write['bo_table'])) {
            $bo_table = (string) $write['bo_table'];
        }
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);

        $map = array(
            'hours' => 'wr_6',
            'closed' => 'wr_7',
            'status' => 'wr_8',
        );
        $extras = array();
        foreach ($map as $key => $field) {
            $value = trim((string) ($write[$field] ?? ''));
            if ($value !== '') {
                $extras[$key] = $value;
            }
        }

        $wr_id = (int) ($write['wr_id'] ?? 0);
        if ($bo_table !== '' && $wr_id > 0) {
            if (!function_exists('eottae_shop_seo_get') && is_file(G5_LIB_PATH.'/eottae-shop-seo.lib.php')) {
                include_once G5_LIB_PATH.'/eottae-shop-seo.lib.php';
            }
            if (function_exists('eottae_shop_seo_get')) {
                $seo = eottae_shop_seo_get($bo_table, $wr_id);
                $intro = trim(get_text($seo['meta_intro'] ?? ''));
                if ($intro !== '') {
                    $extras['intro'] = $intro;
                }
            }

            $events = eottae_translation_shop_events_text($bo_table, $wr_id);
            if ($events !== '') {
                $extras['events'] = $events;
            }

            $coupons = eottae_translation_shop_coupons_text($write, $bo_table);
            if ($coupons !== '') {
                $extras['coupons'] = $coupons;
            }
        }

        return $extras;
    }
}

if (!function_exists('eottae_translation_event_extras_from_write')) {
    function eottae_translation_event_extras_from_write(array $write)
    {
        $map = array(
            'display_name' => 'wr_3',
            'benefit' => 'wr_7',
            'contact' => 'wr_8',
        );
        $extras = array();
        foreach ($map as $key => $field) {
            $value = trim((string) ($write[$field] ?? ''));
            if ($value !== '') {
                $extras[$key] = $value;
            }
        }

        return $extras;
    }
}

if (!function_exists('eottae_translation_extras_from_write')) {
    function eottae_translation_extras_from_write($bo_table, array $write)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        if (function_exists('eottae_is_shop_board') && eottae_is_shop_board($bo_table)) {
            return eottae_translation_shop_extras_from_write($write, $bo_table);
        }
        if (function_exists('eottae_is_event_board') && eottae_is_event_board($bo_table)) {
            return eottae_translation_event_extras_from_write($write);
        }
        if (function_exists('eottae_is_job_board') && eottae_is_job_board($bo_table)) {
            if (!function_exists('eottae_job_translation_extras_from_write') && is_file(G5_LIB_PATH.'/eottae-job.lib.php')) {
                include_once G5_LIB_PATH.'/eottae-job.lib.php';
            }
            if (function_exists('eottae_job_translation_extras_from_write')) {
                return eottae_job_translation_extras_from_write($write);
            }
        }

        return array();
    }
}

if (!function_exists('eottae_translation_encode_extras')) {
    function eottae_translation_encode_extras(array $extras)
    {
        $extras = array_filter($extras, function ($value) {
            return trim((string) $value) !== '';
        });

        return $extras ? json_encode($extras, JSON_UNESCAPED_UNICODE) : '';
    }
}

if (!function_exists('eottae_translation_decode_extras')) {
    function eottae_translation_decode_extras($json)
    {
        $json = trim((string) $json);
        if ($json === '') {
            return array();
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : array();
    }
}

if (!function_exists('eottae_translation_sanitize_extras')) {
    function eottae_translation_sanitize_extras(array $extras, $bo_table = '')
    {
        $allowed = eottae_translation_extra_allowed_keys($bo_table);
        if (!$allowed) {
            $allowed = array('intro', 'events', 'coupons', 'hours', 'closed', 'status', 'display_name', 'benefit', 'contact');
        }
        $clean = array();
        foreach ($allowed as $key) {
            if (!isset($extras[$key])) {
                continue;
            }
            $value = trim(clean_xss_tags((string) $extras[$key], 1, 1));
            if ($value !== '') {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }
}

if (!function_exists('eottae_translation_build_translate_args')) {
    function eottae_translation_build_translate_args($bo_table, array $write, $source_language, $target_language)
    {
        $args = array(
            'bo_table' => preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table),
            'title' => (string) ($write['wr_subject'] ?? ''),
            'content' => (string) ($write['wr_content'] ?? ''),
            'sourceLanguage' => $source_language,
            'targetLanguage' => $target_language,
            'extras' => array(),
        );

        $args['extras'] = eottae_translation_extras_from_write($bo_table, $write);

        return $args;
    }
}

if (!function_exists('eottae_translation_list_snippet')) {
    function eottae_translation_list_snippet($content, $limit = 110)
    {
        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $content)));
        if ($plain === '') {
            return '';
        }

        if (function_exists('cut_str')) {
            return cut_str($plain, (int) $limit);
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr') && mb_strlen($plain, 'UTF-8') > $limit) {
            return mb_substr($plain, 0, $limit, 'UTF-8').'…';
        }

        return strlen($plain) > $limit ? substr($plain, 0, $limit).'…' : $plain;
    }
}

if (!function_exists('eottae_translation_cache_batch_lookup')) {
    function eottae_translation_cache_batch_lookup(array $items, $target_language)
    {
        eottae_translation_ensure_schema();

        $target_language = eottae_translation_normalize_language($target_language, '');
        if ($target_language === '' || $target_language === 'ko') {
            return array();
        }

        $results = array();
        $seen = array();
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) ($item['bo_table'] ?? ''));
            $wr_id = (int) ($item['wr_id'] ?? 0);
            if ($bo_table === '' || $wr_id < 1) {
                continue;
            }

            $key = $bo_table.':'.$wr_id;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $write = eottae_translation_post_fetch($bo_table, $wr_id);
            if (!$write) {
                continue;
            }

            $source_updated_at = eottae_translation_source_updated_at($write);
            $cached = eottae_translation_cache_get($bo_table, $wr_id, $target_language, $source_updated_at);
            if (!$cached) {
                continue;
            }

            $title = eottae_translation_sanitize_title($cached['translated_title'] ?? '');
            if ($title === '') {
                continue;
            }

            $payload = array(
                'translatedTitle' => $title,
                'reviewStatus' => (string) ($cached['review_status'] ?? 'auto'),
            );

            $content = eottae_translation_sanitize_content($cached['translated_content'] ?? '', eottae_translation_post_html_mode($write));
            $snippet = eottae_translation_list_snippet($content);
            if ($snippet !== '') {
                $payload['translatedSnippet'] = $snippet;
            }

            $extras = eottae_translation_sanitize_extras(
                eottae_translation_decode_extras($cached['translated_extras'] ?? ''),
                $bo_table
            );
            if ($extras) {
                $payload['translatedExtras'] = $extras;
            }

            $results[$key] = $payload;
        }

        return $results;
    }
}

if (!function_exists('eottae_translation_cache_save_review')) {
    function eottae_translation_cache_save_review($id, $title, $content, $reviewed_by = '', $extras = null)
    {
        eottae_translation_ensure_schema();

        $cache = eottae_translation_cache_get_by_id($id);
        if (!$cache) {
            return array('success' => false, 'message' => 'cache_not_found');
        }

        $write = eottae_translation_post_fetch($cache['board_type'], $cache['post_id']);
        if (!$write) {
            return array('success' => false, 'message' => 'post_not_found');
        }

        $html = eottae_translation_post_html_mode($write);
        $title = eottae_translation_sanitize_title($title);
        $content = eottae_translation_sanitize_content($content, $html);
        if ($title === '' && $content === '') {
            return array('success' => false, 'message' => 'empty_translation');
        }

        $table = eottae_translation_table();
        $id = (int) $cache['id'];
        $title_sql = sql_escape_string($title);
        $content_sql = sql_escape_string($content);
        $reviewed_by_sql = sql_escape_string((string) $reviewed_by);
        $extras_sql = '';
        if (is_array($extras)) {
            $extras_sql = ", translated_extras = '".sql_escape_string(eottae_translation_encode_extras(eottae_translation_sanitize_extras($extras, (string) ($cache['board_type'] ?? ''))))."'";
        }

        sql_query(" update `{$table}`
            set translated_title = '{$title_sql}',
                translated_content = '{$content_sql}',
                review_status = 'reviewed',
                reviewed_by = '{$reviewed_by_sql}',
                reviewed_at = '".G5_TIME_YMDHIS."',
                updated_at = '".G5_TIME_YMDHIS."'
                {$extras_sql}
            where id = '{$id}' ", false);

        return array('success' => true);
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
    function eottae_translation_cache_save($bo_table, $wr_id, $source_language, $target_language, $title, $content, $provider, $source_updated_at, array $extras = array())
    {
        eottae_translation_ensure_schema();

        $table = eottae_translation_table();
        $bo_table_sql = sql_escape_string($bo_table);
        $source_sql = sql_escape_string($source_language);
        $target_sql = sql_escape_string($target_language);
        $title_sql = sql_escape_string($title);
        $content_sql = sql_escape_string($content);
        $extras_sql = sql_escape_string(eottae_translation_encode_extras(eottae_translation_sanitize_extras($extras, $bo_table)));
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
                translated_extras = '{$extras_sql}',
                provider = '{$provider_sql}',
                review_status = 'auto',
                reviewed_by = '',
                reviewed_at = '0000-00-00 00:00:00',
                source_updated_at = '{$source_updated_sql}',
                created_at = '".G5_TIME_YMDHIS."',
                updated_at = '".G5_TIME_YMDHIS."'
            on duplicate key update
                source_language = values(source_language),
                translated_title = values(translated_title),
                translated_content = values(translated_content),
                translated_extras = values(translated_extras),
                provider = values(provider),
                review_status = 'auto',
                reviewed_by = '',
                reviewed_at = '0000-00-00 00:00:00',
                source_updated_at = values(source_updated_at),
                updated_at = values(updated_at) ", false);
    }
}

if (!function_exists('eottae_translation_auto_prewarm_enabled')) {
    function eottae_translation_auto_prewarm_enabled()
    {
        $enabled = strtolower(eottae_translation_secret('translation_auto_prewarm', array('TRANSLATION_AUTO_PREWARM'), '1'));

        return !in_array($enabled, array('0', 'false', 'no', 'off'), true);
    }
}

if (!function_exists('eottae_translation_pretranslate_targets')) {
    function eottae_translation_pretranslate_targets($source_language = 'ko')
    {
        $source_language = eottae_translation_normalize_language($source_language, 'ko');
        $targets = array('en', 'ja', 'zh');

        return array_values(array_filter($targets, function ($language) use ($source_language) {
            return $language !== $source_language;
        }));
    }
}

if (!function_exists('eottae_translation_post_html_mode')) {
    function eottae_translation_post_html_mode(array $write)
    {
        $option = (string) ($write['wr_option'] ?? '');
        if (strpos($option, 'html1') !== false) {
            return 1;
        }
        if (strpos($option, 'html2') !== false) {
            return 2;
        }
        if (function_exists('eottae_icrm_content_needs_html') && eottae_icrm_content_needs_html($write['wr_content'] ?? '')) {
            return 1;
        }

        return 0;
    }
}

if (!function_exists('eottae_translation_should_pretranslate_board')) {
    function eottae_translation_should_pretranslate_board($bo_table)
    {
        $bo_table = (string) $bo_table;
        if ($bo_table === '') {
            return false;
        }

        if (function_exists('eottae_is_shop_board') && eottae_is_shop_board($bo_table)) {
            return true;
        }
        if (function_exists('eottae_is_community_board') && eottae_is_community_board($bo_table)) {
            return true;
        }
        if (function_exists('eottae_is_event_board') && eottae_is_event_board($bo_table)) {
            return true;
        }
        if (function_exists('eottae_is_job_board') && eottae_is_job_board($bo_table)) {
            return true;
        }

        return in_array($bo_table, array('column', 'gallery', 'youtube'), true);
    }
}

if (!function_exists('eottae_translation_detect_post_source_language')) {
    function eottae_translation_detect_post_source_language($bo_table, array $write)
    {
        if (function_exists('eottae_is_shop_board') && eottae_is_shop_board($bo_table)) {
            return 'ko';
        }

        if (function_exists('eottae_lang_from_row')) {
            return eottae_lang_from_row($write);
        }

        return eottae_translation_normalize_language($write['language'] ?? 'ko', 'ko');
    }
}

if (!function_exists('eottae_translation_pretranslate_post')) {
    function eottae_translation_pretranslate_post($bo_table, $wr_id, $source_language = '')
    {
        global $g5;

        if (!eottae_translation_auto_prewarm_enabled()) {
            return array('success' => false, 'message' => 'prewarm_disabled');
        }

        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1 || !eottae_translation_should_pretranslate_board($bo_table)) {
            return array('success' => false, 'message' => 'unsupported_post');
        }

        $provider = eottae_translation_provider();
        if (eottae_translation_api_key($provider) === '') {
            return array('success' => false, 'message' => 'no_api_key');
        }

        $board = sql_fetch(" select * from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' limit 1 ");
        if (empty($board['bo_table'])) {
            return array('success' => false, 'message' => 'board_not_found');
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $write = sql_fetch(" select * from `{$write_table}` where wr_id = '{$wr_id}' and wr_is_comment = 0 limit 1 ");
        if (empty($write['wr_id'])) {
            return array('success' => false, 'message' => 'post_not_found');
        }

        $source_language = eottae_translation_normalize_language(
            $source_language !== '' ? $source_language : eottae_translation_detect_post_source_language($bo_table, $write),
            'ko'
        );
        $source_updated_at = eottae_translation_source_updated_at($write);
        $html = eottae_translation_post_html_mode($write);
        $done = 0;
        $failed = array();

        @set_time_limit(240);
        foreach (eottae_translation_pretranslate_targets($source_language) as $target_language) {
            if (eottae_translation_cache_get($bo_table, $wr_id, $target_language, $source_updated_at)) {
                continue;
            }

            $result = eottae_translate_post(eottae_translation_build_translate_args(
                $bo_table,
                $write,
                $source_language,
                $target_language
            ));

            if (empty($result['success'])) {
                $failed[$target_language] = (string) ($result['message'] ?? 'translation_failed');
                continue;
            }

            $translated_title = eottae_translation_sanitize_title($result['translatedTitle'] ?? '');
            $translated_content = eottae_translation_sanitize_content($result['translatedContent'] ?? '', $html);
            if ($translated_title === '' && $translated_content === '') {
                $failed[$target_language] = 'empty_translation';
                continue;
            }

            eottae_translation_cache_save(
                $bo_table,
                $wr_id,
                $source_language,
                $target_language,
                $translated_title,
                $translated_content,
                (string) ($result['provider'] ?? $provider),
                $source_updated_at,
                eottae_translation_sanitize_extras($result['translatedExtras'] ?? array(), $bo_table)
            );
            $done += 1;
        }

        return array('success' => true, 'done' => $done, 'failed' => $failed);
    }
}

if (!function_exists('eottae_translation_process_job')) {
    function eottae_translation_process_job(array $job)
    {
        global $g5;

        $job_table = eottae_translation_job_table();
        $id = (int) ($job['id'] ?? 0);
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) ($job['board_type'] ?? ''));
        $wr_id = (int) ($job['post_id'] ?? 0);
        $target_language = eottae_translation_normalize_language($job['target_language'] ?? '', '');
        $source_language = eottae_translation_normalize_language($job['source_language'] ?? 'ko', 'ko');
        if ($id < 1 || $bo_table === '' || $wr_id < 1 || $target_language === '') {
            return array('success' => false, 'message' => 'invalid_job');
        }

        sql_query(" update `{$job_table}` set status = 'processing', locked_at = '".G5_TIME_YMDHIS."', updated_at = '".G5_TIME_YMDHIS."' where id = '{$id}' ", false);

        $write_table = $g5['write_prefix'].$bo_table;
        $write = sql_fetch(" select * from `{$write_table}` where wr_id = '{$wr_id}' and wr_is_comment = 0 limit 1 ");
        if (empty($write['wr_id'])) {
            sql_query(" update `{$job_table}` set status = 'failed', attempts = attempts + 1, last_error = 'post_not_found', updated_at = '".G5_TIME_YMDHIS."' where id = '{$id}' ", false);
            return array('success' => false, 'message' => 'post_not_found');
        }

        $source_updated_at = eottae_translation_source_updated_at($write);
        if (!empty($job['source_updated_at']) && $job['source_updated_at'] !== '0000-00-00 00:00:00' && strcmp($job['source_updated_at'], $source_updated_at) > 0) {
            $source_updated_at = $job['source_updated_at'];
        }

        if (eottae_translation_cache_get($bo_table, $wr_id, $target_language, $source_updated_at)) {
            sql_query(" update `{$job_table}` set status = 'done', last_error = '', updated_at = '".G5_TIME_YMDHIS."' where id = '{$id}' ", false);
            return array('success' => true, 'cached' => true);
        }

        $result = eottae_translate_post(eottae_translation_build_translate_args(
            $bo_table,
            $write,
            $source_language,
            $target_language
        ));

        if (empty($result['success'])) {
            $error = substr((string) ($result['message'] ?? 'translation_failed'), 0, 240);
            $next_status = ((int) ($job['attempts'] ?? 0) + 1) >= 3 ? 'failed' : 'queued';
            sql_query(" update `{$job_table}`
                set status = '{$next_status}',
                    attempts = attempts + 1,
                    last_error = '".sql_escape_string($error)."',
                    updated_at = '".G5_TIME_YMDHIS."'
                where id = '{$id}' ", false);
            return array('success' => false, 'message' => $error);
        }

        $html = eottae_translation_post_html_mode($write);
        $translated_title = eottae_translation_sanitize_title($result['translatedTitle'] ?? '');
        $translated_content = eottae_translation_sanitize_content($result['translatedContent'] ?? '', $html);
        if ($translated_title === '' && $translated_content === '') {
            sql_query(" update `{$job_table}` set status = 'failed', attempts = attempts + 1, last_error = 'empty_translation', updated_at = '".G5_TIME_YMDHIS."' where id = '{$id}' ", false);
            return array('success' => false, 'message' => 'empty_translation');
        }

        eottae_translation_cache_save(
            $bo_table,
            $wr_id,
            $source_language,
            $target_language,
            $translated_title,
            $translated_content,
            (string) ($result['provider'] ?? eottae_translation_provider()),
            $source_updated_at,
            eottae_translation_sanitize_extras($result['translatedExtras'] ?? array())
        );

        sql_query(" update `{$job_table}` set status = 'done', last_error = '', updated_at = '".G5_TIME_YMDHIS."' where id = '{$id}' ", false);
        return array('success' => true, 'cached' => false);
    }
}

if (!function_exists('eottae_translation_run_queue')) {
    function eottae_translation_run_queue($limit = 5)
    {
        eottae_translation_ensure_schema();

        $limit = max(1, min(30, (int) $limit));
        $job_table = eottae_translation_job_table();
        $result = sql_query(" select * from `{$job_table}`
            where status = 'queued'
              and attempts < 3
            order by updated_at asc, id asc
            limit {$limit} ", false);

        $summary = array('processed' => 0, 'succeeded' => 0, 'failed' => 0);
        while ($row = sql_fetch_array($result)) {
            $summary['processed'] += 1;
            $job_result = eottae_translation_process_job($row);
            if (!empty($job_result['success'])) {
                $summary['succeeded'] += 1;
            } else {
                $summary['failed'] += 1;
            }
        }

        return $summary;
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

if (!function_exists('eottae_translation_job_delete')) {
    function eottae_translation_job_delete($bo_table, $wr_id)
    {
        eottae_translation_ensure_schema();

        $job_table = eottae_translation_job_table();
        $bo_table_sql = sql_escape_string($bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table_sql === '' || $wr_id < 1) {
            return;
        }

        sql_query(" delete from `{$job_table}` where board_type = '{$bo_table_sql}' and post_id = '{$wr_id}' ", false);
    }
}

if (!function_exists('eottae_translation_job_enqueue')) {
    function eottae_translation_job_enqueue($bo_table, $wr_id, $source_language, $target_language, $source_updated_at)
    {
        eottae_translation_ensure_schema();

        $job_table = eottae_translation_job_table();
        $bo_table_sql = sql_escape_string($bo_table);
        $source_sql = sql_escape_string($source_language);
        $target_sql = sql_escape_string($target_language);
        $source_updated_sql = sql_escape_string($source_updated_at);
        $wr_id = (int) $wr_id;
        if ($bo_table_sql === '' || $wr_id < 1 || $target_sql === '') {
            return;
        }

        sql_query(" insert into `{$job_table}`
            set post_id = '{$wr_id}',
                board_type = '{$bo_table_sql}',
                source_language = '{$source_sql}',
                target_language = '{$target_sql}',
                source_updated_at = '{$source_updated_sql}',
                status = 'queued',
                attempts = 0,
                last_error = '',
                locked_at = '0000-00-00 00:00:00',
                created_at = '".G5_TIME_YMDHIS."',
                updated_at = '".G5_TIME_YMDHIS."'
            on duplicate key update
                source_language = values(source_language),
                source_updated_at = values(source_updated_at),
                status = if(status = 'done', 'done', 'queued'),
                last_error = '',
                updated_at = values(updated_at) ", false);
    }
}

if (!function_exists('eottae_translation_enqueue_post')) {
    function eottae_translation_enqueue_post($bo_table, $wr_id, $source_language = '')
    {
        global $g5;

        if (!eottae_translation_auto_prewarm_enabled()) {
            return array('success' => false, 'message' => 'prewarm_disabled');
        }

        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1 || !eottae_translation_should_pretranslate_board($bo_table)) {
            return array('success' => false, 'message' => 'unsupported_post');
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $write = sql_fetch(" select * from `{$write_table}` where wr_id = '{$wr_id}' and wr_is_comment = 0 limit 1 ");
        if (empty($write['wr_id'])) {
            return array('success' => false, 'message' => 'post_not_found');
        }

        $source_language = eottae_translation_normalize_language(
            $source_language !== '' ? $source_language : eottae_translation_detect_post_source_language($bo_table, $write),
            'ko'
        );
        $source_updated_at = eottae_translation_source_updated_at($write);
        $queued = 0;
        foreach (eottae_translation_pretranslate_targets($source_language) as $target_language) {
            if (eottae_translation_cache_get($bo_table, $wr_id, $target_language, $source_updated_at)) {
                continue;
            }
            eottae_translation_job_enqueue($bo_table, $wr_id, $source_language, $target_language, $source_updated_at);
            $queued += 1;
        }

        return array('success' => true, 'queued' => $queued);
    }
}

if (!function_exists('eottae_translation_on_write_update_after')) {
    function eottae_translation_on_write_update_after($board, $wr_id, $w, $qstr = '', $redirect_url = '')
    {
        if ($w !== 'u' || empty($board['bo_table']) || (int) $wr_id < 1) {
            return;
        }

        eottae_translation_cache_delete($board['bo_table'], (int) $wr_id);
        eottae_translation_job_delete($board['bo_table'], (int) $wr_id);
    }
}

if (!function_exists('eottae_translation_on_write_update_prewarm_after')) {
    function eottae_translation_on_write_update_prewarm_after($board, $wr_id, $w, $qstr = '', $redirect_url = '')
    {
        if (empty($board['bo_table']) || (int) $wr_id < 1) {
            return;
        }

        eottae_translation_enqueue_post($board['bo_table'], (int) $wr_id);
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
        $extras = is_array($args['extras'] ?? null) ? $args['extras'] : array();
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
            ."본문:\n{$content}\n";

        if ($extras) {
            $labels = eottae_translation_extra_labels((string) ($args['bo_table'] ?? ''));
            $prompt .= "\n추가 정보:\n";
            foreach ($extras as $key => $value) {
                $label = isset($labels[$key]) ? $labels[$key] : $key;
                $prompt .= "{$label}:\n{$value}\n";
            }
        }

        $json_format = "{\n  \"translatedTitle\": \"\",\n  \"translatedContent\": \"\"";
        if ($extras) {
            $extra_json = array();
            foreach (array_keys($extras) as $extra_key) {
                $extra_json[] = '"'.addslashes($extra_key).'": ""';
            }
            $json_format .= ",\n  \"translatedExtras\": {\n    ".implode(",\n    ", $extra_json)."\n  }";
        }
        $json_format .= "\n}";
        $prompt .= "\n반환 형식은 JSON으로만 해라.\n\n".$json_format;

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

        $response = array(
            'success' => true,
            'translatedTitle' => $translated_title,
            'translatedContent' => $translated_content,
            'provider' => 'openai',
        );
        if ($extras && is_array($translated['translatedExtras'] ?? null)) {
            $response['translatedExtras'] = eottae_translation_sanitize_extras(
                $translated['translatedExtras'],
                (string) ($args['bo_table'] ?? '')
            );
        }

        return $response;
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

if (!function_exists('eottae_list_translation_enqueue_assets')) {
    function eottae_list_translation_enqueue_assets()
    {
        static $enqueued = false;
        if ($enqueued) {
            return;
        }
        $enqueued = true;

        $js = G5_PATH.'/js/eottae-list-translation.js';
        if (is_file($js)) {
            add_javascript('<script src="'.G5_JS_URL.'/eottae-list-translation.js?ver='.(int) filemtime($js).'" defer></script>', 32);
        }
    }
}

if (!function_exists('eottae_translation_target_button_label')) {
    /**
     * 번역 보기 버튼 — 각 대상 언어로 고정 표기
     */
    function eottae_translation_target_button_label($lang)
    {
        $lang = eottae_translation_normalize_language($lang, '');
        $labels = array(
            'en' => 'English View',
            'ja' => '日本語表示',
            'zh' => '中文查看',
        );

        return $labels[$lang] ?? strtoupper($lang).' View';
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
        $targets = array('en', 'ja', 'zh');

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
                <?php foreach ($targets as $lang) { ?>
                <button type="button" class="post-translation__btn" data-translation-target="<?php echo get_text($lang); ?>"><?php echo get_text(eottae_translation_target_button_label($lang)); ?></button>
                <?php } ?>
            </div>
            <p class="post-translation__status" data-translation-status role="status" aria-live="polite" hidden></p>
            <p class="post-translation__notice" data-translation-notice data-i18n="translation.notice" hidden>이 번역은 자동 번역으로 제공됩니다. 일부 표현이 자연스럽지 않을 수 있습니다.</p>
        </div>
        <?php
        return trim(ob_get_clean());
    }
}

if (!function_exists('eottae_translation_cron_key')) {
    function eottae_translation_cron_key()
    {
        $key = trim(eottae_translation_secret('translation_cron_key', array('TRANSLATION_CRON_KEY'), ''));
        if ($key !== '') {
            return $key;
        }

        if (function_exists('g5site_cfg')) {
            foreach (array('translation_cron_key', 'talkroom_ai_cron_key') as $cfg_key) {
                $fallback = trim((string) g5site_cfg($cfg_key, ''));
                if ($fallback !== '') {
                    return $fallback;
                }
            }
        }

        return '';
    }
}

if (!function_exists('eottae_translation_verify_cron_key')) {
    function eottae_translation_verify_cron_key($provided_key)
    {
        $expected = eottae_translation_cron_key();
        if ($expected === '') {
            return false;
        }

        return hash_equals($expected, (string) $provided_key);
    }
}

if (!function_exists('eottae_translation_traffic_tick_config')) {
    function eottae_translation_traffic_tick_config()
    {
        $enabled_flag = strtolower(eottae_translation_secret('translation_traffic_tick_enabled', array('TRANSLATION_TRAFFIC_TICK_ENABLED'), ''));
        if ($enabled_flag === '') {
            if (function_exists('g5site_cfg_bool')) {
                $enabled = g5site_cfg_bool('translation_traffic_tick_enabled', true);
            } elseif (function_exists('g5site_cfg')) {
                $cfg_enabled = strtolower(trim((string) g5site_cfg('translation_traffic_tick_enabled', '1')));
                $enabled = !in_array($cfg_enabled, array('0', 'false', 'no', 'off'), true);
            } else {
                $enabled = true;
            }
        } else {
            $enabled = !in_array($enabled_flag, array('0', 'false', 'no', 'off'), true);
        }

        if (!eottae_translation_auto_prewarm_enabled()) {
            $enabled = false;
        }

        $interval = (int) eottae_translation_secret('translation_traffic_tick_interval', array('TRANSLATION_TRAFFIC_TICK_INTERVAL'), '');
        if ($interval < 1 && function_exists('g5site_cfg')) {
            $interval = (int) g5site_cfg('translation_traffic_tick_interval', '90');
        }
        if ($interval < 1) {
            $interval = 90;
        }

        $limit = (int) eottae_translation_secret('translation_traffic_tick_limit', array('TRANSLATION_TRAFFIC_TICK_LIMIT'), '');
        if ($limit < 1 && function_exists('g5site_cfg')) {
            $limit = (int) g5site_cfg('translation_traffic_tick_limit', '2');
        }
        if ($limit < 1) {
            $limit = 2;
        }

        $percent = (int) eottae_translation_secret('translation_traffic_tick_percent', array('TRANSLATION_TRAFFIC_TICK_PERCENT'), '');
        if ($percent < 1 && function_exists('g5site_cfg')) {
            $percent = (int) g5site_cfg('translation_traffic_tick_percent', '5');
        }
        if ($percent < 1) {
            $percent = 5;
        }

        return array(
            'enabled' => $enabled,
            'interval' => max(30, min(600, $interval)),
            'limit' => max(1, min(10, $limit)),
            'trigger_percent' => max(1, min(100, $percent)),
        );
    }
}

if (!function_exists('eottae_translation_traffic_tick_lock_path')) {
    function eottae_translation_traffic_tick_lock_path()
    {
        $dir = G5_DATA_PATH.'/cache';
        if (!is_dir($dir)) {
            @mkdir($dir, G5_DIR_PERMISSION, true);
        }

        return $dir.'/translation_traffic_tick.lock';
    }
}

if (!function_exists('eottae_translation_traffic_tick_try_lock')) {
    function eottae_translation_traffic_tick_try_lock($interval_seconds = 90)
    {
        $path = eottae_translation_traffic_tick_lock_path();
        $fp = @fopen($path, 'c+');
        if (!$fp) {
            return false;
        }

        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            fclose($fp);

            return false;
        }

        $last = 0;
        $raw = stream_get_contents($fp);
        if ($raw !== false && trim($raw) !== '') {
            $last = (int) trim($raw);
        }
        $now = time();
        if ($last > 0 && ($now - $last) < max(30, (int) $interval_seconds)) {
            flock($fp, LOCK_UN);
            fclose($fp);

            return false;
        }

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, (string) $now);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }
}

if (!function_exists('eottae_translation_web_cron_urls')) {
    function eottae_translation_web_cron_urls()
    {
        if (!defined('G5_URL')) {
            return array();
        }

        $key = eottae_translation_cron_key();
        $key_qs = $key !== '' ? 'key='.rawurlencode($key).'&' : '';

        return array(
            'queue' => G5_URL.'/cron/sebu_translation_queue.php?'.$key_qs.'limit=5',
            'traffic_tick' => G5_URL.'/proc/eottae-translation-traffic-tick.php'.($key !== '' ? '?key='.rawurlencode($key) : ''),
        );
    }
}

if (!function_exists('eottae_translation_maybe_run_queue_tick')) {
    function eottae_translation_maybe_run_queue_tick(array $options = array())
    {
        $cfg = eottae_translation_traffic_tick_config();
        if (empty($cfg['enabled'])) {
            return array('ok' => false, 'ran' => false, 'reason' => 'disabled');
        }

        $provider = eottae_translation_provider();
        if (eottae_translation_api_key($provider) === '') {
            return array('ok' => false, 'ran' => false, 'reason' => 'no_api_key');
        }

        $limit = isset($options['limit']) ? (int) $options['limit'] : (int) $cfg['limit'];
        $limit = max(1, min(30, $limit));
        $force = !empty($options['force']);
        $dry_run = !empty($options['dry_run']);

        if (!$force && !$dry_run && !eottae_translation_traffic_tick_try_lock((int) $cfg['interval'])) {
            return array('ok' => true, 'ran' => false, 'reason' => 'locked');
        }

        if ($dry_run) {
            return array(
                'ok' => true,
                'ran' => false,
                'reason' => 'dry_run',
                'limit' => $limit,
            );
        }

        $summary = eottae_translation_run_queue($limit);

        return array(
            'ok' => true,
            'ran' => true,
            'reason' => 'processed',
            'limit' => $limit,
            'processed' => (int) ($summary['processed'] ?? 0),
            'succeeded' => (int) ($summary['succeeded'] ?? 0),
            'failed' => (int) ($summary['failed'] ?? 0),
        );
    }
}

if (!function_exists('eottae_translation_traffic_tick_shutdown')) {
    function eottae_translation_traffic_tick_shutdown($limit = 2)
    {
        if (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
        }
        @ignore_user_abort(true);
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }

        eottae_translation_run_queue(max(1, min(10, (int) $limit)));
    }
}

if (!function_exists('eottae_translation_on_traffic_tick_common_header')) {
    function eottae_translation_on_traffic_tick_common_header()
    {
        static $scheduled = false;
        if ($scheduled) {
            return;
        }

        $cfg = eottae_translation_traffic_tick_config();
        if (empty($cfg['enabled'])) {
            return;
        }

        if (defined('G5_IS_ADMIN') && G5_IS_ADMIN) {
            return;
        }
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            return;
        }
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return;
        }

        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        if ($uri !== '' && preg_match('#/(proc|cron|adm|setup)/#', $uri)) {
            return;
        }

        $provider = eottae_translation_provider();
        if (eottae_translation_api_key($provider) === '') {
            return;
        }

        if ((int) $cfg['trigger_percent'] < 100 && mt_rand(1, 100) > (int) $cfg['trigger_percent']) {
            return;
        }

        if (!eottae_translation_traffic_tick_try_lock((int) $cfg['interval'])) {
            return;
        }

        $scheduled = true;
        register_shutdown_function('eottae_translation_traffic_tick_shutdown', (int) $cfg['limit']);
    }
}
