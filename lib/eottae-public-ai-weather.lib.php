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

        $lat = function_exists('g5site_cfg') ? (float) g5site_cfg('public_ai_weather_lat', '10.3157') : 10.3157;
        $lng = function_exists('g5site_cfg') ? (float) g5site_cfg('public_ai_weather_lon', '123.8854') : 123.8854;
        if ($lat < -90 || $lat > 90) {
            $lat = 10.3157;
        }
        if ($lng < -180 || $lng > 180) {
            $lng = 123.8854;
        }

        $url = 'https://api.open-meteo.com/v1/forecast?latitude='.rawurlencode((string) $lat)
            .'&longitude='.rawurlencode((string) $lng)
            .'&daily=weathercode,temperature_2m_max,temperature_2m_min,precipitation_probability_max'
            .'&timezone=Asia%2FManila&forecast_days=4';

        $ctx = stream_context_create(array(
            'http' => array(
                'timeout' => 8,
                'header'  => "User-Agent: SebuEottaePublicAI/1.0\r\n",
            ),
        ));
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false || trim($raw) === '') {
            return null;
        }

        $json = json_decode($raw, true);
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
