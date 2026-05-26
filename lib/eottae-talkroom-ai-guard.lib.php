<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_default_site_daily_limit')) {
    function eottae_talkroom_ai_default_site_daily_limit()
    {
        return 100;
    }
}

if (!function_exists('eottae_talkroom_ai_get_global_policy_row')) {
    function eottae_talkroom_ai_get_global_policy_row()
    {
        return eottae_talkroom_ai_get_settings_row(eottae_talkroom_ai_global_room_id());
    }
}

if (!function_exists('eottae_talkroom_ai_get_global_policy')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_get_global_policy()
    {
        $row = eottae_talkroom_ai_get_global_policy_row();
        $defaults = array(
            'site_ai_enabled'       => 1,
            'owner_config_allowed'  => 1,
            'site_daily_limit'      => eottae_talkroom_ai_default_site_daily_limit(),
        );

        if (!$row) {
            return $defaults;
        }

        $site_ai_enabled = 1;
        if (array_key_exists('site_ai_enabled', $row)) {
            $site_ai_enabled = (int) !empty($row['site_ai_enabled']);
        }

        return array(
            'site_ai_enabled'      => $site_ai_enabled,
            'owner_config_allowed' => !empty($row['ai_enabled']) ? 1 : 0,
            'site_daily_limit'     => max(1, (int) ($row['max_messages_per_day'] ?? eottae_talkroom_ai_default_site_daily_limit())),
        );
    }
}

if (!function_exists('eottae_talkroom_ai_is_site_ai_enabled')) {
    function eottae_talkroom_ai_is_site_ai_enabled()
    {
        $policy = eottae_talkroom_ai_get_global_policy();

        return !empty($policy['site_ai_enabled']);
    }
}

if (!function_exists('eottae_talkroom_ai_is_owner_config_allowed')) {
    function eottae_talkroom_ai_is_owner_config_allowed()
    {
        $policy = eottae_talkroom_ai_get_global_policy();

        return !empty($policy['owner_config_allowed']);
    }
}

if (!function_exists('eottae_talkroom_ai_get_site_daily_limit')) {
    function eottae_talkroom_ai_get_site_daily_limit()
    {
        $policy = eottae_talkroom_ai_get_global_policy();

        return max(1, (int) ($policy['site_daily_limit'] ?? eottae_talkroom_ai_default_site_daily_limit()));
    }
}

if (!function_exists('eottae_talkroom_ai_get_today_site_message_count')) {
    function eottae_talkroom_ai_get_today_site_message_count($target_date = null)
    {
        $target_date = $target_date ?: G5_TIME_YMD;
        $tables = eottae_talkroom_ai_table_names();

        if (!eottae_talkroom_ai_table_exists($tables['daily_limits'])) {
            return 0;
        }

        $row = sql_fetch("
            SELECT SUM(message_count) AS total_count
            FROM `{$tables['daily_limits']}`
            WHERE target_date = '".sql_escape_string($target_date)."'
        ", false);

        return (int) ($row['total_count'] ?? 0);
    }
}

if (!function_exists('eottae_talkroom_ai_min_messages_per_day')) {
    function eottae_talkroom_ai_min_messages_per_day()
    {
        return 2;
    }
}

if (!function_exists('eottae_talkroom_ai_max_messages_per_day_cap')) {
    function eottae_talkroom_ai_max_messages_per_day_cap()
    {
        return 10;
    }
}

if (!function_exists('eottae_talkroom_ai_normalize_max_messages_per_day')) {
    function eottae_talkroom_ai_normalize_max_messages_per_day($value)
    {
        $min = eottae_talkroom_ai_min_messages_per_day();
        $max = eottae_talkroom_ai_max_messages_per_day_cap();
        $value = (int) $value;

        if ($value < $min) {
            return $min;
        }
        if ($value > $max) {
            return $max;
        }

        return $value;
    }
}

if (!function_exists('eottae_talkroom_ai_room_member_activity_total')) {
    /**
     * 오늘 톡방 회원 대화량 (AI·봇 제외 글+댓글)
     */
    function eottae_talkroom_ai_room_member_activity_total($room_id, $target_date = null)
    {
        if (!function_exists('eottae_talkroom_ai_fetch_room_activity_for_date')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-summary.lib.php';
        }

        $activity = eottae_talkroom_ai_fetch_room_activity_for_date((int) $room_id, $target_date ?: G5_TIME_YMD);

        return (int) ($activity['total'] ?? 0);
    }
}

if (!function_exists('eottae_talkroom_ai_effective_daily_limit')) {
    /**
     * 대화량에 따라 2~설정 최대값 사이에서 오늘 허용 AI 발언 수 계산
     */
    function eottae_talkroom_ai_effective_daily_limit($room_id, $settings = null, $target_date = null)
    {
        $room_id = (int) $room_id;
        $target_date = $target_date ?: G5_TIME_YMD;

        if (!is_array($settings)) {
            $settings = eottae_talkroom_ai_get_settings($room_id);
        }

        $min_cap = eottae_talkroom_ai_min_messages_per_day();
        $max_cap = eottae_talkroom_ai_normalize_max_messages_per_day($settings['max_messages_per_day'] ?? $min_cap);
        if ($max_cap <= $min_cap) {
            return $min_cap;
        }

        $activity = eottae_talkroom_ai_room_member_activity_total($room_id, $target_date);
        $range = $max_cap - $min_cap;

        if ($activity <= 0) {
            $boost = 0;
        } elseif ($activity <= 3) {
            $boost = 0;
        } elseif ($activity <= 8) {
            $boost = (int) max(1, ceil($range * 0.25));
        } elseif ($activity <= 15) {
            $boost = (int) max(1, ceil($range * 0.5));
        } elseif ($activity <= 30) {
            $boost = (int) max(1, ceil($range * 0.75));
        } else {
            $boost = $range;
        }

        return min($max_cap, $min_cap + min($boost, $range));
    }
}

if (!function_exists('eottae_talkroom_ai_daily_limit_status')) {
    /**
     * @return array{today_count:int, effective_limit:int, max_cap:int, min_cap:int, member_activity:int}
     */
    function eottae_talkroom_ai_daily_limit_status($room_id, $settings = null, $target_date = null)
    {
        $room_id = (int) $room_id;
        $target_date = $target_date ?: G5_TIME_YMD;

        if (!is_array($settings)) {
            $settings = eottae_talkroom_ai_get_settings($room_id);
        }

        $min_cap = eottae_talkroom_ai_min_messages_per_day();
        $max_cap = eottae_talkroom_ai_normalize_max_messages_per_day($settings['max_messages_per_day'] ?? $min_cap);
        $member_activity = eottae_talkroom_ai_room_member_activity_total($room_id, $target_date);
        $effective_limit = eottae_talkroom_ai_effective_daily_limit($room_id, $settings, $target_date);

        return array(
            'today_count'     => eottae_talkroom_ai_get_today_message_count($room_id, $target_date),
            'effective_limit' => $effective_limit,
            'max_cap'         => $max_cap,
            'min_cap'         => $min_cap,
            'member_activity' => $member_activity,
        );
    }
}

if (!function_exists('eottae_talkroom_ai_is_site_daily_limit_reached')) {
    function eottae_talkroom_ai_is_site_daily_limit_reached($target_date = null)
    {
        $target_date = $target_date ?: G5_TIME_YMD;
        $limit = eottae_talkroom_ai_get_site_daily_limit();

        return eottae_talkroom_ai_get_today_site_message_count($target_date) >= $limit;
    }
}

if (!function_exists('eottae_talkroom_ai_is_room_force_disabled')) {
    function eottae_talkroom_ai_is_room_force_disabled($room_id)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1) {
            return false;
        }

        $row = eottae_talkroom_ai_get_settings_row($room_id);

        return is_array($row) && !empty($row['admin_force_disabled']);
    }
}

