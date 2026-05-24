<?php
/**
 * 세부어때 공개 JSON API helpers
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_api_json')) {
    function eottae_api_json($data, $code = 200)
    {
        if (!headers_sent()) {
            http_response_code((int) $code);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store, max-age=0');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('eottae_api_error')) {
    function eottae_api_error($message, $code = 400)
    {
        eottae_api_json(array('success' => false, 'message' => $message), $code);
    }
}

if (!function_exists('eottae_api_shop_thumb')) {
    function eottae_api_shop_thumb($shop_wr_id)
    {
        global $g5;

        $shop_wr_id = (int) $shop_wr_id;
        if ($shop_wr_id < 1) {
            return '';
        }

        $bo_table = EOTTae_SHOP_TABLE;
        $row = sql_fetch(" select bf_file from {$g5['board_file_table']}
            where bo_table = '{$bo_table}' and wr_id = '{$shop_wr_id}'
            order by bf_no asc limit 1 ");
        if (empty($row['bf_file'])) {
            return '';
        }

        return G5_DATA_URL.'/file/'.$bo_table.'/'.$row['bf_file'];
    }
}

if (!function_exists('eottae_api_format_shop_row')) {
    function eottae_api_format_shop_row($row)
    {
        if (!is_array($row)) {
            return null;
        }

        $shop = eottae_shop_from_write($row);
        $wr_id = (int) $shop['wr_id'];
        $summary = eottae_get_shop_review_summary($wr_id);

        return array(
            'wr_id'       => $wr_id,
            'name'        => $shop['name'],
            'category'    => $shop['category'],
            'region'      => $shop['region'],
            'status'      => $shop['status'],
            'address'     => $shop['address'],
            'rating'      => $summary['average'],
            'review_count'=> $summary['count'],
            'thumb'       => eottae_api_shop_thumb($wr_id),
            'url'         => G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE.'&wr_id='.$wr_id,
        );
    }
}

if (!function_exists('eottae_api_get_featured_shops')) {
    function eottae_api_get_featured_shops($limit = 6)
    {
        global $g5;

        $limit = max(1, min(20, (int) $limit));
        $write_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
        $result = sql_query(" select * from {$write_table}
            where wr_is_comment = 0
            order by wr_id desc
            limit {$limit} ");
        $items = array();
        while ($row = sql_fetch_array($result)) {
            $formatted = eottae_api_format_shop_row($row);
            if ($formatted) {
                $items[] = $formatted;
            }
        }

        return $items;
    }
}

if (!function_exists('eottae_api_get_shop_summary')) {
    function eottae_api_get_shop_summary($shop_wr_id)
    {
        global $g5;

        $shop_wr_id = (int) $shop_wr_id;
        if ($shop_wr_id < 1) {
            return null;
        }

        $write_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
        $row = sql_fetch(" select * from {$write_table} where wr_id = '{$shop_wr_id}' and wr_is_comment = 0 limit 1 ");
        if (empty($row['wr_id'])) {
            return null;
        }

        $shop = eottae_api_format_shop_row($row);
        $shop['reviews'] = eottae_get_shop_reviews($shop_wr_id, 5);
        $shop['review_summary'] = eottae_get_shop_review_summary($shop_wr_id);

        return $shop;
    }
}

if (!function_exists('eottae_api_get_events')) {
    function eottae_api_get_events($limit = 10)
    {
        $events = eottae_get_events($limit);
        $items = array();
        foreach ($events as $event) {
            $items[] = array(
                'wr_id'    => $event['wr_id'],
                'subject'  => $event['subject'],
                'content'  => $event['content'],
                'category' => $event['category'],
                'datetime' => $event['datetime'],
                'url'      => $event['href'],
            );
        }

        return $items;
    }
}

if (!function_exists('eottae_api_community_table')) {
    function eottae_api_community_table()
    {
        return defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community';
    }
}

if (!function_exists('eottae_api_community_board_ready')) {
    function eottae_api_community_board_ready()
    {
        global $g5;

        $bo_table = eottae_api_community_table();
        $row = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' ");

        return !empty($row['cnt']);
    }
}

if (!function_exists('eottae_api_relative_time_label')) {
    function eottae_api_relative_time_label($datetime)
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

        return date('Y-m-d', $ts);
    }
}

if (!function_exists('eottae_api_format_community_row')) {
    function eottae_api_format_community_row($row, $with_thumb = false)
    {
        if (!is_array($row) || empty($row['wr_id'])) {
            return null;
        }

        $bo_table = eottae_api_community_table();
        $wr_id = (int) $row['wr_id'];
        $datetime = isset($row['wr_datetime']) ? $row['wr_datetime'] : '';
        $ts = strtotime($datetime);
        $is_new = $ts ? (G5_SERVER_TIME - $ts) < 86400 : false;
        $hit = isset($row['wr_hit']) ? (int) $row['wr_hit'] : 0;
        $comment = isset($row['wr_comment']) ? (int) $row['wr_comment'] : 0;

        $item = array(
            'wr_id'     => $wr_id,
            'board'     => isset($row['ca_name']) ? get_text($row['ca_name']) : '',
            'title'     => isset($row['wr_subject']) ? get_text($row['wr_subject']) : '',
            'comments'  => $comment,
            'views'     => $hit,
            'datetime'  => $datetime,
            'time'      => eottae_api_relative_time_label($datetime),
            'is_new'    => $is_new,
            'is_hot'    => ($hit >= 100 || $comment >= 10),
            'url'       => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id,
            'thumb'     => '',
        );

        if ($with_thumb) {
            if (!function_exists('get_list_thumbnail')) {
                include_once G5_LIB_PATH.'/thumbnail.lib.php';
            }
            $thumb = get_list_thumbnail($bo_table, $wr_id, 400, 400, false, true);
            if (!empty($thumb['src'])) {
                $item['thumb'] = $thumb['src'];
            }
        }

        return $item;
    }
}

if (!function_exists('eottae_api_get_community_posts')) {
    function eottae_api_get_community_posts($limit = 4, $category = '', $order = 'latest', $with_thumb = false)
    {
        global $g5;

        if (!eottae_api_community_board_ready()) {
            return array();
        }

        $limit = max(1, min(20, (int) $limit));
        $bo_table = eottae_api_community_table();
        $write_table = $g5['write_prefix'].$bo_table;
        $where = " wr_is_comment = 0 ";
        if ($category !== '') {
            $category = sql_escape_string($category);
            $where .= " and ca_name = '{$category}' ";
        }

        switch ($order) {
            case 'hit':
                $order_sql = ' wr_hit desc, wr_id desc ';
                break;
            case 'comment':
                $order_sql = ' wr_comment desc, wr_id desc ';
                break;
            default:
                $order_sql = ' wr_id desc ';
                break;
        }

        $result = sql_query(" select wr_id, ca_name, wr_subject, wr_comment, wr_hit, wr_datetime, wr_file
            from {$write_table}
            where {$where}
            order by {$order_sql}
            limit {$limit} ");
        $items = array();
        while ($row = sql_fetch_array($result)) {
            $formatted = eottae_api_format_community_row($row, $with_thumb);
            if ($formatted) {
                $items[] = $formatted;
            }
        }

        return $items;
    }
}

if (!function_exists('eottae_api_get_community_gallery')) {
    function eottae_api_get_community_gallery($limit = 6)
    {
        global $g5;

        if (!eottae_api_community_board_ready()) {
            return array();
        }

        $limit = max(1, min(20, (int) $limit));
        $bo_table = eottae_api_community_table();
        $write_table = $g5['write_prefix'].$bo_table;

        $result = sql_query(" select wr_id, ca_name, wr_subject, wr_comment, wr_hit, wr_datetime, wr_file
            from {$write_table}
            where wr_is_comment = 0 and wr_file > 0
            order by wr_id desc
            limit {$limit} ");
        $items = array();
        while ($row = sql_fetch_array($result)) {
            $formatted = eottae_api_format_community_row($row, true);
            if ($formatted && $formatted['thumb'] !== '') {
                $items[] = $formatted;
            }
        }

        if (count($items) >= $limit) {
            return $items;
        }

        $need = $limit - count($items);
        $exclude = array();
        foreach ($items as $item) {
            $exclude[] = (int) $item['wr_id'];
        }
        $exclude_sql = '';
        if (!empty($exclude)) {
            $exclude_sql = ' and wr_id not in ('.implode(',', $exclude).') ';
        }

        $result = sql_query(" select wr_id, ca_name, wr_subject, wr_comment, wr_hit, wr_datetime, wr_file
            from {$write_table}
            where wr_is_comment = 0 {$exclude_sql}
            order by wr_id desc
            limit {$need} ");
        while ($row = sql_fetch_array($result)) {
            $formatted = eottae_api_format_community_row($row, true);
            if ($formatted) {
                $items[] = $formatted;
            }
        }

        return $items;
    }
}

if (!function_exists('eottae_api_get_community_home')) {
    function eottae_api_get_community_home()
    {
        $categories = array('자유', '질문', '정보', '구인구직', '후기');
        $by_category = array();
        foreach ($categories as $cat) {
            $by_category[$cat] = eottae_api_get_community_posts(4, $cat);
        }

        return array(
            'hit'         => eottae_api_get_community_posts(4, '', 'hit'),
            'comment'     => eottae_api_get_community_posts(4, '', 'comment'),
            'latest'      => eottae_api_get_community_posts(4, '', 'latest'),
            'by_category' => $by_category,
            'gallery'     => eottae_api_get_community_gallery(6),
        );
    }
}

if (!function_exists('eottae_api_youtube_table')) {
    function eottae_api_youtube_table()
    {
        return defined('EOTTae_YOUTUBE_TABLE') ? EOTTae_YOUTUBE_TABLE : 'youtube';
    }
}

if (!function_exists('eottae_api_youtube_board_ready')) {
    function eottae_api_youtube_board_ready()
    {
        global $g5;

        $bo_table = eottae_api_youtube_table();
        $row = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' ");

        return !empty($row['cnt']);
    }
}

if (!function_exists('eottae_api_format_youtube_row')) {
    function eottae_api_format_youtube_row($row)
    {
        if (!is_array($row) || empty($row['wr_id'])) {
            return null;
        }

        if (!function_exists('g5b_youtube_id_from_write')) {
            include_once G5_SKIN_PATH.'/board/_inc/g5b-youtube.php';
        }

        $bo_table = eottae_api_youtube_table();
        $wr_id = (int) $row['wr_id'];
        $datetime = isset($row['wr_datetime']) ? $row['wr_datetime'] : '';
        $video_id = g5b_youtube_id_from_write($row);
        $thumb = $video_id ? g5b_youtube_thumb_url($video_id) : '';
        $duration_sec = g5b_youtube_duration_seconds($row);
        $category = isset($row['ca_name']) && $row['ca_name'] !== '' ? get_text($row['ca_name']) : '정보';
        $channel = function_exists('g5b_youtube_channel_label') ? g5b_youtube_channel_label($row) : '';

        return array(
            'wr_id'    => $wr_id,
            'board'    => $category,
            'title'    => isset($row['wr_subject']) ? get_text($row['wr_subject']) : '',
            'comments' => isset($row['wr_comment']) ? (int) $row['wr_comment'] : 0,
            'views'    => isset($row['wr_hit']) ? (int) $row['wr_hit'] : 0,
            'datetime' => $datetime,
            'time'     => eottae_api_relative_time_label($datetime),
            'is_new'   => strtotime($datetime) ? (G5_SERVER_TIME - strtotime($datetime)) < 86400 : false,
            'is_hot'   => false,
            'url'      => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id,
            'thumb'    => $thumb,
            'channel'  => $channel,
            'video_id' => $video_id,
            'duration' => $duration_sec,
            'duration_label' => g5b_youtube_format_duration($duration_sec),
        );
    }
}

if (!function_exists('eottae_api_get_youtube_posts')) {
    function eottae_api_get_youtube_posts($limit = 9)
    {
        global $g5;

        if (!eottae_api_youtube_board_ready()) {
            return array();
        }

        $limit = max(1, min(20, (int) $limit));
        $bo_table = eottae_api_youtube_table();
        $write_table = $g5['write_prefix'].$bo_table;

        $result = sql_query(" select wr_id, ca_name, wr_subject, wr_comment, wr_hit, wr_datetime, wr_1, wr_2, wr_3, wr_name
            from {$write_table}
            where wr_is_comment = 0
            order by wr_id desc
            limit {$limit} ");
        $items = array();
        while ($row = sql_fetch_array($result)) {
            $formatted = eottae_api_format_youtube_row($row);
            if ($formatted) {
                $items[] = $formatted;
            }
        }

        return $items;
    }
}

if (!function_exists('eottae_api_gallery_table')) {
    function eottae_api_gallery_table()
    {
        return defined('EOTTae_GALLERY_TABLE') ? EOTTae_GALLERY_TABLE : 'gallery';
    }
}

if (!function_exists('eottae_api_gallery_board_ready')) {
    function eottae_api_gallery_board_ready()
    {
        global $g5;

        $bo_table = eottae_api_gallery_table();
        $row = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' ");

        return !empty($row['cnt']);
    }
}

if (!function_exists('eottae_api_format_gallery_row')) {
    function eottae_api_format_gallery_row($row)
    {
        if (!is_array($row) || empty($row['wr_id'])) {
            return null;
        }

        $bo_table = eottae_api_gallery_table();
        $wr_id = (int) $row['wr_id'];
        $datetime = isset($row['wr_datetime']) ? $row['wr_datetime'] : '';
        $ts = strtotime($datetime);
        $is_new = $ts ? (G5_SERVER_TIME - $ts) < 86400 : false;
        $hit = isset($row['wr_hit']) ? (int) $row['wr_hit'] : 0;

        $item = array(
            'wr_id'    => $wr_id,
            'board'    => isset($row['ca_name']) && $row['ca_name'] !== '' ? get_text($row['ca_name']) : '풍경',
            'title'    => isset($row['wr_subject']) ? get_text($row['wr_subject']) : '',
            'comments' => isset($row['wr_comment']) ? (int) $row['wr_comment'] : 0,
            'views'    => $hit,
            'datetime' => $datetime,
            'time'     => eottae_api_relative_time_label($datetime),
            'is_new'   => $is_new,
            'is_hot'   => false,
            'url'      => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id,
            'thumb'    => '',
        );

        if (!function_exists('get_list_thumbnail')) {
            include_once G5_LIB_PATH.'/thumbnail.lib.php';
        }
        $thumb = get_list_thumbnail($bo_table, $wr_id, 400, 400, false, true);
        if (!empty($thumb['src'])) {
            $item['thumb'] = $thumb['src'];
        }

        return $item;
    }
}

if (!function_exists('eottae_api_get_gallery_posts')) {
    function eottae_api_get_gallery_posts($limit = 27)
    {
        global $g5;

        if (!eottae_api_gallery_board_ready()) {
            return array();
        }

        $limit = max(1, min(30, (int) $limit));
        $bo_table = eottae_api_gallery_table();
        $write_table = $g5['write_prefix'].$bo_table;

        $result = sql_query(" select wr_id, ca_name, wr_subject, wr_comment, wr_hit, wr_datetime, wr_file
            from {$write_table}
            where wr_is_comment = 0 and wr_file > 0
            order by wr_id desc
            limit {$limit} ");
        $items = array();
        while ($row = sql_fetch_array($result)) {
            $formatted = eottae_api_format_gallery_row($row);
            if ($formatted && $formatted['thumb'] !== '') {
                $items[] = $formatted;
            }
        }

        return $items;
    }
}

if (!function_exists('eottae_api_get_home_bundle')) {
    function eottae_api_get_home_bundle()
    {
        return array(
            'featured_shops' => eottae_api_get_featured_shops(4),
            'events'         => eottae_api_get_events(4),
            'community'      => eottae_api_get_community_home(),
            'youtube'        => eottae_api_get_youtube_posts(9),
            'gallery_posts'  => eottae_api_get_gallery_posts(27),
            'urls'           => array(
                'shop'      => G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE,
                'community' => G5_BBS_URL.'/board.php?bo_table='.EOTTae_COMMUNITY_TABLE,
                'youtube'   => G5_BBS_URL.'/board.php?bo_table='.eottae_api_youtube_table(),
                'gallery'   => G5_BBS_URL.'/board.php?bo_table='.eottae_api_gallery_table(),
                'mypage'    => G5_URL.'/page/eottae-mypage.php',
            ),
        );
    }
}
