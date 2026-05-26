<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_daily_question_cta')) {
    function eottae_talkroom_ai_daily_question_cta(array $settings = array())
    {
        $tone = trim((string) ($settings['ai_tone'] ?? 'friendly'));
        if ($tone === 'brief') {
            return '댓글로 남겨주세요.';
        }
        if ($tone === 'lively') {
            return '댓글로 편하게 참여해 주세요! 😊';
        }

        return '댓글로 편하게 남겨주세요 😊';
    }
}

if (!function_exists('eottae_talkroom_ai_daily_question_templates')) {
    /**
     * 카테고리 코드(expat_life, parenting …)별 질문 풀
     *
     * @return array<string, array<int, string>>
     */
    function eottae_talkroom_ai_daily_question_templates()
    {
        return array(
            'expat_life' => array(
                '세부 살면서 \'이건 한국보다 좋다\' 싶은 게 있으세요?',
                '세부 생활하면서 가장 자주 이용하는 편의시설이나 서비스는 무엇인가요?',
                '세부에서 새로 알게 된 생활 꿀팁 하나를 공유해 주실 분 계신가요?',
            ),
            'parenting' => array(
                '아이 키우면서 세부에서 가장 만족하는 점은 무엇인가요?',
                '세부에서 아이와 함께 가기 좋았던 장소가 있다면 추천해 주세요.',
                '육아하면서 세부에서 가장 도움 받은 정보는 무엇이었나요?',
            ),
            'sports' => array(
                '이번 주 운동·모임 계획 있으신가요? 종목과 가능한 시간을 댓글로 알려주세요.',
                '세부에서 즐기는 운동이 있다면 무엇인가요? 같이할 분도 환영해요!',
                '주말에 함께할 스포츠 모임이 있다면 어떤 활동이 좋을까요?',
            ),
            'travel' => array(
                '세부 여행에서 꼭 하고 싶은 것 하나만 고른다면? 호핑 / 마사지 / 맛집 / 쇼핑 / 리조트 휴식',
                '세부 처음 방문하신다면 가장 먼저 궁금한 게 무엇인가요?',
                '세부 여행 중 가장 기억에 남았던 경험을 하나만 골라본다면?',
            ),
            'business' => array(
                '필리핀 직원과 일하면서 가장 중요하게 보는 점은 무엇인가요?',
                '세부에서 사업 운영하며 가장 어려웠던 점은 무엇이었나요?',
                '사업자분들께, 세부에서 마케팅할 때 효과 봤던 방법이 있으신가요?',
            ),
            'used' => array(
                '이번 주 집에서 정리하고 싶은 물건 있으신가요? 안 쓰는 물건을 올려보세요 😊',
                '최근에 세부에서 구하거나 팔고 싶은 물건이 있으신가요?',
                '중고거래할 때 가장 중요하게 보는 기준이 무엇인가요?',
            ),
            'job' => array(
                '세부에서 일·알바 구할 때 가장 먼저 확인하는 조건은 무엇인가요?',
                '구인·구직 경험 중 도움이 됐던 팁이 있다면 공유해 주세요.',
                '이번 달 세부에서 함께 일할 분을 찾고 계신가요?',
            ),
            'estate' => array(
                '세부에서 집·상가를 구할 때 가장 중요하게 보는 조건은 무엇인가요?',
                '거주 중인 지역의 장단점을 간단히 공유해 주실 분 계신가요?',
                '세부 부동산 관련해서 최근 궁금했던 점이 있으신가요?',
            ),
            'food' => array(
                '요즘 세부에서 자주 가는 맛집·카페 하나만 추천해 주세요.',
                '세부에서 꼭 먹어봐야 한다고 생각하는 음식은 무엇인가요?',
                '새로 오픈했거나 숨은 맛집 정보가 있다면 나눠주세요.',
            ),
            'hobby' => array(
                '요즘 즐기는 취미나 함께할 모임 주제가 있으신가요?',
                '세부에서 같이할 취미 모임이 있다면 어떤 활동이 좋을까요?',
                '이번 주말 가볍게 참여할 수 있는 모임 아이디어가 있으신가요?',
            ),
            'etc' => array(
                '오늘 세부에서 있었던 소소한 일 중 하나를 공유해 보실래요?',
                '요즘 가장 궁금하거나 고민되는 점이 있다면 무엇인가요?',
                '이 톡방에서 나누고 싶은 이야기 주제를 하나 제안해 주세요.',
            ),
        );
    }
}

