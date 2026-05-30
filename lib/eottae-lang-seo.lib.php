<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_lang_seo_config_resolve')) {
    function eottae_lang_seo_config_resolve()
    {
        $enabled = false;
        if (function_exists('eottae_translation_secret')) {
            $flag = strtolower(eottae_translation_secret('lang_seo_enabled', array('LANG_SEO_ENABLED'), '0'));
            $enabled = in_array($flag, array('1', 'true', 'yes', 'on'), true);
        } elseif (function_exists('g5site_cfg')) {
            $enabled = (bool) g5site_cfg('lang_seo_enabled', false);
        }

        return array(
            'enabled' => $enabled,
            'prefixes' => array('en', 'ja', 'zh'),
            'default_language' => 'ko',
            'index_auto_translations' => false,
            'manual_review_required' => true,
            'hreflang_map' => array(
                'ko' => 'ko',
                'en' => 'en',
                'ja' => 'ja',
                'zh' => 'zh-Hans',
            ),
        );
    }
}

if (!function_exists('eottae_lang_seo_enabled')) {
    function eottae_lang_seo_enabled()
    {
        static $enabled = null;
        if ($enabled !== null) {
            return $enabled;
        }

        $config = function_exists('eottae_lang_seo_config') ? eottae_lang_seo_config() : array();

        return $enabled = !empty($config['enabled']);
    }
}

if (!function_exists('eottae_lang_seo_auto_route_enabled')) {
    function eottae_lang_seo_auto_route_enabled()
    {
        static $enabled = null;
        if ($enabled !== null) {
            return $enabled;
        }

        if (function_exists('eottae_translation_secret')) {
            $flag = strtolower(eottae_translation_secret('lang_seo_auto_route', array('LANG_SEO_AUTO_ROUTE'), '1'));

            return $enabled = in_array($flag, array('1', 'true', 'yes', 'on'), true);
        }

        return $enabled = true;
    }
}

if (!function_exists('eottae_lang_seo_request_path')) {
    function eottae_lang_seo_request_path()
    {
        $path = (string) strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?');
        if (defined('G5_URL')) {
            $base_path = (string) parse_url(G5_URL, PHP_URL_PATH);
            if ($base_path !== '' && $base_path !== '/') {
                $path = preg_replace('#^'.preg_quote($base_path, '#').'#', '', $path);
            }
        }

        return '/'.ltrim($path, '/');
    }
}

if (!function_exists('eottae_lang_seo_detect_accept_language')) {
    function eottae_lang_seo_detect_accept_language()
    {
        $header = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? (string) $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
        if ($header === '') {
            return '';
        }

        $candidates = array();
        foreach (explode(',', $header) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $pieces = explode(';', $part);
            $tag = strtolower(trim((string) ($pieces[0] ?? '')));
            $q = 1.0;
            if (isset($pieces[1]) && preg_match('/q=([0-9.]+)/', (string) $pieces[1], $matches)) {
                $q = (float) $matches[1];
            }
            if ($tag !== '') {
                $candidates[] = array('tag' => $tag, 'q' => $q);
            }
        }

        usort($candidates, function ($a, $b) {
            if ($a['q'] === $b['q']) {
                return 0;
            }

            return ($a['q'] > $b['q']) ? -1 : 1;
        });

        foreach ($candidates as $candidate) {
            $normalized = function_exists('eottae_lang_normalize')
                ? eottae_lang_normalize($candidate['tag'], '')
                : preg_replace('/[^a-z]/', '', $candidate['tag']);
            if ($normalized !== '' && isset(eottae_lang_supported()[$normalized])) {
                return $normalized;
            }
        }

        return '';
    }
}

if (!function_exists('eottae_lang_seo_resolve_visitor_language')) {
    function eottae_lang_seo_resolve_visitor_language()
    {
        global $is_member, $member;

        if (!empty($is_member) && function_exists('eottae_member_preferred_language_get')) {
            $preferred = eottae_member_preferred_language_get($member);
            if ($preferred !== '') {
                return $preferred;
            }
        }

        $cookie_key = 'cebuatteLanguage';
        $cookie = isset($_COOKIE[$cookie_key]) ? (string) $_COOKIE[$cookie_key] : '';
        if ($cookie !== '' && function_exists('eottae_lang_normalize')) {
            $normalized = eottae_lang_normalize($cookie, '');
            if ($normalized !== '') {
                return $normalized;
            }
        }

        return eottae_lang_seo_detect_accept_language();
    }
}

