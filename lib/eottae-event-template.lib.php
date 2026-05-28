<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_event_board_table')) {
    function eottae_event_board_table()
    {
        return defined('EOTTae_EVENT_TABLE') ? EOTTae_EVENT_TABLE : 'event';
    }
}

if (!function_exists('eottae_is_event_board')) {
    /**
     * 이벤트/프로모션 전용 게시판 (bo_table=event)
     */
    function eottae_is_event_board($bo_table)
    {
        $event = eottae_event_board_table();
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return $bo_table !== '' && $bo_table === $event;
    }
}

if (!function_exists('eottae_event_template_load_assets')) {
    function eottae_event_template_load_assets()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        $css_path = G5_PATH.'/css/eottae-event-template.css';
        $js_path = G5_PATH.'/js/eottae-event-template.js';
        $css_ver = is_file($css_path) ? (int) filemtime($css_path) : 0;
        $js_ver = is_file($js_path) ? (int) filemtime($js_path) : 0;

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-event-template.css?ver='.$css_ver.'">', 25);
        add_javascript('<script src="'.G5_JS_URL.'/eottae-event-template.js?ver='.$js_ver.'" defer></script>', 25);
    }
}

if (!function_exists('eottae_event_board_load_assets')) {
    function eottae_event_board_load_assets()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        eottae_event_template_load_assets();

        $css_path = G5_PATH.'/css/eottae-event-board.css';
        $css_ver = is_file($css_path) ? (int) filemtime($css_path) : 0;
        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-event-board.css?ver='.$css_ver.'">', 26);
    }
}
