<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-location.lib.php';
include_once G5_LIB_PATH.'/eottae-market.lib.php';
include_once G5_LIB_PATH.'/eottae-job.lib.php';
include_once G5_LIB_PATH.'/eottae-estate.lib.php';
include_once G5_LIB_PATH.'/eottae.lib.php';

if (!function_exists('cebu_map_board_exists')) {
    function cebu_map_board_exists($bo_table)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return false;
        }

        $row = sql_fetch(" SELECT bo_table FROM `{$g5['board_table']}` WHERE bo_table = '".sql_escape_string($bo_table)."' LIMIT 1 ");

        return !empty($row['bo_table']);
    }
}

if (!function_exists('cebu_map_post_url')) {
    function cebu_map_post_url($bo_table, $wr_id)
    {
        return function_exists('get_pretty_url')
            ? get_pretty_url($bo_table, (int) $wr_id)
            : G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.(int) $wr_id;
    }
}

if (!function_exists('cebu_map_absolute_url')) {
    function cebu_map_absolute_url($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }
        if ($url[0] === '/') {
            return G5_URL.$url;
        }

        return G5_URL.'/'.$url;
    }
}

if (!function_exists('cebu_map_marker_thumb_url')) {
    function cebu_map_marker_thumb_url($type, $bo_table, $row)
    {
        $wr_id = (int) ($row['wr_id'] ?? 0);
        if ($wr_id < 1) {
            return '';
        }

        $thumb = '';
        if ($type === 'market' && function_exists('eottae_market_thumb_url')) {
            $thumb = eottae_market_thumb_url($bo_table, $wr_id, 216, 216);
        } elseif (function_exists('eottae_community_list_thumb')) {
            $content = isset($row['wr_content']) ? (string) $row['wr_content'] : '';
            $thumb = eottae_community_list_thumb($bo_table, $wr_id, $content);
        }

        if ($thumb === '' && in_array($type, array('job', 'estate'), true)) {
            $mb_id = trim((string) ($row['mb_id'] ?? ''));
            if ($mb_id !== '' && function_exists('eottae_estate_member_thumb_url')) {
                $thumb = eottae_estate_member_thumb_url($mb_id);
            }
        }

        if ($thumb !== '' && function_exists('eottae_map_public_url')) {
            $thumb = eottae_map_public_url($thumb);
        }

        return $thumb;
    }
}

if (!function_exists('cebu_map_can_read_board')) {
    function cebu_map_can_read_board($board)
    {
        global $member, $is_admin;

        if (!is_array($board) || empty($board['bo_table'])) {
            return false;
        }
        if ($is_admin) {
            return true;
        }

        $level = isset($member['mb_level']) ? (int) $member['mb_level'] : 1;

        return $level >= (int) ($board['bo_read_level'] ?? 1);
    }
}

if (!function_exists('cebu_map_area_key_from_label')) {
    function cebu_map_area_key_from_label($area)
    {
        $key = eottae_location_normalize_area($area);

        return $key !== '' ? $key : 'other';
    }
}

if (!function_exists('cebu_map_marker_base')) {
    function cebu_map_marker_base($type, $label, $bo_table, $row, $loc)
    {
        $wr_id = (int) ($row['wr_id'] ?? 0);
        $lat = trim((string) ($loc['latitude'] ?? ($loc['lat'] ?? '')));
        $lng = trim((string) ($loc['longitude'] ?? ($loc['lng'] ?? '')));
        if ($wr_id < 1 || $lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
            return null;
        }

        $area_label = trim((string) ($loc['area_label'] ?? ''));
        $area_key = cebu_map_area_key_from_label($loc['auto_area'] ?? $area_label);
        if ($area_label === '') {
            $area_label = eottae_location_area_label($area_key);
        }
        $location_text = trim(strip_tags((string) ($loc['location_text'] ?? ($loc['address'] ?? ''))));
        $post_url = cebu_map_post_url($bo_table, $wr_id);
        $directions_url = function_exists('eottae_maps_directions_url')
            ? eottae_maps_directions_url($lat, $lng, $location_text)
            : 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($lat.','.$lng);

        return array(
            'type'          => $type,
            'label'         => $label,
            'bo_table'      => $bo_table,
            'wr_id'         => $wr_id,
            'title'         => get_text($row['wr_subject'] ?? ''),
            'price'         => '',
            'status'        => '',
            'status_key'    => '',
            'area'          => $area_label,
            'area_key'      => $area_key,
            'location_text' => $location_text,
            'location'      => function_exists('format_location_display')
                ? format_location_display(array('auto_area' => $area_key, 'location_text' => $location_text))
                : trim($area_label.' · '.$location_text, ' ·'),
            'lat'           => (float) $lat,
            'lng'           => (float) $lng,
            'url'           => $post_url,
            'share_url'     => cebu_map_absolute_url($post_url),
            'directions_url'=> $directions_url,
            'thumbnail'     => cebu_map_marker_thumb_url($type, $bo_table, $row),
            'datetime'      => (string) ($row['wr_datetime'] ?? ''),
            'timestamp'     => isset($row['wr_datetime']) ? (int) strtotime($row['wr_datetime']) : 0,
            'price_num'     => 0,
        );
    }
}

