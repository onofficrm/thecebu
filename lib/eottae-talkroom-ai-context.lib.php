<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_context_proactive_triggers')) {
    /**
     * 회원 대화를 끊을 수 있는 선제적 AI 트리거
     *
     * @return string[]
     */
    function eottae_talkroom_ai_context_proactive_triggers()
    {
        return array('quiet_room', 'daily_question', 'meetup_suggest', 'summary');
    }
}

if (!function_exists('eottae_talkroom_ai_context_responsive_triggers')) {
    /**
     * @return string[]
     */
    function eottae_talkroom_ai_context_responsive_triggers()
    {
        return array('reaction', 'welcome');
    }
}

if (!function_exists('eottae_talkroom_ai_context_member_activity_window_minutes')) {
    function eottae_talkroom_ai_context_member_activity_window_minutes()
    {
        return 90;
    }
}

if (!function_exists('eottae_talkroom_ai_context_hot_window_minutes')) {
    function eottae_talkroom_ai_context_hot_window_minutes()
    {
        return 30;
    }
}

if (!function_exists('eottae_talkroom_ai_context_proactive_gap_minutes')) {
    function eottae_talkroom_ai_context_proactive_gap_minutes()
    {
        return 120;
    }
}

if (!function_exists('eottae_talkroom_ai_context_max_ai_share')) {
    /** AI 발언이 전체 대화에서 차지할 수 있는 최대 비율 */
    function eottae_talkroom_ai_context_max_ai_share()
    {
        return 0.35;
    }
}