if (!function_exists('eottae_lang_seo_should_auto_redirect')) {
    function eottae_lang_seo_should_auto_redirect()
    {
        if (!eottae_lang_seo_auto_route_enabled()) {
            return false;
        }

        if (!empty($_GET['eottae_lang'])) {
            return false;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            return false;
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return false;
        }

        $path = eottae_lang_seo_request_path();
        if (preg_match('#^/(adm|proc|cron|api|setup)(/|$)#', $path)) {
            return false;
        }

        $config = eottae_lang_seo_config();
        $prefixes = isset($config['prefixes']) ? (array) $config['prefixes'] : array('en', 'ja', 'zh');
        $relative = ltrim($path, '/');
        $prefix_pattern = implode('|', array_map(function ($code) {
            return preg_quote((string) $code, '#');
        }, $prefixes));
        if ($prefix_pattern !== '' && preg_match('#^('.$prefix_pattern.')(?:/|$)#', $relative)) {
            return false;
        }

        $preferred = eottae_lang_seo_resolve_visitor_language();
        $default = (string) ($config['default_language'] ?? 'ko');
        if ($preferred === '' || $preferred === $default) {
            return false;
        }

        if (!function_exists('eottae_lang_seo_uses_prefix') || !eottae_lang_seo_uses_prefix($preferred)) {
            return false;
        }

        return $preferred;
    }
}

if (!function_exists('eottae_lang_seo_maybe_auto_redirect')) {
    function eottae_lang_seo_maybe_auto_redirect()
    {
        $target = eottae_lang_seo_should_auto_redirect();
        if ($target === false || !is_string($target) || $target === '') {
            return;
        }

        $next_path = eottae_lang_seo_switch_path($target);
        $current_path = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        if ($next_path === '' || $next_path === $current_path) {
            return;
        }

        $cookie_path = '/';
        if (defined('G5_COOKIE_PATH') && G5_COOKIE_PATH !== '') {
            $cookie_path = G5_COOKIE_PATH;
        }

        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        setcookie('cebuatteLanguage', $target, time() + 365 * 86400, $cookie_path, '', $secure, false);

        goto_url($next_path);
    }
}

if (!function_exists('eottae_lang_seo_bootstrap_request')) {
    function eottae_lang_seo_bootstrap_request()
    {
        if (!eottae_lang_seo_enabled()) {
            return;
        }

        $config = eottae_lang_seo_config();
        $prefixes = isset($config['prefixes']) ? (array) $config['prefixes'] : array('en', 'ja', 'zh');
        $language = '';
        $path_has_prefix = false;

        if (!empty($_GET['eottae_lang'])) {
            $language = function_exists('eottae_lang_normalize')
                ? eottae_lang_normalize((string) $_GET['eottae_lang'], '')
                : preg_replace('/[^a-z]/', '', (string) $_GET['eottae_lang']);
        }

        if ($language === '' && !empty($_SERVER['REQUEST_URI'])) {
            $path = ltrim(eottae_lang_seo_request_path(), '/');
            $prefix_pattern = implode('|', array_map(function ($code) {
                return preg_quote((string) $code, '#');
            }, $prefixes));
            if ($prefix_pattern !== '' && preg_match('#^('.$prefix_pattern.')(?:/|$)#', $path, $matches)) {
                $language = (string) $matches[1];
                $path_has_prefix = true;
            }
        }

        if (!$path_has_prefix && $language === '') {
            eottae_lang_seo_maybe_auto_redirect();
        }

        if ($language === '' || !isset(eottae_lang_supported()[$language])) {
            $language = (string) ($config['default_language'] ?? 'ko');
        }

        $GLOBALS['eottae_lang_seo_language'] = $language;
    }
}

if (!function_exists('eottae_lang_seo_current')) {
    function eottae_lang_seo_current()
    {
        if (!eottae_lang_seo_enabled()) {
            return 'ko';
        }

        if (!empty($GLOBALS['eottae_lang_seo_language'])) {
            return function_exists('eottae_lang_normalize')
                ? eottae_lang_normalize($GLOBALS['eottae_lang_seo_language'], 'ko')
                : (string) $GLOBALS['eottae_lang_seo_language'];
        }

        return 'ko';
    }
}