if (!function_exists('cebu_map_fetch_board_rows')) {
    function cebu_map_fetch_board_rows($bo_table, $where, $limit = 200)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if (!cebu_map_board_exists($bo_table)) {
            return array();
        }

        $board = sql_fetch(" SELECT * FROM `{$g5['board_table']}` WHERE bo_table = '".sql_escape_string($bo_table)."' LIMIT 1 ");
        if (!cebu_map_can_read_board($board)) {
            return array();
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $limit = max(1, min(500, (int) $limit));
        $where = trim((string) $where);
        if ($where !== '') {
            $where = ' AND '.$where;
        }

        $rows = array();
        $result = sql_query("
            SELECT *
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_id = wr_parent
              AND wr_5 <> ''
              AND wr_6 <> ''
              AND wr_option NOT LIKE '%secret%'
              {$where}
            ORDER BY wr_datetime DESC
            LIMIT {$limit}
        ");
        while ($row = sql_fetch_array($result)) {
            $rows[] = $row;
        }

        return $rows;
    }
}

if (!function_exists('cebu_map_market_markers')) {
    function cebu_map_market_markers($limit = 200)
    {
        $bo_table = function_exists('eottae_market_board_table') ? eottae_market_board_table() : 'market';
        $markers = array();
        foreach (cebu_map_fetch_board_rows($bo_table, "wr_9 <> '0'", $limit) as $row) {
            $status_key = eottae_market_normalize_status($row['wr_2'] ?? '');
            $loc = array(
                'auto_area'     => $row['wr_3'] ?? '',
                'area_label'    => eottae_market_region_label($row['wr_3'] ?? ''),
                'location_text' => $row['wr_4'] ?? '',
                'latitude'      => $row['wr_5'] ?? '',
                'longitude'     => $row['wr_6'] ?? '',
            );
            $marker = cebu_map_marker_base('market', '중고장터', $bo_table, $row, $loc);
            if (!$marker) {
                continue;
            }
            $marker['price'] = eottae_market_format_price($row['wr_1'] ?? 0, $row['wr_10'] ?? '');
            $marker['price_num'] = (int) preg_replace('/[^0-9]/', '', (string) ($row['wr_1'] ?? ''));
            $marker['status_key'] = $status_key;
            $marker['status'] = eottae_market_status_label($status_key);
            $marker['is_dimmed'] = ($status_key === 'sold');
            $markers[] = $marker;
        }

        return $markers;
    }
}

if (!function_exists('cebu_map_job_markers')) {
    function cebu_map_job_markers($limit = 200)
    {
        $bo_table = function_exists('eottae_job_board_table') ? eottae_job_board_table() : 'job';
        $markers = array();
        foreach (cebu_map_fetch_board_rows($bo_table, "wr_7 <> '0'", $limit) as $row) {
            $loc = eottae_job_location_from_row($row);
            $marker = cebu_map_marker_base('job', '구인구직', $bo_table, $row, $loc);
            if (!$marker) {
                continue;
            }
            $tpl = eottae_job_template_from_row($row);
            $status_key = eottae_job_recruit_status_from_row($row);
            $marker['price'] = is_array($tpl) ? trim((string) ($tpl['salary'] ?? '')) : '';
            $marker['price_num'] = (int) preg_replace('/[^0-9]/', '', $marker['price']);
            $marker['status_key'] = $status_key;
            $marker['status'] = eottae_job_recruit_status_meta($status_key)['label'];
            $markers[] = $marker;
        }

        return $markers;
    }
}

if (!function_exists('cebu_map_estate_markers')) {
    function cebu_map_estate_markers($limit = 200)
    {
        $bo_table = function_exists('eottae_estate_board_table') ? eottae_estate_board_table() : 'estate';
        $markers = array();
        foreach (cebu_map_fetch_board_rows($bo_table, "wr_7 <> '0'", $limit) as $row) {
            $loc = eottae_estate_location_from_row($row);
            $marker = cebu_map_marker_base('estate', '부동산', $bo_table, $row, $loc);
            if (!$marker) {
                continue;
            }
            $tpl = eottae_estate_template_from_row($row);
            $status_key = eottae_estate_deal_status_from_row($row);
            $marker['price'] = is_array($tpl) ? trim((string) ($tpl['price'] ?? '')) : '';
            $marker['price_num'] = (int) preg_replace('/[^0-9]/', '', $marker['price']);
            $marker['status_key'] = $status_key;
            $marker['status'] = eottae_estate_deal_status_meta($status_key)['label'];
            $markers[] = $marker;
        }

        return $markers;
    }
}

if (!function_exists('cebu_map_shop_markers')) {
    function cebu_map_shop_markers($limit = 200)
    {
        $bo_table = function_exists('eottae_shop_table') ? eottae_shop_table() : 'shop';
        if (!function_exists('eottae_shop_fetch_raw_rows') || !function_exists('eottae_shop_from_write')) {
            return array();
        }

        $chunk = eottae_shop_fetch_raw_rows($bo_table, array(
            'limit'    => max(1, min(500, (int) $limit)),
            'max_rows' => max(1, min(500, (int) $limit)),
        ));
        $rows = isset($chunk['rows']) && is_array($chunk['rows']) ? $chunk['rows'] : array();
        $markers = array();

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $shop = eottae_shop_from_write($row, $bo_table);
            $lat = trim((string) ($shop['lat'] ?? ''));
            $lng = trim((string) ($shop['lng'] ?? ''));
            $address = trim((string) ($shop['address'] ?? ''));
            $region = trim((string) ($shop['region'] ?? ''));
            if (($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) && function_exists('eottae_shop_guess_coords')) {
                $fallback = eottae_shop_guess_coords($address, $region);
                if (!empty($fallback['lat']) && !empty($fallback['lng'])) {
                    $lat = (string) $fallback['lat'];
                    $lng = (string) $fallback['lng'];
                }
            }
            if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
                continue;
            }

            $loc = array(
                'auto_area'     => $region,
                'area_label'    => $region,
                'location_text' => $address,
                'latitude'      => $lat,
                'longitude'     => $lng,
            );
            $marker = cebu_map_marker_base('shop', '업체', $bo_table, $row, $loc);
            if (!$marker) {
                continue;
            }

            $summary = function_exists('eottae_get_shop_review_summary')
                ? eottae_get_shop_review_summary((int) ($shop['wr_id'] ?? 0))
                : array('average' => 0, 'count' => 0);
            $rating = isset($summary['average']) ? (float) $summary['average'] : 0;
            $review_count = isset($summary['count']) ? (int) $summary['count'] : 0;

            $marker['title'] = trim((string) ($shop['name'] ?? ''));
            $marker['status'] = trim((string) ($shop['category'] ?? ''));
            $marker['status_key'] = 'shop';
            $marker['price'] = $review_count > 0
                ? '★ '.number_format($rating, 1).' · 리뷰 '.number_format($review_count)
                : trim((string) ($shop['hours'] ?? ''));
            $marker['thumbnail'] = function_exists('eottae_shop_listing_thumb_url')
                ? eottae_shop_listing_thumb_url($bo_table, (int) ($shop['wr_id'] ?? 0), $row)
                : $marker['thumbnail'];
            if ($marker['thumbnail'] !== '' && function_exists('eottae_map_public_url')) {
                $marker['thumbnail'] = eottae_map_public_url($marker['thumbnail']);
            }
            $marker['url'] = function_exists('eottae_shop_view_url')
                ? eottae_shop_view_url((int) ($shop['wr_id'] ?? 0), $bo_table)
                : $marker['url'];
            $marker['share_url'] = cebu_map_absolute_url($marker['url']);

            $markers[] = $marker;
        }

        return $markers;
    }
}

if (!function_exists('cebu_map_markers')) {
    function cebu_map_markers($limit_per_board = 200)
    {
        return array_values(array_merge(
            cebu_map_shop_markers($limit_per_board),
            cebu_map_market_markers($limit_per_board),
            cebu_map_job_markers($limit_per_board),
            cebu_map_estate_markers($limit_per_board)
        ));
    }
}
