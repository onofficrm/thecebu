<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!defined('EOTTae_AD_SLOT_SIDEBAR')) {
    define('EOTTae_AD_SLOT_SIDEBAR', 'community_sidebar');
}

if (!defined('EOTTae_AD_SHOP_FLAG')) {
    define('EOTTae_AD_SHOP_FLAG', 'sidebar-ad');
}

if (!function_exists('eottae_ad_table')) {
    function eottae_ad_table()
    {
        return G5_TABLE_PREFIX.'eottae_ad';
    }
}

if (!function_exists('eottae_ad_ensure_table')) {
    function eottae_ad_ensure_table()
    {
        global $g5;

        $table = eottae_ad_table();
        $exists = sql_fetch(" show tables like '{$table}' ");
        if (!empty($exists)) {
            return true;
        }

        $sql = " create table if not exists `{$table}` (
            `ad_id` int unsigned not null auto_increment,
            `ad_slot` varchar(32) not null default 'community_sidebar',
            `shop_bo_table` varchar(20) not null default '',
            `shop_wr_id` int unsigned not null default 0,
            `ad_title` varchar(255) not null default '',
            `ad_subtitle` varchar(500) not null default '',
            `ad_cta` varchar(100) not null default '자세히 보기',
            `ad_link` varchar(500) not null default '',
            `ad_image` varchar(500) not null default '',
            `ad_maps_url` varchar(500) not null default '',
            `ad_lat` varchar(30) not null default '',
            `ad_lng` varchar(30) not null default '',
            `ad_active` tinyint unsigned not null default 1,
            `ad_order` int not null default 0,
            `ad_regdatetime` datetime not null default '0000-00-00 00:00:00',
            `ad_updated` datetime not null default '0000-00-00 00:00:00',
            primary key (`ad_id`),
            key `idx_slot_active` (`ad_slot`, `ad_active`, `ad_order`)
        ) engine=InnoDB default charset=utf8 ";
        sql_query($sql, false);

        return true;
    }
}

if (!function_exists('eottae_ad_row_normalize')) {
    function eottae_ad_row_normalize($row)
    {
        if (!is_array($row) || empty($row['ad_id'])) {
            return null;
        }

        $shop_url = '';
        if (!empty($row['shop_wr_id']) && !empty($row['shop_bo_table'])) {
            $shop_url = G5_BBS_URL.'/board.php?bo_table='.$row['shop_bo_table'].'&wr_id='.(int) $row['shop_wr_id'];
        }

        return array(
            'ad_id'      => (int) $row['ad_id'],
            'title'      => get_text($row['ad_title']),
            'subtitle'   => get_text($row['ad_subtitle']),
            'cta'        => get_text($row['ad_cta']) !== '' ? get_text($row['ad_cta']) : '자세히 보기',
            'link'       => get_text($row['ad_link']),
            'image'      => get_text($row['ad_image']),
            'maps_url'   => get_text($row['ad_maps_url']),
            'lat'        => get_text($row['ad_lat']),
            'lng'        => get_text($row['ad_lng']),
            'shop_url'   => $shop_url,
            'shop_wr_id' => (int) $row['shop_wr_id'],
        );
    }
}

if (!function_exists('eottae_ad_get_active')) {
    function eottae_ad_get_active($slot = EOTTae_AD_SLOT_SIDEBAR, $limit = 10)
    {
        eottae_ad_ensure_table();

        $slot = sql_escape_string($slot);
        $limit = max(1, min(20, (int) $limit));
        $table = eottae_ad_table();

        $count_row = sql_fetch(" select count(*) as cnt from {$table} ");
        if ((int) $count_row['cnt'] === 0 && function_exists('eottae_ad_seed_defaults')) {
            eottae_ad_seed_defaults();
        }

        $result = sql_query(" select * from {$table}
            where ad_slot = '{$slot}' and ad_active = 1
            order by ad_order asc, ad_id asc
            limit {$limit} ");

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $item = eottae_ad_row_normalize($row);
            if ($item) {
                $rows[] = $item;
            }
        }

        return $rows;
    }
}

