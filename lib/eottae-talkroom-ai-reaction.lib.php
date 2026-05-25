<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_reaction_room_gap_seconds')) {
    function eottae_talkroom_ai_reaction_room_gap_seconds()
    {
        return 900;
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_hourly_limit')) {
    function eottae_talkroom_ai_reaction_hourly_limit()
    {
        return 2;
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_max_post_comments')) {
    function eottae_talkroom_ai_reaction_max_post_comments()
    {
        return 3;
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_intro_member_days')) {
    function eottae_talkroom_ai_reaction_intro_member_days()
    {
        return 14;
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_type_labels')) {
    /**
     * @return array<string, string>
     */
    function eottae_talkroom_ai_reaction_type_labels()
    {
        return array(
            'question' => '질문 보완 유도',
            'meetup'   => '참여 댓글 유도',
            'sale'     => '중고거래 작성 팁',
            'info'     => '정보 공유 감사',
            'intro'    => '신규회원 환영',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_exclude_patterns')) {
    /**
     * @return array<string, string>
     */
    function eottae_talkroom_ai_reaction_exclude_patterns()
    {
        return array(
            'dispute'   => '/분쟁|사기(?:꾼|당|쳤|당함)?|고소|소송|환불(?:\s*거부)?|신고(?:합니다|할|당)|협박|빌려(?:줬|준|갔)|안\s*갚|욕(?:설|함|해)|비방|허위(?:\s*사실)?|명예훼손|사기(?:\s*당)?/ui',
            'politics'  => '/대통령|정치(?:인|적|논쟁)?|선거|탄핵|정당|여당|야당|국민의힘|민주당|보수|진보(?:\s*진영)?/ui',
            'religion'  => '/종교(?:\s*논쟁)?|교회|성당|예수|allah|알라|모슬람|불교|기도(?:\s*해)?|신(?:\s*을)?/ui',
            'profanity' => '/씨발|시발|ㅅㅂ|ㅂㅅ|병신|지랄|개새|fuck|shit|damn|asshole/ui',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_templates')) {
    /**
     * @return array<string, array<string, string>>
     */
    function eottae_talkroom_ai_reaction_templates()
    {
        return array(
            'question' => array(
                'default'  => '좋은 질문이에요. 어느 지역 근처를 찾으시는지 함께 적어주시면 다른 분들이 더 정확히 추천해주실 수 있어요.',
                'travel'   => '좋은 질문이에요 ✈️ 여행 일정이나 숙소 위치를 함께 적어주시면 더 맞는 답을 받기 쉬울 거예요.',
                'used'     => '궁금한 점이 있으시군요. 상품 상태나 희망 가격대를 함께 적어주시면 답변받기 더 수월해요.',
                'food'     => '좋은 질문이에요 ☕ 자주 가는 지역이나 분위기를 함께 남겨주시면 추천이 더 정확해질 거예요.',
                'parenting'=> '좋은 질문이에요. 아이 나이대나 거주 지역을 함께 적어주시면 경험담을 더 잘 받을 수 있어요.',
            ),
            'meetup' => array(
                'default'  => '좋은 제안이에요 👍 참여하실 분들은 가능한 시간과 지역을 같이 남기면 모임 잡기 쉬울 것 같아요.',
                'sports'   => '좋은 제안이에요 ⚽ 가능한 요일·시간·장소를 댓글로 남겨주시면 함께할 분들이 더 빨리 모일 거예요.',
                'hobby'    => '재미있는 모임 제안이네요 🎉 관심 있는 분들은 참여 가능한 시간을 댓글로 알려주세요.',
                'parenting'=> '좋은 제안이에요. 아이 동반 여부나 희망 시간대를 함께 적어주시면 참여하기 편할 거예요.',
                'food'     => '맛있는 모임 제안이네요 🍽 가고 싶은 지역이나 메뉴 취향을 댓글로 남겨주시면 좋아요.',
            ),
            'sale' => array(
                'default'  => '중고거래 글에는 가격, 거래 지역, 상품 상태, 연락 방법을 함께 적으면 거래가 더 빨리 진행돼요 😊',
                'used'     => '중고거래 글에는 가격, 거래 지역, 상품 상태, 연락 방법을 함께 적으면 거래가 더 빨리 진행돼요 😊',
                'estate'   => '매물 글에는 희망 가격, 위치, 면적·조건, 연락 방법을 함께 적어주시면 문의가 더 빨리 올 거예요.',
            ),
            'info' => array(
                'default'  => '유용한 정보 공유 감사해요 🙏 다른 분들도 참고하기 좋을 것 같아요.',
                'travel'   => '여행 정보 공유 감사해요 ✈️ 다른 분들께도 큰 도움이 될 거예요.',
                'food'     => '맛집·카페 정보 공유 감사해요 ☕ 다른 분들도 참고하기 좋을 것 같아요.',
                'parenting'=> '육아·생활 정보 공유 감사해요 😊 다른 분들께도 도움이 될 거예요.',
            ),
            'intro' => array(
                'default'  => '반갑습니다 😊 편하게 한 줄 소개 남겨주시면 톡방 분위기에도 도움이 돼요.',
                'expat_life' => '세부톡방에 오신 걸 환영해요 😊 거주 중이신지, 여행 준비 중이신지 편하게 알려주세요.',
                'parenting'  => '맘수다방에 오신 걸 환영해요. 아이 나이대나 궁금한 점을 편하게 남겨주세요.',
                'sports'     => '스포츠 톡방에 오신 걸 환영해요 ⚽ 관심 있는 운동이나 가능한 요일을 알려주시면 좋아요.',
                'travel'     => '여행자 질문방에 오신 걸 환영해요 ✈️ 궁금한 점이나 일정을 편하게 남겨주세요.',
                'used'       => '중고거래 톡방에 오신 걸 환영해요 😊 필요한 물건이나 나누고 싶은 소식을 편하게 남겨주세요.',
            ),
        );
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_category_key')) {
    function eottae_talkroom_ai_reaction_category_key(array $room)
    {
        if (!function_exists('eottae_talkroom_ai_daily_question_category_key')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-daily-question.lib.php';
        }

        return eottae_talkroom_ai_daily_question_category_key($room);
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_normalize_text')) {
    function eottae_talkroom_ai_reaction_normalize_text(array $post)
    {
        $subject = trim(strip_tags((string) ($post['wr_subject'] ?? '')));
        $content = trim(strip_tags((string) ($post['wr_content'] ?? '')));
        $text = trim($subject."\n".$content);
        $plain = preg_replace('/\s+/u', ' ', $text);

        return array(
            'subject' => $subject,
            'content' => $content,
            'text'    => $text,
            'plain'   => $plain,
        );
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_count_pii_signals')) {
    function eottae_talkroom_ai_reaction_count_pii_signals($text)
    {
        $text = (string) $text;
        $count = 0;

        if (preg_match_all('/[\w.-]+@[\w.-]+\.[A-Za-z]{2,}/u', $text, $m)) {
            $count += count($m[0]);
        }
        if (preg_match_all('/(\+?\d{1,3}[\s-]?)?(\d{2,4}[\s-]?){2,3}\d{2,4}/u', $text, $m)) {
            $count += count($m[0]);
        }
        if (preg_match_all('/(카카오|카톡|kakao|kakaotalk|텔레|telegram|whatsapp|라인)[^\s]*/ui', $text, $m)) {
            $count += count($m[0]);
        }
        if (preg_match_all('/@[a-z0-9._-]{3,}/ui', $text, $m)) {
            $count += count($m[0]);
        }

        return $count;
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_match_any')) {
    function eottae_talkroom_ai_reaction_match_any($text, array $patterns)
    {
        foreach ($patterns as $pattern) {
            if (@preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_ca_name_hint')) {
    function eottae_talkroom_ai_reaction_ca_name_hint($ca_name)
    {
        $ca_name = trim(strip_tags((string) $ca_name));
        if ($ca_name === '') {
            return '';
        }

        $map = array(
            '질문'     => 'question',
            'Q&A'      => 'question',
            '정보요청' => 'question',
            '모임'     => 'meetup',
            '모임모집' => 'meetup',
            '중고'     => 'sale',
            '거래'     => 'sale',
            '판매'     => 'sale',
            '정보'     => 'info',
            '후기'     => 'info',
            '공유'     => 'info',
            '소개'     => 'intro',
            '자기소개' => 'intro',
        );

        if (isset($map[$ca_name])) {
            return $map[$ca_name];
        }

        foreach ($map as $label => $type) {
            if (mb_strpos($ca_name, $label) !== false) {
                return $type;
            }
        }

        return '';
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_is_intro_member_post')) {
    function eottae_talkroom_ai_reaction_is_intro_member_post($room_id, $mb_id, array $text_parts)
    {
        if (!function_exists('eottae_talkroom_get_member_row')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($room_id < 1 || $mb_id === '') {
            return false;
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        if (!$member_row) {
            return false;
        }

        $joined_at = trim((string) ($member_row['joined_at'] ?? ''));
        $days = eottae_talkroom_ai_reaction_intro_member_days();
        if ($joined_at !== '' && $joined_at !== '0000-00-00 00:00:00') {
            $cutoff = date('Y-m-d H:i:s', G5_SERVER_TIME - ($days * 86400));
            if ($joined_at < $cutoff) {
                return false;
            }
        }

        $intro_patterns = array(
            '/처음(?:\s*(?:가입|왔|입니다|이에요|이예요|이요))?/ui',
            '/(?:가입|입장)\s*(?:했|한|합니다|했어요|했습니다)/ui',
            '/(?:자기)?소개/ui',
            '/반갑/ui',
            '/안녕(?:하세요|합니다)/ui',
            '/새(?:\s*)?(?:회원|멤버)/ui',
        );
        if (eottae_talkroom_ai_reaction_match_any($text_parts['plain'], $intro_patterns)) {
            return true;
        }

        return eottae_talkroom_ai_reaction_member_post_count($room_id, $mb_id) <= 1;
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_member_post_count')) {
    function eottae_talkroom_ai_reaction_member_post_count($room_id, $mb_id)
    {
        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $write_table = eottae_talkroom_write_table();
        if ($room_id < 1 || $mb_id === '' || $write_table === '') {
            return 0;
        }

        $visible = eottae_talkroom_post_visible_sql();
        $bot_id = sql_escape_string(eottae_talkroom_ai_bot_mb_id());
        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_1 = '{$room_id}'
              AND mb_id = '".sql_escape_string($mb_id)."'
              AND mb_id != '{$bot_id}'
              AND wr_3 NOT LIKE 'ai:%'
              AND {$visible}
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_talkroom_ai_classify_post_for_reaction')) {
    /**
     * @return array{type:string, source:string}|null
     */
    function eottae_talkroom_ai_classify_post_for_reaction(array $post, array $room)
    {
        $text_parts = eottae_talkroom_ai_reaction_normalize_text($post);
        if ($text_parts['plain'] === '' || mb_strlen($text_parts['plain']) < 8) {
            return null;
        }

        $ca_name = trim(strip_tags((string) ($post['ca_name'] ?? '')));
        if ($ca_name !== '' && (mb_strpos($ca_name, 'AI·') === 0 || $ca_name === '공지')) {
            return null;
        }

        $ca_hint = eottae_talkroom_ai_reaction_ca_name_hint($ca_name);
        $plain = $text_parts['plain'];
        $room_id = (int) ($post['wr_1'] ?? 0);
        $mb_id = (string) ($post['mb_id'] ?? '');

        $question_patterns = array(
            '/[?？]/u',
            '/(?:어디|어떻|어떤|무엇|뭐(?:가|야|예요|에요)?|알려|추천(?:해|좀)?|있(?:어|나|을까|습니까)|없(?:어|나|을까)|할까|일까|인가요|인지|되나요|가능(?:한가|할까)|궁금)/ui',
        );
        $meetup_patterns = array(
            '/(?:모임|족구|풋살|함께|같이|하실\s*분|참여(?:하실|할|해)|모셔|모집|같이\s*(?:할|가|먹|놀|운동))/ui',
        );
        $sale_patterns = array(
            '/(?:팝니다|삽니다|양도|나눔|중고|거래|매매|렌트|임대|급매|네고|가격\s*문의)/ui',
        );
        $info_patterns = array(
            '/(?:공유|팁|후기|정리(?:해)?|알려드|추천합니다|다녀왔|경험(?:담|상)|도움(?:이\s*될|됐))/ui',
        );

        if ($ca_hint === 'intro' || eottae_talkroom_ai_reaction_is_intro_member_post($room_id, $mb_id, $text_parts)) {
            return array('type' => 'intro', 'source' => $ca_hint === 'intro' ? 'ca_name' : 'intro_member');
        }
        if ($ca_hint === 'sale' || eottae_talkroom_ai_reaction_match_any($plain, $sale_patterns)) {
            return array('type' => 'sale', 'source' => $ca_hint === 'sale' ? 'ca_name' : 'keyword');
        }
        if ($ca_hint === 'meetup' || eottae_talkroom_ai_reaction_match_any($plain, $meetup_patterns)) {
            return array('type' => 'meetup', 'source' => $ca_hint === 'meetup' ? 'ca_name' : 'keyword');
        }
        if ($ca_hint === 'question' || eottae_talkroom_ai_reaction_match_any($plain, $question_patterns)) {
            return array('type' => 'question', 'source' => $ca_hint === 'question' ? 'ca_name' : 'keyword');
        }
        if ($ca_hint === 'info' || eottae_talkroom_ai_reaction_match_any($plain, $info_patterns)) {
            if (!preg_match('/[?？]/u', $plain)) {
                return array('type' => 'info', 'source' => $ca_hint === 'info' ? 'ca_name' : 'keyword');
            }
        }

        $room_category = trim((string) ($room['category'] ?? ''));
        if ($room_category === 'used' && eottae_talkroom_ai_reaction_match_any($plain, array('/(?:팔|살|나눔|양도)/ui'))) {
            return array('type' => 'sale', 'source' => 'room_category');
        }

        return null;
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_should_exclude_post')) {
    /**
     * @return array{exclude:bool, reason:string}
     */
    function eottae_talkroom_ai_reaction_should_exclude_post(array $post)
    {
        $text_parts = eottae_talkroom_ai_reaction_normalize_text($post);

        $plain = $text_parts['plain'];
        if ($plain === '') {
            return array('exclude' => true, 'reason' => 'empty_post');
        }

        foreach (eottae_talkroom_ai_reaction_exclude_patterns() as $reason => $pattern) {
            if (@preg_match($pattern, $plain)) {
                return array('exclude' => true, 'reason' => $reason);
            }
        }

        $sensitive = eottae_talkroom_ai_should_skip_reaction_for_content($plain);
        if ($sensitive) {
            return array('exclude' => true, 'reason' => (string) ($sensitive['reason'] ?? 'sensitive_content'));
        }

        if (eottae_talkroom_ai_reaction_count_pii_signals($plain) >= 2) {
            return array('exclude' => true, 'reason' => 'pii_heavy');
        }

        if ((int) ($post['wr_comment'] ?? 0) >= eottae_talkroom_ai_reaction_max_post_comments()) {
            return array('exclude' => true, 'reason' => 'enough_comments');
        }

        if (function_exists('eottae_talkroom_ai_is_ai_write_row') && eottae_talkroom_ai_is_ai_write_row($post)) {
            return array('exclude' => true, 'reason' => 'ai_author');
        }

        $ca_name = trim(strip_tags((string) ($post['ca_name'] ?? '')));
        if ($ca_name !== '' && mb_strpos($ca_name, 'AI·') === 0) {
            return array('exclude' => true, 'reason' => 'ai_category');
        }

        return array('exclude' => false, 'reason' => '');
    }
}

if (!function_exists('eottae_talkroom_ai_post_has_reaction')) {
    function eottae_talkroom_ai_post_has_reaction($room_id, $post_id)
    {
        $room_id = (int) $room_id;
        $post_id = (int) $post_id;
        if ($room_id < 1 || $post_id < 1) {
            return false;
        }

        $tables = eottae_talkroom_ai_table_names();
        if (eottae_talkroom_ai_table_exists($tables['logs'])) {
            $row = sql_fetch("
                SELECT log_id
                FROM `{$tables['logs']}`
                WHERE room_id = '{$room_id}'
                  AND trigger_type = 'reaction'
                  AND post_id = '{$post_id}'
                  AND status = 'success'
                LIMIT 1
            ", false);
            if (!empty($row['log_id'])) {
                return true;
            }
        }

        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $write_table = eottae_talkroom_write_table();
        $bot_id = sql_escape_string(eottae_talkroom_ai_bot_mb_id());
        if ($write_table === '' || $bot_id === '') {
            return false;
        }

        $visible = eottae_talkroom_post_visible_sql();
        $row = sql_fetch("
            SELECT wr_id
            FROM `{$write_table}`
            WHERE wr_is_comment = 1
              AND wr_parent = '{$post_id}'
              AND wr_1 = '{$room_id}'
              AND (mb_id = '{$bot_id}' OR wr_3 = 'ai:reaction')
              AND {$visible}
            LIMIT 1
        ", false);

        return !empty($row['wr_id']);
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_count_recent_success')) {
    function eottae_talkroom_ai_reaction_count_recent_success($room_id, $seconds)
    {
        $room_id = (int) $room_id;
        $seconds = max(60, (int) $seconds);
        $tables = eottae_talkroom_ai_table_names();
        if (!eottae_talkroom_ai_table_exists($tables['logs']) || $room_id < 1) {
            return 0;
        }

        $since = date('Y-m-d H:i:s', G5_SERVER_TIME - $seconds);
        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$tables['logs']}`
            WHERE room_id = '{$room_id}'
              AND trigger_type = 'reaction'
              AND status = 'success'
              AND created_at >= '".sql_escape_string($since)."'
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_last_success_at')) {
    function eottae_talkroom_ai_reaction_last_success_at($room_id)
    {
        $room_id = (int) $room_id;
        $tables = eottae_talkroom_ai_table_names();
        if (!eottae_talkroom_ai_table_exists($tables['logs']) || $room_id < 1) {
            return '';
        }

        $row = sql_fetch("
            SELECT created_at
            FROM `{$tables['logs']}`
            WHERE room_id = '{$room_id}'
              AND trigger_type = 'reaction'
              AND status = 'success'
            ORDER BY log_id DESC
            LIMIT 1
        ", false);

        return trim((string) ($row['created_at'] ?? ''));
    }
}

if (!function_exists('eottae_talkroom_ai_generate_reaction_via_api')) {
    /**
     * @return array<string, string>|null
     */
    function eottae_talkroom_ai_generate_reaction_via_api(array $room, array $settings, array $post, array $classification)
    {
        return null;
    }
}

if (!function_exists('eottae_talkroom_ai_generate_reaction_from_template')) {
    /**
     * @return array{content:string, prompt_text:string, reaction_type:string}
     */
    function eottae_talkroom_ai_generate_reaction_from_template(array $room, array $settings, array $post, array $classification)
    {
        $templates = eottae_talkroom_ai_reaction_templates();
        $reaction_type = (string) ($classification['type'] ?? '');
        $pool = isset($templates[$reaction_type]) ? $templates[$reaction_type] : array();
        if (empty($pool)) {
            return array(
                'content'       => '',
                'prompt_text'   => '',
                'reaction_type' => $reaction_type,
            );
        }

        $category_key = eottae_talkroom_ai_reaction_category_key($room);
        $template = isset($pool[$category_key]) ? $pool[$category_key] : $pool['default'];
        $content = trim((string) $template);

        $tone = trim((string) ($settings['ai_tone'] ?? 'friendly'));
        if ($tone === 'brief') {
            $content = cut_str(preg_replace('/\s+/u', ' ', $content), 140, '…');
        }

        return array(
            'content'       => $content,
            'prompt_text'   => 'template:'.$reaction_type.'/'.$category_key.'|source:'.($classification['source'] ?? 'unknown'),
            'reaction_type' => $reaction_type,
        );
    }
}

if (!function_exists('eottae_talkroom_ai_generate_reaction_message')) {
    function eottae_talkroom_ai_generate_reaction_message(array $room, array $settings, array $post, array $classification)
    {
        $api_message = eottae_talkroom_ai_generate_reaction_via_api($room, $settings, $post, $classification);
        if (is_array($api_message) && !empty($api_message['content'])) {
            return array(
                'content'       => (string) $api_message['content'],
                'prompt_text'   => (string) ($api_message['prompt_text'] ?? 'api'),
                'reaction_type' => (string) ($api_message['reaction_type'] ?? ($classification['type'] ?? '')),
            );
        }

        return eottae_talkroom_ai_generate_reaction_from_template($room, $settings, $post, $classification);
    }
}

if (!function_exists('eottae_talkroom_ai_evaluate_reaction')) {
    /**
     * @return array{ok:bool, reason:string, classification?:array}
     */
    function eottae_talkroom_ai_evaluate_reaction($room_id, array $post, $now = null, array $options = array())
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $post_id = (int) ($post['wr_id'] ?? 0);
        $now = $now ?: G5_TIME_YMDHIS;
        $force = !empty($options['force']);

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array('ok' => false, 'reason' => 'not_operating');
        }

        $settings = eottae_talkroom_ai_get_settings($room_id);
        if (empty($settings['reaction_enabled'])) {
            return array('ok' => false, 'reason' => 'reaction_disabled');
        }

        $shared = eottae_talkroom_ai_evaluate_shared_limits($room_id, $now, array(
            'force'   => $force,
            'skip_consecutive' => true,
        ));
        if (empty($shared['ok'])) {
            return array('ok' => false, 'reason' => $shared['reason']);
        }

        if (!$force && !eottae_talkroom_ai_is_within_active_hours($settings, $now)) {
            return array('ok' => false, 'reason' => 'outside_active_hours');
        }

        if (eottae_talkroom_ai_post_has_reaction($room_id, $post_id)) {
            return array('ok' => false, 'reason' => 'already_reacted');
        }

        $exclude = eottae_talkroom_ai_reaction_should_exclude_post($post);
        if (!empty($exclude['exclude'])) {
            return array('ok' => false, 'reason' => (string) $exclude['reason']);
        }

        if (!$force) {
            $hourly = eottae_talkroom_ai_reaction_count_recent_success($room_id, 3600);
            if ($hourly >= eottae_talkroom_ai_reaction_hourly_limit()) {
                return array('ok' => false, 'reason' => 'room_reaction_burst');
            }

            $last_at = eottae_talkroom_ai_reaction_last_success_at($room_id);
            if ($last_at !== '') {
                $gap = eottae_talkroom_ai_reaction_room_gap_seconds();
                if (G5_SERVER_TIME - strtotime($last_at) < $gap) {
                    return array('ok' => false, 'reason' => 'room_rate_limited');
                }
            }
        }

        $classification = eottae_talkroom_ai_classify_post_for_reaction($post, $room);
        if (!$classification) {
            return array('ok' => false, 'reason' => 'not_eligible_type');
        }

        return array(
            'ok'             => true,
            'reason'         => 'eligible',
            'classification' => $classification,
        );
    }
}

if (!function_exists('eottae_talkroom_ai_run_reaction_for_post')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_run_reaction_for_post($post_wr_id, array $options = array())
    {
        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        eottae_talkroom_ai_ensure_schema();

        $post_wr_id = (int) $post_wr_id;
        $dry_run = !empty($options['dry_run']);
        $force = !empty($options['force']);
        $now = isset($options['now']) ? (string) $options['now'] : G5_TIME_YMDHIS;

        $write_table = eottae_talkroom_write_table();
        if ($post_wr_id < 1 || $write_table === '') {
            return array(
                'status'  => 'skipped',
                'reason'  => 'invalid_post',
                'message' => '게시글 정보가 올바르지 않습니다.',
            );
        }

        $post = sql_fetch("
            SELECT *
            FROM `{$write_table}`
            WHERE wr_id = '{$post_wr_id}'
              AND wr_is_comment = 0
            LIMIT 1
        ", false);

        if (empty($post['wr_id'])) {
            return array(
                'status'  => 'skipped',
                'reason'  => 'post_not_found',
                'message' => '게시글을 찾을 수 없습니다.',
            );
        }

        $room_id = (int) ($post['wr_1'] ?? 0);
        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array(
                'post_id' => $post_wr_id,
                'room_id' => $room_id,
                'status'  => 'skipped',
                'reason'  => 'not_operating',
                'message' => '운영 중인 톡방이 아닙니다.',
            );
        }

        $check = eottae_talkroom_ai_evaluate_reaction($room_id, $post, $now, array(
            'force' => $force,
        ));

        if (empty($check['ok'])) {
            if (!$dry_run) {
                eottae_talkroom_ai_write_log($room_id, 'reaction', array(
                    'status'        => 'skipped',
                    'post_id'       => $post_wr_id,
                    'error_message' => $check['reason'],
                    'prompt_text'   => 'post:'.$post_wr_id,
                ));
            }

            return array(
                'post_id'   => $post_wr_id,
                'room_id'   => $room_id,
                'room_name' => get_text($room['room_name'] ?? ''),
                'status'    => 'skipped',
                'reason'    => $check['reason'],
                'message'   => '조건 미충족: '.$check['reason'],
            );
        }

        $settings = eottae_talkroom_ai_get_settings($room_id);
        $classification = $check['classification'];
        $generated = eottae_talkroom_ai_generate_reaction_message($room, $settings, $post, $classification);
        if (trim((string) ($generated['content'] ?? '')) === '') {
            if (!$dry_run) {
                eottae_talkroom_ai_write_log($room_id, 'reaction', array(
                    'status'        => 'failed',
                    'post_id'       => $post_wr_id,
                    'error_message' => 'empty_template',
                    'prompt_text'   => (string) ($generated['prompt_text'] ?? ''),
                ));
            }

            return array(
                'post_id' => $post_wr_id,
                'room_id' => $room_id,
                'status'  => 'failed',
                'reason'  => 'empty_template',
                'message' => '댓글 템플릿 생성 실패',
            );
        }

        if ($dry_run) {
            return array(
                'post_id'       => $post_wr_id,
                'room_id'       => $room_id,
                'room_name'     => get_text($room['room_name'] ?? ''),
                'status'        => 'dry_run',
                'reason'        => 'eligible',
                'reaction_type' => $generated['reaction_type'],
                'content'       => $generated['content'],
                'message'       => 'dry-run: 댓글 등록 생략',
            );
        }

        $insert = eottae_talkroom_ai_insert_comment(
            $room_id,
            $post_wr_id,
            $generated['content'],
            array(
                'ai_name'              => $settings['ai_name'] ?? '어때봇',
                'trigger_type'         => 'reaction',
                'target_mb_id'         => (string) ($post['mb_id'] ?? ''),
                'bump_parent_last'     => true,
                'show_ai_helper_label' => true,
            )
        );

        if (empty($insert['ok'])) {
            eottae_talkroom_ai_write_log($room_id, 'reaction', array(
                'status'        => 'failed',
                'post_id'       => $post_wr_id,
                'prompt_text'   => $generated['prompt_text'],
                'response_text' => $generated['content'],
                'error_message' => $insert['message'],
            ));

            return array(
                'post_id' => $post_wr_id,
                'room_id' => $room_id,
                'status'  => 'failed',
                'reason'  => 'insert_failed',
                'message' => $insert['message'],
            );
        }

        eottae_talkroom_ai_increment_daily_count($room_id, $now);
        eottae_talkroom_ai_write_log($room_id, 'reaction', array(
            'status'        => 'success',
            'prompt_text'   => $generated['prompt_text'],
            'response_text' => $generated['content'],
            'post_id'       => $post_wr_id,
            'comment_id'    => (int) ($insert['comment_id'] ?? 0),
        ));

        return array(
            'post_id'       => $post_wr_id,
            'room_id'       => $room_id,
            'room_name'     => get_text($room['room_name'] ?? ''),
            'status'        => 'success',
            'reason'        => 'commented',
            'reaction_type' => $generated['reaction_type'],
            'comment_id'    => (int) ($insert['comment_id'] ?? 0),
            'content'       => $generated['content'],
            'message'       => 'AI 리액션 댓글 등록 완료',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_schedule_reaction_for_post')) {
    function eottae_talkroom_ai_schedule_reaction_for_post($post_wr_id)
    {
        static $queued = array();

        $post_wr_id = (int) $post_wr_id;
        if ($post_wr_id < 1 || isset($queued[$post_wr_id])) {
            return;
        }

        $queued[$post_wr_id] = true;

        register_shutdown_function(function () use ($post_wr_id) {
            if (!function_exists('eottae_talkroom_ai_run_reaction_for_post')) {
                include_once G5_LIB_PATH.'/eottae-talkroom-ai-reaction.lib.php';
            }

            eottae_talkroom_ai_run_reaction_for_post($post_wr_id);
        });
    }
}
