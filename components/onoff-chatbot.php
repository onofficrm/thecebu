<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('g5site_cfg') && is_file(G5_PATH . '/_site.config.php')) {
    include_once G5_PATH . '/_site.config.php';
}

if (!function_exists('onoff_chatbot_site_key')) {
    function onoff_chatbot_site_key()
    {
        if (!function_exists('g5site_cfg')) {
            return '';
        }

        return trim((string) g5site_cfg('onoff_chatbot_site_key', ''));
    }
}

if (!function_exists('onoff_chatbot_is_mobile_hidden')) {
    /**
     * 모바일·좁은 화면 — iCRM 위젯·상담 폴백 FAB 미노출
     */
    function onoff_chatbot_is_mobile_hidden()
    {
        if (defined('G5_IS_MOBILE') && G5_IS_MOBILE) {
            return true;
        }

        return function_exists('is_mobile') && is_mobile();
    }
}

if (!function_exists('onoff_chatbot_config_enabled')) {
    function onoff_chatbot_config_enabled()
    {
        if (!function_exists('g5site_cfg_bool')) {
            return onoff_chatbot_site_key() !== '';
        }

        return g5site_cfg_bool('onoff_chatbot_enabled', true);
    }
}

if (!function_exists('onoff_chatbot_bootstrap_page_url')) {
    function onoff_chatbot_bootstrap_page_url()
    {
        if (function_exists('g5site_cfg')) {
            $override = trim((string) g5site_cfg('onoff_chatbot_page_url', ''));
            if ($override !== '') {
                return $override;
            }
        }

        if (defined('G5_URL') && G5_URL !== '') {
            return rtrim(G5_URL, '/').'/';
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';

        return $host !== '' ? $scheme.'://'.$host.'/' : '';
    }
}

if (!function_exists('onoff_chatbot_origin_cache_path')) {
    function onoff_chatbot_origin_cache_path($site_key, $page_url)
    {
        $cache_dir = G5_DATA_PATH.'/cache';
        if (!is_dir($cache_dir)) {
            @mkdir($cache_dir, G5_DIR_PERMISSION);
            @chmod($cache_dir, G5_DIR_PERMISSION);
        }

        return $cache_dir.'/onoff-chatbot-'.md5($site_key.'|'.$page_url).'.json';
    }
}

if (!function_exists('onoff_chatbot_origin_check_ttl')) {
    function onoff_chatbot_origin_check_ttl()
    {
        if (function_exists('g5site_cfg')) {
            $ttl = (int) g5site_cfg('onoff_chatbot_origin_check_ttl', 300);
            return $ttl > 0 ? $ttl : 300;
        }

        return 300;
    }
}

if (!function_exists('onoff_chatbot_probe_bootstrap')) {
    /**
     * iCRM widget-frame.php 부트스트랩 API — origin_not_allowed 여부 확인
     */
    function onoff_chatbot_probe_bootstrap($site_key, $page_url)
    {
        $site_key = trim((string) $site_key);
        $page_url = trim((string) $page_url);
        if ($site_key === '' || $page_url === '') {
            return false;
        }

        $cache_path = onoff_chatbot_origin_cache_path($site_key, $page_url);
        if (is_file($cache_path)) {
            $cached_raw = @file_get_contents($cache_path);
            if (is_string($cached_raw) && $cached_raw !== '') {
                $cached = json_decode($cached_raw, true);
                if (is_array($cached)
                    && isset($cached['checked_at'], $cached['allowed'])
                    && (time() - (int) $cached['checked_at']) < onoff_chatbot_origin_check_ttl()) {
                    return (bool) $cached['allowed'];
                }
            }
        }

        $query = http_build_query(array(
            'site_key'    => $site_key,
            'visitor_key' => 'probe_'.substr(md5($site_key.$page_url), 0, 12),
            'page_url'    => $page_url,
            'referrer'    => '',
        ), '', '&', PHP_QUERY_RFC3986);
        $endpoint = 'https://chat.icrm.co.kr/widget-frame.php?'.$query;

        $response_body = '';
        if (function_exists('curl_init')) {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_HTTPHEADER     => array('Accept: application/json'),
            ));
            $response_body = (string) curl_exec($ch);
            if (PHP_VERSION_ID < 80500) {
                curl_close($ch);
            }
        } else {
            $context = stream_context_create(array(
                'http' => array(
                    'method'  => 'GET',
                    'timeout' => 8,
                    'header'  => "Accept: application/json\r\n",
                ),
            ));
            $response_body = @file_get_contents($endpoint, false, $context);
            $response_body = is_string($response_body) ? $response_body : '';
        }

        $allowed = false;
        $payload = json_decode($response_body, true);
        if (is_array($payload) && !empty($payload['ok'])) {
            $allowed = true;
        }

        @file_put_contents($cache_path, json_encode(array(
            'checked_at' => time(),
            'allowed'    => $allowed,
            'page_url'   => $page_url,
        ), JSON_UNESCAPED_UNICODE));

        return $allowed;
    }
}

