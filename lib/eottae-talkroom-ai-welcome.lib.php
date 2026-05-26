<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_welcome_rejoin_cooldown_days')) {
    function eottae_talkroom_ai_welcome_rejoin_cooldown_days()
    {
        return 7;
    }
}

if (!function_exists('eottae_talkroom_ai_welcome_room_gap_seconds')) {
    function eottae_talkroom_ai_welcome_room_gap_seconds()
    {
        return 120;
    }
}

if (!function_exists('eottae_talkroom_ai_welcome_member_prompt_key')) {
    function eottae_talkroom_ai_welcome_member_prompt_key($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);

        return 'member:'.$mb_id;
    }
}

if (!function_exists('eottae_talkroom_ai_welcome_templates')) {
    /**
     * @return array<string, string>
     */
    function eottae_talkroom_ai_welcome_templates()
    {
        return array(
            'expat_life' => '{nick}님, 세부톡방에 오신 걸 환영합니다 😊 세부 거주 중이신가요, 여행 준비 중이신가요? 편하게 한 줄 소개 남겨주세요.',
            'parenting'  => '{nick}님, 맘수다방에 오신 걸 환영합니다 😊 아이 나이대나 궁금한 생활정보가 있으면 편하게 남겨주세요.',
            'sports'     => '{nick}님, 스포츠 톡방에 오신 걸 환영합니다 ⚽ 가능한 요일이나 선호 지역을 댓글로 남겨주시면 모임 잡을 때 참고할게요.',
            'travel'     => '{nick}님, 세부 여행자 질문방에 오신 걸 환영합니다 ✈️ 여행 일정이나 궁금한 점을 남겨주시면 함께 정보 나눠볼게요.',
            'business'   => '{nick}님, 사업자방에 오신 걸 환영합니다. 어떤 업종이신지, 요즘 가장 고민되는 부분이 무엇인지 편하게 이야기해주세요.',
            'used'       => '{nick}님, 중고거래 톡방에 오신 걸 환영합니다 😊 필요한 물건이나 나누고 싶은 소식을 편하게 남겨주세요.',
            'job'        => '{nick}님, 구인구직 톡방에 오신 걸 환영합니다. 찾고 계신 일이나 경력을 한 줄로 소개해 주세요.',
            'estate'     => '{nick}님, 부동산 톡방에 오신 걸 환영합니다. 관심 지역이나 찾으시는 조건을 편하게 알려주세요.',
            'food'       => '{nick}님, 맛집·카페 톡방에 오신 걸 환영합니다 ☕ 자주 가는 곳이나 추천하고 싶은 메뉴를 남겨주세요.',
            'hobby'      => '{nick}님, 취미·모임 톡방에 오신 걸 환영합니다 🎉 관심 있는 활동이나 함께하고 싶은 모임 주제를 알려주세요.',
            'etc'        => '{nick}님, 세부톡방에 오신 걸 환영합니다 😊 편하게 한 줄 자기소개 남겨주세요.',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_welcome_category_key')) {
    function eottae_talkroom_ai_welcome_category_key(array $room)
    {
        if (!function_exists('eottae_talkroom_ai_daily_question_category_key')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-daily-question.lib.php';
        }

        return eottae_talkroom_ai_daily_question_category_key($room);
    }
}

if (!function_exists('eottae_talkroom_ai_generate_welcome_via_api')) {
    /**
     * @return array<string, string>|null
     */
    function eottae_talkroom_ai_generate_welcome_via_api(array $room, array $settings, $mb_id, $mb_nick)
    {
        return null;
    }
}

if (!function_exists('eottae_talkroom_ai_generate_welcome_from_template')) {
    function eottae_talkroom_ai_generate_welcome_from_template(array $room, array $settings, $mb_id, $mb_nick)
    {
        $templates = eottae_talkroom_ai_welcome_templates();
        $category_key = eottae_talkroom_ai_welcome_category_key($room);
        $template = isset($templates[$category_key]) ? $templates[$category_key] : $templates['etc'];

        $nick = trim(strip_tags((string) $mb_nick));
        if ($nick === '') {
            $nick = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        }
        if ($nick === '') {
            $nick = '새 회원';
        }

        $content = str_replace('{nick}', $nick, $template);
        $tone = trim((string) ($settings['ai_tone'] ?? 'friendly'));
        if ($tone === 'brief') {
            $content = cut_str(preg_replace('/\s+/u', ' ', $content), 140, '…');
        }

        return array(
            'content'      => $content,
            'prompt_text'  => 'template:'.$category_key.'|member:'.$mb_id,
            'category_key' => $category_key,
        );
    }
}

if (!function_exists('eottae_talkroom_ai_generate_welcome_message')) {
    function eottae_talkroom_ai_generate_welcome_message(array $room, array $settings, $mb_id, $mb_nick)
    {
        $api_message = eottae_talkroom_ai_generate_welcome_via_api($room, $settings, $mb_id, $mb_nick);
        if (is_array($api_message) && !empty($api_message['content'])) {
            return array(
                'content'      => (string) $api_message['content'],
                'prompt_text'  => (string) ($api_message['prompt_text'] ?? 'api|member:'.$mb_id),
                'category_key' => (string) ($api_message['category_key'] ?? 'api'),
            );
        }

        return eottae_talkroom_ai_generate_welcome_from_template($room, $settings, $mb_id, $mb_nick);
    }
}

if (!function_exists('eottae_talkroom_ai_has_recent_welcome_for_member')) {
    function eottae_talkroom_ai_has_recent_welcome_for_member($room_id, $mb_id, $days = null)
    {
        $room_id = (int) $room_id;
        $days = $days === null ? eottae_talkroom_ai_welcome_rejoin_cooldown_days() : max(1, (int) $days);
        $tables = eottae_talkroom_ai_table_names();
        $prompt_key = eottae_talkroom_ai_welcome_member_prompt_key($mb_id);

        if (!eottae_talkroom_ai_table_exists($tables['logs'])) {
            return false;
        }

        $since = date('Y-m-d H:i:s', G5_SERVER_TIME - ($days * 86400));
        $row = sql_fetch("
            SELECT log_id
            FROM `{$tables['logs']}`
            WHERE room_id = '{$room_id}'
              AND trigger_type = 'welcome'
              AND status = 'success'
              AND prompt_text = '".sql_escape_string($prompt_key)."'
              AND created_at >= '".sql_escape_string($since)."'
            LIMIT 1
        ", false);

        return !empty($row['log_id']);
    }
}

if (!function_exists('eottae_talkroom_ai_has_pending_welcome_for_member')) {
    function eottae_talkroom_ai_has_pending_welcome_for_member($room_id, $mb_id)
    {
        $room_id = (int) $room_id;
        $tables = eottae_talkroom_ai_table_names();
        $prompt_key = eottae_talkroom_ai_welcome_member_prompt_key($mb_id);

        if (!eottae_talkroom_ai_table_exists($tables['logs'])) {
            return false;
        }

        $row = sql_fetch("
            SELECT log_id
            FROM `{$tables['logs']}`
            WHERE room_id = '{$room_id}'
              AND trigger_type = 'welcome'
              AND status = 'pending'
              AND prompt_text = '".sql_escape_string($prompt_key)."'
            LIMIT 1
        ", false);

        return !empty($row['log_id']);
    }
}

if (!function_exists('eottae_talkroom_ai_room_last_welcome_success_at')) {
    function eottae_talkroom_ai_room_last_welcome_success_at($room_id)
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
              AND trigger_type = 'welcome'
              AND status = 'success'
            ORDER BY log_id DESC
            LIMIT 1
        ", false);

        return trim((string) ($row['created_at'] ?? ''));
    }
}

if (!function_exists('eottae_talkroom_ai_should_welcome_member')) {
    function eottae_talkroom_ai_should_welcome_member($room_id, $mb_id)
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($room_id < 1 || $mb_id === '') {
            return false;
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room || $mb_id === ($room['owner_mb_id'] ?? '')) {
            return false;
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        if (!$member_row || ($member_row['status'] ?? '') !== 'active') {
            return false;
        }

        if (function_exists('eottae_talkroom_is_kicked_status') && eottae_talkroom_is_kicked_status($member_row['status'] ?? '')) {
            return false;
        }

        if (($member_row['role'] ?? '') === 'owner') {
            return false;
        }

        return true;
    }
}

if (!function_exists('eottae_talkroom_ai_evaluate_welcome')) {
    /**
     * @return array{ok:bool, reason:string}
     */
    function eottae_talkroom_ai_evaluate_welcome($room_id, $mb_id, $now = null)
    {
        $now = $now ?: G5_TIME_YMDHIS;
        $room_id = (int) $room_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);

        if (!eottae_talkroom_ai_should_welcome_member($room_id, $mb_id)) {
            return array('ok' => false, 'reason' => 'member_not_eligible');
        }

        $settings = eottae_talkroom_ai_get_settings($room_id);
        $shared = eottae_talkroom_ai_evaluate_shared_limits($room_id, $now, array(
            'skip_consecutive' => true,
        ));
        if (empty($shared['ok'])) {
            return array('ok' => false, 'reason' => $shared['reason']);
        }

        if (empty($settings['welcome_enabled'])) {
            return array('ok' => false, 'reason' => 'welcome_disabled');
        }
        if (!eottae_talkroom_ai_is_within_active_hours($settings, $now)) {
            return array('ok' => false, 'reason' => 'outside_active_hours');
        }

        if (eottae_talkroom_ai_has_recent_welcome_for_member($room_id, $mb_id)) {
            return array('ok' => false, 'reason' => 'already_welcomed_recently');
        }

        $last_room_welcome = eottae_talkroom_ai_room_last_welcome_success_at($room_id);
        if ($last_room_welcome !== '') {
            $gap = eottae_talkroom_ai_welcome_room_gap_seconds();
            $elapsed = G5_SERVER_TIME - strtotime($last_room_welcome);
            if ($elapsed >= 0 && $elapsed < $gap) {
                return array('ok' => false, 'reason' => 'room_rate_limited');
            }
        }

        $context = eottae_talkroom_ai_evaluate_trigger_context($room_id, 'welcome', $now, array());
        if (empty($context['ok'])) {
            return array('ok' => false, 'reason' => $context['reason']);
        }

        return array('ok' => true, 'reason' => 'eligible');
    }
}

if (!function_exists('eottae_talkroom_ai_schedule_welcome')) {
    function eottae_talkroom_ai_schedule_welcome($room_id, $mb_id)
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        eottae_talkroom_ai_ensure_schema();

        $room_id = (int) $room_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($room_id < 1 || $mb_id === '' || !eottae_talkroom_ai_should_welcome_member($room_id, $mb_id)) {
            return array('ok' => false, 'message' => '환영 대상이 아닙니다.');
        }

        if (eottae_talkroom_ai_has_pending_welcome_for_member($room_id, $mb_id)) {
            eottae_talkroom_ai_process_welcome_queue($room_id, 1);

            return array('ok' => true, 'message' => 'already_queued');
        }

        if (eottae_talkroom_ai_has_recent_welcome_for_member($room_id, $mb_id)) {
            return array('ok' => false, 'message' => 'already_welcomed_recently');
        }

        eottae_talkroom_ai_write_log($room_id, 'welcome', array(
            'status'      => 'pending',
            'prompt_text' => eottae_talkroom_ai_welcome_member_prompt_key($mb_id),
        ));

        return eottae_talkroom_ai_process_welcome_queue($room_id, 1);
    }
}

if (!function_exists('eottae_talkroom_ai_run_welcome_for_member')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_run_welcome_for_member($room_id, $mb_id, $log_id = 0)
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $log_id = (int) $log_id;
        $now = G5_TIME_YMDHIS;

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array('ok' => false, 'status' => 'skipped', 'reason' => 'not_operating', 'message' => '운영 중인 톡방이 아닙니다.');
        }

        $check = eottae_talkroom_ai_evaluate_welcome($room_id, $mb_id, $now);
        if (empty($check['ok'])) {
            if (($check['reason'] ?? '') === 'room_rate_limited') {
                return array(
                    'ok'      => false,
                    'status'  => 'pending',
                    'reason'  => 'room_rate_limited',
                    'message' => '방별 환영 간격 대기 중',
                );
            }

            if ($log_id > 0) {
                sql_query("
                    UPDATE `".eottae_talkroom_ai_table_names()['logs']."` SET
                        status = 'skipped',
                        error_message = '".sql_escape_string($check['reason'])."'
                    WHERE log_id = '{$log_id}'
                ", false);
            } else {
                eottae_talkroom_ai_write_log($room_id, 'welcome', array(
                    'status'        => 'skipped',
                    'prompt_text'   => eottae_talkroom_ai_welcome_member_prompt_key($mb_id),
                    'error_message' => $check['reason'],
                ));
            }

            return array(
                'ok'      => false,
                'status'  => 'skipped',
                'reason'  => $check['reason'],
                'message' => '조건 미충족: '.$check['reason'],
            );
        }

        $settings = eottae_talkroom_ai_get_settings($room_id);
        $target = get_member($mb_id, 'mb_nick');
        $mb_nick = !empty($target['mb_nick']) ? $target['mb_nick'] : $mb_id;
        $generated = eottae_talkroom_ai_generate_welcome_message($room, $settings, $mb_id, $mb_nick);

        $hub = eottae_talkroom_ai_ensure_welcome_hub_post($room_id, $settings['ai_name'] ?? '어때봇');
        if (empty($hub['ok']) || empty($hub['wr_id'])) {
            $fail_msg = $hub['message'] ?? '환영 게시글 생성 실패';
            if ($log_id > 0) {
                sql_query("
                    UPDATE `".eottae_talkroom_ai_table_names()['logs']."` SET
                        status = 'failed',
                        error_message = '".sql_escape_string($fail_msg)."'
                    WHERE log_id = '{$log_id}'
                ", false);
            }

            return array('ok' => false, 'status' => 'failed', 'reason' => 'hub_failed', 'message' => $fail_msg);
        }

        $insert = eottae_talkroom_ai_insert_comment(
            $room_id,
            (int) $hub['wr_id'],
            $generated['content'],
            array(
                'ai_name'        => $settings['ai_name'] ?? '어때봇',
                'trigger_type'   => 'welcome',
                'target_mb_id'   => $mb_id,
                'bump_parent_last' => false,
            )
        );

        if (empty($insert['ok'])) {
            if ($log_id > 0) {
                sql_query("
                    UPDATE `".eottae_talkroom_ai_table_names()['logs']."` SET
                        status = 'failed',
                        prompt_text = '".sql_escape_string($generated['prompt_text'])."',
                        response_text = '".sql_escape_string($generated['content'])."',
                        error_message = '".sql_escape_string($insert['message'])."'
                    WHERE log_id = '{$log_id}'
                ", false);
            }

            return array('ok' => false, 'status' => 'failed', 'reason' => 'insert_failed', 'message' => $insert['message']);
        }

        eottae_talkroom_ai_increment_daily_count($room_id, $now);

        $log_data = array(
            'status'        => 'success',
            'prompt_text'   => eottae_talkroom_ai_welcome_member_prompt_key($mb_id),
            'response_text' => $generated['content'],
            'post_id'       => (int) ($insert['post_id'] ?? 0),
            'comment_id'    => (int) ($insert['comment_id'] ?? 0),
        );

        if ($log_id > 0) {
            $tables = eottae_talkroom_ai_table_names();
            sql_query("
                UPDATE `{$tables['logs']}` SET
                    status = 'success',
                    prompt_text = '".sql_escape_string($log_data['prompt_text'])."',
                    response_text = '".sql_escape_string($log_data['response_text'])."',
                    post_id = '".(int) $log_data['post_id']."',
                    comment_id = '".(int) $log_data['comment_id']."',
                    error_message = ''
                WHERE log_id = '{$log_id}'
            ", false);
        } else {
            eottae_talkroom_ai_write_log($room_id, 'welcome', $log_data);
        }

        return array(
            'ok'         => true,
            'status'     => 'success',
            'reason'     => 'posted',
            'message'    => '환영 메시지 등록 완료',
            'room_id'    => $room_id,
            'mb_id'      => $mb_id,
            'post_id'    => (int) ($insert['post_id'] ?? 0),
            'comment_id' => (int) ($insert['comment_id'] ?? 0),
            'content'    => $generated['content'],
        );
    }
}

if (!function_exists('eottae_talkroom_ai_list_pending_welcome_logs')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_ai_list_pending_welcome_logs($room_id = 0, $limit = 50)
    {
        $tables = eottae_talkroom_ai_table_names();
        if (!eottae_talkroom_ai_table_exists($tables['logs'])) {
            return array();
        }

        $limit = max(1, min(200, (int) $limit));
        $where = " trigger_type = 'welcome' AND status = 'pending' ";
        if ($room_id > 0) {
            $where .= " AND room_id = '".(int) $room_id."' ";
        }

        $result = sql_query("
            SELECT log_id, room_id, prompt_text, created_at
            FROM `{$tables['logs']}`
            WHERE {$where}
            ORDER BY log_id ASC
            LIMIT {$limit}
        ", false);

        $rows = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_talkroom_ai_process_welcome_queue')) {
    function eottae_talkroom_ai_process_welcome_queue($room_id = 0, $limit = 5)
    {
        $limit = max(1, min(20, (int) $limit));
        $logs = eottae_talkroom_ai_list_pending_welcome_logs($room_id, $limit);
        $results = array();

        foreach ($logs as $log) {
            $prompt = trim((string) ($log['prompt_text'] ?? ''));
            $mb_id = '';
            if (strpos($prompt, 'member:') === 0) {
                $mb_id = substr($prompt, 7);
            }
            if ($mb_id === '') {
                continue;
            }

            $result = eottae_talkroom_ai_run_welcome_for_member(
                (int) $log['room_id'],
                $mb_id,
                (int) $log['log_id']
            );
            $results[] = $result;

            if (($result['status'] ?? '') === 'pending' && ($result['reason'] ?? '') === 'room_rate_limited') {
                break;
            }
        }

        return array(
            'processed' => count($results),
            'results'   => $results,
        );
    }
}

if (!function_exists('eottae_talkroom_ai_run_welcome_cron')) {
    function eottae_talkroom_ai_run_welcome_cron(array $options = array())
    {
        eottae_talkroom_ai_ensure_schema();

        $room_id = isset($options['room_id']) ? (int) $options['room_id'] : 0;
        $limit = isset($options['limit']) ? (int) $options['limit'] : 20;

        $queue = eottae_talkroom_ai_process_welcome_queue($room_id, $limit);
        $summary = array(
            'processed' => (int) ($queue['processed'] ?? 0),
            'success'   => 0,
            'skipped'   => 0,
            'failed'    => 0,
        );

        foreach ($queue['results'] as $row) {
            if (($row['status'] ?? '') === 'success') {
                $summary['success']++;
            } elseif (($row['status'] ?? '') === 'failed') {
                $summary['failed']++;
            } else {
                $summary['skipped']++;
            }
        }

        return array(
            'summary' => $summary,
            'results' => $queue['results'],
        );
    }
}
