<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!defined('EOTTae_AD_PLATFORM_SLOT_SHOP_TOP')) {
    define('EOTTae_AD_PLATFORM_SLOT_SHOP_TOP', 'shop_top');
}
if (!defined('EOTTae_AD_PLATFORM_SLOT_MARKET_TOP')) {
    define('EOTTae_AD_PLATFORM_SLOT_MARKET_TOP', 'board_market_top');
}
if (!defined('EOTTae_AD_PLATFORM_SLOT_JOB_TOP')) {
    define('EOTTae_AD_PLATFORM_SLOT_JOB_TOP', 'board_job_top');
}
if (!defined('EOTTae_AD_PLATFORM_SLOT_ESTATE_TOP')) {
    define('EOTTae_AD_PLATFORM_SLOT_ESTATE_TOP', 'board_estate_top');
}
if (!defined('EOTTae_AD_PLATFORM_SLOT_PREMIUM')) {
    define('EOTTae_AD_PLATFORM_SLOT_PREMIUM', 'ad_board_premium');
}

if (!function_exists('eottae_ad_platform_slot_table')) {
    function eottae_ad_platform_slot_table()
    {
        return G5_TABLE_PREFIX.'eottae_ad_slot';
    }
}

if (!function_exists('eottae_ad_platform_campaign_table')) {
    function eottae_ad_platform_campaign_table()
    {
        return G5_TABLE_PREFIX.'eottae_ad_campaign';
    }
}

if (!function_exists('eottae_ad_platform_statuses')) {
    function eottae_ad_platform_statuses()
    {
        return array(
            'draft'          => '임시저장',
            'pending_review' => '승인 대기',
            'approved'       => '승인됨',
            'scheduled'      => '예약',
            'active'         => '노출 중',
            'waitlisted'     => '대기등록',
            'rejected'       => '반려',
            'paused'         => '일시중지',
            'expired'        => '종료',
            'cancelled'      => '취소',
        );
    }
}

if (!function_exists('eottae_ad_platform_status_label')) {
    function eottae_ad_platform_status_label($status)
    {
        $labels = eottae_ad_platform_statuses();
        $status = trim((string) $status);

        return isset($labels[$status]) ? $labels[$status] : $status;
    }
}

