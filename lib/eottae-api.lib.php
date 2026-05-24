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

if (!function_exists('eottae_api_get_home_bundle')) {
    function eottae_api_get_home_bundle()
    {
        return array(
            'featured_shops' => eottae_api_get_featured_shops(4),
            'events'         => eottae_api_get_events(4),
            'urls'           => array(
                'shop'      => G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE,
                'community' => G5_BBS_URL.'/board.php?bo_table='.EOTTae_COMMUNITY_TABLE,
                'mypage'    => G5_URL.'/page/eottae-mypage.php',
            ),
        );
    }
}
