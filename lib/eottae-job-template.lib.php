<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_job_board_table')) {
    function eottae_job_board_table()
    {
        return defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job';
    }
}

if (!function_exists('eottae_is_job_board')) {
    /**
     * 구인구직 전용 게시판 (bo_table=job)
     */
    function eottae_is_job_board($bo_table)
    {
        $job = eottae_job_board_table();
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return $bo_table !== '' && $bo_table === $job;
    }
}

if (!function_exists('eottae_job_template_load_assets')) {
    function eottae_job_template_load_assets()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        $css_path = G5_PATH.'/css/eottae-job-template.css';
        $js_path = G5_PATH.'/js/eottae-job-template.js';
        $css_ver = is_file($css_path) ? (int) filemtime($css_path) : 0;
        $js_ver = is_file($js_path) ? (int) filemtime($js_path) : 0;

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-job-template.css?ver='.$css_ver.'">', 25);
        add_javascript('<script src="'.G5_JS_URL.'/eottae-job-template.js?ver='.$js_ver.'" defer></script>', 25);

        if (!function_exists('eottae_location_picker_load_assets') && is_file(G5_LIB_PATH.'/eottae-location.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-location.lib.php';
        }
        if (function_exists('eottae_location_picker_load_assets')) {
            eottae_location_picker_load_assets(true);
        }
    }
}
