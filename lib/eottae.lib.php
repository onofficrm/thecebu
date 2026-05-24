<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_is_business_member')) {
    function eottae_is_business_member($member = null)
    {
        global $member;

        $m = is_array($member) ? $member : (isset($member) && is_array($member) ? $member : array());
        if (!$m && function_exists('get_member')) {
            global $is_member;
            if (!empty($is_member) && isset($member['mb_id'])) {
                $m = $member;
            }
        }
        if (!is_array($m) || empty($m['mb_id'])) {
            return false;
        }

        $level = isset($m['mb_level']) ? (int) $m['mb_level'] : 0;
        if ($level >= (defined('EOTTae_BUSINESS_LEVEL') ? EOTTae_BUSINESS_LEVEL : 5)) {
            return true;
        }

        return isset($m['mb_1']) && $m['mb_1'] === 'business';
    }
}

if (!function_exists('eottae_shop_from_write')) {
    /**
     * shop 게시판 wr_* → 표준 배열 (명세 매핑)
     */
    function eottae_shop_from_write($wr)
    {
        if (!is_array($wr)) {
            return array();
        }

        return array(
            'name'          => isset($wr['wr_subject']) ? get_text($wr['wr_subject']) : '',
            'category'      => isset($wr['wr_1']) ? get_text($wr['wr_1']) : '',
            'region'        => isset($wr['wr_2']) ? get_text($wr['wr_2']) : '',
            'address'       => isset($wr['wr_3']) ? get_text($wr['wr_3']) : '',
            'phone'         => isset($wr['wr_4']) ? get_text($wr['wr_4']) : '',
            'inquiry_code'  => isset($wr['wr_5']) ? get_text($wr['wr_5']) : '',
            'hours'         => isset($wr['wr_6']) ? get_text($wr['wr_6']) : '',
            'closed'        => isset($wr['wr_7']) ? get_text($wr['wr_7']) : '',
            'status'        => isset($wr['wr_8']) ? get_text($wr['wr_8']) : '',
            'lat'           => isset($wr['wr_9']) ? get_text($wr['wr_9']) : '',
            'lng'           => isset($wr['wr_10']) ? get_text($wr['wr_10']) : '',
            'website'       => isset($wr['wr_link1']) ? get_text($wr['wr_link1']) : '',
            'sns'           => isset($wr['wr_link2']) ? get_text($wr['wr_link2']) : '',
            'content'       => isset($wr['wr_content']) ? $wr['wr_content'] : '',
            'wr_id'         => isset($wr['wr_id']) ? (int) $wr['wr_id'] : 0,
        );
    }
}

if (!function_exists('eottae_tel_href')) {
    function eottae_tel_href($phone)
    {
        $digits = preg_replace('/[^0-9+]/', '', (string) $phone);

        return $digits !== '' ? 'tel:'.$digits : '#';
    }
}

if (!function_exists('eottae_maps_directions_url')) {
    function eottae_maps_directions_url($lat, $lng, $address = '')
    {
        if ($lat !== '' && $lng !== '') {
            return 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($lat.','.$lng);
        }
        if ($address !== '') {
            return 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($address);
        }

        return '#';
    }
}