if (!function_exists('onoff_chatbot_icrm_ready')) {
    function onoff_chatbot_icrm_ready()
    {
        if (!onoff_chatbot_config_enabled()) {
            return false;
        }

        $site_key = onoff_chatbot_site_key();
        if ($site_key === '') {
            return false;
        }

        if (function_exists('g5site_cfg_bool') && g5site_cfg_bool('onoff_chatbot_skip_origin_check', false)) {
            return true;
        }

        return onoff_chatbot_probe_bootstrap($site_key, onoff_chatbot_bootstrap_page_url());
    }
}

if (!function_exists('onoff_chatbot_use_icrm_widget')) {
    function onoff_chatbot_use_icrm_widget()
    {
        if (onoff_chatbot_is_mobile_hidden()) {
            return false;
        }

        return onoff_chatbot_icrm_ready();
    }
}

if (!function_exists('onoff_chatbot_use_fallback')) {
    function onoff_chatbot_use_fallback()
    {
        if (onoff_chatbot_is_mobile_hidden()) {
            return false;
        }

        if (!onoff_chatbot_config_enabled()) {
            return false;
        }

        if (onoff_chatbot_site_key() === '') {
            return false;
        }

        return !onoff_chatbot_use_icrm_widget();
    }
}

if (!function_exists('onoff_chatbot_is_enabled')) {
    function onoff_chatbot_is_enabled()
    {
        return onoff_chatbot_use_icrm_widget();
    }
}

if (!function_exists('onoff_chatbot_script_html')) {
    function onoff_chatbot_script_html()
    {
        if (!onoff_chatbot_use_icrm_widget()) {
            return '';
        }

        $site_key = onoff_chatbot_site_key();
        $safe_key = htmlspecialchars($site_key, ENT_QUOTES, 'UTF-8');

        return '<script src="https://chat.icrm.co.kr/widget.js" data-site-key="'.$safe_key.'" async></script>';
    }
}

if (!function_exists('onoff_chatbot_consult_modal_html')) {
    function onoff_chatbot_consult_modal_html()
    {
        if (!is_file(G5_PATH.'/components/consult-modal.php')) {
            return '';
        }

        ob_start();
        include G5_PATH.'/components/consult-modal.php';

        return (string) ob_get_clean();
    }
}

if (!function_exists('onoff_chatbot_builder_support_html')) {
    function onoff_chatbot_builder_support_html()
    {
        $parts = array();

        if (defined('G5_CSS_URL')) {
            $custom_css = G5_PATH.'/css/custom.css';
            $custom_css_url = G5_CSS_URL.'/custom.css';
            if (is_file($custom_css)) {
                $custom_css_url .= '?ver='.(int) filemtime($custom_css);
            }
            if (stripos($custom_css_url, 'custom.css') !== false) {
                $parts[] = '<link rel="stylesheet" href="'.htmlspecialchars($custom_css_url, ENT_QUOTES, 'UTF-8').'">';
            }
        }

        if (defined('G5_JS_URL')) {
            $fallback_js = G5_PATH.'/js/onoff-chatbot-fallback.js';
            $fallback_js_url = G5_JS_URL.'/onoff-chatbot-fallback.js';
            if (is_file($fallback_js)) {
                $fallback_js_url .= '?ver='.(int) filemtime($fallback_js);
            }
            $parts[] = '<script src="'.htmlspecialchars($fallback_js_url, ENT_QUOTES, 'UTF-8').'" defer></script>';
        }

        return implode(PHP_EOL, $parts);
    }
}

if (!function_exists('onoff_chatbot_fallback_label')) {
    function onoff_chatbot_fallback_label()
    {
        if (function_exists('g5site_cfg')) {
            return trim((string) g5site_cfg('consultation_text', '상담문의'));
        }

        return '상담문의';
    }
}

if (!function_exists('onoff_chatbot_fallback_html')) {
    function onoff_chatbot_fallback_html($include_builder_assets = false)
    {
        if (!onoff_chatbot_use_fallback()) {
            return '';
        }

        $label = onoff_chatbot_fallback_label();
        $safe_label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $html = '';

        if ($include_builder_assets) {
            $html .= onoff_chatbot_builder_support_html().PHP_EOL;
            $html .= onoff_chatbot_consult_modal_html().PHP_EOL;
        }

        $html .= '<div class="onoff-chatbot-fallback" data-onoff-chatbot-fallback="1">'
            .'<button type="button" class="onoff-chatbot-fallback__launcher consult-modal-open" data-target="#cmpConsultModal" aria-label="'.$safe_label.' 열기">'
            .'<span class="onoff-chatbot-fallback__icon" aria-hidden="true">'.onoff_chatbot_fallback_icon_html().'</span>'
            .'<span class="onoff-chatbot-fallback__text">'.$safe_label.'</span>'
            .'</button>'
            .'</div>';

        if (!$include_builder_assets) {
            $html .= onoff_chatbot_fallback_script_html();
        }

        return $html;
    }
}

