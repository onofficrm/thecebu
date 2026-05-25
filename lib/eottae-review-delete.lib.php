<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_review_delete_bootstrap_tables')) {
    function eottae_review_delete_bootstrap_tables()
    {
        global $g5;

        if (!isset($g5['eottae_review_delete_request_table'])) {
            $g5['eottae_review_delete_request_table'] = G5_TABLE_PREFIX.'eottae_review_delete_request';
        }
    }
}

if (!function_exists('eottae_review_delete_ensure_schema')) {
    function eottae_review_delete_ensure_schema()
    {
        global $g5;

        eottae_review_delete_bootstrap_tables();
        $table = $g5['eottae_review_delete_request_table'];

        sql_query(" CREATE TABLE IF NOT EXISTS `{$table}` (
            `rdr_id` int(11) NOT NULL AUTO_INCREMENT,
            `review_wr_id` int(11) NOT NULL DEFAULT '0',
            `shop_wr_id` int(11) NOT NULL DEFAULT '0',
            `request_mb_id` varchar(20) NOT NULL DEFAULT '',
            `request_reason` text NOT NULL,
            `request_status` varchar(20) NOT NULL DEFAULT 'pending',
            `request_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `processed_by` varchar(20) NOT NULL DEFAULT '',
            `processed_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `process_note` text NOT NULL,
            PRIMARY KEY (`rdr_id`),
            KEY `review_wr_id` (`review_wr_id`),
            KEY `shop_wr_id` (`shop_wr_id`),
            KEY `request_status` (`request_status`),
            KEY `request_mb_id` (`request_mb_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

        return true;
    }
}

if (!function_exists('eottae_review_visible_sql')) {
    function eottae_review_visible_sql($alias = '')
    {
        $prefix = $alias !== '' ? $alias.'.' : '';

        return " ({$prefix}wr_4 = '' or {$prefix}wr_4 = 'visible') ";
    }
}

if (!function_exists('eottae_review_is_visible_status')) {
    function eottae_review_is_visible_status($status)
    {
        $status = (string) $status;

        return $status === '' || $status === 'visible';
    }
}

if (!function_exists('eottae_review_delete_token')) {
    function eottae_review_delete_token($regenerate = false)
    {
        $key = 'eottae_review_delete_token';
        $token = get_session($key);
        if ($regenerate || $token === '' || $token === null) {
            $token = md5(uniqid((string) mt_rand(), true));
            set_session($key, $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_review_delete_execute')) {
    function eottae_review_delete_execute($review_wr_id)
    {
        global $g5;

        $review_wr_id = (int) $review_wr_id;
        if ($review_wr_id < 1) {
            return array('ok' => false, 'message' => '리뷰를 찾을 수 없습니다.');
        }

        $write_table = eottae_review_write_table();
        $visible = eottae_review_visible_sql();
        $row = sql_fetch(" select wr_id, mb_id, wr_1, wr_4 from {$write_table}
            where wr_id = '{$review_wr_id}' and wr_is_comment = 0 and {$visible} limit 1 ");
        if (empty($row['wr_id'])) {
            return array('ok' => false, 'message' => '삭제할 리뷰를 찾을 수 없습니다.');
        }

        $shop_wr_id = (int) $row['wr_1'];
        sql_query(" update {$write_table} set wr_4 = 'deleted' where wr_id = '{$review_wr_id}' ");
        if (!empty($row['mb_id'])) {
            eottae_revoke_review_points($row['mb_id'], $review_wr_id);
        }
        if ($shop_wr_id > 0 && function_exists('eottae_sync_shop_review_stats')) {
            eottae_sync_shop_review_stats($shop_wr_id);
        }

        $req_table = $g5['eottae_review_delete_request_table'];
        sql_query(" update {$req_table}
            set request_status = 'approved',
                processed_datetime = '".G5_TIME_YMDHIS."'
            where review_wr_id = '{$review_wr_id}' and request_status = 'pending' ");

        return array(
            'ok'         => true,
            'message'    => '리뷰가 삭제되었습니다.',
            'shop_wr_id' => $shop_wr_id,
        );
    }
}

if (!function_exists('eottae_review_delete_request_pending_for_review')) {
    function eottae_review_delete_request_pending_for_review($review_wr_id)
    {
        global $g5;

        eottae_review_delete_bootstrap_tables();
        $review_wr_id = (int) $review_wr_id;
        if ($review_wr_id < 1) {
            return null;
        }

        $table = $g5['eottae_review_delete_request_table'];
        $row = sql_fetch(" select * from {$table}
            where review_wr_id = '{$review_wr_id}' and request_status = 'pending'
            order by rdr_id desc limit 1 ");

        return !empty($row['rdr_id']) ? $row : null;
    }
}

if (!function_exists('eottae_review_delete_pending_ids_for_shop')) {
    function eottae_review_delete_pending_ids_for_shop($shop_wr_id)
    {
        global $g5;

        eottae_review_delete_bootstrap_tables();
        $shop_wr_id = (int) $shop_wr_id;
        if ($shop_wr_id < 1) {
            return array();
        }

        $table = $g5['eottae_review_delete_request_table'];
        $result = sql_query(" select review_wr_id from {$table}
            where shop_wr_id = '{$shop_wr_id}' and request_status = 'pending' ");
        $ids = array();
        while ($row = sql_fetch_array($result)) {
            $ids[(int) $row['review_wr_id']] = true;
        }

        return $ids;
    }
}

if (!function_exists('eottae_review_delete_request_create')) {
    function eottae_review_delete_request_create($review_wr_id, $shop_wr_id, $mb_id, $reason = '')
    {
        global $g5;

        eottae_review_delete_ensure_schema();

        $review_wr_id = (int) $review_wr_id;
        $shop_wr_id = (int) $shop_wr_id;
        $mb_id = sql_escape_string((string) $mb_id);
        $reason = trim(strip_tags((string) $reason));

        if ($review_wr_id < 1 || $shop_wr_id < 1 || $mb_id === '') {
            return array('ok' => false, 'message' => '요청 정보가 올바르지 않습니다.');
        }

        if (!eottae_business_owns_shop($mb_id, $shop_wr_id)) {
            return array('ok' => false, 'message' => '본인 업체 리뷰만 삭제를 요청할 수 있습니다.');
        }

        $write_table = eottae_review_write_table();
        $visible = eottae_review_visible_sql();
        $review = sql_fetch(" select wr_id, wr_1 from {$write_table}
            where wr_id = '{$review_wr_id}' and wr_is_comment = 0 and {$visible} limit 1 ");
        if (empty($review['wr_id'])) {
            return array('ok' => false, 'message' => '리뷰를 찾을 수 없습니다.');
        }
        if ((int) $review['wr_1'] !== $shop_wr_id) {
            return array('ok' => false, 'message' => '리뷰와 업체 정보가 일치하지 않습니다.');
        }

        if (eottae_review_delete_request_pending_for_review($review_wr_id)) {
            return array('ok' => false, 'message' => '이미 삭제 검토 중인 리뷰입니다.');
        }

        $table = $g5['eottae_review_delete_request_table'];
        $reason_sql = sql_escape_string($reason);
        sql_query(" insert into {$table} set
            review_wr_id = '{$review_wr_id}',
            shop_wr_id = '{$shop_wr_id}',
            request_mb_id = '{$mb_id}',
            request_reason = '{$reason_sql}',
            request_status = 'pending',
            request_datetime = '".G5_TIME_YMDHIS."' ");

        return array('ok' => true, 'message' => '삭제 요청이 접수되었습니다. 최고관리자 승인 후 반영됩니다.');
    }
}

if (!function_exists('eottae_review_delete_request_list')) {
    function eottae_review_delete_request_list($status = 'pending', $limit = 50)
    {
        global $g5;

        eottae_review_delete_bootstrap_tables();
        $table = $g5['eottae_review_delete_request_table'];
        $write_table = eottae_review_write_table();
        $shop_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
        $status = sql_escape_string((string) $status);
        $limit = max(1, min(100, (int) $limit));

        $where = "1=1";
        if ($status !== '' && $status !== 'all') {
            $where = " r.request_status = '{$status}' ";
        }

        $sql = " select r.*,
                rev.wr_content as review_content,
                rev.wr_name as review_author,
                rev.wr_2 as review_rating,
                rev.wr_datetime as review_datetime,
                shop.wr_subject as shop_name
            from {$table} r
            left join {$write_table} rev on rev.wr_id = r.review_wr_id and rev.wr_is_comment = 0
            left join {$shop_table} shop on shop.wr_id = r.shop_wr_id
            where {$where}
            order by r.rdr_id desc
            limit {$limit} ";
        $result = sql_query($sql);
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = $row;
        }

        return $rows;
    }
}

if (!function_exists('eottae_review_delete_pending_count')) {
    function eottae_review_delete_pending_count()
    {
        global $g5;

        eottae_review_delete_bootstrap_tables();
        $table = $g5['eottae_review_delete_request_table'];
        $row = sql_fetch(" select count(*) as cnt from {$table} where request_status = 'pending' ");

        return isset($row['cnt']) ? (int) $row['cnt'] : 0;
    }
}

if (!function_exists('eottae_review_delete_request_approve')) {
    function eottae_review_delete_request_approve($rdr_id, $admin_mb_id)
    {
        global $g5;

        eottae_review_delete_bootstrap_tables();
        $rdr_id = (int) $rdr_id;
        $admin_mb_id = sql_escape_string((string) $admin_mb_id);
        if ($rdr_id < 1 || $admin_mb_id === '') {
            return array('ok' => false, 'message' => '요청 정보가 올바르지 않습니다.');
        }

        $table = $g5['eottae_review_delete_request_table'];
        $req = sql_fetch(" select * from {$table} where rdr_id = '{$rdr_id}' limit 1 ");
        if (empty($req['rdr_id'])) {
            return array('ok' => false, 'message' => '삭제 요청을 찾을 수 없습니다.');
        }
        if ($req['request_status'] !== 'pending') {
            return array('ok' => false, 'message' => '이미 처리된 요청입니다.');
        }

        $delete = eottae_review_delete_execute((int) $req['review_wr_id']);
        if (empty($delete['ok'])) {
            return $delete;
        }

        sql_query(" update {$table} set
            request_status = 'approved',
            processed_by = '{$admin_mb_id}',
            processed_datetime = '".G5_TIME_YMDHIS."'
            where rdr_id = '{$rdr_id}' ");

        return array('ok' => true, 'message' => '리뷰 삭제를 승인했습니다.');
    }
}

if (!function_exists('eottae_review_delete_request_reject')) {
    function eottae_review_delete_request_reject($rdr_id, $admin_mb_id, $note = '')
    {
        global $g5;

        eottae_review_delete_bootstrap_tables();
        $rdr_id = (int) $rdr_id;
        $admin_mb_id = sql_escape_string((string) $admin_mb_id);
        $note = sql_escape_string(trim(strip_tags((string) $note)));

        if ($rdr_id < 1 || $admin_mb_id === '') {
            return array('ok' => false, 'message' => '요청 정보가 올바르지 않습니다.');
        }

        $table = $g5['eottae_review_delete_request_table'];
        $req = sql_fetch(" select rdr_id, request_status from {$table} where rdr_id = '{$rdr_id}' limit 1 ");
        if (empty($req['rdr_id'])) {
            return array('ok' => false, 'message' => '삭제 요청을 찾을 수 없습니다.');
        }
        if ($req['request_status'] !== 'pending') {
            return array('ok' => false, 'message' => '이미 처리된 요청입니다.');
        }

        sql_query(" update {$table} set
            request_status = 'rejected',
            processed_by = '{$admin_mb_id}',
            processed_datetime = '".G5_TIME_YMDHIS."',
            process_note = '{$note}'
            where rdr_id = '{$rdr_id}' ");

        return array('ok' => true, 'message' => '삭제 요청을 반려했습니다.');
    }
}
