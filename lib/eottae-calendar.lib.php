<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_calendar_bootstrap_tables')) {
    function eottae_calendar_bootstrap_tables()
    {
        global $g5;

        if (!isset($g5['sebu_calendar_events_table'])) {
            $g5['sebu_calendar_events_table'] = G5_TABLE_PREFIX.'sebu_calendar_events';
        }
        if (!isset($g5['sebu_calendar_reports_table'])) {
            $g5['sebu_calendar_reports_table'] = G5_TABLE_PREFIX.'sebu_calendar_reports';
        }
        if (!isset($g5['sebu_calendar_sync_logs_table'])) {
            $g5['sebu_calendar_sync_logs_table'] = G5_TABLE_PREFIX.'sebu_calendar_sync_logs';
        }
    }
}

if (!function_exists('eottae_calendar_table_name')) {
    function eottae_calendar_table_name()
    {
        eottae_calendar_bootstrap_tables();
        global $g5;

        return $g5['sebu_calendar_events_table'];
    }
}

if (!function_exists('eottae_calendar_table_exists')) {
    function eottae_calendar_table_exists()
    {
        $table = preg_replace('/[^a-z0-9_]/i', '', eottae_calendar_table_name());
        if ($table === '') {
            return false;
        }

        $row = sql_fetch(" SHOW TABLES LIKE '{$table}' ", false);

        return !empty($row);
    }
}

if (!function_exists('eottae_calendar_category_options')) {
    function eottae_calendar_category_options()
    {
        return array(
            'holiday'  => '법정공휴일',
            'talk'     => '세부톡',
            'festival' => '축제',
            'event'    => '이벤트',
            'meetup'   => '모임',
            'travel'   => '여행',
            'life'     => '생활정보',
            'business' => '업체행사',
            'etc'      => '기타',
        );
    }
}

if (!function_exists('eottae_calendar_area_options')) {
    function eottae_calendar_area_options()
    {
        return array(
            'cebu_city' => '세부시티',
            'mactan'    => '막탄',
            'lapu_lapu' => '라푸라푸',
            'mandaue'   => '만다우에',
            'talamban'  => '탈람반',
            'banilad'   => '바닐라드',
            'it_park'   => 'IT Park',
            'ayala'     => '아얄라',
            'sm_city'   => 'SM City',
            'etc'       => '기타',
        );
    }
}

if (!function_exists('eottae_calendar_badge_options')) {
    function eottae_calendar_badge_options()
    {
        return array(
            'default'  => array('label' => '기본', 'class' => 'calendar-badge-default'),
            'important'=> array('label' => '중요', 'class' => 'calendar-badge-important'),
            'recommend'=> array('label' => '추천', 'class' => 'calendar-badge-recommend'),
            'new'      => array('label' => '신규', 'class' => 'calendar-badge-new'),
            'hot'      => array('label' => '인기', 'class' => 'calendar-badge-hot'),
            'free'     => array('label' => '무료', 'class' => 'calendar-badge-free'),
            'paid'     => array('label' => '유료', 'class' => 'calendar-badge-paid'),
            'deadline' => array('label' => '마감임박', 'class' => 'calendar-badge-deadline'),
        );
    }
}

if (!function_exists('eottae_calendar_category_label')) {
    function eottae_calendar_category_label($code)
    {
        $options = eottae_calendar_category_options();

        return isset($options[$code]) ? $options[$code] : '기타';
    }
}

if (!function_exists('eottae_calendar_area_label')) {
    function eottae_calendar_area_label($code)
    {
        $options = eottae_calendar_area_options();

        return isset($options[$code]) ? $options[$code] : '';
    }
}

if (!function_exists('eottae_calendar_badge_meta')) {
    function eottae_calendar_badge_meta($code)
    {
        $options = eottae_calendar_badge_options();
        $code = preg_replace('/[^a-z_]/', '', (string) $code);
        if ($code === '' || !isset($options[$code])) {
            return $options['default'];
        }

        return $options[$code];
    }
}

if (!function_exists('eottae_calendar_category_class')) {
    function eottae_calendar_category_class($code)
    {
        $code = preg_replace('/[^a-z_]/', '', (string) $code);

        return 'sebu-cal-cat sebu-cal-cat--'.($code !== '' ? $code : 'etc');
    }
}

if (!function_exists('eottae_calendar_list_url')) {
    function eottae_calendar_list_url(array $params = array())
    {
        $url = G5_URL.'/calendar/';
        if (!$params) {
            return $url;
        }

        return $url.'?'.http_build_query($params);
    }
}

if (!function_exists('eottae_calendar_create_url')) {
    function eottae_calendar_create_url(array $params = array())
    {
        $url = G5_URL.'/page/eottae-calendar-create.php';
        if (!$params) {
            return $url;
        }

        return $url.'?'.http_build_query($params);
    }
}

if (!function_exists('eottae_calendar_create_from_talk_url')) {
    function eottae_calendar_create_from_talk_url($room_id, array $params = array())
    {
        $params['room_id'] = (int) $room_id;
        $params['from'] = 'talk';
        $params['category'] = $params['category'] ?? 'talk';
        $params['badge_style'] = $params['badge_style'] ?? 'recommend';

        return eottae_calendar_create_url($params);
    }
}