if (!function_exists('eottae_ad_upsert')) {
    function eottae_ad_upsert($data)
    {
        eottae_ad_ensure_table();

        $table = eottae_ad_table();
        $slot = sql_escape_string(isset($data['ad_slot']) ? $data['ad_slot'] : EOTTae_AD_SLOT_SIDEBAR);
        $shop_bo = sql_escape_string(isset($data['shop_bo_table']) ? $data['shop_bo_table'] : EOTTae_SHOP_TABLE);
        $shop_wr_id = (int) (isset($data['shop_wr_id']) ? $data['shop_wr_id'] : 0);
        $title = sql_escape_string(isset($data['ad_title']) ? $data['ad_title'] : '');
        $subtitle = sql_escape_string(isset($data['ad_subtitle']) ? $data['ad_subtitle'] : '');
        $cta = sql_escape_string(isset($data['ad_cta']) ? $data['ad_cta'] : '자세히 보기');
        $link = sql_escape_string(isset($data['ad_link']) ? $data['ad_link'] : '');
        $image = sql_escape_string(isset($data['ad_image']) ? $data['ad_image'] : '');
        $maps = sql_escape_string(isset($data['ad_maps_url']) ? $data['ad_maps_url'] : '');
        $lat = sql_escape_string(isset($data['ad_lat']) ? $data['ad_lat'] : '');
        $lng = sql_escape_string(isset($data['ad_lng']) ? $data['ad_lng'] : '');
        $active = isset($data['ad_active']) ? (int) (bool) $data['ad_active'] : 1;
        $order = (int) (isset($data['ad_order']) ? $data['ad_order'] : 0);
        $now = G5_TIME_YMDHIS;

        $existing = null;
        if ($shop_wr_id > 0) {
            $existing = sql_fetch(" select ad_id from {$table}
                where ad_slot = '{$slot}' and shop_bo_table = '{$shop_bo}' and shop_wr_id = '{$shop_wr_id}' limit 1 ");
        } elseif ($title !== '') {
            $existing = sql_fetch(" select ad_id from {$table}
                where ad_slot = '{$slot}' and ad_title = '{$title}' limit 1 ");
        }

        if ($existing && !empty($existing['ad_id'])) {
            $ad_id = (int) $existing['ad_id'];
            sql_query(" update {$table} set
                ad_title = '{$title}',
                ad_subtitle = '{$subtitle}',
                ad_cta = '{$cta}',
                ad_link = '{$link}',
                ad_image = '{$image}',
                ad_maps_url = '{$maps}',
                ad_lat = '{$lat}',
                ad_lng = '{$lng}',
                ad_active = '{$active}',
                ad_order = '{$order}',
                ad_updated = '{$now}'
                where ad_id = '{$ad_id}' ");

            return $ad_id;
        }

        sql_query(" insert into {$table} set
            ad_slot = '{$slot}',
            shop_bo_table = '{$shop_bo}',
            shop_wr_id = '{$shop_wr_id}',
            ad_title = '{$title}',
            ad_subtitle = '{$subtitle}',
            ad_cta = '{$cta}',
            ad_link = '{$link}',
            ad_image = '{$image}',
            ad_maps_url = '{$maps}',
            ad_lat = '{$lat}',
            ad_lng = '{$lng}',
            ad_active = '{$active}',
            ad_order = '{$order}',
            ad_regdatetime = '{$now}',
            ad_updated = '{$now}' ");

        return (int) sql_insert_id();
    }
}

if (!function_exists('eottae_ad_sync_from_shop')) {
    /**
     * shop 게시글(wr_5=sidebar-ad) → 사이드바 광고 테이블 동기화
     * wr_6=부제, wr_7=CTA, wr_link1=외부링크, wr_link2=배너이미지
     */
    function eottae_ad_sync_from_shop($bo_table, $wr_id)
    {
        if (!eottae_is_shop_board($bo_table)) {
            return false;
        }

        global $g5;

        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return false;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $row = sql_fetch(" select * from {$write_table} where wr_id = '{$wr_id}' and wr_is_comment = 0 limit 1 ");
        if (!$row) {
            return false;
        }

        $flag = isset($row['wr_5']) ? trim((string) $row['wr_5']) : '';
        if ($flag !== EOTTae_AD_SHOP_FLAG) {
            eottae_ad_deactivate_by_shop($bo_table, $wr_id);
            return false;
        }

        $link = isset($row['wr_link1']) ? trim((string) $row['wr_link1']) : '';
        if ($link === '' && !empty($row['wr_id'])) {
            $link = G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id;
        }

        eottae_ad_upsert(array(
            'shop_bo_table' => $bo_table,
            'shop_wr_id'    => $wr_id,
            'ad_title'      => isset($row['wr_subject']) ? $row['wr_subject'] : '',
            'ad_subtitle'   => isset($row['wr_6']) ? $row['wr_6'] : '',
            'ad_cta'        => isset($row['wr_7']) && trim($row['wr_7']) !== '' ? $row['wr_7'] : '자세히 보기',
            'ad_link'       => $link,
            'ad_image'      => isset($row['wr_link2']) ? $row['wr_link2'] : '',
            'ad_lat'        => isset($row['wr_9']) ? $row['wr_9'] : '',
            'ad_lng'        => isset($row['wr_10']) ? $row['wr_10'] : '',
            'ad_active'     => (isset($row['wr_8']) && $row['wr_8'] === '휴업') ? 0 : 1,
            'ad_order'      => (int) $wr_id,
        ));

        return true;
    }
}

if (!function_exists('eottae_ad_deactivate_by_shop')) {
    function eottae_ad_deactivate_by_shop($bo_table, $wr_id)
    {
        eottae_ad_ensure_table();

        $table = eottae_ad_table();
        $bo_table = sql_escape_string($bo_table);
        $wr_id = (int) $wr_id;

        sql_query(" update {$table} set ad_active = 0, ad_updated = '".G5_TIME_YMDHIS."'
            where shop_bo_table = '{$bo_table}' and shop_wr_id = '{$wr_id}' ");

        return true;
    }
}

if (!function_exists('eottae_ad_seed_shop_upsert')) {
    function eottae_ad_seed_shop_upsert($data)
    {
        global $g5;

        if (!function_exists('eottae_seed_insert_shop')) {
            include_once G5_PATH.'/setup/tools/eottae-seed.lib.php';
        }

        $bo_table = EOTTae_SHOP_TABLE;
        $write_table = $g5['write_prefix'].$bo_table;
        $subject = sql_escape_string($data['wr_subject']);
        $row = sql_fetch(" select wr_id from {$write_table} where wr_subject = '{$subject}' limit 1 ");

        if (!empty($row['wr_id'])) {
            $wr_id = (int) $row['wr_id'];
            $fields = array('wr_1', 'wr_2', 'wr_3', 'wr_4', 'wr_5', 'wr_6', 'wr_7', 'wr_8', 'wr_9', 'wr_10');
            $sets = array();
            foreach ($fields as $f) {
                if (isset($data[$f])) {
                    $sets[] = "{$f} = '".sql_escape_string($data[$f])."'";
                }
            }
            if (isset($data['wr_content'])) {
                $sets[] = "wr_content = '".sql_escape_string($data['wr_content'])."'";
            }
            if (isset($data['wr_link1'])) {
                $sets[] = "wr_link1 = '".sql_escape_string($data['wr_link1'])."'";
            }
            if (isset($data['wr_link2'])) {
                $sets[] = "wr_link2 = '".sql_escape_string($data['wr_link2'])."'";
            }
            if (isset($data['ca_name'])) {
                $sets[] = "ca_name = '".sql_escape_string($data['ca_name'])."'";
            }
            if ($sets) {
                sql_query(" update {$write_table} set ".implode(', ', $sets)." where wr_id = '{$wr_id}' ");
            }

            return $wr_id;
        }

        $log = eottae_seed_insert_shop($data);
        $row = sql_fetch(" select wr_id from {$write_table} where wr_subject = '{$subject}' limit 1 ");

        return !empty($row['wr_id']) ? (int) $row['wr_id'] : 0;
    }
}

if (!function_exists('eottae_ad_seed_defaults')) {
    function eottae_ad_seed_defaults()
    {
        $img = 'https://images.unsplash.com/photo-%s?auto=format&fit=crop&w=900&q=85';
        $defs = array(
            array(
                'wr_subject'  => 'SEL 아카데미',
                'ca_name'     => '기타',
                'wr_1'        => '교육',
                'wr_2'        => '세부 시티',
                'wr_3'        => 'SEL Academy, Cebu City',
                'wr_4'        => '',
                'maps_url'    => 'https://maps.app.goo.gl/svvYcppifKUypZGK6',
                'wr_5'        => EOTTae_AD_SHOP_FLAG,
                'wr_6'        => '10만 교민·관광객에게 가장 빠르게 홍보하세요',
                'wr_7'        => 'SEL 아카데미 바로가기',
                'wr_8'        => '영업중',
                'wr_9'        => '10.3626463',
                'wr_10'       => '123.915976',
                'wr_link1'    => 'https://sel-academy.com/',
                'wr_link2'    => sprintf($img, '1523050856489-6224f4481a5a'),
                'wr_content'  => '세부 현지 어학원 SEL 아카데미 — 영어캠프, 조기유학, 가족연수 프로그램.',
            ),
            array(
                'wr_subject'  => '럭키풀빌라',
                'ca_name'     => '숙소',
                'wr_1'        => '숙소',
                'wr_2'        => '세부',
                'wr_3'        => 'Cebu, Philippines',
                'wr_4'        => '',
                'maps_url'    => 'https://maps.app.goo.gl/R9zAk1Wq82Jhmrqo7',
                'wr_5'        => EOTTae_AD_SHOP_FLAG,
                'wr_6'        => '프리미엄 풀빌라 · 세부 최고의 휴양 공간',
                'wr_7'        => '럭키풀빌라 예약하기',
                'wr_8'        => '영업중',
                'wr_9'        => '10.335339',
                'wr_10'       => '123.917187',
                'wr_link1'    => 'https://lucky-villa.com/',
                'wr_link2'    => sprintf($img, '1600596542815-ffad4c1539a9'),
                'wr_content'  => '세부 럭키풀빌라 — 프라이빗 수영장과 넓은 공간의 프리미엄 숙소.',
            ),
            array(
                'wr_subject'  => '세부나이트',
                'ca_name'     => '기타',
                'wr_1'        => '정보',
                'wr_2'        => '세부',
                'wr_3'        => '',
                'wr_4'        => '',
                'wr_5'        => EOTTae_AD_SHOP_FLAG,
                'wr_6'        => '세부 밤문화·로컬 정보 커뮤니티',
                'wr_7'        => '세부나이트 방문하기',
                'wr_8'        => '영업중',
                'wr_9'        => '',
                'wr_10'       => '',
                'wr_link1'    => 'http://cebunight.com/',
                'wr_link2'    => sprintf($img, '1514939904675-446e048d5a4e'),
                'wr_content'  => '세부나이트 — KTV, 투어, 갤러리 등 세부 밤문화 정보 커뮤니티.',
            ),
        );

        $logs = array();
        foreach ($defs as $idx => $def) {
            $wr_id = eottae_ad_seed_shop_upsert($def);
            if ($wr_id < 1) {
                $logs[] = 'FAIL shop: '.$def['wr_subject'];
                continue;
            }
            eottae_ad_sync_from_shop(EOTTae_SHOP_TABLE, $wr_id);
            $ad_id = eottae_ad_upsert(array(
                'shop_bo_table' => EOTTae_SHOP_TABLE,
                'shop_wr_id'    => $wr_id,
                'ad_title'      => $def['wr_subject'],
                'ad_subtitle'   => $def['wr_6'],
                'ad_cta'        => $def['wr_7'],
                'ad_link'       => $def['wr_link1'],
                'ad_image'      => $def['wr_link2'],
                'ad_maps_url'   => isset($def['maps_url']) ? $def['maps_url'] : '',
                'ad_lat'        => $def['wr_9'],
                'ad_lng'        => $def['wr_10'],
                'ad_active'     => 1,
                'ad_order'      => $idx + 1,
            ));
            $logs[] = 'OK '.$def['wr_subject'].' (shop='.$wr_id.', ad='.$ad_id.')';
        }

        return $logs;
    }
}
