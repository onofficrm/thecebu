<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-talkroom-public-chat.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-publish.lib.php';
if (is_file(G5_LIB_PATH.'/eottae-calendar.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
}
if (is_file(G5_LIB_PATH.'/eottae-public-ai-weather.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-public-ai-weather.lib.php';
}

if (!function_exists('eottae_public_chat_life_qa_clean')) {
    function eottae_public_chat_life_qa_clean($value, $len = 300)
    {
        $value = trim(strip_tags((string) $value));
        if ($value !== '' && function_exists('cut_str')) {
            $value = cut_str($value, max(1, (int) $len), '');
        }

        return $value;
    }
}

if (!function_exists('eottae_public_chat_life_qa_fallback')) {
    function eottae_public_chat_life_qa_fallback($question)
    {
        return "질문 고마워요. 이 내용은 세부 생활 정보라 상황에 따라 달라질 수 있어요.\n\n"
            ."비자·병원·날씨·환율처럼 변동되는 정보는 공식 기관, 병원, 은행/환전소 기준을 함께 확인해 주세요.\n"
            ."질문: ".$question;
    }
}

if (!function_exists('eottae_public_chat_life_qa_keywords')) {
    function eottae_public_chat_life_qa_keywords($question)
    {
        $text = function_exists('mb_strtolower')
            ? mb_strtolower((string) $question, 'UTF-8')
            : strtolower((string) $question);
        $text = preg_replace('/https?:\/\/\S+/i', ' ', $text);
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $stop = array('언제', '어디', '뭐야', '뭔가요', '알려줘', '알려주세요', '궁금해', '있나요', '있어', '이야', '인가요', '계약은');
        $keywords = array();

        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part === '') {
                continue;
            }
            $part = preg_replace('/(에서|으로|부터|까지|에게|한테|은|는|이|가|을|를|과|와|의|에|로|도|만|야|요)$/u', '', $part);
            if ($part === '' || in_array($part, $stop, true)) {
                continue;
            }
            $len = function_exists('mb_strlen') ? mb_strlen($part, 'UTF-8') : strlen($part);
            if ($len < 2) {
                continue;
            }
            $keywords[$part] = true;
        }

        return array_slice(array_keys($keywords), 0, 8);
    }
}

if (!function_exists('eottae_public_chat_life_qa_like_sql')) {
    function eottae_public_chat_life_qa_like_sql(array $columns, array $keywords)
    {
        $chunks = array();
        foreach ($keywords as $keyword) {
            $keyword = trim((string) $keyword);
            if ($keyword === '') {
                continue;
            }
            $safe = sql_escape_string($keyword);
            $ors = array();
            foreach ($columns as $column) {
                $column = preg_replace('/[^a-z0-9_`.]/i', '', (string) $column);
                if ($column !== '') {
                    $ors[] = "{$column} LIKE '%{$safe}%'";
                }
            }
            if (!empty($ors)) {
                $chunks[] = '('.implode(' OR ', $ors).')';
            }
        }

        return !empty($chunks) ? implode(' AND ', $chunks) : '';
    }
}

if (!function_exists('eottae_public_chat_life_qa_source_line')) {
    function eottae_public_chat_life_qa_source_line(array $item)
    {
        $line = '['.($item['type'] ?? '자료').'] '.($item['title'] ?? '');
        if (!empty($item['date'])) {
            $line .= ' / 날짜: '.$item['date'];
        }
        if (!empty($item['time'])) {
            $line .= ' / 시간: '.$item['time'];
        }
        if (!empty($item['summary'])) {
            $line .= ' / 내용: '.$item['summary'];
        }
        if (!empty($item['url'])) {
            $line .= ' / 링크: '.$item['url'];
        }

        return $line;
    }
}

