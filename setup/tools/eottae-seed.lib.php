<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_seed_log')) {
    function eottae_seed_log($action, $message, $ok = true)
    {
        return array('ok' => $ok, 'action' => $action, 'message' => $message);
    }
}

if (!function_exists('eottae_seed_next_menu_code')) {
    function eottae_seed_next_menu_code($length = 2)
    {
        global $g5;

        if ($length === 2) {
            $row = sql_fetch(" select MAX(SUBSTRING(me_code,1,2)) as max_me_code from {$g5['menu_table']} where LENGTH(me_code) = '2' ");
            $num = isset($row['max_me_code']) && $row['max_me_code'] !== '' ? (int) base_convert($row['max_me_code'], 36, 10) : 0;
            $num += 36;

            return base_convert((string) $num, 10, 36);
        }

        return '10';
    }
}

if (!function_exists('eottae_seed_insert_menu')) {
    function eottae_seed_insert_menu($name, $link, $order, $target = 'self')
    {
        global $g5;

        $me_code = eottae_seed_next_menu_code();
        $me_name = sql_escape_string($name);
        $me_link = sql_escape_string($link);
        $me_target = sql_escape_string($target);
        $me_order = (int) $order;

        sql_query(" insert into {$g5['menu_table']} set
            me_code = '{$me_code}',
            me_name = '{$me_name}',
            me_link = '{$me_link}',
            me_target = '{$me_target}',
            me_order = '{$me_order}',
            me_use = '1',
            me_mobile_use = '1' ");

        return $me_code;
    }
}

if (!function_exists('eottae_seed_menus')) {
    function eottae_seed_menus($replace = true)
    {
        global $g5;

        $logs = array();

        if ($replace) {
            sql_query(" delete from {$g5['menu_table']} ");
            $logs[] = eottae_seed_log('menu', 'existing menus cleared');
        }

        $items = array(
            array('홈', G5_URL.'/', 1),
            array('내주변', G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE, 2),
            array('커뮤니티', G5_BBS_URL.'/board.php?bo_table='.EOTTae_COMMUNITY_TABLE, 3),
            array('MY', G5_URL.'/page/eottae-mypage.php', 4),
        );

        foreach ($items as $item) {
            $code = eottae_seed_insert_menu($item[0], $item[1], $item[2]);
            $logs[] = eottae_seed_log('menu', 'menu '.$item[0].' ('.$code.')');
        }

        return $logs;
    }
}

if (!function_exists('eottae_seed_shop_exists')) {
    function eottae_seed_shop_exists($subject)
    {
        global $g5;

        $write_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
        $subject = sql_escape_string($subject);
        $row = sql_fetch(" select wr_id from {$write_table} where wr_subject = '{$subject}' limit 1 ");

        return !empty($row['wr_id']);
    }
}

if (!function_exists('eottae_seed_insert_shop')) {
    function eottae_seed_insert_shop($data)
    {
        global $g5, $config;

        $bo_table = EOTTae_SHOP_TABLE;
        $write_table = $g5['write_prefix'].$bo_table;

        $subject = sql_escape_string($data['wr_subject']);
        if (eottae_seed_shop_exists($data['wr_subject'])) {
            return eottae_seed_log('shop', $data['wr_subject'].' already exists', true);
        }

        $content = sql_escape_string($data['wr_content']);
        $ca_name = sql_escape_string(isset($data['ca_name']) ? $data['ca_name'] : '맛집');

        $fields = array('wr_1', 'wr_2', 'wr_3', 'wr_4', 'wr_5', 'wr_6', 'wr_7', 'wr_8', 'wr_9', 'wr_10');
        $wr_extra = array();
        foreach ($fields as $f) {
            $wr_extra[$f] = sql_escape_string(isset($data[$f]) ? $data[$f] : '');
        }

        $wr_link1 = sql_escape_string(isset($data['wr_link1']) ? $data['wr_link1'] : '');
        $wr_link2 = sql_escape_string(isset($data['wr_link2']) ? $data['wr_link2'] : '');

        $mb_id = sql_escape_string(isset($data['mb_id']) ? $data['mb_id'] : 'admin');
        $wr_name = sql_escape_string(isset($data['wr_name']) ? $data['wr_name'] : '세부어때');
        $wr_email = sql_escape_string(isset($data['wr_email']) ? $data['wr_email'] : 'admin@thecebu.co.kr');
        $wr_seo_title = sql_escape_string(preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($data['wr_subject'])));

        $sql = " insert into {$write_table} set
            wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM {$write_table} as sq),
            wr_reply = '',
            wr_comment = 0,
            ca_name = '{$ca_name}',
            wr_option = 'html1',
            wr_subject = '{$subject}',
            wr_content = '{$content}',
            wr_seo_title = '{$wr_seo_title}',
            wr_link1 = '{$wr_link1}',
            wr_link2 = '{$wr_link2}',
            wr_link1_hit = 0,
            wr_link2_hit = 0,
            wr_hit = 0,
            wr_good = 0,
            wr_nogood = 0,
            mb_id = '{$mb_id}',
            wr_password = '',
            wr_name = '{$wr_name}',
            wr_email = '{$wr_email}',
            wr_homepage = '',
            wr_datetime = '".G5_TIME_YMDHIS."',
            wr_last = '".G5_TIME_YMDHIS."',
            wr_ip = '127.0.0.1',
            wr_1 = '{$wr_extra['wr_1']}',
            wr_2 = '{$wr_extra['wr_2']}',
            wr_3 = '{$wr_extra['wr_3']}',
            wr_4 = '{$wr_extra['wr_4']}',
            wr_5 = '{$wr_extra['wr_5']}',
            wr_6 = '{$wr_extra['wr_6']}',
            wr_7 = '{$wr_extra['wr_7']}',
            wr_8 = '{$wr_extra['wr_8']}',
            wr_9 = '{$wr_extra['wr_9']}',
            wr_10 = '{$wr_extra['wr_10']}' ";
        sql_query($sql);

        $wr_id = sql_insert_id();
        sql_query(" update {$write_table} set wr_parent = '{$wr_id}' where wr_id = '{$wr_id}' ");
        sql_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id )
            values ( '{$bo_table}', '{$wr_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', '{$mb_id}' ) ");
        sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write + 1 where bo_table = '{$bo_table}' ");

        return eottae_seed_log('shop', $data['wr_subject'].' created (wr_id='.$wr_id.')');
    }
}