if (!function_exists('eottae_talkroom_ai_daily_question_category_key')) {
    function eottae_talkroom_ai_daily_question_category_key(array $room)
    {
        $category = trim((string) ($room['category'] ?? ''));
        $map = array(
            'life'   => 'expat_life',
            'kids'   => 'parenting',
            'study'  => 'parenting',
        );
        if (isset($map[$category])) {
            return $map[$category];
        }

        $templates = eottae_talkroom_ai_daily_question_templates();
        if (isset($templates[$category])) {
            return $category;
        }

        return 'etc';
    }
}

if (!function_exists('eottae_talkroom_ai_generate_daily_question_via_api')) {
    /**
     * @return array<string, string>|null
     */
    function eottae_talkroom_ai_generate_daily_question_via_api(array $room, array $settings)
    {
        return null;
    }
}

if (!function_exists('eottae_talkroom_ai_generate_daily_question_from_template')) {
    /**
     * @return array{subject:string, content:string, prompt_text:string, category_key:string}
     */
    function eottae_talkroom_ai_generate_daily_question_from_template(array $room, array $settings)
    {
        $templates = eottae_talkroom_ai_daily_question_templates();
        $category_key = eottae_talkroom_ai_daily_question_category_key($room);
        $pool = isset($templates[$category_key]) ? $templates[$category_key] : $templates['etc'];
        $question = $pool[array_rand($pool)];

        $tone = trim((string) ($settings['ai_tone'] ?? 'friendly'));
        if ($tone === 'brief') {
            $question = cut_str(preg_replace('/\s+/u', ' ', $question), 100, '…');
        }

        $cta = eottae_talkroom_ai_daily_question_cta($settings);
        $content = $question."\n\n".$cta;

        $ai_name = trim((string) ($settings['ai_name'] ?? '어때봇'));
        if ($ai_name === '') {
            $ai_name = '어때봇';
        }

        return array(
            'subject'      => '['.$ai_name.'] 오늘의 질문',
            'content'      => $content,
            'prompt_text'  => 'template:'.$category_key.'|tone:'.$tone,
            'category_key' => $category_key,
        );
    }
}

if (!function_exists('eottae_talkroom_ai_generate_daily_question')) {
    /**
     * @return array{subject:string, content:string, prompt_text:string, category_key:string}
     */
    function eottae_talkroom_ai_generate_daily_question(array $room, array $settings)
    {
        $api_message = eottae_talkroom_ai_generate_daily_question_via_api($room, $settings);
        if (is_array($api_message) && !empty($api_message['content'])) {
            $ai_name = trim((string) ($settings['ai_name'] ?? '어때봇'));
            if ($ai_name === '') {
                $ai_name = '어때봇';
            }
            $cta = eottae_talkroom_ai_daily_question_cta($settings);
            $content = trim((string) $api_message['content']);
            if (strpos($content, $cta) === false) {
                $content .= "\n\n".$cta;
            }

            return array(
                'subject'      => !empty($api_message['subject']) ? (string) $api_message['subject'] : '['.$ai_name.'] 오늘의 질문',
                'content'      => $content,
                'prompt_text'  => (string) ($api_message['prompt_text'] ?? 'api'),
                'category_key' => (string) ($api_message['category_key'] ?? 'api'),
            );
        }

        return eottae_talkroom_ai_generate_daily_question_from_template($room, $settings);
    }
}

