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

if (!function_exists('eottae_use_site_chrome')) {
    /**
     * 세부어때 공통 GNB·푸터·eottae.css 적용 여부 (관리자·설치 도구 제외)
     */
    function eottae_use_site_chrome()
    {
        if (defined('G5_IS_ADMIN') && G5_IS_ADMIN) {
            return false;
        }
        if (defined('EOTTAE_SETUP_MINIMAL') && EOTTAE_SETUP_MINIMAL) {
            return false;
        }

        return true;
    }
}

if (!function_exists('eottae_should_load_assets')) {
    function eottae_should_load_assets()
    {
        return eottae_use_site_chrome();
    }
}

if (!function_exists('eottae_prepare_site_header')) {
    /**
     * site-header.php include 전 공통 변수
     */
    function eottae_prepare_site_header()
    {
        global $g5_site_title, $config;

        if (!isset($g5_site_title) || $g5_site_title === '') {
            $g5_site_title = function_exists('g5site_cfg')
                ? g5site_cfg('site_name', get_text($config['cf_title']))
                : get_text($config['cf_title']);
            if ($g5_site_title === '') {
                $g5_site_title = get_text($config['cf_title']);
            }
        }
    }
}

if (!function_exists('eottae_render_site_header')) {
    function eottae_render_site_header()
    {
        if (!is_file(G5_PATH.'/components/eottae/site-header.php')) {
            return false;
        }

        eottae_prepare_site_header();
        include G5_PATH.'/components/eottae/site-header.php';

        return true;
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
    function eottae_render_shop_card($list_row, $bo_table = '', $layout = 'grid')
    {
        eottae_load_component('shop-card');

        if (function_exists('eottae_shop_card_html')) {
            echo eottae_shop_card_html($list_row, $bo_table, $layout);
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

if (!function_exists('eottae_review_reply_token')) {
    function eottae_review_reply_token($regenerate = false)
    {
        $key = 'eottae_review_reply_token';
        $token = get_session($key);
        if ($regenerate || $token === '' || $token === null) {
            $token = md5(uniqid((string) mt_rand(), true));
            set_session($key, $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_business_owns_shop')) {
    function eottae_business_owns_shop($mb_id, $shop_wr_id)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        $shop_wr_id = (int) $shop_wr_id;
        if ($mb_id === '' || $shop_wr_id < 1) {
            return false;
        }

        $shop_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
        $row = sql_fetch(" select wr_id from {$shop_table}
            where wr_id = '{$shop_wr_id}' and mb_id = '{$mb_id}' limit 1 ");

        return !empty($row['wr_id']);
    }
}

if (!function_exists('eottae_review_has_business_reply')) {
    function eottae_review_has_business_reply($review_wr_id, $mb_id = '')
    {
        global $g5;

        $review_wr_id = (int) $review_wr_id;
        if ($review_wr_id < 1) {
            return false;
        }

        $write_table = eottae_review_write_table();
        $mb_filter = '';
        if ($mb_id !== '') {
            $mb_filter = " and mb_id = '".sql_escape_string((string) $mb_id)."' ";
        }

        $result = sql_query(" select mb_id from {$write_table}
            where wr_is_comment = 1 and wr_parent = '{$review_wr_id}' {$mb_filter} ");
        while ($row = sql_fetch_array($result)) {
            $member_row = function_exists('get_member') ? get_member($row['mb_id']) : array('mb_id' => $row['mb_id']);
            if (eottae_is_business_member($member_row)) {
                return true;
            }
        }

        return false;
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

        $bo_table = EOTTae_SHOP_TABLE;
        $rows = array();
        foreach ($clean as $id) {
            if (isset($map[$id])) {
                $map[$id]['bo_table'] = $bo_table;
                $map[$id]['href'] = G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$id;
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

if (!function_exists('eottae_sync_shop_review_stats')) {
    /**
     * 업체 게시글 wr_comment(리뷰수)·wr_good(평점×10) 동기화 — 목록 정렬용
     */
    function eottae_sync_shop_review_stats($shop_wr_id)
    {
        global $g5;

        $shop_wr_id = (int) $shop_wr_id;
        if ($shop_wr_id < 1) {
            return;
        }

        $summary = eottae_get_shop_review_summary($shop_wr_id);
        $shop_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
        $count = (int) $summary['count'];
        $good = (int) round((float) $summary['average'] * 10);

        sql_query(" update {$shop_table} set wr_comment = '{$count}', wr_good = '{$good}' where wr_id = '{$shop_wr_id}' ");
    }
}

if (!function_exists('eottae_render_mypage_back')) {
    function eottae_render_mypage_back()
    {
        echo '<p class="mypage-subpage__back"><a href="'.eottae_mypage_url().'">← MY</a></p>';
    }
}

if (!function_exists('eottae_render_shop_save_button')) {
    function eottae_render_shop_save_button($shop_wr_id, $is_saved = false, $return_url = '')
    {
        global $is_member;

        $shop_wr_id = (int) $shop_wr_id;
        if ($return_url === '' && isset($_SERVER['REQUEST_URI'])) {
            $return_url = (string) $_SERVER['REQUEST_URI'];
        }

        if (!$is_member) {
            echo '<a href="'.eottae_login_url($return_url).'" class="shop-save-btn shop-save-btn--guest">찜하기</a>';
            return;
        }

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

if (!function_exists('eottae_community_board_table')) {
    function eottae_community_board_table($bo_table = '')
    {
        if ($bo_table === '') {
            return defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community';
        }

        return preg_replace('/[^a-z0-9_]/', '', $bo_table);
    }
}

if (!function_exists('eottae_community_relative_time')) {
    function eottae_community_relative_time($datetime)
    {
        $ts = strtotime((string) $datetime);
        if (!$ts) {
            return '';
        }

        $diff = G5_SERVER_TIME - $ts;
        if ($diff < 60) {
            return '방금 전';
        }
        if ($diff < 3600) {
            return floor($diff / 60).'분 전';
        }
        if ($diff < 86400) {
            return floor($diff / 3600).'시간 전';
        }
        if ($diff < 172800) {
            return '어제';
        }
        if ($diff < 604800) {
            return floor($diff / 86400).'일 전';
        }

        return date('y.m.d', $ts);
    }
}

if (!function_exists('eottae_community_snippet')) {
    function eottae_community_snippet($content, $len = 110)
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $content)));
        if ($text === '') {
            return '';
        }

        return cut_str($text, (int) $len, '…');
    }
}

if (!function_exists('eottae_community_today_count')) {
    function eottae_community_today_count($bo_table = '')
    {
        global $g5;

        $bo_table = eottae_community_board_table($bo_table);
        $write_table = $g5['write_prefix'].$bo_table;
        $today = G5_TIME_YMD.' 00:00:00';
        $row = sql_fetch(" select count(*) as cnt from {$write_table}
            where wr_is_comment = 0 and wr_datetime >= '{$today}' ");

        return isset($row['cnt']) ? (int) $row['cnt'] : 0;
    }
}

if (!function_exists('eottae_community_category_tabs')) {
    function eottae_community_category_tabs($board)
    {
        global $g5;

        if (empty($board['bo_table'])) {
            return array();
        }

        $bo_table = $board['bo_table'];
        $write_table = $g5['write_prefix'].$bo_table;
        $total = isset($board['bo_count_write']) ? (int) $board['bo_count_write'] : 0;
        $tabs = array(
            array('slug' => '', 'label' => '전체', 'count' => $total),
        );

        if (!empty($board['bo_use_category']) && !empty($board['bo_category_list'])) {
            $categories = explode('|', $board['bo_category_list']);
            foreach ($categories as $cat) {
                $cat = trim($cat);
                if ($cat === '') {
                    continue;
                }
                $esc = sql_escape_string($cat);
                $row = sql_fetch(" select count(*) as cnt from {$write_table}
                    where wr_is_comment = 0 and ca_name = '{$esc}' ");
                $tabs[] = array(
                    'slug'  => $cat,
                    'label' => $cat,
                    'count' => isset($row['cnt']) ? (int) $row['cnt'] : 0,
                );
            }
        }

        return $tabs;
    }
}

if (!function_exists('eottae_community_list_thumb')) {
    function eottae_community_list_thumb($bo_table, $wr_id)
    {
        if (!function_exists('get_list_thumbnail')) {
            include_once G5_LIB_PATH.'/thumbnail.lib.php';
        }

        $thumb = get_list_thumbnail($bo_table, (int) $wr_id, 160, 160, false, true);

        return !empty($thumb['src']) ? $thumb['src'] : '';
    }
}

if (!function_exists('eottae_community_weekly_popular')) {
    function eottae_community_weekly_popular($bo_table = '', $limit = 5)
    {
        global $g5;

        $bo_table = eottae_community_board_table($bo_table);
        $write_table = $g5['write_prefix'].$bo_table;
        $limit = max(1, min(10, (int) $limit));
        $since = date('Y-m-d H:i:s', G5_SERVER_TIME - (7 * 86400));

        $result = sql_query(" select wr_id, wr_subject, wr_hit, wr_comment, ca_name
            from {$write_table}
            where wr_is_comment = 0 and wr_datetime >= '{$since}'
            order by wr_hit desc, wr_id desc
            limit {$limit} ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = array(
                'wr_id'    => (int) $row['wr_id'],
                'subject'  => get_text($row['wr_subject']),
                'hit'      => (int) $row['wr_hit'],
                'comment'  => (int) $row['wr_comment'],
                'category' => get_text($row['ca_name']),
                'url'      => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$row['wr_id'],
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_community_badge_class')) {
    function eottae_community_badge_class($ca_name, $is_notice = false)
    {
        if ($is_notice) {
            return 'community-badge--notice';
        }

        $map = array(
            '질문'    => 'community-badge--question',
            '정보'    => 'community-badge--info',
            '후기'    => 'community-badge--review',
            '자유'    => 'community-badge--free',
            '구인구직'=> 'community-badge--job',
            '공지'    => 'community-badge--notice',
        );

        return isset($map[$ca_name]) ? $map[$ca_name] : 'community-badge--default';
    }
}

if (!function_exists('eottae_community_is_new')) {
    function eottae_community_is_new($datetime, $hours = 24)
    {
        $ts = strtotime((string) $datetime);

        return $ts ? (G5_SERVER_TIME - $ts) < ((int) $hours * 3600) : false;
    }
}

if (!function_exists('eottae_community_is_hot')) {
    function eottae_community_is_hot($hit, $comment, $board = null)
    {
        $hit = (int) $hit;
        $comment = (int) $comment;
        $hot = 100;
        if (is_array($board) && !empty($board['bo_hot'])) {
            $hot = (int) $board['bo_hot'];
        }

        return $hit >= $hot || $comment >= 10;
    }
}

if (!function_exists('eottae_community_sort_options')) {
    function eottae_community_sort_options($current_sst = '', $current_sod = 'desc')
    {
        $options = array(
            array('label' => '최신순', 'sst' => 'wr_datetime', 'sod' => 'desc'),
            array('label' => '조회순', 'sst' => 'wr_hit', 'sod' => 'desc'),
            array('label' => '댓글순', 'sst' => 'wr_comment', 'sod' => 'desc'),
        );
        foreach ($options as &$opt) {
            $opt['active'] = ($current_sst === $opt['sst'] || ($current_sst === '' && $opt['sst'] === 'wr_datetime'));
        }

        return $options;
    }
}

if (!function_exists('eottae_community_region_options')) {
    function eottae_community_region_options()
    {
        return array('세부시티', '막탄', 'IT Park', '아얄라', '만다우에', '라푸라푸');
    }
}

if (!function_exists('eottae_shop_region_options')) {
    function eottae_shop_region_options()
    {
        return eottae_community_region_options();
    }
}

if (!function_exists('eottae_shop_quick_categories')) {
    function eottae_shop_quick_categories($board)
    {
        $items = array(array('slug' => '', 'label' => '전체'));
        $defaults = array('맛집', '마사지', '렌트카', '투어', '카페', '병원', '학원', '숙소');
        $board_cats = array();

        if (!empty($board['bo_category_list'])) {
            $board_cats = array_filter(array_map('trim', explode('|', $board['bo_category_list'])));
        }

        $merged = array_unique(array_merge($board_cats, $defaults));
        foreach ($merged as $cat) {
            if ($cat === '') {
                continue;
            }
            $items[] = array('slug' => $cat, 'label' => $cat);
        }

        return $items;
    }
}

if (!function_exists('eottae_shop_sort_links')) {
    function eottae_shop_sort_links($current_sst = '')
    {
        $maps_enabled = false;
        if (is_file(G5_PATH.'/components/maps/map-config.php')) {
            include_once G5_PATH.'/components/maps/map-config.php';
            $maps_enabled = function_exists('onoff_map_has_api_key') && onoff_map_has_api_key();
        }

        return array(
            array('label' => '가까운순', 'sst' => 'near', 'sod' => 'asc', 'disabled' => !$maps_enabled),
            array('label' => '인기순', 'sst' => 'wr_hit', 'sod' => 'desc'),
            array('label' => '리뷰많은순', 'sst' => 'wr_comment', 'sod' => 'desc'),
            array('label' => '평점높은순', 'sst' => 'wr_good', 'sod' => 'desc'),
            array('label' => '최신등록순', 'sst' => 'wr_datetime', 'sod' => 'desc'),
        );
    }
}

if (!function_exists('eottae_shop_user_coords_from_request')) {
    function eottae_shop_user_coords_from_request()
    {
        $lat = isset($_GET['eottae_lat']) ? trim((string) $_GET['eottae_lat']) : '';
        $lng = isset($_GET['eottae_lng']) ? trim((string) $_GET['eottae_lng']) : '';
        if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
            return null;
        }

        return array(
            'lat' => (float) $lat,
            'lng' => (float) $lng,
        );
    }
}

if (!function_exists('eottae_haversine_km')) {
    function eottae_haversine_km($lat1, $lng1, $lat2, $lng2)
    {
        $r = 6371;
        $d_lat = deg2rad($lat2 - $lat1);
        $d_lng = deg2rad($lng2 - $lng1);
        $a = sin($d_lat / 2) * sin($d_lat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($d_lng / 2) * sin($d_lng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $r * $c;
    }
}

if (!function_exists('eottae_shop_sort_list_by_distance')) {
    function eottae_shop_sort_list_by_distance(&$list, $user_lat, $user_lng)
    {
        if (!is_array($list)) {
            return;
        }

        foreach ($list as $idx => $row) {
            if (!is_array($row)) {
                continue;
            }
            $shop = eottae_shop_from_write($row);
            if ($shop['lat'] !== '' && $shop['lng'] !== '' && is_numeric($shop['lat']) && is_numeric($shop['lng'])) {
                $list[$idx]['_eottae_distance_km'] = eottae_haversine_km(
                    (float) $user_lat,
                    (float) $user_lng,
                    (float) $shop['lat'],
                    (float) $shop['lng']
                );
            } else {
                $list[$idx]['_eottae_distance_km'] = 99999;
            }
        }

        usort($list, function ($a, $b) {
            $da = isset($a['_eottae_distance_km']) ? (float) $a['_eottae_distance_km'] : 99999;
            $db = isset($b['_eottae_distance_km']) ? (float) $b['_eottae_distance_km'] : 99999;
            if ($da === $db) {
                return 0;
            }

            return ($da < $db) ? -1 : 1;
        });
    }
}

if (!function_exists('eottae_shop_format_distance_km')) {
    function eottae_shop_format_distance_km($km)
    {
        $km = (float) $km;
        if ($km >= 99999) {
            return '';
        }
        if ($km < 1) {
            return (int) round($km * 1000).'m';
        }

        return number_format($km, 1).'km';
    }
}

if (!function_exists('eottae_shop_append_coords_query')) {
    function eottae_shop_append_coords_query($params = array())
    {
        $coords = eottae_shop_user_coords_from_request();
        if ($coords) {
            $params['eottae_lat'] = $coords['lat'];
            $params['eottae_lng'] = $coords['lng'];
        }

        return $params;
    }
}

if (!function_exists('eottae_shop_is_sort_active')) {
    function eottae_shop_is_sort_active($link, $current_sst, $current_sod)
    {
        if (!empty($link['disabled'])) {
            return false;
        }
        if ($current_sst === '') {
            return $link['sst'] === 'wr_datetime';
        }

        if ($link['sst'] === 'near') {
            return $current_sst === 'near';
        }

        return $link['sst'] === $current_sst && ($current_sod === '' || $link['sod'] === $current_sod);
    }
}

if (!function_exists('eottae_shop_list_snippet')) {
    function eottae_shop_list_snippet($content, $len = 90)
    {
        return eottae_community_snippet($content, $len);
    }
}

if (!function_exists('eottae_shop_map_markers')) {
    function eottae_shop_map_markers($list, $bo_table = '')
    {
        $markers = array();
        if (!is_array($list)) {
            return $markers;
        }

        foreach ($list as $row) {
            if (!is_array($row)) {
                continue;
            }
            $shop = eottae_shop_from_write($row);
            if ($shop['lat'] === '' || $shop['lng'] === '') {
                continue;
            }
            $markers[] = array(
                'wr_id'    => (int) $shop['wr_id'],
                'name'     => $shop['name'],
                'category' => $shop['category'],
                'lat'      => $shop['lat'],
                'lng'      => $shop['lng'],
                'url'      => isset($row['href']) ? $row['href'] : G5_BBS_URL.'/board.php?bo_table='.($bo_table !== '' ? $bo_table : (defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop')).'&wr_id='.$shop['wr_id'],
            );
        }

        return $markers;
    }
}

if (!function_exists('eottae_shop_map_locations_json')) {
    /**
     * Google Maps JS용 업체 좌표 JSON
     *
     * @param array<int, array<string, mixed>> $markers
     * @return string
     */
    function eottae_shop_map_locations_json($markers)
    {
        $locations = array();
        foreach ((array) $markers as $marker) {
            if (!is_array($marker)) {
                continue;
            }
            $lat = isset($marker['lat']) ? trim((string) $marker['lat']) : '';
            $lng = isset($marker['lng']) ? trim((string) $marker['lng']) : '';
            if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
                continue;
            }
            $locations[] = array(
                'id'       => isset($marker['wr_id']) ? (int) $marker['wr_id'] : 0,
                'name'     => isset($marker['name']) ? (string) $marker['name'] : '',
                'category' => isset($marker['category']) ? (string) $marker['category'] : '',
                'lat'      => (float) $lat,
                'lng'      => (float) $lng,
                'link'     => isset($marker['url']) ? (string) $marker['url'] : '',
            );
        }

        $json = json_encode($locations, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return '[]';
        }

        return $json;
    }
}

if (!function_exists('eottae_enqueue_google_maps')) {
    /**
     * Google Maps API + eottae-shop-map.js 로드
     *
     * @return bool API 키가 있어 스크립트를 등록했으면 true
     */
    function eottae_enqueue_google_maps()
    {
        static $enqueued = false;

        if ($enqueued) {
            return function_exists('onoff_map_has_api_key') && onoff_map_has_api_key();
        }

        if (!is_file(G5_PATH.'/components/maps/map-config.php')) {
            return false;
        }

        include_once G5_PATH.'/components/maps/map-config.php';

        if (!function_exists('onoff_map_has_api_key') || !onoff_map_has_api_key()) {
            return false;
        }

        $cfg = onoff_map_get_config();
        $key = isset($cfg['api_key']) ? htmlspecialchars($cfg['api_key'], ENT_QUOTES, 'UTF-8') : '';
        if ($key === '') {
            return false;
        }

        add_javascript('<script src="'.G5_JS_URL.'/eottae-shop-map.js"></script>', 25);
        add_javascript(
            '<script src="https://maps.googleapis.com/maps/api/js?key='.$key.'&amp;callback=initEottaeShopMaps" defer></script>',
            5
        );

        $enqueued = true;

        return true;
    }
}

if (!function_exists('eottae_shop_table')) {
    function eottae_shop_table()
    {
        return defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop';
    }
}

if (!function_exists('eottae_shop_list_url')) {
    function eottae_shop_list_url($params = array())
    {
        $base = G5_BBS_URL.'/board.php?bo_table='.eottae_shop_table();
        if (empty($params)) {
            return $base;
        }

        return $base.'&'.http_build_query($params);
    }
}

if (!function_exists('eottae_shop_category_url')) {
    function eottae_shop_category_url($category, $board = null)
    {
        $category = trim((string) $category);
        if ($category === '') {
            return eottae_shop_list_url();
        }

        $board_cats = array();
        if (is_array($board) && !empty($board['bo_category_list'])) {
            $board_cats = array_filter(array_map('trim', explode('|', $board['bo_category_list'])));
        }

        if (!empty($board_cats) && in_array($category, $board_cats, true)) {
            return eottae_shop_list_url(array('sca' => $category));
        }

        return eottae_shop_list_url(array('sfl' => 'wr_1', 'stx' => $category));
    }
}

if (!function_exists('eottae_shop_gallery_images')) {
    function eottae_shop_gallery_images($view)
    {
        $images = array();
        if (!is_array($view) || empty($view['file']['count'])) {
            return $images;
        }

        for ($i = 0; $i < (int) $view['file']['count']; $i++) {
            if (empty($view['file'][$i]['view'])) {
                continue;
            }
            $images[] = array(
                'src'   => $view['file'][$i]['path'].'/'.$view['file'][$i]['file'],
                'alt'   => isset($view['file'][$i]['source']) ? get_text($view['file'][$i]['source']) : '',
            );
        }

        return $images;
    }
}

if (!function_exists('eottae_shop_detail_flags')) {
    function eottae_shop_detail_flags($shop, $summary = null)
    {
        if (!is_array($summary)) {
            $summary = eottae_get_shop_review_summary(isset($shop['wr_id']) ? (int) $shop['wr_id'] : 0);
        }

        return array(
            'recommended' => $summary['average'] >= 4.5 && $summary['count'] > 0,
            'ad'          => !empty($shop['sns']) && stripos((string) $shop['sns'], 'ad') !== false,
        );
    }
}

if (!function_exists('eottae_community_list_url')) {
    function eottae_community_list_url($params = array())
    {
        $base = G5_BBS_URL.'/board.php?bo_table='.eottae_community_board_table();
        if (empty($params)) {
            return $base;
        }

        return $base.'&'.http_build_query($params);
    }
}

if (!function_exists('eottae_shop_board_tables')) {
    function eottae_shop_board_tables()
    {
        return array(
            eottae_shop_table(),
            defined('EOTTae_FOOD_TABLE') ? EOTTae_FOOD_TABLE : 'food',
            defined('EOTTae_MASSAGE_TABLE') ? EOTTae_MASSAGE_TABLE : 'massage',
            defined('EOTTae_RENTCAR_TABLE') ? EOTTae_RENTCAR_TABLE : 'rentcar',
            defined('EOTTae_TOUR_TABLE') ? EOTTae_TOUR_TABLE : 'tour',
        );
    }
}

if (!function_exists('eottae_is_shop_board')) {
    function eottae_is_shop_board($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return $bo_table !== '' && in_array($bo_table, eottae_shop_board_tables(), true);
    }
}

if (!function_exists('eottae_board_list_url')) {
    function eottae_board_list_url($bo_table, $params = array())
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return G5_BBS_URL.'/board.php';
        }

        $base = G5_BBS_URL.'/board.php?bo_table='.$bo_table;
        if (empty($params)) {
            return $base;
        }

        return $base.'&'.http_build_query($params);
    }
}

if (!function_exists('eottae_shop_write_url')) {
    function eottae_shop_write_url($bo_table = '')
    {
        if ($bo_table === '') {
            $bo_table = eottae_shop_table();
        }
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            $bo_table = eottae_shop_table();
        }

        return G5_BBS_URL.'/write.php?bo_table='.$bo_table;
    }
}

if (!function_exists('eottae_gnb_board_tables')) {
    function eottae_gnb_board_tables()
    {
        $tables = eottae_shop_board_tables();
        $tables[] = eottae_community_board_table();
        $tables[] = defined('EOTTae_PEOPLE_TABLE') ? EOTTae_PEOPLE_TABLE : 'people';
        $tables[] = defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job';
        $tables[] = defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate';
        $tables[] = defined('EOTTae_GALLERY_TABLE') ? EOTTae_GALLERY_TABLE : 'gallery';
        $tables[] = defined('EOTTae_YOUTUBE_TABLE') ? EOTTae_YOUTUBE_TABLE : 'youtube';

        return array_values(array_unique($tables));
    }
}

if (!function_exists('eottae_gnb_nav_links')) {
    function eottae_gnb_nav_links()
    {
        return array(
            array('key' => 'home', 'label' => '홈', 'href' => G5_URL.'/'),
            array('key' => 'shop', 'label' => '내주변', 'href' => eottae_board_list_url(eottae_shop_table())),
            array('key' => 'food', 'label' => '맛집', 'href' => eottae_board_list_url(defined('EOTTae_FOOD_TABLE') ? EOTTae_FOOD_TABLE : 'food')),
            array('key' => 'massage', 'label' => '마사지', 'href' => eottae_board_list_url(defined('EOTTae_MASSAGE_TABLE') ? EOTTae_MASSAGE_TABLE : 'massage')),
            array('key' => 'rentcar', 'label' => '렌트카', 'href' => eottae_board_list_url(defined('EOTTae_RENTCAR_TABLE') ? EOTTae_RENTCAR_TABLE : 'rentcar')),
            array('key' => 'tour', 'label' => '투어', 'href' => eottae_board_list_url(defined('EOTTae_TOUR_TABLE') ? EOTTae_TOUR_TABLE : 'tour')),
            array('key' => 'community', 'label' => '커뮤니티', 'href' => eottae_community_list_url()),
            array('key' => 'people', 'label' => '사람찾기', 'href' => eottae_board_list_url(defined('EOTTae_PEOPLE_TABLE') ? EOTTae_PEOPLE_TABLE : 'people')),
            array('key' => 'job', 'label' => '구인구직', 'href' => eottae_board_list_url(defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job')),
            array('key' => 'estate', 'label' => '부동산', 'href' => eottae_board_list_url(defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate')),
            array('key' => 'gallery', 'label' => '갤러리', 'href' => eottae_board_list_url(defined('EOTTae_GALLERY_TABLE') ? EOTTae_GALLERY_TABLE : 'gallery')),
            array('key' => 'youtube', 'label' => '유튜브', 'href' => eottae_board_list_url(defined('EOTTae_YOUTUBE_TABLE') ? EOTTae_YOUTUBE_TABLE : 'youtube')),
        );
    }
}

if (!function_exists('eottae_gnb_link_is_active')) {
    function eottae_gnb_link_is_active($key)
    {
        $bo = isset($_GET['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', $_GET['bo_table']) : '';
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

        $board_map = array(
            'shop'      => eottae_shop_table(),
            'food'      => defined('EOTTae_FOOD_TABLE') ? EOTTae_FOOD_TABLE : 'food',
            'massage'   => defined('EOTTae_MASSAGE_TABLE') ? EOTTae_MASSAGE_TABLE : 'massage',
            'rentcar'   => defined('EOTTae_RENTCAR_TABLE') ? EOTTae_RENTCAR_TABLE : 'rentcar',
            'tour'      => defined('EOTTae_TOUR_TABLE') ? EOTTae_TOUR_TABLE : 'tour',
            'community' => eottae_community_board_table(),
            'people'    => defined('EOTTae_PEOPLE_TABLE') ? EOTTae_PEOPLE_TABLE : 'people',
            'job'       => defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job',
            'estate'    => defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate',
            'gallery'   => defined('EOTTae_GALLERY_TABLE') ? EOTTae_GALLERY_TABLE : 'gallery',
            'youtube'   => defined('EOTTae_YOUTUBE_TABLE') ? EOTTae_YOUTUBE_TABLE : 'youtube',
        );

        switch ($key) {
            case 'home':
                return defined('_INDEX_');
            case 'mypage':
                return strpos($uri, '/page/eottae-') !== false;
            default:
                return isset($board_map[$key]) && $bo === $board_map[$key];
        }
    }
}
