<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_calendar_google_calendar_id')) {
    function eottae_calendar_google_calendar_id()
    {
        $default = '4932d5025ebdd69d35ff4827f24d5fe976d7ac73a6020d89dd8fdc380b30c99c@group.calendar.google.com';
        if (function_exists('g5site_cfg')) {
            $cfg = trim((string) g5site_cfg('calendar_google_id', ''));
            if ($cfg !== '') {
                return $cfg;
            }
        }

        return $default;
    }
}

if (!function_exists('eottae_calendar_google_ical_url')) {
    function eottae_calendar_google_ical_url()
    {
        $calendar_id = rawurlencode(eottae_calendar_google_calendar_id());

        return 'https://calendar.google.com/calendar/ical/'.$calendar_id.'/public/basic.ics';
    }
}

if (!function_exists('eottae_calendar_sync_logs_table')) {
    function eottae_calendar_sync_logs_table()
    {
        return G5_TABLE_PREFIX.'sebu_calendar_sync_logs';
    }
}

if (!function_exists('eottae_calendar_google_classify_title')) {
    function eottae_calendar_google_classify_title($title)
    {
        $title_lower = function_exists('mb_strtolower')
            ? mb_strtolower((string) $title, 'UTF-8')
            : strtolower((string) $title);

        $rules = array(
            'holiday'  => array('holiday', '공휴일', '휴일', '법정'),
            'festival' => array('festival', '축제'),
            'talk'     => array('세부톡', 'talk'),
            'meetup'   => array('meeting', '모임', 'meetup', '모임'),
            'travel'   => array('travel', '여행'),
            'life'     => array('생활', 'life', '정보'),
            'business' => array('업체', 'business', '행사'),
            'event'    => array('event', '이벤트'),
        );

        foreach ($rules as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if ($keyword !== '' && strpos($title_lower, $keyword) !== false) {
                    return $category;
                }
            }
        }

        return 'event';
    }
}

if (!function_exists('eottae_calendar_google_badge_for_category')) {
    function eottae_calendar_google_badge_for_category($category)
    {
        if (in_array($category, array('festival', 'event', 'meetup', 'talk'), true)) {
            return 'recommend';
        }

        return 'default';
    }
}

if (!function_exists('eottae_calendar_google_unfold_ical')) {
    function eottae_calendar_google_unfold_ical($content)
    {
        $lines = preg_split("/\r\n|\n|\r/", (string) $content);
        $out = array();
        foreach ($lines as $line) {
            if ($line === '') {
                $out[] = $line;
                continue;
            }
            if ($out && ($line[0] === ' ' || $line[0] === "\t")) {
                $out[count($out) - 1] .= substr($line, 1);
            } else {
                $out[] = $line;
            }
        }

        return implode("\n", $out);
    }
}

if (!function_exists('eottae_calendar_google_parse_datetime')) {
    function eottae_calendar_google_parse_datetime($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})Z?$/', $value, $m)) {
            return array(
                'date'      => $m[1].'-'.$m[2].'-'.$m[3],
                'time'      => $m[4].':'.$m[5].':00',
                'is_all_day'=> 0,
            );
        }

        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $value, $m)) {
            return array(
                'date'      => $m[1].'-'.$m[2].'-'.$m[3],
                'time'      => '',
                'is_all_day'=> 1,
            );
        }

        return null;
    }
}

if (!function_exists('eottae_calendar_google_parse_ical')) {
    function eottae_calendar_google_parse_ical($content)
    {
        $content = eottae_calendar_google_unfold_ical($content);
        $blocks = preg_split('/BEGIN:VEVENT\s*/', $content);
        $events = array();

        foreach ($blocks as $block) {
            if (strpos($block, 'END:VEVENT') === false) {
                continue;
            }
            $block = substr($block, 0, strpos($block, 'END:VEVENT'));
            $fields = array();
            foreach (preg_split("/\r\n|\n|\r/", $block) as $line) {
                if ($line === '' || strpos($line, ':') === false) {
                    continue;
                }
                list($key, $val) = explode(':', $line, 2);
                $key = strtoupper(trim($key));
                if (strpos($key, ';') !== false) {
                    $key = substr($key, 0, strpos($key, ';'));
                }
                $fields[$key] = trim($val);
            }

            $uid = (string) ($fields['UID'] ?? '');
            if ($uid === '') {
                continue;
            }

            $start = eottae_calendar_google_parse_datetime($fields['DTSTART'] ?? '');
            if (!$start) {
                continue;
            }

            $end = eottae_calendar_google_parse_datetime($fields['DTEND'] ?? '');
            if (!$end) {
                $end = $start;
            }

            $start_date = $start['date'];
            $end_date = $end['date'];
            if (!empty($start['is_all_day']) && !empty($end['is_all_day'])) {
                $end_ts = strtotime($end_date.' -1 day');
                if ($end_ts !== false) {
                    $end_date = date('Y-m-d', $end_ts);
                }
                if ($end_date < $start_date) {
                    $end_date = $start_date;
                }
            }

            $title = eottae_calendar_clean_text($fields['SUMMARY'] ?? 'Google 일정', 200);
            $description = eottae_calendar_clean_text($fields['DESCRIPTION'] ?? '', 10000);
            $location = eottae_calendar_clean_text($fields['LOCATION'] ?? '', 255);
            $category = eottae_calendar_google_classify_title($title);

            $events[] = array(
                'google_event_id' => $uid,
                'title'           => $title,
                'description'     => $description,
                'start_date'      => $start_date,
                'end_date'        => $end_date,
                'start_time'      => $start['time'],
                'end_time'        => $end['time'],
                'is_all_day'      => !empty($start['is_all_day']) ? 1 : 0,
                'location'        => $location,
                'category'        => $category,
                'badge_style'     => eottae_calendar_google_badge_for_category($category),
            );
        }

        return $events;
    }
}