if (!function_exists('eottae_talkroom_ai_is_room_ai_effective')) {
    function eottae_talkroom_ai_is_room_ai_effective($room_id)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1) {
            return false;
        }
        if (!eottae_talkroom_ai_is_site_ai_enabled()) {
            return false;
        }
        if (eottae_talkroom_ai_is_room_force_disabled($room_id)) {
            return false;
        }

        $settings = eottae_talkroom_ai_get_settings($room_id);

        return !empty($settings['ai_enabled']);
    }
}

if (!function_exists('eottae_talkroom_ai_room_latest_post_is_ai')) {
    function eottae_talkroom_ai_room_latest_post_is_ai($room_id)
    {
        $latest = eottae_talkroom_ai_room_latest_post_row((int) $room_id);
        if (!$latest) {
            return false;
        }

        return eottae_talkroom_ai_is_ai_write_row($latest);
    }
}

if (!function_exists('eottae_talkroom_ai_evaluate_shared_limits')) {
    /**
     * @return array{ok:bool, reason:string, settings?:array}
     */
    function eottae_talkroom_ai_evaluate_shared_limits($room_id, $now = null, array $options = array())
    {
        $room_id = (int) $room_id;
        $now = $now ?: G5_TIME_YMDHIS;
        $force = !empty($options['force']);
        $is_test = !empty($options['is_test']);
        $skip_consecutive = !empty($options['skip_consecutive']);
        $skip_daily = !empty($options['skip_daily']);
        $bypass_policy = $force && $is_test;

        $settings = eottae_talkroom_ai_get_settings($room_id);

        if (!$bypass_policy && !eottae_talkroom_ai_is_site_ai_enabled()) {
            return array('ok' => false, 'reason' => 'site_ai_disabled', 'settings' => $settings);
        }

        if (!$bypass_policy && eottae_talkroom_ai_is_room_force_disabled($room_id)) {
            return array('ok' => false, 'reason' => 'admin_force_disabled', 'settings' => $settings);
        }

        if (!$bypass_policy && empty($settings['ai_enabled'])) {
            return array('ok' => false, 'reason' => 'ai_disabled', 'settings' => $settings);
        }

        if (!$skip_daily && !$bypass_policy) {
            if (eottae_talkroom_ai_is_site_daily_limit_reached(substr($now, 0, 10))) {
                return array('ok' => false, 'reason' => 'site_daily_limit_reached', 'settings' => $settings);
            }

            $limit_status = eottae_talkroom_ai_daily_limit_status($room_id, $settings, substr($now, 0, 10));
            $max_per_day = (int) ($limit_status['effective_limit'] ?? eottae_talkroom_ai_min_messages_per_day());
            if ((int) ($limit_status['today_count'] ?? 0) >= $max_per_day) {
                return array('ok' => false, 'reason' => 'daily_limit_reached', 'settings' => $settings);
            }
        }

        if (!$skip_consecutive && !$bypass_policy && eottae_talkroom_ai_room_latest_post_is_ai($room_id)) {
            return array('ok' => false, 'reason' => 'latest_post_is_ai', 'settings' => $settings);
        }

        return array('ok' => true, 'reason' => 'ok', 'settings' => $settings);
    }
}
