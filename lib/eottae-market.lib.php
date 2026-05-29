<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('sanitize_location_fields') && is_file(G5_LIB_PATH.'/eottae-location.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-location.lib.php';
}

/**
 * 중고장터 (bo_table=market)
 *
 * wr_1  가격(필리핀 페소)
 * wr_2  거래상태 selling|reserved|sold
 * wr_3  자동분류 지역
 * wr_4  상세위치 텍스트
 * wr_5  위도 latitude
 * wr_6  경도 longitude
 * wr_7  연락방법
 * wr_8  가격제안 가능 여부 1|0
 * wr_9  지도표시 여부 1|0
 * wr_10 free = 무료나눔
 */

if (!function_exists('eottae_market_board_table')) {
    function eottae_market_board_table()
    {
        return defined('EOTTae_MARKET_TABLE') ? EOTTae_MARKET_TABLE : 'market';
    }
}

if (!function_exists('eottae_is_market_board')) {
    function eottae_is_market_board($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return $bo_table !== '' && $bo_table === eottae_market_board_table();
    }
}

if (!function_exists('eottae_market_statuses')) {
    function eottae_market_statuses()
    {
        return array(
            'selling'  => '판매중',
            'reserved' => '예약중',
            'sold'     => '판매완료',
        );
    }
}

if (!function_exists('eottae_market_normalize_status')) {
    function eottae_market_normalize_status($status)
    {
        $status = preg_replace('/[^a-z]/', '', (string) $status);

        return array_key_exists($status, eottae_market_statuses()) ? $status : 'selling';
    }
}

if (!function_exists('eottae_market_status_label')) {
    function eottae_market_status_label($status)
    {
        $statuses = eottae_market_statuses();

        return $statuses[eottae_market_normalize_status($status)] ?? '판매중';
    }
}

if (!function_exists('eottae_market_render_status_badge')) {
    function eottae_market_render_status_badge($status, $extra_class = '')
    {
        $status = eottae_market_normalize_status($status);
        $class = 'market-status-badge market-status-badge--'.$status;
        if ($extra_class !== '') {
            $class .= ' '.trim($extra_class);
        }

        return '<span class="'.$class.'">'.get_text(eottae_market_status_label($status)).'</span>';
    }
}

if (!function_exists('eottae_market_regions')) {
    function eottae_market_regions()
    {
        return function_exists('eottae_location_area_options')
            ? eottae_location_area_options()
            : array(
                'cebu'        => '세부시티',
                'mactan'      => '막탄',
                'lapu'        => '라푸라푸',
                'mandaue'     => '만다우에',
                'talisay'     => '탈리사이',
                'consolacion' => '콘솔라시온',
                'other'       => '기타',
            );
    }
}

if (!function_exists('eottae_market_normalize_region')) {
    function eottae_market_normalize_region($region)
    {
        if (function_exists('eottae_location_normalize_area')) {
            return eottae_location_normalize_area($region);
        }

        $region = trim(strip_tags((string) $region));
        $regions = eottae_market_regions();
        if (isset($regions[$region])) {
            return $region;
        }
        foreach ($regions as $key => $label) {
            if ($region === $label) {
                return $key;
            }
        }

        return $region !== '' ? 'other' : '';
    }
}

if (!function_exists('eottae_market_region_label')) {
    function eottae_market_region_label($region)
    {
        return function_exists('eottae_location_area_label')
            ? eottae_location_area_label($region)
            : (eottae_market_regions()[eottae_market_normalize_region($region)] ?? '기타');
    }
}

if (!function_exists('eottae_market_detect_region_from_text')) {
    function eottae_market_detect_region_from_text($text)
    {
        return function_exists('get_auto_area_from_address') ? get_auto_area_from_address($text) : '';
    }
}

if (!function_exists('eottae_market_detect_region_from_coords')) {
    function eottae_market_detect_region_from_coords($lat, $lng)
    {
        return function_exists('get_auto_area_from_latlng') ? get_auto_area_from_latlng($lat, $lng) : '';
    }
}

if (!function_exists('eottae_market_detect_region')) {
    function eottae_market_detect_region($address, $lat = '', $lng = '')
    {
        return function_exists('eottae_location_auto_area')
            ? eottae_location_auto_area($address, $lat, $lng)
            : 'other';
    }
}

