<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_PATH.'/extend/eottae.config.php';
include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-coupon.lib.php';

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
        if (empty($board['bo_table']) || $board['bo_table'] !== eottae_shop_table()) {
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

if (eottae_should_load_assets()) {
    add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae.css">', 5);
    add_javascript('<script src="'.G5_JS_URL.'/eottae.js" defer></script>', 20);
}

if (isset($board) && is_array($board) && isset($board['bo_skin'])) {
    $skin = (string) $board['bo_skin'];
    if (strpos($skin, 'eottae-') === 0) {
        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/g5b-board.css">', 4);
    }
}
