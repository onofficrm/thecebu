<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_PATH.'/extend/eottae.config.php';
include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-coupon.lib.php';
include_once G5_LIB_PATH.'/eottae-ad.lib.php';
include_once G5_LIB_PATH.'/eottae-shop-seo.lib.php';
include_once G5_LIB_PATH.'/eottae-business-snippet.lib.php';
include_once G5_LIB_PATH.'/eottae-shop-owner.lib.php';

include_once G5_LIB_PATH.'/eottae-business-coupon.lib.php';
include_once G5_LIB_PATH.'/eottae-promo-coupon.lib.php';

if (function_exists('eottae_business_coupon_ensure_schema')) {
    eottae_business_coupon_ensure_schema();
}
if (function_exists('eottae_promo_coupon_ensure_schema')) {
    eottae_promo_coupon_ensure_schema();
}
if (function_exists('eottae_ad_ensure_table')) {
    eottae_ad_ensure_table();
}
if (function_exists('eottae_shop_seo_ensure_table')) {
    eottae_shop_seo_ensure_table();
}
if (function_exists('eottae_shop_map_thumb_ensure_table')) {
    eottae_shop_map_thumb_ensure_table();
}
if (function_exists('eottae_business_snippet_ensure_table')) {
    eottae_business_snippet_ensure_table();
}
if (function_exists('eottae_shop_sync_board_categories')) {
    eottae_shop_sync_board_categories();
}
if (function_exists('eottae_shop_backfill_missing_coords')) {
    eottae_shop_backfill_missing_coords(50);
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

if (function_exists('add_replace')) {
    add_replace('get_pretty_url', 'eottae_pretty_shop_board_url', 5, 5);
}

if (!function_exists('eottae_on_shop_write_before')) {
    function eottae_on_shop_write_before($board, $wr_id, $w, $qstr)
    {
        global $is_admin;

        if (empty($board['bo_table']) || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }

        if (function_exists('eottae_shop_prepare_write_post')) {
            eottae_shop_prepare_write_post($board);
        }

        $ca = isset($_POST['ca_name']) ? trim((string) $_POST['ca_name']) : '';
        $wr1 = isset($_POST['wr_1']) ? trim((string) $_POST['wr_1']) : '';

        if ($ca !== '' && $wr1 === '') {
            $_POST['wr_1'] = $ca;
        } elseif ($wr1 !== '' && $ca === '') {
            $_POST['ca_name'] = $wr1;
        }

        $address = isset($_POST['wr_3']) ? trim((string) $_POST['wr_3']) : '';
        $region = isset($_POST['wr_2']) ? trim((string) $_POST['wr_2']) : '';
        if ($region === '' && $address !== '' && function_exists('eottae_shop_detect_region')) {
            $_POST['wr_2'] = eottae_shop_detect_region($address);
        }

        if ($is_admin === 'super' && isset($_POST['eottae_owner_mb_id'])) {
            $owner_mb_id = trim(strip_tags((string) $_POST['eottae_owner_mb_id']));
            if ($owner_mb_id !== '') {
                $check = eottae_shop_owner_validate($owner_mb_id);
                if (empty($check['ok'])) {
                    alert($check['message']);
                }
            }
        }
    }
}
add_event('write_update_before', 'eottae_on_shop_write_before', 10, 4);

if (!function_exists('eottae_on_shop_owner_assign')) {
    function eottae_on_shop_owner_assign($board, $wr_id, $w, $qstr, $redirect_url)
    {
        global $is_admin;

        if ($is_admin !== 'super') {
            return;
        }
        if (empty($board['bo_table']) || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }
        if (!isset($_POST['eottae_owner_mb_id'])) {
            return;
        }

        $owner_mb_id = trim(strip_tags((string) $_POST['eottae_owner_mb_id']));
        if ($owner_mb_id === '') {
            return;
        }

        eottae_shop_assign_owner($board['bo_table'], (int) $wr_id, $owner_mb_id);
    }
}
add_event('write_update_after', 'eottae_on_shop_owner_assign', 15, 5);

if (!function_exists('eottae_on_shop_write_after')) {
    function eottae_on_shop_write_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        if (empty($board['bo_table']) || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }

        if (function_exists('eottae_ad_sync_from_shop')) {
            eottae_ad_sync_from_shop($board['bo_table'], (int) $wr_id);
        }

        if (function_exists('eottae_shop_seo_save')) {
            eottae_shop_seo_save($board['bo_table'], (int) $wr_id, eottae_shop_seo_from_post());
        }

        if (function_exists('eottae_shop_map_thumb_save_from_upload')) {
            eottae_shop_map_thumb_save_from_upload($board['bo_table'], (int) $wr_id);
        }
    }
}
add_event('write_update_after', 'eottae_on_shop_write_after', 10, 5);

if (!function_exists('eottae_on_shop_board_head')) {
    function eottae_on_shop_board_head($board, $write, $wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || !is_array($write) || empty($write['wr_id'])) {
            return;
        }
        if (empty($board['bo_table']) || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }

        eottae_shop_seo_apply_page($board, $write);
    }
}
add_event('board_head_before', 'eottae_on_shop_board_head', 10, 3);

if (!function_exists('eottae_on_shop_delete')) {
    function eottae_on_shop_delete($write, $board)
    {
        if (empty($board['bo_table']) || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }
        if (!is_array($write) || empty($write['wr_id'])) {
            return;
        }

        eottae_shop_seo_delete($board['bo_table'], (int) $write['wr_id']);
        if (function_exists('eottae_shop_map_thumb_delete')) {
            eottae_shop_map_thumb_delete($board['bo_table'], (int) $write['wr_id']);
        }
        if (function_exists('eottae_ad_deactivate_by_shop')) {
            eottae_ad_deactivate_by_shop($board['bo_table'], (int) $write['wr_id']);
        }
    }
}
add_event('bbs_delete', 'eottae_on_shop_delete', 10, 2);

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

if (!function_exists('eottae_is_community_board')) {
    function eottae_is_community_board($bo_table)
    {
        $community = defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community';
        return (string) $bo_table !== '' && (string) $bo_table === $community;
    }
}

if (!function_exists('eottae_on_promo_write_after')) {
    function eottae_on_promo_write_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        global $member;

        if (empty($board['bo_table']) || !eottae_is_community_board($board['bo_table'])) {
            return;
        }
        if (!function_exists('eottae_promo_check_post_count')) {
            return;
        }

        $mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
        if ($mb_id === '') {
            return;
        }

        eottae_promo_check_post_count($mb_id);
    }
}
add_event('write_update_after', 'eottae_on_promo_write_after', 25, 5);

if (!function_exists('eottae_on_promo_board_view')) {
    function eottae_on_promo_board_view($board, $write, $wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || !is_array($write) || empty($write['wr_id'])) {
            return;
        }
        if (empty($board['bo_table']) || !eottae_is_community_board($board['bo_table'])) {
            return;
        }
        if (!function_exists('eottae_promo_check_post_views')) {
            return;
        }

        eottae_promo_check_post_views($board, $write, $wr_id);
    }
}
add_event('board_head_before', 'eottae_on_promo_board_view', 20, 3);

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
