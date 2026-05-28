<?php
/**
 * 부동산 템플릿 — wr_1 지역, wr_2 거래상태, wr_3 JSON(템플릿 전체)
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_estate_template_option_labels')) {
    function eottae_estate_template_option_labels()
    {
        return array(
            'property_type' => array(
                'condo'      => '콘도',
                'house'      => '하우스',
                'villa'      => '빌라',
                'office'     => '오피스',
                'commercial' => '상가',
                'land'       => '토지',
                'other'      => '기타',
            ),
            'deal_type' => array(
                'month' => '월세',
                'sale'  => '매매',
                'short' => '단기임대',
                'long'  => '장기임대',
            ),
            'furnishing' => array(
                'full'        => '풀퍼니처',
                'semi'        => '세미퍼니처',
                'unfurnished' => '비가구',
                'nego'        => '협의',
            ),
        );
    }
}

if (!function_exists('eottae_estate_template_label')) {
    function eottae_estate_template_label($group, $key)
    {
        $maps = eottae_estate_template_option_labels();
        $key = (string) $key;
        if ($key === '' || !isset($maps[$group][$key])) {
            return '';
        }

        return $maps[$group][$key];
    }
}

if (!function_exists('eottae_estate_template_normalize_data')) {
    function eottae_estate_template_normalize_data($data)
    {
        if (!is_array($data)) {
            $data = array();
        }

        $keys = array(
            'property_type', 'deal_type', 'region', 'building_name', 'price', 'estate_deal_status',
            'rooms', 'bathrooms', 'furnishing', 'move_in',
            'description', 'nearby', 'contact', 'kakao_id', 'extra',
            'address', 'lat', 'lng',
        );

        $out = array();
        foreach ($keys as $key) {
            $out[$key] = trim(strip_tags((string) ($data[$key] ?? '')));
        }

        if (!function_exists('eottae_estate_normalize_deal_status')) {
            include_once G5_LIB_PATH.'/eottae-estate.lib.php';
        }
        $out['estate_deal_status'] = eottae_estate_normalize_deal_status($out['estate_deal_status'] ?: 'trading');

        return $out;
    }
}

if (!function_exists('eottae_estate_template_encode_json')) {
    function eottae_estate_template_encode_json(array $data)
    {
        $data = eottae_estate_template_normalize_data($data);
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);

        return ($json === false) ? '' : (function_exists('cut_str') ? cut_str($json, 65000, '') : $json);
    }
}

if (!function_exists('eottae_estate_template_decode_json')) {
    function eottae_estate_template_decode_json($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '' || $raw[0] !== '{') {
            return null;
        }

        $data = json_decode($raw, true);

        return is_array($data) ? eottae_estate_template_normalize_data($data) : null;
    }
}

if (!function_exists('eottae_estate_template_has_core_fields')) {
    function eottae_estate_template_has_core_fields(array $data)
    {
        return !empty($data['property_type']) || !empty($data['deal_type']) || !empty($data['description']) || !empty($data['price']);
    }
}

if (!function_exists('eottae_estate_template_has_display_fields')) {
    function eottae_estate_template_has_display_fields(array $data)
    {
        $data = eottae_estate_template_normalize_data($data);
        foreach ($data as $key => $value) {
            if ($key === 'estate_deal_status') {
                continue;
            }
            if (trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('eottae_estate_template_decode_json_loose')) {
    function eottae_estate_template_decode_json_loose($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        $candidates = array($raw);
        $decoded = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded !== $raw) {
            $candidates[] = trim($decoded);
        }

        if (preg_match('/\{[\s\S]*\}/u', $raw, $m)) {
            $candidates[] = trim($m[0]);
        }

        $b64 = base64_decode($raw, true);
        if ($b64 !== false && strpos($b64, '{') !== false) {
            $candidates[] = trim($b64);
        }

        foreach (array_unique($candidates) as $candidate) {
            $data = eottae_estate_template_decode_json($candidate);
            if ($data !== null) {
                return $data;
            }
        }

        return null;
    }
}

if (!function_exists('eottae_estate_template_merge_row_meta')) {
    function eottae_estate_template_merge_row_meta(array $data, $row)
    {
        if (!is_array($row)) {
            return $data;
        }

        if ($data['region'] === '' && !empty($row['wr_1'])) {
            $data['region'] = trim(strip_tags((string) $row['wr_1']));
        }
        if (!empty($row['wr_2'])) {
            if (!function_exists('eottae_estate_normalize_deal_status')) {
                include_once G5_LIB_PATH.'/eottae-estate.lib.php';
            }
            $data['estate_deal_status'] = eottae_estate_normalize_deal_status($row['wr_2']);
        }
        if ($data['address'] === '' && !empty($row['wr_4'])) {
            $data['address'] = trim(strip_tags((string) $row['wr_4']));
        }
        if ($data['lat'] === '' && !empty($row['wr_5'])) {
            $data['lat'] = trim((string) $row['wr_5']);
        }
        if ($data['lng'] === '' && !empty($row['wr_6'])) {
            $data['lng'] = trim((string) $row['wr_6']);
        }

        return $data;
    }
}

if (!function_exists('eottae_estate_template_icrm_label_map')) {
    function eottae_estate_template_icrm_label_map()
    {
        return array(
            '매물종류'       => 'property_type',
            '거래유형'       => 'deal_type',
            '지역'           => 'region',
            '매물명/건물명'  => 'building_name',
            '건물명'         => 'building_name',
            '매물명'         => 'building_name',
            '가격'           => 'price',
            '방 개수'        => 'rooms',
            '화장실'         => 'bathrooms',
            '화장실 개수'    => 'bathrooms',
            '가구'           => 'furnishing',
            '가구 여부'      => 'furnishing',
            '입주 가능일'    => 'move_in',
            '매물 설명'      => 'description',
            '설명'           => 'description',
            '주변 정보'      => 'nearby',
            '연락처'         => 'contact',
            '카카오톡 ID'    => 'kakao_id',
            '카카오 ID'      => 'kakao_id',
            '기타 안내'      => 'extra',
            '기타 안내사항'  => 'extra',
        );
    }
}

if (!function_exists('eottae_estate_template_assign_field_value')) {
    function eottae_estate_template_assign_field_value(array &$data, $field, $value)
    {
        $value = trim(strip_tags((string) $value));
        if ($value === '') {
            return;
        }

        if (in_array($field, array('property_type', 'deal_type', 'furnishing'), true)) {
            foreach (eottae_estate_template_option_labels()[$field] ?? array() as $code => $name) {
                if ($value === $name || $value === $code) {
                    $data[$field] = $code;

                    return;
                }
            }
        }

        $data[$field] = $value;
    }
}

if (!function_exists('eottae_estate_template_from_property_fields_html')) {
    function eottae_estate_template_from_property_fields_html($html)
    {
        $html = (string) $html;
        if ($html === '' || stripos($html, 'data-property-field') === false) {
            return null;
        }

        $data = eottae_estate_template_normalize_data(array());

        if (preg_match_all('/data-property-field=["\']([a-z_]+)["\'][^>]*\bvalue=["\']([^"\']*)["\']/i', $html, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                eottae_estate_template_assign_field_value($data, $match[1], html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
        }

        if (preg_match_all('/data-property-field=["\']([a-z_]+)["\'][^>]*>([^<]+)</i', $html, $m2, PREG_SET_ORDER)) {
            foreach ($m2 as $match) {
                eottae_estate_template_assign_field_value($data, $match[1], html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
        }

        return eottae_estate_template_has_display_fields($data) ? $data : null;
    }
}

if (!function_exists('eottae_estate_template_parse_icrm_blocks')) {
    function eottae_estate_template_parse_icrm_blocks($html, array &$data)
    {
        $html = (string) $html;
        $label_map = eottae_estate_template_icrm_label_map();
        $blocks = array();

        if (preg_match_all('/<div[^>]*\bicrm-(?:section|facility-card)\b[^>]*>(.*?)<\/div>/is', $html, $sections)) {
            $blocks = $sections[1];
        }

        foreach ($blocks as $block) {
            if (!preg_match('/<strong[^>]*>\s*(.*?)\s*<\/strong>/is', $block, $label_match)) {
                continue;
            }

            $label = trim(strip_tags($label_match[1]));
            if ($label === '' || !isset($label_map[$label])) {
                continue;
            }

            $value_html = preg_replace('/<strong[^>]*>.*?<\/strong>/is', '', $block, 1);
            $value = trim(strip_tags((string) $value_html));
            eottae_estate_template_assign_field_value($data, $label_map[$label], $value);
        }

        if (preg_match_all('/<dt[^>]*>\s*(.*?)\s*<\/dt>\s*<dd[^>]*>\s*(.*?)\s*<\/dd>/is', $html, $dl, PREG_SET_ORDER)) {
            foreach ($dl as $row) {
                $label = trim(strip_tags($row[1]));
                if ($label === '' || !isset($label_map[$label])) {
                    continue;
                }
                eottae_estate_template_assign_field_value($data, $label_map[$label], $row[2]);
            }
        }
    }
}

if (!function_exists('eottae_estate_template_from_icrm_html')) {
    function eottae_estate_template_from_icrm_html($html)
    {
        $html = (string) $html;
        if ($html === '' || strpos($html, '<') === false) {
            return null;
        }

        if (!function_exists('eottae_icrm_extract_embedded_json')) {
            include_once G5_LIB_PATH.'/eottae-icrm-template.lib.php';
        }

        $embedded = function_exists('eottae_icrm_extract_embedded_json')
            ? eottae_icrm_extract_embedded_json($html)
            : null;
        if ($embedded !== null) {
            $data = eottae_estate_template_decode_json_loose($embedded);
            if ($data !== null && eottae_estate_template_has_display_fields($data)) {
                return $data;
            }
        }

        $from_fields = eottae_estate_template_from_property_fields_html($html);
        if ($from_fields !== null) {
            return $from_fields;
        }

        $is_icrm = function_exists('eottae_icrm_content_should_preserve_html')
            && eottae_icrm_content_should_preserve_html($html);
        if (!$is_icrm) {
            return null;
        }

        $data = eottae_estate_template_normalize_data(array());
        eottae_estate_template_parse_icrm_blocks($html, $data);

        return eottae_estate_template_has_display_fields($data) ? $data : null;
    }
}

if (!function_exists('eottae_estate_template_from_row')) {
    function eottae_estate_template_from_row($row)
    {
        if (!is_array($row)) {
            return null;
        }

        $data = eottae_estate_template_decode_json_loose($row['wr_3'] ?? '');
        if ($data !== null && eottae_estate_template_has_display_fields($data)) {
            return eottae_estate_template_merge_row_meta($data, $row);
        }

        $parsed = eottae_estate_template_parse_content($row['wr_content'] ?? '');
        if ($parsed !== null) {
            return eottae_estate_template_merge_row_meta($parsed, $row);
        }

        $from_fields = eottae_estate_template_from_property_fields_html($row['wr_content'] ?? '');
        if ($from_fields !== null) {
            return eottae_estate_template_merge_row_meta($from_fields, $row);
        }

        $from_icrm = eottae_estate_template_from_icrm_html($row['wr_content'] ?? '');
        if ($from_icrm !== null) {
            return eottae_estate_template_merge_row_meta($from_icrm, $row);
        }

        return null;
    }
}

if (!function_exists('eottae_estate_template_parse_content')) {
    function eottae_estate_template_parse_content($content)
    {
        $text = trim(strip_tags((string) $content));
        if ($text === '' || strpos($text, '[부동산 매물정보]') === false) {
            return null;
        }

        $data = eottae_estate_template_normalize_data(array());

        if (preg_match('/\[부동산 매물정보\](.*?)(?=\[(?:매물 상세정보|매물 설명|주변 정보|연락정보|기타)|\z)/su', $text, $m)) {
            eottae_estate_template_parse_kv_block($data, $m[1], array(
                'property_type' => '매물종류',
                'deal_type'     => '거래유형',
                'region'        => '지역',
                'building_name' => '매물명/건물명',
                'price'         => '가격',
            ));
        }

        if (preg_match('/\[매물 상세정보\](.*?)(?=\[(?:매물 설명|주변 정보|연락정보|기타)|\z)/su', $text, $m)) {
            eottae_estate_template_parse_kv_block($data, $m[1], array(
                'rooms'      => '방 개수',
                'bathrooms'  => '화장실 개수',
                'furnishing' => '가구 여부',
                'move_in'    => '입주 가능일',
            ));
        }

        if (preg_match('/\[매물 설명\]\s*(.*?)(?=\[(?:주변 정보|연락정보|기타)|\z)/su', $text, $m)) {
            $data['description'] = trim($m[1]);
        }
        if (preg_match('/\[주변 정보\]\s*(.*?)(?=\[(?:연락정보|기타)|\z)/su', $text, $m)) {
            $data['nearby'] = trim($m[1]);
        }
        if (preg_match('/\[연락정보\](.*?)(?=\[기타|\z)/su', $text, $m)) {
            eottae_estate_template_parse_kv_block($data, $m[1], array(
                'contact'  => '연락처',
                'kakao_id' => '카카오톡 ID',
            ));
        }
        if (preg_match('/\[기타 안내사항\]\s*(.*)/su', $text, $m)) {
            $data['extra'] = trim($m[1]);
        }

        return eottae_estate_template_has_core_fields($data) ? $data : null;
    }
}

if (!function_exists('eottae_estate_template_parse_kv_block')) {
    function eottae_estate_template_parse_kv_block(array &$data, $block, array $map)
    {
        foreach ($map as $field => $label) {
            $pattern = '/'.preg_quote($label, '/').'\s*:\s*(.+?)(?=\n|$)/u';
            if (preg_match($pattern, $block, $m)) {
                $val = trim($m[1]);
                if ($field === 'property_type' || $field === 'deal_type' || $field === 'furnishing') {
                    foreach (eottae_estate_template_option_labels()[$field] ?? array() as $code => $name) {
                        if ($val === $name) {
                            $data[$field] = $code;
                            continue 2;
                        }
                    }
                }
                $data[$field] = $val;
            }
        }
    }
}

if (!function_exists('eottae_estate_template_build_title')) {
    function eottae_estate_template_build_title(array $data)
    {
        $data = eottae_estate_template_normalize_data($data);
        $region = $data['region'] !== '' ? $data['region'] : '세부';
        $deal = eottae_estate_template_label('deal_type', $data['deal_type']);
        $type = eottae_estate_template_label('property_type', $data['property_type']);
        $middle = trim($deal.' '.$type);
        if ($data['building_name'] !== '') {
            $middle = $data['building_name'].' '.$middle;
        }
        $price = $data['price'] !== '' ? $data['price'] : '협의';

        return '['.$region.'] '.$middle.' / '.$price;
    }
}

if (!function_exists('eottae_estate_template_build_body')) {
    function eottae_estate_template_build_body(array $data)
    {
        $data = eottae_estate_template_normalize_data($data);
        $lines = array();

        $info = array_filter(array(
            '매물종류: '.eottae_estate_template_label('property_type', $data['property_type']),
            '거래유형: '.eottae_estate_template_label('deal_type', $data['deal_type']),
            '지역: '.$data['region'],
            '매물명/건물명: '.$data['building_name'],
            '가격: '.$data['price'],
        ), function ($row) {
            return preg_match('/:\s*.+/u', $row);
        });

        if ($info) {
            $lines[] = '[부동산 매물정보]';
            $lines[] = '';
            $lines = array_merge($lines, array_values($info));
            $lines[] = '';
        }

        $detail = array_filter(array(
            '방 개수: '.$data['rooms'],
            '화장실 개수: '.$data['bathrooms'],
            '가구 여부: '.eottae_estate_template_label('furnishing', $data['furnishing']),
            '입주 가능일: '.$data['move_in'],
        ), function ($row) {
            return preg_match('/:\s*.+/u', $row);
        });
        if ($detail) {
            $lines[] = '[매물 상세정보]';
            $lines[] = '';
            $lines = array_merge($lines, array_values($detail));
            $lines[] = '';
        }

        if ($data['description'] !== '') {
            $lines[] = '[매물 설명]';
            $lines[] = $data['description'];
            $lines[] = '';
        }
        if ($data['nearby'] !== '') {
            $lines[] = '[주변 정보]';
            $lines[] = $data['nearby'];
            $lines[] = '';
        }

        if ($data['address'] !== '' || ($data['lat'] !== '' && $data['lng'] !== '')) {
            $lines[] = '[위치]';
            if ($data['address'] !== '') {
                $lines[] = '주소: '.$data['address'];
            }
            if ($data['lat'] !== '' && $data['lng'] !== '') {
                $lines[] = '좌표: '.$data['lat'].', '.$data['lng'];
            }
            $lines[] = '';
        }

        $contact = array_filter(array(
            '연락처: '.$data['contact'],
            '카카오톡 ID: '.$data['kakao_id'],
        ), function ($row) {
            return preg_match('/:\s*.+/u', $row);
        });
        if ($contact) {
            $lines[] = '[연락정보]';
            $lines[] = '';
            $lines = array_merge($lines, array_values($contact));
            $lines[] = '';
        }

        if ($data['extra'] !== '') {
            $lines[] = '[기타 안내사항]';
            $lines[] = $data['extra'];
            $lines[] = '';
        }

        return trim(implode("\n", $lines));
    }
}

if (!function_exists('eottae_estate_template_sync_wr3_from_row')) {
    /**
     * wr_3 비어 있을 때 본문(iCRM HTML·템플릿 블록)에서 JSON 백필
     */
    function eottae_estate_template_sync_wr3_from_row($bo_table, $wr_id)
    {
        $bo_table = (string) $bo_table;
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return;
        }
        if (!function_exists('eottae_is_estate_board') || !eottae_is_estate_board($bo_table)) {
            return;
        }

        $write_table = get_write_table_name($bo_table);
        $write = get_write($write_table, $wr_id, true);
        if (empty($write['wr_id'])) {
            return;
        }

        $existing = eottae_estate_template_decode_json_loose($write['wr_3'] ?? '');
        if ($existing !== null && eottae_estate_template_has_display_fields($existing)) {
            return;
        }

        $data = eottae_estate_template_from_row($write);
        if ($data === null || !eottae_estate_template_has_display_fields($data)) {
            return;
        }

        $json = eottae_estate_template_encode_json($data);
        if ($json === '') {
            return;
        }

        sql_query(" UPDATE `{$write_table}` SET wr_3 = '".sql_real_escape_string($json)."' WHERE wr_id = '{$wr_id}' ");
        if (function_exists('get_write')) {
            get_write($write_table, $wr_id, false);
        }
    }
}

