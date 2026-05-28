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

if (!function_exists('eottae_estate_template_from_row')) {
    function eottae_estate_template_from_row($row)
    {
        if (!is_array($row)) {
            return null;
        }

        $data = eottae_estate_template_decode_json($row['wr_3'] ?? '');
        if ($data !== null && eottae_estate_template_has_core_fields($data)) {
            if ($data['region'] === '' && !empty($row['wr_1'])) {
                $data['region'] = trim(strip_tags((string) $row['wr_1']));
            }
            if (!empty($row['wr_2'])) {
                $data['estate_deal_status'] = eottae_estate_normalize_deal_status($row['wr_2']);
            }

            return $data;
        }

        $parsed = eottae_estate_template_parse_content($row['wr_content'] ?? '');
        if ($parsed !== null) {
            if ($parsed['region'] === '' && !empty($row['wr_1'])) {
                $parsed['region'] = trim(strip_tags((string) $row['wr_1']));
            }

            return $parsed;
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
