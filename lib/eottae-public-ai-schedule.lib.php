<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_ai_schedule_load_dependencies')) {
    function eottae_public_ai_schedule_load_dependencies()
    {
        include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
        include_once G5_LIB_PATH.'/eottae-public-ai-generator.lib.php';
        include_once G5_LIB_PATH.'/eottae-public-ai-publish.lib.php';
        if (is_file(G5_LIB_PATH.'/eottae-public-ai-weather.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-weather.lib.php';
        }
    }
}

if (!function_exists('eottae_public_ai_publish_slots')) {
    /**
     * 공개단톡 정기 발송 슬롯 (서버 시간대 = config.php Asia/Seoul, 세부와 동일 UTC+8)
     *
     * @return array<string, array<string, mixed>>
     */
    function eottae_public_ai_publish_slots()
    {
        return array(
            'morning' => array(
                'label' => '아침',
                'start' => '07:00:00',
                'end'   => '08:59:59',
            ),
            'noon' => array(
                'label' => '점심',
                'start' => '12:00:00',
                'end'   => '13:59:59',
            ),
            'evening' => array(
                'label' => '저녁',
                'start' => '18:00:00',
                'end'   => '19:59:59',
            ),
            'midnight' => array(
                'label' => '자정',
                'start' => '23:00:00',
                'end'   => '00:59:59',
                'wrap'  => true,
            ),
        );
    }
}

if (!function_exists('eottae_public_ai_slot_trigger_priorities')) {
    function eottae_public_ai_slot_trigger_priorities($slot_key)
    {
        $map = array(
            'morning' => array(
                'weather',
                'calendar_today',
                'holiday',
                'external_news',
                'popular_post',
                'talk_room_activity',
                'business_event',
                'calendar_tomorrow',
                'quiet_chat',
                'scheduled_slot',
            ),
            'noon' => array(
                'calendar_today',
                'popular_post',
                'weather',
                'business_event',
                'talk_room_activity',
                'external_news',
                'holiday',
                'calendar_tomorrow',
                'quiet_chat',
                'scheduled_slot',
            ),
            'evening' => array(
                'calendar_tomorrow',
                'talk_room_activity',
                'popular_post',
                'weather',
                'business_event',
                'calendar_today',
                'external_news',
                'holiday',
                'quiet_chat',
                'scheduled_slot',
            ),
            'midnight' => array(
                'calendar_tomorrow',
                'calendar_day_after',
                'holiday',
                'external_news',
                'weather',
                'quiet_chat',
                'popular_post',
                'talk_room_activity',
                'business_event',
                'scheduled_slot',
            ),
        );

        return isset($map[$slot_key]) ? $map[$slot_key] : $map['noon'];
    }
}

if (!function_exists('eottae_public_ai_is_time_in_slot_range')) {
    function eottae_public_ai_is_time_in_slot_range($current_hms, $start, $end, $wrap = false)
    {
        $current_hms = eottae_public_ai_normalize_time($current_hms);
        $start = eottae_public_ai_normalize_time($start);
        $end = eottae_public_ai_normalize_time($end);

        if (!$wrap) {
            return $current_hms >= $start && $current_hms <= $end;
        }

        return $current_hms >= $start || $current_hms <= $end;
    }
}

