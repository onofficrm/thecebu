<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_meetup_cooldown_days')) {
    function eottae_talkroom_ai_meetup_cooldown_days()
    {
        return 7;
    }
}

if (!function_exists('eottae_talkroom_ai_meetup_eligible_category_codes')) {
    /**
     * @return string[]
     */
    function eottae_talkroom_ai_meetup_eligible_category_codes()
    {
        return array('sports', 'hobby', 'parenting', 'kids', 'study', 'business', 'travel', 'food');
    }
}

if (!function_exists('eottae_talkroom_ai_meetup_is_eligible_category')) {
    function eottae_talkroom_ai_meetup_is_eligible_category($category_code)
    {
        $category_code = trim((string) $category_code);
        $map = array(
            'kids'  => 'parenting',
            'study' => 'parenting',
        );
        if (isset($map[$category_code])) {
            $category_code = $map[$category_code];
        }

        return in_array($category_code, eottae_talkroom_ai_meetup_eligible_category_codes(), true);
    }
}

if (!function_exists('eottae_talkroom_ai_meetup_category_key')) {
    function eottae_talkroom_ai_meetup_category_key(array $room)
    {
        $category = trim((string) ($room['category'] ?? ''));
        if (in_array($category, array('kids', 'study'), true)) {
            return 'parenting';
        }
        if (eottae_talkroom_ai_meetup_is_eligible_category($category)) {
            return $category;
        }

        return '';
    }
}

if (!function_exists('eottae_talkroom_ai_meetup_cta')) {
    function eottae_talkroom_ai_meetup_cta(array $settings = array())
    {
        $tone = trim((string) ($settings['ai_tone'] ?? 'friendly'));
        if ($tone === 'brief') {
            return "관심 있으시면 댓글로 '참여' 남겨주세요.";
        }

        return "참여 가능하신 분은 댓글로 '참여'라고 남겨주세요 😊";
    }
}

if (!function_exists('eottae_talkroom_ai_meetup_message_templates')) {
    /**
     * @return array<string, array<int, array{key:string, text:string}>>
     */
    function eottae_talkroom_ai_meetup_message_templates()
    {
        return array(
            'sports' => array(
                array('key' => 'jokgu', 'text' => '이번 주 토요일 오후에 족구 한 판 어떠세요? 4명만 모여도 가볍게 시작할 수 있을 것 같아요.'),
                array('key' => 'football', 'text' => '이번 주말 풋살 가능하신 분 체크해볼까요? 토요일 저녁 / 일요일 오전 중 가능한 시간도 같이 남겨주세요 ⚽'),
                array('key' => 'golf', 'text' => '이번 주 가볍게 라운딩 또는 연습장 모임 어떠세요? 경험과 관계없이 편하게 참여해 주세요 ⛳'),
                array('key' => 'table_tennis', 'text' => '탁구 한 게임 가볍게 어떠세요? 초보도 환영합니다. 가능한 요일을 댓글로 알려주세요.'),
                array('key' => 'badminton', 'text' => '배드민턴 가볍게 칠 분 찾아요. 이번 주 저녁이나 주말 중 가능한 시간을 댓글로 남겨주세요 🏸'),
            ),
            'hobby' => array(
                array('key' => 'coffee', 'text' => '이번 주 커피 한 잔 하며 수다 나눌 분 계신가요? 부담 없이 가볍게 만나 이야기해요 ☕'),
                array('key' => 'food', 'text' => '이번 주 맛집 한 곳 같이 가볼까요? 추천하고 싶은 메뉴나 가능한 요일을 댓글로 남겨주세요 🍽'),
                array('key' => 'riding', 'text' => '라이딩 같이할 분 찾아요. 짧은 코스부터 가볍게 시작해 보면 어떨까요? 🚴'),
                array('key' => 'diving', 'text' => '다이빙·스노클링 관심 있는 분들끼리 정보 나눠볼까요? 경험 공유 환영합니다 🤿'),
            ),
            'parenting' => array(
                array('key' => 'mom_talk', 'text' => '이번 주 금요일 오전에 커피 수다 모임 어떠세요? 아이 학교, 병원, 생활정보 같이 나눠보면 좋을 것 같아요.'),
                array('key' => 'english_camp', 'text' => '영어캠프·학원 정보 나눠볼까요? 다녀본 경험이나 궁금한 점을 편하게 남겨주세요 📚'),
                array('key' => 'school_hospital', 'text' => '아이 병원·학교 정보 나눔 시간 어떠세요? 도움이 됐던 곳이나 팁을 댓글로 공유해 주세요.'),
            ),
            'business' => array(
                array('key' => 'network', 'text' => '이번 주 사업자분들끼리 가볍게 정보 교류해 보면 어떨까요? 업종과 관심 주제를 댓글로 남겨주세요.'),
                array('key' => 'hiring', 'text' => '이번 주 사업자방 주제로 \'필리핀 직원 채용\' 이야기를 나눠보면 어떨까요? 경험 있으신 분들 댓글로 의견 남겨주세요.'),
                array('key' => 'marketing', 'text' => '마케팅·홍보 정보 나눔 어떠세요? 효과 봤던 방법이나 고민 중인 점을 편하게 공유해 주세요.'),
            ),
            'travel' => array(
                array('key' => 'travel_qna', 'text' => '이번 주 세부 여행 준비 중인 분들끼리 질문 모임처럼 이야기 나눠볼까요? 공항픽업, 환전, 마사지 중 궁금한 주제를 남겨주세요.'),
                array('key' => 'travel_plan', 'text' => '세부 여행 일정 짜는 분들끼리 가볍게 정보 나눠볼까요? 궁금한 점을 댓글로 남겨주세요 ✈️'),
            ),
            'food' => array(
                array('key' => 'restaurant_meetup', 'text' => '이번 주 맛집·카페 탐방 모임 어떠세요? 가보고 싶은 곳이나 추천 메뉴를 댓글로 알려주세요 🍜'),
                array('key' => 'food_share', 'text' => '세부에서 자주 가는 맛집을 공유하는 가벼운 모임 어떠세요? 부담 없이 추천만 남겨주셔도 좋아요.'),
            ),
        );
    }
}

