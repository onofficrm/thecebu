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
     * 모바일·좁은 화면에서는 하단 글로벌 메뉴를 가리므로 챗봇 비활성
     */
    function onoff_chatbot_is_mobile_hidden()
    {
        if (defined('G5_IS_MOBILE') && G5_IS_MOBILE) {
            return true;
        }

        return function_exists('is_mobile') && is_mobile();
    }
}

if (!function_exists('onoff_chatbot_is_enabled')) {
    function onoff_chatbot_is_enabled()
    {
        if (onoff_chatbot_is_mobile_hidden()) {
            return false;
        }

        if (function_exists('g5site_cfg_bool')) {
            return g5site_cfg_bool('onoff_chatbot_enabled', true);
        }

        return onoff_chatbot_site_key() !== '';
    }
}

if (!function_exists('onoff_chatbot_script_html')) {
    function onoff_chatbot_script_html()
    {
        if (!onoff_chatbot_is_enabled()) {
            return '';
        }

        $site_key = onoff_chatbot_site_key();
        if ($site_key === '') {
            return '';
        }

        $safe_key = htmlspecialchars($site_key, ENT_QUOTES, 'UTF-8');

        return '<script src="https://chat.icrm.co.kr/widget.js" data-site-key="'.$safe_key.'" async></script>';
    }
}

if (!function_exists('onoff_chatbot_inject_html')) {
    function onoff_chatbot_inject_html($html)
    {
        $script = onoff_chatbot_script_html();
        if ($script === '' || stripos($html, 'chat.icrm.co.kr/widget.js') !== false) {
            return $html;
        }

        if (preg_match('#</body>#i', $html)) {
            return preg_replace('#</body>#i', $script.PHP_EOL.'</body>', $html, 1);
        }

        return $html.$script;
    }
}

echo onoff_chatbot_script_html();
