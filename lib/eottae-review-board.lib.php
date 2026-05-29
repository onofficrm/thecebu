<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_review_board_table')) {
    function eottae_review_board_table()
    {
        return function_exists('eottae_review_table') ? eottae_review_table() : 'review';
    }
}

if (!function_exists('eottae_is_review_board')) {
    function eottae_is_review_board($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return $bo_table !== '' && $bo_table === eottae_review_board_table();
    }
}

if (!function_exists('eottae_review_board_load_assets')) {
    function eottae_review_board_load_assets()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        $css_path = G5_PATH.'/css/eottae-review-board.css';
        $js_path = G5_PATH.'/js/eottae-review-shop-picker.js';
        $css_ver = is_file($css_path) ? (int) filemtime($css_path) : 0;
        $js_ver = is_file($js_path) ? (int) filemtime($js_path) : 0;

        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-review-board.css?ver='.$css_ver.'">', 25);
        add_javascript('<script src="'.G5_JS_URL.'/eottae-review-shop-picker.js?ver='.$js_ver.'" defer></script>', 25);
    }
}

if (!function_exists('eottae_review_board_shop_labels')) {
    function eottae_review_board_shop_labels()
    {
        return array(
            'shop'    => '업체',
            'food'    => '맛집',
            'massage' => '마사지',
            'rentcar' => '렌트카',
            'tour'    => '투어',
        );
    }
}

if (!function_exists('eottae_review_board_normalize_shop_bo')) {
    function eottae_review_board_normalize_shop_bo($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '' || !function_exists('eottae_is_shop_board') || !eottae_is_shop_board($bo_table)) {
            return function_exists('eottae_shop_table') ? eottae_shop_table() : 'shop';
        }

        return $bo_table;
    }
}

if (!function_exists('eottae_review_board_shop_row_to_picker')) {
    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>|null
     */
    function eottae_review_board_shop_row_to_picker(array $row, $bo_table)
    {
        global $g5;

        $bo_table = eottae_review_board_normalize_shop_bo($bo_table);
        $wr_id = (int) ($row['wr_id'] ?? 0);
        if ($wr_id < 1) {
            return null;
        }

        $labels = eottae_review_board_shop_labels();
        $name = trim(get_text($row['wr_subject'] ?? ''));
        if ($name === '') {
            return null;
        }

        $region = trim(get_text($row['wr_2'] ?? ''));
        $address = trim(get_text($row['wr_3'] ?? ''));
        $category = trim(get_text($row['ca_name'] ?? ($row['sca'] ?? '')));
        $board_label = $labels[$bo_table] ?? $bo_table;

        $shop = function_exists('eottae_shop_from_write')
            ? eottae_shop_from_write($row, $bo_table)
            : array();

        if ($region === '' && !empty($shop['region'])) {
            $region = (string) $shop['region'];
        }
        if ($address === '' && !empty($shop['address'])) {
            $address = (string) $shop['address'];
        }

        $thumb_url = '';
        if (function_exists('eottae_adroom_shop_thumb_url')) {
            include_once G5_LIB_PATH.'/eottae-adroom.lib.php';
            $thumb_url = eottae_adroom_shop_thumb_url($row, $bo_table);
        } elseif (function_exists('eottae_shop_listing_thumb_url')) {
            $thumb_url = eottae_shop_listing_thumb_url($bo_table, $wr_id, $row);
        }

        $search_blob = $name.' '.$board_label.' '.$region.' '.$address.' '.$category;
        $search_text = function_exists('mb_strtolower')
            ? mb_strtolower($search_blob, 'UTF-8')
            : strtolower($search_blob);

        return array(
            'bo_table'    => $bo_table,
            'wr_id'       => $wr_id,
            'name'        => $name,
            'region'      => $region,
            'address'     => $address,
            'category'    => $category,
            'board_label' => $board_label,
            'view_url'    => function_exists('eottae_shop_view_url')
                ? eottae_shop_view_url($wr_id, $bo_table)
                : G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id,
            'thumb_url'   => $thumb_url,
            'search_text' => $search_text,
        );
    }
}

if (!function_exists('eottae_review_board_resolve_shop_bo_for_row')) {
    function eottae_review_board_resolve_shop_bo_for_row(array $row, $fallback = '')
    {
        $fallback = eottae_review_board_normalize_shop_bo($fallback);
        if ($fallback === '') {
            $fallback = function_exists('eottae_shop_table') ? eottae_shop_table() : 'shop';
        }

        if (!function_exists('eottae_shop_segment_master_map')) {
            return $fallback;
        }

        $master = trim(get_text($row['wr_1'] ?? ''));
        if ($master === '') {
            return $fallback;
        }

        foreach (eottae_shop_segment_master_map() as $bo_table => $label) {
            if ($master === $label) {
                return preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
            }
        }

        return $fallback;
    }
}