if (!function_exists('eottae_talkroom_ai_context_count_member_activity_since')) {
    function eottae_talkroom_ai_context_count_member_activity_since($room_id, $since_datetime)
    {
        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $since_datetime = trim((string) $since_datetime);
        $write_table = eottae_talkroom_write_table();
        if ($room_id < 1 || $write_table === '' || $since_datetime === '') {
            return 0;
        }

        $since_sql = sql_escape_string($since_datetime);
        $visible_p = eottae_talkroom_post_visible_sql('p');
        $visible_c = eottae_talkroom_post_visible_sql('c');
        $bot_id = function_exists('eottae_talkroom_ai_bot_mb_id')
            ? sql_escape_string(eottae_talkroom_ai_bot_mb_id())
            : 'sebu_ai';

        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM (
                SELECT p.wr_id
                FROM `{$write_table}` p
                WHERE p.wr_is_comment = 0
                  AND p.wr_1 = '{$room_id}'
                  AND {$visible_p}
                  AND p.wr_datetime >= '{$since_sql}'
                  AND p.wr_3 NOT LIKE 'ai:%'
                  AND p.mb_id != '{$bot_id}'
                UNION ALL
                SELECT c.wr_id
                FROM `{$write_table}` c
                INNER JOIN `{$write_table}` p
                    ON c.wr_parent = p.wr_id AND c.wr_is_comment = 1
                WHERE p.wr_1 = '{$room_id}'
                  AND {$visible_p}
                  AND {$visible_c}
                  AND c.wr_datetime >= '{$since_sql}'
                  AND c.wr_3 NOT LIKE 'ai:%'
                  AND c.mb_id != '{$bot_id}'
            ) t
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_talkroom_ai_context_last_member_activity_at')) {
    function eottae_talkroom_ai_context_last_member_activity_at($room_id)
    {
        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $write_table = eottae_talkroom_write_table();
        if ($room_id < 1 || $write_table === '') {
            return '';
        }

        $visible_p = eottae_talkroom_post_visible_sql('p');
        $visible_c = eottae_talkroom_post_visible_sql('c');
        $bot_id = function_exists('eottae_talkroom_ai_bot_mb_id')
            ? sql_escape_string(eottae_talkroom_ai_bot_mb_id())
            : 'sebu_ai';

        $row = sql_fetch("
            SELECT MAX(t.activity_at) AS latest_at
            FROM (
                SELECT p.wr_datetime AS activity_at
                FROM `{$write_table}` p
                WHERE p.wr_is_comment = 0
                  AND p.wr_1 = '{$room_id}'
                  AND {$visible_p}
                  AND p.wr_3 NOT LIKE 'ai:%'
                  AND p.mb_id != '{$bot_id}'
                UNION ALL
                SELECT c.wr_datetime AS activity_at
                FROM `{$write_table}` c
                INNER JOIN `{$write_table}` p
                    ON c.wr_parent = p.wr_id AND c.wr_is_comment = 1
                WHERE p.wr_1 = '{$room_id}'
                  AND {$visible_p}
                  AND {$visible_c}
                  AND c.wr_3 NOT LIKE 'ai:%'
                  AND c.mb_id != '{$bot_id}'
            ) t
        ", false);

        return trim((string) ($row['latest_at'] ?? ''));
    }
}

if (!function_exists('eottae_talkroom_ai_context_last_proactive_ai_at')) {
    function eottae_talkroom_ai_context_last_proactive_ai_at($room_id)
    {
        $room_id = (int) $room_id;
        if (!function_exists('eottae_talkroom_ai_minutes_since')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-quiet.lib.php';
        }
        if (!function_exists('eottae_talkroom_ai_last_success_log_at')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-quiet.lib.php';
        }

        $latest = '';
        foreach (eottae_talkroom_ai_context_proactive_triggers() as $trigger) {
            $at = eottae_talkroom_ai_last_success_log_at($room_id, $trigger);
            if ($at !== '' && ($latest === '' || strtotime($at) > strtotime($latest))) {
                $latest = $at;
            }
        }

        return $latest;
    }
}

if (!function_exists('eottae_talkroom_ai_context_momentum_label')) {
    function eottae_talkroom_ai_context_momentum_label($member_hot, $member_window, $minutes_since_member)
    {
        if ($member_hot >= 2) {
            return 'hot';
        }
        if ($member_window >= 4) {
            return 'active';
        }
        if ($member_window >= 1 || ($minutes_since_member !== null && $minutes_since_member <= 180)) {
            return 'warm';
        }
        if ($minutes_since_member === null || $minutes_since_member >= 720) {
            return 'silent';
        }

        return 'quiet';
    }
}

if (!function_exists('eottae_talkroom_ai_build_room_context_snapshot')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_build_room_context_snapshot($room_id, $now = null)
    {
        $room_id = (int) $room_id;
        $now = $now ?: G5_TIME_YMDHIS;
        $target_date = substr($now, 0, 10);

        if (!function_exists('eottae_talkroom_ai_minutes_since')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-quiet.lib.php';
        }

        $hot_window = eottae_talkroom_ai_context_hot_window_minutes();
        $activity_window = eottae_talkroom_ai_context_member_activity_window_minutes();
        $hot_since = date('Y-m-d H:i:s', strtotime($now.' -'.$hot_window.' minutes'));
        $window_since = date('Y-m-d H:i:s', strtotime($now.' -'.$activity_window.' minutes'));

        $last_member_at = eottae_talkroom_ai_context_last_member_activity_at($room_id);
        $minutes_since_member = $last_member_at !== ''
            ? eottae_talkroom_ai_minutes_since($last_member_at, $now)
            : null;

        $member_hot = eottae_talkroom_ai_context_count_member_activity_since($room_id, $hot_since);
        $member_window = eottae_talkroom_ai_context_count_member_activity_since($room_id, $window_since);
        $member_today = eottae_talkroom_ai_room_member_activity_total($room_id, $target_date);
        $ai_today = eottae_talkroom_ai_get_today_message_count($room_id, $target_date);
        $total_today = $member_today + $ai_today;
        $ai_share = $total_today > 0 ? ($ai_today / $total_today) : 0.0;

        $latest_post = eottae_talkroom_ai_room_latest_post_row($room_id);
        $latest_post_is_ai = $latest_post && eottae_talkroom_ai_is_ai_write_row($latest_post);

        return array(
            'momentum'              => eottae_talkroom_ai_context_momentum_label($member_hot, $member_window, $minutes_since_member),
            'member_hot_count'      => $member_hot,
            'member_window_count'   => $member_window,
            'member_today_count'    => $member_today,
            'ai_today_count'        => $ai_today,
            'ai_share'              => $ai_share,
            'minutes_since_member'  => $minutes_since_member,
            'last_member_at'        => $last_member_at,
            'latest_post_is_ai'     => $latest_post_is_ai,
            'last_proactive_ai_at'  => eottae_talkroom_ai_context_last_proactive_ai_at($room_id),
        );
    }
}

if (!function_exists('eottae_talkroom_ai_reaction_classification_confidence')) {
    /**
     * @return array{ok:bool, level:string, reason:string}
     */
    function eottae_talkroom_ai_reaction_classification_confidence(array $classification, array $post)
    {
        $type = trim((string) ($classification['type'] ?? ''));
        $source = trim((string) ($classification['source'] ?? ''));

        if ($type === '' || $type === 'info') {
            return array('ok' => false, 'level' => 'low', 'reason' => 'weak_reaction_type');
        }

        $text_parts = function_exists('eottae_talkroom_ai_reaction_normalize_text')
            ? eottae_talkroom_ai_reaction_normalize_text($post)
            : array('plain' => '');
        $plain = trim((string) ($text_parts['plain'] ?? ''));
        if ($plain === '' || mb_strlen($plain) < 12) {
            return array('ok' => false, 'level' => 'low', 'reason' => 'post_too_short');
        }

        if ($type === 'question') {
            $has_question_mark = (bool) preg_match('/[?？]/u', $plain);
            if ($source === 'ca_name' || $has_question_mark) {
                return array('ok' => true, 'level' => 'high', 'reason' => 'question_signal');
            }

            return array('ok' => false, 'level' => 'low', 'reason' => 'question_signal_weak');
        }

        if (in_array($type, array('meetup', 'sale', 'intro'), true)) {
            if ($source === 'ca_name' || $source === 'keyword' || $source === 'intro_member') {
                return array('ok' => true, 'level' => 'high', 'reason' => $type.'_signal');
            }
        }

        return array('ok' => false, 'level' => 'low', 'reason' => 'context_unclear');
    }
}

if (!function_exists('eottae_talkroom_ai_post_has_ai_comment')) {
    function eottae_talkroom_ai_post_has_ai_comment($room_id, $post_id)
    {
        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $post_id = (int) $post_id;
        $write_table = eottae_talkroom_write_table();
        if ($room_id < 1 || $post_id < 1 || $write_table === '') {
            return false;
        }

        $visible = eottae_talkroom_post_visible_sql();
        $bot_id = function_exists('eottae_talkroom_ai_bot_mb_id')
            ? sql_escape_string(eottae_talkroom_ai_bot_mb_id())
            : 'sebu_ai';

        $row = sql_fetch("
            SELECT wr_id
            FROM `{$write_table}`
            WHERE wr_is_comment = 1
              AND wr_parent = '{$post_id}'
              AND {$visible}
              AND (wr_3 LIKE 'ai:%' OR mb_id = '{$bot_id}')
            LIMIT 1
        ", false);

        return !empty($row['wr_id']);
    }
}

if (!function_exists('eottae_talkroom_ai_evaluate_trigger_context')) {
    /**
     * 트리거별 맥락 적합성 — 횟수 한도와 별도로 '지금 말해야 하는가' 판단
     *
     * @return array{ok:bool, reason:string, snapshot?:array}
     */
    function eottae_talkroom_ai_evaluate_trigger_context($room_id, $trigger, $now = null, array $options = array())
    {
        $room_id = (int) $room_id;
        $trigger = trim((string) $trigger);
        $now = $now ?: G5_TIME_YMDHIS;
        $force = !empty($options['force']);
        $is_test = !empty($options['is_test']);

        if (($force && $is_test) || $trigger === 'admin_test') {
            return array('ok' => true, 'reason' => 'test_bypass');
        }

        if (!function_exists('eottae_talkroom_ai_minutes_since')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-quiet.lib.php';
        }

        $snapshot = eottae_talkroom_ai_build_room_context_snapshot($room_id, $now);
        $momentum = (string) ($snapshot['momentum'] ?? 'quiet');

        if ($snapshot['ai_share'] > eottae_talkroom_ai_context_max_ai_share()
            && (int) ($snapshot['member_today_count'] ?? 0) > 0) {
            return array('ok' => false, 'reason' => 'ai_share_too_high', 'snapshot' => $snapshot);
        }

        if (in_array($trigger, eottae_talkroom_ai_context_proactive_triggers(), true)) {
            if (in_array($momentum, array('hot', 'active'), true)) {
                return array('ok' => false, 'reason' => 'conversation_in_progress', 'snapshot' => $snapshot);
            }

            if ($trigger !== 'quiet_room' && $momentum === 'warm') {
                return array('ok' => false, 'reason' => 'members_recently_active', 'snapshot' => $snapshot);
            }

            $last_proactive = trim((string) ($snapshot['last_proactive_ai_at'] ?? ''));
            if ($last_proactive !== '') {
                $gap = eottae_talkroom_ai_minutes_since($last_proactive, $now);
                $min_gap = eottae_talkroom_ai_context_proactive_gap_minutes();
                if ($gap !== null && $gap < $min_gap) {
                    return array('ok' => false, 'reason' => 'proactive_cooldown', 'snapshot' => $snapshot);
                }
            }

            if ($trigger === 'daily_question' && (int) ($snapshot['member_today_count'] ?? 0) >= 8) {
                return array('ok' => false, 'reason' => 'room_already_engaged', 'snapshot' => $snapshot);
            }

            if ($trigger === 'meetup_suggest') {
                if ((int) ($snapshot['member_window_count'] ?? 0) >= 2) {
                    return array('ok' => false, 'reason' => 'meetup_context_busy', 'snapshot' => $snapshot);
                }
                if ($momentum === 'silent' && (int) ($snapshot['member_today_count'] ?? 0) < 1) {
                    return array('ok' => false, 'reason' => 'meetup_no_member_context', 'snapshot' => $snapshot);
                }
            }

            if ($trigger === 'summary' && (int) ($snapshot['member_today_count'] ?? 0) < 3) {
                return array('ok' => false, 'reason' => 'summary_insufficient_context', 'snapshot' => $snapshot);
            }

            return array('ok' => true, 'reason' => 'proactive_fit', 'snapshot' => $snapshot);
        }

        if ($trigger === 'reaction') {
            $post = isset($options['post']) && is_array($options['post']) ? $options['post'] : null;
            $classification = isset($options['classification']) && is_array($options['classification'])
                ? $options['classification']
                : null;

            if (!$post || !$classification) {
                return array('ok' => false, 'reason' => 'reaction_context_missing', 'snapshot' => $snapshot);
            }

            if (eottae_talkroom_ai_is_ai_write_row($post)) {
                return array('ok' => false, 'reason' => 'target_is_ai', 'snapshot' => $snapshot);
            }

            if (eottae_talkroom_ai_post_has_ai_comment($room_id, (int) ($post['wr_id'] ?? 0))) {
                return array('ok' => false, 'reason' => 'already_commented', 'snapshot' => $snapshot);
            }

            $confidence = eottae_talkroom_ai_reaction_classification_confidence($classification, $post);
            if (empty($confidence['ok'])) {
                return array('ok' => false, 'reason' => (string) ($confidence['reason'] ?? 'reaction_not_fit'), 'snapshot' => $snapshot);
            }

            if ($momentum === 'hot' && (int) ($post['wr_comment'] ?? 0) >= 2) {
                return array('ok' => false, 'reason' => 'thread_already_flowing', 'snapshot' => $snapshot);
            }

            return array('ok' => true, 'reason' => 'reaction_fit', 'snapshot' => $snapshot);
        }

        if ($trigger === 'welcome') {
            if (in_array($momentum, array('hot'), true)) {
                return array('ok' => false, 'reason' => 'welcome_busy_room', 'snapshot' => $snapshot);
            }

            return array('ok' => true, 'reason' => 'welcome_fit', 'snapshot' => $snapshot);
        }

        return array('ok' => true, 'reason' => 'no_context_rule', 'snapshot' => $snapshot);
    }
}

if (!function_exists('eottae_talkroom_ai_validate_generated_message')) {
    /**
     * 생성된 AI 메시지 맥락·안전성 최종 검증
     *
     * @return array{ok:bool, reason:string}
     */
    function eottae_talkroom_ai_validate_generated_message($subject, $content, array $options = array())
    {
        $subject = trim(strip_tags((string) $subject));
        $content = trim(strip_tags((string) $content));
        $combined = trim($subject."\n".$content);

        if ($combined === '') {
            return array('ok' => false, 'reason' => 'empty_message');
        }

        if (function_exists('eottae_talkroom_ai_detect_sensitive_content')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-safety.lib.php';
            $sensitive = eottae_talkroom_ai_detect_sensitive_content($combined);
            if (!empty($sensitive['hit'])) {
                return array('ok' => false, 'reason' => 'sensitive_content');
            }
        }

        return array('ok' => true, 'reason' => 'ok');
    }
}

if (!function_exists('eottae_talkroom_ai_context_momentum_label_ko')) {
    function eottae_talkroom_ai_context_momentum_label_ko($momentum)
    {
        $map = array(
            'hot'    => '대화 활발',
            'active' => '대화 진행 중',
            'warm'   => '최근 대화 있음',
            'quiet'  => '한산함',
            'silent' => '조용함',
        );

        return isset($map[$momentum]) ? $map[$momentum] : '보통';
    }
}
