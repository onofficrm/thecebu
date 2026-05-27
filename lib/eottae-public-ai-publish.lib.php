<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_ai_publish_load_dependencies')) {
    function eottae_public_ai_publish_load_dependencies()
    {
        include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
        if (!function_exists('eottae_talkroom_public_group_room_id')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-public-chat.lib.php';
        }
        if (!function_exists('eottae_talkroom_ai_bot_mb_id')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
        }
        if (!function_exists('eottae_talkroom_ai_minutes_since')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-quiet.lib.php';
        }
    }
}

if (!function_exists('eottae_public_ai_publish_bot_display_name')) {
    function eottae_public_ai_publish_bot_display_name(array $settings = array())
    {
        if (!$settings) {
            $settings = eottae_public_ai_get_settings();
        }
        $name = trim((string) ($settings['ai_name'] ?? '어때봇'));
        if ($name === '') {
            $name = '어때봇';
        }

        return $name;
    }
}

if (!function_exists('eottae_public_ai_count_public_chat_published_today')) {
    function eottae_public_ai_count_public_chat_published_today($now = null)
    {
        eottae_public_ai_publish_load_dependencies();

        $table = eottae_public_ai_candidates_table();
        $day_start = eottae_public_ai_day_start_datetime(eottae_public_ai_today_ymd($now));
        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$table}`
            WHERE status = 'published'
              AND published_at >= '".sql_escape_string($day_start)."'
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_public_ai_count_business_event_published_today')) {
    function eottae_public_ai_count_business_event_published_today($now = null)
    {
        eottae_public_ai_publish_load_dependencies();
        $table = eottae_public_ai_candidates_table();
        $day_start = eottae_public_ai_day_start_datetime(eottae_public_ai_today_ymd($now));
        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$table}`
            WHERE status = 'published'
              AND trigger_type = 'business_event'
              AND published_at >= '".sql_escape_string($day_start)."'
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_public_ai_public_chat_latest_is_ai')) {
    function eottae_public_ai_public_chat_latest_is_ai()
    {
        eottae_public_ai_publish_load_dependencies();

        $room_id = (int) eottae_talkroom_public_group_room_id();
        if ($room_id < 1 || !function_exists('eottae_talkroom_ai_room_latest_post_row')) {
            return false;
        }

        $latest = eottae_talkroom_ai_room_latest_post_row($room_id);

        return $latest && function_exists('eottae_talkroom_ai_is_ai_write_row')
            && eottae_talkroom_ai_is_ai_write_row($latest);
    }
}