if (!function_exists('eottae_market_free_flag')) {
    function eottae_market_free_flag()
    {
        return 'free';
    }
}

if (!function_exists('eottae_market_is_free_giveaway')) {
    /**
     * @param array<string, mixed>|int|string $price_or_row
     */
    function eottae_market_is_free_giveaway($price_or_row, $wr_10 = '')
    {
        if (is_array($price_or_row)) {
            $wr_10 = (string) ($price_or_row['wr_10'] ?? '');
            $price_or_row = $price_or_row['wr_1'] ?? 0;
        }

        if ((string) $wr_10 === eottae_market_free_flag()) {
            return true;
        }

        return (int) preg_replace('/[^0-9]/', '', (string) $price_or_row) < 1;
    }
}

if (!function_exists('eottae_market_format_price')) {
    function eottae_market_format_price($price, $wr_10 = '')
    {
        if (eottae_market_is_free_giveaway($price, $wr_10)) {
            return '무료나눔';
        }

        $price = (int) preg_replace('/[^0-9]/', '', (string) $price);

        return '₱'.number_format($price);
    }
}

if (!function_exists('eottae_market_list_url')) {
    function eottae_market_list_url(array $params = array())
    {
        $bo_table = eottae_market_board_table();
        $url = function_exists('get_pretty_url')
            ? get_pretty_url($bo_table)
            : G5_BBS_URL.'/board.php?bo_table='.$bo_table;

        if (empty($params)) {
            return $url;
        }

        $query = http_build_query($params);

        return $url.(strpos($url, '?') !== false ? '&' : '?').$query;
    }
}

if (!function_exists('eottae_market_free_list_url')) {
    function eottae_market_free_list_url()
    {
        return eottae_market_list_url(array(
            'sfl' => 'wr_10',
            'stx' => eottae_market_free_flag(),
        ));
    }
}

if (!function_exists('eottae_market_write_free_url')) {
    function eottae_market_write_free_url($write_href = '')
    {
        $write_href = trim((string) $write_href);
        if ($write_href === '') {
            $write_href = G5_BBS_URL.'/write.php?bo_table='.eottae_market_board_table();
        }

        return $write_href.(strpos($write_href, '?') !== false ? '&' : '?').'free=1';
    }
}

if (!function_exists('eottae_market_offer_label')) {
    function eottae_market_offer_label($value)
    {
        return (string) $value === '1' ? '가격제안 가능' : '가격제안 불가';
    }
}

if (!function_exists('eottae_market_normalize_coord')) {
    function eottae_market_normalize_coord($value)
    {
        return function_exists('eottae_location_normalize_coord') ? eottae_location_normalize_coord($value) : '';
    }
}