if (!function_exists('eottae_calendar_create_from_talk_post_url')) {
    function eottae_calendar_create_from_talk_post_url($room_id, $post_url, $title = '', $description = '')
    {
        return eottae_calendar_create_url(array(
            'from'             => 'talk_post',
            'room_id'          => (int) $room_id,
            'title'            => (string) $title,
            'description'      => (string) $description,
            'related_post_url' => (string) $post_url,
            'category'         => 'talk',
            'badge_style'      => 'recommend',
        ));
    }
}

if (!function_exists('eottae_calendar_edit_url')) {
    function eottae_calendar_edit_url($event_id)
    {
        return G5_URL.'/page/eottae-calendar-edit.php?event_id='.(int) $event_id;
    }
}

if (!function_exists('eottae_calendar_event_url')) {
    function eottae_calendar_event_url($event_id)
    {
        return G5_URL.'/page/eottae-calendar-event.php?event_id='.(int) $event_id;
    }
}

if (!function_exists('eottae_calendar_ensure_schema')) {
    function eottae_calendar_ensure_schema()
    {
        global $g5;

        eottae_calendar_bootstrap_tables();

        $table = eottae_calendar_table_name();
        $results = array();

        $ddl = " CREATE TABLE IF NOT EXISTS `{$table}` (
            `event_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(200) NOT NULL DEFAULT '',
            `description` text NOT NULL,
            `start_date` date NOT NULL,
            `end_date` date NOT NULL,
            `start_time` time DEFAULT NULL,
            `end_time` time DEFAULT NULL,
            `is_all_day` tinyint(1) NOT NULL DEFAULT '0',
            `location` varchar(255) NOT NULL DEFAULT '',
            `area` varchar(30) NOT NULL DEFAULT '',
            `category` varchar(30) NOT NULL DEFAULT 'etc',
            `badge_style` varchar(30) NOT NULL DEFAULT 'default',
            `related_url` varchar(500) NOT NULL DEFAULT '',
            `related_post_url` varchar(500) NOT NULL DEFAULT '',
            `related_room_id` int(11) unsigned NOT NULL DEFAULT '0',
            `source_type` varchar(20) NOT NULL DEFAULT 'local',
            `google_event_id` varchar(255) DEFAULT NULL,
            `writer_mb_id` varchar(20) NOT NULL DEFAULT '',
            `writer_name` varchar(100) NOT NULL DEFAULT '',
            `status` varchar(20) NOT NULL DEFAULT 'active',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `deleted_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `deleted_by` varchar(20) NOT NULL DEFAULT '',
            PRIMARY KEY (`event_id`),
            KEY `idx_status_dates` (`status`, `start_date`, `end_date`),
            KEY `idx_category` (`category`),
            KEY `idx_area` (`area`),
            KEY `idx_writer` (`writer_mb_id`),
            KEY `idx_source` (`source_type`),
            UNIQUE KEY `uniq_google_event_id` (`google_event_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";

        $ok = (bool) sql_query($ddl, false);
        $results[] = array(
            'table'  => $table,
            'action' => 'create',
            'ok'     => $ok,
        );

        $reports = $g5['sebu_calendar_reports_table'];
        $reports_ddl = " CREATE TABLE IF NOT EXISTS `{$reports}` (
            `report_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `event_id` int(11) unsigned NOT NULL DEFAULT '0',
            `reporter_mb_id` varchar(20) NOT NULL DEFAULT '',
            `reason` varchar(30) NOT NULL DEFAULT '',
            `memo` varchar(1000) NOT NULL DEFAULT '',
            `status` varchar(20) NOT NULL DEFAULT 'pending',
            `handled_by` varchar(20) NOT NULL DEFAULT '',
            `handled_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`report_id`),
            UNIQUE KEY `uniq_event_reporter` (`event_id`, `reporter_mb_id`),
            KEY `idx_status` (`status`),
            KEY `idx_event` (`event_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";
        $results[] = array(
            'table'  => $reports,
            'action' => 'create',
            'ok'     => (bool) sql_query($reports_ddl, false),
        );

        $logs = $g5['sebu_calendar_sync_logs_table'];
        $logs_ddl = " CREATE TABLE IF NOT EXISTS `{$logs}` (
            `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `source` varchar(30) NOT NULL DEFAULT 'google',
            `status` varchar(20) NOT NULL DEFAULT 'ok',
            `message` varchar(500) NOT NULL DEFAULT '',
            `fetched_count` int(11) NOT NULL DEFAULT '0',
            `inserted_count` int(11) NOT NULL DEFAULT '0',
            `updated_count` int(11) NOT NULL DEFAULT '0',
            `hidden_count` int(11) NOT NULL DEFAULT '0',
            `error_message` text NOT NULL,
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`log_id`),
            KEY `idx_source_created` (`source`, `created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";
        $results[] = array(
            'table'  => $logs,
            'action' => 'create',
            'ok'     => (bool) sql_query($logs_ddl, false),
        );

        eottae_calendar_upgrade_schema();

        return $results;
    }
}

if (!function_exists('eottae_calendar_upgrade_schema')) {
    function eottae_calendar_upgrade_schema()
    {
        $table = eottae_calendar_table_name();
        if (!eottae_calendar_table_exists()) {
            return;
        }

        $col = sql_fetch(" SHOW COLUMNS FROM `{$table}` LIKE 'related_post_url' ", false);
        if (empty($col)) {
            sql_query(" ALTER TABLE `{$table}` ADD COLUMN `related_post_url` varchar(500) NOT NULL DEFAULT '' AFTER `related_url` ", false);
        }

        $idx = sql_fetch(" SHOW INDEX FROM `{$table}` WHERE Key_name = 'uniq_google_event_id' ", false);
        if (empty($idx)) {
            @sql_query(" ALTER TABLE `{$table}` ADD UNIQUE KEY `uniq_google_event_id` (`google_event_id`) ", false);
        }

        @sql_query(" ALTER TABLE `{$table}` MODIFY `google_event_id` varchar(255) DEFAULT NULL ", false);
        @sql_query(" UPDATE `{$table}` SET google_event_id = NULL WHERE google_event_id = '' OR source_type <> 'google' ", false);
    }
}

if (!function_exists('eottae_calendar_schema_status')) {
    function eottae_calendar_schema_status()
    {
        global $g5;
        eottae_calendar_bootstrap_tables();

        return array(
            'events' => array(
                'table'  => eottae_calendar_table_name(),
                'exists' => eottae_calendar_table_exists(),
            ),
            'reports' => array(
                'table'  => $g5['sebu_calendar_reports_table'],
                'exists' => (bool) sql_fetch(" SHOW TABLES LIKE '".preg_replace('/[^a-z0-9_]/i', '', $g5['sebu_calendar_reports_table'])."' ", false),
            ),
            'sync_logs' => array(
                'table'  => $g5['sebu_calendar_sync_logs_table'],
                'exists' => (bool) sql_fetch(" SHOW TABLES LIKE '".preg_replace('/[^a-z0-9_]/i', '', $g5['sebu_calendar_sync_logs_table'])."' ", false),
            ),
        );
    }
}

if (!function_exists('eottae_calendar_is_google_event')) {
    function eottae_calendar_is_google_event(array $event)
    {
        return ($event['source_type'] ?? '') === 'google';
    }
}

if (!function_exists('eottae_calendar_can_edit_event')) {
    function eottae_calendar_can_edit_event(array $event, $mb_id, $is_super = false)
    {
        if (eottae_calendar_is_google_event($event)) {
            return false;
        }

        return eottae_calendar_can_manage_event($event, $mb_id, $is_super);
    }
}

if (!function_exists('eottae_calendar_can_delete_event')) {
    function eottae_calendar_can_delete_event(array $event, $mb_id, $is_super = false)
    {
        if (eottae_calendar_is_google_event($event)) {
            return !empty($is_super);
        }

        return eottae_calendar_can_manage_event($event, $mb_id, $is_super);
    }
}

if (!function_exists('eottae_calendar_can_create_from_talk')) {
    function eottae_calendar_can_create_from_talk($room_id, $mb_id, $is_super = false)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1) {
            return false;
        }
        if (!empty($is_super)) {
            return true;
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '' || !function_exists('eottae_talkroom_get_operating_room')) {
            return false;
        }

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return false;
        }

        $member_row = function_exists('eottae_talkroom_get_member_row')
            ? eottae_talkroom_get_member_row($room_id, $mb_id)
            : null;
        $membership = function_exists('eottae_talkroom_membership_state')
            ? eottae_talkroom_membership_state($room, $member_row, $mb_id)
            : '';

        return in_array($membership, array('owner', 'active'), true);
    }
}

