<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_apply_ai_allowed_modes')) {
    function eottae_talkroom_apply_ai_allowed_modes()
    {
        return array('all', 'room_name', 'room_description', 'room_detail', 'rules', 'apply_reason', 'emoji');
    }
}

if (!function_exists('eottae_talkroom_apply_ai_default_emoji')) {
    function eottae_talkroom_apply_ai_default_emoji($category = '')
    {
        $map = array(
            'expat_life' => '🏠',
            'parenting'  => '👶',
            'sports'     => '⚽',
            'travel'     => '✈️',
            'business'   => '💼',
            'used'       => '🛍️',
            'job'        => '💼',
            'estate'     => '🏢',
            'food'       => '🍜',
            'hobby'      => '🎨',
            'etc'        => '💬',
        );
        $category = preg_replace('/[^a-z0-9_]/', '', (string) $category);

        return isset($map[$category]) ? $map[$category] : '💬';
    }
}

if (!function_exists('eottae_talkroom_apply_ai_parse_input')) {
    function eottae_talkroom_apply_ai_parse_input(array $post)
    {
        $categories = function_exists('eottae_talkroom_category_options')
            ? eottae_talkroom_category_options()
            : array();

        $category = isset($post['category']) ? preg_replace('/[^a-z0-9_]/', '', (string) $post['category']) : '';
        if (!isset($categories[$category])) {
            $category = '';
        }

        $mode = isset($post['mode']) ? preg_replace('/[^a-z_]/', '', (string) $post['mode']) : 'all';
        if (!in_array($mode, eottae_talkroom_apply_ai_allowed_modes(), true)) {
            $mode = 'all';
        }

        return array(
            'mode'             => $mode,
            'category'         => $category,
            'category_label'   => $category !== '' ? eottae_talkroom_category_label($category) : '',
            'room_name'        => function_exists('eottae_talkroom_clean_text')
                ? eottae_talkroom_clean_text($post['room_name'] ?? '', 40)
                : cut_str(strip_tags((string) ($post['room_name'] ?? '')), 40, ''),
            'room_description' => function_exists('eottae_talkroom_clean_text')
                ? eottae_talkroom_clean_text($post['room_description'] ?? '', 500)
                : cut_str(strip_tags((string) ($post['room_description'] ?? '')), 500, ''),
            'room_detail'      => function_exists('eottae_talkroom_clean_text')
                ? eottae_talkroom_clean_text($post['room_detail'] ?? '', 5000)
                : cut_str(strip_tags((string) ($post['room_detail'] ?? '')), 5000, ''),
            'rules'            => function_exists('eottae_talkroom_clean_text')
                ? eottae_talkroom_clean_text($post['rules'] ?? '', 5000)
                : cut_str(strip_tags((string) ($post['rules'] ?? '')), 5000, ''),
            'apply_reason'     => function_exists('eottae_talkroom_clean_text')
                ? eottae_talkroom_clean_text($post['apply_reason'] ?? '', 2000)
                : cut_str(strip_tags((string) ($post['apply_reason'] ?? '')), 2000, ''),
            'topic_hint'       => function_exists('eottae_talkroom_clean_text')
                ? eottae_talkroom_clean_text($post['topic_hint'] ?? '', 200)
                : cut_str(strip_tags((string) ($post['topic_hint'] ?? '')), 200, ''),
            'emoji'            => function_exists('eottae_talkroom_sanitize_emoji')
                ? eottae_talkroom_sanitize_emoji($post['emoji'] ?? '')
                : trim((string) ($post['emoji'] ?? '💬')),
        );
    }
}

if (!function_exists('eottae_talkroom_apply_ai_topic_label')) {
    function eottae_talkroom_apply_ai_topic_label(array $input)
    {
        if ($input['room_name'] !== '') {
            return $input['room_name'];
        }
        if ($input['topic_hint'] !== '') {
            return $input['topic_hint'];
        }

        return '세부 모임';
    }
}