if (!function_exists('eottae_public_ai_detect_publish_slot')) {
    function eottae_public_ai_detect_publish_slot($now = null)
    {
        $now = $now ?: (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $current = date('H:i:s', strtotime($now));

        foreach (eottae_public_ai_publish_slots() as $key => $slot) {
            $wrap = !empty($slot['wrap']);
            if (eottae_public_ai_is_time_in_slot_range($current, $slot['start'], $slot['end'], $wrap)) {
                return $key;
            }
        }

        return '';
    }
}

if (!function_exists('eottae_public_ai_slot_admin_memo')) {
    function eottae_public_ai_slot_admin_memo($slot_key)
    {
        $slot_key = preg_replace('/[^a-z0-9_]/', '', (string) $slot_key);

        return 'slot:'.$slot_key;
    }
}

if (!function_exists('eottae_public_ai_slot_published_today')) {
    function eottae_public_ai_slot_published_today($slot_key, $now = null)
    {
        eottae_public_ai_ensure_schema();
        $slot_key = preg_replace('/[^a-z0-9_]/', '', (string) $slot_key);
        if ($slot_key === '') {
            return false;
        }

        $table = eottae_public_ai_candidates_table();
        $day_start = eottae_public_ai_day_start_datetime(eottae_public_ai_today_ymd($now));
        $memo = eottae_public_ai_slot_admin_memo($slot_key);
        $row = sql_fetch("
            SELECT candidate_id
            FROM `{$table}`
            WHERE status = 'published'
              AND admin_memo = '".sql_escape_string($memo)."'
              AND published_at >= '".sql_escape_string($day_start)."'
            LIMIT 1
        ", false);

        return !empty($row['candidate_id']);
    }
}

if (!function_exists('eottae_public_ai_sort_candidates_for_slot')) {
    function eottae_public_ai_sort_candidates_for_slot(array $candidates, $slot_key)
    {
        $priorities = eottae_public_ai_slot_trigger_priorities($slot_key);
        $rank = array();
        foreach ($priorities as $idx => $trigger) {
            $rank[$trigger] = $idx;
        }

        usort($candidates, function ($a, $b) use ($rank) {
            $ta = trim((string) ($a['trigger_type'] ?? ''));
            $tb = trim((string) ($b['trigger_type'] ?? ''));
            $ra = isset($rank[$ta]) ? (int) $rank[$ta] : 999;
            $rb = isset($rank[$tb]) ? (int) $rank[$tb] : 999;
            if ($ra === $rb) {
                return 0;
            }

            return ($ra < $rb) ? -1 : 1;
        });

        return $candidates;
    }
}

if (!function_exists('eottae_public_ai_sync_weather_for_ai')) {
    function eottae_public_ai_sync_weather_for_ai($now = null)
    {
        if (!function_exists('eottae_public_ai_weather_fetch_from_api')
            || !function_exists('eottae_public_ai_weather_get_for_date')) {
            return array('ok' => false, 'synced' => 0);
        }

        $now = $now ?: (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $today = substr($now, 0, 10);
        $tomorrow = date('Y-m-d', strtotime($today.' +1 day'));
        $synced = 0;

        foreach (array($today, $tomorrow) as $date) {
            $existing = eottae_public_ai_weather_get_for_date($date);
            if ($existing && ($existing['source'] ?? '') === 'open-meteo') {
                $updated = strtotime((string) ($existing['updated_at'] ?? ''));
                if ($updated > 0 && $updated > strtotime('-6 hours')) {
                    continue;
                }
            }
            $fetched = eottae_public_ai_weather_fetch_from_api($date);
            if ($fetched) {
                $synced++;
            }
        }

        return array('ok' => true, 'synced' => $synced);
    }
}

if (!function_exists('eottae_public_ai_build_slot_fallback_candidate')) {
    function eottae_public_ai_build_slot_fallback_candidate($slot_key, array $sources)
    {
        $slots = eottae_public_ai_publish_slots();
        $label = isset($slots[$slot_key]['label']) ? $slots[$slot_key]['label'] : '정기';
        $today = isset($sources['today']) ? (string) $sources['today'] : date('Y-m-d');
        $calendar_today = isset($sources['calendar']['today']) && is_array($sources['calendar']['today'])
            ? $sources['calendar']['today'] : array();
        $calendar_tomorrow = isset($sources['calendar']['tomorrow']) && is_array($sources['calendar']['tomorrow'])
            ? $sources['calendar']['tomorrow'] : array();
        $event_count = count($calendar_today) + count($calendar_tomorrow);

        $popular_title = '';
        if (!empty($sources['popular']['hit'][0]['title'])) {
            $popular_title = trim((string) $sources['popular']['hit'][0]['title']);
        } elseif (!empty($sources['popular']['comment'][0]['title'])) {
            $popular_title = trim((string) $sources['popular']['comment'][0]['title']);
        }

        $weather_line = '';
        if (!empty($sources['weather'][$today]['weather_summary'])) {
            $weather_line = '오늘 세부 날씨: '.trim((string) $sources['weather'][$today]['weather_summary']);
        }

        $calendar_url = function_exists('eottae_calendar_list_url') ? eottae_calendar_list_url() : G5_URL.'/calendar/';
        $community_url = G5_URL.'/community/';

        $message = '';
        $action_url = G5_URL.'/';
        $action_label = '세부어때 보기';
        if ($slot_key === 'morning') {
            $message = '좋은 아침이에요 ☀️ 세부어때 공개단톡입니다.'."\n";
            if ($weather_line !== '') {
                $message .= $weather_line."\n";
            }
            if ($event_count > 0) {
                $message .= '오늘·내일 세부 일정이 '.$event_count.'건 등록되어 있어요. 캘린더에서 확인해보세요.'."\n";
            }
            $message .= '오늘 세부에서 계획하시는 일정이나 궁금한 점 있으면 편하게 남겨주세요.';
            if ($event_count > 0) {
                $action_url = $calendar_url;
                $action_label = '오늘 일정 보기';
            }
        } elseif ($slot_key === 'noon') {
            $message = '점심 시간이에요 🍽️ 세부어때에서 오늘의 소식을 전해드려요.'."\n";
            if ($event_count > 0) {
                $message .= '오늘 세부 일정 '.$event_count.'건 — 캘린더에서 시간 맞춰 보시면 좋아요.'."\n";
            }
            if ($popular_title !== '') {
                $message .= '커뮤니티에서 요즘 화제: 「'.$popular_title.'」'."\n";
            }
            $message .= '맛집·병원·렌트·모임 정보 궁금하시면 언제든 물어보세요.';
            if ($popular_title !== '') {
                $action_url = $community_url;
                $action_label = '커뮤니티 보기';
            } elseif ($event_count > 0) {
                $action_url = $calendar_url;
                $action_label = '오늘 일정 보기';
            }
        } elseif ($slot_key === 'evening') {
            $message = '저녁 시간입니다 🌙 세부어때 공개단톡이에요.'."\n";
            if (count($calendar_tomorrow) > 0) {
                $first = $calendar_tomorrow[0];
                $title = trim((string) ($first['title'] ?? ''));
                if ($title !== '') {
                    $message .= '내일은 「'.$title.'」 일정이 있어요. 미리 준비해보세요.'."\n";
                }
            }
            if ($popular_title !== '') {
                $message .= '오늘 커뮤니티 인기글: '.$popular_title."\n";
            }
            $message .= '오늘 하루 세부 생활은 어떠셨나요? 후기나 팁을 나눠주시면 도움이 됩니다.';
            if (count($calendar_tomorrow) > 0) {
                $action_url = $calendar_url;
                $action_label = '내일 일정 보기';
            }
        } else {
            $message = '자정이 가까워졌어요 🌙 세부어때입니다.'."\n";
            if (count($calendar_tomorrow) > 0) {
                $message .= '내일 세부 일정을 미리 확인해보세요.'."\n";
            }
            $message .= '내일 계획이나 궁금한 점은 아침에 다시 이야기 나눠요. 편안한 밤 되세요.';
            if (count($calendar_tomorrow) > 0) {
                $action_url = $calendar_url;
                $action_label = '내일 일정 보기';
            }
        }

        $slot_ids = array('morning' => 1, 'noon' => 2, 'evening' => 3, 'midnight' => 4);

        return array(
            'trigger_type' => 'scheduled_slot',
            'source_type'  => 'manual',
            'source_id'    => isset($slot_ids[$slot_key]) ? (int) $slot_ids[$slot_key] : 9,
            'title'        => $label.' 안내',
            'message'      => $message,
            'action_label' => $action_label,
            'action_url'   => $action_url,
            'admin_memo'   => eottae_public_ai_slot_admin_memo($slot_key),
        );
    }
}

if (!function_exists('eottae_public_ai_apply_schedule_friendly_settings')) {
    /**
     * 정기 슬롯(하루 4회) 운영에 맞게 설정을 보정 (기존 보수적 기본값 → 슬롯 운영값)
     */
    function eottae_public_ai_apply_schedule_friendly_settings()
    {
        eottae_public_ai_ensure_schema();
        $table = eottae_public_ai_settings_table();
        $row = sql_fetch(" SELECT * FROM `{$table}` WHERE id = 1 LIMIT 1 ", false);
        if (empty($row['id'])) {
            return array('ok' => false, 'updated' => false);
        }

        $needs = false;
        $max = (int) ($row['max_messages_per_day'] ?? 3);
        if ($max < 4) {
            $needs = true;
        }
        $end = eottae_public_ai_normalize_time($row['active_end_time'] ?? '', '22:00:00');
        if ($end === '22:00:00' || $end < '23:00:00') {
            $needs = true;
        }
        $start = eottae_public_ai_normalize_time($row['active_start_time'] ?? '', '08:00:00');
        if ($start > '07:00:00') {
            $needs = true;
        }
        if (empty($row['use_weather'])) {
            $needs = true;
        }
        if (empty($row['ai_enabled'])) {
            $needs = true;
        }
        if (empty($row['auto_publish']) || !empty($row['require_admin_approval'])) {
            $needs = true;
        }

        if (!$needs) {
            return array('ok' => true, 'updated' => false);
        }

        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        $ok = (bool) sql_query("
            UPDATE `{$table}` SET
                ai_enabled = '1',
                max_messages_per_day = GREATEST(max_messages_per_day, 4),
                active_start_time = '07:00:00',
                active_end_time = '23:59:59',
                use_weather = '1',
                auto_publish = '1',
                require_admin_approval = '0',
                updated_at = '{$now}'
            WHERE id = 1
            LIMIT 1
        ", false);

        return array('ok' => $ok, 'updated' => $ok);
    }
}

if (!function_exists('eottae_public_ai_run_slot_broadcast')) {
    /**
     * 정기 슬롯 1회 — 후보 생성 후 즉시 발행 (슬롯당 하루 1건)
     *
     * @param array<string, mixed> $options slot, force, dry_run, now
     */
    function eottae_public_ai_run_slot_broadcast(array $options = array())
    {
        eottae_public_ai_schedule_load_dependencies();
        eottae_public_ai_ensure_schema();
        eottae_public_ai_apply_schedule_friendly_settings();

        $settings = eottae_public_ai_get_settings();
        $now = isset($options['now']) ? (string) $options['now'] : (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $force = !empty($options['force']);
        $dry_run = !empty($options['dry_run']);
        $slot_key = isset($options['slot']) ? preg_replace('/[^a-z0-9_]/', '', (string) $options['slot']) : '';

        $result = array(
            'ok'            => true,
            'reason'        => '',
            'slot'          => '',
            'published'     => 0,
            'candidate_id'  => 0,
            'wr_id'         => 0,
            'used_fallback' => false,
        );

        if ($slot_key === '') {
            $slot_key = eottae_public_ai_detect_publish_slot($now);
        }
        $result['slot'] = $slot_key;

        if ($slot_key === '') {
            $result['ok'] = false;
            $result['reason'] = 'outside_slot_window';

            return $result;
        }

        if (empty($settings['ai_enabled']) && !$force) {
            $result['ok'] = false;
            $result['reason'] = 'ai_disabled';

            return $result;
        }

        if (eottae_public_ai_slot_published_today($slot_key, $now) && !$force) {
            $result['ok'] = false;
            $result['reason'] = 'slot_already_published';

            return $result;
        }

        $calendar_sync = eottae_public_ai_sync_calendar_before_slot(array('dry_run' => $dry_run));
        $result['calendar_sync'] = $calendar_sync;

        eottae_public_ai_sync_weather_for_ai($now);

        $slot_settings = $settings;
        $slot_settings['use_weather'] = 1;

        $sources = eottae_public_ai_collect_sources($slot_settings, $now);
        $candidates = eottae_public_ai_generate_candidates($sources);
        $candidates = eottae_public_ai_sort_candidates_for_slot($candidates, $slot_key);

        $memo = eottae_public_ai_slot_admin_memo($slot_key);
        $save_opts = array(
            'now'            => $now,
            'skip_dedup'     => true,
            'slot_broadcast' => true,
        );

        $try_publish = function (array $candidate) use ($dry_run, $save_opts, $memo, $force, $now, &$result) {
            $candidate['admin_memo'] = $memo;
            if ($dry_run) {
                $result['published'] = 1;
                $result['reason'] = 'dry_run';

                return true;
            }

            $save = eottae_public_ai_save_candidate_from_generator($candidate, $save_opts);
            if (empty($save['ok']) || (int) ($save['candidate_id'] ?? 0) < 1) {
                return false;
            }

            $candidate_id = (int) $save['candidate_id'];
            $publish = eottae_public_ai_publish_candidate($candidate_id, '', array(
                'cron'           => true,
                'force'          => $force,
                'slot_broadcast' => true,
                'now'            => $now,
            ));

            if (!empty($publish['ok'])) {
                $result['published'] = 1;
                $result['candidate_id'] = $candidate_id;
                $result['wr_id'] = (int) ($publish['wr_id'] ?? 0);
                $result['reason'] = 'published';

                return true;
            }

            return false;
        };

        foreach ($candidates as $candidate) {
            if (!is_array($candidate) || trim((string) ($candidate['message'] ?? '')) === '') {
                continue;
            }
            if ($try_publish($candidate)) {
                return $result;
            }
        }

        $fallback = eottae_public_ai_build_slot_fallback_candidate($slot_key, $sources);
        $result['used_fallback'] = true;
        if ($try_publish($fallback)) {
            return $result;
        }

        $result['ok'] = false;
        $result['reason'] = 'publish_failed';
        if (!$dry_run && function_exists('eottae_public_ai_notify_admin_slot_issue')) {
            eottae_public_ai_notify_admin_slot_issue('publish_failed', array(
                'slot'   => $slot_key,
                'reason' => 'publish_failed',
                'now'    => $now,
            ));
        }

        return $result;
    }
}

if (!function_exists('eottae_public_ai_slot_time_range_label')) {
    function eottae_public_ai_slot_time_range_label($slot_key)
    {
        $slots = eottae_public_ai_publish_slots();
        if (!isset($slots[$slot_key])) {
            return '';
        }
        $slot = $slots[$slot_key];
        $start = substr(eottae_public_ai_normalize_time($slot['start']), 0, 5);
        $end = substr(eottae_public_ai_normalize_time($slot['end']), 0, 5);

        return $start.'~'.$end;
    }
}

if (!function_exists('eottae_public_ai_slot_is_past_deadline')) {
    function eottae_public_ai_slot_is_past_deadline($slot_key, $now = null)
    {
        $slots = eottae_public_ai_publish_slots();
        if (!isset($slots[$slot_key])) {
            return false;
        }

        $now = $now ?: (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $current = date('H:i:s', strtotime($now));
        $slot = $slots[$slot_key];

        if (empty($slot['wrap'])) {
            return $current > eottae_public_ai_normalize_time($slot['end']);
        }

        return $current >= '01:00:00' && $current < '23:00:00';
    }
}

if (!function_exists('eottae_public_ai_slot_row_state')) {
    function eottae_public_ai_slot_row_state($slot_key, $now = null)
    {
        $now = $now ?: (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        if (eottae_public_ai_slot_published_today($slot_key, $now)) {
            return 'published';
        }
        if (eottae_public_ai_slot_is_past_deadline($slot_key, $now)) {
            return 'missed';
        }
        if (eottae_public_ai_detect_publish_slot($now) === $slot_key) {
            return 'active';
        }

        return 'pending';
    }
}

if (!function_exists('eottae_public_ai_traffic_tick_config')) {
    function eottae_public_ai_traffic_tick_config()
    {
        if (!function_exists('g5site_cfg') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }

        $enabled = function_exists('g5site_cfg') ? g5site_cfg_bool('public_ai_traffic_tick_enabled', true) : true;
        $interval = function_exists('g5site_cfg') ? (int) g5site_cfg('public_ai_traffic_tick_interval', '90') : 90;
        $grace = function_exists('g5site_cfg') ? (int) g5site_cfg('public_ai_traffic_grace_minutes', '45') : 45;

        return array(
            'enabled'  => $enabled,
            'interval' => max(30, min(600, $interval)),
            'grace'    => max(15, min(180, $grace)),
        );
    }
}

if (!function_exists('eottae_public_ai_traffic_tick_lock_path')) {
    function eottae_public_ai_traffic_tick_lock_path()
    {
        $dir = G5_DATA_PATH.'/cache';
        if (!is_dir($dir)) {
            @mkdir($dir, G5_DIR_PERMISSION, true);
        }

        return $dir.'/public_ai_traffic_tick.lock';
    }
}

if (!function_exists('eottae_public_ai_traffic_tick_try_lock')) {
    function eottae_public_ai_traffic_tick_try_lock($interval_seconds = 90)
    {
        $path = eottae_public_ai_traffic_tick_lock_path();
        $fp = @fopen($path, 'c+');
        if (!$fp) {
            return false;
        }

        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            fclose($fp);

            return false;
        }

        $last = 0;
        $raw = stream_get_contents($fp);
        if ($raw !== false && trim($raw) !== '') {
            $last = (int) trim($raw);
        }
        $now = time();
        if ($last > 0 && ($now - $last) < max(30, (int) $interval_seconds)) {
            flock($fp, LOCK_UN);
            fclose($fp);

            return false;
        }

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, (string) $now);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }
}

if (!function_exists('eottae_public_ai_slot_deadline_timestamp')) {
    function eottae_public_ai_slot_deadline_timestamp($slot_key, $now = null)
    {
        $slots = eottae_public_ai_publish_slots();
        if (!isset($slots[$slot_key])) {
            return 0;
        }

        $now = $now ?: (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $ts = strtotime($now);
        if ($ts === false) {
            return 0;
        }

        $ymd = date('Y-m-d', $ts);
        $slot = $slots[$slot_key];
        if (!empty($slot['wrap'])) {
            $current = date('H:i:s', $ts);
            if ($current <= eottae_public_ai_normalize_time($slot['end'])) {
                return strtotime($ymd.' '.eottae_public_ai_normalize_time($slot['end']));
            }

            return strtotime($ymd.' '.eottae_public_ai_normalize_time($slot['end']));
        }

        return strtotime($ymd.' '.eottae_public_ai_normalize_time($slot['end']));
    }
}

if (!function_exists('eottae_public_ai_slot_in_traffic_grace')) {
    function eottae_public_ai_slot_in_traffic_grace($slot_key, $now = null, $grace_minutes = null)
    {
        if (eottae_public_ai_slot_published_today($slot_key, $now)) {
            return false;
        }

        $cfg = eottae_public_ai_traffic_tick_config();
        if ($grace_minutes === null) {
            $grace_minutes = (int) $cfg['grace'];
        }

        $now = $now ?: (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $ts = strtotime($now);
        if ($ts === false) {
            return false;
        }

        if (eottae_public_ai_detect_publish_slot($now) === $slot_key) {
            return true;
        }

        if (!eottae_public_ai_slot_is_past_deadline($slot_key, $now)) {
            return false;
        }

        $deadline = eottae_public_ai_slot_deadline_timestamp($slot_key, $now);
        if ($deadline < 1) {
            return false;
        }

        return $ts <= ($deadline + ((int) $grace_minutes * 60));
    }
}

if (!function_exists('eottae_public_ai_find_traffic_due_slot')) {
    function eottae_public_ai_find_traffic_due_slot($now = null)
    {
        $now = $now ?: (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $order = array('morning', 'noon', 'evening', 'midnight');

        foreach ($order as $slot_key) {
            if (eottae_public_ai_slot_in_traffic_grace($slot_key, $now)) {
                return $slot_key;
            }
        }

        return '';
    }
}

if (!function_exists('eottae_public_ai_web_cron_urls')) {
    /**
     * 서버 crontab 없이 외부 스케줄러(cron-job.org 등)에서 호출할 URL
     *
     * @return array<string, string>
     */
    function eottae_public_ai_web_cron_urls()
    {
        if (!function_exists('g5site_cfg') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }

        $key = function_exists('g5site_cfg') ? trim((string) g5site_cfg('public_ai_cron_key', '')) : '';
        if ($key === '' && function_exists('g5site_cfg')) {
            $key = trim((string) g5site_cfg('talkroom_ai_cron_key', ''));
        }

        $base = defined('G5_URL') ? G5_URL : '';
        $urls = array(
            'traffic_tick' => $base.'/proc/eottae-public-ai-traffic-tick.php',
            'slot_broadcast' => $base.'/cron/sebu_public_ai_slot_broadcast.php',
            'health_monitor' => $base.'/cron/sebu_public_ai_health_monitor.php',
        );

        if ($key !== '') {
            foreach ($urls as $name => $url) {
                $urls[$name] = $url.'?key='.rawurlencode($key);
            }
        }

        return $urls;
    }
}

if (!function_exists('eottae_public_ai_maybe_run_traffic_slot_broadcast')) {
    /**
     * 일반 호스팅 대안 — 홈 공개톡 폴링·페이지 방문 시 슬롯 발송 시도
     *
     * @param array<string, mixed> $options source, dry_run
     */
    function eottae_public_ai_maybe_run_traffic_slot_broadcast(array $options = array())
    {
        eottae_public_ai_schedule_load_dependencies();
        eottae_public_ai_ensure_schema();

        $cfg = eottae_public_ai_traffic_tick_config();
        $source = trim((string) ($options['source'] ?? 'traffic'));
        $dry_run = !empty($options['dry_run']);
        $now = isset($options['now']) ? (string) $options['now'] : (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));

        $result = array(
            'ok'        => true,
            'ran'       => false,
            'reason'    => 'disabled',
            'source'    => $source,
            'slot'      => '',
            'published' => 0,
        );

        if (empty($cfg['enabled']) && empty($options['force'])) {
            return $result;
        }

        $settings = eottae_public_ai_get_settings();
        if (empty($settings['ai_enabled']) && empty($options['force'])) {
            $result['reason'] = 'ai_disabled';

            return $result;
        }

        $slot_key = eottae_public_ai_find_traffic_due_slot($now);
        if ($slot_key === '') {
            $result['reason'] = 'no_due_slot';

            return $result;
        }
        $result['slot'] = $slot_key;

        if (!$dry_run && !eottae_public_ai_traffic_tick_try_lock((int) $cfg['interval'])) {
            $result['reason'] = 'throttled';

            return $result;
        }

        if ($dry_run) {
            $result['ran'] = true;
            $result['reason'] = 'dry_run';

            return $result;
        }

        $broadcast = eottae_public_ai_run_slot_broadcast(array(
            'slot'    => $slot_key,
            'now'     => $now,
            'traffic' => true,
        ));

        $result['ran'] = true;
        $result['reason'] = (string) ($broadcast['reason'] ?? '');
        $result['published'] = (int) ($broadcast['published'] ?? 0);
        $result['ok'] = !empty($broadcast['ok']) || ($broadcast['reason'] ?? '') === 'slot_already_published';

        eottae_public_ai_insert_log(array(
            'candidate_id'   => (int) ($broadcast['candidate_id'] ?? 0),
            'trigger_type'   => 'traffic_tick',
            'message'        => 'traffic:'.$source.':'.$slot_key,
            'publish_status' => $result['published'] > 0 ? 'success' : 'skipped',
            'error_message'  => $result['reason'],
        ));

        return $result;
    }
}

if (!function_exists('eottae_public_ai_sync_calendar_before_slot')) {
    function eottae_public_ai_sync_calendar_before_slot(array $options = array())
    {
        if (!function_exists('eottae_calendar_google_sync')) {
            if (is_file(G5_LIB_PATH.'/eottae-calendar-google.lib.php')) {
                include_once G5_LIB_PATH.'/eottae-calendar-google.lib.php';
            }
        }

        if (!function_exists('eottae_calendar_google_sync')) {
            return array('ok' => false, 'skipped' => true, 'reason' => 'no_sync_lib');
        }

        $calendar_id = function_exists('eottae_calendar_google_calendar_id')
            ? trim((string) eottae_calendar_google_calendar_id())
            : '';
        if ($calendar_id === '') {
            return array('ok' => false, 'skipped' => true, 'reason' => 'no_calendar_id');
        }

        if (!empty($options['dry_run'])) {
            return array('ok' => true, 'skipped' => true, 'reason' => 'dry_run');
        }

        $result = eottae_calendar_google_sync(array(
            'dry_run' => false,
        ));

        return is_array($result) ? $result : array('ok' => false, 'reason' => 'sync_failed');
    }
}

if (!function_exists('eottae_public_ai_slot_dashboard_stats')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_public_ai_slot_dashboard_stats($now = null)
    {
        eottae_public_ai_schedule_load_dependencies();
        eottae_public_ai_ensure_schema();

        $now = $now ?: (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $slots_meta = eottae_public_ai_publish_slots();
        $rows = array();
        $published_count = 0;
        $missed_count = 0;

        foreach ($slots_meta as $key => $meta) {
            $state = eottae_public_ai_slot_row_state($key, $now);
            $published = ($state === 'published');
            if ($published) {
                $published_count++;
            }
            if ($state === 'missed') {
                $missed_count++;
            }

            $rows[$key] = array(
                'key'        => $key,
                'label'      => $meta['label'],
                'time_range' => eottae_public_ai_slot_time_range_label($key),
                'published'  => $published,
                'state'      => $state,
            );
        }

        return array(
            'total_slots'     => count($slots_meta),
            'published_count' => $published_count,
            'missed_count'    => $missed_count,
            'current_slot'    => eottae_public_ai_detect_publish_slot($now),
            'slots'           => $rows,
            'has_alerts'      => $missed_count > 0,
        );
    }
}

if (!function_exists('eottae_public_ai_alert_sent_today')) {
    function eottae_public_ai_alert_sent_today($alert_code, $now = null)
    {
        eottae_public_ai_ensure_schema();
        $alert_code = preg_replace('/[^a-z0-9_:-]/i', '', (string) $alert_code);
        if ($alert_code === '') {
            return false;
        }

        $table = eottae_public_ai_logs_table();
        $day_start = eottae_public_ai_day_start_datetime(eottae_public_ai_today_ymd($now));
        $needle = 'alert:'.$alert_code;
        $row = sql_fetch("
            SELECT log_id
            FROM `{$table}`
            WHERE error_message = '".sql_escape_string($needle)."'
              AND created_at >= '".sql_escape_string($day_start)."'
            LIMIT 1
        ", false);

        return !empty($row['log_id']);
    }
}

if (!function_exists('eottae_public_ai_notify_admin_slot_issue')) {
    function eottae_public_ai_notify_admin_slot_issue($alert_code, array $context = array())
    {
        global $config;

        eottae_public_ai_schedule_load_dependencies();
        $alert_code = preg_replace('/[^a-z0-9_:-]/i', '', (string) $alert_code);
        if ($alert_code === '') {
            return array('ok' => false, 'message' => 'invalid_code');
        }

        $now = isset($context['now']) ? (string) $context['now'] : (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        if (eottae_public_ai_alert_sent_today($alert_code, $now)) {
            return array('ok' => true, 'message' => 'already_sent');
        }

        $slot_key = isset($context['slot']) ? preg_replace('/[^a-z0-9_]/', '', (string) $context['slot']) : '';
        $reason = trim((string) ($context['reason'] ?? $alert_code));
        $slots = eottae_public_ai_publish_slots();
        $slot_label = ($slot_key !== '' && isset($slots[$slot_key]['label'])) ? $slots[$slot_key]['label'] : '슬롯';

        $summary = '[세부어때] 공개단톡 AI 알림 — '.$slot_label.' ('.$reason.')';
        $body = $summary."\n"
            .'시각: '.$now."\n"
            .'슬롯: '.($slot_key !== '' ? $slot_key : '-')."\n"
            .'사유: '.$reason."\n"
            .'관리: '.G5_URL.'/page/eottae-admin-public-ai.php'."\n"
            .'마이페이지: '.G5_URL.'/page/eottae-mypage.php#sebu-public-ai-admin';

        eottae_public_ai_insert_log(array(
            'candidate_id'   => 0,
            'trigger_type'   => 'scheduled_slot',
            'message'        => $summary,
            'publish_status' => 'failed',
            'error_message'  => 'alert:'.$alert_code,
        ));

        $admin_email = isset($config['cf_admin_email']) ? trim((string) $config['cf_admin_email']) : '';
        if ($admin_email === '' || !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            return array('ok' => true, 'message' => 'logged_only');
        }

        if (!function_exists('mailer') && is_file(G5_LIB_PATH.'/mailer.lib.php')) {
            include_once G5_LIB_PATH.'/mailer.lib.php';
        }
        if (!function_exists('mailer')) {
            return array('ok' => true, 'message' => 'logged_only');
        }

        $from_name = isset($config['cf_admin_email_name']) ? (string) $config['cf_admin_email_name'] : '세부어때';
        $from_mail = $admin_email;
        $sent = mailer($from_name, $from_mail, $admin_email, $summary, nl2br(get_text($body)), 1);

        return array('ok' => (bool) $sent, 'message' => $sent ? 'mailed' : 'mail_failed');
    }
}

if (!function_exists('eottae_public_ai_run_health_monitor')) {
    function eottae_public_ai_run_health_monitor(array $options = array())
    {
        eottae_public_ai_schedule_load_dependencies();
        eottae_public_ai_ensure_schema();

        $now = isset($options['now']) ? (string) $options['now'] : (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $notify = !isset($options['notify']) || !empty($options['notify']);
        $settings = eottae_public_ai_get_settings();
        $health = eottae_public_ai_schedule_health_check($now);
        $slot_stats = eottae_public_ai_slot_dashboard_stats($now);
        $issues = array();

        if (empty($settings['ai_enabled'])) {
            $issues[] = 'ai_disabled';
            if ($notify) {
                eottae_public_ai_notify_admin_slot_issue('ai_disabled', array(
                    'reason' => 'ai_disabled',
                    'now'    => $now,
                ));
            }
        }

        if (empty($health['cron_key_configured'])) {
            $issues[] = 'cron_key_missing';
        }

        if ((int) ($health['public_room_id'] ?? 0) < 1) {
            $issues[] = 'public_room_missing';
        }

        foreach ($slot_stats['slots'] as $slot_key => $row) {
            if (($row['state'] ?? '') !== 'missed') {
                continue;
            }
            $issues[] = 'missed:'.$slot_key;
            if ($notify) {
                eottae_public_ai_notify_admin_slot_issue('missed_'.$slot_key, array(
                    'slot'   => $slot_key,
                    'reason' => 'slot_missed',
                    'now'    => $now,
                ));
            }
        }

        return array(
            'ok'             => count($issues) === 0,
            'issues'         => $issues,
            'health'         => $health,
            'slot_stats'     => $slot_stats,
            'checked_at'     => $now,
        );
    }
}

if (!function_exists('eottae_public_ai_render_slot_schedule_status')) {
    function eottae_public_ai_render_slot_schedule_status($variant = 'mypage')
    {
        if (!function_exists('eottae_public_ai_slot_dashboard_stats')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-schedule.lib.php';
        }

        $slot_stats = eottae_public_ai_slot_dashboard_stats();
        $prefix = ($variant === 'admin') ? 'public-ai-admin-dashboard' : 'my-public-ai-admin';
        $published = (int) ($slot_stats['published_count'] ?? 0);
        $total = (int) ($slot_stats['total_slots'] ?? 4);
        $missed = (int) ($slot_stats['missed_count'] ?? 0);
        ?>
        <div class="<?php echo $prefix; ?>__slots" aria-label="오늘 정기 슬롯 발행 현황">
            <div class="<?php echo $prefix; ?>__slots-head">
                <strong class="<?php echo $prefix; ?>__slots-title">정기 슬롯</strong>
                <span class="<?php echo $prefix; ?>__slots-summary"><?php echo number_format($published); ?> / <?php echo number_format($total); ?> 발행</span>
                <?php if ($missed > 0) { ?>
                <span class="<?php echo $prefix; ?>__slots-alert">누락 <?php echo number_format($missed); ?>건</span>
                <?php } ?>
            </div>
            <ul class="<?php echo $prefix; ?>__slots-list">
                <?php foreach ($slot_stats['slots'] as $row) {
                    $state = (string) ($row['state'] ?? 'pending');
                    $state_label = array(
                        'published' => '발행됨',
                        'missed'    => '누락',
                        'active'    => '진행 중',
                        'pending'   => '대기',
                    );
                    ?>
                <li class="<?php echo $prefix; ?>__slots-item is-<?php echo htmlspecialchars($state, ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="<?php echo $prefix; ?>__slots-label"><?php echo get_text($row['label'] ?? ''); ?></span>
                    <span class="<?php echo $prefix; ?>__slots-time"><?php echo get_text($row['time_range'] ?? ''); ?></span>
                    <span class="<?php echo $prefix; ?>__slots-state"><?php echo $state_label[$state] ?? $state; ?></span>
                </li>
                <?php } ?>
            </ul>
            <p class="<?php echo $prefix; ?>__slots-hint">
                일반 호스팅: 홈 공개톡 접속·폴링 시 자동 발송(방문 트리거).
                외부 크론: <code>cron-job.org</code> 등에서
                <code>/proc/eottae-public-ai-traffic-tick.php</code> 호출(키 설정 시 <code>?key=</code>).
            </p>
        </div>
        <?php
    }
}

if (!function_exists('eottae_public_ai_schedule_health_check')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_public_ai_schedule_health_check($now = null)
    {
        eottae_public_ai_schedule_load_dependencies();
        eottae_public_ai_ensure_schema();

        $settings = eottae_public_ai_get_settings();
        $now = $now ?: (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $slot = eottae_public_ai_detect_publish_slot($now);
        $slots = eottae_public_ai_publish_slots();
        $slot_status = array();

        foreach ($slots as $key => $meta) {
            $slot_status[$key] = array(
                'label'     => $meta['label'],
                'published' => eottae_public_ai_slot_published_today($key, $now),
            );
        }

        $room_id = 0;
        if (function_exists('eottae_talkroom_public_group_room_id')) {
            $room_id = (int) eottae_talkroom_public_group_room_id();
        }

        $openai_key = '';
        if (function_exists('eottae_public_ai_openai_resolve_api_key')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-openai.lib.php';
            $openai_key = eottae_public_ai_openai_resolve_api_key();
        }

        return array(
            'ai_enabled'          => !empty($settings['ai_enabled']),
            'auto_publish'        => !empty($settings['auto_publish']),
            'require_approval'    => !empty($settings['require_admin_approval']),
            'max_per_day'         => (int) ($settings['max_messages_per_day'] ?? 0),
            'active_hours'        => ($settings['active_start_time'] ?? '').' ~ '.($settings['active_end_time'] ?? ''),
            'use_weather'         => !empty($settings['use_weather']),
            'current_slot'        => $slot,
            'slots_today'         => $slot_status,
            'slot_missed_count'   => function_exists('eottae_public_ai_slot_dashboard_stats')
                ? (int) (eottae_public_ai_slot_dashboard_stats($now)['missed_count'] ?? 0) : 0,
            'published_today'     => function_exists('eottae_public_ai_count_public_chat_published_today')
                ? eottae_public_ai_count_public_chat_published_today($now) : 0,
            'public_room_id'      => $room_id,
            'openai_configured'   => $openai_key !== '',
            'cron_key_configured' => (function () {
                if (!function_exists('g5site_cfg')) {
                    return php_sapi_name() === 'cli';
                }
                $key = trim((string) g5site_cfg('public_ai_cron_key', ''));
                if ($key === '') {
                    $key = trim((string) g5site_cfg('talkroom_ai_cron_key', ''));
                }

                return $key !== '' || php_sapi_name() === 'cli';
            })(),
        );
    }
}
