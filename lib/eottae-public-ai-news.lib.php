<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_ai_external_news_table')) {
    function eottae_public_ai_external_news_table()
    {
        global $g5;
        if (!isset($g5['sebu_public_ai_external_news_table'])) {
            $g5['sebu_public_ai_external_news_table'] = G5_TABLE_PREFIX.'sebu_public_ai_external_news';
        }

        return $g5['sebu_public_ai_external_news_table'];
    }
}

if (!function_exists('eottae_public_ai_external_news_categories')) {
    function eottae_public_ai_external_news_categories()
    {
        return array(
            'festival'  => '축제·행사',
            'tourism'   => '관광',
            'airport'   => '공항·교통',
            'traffic'   => '교통',
            'local'     => '지역소식',
            'business'  => '생활·상권',
            'other'     => '기타',
        );
    }
}

if (!function_exists('eottae_public_ai_external_news_ensure_schema')) {
    function eottae_public_ai_external_news_ensure_schema()
    {
        if (!function_exists('eottae_talkroom_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $table = eottae_public_ai_external_news_table();
        if (eottae_talkroom_table_exists($table)) {
            return true;
        }

        return (bool) sql_query("
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `news_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(200) NOT NULL DEFAULT '',
                `summary` varchar(500) NOT NULL DEFAULT '',
                `source_name` varchar(80) NOT NULL DEFAULT '',
                `source_url` varchar(255) NOT NULL DEFAULT '',
                `category` varchar(40) NOT NULL DEFAULT 'other',
                `is_sensitive` tinyint(1) NOT NULL DEFAULT '0',
                `status` varchar(20) NOT NULL DEFAULT 'active',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`news_id`),
                KEY `idx_public_ai_news_status` (`status`, `created_at`),
                KEY `idx_public_ai_news_category` (`category`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", false);
    }
}

if (!function_exists('eottae_public_ai_external_news_format_row')) {
    function eottae_public_ai_external_news_format_row(array $row)
    {
        $cats = eottae_public_ai_external_news_categories();

        return array(
            'news_id'      => (int) ($row['news_id'] ?? 0),
            'title'        => get_text($row['title'] ?? ''),
            'summary'      => get_text($row['summary'] ?? ''),
            'source_name'  => get_text($row['source_name'] ?? ''),
            'source_url'   => get_text($row['source_url'] ?? ''),
            'category'     => trim((string) ($row['category'] ?? 'other')),
            'category_label' => eottae_public_ai_label($cats, $row['category'] ?? 'other', '기타'),
            'is_sensitive' => (int) !empty($row['is_sensitive']),
            'status'       => trim((string) ($row['status'] ?? 'active')),
            'created_at'   => trim((string) ($row['created_at'] ?? '')),
            'updated_at'   => trim((string) ($row['updated_at'] ?? '')),
        );
    }
}

if (!function_exists('eottae_public_ai_external_news_save')) {
    function eottae_public_ai_external_news_save(array $data)
    {
        global $is_admin;

        if ($is_admin !== 'super') {
            return array('ok' => false, 'message' => '권한이 없습니다.');
        }

        if (!function_exists('eottae_public_ai_guard_scan_text')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-guard.lib.php';
        }

        eottae_public_ai_external_news_ensure_schema();
        $news_id = (int) ($data['news_id'] ?? 0);
        $title = trim(strip_tags((string) ($data['title'] ?? '')));
        $summary = trim(strip_tags((string) ($data['summary'] ?? '')));
        if ($title === '' || $summary === '') {
            return array('ok' => false, 'message' => '제목과 요약을 입력해 주세요.');
        }

        $cats = array_keys(eottae_public_ai_external_news_categories());
        $category = trim((string) ($data['category'] ?? 'other'));
        if (!in_array($category, $cats, true)) {
            $category = 'other';
        }

        $source_name = trim(strip_tags((string) ($data['source_name'] ?? '')));
        $source_url = trim((string) ($data['source_url'] ?? ''));
        if ($source_url !== '' && !preg_match('#^https?://#i', $source_url)) {
            return array('ok' => false, 'message' => '출처 URL 형식이 올바르지 않습니다.');
        }

        $scan = eottae_public_ai_guard_scan_text($title.' '.$summary);
        $is_sensitive = !empty($data['is_sensitive']) || !empty($scan['is_sensitive'])
            || eottae_public_ai_guard_news_is_blocked(array(
                'title' => $title,
                'summary' => $summary,
                'category' => $category,
                'is_sensitive' => !empty($scan['is_sensitive']),
            )) ? 1 : 0;

        $status = trim((string) ($data['status'] ?? 'active'));
        if (!in_array($status, array('active', 'hidden'), true)) {
            $status = 'active';
        }

        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        $table = eottae_public_ai_external_news_table();

        if ($news_id > 0) {
            $ok = (bool) sql_query("
                UPDATE `{$table}` SET
                    title = '".sql_escape_string($title)."',
                    summary = '".sql_escape_string($summary)."',
                    source_name = '".sql_escape_string($source_name)."',
                    source_url = '".sql_escape_string($source_url)."',
                    category = '".sql_escape_string($category)."',
                    is_sensitive = '{$is_sensitive}',
                    status = '".sql_escape_string($status)."',
                    updated_at = '{$now}'
                WHERE news_id = '{$news_id}'
                LIMIT 1
            ", false);
        } else {
            $ok = (bool) sql_query("
                INSERT INTO `{$table}` SET
                    title = '".sql_escape_string($title)."',
                    summary = '".sql_escape_string($summary)."',
                    source_name = '".sql_escape_string($source_name)."',
                    source_url = '".sql_escape_string($source_url)."',
                    category = '".sql_escape_string($category)."',
                    is_sensitive = '{$is_sensitive}',
                    status = '".sql_escape_string($status)."',
                    created_at = '{$now}',
                    updated_at = '{$now}'
            ", false);
            $news_id = (int) sql_insert_id();
        }

        return array(
            'ok'      => $ok,
            'message' => $ok ? '외부뉴스를 저장했습니다.' : '저장에 실패했습니다.',
            'news_id' => $news_id,
        );
    }
}

if (!function_exists('eottae_public_ai_external_news_list_active')) {
    function eottae_public_ai_external_news_list_active($limit = 10)
    {
        eottae_public_ai_external_news_ensure_schema();
        $table = eottae_public_ai_external_news_table();
        $limit = max(1, min(50, (int) $limit));
        $rows = array();

        $result = sql_query("
            SELECT *
            FROM `{$table}`
            WHERE status = 'active'
            ORDER BY news_id DESC
            LIMIT {$limit}
        ", false);

        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $formatted = eottae_public_ai_external_news_format_row($row);
                if (!eottae_public_ai_guard_news_is_blocked($formatted)) {
                    $rows[] = $formatted;
                }
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_public_ai_generator_build_external_news_candidates')) {
    function eottae_public_ai_generator_build_external_news_candidates(array $sources)
    {
        if (!function_exists('eottae_public_ai_guard_news_is_blocked')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-guard.lib.php';
        }

        $candidates = array();
        $news_list = isset($sources['external_news']) && is_array($sources['external_news'])
            ? $sources['external_news'] : array();

        foreach (array_slice($news_list, 0, 2) as $news) {
            $news_id = (int) ($news['news_id'] ?? 0);
            if ($news_id < 1 || eottae_public_ai_guard_news_is_blocked($news)) {
                continue;
            }

            $category = trim((string) ($news['category'] ?? 'other'));
            $source_url = trim((string) ($news['source_url'] ?? ''));
            $action_label = $source_url !== '' ? '공식 안내 보기' : '';

            if (in_array($category, array('airport', 'traffic'), true)) {
                $message = '공항·교통 관련 소식이 있어 이동 계획이 있는 분들은 출발 전 확인해보시면 좋겠습니다.'."\n"
                    .'자세한 내용은 공식 안내를 참고해 주세요.';
            } elseif ($category === 'festival') {
                $message = '세부 지역 행사 관련 소식이 있어요.'."\n"
                    .'관심 있는 분들은 공식 안내를 확인해보시고, 다녀오신 분들은 후기 남겨주세요.';
            } else {
                $message = '세부 지역 관련 소식이 있어요.'."\n"
                    .'관심 있는 분들은 공식 안내를 확인해보시고, 경험 있으신 분들은 의견을 나눠주세요.';
            }

            $candidates[] = array(
                'trigger_type'          => 'external_news',
                'source_type'           => 'external_news',
                'source_id'             => $news_id,
                'title'                 => trim((string) ($news['title'] ?? '')),
                'message'               => $message,
                'action_label'          => $action_label,
                'action_url'            => $source_url,
                'admin_memo'            => 'news:'.$category,
                'force_admin_approval'  => 1,
                'is_sensitive'          => (int) ($news['is_sensitive'] ?? 0),
            );
        }

        return $candidates;
    }
}