if (!function_exists('eottae_talkroom_apply_ai_generate_from_template')) {
    function eottae_talkroom_apply_ai_generate_from_template(array $input)
    {
        $topic = eottae_talkroom_apply_ai_topic_label($input);
        $cat_label = $input['category_label'] !== '' ? $input['category_label'] : '커뮤니티';
        $category = $input['category'] !== '' ? $input['category'] : 'etc';
        $emoji = eottae_talkroom_apply_ai_default_emoji($category);

        $room_name = $input['room_name'] !== '' ? $input['room_name'] : '세부 '.$cat_label.' · '.$topic;
        $room_description = $input['room_description'] !== ''
            ? $input['room_description']
            : '세부 '.$cat_label.' 관심 있는 분들과 정보·소통·모임을 나누는 톡방입니다.';
        $room_detail = $input['room_detail'] !== ''
            ? $input['room_detail']
            : "필리핀 세부에서 {$cat_label} 주제로 함께 이야기하는 공간입니다.\n"
            ."{$topic}에 관심 있는 교민·여행자·사업자분들이 가볍게 질문하고, 정보를 공유하며, 필요할 때 오프라인 모임도 제안할 수 있습니다.\n"
            ."서로 존중하며 세부 생활에 도움이 되는 대화를 나눠 주세요.";
        $rules = $input['rules'] !== ''
            ? $input['rules']
            : "1. 광고·홍보·스팸성 게시글은 금지합니다.\n"
            ."2. 욕설, 비방, 혐오 표현을 삼가 주세요.\n"
            ."3. 세부 지역 생활과 관련된 정보 중심으로 대화해 주세요.\n"
            ."4. 개인정보(연락처, 주소 등)는 공개 게시글에 올리지 않습니다.\n"
            ."5. 분쟁이 생기면 방장·운영자 판단을 따릅니다.";
        $apply_reason = $input['apply_reason'] !== ''
            ? $input['apply_reason']
            : "세부 {$cat_label} 관련 정보가 흩어져 있어, 같은 관심사를 가진 분들이 한곳에서 소통할 수 있는 톡방이 필요합니다.\n"
            ."{$topic} 주제로 꾸준히 운영하며, 유용한 정보와 모임 제안이 오가는 건강한 커뮤니티를 만들고자 신청합니다.";

        return array(
            'room_name'        => $room_name,
            'room_description' => $room_description,
            'room_detail'      => $room_detail,
            'category'         => $category,
            'emoji'            => $emoji,
            'rules'            => $rules,
            'apply_reason'     => $apply_reason,
        );
    }
}

if (!function_exists('eottae_talkroom_apply_ai_generate_via_api')) {
    function eottae_talkroom_apply_ai_generate_via_api(array $input)
    {
        if (!function_exists('g5site_cfg_bool') || !function_exists('g5site_cfg')) {
            return null;
        }

        $enabled = g5site_cfg_bool('ai_generate_enabled', false);
        $api_key = trim((string) g5site_cfg('ai_generate_api_key', ''));
        $model = trim((string) g5site_cfg('ai_generate_model', 'gpt-4o-mini'));
        if ($model === '') {
            $model = 'gpt-4o-mini';
        }

        if (!$enabled || $api_key === '' || !function_exists('curl_init')) {
            return null;
        }

        $categories = eottae_talkroom_category_options();
        $category_lines = array();
        foreach ($categories as $code => $label) {
            $category_lines[] = $code.': '.$label;
        }

        $context = array();
        if ($input['category_label'] !== '') {
            $context['선택 카테고리'] = $input['category_label'].' ('.$input['category'].')';
        }
        if ($input['room_name'] !== '') {
            $context['톡방 이름 힌트'] = $input['room_name'];
        }
        if ($input['topic_hint'] !== '') {
            $context['만들고 싶은 톡방 주제'] = $input['topic_hint'];
        }
        if ($input['room_description'] !== '') {
            $context['기존 한 줄 소개'] = $input['room_description'];
        }
        if ($input['room_detail'] !== '') {
            $context['기존 상세 설명'] = $input['room_detail'];
        }

        $lines = array();
        foreach ($context as $key => $value) {
            if ($value !== '') {
                $lines[] = $key.': '.$value;
            }
        }

        $mode = $input['mode'];
        $field_note = $mode === 'all'
            ? '모든 필드를 채워 주세요.'
            : '요청 mode에 해당하는 필드만 작성하고, 나머지는 빈 문자열로 두세요. mode='.$mode;

        $prompt = "필리핀 세부 지역 커뮤니티 '세부어때'의 톡방 개설 신청서 초안을 작성해 주세요.\n"
            ."자연스러운 한국어, 과장 없이, 모바일 폼에 바로 넣을 수 있게 작성합니다.\n"
            .$field_note."\n"
            ."반드시 JSON만 응답: room_name, room_description, room_detail, category, emoji, rules, apply_reason\n"
            ."- room_name: 40자 이내\n"
            ."- room_description: 500자 이내 한 줄 소개\n"
            ."- room_detail: 500~1200자 상세 설명, 줄바꿈 포함\n"
            ."- category: 아래 코드 중 하나\n"
            ."- emoji: 톡방 대표 이모지 1개\n"
            ."- rules: 운영 규칙 4~6줄\n"
            ."- apply_reason: 관리자 검토용 신청 사유 2~4문장\n\n"
            ."카테고리 코드:\n".implode("\n", $category_lines)."\n\n"
            ."입력 정보:\n".($lines ? implode("\n", $lines) : '(없음 — 주제를 추론해 작성)');

        $payload = array(
            'model' => $model,
            'messages' => array(
                array('role' => 'system', 'content' => 'You write Korean talk room application drafts for a Cebu community site. Return strict JSON only.'),
                array('role' => 'user', 'content' => $prompt),
            ),
            'temperature' => 0.75,
            'max_tokens' => 1200,
            'response_format' => array('type' => 'json_object'),
        );

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$api_key,
            ),
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 30,
        ));

        $raw = curl_exec($ch);
        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $raw === '' || $http_code < 200 || $http_code >= 300) {
            return null;
        }

        $decoded = json_decode($raw, true);
        $content = isset($decoded['choices'][0]['message']['content']) ? trim((string) $decoded['choices'][0]['message']['content']) : '';
        $generated = json_decode($content, true);
        if (!is_array($generated)) {
            return null;
        }

        $categories = eottae_talkroom_category_options();
        $category = isset($generated['category']) ? preg_replace('/[^a-z0-9_]/', '', (string) $generated['category']) : $input['category'];
        if (!isset($categories[$category])) {
            $category = $input['category'] !== '' ? $input['category'] : 'etc';
        }

        return array(
            'room_name'        => isset($generated['room_name']) ? eottae_talkroom_clean_text($generated['room_name'], 40) : '',
            'room_description' => isset($generated['room_description']) ? eottae_talkroom_clean_text($generated['room_description'], 500) : '',
            'room_detail'      => isset($generated['room_detail']) ? eottae_talkroom_clean_text($generated['room_detail'], 5000) : '',
            'category'         => $category,
            'emoji'            => eottae_talkroom_sanitize_emoji($generated['emoji'] ?? ''),
            'rules'            => isset($generated['rules']) ? eottae_talkroom_clean_text($generated['rules'], 5000) : '',
            'apply_reason'     => isset($generated['apply_reason']) ? eottae_talkroom_clean_text($generated['apply_reason'], 2000) : '',
        );
    }
}

