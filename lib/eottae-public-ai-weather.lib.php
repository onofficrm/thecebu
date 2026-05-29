<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_ai_weather_table')) {
    function eottae_public_ai_weather_table()
    {
        global $g5;
        if (!isset($g5['sebu_public_ai_weather_table'])) {
            $g5['sebu_public_ai_weather_table'] = G5_TABLE_PREFIX.'sebu_public_ai_weather';
        }

        return $g5['sebu_public_ai_weather_table'];
    }
}

if (!function_exists('eottae_public_ai_weather_ensure_schema')) {
    function eottae_public_ai_weather_ensure_schema()
    {
        if (!function_exists('eottae_talkroom_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $table = eottae_public_ai_weather_table();
        if (eottae_talkroom_table_exists($table)) {
            return true;
        }

        return (bool) sql_query("
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `weather_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `forecast_date` date NOT NULL,
                `weather_summary` varchar(120) NOT NULL DEFAULT '',
                `rain_chance` tinyint(3) unsigned NOT NULL DEFAULT '0',
                `temperature_min` smallint(6) DEFAULT NULL,
                `temperature_max` smallint(6) DEFAULT NULL,
                `source` varchar(80) NOT NULL DEFAULT 'manual',
                `source_note` varchar(255) NOT NULL DEFAULT '',
                `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`weather_id`),
                UNIQUE KEY `uk_public_ai_weather_date` (`forecast_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", false);
    }
}

if (!function_exists('eottae_public_ai_weather_normalize_date')) {
    function eottae_public_ai_weather_normalize_date($date)
    {
        $date = trim((string) $date);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : '';
    }
}

if (!function_exists('eottae_public_ai_weather_get_for_date')) {
    function eottae_public_ai_weather_get_for_date($date)
    {
        eottae_public_ai_weather_ensure_schema();
        $date = eottae_public_ai_weather_normalize_date($date);
        if ($date === '') {
            return null;
        }

        $table = eottae_public_ai_weather_table();
        $row = sql_fetch(" SELECT * FROM `{$table}` WHERE forecast_date = '".sql_real_escape_string($date)."' LIMIT 1 ", false);
        if (empty($row['weather_id'])) {
            return null;
        }

        return eottae_public_ai_weather_format_row($row);
    }
}

if (!function_exists('eottae_public_ai_weather_format_row')) {
    function eottae_public_ai_weather_format_row(array $row)
    {
        return array(
            'weather_id'      => (int) ($row['weather_id'] ?? 0),
            'forecast_date'   => (string) ($row['forecast_date'] ?? ''),
            'weather_summary' => get_text($row['weather_summary'] ?? ''),
            'rain_chance'     => (int) ($row['rain_chance'] ?? 0),
            'temperature_min' => isset($row['temperature_min']) ? (int) $row['temperature_min'] : null,
            'temperature_max' => isset($row['temperature_max']) ? (int) $row['temperature_max'] : null,
            'source'          => get_text($row['source'] ?? 'manual'),
            'source_note'     => get_text($row['source_note'] ?? ''),
            'updated_at'      => trim((string) ($row['updated_at'] ?? '')),
            'created_at'      => trim((string) ($row['created_at'] ?? '')),
        );
    }
}

if (!function_exists('eottae_public_ai_weather_save')) {
    function eottae_public_ai_weather_save(array $data)
    {
        global $is_admin;

        if ($is_admin !== 'super') {
            return array('ok' => false, 'message' => '권한이 없습니다.');
        }

        eottae_public_ai_weather_ensure_schema();
        $date = eottae_public_ai_weather_normalize_date($data['forecast_date'] ?? '');
        if ($date === '') {
            return array('ok' => false, 'message' => '날짜 형식이 올바르지 않습니다.');
        }

        $summary = trim(strip_tags((string) ($data['weather_summary'] ?? '')));
        if ($summary === '') {
            return array('ok' => false, 'message' => '날씨 요약을 입력해 주세요.');
        }

        $rain = max(0, min(100, (int) ($data['rain_chance'] ?? 0)));
        $tmin = isset($data['temperature_min']) && $data['temperature_min'] !== '' ? (int) $data['temperature_min'] : 'NULL';
        $tmax = isset($data['temperature_max']) && $data['temperature_max'] !== '' ? (int) $data['temperature_max'] : 'NULL';
        $source = trim(strip_tags((string) ($data['source'] ?? 'manual')));
        if ($source === '') {
            $source = 'manual';
        }
        $note = trim(strip_tags((string) ($data['source_note'] ?? '')));
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');

        $tmin_sql = $tmin === 'NULL' ? 'NULL' : "'".(int) $tmin."'";
        $tmax_sql = $tmax === 'NULL' ? 'NULL' : "'".(int) $tmax."'";

        $ok = (bool) sql_query("
            INSERT INTO `".eottae_public_ai_weather_table()."` SET
                forecast_date = '".sql_real_escape_string($date)."',
                weather_summary = '".sql_escape_string($summary)."',
                rain_chance = '{$rain}',
                temperature_min = {$tmin_sql},
                temperature_max = {$tmax_sql},
                source = '".sql_escape_string($source)."',
                source_note = '".sql_escape_string($note)."',
                created_at = '{$now}',
                updated_at = '{$now}'
            ON DUPLICATE KEY UPDATE
                weather_summary = VALUES(weather_summary),
                rain_chance = VALUES(rain_chance),
                temperature_min = VALUES(temperature_min),
                temperature_max = VALUES(temperature_max),
                source = VALUES(source),
                source_note = VALUES(source_note),
                updated_at = VALUES(updated_at)
        ", false);

        return array(
            'ok'      => $ok,
            'message' => $ok ? '날씨 정보를 저장했습니다.' : '저장에 실패했습니다.',
        );
    }
}

if (!function_exists('eottae_public_ai_weather_list_recent')) {
    function eottae_public_ai_weather_list_recent($days = 7)
    {
        eottae_public_ai_weather_ensure_schema();
        $table = eottae_public_ai_weather_table();
        $days = max(1, min(30, (int) $days));
        $start = date('Y-m-d', strtotime('-1 day'));
        $end = date('Y-m-d', strtotime('+'.($days - 1).' days'));

        $rows = array();
        $result = sql_query("
            SELECT *
            FROM `{$table}`
            WHERE forecast_date >= '".sql_real_escape_string($start)."'
              AND forecast_date <= '".sql_real_escape_string($end)."'
            ORDER BY forecast_date ASC
        ", false);

        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $rows[] = eottae_public_ai_weather_format_row($row);
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_public_ai_weather_wmo_summary_ko')) {
    function eottae_public_ai_weather_wmo_summary_ko($code)
    {
        $code = (int) $code;
        if ($code === 0) {
            return '맑음';
        }
        if (in_array($code, array(1, 2, 3), true)) {
            return '구름 조금';
        }
        if (in_array($code, array(45, 48), true)) {
            return '안개';
        }
        if (in_array($code, array(51, 53, 55, 56, 57), true)) {
            return '이슬비';
        }
        if (in_array($code, array(61, 63, 65, 66, 67, 80, 81, 82), true)) {
            return '비';
        }
        if (in_array($code, array(71, 73, 75, 77, 85, 86), true)) {
            return '눈';
        }
        if (in_array($code, array(95, 96, 99), true)) {
            return '뇌우';
        }

        return '흐림';
    }
}

if (!function_exists('eottae_public_ai_weather_upsert_system')) {
    function eottae_public_ai_weather_upsert_system(array $data)
    {
        eottae_public_ai_weather_ensure_schema();
        $date = eottae_public_ai_weather_normalize_date($data['forecast_date'] ?? '');
        if ($date === '') {
            return null;
        }

        $summary = trim(strip_tags((string) ($data['weather_summary'] ?? '')));
        if ($summary === '') {
            return null;
        }

        $rain = max(0, min(100, (int) ($data['rain_chance'] ?? 0)));
        $tmin = isset($data['temperature_min']) && $data['temperature_min'] !== '' && $data['temperature_min'] !== null
            ? (int) $data['temperature_min'] : null;
        $tmax = isset($data['temperature_max']) && $data['temperature_max'] !== '' && $data['temperature_max'] !== null
            ? (int) $data['temperature_max'] : null;
        $source = trim(strip_tags((string) ($data['source'] ?? 'open-meteo')));
        if ($source === '') {
            $source = 'open-meteo';
        }
        $note = trim(strip_tags((string) ($data['source_note'] ?? '')));
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');

        $tmin_sql = $tmin === null ? 'NULL' : "'".(int) $tmin."'";
        $tmax_sql = $tmax === null ? 'NULL' : "'".(int) $tmax."'";

        $ok = (bool) sql_query("
            INSERT INTO `".eottae_public_ai_weather_table()."` SET
                forecast_date = '".sql_real_escape_string($date)."',
                weather_summary = '".sql_escape_string($summary)."',
                rain_chance = '{$rain}',
                temperature_min = {$tmin_sql},
                temperature_max = {$tmax_sql},
                source = '".sql_escape_string($source)."',
                source_note = '".sql_escape_string($note)."',
                created_at = '{$now}',
                updated_at = '{$now}'
            ON DUPLICATE KEY UPDATE
                weather_summary = VALUES(weather_summary),
                rain_chance = VALUES(rain_chance),
                temperature_min = VALUES(temperature_min),
                temperature_max = VALUES(temperature_max),
                source = VALUES(source),
                source_note = VALUES(source_note),
                updated_at = VALUES(updated_at)
        ", false);

        if (!$ok) {
            return null;
        }

        return eottae_public_ai_weather_get_for_date($date);
    }
}

if (!function_exists('eottae_public_ai_weather_local_now')) {
    function eottae_public_ai_weather_local_now()
    {
        try {
            $dt = new DateTime('now', new DateTimeZone('Asia/Manila'));
        } catch (Exception $e) {
            $dt = new DateTime('now');
        }

        return array(
            'date' => $dt->format('Y-m-d'),
            'datetime' => $dt->format('Y-m-d H:i:s'),
            'hour' => $dt->format('H:i'),
            'ts' => $dt->getTimestamp(),
        );
    }
}

if (!function_exists('eottae_public_ai_weather_coords')) {
    function eottae_public_ai_weather_coords()
    {
        if (!function_exists('g5site_cfg') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }

        $lat = function_exists('g5site_cfg') ? (float) g5site_cfg('public_ai_weather_lat', '10.3157') : 10.3157;
        $lng = function_exists('g5site_cfg') ? (float) g5site_cfg('public_ai_weather_lon', '123.8854') : 123.8854;
        if ($lat < -90 || $lat > 90) {
            $lat = 10.3157;
        }
        if ($lng < -180 || $lng > 180) {
            $lng = 123.8854;
        }

        return array('lat' => $lat, 'lng' => $lng);
    }
}

if (!function_exists('eottae_public_ai_weather_http_get_json')) {
    function eottae_public_ai_weather_http_get_json($url)
    {
        $raw = false;
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_HTTPHEADER => array('User-Agent: SebuEottaePublicAI/1.0'),
            ));
            $raw = curl_exec($ch);
            curl_close($ch);
        } else {
            $ctx = stream_context_create(array(
                'http' => array(
                    'timeout' => 8,
                    'header'  => "User-Agent: SebuEottaePublicAI/1.0\r\n",
                ),
            ));
            $raw = @file_get_contents($url, false, $ctx);
        }

        if ($raw === false || trim((string) $raw) === '') {
            return null;
        }

        $json = json_decode($raw, true);

        return is_array($json) ? $json : null;
    }
}

if (!function_exists('eottae_public_ai_weather_is_rain_weathercode')) {
    function eottae_public_ai_weather_is_rain_weathercode($code)
    {
        $code = (int) $code;

        return in_array($code, array(51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82, 95, 96, 99), true);
    }
}

if (!function_exists('eottae_public_ai_weather_is_weather_question')) {
    function eottae_public_ai_weather_is_weather_question($question)
    {
        $text = function_exists('mb_strtolower')
            ? mb_strtolower((string) $question, 'UTF-8')
            : strtolower((string) $question);

        return (bool) preg_match('/날씨|기온|온도|강수|우산|비\s*오|비가|소나기|폭우|태풍|맑|흐림|더워|덥|춥|습도|weather|rain|umbrella/u', $text);
    }
}

if (!function_exists('eottae_public_ai_weather_target_date_from_question')) {
    function eottae_public_ai_weather_target_date_from_question($question)
    {
        $local = eottae_public_ai_weather_local_now();
        $base_ts = strtotime($local['date'].' 12:00:00');
        $text = function_exists('mb_strtolower')
            ? mb_strtolower((string) $question, 'UTF-8')
            : strtolower((string) $question);

        if (preg_match('/모레/u', $text)) {
            return date('Y-m-d', strtotime('+2 day', $base_ts));
        }
        if (preg_match('/내일/u', $text)) {
            return date('Y-m-d', strtotime('+1 day', $base_ts));
        }
        if (preg_match('/(\d{4})[.\-\/년\s]*(\d{1,2})[.\-\/월\s]*(\d{1,2})/u', $text, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }

        return $local['date'];
    }
}

if (!function_exists('eottae_public_ai_weather_target_label')) {
    function eottae_public_ai_weather_target_label($date)
    {
        $date = eottae_public_ai_weather_normalize_date($date);
        $local = eottae_public_ai_weather_local_now();
        $today = $local['date'];
        $tomorrow = date('Y-m-d', strtotime($today.' +1 day'));

        if ($date === $today) {
            return '오늘';
        }
        if ($date === $tomorrow) {
            return '내일';
        }

        return date('n월 j일', strtotime($date));
    }
}

if (!function_exists('eottae_public_ai_weather_fetch_hourly_forecast')) {
    /**
     * Open-Meteo 시간별 예보 (질문 시점 실시간 조회)
     *
     * @return array|null
     */
    function eottae_public_ai_weather_fetch_hourly_forecast($forecast_days = 2)
    {
        static $cache = null;
        if (is_array($cache)) {
            return $cache;
        }

        $coords = eottae_public_ai_weather_coords();
        $forecast_days = max(1, min(3, (int) $forecast_days));
        $url = 'https://api.open-meteo.com/v1/forecast?latitude='.rawurlencode((string) $coords['lat'])
            .'&longitude='.rawurlencode((string) $coords['lng'])
            .'&hourly=temperature_2m,precipitation_probability,precipitation,weathercode'
            .'&timezone=Asia%2FManila&forecast_days='.$forecast_days;

        $json = eottae_public_ai_weather_http_get_json($url);
        if (!is_array($json) || empty($json['hourly']['time']) || !is_array($json['hourly']['time'])) {
            return null;
        }

        $hours = array();
        $times = $json['hourly']['time'];
        $count = count($times);
        for ($i = 0; $i < $count; $i++) {
            $time = (string) ($times[$i] ?? '');
            if ($time === '') {
                continue;
            }
            $hours[] = array(
                'time' => $time,
                'date' => substr($time, 0, 10),
                'hour' => substr($time, 11, 5),
                'temperature' => isset($json['hourly']['temperature_2m'][$i])
                    ? (int) round((float) $json['hourly']['temperature_2m'][$i]) : null,
                'rain_chance' => isset($json['hourly']['precipitation_probability'][$i])
                    ? (int) round((float) $json['hourly']['precipitation_probability'][$i]) : 0,
                'precipitation' => isset($json['hourly']['precipitation'][$i])
                    ? (float) $json['hourly']['precipitation'][$i] : 0.0,
                'weathercode' => isset($json['hourly']['weathercode'][$i])
                    ? (int) $json['hourly']['weathercode'][$i] : 0,
            );
        }

        $cache = array(
            'timezone' => (string) ($json['timezone'] ?? 'Asia/Manila'),
            'hours' => $hours,
            'fetched_at' => defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'),
        );

        return $cache;
    }
}

if (!function_exists('eottae_public_ai_weather_hour_is_rainy')) {
    function eottae_public_ai_weather_hour_is_rainy(array $hour)
    {
        $rain_chance = (int) ($hour['rain_chance'] ?? 0);
        $precipitation = (float) ($hour['precipitation'] ?? 0);
        $code = (int) ($hour['weathercode'] ?? 0);

        return $rain_chance >= 40 || $precipitation >= 0.1 || eottae_public_ai_weather_is_rain_weathercode($code);
    }
}

if (!function_exists('eottae_public_ai_weather_build_life_qa_snapshot')) {
    function eottae_public_ai_weather_build_life_qa_snapshot($question)
    {
        $target_date = eottae_public_ai_weather_target_date_from_question($question);
        $forecast = eottae_public_ai_weather_fetch_hourly_forecast(3);
        if (!is_array($forecast) || empty($forecast['hours'])) {
            return null;
        }

        $day_hours = array_values(array_filter($forecast['hours'], function ($hour) use ($target_date) {
            return ($hour['date'] ?? '') === $target_date;
        }));
        if (empty($day_hours)) {
            return null;
        }

        $local = eottae_public_ai_weather_local_now();
        $now_ts = (int) $local['ts'];
        $now_date = $local['date'];
        $now_hour = $local['hour'];

        $current_temp = null;
        $temps = array();
        $codes = array();
        $max_rain_chance = 0;
        $max_future_rain_chance = 0;
        $first_rain_hour = '';
        $peak_rain_hour = '';
        $rain_hours = array();

        foreach ($day_hours as $hour) {
            if (isset($hour['temperature'])) {
                $temps[] = (int) $hour['temperature'];
            }
            $codes[] = (int) ($hour['weathercode'] ?? 0);
            $hour_rain = (int) ($hour['rain_chance'] ?? 0);
            $max_rain_chance = max($max_rain_chance, $hour_rain);

            $hour_label = (string) ($hour['hour'] ?? '');
            $is_future = ($target_date > $now_date) || ($target_date === $now_date && $hour_label >= date('H:00', $now_ts));
            if ($is_future) {
                if ($hour_rain > $max_future_rain_chance) {
                    $max_future_rain_chance = $hour_rain;
                    $peak_rain_hour = $hour_label;
                }
                if (eottae_public_ai_weather_hour_is_rainy($hour)) {
                    $rain_hours[] = $hour_label;
                    if ($first_rain_hour === '') {
                        $first_rain_hour = $hour_label;
                    }
                }
            }

            if ($target_date === $now_date && $hour_label === date('H:00', $now_ts) && isset($hour['temperature'])) {
                $current_temp = (int) $hour['temperature'];
            }
        }

        if ($current_temp === null && $target_date === $now_date) {
            foreach ($day_hours as $hour) {
                $hour_label = (string) ($hour['hour'] ?? '');
                if ($hour_label <= $now_hour && isset($hour['temperature'])) {
                    $current_temp = (int) $hour['temperature'];
                }
            }
        }

        if ($first_rain_hour === '' && $peak_rain_hour !== '' && $max_future_rain_chance >= 50) {
            $first_rain_hour = $peak_rain_hour;
        }

        $temp_min = !empty($temps) ? min($temps) : null;
        $temp_max = !empty($temps) ? max($temps) : null;
        $summary_code = !empty($codes) ? (int) round(array_sum($codes) / count($codes)) : 0;

        return array(
            'ok' => true,
            'target_date' => $target_date,
            'target_label' => eottae_public_ai_weather_target_label($target_date),
            'current_temp' => $current_temp,
            'temp_min' => $temp_min,
            'temp_max' => $temp_max,
            'max_rain_chance' => $max_rain_chance,
            'summary' => eottae_public_ai_weather_wmo_summary_ko($summary_code),
            'first_rain_hour' => $first_rain_hour,
            'rain_hours' => array_values(array_unique($rain_hours)),
            'source' => 'Open-Meteo',
            'fetched_at' => (string) ($forecast['fetched_at'] ?? ''),
        );
    }
}

if (!function_exists('eottae_public_ai_weather_format_hour_ko')) {
    function eottae_public_ai_weather_format_hour_ko($hour)
    {
        $hour = trim((string) $hour);
        if ($hour === '' || !preg_match('/^(\d{2}):(\d{2})$/', $hour, $m)) {
            return $hour;
        }

        return (int) $m[1].'시';
    }
}

if (!function_exists('eottae_public_ai_weather_format_life_qa_answer')) {
    function eottae_public_ai_weather_format_life_qa_answer(array $snapshot, $question = '')
    {
        if (empty($snapshot['ok'])) {
            return '';
        }

        $label = (string) ($snapshot['target_label'] ?? '오늘');
        $lines = array($label.' 세부 날씨 예보입니다.');

        $temp_bits = array();
        if (isset($snapshot['current_temp']) && $snapshot['current_temp'] !== null) {
            $temp_bits[] = '현재 기온 약 '.(int) $snapshot['current_temp'].'°C';
        }
        if (isset($snapshot['temp_min'], $snapshot['temp_max']) && $snapshot['temp_min'] !== null && $snapshot['temp_max'] !== null) {
            $temp_bits[] = $label.' 최저 '.(int) $snapshot['temp_min'].'°C / 최고 '.(int) $snapshot['temp_max'].'°C 예상';
        }
        if (!empty($temp_bits)) {
            $lines[] = implode(', ', $temp_bits).'이에요.';
        }

        $summary = trim((string) ($snapshot['summary'] ?? ''));
        if ($summary !== '') {
            $lines[] = '하늘 상태는 '.$summary.' 예상입니다.';
        }

        $max_rain = (int) ($snapshot['max_rain_chance'] ?? 0);
        $first_rain = trim((string) ($snapshot['first_rain_hour'] ?? ''));
        if ($max_rain > 0) {
            $rain_line = '최대 강수확률은 '.$max_rain.'%';
            if ($first_rain !== '') {
                $rain_line .= '이며, '.eottae_public_ai_weather_format_hour_ko($first_rain).'부터 비가 올 가능성이 있습니다';
            } elseif ($max_rain >= 50) {
                $rain_line .= '로 비 소식이 있어 보입니다';
            }
            if ($max_rain >= 40) {
                $lines[] = $rain_line.'. 우산을 챙기시면 좋겠어요.';
            } else {
                $lines[] = $rain_line.'입니다.';
            }
        } elseif (preg_match('/비|우산|강수|소나기/u', (string) $question)) {
            $lines[] = '현재 예보 기준으로는 '.$label.' 뚜렷한 비 소식은 크지 않아 보입니다.';
        }

        $lines[] = '예보는 변동될 수 있습니다. (출처: Open-Meteo)';

        return implode("\n\n", $lines);
    }
}

if (!function_exists('eottae_public_ai_weather_answer_life_qa')) {
    function eottae_public_ai_weather_answer_life_qa($question)
    {
        if (!eottae_public_ai_weather_is_weather_question($question)) {
            return '';
        }

        $snapshot = eottae_public_ai_weather_build_life_qa_snapshot($question);
        if (!is_array($snapshot)) {
            $date = eottae_public_ai_weather_target_date_from_question($question);
            if (function_exists('eottae_public_ai_weather_fetch_from_api')) {
                eottae_public_ai_weather_fetch_from_api($date);
            }
            $stored = function_exists('eottae_public_ai_weather_get_for_date')
                ? eottae_public_ai_weather_get_for_date($date)
                : null;
            if (!is_array($stored)) {
                return '';
            }

            $label = eottae_public_ai_weather_target_label($date);
            $lines = array($label.' 세부 날씨 예보입니다.');
            if (isset($stored['temperature_min'], $stored['temperature_max']) && $stored['temperature_min'] !== null && $stored['temperature_max'] !== null) {
                $lines[] = '예상 기온은 '.(int) $stored['temperature_min'].'°C ~ '.(int) $stored['temperature_max'].'°C, 강수확률은 '.(int) ($stored['rain_chance'] ?? 0).'%입니다.';
            } else {
                $lines[] = trim((string) ($stored['weather_summary'] ?? ''));
            }
            $lines[] = '시간대별 비 예보는 확인되지 않아 일별 요약만 안내드립니다. (출처: '.get_text($stored['source'] ?? '세부어때').')';

            return implode("\n\n", $lines);
        }

        return eottae_public_ai_weather_format_life_qa_answer($snapshot, $question);
    }
}

if (!function_exists('eottae_public_ai_weather_fetch_from_api')) {
    /**
     * Open-Meteo (무료) — 세부 좌표 기준 일별 예보
     *
     * @return array|null
     */
    function eottae_public_ai_weather_fetch_from_api($date)
    {
        if (!function_exists('g5site_cfg') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }

        $date = eottae_public_ai_weather_normalize_date($date);
        if ($date === '') {
            return null;
        }

        $coords = eottae_public_ai_weather_coords();
        $url = 'https://api.open-meteo.com/v1/forecast?latitude='.rawurlencode((string) $coords['lat'])
            .'&longitude='.rawurlencode((string) $coords['lng'])
            .'&daily=weathercode,temperature_2m_max,temperature_2m_min,precipitation_probability_max'
            .'&timezone=Asia%2FManila&forecast_days=4';

        $json = eottae_public_ai_weather_http_get_json($url);
        if (!is_array($json) || empty($json['daily']['time']) || !is_array($json['daily']['time'])) {
            return null;
        }

        $idx = array_search($date, $json['daily']['time'], true);
        if ($idx === false) {
            return null;
        }

        $code = isset($json['daily']['weathercode'][$idx]) ? (int) $json['daily']['weathercode'][$idx] : 0;
        $tmax = isset($json['daily']['temperature_2m_max'][$idx]) ? (int) round((float) $json['daily']['temperature_2m_max'][$idx]) : null;
        $tmin = isset($json['daily']['temperature_2m_min'][$idx]) ? (int) round((float) $json['daily']['temperature_2m_min'][$idx]) : null;
        $rain = isset($json['daily']['precipitation_probability_max'][$idx])
            ? (int) round((float) $json['daily']['precipitation_probability_max'][$idx]) : 0;

        $summary = eottae_public_ai_weather_wmo_summary_ko($code);
        if ($tmin !== null && $tmax !== null) {
            $summary .= ' ('.$tmin.'~'.$tmax.'°C)';
        }

        return eottae_public_ai_weather_upsert_system(array(
            'forecast_date'   => $date,
            'weather_summary' => $summary,
            'rain_chance'     => $rain,
            'temperature_min' => $tmin,
            'temperature_max' => $tmax,
            'source'          => 'open-meteo',
            'source_note'     => 'Open-Meteo 자동 수집',
        ));
    }
}

if (!function_exists('eottae_public_ai_weather_detect_tone')) {
    function eottae_public_ai_weather_detect_tone(array $weather)
    {
        $summary = mb_strtolower((string) ($weather['weather_summary'] ?? ''), 'UTF-8');
        $rain = (int) ($weather['rain_chance'] ?? 0);
        $tmax = isset($weather['temperature_max']) ? (int) $weather['temperature_max'] : null;

        if ($rain >= 50 || preg_match('/비|소나기|폭우|태풍|호우/u', $summary)) {
            return 'rain';
        }
        if ($tmax !== null && $tmax >= 33) {
            return 'hot';
        }
        if (preg_match('/맑|화창|쾌청|맑음/u', $summary)) {
            return 'clear';
        }

        return 'general';
    }
}

if (!function_exists('eottae_public_ai_generator_build_weather_candidates')) {
    function eottae_public_ai_generator_build_weather_candidates(array $sources)
    {
        $candidates = array();
        $weather_rows = isset($sources['weather']) && is_array($sources['weather']) ? $sources['weather'] : array();
        $today = isset($sources['today']) ? (string) $sources['today'] : date('Y-m-d');
        $tomorrow = isset($sources['tomorrow']) ? (string) $sources['tomorrow'] : date('Y-m-d', strtotime('+1 day'));

        $slots = array(
            $today    => '오늘',
            $tomorrow => '내일',
        );

        foreach ($slots as $date => $label) {
            if (!isset($weather_rows[$date]) || !is_array($weather_rows[$date])) {
                continue;
            }
            $w = $weather_rows[$date];
            $weather_id = (int) ($w['weather_id'] ?? 0);
            if ($weather_id < 1) {
                continue;
            }

            $tone = eottae_public_ai_weather_detect_tone($w);
            $source_note = trim((string) ($w['source_note'] ?? ''));
            $source_line = '';
            if ($source_note !== '') {
                $source_line = "\n(참고: ".$source_note.')';
            } elseif (($w['source'] ?? '') !== '' && ($w['source'] ?? '') !== 'manual') {
                $source_line = "\n(출처: ".$w['source'].')';
            }

            if ($tone === 'rain') {
                $message = $label.'은 세부에 비 예보가 있을 수 있어요 ☔'."\n"
                    .'외출하시는 분들은 우산을 챙기시면 좋겠습니다.'."\n"
                    .'비 오는 날 세부에서 가기 좋은 실내 장소도 추천해주세요.'.$source_line;
            } elseif ($tone === 'hot') {
                $message = $label.'은 낮에 많이 더울 수 있어요.'."\n"
                    .'아이들과 외출하시는 분들은 실내 일정도 고려해보세요.'.$source_line;
            } elseif ($tone === 'clear') {
                $message = $label.' 세부 날씨가 화창할 수 있어요.'."\n"
                    .'이런 날은 막탄 바다나 리조트 수영장 가기 좋을 것 같아요.'."\n"
                    .'오늘 외출 계획 있으세요?'.$source_line;
            } else {
                $summary = trim((string) ($w['weather_summary'] ?? ''));
                $message = $label.' 세부 날씨 예보: '.$summary."\n"
                    .'일정 잡으실 때 참고해보세요. (날씨는 변동될 수 있어요)'.$source_line;
            }

            if (preg_match('/태풍|폭우|호우|대설|강풍|위험/u', (string) ($w['weather_summary'] ?? ''))) {
                $message .= "\n".'기상 관련 공식 안내를 한번 확인해보시면 좋겠습니다.';
            }

            $candidates[] = array(
                'trigger_type' => 'weather',
                'source_type'  => 'weather',
                'source_id'    => $weather_id,
                'title'        => $label.' 날씨',
                'message'      => $message,
                'action_label' => '',
                'action_url'   => '',
                'admin_memo'   => 'weather:'.$tone,
            );
        }

        return $candidates;
    }
}
