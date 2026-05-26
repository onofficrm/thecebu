<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('collect_today_sebu_briefing_data')) {
    include_once G5_LIB_PATH.'/eottae-briefing.lib.php';
}

if (function_exists('eottae_briefing_load_assets')) {
    eottae_briefing_load_assets();
}

render_today_sebu_briefing(collect_today_sebu_briefing_data());