if (!function_exists('eottae_calendar_hide_event')) {
    function eottae_calendar_hide_event($event_id, $handler_mb_id, $is_super = false)
    {
        $event = eottae_calendar_get_event($event_id, true);
        if (!$event || ($event['status'] ?? '') === 'hidden') {
            return array('ok' => false, 'message' => '일정을 찾을 수 없습니다.');
        }
        if (!eottae_calendar_can_delete_event($event, $handler_mb_id, $is_super)) {
            return array('ok' => false, 'message' => '숨김 처리 권한이 없습니다.');
        }

        $table = eottae_calendar_table_name();
        $event_id = (int) $event_id;
        $handler_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $handler_mb_id);
        $now = G5_TIME_YMDHIS;

        $ok = sql_query("
            UPDATE `{$table}` SET
                status = 'hidden',
                deleted_at = '{$now}',
                deleted_by = '".sql_real_escape_string($handler_mb_id)."',
                updated_at = '{$now}'
            WHERE event_id = {$event_id}
            LIMIT 1
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '일정 숨김에 실패했습니다.');
        }

        return array('ok' => true, 'message' => '일정을 숨겼습니다.');
    }
}

if (!function_exists('eottae_calendar_member_token')) {
    function eottae_calendar_member_token($regenerate = false)
    {
        $token = get_session('eottae_calendar_member_token');
        if ($regenerate || $token === '' || $token === null) {
            try {
                $token = bin2hex(random_bytes(16));
            } catch (Exception $e) {
                $token = md5(uniqid((string) mt_rand(), true));
            }
            set_session('eottae_calendar_member_token', $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_calendar_verify_member_token')) {
    function eottae_calendar_verify_member_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_calendar_member_token');

        return $token !== '' && $session_token !== '' && hash_equals($session_token, $token);
    }
}

if (!function_exists('eottae_calendar_visible_sql')) {
    function eottae_calendar_visible_sql($alias = '')
    {
        $prefix = $alias !== '' ? rtrim($alias, '.').'.' : '';

        return " {$prefix}`status` = 'active' ";
    }
}

if (!function_exists('eottae_calendar_clean_text')) {
    function eottae_calendar_clean_text($value, $max_len = 5000)
    {
        $value = trim(strip_tags((string) $value));
        if ($max_len < 1) {
            return $value;
        }
        if (function_exists('cut_str')) {
            return cut_str($value, $max_len, '');
        }

        return function_exists('mb_substr') ? mb_substr($value, 0, $max_len, 'UTF-8') : substr($value, 0, $max_len);
    }
}

if (!function_exists('eottae_calendar_normalize_category')) {
    function eottae_calendar_normalize_category($code)
    {
        $code = preg_replace('/[^a-z_]/', '', (string) $code);
        $options = eottae_calendar_category_options();

        return isset($options[$code]) ? $code : 'etc';
    }
}

if (!function_exists('eottae_calendar_normalize_area')) {
    function eottae_calendar_normalize_area($code)
    {
        $code = preg_replace('/[^a-z_]/', '', (string) $code);
        $options = eottae_calendar_area_options();

        return isset($options[$code]) ? $code : 'etc';
    }
}

if (!function_exists('eottae_calendar_normalize_badge')) {
    function eottae_calendar_normalize_badge($code)
    {
        $code = preg_replace('/[^a-z_]/', '', (string) $code);
        $options = eottae_calendar_badge_options();

        return isset($options[$code]) ? $code : 'default';
    }
}

if (!function_exists('eottae_calendar_can_manage_event')) {
    function eottae_calendar_can_manage_event(array $event, $mb_id, $is_super = false)
    {
        if (!empty($is_super)) {
            return true;
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $writer = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($event['writer_mb_id'] ?? ''));

        return $mb_id !== '' && $writer !== '' && hash_equals($writer, $mb_id);
    }
}

if (!function_exists('eottae_calendar_get_event')) {
    function eottae_calendar_get_event($event_id, $include_deleted = false)
    {
        if (!eottae_calendar_table_exists()) {
            return null;
        }

        $event_id = (int) $event_id;
        if ($event_id < 1) {
            return null;
        }

        $table = eottae_calendar_table_name();
        $where = $include_deleted ? '1=1' : eottae_calendar_visible_sql();
        $row = sql_fetch("
            SELECT *
            FROM `{$table}`
            WHERE event_id = {$event_id}
              AND {$where}
            LIMIT 1
        ", false);

        if (!$row || empty($row['event_id'])) {
            return null;
        }

        return eottae_calendar_present_event($row);
    }
}

if (!function_exists('eottae_calendar_present_event')) {
    function eottae_calendar_present_event(array $row)
    {
        $category = eottae_calendar_normalize_category($row['category'] ?? 'etc');
        $area = eottae_calendar_normalize_area($row['area'] ?? '');
        $badge = eottae_calendar_normalize_badge($row['badge_style'] ?? 'default');
        $badge_meta = eottae_calendar_badge_meta($badge);
        $is_all_day = !empty($row['is_all_day']);

        $start_date = (string) ($row['start_date'] ?? '');
        $end_date = (string) ($row['end_date'] ?? '');
        if ($end_date === '' || $end_date === '0000-00-00') {
            $end_date = $start_date;
        }

        $start_time = (string) ($row['start_time'] ?? '');
        $end_time = (string) ($row['end_time'] ?? '');
        if ($start_time === '00:00:00' && $is_all_day) {
            $start_time = '';
        }
        if ($end_time === '00:00:00' && $is_all_day) {
            $end_time = '';
        }

        $date_label = $start_date;
        if ($end_date !== '' && $end_date !== $start_date) {
            $date_label = $start_date.' ~ '.$end_date;
        }

        $time_label = '';
        if ($is_all_day) {
            $time_label = '하루종일';
        } elseif ($start_time !== '') {
            $time_label = substr($start_time, 0, 5);
            if ($end_time !== '') {
                $time_label .= ' ~ '.substr($end_time, 0, 5);
            }
        }

        $related_room_id = (int) ($row['related_room_id'] ?? 0);
        $related_room_name = '';
        $related_room_href = '';
        if ($related_room_id > 0 && function_exists('eottae_talkroom_get_room')) {
            $room = eottae_talkroom_get_room($related_room_id);
            if (is_array($room) && !empty($room['room_name'])) {
                $related_room_name = (string) $room['room_name'];
                $related_room_href = function_exists('eottae_talkroom_enter_url')
                    ? eottae_talkroom_enter_url($related_room_id)
                    : '';
            }
        }

        $related_url = trim((string) ($row['related_url'] ?? ''));
        if ($related_url !== '' && !preg_match('#^https?://#i', $related_url)) {
            $related_url = '';
        }

        $related_post_url = trim((string) ($row['related_post_url'] ?? ''));
        if ($related_post_url !== '' && !preg_match('#^https?://#i', $related_post_url)) {
            $related_post_url = '';
        }

        $source_type = ($row['source_type'] ?? '') === 'google' ? 'google' : 'local';
        $is_google = $source_type === 'google';
        $source_label = $is_google ? 'Google Calendar' : '세부어때';
        $writer_display = $is_google ? 'Google Calendar' : get_text((string) ($row['writer_name'] ?? ''));

        return array_merge($row, array(
            'event_id'           => (int) ($row['event_id'] ?? 0),
            'source_type'        => $source_type,
            'is_google'          => $is_google ? 1 : 0,
            'source_label'       => $source_label,
            'writer_display'     => $writer_display,
            'category'           => $category,
            'category_label'     => eottae_calendar_category_label($category),
            'category_class'     => eottae_calendar_category_class($category),
            'area'               => $area,
            'area_label'         => eottae_calendar_area_label($area),
            'badge_style'        => $badge,
            'badge_label'        => $badge_meta['label'],
            'badge_class'        => $badge_meta['class'],
            'is_all_day'         => $is_all_day ? 1 : 0,
            'start_date'         => $start_date,
            'end_date'           => $end_date,
            'date_label'         => $date_label,
            'time_label'         => $time_label,
            'detail_href'        => eottae_calendar_event_url((int) ($row['event_id'] ?? 0)),
            'edit_href'          => eottae_calendar_edit_url((int) ($row['event_id'] ?? 0)),
            'related_room_id'    => $related_room_id,
            'related_room_name'  => $related_room_name,
            'related_room_href'  => $related_room_href,
            'related_url'        => $related_url,
            'related_post_url'   => $related_post_url,
            'writer_name'        => get_text((string) ($row['writer_name'] ?? '')),
            'description_html'   => nl2br(get_text((string) ($row['description'] ?? ''))),
        ));
    }
}

if (!function_exists('eottae_calendar_event_detail_api_payload')) {
    /**
     * 일정 상세 팝업용 JSON 페이로드
     *
     * @return array<string, mixed>|null
     */
    function eottae_calendar_event_detail_api_payload($event_id)
    {
        $event = eottae_calendar_get_event((int) $event_id);
        if (!$event) {
            return null;
        }

        global $is_member, $member, $is_admin;

        $mb_id = !empty($is_member) && is_array($member) ? (string) ($member['mb_id'] ?? '') : '';
        $is_super = (isset($is_admin) && $is_admin === 'super');
        $can_edit = $is_member && eottae_calendar_can_edit_event($event, $mb_id, $is_super);
        $can_delete = $is_member && eottae_calendar_can_delete_event($event, $mb_id, $is_super);
        $is_google = !empty($event['is_google']);

        $description = trim(strip_tags((string) ($event['description'] ?? '')));

        return array(
            'event_id'          => (int) ($event['event_id'] ?? 0),
            'title'             => (string) ($event['title'] ?? ''),
            'category'          => (string) ($event['category'] ?? ''),
            'category_label'    => (string) ($event['category_label'] ?? ''),
            'category_class'    => (string) ($event['category_class'] ?? ''),
            'badge_label'       => (string) ($event['badge_label'] ?? ''),
            'badge_class'       => (string) ($event['badge_class'] ?? ''),
            'is_google'         => $is_google ? 1 : 0,
            'source_label'      => (string) ($event['source_label'] ?? '세부어때'),
            'date_label'        => (string) ($event['date_label'] ?? ''),
            'time_label'        => (string) ($event['time_label'] ?? ''),
            'area_label'        => (string) ($event['area_label'] ?? ''),
            'location'          => (string) ($event['location'] ?? ''),
            'writer_display'    => (string) ($event['writer_display'] ?? $event['writer_name'] ?? ''),
            'created_at'        => substr((string) ($event['created_at'] ?? ''), 0, 16),
            'updated_at'        => substr((string) ($event['updated_at'] ?? ''), 0, 16),
            'description'       => $description,
            'description_html'  => (string) ($event['description_html'] ?? ''),
            'related_url'       => (string) ($event['related_url'] ?? ''),
            'related_room_name' => (string) ($event['related_room_name'] ?? ''),
            'related_room_href' => (string) ($event['related_room_href'] ?? ''),
            'related_post_url'  => (string) ($event['related_post_url'] ?? ''),
            'detail_href'       => (string) ($event['detail_href'] ?? ''),
            'edit_href'         => (string) ($event['edit_href'] ?? ''),
            'can_edit'          => $can_edit ? 1 : 0,
            'can_delete'        => $can_delete ? 1 : 0,
            'delete_label'      => $is_google ? '숨김' : '삭제',
            'delete_confirm'    => $is_google ? '이 Google 일정을 숨김 처리할까요?' : '이 일정을 삭제할까요?',
        );
    }
}

if (!function_exists('eottae_calendar_list_events')) {
    function eottae_calendar_list_events(array $options = array())
    {
        if (!eottae_calendar_table_exists()) {
            return array('total' => 0, 'rows' => array());
        }

        $table = eottae_calendar_table_name();
        $category = '';
        if (!empty($options['category']) && (string) $options['category'] !== 'all') {
            $category = eottae_calendar_normalize_category($options['category']);
        }
        $range_start = isset($options['range_start']) ? trim((string) $options['range_start']) : '';
        $range_end = isset($options['range_end']) ? trim((string) $options['range_end']) : '';
        $limit = isset($options['limit']) ? max(1, min(500, (int) $options['limit'])) : 200;
        $offset = isset($options['offset']) ? max(0, (int) $options['offset']) : 0;
        $order = isset($options['order']) ? trim((string) $options['order']) : 'start';

        $where = array(eottae_calendar_visible_sql());
        if ($category !== '') {
            $where[] = " category = '".sql_real_escape_string($category)."' ";
        }
        if ($range_start !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $range_start)) {
            $where[] = " end_date >= '".sql_real_escape_string($range_start)."' ";
        }
        if ($range_end !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $range_end)) {
            $where[] = " start_date <= '".sql_real_escape_string($range_end)."' ";
        }

        $where_sql = implode(' AND ', $where);
        $count_row = sql_fetch("SELECT COUNT(*) AS cnt FROM `{$table}` WHERE {$where_sql}", false);
        $total = (int) ($count_row['cnt'] ?? 0);
        if ($total < 1) {
            return array('total' => 0, 'rows' => array());
        }

        $order_sql = $order === 'updated'
            ? 'updated_at DESC, event_id DESC'
            : 'start_date ASC, start_time ASC, event_id ASC';

        $result = sql_query("
            SELECT *
            FROM `{$table}`
            WHERE {$where_sql}
            ORDER BY {$order_sql}
            LIMIT {$offset}, {$limit}
        ", false);

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = eottae_calendar_present_event($row);
        }

        return array('total' => $total, 'rows' => $rows);
    }
}

if (!function_exists('eottae_calendar_events_for_month')) {
    function eottae_calendar_events_for_month($year, $month, $category = '')
    {
        $year = (int) $year;
        $month = (int) $month;
        if ($year < 1970 || $month < 1 || $month > 12) {
            return array();
        }

        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));

        $list = eottae_calendar_list_events(array(
            'category'    => $category,
            'range_start' => $start,
            'range_end'   => $end,
            'limit'       => 500,
        ));

        return isset($list['rows']) ? $list['rows'] : array();
    }
}

