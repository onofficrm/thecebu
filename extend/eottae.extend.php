<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_PATH.'/extend/eottae.config.php';
include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-coupon.lib.php';
include_once G5_LIB_PATH.'/eottae-ad.lib.php';

if (function_exists('eottae_ad_ensure_table')) {
    eottae_ad_ensure_table();
}

if (!function_exists('eottae_on_register_after')) {
    function eottae_on_register_after($mb_id, $w)
    {
        if ($w !== '' || $mb_id === '') {
            return;
        }

        eottae_coupon_ensure_welcome($mb_id);
    }
}
add_event('register_form_update_after', 'eottae_on_register_after', 10, 2);

if (!function_exists('eottae_on_shop_write_before')) {
    function eottae_on_shop_write_before($board, $wr_id, $w, $qstr)
    {
        if (empty($board['bo_table']) || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }

        $ca = isset($_POST['ca_name']) ? trim((string) $_POST['ca_name']) : '';
        $wr1 = isset($_POST['wr_1']) ? trim((string) $_POST['wr_1']) : '';

        if ($ca !== '' && $wr1 === '') {
            $_POST['wr_1'] = $ca;
        } elseif ($wr1 !== '' && $ca === '') {
            $_POST['ca_name'] = $wr1;
        }
    }
}
add_event('write_update_before', 'eottae_on_shop_write_before', 10, 4);

if (!function_exists('eottae_on_shop_write_after')) {
    function eottae_on_shop_write_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        if (empty($board['bo_table']) || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }

        if (function_exists('eottae_ad_sync_from_shop')) {
            eottae_ad_sync_from_shop($board['bo_table'], (int) $wr_id);
        }
    }
}
add_event('write_update_after', 'eottae_on_shop_write_after', 10, 5);

if (!function_exists('eottae_is_youtube_board')) {
    function eottae_is_youtube_board($bo_table)
    {
        $bo_table = (string) $bo_table;
        $youtube = defined('EOTTae_YOUTUBE_TABLE') ? EOTTae_YOUTUBE_TABLE : 'youtube';

        return $bo_table !== '' && $bo_table === $youtube;
    }
}

if (!function_exists('eottae_on_youtube_write_after')) {
    function eottae_on_youtube_write_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        if (empty($board['bo_table']) || !eottae_is_youtube_board($board['bo_table'])) {
            return;
        }

        if (!function_exists('g5b_youtube_save_duration')) {
            include_once G5_SKIN_PATH.'/board/_inc/g5b-youtube.php';
        }

        g5b_youtube_save_duration($board['bo_table'], (int) $wr_id);
    }
}
add_event('write_update_after', 'eottae_on_youtube_write_after', 20, 5);

if (eottae_should_load_assets()) {
    add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae.css">', 20);
    add_javascript('<script src="'.G5_JS_URL.'/eottae.js" defer></script>', 20);
    if (function_exists('eottae_coupon_ensure_ready')) {
        eottae_coupon_ensure_ready();
    }
    if (!isset($g5['body_script'])) {
        $g5['body_script'] = '';
    }
    if (strpos($g5['body_script'], 'eottae-page') === false) {
        $g5['body_script'] .= ' class="eottae-page"';
    }
}

if (isset($board) && is_array($board) && isset($board['bo_skin'])) {
    $skin = (string) $board['bo_skin'];
    if (strpos($skin, 'eottae-') === 0) {
        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/g5b-board.css">', 4);
    }
}
