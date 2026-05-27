<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_ai_news_feed_table')) {
    function eottae_public_ai_news_feed_table()
    {
        global $g5;
        if (!isset($g5['sebu_public_ai_news_feed_table'])) {
            $g5['sebu_public_ai_news_feed_table'] = G5_TABLE_PREFIX.'sebu_public_ai_news_feed';
        }

        return $g5['sebu_public_ai_news_feed_table'];
    }
}

if (!function_exists('eottae_public_ai_news_feed_ensure_schema')) {
    function eottae_public_ai_news_feed_ensure_schema()
    {
        if (!function_exists('eottae_talkroom_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }
        if (!function_exists('eottae_public_ai_external_news_ensure_schema')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-news.lib.php';
        }

        eottae_public_ai_external_news_ensure_schema();
        eottae_public_ai_external_news_upgrade_feed_columns();

        $table = eottae_public_ai_news_feed_table();
        if (eottae_talkroom_table_exists($table)) {
            return true;
        }

        return (bool) sql_query("
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `feed_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(120) NOT NULL DEFAULT '',
                `feed_url` varchar(500) NOT NULL DEFAULT '',
                `site_url` varchar(255) NOT NULL DEFAULT '',
                `category` varchar(40) NOT NULL DEFAULT 'local',
                `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
                `fetch_interval_min` int(11) unsigned NOT NULL DEFAULT '60',
                `max_items_per_run` int(11) unsigned NOT NULL DEFAULT '8',
                `last_fetched_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `last_new_count` int(11) unsigned NOT NULL DEFAULT '0',
                `last_error` varchar(500) NOT NULL DEFAULT '',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`feed_id`),
                KEY `idx_feed_enabled` (`is_enabled`, `last_fetched_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", false);
    }
}

if (!function_exists('eottae_public_ai_news_feed_format_row')) {
    function eottae_public_ai_news_feed_format_row(array $row)
    {
        $cats = function_exists('eottae_public_ai_external_news_categories')
            ? eottae_public_ai_external_news_categories()
            : array();

        return array(
            'feed_id'            => (int) ($row['feed_id'] ?? 0),
            'name'               => get_text($row['name'] ?? ''),
            'feed_url'           => get_text($row['feed_url'] ?? ''),
            'site_url'           => get_text($row['site_url'] ?? ''),
            'category'           => trim((string) ($row['category'] ?? 'local')),
            'category_label'     => function_exists('eottae_public_ai_label')
                ? eottae_public_ai_label($cats, $row['category'] ?? 'local', '지역소식')
                : '',
            'is_enabled'         => (int) !empty($row['is_enabled']),
            'fetch_interval_min' => max(15, min(1440, (int) ($row['fetch_interval_min'] ?? 60))),
            'max_items_per_run'  => max(1, min(30, (int) ($row['max_items_per_run'] ?? 8))),
            'last_fetched_at'    => trim((string) ($row['last_fetched_at'] ?? '')),
            'last_new_count'     => (int) ($row['last_new_count'] ?? 0),
            'last_error'         => get_text($row['last_error'] ?? ''),
            'created_at'         => trim((string) ($row['created_at'] ?? '')),
            'updated_at'         => trim((string) ($row['updated_at'] ?? '')),
        );
    }
}

if (!function_exists('eottae_public_ai_news_feed_list')) {
    function eottae_public_ai_news_feed_list($enabled_only = false)
    {
        eottae_public_ai_news_feed_ensure_schema();
        $table = eottae_public_ai_news_feed_table();
        $where = $enabled_only ? " WHERE is_enabled = '1' " : '';
        $rows = array();
        $result = sql_query(" SELECT * FROM `{$table}` {$where} ORDER BY feed_id ASC ", false);
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $rows[] = eottae_public_ai_news_feed_format_row($row);
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_public_ai_news_feed_get')) {
    function eottae_public_ai_news_feed_get($feed_id)
    {
        $feed_id = (int) $feed_id;
        if ($feed_id < 1) {
            return null;
        }

        eottae_public_ai_news_feed_ensure_schema();
        $table = eottae_public_ai_news_feed_table();
        $row = sql_fetch(" SELECT * FROM `{$table}` WHERE feed_id = '{$feed_id}' LIMIT 1 ", false);

        return is_array($row) && !empty($row['feed_id']) ? eottae_public_ai_news_feed_format_row($row) : null;
    }
}

if (!function_exists('eottae_public_ai_news_feed_save')) {
    function eottae_public_ai_news_feed_save(array $data)
    {
        global $is_admin;

        if ($is_admin !== 'super') {
            return array('ok' => false, 'message' => '권한이 없습니다.');
        }

        eottae_public_ai_news_feed_ensure_schema();

        $feed_id = (int) ($data['feed_id'] ?? 0);
        $name = trim(strip_tags((string) ($data['name'] ?? '')));
        $feed_url = trim((string) ($data['feed_url'] ?? ''));
        if ($name === '' || $feed_url === '') {
            return array('ok' => false, 'message' => '피드 이름과 RSS URL을 입력해 주세요.');
        }
        if (!preg_match('#^https?://#i', $feed_url)) {
            return array('ok' => false, 'message' => 'RSS URL은 http(s)로 시작해야 합니다.');
        }

        $cats = array_keys(eottae_public_ai_external_news_categories());
        $category = trim((string) ($data['category'] ?? 'local'));
        if (!in_array($category, $cats, true)) {
            $category = 'local';
        }

        $site_url = trim((string) ($data['site_url'] ?? ''));
        if ($site_url !== '' && !preg_match('#^https?://#i', $site_url)) {
            return array('ok' => false, 'message' => '사이트 URL 형식이 올바르지 않습니다.');
        }

        $is_enabled = !empty($data['is_enabled']) ? 1 : 0;
        $fetch_interval_min = max(15, min(1440, (int) ($data['fetch_interval_min'] ?? 60)));
        $max_items_per_run = max(1, min(30, (int) ($data['max_items_per_run'] ?? 8)));
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        $table = eottae_public_ai_news_feed_table();

        if ($feed_id > 0) {
            $ok = (bool) sql_query("
                UPDATE `{$table}` SET
                    name = '".sql_escape_string($name)."',
                    feed_url = '".sql_escape_string($feed_url)."',
                    site_url = '".sql_escape_string($site_url)."',
                    category = '".sql_escape_string($category)."',
                    is_enabled = '{$is_enabled}',
                    fetch_interval_min = '{$fetch_interval_min}',
                    max_items_per_run = '{$max_items_per_run}',
                    updated_at = '{$now}'
                WHERE feed_id = '{$feed_id}'
                LIMIT 1
            ", false);
        } else {
            $ok = (bool) sql_query("
                INSERT INTO `{$table}` SET
                    name = '".sql_escape_string($name)."',
                    feed_url = '".sql_escape_string($feed_url)."',
                    site_url = '".sql_escape_string($site_url)."',
                    category = '".sql_escape_string($category)."',
                    is_enabled = '{$is_enabled}',
                    fetch_interval_min = '{$fetch_interval_min}',
                    max_items_per_run = '{$max_items_per_run}',
                    created_at = '{$now}',
                    updated_at = '{$now}'
            ", false);
            $feed_id = (int) sql_insert_id();
        }

        return array(
            'ok'      => $ok,
            'message' => $ok ? 'RSS 피드를 저장했습니다.' : '저장에 실패했습니다.',
            'feed_id' => $feed_id,
        );
    }
}

if (!function_exists('eottae_public_ai_news_feed_delete')) {
    function eottae_public_ai_news_feed_delete($feed_id)
    {
        global $is_admin;

        if ($is_admin !== 'super') {
            return array('ok' => false, 'message' => '권한이 없습니다.');
        }

        $feed_id = (int) $feed_id;
        if ($feed_id < 1) {
            return array('ok' => false, 'message' => '피드 ID가 없습니다.');
        }

        eottae_public_ai_news_feed_ensure_schema();
        $table = eottae_public_ai_news_feed_table();
        $ok = (bool) sql_query(" DELETE FROM `{$table}` WHERE feed_id = '{$feed_id}' LIMIT 1 ", false);

        return array('ok' => $ok, 'message' => $ok ? 'RSS 피드를 삭제했습니다.' : '삭제에 실패했습니다.');
    }
}

if (!function_exists('eottae_public_ai_news_feed_http_get')) {
    function eottae_public_ai_news_feed_http_get($url)
    {
        $url = trim((string) $url);
        if ($url === '' || !preg_match('#^https?://#i', $url)) {
            return array('ok' => false, 'message' => 'URL이 올바르지 않습니다.', 'body' => '');
        }

        $ua = 'SebuEottaeNewsFeed/1.0 (+'.(defined('G5_URL') ? G5_URL : '').')';

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 4,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_USERAGENT      => $ua,
                CURLOPT_SSL_VERIFYPEER => true,
            ));
            $body = curl_exec($ch);
            $err = curl_error($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($body === false || $code < 200 || $code >= 400) {
                return array(
                    'ok'      => false,
                    'message' => $err !== '' ? $err : 'HTTP '.$code,
                    'body'    => '',
                );
            }

            return array('ok' => true, 'message' => '', 'body' => (string) $body);
        }

        $ctx = stream_context_create(array(
            'http' => array(
                'timeout' => 20,
                'header'  => "User-Agent: {$ua}\r\n",
            ),
        ));
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false || trim($body) === '') {
            return array('ok' => false, 'message' => '피드를 가져오지 못했습니다.', 'body' => '');
        }

        return array('ok' => true, 'message' => '', 'body' => (string) $body);
    }
}

if (!function_exists('eottae_public_ai_news_feed_strip_text')) {
    function eottae_public_ai_news_feed_strip_text($html, $max = 500)
    {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        if ($text === '') {
            return '';
        }
        if (function_exists('cut_str')) {
            return cut_str($text, (int) $max, '…');
        }

        return mb_strlen($text, 'UTF-8') > $max ? mb_substr($text, 0, $max - 1, 'UTF-8').'…' : $text;
    }
}

if (!function_exists('eottae_public_ai_news_feed_parse_xml')) {
    /**
     * @return array{ok:bool, message:string, items:array<int, array<string, string>>}
     */
    function eottae_public_ai_news_feed_parse_xml($xml_string)
    {
        $xml_string = trim((string) $xml_string);
        if ($xml_string === '') {
            return array('ok' => false, 'message' => 'XML이 비어 있습니다.', 'items' => array());
        }

        libxml_use_internal_errors(true);
        $xml = @simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
        if ($xml === false) {
            $err = libxml_get_errors();
            libxml_clear_errors();

            return array(
                'ok'      => false,
                'message' => !empty($err[0]->message) ? trim((string) $err[0]->message) : 'XML 파싱 실패',
                'items'   => array(),
            );
        }

        $items = array();

        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $node) {
                $link = trim((string) ($node->link ?? ''));
                if ($link === '' && isset($node->link['href'])) {
                    $link = trim((string) $node->link['href']);
                }
                $guid = trim((string) ($node->guid ?? ''));
                if ($guid === '') {
                    $guid = $link;
                }
                $items[] = array(
                    'title'       => trim((string) ($node->title ?? '')),
                    'link'        => $link,
                    'description' => trim((string) ($node->description ?? '')),
                    'guid'        => $guid,
                    'published'   => trim((string) ($node->pubDate ?? '')),
                );
            }
        } elseif (isset($xml->entry)) {
            $entries = $xml->entry;
            if (!is_array($entries) && !($entries instanceof Traversable)) {
                $entries = array($xml->entry);
            }
            foreach ($entries as $node) {
                $link = '';
                if (isset($node->link)) {
                    foreach ($node->link as $lnk) {
                        $rel = strtolower(trim((string) ($lnk['rel'] ?? 'alternate')));
                        $href = trim((string) ($lnk['href'] ?? ''));
                        if ($href !== '' && ($rel === '' || $rel === 'alternate')) {
                            $link = $href;
                            break;
                        }
                    }
                }
                $summary = trim((string) ($node->summary ?? ''));
                if ($summary === '') {
                    $summary = trim((string) ($node->content ?? ''));
                }
                $published = trim((string) ($node->published ?? ''));
                if ($published === '') {
                    $published = trim((string) ($node->updated ?? ''));
                }
                $guid = trim((string) ($node->id ?? ''));
                if ($guid === '') {
                    $guid = $link;
                }
                $items[] = array(
                    'title'       => trim((string) ($node->title ?? '')),
                    'link'        => $link,
                    'description' => $summary,
                    'guid'        => $guid,
                    'published'   => $published,
                );
            }
        }

        if (empty($items)) {
            return array('ok' => false, 'message' => 'RSS/Atom 항목을 찾지 못했습니다.', 'items' => array());
        }

        return array('ok' => true, 'message' => '', 'items' => $items);
    }
}

if (!function_exists('eottae_public_ai_news_feed_guess_category')) {
    function eottae_public_ai_news_feed_guess_category($title, $summary, $default = 'local')
    {
        $blob = mb_strtolower($title.' '.$summary, 'UTF-8');
        $map = array(
            'airport'   => array('airport', 'flight', '공항', '항공', 'mactan', 'cebu pacific'),
            'traffic'   => array('traffic', '교통', 'ferry', '페리', 'port'),
            'festival'  => array('festival', '축제', 'sinulog', 'event', '행사'),
            'tourism'   => array('tourism', '관광', 'resort', '리조트', 'hotel', '호텔', 'beach'),
            'business'  => array('mall', '쇼핑', 'business', '상권', 'opening', '오픈'),
        );

        foreach ($map as $cat => $words) {
            foreach ($words as $word) {
                if ($word !== '' && mb_strpos($blob, $word, 0, 'UTF-8') !== false) {
                    return $cat;
                }
            }
        }

        return $default;
    }
}

if (!function_exists('eottae_public_ai_news_feed_should_fetch')) {
    function eottae_public_ai_news_feed_should_fetch(array $feed, $force = false)
    {
        if ($force) {
            return true;
        }

        if (empty($feed['is_enabled'])) {
            return false;
        }

        $last = trim((string) ($feed['last_fetched_at'] ?? ''));
        if ($last === '' || $last === '0000-00-00 00:00:00') {
            return true;
        }

        $interval = max(15, (int) ($feed['fetch_interval_min'] ?? 60));
        $next = strtotime($last) + ($interval * 60);

        return $next <= time();
    }
}

if (!function_exists('eottae_public_ai_news_feed_update_status')) {
    function eottae_public_ai_news_feed_update_status($feed_id, array $patch)
    {
        $feed_id = (int) $feed_id;
        if ($feed_id < 1) {
            return false;
        }

        $table = eottae_public_ai_news_feed_table();
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        $sets = array("updated_at = '{$now}'");

        if (array_key_exists('last_fetched_at', $patch)) {
            $sets[] = "last_fetched_at = '".sql_escape_string((string) $patch['last_fetched_at'])."'";
        }
        if (array_key_exists('last_new_count', $patch)) {
            $sets[] = "last_new_count = '".(int) $patch['last_new_count']."'";
        }
        if (array_key_exists('last_error', $patch)) {
            $sets[] = "last_error = '".sql_escape_string(mb_substr((string) $patch['last_error'], 0, 500, 'UTF-8'))."'";
        }

        return (bool) sql_query(" UPDATE `{$table}` SET ".implode(', ', $sets)." WHERE feed_id = '{$feed_id}' LIMIT 1 ", false);
    }
}

if (!function_exists('eottae_public_ai_news_feed_fetch_one')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_public_ai_news_feed_fetch_one($feed_id, array $options = array())
    {
        if (!function_exists('eottae_public_ai_external_news_import_rss_item')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-news.lib.php';
        }

        $force = !empty($options['force']);
        $dry_run = !empty($options['dry_run']);
        $feed = eottae_public_ai_news_feed_get($feed_id);

        if (!$feed) {
            return array('ok' => false, 'message' => '피드를 찾을 수 없습니다.', 'inserted' => 0);
        }

        if (!$force && empty($feed['is_enabled'])) {
            return array('ok' => false, 'message' => '비활성 피드입니다.', 'inserted' => 0);
        }

        if (!$force && !eottae_public_ai_news_feed_should_fetch($feed, false)) {
            return array('ok' => true, 'message' => '수집 주기 전입니다.', 'inserted' => 0, 'skipped' => 'interval');
        }

        $http = eottae_public_ai_news_feed_http_get($feed['feed_url']);
        if (empty($http['ok'])) {
            if (!$dry_run) {
                eottae_public_ai_news_feed_update_status($feed_id, array(
                    'last_fetched_at' => defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'),
                    'last_new_count'  => 0,
                    'last_error'      => (string) ($http['message'] ?? 'fetch failed'),
                ));
            }

            return array('ok' => false, 'message' => (string) ($http['message'] ?? ''), 'inserted' => 0);
        }

        $parsed = eottae_public_ai_news_feed_parse_xml($http['body']);
        if (empty($parsed['ok'])) {
            if (!$dry_run) {
                eottae_public_ai_news_feed_update_status($feed_id, array(
                    'last_fetched_at' => defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'),
                    'last_new_count'  => 0,
                    'last_error'      => (string) ($parsed['message'] ?? 'parse failed'),
                ));
            }

            return array('ok' => false, 'message' => (string) ($parsed['message'] ?? ''), 'inserted' => 0);
        }

        $limit = (int) ($feed['max_items_per_run'] ?? 8);
        $items = array_slice($parsed['items'], 0, $limit);
        $inserted = 0;
        $skipped_dup = 0;
        $skipped_block = 0;
        $skipped_empty = 0;

        foreach ($items as $item) {
            if ($dry_run) {
                $inserted++;
                continue;
            }

            $import = eottae_public_ai_external_news_import_rss_item($item, $feed);
            if (!empty($import['inserted'])) {
                $inserted++;
            } elseif (($import['reason'] ?? '') === 'duplicate') {
                $skipped_dup++;
            } elseif (($import['reason'] ?? '') === 'blocked') {
                $skipped_block++;
            } else {
                $skipped_empty++;
            }
        }

        if (!$dry_run) {
            eottae_public_ai_news_feed_update_status($feed_id, array(
                'last_fetched_at' => defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'),
                'last_new_count'  => $inserted,
                'last_error'      => '',
            ));
        }

        return array(
            'ok'            => true,
            'message'       => '수집 완료',
            'inserted'      => $inserted,
            'skipped_dup'   => $skipped_dup,
            'skipped_block' => $skipped_block,
            'skipped_empty' => $skipped_empty,
            'feed_id'       => $feed_id,
        );
    }
}

if (!function_exists('eottae_public_ai_news_feed_run_all')) {
    function eottae_public_ai_news_feed_run_all(array $options = array())
    {
        $feeds = eottae_public_ai_news_feed_list(true);
        $force = !empty($options['force']);
        $dry_run = !empty($options['dry_run']);
        $summary = array(
            'ok'       => true,
            'feeds'    => 0,
            'inserted' => 0,
            'errors'   => array(),
            'details'  => array(),
        );

        foreach ($feeds as $feed) {
            if (!$force && !eottae_public_ai_news_feed_should_fetch($feed, false)) {
                continue;
            }

            $summary['feeds']++;
            $result = eottae_public_ai_news_feed_fetch_one((int) $feed['feed_id'], array(
                'force'   => $force,
                'dry_run' => $dry_run,
            ));
            $summary['inserted'] += (int) ($result['inserted'] ?? 0);
            $summary['details'][] = array(
                'feed_id' => (int) $feed['feed_id'],
                'name'    => $feed['name'],
                'result'  => $result,
            );
            if (empty($result['ok']) && ($result['skipped'] ?? '') !== 'interval') {
                $summary['errors'][] = $feed['name'].': '.($result['message'] ?? 'error');
            }
        }

        if (!empty($summary['errors'])) {
            $summary['ok'] = $summary['inserted'] > 0;
        }

        return $summary;
    }
}