if (!function_exists('eottae_public_ai_evaluate_publish_eligibility')) {
    /**
     * @return array{ok:bool, reason:string}
     */
    function eottae_public_ai_evaluate_publish_eligibility(array $candidate, array $settings = array(), array $options = array())
    {
        eottae_public_ai_publish_load_dependencies();

        if (!$settings) {
            $settings = eottae_public_ai_get_settings();
        }

        $now = isset($options['now']) ? (string) $options['now'] : (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $force = !empty($options['force']);
        $is_admin_publish = !empty($options['is_admin_publish']);

        $slot_broadcast = !empty($options['slot_broadcast']);

        if (empty($settings['ai_enabled']) && !$slot_broadcast && !$force) {
            return array('ok' => false, 'reason' => 'ai_disabled');
        }

        $status = trim((string) ($candidate['status'] ?? ''));
        if ($status === 'published') {
            return array('ok' => false, 'reason' => 'already_published');
        }
        if (in_array($status, array('rejected', 'deleted'), true)) {
            return array('ok' => false, 'reason' => 'invalid_status');
        }

        $message = trim((string) ($candidate['message'] ?? ''));
        if ($message === '') {
            return array('ok' => false, 'reason' => 'empty_message');
        }

        if (!$force && !$is_admin_publish && !$slot_broadcast) {
            if (!eottae_public_ai_is_within_active_hours($settings, $now)) {
                return array('ok' => false, 'reason' => 'outside_active_hours');
            }
        }

        if (!$slot_broadcast) {
            $max_per_day = max(1, (int) ($settings['max_messages_per_day'] ?? 3));
            $published_today = eottae_public_ai_count_public_chat_published_today($now);
            if ($published_today >= $max_per_day) {
                return array('ok' => false, 'reason' => 'daily_publish_limit');
            }
        }

        if (!$force && !$is_admin_publish && !$slot_broadcast && eottae_public_ai_public_chat_latest_is_ai()) {
            return array('ok' => false, 'reason' => 'latest_message_is_ai');
        }

        $trigger_type = trim((string) ($candidate['trigger_type'] ?? ''));
        if ($trigger_type === 'business_event') {
            $biz_count = eottae_public_ai_count_business_event_published_today($now);
            if ($biz_count >= 1) {
                return array('ok' => false, 'reason' => 'business_event_daily_limit');
            }
        }

        if (!empty($settings['require_admin_approval']) && $status === 'pending' && empty($options['allow_pending_publish'])) {
            return array('ok' => false, 'reason' => 'approval_required');
        }

        if (!empty($candidate['force_admin_approval']) && empty($options['allow_pending_publish']) && empty($is_admin_publish)) {
            return array('ok' => false, 'reason' => 'force_admin_approval');
        }

        if (!empty($candidate['is_sensitive']) && empty($is_admin_publish)) {
            return array('ok' => false, 'reason' => 'sensitive_requires_admin');
        }

        if ($trigger_type === 'external_news' && empty($is_admin_publish)) {
            return array('ok' => false, 'reason' => 'external_news_admin_only');
        }

        if (!$force && !$is_admin_publish) {
            if (function_exists('eottae_public_ai_public_chat_consecutive_ai_count')
                && eottae_public_ai_public_chat_consecutive_ai_count(3) >= 2) {
                return array('ok' => false, 'reason' => 'consecutive_ai_limit');
            }
        }

        if (function_exists('eottae_public_ai_guard_scan_text')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-guard.lib.php';
            $scan = eottae_public_ai_guard_scan_text($message);
            if (!empty($scan['is_sensitive']) && empty($is_admin_publish)) {
                return array('ok' => false, 'reason' => 'sensitive_content');
            }
        }

        return array('ok' => true, 'reason' => 'eligible');
    }
}

if (!function_exists('eottae_talkroom_public_group_send_ai_message')) {
    /**
     * 홈 공개톡 — AI 메시지 등록 (어때봇)
     *
     * @return array{ok:bool, message:string, wr_id?:int, message_row?:array|null}
     */
    function eottae_talkroom_public_group_send_ai_message($room_id, $content, array $options = array())
    {
        global $g5;

        eottae_public_ai_publish_load_dependencies();

        $room_id = (int) $room_id;
        $public_room_id = (int) eottae_talkroom_public_group_room_id();
        if ($room_id < 1 || $room_id !== $public_room_id) {
            return array('ok' => false, 'message' => '공개 단체톡방이 아닙니다.');
        }

        if (!function_exists('eottae_talkroom_public_group_ensure_provisioned')) {
            return array('ok' => false, 'message' => '공개톡방을 준비할 수 없습니다.');
        }

        eottae_talkroom_public_group_ensure_provisioned();

        $settings = isset($options['settings']) && is_array($options['settings'])
            ? $options['settings']
            : eottae_public_ai_get_settings();

        $ai_name = eottae_public_ai_publish_bot_display_name($settings);
        $bot = eottae_talkroom_ai_ensure_bot_member($ai_name);
        if (empty($bot['ok'])) {
            return array('ok' => false, 'message' => $bot['message'] ?? 'AI 계정 준비에 실패했습니다.');
        }

        $member = eottae_talkroom_ai_get_bot_member();
        if (!$member || empty($member['mb_id'])) {
            return array('ok' => false, 'message' => 'AI 계정을 찾을 수 없습니다.');
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $member['mb_id']);
        $content = trim(strip_tags((string) $content));
        if ($content === '') {
            return array('ok' => false, 'message' => '메시지 내용이 비어 있습니다.');
        }
        if (function_exists('mb_strlen') && mb_strlen($content, 'UTF-8') > 500) {
            return array('ok' => false, 'message' => '메시지는 500자 이내로 작성해 주세요.');
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        if (!$member_row || ($member_row['status'] ?? '') !== 'active') {
            $join = eottae_talkroom_join_room($room_id, $mb_id);
            if (empty($join['ok']) && ($join['message'] ?? '') !== '이미 참여 중인 톡방입니다.') {
                return array('ok' => false, 'message' => $join['message'] ?? 'AI 계정 참여에 실패했습니다.');
            }
        }

        $trigger_type = trim((string) ($options['trigger_type'] ?? 'public_chat'));
        $trigger_type = preg_replace('/[^a-z0-9_]/', '', $trigger_type);
        if ($trigger_type === '') {
            $trigger_type = 'public_chat';
        }

        $action_url = trim((string) ($options['action_url'] ?? ''));
        if ($action_url !== '' && !preg_match('#^https?://#i', $action_url)) {
            $action_url = '';
        }
        $action_label = trim(strip_tags((string) ($options['action_label'] ?? '')));
        $candidate_id = max(0, (int) ($options['candidate_id'] ?? 0));

        $write_table = eottae_talkroom_write_table();
        $bo_table = preg_replace('/[^a-z0-9_]/', '', eottae_talkroom_board_table());
        if ($write_table === '' || $bo_table === '') {
            return array('ok' => false, 'message' => '게시판 설정을 찾을 수 없습니다.');
        }

        $subject = function_exists('cut_str') ? cut_str($content, 40, '…') : mb_substr($content, 0, 40, 'UTF-8');
        $subject_sql = sql_escape_string($subject);
        $content_sql = sql_escape_string($content);
        $mb_id_sql = sql_escape_string($mb_id);
        $wr_name_sql = sql_escape_string($ai_name);
        $wr_email_sql = sql_escape_string($member['mb_email'] ?? ($mb_id.'@ai.local'));
        $wr_3 = sql_escape_string('ai:public:'.$trigger_type);
        $wr_link1_sql = sql_escape_string($action_url);
        $wr_link2_sql = sql_escape_string($action_label);
        $wr_4_sql = $candidate_id > 0 ? "'{$candidate_id}'" : "''";
        $seo = sql_escape_string(preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($subject)));

        sql_query(" INSERT INTO `{$write_table}` SET
            wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM `{$write_table}` AS sq),
            wr_reply = '',
            wr_comment = 0,
            ca_name = '한마디',
            wr_option = '',
            wr_subject = '{$subject_sql}',
            wr_content = '{$content_sql}',
            wr_seo_title = '{$seo}',
            wr_link1 = '{$wr_link1_sql}',
            wr_link2 = '{$wr_link2_sql}',
            wr_link1_hit = 0,
            wr_link2_hit = 0,
            wr_hit = 0,
            wr_good = 0,
            wr_nogood = 0,
            mb_id = '{$mb_id_sql}',
            wr_password = '',
            wr_name = '{$wr_name_sql}',
            wr_email = '{$wr_email_sql}',
            wr_homepage = '',
            wr_datetime = '".G5_TIME_YMDHIS."',
            wr_last = '".G5_TIME_YMDHIS."',
            wr_ip = '127.0.0.1',
            wr_1 = '{$room_id}',
            wr_2 = '',
            wr_3 = '{$wr_3}',
            wr_4 = {$wr_4_sql},
            wr_5 = '',
            wr_6 = '',
            wr_7 = '',
            wr_8 = '',
            wr_9 = '',
            wr_10 = '' ", false);

        $wr_id = (int) sql_insert_id();
        if ($wr_id < 1) {
            return array('ok' => false, 'message' => '공개톡 메시지 등록에 실패했습니다.');
        }

        sql_query(" UPDATE `{$write_table}` SET wr_parent = '{$wr_id}' WHERE wr_id = '{$wr_id}' ", false);
        sql_query(" INSERT INTO {$g5['board_new_table']}
            (bo_table, wr_id, wr_parent, bn_datetime, mb_id)
            VALUES ('{$bo_table}', '{$wr_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', '{$mb_id_sql}') ", false);
        sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '{$bo_table}' ", false);

        if (function_exists('delete_cache_latest')) {
            delete_cache_latest($bo_table);
        }

        $row = sql_fetch(" SELECT wr_id, wr_subject, wr_content, wr_name, wr_datetime, mb_id, wr_3, wr_1, wr_link1, wr_link2
            FROM `{$write_table}` WHERE wr_id = '{$wr_id}' LIMIT 1 ");

        return array(
            'ok'          => true,
            'message'     => '공개톡에 발행했습니다.',
            'wr_id'       => $wr_id,
            'message_row' => $row ? eottae_talkroom_public_group_format_message($row, '') : null,
        );
    }
}

if (!function_exists('eottae_public_ai_mark_candidate_published')) {
    function eottae_public_ai_mark_candidate_published($candidate_id, $chat_message_id = 0, $admin_mb_id = '')
    {
        eottae_public_ai_ensure_schema();
        $candidate_id = (int) $candidate_id;
        $chat_message_id = max(0, (int) $chat_message_id);
        if ($candidate_id < 1) {
            return false;
        }

        $table = eottae_public_ai_candidates_table();
        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');

        return (bool) sql_query("
            UPDATE `{$table}` SET
                status = 'published',
                published_at = '{$now}',
                updated_at = '{$now}',
                approved_by = IF(approved_by = '', '".sql_escape_string($admin_mb_id)."', approved_by),
                approved_at = IF(approved_at = '0000-00-00 00:00:00', '{$now}', approved_at)
            WHERE candidate_id = '{$candidate_id}'
            LIMIT 1
        ", false);
    }
}

if (!function_exists('eottae_public_ai_publish_candidate')) {
    /**
     * 후보 메시지를 홈 공개톡에 발행
     *
     * @return array{ok:bool, message:string, wr_id?:int, reason?:string}
     */
    function eottae_public_ai_publish_candidate($candidate_id, $admin_mb_id = '', array $options = array())
    {
        global $is_admin;

        eottae_public_ai_publish_load_dependencies();

        if ($is_admin !== 'super' && empty($options['cron'])) {
            return array('ok' => false, 'message' => '권한이 없습니다.');
        }

        $candidate_id = (int) $candidate_id;
        $candidate = eottae_public_ai_get_candidate($candidate_id);
        if (!$candidate) {
            return array('ok' => false, 'message' => '후보 메시지를 찾을 수 없습니다.');
        }

        $settings = eottae_public_ai_get_settings();
        $force = !empty($options['force']);
        $is_admin_publish = $is_admin === 'super' || !empty($options['cron']);

        if ($candidate['status'] === 'pending' && $is_admin_publish) {
            eottae_public_ai_set_candidate_status($candidate_id, 'approved', $admin_mb_id);
            $candidate = eottae_public_ai_get_candidate($candidate_id);
        }

        $eligibility = eottae_public_ai_evaluate_publish_eligibility($candidate, $settings, array(
            'now'                   => isset($options['now']) ? $options['now'] : null,
            'force'                 => $force,
            'is_admin_publish'      => $is_admin_publish,
            'allow_pending_publish' => $is_admin_publish,
        ));

        if (empty($eligibility['ok'])) {
            $reason = (string) ($eligibility['reason'] ?? 'not_eligible');
            eottae_public_ai_insert_log(array(
                'candidate_id'    => $candidate_id,
                'trigger_type'    => $candidate['trigger_type'],
                'message'         => $candidate['message'],
                'publish_status'  => 'failed',
                'chat_message_id' => 0,
                'error_message'   => $reason,
            ));

            return array(
                'ok'      => false,
                'message' => eottae_public_ai_publish_reason_message($reason),
                'reason'  => $reason,
            );
        }

        if (empty($options['slot_broadcast'])
            && eottae_public_ai_has_similar_published_message($candidate['message'])) {
            eottae_public_ai_insert_log(array(
                'candidate_id'    => $candidate_id,
                'trigger_type'    => $candidate['trigger_type'],
                'message'         => $candidate['message'],
                'publish_status'  => 'failed',
                'chat_message_id' => 0,
                'error_message'   => 'similar_published',
            ));

            return array('ok' => false, 'message' => '최근 발행된 메시지와 유사해 발행하지 않았습니다.', 'reason' => 'similar_published');
        }

        $publish_message = $candidate['message'];
        if (!empty($candidate['poll_options']) && function_exists('eottae_public_ai_poll_append_to_message')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-poll.lib.php';
            $publish_message = eottae_public_ai_poll_append_to_message($publish_message, $candidate['poll_options']);
        }

        $room_id = (int) eottae_talkroom_public_group_room_id();
        $send = eottae_talkroom_public_group_send_ai_message($room_id, $publish_message, array(
            'trigger_type'  => $candidate['trigger_type'],
            'action_label'  => $candidate['action_label'],
            'action_url'    => $candidate['action_url'],
            'candidate_id'  => $candidate_id,
            'settings'      => $settings,
        ));

        if (empty($send['ok'])) {
            eottae_public_ai_insert_log(array(
                'candidate_id'    => $candidate_id,
                'trigger_type'    => $candidate['trigger_type'],
                'message'         => $candidate['message'],
                'publish_status'  => 'failed',
                'chat_message_id' => 0,
                'error_message'   => (string) ($send['message'] ?? 'send_failed'),
            ));

            return array(
                'ok'      => false,
                'message' => (string) ($send['message'] ?? '공개톡 발행에 실패했습니다.'),
                'reason'  => 'send_failed',
            );
        }

        $wr_id = (int) ($send['wr_id'] ?? 0);
        eottae_public_ai_mark_candidate_published($candidate_id, $wr_id, $admin_mb_id);
        eottae_public_ai_insert_log(array(
            'candidate_id'    => $candidate_id,
            'trigger_type'    => $candidate['trigger_type'],
            'message'         => $candidate['message'],
            'publish_status'  => 'success',
            'chat_message_id' => $wr_id,
            'error_message'   => '',
        ));

        return array(
            'ok'      => true,
            'message' => '공개톡에 발행했습니다.',
            'wr_id'   => $wr_id,
        );
    }
}

if (!function_exists('eottae_public_ai_publish_reason_message')) {
    function eottae_public_ai_publish_reason_message($reason)
    {
        $map = array(
            'ai_disabled'               => 'AI가 비활성화되어 발행할 수 없습니다.',
            'already_published'         => '이미 발행된 후보 메시지입니다.',
            'invalid_status'            => '반려·삭제된 후보는 발행할 수 없습니다.',
            'empty_message'             => '메시지 내용이 비어 있습니다.',
            'outside_active_hours'      => 'AI 활동 시간이 아닙니다.',
            'daily_publish_limit'       => '오늘 발행 한도에 도달했습니다.',
            'latest_message_is_ai'      => '공개톡 최근 메시지가 AI입니다. 잠시 후 다시 시도해 주세요.',
            'business_event_daily_limit'=> '업체 이벤트는 하루 1건만 발행할 수 있습니다.',
            'approval_required'         => '관리자 승인이 필요합니다.',
            'similar_published'         => '최근 발행된 메시지와 유사합니다.',
            'send_failed'               => '공개톡 전송에 실패했습니다.',
            'force_admin_approval'      => '관리자 확인이 필요한 후보입니다.',
            'sensitive_requires_admin'  => '민감 키워드가 포함되어 관리자 발행만 가능합니다.',
            'sensitive_content'         => '민감 키워드가 포함되어 자동 발행할 수 없습니다.',
            'external_news_admin_only'  => '외부뉴스는 관리자 승인 후 발행만 가능합니다.',
            'consecutive_ai_limit'      => 'AI 메시지가 연속으로 올라가 최근에는 발행하지 않습니다.',
        );

        return isset($map[$reason]) ? $map[$reason] : '발행 조건을 충족하지 않습니다.';
    }
}

if (!function_exists('eottae_public_ai_list_publishable_approved_candidates')) {
    function eottae_public_ai_list_publishable_approved_candidates($limit = 10)
    {
        eottae_public_ai_ensure_schema();
        $table = eottae_public_ai_candidates_table();
        $limit = max(1, min(50, (int) $limit));
        $rows = array();

        $result = sql_query("
            SELECT *
            FROM `{$table}`
            WHERE status = 'approved'
            ORDER BY approved_at ASC, candidate_id ASC
            LIMIT {$limit}
        ", false);

        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $rows[] = eottae_public_ai_format_candidate_row($row);
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_public_ai_run_publish_approved')) {
    function eottae_public_ai_run_publish_approved(array $options = array())
    {
        eottae_public_ai_publish_load_dependencies();
        eottae_public_ai_ensure_schema();

        $settings = eottae_public_ai_get_settings();
        $dry_run = !empty($options['dry_run']);
        $force = !empty($options['force']);

        $result = array(
            'ok'            => true,
            'reason'        => '',
            'published'     => 0,
            'skipped'       => 0,
            'candidate_ids' => array(),
            'wr_ids'        => array(),
        );

        if (empty($settings['ai_enabled'])) {
            $result['ok'] = false;
            $result['reason'] = 'ai_disabled';

            return $result;
        }

        $candidates = eottae_public_ai_list_publishable_approved_candidates(20);
        if (!$candidates) {
            $result['reason'] = 'no_approved_candidates';

            return $result;
        }

        $max_per_day = max(1, (int) ($settings['max_messages_per_day'] ?? 3));
        $remaining = $max_per_day - eottae_public_ai_count_public_chat_published_today();

        foreach ($candidates as $candidate) {
            if ($result['published'] >= $remaining) {
                break;
            }

            if ($dry_run) {
                $check = eottae_public_ai_evaluate_publish_eligibility($candidate, $settings, array(
                    'force'                 => $force,
                    'allow_pending_publish' => false,
                ));
                if (empty($check['ok'])) {
                    $result['skipped']++;
                    continue;
                }
                $result['published']++;
                continue;
            }

            $publish = eottae_public_ai_publish_candidate((int) $candidate['candidate_id'], '', array(
                'cron'  => true,
                'force' => $force,
            ));

            if (!empty($publish['ok'])) {
                $result['published']++;
                $result['candidate_ids'][] = (int) $candidate['candidate_id'];
                $result['wr_ids'][] = (int) ($publish['wr_id'] ?? 0);
                continue;
            }

            $result['skipped']++;
        }

        if ($result['published'] < 1 && $result['reason'] === '') {
            $result['reason'] = 'all_skipped';
        }

        return $result;
    }
}

if (!function_exists('eottae_public_ai_maybe_auto_publish_candidate')) {
    function eottae_public_ai_maybe_auto_publish_candidate($candidate_id, array $settings = array())
    {
        eottae_public_ai_publish_load_dependencies();

        if (!$settings) {
            $settings = eottae_public_ai_get_settings();
        }

        if (empty($settings['auto_publish']) || !empty($settings['require_admin_approval'])) {
            return array('ok' => false, 'message' => 'auto_publish_off');
        }

        $candidate = eottae_public_ai_get_candidate((int) $candidate_id);
        if (!$candidate || !function_exists('eottae_public_ai_guard_can_auto_publish')
            || !eottae_public_ai_guard_can_auto_publish($candidate)) {
            return array('ok' => false, 'message' => 'auto_publish_blocked');
        }

        return eottae_public_ai_publish_candidate((int) $candidate_id, '', array('cron' => true));
    }
}