if (!function_exists('eottae_lang_seo_hreflang_code')) {
    function eottae_lang_seo_hreflang_code($language)
    {
        $language = function_exists('eottae_lang_normalize') ? eottae_lang_normalize($language, 'ko') : 'ko';
        $config = eottae_lang_seo_config();
        $map = isset($config['hreflang_map']) ? (array) $config['hreflang_map'] : array();

        return isset($map[$language]) ? (string) $map[$language] : $language;
    }
}

if (!function_exists('eottae_lang_seo_uses_prefix')) {
    function eottae_lang_seo_uses_prefix($language)
    {
        $language = function_exists('eottae_lang_normalize') ? eottae_lang_normalize($language, 'ko') : 'ko';
        $config = eottae_lang_seo_config();

        if ($language === (string) ($config['default_language'] ?? 'ko')) {
            return false;
        }

        return in_array($language, (array) ($config['prefixes'] ?? array()), true);
    }
}

if (!function_exists('eottae_lang_seo_prefix_url')) {
    function eottae_lang_seo_prefix_url($url, $language = '')
    {
        if (!eottae_lang_seo_enabled() || $url === '') {
            return $url;
        }

        $language = $language !== ''
            ? (function_exists('eottae_lang_normalize') ? eottae_lang_normalize($language, 'ko') : $language)
            : eottae_lang_seo_current();

        if (!eottae_lang_seo_uses_prefix($language)) {
            return $url;
        }

        $parts = parse_url($url);
        if (empty($parts['scheme']) || empty($parts['host'])) {
            return $url;
        }

        $path = isset($parts['path']) ? (string) $parts['path'] : '/';
        $base_path = defined('G5_URL') ? (string) parse_url(G5_URL, PHP_URL_PATH) : '';
        if ($base_path !== '' && $base_path !== '/') {
            if (strpos($path, $base_path) === 0) {
                $path = substr($path, strlen($base_path));
            }
        }

        $path = ltrim($path, '/');
        $config = eottae_lang_seo_config();
        $prefixes = (array) ($config['prefixes'] ?? array());
        foreach ($prefixes as $prefix) {
            if ($path === $prefix || strpos($path, $prefix.'/') === 0) {
                return $url;
            }
        }

        $new_path = '/'.$language.($path !== '' ? '/'.$path : '/');
        if ($base_path !== '' && $base_path !== '/') {
            $new_path = rtrim($base_path, '/').$new_path;
        }

        $query = isset($parts['query']) && $parts['query'] !== '' ? '?'.$parts['query'] : '';

        return $parts['scheme'].'://'.$parts['host']
            .(isset($parts['port']) ? ':'.$parts['port'] : '')
            .$new_path
            .$query
            .(isset($parts['fragment']) && $parts['fragment'] !== '' ? '#'.$parts['fragment'] : '');
    }
}

if (!function_exists('eottae_lang_seo_strip_prefix_from_path')) {
    function eottae_lang_seo_strip_prefix_from_path($path)
    {
        $path = ltrim((string) $path, '/');
        $config = eottae_lang_seo_config();
        $prefixes = (array) ($config['prefixes'] ?? array());
        $prefix_pattern = implode('|', array_map(function ($code) {
            return preg_quote((string) $code, '#');
        }, $prefixes));

        if ($prefix_pattern !== '' && preg_match('#^('.$prefix_pattern.')/(.*)$#', $path, $matches)) {
            return (string) $matches[2];
        }

        return $path;
    }
}