if (!function_exists('eottae_calendar_build_month_grid')) {
    function eottae_calendar_build_month_grid($year, $month, array $events = array())
    {
        $year = (int) $year;
        $month = (int) $month;
        $first_ts = strtotime(sprintf('%04d-%02d-01', $year, $month));
        $days_in_month = (int) date('t', $first_ts);
        $start_weekday = (int) date('w', $first_ts);
        $month_start = date('Y-m-01', $first_ts);
        $month_end = date('Y-m-t', $first_ts);

        $by_date = array();
        foreach ($events as $event) {
            $event_start = (string) ($event['start_date'] ?? '');
            $event_end = (string) ($event['end_date'] ?? $event_start);
            if ($event_start === '') {
                continue;
            }
            $cursor = max($event_start, $month_start);
            $until = min($event_end, $month_end);
            while ($cursor <= $until) {
                if (!isset($by_date[$cursor])) {
                    $by_date[$cursor] = array();
                }
                $by_date[$cursor][] = $event;
                $cursor = date('Y-m-d', strtotime($cursor.' +1 day'));
            }
        }

        $weeks = array();
        $day = 1;
        $week = array();
        for ($i = 0; $i < $start_weekday; $i++) {
            $week[] = null;
        }
        while ($day <= $days_in_month) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $week[] = array(
                'date'   => $date,
                'day'    => $day,
                'events' => isset($by_date[$date]) ? $by_date[$date] : array(),
                'is_today'=> $date === date('Y-m-d'),
            );
            if (count($week) === 7) {
                $weeks[] = $week;
                $week = array();
            }
            $day++;
        }
        if ($week) {
            while (count($week) < 7) {
                $week[] = null;
            }
            $weeks[] = $week;
        }

        return $weeks;
    }
}