if (!function_exists('eottae_talkroom_ai_evaluate_daily_question')) {
    /**
     * @return array{ok:bool, reason:string}
     */
    function eottae_talkroom_ai_evaluate_daily_question($room_id, $now = null, array $options = array())
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $now = $now ?: G5_TIME_YMDHIS;
        $force = !empty($options['force']);
        $is_test = !empty($options['is_test']);
        $target_date = substr($now, 0, 10);

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array('ok' => false, 'reason' => 'not_operating');
        }

        $settings = eottae_talkroom_ai_get_settings($room_id);

        if (!$force || !$is_test) {
            $shared = eottae_talkroom_ai_evaluate_shared_limits($room_id, $now, array(
                'force'   => $force,
                'is_test' => $is_test,
            ));
            if (empty($shared['ok'])) {
                return array('ok' => false, 'reason' => $shared['reason']);
            }

            if (empty($settings['daily_question_enabled'])) {
                return array('ok' => false, 'reason' => 'daily_question_disabled');
            }
            if (!eottae_talkroom_ai_is_within_active_hours($settings, $now)) {
                return array('ok' => false, 'reason' => 'outside_active_hours');
            }
        }

        if (!$is_test && eottae_talkroom_ai_has_success_log_on_date($room_id, 'daily_question', $target_date)) {
            return array('ok' => false, 'reason' => 'already_posted_today');
        }

        if (!$force || !$is_test) {
            $context = eottae_talkroom_ai_evaluate_trigger_context($room_id, 'daily_question', $now, array(
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

if (!function_exists('eottae_talkroom_ai_list_daily_question_candidate_room_ids')) {
    /**
     * @return int[]
     */
    function eottae_talkroom_ai_list_daily_question_candidate_room_ids($limit = 200)
    {
        if (!function_exists('eottae_talkroom_table_names')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $tables = eottae_talkroom_table_names();
        $ai_tables = eottae_talkroom_ai_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms']) || !eottae_talkroom_ai_table_exists($ai_tables['settings'])) {
            return array();
        }

        $limit = max(1, min(500, (int) $limit));
        $result = sql_query("
            SELECT r.room_id
            FROM `{$tables['rooms']}` r
            INNER JOIN `{$ai_tables['settings']}` s ON s.room_id = r.room_id
            WHERE r.status IN ('approved', 'active')
              AND s.ai_enabled = 1
              AND s.daily_question_enabled = 1
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

if (!function_exists('eottae_talkroom_ai_run_daily_question_trigger')) {
    /**
     * @param array<string, mixed> $options force, is_test, dry_run, now
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_run_daily_question_trigger($room_id, array $options = array())
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $dry_run = !empty($options['dry_run']);
        $force = !empty($options['force']);
        $is_test = !empty($options['is_test']);
        $now = isset($options['now']) ? (string) $options['now'] : G5_TIME_YMDHIS;
        $trigger_type = $is_test ? 'admin_test' : 'daily_question';
        $ca_name = $is_test ? 'AI·질문(테스트)' : 'AI·질문';

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
        $check = eottae_talkroom_ai_evaluate_daily_question($room_id, $now, array(
            'force'   => $force,
            'is_test' => $is_test,
        ));

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

        $generated = eottae_talkroom_ai_generate_daily_question($room, $settings);
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
            'message'   => $is_test ? '오늘의 질문 테스트 게시글 등록 완료' : '오늘의 질문 게시글 등록 완료',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_run_daily_question_cron')) {
    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_run_daily_question_cron(array $options = array())
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
            : eottae_talkroom_ai_list_daily_question_candidate_room_ids($limit);

        foreach ($room_ids as $id) {
            $summary['checked']++;
            $result = eottae_talkroom_ai_run_daily_question_trigger($id, array(
                'dry_run' => $dry_run,
                'now'     => $now,
            ));
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
