<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-market.lib.php';

if (!function_exists('eottae_market_price_ai_clean')) {
    function eottae_market_price_ai_clean($value, $len = 800)
    {
        $value = trim(strip_tags((string) $value));
        if ($value !== '' && function_exists('cut_str')) {
            $value = cut_str($value, max(1, (int) $len), '');
        }

        return $value;
    }
}

if (!function_exists('eottae_market_price_ai_keywords')) {
    function eottae_market_price_ai_keywords($subject)
    {
        $subject = preg_replace('/[^\p{Hangul}a-zA-Z0-9\s]/u', ' ', (string) $subject);
        $parts = preg_split('/\s+/u', trim($subject));
        $stop = array('판매', '팝니다', '삽니다', '중고', '급처', '무료', '나눔', '합니다', '있어요');
        $keywords = array();

        foreach ((array) $parts as $part) {
            $part = trim((string) $part);
            if ($part === '' || mb_strlen($part, 'UTF-8') < 2 || in_array($part, $stop, true)) {
                continue;
            }
            $keywords[] = $part;
        }

        return array_slice(array_values(array_unique($keywords)), 0, 5);
    }
}

if (!function_exists('eottae_market_price_ai_find_comps')) {
    function eottae_market_price_ai_find_comps($subject, $region = '', $limit = 8)
    {
        global $g5;

        $bo_table = eottae_market_board_table();
        $write_prefix = isset($g5['write_prefix']) ? (string) $g5['write_prefix'] : (defined('G5_TABLE_PREFIX') ? G5_TABLE_PREFIX.'write_' : 'g5_write_');
        $write_table = $write_prefix.$bo_table;
        $limit = max(3, min(12, (int) $limit));
        $keywords = eottae_market_price_ai_keywords($subject);
        $where = array(
            "wr_is_comment = 0",
            "wr_1 REGEXP '^[0-9]+$'",
            "CAST(wr_1 AS UNSIGNED) > 0",
            "wr_10 <> '".sql_real_escape_string(eottae_market_free_flag())."'",
        );

        $region = eottae_market_normalize_region($region);
        if ($region !== '') {
            $where[] = "wr_3 = '".sql_real_escape_string($region)."'";
        }

        if (!empty($keywords)) {
            $likes = array();
            foreach ($keywords as $keyword) {
                $escaped = sql_real_escape_string($keyword);
                $likes[] = "wr_subject LIKE '%".$escaped."%'";
            }
            $where[] = '('.implode(' OR ', $likes).')';
        }

        $sql = " select wr_id, wr_subject, wr_1, wr_2, wr_3, wr_datetime
                   from {$write_table}
                  where ".implode(' and ', $where)."
                  order by wr_datetime desc
                  limit {$limit} ";
        $result = sql_query($sql, false);
        $rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $price = (int) preg_replace('/[^0-9]/', '', (string) ($row['wr_1'] ?? ''));
                if ($price < 1) {
                    continue;
                }
                $rows[] = array(
                    'title'  => get_text($row['wr_subject'] ?? ''),
                    'price'  => $price,
                    'status' => eottae_market_status_label($row['wr_2'] ?? 'selling'),
                    'region' => eottae_market_region_label($row['wr_3'] ?? ''),
                    'date'   => substr((string) ($row['wr_datetime'] ?? ''), 0, 10),
                );
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_market_price_ai_parse_input')) {
    function eottae_market_price_ai_parse_input(array $post)
    {
        return array(
            'subject' => eottae_market_price_ai_clean($post['subject'] ?? $post['wr_subject'] ?? '', 160),
            'region'  => eottae_market_price_ai_clean($post['region'] ?? $post['wr_3'] ?? '', 80),
            'content' => eottae_market_price_ai_clean($post['content'] ?? $post['wr_content'] ?? '', 1000),
        );
    }
}

if (!function_exists('eottae_market_price_ai_stats')) {
    function eottae_market_price_ai_stats(array $comps)
    {
        $prices = array();
        foreach ($comps as $row) {
            $prices[] = (int) ($row['price'] ?? 0);
        }
        sort($prices);
        $count = count($prices);
        if ($count < 1) {
            return array('count' => 0, 'min' => 0, 'max' => 0, 'avg' => 0, 'median' => 0);
        }

        return array(
            'count'  => $count,
            'min'    => $prices[0],
            'max'    => $prices[$count - 1],
            'avg'    => (int) round(array_sum($prices) / $count),
            'median' => $prices[(int) floor(($count - 1) / 2)],
        );
    }
}

if (!function_exists('eottae_market_price_ai_generate')) {
    function eottae_market_price_ai_generate(array $input)
    {
        $comps = eottae_market_price_ai_find_comps($input['subject'], $input['region']);
        $stats = eottae_market_price_ai_stats($comps);
        $fallback_price = $stats['median'] > 0 ? $stats['median'] : 0;
        $fallback = array(
            'price_min'       => $fallback_price > 0 ? (int) round($fallback_price * 0.85) : 0,
            'price_max'       => $fallback_price > 0 ? (int) round($fallback_price * 1.15) : 0,
            'suggested_price' => $fallback_price,
            'summary'         => $stats['count'] > 0
                ? '최근 유사 매물 '.$stats['count'].'건을 기준으로 참고 가격대를 계산했습니다.'
                : '유사 매물이 부족해 정확한 평균가 산정이 어렵습니다. 상품 상태와 구매가를 함께 고려해 주세요.',
            'disclaimer'      => '참고용 가격입니다. 실제 거래가는 상품 상태, 구성품, 협의 여부에 따라 달라질 수 있습니다.',
        );

        if (!function_exists('eottae_ai_openai_chat_completion')) {
            return array('data' => $fallback, 'comps' => $comps, 'stats' => $stats, 'source' => 'local');
        }

        $cfg = eottae_ai_generate_bootstrap_config();
        if (empty($cfg['enabled']) || empty($cfg['api_key'])) {
            return array('data' => $fallback, 'comps' => $comps, 'stats' => $stats, 'source' => 'local');
        }

        $prompt = "세부어때 중고장터 판매글의 참고 가격을 제안해 주세요.\n"
            ."외부 시세를 아는 척하지 말고, 제공된 최근 매물 비교와 상품 설명만 근거로 하세요.\n"
            ."반드시 JSON만 응답하세요. 키: price_min, price_max, suggested_price, summary, disclaimer\n"
            ."가격은 필리핀 페소 정수로만 작성합니다. 근거가 부족하면 보수적으로 말하세요.\n\n"
            ."판매 예정 상품:\n".json_encode($input, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n"
            ."비교 매물 통계:\n".json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n"
            ."비교 매물:\n".json_encode($comps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $completion = eottae_ai_openai_chat_completion(array(
            'model' => $cfg['model'],
            'messages' => array(
                array('role' => 'system', 'content' => 'You suggest practical used-item price references for a Cebu Korean marketplace. Return strict JSON only.'),
                array('role' => 'user', 'content' => $prompt),
            ),
            'temperature' => 0.35,
            'max_tokens' => 700,
            'response_format' => array('type' => 'json_object'),
        ), array('timeout' => 40, 'connect_timeout' => 10));

        $json = eottae_ai_openai_parse_json_content($completion);
        if (!is_array($json)) {
            return array('data' => $fallback, 'comps' => $comps, 'stats' => $stats, 'source' => 'local');
        }

        $data = array(
            'price_min'       => max(0, (int) preg_replace('/[^0-9]/', '', (string) ($json['price_min'] ?? $fallback['price_min']))),
            'price_max'       => max(0, (int) preg_replace('/[^0-9]/', '', (string) ($json['price_max'] ?? $fallback['price_max']))),
            'suggested_price' => max(0, (int) preg_replace('/[^0-9]/', '', (string) ($json['suggested_price'] ?? $fallback['suggested_price']))),
            'summary'         => eottae_market_price_ai_clean($json['summary'] ?? $fallback['summary'], 400),
            'disclaimer'      => eottae_market_price_ai_clean($json['disclaimer'] ?? $fallback['disclaimer'], 300),
        );

        return array('data' => $data, 'comps' => $comps, 'stats' => $stats, 'source' => 'api');
    }
}
