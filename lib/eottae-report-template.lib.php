<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_report_template_load_assets')) {
    function eottae_report_template_load_assets()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        if (!function_exists('eottae_report_types') && is_file(G5_LIB_PATH.'/eottae-report.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-report.lib.php';
        }

        $css_path = G5_PATH.'/css/eottae-report-template.css';
        $js_path = G5_PATH.'/js/eottae-report-write.js';
        $css_ver = is_file($css_path) ? (int) filemtime($css_path) : 0;
        $js_ver = is_file($js_path) ? (int) filemtime($js_path) : 0;

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-report-template.css?ver='.$css_ver.'">', 25);
        add_javascript('<script src="'.G5_JS_URL.'/eottae-report-write.js?ver='.$js_ver.'" defer></script>', 25);
    }
}

if (!function_exists('eottae_report_board_load_assets')) {
    function eottae_report_board_load_assets()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        eottae_report_template_load_assets();

        $css_path = G5_PATH.'/css/eottae-report-board.css';
        $css_ver = is_file($css_path) ? (int) filemtime($css_path) : 0;
        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-report-board.css?ver='.$css_ver.'">', 26);
    }
}
