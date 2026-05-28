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
                    `cp_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`wr_id`),
                    KEY `idx_shop` (`shop_bo_table`, `shop_wr_id`),
                    KEY `idx_cp` (`cp_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8
            ", false);
        } elseif (!eottae_adroom_meta_has_column('cp_id')) {
            sql_query(" ALTER TABLE `{$table}` ADD COLUMN `cp_id` int(11) unsigned NOT NULL DEFAULT '0' AFTER `shop_lng`, ADD KEY `idx_cp` (`cp_id`) ", false);
        }

        eottae_adroom_ensure_board();
        eottae_adroom_sync_board_settings();
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
            'bo_upload_count'     => 1,
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

if (!function_exists('eottae_adroom_meta_has_column')) {
    function eottae_adroom_meta_has_column($column)
    {
        $table = eottae_adroom_meta_table();
        $column = preg_replace('/[^a-z0-9_]/i', '', (string) $column);
        if ($column === '' || !eottae_adroom_table_exists($table)) {
            return false;
        }

        $row = sql_fetch(" SHOW COLUMNS FROM `{$table}` LIKE '".sql_escape_string($column)."' ", false);

        return is_array($row) && !empty($row['Field']);
    }
}

if (!function_exists('eottae_adroom_sync_board_settings')) {
    function eottae_adroom_sync_board_settings()
    {
        global $g5;

        $bo_table = eottae_adroom_board_table();
        sql_query("
            UPDATE {$g5['board_table']} SET
                bo_upload_count = 1,
                bo_skin = 'eottae-adroom',
                bo_mobile_skin = 'eottae-adroom'
            WHERE bo_table = '".sql_escape_string($bo_table)."'
            LIMIT 1
        ", false);
    }
}

if (!function_exists('eottae_adroom_shop_thumb_url')) {
    function eottae_adroom_shop_thumb_url($shop_row, $bo_table = '')
    {
        if (!is_array($shop_row)) {
            return '';
        }

        if (!function_exists('eottae_shop_card_thumb')) {
            include_once G5_PATH.'/components/eottae/shop-card.php';
        }

        $url = function_exists('eottae_shop_card_thumb')
            ? eottae_shop_card_thumb($shop_row, $bo_table)
            : '';

        if ($url !== '' && function_exists('eottae_map_public_url')) {
            $url = eottae_map_public_url($url);
        }

        return $url;
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

if (!function_exists('eottae_adroom_write_eligibility')) {
    /**
     * 광고 등록 버튼·안내 노출 판단
     *
     * @param array<string, mixed>|null $member
     * @param array<string, mixed>|null $board
     * @return array{can_write:bool,is_biz:bool,has_board_write:bool,mb_level:int,bo_write_level:int,biz_level:int}
     */
    function eottae_adroom_write_eligibility($member = null, $board = null, $is_member = false, $is_super = false)
    {
        $biz_level = defined('EOTTae_BUSINESS_LEVEL') ? (int) EOTTae_BUSINESS_LEVEL : 5;
        if (!is_array($board) || empty($board['bo_table'])) {
            if (function_exists('get_board_db')) {
                $board = get_board_db(eottae_adroom_board_table(), true);
            } else {
                $board = array();
            }
        }
        $bo_write_level = isset($board['bo_write_level']) ? (int) $board['bo_write_level'] : $biz_level;
        $mb_level = ($is_member && is_array($member)) ? (int) ($member['mb_level'] ?? 0) : 0;
        $is_biz = $is_member && function_exists('eottae_is_business_member') && eottae_is_business_member($member);
        $has_board_write = $is_super || ($is_member && $mb_level >= $bo_write_level);
        $can_write = $is_super || ($is_biz && $has_board_write);

        return array(
            'can_write'        => $can_write,
            'is_biz'           => $is_biz,
            'has_board_write'  => $has_board_write,
            'mb_level'         => $mb_level,
            'bo_write_level'   => $bo_write_level,
            'biz_level'        => $biz_level,
        );
    }
}

if (!function_exists('eottae_adroom_can_write')) {
    function eottae_adroom_can_write($member = null, $is_super = false, $board = null)
    {
        global $is_member;

        $elig = eottae_adroom_write_eligibility(
            is_array($member) ? $member : null,
            is_array($board) ? $board : null,
            !empty($is_member) || (is_array($member) && !empty($member['mb_id'])),
            $is_super
        );

        return !empty($elig['can_write']);
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
                'thumb_url'    => eottae_adroom_shop_thumb_url($shop_row, $bo_table),
                'search_text'  => function_exists('mb_strtolower')
                    ? mb_strtolower($name.' '.$board_label.' '.($shop['region'] ?? '').' '.($shop['address'] ?? '').' '.($shop['category'] ?? ''), 'UTF-8')
                    : strtolower($name.' '.$board_label.' '.($shop['region'] ?? '').' '.($shop['address'] ?? '').' '.($shop['category'] ?? '')),
            );
        }

        return $shops;
    }
}

if (!function_exists('eottae_adroom_member_coupon_options')) {
    /**
     * 광고에 연동 가능한 사업자 쿠폰 목록
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_adroom_member_coupon_options($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '' || !function_exists('eottae_business_coupon_campaigns')) {
            return array();
        }

        include_once G5_LIB_PATH.'/eottae-business-coupon.lib.php';
        eottae_business_coupon_ensure_schema();

        $rows = eottae_business_coupon_campaigns($mb_id, 100);
        $options = array();
        foreach ($rows as $row) {
            $cp_id = (int) ($row['cp_id'] ?? 0);
            if ($cp_id < 1) {
                continue;
            }

            if (!empty($row['cp_expires_at']) && $row['cp_expires_at'] !== '0000-00-00 00:00:00' && $row['cp_expires_at'] < G5_TIME_YMDHIS) {
                continue;
            }

            $max = (int) ($row['cp_max_issue'] ?? 0);
            $issued = (int) ($row['issued_count'] ?? 0);
            if ($max > 0 && $issued >= $max) {
                continue;
            }

            $benefit = function_exists('eottae_business_coupon_format_benefit')
                ? eottae_business_coupon_format_benefit($row)
                : (string) ($row['cp_title'] ?? '');

            $options[] = array(
                'cp_id'       => $cp_id,
                'title'       => (string) ($row['cp_title'] ?? ''),
                'desc'        => (string) ($row['cp_desc'] ?? ''),
                'benefit'     => $benefit,
                'issued_count'=> $issued,
                'max_issue'   => $max,
                'expires_at'  => (string) ($row['cp_expires_at'] ?? ''),
            );
        }

        return $options;
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
    function eottae_adroom_save_meta($wr_id, $shop_bo_table, $shop_wr_id, $cp_id = 0)
    {
        $wr_id = (int) $wr_id;
        $shop_bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $shop_bo_table);
        $shop_wr_id = (int) $shop_wr_id;
        $cp_id = (int) $cp_id;

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

        global $member, $is_admin;
        if ($cp_id > 0) {
            include_once G5_LIB_PATH.'/eottae-business-coupon.lib.php';
            $owner_mb_id = (string) ($member['mb_id'] ?? '');
            $coupon = eottae_business_coupon_get($cp_id, ($is_admin === 'super') ? '' : $owner_mb_id);
            if (empty($coupon['cp_id'])) {
                return array('ok' => false, 'message' => '선택한 쿠폰을 찾을 수 없습니다.');
            }
            $cp_id = (int) $coupon['cp_id'];
        }

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
            'cp_id'          => $cp_id,
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
                    cp_id = '{$cp_id}',
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
                wr_3 = '{$fields['shop_region']}',
                wr_4 = '{$cp_id}'
            WHERE wr_id = '{$wr_id}'
            LIMIT 1
        ", false);

        return array('ok' => true, 'message' => '', 'shop' => $shop, 'cp_id' => $cp_id);
    }
}

if (!function_exists('eottae_adroom_get_linked_coupon')) {
    function eottae_adroom_get_linked_coupon($wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return array();
        }

        $meta = eottae_adroom_get_meta($wr_id);
        $cp_id = (int) ($meta['cp_id'] ?? 0);
        if ($cp_id < 1) {
            global $g5;
            $write_table = $g5['write_prefix'].eottae_adroom_board_table();
            $row = sql_fetch(" SELECT wr_4 FROM `{$write_table}` WHERE wr_id = '{$wr_id}' LIMIT 1 ", false);
            $cp_id = (int) ($row['wr_4'] ?? 0);
        }

        if ($cp_id < 1 || !function_exists('eottae_business_coupon_get')) {
            return array();
        }

        include_once G5_LIB_PATH.'/eottae-business-coupon.lib.php';
        $coupon = eottae_business_coupon_get($cp_id);
        if (empty($coupon['cp_id'])) {
            return array();
        }

        if (!empty($coupon['cp_expires_at']) && $coupon['cp_expires_at'] !== '0000-00-00 00:00:00' && $coupon['cp_expires_at'] < G5_TIME_YMDHIS) {
            return array();
        }

        $coupon['issued_count'] = eottae_business_coupon_issue_count($cp_id);
        $coupon['benefit_line'] = eottae_business_coupon_format_benefit($coupon);

        return $coupon;
    }
}

if (!function_exists('eottae_adroom_member_coupon_issue')) {
    function eottae_adroom_member_coupon_issue($wr_id, $mb_id)
    {
        $wr_id = (int) $wr_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($wr_id < 1 || $mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 쿠폰을 받을 수 있습니다.');
        }

        $coupon = eottae_adroom_get_linked_coupon($wr_id);
        if (empty($coupon['cp_id'])) {
            return array('ok' => false, 'message' => '이 광고에 연결된 쿠폰이 없습니다.');
        }

        $owner_mb_id = (string) ($coupon['cp_owner_mb_id'] ?? '');
        if ($owner_mb_id === '') {
            return array('ok' => false, 'message' => '쿠폰 정보가 올바르지 않습니다.');
        }

        include_once G5_LIB_PATH.'/eottae-business-coupon.lib.php';
        $result = eottae_business_coupon_issue_to_member($owner_mb_id, (int) $coupon['cp_id'], $mb_id);
        if (empty($result['ok'])) {
            return $result;
        }

        return array(
            'ok'      => true,
            'message' => '쿠폰이 발급되었습니다. 마이페이지 → 쿠폰함에서 확인하세요.',
            'ci_id'   => (int) ($result['ci_id'] ?? 0),
            'coupons_url' => G5_URL.'/page/eottae-coupons.php',
        );
    }
}

if (!function_exists('eottae_adroom_member_has_active_coupon')) {
    function eottae_adroom_member_has_active_coupon($cp_id, $mb_id)
    {
        global $g5;

        $cp_id = (int) $cp_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($cp_id < 1 || $mb_id === '') {
            return false;
        }

        if (!function_exists('eottae_coupon_bootstrap_tables')) {
            include_once G5_LIB_PATH.'/eottae-coupon.lib.php';
        }
        eottae_coupon_bootstrap_tables();

        $row = sql_fetch("
            SELECT ci_id
            FROM {$g5['eottae_coupon_issue_table']}
            WHERE cp_id = '{$cp_id}'
              AND mb_id = '".sql_escape_string($mb_id)."'
              AND ci_status = 'active'
            LIMIT 1
        ", false);

        return is_array($row) && !empty($row['ci_id']);
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

        $cp_id = isset($_POST['eottae_adroom_cp_id']) ? (int) $_POST['eottae_adroom_cp_id'] : (int) ($_POST['wr_4'] ?? 0);
        eottae_adroom_save_meta((int) $wr_id, $shop_bo, $shop_wr_id, $cp_id);
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

if (!function_exists('eottae_adroom_render_write_guide')) {
    /**
     * 광고 등록 버튼 노출 조건 안내 (권한 없을 때)
     */
    function eottae_adroom_render_write_guide($member, $board, $is_member, $is_super, $write_href, $can_write_ad)
    {
        if ($can_write_ad) {
            return '';
        }

        $elig = eottae_adroom_write_eligibility($member, $board, $is_member, $is_super);
        $biz_level = (int) $elig['biz_level'];
        $bo_write_level = (int) $elig['bo_write_level'];
        $mb_level = (int) $elig['mb_level'];
        $is_biz = !empty($elig['is_biz']);
        $has_board_write = !empty($elig['has_board_write']);
        $mb_role = ($is_member && is_array($member) && isset($member['mb_1'])) ? (string) $member['mb_1'] : '';
        $role_business = $mb_role === 'business';

        $mypage_url = function_exists('eottae_mypage_url') ? eottae_mypage_url() : G5_URL.'/page/eottae-mypage.php';
        $login_url = function_exists('eottae_login_url')
            ? eottae_login_url(eottae_adroom_list_url())
            : G5_BBS_URL.'/login.php?url='.urlencode(eottae_adroom_list_url());
        $shop_table = defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop';
        $shop_write_url = G5_BBS_URL.'/write.php?bo_table='.$shop_table;

        ob_start();
        ?>
        <aside class="adroom-write-guide" id="adroom-write-guide" aria-labelledby="adroom-write-guide-title" tabindex="-1">
            <div class="adroom-write-guide__banner">
                <span class="adroom-write-guide__badge">안내</span>
                <h2 class="adroom-write-guide__title" id="adroom-write-guide-title">광고 등록 안내</h2>
                <p class="adroom-write-guide__lead">사업자 회원으로 로그인한 뒤, <strong>아래 두 조건을 모두</strong> 충족하면 상단에 <strong>「광고 등록」</strong> 버튼이 표시됩니다.</p>
            </div>

            <div class="adroom-write-guide__grid" role="list">
                <article class="adroom-write-guide__card<?php echo $is_biz ? ' is-ok' : ' is-pending'; ?>" role="listitem">
                    <div class="adroom-write-guide__card-top">
                        <span class="adroom-write-guide__num" aria-hidden="true">1</span>
                        <h3 class="adroom-write-guide__card-title">사업자(업체) 회원</h3>
                        <?php if ($is_member) { ?>
                        <span class="adroom-write-guide__pill<?php echo $is_biz ? ' is-ok' : ' is-no'; ?>"><?php echo $is_biz ? '충족' : '미충족'; ?></span>
                        <?php } ?>
                    </div>
                    <ul class="adroom-write-guide__checks">
                        <li>회원 레벨 <strong><?php echo (int) $biz_level; ?> 이상</strong></li>
                        <li>또는 가입 시 <strong>사업자</strong>로 선택</li>
                    </ul>
                    <?php if ($is_member && !$is_biz) { ?>
                    <p class="adroom-write-guide__meta">내 정보 · 레벨 <strong><?php echo (int) $mb_level; ?></strong> · <?php echo $role_business ? '가입 역할 <strong>사업자</strong>' : '가입 역할 <strong>일반</strong>'; ?></p>
                    <?php } ?>
                </article>

                <article class="adroom-write-guide__card<?php echo $has_board_write ? ' is-ok' : ' is-pending'; ?>" role="listitem">
                    <div class="adroom-write-guide__card-top">
                        <span class="adroom-write-guide__num" aria-hidden="true">2</span>
                        <h3 class="adroom-write-guide__card-title">광고방 글쓰기 권한</h3>
                        <?php if ($is_member) { ?>
                        <span class="adroom-write-guide__pill<?php echo $has_board_write ? ' is-ok' : ' is-no'; ?>"><?php echo $has_board_write ? '충족' : '미충족'; ?></span>
                        <?php } ?>
                    </div>
                    <ul class="adroom-write-guide__checks">
                        <li>광고방 글쓰기 레벨 <strong><?php echo (int) $bo_write_level; ?> 이상</strong> 필요</li>
                        <?php if ($is_member) { ?>
                        <li>현재 내 레벨 <strong><?php echo (int) $mb_level; ?></strong></li>
                        <?php } ?>
                    </ul>
                </article>
            </div>

            <div class="adroom-write-guide__tips">
                <p class="adroom-write-guide__tips-title">참고</p>
                <ul class="adroom-write-guide__tips-list">
                    <li><strong>업소 등록</strong>과 <strong>광고 등록</strong>은 별도입니다. 업소만 등록해 두어도 사업자 회원이 아니면 버튼이 보이지 않습니다.</li>
                    <li>광고를 올릴 때는 <strong>본인 명의 업체 1곳</strong>을 반드시 연동해야 합니다.</li>
                </ul>
            </div>

            <div class="adroom-write-guide__foot">
                <?php if (!$is_member) { ?>
                <p class="adroom-write-guide__foot-text">사업자 회원 계정으로 로그인해 주세요.</p>
                <a href="<?php echo $login_url; ?>" class="adroom-btn adroom-btn--primary adroom-write-guide__cta">로그인</a>
                <?php } else { ?>
                <p class="adroom-write-guide__foot-text">조건을 충족했는데도 버튼이 없다면 마이페이지에서 회원 유형을 확인해 주세요.</p>
                <div class="adroom-write-guide__actions">
                    <a href="<?php echo $mypage_url; ?>" class="adroom-btn adroom-btn--primary adroom-write-guide__cta">마이페이지</a>
                    <a href="<?php echo $shop_write_url; ?>" class="adroom-btn adroom-btn--outline">업소 등록</a>
                </div>
                <?php } ?>
            </div>
        </aside>
        <?php

        return (string) ob_get_clean();
    }
}
