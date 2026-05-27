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

if (!function_exists('eottae_member_audience_options')) {
    function eottae_member_audience_options()
    {
        return array(
            'tourist' => '관광객',
            'expat'   => '교민',
            'both'    => '둘 다',
        );
    }
}

if (!function_exists('eottae_member_resident_role_options')) {
    function eottae_member_resident_role_options()
    {
        return array(
            'member'   => '일반인',
            'business' => '사업자',
        );
    }
}

if (!function_exists('eottae_member_audience_type')) {
    function eottae_member_audience_type($member = null)
    {
        global $member;

        $m = is_array($member) ? $member : (isset($member) && is_array($member) ? $member : array());
        if (empty($m) && !empty($GLOBALS['member']['mb_id'])) {
            $m = $GLOBALS['member'];
        }

        $type = isset($m['mb_2']) ? trim((string) $m['mb_2']) : '';
        $allowed = array_keys(eottae_member_audience_options());

        return in_array($type, $allowed, true) ? $type : '';
    }
}

if (!function_exists('eottae_member_audience_label')) {
    function eottae_member_audience_label($audience = '')
    {
        $options = eottae_member_audience_options();

        return isset($options[$audience]) ? $options[$audience] : '';
    }
}

if (!function_exists('eottae_member_profile_type_label')) {
    function eottae_member_profile_type_label($member = null)
    {
        $audience = eottae_member_audience_type($member);
        $is_biz = eottae_is_business_member($member);

        if ($audience === 'tourist') {
            return '관광객';
        }
        if ($audience === 'expat' || $audience === 'both') {
            $audience_label = eottae_member_audience_label($audience);
            $role_label = $is_biz ? '사업자' : '일반인';

            return $audience_label.' · '.$role_label;
        }

        return $is_biz ? '사업자회원' : '일반회원';
    }
}

if (!function_exists('eottae_normalize_member_type_fields')) {
    function eottae_normalize_member_type_fields($mb_1 = '', $mb_2 = '')
    {
        $audience_options = array_keys(eottae_member_audience_options());
        $role_options = array_keys(eottae_member_resident_role_options());

        $mb_2 = trim((string) $mb_2);
        if (!in_array($mb_2, $audience_options, true)) {
            $mb_2 = '';
        }

        $mb_1 = trim((string) $mb_1);
        if (!in_array($mb_1, $role_options, true)) {
            $mb_1 = 'member';
        }

        if ($mb_2 === 'tourist') {
            $mb_1 = 'member';
        }

        return array($mb_1, $mb_2);
    }
}

if (!function_exists('eottae_validate_member_type_fields')) {
    function eottae_validate_member_type_fields($mb_1, $mb_2, $is_new = true)
    {
        list($mb_1, $mb_2) = eottae_normalize_member_type_fields($mb_1, $mb_2);

        if ($is_new && $mb_2 === '') {
            return '회원 유형(관광객/교민/둘 다)을 선택해 주세요.';
        }

        if (($mb_2 === 'expat' || $mb_2 === 'both') && !in_array($mb_1, array('member', 'business'), true)) {
            return '교민 회원은 일반인 또는 사업자를 선택해 주세요.';
        }

        return '';
    }
}

