<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_quiet_message_templates')) {
    /**
     * @return array<string, array<int, string>>
     */
    function eottae_talkroom_ai_quiet_message_templates()
    {
        return array(
            'general' => array(
                '오늘 방이 조용하네요 😊 세부 사시는 분들, 요즘 가장 자주 가는 맛집 하나씩 추천해볼까요?',
                '오늘은 어떤 이야기가 오가면 좋을까요? 세부 생활 꿀팁이나 근황을 가볍게 나눠보세요.',
                '잠깐 쉬어가는 시간이에요 ☕ 요즘 세부에서 새로 알게 된 장소나 정보가 있으면 공유해 주세요.',
            ),
            'sports' => array(
                '이번 주말 운동하기 좋은 시간대가 있으면 족구 한 판 어떠세요? 가능하신 분은 댓글 남겨주세요.',
                '요즘 같이 운동하실 분 찾으시나요? 관심 종목이나 가능한 요일을 댓글로 알려주세요.',
                '세부에서 즐기기 좋은 운동이나 모임이 있다면 추천해 주세요. 주말에 같이할 분도 환영해요!',
            ),
            'parenting' => array(
                '오늘의 맘수다 질문입니다 😊 세부에서 아이 키우면서 가장 도움이 됐던 장소나 서비스가 있으세요?',
                '육아하시면서 세부에서 꼭 알아두면 좋은 팁이 있다면 나눠주세요. 다른 분들께도 큰 도움이 될 거예요.',
                '아이와 함께 가기 좋은 세부 장소 추천해 주실 분 계신가요? 경험담을 댓글로 남겨주세요.',
            ),
            'business' => array(
                '오늘의 사업자 질문입니다. 필리핀 직원 채용할 때 가장 중요하게 보는 점은 무엇인가요?',
                '세부에서 사업 운영하시면서 가장 어려웠던 점과 해결 방법을 나눠보실까요?',
                '사업자분들께 여쭤봅니다. 세부에서 마케팅·홍보할 때 효과 봤던 방법이 있으신가요?',
            ),
            'travel' => array(
                '세부 처음 오시는 분들이 가장 많이 궁금해하는 건 공항픽업, 환전, 마사지, 호핑투어입니다. 오늘은 어떤 게 궁금하세요?',
                '세부 여행 준비 중이신가요? 일정·숙소·투어 관련해서 궁금한 점을 편하게 질문해 주세요.',
                '세부에서 꼭 해보면 좋은 체험이나 코스 추천해 주실 분 계신가요? 여행자분들께 도움이 됩니다.',
            ),
        );
    }
}

if (!function_exists('eottae_talkroom_ai_quiet_template_group')) {
    function eottae_talkroom_ai_quiet_template_group(array $room, array $settings)
    {
        $category = trim((string) ($room['category'] ?? ''));
        $persona = trim((string) ($settings['ai_persona'] ?? ''));

        if ($category === 'sports' || $persona === 'meetup_helper') {
            return 'sports';
        }
        if (in_array($category, array('parenting', 'kids', 'study'), true) || $persona === 'mom_talk') {
            return 'parenting';
        }
        if ($category === 'business' || $persona === 'business_partner') {
            return 'business';
        }
        if ($category === 'travel' || $persona === 'travel_guide') {
            return 'travel';
        }

        return 'general';
    }
}

if (!function_exists('eottae_talkroom_ai_generate_quiet_message_via_api')) {
    /**
     * OpenAI 등 API 연동용 (4단계에서는 미사용)
     *
     * @return array<string, string>|null
     */
    function eottae_talkroom_ai_generate_quiet_message_via_api(array $room, array $settings)
    {
        return null;
    }
}