if (!function_exists('eottae_seed_community_exists')) {
    function eottae_seed_community_exists($subject)
    {
        global $g5;

        $write_table = $g5['write_prefix'].EOTTae_COMMUNITY_TABLE;
        $subject = sql_escape_string($subject);
        $row = sql_fetch(" select wr_id from {$write_table} where wr_subject = '{$subject}' limit 1 ");

        return !empty($row['wr_id']);
    }
}

if (!function_exists('eottae_seed_insert_community_post')) {
    function eottae_seed_insert_community_post($data)
    {
        global $g5;

        $bo_table = EOTTae_COMMUNITY_TABLE;
        $write_table = $g5['write_prefix'].$bo_table;

        if (eottae_seed_community_exists($data['wr_subject'])) {
            return eottae_seed_log('community', $data['wr_subject'].' already exists', true);
        }

        $subject = sql_escape_string($data['wr_subject']);
        $content = sql_escape_string($data['wr_content']);
        $ca_name = sql_escape_string(isset($data['ca_name']) ? $data['ca_name'] : '정보');
        $mb_id = sql_escape_string('admin');
        $wr_name = sql_escape_string('세부어때');
        $wr_seo_title = sql_escape_string('welcome-eottae');

        $sql = " insert into {$write_table} set
            wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM {$write_table} as sq),
            wr_reply = '',
            wr_comment = 0,
            ca_name = '{$ca_name}',
            wr_option = 'html1',
            wr_subject = '{$subject}',
            wr_content = '{$content}',
            wr_seo_title = '{$wr_seo_title}',
            wr_link1 = '',
            wr_link2 = '',
            mb_id = '{$mb_id}',
            wr_password = '',
            wr_name = '{$wr_name}',
            wr_email = '',
            wr_homepage = '',
            wr_datetime = '".G5_TIME_YMDHIS."',
            wr_last = '".G5_TIME_YMDHIS."',
            wr_ip = '127.0.0.1',
            wr_1 = '', wr_2 = '', wr_3 = '', wr_4 = '', wr_5 = '',
            wr_6 = '', wr_7 = '', wr_8 = '', wr_9 = '', wr_10 = '' ";
        sql_query($sql);

        $wr_id = sql_insert_id();
        sql_query(" update {$write_table} set wr_parent = '{$wr_id}' where wr_id = '{$wr_id}' ");
        sql_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id )
            values ( '{$bo_table}', '{$wr_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', '{$mb_id}' ) ");
        sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write + 1 where bo_table = '{$bo_table}' ");

        return eottae_seed_log('community', $data['wr_subject'].' created');
    }
}

