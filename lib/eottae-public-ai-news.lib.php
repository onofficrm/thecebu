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
        if (!eottae_talkroom_table_exists($table)) {
            sql_query("
                CREATE TABLE IF NOT EXISTS `{$table}` (
                    `news_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `title` varchar(200) NOT NULL DEFAULT '',
                    `summary` varchar(500) NOT NULL DEFAULT '',
                    `source_name` varchar(80) NOT NULL DEFAULT '',
                    `source_url` varchar(255) NOT NULL DEFAULT '',
                    `category` varchar(40) NOT NULL DEFAULT 'other',
                    `is_sensitive` tinyint(1) NOT NULL DEFAULT '0',
                    `status` varchar(20) NOT NULL DEFAULT 'active',
                    `feed_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `feed_guid` varchar(255) NOT NULL DEFAULT '',
                    `published_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`news_id`),
                    KEY `idx_public_ai_news_status` (`status`, `created_at`),
                    KEY `idx_public_ai_news_category` (`category`),
                    KEY `idx_public_ai_news_feed` (`feed_id`, `feed_guid`(64)),
                    KEY `idx_public_ai_news_source_url` (`source_url`(191))
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ", false);
        }

        eottae_public_ai_external_news_upgrade_feed_columns();

        return eottae_talkroom_table_exists($table);
    }
}

if (!function_exists('eottae_public_ai_external_news_upgrade_feed_columns')) {
    function eottae_public_ai_external_news_upgrade_feed_columns()
    {
        static $done = false;
        if ($done) {
            return true;
        }

        if (!function_exists('eottae_talkroom_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $table = eottae_public_ai_external_news_table();
        if (!eottae_talkroom_table_exists($table)) {
            return false;
        }

        $cols = array();
        $res = sql_query(" SHOW COLUMNS FROM `{$table}` ", false);
        if ($res) {
            while ($row = sql_fetch_array($res)) {
                $cols[$row['Field']] = true;
            }
        }

        if (empty($cols['feed_id'])) {
            sql_query(" ALTER TABLE `{$table}` ADD `feed_id` int(11) unsigned NOT NULL DEFAULT '0' AFTER `status` ", false);
        }
        if (empty($cols['feed_guid'])) {
            sql_query(" ALTER TABLE `{$table}` ADD `feed_guid` varchar(255) NOT NULL DEFAULT '' AFTER `feed_id` ", false);
        }
        if (empty($cols['published_at'])) {
            sql_query(" ALTER TABLE `{$table}` ADD `published_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `feed_guid` ", false);
        }

        $done = true;

        return true;
    }
}