if (!function_exists('eottae_talkroom_apply_ai_pick_mode_fields')) {
    function eottae_talkroom_apply_ai_pick_mode_fields(array $full, array $input)
    {
        $mode = $input['mode'];
        if ($mode === 'all') {
            return $full;
        }

        $result = array(
            'room_name'        => '',
            'room_description' => '',
            'room_detail'      => '',
            'category'         => '',
            'emoji'            => '',
            'rules'            => '',
            'apply_reason'     => '',
        );

        if ($mode === 'room_name') {
            $result['room_name'] = $full['room_name'];
            $result['room_description'] = $full['room_description'];
        } elseif ($mode === 'room_description') {
            $result['room_description'] = $full['room_description'];
        } elseif ($mode === 'room_detail') {
            $result['room_detail'] = $full['room_detail'];
        } elseif ($mode === 'rules') {
            $result['rules'] = $full['rules'];
        } elseif ($mode === 'apply_reason') {
            $result['apply_reason'] = $full['apply_reason'];
        } elseif ($mode === 'emoji') {
            $result['emoji'] = $full['emoji'];
            if ($input['category'] === '' && !empty($full['category'])) {
                $result['category'] = $full['category'];
            }
        }

        return $result;
    }
}

if (!function_exists('eottae_talkroom_apply_ai_generate')) {
    function eottae_talkroom_apply_ai_generate(array $input)
    {
        $api = eottae_talkroom_apply_ai_generate_via_api($input);
        $full = is_array($api) ? $api : eottae_talkroom_apply_ai_generate_from_template($input);

        if ($input['category'] === '' && !empty($full['category'])) {
            $input['category'] = $full['category'];
            $full['category'] = $full['category'];
        }

        if ($full['emoji'] === '' || $full['emoji'] === '💬') {
            $full['emoji'] = eottae_talkroom_apply_ai_default_emoji($full['category'] ?: $input['category']);
        }

        $picked = eottae_talkroom_apply_ai_pick_mode_fields($full, $input);
        $picked['source'] = is_array($api) ? 'api' : 'template';

        return $picked;
    }
}