if (!function_exists('onoff_chatbot_assets_html')) {
    function onoff_chatbot_assets_html($include_builder_assets = false)
    {
        $parts = array();

        $script = onoff_chatbot_script_html();
        if ($script !== '') {
            $parts[] = $script;
        }

        $fallback = onoff_chatbot_fallback_html($include_builder_assets);
        if ($fallback !== '') {
            $parts[] = $fallback;
        }

        return implode(PHP_EOL, array_filter($parts));
    }
}

if (!function_exists('onoff_chatbot_strip_widget_scripts')) {
    function onoff_chatbot_strip_widget_scripts($html)
    {
        if (!is_string($html) || $html === '') {
            return $html;
        }

        $html = preg_replace('#<script[^>]+chat\.icrm\.co\.kr/widget\.js[^>]*>\s*</script>\s*#i', '', $html);
        $html = preg_replace('#<script[^>]+src=["\']https?://chat\.icrm\.co\.kr/widget\.js["\'][^>]*>\s*</script>\s*#i', '', $html);

        return $html;
    }
}

if (!function_exists('onoff_chatbot_filter_legacy_scripts')) {
    /**
     * 그누보드 cf_add_script 등 레거시 iCRM 삽입 제거 — origin 실패 시 widget.js 중복 방지
     */
    function onoff_chatbot_filter_legacy_scripts()
    {
        global $config;

        if (!is_array($config) || empty($config['cf_add_script'])) {
            return;
        }

        if (!onoff_chatbot_use_icrm_widget()) {
            $config['cf_add_script'] = onoff_chatbot_strip_widget_scripts((string) $config['cf_add_script']);
        }
    }
}

if (!function_exists('onoff_chatbot_on_pre_head')) {
    function onoff_chatbot_on_pre_head()
    {
        if (!onoff_chatbot_config_enabled()) {
            return;
        }

        onoff_chatbot_filter_legacy_scripts();
    }
}

if (!function_exists('onoff_chatbot_fallback_icon_html')) {
    function onoff_chatbot_fallback_icon_html()
    {
        return '<svg class="onoff-chatbot-fallback__svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false">'
            .'<path fill="currentColor" d="M4 4.5A2.5 2.5 0 0 1 6.5 2h11A2.5 2.5 0 0 1 20 4.5v8A2.5 2.5 0 0 1 17.5 15H11l-4.2 3.15a1 1 0 0 1-1.55-.83V15H6.5A2.5 2.5 0 0 1 4 12.5v-8Z"/>'
            .'<circle cx="9" cy="9.5" r="1.1" fill="#2563eb"/>'
            .'<circle cx="12" cy="9.5" r="1.1" fill="#2563eb"/>'
            .'<circle cx="15" cy="9.5" r="1.1" fill="#2563eb"/>'
            .'</svg>';
    }
}

if (!function_exists('onoff_chatbot_fallback_script_html')) {
    function onoff_chatbot_fallback_script_html()
    {
        if (!defined('G5_JS_URL')) {
            return '';
        }

        $fallback_js = G5_PATH.'/js/onoff-chatbot-fallback.js';
        $fallback_js_url = G5_JS_URL.'/onoff-chatbot-fallback.js';
        if (is_file($fallback_js)) {
            $fallback_js_url .= '?ver='.(int) filemtime($fallback_js);
        }

        return '<script src="'.htmlspecialchars($fallback_js_url, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }
}

if (!function_exists('onoff_chatbot_inject_html')) {
    function onoff_chatbot_inject_html($html)
    {
        if (!is_string($html) || $html === '') {
            return $html;
        }

        $html = onoff_chatbot_strip_widget_scripts($html);
        $inject = onoff_chatbot_assets_html(true);
        if ($inject === '') {
            return $html;
        }

        if (preg_match('#</body>#i', $html)) {
            return preg_replace('#</body>#i', $inject.PHP_EOL.'</body>', $html, 1);
        }

        return $html.$inject;
    }
}

if (!defined('ONOFF_CHATBOT_EVENTS_REGISTERED')) {
    define('ONOFF_CHATBOT_EVENTS_REGISTERED', true);
    if (function_exists('add_event')) {
        add_event('pre_head', 'onoff_chatbot_on_pre_head', 3);
    }
}

if (!defined('ONOFF_CHATBOT_LIBRARY_ONLY') && !defined('ONOFF_CHATBOT_ASSETS_EMITTED')) {
    define('ONOFF_CHATBOT_ASSETS_EMITTED', true);
    echo onoff_chatbot_assets_html(false);
}