if (!function_exists('eottae_talkroom_ai_generate_quiet_message_from_template')) {
    /**
     * @return array{subject:string, content:string, prompt_text:string, template_group:string}
     */
    function eottae_talkroom_ai_generate_quiet_message_from_template(array $room, array $settings)
    {
        $templates = eottae_talkroom_ai_quiet_message_templates();
        $group = eottae_talkroom_ai_quiet_template_group($room, $settings);
        $pool = isset($templates[$group]) ? $templates[$group] : $templates['general'];
        $content = $pool[array_rand($pool)];

        $tone = trim((string) ($settings['ai_tone'] ?? 'friendly'));
        if ($tone === 'brief') {
            $content = preg_replace('/\s+/u', ' ', $content);
            $content = cut_str($content, 120, '…');
        } elseif ($tone === 'informative') {
            $content = '💡 '.$content;
        }

        $ai_name = trim((string) ($settings['ai_name'] ?? '어때봇'));
        if ($ai_name === '') {
            $ai_name = '어때봇';
        }

        return array(
            'subject'        => '['.$ai_name.'] 오늘의 이야기 주제',
            'content'        => $content,
            'prompt_text'    => 'template:'.$group.'|tone:'.$tone,
            'template_group' => $group,
        );
    }
}

if (!function_exists('eottae_talkroom_ai_generate_quiet_message')) {
    /**
     * @return array{subject:string, content:string, prompt_text:string, template_group:string}
     */
    function eottae_talkroom_ai_generate_quiet_message(array $room, array $settings)
    {
        $api_message = eottae_talkroom_ai_generate_quiet_message_via_api($room, $settings);
        if (is_array($api_message) && !empty($api_message['content'])) {
            $ai_name = trim((string) ($settings['ai_name'] ?? '어때봇'));
            if ($ai_name === '') {
                $ai_name = '어때봇';
            }

            return array(
                'subject'        => !empty($api_message['subject']) ? (string) $api_message['subject'] : '['.$ai_name.'] 오늘의 이야기 주제',
                'content'        => (string) $api_message['content'],
                'prompt_text'    => (string) ($api_message['prompt_text'] ?? 'api'),
                'template_group' => (string) ($api_message['template_group'] ?? 'api'),
            );
        }

        return eottae_talkroom_ai_generate_quiet_message_from_template($room, $settings);
    }
}

if (!function_exists('eottae_talkroom_ai_minutes_since')) {
    function eottae_talkroom_ai_minutes_since($datetime, $now = null)
    {
        $datetime = trim((string) $datetime);
        if ($datetime === '' || $datetime === '0000-00-00 00:00:00') {
            return null;
        }

        $now_ts = $now ? strtotime($now) : G5_SERVER_TIME;
        $then_ts = strtotime($datetime);
        if (!$now_ts || !$then_ts) {
            return null;
        }

        return (int) floor(max(0, $now_ts - $then_ts) / 60);
    }
}