if (!function_exists('eottae_talkroom_ai_room_has_recent_meetup_activity')) {
    function eottae_talkroom_ai_room_has_recent_meetup_activity($room_id, $days = null)
    {
        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $days = $days === null ? eottae_talkroom_ai_meetup_cooldown_days() : max(1, (int) $days);
        $write_table = eottae_talkroom_write_table();
        if ($room_id < 1 || $write_table === '') {
            return false;
        }

        $since = date('Y-m-d H:i:s', G5_SERVER_TIME - ($days * 86400));
        $visible = eottae_talkroom_post_visible_sql();

        $row = sql_fetch("
            SELECT wr_id
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_1 = '{$room_id}'
              AND {$visible}
              AND wr_datetime >= '".sql_escape_string($since)."'
              AND (
                    ca_name LIKE '%모임%'
                 OR wr_subject LIKE '%모임%'
                 OR wr_3 = 'ai:meetup_suggest'
                 OR ca_name = '모임공지'
              )
            LIMIT 1
        ", false);

        return !empty($row['wr_id']);
    }
}

if (!function_exists('eottae_talkroom_ai_generate_meetup_via_api')) {
    /**
     * @return array<string, string>|null
     */
    function eottae_talkroom_ai_generate_meetup_via_api(array $room, array $settings)
    {
        return null;
    }
}

if (!function_exists('eottae_talkroom_ai_generate_meetup_from_template')) {
    /**
     * @return array{subject:string, content:string, prompt_text:string, template_key:string}
     */
    function eottae_talkroom_ai_generate_meetup_from_template(array $room, array $settings)
    {
        $templates = eottae_talkroom_ai_meetup_message_templates();
        $category_key = eottae_talkroom_ai_meetup_category_key($room);
        $pool = isset($templates[$category_key]) ? $templates[$category_key] : array();

        if (empty($pool)) {
            $pool = array(
                array('key' => 'general', 'text' => '이번 주 가볍게 이야기 나눌 모임 어떠세요? 부담 없이 참여 의사를 댓글로 남겨주세요.'),
            );
        }

        $picked = $pool[array_rand($pool)];
        $body = trim((string) ($picked['text']));
        $cta = eottae_talkroom_ai_meetup_cta($settings);
        $content = $body."\n\n".$cta;

        $tone = trim((string) ($settings['ai_tone'] ?? 'friendly'));
        if ($tone === 'brief') {
            $content = cut_str(preg_replace('/\s+/u', ' ', $content), 220, '…');
        }

        $ai_name = trim((string) ($settings['ai_name'] ?? '어때봇'));
        if ($ai_name === '') {
            $ai_name = '어때봇';
        }

        return array(
            'subject'      => '['.$ai_name.'] 이번 주 모임 제안',
            'content'      => $content,
            'prompt_text'  => 'template:'.$category_key.'/'.$picked['key'].'|tone:'.$tone,
            'template_key' => (string) $picked['key'],
        );
    }
}

if (!function_exists('eottae_talkroom_ai_generate_meetup_message')) {
    function eottae_talkroom_ai_generate_meetup_message(array $room, array $settings)
    {
        $api_message = eottae_talkroom_ai_generate_meetup_via_api($room, $settings);
        if (is_array($api_message) && !empty($api_message['content'])) {
            $ai_name = trim((string) ($settings['ai_name'] ?? '어때봇'));
            if ($ai_name === '') {
                $ai_name = '어때봇';
            }
            $cta = eottae_talkroom_ai_meetup_cta($settings);
            $content = trim((string) $api_message['content']);
            if (strpos($content, $cta) === false) {
                $content .= "\n\n".$cta;
            }

            return array(
                'subject'      => !empty($api_message['subject']) ? (string) $api_message['subject'] : '['.$ai_name.'] 이번 주 모임 제안',
                'content'      => $content,
                'prompt_text'  => (string) ($api_message['prompt_text'] ?? 'api'),
                'template_key' => (string) ($api_message['template_key'] ?? 'api'),
            );
        }

        return eottae_talkroom_ai_generate_meetup_from_template($room, $settings);
    }
}

if (!function_exists('eottae_talkroom_ai_evaluate_meetup_suggest')) {
    /**
     * @return array{ok:bool, reason:string}
     */
    function eottae_talkroom_ai_evaluate_meetup_suggest($room_id, $now = null, array $options = array())
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $now = $now ?: G5_TIME_YMDHIS;
        $force = !empty($options['force']);
        $is_test = !empty($options['is_test']);
        $bypass = $force && $is_test;

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array('ok' => false, 'reason' => 'not_operating');
        }

        if (!$bypass && !eottae_talkroom_ai_meetup_is_eligible_category($room['category'] ?? '')) {
            return array('ok' => false, 'reason' => 'category_not_eligible');
        }

        $settings = eottae_talkroom_ai_get_settings($room_id);
        if (!$bypass) {
            $shared = eottae_talkroom_ai_evaluate_shared_limits($room_id, $now, array(
                'force'   => $force,
                'is_test' => $is_test,
            ));
            if (empty($shared['ok'])) {
                return array('ok' => false, 'reason' => $shared['reason']);
            }

            if (empty($settings['meetup_suggest_enabled'])) {
                return array('ok' => false, 'reason' => 'meetup_suggest_disabled');
            }
            if (!eottae_talkroom_ai_is_within_active_hours($settings, $now)) {
                return array('ok' => false, 'reason' => 'outside_active_hours');
            }

            $cooldown = eottae_talkroom_ai_meetup_cooldown_days();
            if (eottae_talkroom_ai_has_success_log_within_days($room_id, 'meetup_suggest', $cooldown)) {
                return array('ok' => false, 'reason' => 'recent_ai_meetup');
            }

            if (eottae_talkroom_ai_room_has_recent_meetup_activity($room_id, $cooldown)) {
                return array('ok' => false, 'reason' => 'recent_meetup_post');
            }

            $context = eottae_talkroom_ai_evaluate_trigger_context($room_id, 'meetup_suggest', $now, array(
                'force'   => $force,
                'is_test' => $is_test,
            ));
            if (empty($context['ok'])) {
                return array('ok' => false, 'reason' => $context['reason']);
            }
        }

        return array('ok' => true, 'reason' => 'eligible');
    }
}