if (!function_exists('eottae_estate_template_apply_to_post')) {
    function eottae_estate_template_apply_to_post()
    {
        $raw = isset($_POST['estate_template_json']) ? (string) $_POST['estate_template_json'] : '';
        if ($raw === '' && !empty($_POST['wr_3']) && is_string($_POST['wr_3']) && $_POST['wr_3'][0] === '{') {
            $raw = (string) $_POST['wr_3'];
        }

        $data = eottae_estate_template_decode_json($raw);
        if ($data === null || !eottae_estate_template_has_core_fields($data)) {
            return;
        }

        $_POST['wr_3'] = eottae_estate_template_encode_json($data);
        $_POST['wr_1'] = $data['region'];
        $_POST['wr_2'] = $data['estate_deal_status'];
        $_POST['wr_4'] = $data['address'];
        $_POST['wr_5'] = $data['lat'];
        $_POST['wr_6'] = $data['lng'];

        $body = eottae_estate_template_build_body($data);
        $content_plain = trim(strip_tags((string) ($_POST['wr_content'] ?? '')));

        if ($body !== '' && ($content_plain === '' || strpos($content_plain, '[부동산 매물정보]') === false)) {
            $_POST['wr_content'] = $body;
        }

        $subject = trim(strip_tags((string) ($_POST['wr_subject'] ?? '')));
        if ($subject === '' || ($data['region'] !== '' && strpos($subject, $data['region']) === false)) {
            $_POST['wr_subject'] = eottae_estate_template_build_title($data);
        }
    }
}

