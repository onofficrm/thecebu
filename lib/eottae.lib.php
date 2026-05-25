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

if (!function_exists('eottae_shop_resolve_inquiry_code')) {
    /**
     * 업체 문의 연결 코드 — 등록자 입력 없이 wr_id 기준 자동 생성
     */
    function eottae_shop_resolve_inquiry_code($wr, $bo_table = '')
    {
        if (!is_array($wr)) {
            return '';
        }

        $manual = isset($wr['wr_5']) ? trim((string) $wr['wr_5']) : '';
        if ($manual !== '') {
            return get_text($manual);
        }

        $wr_id = isset($wr['wr_id']) ? (int) $wr['wr_id'] : 0;
        if ($wr_id < 1) {
            return '';
        }

        if ($bo_table === '' && !empty($wr['bo_table'])) {
            $bo_table = (string) $wr['bo_table'];
        }
        if ($bo_table === '') {
            $bo_table = defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop';
        }
        $bo_table = preg_replace('/[^a-z0-9_]/', '', $bo_table);

        return 'shop-'.$bo_table.'-'.$wr_id;
    }
}

if (!function_exists('eottae_shop_ensure_inquiry_code')) {
    /** 문의 연결 코드가 비어 있으면 shop-{게시판}-{글번호} 로 자동 저장 */
    function eottae_shop_ensure_inquiry_code($bo_table, $wr_id)
    {
        global $g5;

        $wr_id = (int) $wr_id;
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($wr_id < 1 || $bo_table === '') {
            return;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $row = sql_fetch(" select wr_5 from {$write_table} where wr_id = '{$wr_id}' ");
        if (!$row || trim((string) $row['wr_5']) !== '') {
            return;
        }

        $code = 'shop-'.$bo_table.'-'.$wr_id;
        sql_query(" update {$write_table} set wr_5 = '".sql_escape_string($code)."' where wr_id = '{$wr_id}' ");
    }
}

if (!function_exists('eottae_shop_wr_link2_raw')) {
    /** wr_link2 SNS JSON — get_text() 없이 원문 유지 */
    function eottae_shop_wr_link2_raw($wr)
    {
        if (!is_array($wr) || !isset($wr['wr_link2'])) {
            return '';
        }

        return trim(stripslashes((string) $wr['wr_link2']));
    }
}

if (!function_exists('eottae_shop_sns_decode')) {
    /**
     * wr_link2 SNS JSON 디코드 (get_text로 깨진 레거시 문자열 보정 시도)
     *
     * @return array<string, string>|null
     */
    function eottae_shop_sns_decode($raw)
    {
        $raw = trim(stripslashes((string) $raw));
        if ($raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (strpos($raw, '&#034;') !== false && function_exists('get_text')) {
            $restored = get_text($raw, 0, true);
            $decoded = json_decode($restored, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}

if (!function_exists('eottae_shop_youtube_id')) {
    function eottae_shop_youtube_id($shop)
    {
        if (!is_array($shop)) {
            return '';
        }

        $url = '';
        if (!empty($shop['youtube'])) {
            $url = trim((string) $shop['youtube']);
        } elseif (!empty($shop['sns'])) {
            $url = eottae_shop_sns_value($shop['sns'], 'youtube');
        }

        if ($url === '') {
            return '';
        }

        if (!function_exists('g5b_youtube_id_from_url')) {
            include_once G5_SKIN_PATH.'/board/_inc/g5b-youtube.php';
        }

        return g5b_youtube_id_from_url($url);
    }
}

if (!function_exists('eottae_shop_from_write')) {
    /**
     * shop 게시판 wr_* → 표준 배열 (명세 매핑)
     */
    function eottae_shop_from_write($wr, $bo_table = '')
    {
        if (!is_array($wr)) {
            return array();
        }

        if ($bo_table === '' && !empty($wr['bo_table'])) {
            $bo_table = (string) $wr['bo_table'];
        }

        $sns_raw = eottae_shop_wr_link2_raw($wr);

        return array(
            'name'          => isset($wr['wr_subject']) ? get_text($wr['wr_subject']) : '',
            'category'      => isset($wr['wr_1']) ? get_text($wr['wr_1']) : '',
            'region'        => isset($wr['wr_2']) ? get_text($wr['wr_2']) : '',
            'address'       => isset($wr['wr_3']) ? get_text($wr['wr_3']) : '',
            'phone'         => isset($wr['wr_4']) ? get_text($wr['wr_4']) : '',
            'inquiry_code'  => eottae_shop_resolve_inquiry_code($wr, $bo_table),
            'hours'         => isset($wr['wr_6']) ? get_text($wr['wr_6']) : '',
            'closed'        => isset($wr['wr_7']) ? get_text($wr['wr_7']) : '',
            'status'        => isset($wr['wr_8']) ? get_text($wr['wr_8']) : '',
            'lat'           => isset($wr['wr_9']) ? get_text($wr['wr_9']) : '',
            'lng'           => isset($wr['wr_10']) ? get_text($wr['wr_10']) : '',
            'website'       => isset($wr['wr_link1']) ? get_text($wr['wr_link1']) : '',
            'youtube'       => eottae_shop_sns_value($sns_raw, 'youtube'),
            'sns'           => $sns_raw,
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

if (!function_exists('eottae_shop_sns_links')) {
    function eottae_shop_sns_links($raw)
    {
        $raw = trim(stripslashes((string) $raw));
        if ($raw === '' || stripos($raw, 'ad') !== false) {
            return array();
        }

        $labels = array(
            'instagram' => '인스타그램',
            'tiktok' => '틱톡',
            'facebook' => '페이스북',
            'naver_blog' => '네이버블로그',
            'youtube' => '유튜브',
            'sns' => 'SNS',
        );

        $decoded = function_exists('eottae_shop_sns_decode') ? eottae_shop_sns_decode($raw) : json_decode($raw, true);
        if (!is_array($decoded)) {
            return array(array('key' => 'sns', 'label' => $labels['sns'], 'url' => get_text($raw)));
        }

        $links = array();
        foreach ($labels as $key => $label) {
            if ($key === 'sns') {
                continue;
            }
            $url = isset($decoded[$key]) ? trim((string) $decoded[$key]) : '';
            if ($url !== '') {
                $links[] = array('key' => $key, 'label' => $label, 'url' => get_text($url));
            }
        }

        return $links;
    }
}

if (!function_exists('eottae_shop_sns_value')) {
    function eottae_shop_sns_value($raw, $key)
    {
        $raw = trim(stripslashes((string) $raw));
        $key = (string) $key;
        if ($raw === '') {
            return '';
        }

        $decoded = function_exists('eottae_shop_sns_decode') ? eottae_shop_sns_decode($raw) : json_decode($raw, true);
        if (is_array($decoded)) {
            return isset($decoded[$key]) ? get_text($decoded[$key]) : '';
        }

        return $key === 'instagram' ? get_text($raw) : '';
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

if (!function_exists('eottae_shop_resolve_map_coords')) {
    /**
     * 지도 표시용 좌표 (저장값 없으면 대표지역 중심으로 보정)
     *
     * @return array{lat: string, lng: string}
     */
    function eottae_shop_resolve_map_coords($shop)
    {
        $lat = is_array($shop) && isset($shop['lat']) ? trim((string) $shop['lat']) : '';
        $lng = is_array($shop) && isset($shop['lng']) ? trim((string) $shop['lng']) : '';

        if ($lat !== '' && $lng !== '' && is_numeric($lat) && is_numeric($lng)) {
            return array('lat' => $lat, 'lng' => $lng);
        }

        $address = is_array($shop) && isset($shop['address']) ? trim((string) $shop['address']) : '';
        $region = is_array($shop) && isset($shop['region']) ? trim((string) $shop['region']) : '';
        if ($address === '' && $region === '') {
            return array('lat' => '', 'lng' => '');
        }

        $fallback = function_exists('eottae_shop_guess_coords') ? eottae_shop_guess_coords($address, $region) : array();
        if (empty($fallback['lat']) || empty($fallback['lng'])) {
            return array('lat' => '', 'lng' => '');
        }

        return array(
            'lat' => (string) $fallback['lat'],
            'lng' => (string) $fallback['lng'],
        );
    }
}

if (!function_exists('eottae_shop_map_embed_url')) {
    /**
     * Google Maps API 키 없이 iframe 임베드 URL
     */
    function eottae_shop_map_embed_url($lat, $lng, $address = '')
    {
        $lat = trim((string) $lat);
        $lng = trim((string) $lng);
        $address = trim((string) $address);

        if ($lat !== '' && $lng !== '' && is_numeric($lat) && is_numeric($lng)) {
            return 'https://maps.google.com/maps?q='.rawurlencode($lat.','.$lng).'&z=15&output=embed';
        }
        if ($address !== '') {
            return 'https://maps.google.com/maps?q='.rawurlencode($address).'&z=15&output=embed';
        }

        return '';
    }
}

if (!function_exists('eottae_shop_content_token')) {
    function eottae_shop_content_token($regenerate = false)
    {
        $key = 'eottae_shop_content_token';
        $token = get_session($key);
        if ($regenerate || $token === '' || $token === null) {
            $token = md5(uniqid((string) mt_rand(), true));
            set_session($key, $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_shop_content_editor_enabled')) {
    function eottae_shop_content_editor_enabled($board)
    {
        global $config, $member;

        if (!is_array($board) || empty($board['bo_use_dhtml_editor'])) {
            return false;
        }

        $is_dhtml_editor_use = !G5_IS_MOBILE || (defined('G5_IS_MOBILE_DHTML_USE') && G5_IS_MOBILE_DHTML_USE);
        if (!$is_dhtml_editor_use || empty($config['cf_editor'])) {
            return false;
        }

        return !empty($member['mb_level']) && (int) $member['mb_level'] >= (int) $board['bo_html_level'];
    }
}

if (!function_exists('eottae_shop_enqueue_content_editor_assets')) {
    function eottae_shop_enqueue_content_editor_assets()
    {
        global $config;

        static $enqueued = false;
        if ($enqueued || empty($config['cf_editor'])) {
            return;
        }

        $editor_path = G5_EDITOR_PATH.'/'.$config['cf_editor'].'/editor.lib.php';
        if (!is_file($editor_path)) {
            return;
        }

        include_once $editor_path;

        $editor_url = G5_EDITOR_URL.'/'.$config['cf_editor'];
        add_javascript('<script src="'.$editor_url.'/js/service/HuskyEZCreator.js"></script>', 0);
        add_javascript(
            '<script>var g5_editor_url = "'.htmlspecialchars($editor_url, ENT_QUOTES, 'UTF-8').'", oEditors = [], ed_nonce = "'.ft_nonce_create('smarteditor').'";</script>',
            1
        );

        $enqueued = true;
    }
}

if (!function_exists('eottae_use_site_chrome')) {
    /**
     * 세부어때 공통 GNB·푸터·eottae.css 적용 여부 (관리자·설치 도구 제외)
     */
    function eottae_use_site_chrome()
    {
        if (defined('G5_IS_ADMIN') && G5_IS_ADMIN) {
            return false;
        }
        if (defined('EOTTAE_SETUP_MINIMAL') && EOTTAE_SETUP_MINIMAL) {
            return false;
        }

        return true;
    }
}

if (!function_exists('eottae_should_load_assets')) {
    function eottae_should_load_assets()
    {
        return eottae_use_site_chrome();
    }
}

if (!function_exists('eottae_prepare_site_header')) {
    /**
     * site-header.php include 전 공통 변수
     */
    function eottae_prepare_site_header()
    {
        global $g5_site_title, $config;

        if (!isset($g5_site_title) || $g5_site_title === '') {
            $g5_site_title = function_exists('g5site_cfg')
                ? g5site_cfg('site_name', get_text($config['cf_title']))
                : get_text($config['cf_title']);
            if ($g5_site_title === '') {
                $g5_site_title = get_text($config['cf_title']);
            }
        }
    }
}

if (!function_exists('eottae_site_logo_url')) {
    /**
     * site_config·파일 존재 확인 후 로고 URL 반환
     *
     * @param string $key logo_path | footer_logo_path
     * @return string
     */
    function eottae_site_logo_url($key = 'logo_path')
    {
        global $site_config;

        if (!isset($site_config) && defined('G5_PATH') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }

        if (function_exists('g5site_cfg')) {
            $path = g5site_cfg($key, '');
            if ($path !== '') {
                if (preg_match('#^https?://#i', $path)) {
                    return $path;
                }
                $rel = ($path[0] === '/') ? $path : '/'.$path;
                if (defined('G5_PATH') && is_file(G5_PATH.$rel) && defined('G5_URL')) {
                    return G5_URL.$rel;
                }
                if (function_exists('g5site_cfg_url')) {
                    return g5site_cfg_url($key, '');
                }
            }
        }

        if (!defined('G5_PATH') || !defined('G5_URL')) {
            return '';
        }

        $fallbacks = $key === 'footer_logo_path'
            ? array('cebu-logo-footer.png', 'cebu-logo-main.png', 'logo.png', 'logo.svg')
            : array('cebu-logo-main.png', 'logo.png', 'logo.svg');

        foreach ($fallbacks as $file) {
            if (is_file(G5_PATH.'/img/logo/'.$file)) {
                return G5_URL.'/img/logo/'.$file;
            }
        }

        return '';
    }
}

if (!function_exists('eottae_home_map_center')) {
    /**
     * 홈 지도 기본 중심 — 세부시티·라푸라푸(막탄) 일대
     *
     * @return array{lat: float, lng: float, zoom: int}
     */
    function eottae_home_map_center()
    {
        if (is_file(G5_PATH.'/components/maps/map-config.php')) {
            include_once G5_PATH.'/components/maps/map-config.php';
            if (function_exists('onoff_map_get_config')) {
                $cfg = onoff_map_get_config();
                $zoom = isset($cfg['default_zoom']) ? (int) $cfg['default_zoom'] : 12;
                if ($zoom < 10 || $zoom > 16) {
                    $zoom = 12;
                }

                return array(
                    'lat'  => isset($cfg['default_lat']) ? (float) $cfg['default_lat'] : 10.313,
                    'lng'  => isset($cfg['default_lng']) ? (float) $cfg['default_lng'] : 123.9174,
                    'zoom' => $zoom,
                );
            }
        }

        return array(
            'lat'  => 10.313,
            'lng'  => 123.9174,
            'zoom' => 12,
        );
    }
}

if (!function_exists('eottae_home_map_locations')) {
    function eottae_home_map_locations($limit = 30)
    {
        global $g5;

        if (!function_exists('eottae_shop_table') || !function_exists('eottae_shop_map_markers')) {
            return array();
        }

        $bo_table = eottae_shop_table();
        $write_table = $g5['write_prefix'].$bo_table;
        $limit = max(1, min(50, (int) $limit));
        $result = sql_query(" select * from {$write_table}
            where wr_is_comment = 0
            order by wr_id desc
            limit {$limit} ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = $row;
        }

        $markers = eottae_shop_map_markers($rows, $bo_table);
        if (!function_exists('eottae_shop_map_locations_json')) {
            return $markers;
        }

        $json = eottae_shop_map_locations_json($markers);
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : array();
    }
}

if (!function_exists('eottae_builder_inject_home_map')) {
    function eottae_builder_inject_home_map($html)
    {
        if (!is_string($html) || $html === '') {
            return $html;
        }

        if (!function_exists('eottae_google_maps_api_key')) {
            return $html;
        }

        $api_key = eottae_google_maps_api_key();
        if ($api_key === '') {
            return $html;
        }

        $center = eottae_home_map_center();
        $locations = eottae_home_map_locations(30);
        $payload = array(
            'lat'       => $center['lat'],
            'lng'       => $center['lng'],
            'zoom'      => $center['zoom'],
            'locations' => $locations,
        );
        $payload_json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload_json === false) {
            return $html;
        }

        $home_map_js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-map.js' : '/js/eottae-home-map.js';
        $key_esc = htmlspecialchars($api_key, ENT_QUOTES, 'UTF-8');
        $script = '<script>window.__EOTTae_HOME_MAP__='.$payload_json.';</script>';
        $script .= '<script src="'.htmlspecialchars($home_map_js, ENT_QUOTES, 'UTF-8').'"></script>';
        $script .= '<script src="https://maps.googleapis.com/maps/api/js?key='.$key_esc.'&amp;callback=initEottaeHomeMaps" defer></script>';

        if (preg_match('#</body>#i', $html)) {
            return preg_replace('#</body>#i', $script.'</body>', $html, 1);
        }

        return $html.$script;
    }
}

if (!function_exists('eottae_builder_logo_url')) {
    function eottae_builder_logo_url()
    {
        return eottae_site_logo_url('logo_path');
    }
}

if (!function_exists('eottae_builder_inject_logo_head_script')) {
    function eottae_builder_inject_logo_head_script()
    {
        $logo = eottae_builder_logo_url();
        if ($logo === '') {
            return '';
        }

        $logo_js = json_encode($logo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return '<script>window.__EOTTae_LOGO__='.$logo_js.';</script>';
    }
}

if (!function_exists('eottae_builder_inject_featured_carousel_script')) {
    function eottae_builder_inject_featured_carousel_script()
    {
        $js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-featured-carousel.js' : '/js/eottae-home-featured-carousel.js';

        return '<script src="'.htmlspecialchars($js, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }
}

if (!function_exists('eottae_builder_inject_html')) {
    function eottae_builder_inject_html($html, $id)
    {
        if ($id !== 'thecebu-main' || !is_string($html) || $html === '') {
            return $html;
        }

        $html = eottae_builder_inject_home_map($html);

        $head_script = eottae_builder_inject_logo_head_script();
        if ($head_script !== '') {
            if (preg_match('#<script[^>]+type=["\']module["\'][^>]*>#i', $html)) {
                $html = preg_replace('#(<script[^>]+type=["\']module["\'][^>]*>)#i', $head_script.'$1', $html, 1);
            } elseif (preg_match('#</head>#i', $html)) {
                $html = preg_replace('#</head>#i', $head_script.'</head>', $html, 1);
            }
        }

        $body_scripts = eottae_builder_inject_featured_carousel_script();

        if ($body_scripts === '') {
            return $html;
        }

        if (preg_match('#</body>#i', $html)) {
            return preg_replace('#</body>#i', $body_scripts.'</body>', $html, 1);
        }

        return $html.$body_scripts;
    }
}

if (!function_exists('eottae_render_site_header')) {
    function eottae_render_site_header()
    {
        if (!is_file(G5_PATH.'/components/eottae/site-header.php')) {
            return false;
        }

        eottae_prepare_site_header();
        $eottae_auth = function_exists('eottae_auth_context') ? eottae_auth_context() : array('is_member' => false, 'is_admin' => false, 'member' => array('mb_id' => '', 'mb_level' => 1, 'mb_point' => 0));
        $is_member = !empty($eottae_auth['is_member']);
        $is_admin = !empty($eottae_auth['is_admin']) ? $eottae_auth['is_admin'] : '';
        $member = isset($eottae_auth['member']) ? $eottae_auth['member'] : array('mb_id' => '', 'mb_level' => 1, 'mb_point' => 0);
        include G5_PATH.'/components/eottae/site-header.php';

        return true;
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
    function eottae_render_shop_card($list_row, $bo_table = '', $layout = 'grid')
    {
        eottae_load_component('shop-card');

        if (function_exists('eottae_shop_card_html')) {
            echo eottae_shop_card_html($list_row, $bo_table, $layout);
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

if (!function_exists('eottae_current_url')) {
    function eottae_current_url()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return G5_URL;
        }

        $uri = (string) $_SERVER['REQUEST_URI'];

        return (strpos($uri, 'http') === 0) ? $uri : G5_URL.$uri;
    }
}

if (!function_exists('eottae_register_url')) {
    function eottae_register_url()
    {
        return G5_BBS_URL.'/register.php';
    }
}

if (!function_exists('eottae_password_lost_url')) {
    function eottae_password_lost_url()
    {
        return G5_BBS_URL.'/password_lost.php';
    }
}

if (!function_exists('eottae_auth_context')) {
    /**
     * include 스코프와 무관하게 로그인 상태를 반환 (헤더·사이드바 공통)
     */
    function eottae_auth_context()
    {
        global $is_member, $is_admin, $member;

        if ((!is_array($member) || empty($member['mb_id'])) && !empty($_SESSION['ss_mb_id']) && function_exists('get_member')) {
            $member = get_member($_SESSION['ss_mb_id']);
        }

        $logged_in = is_array($member) && !empty($member['mb_id']);

        if ($logged_in) {
            $is_member = true;
            if ($is_admin === '' || $is_admin === false) {
                $is_admin = function_exists('is_admin') ? is_admin($member['mb_id']) : '';
            }
        }

        return array(
            'is_member' => $logged_in,
            'is_admin'  => !empty($is_admin),
            'member'    => $logged_in ? $member : array(
                'mb_id'    => '',
                'mb_level' => 1,
                'mb_point' => 0,
            ),
        );
    }
}

if (!function_exists('eottae_review_table')) {
    function eottae_review_table()
    {
        return defined('EOTTae_REVIEW_TABLE') ? EOTTae_REVIEW_TABLE : 'review';
    }
}

if (!function_exists('eottae_review_from_write')) {
    function eottae_review_from_write($wr)
    {
        if (!is_array($wr)) {
            return array();
        }

        $rating = isset($wr['wr_2']) ? (float) $wr['wr_2'] : 0;
        if ($rating < 1 || $rating > 5) {
            $rating = 0;
        }

        return array(
            'wr_id'       => isset($wr['wr_id']) ? (int) $wr['wr_id'] : 0,
            'shop_id'     => isset($wr['wr_1']) ? (int) $wr['wr_1'] : 0,
            'rating'      => $rating,
            'shop_name'   => isset($wr['wr_3']) ? get_text($wr['wr_3']) : '',
            'status'      => isset($wr['wr_4']) ? get_text($wr['wr_4']) : 'visible',
            'photo_count' => isset($wr['wr_5']) ? (int) $wr['wr_5'] : 0,
            'subject'     => isset($wr['wr_subject']) ? get_text($wr['wr_subject']) : '',
            'content'     => isset($wr['wr_content']) ? $wr['wr_content'] : '',
            'author'      => isset($wr['wr_name']) ? get_text($wr['wr_name']) : '',
            'mb_id'       => isset($wr['mb_id']) ? $wr['mb_id'] : '',
            'datetime'    => isset($wr['wr_datetime']) ? $wr['wr_datetime'] : '',
            'datetime_ts' => isset($wr['wr_datetime']) ? strtotime($wr['wr_datetime']) : 0,
        );
    }
}

if (!function_exists('eottae_review_write_table')) {
    function eottae_review_write_table()
    {
        global $g5;

        return $g5['write_prefix'].eottae_review_table();
    }
}

if (!function_exists('eottae_get_shop_review_summary')) {
    function eottae_get_shop_review_summary($shop_wr_id)
    {
        global $g5;

        $shop_wr_id = (int) $shop_wr_id;
        if ($shop_wr_id < 1) {
            return array('count' => 0, 'average' => 0, 'distribution' => array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0));
        }

        $write_table = eottae_review_write_table();
        $sql = " select wr_2 from {$write_table}
            where wr_is_comment = 0
              and wr_1 = '{$shop_wr_id}'
              and (wr_4 = '' or wr_4 = 'visible')
            order by wr_id desc ";
        $result = sql_query($sql);

        $distribution = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0);
        $total = 0;
        $count = 0;

        while ($row = sql_fetch_array($result)) {
            $star = (int) round((float) $row['wr_2']);
            if ($star < 1) {
                $star = 1;
            }
            if ($star > 5) {
                $star = 5;
            }
            $distribution[$star]++;
            $total += (float) $row['wr_2'];
            $count++;
        }

        return array(
            'count'          => $count,
            'average'        => $count > 0 ? round($total / $count, 1) : 0,
            'distribution'   => $distribution,
        );
    }
}

if (!function_exists('eottae_get_shop_reviews')) {
    function eottae_get_shop_reviews($shop_wr_id, $limit = 10, $offset = 0)
    {
        global $g5;

        $shop_wr_id = (int) $shop_wr_id;
        $limit = max(1, min(50, (int) $limit));
        $offset = max(0, (int) $offset);
        if ($shop_wr_id < 1) {
            return array();
        }

        $write_table = eottae_review_write_table();
        $sql = " select * from {$write_table}
            where wr_is_comment = 0
              and wr_1 = '{$shop_wr_id}'
              and (wr_4 = '' or wr_4 = 'visible')
            order by wr_id desc
            limit {$offset}, {$limit} ";
        $result = sql_query($sql);
        $rows = array();

        while ($row = sql_fetch_array($result)) {
            $review = eottae_review_from_write($row);
            $review['replies'] = eottae_get_review_replies($review['wr_id']);
            $review['photos'] = eottae_get_review_photos(eottae_review_table(), $review['wr_id']);
            $rows[] = $review;
        }

        return $rows;
    }
}

if (!function_exists('eottae_get_review_replies')) {
    function eottae_get_review_replies($review_wr_id)
    {
        global $g5;

        $review_wr_id = (int) $review_wr_id;
        if ($review_wr_id < 1) {
            return array();
        }

        $write_table = eottae_review_write_table();
        $sql = " select * from {$write_table}
            where wr_is_comment = 1
              and wr_parent = '{$review_wr_id}'
            order by wr_comment asc, wr_id asc ";
        $result = sql_query($sql);
        $rows = array();

        while ($row = sql_fetch_array($result)) {
            $member_row = function_exists('get_member') ? get_member($row['mb_id']) : array('mb_id' => $row['mb_id']);
            $rows[] = array(
                'wr_id'    => (int) $row['wr_id'],
                'content'  => get_text(strip_tags($row['wr_content'])),
                'author'   => get_text($row['wr_name']),
                'mb_id'    => isset($row['mb_id']) ? $row['mb_id'] : '',
                'datetime' => $row['wr_datetime'],
                'is_biz'   => eottae_is_business_member($member_row),
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_get_review_photos')) {
    function eottae_get_review_photos($bo_table, $wr_id)
    {
        global $g5;

        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return array();
        }

        $bo_table = preg_replace('/[^a-z0-9_]/', '', $bo_table);
        $sql = " select bf_file, bf_source from {$g5['board_file_table']}
            where bo_table = '{$bo_table}' and wr_id = '{$wr_id}'
            order by bf_no asc ";
        $result = sql_query($sql);
        $photos = array();

        while ($row = sql_fetch_array($result)) {
            if (empty($row['bf_file'])) {
                continue;
            }
            $photos[] = G5_DATA_URL.'/file/'.$bo_table.'/'.$row['bf_file'];
        }

        return $photos;
    }
}

if (!function_exists('eottae_shop_map_thumb_table')) {
    function eottae_shop_map_thumb_table()
    {
        global $g5;

        return G5_TABLE_PREFIX.'eottae_shop_map_thumb';
    }
}

if (!function_exists('eottae_shop_map_thumb_ensure_table')) {
    function eottae_shop_map_thumb_ensure_table()
    {
        $table = eottae_shop_map_thumb_table();
        sql_query(" create table if not exists {$table} (
            bo_table varchar(20) not null,
            wr_id int(11) not null,
            file_name varchar(255) not null,
            source_name varchar(255) not null,
            created_at datetime not null,
            updated_at datetime not null,
            primary key (bo_table, wr_id)
        ) ", false);
    }
}

if (!function_exists('eottae_shop_map_thumb_dir')) {
    function eottae_shop_map_thumb_dir()
    {
        return G5_DATA_PATH.'/eottae-shop-map-thumb';
    }
}

if (!function_exists('eottae_shop_map_thumb_url_base')) {
    function eottae_shop_map_thumb_url_base()
    {
        return G5_DATA_URL.'/eottae-shop-map-thumb';
    }
}

if (!function_exists('eottae_shop_map_thumb_tmp_dir')) {
    function eottae_shop_map_thumb_tmp_dir()
    {
        return eottae_shop_map_thumb_dir().'/tmp';
    }
}

if (!function_exists('eottae_shop_map_thumb_tmp_url_base')) {
    function eottae_shop_map_thumb_tmp_url_base()
    {
        return eottae_shop_map_thumb_url_base().'/tmp';
    }
}

if (!function_exists('eottae_shop_map_thumb_get')) {
    function eottae_shop_map_thumb_get($bo_table, $wr_id)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return array();
        }

        $table = eottae_shop_map_thumb_table();
        $row = sql_fetch(" select * from {$table} where bo_table = '".sql_escape_string($bo_table)."' and wr_id = '{$wr_id}' ");
        if (empty($row['file_name'])) {
            return array();
        }

        return array(
            'file_name' => $row['file_name'],
            'source_name' => isset($row['source_name']) ? $row['source_name'] : '',
            'url' => eottae_shop_map_thumb_url_base().'/'.$row['file_name'],
        );
    }
}

if (!function_exists('eottae_shop_map_thumb_delete')) {
    function eottae_shop_map_thumb_delete($bo_table, $wr_id)
    {
        $thumb = eottae_shop_map_thumb_get($bo_table, $wr_id);
        if (!empty($thumb['file_name'])) {
            @unlink(eottae_shop_map_thumb_dir().'/'.$thumb['file_name']);
        }

        $table = eottae_shop_map_thumb_table();
        sql_query(" delete from {$table} where bo_table = '".sql_escape_string((string) $bo_table)."' and wr_id = '".(int) $wr_id."' ", false);
    }
}

if (!function_exists('eottae_shop_map_thumb_save_from_upload')) {
    function eottae_shop_map_thumb_save_from_upload($bo_table, $wr_id)
    {
        if (!empty($_POST['eottae_map_thumb_ai_tmp']) && eottae_shop_map_thumb_save_from_tmp($bo_table, $wr_id)) {
            return;
        }

        if (empty($_FILES['eottae_map_thumb']['name']) || empty($_FILES['eottae_map_thumb']['tmp_name'])) {
            if (!empty($_POST['eottae_map_thumb_del'])) {
                eottae_shop_map_thumb_delete($bo_table, $wr_id);
            }
            return;
        }
        if (!is_uploaded_file($_FILES['eottae_map_thumb']['tmp_name'])) {
            return;
        }

        $info = @getimagesize($_FILES['eottae_map_thumb']['tmp_name']);
        if (!$info) {
            return;
        }

        $ext_map = array(IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp');
        $type = isset($info[2]) ? (int) $info[2] : 0;
        if (empty($ext_map[$type])) {
            return;
        }

        $dir = eottae_shop_map_thumb_dir();
        if (!is_dir($dir)) {
            @mkdir($dir, G5_DIR_PERMISSION, true);
            @chmod($dir, G5_DIR_PERMISSION);
        }

        eottae_shop_map_thumb_delete($bo_table, $wr_id);

        $file_name = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table).'_'.(int) $wr_id.'_'.substr(md5(uniqid('', true)), 0, 12).'.'.$ext_map[$type];
        if (@move_uploaded_file($_FILES['eottae_map_thumb']['tmp_name'], $dir.'/'.$file_name)) {
            @chmod($dir.'/'.$file_name, G5_FILE_PERMISSION);
            $table = eottae_shop_map_thumb_table();
            $source = sql_escape_string(substr(trim(strip_tags((string) $_FILES['eottae_map_thumb']['name'])), 0, 255));
            sql_query(" replace into {$table}
                set bo_table = '".sql_escape_string((string) $bo_table)."',
                    wr_id = '".(int) $wr_id."',
                    file_name = '".sql_escape_string($file_name)."',
                    source_name = '{$source}',
                    created_at = now(),
                    updated_at = now() ", false);
        }
    }
}

if (!function_exists('eottae_shop_map_thumb_save_from_tmp')) {
    function eottae_shop_map_thumb_save_from_tmp($bo_table, $wr_id)
    {
        $tmp = isset($_POST['eottae_map_thumb_ai_tmp']) ? basename((string) $_POST['eottae_map_thumb_ai_tmp']) : '';
        if ($tmp === '' || !preg_match('/^[a-z0-9_.-]+$/i', $tmp)) {
            return false;
        }

        $src = eottae_shop_map_thumb_tmp_dir().'/'.$tmp;
        if (!is_file($src)) {
            return false;
        }

        $info = @getimagesize($src);
        if (!$info) {
            @unlink($src);
            return false;
        }

        $dir = eottae_shop_map_thumb_dir();
        if (!is_dir($dir)) {
            @mkdir($dir, G5_DIR_PERMISSION, true);
            @chmod($dir, G5_DIR_PERMISSION);
        }

        eottae_shop_map_thumb_delete($bo_table, $wr_id);

        $file_name = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table).'_'.(int) $wr_id.'_ai_'.substr(md5(uniqid('', true)), 0, 12).'.png';
        if (@rename($src, $dir.'/'.$file_name)) {
            @chmod($dir.'/'.$file_name, G5_FILE_PERMISSION);
            $table = eottae_shop_map_thumb_table();
            sql_query(" replace into {$table}
                set bo_table = '".sql_escape_string((string) $bo_table)."',
                    wr_id = '".(int) $wr_id."',
                    file_name = '".sql_escape_string($file_name)."',
                    source_name = 'AI 지도 썸네일',
                    created_at = now(),
                    updated_at = now() ", false);
            return true;
        }

        return false;
    }
}

if (!function_exists('eottae_shop_representative_image_url')) {
    function eottae_shop_representative_image_url($bo_table, $wr_id)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return '';
        }

        $row = sql_fetch(" select bf_file from {$g5['board_file_table']}
            where bo_table = '".sql_escape_string($bo_table)."' and wr_id = '{$wr_id}' and bf_file <> ''
            order by bf_no asc limit 1 ");
        if (empty($row['bf_file'])) {
            return '';
        }

        return G5_DATA_URL.'/file/'.$bo_table.'/'.$row['bf_file'];
    }
}

if (!function_exists('eottae_shop_listing_thumb_url')) {
    function eottae_shop_listing_thumb_url($bo_table, $wr_id, $row = null)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return '';
        }
        if ($bo_table === '') {
            $bo_table = defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop';
        }
        $storage_bo = function_exists('eottae_shop_storage_bo_table')
            ? eottae_shop_storage_bo_table($bo_table)
            : $bo_table;

        if (is_array($row) && !empty($row['file'][0]['file']) && !empty($row['file'][0]['path'])) {
            return $row['file'][0]['path'].'/'.$row['file'][0]['file'];
        }

        if (function_exists('eottae_shop_map_thumb_get')) {
            $map_thumb = eottae_shop_map_thumb_get($storage_bo, $wr_id);
            if (!empty($map_thumb['url'])) {
                return $map_thumb['url'];
            }
        }

        $representative = eottae_shop_representative_image_url($storage_bo, $wr_id);
        if ($representative !== '') {
            return $representative;
        }

        if (!function_exists('get_list_thumbnail')) {
            include_once G5_LIB_PATH.'/thumbnail.lib.php';
        }
        if (function_exists('get_list_thumbnail')) {
            $thumb = get_list_thumbnail($storage_bo, $wr_id, 200, 200, false, true);
            if (!empty($thumb['src'])) {
                return $thumb['src'];
            }
        }

        return '';
    }
}

if (!function_exists('eottae_user_reviewed_shop')) {
    function eottae_user_reviewed_shop($mb_id, $shop_wr_id)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        $shop_wr_id = (int) $shop_wr_id;
        if ($mb_id === '' || $shop_wr_id < 1) {
            return false;
        }

        $write_table = eottae_review_write_table();
        $row = sql_fetch(" select wr_id from {$write_table}
            where wr_is_comment = 0 and mb_id = '{$mb_id}' and wr_1 = '{$shop_wr_id}' limit 1 ");

        return !empty($row['wr_id']);
    }
}

if (!function_exists('eottae_get_member_reviews')) {
    function eottae_get_member_reviews($mb_id, $limit = 20)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        $limit = max(1, min(50, (int) $limit));
        if ($mb_id === '') {
            return array();
        }

        $write_table = eottae_review_write_table();
        $sql = " select * from {$write_table}
            where wr_is_comment = 0 and mb_id = '{$mb_id}'
            order by wr_id desc
            limit {$limit} ";
        $result = sql_query($sql);
        $rows = array();

        while ($row = sql_fetch_array($result)) {
            $rows[] = eottae_review_from_write($row);
        }

        return $rows;
    }
}

if (!function_exists('eottae_review_reply_token')) {
    function eottae_review_reply_token($regenerate = false)
    {
        $key = 'eottae_review_reply_token';
        $token = get_session($key);
        if ($regenerate || $token === '' || $token === null) {
            $token = md5(uniqid((string) mt_rand(), true));
            set_session($key, $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_business_owns_shop')) {
    function eottae_business_owns_shop($mb_id, $shop_wr_id, $bo_table = '')
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        $shop_wr_id = (int) $shop_wr_id;
        if ($mb_id === '' || $shop_wr_id < 1) {
            return false;
        }

        if ($bo_table === '' || !function_exists('eottae_is_shop_board') || !eottae_is_shop_board($bo_table)) {
            $bo_table = defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop';
        }
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        $shop_table = $g5['write_prefix'].$bo_table;
        $row = sql_fetch(" select wr_id from {$shop_table}
            where wr_id = '{$shop_wr_id}' and mb_id = '{$mb_id}' limit 1 ");

        return !empty($row['wr_id']);
    }
}

if (!function_exists('eottae_review_has_business_reply')) {
    function eottae_review_has_business_reply($review_wr_id, $mb_id = '')
    {
        global $g5;

        $review_wr_id = (int) $review_wr_id;
        if ($review_wr_id < 1) {
            return false;
        }

        $write_table = eottae_review_write_table();
        $mb_filter = '';
        if ($mb_id !== '') {
            $mb_filter = " and mb_id = '".sql_escape_string((string) $mb_id)."' ";
        }

        $result = sql_query(" select mb_id from {$write_table}
            where wr_is_comment = 1 and wr_parent = '{$review_wr_id}' {$mb_filter} ");
        while ($row = sql_fetch_array($result)) {
            $member_row = function_exists('get_member') ? get_member($row['mb_id']) : array('mb_id' => $row['mb_id']);
            if (eottae_is_business_member($member_row)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('eottae_review_token')) {
    function eottae_review_token($regenerate = false)
    {
        $key = 'eottae_review_token';
        $token = get_session($key);
        if ($regenerate || $token === '' || $token === null) {
            $token = md5(uniqid((string) mt_rand(), true));
            set_session($key, $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_render_review_section')) {
    function eottae_render_review_section($shop_wr_id, $shop_name = '')
    {
        eottae_load_component('review-section');

        if (function_exists('eottae_review_section_html')) {
            echo eottae_review_section_html($shop_wr_id, $shop_name);
        }
    }
}

if (!function_exists('eottae_business_pending_replies_count')) {
    function eottae_business_pending_replies_count($mb_id)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        if ($mb_id === '') {
            return 0;
        }

        $shop_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
        $review_table = eottae_review_write_table();

        $shops = sql_query(" select wr_id from {$shop_table} where mb_id = '{$mb_id}' ");
        $shop_ids = array();
        while ($row = sql_fetch_array($shops)) {
            $shop_ids[] = (int) $row['wr_id'];
        }

        if (empty($shop_ids)) {
            return 0;
        }

        $ids = implode(',', $shop_ids);
        $row = sql_fetch(" select count(*) as cnt from {$review_table} r
            where r.wr_is_comment = 0
              and r.wr_1 in ({$ids})
              and (r.wr_4 = '' or r.wr_4 = 'visible')
              and not exists (
                select 1 from {$review_table} c
                where c.wr_is_comment = 1 and c.wr_parent = r.wr_id and c.mb_id = '{$mb_id}'
              ) ");

        return isset($row['cnt']) ? (int) $row['cnt'] : 0;
    }
}

if (!function_exists('eottae_shop_save_token')) {
    function eottae_shop_save_token($regenerate = false)
    {
        $key = 'eottae_shop_save_token';
        $token = get_session($key);
        if ($regenerate || $token === '' || $token === null) {
            $token = md5(uniqid((string) mt_rand(), true));
            set_session($key, $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_is_shop_saved')) {
    function eottae_is_shop_saved($mb_id, $shop_wr_id)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        $shop_wr_id = (int) $shop_wr_id;
        if ($mb_id === '' || $shop_wr_id < 1) {
            return false;
        }

        $bo_table = EOTTae_SHOP_TABLE;
        $row = sql_fetch(" select ms_id from {$g5['scrap_table']}
            where mb_id = '{$mb_id}' and bo_table = '{$bo_table}' and wr_id = '{$shop_wr_id}' limit 1 ");

        return !empty($row['ms_id']);
    }
}

if (!function_exists('eottae_get_saved_shop_ids')) {
    function eottae_get_saved_shop_ids($mb_id, $limit = 30)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        $limit = max(1, min(50, (int) $limit));
        if ($mb_id === '') {
            return array();
        }

        $bo_table = EOTTae_SHOP_TABLE;
        $result = sql_query(" select wr_id from {$g5['scrap_table']}
            where mb_id = '{$mb_id}' and bo_table = '{$bo_table}'
            order by ms_id desc
            limit {$limit} ");
        $ids = array();
        while ($row = sql_fetch_array($result)) {
            $ids[] = (int) $row['wr_id'];
        }

        return $ids;
    }
}

if (!function_exists('eottae_track_recent_shop')) {
    function eottae_track_recent_shop($shop_wr_id)
    {
        $shop_wr_id = (int) $shop_wr_id;
        if ($shop_wr_id < 1) {
            return;
        }

        $list = get_session('eottae_recent_shops');
        if (!is_array($list)) {
            $list = array();
        }

        $filtered = array();
        foreach ($list as $id) {
            $id = (int) $id;
            if ($id > 0 && $id !== $shop_wr_id) {
                $filtered[] = $id;
            }
        }
        array_unshift($filtered, $shop_wr_id);
        set_session('eottae_recent_shops', array_slice($filtered, 0, 20));
    }
}

if (!function_exists('eottae_get_recent_shop_ids')) {
    function eottae_get_recent_shop_ids($limit = 20)
    {
        $list = get_session('eottae_recent_shops');
        if (!is_array($list)) {
            return array();
        }

        $limit = max(1, min(20, (int) $limit));
        $ids = array();
        foreach ($list as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[] = $id;
            }
            if (count($ids) >= $limit) {
                break;
            }
        }

        return $ids;
    }
}

if (!function_exists('eottae_get_shop_rows_by_ids')) {
    function eottae_get_shop_rows_by_ids($ids)
    {
        global $g5;

        if (!is_array($ids) || empty($ids)) {
            return array();
        }

        $clean = array();
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $clean[$id] = $id;
            }
        }
        if (empty($clean)) {
            return array();
        }

        $write_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
        $id_list = implode(',', $clean);
        $result = sql_query(" select * from {$write_table}
            where wr_id in ({$id_list}) and wr_is_comment = 0 ");
        $map = array();
        while ($row = sql_fetch_array($result)) {
            $map[(int) $row['wr_id']] = $row;
        }

        $bo_table = EOTTae_SHOP_TABLE;
        $rows = array();
        foreach ($clean as $id) {
            if (isset($map[$id])) {
                $map[$id]['bo_table'] = $bo_table;
                $map[$id]['href'] = G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$id;
                $rows[] = $map[$id];
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_get_member_inquiries')) {
    function eottae_get_member_inquiries($mb_id, $limit = 20)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        $limit = max(1, min(50, (int) $limit));
        if ($mb_id === '') {
            return array();
        }

        $bo_table = defined('EOTTae_INQUIRY_TABLE') ? EOTTae_INQUIRY_TABLE : 'inquiry';
        $write_table = $g5['write_prefix'].$bo_table;
        $exists = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = '{$bo_table}' ");
        if (empty($exists['cnt'])) {
            return array();
        }

        $result = sql_query(" select wr_id, wr_subject, wr_content, wr_datetime, wr_6
            from {$write_table}
            where wr_is_comment = 0 and mb_id = '{$mb_id}'
            order by wr_id desc
            limit {$limit} ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = array(
                'wr_id'    => (int) $row['wr_id'],
                'subject'  => get_text($row['wr_subject']),
                'content'  => get_text(strip_tags($row['wr_content'])),
                'status'   => get_text($row['wr_6'] ?: '신규'),
                'datetime' => $row['wr_datetime'],
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_event_table')) {
    function eottae_event_table()
    {
        return defined('EOTTae_EVENT_TABLE') ? EOTTae_EVENT_TABLE : 'event';
    }
}

if (!function_exists('eottae_get_events')) {
    function eottae_get_events($limit = 12, $category = '')
    {
        global $g5;

        $bo_table = eottae_event_table();
        $exists = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' ");
        if (empty($exists['cnt'])) {
            return array();
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $limit = max(1, min(30, (int) $limit));
        $where = " wr_is_comment = 0 ";
        if ($category !== '') {
            $category = sql_escape_string($category);
            $where .= " and ca_name = '{$category}' ";
        }

        $result = sql_query(" select wr_id, wr_subject, wr_content, ca_name, wr_datetime
            from {$write_table}
            where {$where}
            order by wr_id desc
            limit {$limit} ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = array(
                'wr_id'    => (int) $row['wr_id'],
                'subject'  => get_text($row['wr_subject']),
                'content'  => cut_str(strip_tags($row['wr_content']), 120),
                'category' => get_text($row['ca_name']),
                'datetime' => $row['wr_datetime'],
                'href'     => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$row['wr_id'],
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_grant_review_points')) {
    /**
     * 리뷰 작성 포인트 지급 (기본 + 사진 보너스)
     *
     * @return array{total:int, base:int, photo:int}
     */
    function eottae_grant_review_points($mb_id, $review_wr_id, $shop_name, $has_photo = false)
    {
        global $config;

        $review_wr_id = (int) $review_wr_id;
        $base = defined('EOTTae_REVIEW_POINT_BASE') ? (int) EOTTae_REVIEW_POINT_BASE : 30;
        $photo = defined('EOTTae_REVIEW_POINT_PHOTO') ? (int) EOTTae_REVIEW_POINT_PHOTO : 20;
        $bo_table = eottae_review_table();
        $shop_label = $shop_name !== '' ? get_text($shop_name) : '업체';

        $granted = array('total' => 0, 'base' => 0, 'photo' => 0);
        if ($review_wr_id < 1 || $mb_id === '' || empty($config['cf_use_point'])) {
            return $granted;
        }

        if ($base > 0) {
            $result = insert_point(
                $mb_id,
                $base,
                $shop_label.' 리뷰 작성',
                $bo_table,
                (string) $review_wr_id,
                'write'
            );
            if ($result === 1) {
                $granted['base'] = $base;
                $granted['total'] += $base;
            }
        }

        if ($has_photo && $photo > 0) {
            $result = insert_point(
                $mb_id,
                $photo,
                $shop_label.' 리뷰 사진 보너스',
                $bo_table,
                (string) $review_wr_id,
                'photo'
            );
            if ($result === 1) {
                $granted['photo'] = $photo;
                $granted['total'] += $photo;
            }
        }

        return $granted;
    }
}

if (!function_exists('eottae_revoke_review_points')) {
    function eottae_revoke_review_points($mb_id, $review_wr_id)
    {
        $review_wr_id = (int) $review_wr_id;
        if ($review_wr_id < 1 || $mb_id === '') {
            return false;
        }

        $bo_table = eottae_review_table();
        delete_point($mb_id, $bo_table, (string) $review_wr_id, 'write');
        delete_point($mb_id, $bo_table, (string) $review_wr_id, 'photo');

        return true;
    }
}

if (!function_exists('eottae_hide_review')) {
    function eottae_hide_review($review_wr_id)
    {
        global $g5;

        $review_wr_id = (int) $review_wr_id;
        if ($review_wr_id < 1) {
            return array('ok' => false, 'message' => '리뷰를 찾을 수 없습니다.');
        }

        $write_table = eottae_review_write_table();
        $row = sql_fetch(" select wr_id, mb_id, wr_4 from {$write_table}
            where wr_id = '{$review_wr_id}' and wr_is_comment = 0 limit 1 ");
        if (empty($row['wr_id'])) {
            return array('ok' => false, 'message' => '리뷰를 찾을 수 없습니다.');
        }
        if ($row['wr_4'] === 'hidden') {
            return array('ok' => true, 'message' => '이미 숨김 처리된 리뷰입니다.');
        }

        sql_query(" update {$write_table} set wr_4 = 'hidden' where wr_id = '{$review_wr_id}' ");
        if (!empty($row['mb_id'])) {
            eottae_revoke_review_points($row['mb_id'], $review_wr_id);
        }

        return array('ok' => true, 'message' => '리뷰가 숨김 처리되었습니다.');
    }
}

if (!function_exists('eottae_sync_shop_review_stats')) {
    /**
     * 업체 게시글 wr_comment(리뷰수)·wr_good(평점×10) 동기화 — 목록 정렬용
     */
    function eottae_sync_shop_review_stats($shop_wr_id)
    {
        global $g5;

        $shop_wr_id = (int) $shop_wr_id;
        if ($shop_wr_id < 1) {
            return;
        }

        $summary = eottae_get_shop_review_summary($shop_wr_id);
        $shop_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
        $count = (int) $summary['count'];
        $good = (int) round((float) $summary['average'] * 10);

        sql_query(" update {$shop_table} set wr_comment = '{$count}', wr_good = '{$good}' where wr_id = '{$shop_wr_id}' ");
    }
}

if (!function_exists('eottae_render_mypage_back')) {
    function eottae_render_mypage_back()
    {
        echo '<p class="mypage-subpage__back"><a href="'.eottae_mypage_url().'">← MY</a></p>';
    }
}

if (!function_exists('eottae_render_shop_save_button')) {
    function eottae_render_shop_save_button($shop_wr_id, $is_saved = false, $return_url = '')
    {
        global $is_member;

        $shop_wr_id = (int) $shop_wr_id;
        if ($return_url === '' && isset($_SERVER['REQUEST_URI'])) {
            $return_url = (string) $_SERVER['REQUEST_URI'];
        }

        if (!$is_member) {
            echo '<a href="'.eottae_login_url($return_url).'" class="shop-save-btn shop-save-btn--guest">찜하기</a>';
            return;
        }

        $token = eottae_shop_save_token(true);
        $saved = $is_saved ? '1' : '0';
        $label = $is_saved ? '찜 해제' : '찜하기';

        echo '<button type="button" class="shop-save-btn'.($is_saved ? ' is-saved' : '').'"';
        echo ' data-shop-save data-shop-id="'.$shop_wr_id.'" data-saved="'.$saved.'"';
        echo ' data-save-token="'.htmlspecialchars($token, ENT_QUOTES, 'UTF-8').'">';
        echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        echo '</button>';
    }
}

if (!function_exists('eottae_community_board_table')) {
    function eottae_community_board_table($bo_table = '')
    {
        if ($bo_table === '') {
            return defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community';
        }

        return preg_replace('/[^a-z0-9_]/', '', $bo_table);
    }
}

if (!function_exists('eottae_community_relative_time')) {
    function eottae_community_relative_time($datetime)
    {
        $ts = strtotime((string) $datetime);
        if (!$ts) {
            return '';
        }

        $diff = G5_SERVER_TIME - $ts;
        if ($diff < 60) {
            return '방금 전';
        }
        if ($diff < 3600) {
            return floor($diff / 60).'분 전';
        }
        if ($diff < 86400) {
            return floor($diff / 3600).'시간 전';
        }
        if ($diff < 172800) {
            return '어제';
        }
        if ($diff < 604800) {
            return floor($diff / 86400).'일 전';
        }

        return date('y.m.d', $ts);
    }
}

if (!function_exists('eottae_community_snippet')) {
    function eottae_community_snippet($content, $len = 110)
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $content)));
        if ($text === '') {
            return '';
        }

        return cut_str($text, (int) $len, '…');
    }
}

if (!function_exists('eottae_community_today_count')) {
    function eottae_community_today_count($bo_table = '')
    {
        global $g5;

        $bo_table = eottae_community_board_table($bo_table);
        $write_table = $g5['write_prefix'].$bo_table;
        $today = G5_TIME_YMD.' 00:00:00';
        $row = sql_fetch(" select count(*) as cnt from {$write_table}
            where wr_is_comment = 0 and wr_datetime >= '{$today}' ");

        return isset($row['cnt']) ? (int) $row['cnt'] : 0;
    }
}

if (!function_exists('eottae_community_category_tabs')) {
    function eottae_community_category_tabs($board)
    {
        global $g5;

        if (empty($board['bo_table'])) {
            return array();
        }

        $bo_table = $board['bo_table'];
        $write_table = $g5['write_prefix'].$bo_table;
        $total = isset($board['bo_count_write']) ? (int) $board['bo_count_write'] : 0;
        $tabs = array(
            array('slug' => '', 'label' => '전체', 'count' => $total),
        );

        if (!empty($board['bo_use_category']) && !empty($board['bo_category_list'])) {
            $categories = explode('|', $board['bo_category_list']);
            foreach ($categories as $cat) {
                $cat = trim($cat);
                if ($cat === '') {
                    continue;
                }
                $esc = sql_escape_string($cat);
                $row = sql_fetch(" select count(*) as cnt from {$write_table}
                    where wr_is_comment = 0 and ca_name = '{$esc}' ");
                $tabs[] = array(
                    'slug'  => $cat,
                    'label' => $cat,
                    'count' => isset($row['cnt']) ? (int) $row['cnt'] : 0,
                );
            }
        }

        return $tabs;
    }
}

if (!function_exists('eottae_community_list_thumb')) {
    function eottae_community_list_thumb($bo_table, $wr_id)
    {
        if (!function_exists('get_list_thumbnail')) {
            include_once G5_LIB_PATH.'/thumbnail.lib.php';
        }

        $thumb = get_list_thumbnail($bo_table, (int) $wr_id, 160, 160, false, true);

        return !empty($thumb['src']) ? $thumb['src'] : '';
    }
}

if (!function_exists('eottae_community_weekly_popular')) {
    function eottae_community_weekly_popular($bo_table = '', $limit = 5)
    {
        global $g5;

        $bo_table = eottae_community_board_table($bo_table);
        $write_table = $g5['write_prefix'].$bo_table;
        $limit = max(1, min(10, (int) $limit));
        $since = date('Y-m-d H:i:s', G5_SERVER_TIME - (7 * 86400));

        $result = sql_query(" select wr_id, wr_subject, wr_hit, wr_comment, ca_name
            from {$write_table}
            where wr_is_comment = 0 and wr_datetime >= '{$since}'
            order by wr_hit desc, wr_id desc
            limit {$limit} ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = array(
                'wr_id'    => (int) $row['wr_id'],
                'subject'  => get_text($row['wr_subject']),
                'hit'      => (int) $row['wr_hit'],
                'comment'  => (int) $row['wr_comment'],
                'category' => get_text($row['ca_name']),
                'url'      => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$row['wr_id'],
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_community_badge_class')) {
    function eottae_community_badge_class($ca_name, $is_notice = false)
    {
        if ($is_notice) {
            return 'community-badge--notice';
        }

        $map = array(
            '질문'    => 'community-badge--question',
            '정보'    => 'community-badge--info',
            '후기'    => 'community-badge--review',
            '자유'    => 'community-badge--free',
            '구인구직'=> 'community-badge--job',
            '공지'    => 'community-badge--notice',
        );

        return isset($map[$ca_name]) ? $map[$ca_name] : 'community-badge--default';
    }
}

if (!function_exists('eottae_community_is_new')) {
    function eottae_community_is_new($datetime, $hours = 24)
    {
        $ts = strtotime((string) $datetime);

        return $ts ? (G5_SERVER_TIME - $ts) < ((int) $hours * 3600) : false;
    }
}

if (!function_exists('eottae_community_is_hot')) {
    function eottae_community_is_hot($hit, $comment, $board = null)
    {
        $hit = (int) $hit;
        $comment = (int) $comment;
        $hot = 100;
        if (is_array($board) && !empty($board['bo_hot'])) {
            $hot = (int) $board['bo_hot'];
        }

        return $hit >= $hot || $comment >= 10;
    }
}

if (!function_exists('eottae_community_sort_options')) {
    function eottae_community_sort_options($current_sst = '', $current_sod = 'desc')
    {
        $options = array(
            array('label' => '최신순', 'sst' => 'wr_datetime', 'sod' => 'desc'),
            array('label' => '조회순', 'sst' => 'wr_hit', 'sod' => 'desc'),
            array('label' => '댓글순', 'sst' => 'wr_comment', 'sod' => 'desc'),
        );
        foreach ($options as &$opt) {
            $opt['active'] = ($current_sst === $opt['sst'] || ($current_sst === '' && $opt['sst'] === 'wr_datetime'));
        }

        return $options;
    }
}

if (!function_exists('eottae_community_region_options')) {
    function eottae_community_region_options()
    {
        return array('세부시티', '막탄', 'IT Park', '아얄라', '만다우에', '라푸라푸');
    }
}

if (!function_exists('eottae_shop_region_options')) {
    function eottae_shop_region_options()
    {
        return eottae_community_region_options();
    }
}

if (!function_exists('eottae_shop_region_match_rules')) {
    function eottae_shop_region_match_rules()
    {
        return array(
            'IT Park'  => array('it park', 'i.t. park', 'cebu it park', 'lahug'),
            '막탄'     => array('mactan', '막탄', 'mactan island'),
            '아얄라'   => array('ayala', 'cebu business park', '아얄라', 'it park ayala'),
            '만다우에' => array('mandaue', '만다우에'),
            '라푸라푸' => array('lapu-lapu', 'lapu lapu', 'lapulapu', '라푸라푸'),
        );
    }
}

if (!function_exists('eottae_shop_region_default_coords')) {
    function eottae_shop_region_default_coords()
    {
        return array(
            'IT Park'  => array('lat' => 10.3327, 'lng' => 123.9072),
            '막탄'     => array('lat' => 10.2956, 'lng' => 123.9736),
            '아얄라'   => array('lat' => 10.3180, 'lng' => 123.9050),
            '만다우에' => array('lat' => 10.3403, 'lng' => 123.9416),
            '라푸라푸' => array('lat' => 10.3103, 'lng' => 123.9494),
            '세부시티' => array('lat' => 10.3157, 'lng' => 123.8854),
        );
    }
}

if (!function_exists('eottae_shop_detect_region')) {
    /**
     * 주소·Geocoding address_components → 대표 지역(wr_2)
     *
     * @param string $address
     * @param array<int, array<string, string>> $components
     * @return string
     */
    function eottae_shop_detect_region($address, $components = array())
    {
        $parts = array((string) $address);
        if (is_array($components)) {
            foreach ($components as $component) {
                if (!is_array($component)) {
                    continue;
                }
                if (!empty($component['long_name'])) {
                    $parts[] = (string) $component['long_name'];
                }
                if (!empty($component['short_name'])) {
                    $parts[] = (string) $component['short_name'];
                }
            }
        }

        $text = mb_strtolower(implode(' ', $parts), 'UTF-8');
        if ($text === '') {
            return '';
        }

        foreach (eottae_shop_region_match_rules() as $region => $keywords) {
            foreach ($keywords as $keyword) {
                if ($keyword !== '' && mb_strpos($text, $keyword, 0, 'UTF-8') !== false) {
                    return $region;
                }
            }
        }

        if (mb_strpos($text, 'cebu city', 0, 'UTF-8') !== false
            || mb_strpos($text, '세부시티', 0, 'UTF-8') !== false
            || mb_strpos($text, '세부', 0, 'UTF-8') !== false
            || mb_strpos($text, 'talamban', 0, 'UTF-8') !== false
            || mb_strpos($text, 'banilad', 0, 'UTF-8') !== false
            || mb_strpos($text, 'sm seaside', 0, 'UTF-8') !== false
            || mb_strpos($text, 'sugbu', 0, 'UTF-8') !== false) {
            return '세부시티';
        }

        return '';
    }
}

if (!function_exists('eottae_shop_guess_coords')) {
    /**
     * API 좌표가 없을 때 내주변/지도에서 누락되지 않도록 대표지역 중심 좌표를 보정한다.
     */
    function eottae_shop_guess_coords($address = '', $region = '')
    {
        $region = trim((string) $region);
        if ($region === '' && $address !== '') {
            $region = eottae_shop_detect_region($address);
        }

        $coords = eottae_shop_region_default_coords();
        if ($region !== '' && isset($coords[$region])) {
            return $coords[$region];
        }

        return array();
    }
}

if (!function_exists('eottae_shop_apply_fallback_coords_to_post')) {
    function eottae_shop_apply_fallback_coords_to_post()
    {
        $lat = isset($_POST['wr_9']) ? trim((string) $_POST['wr_9']) : '';
        $lng = isset($_POST['wr_10']) ? trim((string) $_POST['wr_10']) : '';
        if ($lat !== '' && $lng !== '' && is_numeric($lat) && is_numeric($lng)) {
            return;
        }

        $address = isset($_POST['wr_3']) ? trim((string) $_POST['wr_3']) : '';
        $region = isset($_POST['wr_2']) ? trim((string) $_POST['wr_2']) : '';
        $coords = eottae_shop_guess_coords($address, $region);
        if (empty($coords)) {
            return;
        }

        $_POST['wr_9'] = (string) $coords['lat'];
        $_POST['wr_10'] = (string) $coords['lng'];
    }
}

if (!function_exists('eottae_shop_master_categories')) {
    function eottae_shop_master_categories()
    {
        return array(
            '맛집',
            '카페',
            '마사지',
            '미용',
            '병원',
            '마트',
            '숙소',
            '학원',
            '렌트카',
            '투어',
            '세탁',
            '법률',
            '회계',
            '부동산',
            '배달',
            '반려동물',
            '헬스',
            'IT',
            '쇼핑',
            '기타',
        );
    }
}

if (!function_exists('eottae_shop_master_category_pipe')) {
    function eottae_shop_master_category_pipe()
    {
        return implode('|', eottae_shop_master_categories());
    }
}

if (!function_exists('eottae_shop_merge_category_lists')) {
    function eottae_shop_merge_category_lists($current, $master = null)
    {
        if (!is_array($master)) {
            $master = eottae_shop_master_categories();
        }

        $current_cats = array_values(array_filter(array_map('trim', explode('|', (string) $current))));
        $merged = array();

        foreach ($master as $cat) {
            if ($cat !== '' && !in_array($cat, $merged, true)) {
                $merged[] = $cat;
            }
        }
        foreach ($current_cats as $cat) {
            if ($cat !== '' && !in_array($cat, $merged, true)) {
                $merged[] = $cat;
            }
        }

        return implode('|', $merged);
    }
}

if (!function_exists('eottae_shop_sync_board_categories')) {
    function eottae_shop_sync_board_categories()
    {
        global $g5;

        if (!function_exists('eottae_shop_table')) {
            return false;
        }

        $bo_table = eottae_shop_table();
        $row = sql_fetch(" select bo_category_list from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' ");
        if (empty($row)) {
            return false;
        }

        $merged = eottae_shop_merge_category_lists($row['bo_category_list']);
        if ($merged === trim((string) $row['bo_category_list'])) {
            return true;
        }

        sql_query(" update {$g5['board_table']} set bo_category_list = '".sql_escape_string($merged)."' where bo_table = '".sql_escape_string($bo_table)."' ");

        return true;
    }
}

if (!function_exists('eottae_shop_board_categories')) {
    function eottae_shop_board_categories($board)
    {
        $cats = array();
        if (is_array($board) && !empty($board['bo_category_list'])) {
            $cats = array_values(array_filter(array_map('trim', explode('|', $board['bo_category_list']))));
        }
        if (empty($cats)) {
            $cats = array('기타');
        }

        return $cats;
    }
}

if (!function_exists('eottae_shop_resolve_category')) {
    function eottae_shop_resolve_category($board, $candidate = '')
    {
        $allowed = eottae_shop_board_categories($board);
        $candidate = trim((string) $candidate);

        if ($candidate !== '' && in_array($candidate, $allowed, true)) {
            return $candidate;
        }

        if (in_array('기타', $allowed, true)) {
            return '기타';
        }

        return $allowed[0];
    }
}

if (!function_exists('eottae_shop_weekday_options')) {
    function eottae_shop_weekday_options()
    {
        return array('월', '화', '수', '목', '금', '토', '일');
    }
}

if (!function_exists('eottae_shop_hours_form_state')) {
    function eottae_shop_hours_form_state($wr_6, $options)
    {
        $wr_6 = trim((string) $wr_6);
        $options = is_array($options) ? $options : array();
        $is_custom = $wr_6 !== '' && !in_array($wr_6, $options, true);

        return array(
            'select' => $is_custom ? '__custom__' : $wr_6,
            'custom' => $is_custom ? $wr_6 : '',
        );
    }
}

if (!function_exists('eottae_shop_parse_closed_days')) {
    function eottae_shop_parse_closed_days($raw)
    {
        $result = array(
            'weekdays' => array(),
            'special'  => '',
            'custom'   => '',
        );

        $raw = trim((string) $raw);
        if ($raw === '') {
            return $result;
        }

        if ($raw === '연중무휴' || strpos($raw, '연중무휴') === 0) {
            $result['special'] = '연중무휴';
            $rest = trim(preg_replace('/^연중무휴\s*(?:·|,|\/)?\s*/u', '', $raw));

            if ($rest !== '' && $rest !== '연중무휴') {
                $result['custom'] = $rest;
            }

            return $result;
        }

        if (strpos($raw, '비정기') !== false) {
            $result['special'] = '비정기';
            $rest = trim(preg_replace('/^비정기\s*휴무\s*(?:·|,|\/)?\s*/u', '', $raw));

            if ($rest !== '' && strpos($rest, '비정기') === false) {
                $result['custom'] = $rest;
            }

            return $result;
        }

        $remaining = $raw;
        foreach (eottae_shop_weekday_options() as $weekday) {
            if (preg_match('/(?:매주\s*)?'.preg_quote($weekday, '/').'요일/u', $raw)) {
                $result['weekdays'][] = $weekday;
                $remaining = preg_replace('/(?:매주\s*)?'.preg_quote($weekday, '/').'요일\s*(?:·|,|\/)?\s*/u', '', $remaining);
            }
        }

        $remaining = trim(preg_replace('/^[\s·,\/]+|[\s·,\/]+$/u', '', $remaining));
        if ($remaining !== '') {
            $result['custom'] = $remaining;
        }

        return $result;
    }
}

if (!function_exists('eottae_shop_format_closed_days')) {
    function eottae_shop_format_closed_days($weekdays, $special, $custom)
    {
        $weekdays = is_array($weekdays) ? array_values(array_filter($weekdays)) : array();
        $special = trim((string) $special);
        $custom = trim((string) $custom);

        if ($special === '연중무휴') {
            return $custom !== '' ? '연중무휴 · '.$custom : '연중무휴';
        }

        if ($special === '비정기') {
            return $custom !== '' ? '비정기 휴무 · '.$custom : '비정기 휴무';
        }

        $parts = array();
        foreach ($weekdays as $weekday) {
            $weekday = trim((string) $weekday);
            if ($weekday !== '') {
                $parts[] = '매주 '.$weekday.'요일';
            }
        }

        $base = implode(' · ', $parts);
        if ($base === '' && $custom !== '') {
            return $custom;
        }

        if ($custom !== '') {
            return $base !== '' ? $base.' · '.$custom : $custom;
        }

        return $base;
    }
}

if (!function_exists('eottae_shop_merge_closed_days_from_post')) {
    function eottae_shop_merge_closed_days_from_post()
    {
        $weekdays = isset($_POST['eottae_closed_weekday']) ? (array) $_POST['eottae_closed_weekday'] : array();
        $weekdays = array_map(function ($day) {
            return preg_replace('/[^가-힣]/u', '', (string) $day);
        }, $weekdays);
        $weekdays = array_values(array_filter($weekdays));

        $special = isset($_POST['eottae_closed_special']) ? trim(strip_tags((string) $_POST['eottae_closed_special'])) : '';
        if ($special !== '연중무휴' && $special !== '비정기') {
            $special = '';
        }

        $custom = isset($_POST['eottae_closed_custom']) ? trim(strip_tags((string) $_POST['eottae_closed_custom'])) : '';

        return eottae_shop_format_closed_days($weekdays, $special, $custom);
    }
}

if (!function_exists('eottae_shop_prepare_write_post')) {
    function eottae_shop_prepare_write_post($board)
    {
        if (empty($board['bo_table']) || !eottae_is_shop_board($board['bo_table'])) {
            return;
        }

        $allowed = eottae_shop_board_categories($board);
        $ca = isset($_POST['ca_name']) ? trim((string) $_POST['ca_name']) : '';
        $wr1 = isset($_POST['wr_1']) ? trim((string) $_POST['wr_1']) : '';

        if ($ca !== '' && !in_array($ca, $allowed, true) && function_exists('eottae_shop_detect_region')) {
            $maybe_region = eottae_shop_detect_region($ca);
            if ($maybe_region === $ca) {
                if (empty($_POST['wr_2'])) {
                    $_POST['wr_2'] = $ca;
                }
                $ca = '';
            }
        }

        if ($ca === '' && $wr1 !== '' && in_array($wr1, $allowed, true)) {
            $ca = $wr1;
        } elseif ($ca === '' && $wr1 !== '') {
            $wr1_region = function_exists('eottae_shop_detect_region') ? eottae_shop_detect_region($wr1) : '';
            if ($wr1_region === $wr1 && empty($_POST['wr_2'])) {
                $_POST['wr_2'] = $wr1;
            }
        }

        $resolved = eottae_shop_resolve_category($board, $ca !== '' ? $ca : $wr1);
        $_POST['ca_name'] = $resolved;
        $_POST['wr_1'] = $resolved;

        $address = isset($_POST['wr_3']) ? trim((string) $_POST['wr_3']) : '';
        $region = isset($_POST['wr_2']) ? trim((string) $_POST['wr_2']) : '';
        if ($region === '' && $address !== '' && function_exists('eottae_shop_detect_region')) {
            $_POST['wr_2'] = eottae_shop_detect_region($address);
        }
        $sns_payload = array();
        $has_sns_fields = false;
        foreach (array('instagram', 'tiktok', 'facebook', 'naver_blog', 'youtube') as $sns_key) {
            $post_key = 'eottae_sns_'.$sns_key;
            if (isset($_POST[$post_key])) {
                $has_sns_fields = true;
            }
            $sns_payload[$sns_key] = isset($_POST[$post_key]) ? trim(strip_tags((string) $_POST[$post_key])) : '';
        }
        if ($has_sns_fields) {
            $_POST['wr_link2'] = array_filter($sns_payload) ? json_encode($sns_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        }
        if (isset($_POST['wr_6_preset'])) {
            $preset = trim((string) $_POST['wr_6_preset']);
            if ($preset === '__custom__') {
                $_POST['wr_6'] = isset($_POST['wr_6_custom']) ? trim(strip_tags((string) $_POST['wr_6_custom'])) : '';
            } elseif ($preset !== '') {
                $_POST['wr_6'] = $preset;
            }
        }

        if (isset($_POST['eottae_closed_weekday']) || isset($_POST['eottae_closed_special']) || isset($_POST['eottae_closed_custom'])) {
            $_POST['wr_7'] = eottae_shop_merge_closed_days_from_post();
        }

        if (function_exists('eottae_shop_apply_fallback_coords_to_post')) {
            eottae_shop_apply_fallback_coords_to_post();
        }
    }
}

if (!function_exists('eottae_shop_quick_categories')) {
    function eottae_shop_quick_categories($board)
    {
        $items = array(array('slug' => '', 'label' => '전체'));
        foreach (eottae_shop_board_categories($board) as $cat) {
            $items[] = array('slug' => $cat, 'label' => $cat);
        }

        return $items;
    }
}

if (!function_exists('eottae_shop_sort_links')) {
    function eottae_shop_sort_links($current_sst = '')
    {
        $maps_enabled = false;
        if (is_file(G5_PATH.'/components/maps/map-config.php')) {
            include_once G5_PATH.'/components/maps/map-config.php';
            $maps_enabled = function_exists('onoff_map_has_api_key') && onoff_map_has_api_key();
        }

        return array(
            array('label' => '가까운순', 'sst' => 'near', 'sod' => 'asc', 'disabled' => !$maps_enabled),
            array('label' => '인기순', 'sst' => 'wr_hit', 'sod' => 'desc'),
            array('label' => '리뷰많은순', 'sst' => 'wr_comment', 'sod' => 'desc'),
            array('label' => '평점높은순', 'sst' => 'wr_good', 'sod' => 'desc'),
            array('label' => '최신등록순', 'sst' => 'wr_datetime', 'sod' => 'desc'),
        );
    }
}

if (!function_exists('eottae_shop_user_coords_from_request')) {
    function eottae_shop_user_coords_from_request()
    {
        $lat = isset($_GET['eottae_lat']) ? trim((string) $_GET['eottae_lat']) : '';
        $lng = isset($_GET['eottae_lng']) ? trim((string) $_GET['eottae_lng']) : '';
        if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
            return null;
        }

        return array(
            'lat' => (float) $lat,
            'lng' => (float) $lng,
        );
    }
}

if (!function_exists('eottae_haversine_km')) {
    function eottae_haversine_km($lat1, $lng1, $lat2, $lng2)
    {
        $r = 6371;
        $d_lat = deg2rad($lat2 - $lat1);
        $d_lng = deg2rad($lng2 - $lng1);
        $a = sin($d_lat / 2) * sin($d_lat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($d_lng / 2) * sin($d_lng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $r * $c;
    }
}

if (!function_exists('eottae_shop_sort_list_by_distance')) {
    function eottae_shop_sort_list_by_distance(&$list, $user_lat, $user_lng)
    {
        if (!is_array($list)) {
            return;
        }

        foreach ($list as $idx => $row) {
            if (!is_array($row)) {
                continue;
            }
            $shop = eottae_shop_from_write($row);
            $lat = $shop['lat'];
            $lng = $shop['lng'];
            if (($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) && function_exists('eottae_shop_guess_coords')) {
                $fallback = eottae_shop_guess_coords($shop['address'], $shop['region']);
                if (!empty($fallback)) {
                    $lat = $fallback['lat'];
                    $lng = $fallback['lng'];
                    $list[$idx]['wr_9'] = (string) $lat;
                    $list[$idx]['wr_10'] = (string) $lng;
                    $list[$idx]['_eottae_coord_fallback'] = true;
                }
            }

            if ($lat !== '' && $lng !== '' && is_numeric($lat) && is_numeric($lng)) {
                $list[$idx]['_eottae_distance_km'] = eottae_haversine_km(
                    (float) $user_lat,
                    (float) $user_lng,
                    (float) $lat,
                    (float) $lng
                );
            } else {
                $list[$idx]['_eottae_distance_km'] = 99999;
            }
        }

        usort($list, function ($a, $b) {
            $da = isset($a['_eottae_distance_km']) ? (float) $a['_eottae_distance_km'] : 99999;
            $db = isset($b['_eottae_distance_km']) ? (float) $b['_eottae_distance_km'] : 99999;
            if ($da === $db) {
                return 0;
            }

            return ($da < $db) ? -1 : 1;
        });
    }
}

if (!function_exists('eottae_shop_format_distance_km')) {
    function eottae_shop_format_distance_km($km)
    {
        $km = (float) $km;
        if ($km >= 99999) {
            return '';
        }
        if ($km < 1) {
            return (int) round($km * 1000).'m';
        }

        return number_format($km, 1).'km';
    }
}

if (!function_exists('eottae_shop_build_nearby_list')) {
    /**
     * 가까운순은 그누보드 기본 페이징 결과 안에서만 정렬하면 정확도가 떨어진다.
     * 필터 조건에 맞는 업체 후보를 넓게 가져와 거리순으로 재구성한다.
     */
    function eottae_shop_build_nearby_list($bo_table, $board, $board_skin_url, $user_lat, $user_lng, $args = array())
    {
        global $g5, $config;

        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        if ($bo_table === '' || !function_exists('eottae_is_shop_board') || !eottae_is_shop_board($bo_table)) {
            return array();
        }

        $master = function_exists('eottae_shop_segment_master_category')
            ? eottae_shop_segment_master_category($bo_table)
            : '';
        if ($master !== '') {
            $write_table = $g5['write_prefix'].eottae_shop_table();
        } else {
            $write_table = $g5['write_prefix'].$bo_table;
        }
        $exists = sql_fetch(" show tables like '".sql_escape_string($write_table)."' ");
        if (empty($exists)) {
            return array();
        }

        $where = array("wr_is_comment = 0");
        if ($master !== '') {
            $where[] = "wr_1 = '".sql_escape_string($master)."'";
        }
        $sca = isset($args['sca']) ? trim((string) $args['sca']) : '';
        $sfl = isset($args['sfl']) ? trim((string) $args['sfl']) : '';
        $stx = isset($args['stx']) ? trim((string) $args['stx']) : '';

        if ($sca !== '') {
            $where[] = "ca_name = '".sql_escape_string($sca)."'";
        }

        if ($stx !== '') {
            $stx_sql = sql_escape_string($stx);
            if ($sfl === 'wr_2') {
                $where[] = "wr_2 = '{$stx_sql}'";
            } elseif ($sfl === 'wr_subject||wr_content') {
                $where[] = "(wr_subject like '%{$stx_sql}%' or wr_content like '%{$stx_sql}%')";
            } elseif (in_array($sfl, array('wr_subject', 'wr_content', 'wr_1', 'wr_2', 'wr_3'), true)) {
                $where[] = "{$sfl} like '%{$stx_sql}%'";
            }
        }

        $where_sql = implode(' and ', $where);
        $count = sql_fetch(" select count(*) as cnt from {$write_table} where {$where_sql} ");
        $total_count = isset($count['cnt']) ? (int) $count['cnt'] : 0;

        $candidate_limit = min(1000, max(100, $total_count));
        $result = sql_query(" select * from {$write_table} where {$where_sql} order by wr_id desc limit {$candidate_limit} ", false);
        if (!$result) {
            return array();
        }

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = get_list($row, $board, $board_skin_url, G5_IS_MOBILE ? $board['bo_mobile_subject_len'] : $board['bo_subject_len']);
        }

        eottae_shop_sort_list_by_distance($rows, $user_lat, $user_lng);

        $page = isset($args['page']) ? max(1, (int) $args['page']) : 1;
        $page_rows = isset($args['page_rows']) ? max(1, (int) $args['page_rows']) : (G5_IS_MOBILE ? (int) $board['bo_mobile_page_rows'] : (int) $board['bo_page_rows']);
        if ($page_rows < 1) {
            $page_rows = 15;
        }

        $from = ($page - 1) * $page_rows;
        $list = array_slice($rows, $from, $page_rows);
        $total_page = $page_rows > 0 ? (int) ceil($total_count / $page_rows) : 1;

        $paging_params = array(
            'sst' => 'near',
            'sod' => 'asc',
            'eottae_lat' => $user_lat,
            'eottae_lng' => $user_lng,
        );
        if ($sca !== '') {
            $paging_params['sca'] = $sca;
        }
        if ($stx !== '' && $sfl !== '') {
            $paging_params['sfl'] = $sfl;
            $paging_params['stx'] = $stx;
        }

        $paging_qs = http_build_query($paging_params, '', '&amp;');
        $pages = get_paging(
            G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'],
            $page,
            max(1, $total_page),
            get_pretty_url($bo_table, '', $paging_qs.'&amp;page=')
        );

        return array(
            'list' => $list,
            'total_count' => $total_count,
            'write_pages' => $pages,
        );
    }
}

if (!function_exists('eottae_shop_append_coords_query')) {
    function eottae_shop_append_coords_query($params = array())
    {
        $coords = eottae_shop_user_coords_from_request();
        if ($coords) {
            $params['eottae_lat'] = $coords['lat'];
            $params['eottae_lng'] = $coords['lng'];
        }

        return $params;
    }
}

if (!function_exists('eottae_shop_is_sort_active')) {
    function eottae_shop_is_sort_active($link, $current_sst, $current_sod)
    {
        if (!empty($link['disabled'])) {
            return false;
        }
        if ($current_sst === '') {
            return $link['sst'] === 'wr_datetime';
        }

        if ($link['sst'] === 'near') {
            return $current_sst === 'near';
        }

        return $link['sst'] === $current_sst && ($current_sod === '' || $link['sod'] === $current_sod);
    }
}

if (!function_exists('eottae_shop_list_snippet')) {
    function eottae_shop_list_snippet($content, $len = 90)
    {
        return eottae_community_snippet($content, $len);
    }
}

if (!function_exists('eottae_shop_map_markers')) {
    function eottae_shop_map_markers($list, $bo_table = '')
    {
        $markers = array();
        if (!is_array($list)) {
            return $markers;
        }

        foreach ($list as $row) {
            if (!is_array($row)) {
                continue;
            }
            $shop = eottae_shop_from_write($row);
            $lat = $shop['lat'];
            $lng = $shop['lng'];
            if (($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) && function_exists('eottae_shop_guess_coords')) {
                $fallback = eottae_shop_guess_coords($shop['address'], $shop['region']);
                if (!empty($fallback)) {
                    $lat = $fallback['lat'];
                    $lng = $fallback['lng'];
                }
            }
            if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
                continue;
            }
            $marker_bo_table = $bo_table !== '' ? $bo_table : (defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop');
            $thumb = eottae_shop_map_thumb_get($marker_bo_table, $shop['wr_id']);
            $thumbnail = !empty($thumb['url']) ? $thumb['url'] : '';
            if ($thumbnail === '' && !empty($row['file'][0]['file']) && !empty($row['file'][0]['path'])) {
                $thumbnail = $row['file'][0]['path'].'/'.$row['file'][0]['file'];
            } else            if ($thumbnail === '') {
                $thumbnail = eottae_shop_representative_image_url($marker_bo_table, $shop['wr_id']);
            }
            $summary = function_exists('eottae_get_shop_review_summary')
                ? eottae_get_shop_review_summary((int) $shop['wr_id'])
                : array('average' => 0, 'count' => 0);
            $markers[] = array(
                'wr_id'    => (int) $shop['wr_id'],
                'name'     => $shop['name'],
                'category' => $shop['category'],
                'region'   => $shop['region'],
                'lat'      => $lat,
                'lng'      => $lng,
                'thumbnail' => $thumbnail,
                'rating'   => isset($summary['average']) ? (float) $summary['average'] : 0,
                'review_count' => isset($summary['count']) ? (int) $summary['count'] : 0,
                'url'      => function_exists('eottae_shop_view_url')
                    ? eottae_shop_view_url($shop['wr_id'], $bo_table !== '' ? $bo_table : eottae_shop_table())
                    : G5_BBS_URL.'/board.php?bo_table='.($bo_table !== '' ? $bo_table : eottae_shop_table()).'&wr_id='.$shop['wr_id'],
            );
        }

        return $markers;
    }
}

if (!function_exists('eottae_shop_map_locations_json')) {
    /**
     * Google Maps JS용 업체 좌표 JSON
     *
     * @param array<int, array<string, mixed>> $markers
     * @return string
     */
    function eottae_shop_map_locations_json($markers)
    {
        $locations = array();
        foreach ((array) $markers as $marker) {
            if (!is_array($marker)) {
                continue;
            }
            $lat = isset($marker['lat']) ? trim((string) $marker['lat']) : '';
            $lng = isset($marker['lng']) ? trim((string) $marker['lng']) : '';
            if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
                continue;
            }
            $locations[] = array(
                'id'       => isset($marker['wr_id']) ? (int) $marker['wr_id'] : 0,
                'name'     => isset($marker['name']) ? (string) $marker['name'] : '',
                'category' => isset($marker['category']) ? (string) $marker['category'] : '',
                'region'   => isset($marker['region']) ? (string) $marker['region'] : '',
                'lat'      => (float) $lat,
                'lng'      => (float) $lng,
                'link'     => isset($marker['url']) ? (string) $marker['url'] : '',
                'thumbnail' => isset($marker['thumbnail']) ? (string) $marker['thumbnail'] : '',
                'rating'   => isset($marker['rating']) ? (float) $marker['rating'] : 0,
                'review_count' => isset($marker['review_count']) ? (int) $marker['review_count'] : 0,
            );
        }

        $json = json_encode($locations, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return '[]';
        }

        return $json;
    }
}

if (!function_exists('eottae_enqueue_google_maps')) {
    /**
     * Google Maps API + eottae-shop-map.js 로드
     *
     * @return bool API 키가 있어 스크립트를 등록했으면 true
     */
    function eottae_enqueue_google_maps()
    {
        static $enqueued = false;

        if ($enqueued) {
            return function_exists('onoff_map_has_api_key') && onoff_map_has_api_key();
        }

        if (!is_file(G5_PATH.'/components/maps/map-config.php')) {
            return false;
        }

        include_once G5_PATH.'/components/maps/map-config.php';

        if (!function_exists('onoff_map_has_api_key') || !onoff_map_has_api_key()) {
            return false;
        }

        $cfg = onoff_map_get_config();
        $key = isset($cfg['api_key']) ? htmlspecialchars($cfg['api_key'], ENT_QUOTES, 'UTF-8') : '';
        if ($key === '') {
            return false;
        }

        add_javascript('<script src="'.G5_JS_URL.'/eottae-shop-map.js"></script>', 25);
        add_javascript(
            '<script src="https://maps.googleapis.com/maps/api/js?key='.$key.'&amp;callback=initEottaeShopMaps" defer></script>',
            5
        );

        $enqueued = true;

        return true;
    }
}

if (!function_exists('eottae_google_maps_api_key')) {
    function eottae_google_maps_api_key()
    {
        if (!is_file(G5_PATH.'/components/maps/map-config.php')) {
            return '';
        }

        include_once G5_PATH.'/components/maps/map-config.php';

        if (!function_exists('onoff_map_has_api_key') || !onoff_map_has_api_key()) {
            return '';
        }

        $cfg = onoff_map_get_config();

        return isset($cfg['api_key']) ? trim((string) $cfg['api_key']) : '';
    }
}

if (!function_exists('eottae_google_geocoder_script')) {
    /**
     * 업체 등록 전용 Geocoder 스크립트.
     * write.php는 head.sub.php가 스킨보다 먼저 출력되므로 add_javascript()가 아닌 스킨에서 직접 출력한다.
     */
    function eottae_google_geocoder_script()
    {
        $key = eottae_google_maps_api_key();
        if ($key === '') {
            return '';
        }

        $safe_key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');

        return '<script>window.initEottaeGeocoderBootstrap=function(){document.dispatchEvent(new CustomEvent("eottae:geocoder-ready"));};</script>'."\n"
            .'<script src="https://maps.googleapis.com/maps/api/js?key='.$safe_key.'&amp;callback=initEottaeGeocoderBootstrap" async defer></script>';
    }
}

if (!function_exists('eottae_shop_backfill_missing_coords')) {
    /**
     * 기존 등록 업체 중 좌표가 없는 글을 대표지역/주소 기반 중심 좌표로 보정한다.
     * 정확 좌표는 업체 수정에서 덮어쓸 수 있고, 이 보정값은 내주변/지도 누락 방지용이다.
     */
    function eottae_shop_backfill_missing_coords($limit_per_board = 100)
    {
        global $g5;

        if (!function_exists('eottae_shop_board_tables') || !function_exists('eottae_shop_guess_coords')) {
            return 0;
        }

        $updated = 0;
        $limit_per_board = max(1, (int) $limit_per_board);
        foreach (eottae_shop_board_tables() as $bo_table) {
            $write_table = $g5['write_prefix'].$bo_table;
            $exists = sql_fetch(" show tables like '".sql_escape_string($write_table)."' ");
            if (empty($exists)) {
                continue;
            }

            $sql = " select wr_id, wr_2, wr_3
                from {$write_table}
                where wr_is_comment = 0
                  and (wr_9 = '' or wr_10 = '' or wr_9 is null or wr_10 is null)
                order by wr_id desc
                limit {$limit_per_board} ";
            $result = sql_query($sql, false);
            if (!$result) {
                continue;
            }

            while ($row = sql_fetch_array($result)) {
                $coords = eottae_shop_guess_coords(isset($row['wr_3']) ? $row['wr_3'] : '', isset($row['wr_2']) ? $row['wr_2'] : '');
                if (empty($coords)) {
                    continue;
                }

                $region = isset($row['wr_2']) ? trim((string) $row['wr_2']) : '';
                if ($region === '' && function_exists('eottae_shop_detect_region')) {
                    $region = eottae_shop_detect_region(isset($row['wr_3']) ? $row['wr_3'] : '');
                }

                $sets = array(
                    "wr_9 = '".sql_escape_string((string) $coords['lat'])."'",
                    "wr_10 = '".sql_escape_string((string) $coords['lng'])."'",
                );
                if ($region !== '') {
                    $sets[] = "wr_2 = '".sql_escape_string($region)."'";
                }
                sql_query(" update {$write_table} set ".implode(', ', $sets)." where wr_id = '".(int) $row['wr_id']."' ");
                $updated++;
            }
        }

        return $updated;
    }
}

if (!function_exists('eottae_shop_table')) {
    function eottae_shop_table()
    {
        return defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop';
    }
}

if (!function_exists('eottae_shop_list_url')) {
    function eottae_shop_list_url($params = array())
    {
        $base = G5_BBS_URL.'/board.php?bo_table='.eottae_shop_table();
        if (empty($params)) {
            return $base;
        }

        return $base.'&'.http_build_query($params);
    }
}

if (!function_exists('eottae_shop_view_url')) {
    /**
     * 업체(shop 게시판) 상세 URL — 영카트 /shop/{id} 짧은주소와 충돌하지 않도록 board.php 사용
     *
     * @param int|string $wr_id
     * @param string     $bo_table
     * @param string     $query_string
     * @return string
     */
    function eottae_shop_view_url($wr_id, $bo_table = '', $query_string = '')
    {
        $wr_id = (int) $wr_id;
        if ($bo_table === '') {
            $bo_table = eottae_shop_table();
        }
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($wr_id < 1 || $bo_table === '') {
            return G5_BBS_URL.'/board.php';
        }

        $url = G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id;
        if ($query_string !== '') {
            $qs = ltrim((string) $query_string, '&');
            if ($qs !== '') {
                $url .= '&'.$qs;
            }
        }

        return $url;
    }
}

if (!function_exists('eottae_pretty_shop_board_url')) {
    /**
     * get_pretty_url('shop', …) 이 영카트 item.php 로 가는 것을 shop 게시판 URL로 교정
     */
    function eottae_pretty_shop_board_url($url, $folder, $no = '', $query_string = '', $action = '')
    {
        if (!function_exists('eottae_is_shop_board') || !eottae_is_shop_board($folder)) {
            return $url;
        }

        if ($folder !== eottae_shop_table()) {
            return $url;
        }

        if ($no !== '' && preg_match('/^(list|type)\-/i', (string) $no)) {
            return $url;
        }

        if ($action === 'write') {
            $write_url = G5_BBS_URL.'/write.php?bo_table='.$folder;
            if ($query_string !== '') {
                $qs = ltrim((string) $query_string, '&');
                if ($qs !== '') {
                    $write_url .= '&'.$qs;
                }
            }

            return $write_url;
        }

        if ($no !== '' && ctype_digit((string) $no)) {
            global $g5;

            $wr_id = (int) $no;
            $write_table = $g5['write_prefix'].$folder;
            $row = sql_fetch(" select wr_id from {$write_table} where wr_id = '{$wr_id}' and wr_is_comment = 0 limit 1 ");
            if (!empty($row['wr_id'])) {
                return eottae_shop_view_url($wr_id, $folder, $query_string);
            }

            return $url;
        }

        if ($no === '') {
            $list_url = G5_BBS_URL.'/board.php?bo_table='.$folder;
            if ($query_string !== '') {
                $qs = ltrim((string) $query_string, '&');
                if ($qs !== '') {
                    $list_url .= '&'.$qs;
                }
            }

            return $list_url;
        }

        return $url;
    }
}

if (!function_exists('eottae_shop_category_url')) {
    function eottae_shop_category_url($category, $board = null)
    {
        $category = trim((string) $category);
        if ($category === '') {
            return eottae_shop_list_url();
        }

        $board_cats = array();
        if (is_array($board) && !empty($board['bo_category_list'])) {
            $board_cats = array_filter(array_map('trim', explode('|', $board['bo_category_list'])));
        }

        if (!empty($board_cats) && in_array($category, $board_cats, true)) {
            return eottae_shop_list_url(array('sca' => $category));
        }

        return eottae_shop_list_url(array('sfl' => 'wr_1', 'stx' => $category));
    }
}

if (!function_exists('eottae_shop_gallery_images')) {
    function eottae_shop_gallery_images($view)
    {
        $images = array();
        if (!is_array($view) || empty($view['file']['count'])) {
            return $images;
        }

        for ($i = 0; $i < (int) $view['file']['count']; $i++) {
            if (empty($view['file'][$i]['view'])) {
                continue;
            }
            $images[] = array(
                'src'   => $view['file'][$i]['path'].'/'.$view['file'][$i]['file'],
                'alt'   => isset($view['file'][$i]['source']) ? get_text($view['file'][$i]['source']) : '',
            );
        }

        return $images;
    }
}

if (!function_exists('eottae_shop_detail_flags')) {
    function eottae_shop_detail_flags($shop, $summary = null)
    {
        if (!is_array($summary)) {
            $summary = eottae_get_shop_review_summary(isset($shop['wr_id']) ? (int) $shop['wr_id'] : 0);
        }

        return array(
            'recommended' => $summary['average'] >= 4.5 && $summary['count'] > 0,
            'ad'          => !empty($shop['sns']) && stripos((string) $shop['sns'], 'ad') !== false,
        );
    }
}

if (!function_exists('eottae_community_board_hero')) {
    function eottae_community_board_hero($board, $sca = '')
    {
        global $bo_table;

        $table = '';
        if (is_array($board) && !empty($board['bo_table'])) {
            $table = (string) $board['bo_table'];
        } elseif (!empty($bo_table)) {
            $table = (string) $bo_table;
        }

        $cebu_img = 'https://images.unsplash.com/photo-%s?auto=format&fit=crop&w=1600&q=85';

        $presets = array(
            'community' => array(
                'kicker' => '세부 자유 게시판',
                'title'  => '세부 생활정보',
                'desc'   => '세부 교민과 여행자가 함께 나누는 생생한 로컬 생활정보 게시판입니다.',
                'image'  => sprintf($cebu_img, '1555881400-0d2f29490987'),
            ),
            'people' => array(
                'kicker' => '사람찾기',
                'title'  => '실종·만남·동행',
                'desc'   => '지인 찾기, 동행 모집, 만남 게시글을 올리는 공간입니다.',
                'image'  => sprintf($cebu_img, '1507525428034-b723cf961d3e'),
            ),
            'job' => array(
                'kicker' => '구인구직',
                'title'  => '일자리 정보',
                'desc'   => '세부 지역 구인·구직·알바 정보를 공유하는 게시판입니다.',
                'image'  => sprintf($cebu_img, '1497366216548-37526070297c'),
            ),
            'estate' => array(
                'kicker' => '부동산',
                'title'  => '매매·전월세',
                'desc'   => '세부 부동산 매매, 전·월세, 양도 정보를 나누는 게시판입니다.',
                'image'  => sprintf($cebu_img, '1560518883-ce09059eeffa'),
            ),
            'event' => array(
                'kicker' => '이벤트',
                'title'  => '프로모션·기획전',
                'desc'   => '업체 이벤트와 세부어때 기획전 소식을 확인하세요.',
                'image'  => sprintf($cebu_img, '1511795409834-ef04bbd61622'),
            ),
            'review' => array(
                'kicker' => '업체 리뷰',
                'title'  => '리뷰 모음',
                'desc'   => '세부 업체에 대한 회원 리뷰를 모아 보는 게시판입니다.',
                'image'  => sprintf($cebu_img, '1414235077428-338989a43e79'),
            ),
        );

        if (isset($presets[$table])) {
            $hero = $presets[$table];
        } else {
            $subject = is_array($board) && isset($board['bo_subject']) ? get_text($board['bo_subject']) : '게시판';
            $hero = array(
                'kicker' => $subject,
                'title'  => $subject,
                'desc'   => '',
                'image'  => sprintf($cebu_img, '1518509562904-7fc873a70436'),
            );
        }

        if ($sca !== '') {
            $hero['title'] = get_text($sca);
        }

        return $hero;
    }
}

if (!function_exists('eottae_community_list_url')) {
    function eottae_community_list_url($params = array())
    {
        global $bo_table;

        $table = '';
        if (isset($params['bo_table'])) {
            $table = (string) $params['bo_table'];
            unset($params['bo_table']);
        } elseif (!empty($bo_table)) {
            $table = (string) $bo_table;
        }

        $base = G5_BBS_URL.'/board.php?bo_table='.eottae_community_board_table($table);
        if (empty($params)) {
            return $base;
        }

        return $base.'&'.http_build_query($params);
    }
}

if (!function_exists('eottae_shop_segment_master_map')) {
    function eottae_shop_segment_master_map()
    {
        return array(
            defined('EOTTae_FOOD_TABLE') ? EOTTae_FOOD_TABLE : 'food'         => '맛집',
            defined('EOTTae_MASSAGE_TABLE') ? EOTTae_MASSAGE_TABLE : 'massage' => '마사지',
            defined('EOTTae_RENTCAR_TABLE') ? EOTTae_RENTCAR_TABLE : 'rentcar' => '렌트카',
            defined('EOTTae_TOUR_TABLE') ? EOTTae_TOUR_TABLE : 'tour'         => '투어',
        );
    }
}

if (!function_exists('eottae_shop_segment_master_category')) {
    function eottae_shop_segment_master_category($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '' || $bo_table === eottae_shop_table()) {
            return '';
        }

        $map = eottae_shop_segment_master_map();

        return isset($map[$bo_table]) ? $map[$bo_table] : '';
    }
}

if (!function_exists('eottae_shop_is_segment_board')) {
    function eottae_shop_is_segment_board($bo_table)
    {
        return eottae_shop_segment_master_category($bo_table) !== '';
    }
}

if (!function_exists('eottae_shop_storage_bo_table')) {
    /** 파일·썸네일 경로용 — 세그먼트 게시판은 shop 테이블 데이터 참조 */
    function eottae_shop_storage_bo_table($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '' || eottae_shop_is_segment_board($bo_table)) {
            return eottae_shop_table();
        }

        return $bo_table;
    }
}

if (!function_exists('eottae_shop_list_segment_sql')) {
    function eottae_shop_list_segment_sql()
    {
        if (empty($GLOBALS['eottae_shop_segment_master'])) {
            return '';
        }

        return " and wr_1 = '".sql_escape_string((string) $GLOBALS['eottae_shop_segment_master'])."' ";
    }
}

if (!function_exists('eottae_shop_list_segment_total_count')) {
    function eottae_shop_list_segment_total_count($write_table)
    {
        if (empty($GLOBALS['eottae_shop_segment_master'])) {
            return null;
        }

        $row = sql_fetch(" select count(*) as cnt from {$write_table} where wr_is_comment = 0 ".eottae_shop_list_segment_sql());

        return isset($row['cnt']) ? (int) $row['cnt'] : 0;
    }
}

if (!function_exists('eottae_shop_apply_segment_board_context')) {
    /**
     * 맛집·마사지 등 세그먼트 게시판 → shop 테이블 + wr_1 대분류 필터
     */
    function eottae_shop_apply_segment_board_context()
    {
        global $bo_table, $write_table, $write, $wr_id, $g5;

        if (empty($bo_table) || !function_exists('eottae_shop_segment_master_category')) {
            return;
        }

        $master = eottae_shop_segment_master_category($bo_table);
        if ($master === '') {
            return;
        }

        $shop_table = $g5['write_prefix'].eottae_shop_table();
        $GLOBALS['eottae_shop_segment_master'] = $master;
        $GLOBALS['eottae_shop_segment_bo_table'] = $bo_table;
        $write_table = $shop_table;

        $wr_id = isset($wr_id) ? (int) $wr_id : 0;
        if ($wr_id > 0) {
            $row = sql_fetch(" select * from {$shop_table}
                where wr_id = '{$wr_id}' and wr_is_comment = 0
                and wr_1 = '".sql_escape_string($master)."' limit 1 ");
            $write = !empty($row['wr_id']) ? $row : array();
        }
    }
}

if (!function_exists('eottae_shop_board_tables')) {
    function eottae_shop_board_tables()
    {
        return array(
            eottae_shop_table(),
            defined('EOTTae_FOOD_TABLE') ? EOTTae_FOOD_TABLE : 'food',
            defined('EOTTae_MASSAGE_TABLE') ? EOTTae_MASSAGE_TABLE : 'massage',
            defined('EOTTae_RENTCAR_TABLE') ? EOTTae_RENTCAR_TABLE : 'rentcar',
            defined('EOTTae_TOUR_TABLE') ? EOTTae_TOUR_TABLE : 'tour',
        );
    }
}

if (!function_exists('eottae_is_shop_board')) {
    function eottae_is_shop_board($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return $bo_table !== '' && in_array($bo_table, eottae_shop_board_tables(), true);
    }
}

if (!function_exists('eottae_board_list_url')) {
    function eottae_board_list_url($bo_table, $params = array())
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return G5_BBS_URL.'/board.php';
        }

        $base = G5_BBS_URL.'/board.php?bo_table='.$bo_table;
        if (empty($params)) {
            return $base;
        }

        return $base.'&'.http_build_query($params);
    }
}

if (!function_exists('eottae_shop_write_url')) {
    function eottae_shop_write_url($bo_table = '')
    {
        if ($bo_table === '') {
            $bo_table = eottae_shop_table();
        }
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            $bo_table = eottae_shop_table();
        }

        return G5_BBS_URL.'/write.php?bo_table='.$bo_table;
    }
}

if (!function_exists('eottae_gnb_board_tables')) {
    function eottae_gnb_board_tables()
    {
        $tables = eottae_shop_board_tables();
        $tables[] = eottae_community_board_table();
        $tables[] = defined('EOTTae_PEOPLE_TABLE') ? EOTTae_PEOPLE_TABLE : 'people';
        $tables[] = defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job';
        $tables[] = defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate';
        $tables[] = defined('EOTTae_GALLERY_TABLE') ? EOTTae_GALLERY_TABLE : 'gallery';
        $tables[] = defined('EOTTae_YOUTUBE_TABLE') ? EOTTae_YOUTUBE_TABLE : 'youtube';

        return array_values(array_unique($tables));
    }
}

if (!function_exists('eottae_gnb_nav_links')) {
    function eottae_gnb_nav_links()
    {
        return array(
            array('key' => 'home', 'label' => '홈', 'href' => G5_URL.'/'),
            array('key' => 'shop', 'label' => '내주변', 'href' => eottae_board_list_url(eottae_shop_table())),
            array('key' => 'food', 'label' => '맛집', 'href' => eottae_board_list_url(defined('EOTTae_FOOD_TABLE') ? EOTTae_FOOD_TABLE : 'food')),
            array('key' => 'massage', 'label' => '마사지', 'href' => eottae_board_list_url(defined('EOTTae_MASSAGE_TABLE') ? EOTTae_MASSAGE_TABLE : 'massage')),
            array('key' => 'rentcar', 'label' => '렌트카', 'href' => eottae_board_list_url(defined('EOTTae_RENTCAR_TABLE') ? EOTTae_RENTCAR_TABLE : 'rentcar')),
            array('key' => 'tour', 'label' => '투어', 'href' => eottae_board_list_url(defined('EOTTae_TOUR_TABLE') ? EOTTae_TOUR_TABLE : 'tour')),
            array('key' => 'community', 'label' => '커뮤니티', 'href' => eottae_community_list_url()),
            array('key' => 'people', 'label' => '사람찾기', 'href' => eottae_board_list_url(defined('EOTTae_PEOPLE_TABLE') ? EOTTae_PEOPLE_TABLE : 'people')),
            array('key' => 'job', 'label' => '구인구직', 'href' => eottae_board_list_url(defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job')),
            array('key' => 'estate', 'label' => '부동산', 'href' => eottae_board_list_url(defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate')),
            array('key' => 'gallery', 'label' => '갤러리', 'href' => eottae_board_list_url(defined('EOTTae_GALLERY_TABLE') ? EOTTae_GALLERY_TABLE : 'gallery')),
            array('key' => 'youtube', 'label' => '유튜브', 'href' => eottae_board_list_url(defined('EOTTae_YOUTUBE_TABLE') ? EOTTae_YOUTUBE_TABLE : 'youtube')),
        );
    }
}

if (!function_exists('eottae_gnb_link_is_active')) {
    function eottae_gnb_link_is_active($key)
    {
        $bo = isset($_GET['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', $_GET['bo_table']) : '';
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

        $board_map = array(
            'shop'      => eottae_shop_table(),
            'food'      => defined('EOTTae_FOOD_TABLE') ? EOTTae_FOOD_TABLE : 'food',
            'massage'   => defined('EOTTae_MASSAGE_TABLE') ? EOTTae_MASSAGE_TABLE : 'massage',
            'rentcar'   => defined('EOTTae_RENTCAR_TABLE') ? EOTTae_RENTCAR_TABLE : 'rentcar',
            'tour'      => defined('EOTTae_TOUR_TABLE') ? EOTTae_TOUR_TABLE : 'tour',
            'community' => eottae_community_board_table(),
            'people'    => defined('EOTTae_PEOPLE_TABLE') ? EOTTae_PEOPLE_TABLE : 'people',
            'job'       => defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job',
            'estate'    => defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate',
            'gallery'   => defined('EOTTae_GALLERY_TABLE') ? EOTTae_GALLERY_TABLE : 'gallery',
            'youtube'   => defined('EOTTae_YOUTUBE_TABLE') ? EOTTae_YOUTUBE_TABLE : 'youtube',
        );

        switch ($key) {
            case 'home':
                return defined('_INDEX_');
            case 'mypage':
                return strpos($uri, '/page/eottae-') !== false;
            default:
                return isset($board_map[$key]) && $bo === $board_map[$key];
        }
    }
}