if (!function_exists('eottae_lang_seo_switch_path')) {
    function eottae_lang_seo_switch_path($target_language)
    {
        $target_language = function_exists('eottae_lang_normalize')
            ? eottae_lang_normalize($target_language, 'ko')
            : 'ko';

        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $path = (string) strtok($uri, '?');
        $query = '';
        if (strpos($uri, '?') !== false) {
            $query = substr($uri, strpos($uri, '?'));
        }

        if (defined('G5_URL')) {
            $base_path = (string) parse_url(G5_URL, PHP_URL_PATH);
            if ($base_path !== '' && $base_path !== '/' && strpos($path, $base_path) === 0) {
                $path = substr($path, strlen($base_path));
            }
        }

        $relative = ltrim($path, '/');
        $relative = eottae_lang_seo_strip_prefix_from_path($relative);

        if (!eottae_lang_seo_uses_prefix($target_language)) {
            $new_path = $relative !== '' ? '/'.$relative : '/';
        } else {
            $new_path = '/'.$target_language.($relative !== '' ? '/'.$relative : '/');
        }

        if (defined('G5_URL')) {
            $base_path = (string) parse_url(G5_URL, PHP_URL_PATH);
            if ($base_path !== '' && $base_path !== '/') {
                $new_path = rtrim($base_path, '/').($new_path === '/' ? '/' : $new_path);
            }
        }

        return $new_path.$query;
    }
}

if (!function_exists('eottae_lang_seo_alternate_urls')) {
    function eottae_lang_seo_alternate_urls($canonical_url = '')
    {
        if (!eottae_lang_seo_enabled()) {
            return array();
        }

        if ($canonical_url === '' && function_exists('g5b_seo_current_url')) {
            $canonical_url = g5b_seo_current_url();
        }
        if ($canonical_url === '') {
            return array();
        }

        $alternates = array();
        foreach (array_keys(eottae_lang_supported()) as $language) {
            $alternates[$language] = eottae_lang_seo_prefix_url($canonical_url, $language);
        }

        return $alternates;
    }
}

if (!function_exists('eottae_lang_seo_build_hreflang_html')) {
    function eottae_lang_seo_build_hreflang_html($canonical_url = '')
    {
        if (!eottae_lang_seo_enabled()) {
            return '';
        }

        $alternates = eottae_lang_seo_alternate_urls($canonical_url);
        if (!$alternates) {
            return '';
        }

        $lines = array();
        foreach ($alternates as $language => $url) {
            if ($url === '') {
                continue;
            }
            $hreflang = eottae_lang_seo_hreflang_code($language);
            $lines[] = '<link rel="alternate" hreflang="'.htmlspecialchars($hreflang, ENT_QUOTES, 'UTF-8').'" href="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'">';
        }

        $default_lang = (string) (eottae_lang_seo_config()['default_language'] ?? 'ko');
        if (!empty($alternates[$default_lang])) {
            $lines[] = '<link rel="alternate" hreflang="x-default" href="'.htmlspecialchars($alternates[$default_lang], ENT_QUOTES, 'UTF-8').'">';
        }

        return $lines ? implode(PHP_EOL, $lines).PHP_EOL : '';
    }
}

if (!function_exists('eottae_lang_seo_filter_add_meta')) {
    function eottae_lang_seo_filter_add_meta($meta)
    {
        return $meta.eottae_lang_seo_build_hreflang_html();
    }
}

if (!function_exists('eottae_lang_seo_filter_buffer')) {
    function eottae_lang_seo_filter_buffer($buffer)
    {
        if (!eottae_lang_seo_enabled()) {
            return $buffer;
        }

        $lang = eottae_lang_seo_hreflang_code(eottae_lang_seo_current());
        $safe_lang = htmlspecialchars($lang, ENT_QUOTES, 'UTF-8');

        if (preg_match('#<html(\s[^>]*)?\slang="[^"]*"#i', $buffer)) {
            $replaced = preg_replace('#<html(\s[^>]*)?\slang="[^"]*"#i', '<html lang="'.$safe_lang.'"', $buffer, 1);
            if (is_string($replaced)) {
                return $replaced;
            }
        }

        $replaced = preg_replace('#<html(\s[^>]*)?>#i', '<html lang="'.$safe_lang.'"$1>', $buffer, 1);

        return is_string($replaced) ? $replaced : $buffer;
    }
}

if (!function_exists('eottae_lang_seo_filter_pretty_url')) {
    function eottae_lang_seo_filter_pretty_url($url, $folder = '', $no = '', $query_string = '', $action = '')
    {
        unset($folder, $no, $query_string, $action);

        if ($url === '' || !eottae_lang_seo_enabled()) {
            return $url;
        }

        return eottae_lang_seo_prefix_url($url, eottae_lang_seo_current());
    }
}