if (!function_exists('eottae_review_board_search_shops')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_review_board_search_shops($keyword = '', $limit = 30)
    {
        $keyword = trim((string) $keyword);
        if ($keyword === '') {
            return array();
        }

        if (!function_exists('eottae_shop_fetch_raw_rows') || !function_exists('eottae_shop_table')) {
            return array();
        }

        $limit = max(5, min(50, (int) $limit));
        $base_bo = eottae_shop_table();
        $result = eottae_shop_fetch_raw_rows($base_bo, array(
            'stx' => $keyword,
            'sfl' => 'wr_subject',
            'limit' => $limit,
        ));

        $shops = array();
        foreach ($result['rows'] as $row) {
            if (!is_array($row)) {
                continue;
            }

            $display_bo = eottae_review_board_resolve_shop_bo_for_row($row, $base_bo);
            $item = eottae_review_board_shop_row_to_picker($row, $display_bo);
            if (!$item) {
                continue;
            }

            $shops[] = $item;
        }

        if (function_exists('mb_stripos')) {
            $needle = mb_strtolower($keyword, 'UTF-8');
            usort($shops, function ($a, $b) use ($needle) {
                $a_name = mb_strtolower((string) ($a['name'] ?? ''), 'UTF-8');
                $b_name = mb_strtolower((string) ($b['name'] ?? ''), 'UTF-8');
                $a_hit = mb_stripos($a_name, $needle, 0, 'UTF-8') === 0 ? 0 : (mb_stripos($a_name, $needle, 0, 'UTF-8') !== false ? 1 : 2);
                $b_hit = mb_stripos($b_name, $needle, 0, 'UTF-8') === 0 ? 0 : (mb_stripos($b_name, $needle, 0, 'UTF-8') !== false ? 1 : 2);
                if ($a_hit !== $b_hit) {
                    return $a_hit <=> $b_hit;
                }

                return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
            });
        }

        return array_slice($shops, 0, $limit);
    }
}