if (!function_exists('eottae_calendar_google_fetch_ical')) {
    function eottae_calendar_google_fetch_ical()
    {
        $url = eottae_calendar_google_ical_url();
        $body = '';

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT        => 45,
                CURLOPT_USERAGENT      => 'thecebu-calendar-sync/1.0',
            ));
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($body === false || $code >= 400) {
                return array('ok' => false, 'message' => 'Google Calendar iCal 요청 실패 (HTTP '.$code.')');
            }
        } else {
            $ctx = stream_context_create(array(
                'http' => array(
                    'timeout' => 45,
                    'header'  => "User-Agent: thecebu-calendar-sync/1.0\r\n",
                ),
            ));
            $body = @file_get_contents($url, false, $ctx);
            if ($body === false || $body === '') {
                return array('ok' => false, 'message' => 'Google Calendar iCal 다운로드 실패');
            }
        }

        return array('ok' => true, 'content' => $body);
    }
}

if (!function_exists('eottae_calendar_sync_log_write')) {
    function eottae_calendar_sync_log_write(array $row)
    {
        $table = eottae_calendar_sync_logs_table();
        $safe_table = preg_replace('/[^a-z0-9_]/i', '', $table);
        if ($safe_table === '') {
            return false;
        }
        $exists = sql_fetch(" SHOW TABLES LIKE '{$safe_table}' ", false);
        if (empty($exists)) {
            return false;
        }

        $now = G5_TIME_YMDHIS;
        $source = sql_real_escape_string((string) ($row['source'] ?? 'google'));
        $status = sql_real_escape_string((string) ($row['status'] ?? 'ok'));
        $message = sql_real_escape_string((string) ($row['message'] ?? ''));
        $fetched = (int) ($row['fetched_count'] ?? 0);
        $inserted = (int) ($row['inserted_count'] ?? 0);
        $updated = (int) ($row['updated_count'] ?? 0);
        $hidden = (int) ($row['hidden_count'] ?? 0);
        $error = sql_real_escape_string((string) ($row['error_message'] ?? ''));

        return (bool) sql_query("
            INSERT INTO `{$table}` SET
                source = '{$source}',
                status = '{$status}',
                message = '{$message}',
                fetched_count = {$fetched},
                inserted_count = {$inserted},
                updated_count = {$updated},
                hidden_count = {$hidden},
                error_message = '{$error}',
                created_at = '{$now}'
        ", false);
    }
}

if (!function_exists('eottae_calendar_google_upsert_event')) {
    function eottae_calendar_google_upsert_event(array $event)
    {
        $table = eottae_calendar_table_name();
        $google_id = sql_real_escape_string((string) ($event['google_event_id'] ?? ''));
        if ($google_id === '') {
            return 'skip';
        }

        $existing = sql_fetch("
            SELECT event_id, status
            FROM `{$table}`
            WHERE google_event_id = '{$google_id}'
            LIMIT 1
        ", false);

        $now = G5_TIME_YMDHIS;
        $start_time_sql = !empty($event['start_time']) ? "'".sql_real_escape_string($event['start_time'])."'" : 'NULL';
        $end_time_sql = !empty($event['end_time']) ? "'".sql_real_escape_string($event['end_time'])."'" : 'NULL';

        $fields = "
            title = '".sql_real_escape_string($event['title'])."',
            description = '".sql_real_escape_string($event['description'])."',
            start_date = '".sql_real_escape_string($event['start_date'])."',
            end_date = '".sql_real_escape_string($event['end_date'])."',
            start_time = {$start_time_sql},
            end_time = {$end_time_sql},
            is_all_day = ".(int) ($event['is_all_day'] ?? 0).",
            location = '".sql_real_escape_string($event['location'])."',
            area = 'etc',
            category = '".sql_real_escape_string($event['category'])."',
            badge_style = '".sql_real_escape_string($event['badge_style'])."',
            source_type = 'google',
            writer_mb_id = '',
            writer_name = 'Google Calendar',
            status = 'active',
            updated_at = '{$now}'
        ";

        if (!empty($existing['event_id'])) {
            $event_id = (int) $existing['event_id'];
            sql_query("UPDATE `{$table}` SET {$fields} WHERE event_id = {$event_id} LIMIT 1", false);

            return 'updated';
        }

        sql_query("
            INSERT INTO `{$table}` SET
                {$fields},
                google_event_id = '{$google_id}',
                related_url = '',
                related_room_id = 0,
                related_post_url = '',
                created_at = '{$now}'
        ", false);

        return 'inserted';
    }
}

if (!function_exists('eottae_calendar_google_hide_missing')) {
    function eottae_calendar_google_hide_missing(array $active_google_ids, $range_start, $range_end)
    {
        $table = eottae_calendar_table_name();
        $range_start = preg_match('/^\d{4}-\d{2}-\d{2}$/', $range_start) ? $range_start : date('Y-m-d', strtotime('-90 days'));
        $range_end = preg_match('/^\d{4}-\d{2}-\d{2}$/', $range_end) ? $range_end : date('Y-m-d', strtotime('+365 days'));
        $now = G5_TIME_YMDHIS;
        $hidden = 0;

        $result = sql_query("
            SELECT event_id, google_event_id
            FROM `{$table}`
            WHERE source_type = 'google'
              AND status = 'active'
              AND start_date <= '".sql_real_escape_string($range_end)."'
              AND end_date >= '".sql_real_escape_string($range_start)."'
        ", false);

        $active_map = array_flip($active_google_ids);
        while ($row = sql_fetch_array($result)) {
            $gid = (string) ($row['google_event_id'] ?? '');
            if ($gid === '' || isset($active_map[$gid])) {
                continue;
            }
            $event_id = (int) ($row['event_id'] ?? 0);
            if ($event_id < 1) {
                continue;
            }
            if (sql_query("
                UPDATE `{$table}` SET
                    status = 'hidden',
                    updated_at = '{$now}',
                    deleted_at = '{$now}',
                    deleted_by = 'google_sync'
                WHERE event_id = {$event_id}
                LIMIT 1
            ", false)) {
                $hidden++;
            }
        }

        return $hidden;
    }
}

if (!function_exists('eottae_calendar_google_sync')) {
    function eottae_calendar_google_sync(array $options = array())
    {
        eottae_calendar_ensure_schema();

        $range_start = isset($options['range_start']) ? (string) $options['range_start'] : date('Y-m-d', strtotime('-90 days'));
        $range_end = isset($options['range_end']) ? (string) $options['range_end'] : date('Y-m-d', strtotime('+365 days'));
        $dry_run = !empty($options['dry_run']);

        $fetch = eottae_calendar_google_fetch_ical();
        if (empty($fetch['ok'])) {
            eottae_calendar_sync_log_write(array(
                'source'        => 'google',
                'status'        => 'error',
                'message'       => 'fetch failed',
                'error_message' => (string) ($fetch['message'] ?? ''),
            ));

            return array('ok' => false, 'message' => (string) ($fetch['message'] ?? '동기화 실패'));
        }

        $parsed = eottae_calendar_google_parse_ical($fetch['content']);
        $filtered = array();
        foreach ($parsed as $event) {
            if ($event['end_date'] < $range_start || $event['start_date'] > $range_end) {
                continue;
            }
            $filtered[] = $event;
        }

        if ($dry_run) {
            return array(
                'ok'            => true,
                'message'       => 'dry-run complete',
                'fetched_count' => count($filtered),
            );
        }

        $inserted = 0;
        $updated = 0;
        $google_ids = array();
        foreach ($filtered as $event) {
            $google_ids[] = (string) $event['google_event_id'];
            $result = eottae_calendar_google_upsert_event($event);
            if ($result === 'inserted') {
                $inserted++;
            } elseif ($result === 'updated') {
                $updated++;
            }
        }

        $hidden = eottae_calendar_google_hide_missing($google_ids, $range_start, $range_end);
        $message = sprintf('Google Calendar 동기화 완료 — %d건 조회, %d건 추가, %d건 갱신, %d건 숨김', count($filtered), $inserted, $updated, $hidden);

        eottae_calendar_sync_log_write(array(
            'source'         => 'google',
            'status'         => 'ok',
            'message'        => $message,
            'fetched_count'  => count($filtered),
            'inserted_count' => $inserted,
            'updated_count'  => $updated,
            'hidden_count'   => $hidden,
        ));

        return array(
            'ok'             => true,
            'message'        => $message,
            'fetched_count'  => count($filtered),
            'inserted_count' => $inserted,
            'updated_count'  => $updated,
            'hidden_count'   => $hidden,
        );
    }
}