if (!function_exists('eottae_lang_seo_rewrite_pre_rules')) {
    function eottae_lang_seo_rewrite_pre_rules($rules, $get_path_url, $base_path, $return_string = false)
    {
        if (!eottae_lang_seo_enabled()) {
            return $rules;
        }

        $config = eottae_lang_seo_config();
        $prefixes = array_filter((array) ($config['prefixes'] ?? array()));
        if (!$prefixes) {
            return $rules;
        }

        $prefix = implode('|', array_map(function ($code) {
            return preg_quote((string) $code, '#');
        }, $prefixes));

        $lines = array(
            'RewriteRule ^('.$prefix.')/?$ index.php?eottae_lang=$1 [QSA,L]',
            'RewriteRule ^('.$prefix.')/(.*)$ $2?eottae_lang=$1 [QSA,L]',
        );

        return implode("\n", $lines).($rules !== '' ? "\n".$rules : '');
    }
}

if (!function_exists('eottae_lang_seo_nginx_pre_rules')) {
    function eottae_lang_seo_nginx_pre_rules($rules, $get_path_url, $base_path, $return_string = false)
    {
        if (!eottae_lang_seo_enabled()) {
            return $rules;
        }

        $config = eottae_lang_seo_config();
        $prefixes = array_filter((array) ($config['prefixes'] ?? array()));
        if (!$prefixes) {
            return $rules;
        }

        $prefix = implode('|', array_map(function ($code) {
            return preg_quote((string) $code, '#');
        }, $prefixes));

        $lines = array(
            'rewrite ^'.$base_path.'('.$prefix.')/?$ '.$base_path.'index.php?eottae_lang=$1 break;',
            'rewrite ^'.$base_path.'('.$prefix.')/(.*)$ '.$base_path.'$2?eottae_lang=$1 break;',
        );

        return implode("\n", $lines).($rules !== '' ? "\n".$rules : '');
    }
}

if (!function_exists('eottae_lang_seo_apply_page_context')) {
    function eottae_lang_seo_apply_page_context()
    {
        if (!eottae_lang_seo_enabled()) {
            return;
        }

        global $page_canonical, $page_robots;

        if (function_exists('g5b_seo_current_url')) {
            $current = g5b_seo_current_url();
            $page_canonical = eottae_lang_seo_prefix_url($current, eottae_lang_seo_current());
        }
    }
}

if (!function_exists('eottae_lang_seo_apply_board_view_robots')) {
    function eottae_lang_seo_apply_board_view_robots($board, $write, $wr_id)
    {
        if (!eottae_lang_seo_enabled() || empty($board['bo_table']) || (int) $wr_id < 1) {
            return;
        }

        $config = eottae_lang_seo_config();
        if (!empty($config['index_auto_translations'])) {
            return;
        }

        $current = eottae_lang_seo_current();
        $default = (string) ($config['default_language'] ?? 'ko');
        if ($current === $default) {
            return;
        }

        if (!is_array($write) || empty($write['wr_id'])) {
            global $page_robots;
            $page_robots = 'noindex,follow';
            return;
        }

        $source_language = function_exists('eottae_translation_detect_post_source_language')
            ? eottae_translation_detect_post_source_language($board['bo_table'], $write)
            : 'ko';
        if ($current === $source_language) {
            return;
        }

        if (!function_exists('eottae_translation_cache_get')) {
            global $page_robots;
            $page_robots = 'noindex,follow';
            return;
        }

        if (!function_exists('eottae_translation_source_updated_at')) {
            return;
        }

        $source_updated_at = eottae_translation_source_updated_at($write);
        $cached = eottae_translation_cache_get($board['bo_table'], (int) $wr_id, $current, $source_updated_at);
        $reviewed = is_array($cached) && (string) ($cached['review_status'] ?? 'auto') === 'reviewed';

        if (!$reviewed && !empty($config['manual_review_required'])) {
            global $page_robots;
            $page_robots = 'noindex,follow';
        }
    }
}