if (!function_exists('eottae_calendar_summary_days')) {
    function eottae_calendar_summary_days($base_date = '', $category = '')
    {
        if ($base_date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $base_date)) {
            $base_date = date('Y-m-d');
        }

        $labels = array('오늘', '내일', '모레');
        $summary = array();
        for ($i = 0; $i < 3; $i++) {
            $date = date('Y-m-d', strtotime($base_date.' +'.$i.' day'));
            $list = eottae_calendar_list_events(array(
                'category'    => $category,
                'range_start' => $date,
                'range_end'   => $date,
                'limit'       => 20,
            ));
            $summary[] = array(
                'label'  => $labels[$i],
                'date'   => $date,
                'count'  => (int) ($list['total'] ?? 0),
                'events' => isset($list['rows']) ? $list['rows'] : array(),
            );
        }

        return $summary;
    }
}

if (!function_exists('eottae_calendar_talkroom_options')) {
    function eottae_calendar_talkroom_options()
    {
        if (!function_exists('eottae_talkroom_list_public')) {
            return array();
        }

        $list = eottae_talkroom_list_public(array('limit' => 200, 'order' => 'updated'));
        $rows = isset($list['rows']) ? $list['rows'] : array();
        $options = array();
        foreach ($rows as $room) {
            $room_id = (int) ($room['room_id'] ?? 0);
            if ($room_id < 1) {
                continue;
            }
            $options[$room_id] = (string) ($room['room_name'] ?? ('톡방 #'.$room_id));
        }

        return $options;
    }
}