if (!function_exists('eottae_talkroom_ai_last_success_log_at')) {
    function eottae_talkroom_ai_last_success_log_at($room_id, $trigger_type = 'quiet_room')
    {
        $room_id = (int) $room_id;
        $tables = eottae_talkroom_ai_table_names();
        if (!eottae_talkroom_ai_table_exists($tables['logs'])) {
            return '';
        }

        $row = sql_fetch("
            SELECT created_at
            FROM `{$tables['logs']}`
            WHERE room_id = '{$room_id}'
              AND trigger_type = '".sql_escape_string((string) $trigger_type)."'
              AND status = 'success'
            ORDER BY log_id DESC
            LIMIT 1
        ", false);

        return trim((string) ($row['created_at'] ?? ''));
    }
}

if (!function_exists('eottae_talkroom_ai_evaluate_quiet_room')) {
    /**
     * @return array{ok:bool, reason:string}
     */
    function eottae_talkroom_ai_evaluate_quiet_room($room_id, $now = null, array $options = array())
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

        $settings = eottae_talkroom_ai_get_settings($room_id);
        if (!$bypass) {
            $shared = eottae_talkroom_ai_evaluate_shared_limits($room_id, $now, array(
                'force'   => $force,
                'is_test' => $is_test,
            ));
            if (empty($shared['ok'])) {
                return array('ok' => false, 'reason' => $shared['reason']);
            }

            if (empty($settings['quiet_trigger_enabled'])) {
                return array('ok' => false, 'reason' => 'quiet_trigger_disabled');
            }
            if (!eottae_talkroom_ai_is_within_active_hours($settings, $now)) {
                return array('ok' => false, 'reason' => 'outside_active_hours');
            }
        }

        $min_silence = max(30, (int) ($settings['min_silence_minutes'] ?? 360));
        if (!$bypass) {
            $last_activity = eottae_talkroom_ai_room_last_activity_at($room_id);
            if ($last_activity !== '') {
                $since = eottae_talkroom_ai_minutes_since($last_activity, $now);
                if ($since !== null && $since < $min_silence) {
                    return array('ok' => false, 'reason' => 'room_not_quiet');
                }
            }

            $latest_post = eottae_talkroom_ai_room_latest_post_row($room_id);
            if ($latest_post && eottae_talkroom_ai_is_ai_write_row($latest_post)) {
                return array('ok' => false, 'reason' => 'latest_post_is_ai');
            }

            $last_ai_at = eottae_talkroom_ai_room_last_ai_post_at($room_id);
            if ($last_ai_at !== '') {
                $since_ai = eottae_talkroom_ai_minutes_since($last_ai_at, $now);
                if ($since_ai !== null && $since_ai < $min_silence) {
                    return array('ok' => false, 'reason' => 'recent_ai_post');
                }
            }

            $last_log_at = eottae_talkroom_ai_last_success_log_at($room_id, 'quiet_room');
            if ($last_log_at !== '') {
                $since_log = eottae_talkroom_ai_minutes_since($last_log_at, $now);
                if ($since_log !== null && $since_log < $min_silence) {
                    return array('ok' => false, 'reason' => 'recent_quiet_log');
                }
            }
        }

        if (!$bypass) {
            $context = eottae_talkroom_ai_evaluate_trigger_context($room_id, 'quiet_room', $now, array(
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

if (!function_exists('eottae_talkroom_ai_list_quiet_candidate_room_ids')) {
    /**
     * @return int[]
     */
    function eottae_talkroom_ai_list_quiet_candidate_room_ids($limit = 200)
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
              AND s.quiet_trigger_enabled = 1
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

if (!function_exists('eottae_talkroom_ai_run_quiet_trigger')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_run_quiet_trigger($room_id, $dry_run = false, $now = null, array $options = array())
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $now = $now ?: G5_TIME_YMDHIS;
        $is_test = !empty($options['is_test']);
        $trigger_type = $is_test ? 'admin_test' : 'quiet_room';
        $ca_name = $is_test ? 'AI·화제(테스트)' : 'AI·화제';
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
        $check = eottae_talkroom_ai_evaluate_quiet_room($room_id, $now, $options);
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

        $generated = eottae_talkroom_ai_generate_quiet_message($room, $settings);
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
            'message'   => 'AI 화제 게시글 등록 완료',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_run_quiet_cron')) {
    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_run_quiet_cron(array $options = array())
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
            : eottae_talkroom_ai_list_quiet_candidate_room_ids($limit);

        foreach ($room_ids as $id) {
            $summary['checked']++;
            $result = eottae_talkroom_ai_run_quiet_trigger($id, $dry_run, $now);
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

if (!function_exists('eottae_talkroom_ai_verify_cron_key')) {
    function eottae_talkroom_ai_verify_cron_key($provided_key)
    {
        if (!function_exists('g5site_cfg') && defined('G5_PATH') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }

        $expected = function_exists('g5site_cfg')
            ? trim((string) g5site_cfg('talkroom_ai_cron_key', ''))
            : '';

        if ($expected === '') {
            return php_sapi_name() === 'cli';
        }

        $provided_key = trim((string) $provided_key);

        return $provided_key !== '' && hash_equals($expected, $provided_key);
    }
}
