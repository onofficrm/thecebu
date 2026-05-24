<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_is_business_member')) {
    function eottae_is_business_member($member = null)
    {
        global $member;

        $m = is_array($member) ? $member : (isset($member) && is_array($member) ? $member : array());
        if (!$m && function_exists('get_member')) {
            global $is_member;
            if (!empty($is_member) && isset($member['mb_id'])) {
                $m = $member;
            }
        }
        if (!is_array($m) || empty($m['mb_id'])) {
            return false;
        }

        $level = isset($m['mb_level']) ? (int) $m['mb_level'] : 0;
        if ($level >= (defined('EOTTae_BUSINESS_LEVEL') ? EOTTae_BUSINESS_LEVEL : 5)) {
            return true;
        }

        return isset($m['mb_1']) && $m['mb_1'] === 'business';
    }
}

if (!function_exists('eottae_shop_from_write')) {
    /**
     * shop 게시판 wr_* → 표준 배열 (명세 매핑)
     */
    function eottae_shop_from_write($wr)
    {
        if (!is_array($wr)) {
            return array();
        }

        return array(
            'name'          => isset($wr['wr_subject']) ? get_text($wr['wr_subject']) : '',
            'category'      => isset($wr['wr_1']) ? get_text($wr['wr_1']) : '',
            'region'        => isset($wr['wr_2']) ? get_text($wr['wr_2']) : '',
            'address'       => isset($wr['wr_3']) ? get_text($wr['wr_3']) : '',
            'phone'         => isset($wr['wr_4']) ? get_text($wr['wr_4']) : '',
            'inquiry_code'  => isset($wr['wr_5']) ? get_text($wr['wr_5']) : '',
            'hours'         => isset($wr['wr_6']) ? get_text($wr['wr_6']) : '',
            'closed'        => isset($wr['wr_7']) ? get_text($wr['wr_7']) : '',
            'status'        => isset($wr['wr_8']) ? get_text($wr['wr_8']) : '',
            'lat'           => isset($wr['wr_9']) ? get_text($wr['wr_9']) : '',
            'lng'           => isset($wr['wr_10']) ? get_text($wr['wr_10']) : '',
            'website'       => isset($wr['wr_link1']) ? get_text($wr['wr_link1']) : '',
            'sns'           => isset($wr['wr_link2']) ? get_text($wr['wr_link2']) : '',
            'content'       => isset($wr['wr_content']) ? $wr['wr_content'] : '',
            'wr_id'         => isset($wr['wr_id']) ? (int) $wr['wr_id'] : 0,
        );
    }
}

if (!function_exists('eottae_tel_href')) {
    function eottae_tel_href($phone)
    {
        $digits = preg_replace('/[^0-9+]/', '', (string) $phone);

        return $digits !== '' ? 'tel:'.$digits : '#';
    }
}

if (!function_exists('eottae_maps_directions_url')) {
    function eottae_maps_directions_url($lat, $lng, $address = '')
    {
        if ($lat !== '' && $lng !== '') {
            return 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($lat.','.$lng);
        }
        if ($address !== '') {
            return 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($address);
        }

        return '#';
    }
}

if (!function_exists('eottae_should_load_assets')) {
    function eottae_should_load_assets()
    {
        if (defined('G5_IS_ADMIN') && G5_IS_ADMIN) {
            return false;
        }

        $script = isset($_SERVER['SCRIPT_NAME']) ? basename($_SERVER['SCRIPT_NAME']) : '';
        $member_scripts = array('login.php', 'register.php', 'register_form.php', 'register_result.php', 'password_lost.php', 'password_reset.php', 'member_confirm.php');
        if (in_array($script, $member_scripts, true)) {
            return true;
        }

        if (isset($_SERVER['SCRIPT_FILENAME']) && strpos($_SERVER['SCRIPT_FILENAME'], 'eottae-mypage.php') !== false) {
            return true;
        }

        $bo = isset($_GET['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', $_GET['bo_table']) : '';
        if (in_array($bo, array(EOTTae_SHOP_TABLE, EOTTae_COMMUNITY_TABLE), true)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('eottae_load_component')) {
    function eottae_load_component($name)
    {
        $path = G5_PATH.'/components/eottae/'.$name.'.php';
        if (is_file($path)) {
            include_once $path;
        }
    }
}

if (!function_exists('eottae_render_inquiry_buttons')) {
    /**
     * @param string $context card|detail|mobile-bar|reservation|business
     * @param array  $opts phone, inquiry_code, lat, lng, address, share_url
     */
    function eottae_render_inquiry_buttons($context, $opts = array())
    {
        eottae_load_component('inquiry-button');

        if (function_exists('eottae_inquiry_buttons_html')) {
            echo eottae_inquiry_buttons_html($context, $opts);
        }
    }
}

if (!function_exists('eottae_render_shop_card')) {
    function eottae_render_shop_card($list_row, $bo_table = '')
    {
        eottae_load_component('shop-card');

        if (function_exists('eottae_shop_card_html')) {
            echo eottae_shop_card_html($list_row, $bo_table);
        }
    }
}

if (!function_exists('eottae_mypage_url')) {
    function eottae_mypage_url()
    {
        return G5_URL.'/page/eottae-mypage.php';
    }
}

if (!function_exists('eottae_login_url')) {
    function eottae_login_url($return = '')
    {
        $url = G5_BBS_URL.'/login.php';
        if ($return !== '') {
            $url .= '?url='.urlencode($return);
        }

        return $url;
    }
}