if (!function_exists('eottae_seed_get_sample_shops')) {
    function eottae_seed_get_sample_shops()
    {
        return array(
            array(
                'wr_subject'  => 'J Park Korean BBQ IT Park',
                'ca_name'     => '맛집',
                'wr_content'  => '<p>IT Park 인근에서 한국식 고기구이를 즐길 수 있는 인기 맛집입니다. 세부 교민들이 자주 찾는 대표 메뉴는 삼겹살·갈비 세트입니다.</p>',
                'wr_1'        => '맛집',
                'wr_2'        => 'IT Park',
                'wr_3'        => 'Cebu IT Park, Apas, Cebu City',
                'wr_4'        => '032-123-4567',
                'wr_5'        => 'shop-jpark-itp',
                'wr_6'        => '11:00 - 22:00',
                'wr_7'        => '연중무휴',
                'wr_8'        => '영업중',
                'wr_9'        => '10.3240',
                'wr_10'       => '123.9050',
                'wr_link1'    => '',
                'wr_link2'    => '',
            ),
            array(
                'wr_subject'  => 'Korean Mart Ayala Center Cebu',
                'ca_name'     => '마트',
                'wr_content'  => '<p>Ayala Center Cebu 지하·주변에서 한국 식재료·즉석식품을 구매할 수 있는 마트입니다. 김치·라면·한국 과자 등을 취급합니다.</p>',
                'wr_1'        => '마트',
                'wr_2'        => 'Ayala',
                'wr_3'        => 'Ayala Center Cebu, Cebu Business Park',
                'wr_4'        => '032-987-6543',
                'wr_5'        => 'shop-kmart-ayala',
                'wr_6'        => '10:00 - 21:00',
                'wr_7'        => '설·추석 당일 휴무',
                'wr_8'        => '영업중',
                'wr_9'        => '10.3175',
                'wr_10'       => '123.9056',
            ),
        );
    }
}

if (!function_exists('eottae_seed_get_shop_wr_id')) {
    function eottae_seed_get_shop_wr_id($subject)
    {
        global $g5;

        $write_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
        $subject = sql_escape_string($subject);
        $row = sql_fetch(" select wr_id from {$write_table} where wr_subject = '{$subject}' limit 1 ");

        return !empty($row['wr_id']) ? (int) $row['wr_id'] : 0;
    }
}

