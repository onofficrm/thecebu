<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_adroom_board_table')) {
    function eottae_adroom_board_table()
    {
        return defined('EOTTae_ADROOM_TABLE') ? EOTTae_ADROOM_TABLE : 'adroom';
    }
}

if (!function_exists('eottae_adroom_is_board')) {
    function eottae_adroom_is_board($bo_table)
    {
        return preg_replace('/[^a-z0-9_]/', '', (string) $bo_table) === eottae_adroom_board_table();
    }
}

if (!function_exists('eottae_adroom_meta_table')) {
    function eottae_adroom_meta_table()
    {
        return G5_TABLE_PREFIX.'sebu_adroom_meta';
    }
}

if (!function_exists('eottae_adroom_table_exists')) {
    function eottae_adroom_table_exists($table)
    {
        $table = preg_replace('/[^a-z0-9_]/i', '', (string) $table);
        if ($table === '') {
            return false;
        }
        if (!function_exists('eottae_talkroom_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        return eottae_talkroom_table_exists($table);
    }
}

if (!function_exists('eottae_adroom_ensure_schema')) {
    function eottae_adroom_ensure_schema()
    {
        static $done = false;
        if ($done) {
            return true;
        }

        $table = eottae_adroom_meta_table();
        if (!eottae_adroom_table_exists($table)) {
            sql_query("
                CREATE TABLE IF NOT EXISTS `{$table}` (
                    `wr_id` int(11) unsigned NOT NULL,
                    `shop_bo_table` varchar(20) NOT NULL DEFAULT '',
                    `shop_wr_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `shop_name` varchar(200) NOT NULL DEFAULT '',
                    `shop_region` varchar(80) NOT NULL DEFAULT '',
                    `shop_address` varchar(255) NOT NULL DEFAULT '',
                    `shop_lat` varchar(32) NOT NULL DEFAULT '',
                    `shop_lng` varchar(32) NOT NULL DEFAULT '',
                    `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`wr_id`),
                    KEY `idx_shop` (`shop_bo_table`, `shop_wr_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8
            ", false);
        }

        eottae_adroom_ensure_board();
        $done = true;

        return true;
    }
}

if (!function_exists('eottae_adroom_board_def')) {
    function eottae_adroom_board_def()
    {
        $level = defined('EOTTae_BUSINESS_LEVEL') ? (int) EOTTae_BUSINESS_LEVEL : 5;

        return array(
            'bo_table'            => eottae_adroom_board_table(),
            'bo_subject'          => '광고방',
            'bo_skin'             => 'eottae-adroom',
            'bo_mobile_skin'      => 'eottae-adroom',
            'gr_id'               => 'community',
            'bo_read_level'       => 1,
            'bo_write_level'      => $level,
            'bo_comment_level'    => 2,
            'bo_use_category'     => 1,
            'bo_category_list'    => '홍보|이벤트|할인|신규오픈|기타',
            'bo_upload_count'     => 5,
            'bo_upload_size'      => 10485760,
            'bo_use_dhtml_editor' => 1,
            'bo_order'            => 17,
            'bo_1_subj'           => '연동 업체',
            'bo_2_subj'           => '업체 ID',
        );
    }
}

if (!function_exists('eottae_adroom_ensure_board')) {
    function eottae_adroom_ensure_board()
    {
        $install_lib = G5_PATH.'/setup/tools/eottae-install.lib.php';
        if (!is_file($install_lib)) {
            return array('ok' => false, 'message' => 'install helper missing');
        }

        include_once $install_lib;
        if (!function_exists('eottae_install_board_exists') || !function_exists('eottae_install_create_board')) {
            return array('ok' => false, 'message' => 'install helper incomplete');
        }

        $bo_table = eottae_adroom_board_table();
        if (eottae_install_board_exists($bo_table)) {
            return array('ok' => true, 'action' => 'skip');
        }

        if (function_exists('eottae_install_ensure_group')) {
            eottae_install_ensure_group('community', '커뮤니티');
        }

        return eottae_install_create_board(eottae_adroom_board_def());
    }
}

if (!function_exists('eottae_adroom_list_url')) {
    function eottae_adroom_list_url()
    {
        return G5_URL.'/ad-room/';
    }
}

if (!function_exists('eottae_adroom_write_url')) {
    function eottae_adroom_write_url()
    {
        return G5_BBS_URL.'/write.php?bo_table='.eottae_adroom_board_table();
    }
}

if (!function_exists('eottae_adroom_can_write')) {
    function eottae_adroom_can_write($member = null, $is_super = false)
    {
        if ($is_super) {
            return true;
        }

        return function_exists('eottae_is_business_member') && eottae_is_business_member($member);
    }
}

if (!function_exists('eottae_adroom_member_shops')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_adroom_member_shops($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '' || !function_exists('eottae_business_shop_posts')) {
            return array();
        }

        $posts = eottae_business_shop_posts($mb_id, 30);
        $shops = array();
        foreach ($posts as $row) {
            if (!is_array($row)) {
                continue;
            }
            $bo_table = (string) ($row['bo_table'] ?? '');
            $wr_id = (int) ($row['wr_id'] ?? 0);
            if ($bo_table === '' || $wr_id < 1) {
                continue;
            }

            $shop_row = eottae_adroom_fetch_shop_row($bo_table, $wr_id);
            if (!$shop_row) {
                continue;
            }

            $shop = function_exists('eottae_shop_from_write')
                ? eottae_shop_from_write($shop_row, $bo_table)
                : array();

            $name = trim((string) ($shop['name'] ?? ''));
            if ($name === '') {
                $name = trim((string) ($row['subject'] ?? ''));
            }
            if ($name === '') {
                continue;
            }

            $board_labels = array(
                'shop'    => '업체',
                'food'    => '맛집',
                'massage' => '마사지',
                'rentcar' => '렌트카',
                'tour'    => '투어',
            );
            $board_label = isset($board_labels[$bo_table]) ? $board_labels[$bo_table] : '';
            if ($board_label === '' && !empty($row['category'])) {
                $board_label = (string) $row['category'];
            }

            $shops[] = array(
                'bo_table'     => $bo_table,
                'wr_id'        => $wr_id,
                'name'         => $name,
                'region'       => (string) ($shop['region'] ?? ''),
                'address'      => (string) ($shop['address'] ?? ''),
                'category'     => (string) ($shop['category'] ?? ''),
                'board_label'  => $board_label,
                'view_url'     => function_exists('eottae_shop_view_url')
                    ? eottae_shop_view_url($wr_id, $bo_table)
                    : G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id,
                'map_url'      => function_exists('eottae_maps_directions_url')
                    ? eottae_maps_directions_url($shop['lat'] ?? '', $shop['lng'] ?? '', $shop['address'] ?? '')
                    : '',
                'thumb_url'    => function_exists('eottae_shop_card_thumb')
                    ? eottae_shop_card_thumb($shop_row, $bo_table)
                    : '',
            );
        }

        return $shops;
    }
}

if (!function_exists('eottae_adroom_fetch_shop_row')) {
    function eottae_adroom_fetch_shop_row($shop_bo_table, $shop_wr_id)
    {
        global $g5;

        $shop_bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $shop_bo_table);
        $shop_wr_id = (int) $shop_wr_id;
        if ($shop_bo_table === '' || $shop_wr_id < 1) {
            return null;
        }

        if (!function_exists('eottae_is_shop_board') || !eottae_is_shop_board($shop_bo_table)) {
            return null;
        }

        $write_table = $g5['write_prefix'].$shop_bo_table;
        $row = sql_fetch("
            SELECT *
            FROM `{$write_table}`
            WHERE wr_id = '{$shop_wr_id}'
              AND wr_is_comment = 0
            LIMIT 1
        ", false);

        return is_array($row) && !empty($row['wr_id']) ? $row : null;
    }
}

if (!function_exists('eottae_adroom_get_meta')) {
    function eottae_adroom_get_meta($wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return null;
        }

        eottae_adroom_ensure_schema();
        $table = eottae_adroom_meta_table();

        return sql_fetch(" SELECT * FROM `{$table}` WHERE wr_id = '{$wr_id}' LIMIT 1 ", false);
    }
}

if (!function_exists('eottae_adroom_save_meta')) {
    function eottae_adroom_save_meta($wr_id, $shop_bo_table, $shop_wr_id)
    {
        $wr_id = (int) $wr_id;
        $shop_bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $shop_bo_table);
        $shop_wr_id = (int) $shop_wr_id;

        if ($wr_id < 1) {
            return array('ok' => false, 'message' => '글 정보가 없습니다.');
        }

        $shop_row = eottae_adroom_fetch_shop_row($shop_bo_table, $shop_wr_id);
        if (!$shop_row) {
            return array('ok' => false, 'message' => '연동할 업체를 선택해 주세요.');
        }

        $shop = function_exists('eottae_shop_from_write')
            ? eottae_shop_from_write($shop_row, $shop_bo_table)
            : array();

        eottae_adroom_ensure_schema();
        $table = eottae_adroom_meta_table();
        $now = G5_TIME_YMDHIS;
        $exists = eottae_adroom_get_meta($wr_id);

        $fields = array(
            'shop_bo_table' => sql_escape_string($shop_bo_table),
            'shop_wr_id'     => $shop_wr_id,
            'shop_name'      => sql_escape_string((string) ($shop['name'] ?? '')),
            'shop_region'    => sql_escape_string((string) ($shop['region'] ?? '')),
            'shop_address'   => sql_escape_string((string) ($shop['address'] ?? '')),
            'shop_lat'       => sql_escape_string((string) ($shop['lat'] ?? '')),
            'shop_lng'       => sql_escape_string((string) ($shop['lng'] ?? '')),
            'updated_at'     => $now,
        );

        if ($exists) {
            $set = array();
            foreach ($fields as $col => $val) {
                $set[] = "`{$col}` = '{$val}'";
            }
            sql_query(" UPDATE `{$table}` SET ".implode(', ', $set)." WHERE wr_id = '{$wr_id}' LIMIT 1 ", false);
        } else {
            sql_query("
                INSERT INTO `{$table}` SET
                    wr_id = '{$wr_id}',
                    shop_bo_table = '{$fields['shop_bo_table']}',
                    shop_wr_id = '{$fields['shop_wr_id']}',
                    shop_name = '{$fields['shop_name']}',
                    shop_region = '{$fields['shop_region']}',
                    shop_address = '{$fields['shop_address']}',
                    shop_lat = '{$fields['shop_lat']}',
                    shop_lng = '{$fields['shop_lng']}',
                    created_at = '{$now}',
                    updated_at = '{$now}'
            ", false);
        }

        global $g5;
        $write_table = $g5['write_prefix'].eottae_adroom_board_table();
        sql_query("
            UPDATE `{$write_table}` SET
                wr_1 = '{$fields['shop_bo_table']}',
                wr_2 = '{$fields['shop_wr_id']}',
                wr_3 = '{$fields['shop_region']}'
            WHERE wr_id = '{$wr_id}'
            LIMIT 1
        ", false);

        return array('ok' => true, 'message' => '', 'shop' => $shop);
    }
}

if (!function_exists('eottae_adroom_snippet')) {
    function eottae_adroom_snippet($content, $len = 140)
    {
        if (function_exists('eottae_community_snippet')) {
            return eottae_community_snippet($content, $len);
        }

        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $content)));

        return $text === '' ? '' : cut_str($text, (int) $len, '…');
    }
}

if (!function_exists('eottae_adroom_list_thumb')) {
    function eottae_adroom_list_thumb($wr_id, array $item = array())
    {
        $wr_id = (int) $wr_id;
        $bo_table = eottae_adroom_board_table();

        if (!function_exists('get_list_thumbnail')) {
            include_once G5_LIB_PATH.'/thumbnail.lib.php';
        }
        if (function_exists('get_list_thumbnail')) {
            $thumb = get_list_thumbnail($bo_table, $wr_id, 400, 300, false, true);
            if (!empty($thumb['src'])) {
                return $thumb['src'];
            }
        }

        $shop_bo = (string) ($item['wr_1'] ?? '');
        $shop_wr_id = (int) ($item['wr_2'] ?? 0);
        if ($shop_bo === '' || $shop_wr_id < 1) {
            $meta = eottae_adroom_get_meta($wr_id);
            if ($meta) {
                $shop_bo = (string) ($meta['shop_bo_table'] ?? '');
                $shop_wr_id = (int) ($meta['shop_wr_id'] ?? 0);
            }
        }

        if ($shop_bo !== '' && $shop_wr_id > 0) {
            $shop_row = eottae_adroom_fetch_shop_row($shop_bo, $shop_wr_id);
            if ($shop_row && function_exists('eottae_shop_card_thumb')) {
                $url = eottae_shop_card_thumb($shop_row, $shop_bo);
                if ($url !== '' && function_exists('eottae_map_public_url')) {
                    $url = eottae_map_public_url($url);
                }
                if ($url !== '') {
                    return $url;
                }
            }
        }

        return '';
    }
}

if (!function_exists('eottae_adroom_shop_block')) {
    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    function eottae_adroom_shop_block(array $item)
    {
        $wr_id = (int) ($item['wr_id'] ?? 0);
        $shop_bo = (string) ($item['wr_1'] ?? '');
        $shop_wr_id = (int) ($item['wr_2'] ?? 0);

        $meta = $wr_id > 0 ? eottae_adroom_get_meta($wr_id) : null;
        if ($meta) {
            if ($shop_bo === '') {
                $shop_bo = (string) ($meta['shop_bo_table'] ?? '');
            }
            if ($shop_wr_id < 1) {
                $shop_wr_id = (int) ($meta['shop_wr_id'] ?? 0);
            }
        }

        if ($shop_bo === '' || $shop_wr_id < 1) {
            return array();
        }

        $shop_row = eottae_adroom_fetch_shop_row($shop_bo, $shop_wr_id);
        if (!$shop_row) {
            return array(
                'bo_table'  => $shop_bo,
                'wr_id'     => $shop_wr_id,
                'name'      => (string) ($meta['shop_name'] ?? ''),
                'region'    => (string) ($meta['shop_region'] ?? ''),
                'address'   => (string) ($meta['shop_address'] ?? ''),
                'lat'       => (string) ($meta['shop_lat'] ?? ''),
                'lng'       => (string) ($meta['shop_lng'] ?? ''),
                'view_url'  => function_exists('eottae_shop_view_url')
                    ? eottae_shop_view_url($shop_wr_id, $shop_bo)
                    : '',
                'map_url'   => '',
            );
        }

        $shop = eottae_shop_from_write($shop_row, $shop_bo);

        return array(
            'bo_table'  => $shop_bo,
            'wr_id'     => $shop_wr_id,
            'name'      => (string) ($shop['name'] ?? ''),
            'region'    => (string) ($shop['region'] ?? ''),
            'address'   => (string) ($shop['address'] ?? ''),
            'category'  => (string) ($shop['category'] ?? ''),
            'phone'     => (string) ($shop['phone'] ?? ''),
            'lat'       => (string) ($shop['lat'] ?? ''),
            'lng'       => (string) ($shop['lng'] ?? ''),
            'view_url'  => function_exists('eottae_shop_view_url')
                ? eottae_shop_view_url($shop_wr_id, $shop_bo)
                : G5_BBS_URL.'/board.php?bo_table='.$shop_bo.'&wr_id='.$shop_wr_id,
            'map_url'   => function_exists('eottae_maps_directions_url')
                ? eottae_maps_directions_url($shop['lat'] ?? '', $shop['lng'] ?? '', $shop['address'] ?? '')
                : '',
        );
    }
}

if (!function_exists('eottae_adroom_validate_write')) {
    function eottae_adroom_validate_write($mb_id, $shop_bo_table, $shop_wr_id, $is_super = false)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인이 필요합니다.');
        }

        if (!$is_super && !eottae_adroom_can_write(null, false)) {
            return array('ok' => false, 'message' => '업체 회원만 광고를 등록할 수 있습니다.');
        }

        $shop_bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $shop_bo_table);
        $shop_wr_id = (int) $shop_wr_id;
        if ($shop_bo_table === '' || $shop_wr_id < 1) {
            return array('ok' => false, 'message' => '연동할 업체를 선택해 주세요.');
        }

        $shop_row = eottae_adroom_fetch_shop_row($shop_bo_table, $shop_wr_id);
        if (!$shop_row) {
            return array('ok' => false, 'message' => '업체 정보를 찾을 수 없습니다.');
        }

        if (!$is_super && function_exists('eottae_business_owns_shop')) {
            if (!eottae_business_owns_shop($mb_id, $shop_wr_id, $shop_bo_table)) {
                return array('ok' => false, 'message' => '본인 업체만 연동할 수 있습니다.');
            }
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_adroom_on_write_after')) {
    function eottae_adroom_on_write_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        global $member, $is_admin;

        if (empty($board['bo_table']) || !eottae_adroom_is_board($board['bo_table'])) {
            return;
        }

        $shop_bo = isset($_POST['eottae_adroom_shop_bo_table'])
            ? preg_replace('/[^a-z0-9_]/', '', (string) $_POST['eottae_adroom_shop_bo_table'])
            : preg_replace('/[^a-z0-9_]/', '', (string) ($_POST['wr_1'] ?? ''));
        $shop_wr_id = isset($_POST['eottae_adroom_shop_wr_id'])
            ? (int) $_POST['eottae_adroom_shop_wr_id']
            : (int) ($_POST['wr_2'] ?? 0);

        $mb_id = (string) ($member['mb_id'] ?? '');
        $is_super = ($is_admin === 'super');
        $check = eottae_adroom_validate_write($mb_id, $shop_bo, $shop_wr_id, $is_super);
        if (empty($check['ok'])) {
            return;
        }

        eottae_adroom_save_meta((int) $wr_id, $shop_bo, $shop_wr_id);
    }
}

if (!function_exists('eottae_adroom_on_bbs_write')) {
    function eottae_adroom_on_bbs_write($board, $wr_id, $w)
    {
        global $member, $is_admin;

        if (empty($board['bo_table']) || !eottae_adroom_is_board($board['bo_table'])) {
            return;
        }

        $is_super = ($is_admin === 'super');
        if (!$is_super && !eottae_adroom_can_write($member, false)) {
            alert('업체 회원만 광고를 등록할 수 있습니다. 업체 등록 후 이용해 주세요.', eottae_adroom_list_url());
        }
    }
}

if (!function_exists('eottae_adroom_on_write_update_before')) {
    function eottae_adroom_on_write_update_before($board, $wr_id, $w, $qstr)
    {
        global $member, $is_admin;

        if (empty($board['bo_table']) || !eottae_adroom_is_board($board['bo_table'])) {
            return;
        }

        $shop_bo = isset($_POST['eottae_adroom_shop_bo_table'])
            ? preg_replace('/[^a-z0-9_]/', '', (string) $_POST['eottae_adroom_shop_bo_table'])
            : preg_replace('/[^a-z0-9_]/', '', (string) ($_POST['wr_1'] ?? ''));
        $shop_wr_id = isset($_POST['eottae_adroom_shop_wr_id'])
            ? (int) $_POST['eottae_adroom_shop_wr_id']
            : (int) ($_POST['wr_2'] ?? 0);

        if ($shop_bo !== '' && $shop_wr_id > 0) {
            $_POST['wr_1'] = $shop_bo;
            $_POST['wr_2'] = (string) $shop_wr_id;
            $shop_row = eottae_adroom_fetch_shop_row($shop_bo, $shop_wr_id);
            if ($shop_row && function_exists('eottae_shop_from_write')) {
                $shop = eottae_shop_from_write($shop_row, $shop_bo);
                if (!empty($shop['region'])) {
                    $_POST['wr_3'] = (string) $shop['region'];
                }
            }
        }

        $mb_id = (string) ($member['mb_id'] ?? '');
        $is_super = ($is_admin === 'super');
        $check = eottae_adroom_validate_write($mb_id, $shop_bo, $shop_wr_id, $is_super);
        if (empty($check['ok'])) {
            alert($check['message']);
        }
    }
}

if (!function_exists('eottae_adroom_board_hero')) {
    function eottae_adroom_board_hero($board, $sca = '')
    {
        $sca = get_text((string) $sca);

        return array(
            'kicker' => 'Business · Ad Room',
            'title'  => $sca !== '' ? $sca : '광고방',
            'desc'   => '세부 업체 회원이 홍보·이벤트·할인 정보를 올리는 공간입니다. 글과 함께 내 업체 지도·연락처가 연동됩니다.',
            'image'  => '',
        );
    }
}

if (!function_exists('eottae_adroom_category_tabs')) {
    function eottae_adroom_category_tabs($board)
    {
        $tabs = array(array('slug' => '', 'label' => '전체', 'count' => 0));
        if (empty($board['bo_category_list'])) {
            return $tabs;
        }

        $cats = array_map('trim', explode('|', (string) $board['bo_category_list']));
        foreach ($cats as $cat) {
            if ($cat === '') {
                continue;
            }
            $tabs[] = array('slug' => $cat, 'label' => $cat, 'count' => 0);
        }

        return $tabs;
    }
}