if (!function_exists('eottae_should_load_assets')) {
    function eottae_should_load_assets()
    {
        if (defined('G5_IS_ADMIN') && G5_IS_ADMIN) {
            return false;
        }

        $script = isset($_SERVER['SCRIPT_NAME']) ? basename($_SERVER['SCRIPT_NAME']) : '';
        $member_scripts = array('login.php', 'register.php', 'register_form.php', 'register_result.php', 'password_lost.php', 'password_reset.php', 'member_confirm.php');
        if (in_array($script, $member_scripts, true)) {
            return true;
        }

        if (isset($_SERVER['SCRIPT_FILENAME']) && strpos($_SERVER['SCRIPT_FILENAME'], 'eottae-mypage.php') !== false) {
            return true;
        }

        $bo = isset($_GET['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', $_GET['bo_table']) : '';
        if (in_array($bo, array(EOTTae_SHOP_TABLE, EOTTae_COMMUNITY_TABLE, EOTTae_REVIEW_TABLE), true)) {
            return true;
        }

        if (isset($_SERVER['SCRIPT_FILENAME'])) {
            $page_scripts = array(
                'eottae-mypage.php', 'eottae-points.php', 'eottae-coupons.php', 'eottae-my-reviews.php',
                'eottae-saved-shops.php', 'eottae-inquiries.php', 'eottae-events.php',
            );
            foreach ($page_scripts as $ps) {
                if (strpos($_SERVER['SCRIPT_FILENAME'], $ps) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (!function_exists('eottae_load_component')) {
    function eottae_load_component($name)
    {
        $path = G5_PATH.'/components/eottae/'.$name.'.php';
        if (is_file($path)) {
            include_once $path;
        }
    }
}

if (!function_exists('eottae_render_inquiry_buttons')) {
    /**
     * @param string $context card|detail|mobile-bar|reservation|business
     * @param array  $opts phone, inquiry_code, lat, lng, address, share_url
     */
    function eottae_render_inquiry_buttons($context, $opts = array())
    {
        eottae_load_component('inquiry-button');

        if (function_exists('eottae_inquiry_buttons_html')) {
            echo eottae_inquiry_buttons_html($context, $opts);
        }
    }
}

if (!function_exists('eottae_render_shop_card')) {
    function eottae_render_shop_card($list_row, $bo_table = '')
    {
        eottae_load_component('shop-card');

        if (function_exists('eottae_shop_card_html')) {
            echo eottae_shop_card_html($list_row, $bo_table);
        }
    }
}

if (!function_exists('eottae_mypage_url')) {
    function eottae_mypage_url()
    {
        return G5_URL.'/page/eottae-mypage.php';
    }
}

if (!function_exists('eottae_login_url')) {
    function eottae_login_url($return = '')
    {
        $url = G5_BBS_URL.'/login.php';
        if ($return !== '') {
            $url .= '?url='.urlencode($return);
        }

        return $url;
    }
}

if (!function_exists('eottae_review_table')) {
    function eottae_review_table()
    {
        return defined('EOTTae_REVIEW_TABLE') ? EOTTae_REVIEW_TABLE : 'review';
    }
}

if (!function_exists('eottae_review_from_write')) {
    function eottae_review_from_write($wr)
    {
        if (!is_array($wr)) {
            return array();
        }

        $rating = isset($wr['wr_2']) ? (float) $wr['wr_2'] : 0;
        if ($rating < 1 || $rating > 5) {
            $rating = 0;
        }

        return array(
            'wr_id'       => isset($wr['wr_id']) ? (int) $wr['wr_id'] : 0,
            'shop_id'     => isset($wr['wr_1']) ? (int) $wr['wr_1'] : 0,
            'rating'      => $rating,
            'shop_name'   => isset($wr['wr_3']) ? get_text($wr['wr_3']) : '',
            'status'      => isset($wr['wr_4']) ? get_text($wr['wr_4']) : 'visible',
            'photo_count' => isset($wr['wr_5']) ? (int) $wr['wr_5'] : 0,
            'subject'     => isset($wr['wr_subject']) ? get_text($wr['wr_subject']) : '',
            'content'     => isset($wr['wr_content']) ? $wr['wr_content'] : '',
            'author'      => isset($wr['wr_name']) ? get_text($wr['wr_name']) : '',
            'mb_id'       => isset($wr['mb_id']) ? $wr['mb_id'] : '',
            'datetime'    => isset($wr['wr_datetime']) ? $wr['wr_datetime'] : '',
            'datetime_ts' => isset($wr['wr_datetime']) ? strtotime($wr['wr_datetime']) : 0,
        );
    }
}

if (!function_exists('eottae_review_write_table')) {
    function eottae_review_write_table()
    {
        global $g5;

        return $g5['write_prefix'].eottae_review_table();
    }
}

if (!function_exists('eottae_get_shop_review_summary')) {
    function eottae_get_shop_review_summary($shop_wr_id)
    {
        global $g5;

        $shop_wr_id = (int) $shop_wr_id;
        if ($shop_wr_id < 1) {
            return array('count' => 0, 'average' => 0, 'distribution' => array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0));
        }

        $write_table = eottae_review_write_table();
        $sql = " select wr_2 from {$write_table}
            where wr_is_comment = 0
              and wr_1 = '{$shop_wr_id}'
              and (wr_4 = '' or wr_4 = 'visible')
            order by wr_id desc ";
        $result = sql_query($sql);

        $distribution = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0);
        $total = 0;
        $count = 0;

        while ($row = sql_fetch_array($result)) {
            $star = (int) round((float) $row['wr_2']);
            if ($star < 1) {
                $star = 1;
            }
            if ($star > 5) {
                $star = 5;
            }
            $distribution[$star]++;
            $total += (float) $row['wr_2'];
            $count++;
        }

        return array(
            'count'          => $count,
            'average'        => $count > 0 ? round($total / $count, 1) : 0,
            'distribution'   => $distribution,
        );
    }
}

if (!function_exists('eottae_get_shop_reviews')) {
    function eottae_get_shop_reviews($shop_wr_id, $limit = 10, $offset = 0)
    {
        global $g5;

        $shop_wr_id = (int) $shop_wr_id;
        $limit = max(1, min(50, (int) $limit));
        $offset = max(0, (int) $offset);
        if ($shop_wr_id < 1) {
            return array();
        }

        $write_table = eottae_review_write_table();
        $sql = " select * from {$write_table}
            where wr_is_comment = 0
              and wr_1 = '{$shop_wr_id}'
              and (wr_4 = '' or wr_4 = 'visible')
            order by wr_id desc
            limit {$offset}, {$limit} ";
        $result = sql_query($sql);
        $rows = array();

        while ($row = sql_fetch_array($result)) {
            $review = eottae_review_from_write($row);
            $review['replies'] = eottae_get_review_replies($review['wr_id']);
            $review['photos'] = eottae_get_review_photos(eottae_review_table(), $review['wr_id']);
            $rows[] = $review;
        }

        return $rows;
    }
}

if (!function_exists('eottae_get_review_replies')) {
    function eottae_get_review_replies($review_wr_id)
    {
        global $g5;

        $review_wr_id = (int) $review_wr_id;
        if ($review_wr_id < 1) {
            return array();
        }

        $write_table = eottae_review_write_table();
        $sql = " select * from {$write_table}
            where wr_is_comment = 1
              and wr_parent = '{$review_wr_id}'
            order by wr_comment asc, wr_id asc ";
        $result = sql_query($sql);
        $rows = array();

        while ($row = sql_fetch_array($result)) {
            $member_row = function_exists('get_member') ? get_member($row['mb_id']) : array('mb_id' => $row['mb_id']);
            $rows[] = array(
                'wr_id'    => (int) $row['wr_id'],
                'content'  => get_text(strip_tags($row['wr_content'])),
                'author'   => get_text($row['wr_name']),
                'mb_id'    => isset($row['mb_id']) ? $row['mb_id'] : '',
                'datetime' => $row['wr_datetime'],
                'is_biz'   => eottae_is_business_member($member_row),
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_get_review_photos')) {
    function eottae_get_review_photos($bo_table, $wr_id)
    {
        global $g5;

        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return array();
        }

        $bo_table = preg_replace('/[^a-z0-9_]/', '', $bo_table);
        $sql = " select bf_file, bf_source from {$g5['board_file_table']}
            where bo_table = '{$bo_table}' and wr_id = '{$wr_id}'
            order by bf_no asc ";
        $result = sql_query($sql);
        $photos = array();

        while ($row = sql_fetch_array($result)) {
            if (empty($row['bf_file'])) {
                continue;
            }
            $photos[] = G5_DATA_URL.'/file/'.$bo_table.'/'.$row['bf_file'];
        }

        return $photos;
    }
}

if (!function_exists('eottae_user_reviewed_shop')) {
    function eottae_user_reviewed_shop($mb_id, $shop_wr_id)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        $shop_wr_id = (int) $shop_wr_id;
        if ($mb_id === '' || $shop_wr_id < 1) {
            return false;
        }

        $write_table = eottae_review_write_table();
        $row = sql_fetch(" select wr_id from {$write_table}
            where wr_is_comment = 0 and mb_id = '{$mb_id}' and wr_1 = '{$shop_wr_id}' limit 1 ");

        return !empty($row['wr_id']);
    }
}

if (!function_exists('eottae_get_member_reviews')) {
    function eottae_get_member_reviews($mb_id, $limit = 20)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        $limit = max(1, min(50, (int) $limit));
        if ($mb_id === '') {
            return array();
        }

        $write_table = eottae_review_write_table();
        $sql = " select * from {$write_table}
            where wr_is_comment = 0 and mb_id = '{$mb_id}'
            order by wr_id desc
            limit {$limit} ";
        $result = sql_query($sql);
        $rows = array();

        while ($row = sql_fetch_array($result)) {
            $rows[] = eottae_review_from_write($row);
        }

        return $rows;
    }
}

if (!function_exists('eottae_review_token')) {
    function eottae_review_token($regenerate = false)
    {
        $key = 'eottae_review_token';
        $token = get_session($key);
        if ($regenerate || $token === '' || $token === null) {
            $token = md5(uniqid((string) mt_rand(), true));
            set_session($key, $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_render_review_section')) {
    function eottae_render_review_section($shop_wr_id, $shop_name = '')
    {
        eottae_load_component('review-section');

        if (function_exists('eottae_review_section_html')) {
            echo eottae_review_section_html($shop_wr_id, $shop_name);
        }
    }
}

if (!function_exists('eottae_business_pending_replies_count')) {
    function eottae_business_pending_replies_count($mb_id)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        if ($mb_id === '') {
            return 0;
        }

        $shop_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
        $review_table = eottae_review_write_table();

        $shops = sql_query(" select wr_id from {$shop_table} where mb_id = '{$mb_id}' ");
        $shop_ids = array();
        while ($row = sql_fetch_array($shops)) {
            $shop_ids[] = (int) $row['wr_id'];
        }

        if (empty($shop_ids)) {
            return 0;
        }

        $ids = implode(',', $shop_ids);
        $row = sql_fetch(" select count(*) as cnt from {$review_table} r
            where r.wr_is_comment = 0
              and r.wr_1 in ({$ids})
              and (r.wr_4 = '' or r.wr_4 = 'visible')
              and not exists (
                select 1 from {$review_table} c
                where c.wr_is_comment = 1 and c.wr_parent = r.wr_id and c.mb_id = '{$mb_id}'
              ) ");

        return isset($row['cnt']) ? (int) $row['cnt'] : 0;
    }
}

if (!function_exists('eottae_shop_save_token')) {
    function eottae_shop_save_token($regenerate = false)
    {
        $key = 'eottae_shop_save_token';
        $token = get_session($key);
        if ($regenerate || $token === '' || $token === null) {
            $token = md5(uniqid((string) mt_rand(), true));
            set_session($key, $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_is_shop_saved')) {
    function eottae_is_shop_saved($mb_id, $shop_wr_id)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        $shop_wr_id = (int) $shop_wr_id;
        if ($mb_id === '' || $shop_wr_id < 1) {
            return false;
        }

        $bo_table = EOTTae_SHOP_TABLE;
        $row = sql_fetch(" select ms_id from {$g5['scrap_table']}
            where mb_id = '{$mb_id}' and bo_table = '{$bo_table}' and wr_id = '{$shop_wr_id}' limit 1 ");

        return !empty($row['ms_id']);
    }
}

if (!function_exists('eottae_get_saved_shop_ids')) {
    function eottae_get_saved_shop_ids($mb_id, $limit = 30)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        $limit = max(1, min(50, (int) $limit));
        if ($mb_id === '') {
            return array();
        }

        $bo_table = EOTTae_SHOP_TABLE;
        $result = sql_query(" select wr_id from {$g5['scrap_table']}
            where mb_id = '{$mb_id}' and bo_table = '{$bo_table}'
            order by ms_id desc
            limit {$limit} ");
        $ids = array();
        while ($row = sql_fetch_array($result)) {
            $ids[] = (int) $row['wr_id'];
        }

        return $ids;
    }
}

if (!function_exists('eottae_track_recent_shop')) {
    function eottae_track_recent_shop($shop_wr_id)
    {
        $shop_wr_id = (int) $shop_wr_id;
        if ($shop_wr_id < 1) {
            return;
        }

        $list = get_session('eottae_recent_shops');
        if (!is_array($list)) {
            $list = array();
        }

        $filtered = array();
        foreach ($list as $id) {
            $id = (int) $id;
            if ($id > 0 && $id !== $shop_wr_id) {
                $filtered[] = $id;
            }
        }
        array_unshift($filtered, $shop_wr_id);
        set_session('eottae_recent_shops', array_slice($filtered, 0, 20));
    }
}

if (!function_exists('eottae_get_recent_shop_ids')) {
    function eottae_get_recent_shop_ids($limit = 20)
    {
        $list = get_session('eottae_recent_shops');
        if (!is_array($list)) {
            return array();
        }

        $limit = max(1, min(20, (int) $limit));
        $ids = array();
        foreach ($list as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[] = $id;
            }
            if (count($ids) >= $limit) {
                break;
            }
        }

        return $ids;
    }
}

if (!function_exists('eottae_get_shop_rows_by_ids')) {
    function eottae_get_shop_rows_by_ids($ids)
    {
        global $g5;

        if (!is_array($ids) || empty($ids)) {
            return array();
        }

        $clean = array();
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $clean[$id] = $id;
            }
        }
        if (empty($clean)) {
            return array();
        }

        $write_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
        $id_list = implode(',', $clean);
        $result = sql_query(" select * from {$write_table}
            where wr_id in ({$id_list}) and wr_is_comment = 0 ");
        $map = array();
        while ($row = sql_fetch_array($result)) {
            $map[(int) $row['wr_id']] = $row;
        }

        $rows = array();
        foreach ($clean as $id) {
            if (isset($map[$id])) {
                $rows[] = $map[$id];
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_get_member_inquiries')) {
    function eottae_get_member_inquiries($mb_id, $limit = 20)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        $limit = max(1, min(50, (int) $limit));
        if ($mb_id === '') {
            return array();
        }

        $bo_table = defined('EOTTae_INQUIRY_TABLE') ? EOTTae_INQUIRY_TABLE : 'inquiry';
        $write_table = $g5['write_prefix'].$bo_table;
        $exists = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = '{$bo_table}' ");
        if (empty($exists['cnt'])) {
            return array();
        }

        $result = sql_query(" select wr_id, wr_subject, wr_content, wr_datetime, wr_6
            from {$write_table}
            where wr_is_comment = 0 and mb_id = '{$mb_id}'
            order by wr_id desc
            limit {$limit} ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = array(
                'wr_id'    => (int) $row['wr_id'],
                'subject'  => get_text($row['wr_subject']),
                'content'  => get_text(strip_tags($row['wr_content'])),
                'status'   => get_text($row['wr_6'] ?: '신규'),
                'datetime' => $row['wr_datetime'],
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_event_table')) {
    function eottae_event_table()
    {
        return defined('EOTTae_EVENT_TABLE') ? EOTTae_EVENT_TABLE : 'event';
    }
}

if (!function_exists('eottae_get_events')) {
    function eottae_get_events($limit = 12, $category = '')
    {
        global $g5;

        $bo_table = eottae_event_table();
        $exists = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' ");
        if (empty($exists['cnt'])) {
            return array();
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $limit = max(1, min(30, (int) $limit));
        $where = " wr_is_comment = 0 ";
        if ($category !== '') {
            $category = sql_escape_string($category);
            $where .= " and ca_name = '{$category}' ";
        }

        $result = sql_query(" select wr_id, wr_subject, wr_content, ca_name, wr_datetime
            from {$write_table}
            where {$where}
            order by wr_id desc
            limit {$limit} ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = array(
                'wr_id'    => (int) $row['wr_id'],
                'subject'  => get_text($row['wr_subject']),
                'content'  => cut_str(strip_tags($row['wr_content']), 120),
                'category' => get_text($row['ca_name']),
                'datetime' => $row['wr_datetime'],
                'href'     => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$row['wr_id'],
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_grant_review_points')) {
    /**
     * 리뷰 작성 포인트 지급 (기본 + 사진 보너스)
     *
     * @return array{total:int, base:int, photo:int}
     */
    function eottae_grant_review_points($mb_id, $review_wr_id, $shop_name, $has_photo = false)
    {
        global $config;

        $review_wr_id = (int) $review_wr_id;
        $base = defined('EOTTae_REVIEW_POINT_BASE') ? (int) EOTTae_REVIEW_POINT_BASE : 30;
        $photo = defined('EOTTae_REVIEW_POINT_PHOTO') ? (int) EOTTae_REVIEW_POINT_PHOTO : 20;
        $bo_table = eottae_review_table();
        $shop_label = $shop_name !== '' ? get_text($shop_name) : '업체';

        $granted = array('total' => 0, 'base' => 0, 'photo' => 0);
        if ($review_wr_id < 1 || $mb_id === '' || empty($config['cf_use_point'])) {
            return $granted;
        }

        if ($base > 0) {
            $result = insert_point(
                $mb_id,
                $base,
                $shop_label.' 리뷰 작성',
                $bo_table,
                (string) $review_wr_id,
                'write'
            );
            if ($result === 1) {
                $granted['base'] = $base;
                $granted['total'] += $base;
            }
        }

        if ($has_photo && $photo > 0) {
            $result = insert_point(
                $mb_id,
                $photo,
                $shop_label.' 리뷰 사진 보너스',
                $bo_table,
                (string) $review_wr_id,
                'photo'
            );
            if ($result === 1) {
                $granted['photo'] = $photo;
                $granted['total'] += $photo;
            }
        }

        return $granted;
    }
}

if (!function_exists('eottae_revoke_review_points')) {
    function eottae_revoke_review_points($mb_id, $review_wr_id)
    {
        $review_wr_id = (int) $review_wr_id;
        if ($review_wr_id < 1 || $mb_id === '') {
            return false;
        }

        $bo_table = eottae_review_table();
        delete_point($mb_id, $bo_table, (string) $review_wr_id, 'write');
        delete_point($mb_id, $bo_table, (string) $review_wr_id, 'photo');

        return true;
    }
}

if (!function_exists('eottae_hide_review')) {
    function eottae_hide_review($review_wr_id)
    {
        global $g5;

        $review_wr_id = (int) $review_wr_id;
        if ($review_wr_id < 1) {
            return array('ok' => false, 'message' => '리뷰를 찾을 수 없습니다.');
        }

        $write_table = eottae_review_write_table();
        $row = sql_fetch(" select wr_id, mb_id, wr_4 from {$write_table}
            where wr_id = '{$review_wr_id}' and wr_is_comment = 0 limit 1 ");
        if (empty($row['wr_id'])) {
            return array('ok' => false, 'message' => '리뷰를 찾을 수 없습니다.');
        }
        if ($row['wr_4'] === 'hidden') {
            return array('ok' => true, 'message' => '이미 숨김 처리된 리뷰입니다.');
        }

        sql_query(" update {$write_table} set wr_4 = 'hidden' where wr_id = '{$review_wr_id}' ");
        if (!empty($row['mb_id'])) {
            eottae_revoke_review_points($row['mb_id'], $review_wr_id);
        }

        return array('ok' => true, 'message' => '리뷰가 숨김 처리되었습니다.');
    }
}

if (!function_exists('eottae_render_shop_save_button')) {
    function eottae_render_shop_save_button($shop_wr_id, $is_saved = false)
    {
        global $is_member;

        if (!$is_member) {
            return;
        }

        $shop_wr_id = (int) $shop_wr_id;
        $token = eottae_shop_save_token(true);
        $saved = $is_saved ? '1' : '0';
        $label = $is_saved ? '찜 해제' : '찜하기';

        echo '<button type="button" class="shop-save-btn'.($is_saved ? ' is-saved' : '').'"';
        echo ' data-shop-save data-shop-id="'.$shop_wr_id.'" data-saved="'.$saved.'"';
        echo ' data-save-token="'.htmlspecialchars($token, ENT_QUOTES, 'UTF-8').'">';
        echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        echo '</button>';
    }
}
