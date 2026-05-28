<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_icrm_content_has_template')) {
    /**
     * iCRM 템플릿 마크업 포함 여부 (저장 본문 기준)
     */
    function eottae_icrm_content_has_template($html)
    {
        $html = (string) $html;
        if ($html === '') {
            return false;
        }

        return (bool) preg_match(
            '/\bicrm-template\b|\bicrm-(?:section|content|cta|facility(?:-grid|-card)?)\b|data-icrm(?:-template|-generated)?|data-design-template/i',
            $html
        );
    }
}

if (!function_exists('eottae_icrm_content_is_rich_html')) {
    /**
     * iCRM 마커 없이 저장된 HTML 템플릿( style + 구조화 markup ) 감지
     */
    function eottae_icrm_content_is_rich_html($html)
    {
        $html = trim((string) $html);
        if ($html === '' || strpos($html, '<') === false) {
            return false;
        }

        if (stripos($html, '<style') === false) {
            return false;
        }

        return (bool) preg_match('/<(div|section|article)\b/i', $html);
    }
}

if (!function_exists('eottae_icrm_content_should_preserve_html')) {
    function eottae_icrm_content_should_preserve_html($html)
    {
        return eottae_icrm_content_has_template($html) || eottae_icrm_content_is_rich_html($html);
    }
}

if (!function_exists('eottae_icrm_content_needs_html')) {
    /**
     * wr_option에 html 플래그가 없어도 iCRM HTML 본문은 HTML로 렌더링
     */
    function eottae_icrm_content_needs_html($content)
    {
        return eottae_icrm_content_should_preserve_html($content);
    }
}

if (!function_exists('eottae_icrm_html_purifier_result')) {
    /**
     * HTMLPurifier 인스턴스 캐시(일반/관리자)와 무관하게 iCRM 템플릿 HTML 유지
     *
     * @param string $purified
     * @param object $purifier
     * @param string $html
     */
    function eottae_icrm_html_purifier_result($purified, $purifier, $html)
    {
        if (!eottae_icrm_content_should_preserve_html($html)) {
            return $purified;
        }

        return (string) $html;
    }
}

if (!function_exists('eottae_icrm_enqueue_template_styles')) {
    function eottae_icrm_enqueue_template_styles()
    {
        static $enqueued = false;

        if ($enqueued) {
            return;
        }

        $css_path = G5_PATH.'/css/eottae-icrm-template.css';
        if (!is_file($css_path)) {
            return;
        }

        $enqueued = true;
        $ver = (int) @filemtime($css_path);

        add_stylesheet(
            '<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-icrm-template.css?ver='.$ver.'">',
            120
        );
    }
}

if (!function_exists('eottae_icrm_maybe_enqueue_template_styles')) {
    /**
     * @param array<string, mixed>|null $write_row
     */
    function eottae_icrm_maybe_enqueue_template_styles($write_row = null)
    {
        if (!is_array($write_row) || empty($write_row['wr_content'])) {
            return;
        }

        if (eottae_icrm_content_should_preserve_html($write_row['wr_content'])) {
            eottae_icrm_enqueue_template_styles();
        }
    }
}

if (!function_exists('eottae_icrm_html_purifier_config')) {
    /**
     * iCRM 본문 — class·style·data-icrm-* 속성 유지
     */
    function eottae_icrm_html_purifier_config($config, $args)
    {
        $html = isset($args['html']) ? (string) $args['html'] : '';
        if (!eottae_icrm_content_should_preserve_html($html)) {
            return;
        }

        if (!is_object($config) || !method_exists($config, 'getHTMLDefinition')) {
            return;
        }

        if (method_exists($config, 'isFinalized') && $config->isFinalized()) {
            return;
        }

        /*
         * getHTMLDefinition(true)는 설정을 finalization 상태로 만들 수 있으므로,
         * directive 변경은 반드시 정의 객체를 요청하기 전에 끝낸다.
         */
        if (method_exists($config, 'set')) {
            $config->set('CSS.Trusted', true);
            $config->set('HTML.Trusted', true);
        }

        $def = $config->getHTMLDefinition(true);
        if (!$def) {
            return;
        }

        $elements = array('div', 'section', 'article', 'a', 'span', 'p', 'ul', 'li', 'h1', 'h2', 'h3', 'img');
        $data_attrs = array(
            'data-icrm-template',
            'data-design-template',
            'data-icrm-generated',
        );

        foreach ($elements as $el) {
            foreach ($data_attrs as $attr) {
                $def->addAttribute($el, $attr, 'Text');
            }
        }

        $property_attrs = array(
            'data-property-field',
            'data-event-field',
            'data-estate-template-json',
            'data-template-json',
            'data-icrm-data',
        );
        foreach ($elements as $el) {
            foreach ($property_attrs as $attr) {
                $def->addAttribute($el, $attr, 'Text');
            }
        }
    }
}

if (!function_exists('eottae_icrm_extract_embedded_json')) {
    /**
     * iCRM·부동산 글쓰기 HTML에 숨겨 둔 JSON 페이로드 추출
     *
     * @return string|null raw JSON string
     */
    function eottae_icrm_extract_embedded_json($html)
    {
        $html = (string) $html;
        if ($html === '') {
            return null;
        }

        $patterns = array(
            '/<script[^>]+type=["\']application\/json["\'][^>]*>(.*?)<\/script>/is',
            '/<input[^>]+(?:id|name)=["\']estate_template_json["\'][^>]+value=["\']([^"\']+)["\']/i',
            '/<input[^>]+value=["\']([^"\']+)["\'][^>]+(?:id|name)=["\']estate_template_json["\']/i',
            '/data-(?:estate-template-json|template-json|icrm-data)=["\']([^"\']+)["\']/i',
            '/<!--\s*estate-template-json\s*:\s*(\{.*?\})\s*-->/is',
        );

        foreach ($patterns as $pattern) {
            if (!preg_match($pattern, $html, $m)) {
                continue;
            }
            $raw = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($raw !== '' && $raw[0] === '{') {
                return $raw;
            }
        }

        return null;
    }
}