if (!function_exists('eottae_talkroom_ai_list_meetup_candidate_room_ids')) {
    /**
     * @return int[]
     */
    function eottae_talkroom_ai_list_meetup_candidate_room_ids($limit = 200)
    {
        if (!function_exists('eottae_talkroom_table_names')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $tables = eottae_talkroom_table_names();
        $ai_tables = eottae_talkroom_ai_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms']) || !eottae_talkroom_ai_table_exists($ai_tables['settings'])) {
            return array();
        }

        $categories = eottae_talkroom_ai_meetup_eligible_category_codes();
        $cat_sql = array();
        foreach ($categories as $code) {
            $cat_sql[] = "'".sql_escape_string($code)."'";
        }
        $cat_in = implode(',', $cat_sql);

        $limit = max(1, min(500, (int) $limit));
        $result = sql_query("
            SELECT r.room_id
            FROM `{$tables['rooms']}` r
            INNER JOIN `{$ai_tables['settings']}` s ON s.room_id = r.room_id
            WHERE r.status IN ('approved', 'active')
              AND r.category IN ({$cat_in})
              AND s.ai_enabled = 1
              AND s.meetup_suggest_enabled = 1
            ORDER BY r.room_id ASC
            LIMIT {$limit}
        ", false);

        $ids = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $ids[] = (int) $row['room_id'];
            }
        }

        return $ids;
    }
}