if (!function_exists('eottae_lang_seo_build_translated_page_title')) {
    function eottae_lang_seo_build_translated_page_title($translated_title, $board, $write)
    {
        $translated_title = trim(strip_tags((string) $translated_title));
        if ($translated_title === '') {
            return '';
        }

        $site_name = function_exists('eottae_board_seo_site_name') ? eottae_board_seo_site_name() : '';
        if ($site_name === '') {
            global $config;
            if (isset($config['cf_title'])) {
                $site_name = trim(strip_tags((string) $config['cf_title']));
            }
        }

        $is_shop = !empty($board['bo_table'])
            && function_exists('eottae_is_shop_board')
            && eottae_is_shop_board($board['bo_table']);

        if ($is_shop) {
            return $site_name !== '' ? $translated_title.' | '.$site_name : $translated_title;
        }

        $board_label = function_exists('eottae_board_seo_board_label')
            ? eottae_board_seo_board_label($board)
            : '';

        if ($board_label !== '') {
            $meta_title = $translated_title.' > '.$board_label;
        } else {
            $meta_title = $translated_title;
        }

        if ($site_name !== '') {
            $meta_title .= ' | '.$site_name;
        }

        return $meta_title;
    }
}

if (!function_exists('eottae_lang_seo_build_translated_page_description')) {
    function eottae_lang_seo_build_translated_page_description($cached, $board)
    {
        if (!is_array($cached)) {
            return '';
        }

        $extras = function_exists('eottae_translation_decode_extras')
            ? eottae_translation_decode_extras($cached['translated_extras'] ?? '')
            : array();

        $is_shop = !empty($board['bo_table'])
            && function_exists('eottae_is_shop_board')
            && eottae_is_shop_board($board['bo_table']);

        if ($is_shop && !empty($extras['intro'])) {
            $intro = trim(strip_tags((string) $extras['intro']));
            if ($intro !== '') {
                if (function_exists('eottae_shop_seo_excerpt')) {
                    return eottae_shop_seo_excerpt($intro, 160);
                }

                return function_exists('eottae_translation_list_snippet')
                    ? eottae_translation_list_snippet($intro, 160)
                    : $intro;
            }
        }

        $content = trim(strip_tags((string) ($cached['translated_content'] ?? '')));
        if ($content === '') {
            return '';
        }

        return function_exists('eottae_translation_list_snippet')
            ? eottae_translation_list_snippet($content, 160)
            : $content;
    }
}

if (!function_exists('eottae_lang_seo_apply_board_view_meta')) {
    function eottae_lang_seo_apply_board_view_meta($board, $write, $wr_id)
    {
        if (!eottae_lang_seo_enabled() || empty($board['bo_table']) || (int) $wr_id < 1) {
            return;
        }

        $current = eottae_lang_seo_current();
        $default = (string) (eottae_lang_seo_config()['default_language'] ?? 'ko');
        if ($current === $default) {
            return;
        }

        if (!is_array($write) || empty($write['wr_id'])) {
            return;
        }

        $source_language = function_exists('eottae_translation_detect_post_source_language')
            ? eottae_translation_detect_post_source_language($board['bo_table'], $write)
            : 'ko';
        if ($current === $source_language) {
            return;
        }

        if (!function_exists('eottae_translation_cache_get')) {
            return;
        }

        if (!function_exists('eottae_translation_source_updated_at')) {
            return;
        }

        $source_updated_at = eottae_translation_source_updated_at($write);
        $cached = eottae_translation_cache_get($board['bo_table'], (int) $wr_id, $current, $source_updated_at);
        if (!is_array($cached) || (string) ($cached['review_status'] ?? 'auto') !== 'reviewed') {
            return;
        }

        global $page_title, $page_description, $g5;

        $meta_title = eottae_lang_seo_build_translated_page_title($cached['translated_title'] ?? '', $board, $write);
        if ($meta_title !== '') {
            $page_title = $meta_title;
            $g5['title'] = strip_tags($meta_title);
        }

        $meta_description = eottae_lang_seo_build_translated_page_description($cached, $board);
        if ($meta_description !== '') {
            $page_description = $meta_description;
        }
    }
}

if (!function_exists('eottae_lang_seo_init')) {
    function eottae_lang_seo_init()
    {
        static $initialized = false;
        if ($initialized) {
            return;
        }
        $initialized = true;

        eottae_lang_seo_bootstrap_request();
        eottae_lang_seo_apply_page_context();

        if (function_exists('add_replace')) {
            add_replace('html_process_add_meta', 'eottae_lang_seo_filter_add_meta', 20, 1);
            add_replace('html_process_buffer', 'eottae_lang_seo_filter_buffer', 20, 1);
            add_replace('get_pretty_url', 'eottae_lang_seo_filter_pretty_url', 999, 5);
        }
    }
}

