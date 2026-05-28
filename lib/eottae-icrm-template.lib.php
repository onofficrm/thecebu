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
            '/\bicrm-template\b|data-icrm-template|data-design-template|data-icrm-generated/i',
            $html
        );
    }
}

if (!function_exists('eottae_icrm_content_needs_html')) {
    /**
     * wr_option에 html 플래그가 없어도 iCRM HTML 본문은 HTML로 렌더링
     */
    function eottae_icrm_content_needs_html($content)
    {
        return eottae_icrm_content_has_template($content);
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

        if (eottae_icrm_content_has_template($write_row['wr_content'])) {
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
        if (!eottae_icrm_content_has_template($html)) {
            return;
        }

        if (!is_object($config) || !method_exists($config, 'getHTMLDefinition')) {
            return;
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

        $config->set('CSS.Trusted', true);
        $config->set('HTML.Trusted', true);
    }
}