if (!function_exists('eottae_estate_template_view_rows')) {
    function eottae_estate_template_view_rows(array $data)
    {
        $data = eottae_estate_template_normalize_data($data);
        $sections = array();

        $basic = array();
        eottae_estate_template_push_row($basic, '매물종류', eottae_estate_template_label('property_type', $data['property_type']));
        eottae_estate_template_push_row($basic, '거래유형', eottae_estate_template_label('deal_type', $data['deal_type']));
        eottae_estate_template_push_row($basic, '지역', $data['region']);
        eottae_estate_template_push_row($basic, '매물명/건물명', $data['building_name']);
        eottae_estate_template_push_row($basic, '가격', $data['price']);
        if ($basic) {
            $sections[] = array('section' => '매물정보', 'rows' => $basic);
        }

        $location = array();
        eottae_estate_template_push_row($location, '주소', $data['address']);
        if ($data['lat'] !== '' && $data['lng'] !== '') {
            eottae_estate_template_push_row($location, '좌표', $data['lat'].', '.$data['lng']);
        }
        if ($location) {
            $sections[] = array('section' => '위치', 'rows' => $location);
        }

        $detail = array();
        eottae_estate_template_push_row($detail, '방 개수', $data['rooms']);
        eottae_estate_template_push_row($detail, '화장실', $data['bathrooms']);
        eottae_estate_template_push_row($detail, '가구', eottae_estate_template_label('furnishing', $data['furnishing']));
        eottae_estate_template_push_row($detail, '입주 가능일', $data['move_in']);
        if ($detail) {
            $sections[] = array('section' => '상세정보', 'rows' => $detail);
        }

        if ($data['description'] !== '') {
            $sections[] = array('section' => '매물 설명', 'rows' => array(array('label' => '', 'value' => $data['description'], 'multiline' => true)));
        }
        if ($data['nearby'] !== '') {
            $sections[] = array('section' => '주변 정보', 'rows' => array(array('label' => '', 'value' => $data['nearby'], 'multiline' => true)));
        }

        $contact = array();
        eottae_estate_template_push_row($contact, '연락처', $data['contact']);
        eottae_estate_template_push_row($contact, '카카오톡 ID', $data['kakao_id']);
        if ($contact) {
            $sections[] = array('section' => '연락정보', 'rows' => $contact);
        }

        if ($data['extra'] !== '') {
            $sections[] = array('section' => '기타 안내', 'rows' => array(array('label' => '', 'value' => $data['extra'], 'multiline' => true)));
        }

        return $sections;
    }
}

if (!function_exists('eottae_estate_template_push_row')) {
    function eottae_estate_template_push_row(array &$rows, $label, $value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return;
        }
        $rows[] = array('label' => $label, 'value' => $value, 'multiline' => false);
    }
}