if (!function_exists('eottae_review_board_fetch_shop')) {
    /**
     * @return array<string, mixed>|null
     */
    function eottae_review_board_fetch_shop($shop_wr_id, $shop_bo_table = '')
    {
        global $g5;

        $shop_wr_id = (int) $shop_wr_id;
        if ($shop_wr_id < 1) {
            return null;
        }

        $shop_bo_table = eottae_review_board_normalize_shop_bo($shop_bo_table);
        $write_table = function_exists('eottae_shop_list_write_table')
            ? eottae_shop_list_write_table($shop_bo_table)
            : $g5['write_prefix'].$shop_bo_table;
        if ($write_table === '') {
            return null;
        }

        $exists = sql_fetch(" show tables like '".sql_escape_string($write_table)."' ");
        if (empty($exists)) {
            return null;
        }

        $where = " wr_id = '{$shop_wr_id}' and wr_is_comment = 0 ";
        if (function_exists('eottae_shop_segment_master_category')) {
            $master = eottae_shop_segment_master_category($shop_bo_table);
            if ($master !== '') {
                $where .= " and wr_1 = '".sql_escape_string($master)."' ";
            }
        }

        $row = sql_fetch(" select wr_id, wr_subject, wr_2, wr_3, ca_name, wr_1 from `{$write_table}`
            where {$where} limit 1 ");

        if (!is_array($row) || empty($row['wr_id'])) {
            return null;
        }

        $display_bo = eottae_review_board_resolve_shop_bo_for_row($row, $shop_bo_table);

        return eottae_review_board_shop_row_to_picker($row, $display_bo);
    }
}

if (!function_exists('eottae_review_board_shop_from_write')) {
    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>|null
     */
    function eottae_review_board_shop_from_write($row)
    {
        if (!is_array($row)) {
            return null;
        }

        $shop_wr_id = (int) ($row['wr_1'] ?? 0);
        if ($shop_wr_id < 1) {
            return null;
        }

        $shop_bo = eottae_review_board_normalize_shop_bo($row['wr_6'] ?? '');

        return eottae_review_board_fetch_shop($shop_wr_id, $shop_bo);
    }
}

if (!function_exists('eottae_review_board_user_reviewed_shop')) {
    function eottae_review_board_user_reviewed_shop($mb_id, $shop_wr_id, $shop_bo_table = '', $exclude_wr_id = 0)
    {
        global $g5;

        $mb_id = sql_escape_string((string) $mb_id);
        $shop_wr_id = (int) $shop_wr_id;
        $exclude_wr_id = (int) $exclude_wr_id;
        if ($mb_id === '' || $shop_wr_id < 1) {
            return false;
        }

        $shop_bo_table = eottae_review_board_normalize_shop_bo($shop_bo_table);
        $write_table = $g5['write_prefix'].eottae_review_board_table();
        $shop_bo_sql = sql_escape_string($shop_bo_table);

        $exclude_sql = $exclude_wr_id > 0 ? " and wr_id <> '{$exclude_wr_id}' " : '';

        $row = sql_fetch(" select wr_id from {$write_table}
            where wr_is_comment = 0
              and mb_id = '{$mb_id}'
              and wr_1 = '{$shop_wr_id}'
              and wr_6 = '{$shop_bo_sql}'
              and (wr_4 = '' or wr_4 = 'visible')
              {$exclude_sql}
            limit 1 ");

        return !empty($row['wr_id']);
    }
}

if (!function_exists('eottae_review_board_normalize_write_post')) {
    function eottae_review_board_normalize_write_post(array $post, $is_update = false)
    {
        $shop_wr_id = isset($post['eottae_review_shop_wr_id'])
            ? (int) $post['eottae_review_shop_wr_id']
            : (int) ($post['wr_1'] ?? 0);
        $shop_bo = isset($post['eottae_review_shop_bo_table'])
            ? eottae_review_board_normalize_shop_bo($post['eottae_review_shop_bo_table'])
            : eottae_review_board_normalize_shop_bo($post['wr_6'] ?? '');
        $shop_name = isset($post['eottae_review_shop_name'])
            ? trim(strip_tags((string) $post['eottae_review_shop_name']))
            : trim(strip_tags((string) ($post['wr_3'] ?? '')));
        $rating = isset($post['wr_2']) ? (int) $post['wr_2'] : 0;

        if ($shop_wr_id < 1) {
            $shop_wr_id = 0;
            $shop_bo = '';
            $shop_name = '';
            $rating = max(0, min(5, $rating));
        } else {
            $shop = eottae_review_board_fetch_shop($shop_wr_id, $shop_bo);
            if (!$shop) {
                return array('ok' => false, 'message' => '선택한 업체를 찾을 수 없습니다.');
            }
            $shop_bo = (string) ($shop['bo_table'] ?? $shop_bo);
            if ($shop_name === '') {
                $shop_name = (string) ($shop['name'] ?? '');
            }
            if ($rating < 1 || $rating > 5) {
                $rating = 0;
            }
        }

        return array(
            'ok'          => true,
            'shop_wr_id'  => $shop_wr_id,
            'shop_bo'     => $shop_bo,
            'shop_name'   => $shop_name,
            'rating'      => $rating,
            'is_update'   => (bool) $is_update,
        );
    }
}

if (!function_exists('eottae_review_board_validate_write_post')) {
    function eottae_review_board_validate_write_post(array $post, $member, $is_admin = '', $wr_id = 0, $w = '')
    {
        $normalized = eottae_review_board_normalize_write_post($post, $w === 'u');
        if (empty($normalized['ok'])) {
            return $normalized;
        }

        if (!empty($member['mb_id']) && function_exists('eottae_is_business_member') && eottae_is_business_member($member)) {
            return array('ok' => false, 'message' => '사업자 회원은 리뷰를 작성할 수 없습니다.');
        }

        if ($normalized['shop_wr_id'] > 0 && !empty($member['mb_id'])) {
            if (eottae_review_board_user_reviewed_shop(
                $member['mb_id'],
                $normalized['shop_wr_id'],
                $normalized['shop_bo'],
                $w === 'u' ? (int) $wr_id : 0
            )) {
                return array('ok' => false, 'message' => '이미 이 업체에 리뷰를 작성하셨습니다.');
            }
        }

        return array('ok' => true, 'normalized' => $normalized);
    }
}

if (!function_exists('eottae_review_board_apply_write_post')) {
    function eottae_review_board_apply_write_post(array $normalized)
    {
        if (empty($normalized['ok'])) {
            return;
        }

        $shop_wr_id = (int) ($normalized['shop_wr_id'] ?? 0);
        if ($shop_wr_id > 0) {
            $_POST['wr_1'] = (string) $shop_wr_id;
            $_POST['wr_2'] = (string) (int) ($normalized['rating'] ?? 0);
            $_POST['wr_3'] = (string) ($normalized['shop_name'] ?? '');
            $_POST['wr_6'] = (string) ($normalized['shop_bo'] ?? '');
            $_POST['wr_4'] = 'visible';
        } else {
            $_POST['wr_1'] = '';
            $_POST['wr_2'] = (string) max(0, min(5, (int) ($normalized['rating'] ?? 0)));
            $_POST['wr_3'] = '';
            $_POST['wr_6'] = '';
            $_POST['wr_4'] = 'visible';
        }
    }
}

if (!function_exists('eottae_review_board_after_write')) {
    function eottae_review_board_after_write($wr_id, array $normalized)
    {
        $shop_wr_id = (int) ($normalized['shop_wr_id'] ?? 0);
        $shop_bo = (string) ($normalized['shop_bo'] ?? '');
        if ($shop_wr_id < 1 || !function_exists('eottae_sync_shop_review_stats')) {
            return;
        }

        $main_shop = function_exists('eottae_shop_table') ? eottae_shop_table() : 'shop';
        if ($shop_bo === '' || $shop_bo === $main_shop) {
            eottae_sync_shop_review_stats($shop_wr_id);
        }
    }
}