if (!function_exists('eottae_public_chat_life_qa_search_calendar')) {
    function eottae_public_chat_life_qa_search_calendar(array $keywords, $limit = 4)
    {
        if (empty($keywords) || !function_exists('eottae_calendar_table_exists') || !eottae_calendar_table_exists()) {
            return array();
        }

        $table = eottae_calendar_table_name();
        $where = eottae_public_chat_life_qa_like_sql(array('title', 'description', 'location', 'area', 'category'), $keywords);
        if ($where === '') {
            return array();
        }
        $today = date('Y-m-d');
        $limit = max(1, min(8, (int) $limit));
        $result = sql_query("
            SELECT *
            FROM `{$table}`
            WHERE ".eottae_calendar_visible_sql()."
              AND {$where}
            ORDER BY
              CASE WHEN end_date >= '".sql_escape_string($today)."' THEN 0 ELSE 1 END,
              start_date ASC,
              start_time ASC,
              event_id DESC
            LIMIT {$limit}
        ", false);

        $items = array();
        while ($row = sql_fetch_array($result)) {
            $event = eottae_calendar_present_event($row);
            $items[] = array(
                'type' => '세부어때 일정표',
                'title' => get_text($event['title'] ?? ''),
                'date' => get_text($event['date_label'] ?? ''),
                'time' => get_text($event['time_label'] ?? ''),
                'summary' => eottae_public_chat_life_qa_clean($event['description'] ?? '', 160),
                'url' => get_text($event['detail_href'] ?? ''),
            );
        }

        return $items;
    }
}

if (!function_exists('eottae_public_chat_life_qa_search_board')) {
    function eottae_public_chat_life_qa_search_board($bo_table, $label, array $keywords, $limit = 3)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        if ($bo_table === '' || empty($g5['write_prefix']) || empty($g5['board_table'])) {
            return array();
        }
        $board = sql_fetch(" SELECT bo_table FROM `{$g5['board_table']}` WHERE bo_table = '".sql_escape_string($bo_table)."' LIMIT 1 ", false);
        if (empty($board['bo_table'])) {
            return array();
        }

        $where = eottae_public_chat_life_qa_like_sql(array('wr_subject', 'wr_content', 'ca_name', 'wr_1', 'wr_2', 'wr_3'), $keywords);
        if ($where === '') {
            return array();
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $limit = max(1, min(6, (int) $limit));
        $result = sql_query("
            SELECT wr_id, wr_subject, wr_content, ca_name, wr_datetime, wr_hit, wr_1, wr_2, wr_3, wr_4, wr_6
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND {$where}
            ORDER BY wr_datetime DESC, wr_id DESC
            LIMIT {$limit}
        ", false);

        $items = array();
        while ($row = sql_fetch_array($result)) {
            $summary = '';
            if (function_exists('eottae_is_shop_board') && eottae_is_shop_board($bo_table) && function_exists('eottae_shop_from_write')) {
                $shop = eottae_shop_from_write($row, $bo_table);
                $bits = array_filter(array(
                    !empty($shop['category']) ? '분류 '.$shop['category'] : '',
                    !empty($shop['region']) ? '지역 '.$shop['region'] : '',
                    !empty($shop['address']) ? '주소 '.$shop['address'] : '',
                    !empty($shop['phone']) ? '연락처 '.$shop['phone'] : '',
                    !empty($shop['hours']) ? '영업시간 '.$shop['hours'] : '',
                ));
                $summary = implode(', ', $bits);
            }
            if ($summary === '') {
                $summary = eottae_public_chat_life_qa_clean($row['wr_content'] ?? '', 160);
            }

            $items[] = array(
                'type' => $label,
                'title' => get_text($row['wr_subject'] ?? ''),
                'date' => substr((string) ($row['wr_datetime'] ?? ''), 0, 10),
                'summary' => $summary,
                'url' => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.(int) ($row['wr_id'] ?? 0),
            );
        }

        return $items;
    }
}

if (!function_exists('eottae_public_chat_life_qa_internal_sources')) {
    function eottae_public_chat_life_qa_internal_sources($question)
    {
        $keywords = eottae_public_chat_life_qa_keywords($question);
        if (empty($keywords)) {
            return array('keywords' => array(), 'items' => array());
        }

        $items = eottae_public_chat_life_qa_search_calendar($keywords, 4);

        $boards = array();
        if (function_exists('eottae_shop_board_tables')) {
            foreach (eottae_shop_board_tables() as $table) {
                $boards[$table] = '세부어때 업체정보';
            }
        }
        $boards[function_exists('eottae_community_board_table') ? eottae_community_board_table() : 'community'] = '세부어때 커뮤니티';
        $boards[function_exists('eottae_free_board_table') ? eottae_free_board_table() : 'free'] = '세부어때 자유게시판';
        $boards[defined('EOTTae_REVIEW_TABLE') ? EOTTae_REVIEW_TABLE : 'review'] = '세부어때 업체리뷰';
        $boards[defined('EOTTae_MARKET_TABLE') ? EOTTae_MARKET_TABLE : 'market'] = '세부어때 중고장터';
        $boards[defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job'] = '세부어때 구인구직';
        $boards[defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate'] = '세부어때 부동산';

        foreach ($boards as $bo_table => $label) {
            if (count($items) >= 10) {
                break;
            }
            $items = array_merge($items, eottae_public_chat_life_qa_search_board($bo_table, $label, $keywords, 2));
        }

        return array('keywords' => $keywords, 'items' => array_slice($items, 0, 10));
    }
}

if (!function_exists('eottae_public_chat_life_qa_external_sources')) {
    function eottae_public_chat_life_qa_external_sources($question)
    {
        $key = function_exists('g5site_cfg') ? trim((string) g5site_cfg('life_qa_serpapi_key', '')) : '';
        if ($key === '' || !function_exists('curl_init')) {
            return array();
        }

        $query = trim((string) $question);
        if (stripos($query, 'cebu') === false && stripos($query, '세부') === false) {
            $query .= ' Cebu Philippines';
        }
        $url = 'https://serpapi.com/search.json?'.http_build_query(array(
            'engine' => 'google',
            'q' => $query,
            'hl' => 'ko',
            'num' => 3,
            'api_key' => $key,
        ));

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 4,
        ));
        $raw = curl_exec($ch);
        curl_close($ch);
        $json = $raw ? json_decode($raw, true) : null;
        if (!is_array($json) || empty($json['organic_results']) || !is_array($json['organic_results'])) {
            return array();
        }

        $items = array();
        foreach (array_slice($json['organic_results'], 0, 3) as $row) {
            $items[] = array(
                'type' => '외부 웹검색',
                'title' => eottae_public_chat_life_qa_clean($row['title'] ?? '', 100),
                'summary' => eottae_public_chat_life_qa_clean($row['snippet'] ?? '', 180),
                'url' => trim((string) ($row['link'] ?? '')),
            );
        }

        return $items;
    }
}

if (!function_exists('eottae_public_chat_life_qa_answer')) {
    function eottae_public_chat_life_qa_answer($question)
    {
        $question = eottae_public_chat_life_qa_clean($question, 300);
        if ($question === '') {
            return '';
        }

        if (function_exists('eottae_public_ai_weather_answer_life_qa')) {
            $weather_answer = eottae_public_ai_weather_answer_life_qa($question);
            if ($weather_answer !== '') {
                return eottae_public_chat_life_qa_clean($weather_answer, 700);
            }
        }

        if (!function_exists('eottae_ai_openai_chat_completion')) {
            return eottae_public_chat_life_qa_fallback($question);
        }

        $cfg = eottae_ai_generate_bootstrap_config();
        if (empty($cfg['enabled']) || empty($cfg['api_key'])) {
            return eottae_public_chat_life_qa_fallback($question);
        }

        $source_pack = eottae_public_chat_life_qa_internal_sources($question);
        $sources = isset($source_pack['items']) ? $source_pack['items'] : array();
        $source_scope = '세부어때 내부 데이터';
        if (empty($sources)) {
            $sources = eottae_public_chat_life_qa_external_sources($question);
            $source_scope = !empty($sources) ? '외부 웹검색' : '근거 없음';
        }

        $source_lines = array();
        foreach ($sources as $item) {
            $source_lines[] = '- '.eottae_public_chat_life_qa_source_line($item);
        }

        $prompt = "세부어때 공개톡에서 사용자가 질문했습니다.\n"
            ."답변 원칙:\n"
            ."- 반드시 제공된 근거 안에서 먼저 답하세요.\n"
            ."- 세부어때 내부 근거가 있으면 외부 정보보다 내부 근거를 우선하세요.\n"
            ."- 일정 질문은 날짜와 시간이 있으면 가장 먼저 알려주세요.\n"
            ."- 근거가 부족하면 추측하지 말고 '세부어때 데이터에서 확인되지 않습니다'라고 말하세요.\n"
            ."- 한국어로 650자 이내, 마지막에 근거 출처를 1~3개 짧게 표시하세요.\n"
            ."- 의료·법률·비자 승인 여부는 확정적으로 말하지 말고 공식 기관/전문가 확인을 권하세요.\n\n"
            ."근거 범위: ".$source_scope."\n"
            ."근거 자료:\n".(!empty($source_lines) ? implode("\n", $source_lines) : "- 검색된 근거 없음")."\n\n"
            ."질문: ".$question;

        $completion = eottae_ai_openai_chat_completion(array(
            'model' => $cfg['model'],
            'messages' => array(
                array('role' => 'system', 'content' => 'You are a retrieval-grounded Cebu life assistant. Use provided sources first. Do not invent dates, prices, contracts, locations, or phone numbers.'),
                array('role' => 'user', 'content' => $prompt),
            ),
            'temperature' => 0.25,
            'max_tokens' => 850,
        ), array('timeout' => 40, 'connect_timeout' => 10));

        if (empty($completion['ok']) || trim((string) ($completion['content'] ?? '')) === '') {
            return eottae_public_chat_life_qa_fallback($question);
        }

        return eottae_public_chat_life_qa_clean($completion['content'], 700);
    }
}

if (!function_exists('eottae_public_chat_life_qa_send')) {
    function eottae_public_chat_life_qa_send($room_id, $mb_id, $question)
    {
        $question = eottae_public_chat_life_qa_clean($question, 300);
        if ($question === '') {
            return array('ok' => false, 'message' => '질문을 입력해 주세요.');
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인이 필요합니다.');
        }

        if (!function_exists('eottae_talkroom_public_group_send_message')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-public-chat.lib.php';
        }

        $question_send = eottae_talkroom_public_group_send_message((int) $room_id, $mb_id, $question);
        if (empty($question_send['ok'])) {
            return $question_send;
        }

        $answer = eottae_public_chat_life_qa_answer($question);
        if ($answer === '') {
            return array('ok' => false, 'message' => '답변을 만들지 못했습니다.');
        }

        $content = "세부 생활 질문에 답변드릴게요.\n\n".$answer;

        $ai_send = eottae_talkroom_public_group_send_ai_message((int) $room_id, $content, array(
            'trigger_type' => 'life_qa',
        ));
        if (empty($ai_send['ok'])) {
            return $ai_send;
        }

        return array(
            'ok'            => true,
            'message'       => 'AI 답변이 등록되었습니다.',
            'wr_id'         => (int) ($ai_send['wr_id'] ?? 0),
            'message_row'   => $ai_send['message_row'] ?? null,
            'question_wr_id'=> (int) ($question_send['wr_id'] ?? 0),
            'question_row'  => $question_send['message_row'] ?? null,
        );
    }
}
