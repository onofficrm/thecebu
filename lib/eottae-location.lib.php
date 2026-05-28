<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 세부어때 공통 위치 입력/저장 헬퍼
 *
 * 게시판별 wr 필드 매핑만 넘기면 같은 UI/JS/PHP 정규화를 재사용할 수 있습니다.
 *
 * 예시 매핑:
 * - market: wr_3(auto_area), wr_4(location_text), wr_5(latitude), wr_6(longitude), wr_9(map_visible)
 * - job: wr_1(auto_area label), wr_3(template JSON), wr_4(location_text), wr_5(latitude), wr_6(longitude), wr_7(map_visible)
 * - estate: wr_1(auto_area label), wr_3(template JSON), wr_4(location_text), wr_5(latitude), wr_6(longitude), wr_7(map_visible)
 * - event: 위치 기반 기능 확장 시 wr_* 여유 필드에 같은 키를 매핑
 */

if (!function_exists('eottae_location_area_options')) {
    function eottae_location_area_options()
    {
        return array(
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

if (!function_exists('eottae_location_normalize_area')) {
    function eottae_location_normalize_area($area)
    {
        $area = trim(strip_tags((string) $area));
        $options = eottae_location_area_options();
        if (isset($options[$area])) {
            return $area;
        }
        foreach ($options as $key => $label) {
            if ($area === $label) {
                return $key;
            }
        }

        return $area !== '' ? 'other' : '';
    }
}

if (!function_exists('eottae_location_area_label')) {
    function eottae_location_area_label($area)
    {
        $area = eottae_location_normalize_area($area);
        $options = eottae_location_area_options();

        return $area !== '' && isset($options[$area]) ? $options[$area] : '기타';
    }
}

if (!function_exists('get_auto_area_from_address')) {
    function get_auto_area_from_address($address)
    {
        $text = mb_strtolower(trim((string) $address), 'UTF-8');
        if ($text === '') {
            return '';
        }

        $rules = array(
            'mactan'      => array('mactan', '막탄', 'mactan newtown', 'newtown'),
            'lapu'        => array('lapu-lapu', 'lapu lapu', 'lapulapu', '라푸라푸'),
            'mandaue'     => array('mandaue', '만다우에'),
            'talisay'     => array('talisay', '탈리사이'),
            'consolacion' => array('consolacion', '콘솔라시온'),
            'cebu'        => array('cebu city', 'cebu', '세부시티', '세부 시티', '세부', 'it park', 'i.t. park', 'ayala', 'lahug', 'banilad', 'talamban', 'sm seaside', 'sugbo'),
        );

        foreach ($rules as $area => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($text, $keyword, 0, 'UTF-8') !== false) {
                    return $area;
                }
            }
        }

        return '';
    }
}

if (!function_exists('get_auto_area_from_latlng')) {
    function get_auto_area_from_latlng($lat, $lng)
    {
        if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
            return '';
        }

        $lat = (float) $lat;
        $lng = (float) $lng;

        if ($lat >= 10.24 && $lat <= 10.36 && $lng >= 123.78 && $lng <= 123.90) {
            return 'cebu';
        }
        if ($lat >= 10.25 && $lat <= 10.38 && $lng >= 123.92 && $lng <= 124.05) {
            return 'mactan';
        }
        if ($lat >= 10.25 && $lat <= 10.35 && $lng >= 123.93 && $lng <= 124.04) {
            return 'lapu';
        }
        if ($lat >= 10.30 && $lat <= 10.40 && $lng >= 123.90 && $lng <= 123.97) {
            return 'mandaue';
        }
        if ($lat >= 10.20 && $lat <= 10.29 && $lng >= 123.80 && $lng <= 123.90) {
            return 'talisay';
        }
        if ($lat >= 10.36 && $lat <= 10.43 && $lng >= 123.91 && $lng <= 124.00) {
            return 'consolacion';
        }

        return '';
    }
}

if (!function_exists('eottae_location_auto_area')) {
    function eottae_location_auto_area($address, $lat = '', $lng = '')
    {
        $area = get_auto_area_from_address($address);
        if ($area !== '') {
            return $area;
        }

        $area = get_auto_area_from_latlng($lat, $lng);

        return $area !== '' ? $area : 'other';
    }
}

if (!function_exists('eottae_location_normalize_coord')) {
    function eottae_location_normalize_coord($value)
    {
        $value = trim((string) $value);
        if ($value === '' || !is_numeric($value)) {
            return '';
        }

        return sprintf('%.7F', (float) $value);
    }
}

if (!function_exists('sanitize_location_fields')) {
    /**
     * @param array<string, mixed> $source
     * @param array<string, string> $map source key map
     * @return array{auto_area:string, location_text:string, latitude:string, longitude:string, map_visible:string}
     */
    function sanitize_location_fields(array $source, array $map = array())
    {
        $map = array_merge(array(
            'auto_area'     => 'auto_area',
            'location_text' => 'location_text',
            'latitude'      => 'latitude',
            'longitude'     => 'longitude',
            'map_visible'   => 'map_visible',
        ), $map);

        $location_text = trim(strip_tags((string) ($source[$map['location_text']] ?? '')));
        if (function_exists('cut_str') && $location_text !== '') {
            $location_text = cut_str($location_text, 180, '');
        }

        $lat = eottae_location_normalize_coord($source[$map['latitude']] ?? '');
        $lng = eottae_location_normalize_coord($source[$map['longitude']] ?? '');
        $manual_area = eottae_location_normalize_area($source[$map['auto_area']] ?? '');
        $auto_area = $manual_area !== '' && $manual_area !== 'other'
            ? $manual_area
            : eottae_location_auto_area($location_text, $lat, $lng);

        return array(
            'auto_area'     => $auto_area,
            'location_text' => $location_text,
            'latitude'      => $lat,
            'longitude'     => $lng,
            'map_visible'   => isset($source[$map['map_visible']]) && (string) $source[$map['map_visible']] === '0' ? '0' : '1',
        );
    }
}

if (!function_exists('format_location_display')) {
    /**
     * @param array<string, mixed> $fields
     */
    function format_location_display(array $fields)
    {
        $area = eottae_location_area_label($fields['auto_area'] ?? '');
        $location = trim(strip_tags((string) ($fields['location_text'] ?? '')));

        return $location !== '' ? $area.' · '.$location : $area;
    }
}

if (!function_exists('eottae_location_picker_load_assets')) {
    function eottae_location_picker_load_assets($with_js = true)
    {
        $css = G5_PATH.'/css/eottae-location-picker.css';
        if (is_file($css)) {
            add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-location-picker.css?ver='.(int) filemtime($css).'">', 34);
        }
        if ($with_js) {
            if (function_exists('eottae_enqueue_google_maps')) {
                eottae_enqueue_google_maps();
            }
            $js = G5_PATH.'/js/eottae-location-picker.js';
            if (is_file($js)) {
                add_javascript('<script src="'.G5_JS_URL.'/eottae-location-picker.js?ver='.(int) filemtime($js).'" defer></script>', 24);
            }
        }
    }
}