if (!function_exists('eottae_calendar_validate_input')) {
    function eottae_calendar_validate_input(array $input)
    {
        $title = eottae_calendar_clean_text($input['title'] ?? '', 200);
        $description = eottae_calendar_clean_text($input['description'] ?? '', 10000);
        $start_date = trim((string) ($input['start_date'] ?? ''));
        $end_date = trim((string) ($input['end_date'] ?? ''));
        $is_all_day = !empty($input['is_all_day']) ? 1 : 0;
        $start_time = trim((string) ($input['start_time'] ?? ''));
        $end_time = trim((string) ($input['end_time'] ?? ''));
        $location = eottae_calendar_clean_text($input['location'] ?? '', 255);
        $area = eottae_calendar_normalize_area($input['area'] ?? '');
        $category = eottae_calendar_normalize_category($input['category'] ?? 'etc');
        $badge_style = eottae_calendar_normalize_badge($input['badge_style'] ?? 'default');
        $related_url = trim((string) ($input['related_url'] ?? ''));
        $related_post_url = trim((string) ($input['related_post_url'] ?? ''));
        $related_room_id = (int) ($input['related_room_id'] ?? 0);

        if ($title === '') {
            return array('ok' => false, 'message' => '일정 제목을 입력해 주세요.');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
            return array('ok' => false, 'message' => '시작일 형식이 올바르지 않습니다.');
        }
        if ($end_date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            $end_date = $start_date;
        }
        if ($end_date < $start_date) {
            return array('ok' => false, 'message' => '종료일은 시작일보다 빠를 수 없습니다.');
        }

        if ($is_all_day) {
            $start_time = '';
            $end_time = '';
        } else {
            if ($start_time !== '' && !preg_match('/^\d{2}:\d{2}$/', $start_time)) {
                return array('ok' => false, 'message' => '시작 시간 형식이 올바르지 않습니다.');
            }
            if ($end_time !== '' && !preg_match('/^\d{2}:\d{2}$/', $end_time)) {
                return array('ok' => false, 'message' => '종료 시간 형식이 올바르지 않습니다.');
            }
            if ($start_time !== '') {
                $start_time .= ':00';
            }
            if ($end_time !== '') {
                $end_time .= ':00';
            }
        }

        if ($related_url !== '' && !preg_match('#^https?://#i', $related_url)) {
            return array('ok' => false, 'message' => '관련 링크는 http:// 또는 https:// 로 시작해야 합니다.');
        }
        if (strlen($related_url) > 500) {
            $related_url = substr($related_url, 0, 500);
        }
        if ($related_post_url !== '' && !preg_match('#^https?://#i', $related_post_url)) {
            return array('ok' => false, 'message' => '관련 글 링크 형식이 올바르지 않습니다.');
        }
        if (strlen($related_post_url) > 500) {
            $related_post_url = substr($related_post_url, 0, 500);
        }

        if ($related_room_id > 0 && function_exists('eottae_talkroom_get_room')) {
            $room = eottae_talkroom_get_room($related_room_id);
            if (!$room) {
                $related_room_id = 0;
            }
        } else {
            $related_room_id = 0;
        }

        return array(
            'ok'      => true,
            'message' => '',
            'data'    => array(
                'title'           => $title,
                'description'     => $description,
                'start_date'      => $start_date,
                'end_date'        => $end_date,
                'start_time'      => $start_time,
                'end_time'        => $end_time,
                'is_all_day'      => $is_all_day,
                'location'        => $location,
                'area'            => $area,
                'category'        => $category,
                'badge_style'     => $badge_style,
                'related_url'     => $related_url,
                'related_post_url'=> $related_post_url,
                'related_room_id' => $related_room_id,
            ),
        );
    }
}

