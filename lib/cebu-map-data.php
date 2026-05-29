<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-location.lib.php';
include_once G5_LIB_PATH.'/eottae-market.lib.php';
include_once G5_LIB_PATH.'/eottae-job.lib.php';
include_once G5_LIB_PATH.'/eottae-estate.lib.php';

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
            'url'           => cebu_map_post_url($bo_table, $wr_id),
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

if (!function_exists('cebu_map_markers')) {
    function cebu_map_markers($limit_per_board = 200)
    {
        return array_values(array_merge(
            cebu_map_market_markers($limit_per_board),
            cebu_map_job_markers($limit_per_board),
            cebu_map_estate_markers($limit_per_board)
        ));
    }
}