if (!function_exists('eottae_seed_attach_shop_image')) {
    function eottae_seed_attach_shop_image($subject, $source_rel, $source_name)
    {
        global $g5;

        $bo_table = EOTTae_SHOP_TABLE;
        $wr_id = eottae_seed_get_shop_wr_id($subject);
        if (!$wr_id) {
            return eottae_seed_log('image', $subject.' not found', false);
        }

        $src = G5_PATH.'/'.$source_rel;
        if (!is_file($src)) {
            return eottae_seed_log('image', 'missing file '.$source_rel, false);
        }

        $exists = sql_fetch(" select bf_no from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = '0' limit 1 ");
        if (is_array($exists) && isset($exists['bf_no'])) {
            return eottae_seed_log('image', $subject.' image already attached', true);
        }

        $dest_dir = G5_DATA_PATH.'/file/'.$bo_table;
        if (!is_dir($dest_dir)) {
            @mkdir($dest_dir, G5_DIR_PERMISSION, true);
        }

        $ext = pathinfo($src, PATHINFO_EXTENSION);
        if ($ext === '') {
            $ext = 'jpg';
        }
        $bf_file = md5(uniqid((string) mt_rand(), true)).'.'.strtolower($ext);
        if (!@copy($src, $dest_dir.'/'.$bf_file)) {
            return eottae_seed_log('image', 'copy failed for '.$subject, false);
        }

        $size = (int) filesize($dest_dir.'/'.$bf_file);
        $info = @getimagesize($dest_dir.'/'.$bf_file);
        $bf_width = isset($info[0]) ? (int) $info[0] : 0;
        $bf_height = isset($info[1]) ? (int) $info[1] : 0;
        $bf_type = isset($info[2]) ? (int) $info[2] : 0;
        $bf_source = sql_escape_string($source_name);

        sql_query(" insert into {$g5['board_file_table']} set
            bo_table = '{$bo_table}',
            wr_id = '{$wr_id}',
            bf_no = '0',
            bf_source = '{$bf_source}',
            bf_file = '{$bf_file}',
            bf_content = '',
            bf_fileurl = '',
            bf_thumburl = '',
            bf_storage = '',
            bf_download = 0,
            bf_filesize = '{$size}',
            bf_width = '{$bf_width}',
            bf_height = '{$bf_height}',
            bf_type = '{$bf_type}',
            bf_datetime = '".G5_TIME_YMDHIS."' ");

        $write_table = $g5['write_prefix'].$bo_table;
        sql_query(" update {$write_table} set wr_file = wr_file + 1 where wr_id = '{$wr_id}' ");

        return eottae_seed_log('image', $subject.' image attached (wr_id='.$wr_id.')');
    }
}

if (!function_exists('eottae_seed_attach_sample_images')) {
    function eottae_seed_attach_sample_images()
    {
        return array(
            eottae_seed_attach_shop_image(
                'J Park Korean BBQ IT Park',
                'img/eottae/shop-jpark-itp.jpg',
                'shop-jpark-itp.jpg'
            ),
            eottae_seed_attach_shop_image(
                'Korean Mart Ayala Center Cebu',
                'img/eottae/shop-kmart-ayala.jpg',
                'shop-kmart-ayala.jpg'
            ),
        );
    }
}

if (!function_exists('eottae_seed_review_board_exists')) {
    function eottae_seed_review_board_exists()
    {
        global $g5;
        $row = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = 'review' ");

        return !empty($row['cnt']);
    }
}

if (!function_exists('eottae_seed_insert_review')) {
    function eottae_seed_insert_review($data)
    {
        global $g5;

        if (!eottae_seed_review_board_exists()) {
            return eottae_seed_log('review', 'review board missing — run install first', false);
        }

        $bo_table = eottae_review_table();
        $write_table = $g5['write_prefix'].$bo_table;

        $shop_id = (int) $data['shop_wr_id'];
        $rating = (int) $data['rating'];
        $subject = sql_escape_string(isset($data['wr_subject']) ? $data['wr_subject'] : '['.$rating.'점] 리뷰');
        $content = sql_escape_string($data['wr_content']);
        $shop_name = sql_escape_string(isset($data['shop_name']) ? $data['shop_name'] : '');
        $mb_id = sql_escape_string(isset($data['mb_id']) ? $data['mb_id'] : 'admin');
        $wr_name = sql_escape_string(isset($data['wr_name']) ? $data['wr_name'] : '세부어때');

        $exists = sql_fetch(" select wr_id from {$write_table}
            where wr_is_comment = 0 and mb_id = '{$mb_id}' and wr_1 = '{$shop_id}' limit 1 ");
        if (!empty($exists['wr_id'])) {
            return eottae_seed_log('review', 'shop '.$shop_id.' review exists for '.$mb_id, true);
        }

        $sql = " insert into {$write_table} set
            wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM {$write_table} as sq),
            wr_reply = '',
            wr_comment = 0,
            ca_name = '',
            wr_option = '',
            wr_subject = '{$subject}',
            wr_content = '{$content}',
            wr_link1 = '', wr_link2 = '',
            mb_id = '{$mb_id}',
            wr_password = '',
            wr_name = '{$wr_name}',
            wr_email = '',
            wr_datetime = '".G5_TIME_YMDHIS."',
            wr_last = '".G5_TIME_YMDHIS."',
            wr_ip = '127.0.0.1',
            wr_1 = '{$shop_id}',
            wr_2 = '{$rating}',
            wr_3 = '{$shop_name}',
            wr_4 = 'visible',
            wr_5 = '0',
            wr_6 = '', wr_7 = '', wr_8 = '', wr_9 = '', wr_10 = '' ";
        sql_query($sql);

        $wr_id = sql_insert_id();
        sql_query(" update {$write_table} set wr_parent = '{$wr_id}' where wr_id = '{$wr_id}' ");
        sql_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id )
            values ( '{$bo_table}', '{$wr_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', '{$mb_id}' ) ");
        sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write + 1 where bo_table = '{$bo_table}' ");

        return eottae_seed_log('review', 'shop '.$shop_id.' review seeded (wr_id='.$wr_id.')');
    }
}

