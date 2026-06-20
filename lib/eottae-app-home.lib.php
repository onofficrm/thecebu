<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_app_include_once')) {
    function eottae_app_include_once($path)
    {
        if (is_file($path)) {
            include_once $path;
        }
    }
}

if (!function_exists('eottae_app_table_exists')) {
    function eottae_app_table_exists($table)
    {
        $table = trim((string) $table);
        if ($table === '') {
            return false;
        }

        $row = sql_fetch(" SHOW TABLES LIKE '".sql_escape_string($table)."' ", false);

        return !empty($row);
    }
}

if (!function_exists('eottae_app_event_table')) {
    function eottae_app_event_table()
    {
        return G5_TABLE_PREFIX.'eottae_app_event';
    }
}

if (!function_exists('eottae_app_ensure_schema')) {
    function eottae_app_ensure_schema()
    {
        $table = eottae_app_event_table();
        sql_query("
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `event_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `event_name` varchar(60) NOT NULL DEFAULT '',
                `event_label` varchar(120) NOT NULL DEFAULT '',
                `mb_id` varchar(20) NOT NULL DEFAULT '',
                `interest` varchar(40) NOT NULL DEFAULT '',
                `url` varchar(500) NOT NULL DEFAULT '',
                `user_agent` varchar(255) NOT NULL DEFAULT '',
                `ip_addr` varchar(45) NOT NULL DEFAULT '',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`event_id`),
                KEY `idx_event_created` (`event_name`, `created_at`),
                KEY `idx_interest_created` (`interest`, `created_at`),
                KEY `idx_mb_created` (`mb_id`, `created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", false);
    }
}

if (!function_exists('eottae_app_interest_options')) {
    function eottae_app_interest_options()
    {
        return array(
            'food' => array('label' => '맛집', 'shop_category' => '맛집'),
            'medical' => array('label' => '병원', 'shop_category' => '병원'),
            'realty' => array('label' => '부동산', 'shop_category' => ''),
            'golf' => array('label' => '골프', 'shop_category' => ''),
            'family' => array('label' => '가족생활', 'shop_category' => ''),
            'benefit' => array('label' => '쿠폰/혜택', 'shop_category' => ''),
        );
    }
}

if (!function_exists('eottae_app_normalize_interest')) {
    function eottae_app_normalize_interest($interest)
    {
        $interest = preg_replace('/[^a-z0-9_-]/i', '', (string) $interest);
        $options = eottae_app_interest_options();

        return isset($options[$interest]) ? $interest : '';
    }
}

if (!function_exists('eottae_app_event_record')) {
    function eottae_app_event_record($event_name, $event_label = '', $interest = '', $url = '', $mb_id = '')
    {
        $event_name = preg_replace('/[^a-z0-9_.-]/i', '', (string) $event_name);
        if ($event_name === '') {
            return false;
        }

        eottae_app_ensure_schema();
        $table = eottae_app_event_table();
        $event_label = cut_str(trim(strip_tags((string) $event_label)), 120, '');
        $interest = eottae_app_normalize_interest($interest);
        $url = cut_str(trim((string) $url), 500, '');
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $agent = cut_str((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 255, '');
        $ip = cut_str((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 45, '');

        sql_query(" INSERT INTO `{$table}` SET
            event_name = '".sql_escape_string($event_name)."',
            event_label = '".sql_escape_string($event_label)."',
            mb_id = '".sql_escape_string($mb_id)."',
            interest = '".sql_escape_string($interest)."',
            url = '".sql_escape_string($url)."',
            user_agent = '".sql_escape_string($agent)."',
            ip_addr = '".sql_escape_string($ip)."',
            created_at = '".G5_TIME_YMDHIS."' ", false);

        return true;
    }
}

if (!function_exists('eottae_app_event_stats')) {
    function eottae_app_event_stats($since = '')
    {
        $table = eottae_app_event_table();
        $empty = array('total' => 0, 'home' => 0, 'onboarding' => 0, 'nearby' => 0, 'coupon' => 0, 'talk' => 0, 'phone' => 0, 'map' => 0, 'checkin' => 0);
        if (!eottae_app_table_exists($table)) {
            return $empty;
        }

        $where = "1=1";
        if ($since !== '') {
            $where .= " AND created_at >= '".sql_escape_string($since)."'";
        }

        $stats = $empty;
        $row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$table}` WHERE {$where} ", false);
        $stats['total'] = (int) ($row['cnt'] ?? 0);
        $result = sql_query(" SELECT event_name, COUNT(*) AS cnt FROM `{$table}` WHERE {$where} GROUP BY event_name ", false);
        while ($event = sql_fetch_array($result)) {
            $name = (string) ($event['event_name'] ?? '');
            $count = (int) ($event['cnt'] ?? 0);
            if (strpos($name, 'home') !== false) {
                $stats['home'] += $count;
            } elseif (strpos($name, 'onboarding') !== false) {
                $stats['onboarding'] += $count;
            } elseif (strpos($name, 'nearby') !== false) {
                $stats['nearby'] += $count;
            } elseif (strpos($name, 'coupon') !== false || strpos($name, 'benefit') !== false) {
                $stats['coupon'] += $count;
            } elseif (strpos($name, 'talk') !== false) {
                $stats['talk'] += $count;
            } elseif (strpos($name, 'phone') !== false) {
                $stats['phone'] += $count;
            } elseif (strpos($name, 'map') !== false) {
                $stats['map'] += $count;
            } elseif (strpos($name, 'checkin') !== false) {
                $stats['checkin'] += $count;
            }
        }

        return $stats;
    }
}

if (!function_exists('eottae_app_latest_shop_cards')) {
    function eottae_app_latest_shop_cards($limit = 5, $category = '')
    {
        global $g5;

        $limit = max(1, min(12, (int) $limit));
        $bo_table = function_exists('eottae_shop_table') ? eottae_shop_table() : (defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop');
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return array();
        }

        $write_table = $g5['write_prefix'].$bo_table;
        if (!eottae_app_table_exists($write_table)) {
            return array();
        }

        eottae_app_include_once(G5_PATH.'/components/eottae/shop-card.php');

        $where = " wr_is_comment = 0 ";
        $category = trim((string) $category);
        if ($category !== '') {
            $where .= " AND (ca_name = '".sql_escape_string($category)."' OR wr_1 = '".sql_escape_string($category)."') ";
        }

        $result = sql_query("
            SELECT *
            FROM `{$write_table}`
            WHERE {$where}
            ORDER BY wr_datetime DESC, wr_id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $shop = function_exists('eottae_shop_from_write') ? eottae_shop_from_write($row, $bo_table) : array();
            $wr_id = (int) ($row['wr_id'] ?? 0);
            if ($wr_id < 1) {
                continue;
            }

            $thumb = function_exists('eottae_shop_card_thumb') ? eottae_shop_card_thumb($row, $bo_table) : '';
            if ($thumb !== '' && function_exists('eottae_map_public_url')) {
                $thumb = eottae_map_public_url($thumb);
            }

            $href = function_exists('eottae_shop_view_url')
                ? eottae_shop_view_url($wr_id, $bo_table)
                : G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id;
            $phone = trim((string) ($shop['phone'] ?? ''));
            $address = trim((string) ($shop['address'] ?? ''));
            $lat = trim((string) ($shop['lat'] ?? ''));
            $lng = trim((string) ($shop['lng'] ?? ''));

            $rows[] = array(
                'wr_id'      => $wr_id,
                'title'      => trim((string) ($shop['name'] ?? ($row['wr_subject'] ?? ''))),
                'category'   => trim((string) ($shop['category'] ?? ($row['ca_name'] ?? ''))),
                'region'     => trim((string) ($shop['region'] ?? '')),
                'address'    => $address,
                'status'     => trim((string) ($shop['status'] ?? '')),
                'thumb'      => $thumb,
                'href'       => $href,
                'phone'      => $phone,
                'phone_href' => function_exists('eottae_tel_href') ? eottae_tel_href($phone) : ($phone !== '' ? 'tel:'.preg_replace('/[^0-9+]/', '', $phone) : '#'),
                'map_href'   => function_exists('eottae_maps_directions_url') ? eottae_maps_directions_url($lat, $lng, $address) : '#',
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_app_member_summary')) {
    function eottae_app_member_summary($mb_id)
    {
        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return array(
                'notification_total' => 0,
                'message_unread'     => 0,
                'talk_summary'       => array(),
                'coupon_count'       => 0,
                'saved_count'        => 0,
                'cards'              => array(),
            );
        }

        eottae_app_include_once(G5_LIB_PATH.'/eottae-message.lib.php');
        eottae_app_include_once(G5_LIB_PATH.'/eottae-notification.lib.php');
        eottae_app_include_once(G5_LIB_PATH.'/eottae-talkroom.lib.php');
        eottae_app_include_once(G5_LIB_PATH.'/eottae-talkroom-dashboard.lib.php');
        eottae_app_include_once(G5_LIB_PATH.'/eottae-coupon.lib.php');

        $notification = function_exists('eottae_mypage_notification_summary')
            ? eottae_mypage_notification_summary($mb_id)
            : array('total' => 0, 'message_unread' => 0, 'talk_hub' => array());
        $talk = isset($notification['talk_hub']) && is_array($notification['talk_hub'])
            ? $notification['talk_hub']
            : (function_exists('eottae_talkroom_mypage_hub_summary') ? eottae_talkroom_mypage_hub_summary($mb_id) : array());
        $coupon_count = function_exists('eottae_coupon_count_active') ? (int) eottae_coupon_count_active($mb_id) : 0;
        $saved_count = function_exists('eottae_get_saved_shop_ids') ? count(eottae_get_saved_shop_ids($mb_id, 100)) : 0;

        $notification_total = (int) ($notification['total'] ?? 0);
        $message_unread = (int) ($notification['message_unread'] ?? 0);
        $talk_activity = (int) ($talk['new_posts'] ?? 0)
            + (int) ($talk['new_comments'] ?? 0)
            + (int) ($talk['notifications'] ?? 0)
            + (int) ($talk['owner_tasks'] ?? 0);

        return array(
            'notification_total' => $notification_total,
            'message_unread'     => $message_unread,
            'talk_summary'       => $talk,
            'coupon_count'       => $coupon_count,
            'saved_count'        => $saved_count,
            'cards'              => array(
                array('label' => '알림', 'value' => $notification_total, 'desc' => $notification_total > 0 ? '확인할 소식이 있어요' : '새 알림 없음', 'href' => G5_URL.'/page/eottae-notifications.php'),
                array('label' => '내 세부톡', 'value' => $talk_activity, 'desc' => (string) ($talk['summary_line'] ?? '톡방 새 활동 보기'), 'href' => function_exists('eottae_mypage_talk_url') ? eottae_mypage_talk_url() : G5_URL.'/page/eottae-mypage-talk.php'),
                array('label' => '쿠폰', 'value' => $coupon_count, 'desc' => $coupon_count > 0 ? '사용 가능한 혜택' : '받을 수 있는 혜택 확인', 'href' => G5_URL.'/page/eottae-coupons.php'),
                array('label' => '저장 업체', 'value' => $saved_count, 'desc' => $saved_count > 0 ? '찜한 업체 바로가기' : '관심 업체를 저장해 보세요', 'href' => G5_URL.'/page/eottae-saved-shops.php'),
            ),
        );
    }
}

if (!function_exists('eottae_app_active_coupons')) {
    function eottae_app_active_coupons($mb_id, $limit = 3)
    {
        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return array();
        }

        eottae_app_include_once(G5_LIB_PATH.'/eottae-coupon.lib.php');
        if (!function_exists('eottae_coupon_get_member_list')) {
            return array();
        }

        $rows = eottae_coupon_get_member_list($mb_id, 'active');

        return array_slice($rows, 0, max(1, min(6, (int) $limit)));
    }
}

if (!function_exists('eottae_app_talk_preview')) {
    function eottae_app_talk_preview($limit = 4)
    {
        eottae_app_include_once(G5_LIB_PATH.'/eottae-talkroom.lib.php');
        if (!function_exists('eottae_talkroom_home_hero_payload')) {
            return array('rooms' => array(), 'list_url' => G5_URL.'/page/eottae-talk.php');
        }

        $payload = eottae_talkroom_home_hero_payload(2, 2);
        $rooms = array();
        foreach (array_merge((array) ($payload['hot'] ?? array()), (array) ($payload['new'] ?? array())) as $room) {
            $room_id = (int) ($room['room_id'] ?? 0);
            if ($room_id < 1 || isset($rooms[$room_id])) {
                continue;
            }
            $rooms[$room_id] = $room;
            if (count($rooms) >= $limit) {
                break;
            }
        }

        return array(
            'rooms'      => array_values($rooms),
            'list_url'   => (string) ($payload['list_url'] ?? G5_URL.'/page/eottae-talk.php'),
            'create_url' => (string) ($payload['create_url'] ?? G5_URL.'/page/eottae-talk-create.php'),
        );
    }
}
