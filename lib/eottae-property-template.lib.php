<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_estate_board_table')) {
    function eottae_estate_board_table()
    {
        return defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate';
    }
}

if (!function_exists('eottae_is_estate_board')) {
    /**
     * 부동산 전용 게시판 (bo_table=estate)
     */
    function eottae_is_estate_board($bo_table)
    {
        $estate = eottae_estate_board_table();
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return $bo_table !== '' && $bo_table === $estate;
    }
}

if (!function_exists('eottae_property_template_load_assets')) {
    function eottae_property_template_load_assets()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        $css_path = G5_PATH.'/css/eottae-property-template.css';
        $js_path = G5_PATH.'/js/eottae-property-template.js';
        $css_ver = is_file($css_path) ? (int) filemtime($css_path) : 0;
        $js_ver = is_file($js_path) ? (int) filemtime($js_path) : 0;

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-property-template.css?ver='.$css_ver.'">', 25);
        add_javascript('<script src="'.G5_JS_URL.'/eottae-property-template.js?ver='.$js_ver.'" defer></script>', 25);
    }
}