if (!function_exists('eottae_calendar_create_event')) {
    function eottae_calendar_create_event(array $input, array $writer)
    {
        if (!eottae_calendar_table_exists()) {
            eottae_calendar_ensure_schema();
        }

        $validated = eottae_calendar_validate_input($input);
        if (empty($validated['ok'])) {
            return $validated;
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($writer['mb_id'] ?? ''));
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 일정을 등록할 수 있습니다.');
        }

        $writer_name = get_text((string) ($writer['mb_nick'] ?? $writer['mb_name'] ?? $mb_id));
        $data = $validated['data'];
        $table = eottae_calendar_table_name();
        $now = G5_TIME_YMDHIS;

        $start_time_sql = $data['start_time'] !== '' ? "'".sql_real_escape_string($data['start_time'])."'" : 'NULL';
        $end_time_sql = $data['end_time'] !== '' ? "'".sql_real_escape_string($data['end_time'])."'" : 'NULL';

        $ok = sql_query("
            INSERT INTO `{$table}` SET
                title = '".sql_real_escape_string($data['title'])."',
                description = '".sql_real_escape_string($data['description'])."',
                start_date = '".sql_real_escape_string($data['start_date'])."',
                end_date = '".sql_real_escape_string($data['end_date'])."',
                start_time = {$start_time_sql},
                end_time = {$end_time_sql},
                is_all_day = ".(int) $data['is_all_day'].",
                location = '".sql_real_escape_string($data['location'])."',
                area = '".sql_real_escape_string($data['area'])."',
                category = '".sql_real_escape_string($data['category'])."',
                badge_style = '".sql_real_escape_string($data['badge_style'])."',
                related_url = '".sql_real_escape_string($data['related_url'])."',
                related_post_url = '".sql_real_escape_string($data['related_post_url'])."',
                related_room_id = ".(int) $data['related_room_id'].",
                source_type = 'local',
                writer_mb_id = '".sql_real_escape_string($mb_id)."',
                writer_name = '".sql_real_escape_string($writer_name)."',
                status = 'active',
                created_at = '{$now}',
                updated_at = '{$now}'
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '일정 저장에 실패했습니다.');
        }

        $event_id = (int) sql_insert_id();

        if ($event_id > 0 && function_exists('eottae_member_growth_on_calendar_event_created')) {
            eottae_member_growth_on_calendar_event_created($event_id, $mb_id);
        }

        return array(
            'ok'       => true,
            'message'  => '일정이 등록되었습니다.',
            'event_id' => $event_id,
        );
    }
}

