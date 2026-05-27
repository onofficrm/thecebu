<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!defined('EOTTae_SHOP_SPOT_SLOT_COUNT')) {
    define('EOTTae_SHOP_SPOT_SLOT_COUNT', 3);
}

if (!function_exists('eottae_shop_spot_bootstrap_tables')) {
    function eottae_shop_spot_bootstrap_tables()
    {
        global $g5;

        if (!isset($g5['eottae_shop_spot_config_table'])) {
            $g5['eottae_shop_spot_config_table'] = G5_TABLE_PREFIX.'eottae_shop_spot_config';
        }
        if (!isset($g5['eottae_shop_spot_booking_table'])) {
            $g5['eottae_shop_spot_booking_table'] = G5_TABLE_PREFIX.'eottae_shop_spot_booking';
        }
    }
}

if (!function_exists('eottae_shop_spot_ensure_schema')) {
    function eottae_shop_spot_ensure_schema()
    {
        global $g5;

        eottae_shop_spot_bootstrap_tables();
        $config_table = $g5['eottae_shop_spot_config_table'];
        $booking_table = $g5['eottae_shop_spot_booking_table'];

        sql_query(" CREATE TABLE IF NOT EXISTS `{$config_table}` (
            `spot_slot` tinyint unsigned NOT NULL,
            `points_required` int NOT NULL DEFAULT '0',
            `days_duration` int NOT NULL DEFAULT '0',
            `is_enabled` tinyint unsigned NOT NULL DEFAULT '1',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_by` varchar(20) NOT NULL DEFAULT '',
            PRIMARY KEY (`spot_slot`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ", false);

        sql_query(" CREATE TABLE IF NOT EXISTS `{$booking_table}` (
            `booking_id` int unsigned NOT NULL AUTO_INCREMENT,
            `spot_slot` tinyint unsigned NOT NULL DEFAULT '1',
            `list_bo_table` varchar(20) NOT NULL DEFAULT '',
            `shop_bo_table` varchar(20) NOT NULL DEFAULT '',
            `shop_wr_id` int unsigned NOT NULL DEFAULT '0',
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `points_paid` int NOT NULL DEFAULT '0',
            `starts_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `ends_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `status` varchar(20) NOT NULL DEFAULT 'active',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`booking_id`),
            KEY `idx_list_active` (`list_bo_table`, `status`, `spot_slot`, `ends_at`),
            KEY `idx_shop` (`shop_bo_table`, `shop_wr_id`, `status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ", false);

        $count = sql_fetch(" select count(*) as cnt from `{$config_table}` ");
        if ((int) ($count['cnt'] ?? 0) === 0) {
            $defaults = array(
                1 => array('points' => 5000, 'days' => 30),
                2 => array('points' => 4000, 'days' => 14),
                3 => array('points' => 3000, 'days' => 7),
            );
            $now = G5_TIME_YMDHIS;
            foreach ($defaults as $slot => $def) {
                $slot = (int) $slot;
                sql_query(" insert into `{$config_table}` set
                    spot_slot = '{$slot}',
                    points_required = '".(int) $def['points']."',
                    days_duration = '".(int) $def['days']."',
                    is_enabled = 1,
                    updated_at = '{$now}',
                    updated_by = 'system' ");
            }
        }

        return true;
    }
}

if (!function_exists('eottae_shop_spot_slot_count')) {
    function eottae_shop_spot_slot_count()
    {
        return (int) EOTTae_SHOP_SPOT_SLOT_COUNT;
    }
}

if (!function_exists('eottae_shop_spot_get_all_config')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_shop_spot_get_all_config()
    {
        global $g5;

        eottae_shop_spot_ensure_schema();
        $config_table = $g5['eottae_shop_spot_config_table'];
        $slots = array();

        for ($i = 1; $i <= eottae_shop_spot_slot_count(); $i++) {
            $slots[$i] = array(
                'spot_slot'       => $i,
                'points_required' => 0,
                'days_duration'   => 0,
                'is_enabled'      => 1,
            );
        }

        $result = sql_query(" select * from `{$config_table}` order by spot_slot asc ");
        while ($row = sql_fetch_array($result)) {
            $slot = (int) $row['spot_slot'];
            if ($slot < 1 || $slot > eottae_shop_spot_slot_count()) {
                continue;
            }
            $slots[$slot] = array(
                'spot_slot'       => $slot,
                'points_required' => max(0, (int) $row['points_required']),
                'days_duration'   => max(1, (int) $row['days_duration']),
                'is_enabled'      => !empty($row['is_enabled']) ? 1 : 0,
                'updated_at'      => isset($row['updated_at']) ? $row['updated_at'] : '',
                'updated_by'      => isset($row['updated_by']) ? $row['updated_by'] : '',
            );
        }

        return $slots;
    }
}

if (!function_exists('eottae_shop_spot_get_config')) {
    function eottae_shop_spot_get_config($spot_slot)
    {
        $all = eottae_shop_spot_get_all_config();
        $spot_slot = (int) $spot_slot;

        return isset($all[$spot_slot]) ? $all[$spot_slot] : array(
            'spot_slot'       => $spot_slot,
            'points_required' => 0,
            'days_duration'   => 30,
            'is_enabled'      => 0,
        );
    }
}

if (!function_exists('eottae_shop_spot_save_config')) {
    function eottae_shop_spot_save_config($spot_slot, $points_required, $days_duration, $is_enabled, $admin_mb_id = '')
    {
        global $g5;

        eottae_shop_spot_ensure_schema();
        $spot_slot = (int) $spot_slot;
        if ($spot_slot < 1 || $spot_slot > eottae_shop_spot_slot_count()) {
            return array('ok' => false, 'message' => '잘못된 슬롯 번호입니다.');
        }

        $points_required = max(0, (int) $points_required);
        $days_duration = max(1, min(365, (int) $days_duration));
        $is_enabled = $is_enabled ? 1 : 0;
        $admin_mb_id = sql_escape_string(substr((string) $admin_mb_id, 0, 20));
        $now = G5_TIME_YMDHIS;
        $config_table = $g5['eottae_shop_spot_config_table'];

        sql_query(" insert into `{$config_table}` set
            spot_slot = '{$spot_slot}',
            points_required = '{$points_required}',
            days_duration = '{$days_duration}',
            is_enabled = '{$is_enabled}',
            updated_at = '{$now}',
            updated_by = '{$admin_mb_id}'
            on duplicate key update
            points_required = '{$points_required}',
            days_duration = '{$days_duration}',
            is_enabled = '{$is_enabled}',
            updated_at = '{$now}',
            updated_by = '{$admin_mb_id}' ");

        return array('ok' => true, 'message' => '저장되었습니다.');
    }
}

if (!function_exists('eottae_shop_spot_expire_due')) {
    function eottae_shop_spot_expire_due()
    {
        global $g5;

        eottae_shop_spot_ensure_schema();
        $booking_table = $g5['eottae_shop_spot_booking_table'];
        $now = G5_TIME_YMDHIS;

        sql_query(" update `{$booking_table}` set status = 'expired'
            where status = 'active' and ends_at > '0000-00-00 00:00:00' and ends_at < '{$now}' ");

        return true;
    }
}

if (!function_exists('eottae_shop_spot_active_bookings')) {
    /**
     * @return array<int, array<string, mixed>> slot => booking row
     */
    function eottae_shop_spot_active_bookings($list_bo_table)
    {
        global $g5;

        eottae_shop_spot_expire_due();
        eottae_shop_spot_ensure_schema();

        $list_bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $list_bo_table);
        if ($list_bo_table === '') {
            return array();
        }

        $booking_table = $g5['eottae_shop_spot_booking_table'];
        $now = G5_TIME_YMDHIS;
        $result = sql_query(" select * from `{$booking_table}`
            where list_bo_table = '".sql_escape_string($list_bo_table)."'
              and status = 'active'
              and starts_at <= '{$now}'
              and ends_at >= '{$now}'
            order by spot_slot asc, booking_id asc ");

        $by_slot = array();
        while ($row = sql_fetch_array($result)) {
            $slot = (int) $row['spot_slot'];
            if ($slot < 1 || isset($by_slot[$slot])) {
                continue;
            }
            $by_slot[$slot] = $row;
        }

        return $by_slot;
    }
}

if (!function_exists('eottae_shop_spot_featured_wr_ids')) {
    /**
     * @return array<int, int> wr_id => spot_slot
     */
    function eottae_shop_spot_featured_wr_ids($list_bo_table)
    {
        $bookings = eottae_shop_spot_active_bookings($list_bo_table);
        $map = array();

        foreach ($bookings as $slot => $booking) {
            $wr_id = (int) $booking['shop_wr_id'];
            if ($wr_id > 0) {
                $map[$wr_id] = (int) $slot;
            }
        }

        return $map;
    }
}

if (!function_exists('eottae_shop_spot_is_featured')) {
    function eottae_shop_spot_is_featured($list_bo_table, $shop_wr_id)
    {
        $shop_wr_id = (int) $shop_wr_id;
        if ($shop_wr_id < 1) {
            return false;
        }

        $featured = eottae_shop_spot_featured_wr_ids($list_bo_table);

        return isset($featured[$shop_wr_id]);
    }
}

if (!function_exists('eottae_shop_spot_get_list_rows')) {
    /**
     * 목록 최상단용 업체 write 행 (슬롯 순)
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_shop_spot_get_list_rows($list_bo_table)
    {
        global $g5;

        $list_bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $list_bo_table);
        if ($list_bo_table === '' || !function_exists('eottae_is_shop_board') || !eottae_is_shop_board($list_bo_table)) {
            return array();
        }

        $bookings = eottae_shop_spot_active_bookings($list_bo_table);
        if (empty($bookings)) {
            return array();
        }

        $storage_bo = function_exists('eottae_shop_storage_bo_table')
            ? eottae_shop_storage_bo_table($list_bo_table)
            : $list_bo_table;
        $write_table = $g5['write_prefix'].$storage_bo;
        $rows = array();

        ksort($bookings);
        foreach ($bookings as $booking) {
            $wr_id = (int) $booking['shop_wr_id'];
            if ($wr_id < 1) {
                continue;
            }
            $row = sql_fetch(" select * from `{$write_table}` where wr_id = '{$wr_id}' and wr_is_comment = 0 limit 1 ");
            if (empty($row['wr_id'])) {
                continue;
            }
            $row['_eottae_spot_slot'] = (int) $booking['spot_slot'];
            $row['_eottae_spot_featured'] = 1;
            $rows[] = $row;
        }

        return $rows;
    }
}

if (!function_exists('eottae_shop_spot_exclude_wr_ids_sql')) {
    function eottae_shop_spot_exclude_wr_ids_sql($list_bo_table, $column = 'wr_id')
    {
        $featured = eottae_shop_spot_featured_wr_ids($list_bo_table);
        if (empty($featured)) {
            return '';
        }

        $ids = array_map('intval', array_keys($featured));
        $ids = array_filter($ids, function ($id) {
            return $id > 0;
        });
        if (empty($ids)) {
            return '';
        }

        $column = preg_replace('/[^a-z0-9_]/', '', (string) $column);
        if ($column === '') {
            $column = 'wr_id';
        }

        return ' and '.$column.' not in ('.implode(',', $ids).') ';
    }
}

if (!function_exists('eottae_shop_spot_merge_list_rows')) {
    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    function eottae_shop_spot_merge_list_rows($list_bo_table, array $rows, $offset = 0)
    {
        $offset = max(0, (int) $offset);
        if ($offset > 0) {
            return $rows;
        }

        $spot_rows = eottae_shop_spot_get_list_rows($list_bo_table);
        if (empty($spot_rows)) {
            return $rows;
        }

        $spot_ids = array();
        foreach ($spot_rows as $spot_row) {
            $spot_ids[(int) $spot_row['wr_id']] = true;
        }

        $filtered = array();
        foreach ($rows as $row) {
            $wr_id = isset($row['wr_id']) ? (int) $row['wr_id'] : 0;
            if ($wr_id > 0 && isset($spot_ids[$wr_id])) {
                continue;
            }
            $filtered[] = $row;
        }

        return array_merge($spot_rows, $filtered);
    }
}

if (!function_exists('eottae_shop_spot_list_spot_count')) {
    function eottae_shop_spot_list_spot_count($list_bo_table)
    {
        return count(eottae_shop_spot_get_list_rows($list_bo_table));
    }
}

if (!function_exists('eottae_shop_spot_shop_booking')) {
    function eottae_shop_spot_shop_booking($list_bo_table, $shop_wr_id)
    {
        global $g5;

        eottae_shop_spot_expire_due();
        $list_bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $list_bo_table);
        $shop_wr_id = (int) $shop_wr_id;
        if ($list_bo_table === '' || $shop_wr_id < 1) {
            return null;
        }

        $booking_table = $g5['eottae_shop_spot_booking_table'];
        $now = G5_TIME_YMDHIS;

        return sql_fetch(" select * from `{$booking_table}`
            where list_bo_table = '".sql_escape_string($list_bo_table)."'
              and shop_wr_id = '{$shop_wr_id}'
              and status = 'active'
              and ends_at >= '{$now}'
            order by booking_id desc
            limit 1 ");
    }
}

if (!function_exists('eottae_shop_spot_apply')) {
    /**
     * 포인트로 최우수 노출 슬롯 신청
     */
    function eottae_shop_spot_apply($mb_id, $list_bo_table, $shop_bo_table, $shop_wr_id, $spot_slot)
    {
        global $g5;

        eottae_shop_spot_ensure_schema();
        eottae_shop_spot_expire_due();

        $mb_id = trim((string) $mb_id);
        $list_bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $list_bo_table);
        $shop_bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $shop_bo_table);
        $shop_wr_id = (int) $shop_wr_id;
        $spot_slot = (int) $spot_slot;

        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 이용해 주세요.');
        }
        if ($list_bo_table === '' || !function_exists('eottae_is_shop_board') || !eottae_is_shop_board($list_bo_table)) {
            return array('ok' => false, 'message' => '지원하지 않는 게시판입니다.');
        }
        if ($spot_slot < 1 || $spot_slot > eottae_shop_spot_slot_count()) {
            return array('ok' => false, 'message' => '잘못된 노출 자리입니다.');
        }

        $config = eottae_shop_spot_get_config($spot_slot);
        if (empty($config['is_enabled'])) {
            return array('ok' => false, 'message' => '현재 신청할 수 없는 노출 자리입니다.');
        }

        $points = (int) $config['points_required'];
        $days = (int) $config['days_duration'];
        if ($points < 1 || $days < 1) {
            return array('ok' => false, 'message' => '노출 조건이 설정되지 않았습니다. 관리자에게 문의해 주세요.');
        }

        $storage_bo = function_exists('eottae_shop_storage_bo_table')
            ? eottae_shop_storage_bo_table($shop_bo_table !== '' ? $shop_bo_table : $list_bo_table)
            : ($shop_bo_table !== '' ? $shop_bo_table : $list_bo_table);
        $write_table = $g5['write_prefix'].$storage_bo;
        $write = sql_fetch(" select * from `{$write_table}` where wr_id = '{$shop_wr_id}' and wr_is_comment = 0 limit 1 ");
        if (empty($write['wr_id'])) {
            return array('ok' => false, 'message' => '업체를 찾을 수 없습니다.');
        }

        if ($shop_bo_table === '') {
            $shop_bo_table = $storage_bo;
        }

        $member = get_member($mb_id);
        if (empty($member['mb_id'])) {
            return array('ok' => false, 'message' => '회원 정보를 확인할 수 없습니다.');
        }

        if (function_exists('eottae_shop_user_can_manage')) {
            if (!eottae_shop_user_can_manage($write, $list_bo_table)) {
                return array('ok' => false, 'message' => '본인 업체만 신청할 수 있습니다.');
            }
        } elseif (trim((string) $write['mb_id']) !== $mb_id) {
            return array('ok' => false, 'message' => '본인 업체만 신청할 수 있습니다.');
        }

        $active = eottae_shop_spot_active_bookings($list_bo_table);
        if (isset($active[$spot_slot])) {
            $occupied = $active[$spot_slot];
            if ((int) $occupied['shop_wr_id'] !== $shop_wr_id) {
                return array('ok' => false, 'message' => '이 자리는 다른 업체가 노출 중입니다. 다른 자리를 선택해 주세요.');
            }

            return array('ok' => false, 'message' => '이미 해당 자리에 노출 중입니다.');
        }

        $existing_shop = eottae_shop_spot_shop_booking($list_bo_table, $shop_wr_id);
        if (!empty($existing_shop['booking_id'])) {
            return array('ok' => false, 'message' => '이 업체는 이미 최우수 노출 중입니다. 종료 후 다시 신청해 주세요.');
        }

        $balance = isset($member['mb_point']) ? (int) $member['mb_point'] : 0;
        if ($balance < $points) {
            return array('ok' => false, 'message' => '포인트가 부족합니다. (필요 '.number_format($points).'P · 보유 '.number_format($balance).'P)');
        }

        $booking_table = $g5['eottae_shop_spot_booking_table'];
        $now = G5_TIME_YMDHIS;
        $ends = date('Y-m-d H:i:s', strtotime('+'.$days.' days'));

        sql_query(" insert into `{$booking_table}` set
            spot_slot = '{$spot_slot}',
            list_bo_table = '".sql_escape_string($list_bo_table)."',
            shop_bo_table = '".sql_escape_string($shop_bo_table)."',
            shop_wr_id = '{$shop_wr_id}',
            mb_id = '".sql_escape_string($mb_id)."',
            points_paid = '{$points}',
            starts_at = '{$now}',
            ends_at = '{$ends}',
            status = 'active',
            created_at = '{$now}' ");

        $booking_id = (int) sql_insert_id();
        if ($booking_id < 1) {
            return array('ok' => false, 'message' => '신청 처리에 실패했습니다.');
        }

        $point_ok = insert_point(
            $mb_id,
            $points * -1,
            '최우수 업체 노출 신청 ('.$spot_slot.'번 자리 · '.$days.'일)',
            '@eottae_shop_spot',
            (string) $booking_id,
            'apply'
        );

        if ($point_ok < 1) {
            sql_query(" delete from `{$booking_table}` where booking_id = '{$booking_id}' ");

            return array('ok' => false, 'message' => '포인트 차감에 실패했습니다. 잠시 후 다시 시도해 주세요.');
        }

        return array(
            'ok'         => true,
            'message'    => $spot_slot.'번 자리 최우수 노출이 시작되었습니다. ('.$days.'일 · '.number_format($points).'P 차감)',
            'booking_id' => $booking_id,
            'ends_at'    => $ends,
            'spot_slot'  => $spot_slot,
        );
    }
}

if (!function_exists('eottae_shop_spot_admin_bookings')) {
    function eottae_shop_spot_admin_bookings($limit = 30)
    {
        global $g5;

        eottae_shop_spot_ensure_schema();
        eottae_shop_spot_expire_due();

        $booking_table = $g5['eottae_shop_spot_booking_table'];
        $limit = max(1, min(100, (int) $limit));
        $result = sql_query(" select * from `{$booking_table}`
            order by field(status, 'active', 'expired', 'cancelled'), ends_at desc, booking_id desc
            limit {$limit} ");

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = $row;
        }

        return $rows;
    }
}