if (!function_exists('eottae_ad_platform_ensure_schema')) {
    function eottae_ad_platform_ensure_schema()
    {
        $slot_table = eottae_ad_platform_slot_table();
        $campaign_table = eottae_ad_platform_campaign_table();

        sql_query(" CREATE TABLE IF NOT EXISTS `{$slot_table}` (
            `slot_id` int unsigned NOT NULL AUTO_INCREMENT,
            `slot_code` varchar(40) NOT NULL DEFAULT '',
            `slot_name` varchar(120) NOT NULL DEFAULT '',
            `slot_desc` varchar(500) NOT NULL DEFAULT '',
            `bo_table` varchar(20) NOT NULL DEFAULT '',
            `page_type` varchar(30) NOT NULL DEFAULT '',
            `max_active_ads` tinyint unsigned NOT NULL DEFAULT '1',
            `point_per_day` int NOT NULL DEFAULT '300',
            `min_days` int unsigned NOT NULL DEFAULT '3',
            `max_days` int unsigned NOT NULL DEFAULT '90',
            `requires_image` tinyint unsigned NOT NULL DEFAULT '0',
            `requires_review` tinyint unsigned NOT NULL DEFAULT '1',
            `is_premium` tinyint unsigned NOT NULL DEFAULT '0',
            `is_active` tinyint unsigned NOT NULL DEFAULT '1',
            `sort_order` int NOT NULL DEFAULT '0',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`slot_id`),
            UNIQUE KEY `uniq_slot_code` (`slot_code`),
            KEY `idx_active_sort` (`is_active`, `sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ", false);

        sql_query(" CREATE TABLE IF NOT EXISTS `{$campaign_table}` (
            `ad_id` int unsigned NOT NULL AUTO_INCREMENT,
            `slot_id` int unsigned NOT NULL DEFAULT '0',
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `shop_bo_table` varchar(20) NOT NULL DEFAULT '',
            `shop_wr_id` int unsigned NOT NULL DEFAULT '0',
            `status` varchar(20) NOT NULL DEFAULT 'pending_review',
            `title` varchar(255) NOT NULL DEFAULT '',
            `description` text NOT NULL,
            `button_text` varchar(100) NOT NULL DEFAULT '자세히 보기',
            `link_url` varchar(500) NOT NULL DEFAULT '',
            `image_url` varchar(500) NOT NULL DEFAULT '',
            `start_date` date NOT NULL DEFAULT '0000-00-00',
            `end_date` date NOT NULL DEFAULT '0000-00-00',
            `days` int unsigned NOT NULL DEFAULT '0',
            `total_points` int NOT NULL DEFAULT '0',
            `points_charged` int NOT NULL DEFAULT '0',
            `waitlist_order` int unsigned NOT NULL DEFAULT '0',
            `review_message` varchar(500) NOT NULL DEFAULT '',
            `approved_by` varchar(20) NOT NULL DEFAULT '',
            `approved_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `impressions` int unsigned NOT NULL DEFAULT '0',
            `clicks` int unsigned NOT NULL DEFAULT '0',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`ad_id`),
            KEY `idx_slot_status_dates` (`slot_id`, `status`, `start_date`, `end_date`),
            KEY `idx_member_status` (`mb_id`, `status`),
            KEY `idx_waitlist` (`slot_id`, `status`, `waitlist_order`, `created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ", false);

        $count = sql_fetch(" select count(*) as cnt from `{$slot_table}` ");
        if ((int) ($count['cnt'] ?? 0) === 0) {
            eottae_ad_platform_seed_slots();
        }

        eottae_ad_platform_upgrade_schema();
        eottae_ad_platform_maintain_if_due();

        return true;
    }
}

if (!function_exists('eottae_ad_platform_stat_daily_table')) {
    function eottae_ad_platform_stat_daily_table()
    {
        return G5_TABLE_PREFIX.'eottae_ad_stat_daily';
    }
}

if (!function_exists('eottae_ad_platform_upgrade_schema')) {
    function eottae_ad_platform_upgrade_schema()
    {
        $campaign_table = eottae_ad_platform_campaign_table();
        $columns = array(
            'target_category'      => "varchar(50) NOT NULL DEFAULT ''",
            'target_region'        => "varchar(50) NOT NULL DEFAULT ''",
            'bid_bonus'            => "int NOT NULL DEFAULT '0'",
            'extend_reminder_sent' => "tinyint unsigned NOT NULL DEFAULT '0'",
        );

        foreach ($columns as $column => $definition) {
            $exists = sql_fetch(" SHOW COLUMNS FROM `{$campaign_table}` LIKE '".sql_escape_string($column)."' ");
            if (empty($exists)) {
                sql_query(" ALTER TABLE `{$campaign_table}` ADD `{$column}` {$definition} ", false);
            }
        }

        $stat_table = eottae_ad_platform_stat_daily_table();
        sql_query(" CREATE TABLE IF NOT EXISTS `{$stat_table}` (
            `stat_id` int unsigned NOT NULL AUTO_INCREMENT,
            `ad_id` int unsigned NOT NULL DEFAULT '0',
            `stat_date` date NOT NULL DEFAULT '0000-00-00',
            `impressions` int unsigned NOT NULL DEFAULT '0',
            `clicks` int unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`stat_id`),
            UNIQUE KEY `uniq_ad_date` (`ad_id`, `stat_date`),
            KEY `idx_ad_date` (`ad_id`, `stat_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ", false);

        $dedupe_table = eottae_ad_platform_event_dedupe_table();
        sql_query(" CREATE TABLE IF NOT EXISTS `{$dedupe_table}` (
            `dedupe_id` int unsigned NOT NULL AUTO_INCREMENT,
            `ad_id` int unsigned NOT NULL DEFAULT '0',
            `event_type` varchar(20) NOT NULL DEFAULT '',
            `dedupe_key` varchar(64) NOT NULL DEFAULT '',
            `expires_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`dedupe_id`),
            UNIQUE KEY `uniq_ad_event_key` (`ad_id`, `event_type`, `dedupe_key`),
            KEY `idx_expires` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ", false);
    }
}

if (!function_exists('eottae_ad_platform_event_dedupe_table')) {
    function eottae_ad_platform_event_dedupe_table()
    {
        return G5_TABLE_PREFIX.'eottae_ad_event_dedupe';
    }
}

if (!function_exists('eottae_ad_platform_seed_slots')) {
    function eottae_ad_platform_seed_slots()
    {
        $now = G5_TIME_YMDHIS;
        $defaults = array(
            array(
                'slot_code'      => EOTTae_AD_PLATFORM_SLOT_SHOP_TOP,
                'slot_name'      => '업소등록 최상단',
                'slot_desc'      => '업소 목록 최상단 대형 배너',
                'bo_table'       => defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop',
                'page_type'      => 'shop_list',
                'max_active_ads' => 1,
                'point_per_day'  => 500,
                'min_days'       => 3,
                'max_days'       => 30,
                'requires_image' => 0,
                'requires_review'=> 1,
                'is_premium'     => 0,
                'sort_order'     => 10,
            ),
            array(
                'slot_code'      => EOTTae_AD_PLATFORM_SLOT_MARKET_TOP,
                'slot_name'      => '중고장터 상단',
                'slot_desc'      => '중고장터 목록 상단 배너',
                'bo_table'       => defined('EOTTae_MARKET_TABLE') ? EOTTae_MARKET_TABLE : 'market',
                'page_type'      => 'board_list',
                'max_active_ads' => 1,
                'point_per_day'  => 300,
                'min_days'       => 3,
                'max_days'       => 30,
                'requires_image' => 0,
                'requires_review'=> 1,
                'is_premium'     => 0,
                'sort_order'     => 20,
            ),
            array(
                'slot_code'      => EOTTae_AD_PLATFORM_SLOT_JOB_TOP,
                'slot_name'      => '구인구직 상단',
                'slot_desc'      => '구인구직 게시판 상단 배너',
                'bo_table'       => defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job',
                'page_type'      => 'board_list',
                'max_active_ads' => 1,
                'point_per_day'  => 300,
                'min_days'       => 3,
                'max_days'       => 30,
                'requires_image' => 0,
                'requires_review'=> 1,
                'is_premium'     => 0,
                'sort_order'     => 30,
            ),
            array(
                'slot_code'      => EOTTae_AD_PLATFORM_SLOT_ESTATE_TOP,
                'slot_name'      => '부동산 상단',
                'slot_desc'      => '부동산 게시판 상단 배너',
                'bo_table'       => defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate',
                'page_type'      => 'board_list',
                'max_active_ads' => 1,
                'point_per_day'  => 400,
                'min_days'       => 3,
                'max_days'       => 30,
                'requires_image' => 0,
                'requires_review'=> 1,
                'is_premium'     => 0,
                'sort_order'     => 40,
            ),
            array(
                'slot_code'      => EOTTae_AD_PLATFORM_SLOT_PREMIUM,
                'slot_name'      => '광고판 프리미엄',
                'slot_desc'      => '광고판 대형 프리미엄 카드',
                'bo_table'       => 'adroom',
                'page_type'      => 'board_list',
                'max_active_ads' => 2,
                'point_per_day'  => 1000,
                'min_days'       => 7,
                'max_days'       => 60,
                'requires_image' => 1,
                'requires_review'=> 1,
                'is_premium'     => 1,
                'sort_order'     => 50,
            ),
        );

        $slot_table = eottae_ad_platform_slot_table();
        foreach ($defaults as $row) {
            $code = sql_escape_string($row['slot_code']);
            $name = sql_escape_string($row['slot_name']);
            $desc = sql_escape_string($row['slot_desc']);
            $bo = sql_escape_string($row['bo_table']);
            $page = sql_escape_string($row['page_type']);
            sql_query(" insert into `{$slot_table}` set
                slot_code = '{$code}',
                slot_name = '{$name}',
                slot_desc = '{$desc}',
                bo_table = '{$bo}',
                page_type = '{$page}',
                max_active_ads = '".(int) $row['max_active_ads']."',
                point_per_day = '".(int) $row['point_per_day']."',
                min_days = '".(int) $row['min_days']."',
                max_days = '".(int) $row['max_days']."',
                requires_image = '".(int) $row['requires_image']."',
                requires_review = '".(int) $row['requires_review']."',
                is_premium = '".(int) $row['is_premium']."',
                is_active = 1,
                sort_order = '".(int) $row['sort_order']."',
                created_at = '{$now}',
                updated_at = '{$now}' ");
        }
    }
}

if (!function_exists('eottae_ad_platform_slot_code_for_board')) {
    function eottae_ad_platform_slot_code_for_board($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $map = array(
            defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop'       => EOTTae_AD_PLATFORM_SLOT_SHOP_TOP,
            defined('EOTTae_MARKET_TABLE') ? EOTTae_MARKET_TABLE : 'market' => EOTTae_AD_PLATFORM_SLOT_MARKET_TOP,
            defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job'          => EOTTae_AD_PLATFORM_SLOT_JOB_TOP,
            defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate'=> EOTTae_AD_PLATFORM_SLOT_ESTATE_TOP,
            'adroom'                                                        => EOTTae_AD_PLATFORM_SLOT_PREMIUM,
        );

        return isset($map[$bo_table]) ? $map[$bo_table] : '';
    }
}

if (!function_exists('eottae_ad_platform_get_slots')) {
    function eottae_ad_platform_get_slots($active_only = true)
    {
        eottae_ad_platform_ensure_schema();
        $slot_table = eottae_ad_platform_slot_table();
        $where = $active_only ? " where is_active = 1 " : '';
        $result = sql_query(" select * from `{$slot_table}` {$where} order by sort_order asc, slot_id asc ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = eottae_ad_platform_normalize_slot($row);
        }

        return $rows;
    }
}

if (!function_exists('eottae_ad_platform_get_slot_by_code')) {
    function eottae_ad_platform_get_slot_by_code($slot_code)
    {
        eottae_ad_platform_ensure_schema();
        $slot_code = sql_escape_string(trim((string) $slot_code));
        if ($slot_code === '') {
            return null;
        }
        $slot_table = eottae_ad_platform_slot_table();
        $row = sql_fetch(" select * from `{$slot_table}` where slot_code = '{$slot_code}' limit 1 ");

        return $row ? eottae_ad_platform_normalize_slot($row) : null;
    }
}

if (!function_exists('eottae_ad_platform_get_slot_by_id')) {
    function eottae_ad_platform_get_slot_by_id($slot_id)
    {
        eottae_ad_platform_ensure_schema();
        $slot_id = (int) $slot_id;
        if ($slot_id < 1) {
            return null;
        }
        $slot_table = eottae_ad_platform_slot_table();
        $row = sql_fetch(" select * from `{$slot_table}` where slot_id = '{$slot_id}' limit 1 ");

        return $row ? eottae_ad_platform_normalize_slot($row) : null;
    }
}

if (!function_exists('eottae_ad_platform_normalize_slot')) {
    function eottae_ad_platform_normalize_slot($row)
    {
        if (!is_array($row)) {
            return null;
        }

        return array(
            'slot_id'         => (int) ($row['slot_id'] ?? 0),
            'slot_code'       => (string) ($row['slot_code'] ?? ''),
            'slot_name'       => get_text($row['slot_name'] ?? ''),
            'slot_desc'       => get_text($row['slot_desc'] ?? ''),
            'bo_table'        => (string) ($row['bo_table'] ?? ''),
            'page_type'       => (string) ($row['page_type'] ?? ''),
            'max_active_ads'  => max(1, (int) ($row['max_active_ads'] ?? 1)),
            'point_per_day'   => max(0, (int) ($row['point_per_day'] ?? 0)),
            'min_days'        => max(1, (int) ($row['min_days'] ?? 3)),
            'max_days'        => max(1, (int) ($row['max_days'] ?? 90)),
            'requires_image'  => !empty($row['requires_image']) ? 1 : 0,
            'requires_review' => !empty($row['requires_review']) ? 1 : 0,
            'is_premium'      => !empty($row['is_premium']) ? 1 : 0,
            'is_active'       => !empty($row['is_active']) ? 1 : 0,
            'sort_order'      => (int) ($row['sort_order'] ?? 0),
        );
    }
}

if (!function_exists('eottae_ad_platform_save_slot')) {
    function eottae_ad_platform_save_slot($slot_id, array $data, $admin_mb_id = '')
    {
        eottae_ad_platform_ensure_schema();
        $slot_id = (int) $slot_id;
        if ($slot_id < 1) {
            return array('ok' => false, 'message' => '잘못된 슬롯입니다.');
        }

        $slot_table = eottae_ad_platform_slot_table();
        $now = G5_TIME_YMDHIS;
        $admin_mb_id = sql_escape_string(substr((string) $admin_mb_id, 0, 20));

        $point_per_day = max(0, (int) ($data['point_per_day'] ?? 0));
        $min_days = max(1, min(365, (int) ($data['min_days'] ?? 3)));
        $max_days = max($min_days, min(365, (int) ($data['max_days'] ?? 90)));
        $max_active_ads = max(1, min(10, (int) ($data['max_active_ads'] ?? 1)));
        $is_active = !empty($data['is_active']) ? 1 : 0;
        $requires_review = !empty($data['requires_review']) ? 1 : 0;
        $requires_image = !empty($data['requires_image']) ? 1 : 0;

        sql_query(" update `{$slot_table}` set
            point_per_day = '{$point_per_day}',
            min_days = '{$min_days}',
            max_days = '{$max_days}',
            max_active_ads = '{$max_active_ads}',
            requires_review = '{$requires_review}',
            requires_image = '{$requires_image}',
            is_active = '{$is_active}',
            updated_at = '{$now}'
            where slot_id = '{$slot_id}' ");

        return array('ok' => true, 'message' => '저장되었습니다.');
    }
}

if (!function_exists('eottae_ad_platform_calc_points')) {
    function eottae_ad_platform_calc_points(array $slot, $days, $bid_bonus = 0)
    {
        $days = max(1, (int) $days);
        $bid_bonus = max(0, (int) $bid_bonus);

        return max(0, (int) $slot['point_per_day']) * $days + $bid_bonus;
    }
}

if (!function_exists('eottae_ad_platform_can_manage')) {
    function eottae_ad_platform_can_manage($member = null)
    {
        global $is_admin;

        if ($is_admin === 'super') {
            return true;
        }

        if ($member === null) {
            global $member;
        }

        return function_exists('eottae_is_business_member') && eottae_is_business_member($member);
    }
}

if (!function_exists('eottae_ad_platform_count_reserved')) {
    function eottae_ad_platform_count_reserved($slot_id, $start_date, $end_date, $exclude_ad_id = 0)
    {
        eottae_ad_platform_ensure_schema();
        $slot_id = (int) $slot_id;
        $exclude_ad_id = (int) $exclude_ad_id;
        if ($slot_id < 1 || $start_date === '' || $end_date === '') {
            return 0;
        }

        $start_date = sql_escape_string($start_date);
        $end_date = sql_escape_string($end_date);
        $campaign_table = eottae_ad_platform_campaign_table();
        $exclude_sql = $exclude_ad_id > 0 ? " and ad_id <> '{$exclude_ad_id}' " : '';
        $statuses = array('pending_review', 'approved', 'scheduled', 'active', 'paused');
        $status_in = "'".implode("','", array_map('sql_escape_string', $statuses))."'";

        $row = sql_fetch(" select count(*) as cnt from `{$campaign_table}`
            where slot_id = '{$slot_id}'
              and status in ({$status_in})
              and start_date <= '{$end_date}'
              and end_date >= '{$start_date}'
              {$exclude_sql} ");

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_ad_platform_slot_has_capacity')) {
    function eottae_ad_platform_slot_has_capacity(array $slot, $start_date, $end_date, $exclude_ad_id = 0)
    {
        $reserved = eottae_ad_platform_count_reserved((int) $slot['slot_id'], $start_date, $end_date, $exclude_ad_id);

        return $reserved < (int) $slot['max_active_ads'];
    }
}

if (!function_exists('eottae_ad_platform_next_waitlist_order')) {
    function eottae_ad_platform_next_waitlist_order($slot_id)
    {
        eottae_ad_platform_ensure_schema();
        $slot_id = (int) $slot_id;
        $campaign_table = eottae_ad_platform_campaign_table();
        $row = sql_fetch(" select max(waitlist_order) as mx from `{$campaign_table}`
            where slot_id = '{$slot_id}' and status = 'waitlisted' ");

        return max(0, (int) ($row['mx'] ?? 0)) + 1;
    }
}

if (!function_exists('eottae_ad_platform_apply')) {
    function eottae_ad_platform_apply($mb_id, array $input)
    {
        eottae_ad_platform_ensure_schema();
        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 이용해 주세요.');
        }

        $slot_code = trim((string) ($input['slot_code'] ?? ''));
        $slot = eottae_ad_platform_get_slot_by_code($slot_code);
        if (!$slot || empty($slot['is_active'])) {
            return array('ok' => false, 'message' => '선택한 광고 위치를 사용할 수 없습니다.');
        }

        $start_date = trim((string) ($input['start_date'] ?? ''));
        $days = max(1, (int) ($input['days'] ?? 0));
        if ($start_date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
            return array('ok' => false, 'message' => '시작일을 올바르게 입력해 주세요.');
        }
        if ($days < (int) $slot['min_days'] || $days > (int) $slot['max_days']) {
            return array('ok' => false, 'message' => '집행 기간은 '.(int) $slot['min_days'].'~'.(int) $slot['max_days'].'일 사이여야 합니다.');
        }

        $end_ts = strtotime($start_date.' +'.($days - 1).' days');
        if ($end_ts === false) {
            return array('ok' => false, 'message' => '종료일 계산에 실패했습니다.');
        }
        $end_date = date('Y-m-d', $end_ts);

        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $button_text = trim((string) ($input['button_text'] ?? '자세히 보기'));
        $link_url = trim((string) ($input['link_url'] ?? ''));
        $image_url = trim((string) ($input['image_url'] ?? ''));
        $shop_bo_table = preg_replace('/[^a-z0-9_]/', '', (string) ($input['shop_bo_table'] ?? ''));
        $shop_wr_id = (int) ($input['shop_wr_id'] ?? 0);
        $target_category = trim((string) ($input['target_category'] ?? ''));
        $target_region = trim((string) ($input['target_region'] ?? ''));
        $bid_bonus = max(0, min(100000, (int) ($input['bid_bonus'] ?? 0)));

        if ($title === '') {
            return array('ok' => false, 'message' => '광고 제목을 입력해 주세요.');
        }
        if ($description === '') {
            return array('ok' => false, 'message' => '광고 설명을 입력해 주세요.');
        }
        if (!empty($slot['requires_image']) && $image_url === '') {
            return array('ok' => false, 'message' => '이 위치는 광고 이미지가 필요합니다.');
        }

        $total_points = eottae_ad_platform_calc_points($slot, $days, $bid_bonus);
        $member = get_member($mb_id, 'mb_point');
        $balance = isset($member['mb_point']) ? (int) $member['mb_point'] : 0;
        if ($balance < $total_points) {
            return array(
                'ok'      => false,
                'message' => '포인트가 부족합니다. (필요 '.number_format($total_points).'P · 보유 '.number_format($balance).'P)',
            );
        }

        $has_capacity = eottae_ad_platform_slot_has_capacity($slot, $start_date, $end_date);
        $status = 'pending_review';
        $waitlist_order = 0;
        if (!$has_capacity) {
            $status = 'waitlisted';
            $waitlist_order = eottae_ad_platform_next_waitlist_order((int) $slot['slot_id']);
        } elseif (empty($slot['requires_review'])) {
            $status = ($start_date <= G5_TIME_YMD) ? 'active' : 'scheduled';
        }

        $now = G5_TIME_YMDHIS;
        $campaign_table = eottae_ad_platform_campaign_table();
        $esc = function ($value) {
            return sql_escape_string((string) $value);
        };

        sql_query(" insert into `{$campaign_table}` set
            slot_id = '".(int) $slot['slot_id']."',
            mb_id = '".$esc($mb_id)."',
            shop_bo_table = '".$esc($shop_bo_table)."',
            shop_wr_id = '{$shop_wr_id}',
            status = '".$esc($status)."',
            title = '".$esc($title)."',
            description = '".$esc($description)."',
            button_text = '".$esc($button_text !== '' ? $button_text : '자세히 보기')."',
            link_url = '".$esc($link_url)."',
            image_url = '".$esc($image_url)."',
            start_date = '".$esc($start_date)."',
            end_date = '".$esc($end_date)."',
            days = '{$days}',
            total_points = '{$total_points}',
            points_charged = '0',
            waitlist_order = '{$waitlist_order}',
            target_category = '".$esc($target_category)."',
            target_region = '".$esc($target_region)."',
            bid_bonus = '{$bid_bonus}',
            extend_reminder_sent = '0',
            created_at = '{$now}',
            updated_at = '{$now}' ");

        $ad_id = (int) sql_insert_id();
        if ($ad_id < 1) {
            return array('ok' => false, 'message' => '광고 등록에 실패했습니다.');
        }

        if ($status === 'active' && empty($slot['requires_review'])) {
            $charge = eottae_ad_platform_charge_points($ad_id, $mb_id, $total_points, '광고 자동 시작');
            if (empty($charge['ok'])) {
                sql_query(" update `{$campaign_table}` set status = 'cancelled', review_message = '".$esc($charge['message'])."', updated_at = '{$now}' where ad_id = '{$ad_id}' ");
                return $charge;
            }
        }

        $message = $status === 'waitlisted'
            ? '선택한 기간에 빈 자리가 없어 대기등록되었습니다. (대기 순번 '.$waitlist_order.'번)'
            : '광고 신청이 접수되었습니다. 관리자 승인 후 집행됩니다.';

        return array(
            'ok'      => true,
            'message' => $message,
            'ad_id'   => $ad_id,
            'status'  => $status,
        );
    }
}

if (!function_exists('eottae_ad_platform_charge_points')) {
    function eottae_ad_platform_charge_points($ad_id, $mb_id, $points, $memo = '')
    {
        $ad_id = (int) $ad_id;
        $points = max(0, (int) $points);
        if ($ad_id < 1 || $points < 1) {
            return array('ok' => false, 'message' => '포인트 차감 정보가 올바르지 않습니다.');
        }

        $member = get_member($mb_id, 'mb_point');
        $balance = isset($member['mb_point']) ? (int) $member['mb_point'] : 0;
        if ($balance < $points) {
            return array('ok' => false, 'message' => '포인트가 부족합니다.');
        }

        $memo = trim((string) $memo);
        if ($memo === '') {
            $memo = '광고 집행 포인트 차감';
        }

        $point_ok = insert_point(
            $mb_id,
            $points * -1,
            $memo,
            '@eottae_ad_campaign',
            (string) $ad_id,
            'charge'
        );
        if (!$point_ok) {
            return array('ok' => false, 'message' => '포인트 차감에 실패했습니다.');
        }

        $campaign_table = eottae_ad_platform_campaign_table();
        sql_query(" update `{$campaign_table}` set
            points_charged = '{$points}',
            updated_at = '".G5_TIME_YMDHIS."'
            where ad_id = '{$ad_id}' ");

        return array('ok' => true);
    }
}

if (!function_exists('eottae_ad_platform_refund_points')) {
    function eottae_ad_platform_refund_points($ad_id, $mb_id, $points, $memo = '')
    {
        $ad_id = (int) $ad_id;
        $points = max(0, (int) $points);
        if ($ad_id < 1 || $points < 1) {
            return true;
        }

        $memo = trim((string) $memo);
        if ($memo === '') {
            $memo = '광고 포인트 환불';
        }

        insert_point(
            $mb_id,
            $points,
            $memo,
            '@eottae_ad_campaign',
            (string) $ad_id,
            'refund'
        );

        return true;
    }
}

if (!function_exists('eottae_ad_platform_get_campaign')) {
    function eottae_ad_platform_get_campaign($ad_id)
    {
        eottae_ad_platform_ensure_schema();
        $ad_id = (int) $ad_id;
        if ($ad_id < 1) {
            return null;
        }
        $campaign_table = eottae_ad_platform_campaign_table();
        $row = sql_fetch(" select c.*, s.slot_code, s.slot_name, s.is_premium
            from `{$campaign_table}` c
            left join `".eottae_ad_platform_slot_table()."` s on s.slot_id = c.slot_id
            where c.ad_id = '{$ad_id}' limit 1 ");

        return $row ? eottae_ad_platform_normalize_campaign($row) : null;
    }
}

if (!function_exists('eottae_ad_platform_normalize_campaign')) {
    function eottae_ad_platform_normalize_campaign($row)
    {
        if (!is_array($row) || empty($row['ad_id'])) {
            return null;
        }

        return array(
            'ad_id'          => (int) $row['ad_id'],
            'slot_id'        => (int) ($row['slot_id'] ?? 0),
            'slot_code'      => (string) ($row['slot_code'] ?? ''),
            'slot_name'      => get_text($row['slot_name'] ?? ''),
            'is_premium'     => !empty($row['is_premium']) ? 1 : 0,
            'mb_id'          => get_text($row['mb_id'] ?? ''),
            'shop_bo_table'  => (string) ($row['shop_bo_table'] ?? ''),
            'shop_wr_id'     => (int) ($row['shop_wr_id'] ?? 0),
            'status'         => (string) ($row['status'] ?? ''),
            'status_label'   => eottae_ad_platform_status_label($row['status'] ?? ''),
            'title'          => get_text($row['title'] ?? ''),
            'description'    => get_text($row['description'] ?? ''),
            'button_text'    => get_text($row['button_text'] ?? '자세히 보기'),
            'link_url'       => get_text($row['link_url'] ?? ''),
            'image_url'      => get_text($row['image_url'] ?? ''),
            'start_date'     => (string) ($row['start_date'] ?? ''),
            'end_date'       => (string) ($row['end_date'] ?? ''),
            'days'           => (int) ($row['days'] ?? 0),
            'total_points'   => (int) ($row['total_points'] ?? 0),
            'points_charged' => (int) ($row['points_charged'] ?? 0),
            'waitlist_order' => (int) ($row['waitlist_order'] ?? 0),
            'review_message' => get_text($row['review_message'] ?? ''),
            'approved_by'    => get_text($row['approved_by'] ?? ''),
            'approved_at'    => (string) ($row['approved_at'] ?? ''),
            'impressions'    => (int) ($row['impressions'] ?? 0),
            'clicks'         => (int) ($row['clicks'] ?? 0),
            'target_category'=> get_text($row['target_category'] ?? ''),
            'target_region'  => get_text($row['target_region'] ?? ''),
            'bid_bonus'      => (int) ($row['bid_bonus'] ?? 0),
            'extend_reminder_sent' => !empty($row['extend_reminder_sent']) ? 1 : 0,
            'ctr'            => ((int) ($row['impressions'] ?? 0)) > 0
                ? round(((int) ($row['clicks'] ?? 0)) / (int) $row['impressions'] * 100, 2)
                : 0,
            'created_at'     => (string) ($row['created_at'] ?? ''),
            'updated_at'     => (string) ($row['updated_at'] ?? ''),
        );
    }
}

if (!function_exists('eottae_ad_platform_admin_approve')) {
    function eottae_ad_platform_admin_approve($ad_id, $admin_mb_id = '')
    {
        $campaign = eottae_ad_platform_get_campaign($ad_id);
        if (!$campaign) {
            return array('ok' => false, 'message' => '광고를 찾을 수 없습니다.');
        }
        if (!in_array($campaign['status'], array('pending_review', 'waitlisted', 'approved'), true)) {
            return array('ok' => false, 'message' => '승인할 수 없는 상태입니다.');
        }

        $slot = eottae_ad_platform_get_slot_by_id((int) $campaign['slot_id']);
        if (!$slot) {
            return array('ok' => false, 'message' => '광고 위치 정보가 없습니다.');
        }

        if (!eottae_ad_platform_slot_has_capacity($slot, $campaign['start_date'], $campaign['end_date'], (int) $campaign['ad_id'])) {
            return array('ok' => false, 'message' => '선택 기간에 빈 슬롯이 없습니다. 대기등록으로 전환하거나 기간을 조정해 주세요.');
        }

        if ((int) $campaign['points_charged'] < 1) {
            $charge = eottae_ad_platform_charge_points((int) $campaign['ad_id'], $campaign['mb_id'], (int) $campaign['total_points'], '광고 승인 · '.$campaign['slot_name']);
            if (empty($charge['ok'])) {
                return $charge;
            }
        }

        $today = G5_TIME_YMD;
        $new_status = ($campaign['start_date'] <= $today) ? 'active' : 'scheduled';
        $now = G5_TIME_YMDHIS;
        $admin_mb_id = sql_escape_string(substr((string) $admin_mb_id, 0, 20));
        $campaign_table = eottae_ad_platform_campaign_table();

        sql_query(" update `{$campaign_table}` set
            status = '{$new_status}',
            waitlist_order = 0,
            approved_by = '{$admin_mb_id}',
            approved_at = '{$now}',
            updated_at = '{$now}'
            where ad_id = '".(int) $campaign['ad_id']."' ");

        return array('ok' => true, 'message' => '승인되었습니다.', 'status' => $new_status);
    }
}

if (!function_exists('eottae_ad_platform_admin_reject')) {
    function eottae_ad_platform_admin_reject($ad_id, $admin_mb_id = '', $message = '')
    {
        $campaign = eottae_ad_platform_get_campaign($ad_id);
        if (!$campaign) {
            return array('ok' => false, 'message' => '광고를 찾을 수 없습니다.');
        }
        if (!in_array($campaign['status'], array('pending_review', 'waitlisted', 'approved', 'scheduled'), true)) {
            return array('ok' => false, 'message' => '반려할 수 없는 상태입니다.');
        }

        if ((int) $campaign['points_charged'] > 0) {
            eottae_ad_platform_refund_points((int) $campaign['ad_id'], $campaign['mb_id'], (int) $campaign['points_charged'], '광고 반려 환불');
        }

        $message = sql_escape_string(trim((string) $message));
        $now = G5_TIME_YMDHIS;
        $campaign_table = eottae_ad_platform_campaign_table();
        sql_query(" update `{$campaign_table}` set
            status = 'rejected',
            review_message = '{$message}',
            points_charged = 0,
            waitlist_order = 0,
            updated_at = '{$now}'
            where ad_id = '".(int) $campaign['ad_id']."' ");

        return array('ok' => true, 'message' => '반려되었습니다.');
    }
}

if (!function_exists('eottae_ad_platform_admin_cancel')) {
    function eottae_ad_platform_admin_cancel($ad_id, $admin_mb_id = '', $message = '')
    {
        $campaign = eottae_ad_platform_get_campaign($ad_id);
        if (!$campaign) {
            return array('ok' => false, 'message' => '광고를 찾을 수 없습니다.');
        }

        if ((int) $campaign['points_charged'] > 0) {
            eottae_ad_platform_refund_points((int) $campaign['ad_id'], $campaign['mb_id'], (int) $campaign['points_charged'], '광고 취소 환불');
        }

        $message = sql_escape_string(trim((string) $message));
        $now = G5_TIME_YMDHIS;
        $campaign_table = eottae_ad_platform_campaign_table();
        sql_query(" update `{$campaign_table}` set
            status = 'cancelled',
            review_message = '{$message}',
            points_charged = 0,
            waitlist_order = 0,
            updated_at = '{$now}'
            where ad_id = '".(int) $campaign['ad_id']."' ");

        eottae_ad_platform_promote_waitlist((int) $campaign['slot_id']);

        return array('ok' => true, 'message' => '취소되었습니다.');
    }
}

if (!function_exists('eottae_ad_platform_maintain_cache_file')) {
    function eottae_ad_platform_maintain_cache_file()
    {
        return (defined('G5_DATA_PATH') ? G5_DATA_PATH : G5_PATH.'/data').'/cache/eottae-ad-platform-maintain.json';
    }
}

if (!function_exists('eottae_ad_platform_maintain_if_due')) {
    function eottae_ad_platform_maintain_if_due($force = false)
    {
        $cache_file = eottae_ad_platform_maintain_cache_file();
        $ttl = 21600;
        $now = time();

        if (!$force && is_file($cache_file)) {
            $raw = @file_get_contents($cache_file);
            $cached = $raw ? json_decode($raw, true) : null;
            if (is_array($cached) && !empty($cached['ran_at']) && ($now - (int) $cached['ran_at']) < $ttl) {
                return array('ok' => true, 'skipped' => true, 'message' => 'maintain skipped (cache)');
            }
        }

        return eottae_ad_platform_maintain(true, false);
    }
}

if (!function_exists('eottae_ad_platform_maintain')) {
    function eottae_ad_platform_maintain($write_cache = false, $full = true)
    {
        eottae_ad_platform_expire_due();
        eottae_ad_platform_activate_scheduled();
        if ($full) {
            eottae_ad_platform_process_expiry_reminders();
            eottae_ad_platform_cleanup_event_dedupe();
        }

        if ($write_cache) {
            $cache_file = eottae_ad_platform_maintain_cache_file();
            $dir = dirname($cache_file);
            if (!is_dir($dir)) {
                @mkdir($dir, G5_DIR_PERMISSION, true);
            }
            @file_put_contents($cache_file, json_encode(array(
                'ran_at' => time(),
                'ran_at_iso' => G5_TIME_YMDHIS,
            ), JSON_UNESCAPED_UNICODE));
        }

        return array('ok' => true, 'skipped' => false, 'message' => 'maintain completed');
    }
}

if (!function_exists('eottae_ad_platform_expire_due')) {
    function eottae_ad_platform_expire_due()
    {
        $campaign_table = eottae_ad_platform_campaign_table();
        $today = G5_TIME_YMD;
        $now = G5_TIME_YMDHIS;

        $result = sql_query(" select ad_id, slot_id from `{$campaign_table}`
            where status in ('active', 'scheduled', 'paused')
              and end_date > '0000-00-00'
              and end_date < '{$today}' ");
        $slot_ids = array();
        while ($row = sql_fetch_array($result)) {
            $slot_ids[(int) $row['slot_id']] = true;
            sql_query(" update `{$campaign_table}` set status = 'expired', updated_at = '{$now}' where ad_id = '".(int) $row['ad_id']."' ");
        }
        foreach (array_keys($slot_ids) as $slot_id) {
            eottae_ad_platform_promote_waitlist($slot_id);
        }
    }
}

if (!function_exists('eottae_ad_platform_activate_scheduled')) {
    function eottae_ad_platform_activate_scheduled()
    {
        $campaign_table = eottae_ad_platform_campaign_table();
        $today = G5_TIME_YMD;
        $now = G5_TIME_YMDHIS;
        sql_query(" update `{$campaign_table}` set status = 'active', updated_at = '{$now}'
            where status = 'scheduled'
              and start_date <= '{$today}'
              and end_date >= '{$today}' ");
    }
}

if (!function_exists('eottae_ad_platform_promote_waitlist')) {
    function eottae_ad_platform_promote_waitlist($slot_id)
    {
        $slot_id = (int) $slot_id;
        if ($slot_id < 1) {
            return;
        }

        $slot = eottae_ad_platform_get_slot_by_id($slot_id);
        if (!$slot) {
            return;
        }

        $campaign_table = eottae_ad_platform_campaign_table();
        $result = sql_query(" select ad_id, start_date, end_date from `{$campaign_table}`
            where slot_id = '{$slot_id}' and status = 'waitlisted'
            order by waitlist_order asc, created_at asc
            limit 20 ");

        while ($row = sql_fetch_array($result)) {
            if (!eottae_ad_platform_slot_has_capacity($slot, $row['start_date'], $row['end_date'], (int) $row['ad_id'])) {
                continue;
            }

            $new_status = empty($slot['requires_review']) ? 'pending_review' : 'pending_review';
            if (empty($slot['requires_review'])) {
                $approve = eottae_ad_platform_admin_approve((int) $row['ad_id'], 'system');
                if (!empty($approve['ok'])) {
                    continue;
                }
            }

            sql_query(" update `{$campaign_table}` set
                status = '{$new_status}',
                waitlist_order = 0,
                updated_at = '".G5_TIME_YMDHIS."'
                where ad_id = '".(int) $row['ad_id']."' ");
            break;
        }
    }
}

if (!function_exists('eottae_ad_platform_get_active')) {
    function eottae_ad_platform_get_active($slot_code, $limit = 1, array $context = array())
    {
        eottae_ad_platform_ensure_schema();
        $slot = eottae_ad_platform_get_slot_by_code($slot_code);
        if (!$slot || empty($slot['is_active'])) {
            return array();
        }

        $limit = max(1, min(5, (int) $limit));
        $today = G5_TIME_YMD;
        $campaign_table = eottae_ad_platform_campaign_table();
        $slot_id = (int) $slot['slot_id'];
        $where_extra = '';

        $category = trim((string) ($context['category'] ?? ''));
        $region = trim((string) ($context['region'] ?? ''));
        if ($category !== '') {
            $where_extra .= " AND (c.target_category = '' OR c.target_category = '".sql_escape_string($category)."') ";
        }
        if ($region !== '') {
            $where_extra .= " AND (c.target_region = '' OR c.target_region = '".sql_escape_string($region)."') ";
        }

        $result = sql_query(" select c.*, s.slot_code, s.slot_name, s.is_premium
            from `{$campaign_table}` c
            left join `".eottae_ad_platform_slot_table()."` s on s.slot_id = c.slot_id
            where c.slot_id = '{$slot_id}'
              and c.status = 'active'
              and c.start_date <= '{$today}'
              and c.end_date >= '{$today}'
              {$where_extra}
            order by c.total_points desc, c.bid_bonus desc, c.approved_at desc, c.ad_id asc
            limit {$limit} ");

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $item = eottae_ad_platform_normalize_campaign($row);
            if ($item) {
                $rows[] = $item;
            }
        }

        if (empty($rows) && ($category !== '' || $region !== '')) {
            return eottae_ad_platform_get_active($slot_code, $limit, array());
        }

        return $rows;
    }
}

if (!function_exists('eottae_ad_platform_record_impression')) {
    function eottae_ad_platform_record_impression($ad_id)
    {
        $ad_id = (int) $ad_id;
        if ($ad_id < 1) {
            return;
        }
        if (!eottae_ad_platform_try_acquire_event($ad_id, 'impression')) {
            return;
        }

        $campaign_table = eottae_ad_platform_campaign_table();
        sql_query(" update `{$campaign_table}` set impressions = impressions + 1 where ad_id = '{$ad_id}' ");
        eottae_ad_platform_record_daily_stat($ad_id, 'impression');
    }
}

if (!function_exists('eottae_ad_platform_record_click')) {
    function eottae_ad_platform_record_click($ad_id)
    {
        $ad_id = (int) $ad_id;
        if ($ad_id < 1) {
            return '';
        }
        $campaign = eottae_ad_platform_get_campaign($ad_id);
        if (!$campaign || $campaign['status'] !== 'active') {
            return '';
        }
        if (!eottae_ad_platform_try_acquire_event($ad_id, 'click')) {
            return $campaign['link_url'];
        }

        $campaign_table = eottae_ad_platform_campaign_table();
        sql_query(" update `{$campaign_table}` set clicks = clicks + 1 where ad_id = '{$ad_id}' ");
        eottae_ad_platform_record_daily_stat($ad_id, 'click');

        return $campaign['link_url'];
    }
}

if (!function_exists('eottae_ad_platform_member_campaigns')) {
    function eottae_ad_platform_member_campaigns($mb_id, $limit = 30)
    {
        eottae_ad_platform_ensure_schema();
        $mb_id = sql_escape_string(trim((string) $mb_id));
        if ($mb_id === '') {
            return array();
        }

        $limit = max(1, min(100, (int) $limit));
        $campaign_table = eottae_ad_platform_campaign_table();
        $result = sql_query(" select c.*, s.slot_code, s.slot_name, s.is_premium
            from `{$campaign_table}` c
            left join `".eottae_ad_platform_slot_table()."` s on s.slot_id = c.slot_id
            where c.mb_id = '{$mb_id}'
            order by c.ad_id desc
            limit {$limit} ");

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $item = eottae_ad_platform_normalize_campaign($row);
            if ($item) {
                $rows[] = $item;
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_ad_platform_admin_dashboard_stats')) {
    function eottae_ad_platform_admin_dashboard_stats()
    {
        eottae_ad_platform_ensure_schema();
        $slot_table = eottae_ad_platform_slot_table();
        $campaign_table = eottae_ad_platform_campaign_table();
        $today = G5_TIME_YMD;

        $result = sql_query(" select s.slot_id, s.slot_code, s.slot_name, s.max_active_ads, s.is_active,
                sum(case when c.status = 'active' and c.start_date <= '{$today}' and c.end_date >= '{$today}' then 1 else 0 end) as active_count,
                sum(case when c.status = 'pending_review' then 1 else 0 end) as pending_count,
                sum(case when c.status = 'waitlisted' then 1 else 0 end) as waitlist_count,
                sum(case when c.status in ('active', 'expired', 'scheduled') then c.impressions else 0 end) as impressions,
                sum(case when c.status in ('active', 'expired', 'scheduled') then c.clicks else 0 end) as clicks,
                sum(case when c.points_charged > 0 then c.points_charged else 0 end) as points_charged
            from `{$slot_table}` s
            left join `{$campaign_table}` c on c.slot_id = s.slot_id
            group by s.slot_id
            order by s.sort_order asc, s.slot_id asc ");

        $slots = array();
        $totals = array(
            'active_count'   => 0,
            'pending_count'  => 0,
            'waitlist_count' => 0,
            'impressions'    => 0,
            'clicks'         => 0,
            'points_charged' => 0,
        );

        while ($row = sql_fetch_array($result)) {
            $active_count = (int) ($row['active_count'] ?? 0);
            $max_active = max(1, (int) ($row['max_active_ads'] ?? 1));
            $impressions = (int) ($row['impressions'] ?? 0);
            $clicks = (int) ($row['clicks'] ?? 0);
            $item = array(
                'slot_id'        => (int) ($row['slot_id'] ?? 0),
                'slot_code'      => (string) ($row['slot_code'] ?? ''),
                'slot_name'      => get_text($row['slot_name'] ?? ''),
                'max_active_ads' => $max_active,
                'is_active'      => !empty($row['is_active']) ? 1 : 0,
                'active_count'   => $active_count,
                'pending_count'  => (int) ($row['pending_count'] ?? 0),
                'waitlist_count' => (int) ($row['waitlist_count'] ?? 0),
                'impressions'    => $impressions,
                'clicks'         => $clicks,
                'points_charged' => (int) ($row['points_charged'] ?? 0),
                'fill_rate'      => round(min(100, ($active_count / $max_active) * 100), 1),
                'ctr'            => $impressions > 0 ? round($clicks / $impressions * 100, 2) : 0,
            );
            $slots[] = $item;
            $totals['active_count'] += $active_count;
            $totals['pending_count'] += $item['pending_count'];
            $totals['waitlist_count'] += $item['waitlist_count'];
            $totals['impressions'] += $impressions;
            $totals['clicks'] += $clicks;
            $totals['points_charged'] += $item['points_charged'];
        }

        $totals['ctr'] = $totals['impressions'] > 0
            ? round($totals['clicks'] / $totals['impressions'] * 100, 2)
            : 0;

        return array(
            'slots'  => $slots,
            'totals' => $totals,
        );
    }
}

if (!function_exists('eottae_ad_platform_admin_campaigns')) {
    function eottae_ad_platform_admin_campaigns($status = '', $limit = 50)
    {
        eottae_ad_platform_ensure_schema();
        $limit = max(1, min(200, (int) $limit));
        $where = '';
        $status = trim((string) $status);
        if ($status !== '') {
            $where = " where c.status = '".sql_escape_string($status)."' ";
        }

        $campaign_table = eottae_ad_platform_campaign_table();
        $result = sql_query(" select c.*, s.slot_code, s.slot_name, s.is_premium
            from `{$campaign_table}` c
            left join `".eottae_ad_platform_slot_table()."` s on s.slot_id = c.slot_id
            {$where}
            order by field(c.status, 'pending_review', 'waitlisted', 'active', 'scheduled', 'approved', 'rejected', 'expired', 'cancelled'), c.ad_id desc
            limit {$limit} ");

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $item = eottae_ad_platform_normalize_campaign($row);
            if ($item) {
                $rows[] = $item;
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_ad_platform_upload_image')) {
    function eottae_ad_platform_upload_image($file)
    {
        if (!is_array($file) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return array('ok' => false, 'message' => '업로드할 이미지가 없습니다.');
        }

        $allowed = array('image/jpeg', 'image/png', 'image/webp', 'image/gif');
        $mime = isset($file['type']) ? (string) $file['type'] : '';
        if ($mime !== '' && !in_array($mime, $allowed, true)) {
            return array('ok' => false, 'message' => 'jpg, png, webp, gif 이미지만 업로드할 수 있습니다.');
        }

        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, array('jpg', 'jpeg', 'png', 'webp', 'gif'), true)) {
            return array('ok' => false, 'message' => '허용되지 않은 파일 형식입니다.');
        }

        $dir = G5_DATA_PATH.'/ad-platform';
        if (!is_dir($dir)) {
            @mkdir($dir, G5_DIR_PERMISSION, true);
            @chmod($dir, G5_DIR_PERMISSION);
        }

        $filename = 'ad_'.date('Ymd_His').'_'.substr(md5(uniqid('', true)), 0, 10).'.'.$ext;
        $dest = $dir.'/'.$filename;
        if (!@move_uploaded_file($file['tmp_name'], $dest)) {
            return array('ok' => false, 'message' => '이미지 저장에 실패했습니다.');
        }

        @chmod($dest, G5_FILE_PERMISSION);
        $url = G5_DATA_URL.'/ad-platform/'.$filename;

        return array('ok' => true, 'url' => $url);
    }
}

if (!function_exists('eottae_ad_platform_click_url')) {
    function eottae_ad_platform_click_url($ad_id)
    {
        return G5_URL.'/proc/eottae-ad-click.php?ad_id='.(int) $ad_id;
    }
}

if (!function_exists('eottae_ad_platform_register_url')) {
    function eottae_ad_platform_register_url($slot_code = '')
    {
        $url = G5_URL.'/page/eottae-ad-register.php';
        if ($slot_code !== '') {
            $url .= '?slot='.rawurlencode((string) $slot_code);
        }

        return $url;
    }
}

if (!function_exists('eottae_ad_platform_edit_url')) {
    function eottae_ad_platform_edit_url($ad_id)
    {
        return G5_URL.'/page/eottae-ad-edit.php?ad_id='.(int) $ad_id;
    }
}

if (!function_exists('eottae_ad_platform_member_owns_campaign')) {
    function eottae_ad_platform_member_owns_campaign(array $campaign, $mb_id, $is_super = false)
    {
        if ($is_super) {
            return true;
        }

        return trim((string) ($campaign['mb_id'] ?? '')) === trim((string) $mb_id);
    }
}

if (!function_exists('eottae_ad_platform_member_can_edit_campaign')) {
    function eottae_ad_platform_member_can_edit_campaign(array $campaign)
    {
        return in_array($campaign['status'] ?? '', array('pending_review', 'waitlisted'), true);
    }
}

if (!function_exists('eottae_ad_platform_member_can_extend_campaign')) {
    function eottae_ad_platform_member_can_extend_campaign(array $campaign)
    {
        return in_array($campaign['status'] ?? '', array('active', 'scheduled'), true);
    }
}

if (!function_exists('eottae_ad_platform_member_can_cancel_campaign')) {
    function eottae_ad_platform_member_can_cancel_campaign(array $campaign)
    {
        return in_array($campaign['status'] ?? '', array('pending_review', 'waitlisted'), true);
    }
}

if (!function_exists('eottae_ad_platform_update_campaign')) {
    function eottae_ad_platform_update_campaign($ad_id, $mb_id, array $input, $is_super = false)
    {
        $campaign = eottae_ad_platform_get_campaign($ad_id);
        if (!$campaign) {
            return array('ok' => false, 'message' => '광고를 찾을 수 없습니다.');
        }
        if (!eottae_ad_platform_member_owns_campaign($campaign, $mb_id, $is_super)) {
            return array('ok' => false, 'message' => '수정 권한이 없습니다.');
        }
        if (!eottae_ad_platform_member_can_edit_campaign($campaign)) {
            return array('ok' => false, 'message' => '승인 대기·대기등록 상태에서만 수정할 수 있습니다.');
        }

        $slot = eottae_ad_platform_get_slot_by_id((int) $campaign['slot_id']);
        if (!$slot) {
            return array('ok' => false, 'message' => '광고 위치 정보가 없습니다.');
        }

        $start_date = trim((string) ($input['start_date'] ?? $campaign['start_date']));
        $days = max(1, (int) ($input['days'] ?? $campaign['days']));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
            return array('ok' => false, 'message' => '시작일을 올바르게 입력해 주세요.');
        }
        if ($days < (int) $slot['min_days'] || $days > (int) $slot['max_days']) {
            return array('ok' => false, 'message' => '집행 기간은 '.(int) $slot['min_days'].'~'.(int) $slot['max_days'].'일 사이여야 합니다.');
        }

        $end_ts = strtotime($start_date.' +'.($days - 1).' days');
        if ($end_ts === false) {
            return array('ok' => false, 'message' => '종료일 계산에 실패했습니다.');
        }
        $end_date = date('Y-m-d', $end_ts);

        $title = trim((string) ($input['title'] ?? $campaign['title']));
        $description = trim((string) ($input['description'] ?? $campaign['description']));
        $button_text = trim((string) ($input['button_text'] ?? $campaign['button_text']));
        $link_url = trim((string) ($input['link_url'] ?? $campaign['link_url']));
        $image_url = trim((string) ($input['image_url'] ?? $campaign['image_url']));

        if ($title === '' || $description === '') {
            return array('ok' => false, 'message' => '제목과 설명을 입력해 주세요.');
        }
        if (!empty($slot['requires_image']) && $image_url === '') {
            return array('ok' => false, 'message' => '이 위치는 광고 이미지가 필요합니다.');
        }

        $target_category = trim((string) ($input['target_category'] ?? $campaign['target_category']));
        $target_region = trim((string) ($input['target_region'] ?? $campaign['target_region']));
        $bid_bonus = max(0, min(100000, (int) ($input['bid_bonus'] ?? $campaign['bid_bonus'])));

        $total_points = eottae_ad_platform_calc_points($slot, $days, $bid_bonus);
        $member = get_member($mb_id, 'mb_point');
        $balance = isset($member['mb_point']) ? (int) $member['mb_point'] : 0;
        if ($balance < $total_points) {
            return array(
                'ok'      => false,
                'message' => '포인트가 부족합니다. (필요 '.number_format($total_points).'P · 보유 '.number_format($balance).'P)',
            );
        }

        $has_capacity = eottae_ad_platform_slot_has_capacity($slot, $start_date, $end_date, (int) $campaign['ad_id']);
        $status = 'pending_review';
        $waitlist_order = 0;
        if (!$has_capacity) {
            $status = 'waitlisted';
            $waitlist_order = eottae_ad_platform_next_waitlist_order((int) $slot['slot_id']);
        }

        $esc = function ($value) {
            return sql_escape_string((string) $value);
        };
        $now = G5_TIME_YMDHIS;
        $campaign_table = eottae_ad_platform_campaign_table();

        sql_query(" update `{$campaign_table}` set
            status = '".$esc($status)."',
            title = '".$esc($title)."',
            description = '".$esc($description)."',
            button_text = '".$esc($button_text !== '' ? $button_text : '자세히 보기')."',
            link_url = '".$esc($link_url)."',
            image_url = '".$esc($image_url)."',
            start_date = '".$esc($start_date)."',
            end_date = '".$esc($end_date)."',
            days = '{$days}',
            total_points = '{$total_points}',
            waitlist_order = '{$waitlist_order}',
            target_category = '".$esc($target_category)."',
            target_region = '".$esc($target_region)."',
            bid_bonus = '{$bid_bonus}',
            review_message = '',
            updated_at = '{$now}'
            where ad_id = '".(int) $campaign['ad_id']."' ");

        return array(
            'ok'      => true,
            'message' => $status === 'waitlisted' ? '수정되었습니다. 선택 기간에 빈 자리가 없어 대기등록 상태입니다.' : '수정되었습니다. 관리자 승인 후 집행됩니다.',
            'status'  => $status,
        );
    }
}

if (!function_exists('eottae_ad_platform_extend_campaign')) {
    function eottae_ad_platform_extend_campaign($ad_id, $mb_id, $extra_days, $is_super = false)
    {
        $campaign = eottae_ad_platform_get_campaign($ad_id);
        if (!$campaign) {
            return array('ok' => false, 'message' => '광고를 찾을 수 없습니다.');
        }
        if (!eottae_ad_platform_member_owns_campaign($campaign, $mb_id, $is_super)) {
            return array('ok' => false, 'message' => '연장 권한이 없습니다.');
        }
        if (!eottae_ad_platform_member_can_extend_campaign($campaign)) {
            return array('ok' => false, 'message' => '노출 중·예약 상태에서만 연장할 수 있습니다.');
        }

        $extra_days = max(1, (int) $extra_days);
        $slot = eottae_ad_platform_get_slot_by_id((int) $campaign['slot_id']);
        if (!$slot) {
            return array('ok' => false, 'message' => '광고 위치 정보가 없습니다.');
        }

        $new_days = (int) $campaign['days'] + $extra_days;
        if ($new_days > (int) $slot['max_days']) {
            return array('ok' => false, 'message' => '최대 '.(int) $slot['max_days'].'일까지 집행할 수 있습니다.');
        }

        $new_end_ts = strtotime($campaign['end_date'].' +'.$extra_days.' days');
        if ($new_end_ts === false) {
            return array('ok' => false, 'message' => '종료일 계산에 실패했습니다.');
        }
        $new_end_date = date('Y-m-d', $new_end_ts);

        if (!eottae_ad_platform_slot_has_capacity($slot, $campaign['start_date'], $new_end_date, (int) $campaign['ad_id'])) {
            return array('ok' => false, 'message' => '연장 기간에 다른 광고가 있어 연장할 수 없습니다.');
        }

        $extra_points = eottae_ad_platform_calc_points($slot, $extra_days);
        $member = get_member($mb_id, 'mb_point');
        $balance = isset($member['mb_point']) ? (int) $member['mb_point'] : 0;
        if ($balance < $extra_points) {
            return array(
                'ok'      => false,
                'message' => '포인트가 부족합니다. (필요 '.number_format($extra_points).'P · 보유 '.number_format($balance).'P)',
            );
        }

        $charge = eottae_ad_platform_charge_points((int) $campaign['ad_id'], $mb_id, $extra_points, '광고 연장 · '.$campaign['slot_name'].' · '.$extra_days.'일');
        if (empty($charge['ok'])) {
            return $charge;
        }

        $new_total = (int) $campaign['total_points'] + $extra_points;
        $new_charged = (int) $campaign['points_charged'] + $extra_points;
        $now = G5_TIME_YMDHIS;
        $campaign_table = eottae_ad_platform_campaign_table();

        sql_query(" update `{$campaign_table}` set
            end_date = '".sql_escape_string($new_end_date)."',
            days = '{$new_days}',
            total_points = '{$new_total}',
            points_charged = '{$new_charged}',
            updated_at = '{$now}'
            where ad_id = '".(int) $campaign['ad_id']."' ");

        return array(
            'ok'           => true,
            'message'      => '광고가 '.$extra_days.'일 연장되었습니다. ('.number_format($extra_points).'P 차감)',
            'end_date'     => $new_end_date,
            'total_points' => $new_total,
        );
    }
}

if (!function_exists('eottae_ad_platform_member_cancel')) {
    function eottae_ad_platform_member_cancel($ad_id, $mb_id, $is_super = false)
    {
        $campaign = eottae_ad_platform_get_campaign($ad_id);
        if (!$campaign) {
            return array('ok' => false, 'message' => '광고를 찾을 수 없습니다.');
        }
        if (!eottae_ad_platform_member_owns_campaign($campaign, $mb_id, $is_super)) {
            return array('ok' => false, 'message' => '취소 권한이 없습니다.');
        }
        if (!eottae_ad_platform_member_can_cancel_campaign($campaign)) {
            return array('ok' => false, 'message' => '승인 대기·대기등록 상태에서만 취소할 수 있습니다.');
        }

        $now = G5_TIME_YMDHIS;
        $campaign_table = eottae_ad_platform_campaign_table();
        sql_query(" update `{$campaign_table}` set
            status = 'cancelled',
            waitlist_order = 0,
            updated_at = '{$now}'
            where ad_id = '".(int) $campaign['ad_id']."' ");

        return array('ok' => true, 'message' => '광고 신청이 취소되었습니다.');
    }
}

if (!function_exists('eottae_ad_platform_save_image_binary')) {
    function eottae_ad_platform_save_image_binary($binary, $ext = 'png')
    {
        $ext = in_array($ext, array('png', 'jpg', 'jpeg', 'webp'), true) ? $ext : 'png';
        $dir = G5_DATA_PATH.'/ad-platform';
        if (!is_dir($dir)) {
            @mkdir($dir, G5_DIR_PERMISSION, true);
            @chmod($dir, G5_DIR_PERMISSION);
        }

        $filename = 'ad_ai_'.date('Ymd_His').'_'.substr(md5(uniqid('', true)), 0, 10).'.'.$ext;
        $dest = $dir.'/'.$filename;
        if (@file_put_contents($dest, $binary) === false) {
            return array('ok' => false, 'message' => 'AI 이미지 저장에 실패했습니다.');
        }
        @chmod($dest, G5_FILE_PERMISSION);

        return array('ok' => true, 'url' => G5_DATA_URL.'/ad-platform/'.$filename);
    }
}

if (!function_exists('eottae_ad_platform_build_ai_context')) {
    function eottae_ad_platform_build_ai_context($mb_id, array $input = array())
    {
        if (!function_exists('eottae_business_primary_shop') && is_file(G5_LIB_PATH.'/eottae-business-snippet.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-business-snippet.lib.php';
        }

        $shop = function_exists('eottae_business_primary_shop') ? eottae_business_primary_shop($mb_id) : array();
        $slot = eottae_ad_platform_get_slot_by_code(isset($input['slot_code']) ? $input['slot_code'] : '');

        $context = array(
            'slot_name' => $slot ? $slot['slot_name'] : '',
            'slot_desc' => $slot ? $slot['slot_desc'] : '',
            'topic'     => trim((string) ($input['topic'] ?? '')),
            'tone'      => trim((string) ($input['tone'] ?? 'friendly')),
            'offer'     => trim((string) ($input['offer'] ?? '')),
            'target'    => trim((string) ($input['target'] ?? '교민·관광객')),
        );

        if (!empty($shop['name'])) {
            $context['shop_name'] = $shop['name'];
        }
        if (!empty($shop['category'])) {
            $context['category'] = $shop['category'];
        }
        if (!empty($shop['region'])) {
            $context['region'] = $shop['region'];
        }
        if (!empty($shop['address'])) {
            $context['address'] = $shop['address'];
        }
        if (!empty($shop['content'])) {
            $context['intro'] = strip_tags($shop['content']);
        }

        return $context;
    }
}

if (!function_exists('eottae_ad_platform_record_daily_stat')) {
    function eottae_ad_platform_record_daily_stat($ad_id, $type)
    {
        $ad_id = (int) $ad_id;
        if ($ad_id < 1) {
            return;
        }

        eottae_ad_platform_ensure_schema();
        $stat_table = eottae_ad_platform_stat_daily_table();
        $today = G5_TIME_YMD;
        $type = ($type === 'click') ? 'click' : 'impression';
        $impressions = $type === 'impression' ? 1 : 0;
        $clicks = $type === 'click' ? 1 : 0;

        sql_query(" insert into `{$stat_table}` set
            ad_id = '{$ad_id}',
            stat_date = '{$today}',
            impressions = '{$impressions}',
            clicks = '{$clicks}'
            on duplicate key update
            impressions = impressions + '{$impressions}',
            clicks = clicks + '{$clicks}' ", false);
    }
}

if (!function_exists('eottae_ad_platform_get_daily_stats')) {
    function eottae_ad_platform_get_daily_stats($ad_id, $days = 30)
    {
        $ad_id = (int) $ad_id;
        $days = max(1, min(90, (int) $days));
        if ($ad_id < 1) {
            return array();
        }

        eottae_ad_platform_ensure_schema();
        $stat_table = eottae_ad_platform_stat_daily_table();
        $from_date = date('Y-m-d', strtotime('-'.($days - 1).' days'));
        $result = sql_query(" select stat_date, impressions, clicks
            from `{$stat_table}`
            where ad_id = '{$ad_id}'
              and stat_date >= '".sql_escape_string($from_date)."'
            order by stat_date asc ");

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $impressions = (int) ($row['impressions'] ?? 0);
            $clicks = (int) ($row['clicks'] ?? 0);
            $rows[] = array(
                'stat_date'   => (string) ($row['stat_date'] ?? ''),
                'impressions' => $impressions,
                'clicks'      => $clicks,
                'ctr'         => $impressions > 0 ? round($clicks / $impressions * 100, 2) : 0,
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_ad_platform_get_campaign_report')) {
    function eottae_ad_platform_get_campaign_report($ad_id, $days = 30)
    {
        $campaign = eottae_ad_platform_get_campaign($ad_id);
        if (!$campaign) {
            return null;
        }

        $daily = eottae_ad_platform_get_daily_stats($ad_id, $days);
        $days_left = 0;
        if ($campaign['end_date'] !== '' && $campaign['end_date'] !== '0000-00-00') {
            $days_left = max(0, (int) floor((strtotime($campaign['end_date']) - strtotime(G5_TIME_YMD)) / 86400));
        }

        return array(
            'campaign' => $campaign,
            'daily'    => $daily,
            'summary'  => array(
                'impressions' => (int) $campaign['impressions'],
                'clicks'      => (int) $campaign['clicks'],
                'ctr'         => (float) $campaign['ctr'],
                'days_left'   => $days_left,
                'bid_bonus'   => (int) $campaign['bid_bonus'],
            ),
        );
    }
}

if (!function_exists('eottae_ad_platform_report_url')) {
    function eottae_ad_platform_report_url($ad_id)
    {
        return G5_URL.'/page/eottae-ad-report.php?ad_id='.(int) $ad_id;
    }
}

if (!function_exists('eottae_ad_platform_target_region_options')) {
    function eottae_ad_platform_target_region_options()
    {
        if (function_exists('eottae_community_region_options')) {
            return eottae_community_region_options();
        }

        return array('세부시티', '막탄', 'IT Park', '아얄라', '만다우에', '라푸라푸');
    }
}

if (!function_exists('eottae_ad_platform_target_category_options')) {
    function eottae_ad_platform_target_category_options($slot_code)
    {
        global $g5;

        $slot = eottae_ad_platform_get_slot_by_code($slot_code);
        if (!$slot || empty($slot['bo_table'])) {
            return array();
        }

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $slot['bo_table']);
        $board = sql_fetch(" select bo_category_list from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' ");
        $cats = array();

        if (function_exists('eottae_shop_board_categories') && $bo_table === (defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop')) {
            $cats = eottae_shop_board_categories($board ?: array());
        } elseif (!empty($board['bo_category_list'])) {
            $cats = array_values(array_filter(array_map('trim', explode('|', $board['bo_category_list']))));
        }

        return $cats;
    }
}

if (!function_exists('eottae_ad_platform_expiring_campaigns')) {
    function eottae_ad_platform_expiring_campaigns($mb_id, $within_days = 2)
    {
        $mb_id = trim((string) $mb_id);
        $within_days = max(1, min(7, (int) $within_days));
        if ($mb_id === '') {
            return array();
        }

        eottae_ad_platform_ensure_schema();
        $campaign_table = eottae_ad_platform_campaign_table();
        $slot_table = eottae_ad_platform_slot_table();
        $today = G5_TIME_YMD;
        $until = date('Y-m-d', strtotime('+'.$within_days.' days'));
        $esc_mb = sql_escape_string($mb_id);

        $result = sql_query(" select c.*, s.slot_code, s.slot_name, s.is_premium
            from `{$campaign_table}` c
            left join `{$slot_table}` s on s.slot_id = c.slot_id
            where c.mb_id = '{$esc_mb}'
              and c.status = 'active'
              and c.end_date >= '{$today}'
              and c.end_date <= '{$until}'
            order by c.end_date asc, c.ad_id asc ");

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $item = eottae_ad_platform_normalize_campaign($row);
            if ($item) {
                $item['days_left'] = max(0, (int) floor((strtotime($item['end_date']) - strtotime($today)) / 86400));
                $rows[] = $item;
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_ad_platform_send_extend_reminder')) {
    function eottae_ad_platform_send_extend_reminder(array $campaign)
    {
        global $g5, $config;

        $recv_mb_id = preg_replace('/[^a-z0-9_]/i', '', (string) ($campaign['mb_id'] ?? ''));
        if ($recv_mb_id === '') {
            return false;
        }

        $sender_mb_id = preg_replace('/[^a-z0-9_]/i', '', (string) ($config['cf_admin'] ?? 'admin'));
        if ($sender_mb_id === '') {
            $sender_mb_id = 'admin';
        }

        $extend_url = eottae_ad_platform_edit_url((int) $campaign['ad_id']);
        $body = "[세부어때 · 광고 연장 안내]\n\n"
            .get_text($campaign['slot_name'])." 광고 \"".get_text($campaign['title'])."\"가 "
            .get_text($campaign['end_date'])."에 종료됩니다.\n"
            ."연장하려면 아래 페이지에서 연장 일수를 입력해 주세요.\n\n"
            .$extend_url;
        $me_memo = sql_escape_string($body);

        if (!function_exists('get_memo_not_read')) {
            include_once G5_LIB_PATH.'/get_data.lib.php';
        }

        sql_query(" insert into {$g5['memo_table']}
            ( me_recv_mb_id, me_send_mb_id, me_send_datetime, me_memo, me_read_datetime, me_type, me_send_ip )
            values (
                '".sql_escape_string($recv_mb_id)."',
                '".sql_escape_string($sender_mb_id)."',
                '".G5_TIME_YMDHIS."',
                '{$me_memo}',
                '0000-00-00 00:00:00',
                'recv',
                '".sql_escape_string($_SERVER['REMOTE_ADDR'] ?? '')."'
            ) ", false);
        $me_id = (int) sql_insert_id();
        if ($me_id < 1) {
            return false;
        }

        sql_query(" insert into {$g5['memo_table']}
            ( me_recv_mb_id, me_send_mb_id, me_send_datetime, me_memo, me_read_datetime, me_send_id, me_type, me_send_ip )
            values (
                '".sql_escape_string($recv_mb_id)."',
                '".sql_escape_string($sender_mb_id)."',
                '".G5_TIME_YMDHIS."',
                '{$me_memo}',
                '0000-00-00 00:00:00',
                '{$me_id}',
                'send',
                '".sql_escape_string($_SERVER['REMOTE_ADDR'] ?? '')."'
            ) ", false);

        $memo_cnt = get_memo_not_read($recv_mb_id);
        sql_query(" update {$g5['member_table']} set
            mb_memo_call = '".sql_escape_string($sender_mb_id)."',
            mb_memo_cnt = '".(int) $memo_cnt."'
            where mb_id = '".sql_escape_string($recv_mb_id)."' ", false);

        return true;
    }
}

if (!function_exists('eottae_ad_platform_process_expiry_reminders')) {
    function eottae_ad_platform_process_expiry_reminders()
    {
        eottae_ad_platform_ensure_schema();
        $campaign_table = eottae_ad_platform_campaign_table();
        $slot_table = eottae_ad_platform_slot_table();
        $reminder_date = date('Y-m-d', strtotime('+2 days'));
        $now = G5_TIME_YMDHIS;

        $result = sql_query(" select c.*, s.slot_code, s.slot_name, s.is_premium
            from `{$campaign_table}` c
            left join `{$slot_table}` s on s.slot_id = c.slot_id
            where c.status = 'active'
              and c.end_date = '".sql_escape_string($reminder_date)."'
              and c.extend_reminder_sent = 0 ");

        while ($row = sql_fetch_array($result)) {
            $campaign = eottae_ad_platform_normalize_campaign($row);
            if (!$campaign) {
                continue;
            }
            if (eottae_ad_platform_send_extend_reminder($campaign)) {
                sql_query(" update `{$campaign_table}` set extend_reminder_sent = 1, updated_at = '{$now}' where ad_id = '".(int) $campaign['ad_id']."' ");
            }
        }
    }
}

if (!function_exists('eottae_ad_platform_visitor_key')) {
    function eottae_ad_platform_visitor_key()
    {
        global $member;

        if (!empty($member['mb_id'])) {
            return 'm:'.md5((string) $member['mb_id']);
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 160) : '';

        return 'g:'.md5($ip.'|'.$ua);
    }
}

if (!function_exists('eottae_ad_platform_try_acquire_event')) {
    function eottae_ad_platform_try_acquire_event($ad_id, $type)
    {
        $ad_id = (int) $ad_id;
        if ($ad_id < 1) {
            return false;
        }

        eottae_ad_platform_ensure_schema();
        $type = ($type === 'click') ? 'click' : 'impression';
        $dedupe_key = md5(eottae_ad_platform_visitor_key());
        if ($type === 'impression') {
            $expires_at = date('Y-m-d', strtotime(G5_TIME_YMD.' +1 day')).' 00:00:00';
        } else {
            $expires_at = date('Y-m-d H:i:s', time() + 1800);
        }

        $table = eottae_ad_platform_event_dedupe_table();
        $now = G5_TIME_YMDHIS;
        sql_query(" insert ignore into `{$table}` set
            ad_id = '{$ad_id}',
            event_type = '".sql_escape_string($type)."',
            dedupe_key = '".sql_escape_string($dedupe_key)."',
            expires_at = '".sql_escape_string($expires_at)."',
            created_at = '{$now}' ", false);

        if (function_exists('get_sql_affected_rows')) {
            return get_sql_affected_rows() > 0;
        }

        $row = sql_fetch(" select dedupe_id from `{$table}`
            where ad_id = '{$ad_id}'
              and event_type = '".sql_escape_string($type)."'
              and dedupe_key = '".sql_escape_string($dedupe_key)."'
              and created_at = '{$now}' ");
        return !empty($row['dedupe_id']);
    }
}

if (!function_exists('eottae_ad_platform_cleanup_event_dedupe')) {
    function eottae_ad_platform_cleanup_event_dedupe()
    {
        eottae_ad_platform_ensure_schema();
        $table = eottae_ad_platform_event_dedupe_table();
        $now = G5_TIME_YMDHIS;
        sql_query(" delete from `{$table}` where expires_at > '0000-00-00 00:00:00' and expires_at <= '{$now}' ", false);
    }
}

if (!function_exists('eottae_ad_platform_verify_cron_key')) {
    function eottae_ad_platform_verify_cron_key($provided_key)
    {
        $provided_key = trim((string) $provided_key);
        if ($provided_key === '') {
            return false;
        }

        $keys = array();
        if (function_exists('g5site_cfg')) {
            foreach (array('ad_platform_cron_key', 'talkroom_ai_cron_key') as $cfg_key) {
                $value = trim((string) g5site_cfg($cfg_key, ''));
                if ($value !== '') {
                    $keys[] = $value;
                }
            }
        }

        $keys = array_values(array_unique($keys));
        if (empty($keys)) {
            return false;
        }

        foreach ($keys as $secret) {
            if (function_exists('hash_equals') ? hash_equals($secret, $provided_key) : ($secret === $provided_key)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('eottae_ad_platform_run_maintain_cron')) {
    function eottae_ad_platform_run_maintain_cron(array $options = array())
    {
        $dry_run = !empty($options['dry_run']);
        if ($dry_run) {
            return array(
                'ok'      => true,
                'dry_run' => true,
                'message' => 'dry-run: expire, activate, reminders, dedupe cleanup',
            );
        }

        eottae_ad_platform_ensure_schema();
        $result = eottae_ad_platform_maintain(true, true);

        return array_merge($result, array(
            'dry_run' => false,
            'ran_at'  => G5_TIME_YMDHIS,
        ));
    }
}