if (!function_exists('eottae_seed_sample_reviews')) {
    function eottae_seed_sample_reviews()
    {
        return array(
            eottae_seed_insert_review(array(
                'shop_wr_id'  => 1,
                'shop_name'   => 'J Park Korean BBQ IT Park',
                'rating'      => 5,
                'wr_subject'  => '[5점] J Park Korean BBQ IT Park 리뷰',
                'wr_content'  => 'IT Park에서 한식 고기 먹기 좋아요. 직원분들도 친절하고 양도 푸짐합니다.',
                'mb_id'       => 'seed_review1',
                'wr_name'     => '세부교민A',
            )),
            eottae_seed_insert_review(array(
                'shop_wr_id'  => 1,
                'shop_name'   => 'J Park Korean BBQ IT Park',
                'rating'      => 4,
                'wr_subject'  => '[4점] J Park Korean BBQ IT Park 리뷰',
                'wr_content'  => '맛은 좋은데 주말 저녁은 웨이팅이 있어요. 평일 점심 추천합니다.',
                'mb_id'       => 'seed_review2',
                'wr_name'     => '맛집탐험가',
            )),
            eottae_seed_insert_review(array(
                'shop_wr_id'  => 2,
                'shop_name'   => 'Korean Mart Ayala Center Cebu',
                'rating'      => 5,
                'wr_subject'  => '[5점] Korean Mart Ayala Center Cebu 리뷰',
                'wr_content'  => '김치·라면·과자까지 한국 식재료가 잘 갖춰져 있어요. Ayala 쇼핑 후 들르기 좋습니다.',
                'mb_id'       => 'seed_review3',
                'wr_name'     => '장보기왕',
            )),
        );
    }
}

if (!function_exists('eottae_seed_run')) {
    function eottae_seed_run()
    {
        $logs = eottae_seed_menus(true);

        foreach (eottae_seed_get_sample_shops() as $shop) {
            $logs[] = eottae_seed_insert_shop($shop);
        }

        $logs[] = eottae_seed_insert_community_post(array(
            'wr_subject' => '세부어때 커뮤니티에 오신 것을 환영합니다',
            'ca_name'    => '공지',
            'wr_content' => '<p>세부 생활 정보·맛집·업체 추천을 나누는 공간입니다. 이용 규칙을 확인하고 즐겁게 참여해 주세요.</p>',
        ));

        if (function_exists('run_event')) {
            run_event('cache_delete', 'menu');
            run_event('cache_delete', 'board');
        }

        return $logs;
    }
}