if (!function_exists('eottae_render_member_type_fields')) {
    function eottae_render_member_type_fields($args = array())
    {
        $defaults = array(
            'audience' => '',
            'role'     => 'member',
            'require_hidden' => true,
            'id_prefix' => '',
        );
        $args = array_merge($defaults, is_array($args) ? $args : array());

        $audience = trim((string) $args['audience']);
        $role = trim((string) $args['role']) === 'business' ? 'business' : 'member';
        $id_prefix = preg_replace('/[^a-z0-9_-]/i', '', (string) $args['id_prefix']);
        $audience_options = eottae_member_audience_options();
        $role_options = eottae_member_resident_role_options();
        $show_role = ($audience === 'expat' || $audience === 'both');

        ob_start();
        ?>
        <?php if (!empty($args['require_hidden'])) { ?>
        <input type="hidden" name="mb_1" id="<?php echo $id_prefix; ?>reg_mb_1" value="<?php echo $role === 'business' ? 'business' : 'member'; ?>">
        <input type="hidden" name="mb_2" id="<?php echo $id_prefix; ?>reg_mb_2" value="<?php echo htmlspecialchars($audience, ENT_QUOTES, 'UTF-8'); ?>">
        <?php } ?>

        <fieldset class="auth-member-type-group">
            <legend class="auth-member-type-group__legend">회원 유형</legend>
            <p class="auth-member-type-group__hint">세부 방문·거주 성격을 선택해 주세요.</p>
            <div class="auth-member-type auth-member-type--audience">
                <?php foreach ($audience_options as $value => $label) { ?>
                <label>
                    <input type="radio" name="eottae_audience_type" value="<?php echo $value; ?>"<?php echo ($audience === $value) ? ' checked' : ''; ?>>
                    <span><?php echo get_text($label); ?></span>
                </label>
                <?php } ?>
            </div>

            <div class="auth-member-type-group__role<?php echo $show_role ? '' : ' is-hidden'; ?>" data-member-role-wrap>
                <p class="auth-member-type-group__sublegend">교민 회원 구분</p>
                <div class="auth-member-type auth-member-type--role">
                    <?php foreach ($role_options as $value => $label) { ?>
                    <label>
                        <input type="radio" name="eottae_member_type" value="<?php echo $value; ?>"<?php echo ($role === $value) ? ' checked' : ''; ?>>
                        <span><?php echo get_text($label); ?></span>
                    </label>
                    <?php } ?>
                </div>
            </div>
        </fieldset>
        <?php

        return ob_get_clean();
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
     * wr_link2 SNS JSON 디코드 (HTML 엔티티·레거시 이중인코딩 보정)
     *
     * @return array<string, string>|null
     */
    function eottae_shop_sns_decode($raw)
    {
        $raw = trim(stripslashes((string) $raw));
        if ($raw === '') {
            return null;
        }

        $attempts = array($raw);

        if (strpos($raw, '&') !== false) {
            if (function_exists('html_entity_decode')) {
                $attempts[] = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
            if (function_exists('get_text') && preg_match('/&#0?34;|&quot;|&lt;|&gt;|&#0?39;/i', $raw)) {
                $attempts[] = get_text($raw, 0, true);
            }
        }

        foreach (array_unique($attempts) as $candidate) {
            $candidate = trim(stripslashes((string) $candidate));
            if ($candidate === '' || $candidate[0] !== '{') {
                continue;
            }
            $decoded = json_decode($candidate, true);
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
        $key = (string) $key;
        $decoded = function_exists('eottae_shop_sns_decode') ? eottae_shop_sns_decode($raw) : null;
        if (!is_array($decoded) || !isset($decoded[$key])) {
            return '';
        }

        $val = trim(stripslashes((string) $decoded[$key]));
        if ($val === '') {
            return '';
        }

        // 레거시: instagram 값에 wr_link2 JSON 전체가 들어간 경우
        if ($val[0] === '{' || preg_match('/&#0?34;|&quot;/i', $val)) {
            $inner = eottae_shop_sns_decode($val);
            if (is_array($inner) && isset($inner[$key])) {
                $val = trim(stripslashes((string) $inner[$key]));
            } else {
                return '';
            }
        }

        return $val;
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

if (!function_exists('eottae_emit_mobile_head_assets')) {
    /**
     * 모바일 head.sub.php 종료 직전 — eottae CSS 선출력
     * (tail.php html_end 미실행 시에도 레이아웃·회원 페이지 스타일 유지)
     */
    function eottae_emit_mobile_head_assets()
    {
        static $emitted = false;

        if ($emitted || !defined('G5_IS_MOBILE') || !G5_IS_MOBILE) {
            return;
        }
        if (!function_exists('eottae_should_load_assets') || !eottae_should_load_assets()) {
            return;
        }

        $emitted = true;
        if (!defined('EOTTAE_MOBILE_HEAD_ASSETS_EMITTED')) {
            define('EOTTAE_MOBILE_HEAD_ASSETS_EMITTED', true);
        }

        $ver = defined('G5_CSS_VER') ? G5_CSS_VER : '';
        $css_base = defined('G5_CSS_URL') ? G5_CSS_URL : '';
        $fa_base = defined('G5_JS_URL') ? G5_JS_URL : '';

        echo '<link rel="stylesheet" href="'.$fa_base.'/font-awesome/css/font-awesome.min.css?ver='.$ver.'">'.PHP_EOL;
        echo '<link rel="stylesheet" href="'.$css_base.'/custom.css?ver='.$ver.'">'.PHP_EOL;

        if (function_exists('g5site_cfg')) {
            $brand_css = '';
            $primary = g5site_cfg('primary_color', '');
            $secondary = g5site_cfg('secondary_color', '');
            if ($primary !== '' && preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/', $primary)) {
                $brand_css .= '--color-primary:'.$primary.';';
            }
            if ($secondary !== '' && preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/', $secondary)) {
                $brand_css .= '--color-secondary:'.$secondary.';--color-muted:'.$secondary.';';
            }
            if ($brand_css !== '') {
                echo '<style>:root{'.$brand_css.'}</style>'.PHP_EOL;
            }
        }

        echo '<link rel="stylesheet" href="'.$css_base.'/eottae.css?ver='.$ver.'">'.PHP_EOL;
        echo '<link rel="stylesheet" href="'.$css_base.'/eottae-kakao-chat.css?ver='.$ver.'">'.PHP_EOL;

        if (function_exists('eottae_talkroom_should_load_ui') && eottae_talkroom_should_load_ui()) {
            echo '<link rel="stylesheet" href="'.$css_base.'/eottae-talkroom-ui.css?ver='.$ver.'">'.PHP_EOL;
        }
    }
}

if (!function_exists('eottae_filter_mobile_duplicate_head_assets')) {
    function eottae_filter_mobile_duplicate_head_assets($links, $needles)
    {
        if (!defined('EOTTAE_MOBILE_HEAD_ASSETS_EMITTED') || !is_array($links)) {
            return $links;
        }

        $filtered = array();
        foreach ($links as $row) {
            if (!is_array($row) || !isset($row[1])) {
                $filtered[] = $row;
                continue;
            }
            $skip = false;
            foreach ((array) $needles as $needle) {
                if ($needle !== '' && strpos($row[1], $needle) !== false) {
                    $skip = true;
                    break;
                }
            }
            if (!$skip) {
                $filtered[] = $row;
            }
        }

        return array_values($filtered);
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

if (!function_exists('eottae_builder_inject_home_search_script')) {
    function eottae_builder_inject_home_search_script()
    {
        $shop_table = function_exists('eottae_shop_table') ? eottae_shop_table() : 'shop';
        $payload = array(
            'bbsUrl'    => G5_BBS_URL.'/board.php',
            'shopTable' => $shop_table,
        );
        $payload_json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload_json === false) {
            return '';
        }

        $js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-search.js' : '/js/eottae-home-search.js';

        return '<script>window.__EOTTae_HOME_SEARCH__='.$payload_json.';</script>'
            .'<script src="'.htmlspecialchars($js, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }
}

if (!function_exists('eottae_builder_inject_featured_carousel_script')) {
    function eottae_builder_inject_featured_carousel_script()
    {
        $js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-featured-carousel.js' : '/js/eottae-home-featured-carousel.js';

        return '<script src="'.htmlspecialchars($js, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }
}

if (!function_exists('eottae_builder_inject_home_talk_feed')) {
    function eottae_builder_inject_home_talk_feed($html)
    {
        if (!is_string($html) || $html === '') {
            return $html;
        }

        if (!function_exists('eottae_talkroom_home_feed_html')) {
            $component = G5_PATH.'/components/eottae/talk-home-feed.php';
            if (is_file($component)) {
                include_once $component;
            }
        }

        if (!function_exists('eottae_talkroom_home_feed_html')) {
            return $html;
        }

        $feed_html = eottae_talkroom_home_feed_html(5);
        if ($feed_html === '') {
            return $html;
        }

        if (preg_match('#(<div\s+id=["\']root["\'][^>]*>\s*</div>)#i', $html)) {
            $html = preg_replace('#(<div\s+id=["\']root["\'][^>]*>\s*</div>)#i', '$1'.$feed_html, $html, 1);
        } elseif (preg_match('#</body>#i', $html)) {
            $html = preg_replace('#</body>#i', $feed_html.'</body>', $html, 1);
        } else {
            $html .= $feed_html;
        }

        return $html;
    }
}

if (!function_exists('eottae_builder_inject_home_talk_feed_script')) {
    function eottae_builder_inject_home_talk_feed_script()
    {
        $js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-talk-feed.js' : '/js/eottae-home-talk-feed.js';

        return '<script src="'.htmlspecialchars($js, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }
}

if (!function_exists('eottae_builder_inject_home_briefing_script')) {
    function eottae_builder_inject_home_briefing_script()
    {
        if (!function_exists('eottae_briefing_home_payload')) {
            include_once G5_LIB_PATH.'/eottae-briefing.lib.php';
        }

        $payload = eottae_briefing_home_payload();
        $payload_json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload_json === false) {
            return '';
        }

        $css = defined('G5_CSS_URL') ? G5_CSS_URL.'/eottae-briefing.css?v=2' : '/css/eottae-briefing.css';
        $js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-briefing.js' : '/js/eottae-home-briefing.js';

        return '<link rel="stylesheet" href="'.htmlspecialchars($css, ENT_QUOTES, 'UTF-8').'">'
            .'<script>window.__EOTTae_HOME_BRIEFING__='.$payload_json.';</script>'
            .'<script src="'.htmlspecialchars($js, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }
}

if (!function_exists('eottae_builder_inject_home_main_section_script')) {
    function eottae_builder_inject_home_main_section_script()
    {
        include_once G5_LIB_PATH.'/eottae-calendar-home.lib.php';

        $payload = eottae_home_main_section_payload();
        $payload_json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload_json === false) {
            return '';
        }

        $css = defined('G5_CSS_URL') ? G5_CSS_URL.'/eottae-home-main-section.css' : '/css/eottae-home-main-section.css';
        $cal_css = defined('G5_CSS_URL') ? G5_CSS_URL.'/eottae-calendar.css' : '/css/eottae-calendar.css';
        $js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-main-section.js' : '/js/eottae-home-main-section.js';
        $cal_js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-calendar.js' : '/js/eottae-calendar.js';

        ob_start();
        if (is_file(G5_PATH.'/components/eottae/calendar-event-modal.php')) {
            include_once G5_PATH.'/components/eottae/calendar-event-modal.php';
            if (function_exists('eottae_calendar_render_event_modal')) {
                eottae_calendar_render_event_modal();
            }
        }
        $modal_html = (string) ob_get_clean();

        return '<link rel="stylesheet" href="'.htmlspecialchars($css, ENT_QUOTES, 'UTF-8').'">'
            .'<link rel="stylesheet" href="'.htmlspecialchars($cal_css, ENT_QUOTES, 'UTF-8').'">'
            .'<script>window.__EOTTae_HOME_MAIN_SECTION__='.$payload_json.';</script>'
            .'<script src="'.htmlspecialchars($js, ENT_QUOTES, 'UTF-8').'" defer></script>'
            .'<script src="'.htmlspecialchars($cal_js, ENT_QUOTES, 'UTF-8').'" defer></script>'
            .$modal_html;
    }
}

if (!function_exists('eottae_builder_inject_home_hero_talk_script')) {
    function eottae_builder_inject_home_hero_talk_script()
    {
        if (!function_exists('eottae_talkroom_home_hero_payload')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }
        if (!function_exists('eottae_plaza_home_hero_payload')) {
            include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
        }

        $talk_payload = eottae_talkroom_home_hero_payload(3, 3);
        $talk_payload['variant'] = 'talk';

        $talk_json = json_encode($talk_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($talk_json === false) {
            return '';
        }

        $js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-hero-talk.js' : '/js/eottae-home-hero-talk.js';

        return '<script>window.__EOTTae_HOME_HERO_TALK__='.$talk_json.';</script>'
            .'<script src="'.htmlspecialchars($js, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }
}

if (!function_exists('eottae_builder_inject_home_hero_layout_script')) {
    function eottae_builder_inject_home_hero_layout_script()
    {
        $js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-hero-layout.js' : '/js/eottae-home-hero-layout.js';

        return '<script src="'.htmlspecialchars($js, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }
}

if (!function_exists('eottae_builder_inject_home_hero_sidebar_script')) {
    function eottae_builder_inject_home_hero_sidebar_script()
    {
        $return_url = function_exists('eottae_current_url') ? eottae_current_url() : G5_URL;
        $payload = array(
            'login_url'               => function_exists('eottae_login_url') ? eottae_login_url($return_url) : G5_BBS_URL.'/login.php',
            'register_url'            => function_exists('eottae_register_url') ? eottae_register_url() : G5_BBS_URL.'/register.php',
            'password_url'            => function_exists('eottae_password_lost_url') ? eottae_password_lost_url() : G5_BBS_URL.'/password_lost.php',
            'member_growth_guide_url' => G5_URL.'/page/eottae-member-growth-guide.php',
            'coupon_guide_url'        => G5_URL.'/page/eottae-coupon-guide.php',
        );
        $payload_json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload_json === false) {
            return '';
        }

        $js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-hero-sidebar.js' : '/js/eottae-home-hero-sidebar.js';

        return '<script>window.__EOTTae_HOME_HERO_SIDEBAR__='.$payload_json.';</script>'
            .'<script src="'.htmlspecialchars($js, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }
}

if (!function_exists('eottae_builder_inject_home_events_banner_script')) {
    function eottae_builder_inject_home_events_banner_script()
    {
        if (!function_exists('eottae_api_get_events')) {
            include_once G5_LIB_PATH.'/eottae-api.lib.php';
        }

        $events = eottae_api_get_events(12);
        if (empty($events)) {
            return '';
        }

        shuffle($events);
        $events = array_slice($events, 0, 2);

        $payload = array(
            'events'   => $events,
            'list_url' => G5_URL.'/page/eottae-events.php',
        );
        $payload_json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload_json === false) {
            return '';
        }

        $js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-events-banner.js' : '/js/eottae-home-events-banner.js';

        return '<script>window.__EOTTae_HOME_EVENTS_BANNER__='.$payload_json.';</script>'
            .'<script src="'.htmlspecialchars($js, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }
}

if (!function_exists('eottae_builder_inject_home_map_categories_script')) {
    function eottae_builder_inject_home_map_categories_script()
    {
        $shop_table = function_exists('eottae_shop_table') ? eottae_shop_table() : 'shop';
        $payload = array(
            'bbsUrl'    => G5_BBS_URL.'/board.php',
            'shopTable' => $shop_table,
        );
        $payload_json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload_json === false) {
            return '';
        }

        $js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-map-categories.js' : '/js/eottae-home-map-categories.js';

        return '<script>window.__EOTTae_HOME_MAP_CATEGORIES__='.$payload_json.';</script>'
            .'<script src="'.htmlspecialchars($js, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }
}

if (!function_exists('eottae_builder_inject_home_header_actions_script')) {
    function eottae_builder_inject_home_header_actions_script()
    {
        $payload = array(
            'talk_url'       => function_exists('eottae_talkroom_list_url') ? eottae_talkroom_list_url() : G5_URL.'/talk',
            'talk_label'     => '세부톡',
            'calendar_url'   => function_exists('eottae_calendar_list_url') ? eottae_calendar_list_url() : G5_URL.'/calendar/',
            'calendar_label' => '세부일정',
            'golf_join_url'  => function_exists('eottae_golf_join_list_url') ? eottae_golf_join_list_url() : G5_URL.'/golf-join/',
            'golf_join_label'=> '골프조인',
            'column_url'     => function_exists('eottae_column_list_url') ? eottae_column_list_url() : G5_URL.'/column/',
            'column_label'   => function_exists('eottae_column_menu_label') ? eottae_column_menu_label() : '컬럼',
        );
        $payload_json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload_json === false) {
            return '';
        }

        $js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-header-actions.js' : '/js/eottae-home-header-actions.js';

        return '<script>window.__EOTTae_HOME_HEADER_ACTIONS__='.$payload_json.';</script>'
            .'<script src="'.htmlspecialchars($js, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }
}

if (!function_exists('eottae_builder_inject_home_public_chat')) {
    function eottae_builder_inject_home_public_chat($html)
    {
        if (!is_string($html) || $html === '') {
            return $html;
        }

        if (!function_exists('eottae_public_group_chat_html')) {
            $component = G5_PATH.'/components/eottae/public-group-chat.php';
            if (is_file($component)) {
                include_once G5_LIB_PATH.'/eottae-talkroom-public-chat.lib.php';
                include_once $component;
            }
        }

        if (!function_exists('eottae_public_group_chat_html')) {
            return $html;
        }

        $chat_html = eottae_public_group_chat_html(20);
        if ($chat_html === '') {
            return $html;
        }

        if (strpos($html, 'id="eottae-home-public-chat"') !== false) {
            return $html;
        }

        $chat_html = '<div class="eottae-home-slot-pending">'.$chat_html.'</div>';

        if (preg_match('#(<div\s+id=["\']root["\'][^>]*>\s*</div>)#i', $html)) {
            $html = preg_replace('#(<div\s+id=["\']root["\'][^>]*>\s*</div>)#i', '$1'.$chat_html, $html, 1);
        } elseif (preg_match('#</body>#i', $html)) {
            $html = preg_replace('#</body>#i', $chat_html.'</body>', $html, 1);
        } else {
            $html .= $chat_html;
        }

        return $html;
    }
}

if (!function_exists('eottae_builder_inject_home_public_chat_script')) {
    function eottae_builder_inject_home_public_chat_script()
    {
        $js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-public-chat.js' : '/js/eottae-home-public-chat.js';

        return '<script src="'.htmlspecialchars($js, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }
}

if (!function_exists('eottae_builder_inject_home_react_ready_script')) {
    function eottae_builder_inject_home_react_ready_script()
    {
        $js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-home-react-ready.js' : '/js/eottae-home-react-ready.js';

        return '<script src="'.htmlspecialchars($js, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }
}

if (!function_exists('eottae_builder_inject_home_plaza_feed')) {
    function eottae_builder_inject_home_plaza_feed($html)
    {
        if (!is_string($html) || $html === '') {
            return $html;
        }

        if (!function_exists('eottae_plaza_home_feed_html')) {
            $component = G5_PATH.'/components/eottae/plaza-home-feed.php';
            if (is_file($component)) {
                include_once G5_LIB_PATH.'/eottae-plaza-home-feed.lib.php';
                include_once $component;
            }
        }

        if (!function_exists('eottae_plaza_home_feed_html')) {
            return $html;
        }

        $feed_html = eottae_plaza_home_feed_html(5);
        if ($feed_html === '') {
            return $html;
        }

        if (preg_match('#(<section[^>]*id=["\']eottae-home-plaza-feed["\'][^>]*>.*?</section>)#is', $html)) {
            return $html;
        } elseif (preg_match('#(<div\s+id=["\']root["\'][^>]*>\s*</div>)#i', $html)) {
            $html = preg_replace('#(<div\s+id=["\']root["\'][^>]*>\s*</div>)#i', '$1'.$feed_html, $html, 1);
        } elseif (preg_match('#</body>#i', $html)) {
            $html = preg_replace('#</body>#i', $feed_html.'</body>', $html, 1);
        } else {
            $html .= $feed_html;
        }

        return $html;
    }
}

if (!function_exists('eottae_builder_inject_site_footer_script')) {
    function eottae_builder_inject_site_footer_script()
    {
        if (!defined('G5_JS_URL')) {
            return '';
        }

        $payload = array(
            'talk_url'                  => G5_URL.'/talk/ai.php',
            'coupon_guide_url'          => G5_URL.'/page/eottae-coupon-guide.php',
            'business_coupon_guide_url' => G5_URL.'/page/eottae-business-coupon-guide.php',
            'challenge_guide_url'       => G5_URL.'/page/eottae-challenge-guide.php',
            'member_growth_guide_url'   => function_exists('eottae_member_growth_guide_url')
                ? eottae_member_growth_guide_url()
                : G5_URL.'/page/eottae-member-growth-guide.php',
            'briefing_url'                => function_exists('eottae_briefing_url')
                ? eottae_briefing_url()
                : G5_URL.'/briefing/',
            'badge_book_url'            => function_exists('eottae_member_growth_badge_book_url')
                ? eottae_member_growth_badge_book_url()
                : G5_URL.'/badges/',
        );

        return '<style>#root>footer,[data-eottae-react-footer]{display:none!important}</style>'
            .'<script>window.__EOTTae_HOME_FOOTER__='
            .json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
            .';</script>'
            .'<script src="'.G5_JS_URL.'/eottae-home-footer.js" defer></script>';
    }
}

if (!function_exists('eottae_builder_inject_site_footer')) {
    function eottae_builder_inject_site_footer($html)
    {
        if (!is_string($html) || $html === '') {
            return $html;
        }

        if (!function_exists('eottae_site_footer_html')) {
            if (!defined('EOTTAE_SITE_FOOTER_RETURN_ONLY')) {
                define('EOTTAE_SITE_FOOTER_RETURN_ONLY', true);
            }
            include_once G5_PATH.'/components/eottae/site-footer.php';
        }

        if (!function_exists('eottae_site_footer_html')) {
            return $html;
        }

        $footer = '<div id="ft" class="site-footer-wrap">'.eottae_site_footer_html().'</div>';
        $footer .= eottae_builder_inject_site_footer_script();

        if (preg_match('#</body>#i', $html)) {
            return preg_replace('#</body>#i', $footer.'</body>', $html, 1);
        }

        return $html.$footer;
    }
}

if (!function_exists('eottae_builder_inject_home_stylesheets')) {
    /**
     * 빌더 홈 — GNUBoard head 없이도 카카오톡 채팅 UI CSS 로드
     */
    function eottae_builder_inject_home_stylesheets($html)
    {
        if (!is_string($html) || $html === '') {
            return $html;
        }

        if (stripos($html, 'eottae-kakao-chat.css') !== false) {
            return $html;
        }

        $css_url = defined('G5_CSS_URL') ? G5_CSS_URL.'/eottae-kakao-chat.css' : '/css/eottae-kakao-chat.css';
        $link = '<link rel="stylesheet" href="'.htmlspecialchars($css_url, ENT_QUOTES, 'UTF-8').'">';

        if (preg_match('#<link[^>]+href=["\'][^"\']*eottae\.css["\'][^>]*>#i', $html)) {
            return preg_replace(
                '#(<link[^>]+href=["\'][^"\']*eottae\.css["\'][^>]*>)#i',
                '$1'."\n    ".$link,
                $html,
                1
            );
        }

        if (preg_match('#</head>#i', $html)) {
            return preg_replace('#</head>#i', '    '.$link."\n".'</head>', $html, 1);
        }

        return $html;
    }
}

if (!function_exists('eottae_builder_inject_html')) {
    function eottae_builder_inject_html($html, $id)
    {
        if ($id !== 'thecebu-main' || !is_string($html) || $html === '') {
            return $html;
        }

        $html = eottae_builder_inject_home_stylesheets($html);
        $html = eottae_builder_inject_home_map($html);
        $html = eottae_builder_inject_home_public_chat($html);

        $head_script = eottae_builder_inject_logo_head_script();
        if ($head_script !== '') {
            if (preg_match('#<script[^>]+type=["\']module["\'][^>]*>#i', $html)) {
                $html = preg_replace('#(<script[^>]+type=["\']module["\'][^>]*>)#i', $head_script.'$1', $html, 1);
            } elseif (preg_match('#</head>#i', $html)) {
                $html = preg_replace('#</head>#i', $head_script.'</head>', $html, 1);
            }
        }

        $body_scripts = eottae_builder_inject_home_react_ready_script();
        $body_scripts .= eottae_builder_inject_featured_carousel_script();
        $body_scripts .= eottae_builder_inject_home_search_script();
        $body_scripts .= eottae_builder_inject_home_briefing_script();
        $body_scripts .= eottae_builder_inject_home_main_section_script();
        $body_scripts .= eottae_builder_inject_home_hero_layout_script();
        $body_scripts .= eottae_builder_inject_home_public_chat_script();
        $body_scripts .= eottae_builder_inject_home_events_banner_script();
        $body_scripts .= eottae_builder_inject_home_hero_sidebar_script();
        $body_scripts .= eottae_builder_inject_home_header_actions_script();
        $body_scripts .= eottae_builder_inject_home_map_categories_script();

        if ($body_scripts === '') {
            return $html;
        }

        if (preg_match('#</body>#i', $html)) {
            $html = preg_replace('#</body>#i', $body_scripts.'</body>', $html, 1);
        } else {
            $html .= $body_scripts;
        }

        return eottae_builder_inject_site_footer($html);
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

if (!function_exists('eottae_render_coupon_visual_card')) {
    /**
     * @param array<string, mixed> $coupon
     * @param array<string, mixed> $opts
     */
    function eottae_render_coupon_visual_card(array $coupon, array $opts = array())
    {
        $coupon_card_opts = $opts;
        include G5_PATH.'/components/eottae/coupon-visual-card.php';
    }
}

if (!function_exists('eottae_mypage_url')) {
    function eottae_mypage_url()
    {
        return G5_URL.'/page/eottae-mypage.php';
    }
}

if (!function_exists('eottae_mypage_talk_url')) {
    function eottae_mypage_talk_url()
    {
        return G5_URL.'/mypage/talk.php';
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

if (!function_exists('eottae_shop_save_count')) {
    function eottae_shop_save_count($shop_wr_id, $bo_table = '')
    {
        global $g5;

        $shop_wr_id = (int) $shop_wr_id;
        if ($shop_wr_id < 1) {
            return 0;
        }

        if ($bo_table === '') {
            $bo_table = defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop';
        }
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);

        $row = sql_fetch(" select count(*) as cnt from {$g5['scrap_table']}
            where bo_table = '".sql_escape_string($bo_table)."' and wr_id = '{$shop_wr_id}' ");

        return !empty($row['cnt']) ? (int) $row['cnt'] : 0;
    }
}

if (!function_exists('eottae_shop_save_counts_batch')) {
    /**
     * @param array<int, int> $shop_wr_ids
     * @return array<int, int>
     */
    function eottae_shop_save_counts_batch(array $shop_wr_ids, $bo_table = '')
    {
        global $g5;

        $ids = array();
        foreach ($shop_wr_ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        if (count($ids) === 0) {
            return array();
        }

        if ($bo_table === '') {
            $bo_table = defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop';
        }
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $id_list = implode(',', array_values($ids));

        $result = sql_query(" select wr_id, count(*) as cnt from {$g5['scrap_table']}
            where bo_table = '".sql_escape_string($bo_table)."' and wr_id in ({$id_list})
            group by wr_id ");
        $counts = array();
        while ($row = sql_fetch_array($result)) {
            $counts[(int) $row['wr_id']] = (int) $row['cnt'];
        }

        return $counts;
    }
}

if (!function_exists('eottae_get_shop_latest_review_previews')) {
    /**
     * 업체별 최신 리뷰 1줄 미리보기
     *
     * @param array<int, int> $shop_wr_ids
     * @return array<int, string>
     */
    function eottae_get_shop_latest_review_previews(array $shop_wr_ids, $snippet_len = 48)
    {
        $ids = array();
        foreach ($shop_wr_ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        if (count($ids) === 0) {
            return array();
        }

        $write_table = eottae_review_write_table();
        $id_list = implode(',', array_values($ids));
        $result = sql_query(" select wr_1, wr_content from {$write_table}
            where wr_is_comment = 0
              and wr_1 in ({$id_list})
              and (wr_4 = '' or wr_4 = 'visible')
            order by wr_id desc ");

        $previews = array();
        while ($row = sql_fetch_array($result)) {
            $shop_id = (int) $row['wr_1'];
            if (isset($previews[$shop_id])) {
                continue;
            }
            $text = function_exists('eottae_shop_list_snippet')
                ? eottae_shop_list_snippet($row['wr_content'], (int) $snippet_len)
                : cut_str(strip_tags((string) $row['wr_content']), (int) $snippet_len);
            if ($text !== '') {
                $previews[$shop_id] = $text;
            }
        }

        return $previews;
    }
}

if (!function_exists('eottae_shop_list_card_badges')) {
    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $summary
     * @param int $save_count
     * @return array{is_ad:bool,is_recommended:bool,is_popular:bool}
     */
    function eottae_shop_list_card_badges(array $row, array $summary, $save_count = 0)
    {
        $average = isset($summary['average']) ? (float) $summary['average'] : 0;
        $count = isset($summary['count']) ? (int) $summary['count'] : 0;
        $hit = isset($row['wr_hit']) ? (int) $row['wr_hit'] : 0;
        $save_count = max(0, (int) $save_count);

        $is_ad = isset($row['wr_link2']) && stripos((string) $row['wr_link2'], 'ad') !== false;
        $is_recommended = $average >= 4.8 && $count >= 10;
        $is_popular = !$is_ad && ($hit >= 30 || $save_count >= 3);

        return array(
            'is_ad'          => $is_ad,
            'is_recommended' => $is_recommended,
            'is_popular'     => $is_popular,
        );
    }
}

if (!function_exists('eottae_shop_list_card_attach_meta')) {
    /**
     * 목록 카드 렌더 전 일괄 메타(찜 수·최신 리뷰) 부착
     *
     * @param array<int, array<string, mixed>> $list
     * @return array<int, array<string, mixed>>
     */
    function eottae_shop_list_card_attach_meta(array $list, $bo_table = '')
    {
        if (count($list) === 0) {
            return $list;
        }

        $ids = array();
        foreach ($list as $row) {
            if (!empty($row['wr_id'])) {
                $ids[] = (int) $row['wr_id'];
            }
        }
        if (count($ids) === 0) {
            return $list;
        }

        $save_counts = eottae_shop_save_counts_batch($ids, $bo_table);
        $review_previews = eottae_get_shop_latest_review_previews($ids);

        foreach ($list as $idx => $row) {
            if (!is_array($row)) {
                continue;
            }
            $wr_id = (int) ($row['wr_id'] ?? 0);
            $list[$idx]['_eottae_save_count'] = isset($save_counts[$wr_id]) ? (int) $save_counts[$wr_id] : 0;
            $list[$idx]['_eottae_latest_review'] = isset($review_previews[$wr_id]) ? (string) $review_previews[$wr_id] : '';
        }

        return $list;
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

if (!function_exists('eottae_map_public_url')) {
    function eottae_map_public_url($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }
        if (strpos($url, '//') === 0) {
            $scheme = (defined('G5_IS_HTTPS') && G5_IS_HTTPS) ? 'https:' : 'http:';

            return $scheme.$url;
        }
        if (!defined('G5_URL')) {
            return $url;
        }
        if ($url[0] === '/') {
            return G5_URL.$url;
        }

        return G5_URL.'/'.$url;
    }
}

if (!function_exists('eottae_shop_map_thumb_get')) {
    function eottae_shop_map_thumb_get($bo_table, $wr_id)
    {
        $requested_bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $bo_table = $requested_bo_table;
        if ($bo_table !== '' && function_exists('eottae_shop_storage_bo_table')) {
            $bo_table = eottae_shop_storage_bo_table($bo_table);
        }
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return array();
        }

        $table = eottae_shop_map_thumb_table();
        $lookup_tables = array($bo_table, $requested_bo_table);
        if (function_exists('eottae_shop_table')) {
            $lookup_tables[] = eottae_shop_table();
        }
        if (function_exists('eottae_shop_board_tables')) {
            $lookup_tables = array_merge($lookup_tables, eottae_shop_board_tables());
        }
        $lookup_tables = array_values(array_unique(array_filter(array_map(function ($value) {
            return preg_replace('/[^a-z0-9_]/i', '', (string) $value);
        }, $lookup_tables))));

        $row = array();
        foreach ($lookup_tables as $lookup_bo_table) {
            if ($lookup_bo_table === '') {
                continue;
            }
            $row = sql_fetch(" select * from {$table} where bo_table = '".sql_escape_string($lookup_bo_table)."' and wr_id = '{$wr_id}' ");
            if (!empty($row['file_name'])) {
                break;
            }
        }
        if (empty($row['file_name'])) {
            return array();
        }

        return array(
            'file_name' => $row['file_name'],
            'source_name' => isset($row['source_name']) ? $row['source_name'] : '',
            'url' => eottae_map_public_url(eottae_shop_map_thumb_url_base().'/'.$row['file_name']),
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
                $storage_bo = function_exists('eottae_shop_storage_bo_table')
                    ? eottae_shop_storage_bo_table($bo_table)
                    : preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
                eottae_shop_map_thumb_delete($storage_bo, $wr_id);
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

        $storage_bo = function_exists('eottae_shop_storage_bo_table')
            ? eottae_shop_storage_bo_table($bo_table)
            : preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);

        eottae_shop_map_thumb_delete($storage_bo, $wr_id);

        $file_name = preg_replace('/[^a-z0-9_]/i', '', (string) $storage_bo).'_'.(int) $wr_id.'_'.substr(md5(uniqid('', true)), 0, 12).'.'.$ext_map[$type];
        if (@move_uploaded_file($_FILES['eottae_map_thumb']['tmp_name'], $dir.'/'.$file_name)) {
            @chmod($dir.'/'.$file_name, G5_FILE_PERMISSION);
            $table = eottae_shop_map_thumb_table();
            $source = sql_escape_string(substr(trim(strip_tags((string) $_FILES['eottae_map_thumb']['name'])), 0, 255));
            sql_query(" replace into {$table}
                set bo_table = '".sql_escape_string((string) $storage_bo)."',
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

        $storage_bo = function_exists('eottae_shop_storage_bo_table')
            ? eottae_shop_storage_bo_table($bo_table)
            : preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);

        eottae_shop_map_thumb_delete($storage_bo, $wr_id);

        $file_name = preg_replace('/[^a-z0-9_]/i', '', (string) $storage_bo).'_'.(int) $wr_id.'_ai_'.substr(md5(uniqid('', true)), 0, 12).'.png';
        if (@rename($src, $dir.'/'.$file_name)) {
            @chmod($dir.'/'.$file_name, G5_FILE_PERMISSION);
            $table = eottae_shop_map_thumb_table();
            sql_query(" replace into {$table}
                set bo_table = '".sql_escape_string((string) $storage_bo)."',
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
        if ($bo_table !== '' && function_exists('eottae_shop_storage_bo_table')) {
            $bo_table = eottae_shop_storage_bo_table($bo_table);
        }
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

        return eottae_map_public_url(G5_DATA_URL.'/file/'.$bo_table.'/'.$row['bf_file']);
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

        if (function_exists('eottae_shop_map_thumb_get')) {
            $map_thumb = eottae_shop_map_thumb_get($storage_bo, $wr_id);
            if (!empty($map_thumb['url'])) {
                return $map_thumb['url'];
            }
        }

        if (!function_exists('get_list_thumbnail')) {
            include_once G5_LIB_PATH.'/thumbnail.lib.php';
        }
        if (function_exists('get_list_thumbnail')) {
            $thumb = get_list_thumbnail($storage_bo, $wr_id, 200, 200, false, true);
            if (!empty($thumb['src'])) {
                return function_exists('eottae_map_public_url') ? eottae_map_public_url($thumb['src']) : $thumb['src'];
            }
        }

        if (is_array($row) && !empty($row['file'][0]['file']) && !empty($row['file'][0]['path'])) {
            return function_exists('eottae_map_public_url')
                ? eottae_map_public_url($row['file'][0]['path'].'/'.$row['file'][0]['file'])
                : $row['file'][0]['path'].'/'.$row['file'][0]['file'];
        }

        $representative = eottae_shop_representative_image_url($storage_bo, $wr_id);
        if ($representative !== '') {
            return function_exists('eottae_map_public_url') ? eottae_map_public_url($representative) : $representative;
        }

        return '';
    }
}

if (!function_exists('eottae_shop_map_marker_thumb_url')) {
    /**
     * Google Maps 마커용 썸네일 — 지도 전용 → 목록 카드(eottae_shop_card_thumb)와 동일 우선순위
     */
    function eottae_shop_map_marker_thumb_url($bo_table, $wr_id, $row = null)
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

        if (function_exists('eottae_shop_map_thumb_get')) {
            $map_thumb = eottae_shop_map_thumb_get($storage_bo, $wr_id);
            if (!empty($map_thumb['url'])) {
                return $map_thumb['url'];
            }
        }

        if (is_array($row) && function_exists('eottae_shop_card_thumb')) {
            $card_thumb = eottae_shop_card_thumb($row, $bo_table);
            if ($card_thumb !== '') {
                return function_exists('eottae_map_public_url') ? eottae_map_public_url($card_thumb) : $card_thumb;
            }
        }

        if (function_exists('eottae_shop_listing_thumb_url')) {
            return eottae_shop_listing_thumb_url($bo_table, $wr_id, $row);
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
            where wr_is_comment = 0 and mb_id = '{$mb_id}' and wr_1 = '{$shop_wr_id}'
              and (wr_4 = '' or wr_4 = 'visible') limit 1 ");

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
        $row = sql_fetch(" select wr_id, mb_id, wr_1, wr_4 from {$write_table}
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

        $shop_wr_id = (int) $row['wr_1'];
        if ($shop_wr_id > 0 && function_exists('eottae_sync_shop_review_stats')) {
            eottae_sync_shop_review_stats($shop_wr_id);
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

if (!function_exists('eottae_community_view_media')) {
    /**
     * 게시글 보기용 첨부 이미지·파일 분리
     *
     * @return array{images: string[], files: array<int, array<string, mixed>>}
     */
    function eottae_community_view_media($view)
    {
        if (!function_exists('get_file_thumbnail')) {
            include_once G5_LIB_PATH.'/thumbnail.lib.php';
        }

        $images = array();
        $files = array();

        if (empty($view['file']) || !is_array($view['file'])) {
            return array('images' => $images, 'files' => $files);
        }

        foreach ($view['file'] as $file) {
            if (empty($file['source'])) {
                continue;
            }

            if (!empty($file['view'])) {
                $images[] = get_file_thumbnail($file);
            } else {
                $files[] = $file;
            }
        }

        return array('images' => $images, 'files' => $files);
    }
}

if (!function_exists('eottae_community_view_gallery_class')) {
    function eottae_community_view_gallery_class($count)
    {
        $count = (int) $count;
        if ($count <= 0) {
            return '';
        }
        if ($count >= 5) {
            return 'community-view-page__gallery--count-many';
        }

        return 'community-view-page__gallery--count-'.$count;
    }
}

if (!function_exists('eottae_community_photo_limit')) {
    function eottae_community_photo_limit()
    {
        return 7;
    }
}

if (!function_exists('eottae_gallery_board_table')) {
    function eottae_gallery_board_table()
    {
        return defined('EOTTae_GALLERY_TABLE') ? EOTTae_GALLERY_TABLE : 'gallery';
    }
}

if (!function_exists('eottae_is_gallery_board_table')) {
    function eottae_is_gallery_board_table($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return $bo_table !== '' && $bo_table === eottae_gallery_board_table();
    }
}

if (!function_exists('eottae_is_gallery_board')) {
    function eottae_is_gallery_board($board)
    {
        return is_array($board) && !empty($board['bo_table']) && eottae_is_gallery_board_table($board['bo_table']);
    }
}

if (!function_exists('eottae_gallery_photo_limit')) {
    function eottae_gallery_photo_limit()
    {
        return 10;
    }
}

if (!function_exists('eottae_gallery_upload_size')) {
    function eottae_gallery_upload_size()
    {
        return 20 * 1024 * 1024;
    }
}

if (!function_exists('eottae_gallery_board_ensure_settings')) {
    function eottae_gallery_board_ensure_settings()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        global $g5, $board;

        $bo_table = eottae_gallery_board_table();
        if ($bo_table === '' || empty($g5['board_table'])) {
            return;
        }

        $limit = eottae_gallery_photo_limit();
        $upload_size = (int) eottae_gallery_upload_size();
        $row = sql_fetch(" select bo_upload_count, bo_upload_size, bo_upload_level from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' ");
        if (!$row) {
            return;
        }

        $sets = array();
        if ((int) $row['bo_upload_count'] < $limit) {
            $sets[] = "bo_upload_count = '{$limit}'";
        }
        if ((int) $row['bo_upload_size'] < $upload_size) {
            $sets[] = "bo_upload_size = '{$upload_size}'";
        }
        if ((int) $row['bo_upload_level'] > 1) {
            $sets[] = "bo_upload_level = '1'";
        }

        if ($sets) {
            sql_query(" update {$g5['board_table']} set ".implode(', ', $sets)." where bo_table = '".sql_escape_string($bo_table)."' ");
        }

        if (is_array($board) && !empty($board['bo_table']) && $board['bo_table'] === $bo_table) {
            if ((int) ($board['bo_upload_count'] ?? 0) < $limit) {
                $board['bo_upload_count'] = $limit;
            }
            if ((int) ($board['bo_upload_size'] ?? 0) < $upload_size) {
                $board['bo_upload_size'] = $upload_size;
            }
            if ((int) ($board['bo_upload_level'] ?? 2) > 1) {
                $board['bo_upload_level'] = 1;
            }
        }
    }
}

if (!function_exists('eottae_gallery_write_prepare')) {
    function eottae_gallery_write_prepare()
    {
        global $board, $member, $file_count, $is_file;

        if (!eottae_is_gallery_board($board)) {
            return;
        }

        eottae_gallery_board_ensure_settings();

        $limit = eottae_gallery_photo_limit();
        $file_count = max((int) $file_count, $limit, (int) ($board['bo_upload_count'] ?? 0));
        $board['bo_upload_count'] = max((int) ($board['bo_upload_count'] ?? 0), $limit);
        $board['bo_upload_size'] = max((int) ($board['bo_upload_size'] ?? 0), eottae_gallery_upload_size());

        if (!empty($member['mb_level']) && (int) $member['mb_level'] >= (int) ($board['bo_upload_level'] ?? 1)) {
            $is_file = true;
        }
    }
}

if (!function_exists('eottae_gallery_file_is_image')) {
    function eottae_gallery_file_is_image($filename)
    {
        $filename = strtolower((string) $filename);

        return (bool) preg_match('/\.(jpe?g|png|gif|webp|bmp|heic|heif|avif)$/i', $filename);
    }
}

if (!function_exists('eottae_gallery_file_view_html')) {
    function eottae_gallery_file_view_html($file_row, $bo_table = '')
    {
        if (!is_array($file_row) || empty($file_row['file'])) {
            return '';
        }

        if (!empty($file_row['view'])) {
            return $file_row['view'];
        }

        if (!eottae_gallery_file_is_image($file_row['source'] ?? $file_row['file'])) {
            return '';
        }

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return '';
        }

        $alt = !empty($file_row['bf_content']) ? get_text($file_row['bf_content']) : get_text($file_row['source'] ?? '');
        $src = G5_DATA_URL.'/file/'.$bo_table.'/'.urlencode($file_row['file']);
        $href = !empty($file_row['href']) ? $file_row['href'] : G5_BBS_URL.'/view_image.php?bo_table='.$bo_table.'&amp;fn='.urlencode($file_row['file']);

        return '<a href="'.$href.'" target="_blank" class="view_image"><img src="'.$src.'" alt="'.htmlspecialchars($alt, ENT_QUOTES).'" class="board-view__hero-img" loading="lazy"></a>';
    }
}

if (!function_exists('eottae_community_board_ensure_settings')) {
    function eottae_community_board_ensure_settings()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        global $g5;

        $bo_table = eottae_community_board_table();
        if ($bo_table === '' || empty($g5['board_table'])) {
            return;
        }

        $limit = eottae_community_photo_limit();
        $row = sql_fetch(" select bo_upload_count from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' ");
        if (!$row) {
            return;
        }

        if ((int) $row['bo_upload_count'] < $limit) {
            sql_query(" update {$g5['board_table']} set bo_upload_count = '{$limit}' where bo_table = '".sql_escape_string($bo_table)."' ");
        }
    }
}

if (!function_exists('eottae_community_normalize_url')) {
    function eottae_community_normalize_url($url)
    {
        $url = trim(strip_tags((string) $url));
        if ($url === '') {
            return '';
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        return substr($url, 0, 1000);
    }
}

if (!function_exists('eottae_community_write_link_values')) {
    /**
     * @return array{youtube: string, url: string}
     */
    function eottae_community_write_link_values($write = null)
    {
        $youtube = '';
        $url = '';

        if (is_array($write)) {
            $youtube = isset($write['wr_link1']) ? get_text($write['wr_link1']) : '';
            $url = isset($write['wr_link2']) ? get_text($write['wr_link2']) : '';
        }

        return array(
            'youtube' => $youtube,
            'url'     => $url,
        );
    }
}

if (!function_exists('eottae_community_write_photo_count')) {
    function eottae_community_write_photo_count($board, $file_count = 0)
    {
        if (!is_array($board) || empty($board['bo_table']) || !function_exists('eottae_is_community_board') || !eottae_is_community_board($board['bo_table'])) {
            return (int) $file_count;
        }

        eottae_community_board_ensure_settings();

        return max(eottae_community_photo_limit(), (int) $file_count, (int) ($board['bo_upload_count'] ?? 0));
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
            '골프',
            '스크린골프',
            '세탁',
            '법률',
            '회계',
            '부동산',
            '배달',
            '반려동물',
            '헬스',
            'IT',
            '쇼핑',
            'JTV',
            'KTV',
            '클럽',
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

if (!function_exists('eottae_shop_near_radius_km')) {
    function eottae_shop_near_radius_km()
    {
        $km = (float) g5site_cfg('map_near_radius_km', '1');
        if ($km <= 0) {
            $km = 1;
        }

        return $km;
    }
}

if (!function_exists('eottae_shop_filter_rows_within_radius')) {
    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    function eottae_shop_filter_rows_within_radius($rows, $radius_km)
    {
        if (!is_array($rows)) {
            return array();
        }

        $radius_km = (float) $radius_km;
        if ($radius_km <= 0) {
            return $rows;
        }

        $filtered = array();
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $dist = isset($row['_eottae_distance_km']) ? (float) $row['_eottae_distance_km'] : 99999;
            if ($dist <= $radius_km) {
                $filtered[] = $row;
            }
        }

        return $filtered;
    }
}

if (!function_exists('eottae_shop_is_near_search_request')) {
    function eottae_shop_is_near_search_request(array $args = array())
    {
        $sst = isset($args['sst']) ? trim((string) $args['sst']) : '';
        if ($sst === '' && isset($_GET['sst'])) {
            $sst = trim((string) $_GET['sst']);
        }
        if ($sst !== 'near') {
            return false;
        }

        $lat = isset($args['eottae_lat']) ? trim((string) $args['eottae_lat']) : '';
        $lng = isset($args['eottae_lng']) ? trim((string) $args['eottae_lng']) : '';
        if ($lat === '' && isset($_GET['eottae_lat'])) {
            $lat = trim((string) $_GET['eottae_lat']);
        }
        if ($lng === '' && isset($_GET['eottae_lng'])) {
            $lng = trim((string) $_GET['eottae_lng']);
        }

        return $lat !== '' && $lng !== '' && is_numeric($lat) && is_numeric($lng);
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

        $where_sql = implode(' and ', eottae_shop_list_where_parts($bo_table, $args));
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
        $rows = eottae_shop_filter_rows_within_radius($rows, eottae_shop_near_radius_km());
        $total_count = count($rows);

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

if (!function_exists('eottae_shop_list_filters_from_request')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_shop_list_filters_from_request()
    {
        $sca = isset($_GET['sca']) ? trim((string) $_GET['sca']) : '';
        $sfl = isset($_GET['sfl']) ? trim((string) $_GET['sfl']) : '';
        $stx = isset($_GET['stx']) ? trim((string) $_GET['stx']) : '';

        if ($stx === '') {
            $sfl = '';
        } elseif ($sfl === 'wr_2' && function_exists('eottae_shop_region_options')) {
            $regions = eottae_shop_region_options();
            if (!in_array($stx, $regions, true)) {
                $sfl = 'wr_subject||wr_content';
            }
        }

        return array(
            'sca'        => $sca,
            'sfl'        => $sfl,
            'stx'        => $stx,
            'sst'        => isset($_GET['sst']) ? trim((string) $_GET['sst']) : '',
            'sod'        => isset($_GET['sod']) ? trim((string) $_GET['sod']) : '',
            'eottae_lat' => isset($_GET['eottae_lat']) ? trim((string) $_GET['eottae_lat']) : '',
            'eottae_lng' => isset($_GET['eottae_lng']) ? trim((string) $_GET['eottae_lng']) : '',
        );
    }
}

if (!function_exists('eottae_shop_list_write_table')) {
    function eottae_shop_list_write_table($bo_table)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        if ($bo_table === '') {
            return '';
        }

        $master = function_exists('eottae_shop_segment_master_category')
            ? eottae_shop_segment_master_category($bo_table)
            : '';

        return $master !== '' ? $g5['write_prefix'].eottae_shop_table() : $g5['write_prefix'].$bo_table;
    }
}

if (!function_exists('eottae_shop_list_where_parts')) {
    /**
     * @param string $bo_table
     * @param array<string, mixed> $args
     * @return array<int, string>
     */
    function eottae_shop_list_where_parts($bo_table, array $args = array())
    {
        $where = array('wr_is_comment = 0');

        $master = function_exists('eottae_shop_segment_master_category')
            ? eottae_shop_segment_master_category($bo_table)
            : '';
        if ($master !== '') {
            $where[] = "wr_1 = '".sql_escape_string($master)."'";
        }

        $sca = isset($args['sca']) ? trim((string) $args['sca']) : '';
        $sfl = isset($args['sfl']) ? trim((string) $args['sfl']) : '';
        $stx = isset($args['stx']) ? trim((string) $args['stx']) : '';

        if ($sca !== '') {
            $esc = sql_escape_string($sca);
            $where[] = "(ca_name = '{$esc}' or wr_1 = '{$esc}')";
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

        return $where;
    }
}

if (!function_exists('eottae_shop_infinite_batch_limit')) {
    function eottae_shop_infinite_batch_limit($offset)
    {
        $offset = max(0, (int) $offset);
        if ($offset < 1) {
            return 20;
        }
        if ($offset < 21) {
            return 30;
        }

        return 40;
    }
}

if (!function_exists('eottae_shop_list_order_sql')) {
    /**
     * @param array<string, mixed> $args
     */
    function eottae_shop_list_order_sql(array $args = array())
    {
        $sst = isset($args['sst']) ? trim((string) $args['sst']) : '';
        $sod = isset($args['sod']) ? strtolower(trim((string) $args['sod'])) : 'desc';
        if ($sod !== 'asc' && $sod !== 'desc') {
            $sod = 'desc';
        }

        if ($sst === 'near') {
            return ' order by wr_id desc ';
        }

        $allowed = array(
            'wr_datetime' => 'wr_datetime',
            'wr_hit'      => 'wr_hit',
            'wr_comment'  => 'wr_comment',
            'wr_good'     => 'wr_good',
        );

        if (isset($allowed[$sst])) {
            return ' order by '.$allowed[$sst].' '.$sod.', wr_id desc ';
        }

        return ' order by wr_datetime desc, wr_id desc ';
    }
}

if (!function_exists('eottae_shop_fetch_raw_rows')) {
    /**
     * 필터에 맞는 업체 원본 행 (지도·목록 공통)
     *
     * @param string $bo_table
     * @param array<string, mixed> $args
     * @return array{total:int, rows:array<int, array<string, mixed>>}
     */
    function eottae_shop_fetch_raw_rows($bo_table, array $args = array())
    {
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        if ($bo_table === '' || !function_exists('eottae_is_shop_board') || !eottae_is_shop_board($bo_table)) {
            return array('total' => 0, 'rows' => array());
        }

        $write_table = eottae_shop_list_write_table($bo_table);
        if ($write_table === '') {
            return array('total' => 0, 'rows' => array());
        }

        $exists = sql_fetch(" show tables like '".sql_escape_string($write_table)."' ");
        if (empty($exists)) {
            return array('total' => 0, 'rows' => array());
        }

        $where_sql = implode(' and ', eottae_shop_list_where_parts($bo_table, $args));
        $count = sql_fetch(" select count(*) as cnt from `{$write_table}` where {$where_sql} ");
        $total = isset($count['cnt']) ? (int) $count['cnt'] : 0;
        if ($total < 1) {
            return array('total' => 0, 'rows' => array());
        }

        $max_rows = isset($args['max_rows']) ? max(1, (int) $args['max_rows']) : 2000;
        $max_rows = min(2000, max($total, $max_rows));

        $sst = isset($args['sst']) ? trim((string) $args['sst']) : '';
        $user_lat = isset($args['eottae_lat']) ? trim((string) $args['eottae_lat']) : '';
        $user_lng = isset($args['eottae_lng']) ? trim((string) $args['eottae_lng']) : '';

        if ($sst === 'near' && $user_lat !== '' && $user_lng !== '' && is_numeric($user_lat) && is_numeric($user_lng)) {
            $result = sql_query(" select * from `{$write_table}` where {$where_sql} order by wr_id desc limit {$max_rows} ", false);
            $rows = array();
            if ($result) {
                while ($row = sql_fetch_array($result)) {
                    $rows[] = $row;
                }
            }
            eottae_shop_sort_list_by_distance($rows, $user_lat, $user_lng);
            $radius_km = eottae_shop_near_radius_km();
            $rows = eottae_shop_filter_rows_within_radius($rows, $radius_km);

            return array('total' => count($rows), 'rows' => $rows);
        }

        $order_sql = eottae_shop_list_order_sql($args);
        $offset = isset($args['offset']) ? max(0, (int) $args['offset']) : 0;
        $limit = isset($args['limit']) ? max(1, (int) $args['limit']) : $max_rows;

        if ($offset > 0 || $limit < $max_rows) {
            $sql = " select * from `{$write_table}` where {$where_sql} {$order_sql} limit {$offset}, {$limit} ";
        } else {
            $sql = " select * from `{$write_table}` where {$where_sql} {$order_sql} limit {$max_rows} ";
        }

        $result = sql_query($sql, false);
        $rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $rows[] = $row;
            }
        }

        return array('total' => $total, 'rows' => $rows);
    }
}

if (!function_exists('eottae_shop_list_chunk')) {
    /**
     * 무한 스크롤용 목록 청크
     *
     * @param string $bo_table
     * @param array<string, mixed> $board
     * @param string $board_skin_url
     * @param array<string, mixed> $args offset, limit, filters
     * @return array{list:array<int, array<string, mixed>>, total_count:int, has_more:bool}
     */
    function eottae_shop_list_chunk($bo_table, $board, $board_skin_url, array $args = array())
    {
        $offset = isset($args['offset']) ? max(0, (int) $args['offset']) : 0;
        $limit = isset($args['limit']) ? max(1, (int) $args['limit']) : eottae_shop_infinite_batch_limit($offset);

        $sst = isset($args['sst']) ? trim((string) $args['sst']) : '';
        $fetch_args = $args;
        unset($fetch_args['offset'], $fetch_args['limit']);

        if ($sst === 'near'
            && !empty($args['eottae_lat']) && is_numeric($args['eottae_lat'])
            && !empty($args['eottae_lng']) && is_numeric($args['eottae_lng'])
        ) {
            $fetch_args['max_rows'] = 2000;
            $bundle = eottae_shop_fetch_raw_rows($bo_table, $fetch_args);
            $slice = array_slice($bundle['rows'], $offset, $limit);
        } else {
            $fetch_args['offset'] = $offset;
            $fetch_args['limit'] = $limit;
            $bundle = eottae_shop_fetch_raw_rows($bo_table, $fetch_args);
            $slice = $bundle['rows'];
        }

        $total = (int) ($bundle['total'] ?? 0);
        $subject_len = G5_IS_MOBILE ? (int) $board['bo_mobile_subject_len'] : (int) $board['bo_subject_len'];
        $list = array();
        foreach ($slice as $row) {
            $list[] = get_list($row, $board, $board_skin_url, $subject_len);
        }

        $loaded = count($list);
        $has_more = ($offset + $loaded) < $total;

        return array(
            'list'         => $list,
            'total_count'  => $total,
            'has_more'     => $has_more,
            'next_offset'  => $offset + $loaded,
        );
    }
}

if (!function_exists('eottae_shop_render_cards_html')) {
    /**
     * @param array<int, array<string, mixed>> $list
     */
    function eottae_shop_render_cards_html($list, $bo_table = '')
    {
        if (!is_array($list) || count($list) === 0) {
            return '';
        }

        $list = eottae_shop_list_card_attach_meta($list, $bo_table);

        ob_start();
        foreach ($list as $row) {
            if (!is_array($row)) {
                continue;
            }
            $row['bo_table'] = $bo_table;
            eottae_render_shop_card($row, $bo_table, 'list');
        }

        return (string) ob_get_clean();
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
            $marker_bo_table = $bo_table !== '' ? $bo_table : eottae_shop_table();
            $thumbnail = '';
            if (function_exists('eottae_shop_map_marker_thumb_url')) {
                $thumbnail = eottae_shop_map_marker_thumb_url($marker_bo_table, $shop['wr_id'], $row);
            } elseif (function_exists('eottae_shop_listing_thumb_url')) {
                $thumbnail = eottae_shop_listing_thumb_url($marker_bo_table, $shop['wr_id'], $row);
            } else {
                $storage_bo = function_exists('eottae_shop_storage_bo_table')
                    ? eottae_shop_storage_bo_table($marker_bo_table)
                    : $marker_bo_table;
                $thumb = eottae_shop_map_thumb_get($storage_bo, $shop['wr_id']);
                $thumbnail = !empty($thumb['url']) ? $thumb['url'] : '';
                if ($thumbnail === '' && !empty($row['file'][0]['file']) && !empty($row['file'][0]['path'])) {
                    $thumbnail = $row['file'][0]['path'].'/'.$row['file'][0]['file'];
                } elseif ($thumbnail === '') {
                    $thumbnail = eottae_shop_representative_image_url($storage_bo, $shop['wr_id']);
                }
            }
            $thumbnail = function_exists('eottae_map_public_url') ? eottae_map_public_url($thumbnail) : $thumbnail;
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

        if (function_exists('eottae_merge_runtime_secrets')) {
            eottae_merge_runtime_secrets();
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

if (!function_exists('eottae_runtime_secrets_path')) {
    function eottae_runtime_secrets_path()
    {
        if (defined('G5_DATA_PATH')) {
            return G5_DATA_PATH.'/eottae-secrets.local.php';
        }

        return G5_PATH.'/data/eottae-secrets.local.php';
    }
}

if (!function_exists('eottae_merge_runtime_secrets')) {
    /**
     * data/eottae-secrets.local.php 값을 $site_config에 병합
     */
    function eottae_merge_runtime_secrets()
    {
        static $merged = false;

        if ($merged) {
            return;
        }
        $merged = true;

        global $site_config;

        if (!isset($site_config) || !is_array($site_config)) {
            $site_config = array();
        }

        $secret_file = eottae_runtime_secrets_path();
        if (!is_file($secret_file) || !is_readable($secret_file)) {
            return;
        }

        $eottae_secrets_override = null;
        include $secret_file;

        if (!isset($eottae_secrets_override) || !is_array($eottae_secrets_override)) {
            return;
        }

        $site_config = array_merge($site_config, $eottae_secrets_override);

        if (function_exists('onoff_map_clear_config_cache')) {
            onoff_map_clear_config_cache();
        }
    }
}

if (!function_exists('eottae_map_runtime_diagnostics')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_map_runtime_diagnostics()
    {
        if (function_exists('eottae_merge_runtime_secrets')) {
            eottae_merge_runtime_secrets();
        }

        $secret_file = function_exists('eottae_runtime_secrets_path')
            ? eottae_runtime_secrets_path()
            : G5_PATH.'/data/eottae-secrets.local.php';

        return array(
            'secrets_path'      => $secret_file,
            'secrets_exists'    => is_file($secret_file),
            'secrets_readable'  => is_file($secret_file) && is_readable($secret_file),
            'has_api_key'       => function_exists('onoff_map_has_api_key') ? onoff_map_has_api_key() : false,
            'local_config_path' => G5_PATH.'/_site.config.local.php',
            'local_config_exists' => is_file(G5_PATH.'/_site.config.local.php'),
        );
    }
}

if (!function_exists('eottae_google_maps_api_key')) {
    function eottae_google_maps_api_key()
    {
        if (function_exists('eottae_merge_runtime_secrets')) {
            eottae_merge_runtime_secrets();
        }

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

if (!function_exists('eottae_shop_resolve_list_href')) {
    /**
     * 업체 상세 — 목록 버튼 URL (영카트 /shop/item.php·짧은주소 충돌 방지)
     *
     * @param string $list_href  bbs/view.php 의 $list_href
     * @param string $bo_table
     * @param string $qstr
     */
    function eottae_shop_resolve_list_href($list_href = '', $bo_table = '', $qstr = '')
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            $bo_table = eottae_shop_table();
        }

        $params = array();
        if ($qstr !== '') {
            $raw = html_entity_decode(str_replace('&amp;', '&', (string) $qstr), ENT_QUOTES, 'UTF-8');
            $parsed = array();
            parse_str(ltrim($raw, '&?'), $parsed);
            $allowed = array('sca', 'sfl', 'stx', 'sst', 'sod', 'sop', 'page', 'eottae_lat', 'eottae_lng');
            foreach ($allowed as $key) {
                if (isset($parsed[$key]) && $parsed[$key] !== '') {
                    $params[$key] = $parsed[$key];
                }
            }
        }

        $safe = function_exists('eottae_board_list_url')
            ? eottae_board_list_url($bo_table, $params)
            : eottae_shop_list_url($params);

        $list_href = trim((string) $list_href);
        if ($list_href === '') {
            return $safe;
        }

        if (preg_match('#/shop/item(?:\.php)?(?:\?|$)#i', $list_href)) {
            return $safe;
        }

        if (defined('G5_SHOP_URL') && G5_SHOP_URL !== '' && strpos($list_href, G5_SHOP_URL) === 0 && strpos($list_href, 'board.php') === false) {
            return $safe;
        }

        if (strpos($list_href, 'board.php') !== false && strpos($list_href, 'bo_table=') !== false) {
            return $list_href;
        }

        if (preg_match('#/shop/?(\?|$)#i', $list_href) && strpos($list_href, 'board.php') === false) {
            return $safe;
        }

        return $safe;
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
     * 업체·세그먼트 게시판 URL 교정
     * - shop: 영카트 /shop/{id} 충돌 방지
     * - food 등: /food/2 짧은주소가 wr_id=2 로 해석되어 페이지 이동 시 "없는 글" 방지
     */
    function eottae_pretty_shop_board_url($url, $folder, $no = '', $query_string = '', $action = '')
    {
        if (!function_exists('eottae_is_shop_board') || !eottae_is_shop_board($folder)) {
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

        if ($no !== '' && ctype_digit((string) $no)) {
            global $g5;

            $wr_id = (int) $no;
            $storage_bo = function_exists('eottae_shop_storage_bo_table')
                ? eottae_shop_storage_bo_table($folder)
                : $folder;
            $write_table = $g5['write_prefix'].$storage_bo;
            $row = sql_fetch(" select wr_id from {$write_table} where wr_id = '{$wr_id}' and wr_is_comment = 0 limit 1 ");
            if (!empty($row['wr_id'])) {
                return eottae_shop_view_url($wr_id, $folder, $query_string);
            }

            return $url;
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

if (!function_exists('eottae_shop_default_cover_url')) {
    /** 업체 사진이 없을 때 상세 대문(히어로) 기본 이미지 */
    function eottae_shop_default_cover_url($category = '')
    {
        $category = trim((string) $category);
        $cebu_img = 'https://images.unsplash.com/photo-%s?auto=format&fit=crop&w=1200&q=80';
        $presets = array(
            '맛집'   => '1555939594-58d7cb561ad1',
            '마사지' => '1544161515-4ab6ce6db874',
            '렌트카' => '1449965408869-aa9dcba15532',
            '투어'   => '1506905925346-21bda4d32df4',
            '골프'   => '1535131743360-94cfed6ab469',
        );

        $photo_id = isset($presets[$category]) ? $presets[$category] : '1518509562904-7fc873a70436';

        return sprintf($cebu_img, $photo_id);
    }
}

if (!function_exists('eottae_shop_gallery_collect_files')) {
    function eottae_shop_gallery_collect_files($files, &$images, $append_image)
    {
        if (empty($files['count'])) {
            return;
        }

        for ($i = 0; $i < (int) $files['count']; $i++) {
            if (empty($files[$i]['file']) || empty($files[$i]['view'])) {
                continue;
            }
            $append_image(
                $files[$i]['path'].'/'.$files[$i]['file'],
                isset($files[$i]['source']) ? get_text($files[$i]['source']) : ''
            );
        }
    }
}

if (!function_exists('eottae_shop_gallery_images')) {
    /**
     * 업체 상세 갤러리 — 첨부 → 지도 썸네일 → 본문 이미지 → 카테고리 기본 대문
     *
     * @param array      $view
     * @param string     $bo_table
     * @param array|null $shop
     */
    function eottae_shop_gallery_images($view, $bo_table = '', $shop = null)
    {
        $images = array();
        if (!is_array($view) || empty($view['wr_id'])) {
            return $images;
        }

        $wr_id = (int) $view['wr_id'];
        if ($bo_table === '') {
            $bo_table = !empty($view['bo_table']) ? (string) $view['bo_table'] : '';
        }
        if ($bo_table === '' && !empty($GLOBALS['bo_table'])) {
            $bo_table = (string) $GLOBALS['bo_table'];
        }

        $view_bo = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $storage_bo = function_exists('eottae_shop_storage_bo_table')
            ? eottae_shop_storage_bo_table($view_bo)
            : $view_bo;

        $append_image = function ($src, $alt = '', $fallback = false) use (&$images) {
            $src = trim((string) $src);
            if ($src === '') {
                return;
            }
            foreach ($images as $img) {
                if ($img['src'] === $src) {
                    return;
                }
            }
            $images[] = array(
                'src'      => $src,
                'alt'      => (string) $alt,
                'fallback' => (bool) $fallback,
            );
        };

        if (!empty($view['file']['count'])) {
            eottae_shop_gallery_collect_files($view['file'], $images, $append_image);
        }

        if (function_exists('get_file')) {
            $file_tables = array();
            if ($storage_bo !== '') {
                $file_tables[] = $storage_bo;
            }
            if ($view_bo !== '' && $view_bo !== $storage_bo) {
                $file_tables[] = $view_bo;
            }
            foreach ($file_tables as $file_bo) {
                if (!empty($images)) {
                    break;
                }
                eottae_shop_gallery_collect_files(get_file($file_bo, $wr_id), $images, $append_image);
            }
        }

        if (empty($images) && function_exists('eottae_shop_map_thumb_get')) {
            $thumb_tables = array();
            if ($storage_bo !== '') {
                $thumb_tables[] = $storage_bo;
            }
            if ($view_bo !== '' && $view_bo !== $storage_bo) {
                $thumb_tables[] = $view_bo;
            }
            foreach ($thumb_tables as $thumb_bo) {
                $map_thumb = eottae_shop_map_thumb_get($thumb_bo, $wr_id);
                if (!empty($map_thumb['url'])) {
                    $append_image($map_thumb['url']);
                    break;
                }
            }
        }

        if (empty($images) && $storage_bo !== '' && function_exists('eottae_shop_representative_image_url')) {
            $representative = eottae_shop_representative_image_url($storage_bo, $wr_id);
            if ($representative !== '') {
                $append_image($representative);
            }
        }

        if (empty($images) && $storage_bo !== '') {
            if (!function_exists('get_list_thumbnail')) {
                include_once G5_LIB_PATH.'/thumbnail.lib.php';
            }
            if (function_exists('get_list_thumbnail')) {
                $thumb = get_list_thumbnail($storage_bo, $wr_id, 1200, 675, false, true);
                if (!empty($thumb['src'])) {
                    $append_image($thumb['src'], isset($thumb['alt']) ? $thumb['alt'] : '');
                }
            }
        }

        if (empty($images)) {
            $category = '';
            if (is_array($shop) && !empty($shop['category'])) {
                $category = (string) $shop['category'];
            } elseif (!empty($view['wr_1'])) {
                $category = (string) $view['wr_1'];
            }
            $append_image(
                eottae_shop_default_cover_url($category),
                $category !== '' ? $category : '세부어때',
                true
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
        $table = '';
        if (isset($params['bo_table'])) {
            $table = (string) $params['bo_table'];
            unset($params['bo_table']);
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
            array('key' => 'golf_join', 'label' => '골프조인', 'href' => function_exists('eottae_golf_join_list_url') ? eottae_golf_join_list_url() : G5_URL.'/golf-join/'),
            array('key' => 'community', 'label' => '커뮤니티', 'href' => eottae_community_list_url()),
            array('key' => 'column', 'label' => function_exists('eottae_column_menu_label') ? eottae_column_menu_label() : '컬럼', 'href' => function_exists('eottae_column_list_url') ? eottae_column_list_url() : G5_URL.'/column/'),
            array('key' => 'adroom', 'label' => '광고방', 'href' => function_exists('eottae_adroom_list_url') ? eottae_adroom_list_url() : G5_URL.'/ad-room/'),
            array('key' => 'people', 'label' => '사람찾기', 'href' => eottae_board_list_url(defined('EOTTae_PEOPLE_TABLE') ? EOTTae_PEOPLE_TABLE : 'people')),
            array('key' => 'job', 'label' => '구인구직', 'href' => eottae_board_list_url(defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job')),
            array('key' => 'estate', 'label' => '부동산', 'href' => eottae_board_list_url(defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate')),
            array('key' => 'gallery', 'label' => '갤러리', 'href' => eottae_board_list_url(defined('EOTTae_GALLERY_TABLE') ? EOTTae_GALLERY_TABLE : 'gallery')),
            array('key' => 'youtube', 'label' => '유튜브', 'href' => eottae_board_list_url(defined('EOTTae_YOUTUBE_TABLE') ? EOTTae_YOUTUBE_TABLE : 'youtube')),
            array('key' => 'talk', 'label' => '세부톡', 'href' => function_exists('eottae_talkroom_list_url') ? eottae_talkroom_list_url() : G5_URL.'/talk', 'desktop_action' => true),
            array('key' => 'calendar', 'label' => '세부일정', 'href' => function_exists('eottae_calendar_list_url') ? eottae_calendar_list_url() : G5_URL.'/calendar/'),
        );
    }
}

if (!function_exists('eottae_gnb_nav_link_classes')) {
    function eottae_gnb_nav_link_classes(array $link, $context = 'desktop', $active = false)
    {
        $classes = array();

        if ($context === 'mobile') {
            $classes[] = 'eottae-gnb-header__mobile-link';
        } else {
            $classes[] = 'eottae-gnb-header__nav-link';
        }

        if (!empty($link['emphasis'])) {
            $emphasis = preg_replace('/[^a-z-]/', '', (string) $link['emphasis']);
            if ($emphasis !== '') {
                $classes[] = ($context === 'mobile' ? 'eottae-gnb-header__mobile-link' : 'eottae-gnb-header__nav-link').'--'.$emphasis;
            }
        }

        if (!empty($link['nav_end']) && $context !== 'mobile') {
            $classes[] = 'eottae-gnb-header__nav-link--end';
        }

        if ($active) {
            $classes[] = 'is-active';
        }

        return implode(' ', $classes);
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
            case 'talk':
                return strpos($uri, '/talk') !== false || strpos($uri, '/page/eottae-talk') !== false;
            case 'calendar':
                return strpos($uri, '/calendar') !== false || strpos($uri, '/page/eottae-calendar') !== false;
            case 'column':
                return strpos($uri, '/column') !== false || strpos($uri, '/page/eottae-column') !== false;
            case 'golf_join':
                return strpos($uri, '/golf-join') !== false || strpos($uri, '/page/eottae-golf') !== false;
            case 'adroom':
                return strpos($uri, '/ad-room') !== false
                    || $bo === (defined('EOTTae_ADROOM_TABLE') ? EOTTae_ADROOM_TABLE : 'adroom');
            case 'mypage':
                return strpos($uri, '/page/eottae-') !== false;
            default:
                return isset($board_map[$key]) && $bo === $board_map[$key];
        }
    }
}