if (!function_exists('eottae_talkroom_ai_run_meetup_suggest_trigger')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_run_meetup_suggest_trigger($room_id, $dry_run = false, $now = null, array $options = array())
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $now = $now ?: G5_TIME_YMDHIS;
        $is_test = !empty($options['is_test']);
        $trigger_type = $is_test ? 'admin_test' : 'meetup_suggest';
        $ca_name = $is_test ? 'AI·모임(테스트)' : 'AI·모임';

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array(
                'room_id' => $room_id,
                'status'  => 'skipped',
                'reason'  => 'not_operating',
                'message' => '운영 중인 톡방이 아닙니다.',
            );
        }

        $settings = eottae_talkroom_ai_get_settings($room_id);
        $check = eottae_talkroom_ai_evaluate_meetup_suggest($room_id, $now, $options);
        if (empty($check['ok'])) {
            if (!$dry_run) {
                eottae_talkroom_ai_write_log($room_id, $trigger_type, array(
                    'status'        => 'skipped',
                    'error_message' => $check['reason'],
                    'prompt_text'   => $is_test ? 'admin_test_precheck' : 'precheck',
                ));
            }

            return array(
                'room_id'   => $room_id,
                'room_name' => get_text($room['room_name'] ?? ''),
                'status'    => 'skipped',
                'reason'    => $check['reason'],
                'message'   => '조건 미충족: '.$check['reason'],
            );
        }

        $generated = eottae_talkroom_ai_generate_meetup_message($room, $settings);
        if ($dry_run) {
            return array(
                'room_id'   => $room_id,
                'room_name' => get_text($room['room_name'] ?? ''),
                'status'    => 'dry_run',
                'reason'    => 'eligible',
                'subject'   => $generated['subject'],
                'content'   => $generated['content'],
                'message'   => 'dry-run: 게시글 등록 생략',
            );
        }

        $insert = eottae_talkroom_ai_insert_post(
            $room_id,
            $generated['subject'],
            $generated['content'],
            array(
                'ai_name'      => $settings['ai_name'] ?? '어때봇',
                'trigger_type' => $trigger_type,
                'ca_name'      => $ca_name,
            )
        );

        if (empty($insert['ok'])) {
            eottae_talkroom_ai_write_log($room_id, $trigger_type, array(
                'status'        => 'failed',
                'prompt_text'   => $generated['prompt_text'],
                'response_text' => $generated['content'],
                'error_message' => $insert['message'],
            ));

            return array(
                'room_id'   => $room_id,
                'room_name' => get_text($room['room_name'] ?? ''),
                'status'    => 'failed',
                'reason'    => 'insert_failed',
                'message'   => $insert['message'],
            );
        }

        eottae_talkroom_ai_increment_daily_count($room_id, $now, array('is_test' => $is_test));
        eottae_talkroom_ai_write_log($room_id, $trigger_type, array(
            'status'        => 'success',
            'prompt_text'   => $generated['prompt_text'].($is_test ? '|mode:test' : ''),
            'response_text' => $generated['content'],
            'post_id'       => (int) ($insert['wr_id'] ?? 0),
        ));

        return array(
            'room_id'   => $room_id,
            'room_name' => get_text($room['room_name'] ?? ''),
            'status'    => 'success',
            'reason'    => $is_test ? 'test_posted' : 'posted',
            'post_id'   => (int) ($insert['wr_id'] ?? 0),
            'subject'   => $generated['subject'],
            'content'   => $generated['content'],
            'message'   => $is_test ? '모임 제안 테스트 게시글 등록 완료' : '모임 제안 게시글 등록 완료',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_run_meetup_cron')) {
    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_run_meetup_cron(array $options = array())
    {
        eottae_talkroom_ai_ensure_schema();

        $dry_run = !empty($options['dry_run']);
        $room_id = isset($options['room_id']) ? (int) $options['room_id'] : 0;
        $limit = isset($options['limit']) ? (int) $options['limit'] : 200;
        $now = isset($options['now']) ? (string) $options['now'] : G5_TIME_YMDHIS;

        $results = array();
        $summary = array(
            'checked' => 0,
            'posted'  => 0,
            'skipped' => 0,
            'failed'  => 0,
            'dry_run' => $dry_run ? 1 : 0,
        );

        $room_ids = $room_id > 0
            ? array($room_id)
            : eottae_talkroom_ai_list_meetup_candidate_room_ids($limit);

        foreach ($room_ids as $id) {
            $summary['checked']++;
            $result = eottae_talkroom_ai_run_meetup_suggest_trigger($id, $dry_run, $now);
            $results[] = $result;

            if (($result['status'] ?? '') === 'success') {
                $summary['posted']++;
            } elseif (($result['status'] ?? '') === 'failed') {
                $summary['failed']++;
            } else {
                $summary['skipped']++;
            }
        }

        return array(
            'summary' => $summary,
            'results' => $results,
        );
    }
}