if (!function_exists('eottae_lang_seo_landing_paths')) {
    function eottae_lang_seo_landing_paths()
    {
        $paths = array('/');

        if (defined('EOTTae_SHOP_TABLE')) {
            $paths[] = '/'.EOTTae_SHOP_TABLE;
        }
        if (defined('EOTTae_COMMUNITY_TABLE')) {
            $paths[] = '/'.EOTTae_COMMUNITY_TABLE;
        }

        return array_values(array_unique($paths));
    }
}

if (!function_exists('eottae_lang_seo_sitemap_entries')) {
    function eottae_lang_seo_sitemap_entries($limit = 200)
    {
        global $g5;

        if (!eottae_lang_seo_enabled()) {
            return array();
        }

        $entries = array();
        $site_url = defined('G5_URL') ? rtrim(G5_URL, '/') : '';

        foreach (eottae_lang_seo_landing_paths() as $path) {
            $loc = $site_url.$path;
            $entries[] = array(
                'loc' => $loc,
                'alternates' => eottae_lang_seo_alternate_urls($loc),
                'lastmod' => date('Y-m-d'),
            );
        }

        $boards = array();
        if (defined('EOTTae_SHOP_TABLE')) {
            $boards[] = EOTTae_SHOP_TABLE;
        }
        if (defined('EOTTae_COMMUNITY_TABLE')) {
            $boards[] = EOTTae_COMMUNITY_TABLE;
        }

        $limit = max(1, min(500, (int) $limit));
        $remaining = $limit;

        foreach ($boards as $bo_table) {
            if ($remaining < 1) {
                break;
            }

            $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
            if ($bo_table === '') {
                continue;
            }

            $write_table = $g5['write_prefix'].$bo_table;
            $result = sql_query(" select wr_id, wr_last, wr_datetime from `{$write_table}` where wr_is_comment = 0 order by wr_id desc limit {$remaining} ", false);
            while ($row = sql_fetch_array($result)) {
                $wr_id = (int) ($row['wr_id'] ?? 0);
                if ($wr_id < 1) {
                    continue;
                }

                $loc = function_exists('get_pretty_url') ? get_pretty_url($bo_table, $wr_id) : $site_url.'/bbs/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id;
                $last = (string) ($row['wr_last'] ?? $row['wr_datetime'] ?? '');
                $lastmod = $last !== '' && $last !== '0000-00-00 00:00:00' ? substr($last, 0, 10) : date('Y-m-d');

                $entries[] = array(
                    'loc' => $loc,
                    'alternates' => eottae_lang_seo_alternate_urls($loc),
                    'lastmod' => $lastmod,
                );
                $remaining -= 1;
                if ($remaining < 1) {
                    break;
                }
            }
        }

        return $entries;
    }
}

if (!function_exists('eottae_lang_seo_render_sitemap_xml')) {
    function eottae_lang_seo_render_sitemap_xml($entries)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ((array) $entries as $entry) {
            $loc = htmlspecialchars((string) ($entry['loc'] ?? ''), ENT_XML1, 'UTF-8');
            if ($loc === '') {
                continue;
            }

            $xml .= "  <url>\n";
            $xml .= '    <loc>'.$loc."</loc>\n";
            if (!empty($entry['lastmod'])) {
                $xml .= '    <lastmod>'.htmlspecialchars((string) $entry['lastmod'], ENT_XML1, 'UTF-8')."</lastmod>\n";
            }

            foreach ((array) ($entry['alternates'] ?? array()) as $language => $alt_url) {
                if ($alt_url === '') {
                    continue;
                }
                $hreflang = eottae_lang_seo_hreflang_code($language);
                $xml .= '    <xhtml:link rel="alternate" hreflang="'.htmlspecialchars($hreflang, ENT_XML1, 'UTF-8').'" href="'.htmlspecialchars((string) $alt_url, ENT_XML1, 'UTF-8')."\" />\n";
            }

            $default_lang = (string) (eottae_lang_seo_config()['default_language'] ?? 'ko');
            if (!empty($entry['alternates'][$default_lang])) {
                $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="'.htmlspecialchars((string) $entry['alternates'][$default_lang], ENT_XML1, 'UTF-8')."\" />\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>\n";

        return $xml;
    }
}