if (!function_exists('eottae_calendar_update_event')) {
    function eottae_calendar_update_event($event_id, array $input, $editor_mb_id, $is_super = false)
    {
        $event = eottae_calendar_get_event($event_id, $is_super);
        if (!$event) {
            return array('ok' => false, 'message' => '일정을 찾을 수 없습니다.');
        }
        if (!eottae_calendar_can_edit_event($event, $editor_mb_id, $is_super)) {
            return array('ok' => false, 'message' => '수정 권한이 없습니다.');
        }

        $validated = eottae_calendar_validate_input($input);
        if (empty($validated['ok'])) {
            return $validated;
        }

        $data = $validated['data'];
        $table = eottae_calendar_table_name();
        $event_id = (int) $event_id;
        $now = G5_TIME_YMDHIS;
        $start_time_sql = $data['start_time'] !== '' ? "'".sql_real_escape_string($data['start_time'])."'" : 'NULL';
        $end_time_sql = $data['end_time'] !== '' ? "'".sql_real_escape_string($data['end_time'])."'" : 'NULL';

        $ok = sql_query("
            UPDATE `{$table}` SET
                title = '".sql_real_escape_string($data['title'])."',
                description = '".sql_real_escape_string($data['description'])."',
                start_date = '".sql_real_escape_string($data['start_date'])."',
                end_date = '".sql_real_escape_string($data['end_date'])."',
                start_time = {$start_time_sql},
                end_time = {$end_time_sql},
                is_all_day = ".(int) $data['is_all_day'].",
                location = '".sql_real_escape_string($data['location'])."',
                area = '".sql_real_escape_string($data['area'])."',
                category = '".sql_real_escape_string($data['category'])."',
                badge_style = '".sql_real_escape_string($data['badge_style'])."',
                related_url = '".sql_real_escape_string($data['related_url'])."',
                related_post_url = '".sql_real_escape_string($data['related_post_url'])."',
                related_room_id = ".(int) $data['related_room_id'].",
                updated_at = '{$now}'
            WHERE event_id = {$event_id}
              AND ".eottae_calendar_visible_sql().'
        ', false);

        if (!$ok) {
            return array('ok' => false, 'message' => '일정 수정에 실패했습니다.');
        }

        return array('ok' => true, 'message' => '일정이 수정되었습니다.', 'event_id' => $event_id);
    }
}

if (!function_exists('eottae_calendar_delete_event')) {
    function eottae_calendar_delete_event($event_id, $deleter_mb_id, $is_super = false)
    {
        $event = eottae_calendar_get_event($event_id, true);
        if (!$event || ($event['status'] ?? '') === 'deleted') {
            return array('ok' => false, 'message' => '일정을 찾을 수 없습니다.');
        }
        if (!eottae_calendar_can_delete_event($event, $deleter_mb_id, $is_super)) {
            return array('ok' => false, 'message' => '삭제 권한이 없습니다.');
        }

        if (eottae_calendar_is_google_event($event)) {
            return eottae_calendar_hide_event($event_id, $deleter_mb_id, $is_super);
        }

        $table = eottae_calendar_table_name();
        $event_id = (int) $event_id;
        $deleter_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $deleter_mb_id);
        $now = G5_TIME_YMDHIS;

        $ok = sql_query("
            UPDATE `{$table}` SET
                status = 'deleted',
                deleted_at = '{$now}',
                deleted_by = '".sql_real_escape_string($deleter_mb_id)."',
                updated_at = '{$now}'
            WHERE event_id = {$event_id}
            LIMIT 1
        ", false);

        if (!$ok) {
            return array('ok' => false, 'message' => '일정 삭제에 실패했습니다.');
        }

        return array('ok' => true, 'message' => '일정이 삭제되었습니다.');
    }
}

if (!function_exists('eottae_calendar_render_event_card')) {
    function eottae_calendar_render_event_card(array $event, $variant = 'list', array $options = array())
    {
        if (!function_exists('eottae_calendar_event_card_html')) {
            include_once G5_PATH.'/components/eottae/calendar-event-card.php';
        }
        echo eottae_calendar_event_card_html($event, $variant, $options);
    }
}

if (!function_exists('eottae_calendar_render_filter_chips')) {
    function eottae_calendar_render_filter_chips($active_category = '', array $params = array())
    {
        if (!function_exists('eottae_calendar_filter_chips_html')) {
            include_once G5_PATH.'/components/eottae/calendar-filter-chips.php';
        }
        echo eottae_calendar_filter_chips_html($active_category, $params);
    }
}