if (!function_exists('eottae_public_ai_external_news_exists_by_source')) {
    function eottae_public_ai_external_news_exists_by_source($source_url, $feed_id = 0, $feed_guid = '')
    {
        eottae_public_ai_external_news_ensure_schema();
        $table = eottae_public_ai_external_news_table();
        $source_url = trim((string) $source_url);
        $feed_id = (int) $feed_id;
        $feed_guid = trim((string) $feed_guid);

        if ($source_url !== '') {
            $esc = sql_escape_string($source_url);
            $row = sql_fetch(" SELECT news_id FROM `{$table}` WHERE source_url = '{$esc}' LIMIT 1 ", false);
            if (!empty($row['news_id'])) {
                return true;
            }
        }

        if ($feed_id > 0 && $feed_guid !== '') {
            $esc_guid = sql_escape_string(mb_substr($feed_guid, 0, 255, 'UTF-8'));
            $row = sql_fetch("
                SELECT news_id FROM `{$table}`
                WHERE feed_id = '{$feed_id}' AND feed_guid = '{$esc_guid}'
                LIMIT 1
            ", false);
            if (!empty($row['news_id'])) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('eottae_public_ai_external_news_import_rss_item')) {
    /**
     * RSS 크론·수동 수집용 (관리자 세션 불필요)
     *
     * @param array<string, string> $item
     * @param array<string, mixed>  $feed
     * @return array{ok:bool, inserted:bool, reason:string, news_id:int}
     */
    function eottae_public_ai_external_news_import_rss_item(array $item, array $feed)
    {
        if (!function_exists('eottae_public_ai_guard_scan_text')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-guard.lib.php';
        }
        if (!function_exists('eottae_public_ai_news_feed_strip_text')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-news-feed.lib.php';
        }

        eottae_public_ai_external_news_ensure_schema();

        $title = trim(strip_tags((string) ($item['title'] ?? '')));
        $link = trim((string) ($item['link'] ?? ''));
        $guid = trim((string) ($item['guid'] ?? ''));
        if ($guid === '') {
            $guid = $link;
        }

        if ($title === '' || ($link === '' && $guid === '')) {
            return array('ok' => false, 'inserted' => false, 'reason' => 'empty', 'news_id' => 0);
        }

        $feed_id = (int) ($feed['feed_id'] ?? 0);
        if (eottae_public_ai_external_news_exists_by_source($link, $feed_id, $guid)) {
            return array('ok' => true, 'inserted' => false, 'reason' => 'duplicate', 'news_id' => 0);
        }

        $summary = eottae_public_ai_news_feed_strip_text($item['description'] ?? '', 480);
        if ($summary === '') {
            $summary = eottae_public_ai_news_feed_strip_text($title, 200);
        }

        $default_cat = trim((string) ($feed['category'] ?? 'local'));
        $category = function_exists('eottae_public_ai_news_feed_guess_category')
            ? eottae_public_ai_news_feed_guess_category($title, $summary, $default_cat)
            : $default_cat;

        $cats = array_keys(eottae_public_ai_external_news_categories());
        if (!in_array($category, $cats, true)) {
            $category = 'other';
        }

        $scan = eottae_public_ai_guard_scan_text($title.' '.$summary);
        $candidate = array(
            'title'        => $title,
            'summary'      => $summary,
            'category'     => $category,
            'is_sensitive' => !empty($scan['is_sensitive']) ? 1 : 0,
        );
        if (eottae_public_ai_guard_news_is_blocked($candidate)) {
            return array('ok' => true, 'inserted' => false, 'reason' => 'blocked', 'news_id' => 0);
        }

        $published_at = '0000-00-00 00:00:00';
        $pub_raw = trim((string) ($item['published'] ?? ''));
        if ($pub_raw !== '') {
            $ts = strtotime($pub_raw);
            if ($ts) {
                $published_at = date('Y-m-d H:i:s', $ts);
            }
        }

        $source_name = trim((string) ($feed['name'] ?? ''));
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        $is_sensitive = !empty($scan['is_sensitive']) ? 1 : 0;
        $table = eottae_public_ai_external_news_table();

        $ok = (bool) sql_query("
            INSERT INTO `{$table}` SET
                title = '".sql_escape_string($title)."',
                summary = '".sql_escape_string($summary)."',
                source_name = '".sql_escape_string($source_name)."',
                source_url = '".sql_escape_string(mb_substr($link, 0, 255, 'UTF-8'))."',
                category = '".sql_escape_string($category)."',
                is_sensitive = '{$is_sensitive}',
                status = 'active',
                feed_id = '{$feed_id}',
                feed_guid = '".sql_escape_string(mb_substr($guid, 0, 255, 'UTF-8'))."',
                published_at = '".sql_escape_string($published_at)."',
                created_at = '{$now}',
                updated_at = '{$now}'
        ", false);

        return array(
            'ok'       => $ok,
            'inserted' => $ok,
            'reason'   => $ok ? 'inserted' : 'db_error',
            'news_id'  => $ok ? (int) sql_insert_id() : 0,
        );
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
            'feed_id'      => (int) ($row['feed_id'] ?? 0),
            'feed_guid'    => get_text($row['feed_guid'] ?? ''),
            'published_at' => trim((string) ($row['published_at'] ?? '')),
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