if (!function_exists('eottae_market_validate_write_post')) {
    function eottae_market_validate_write_post(array $post)
    {
        $subject = trim(strip_tags((string) ($post['wr_subject'] ?? '')));
        if ($subject === '') {
            return array('ok' => false, 'message' => '상품명을 입력해 주세요.');
        }

        $is_free = !empty($post['market_free_giveaway'])
            || (string) ($post['wr_10'] ?? '') === eottae_market_free_flag();

        $price = (int) preg_replace('/[^0-9]/', '', (string) ($post['wr_1'] ?? ''));
        if (!$is_free && $price < 1) {
            return array('ok' => false, 'message' => '가격을 입력해 주세요.');
        }

        $location = trim(strip_tags((string) ($post['wr_4'] ?? '')));
        if ($location === '') {
            return array('ok' => false, 'message' => '거래 상세위치를 입력해 주세요.');
        }

        $content = trim(strip_tags((string) ($post['wr_content'] ?? '')));
        if ($content === '') {
            return array('ok' => false, 'message' => '상품설명을 입력해 주세요.');
        }

        $contact = trim(strip_tags((string) ($post['wr_7'] ?? '')));
        if ($contact === '') {
            return array('ok' => false, 'message' => '연락방법을 입력해 주세요.');
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_market_normalize_write_post')) {
    function eottae_market_normalize_write_post(array $post)
    {
        $is_free = !empty($post['market_free_giveaway'])
            || (string) ($post['wr_10'] ?? '') === eottae_market_free_flag();

        $out = array();
        if ($is_free) {
            $out['wr_1'] = '0';
            $out['wr_8'] = '0';
            $out['wr_10'] = eottae_market_free_flag();
        } else {
            $out['wr_1'] = (string) max(0, (int) preg_replace('/[^0-9]/', '', (string) ($post['wr_1'] ?? '')));
            $out['wr_10'] = '';
        }
        $out['wr_2'] = eottae_market_normalize_status($post['wr_2'] ?? 'selling');
        $location = sanitize_location_fields($post, array(
            'auto_area'     => 'wr_3',
            'location_text' => 'wr_4',
            'latitude'      => 'wr_5',
            'longitude'     => 'wr_6',
            'map_visible'   => 'wr_9',
        ));
        $out['wr_3'] = $location['auto_area'];
        $out['wr_4'] = $location['location_text'];
        $out['wr_5'] = $location['latitude'];
        $out['wr_6'] = $location['longitude'];
        $out['wr_7'] = trim(strip_tags((string) ($post['wr_7'] ?? '')));
        if (function_exists('cut_str') && $out['wr_7'] !== '') {
            $out['wr_7'] = cut_str($out['wr_7'], 200, '');
        }
        if (!$is_free) {
            $out['wr_8'] = !empty($post['wr_8']) && (string) $post['wr_8'] === '1' ? '1' : '0';
        }
        $out['wr_9'] = $location['map_visible'];

        return $out;
    }
}

if (!function_exists('eottae_market_apply_write_post')) {
    function eottae_market_apply_write_post(array $normalized)
    {
        foreach ($normalized as $key => $value) {
            $_POST[$key] = $value;
        }
    }
}

if (!function_exists('eottae_market_get_existing_write')) {
    function eottae_market_get_existing_write($bo_table, $wr_id)
    {
        global $g5;

        if (!eottae_is_market_board($bo_table) || (int) $wr_id < 1) {
            return null;
        }

        $write_table = $g5['write_prefix'].eottae_market_board_table();

        return sql_fetch(" SELECT * FROM `{$write_table}` WHERE wr_id = '".(int) $wr_id."' AND wr_is_comment = 0 LIMIT 1 ");
    }
}

if (!function_exists('eottae_market_can_change_status')) {
    function eottae_market_can_change_status($write, $mb_id, $is_admin = false)
    {
        if ($is_admin) {
            return true;
        }
        if (!is_array($write) || empty($write['wr_id'])) {
            return false;
        }

        return $mb_id !== '' && (string) ($write['mb_id'] ?? '') === (string) $mb_id;
    }
}

if (!function_exists('eottae_market_set_status')) {
    function eottae_market_set_status($bo_table, $wr_id, $status, $mb_id, $is_admin = false)
    {
        if (!eottae_is_market_board($bo_table)) {
            return array('ok' => false, 'message' => '중고장터 게시판이 아닙니다.');
        }

        global $g5;

        $write = eottae_market_get_existing_write($bo_table, $wr_id);
        if (!$write) {
            return array('ok' => false, 'message' => '게시글을 찾을 수 없습니다.');
        }
        if (!eottae_market_can_change_status($write, $mb_id, $is_admin)) {
            return array('ok' => false, 'message' => '거래상태 변경 권한이 없습니다.');
        }

        $status = eottae_market_normalize_status($status);
        $write_table = $g5['write_prefix'].eottae_market_board_table();
        sql_query(" UPDATE `{$write_table}` SET wr_2 = '".sql_escape_string($status)."' WHERE wr_id = '".(int) $wr_id."' ");

        return array(
            'ok'      => true,
            'message' => '거래상태가 '.eottae_market_status_label($status).'으로 변경되었습니다.',
            'status'  => $status,
            'label'   => eottae_market_status_label($status),
        );
    }
}

if (!function_exists('eottae_market_thumb_url')) {
    function eottae_market_thumb_url($bo_table, $wr_id, $width = 420, $height = 420)
    {
        if (!function_exists('get_list_thumbnail')) {
            include_once G5_LIB_PATH.'/thumbnail.lib.php';
        }
        if (function_exists('get_list_thumbnail')) {
            $thumb = get_list_thumbnail($bo_table, (int) $wr_id, $width, $height, false, true);
            if (!empty($thumb['src'])) {
                return $thumb['src'];
            }
        }

        return '';
    }
}

if (!function_exists('eottae_market_view_url')) {
    function eottae_market_view_url($wr_id)
    {
        return function_exists('get_pretty_url')
            ? get_pretty_url(eottae_market_board_table(), (int) $wr_id)
            : G5_BBS_URL.'/board.php?bo_table='.eottae_market_board_table().'&wr_id='.(int) $wr_id;
    }
}

if (!function_exists('eottae_market_load_assets')) {
    function eottae_market_load_assets($with_js = false)
    {
        if (function_exists('eottae_location_picker_load_assets')) {
            eottae_location_picker_load_assets($with_js);
        }

        $css = G5_PATH.'/css/eottae-market.css';
        if (is_file($css)) {
            add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-market.css?ver='.(int) filemtime($css).'">', 35);
        }
        if ($with_js) {
            $js = G5_PATH.'/js/eottae-market.js';
            if (is_file($js)) {
                add_javascript('<script src="'.G5_JS_URL.'/eottae-market.js?ver='.(int) filemtime($js).'" defer></script>', 25);
            }
        }
    }
}

if (!function_exists('eottae_market_board_def')) {
    function eottae_market_board_def()
    {
        return array(
            'bo_table'            => eottae_market_board_table(),
            'bo_subject'          => '중고장터',
            'bo_skin'             => 'eottae-market',
            'bo_mobile_skin'      => 'eottae-market',
            'gr_id'               => 'community',
            'bo_read_level'       => 1,
            'bo_write_level'      => 2,
            'bo_comment_level'    => 2,
            'bo_use_category'     => 0,
            'bo_category_list'    => '',
            'bo_upload_count'     => 8,
            'bo_use_dhtml_editor' => 0,
            'bo_order'            => 19,
            'bo_1_subj'           => '가격',
            'bo_2_subj'           => '거래상태',
            'bo_3_subj'           => '자동분류 지역',
            'bo_4_subj'           => '상세위치',
            'bo_5_subj'           => '위도',
            'bo_6_subj'           => '경도',
            'bo_7_subj'           => '연락방법',
            'bo_8_subj'           => '가격제안 가능 여부',
            'bo_9_subj'           => '지도표시 여부',
            'bo_10_subj'          => '무료나눔 여부',
        );
    }
}

if (!function_exists('eottae_market_ensure_board')) {
    function eottae_market_ensure_board()
    {
        $install_lib = G5_PATH.'/setup/tools/eottae-install.lib.php';
        if (!is_file($install_lib)) {
            return array('ok' => false, 'message' => 'install helper missing');
        }

        include_once $install_lib;
        if (!function_exists('eottae_install_board_exists') || !function_exists('eottae_install_create_board')) {
            return array('ok' => false, 'message' => 'install helper incomplete');
        }

        $bo_table = eottae_market_board_table();
        if (eottae_install_board_exists($bo_table)) {
            return array('ok' => true, 'action' => 'skip');
        }

        if (function_exists('eottae_install_ensure_group')) {
            eottae_install_ensure_group('community', '커뮤니티');
        }

        return eottae_install_create_board(eottae_market_board_def());
    }
}

if (!function_exists('eottae_market_sync_board_settings')) {
    function eottae_market_sync_board_settings()
    {
        global $g5;

        $bo_table = eottae_market_board_table();
        sql_query("
            UPDATE {$g5['board_table']} SET
                bo_skin = 'eottae-market',
                bo_mobile_skin = 'eottae-market',
                bo_use_category = 0,
                bo_use_dhtml_editor = 0,
                bo_upload_count = 8
            WHERE bo_table = '".sql_escape_string($bo_table)."'
            LIMIT 1
        ", false);
    }
}

if (!function_exists('eottae_market_ensure_schema')) {
    function eottae_market_ensure_schema()
    {
        static $done = false;
        if ($done) {
            return true;
        }

        eottae_market_ensure_board();
        eottae_market_sync_board_settings();
        $done = true;

        return true;
    }
}
