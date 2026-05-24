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

        $shop = G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE;
        $items = array(
            array('홈', G5_URL.'/', 1),
            array('내주변', $shop, 2),
            array('맛집', $shop.'&sca='.urlencode('맛집'), 3),
            array('마사지', $shop.'&sfl=wr_1&stx='.urlencode('마사지'), 4),
            array('렌트카', $shop.'&sfl=wr_1&stx='.urlencode('렌트카'), 5),
            array('투어', $shop.'&sfl=wr_1&stx='.urlencode('투어'), 6),
            array('커뮤니티', G5_BBS_URL.'/board.php?bo_table='.EOTTae_COMMUNITY_TABLE, 7),
            array('MY', G5_URL.'/page/eottae-mypage.php', 8),
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
        $wr_seo_title = sql_escape_string(isset($data['wr_seo_title']) ? $data['wr_seo_title'] : 'eottae-'.md5($data['wr_subject']));
        $wr_hit = isset($data['wr_hit']) ? (int) $data['wr_hit'] : 0;
        $wr_comment = isset($data['wr_comment']) ? (int) $data['wr_comment'] : 0;
        $wr_datetime = isset($data['wr_datetime']) ? sql_escape_string($data['wr_datetime']) : G5_TIME_YMDHIS;

        $sql = " insert into {$write_table} set
            wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM {$write_table} as sq),
            wr_reply = '',
            wr_comment = '{$wr_comment}',
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
            wr_datetime = '{$wr_datetime}',
            wr_last = '{$wr_datetime}',
            wr_ip = '127.0.0.1',
            wr_hit = '{$wr_hit}',
            wr_1 = '', wr_2 = '', wr_3 = '', wr_4 = '', wr_5 = '',
            wr_6 = '', wr_7 = '', wr_8 = '', wr_9 = '', wr_10 = '' ";
        sql_query($sql);

        $wr_id = sql_insert_id();
        sql_query(" update {$write_table} set wr_parent = '{$wr_id}' where wr_id = '{$wr_id}' ");
        sql_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id )
            values ( '{$bo_table}', '{$wr_id}', '{$wr_id}', '{$wr_datetime}', '{$mb_id}' ) ");
        sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write + 1 where bo_table = '{$bo_table}' ");

        return eottae_seed_log('community', $data['wr_subject'].' created');
    }
}

if (!function_exists('eottae_seed_community_datetime')) {
    function eottae_seed_community_datetime($offset_seconds = 0)
    {
        return date('Y-m-d H:i:s', G5_SERVER_TIME + (int) $offset_seconds);
    }
}

if (!function_exists('eottae_seed_get_sample_community_posts')) {
    function eottae_seed_get_sample_community_posts()
    {
        $img = 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&q=80&w=800';
        $img2 = 'https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&q=80&w=800';
        $img3 = 'https://images.unsplash.com/photo-1544365558-35aa4afcf11f?auto=format&fit=crop&q=80&w=800';

        return array(
            array(
                'wr_subject'   => '오늘 막탄 날씨 너무 좋네요~ 호핑 가시는 분들 부럽습니다',
                'ca_name'      => '자유',
                'wr_content'   => '<p>막탄 쪽 하늘이 맑아서 바다색이 정말 예쁩니다. 오늘 호핑 나가시는 분들 후기 기대할게요!</p>',
                'wr_hit'       => 342,
                'wr_comment'   => 12,
                'wr_datetime'  => eottae_seed_community_datetime(-7200),
            ),
            array(
                'wr_subject'   => 'IT Park 근처에 늦게까지 하는 약국 있나요?',
                'ca_name'      => '질문',
                'wr_content'   => '<p>저녁 9시쯤까지 영업하는 약국 정보 아시는 분 계신가요? IT Park 또는 Ayala 쪽이면 좋겠습니다.</p>',
                'wr_hit'       => 128,
                'wr_comment'   => 5,
                'wr_datetime'  => eottae_seed_community_datetime(-10800),
            ),
            array(
                'wr_subject'   => '귀국 세일) 선풍기, 밥솥, 식탁 일괄 처분합니다',
                'ca_name'      => '정보',
                'wr_content'   => '<p>12월 귀국 예정이라 가전·가구 일괄 처분합니다. 막탄 Newtown 픽업 가능, DM 주세요.</p>',
                'wr_hit'       => 512,
                'wr_comment'   => 24,
                'wr_datetime'  => eottae_seed_community_datetime(-18000),
            ),
            array(
                'wr_subject'   => '아얄라 루스탄스 근처 1베드룸 (1년 계약) 양도합니다',
                'ca_name'      => '후기',
                'wr_content'   => '<p>루스탄스 Residences 1BR 계약 승계합니다. 관리비·보증금 조건은 댓글 또는 쪽지로 문의해 주세요.</p>',
                'wr_hit'       => 245,
                'wr_comment'   => 8,
                'wr_datetime'  => eottae_seed_community_datetime(-86400),
            ),
            array(
                'wr_subject'   => '새로 오픈한 만다우에 고깃집 다녀왔습니다 (내돈내산)',
                'ca_name'      => '후기',
                'wr_content'   => '<p>만다우에 쪽 신규 한식 고깃집 후기입니다. 고기 질은 괜찮고 밑반찬 리필이 빨라서 만족했어요.</p>',
                'wr_hit'       => 420,
                'wr_comment'   => 18,
                'wr_datetime'  => eottae_seed_community_datetime(-90000),
            ),
            array(
                'wr_subject'   => '오늘 아얄라몰 환율 얼마나 되나요?',
                'ca_name'      => '자유',
                'wr_content'   => '<p>오후에 환전하려는데 아얄라몰 환전소 기준 대략 얼마인지 공유 부탁드립니다.</p>',
                'wr_hit'       => 45,
                'wr_comment'   => 3,
                'wr_datetime'  => eottae_seed_community_datetime(-600),
            ),
            array(
                'wr_subject'   => '마리바고 블루워터 근처 맛집 추천좀요',
                'ca_name'      => '질문',
                'wr_content'   => '<p>주말에 블루워터 쪽 방문 예정인데 근처에서 저녁 먹기 좋은 곳 추천 부탁드려요.</p>',
                'wr_hit'       => 22,
                'wr_comment'   => 1,
                'wr_datetime'  => eottae_seed_community_datetime(-900),
            ),
            array(
                'wr_subject'   => '아이폰 13 프로 맥스 팝니다 (교민장터)',
                'ca_name'      => '정보',
                'wr_content'   => '<p>배터리 87%, 케이스·강화유리 포함 45000페소. 세부시티 직거래 가능합니다.</p>',
                'wr_hit'       => 15,
                'wr_comment'   => 0,
                'wr_datetime'  => eottae_seed_community_datetime(-1800),
            ),
            array(
                'wr_subject'   => '막탄 뉴타운 스튜디오 6개월 단기 렌트',
                'ca_name'      => '후기',
                'wr_content'   => '<p>6개월 단기 계약 가능한 스튜디오 정보 공유합니다. 관리비 포함 여부는 개별 문의 바랍니다.</p>',
                'wr_hit'       => 88,
                'wr_comment'   => 2,
                'wr_datetime'  => eottae_seed_community_datetime(-3600),
            ),
            array(
                'wr_subject'   => '막탄 호텔 프런트 한국어 가능 스태프 구합니다',
                'ca_name'      => '구인구직',
                'wr_content'   => '<p>막탄 4성급 호텔 프런트 데스크 한국어 가능 인력 채용. 비자 조건·급여는 면접 시 안내드립니다.</p>',
                'wr_hit'       => 156,
                'wr_comment'   => 6,
                'wr_datetime'  => eottae_seed_community_datetime(-14400),
            ),
            array(
                'wr_subject'   => '세부 10월 날씨 어떤가요? 건기인지 우기인지 궁금해요',
                'ca_name'      => '정보',
                'wr_content'   => '<p>10월 방문 예정인데 우기 말미인지, 비가 자주 오는지 경험담 공유 부탁드립니다.</p>',
                'wr_hit'       => 42,
                'wr_comment'   => 3,
                'wr_datetime'  => eottae_seed_community_datetime(-600),
            ),
            array(
                'wr_subject'   => '막탄 괜찮은 환전소 추천 부탁드립니다 (샹스몰 근처)',
                'ca_name'      => '정보',
                'wr_content'   => '<p>샹스몰·세부시티 사이 환전소 중 수수료 괜찮은 곳 추천해 주세요.</p>',
                'wr_hit'       => 180,
                'wr_comment'   => 12,
                'wr_datetime'  => eottae_seed_community_datetime(-3600),
            ),
            array(
                'wr_subject'   => '오슬롭 고래상어 투어 다녀왔습니다',
                'ca_name'      => '후기',
                'wr_content'   => '<p><img src="'.$img.'" alt="오슬롭 투어"></p><p>새벽 출발이 힘들었지만 고래상어 스노클링은 정말 인생샷이었습니다. 주의사항 꼭 확인하세요.</p>',
                'wr_hit'       => 120,
                'wr_comment'   => 4,
                'wr_datetime'  => eottae_seed_community_datetime(-5400),
            ),
            array(
                'wr_subject'   => '세부시티 야경 명소 탑스힐',
                'ca_name'      => '후기',
                'wr_content'   => '<p><img src="'.$img2.'" alt="탑스힐 야경"></p><p>일몰 30분 전 도착 추천. 카페 테라스 자리가 빨리 찹니다.</p>',
                'wr_hit'       => 85,
                'wr_comment'   => 2,
                'wr_datetime'  => eottae_seed_community_datetime(-10800),
            ),
            array(
                'wr_subject'   => '막탄 샹그릴라 프라이빗 비치',
                'ca_name'      => '후기',
                'wr_content'   => '<p><img src="'.$img3.'" alt="샹그릴라 비치"></p><p>비수기라 한적했고 수질도 좋았습니다. 데이유즈 패키지 가성비 괜찮아요.</p>',
                'wr_hit'       => 240,
                'wr_comment'   => 7,
                'wr_datetime'  => eottae_seed_community_datetime(-86400),
            ),
        );
    }
}

if (!function_exists('eottae_seed_community_samples_run')) {
    function eottae_seed_community_samples_run()
    {
        $logs = array();
        foreach (eottae_seed_get_sample_community_posts() as $post) {
            $logs[] = eottae_seed_insert_community_post($post);
        }

        if (function_exists('run_event')) {
            run_event('cache_delete', 'board');
        }

        return $logs;
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
            array(
                'wr_subject'  => 'Cebu Mango Cafe IT Park',
                'ca_name'     => '카페',
                'wr_content'  => '<p>IT Park 직장인·교민이 자주 찾는 디저트 카페입니다. 망고 쉐이크·마카롱·브런치 메뉴가 인기입니다.</p>',
                'wr_1'        => '카페',
                'wr_2'        => 'IT Park',
                'wr_3'        => 'Garden Bloc, Cebu IT Park, Lahug',
                'wr_4'        => '032-555-0101',
                'wr_5'        => 'shop-mango-cafe',
                'wr_6'        => '08:00 - 22:00',
                'wr_7'        => '',
                'wr_8'        => '영업중',
                'wr_9'        => '10.3255',
                'wr_10'       => '123.9082',
            ),
            array(
                'wr_subject'  => 'Mactan Korean Spa & Massage',
                'ca_name'     => '마사지',
                'wr_content'  => '<p>막탄·ラパラプ시 인근 한국식 스파·마사지샵입니다. 발마사지·전신 아로마 코스를 제공합니다.</p>',
                'wr_1'        => '마사지',
                'wr_2'        => 'Mactan',
                'wr_3'        => 'Mactan Newtown, Lapu-Lapu City',
                'wr_4'        => '032-555-0202',
                'wr_5'        => 'shop-mactan-spa',
                'wr_6'        => '10:00 - 23:00',
                'wr_7'        => '',
                'wr_8'        => '영업중',
                'wr_9'        => '10.3134',
                'wr_10'       => '123.9494',
            ),
            array(
                'wr_subject'  => 'Banana Leaf Filipino Kitchen Ayala',
                'ca_name'     => '맛집',
                'wr_content'  => '<p>필리핀 로컬 요리와 세부 해산물을 즐길 수 있는 레스토랑입니다. 가족 단위 외식·현지인 추천 메뉴가 풍부합니다.</p>',
                'wr_1'        => '맛집',
                'wr_2'        => 'Ayala',
                'wr_3'        => 'Level 1, Ayala Center Cebu, Cebu City',
                'wr_4'        => '032-555-0303',
                'wr_5'        => 'shop-banana-leaf',
                'wr_6'        => '11:00 - 21:30',
                'wr_7'        => '',
                'wr_8'        => '영업중',
                'wr_9'        => '10.3188',
                'wr_10'       => '123.9064',
            ),
            array(
                'wr_subject'  => 'SM Seaside Korean Grocery',
                'ca_name'     => '마트',
                'wr_content'  => '<p>SM Seaside City 인근 한국 식료품·냉동식품 전문 마트입니다. 주말 장보기·교민 생필품 구매에 편리합니다.</p>',
                'wr_1'        => '마트',
                'wr_2'        => 'SM Seaside',
                'wr_3'        => 'South Road Properties, Cebu City',
                'wr_4'        => '032-555-0404',
                'wr_5'        => 'shop-sm-seaside-mart',
                'wr_6'        => '09:30 - 21:00',
                'wr_7'        => '연중무휴',
                'wr_8'        => '영업중',
                'wr_9'        => '10.2986',
                'wr_10'       => '123.8801',
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

if (!function_exists('eottae_seed_sample_events')) {
    function eottae_seed_sample_events()
    {
        global $g5;

        $bo_table = defined('EOTTae_EVENT_TABLE') ? EOTTae_EVENT_TABLE : 'event';
        $row = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = '{$bo_table}' ");
        if (empty($row['cnt'])) {
            return array(eottae_seed_log('event', 'event board missing', false));
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $logs = array();
        $samples = array(
            array(
                'subject' => '신규 가입 웰컴 — 첫 리뷰 작성 이벤트',
                'category' => '진행중',
                'content' => '<p>세부어때에 가입하고 첫 업체 리뷰를 작성해 주세요. 포인트 자동 지급은 3차 업데이트 예정입니다.</p>',
            ),
            array(
                'subject' => 'IT Park 맛집 주말 할인 프로모션',
                'category' => '진행중',
                'content' => '<p>IT Park 인근 제휴 맛집에서 주말 한정 할인 혜택을 준비 중입니다.</p>',
            ),
        );

        foreach ($samples as $sample) {
            $subject = sql_escape_string($sample['subject']);
            $exists = sql_fetch(" select wr_id from {$write_table} where wr_subject = '{$subject}' limit 1 ");
            if (!empty($exists['wr_id'])) {
                $logs[] = eottae_seed_log('event', $sample['subject'].' already exists', true);
                continue;
            }

            $content = sql_escape_string($sample['content']);
            $ca_name = sql_escape_string($sample['category']);
            sql_query(" insert into {$write_table} set
                wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM {$write_table} as sq),
                wr_reply = '', wr_comment = 0, ca_name = '{$ca_name}',
                wr_option = 'html1', wr_subject = '{$subject}', wr_content = '{$content}',
                mb_id = 'admin', wr_password = '', wr_name = '세부어때',
                wr_datetime = '".G5_TIME_YMDHIS."', wr_last = '".G5_TIME_YMDHIS."',
                wr_ip = '127.0.0.1', wr_1 = '', wr_2 = '', wr_3 = '', wr_4 = '', wr_5 = '',
                wr_6 = '', wr_7 = '', wr_8 = '', wr_9 = '', wr_10 = '' ");
            $wr_id = sql_insert_id();
            sql_query(" update {$write_table} set wr_parent = '{$wr_id}' where wr_id = '{$wr_id}' ");
            sql_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id )
                values ( '{$bo_table}', '{$wr_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', 'admin' ) ");
            sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write + 1 where bo_table = '{$bo_table}' ");
            $logs[] = eottae_seed_log('event', $sample['subject'].' created');
        }

        return $logs;
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

if (!function_exists('eottae_seed_youtube_video_id')) {
    function eottae_seed_youtube_video_id($url)
    {
        if (!function_exists('g5b_youtube_id_from_url')) {
            include_once G5_SKIN_PATH.'/board/_inc/g5b-youtube.php';
        }

        return g5b_youtube_id_from_url($url);
    }
}

if (!function_exists('eottae_seed_youtube_exists')) {
    function eottae_seed_youtube_exists($video_id)
    {
        global $g5;

        $video_id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $video_id);
        if ($video_id === '') {
            return false;
        }

        $bo_table = defined('EOTTae_YOUTUBE_TABLE') ? EOTTae_YOUTUBE_TABLE : 'youtube';
        $write_table = $g5['write_prefix'].$bo_table;
        $like = sql_escape_string('%'.$video_id.'%');
        $row = sql_fetch(" select wr_id from {$write_table} where wr_1 like '{$like}' limit 1 ");

        return !empty($row['wr_id']);
    }
}

if (!function_exists('eottae_seed_youtube_oembed')) {
    function eottae_seed_youtube_oembed($watch_url)
    {
        $watch_url = trim((string) $watch_url);
        if ($watch_url === '') {
            return null;
        }

        $api = 'https://www.youtube.com/oembed?format=json&url='.rawurlencode($watch_url);
        $ctx = stream_context_create(array(
            'http' => array(
                'timeout' => 8,
                'ignore_errors' => true,
                'header' => "User-Agent: thecebu-seed/1.0\r\n",
            ),
        ));
        $raw = @file_get_contents($api, false, $ctx);
        if ($raw === false || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : null;
    }
}

if (!function_exists('eottae_seed_insert_youtube')) {
    function eottae_seed_insert_youtube($url, $data = array())
    {
        global $g5;

        $video_id = eottae_seed_youtube_video_id($url);
        if ($video_id === '') {
            return eottae_seed_log('youtube', 'invalid url: '.$url, false);
        }

        if (eottae_seed_youtube_exists($video_id)) {
            return eottae_seed_log('youtube', $video_id.' already exists', true);
        }

        $watch_url = 'https://www.youtube.com/watch?v='.$video_id;
        $oembed = eottae_seed_youtube_oembed($watch_url);

        $title = isset($data['wr_subject']) ? $data['wr_subject'] : '';
        if ($title === '' && is_array($oembed) && !empty($oembed['title'])) {
            $title = $oembed['title'];
        }
        if ($title === '') {
            $title = 'YouTube '.$video_id;
        }

        $summary = isset($data['wr_2']) ? $data['wr_2'] : '';
        if ($summary === '' && is_array($oembed) && !empty($oembed['author_name'])) {
            $summary = $oembed['author_name'].' 채널';
        }

        $content = isset($data['wr_content']) ? $data['wr_content'] : $title;
        if ($content === '') {
            $content = $title;
        }

        $bo_table = defined('EOTTae_YOUTUBE_TABLE') ? EOTTae_YOUTUBE_TABLE : 'youtube';
        $write_table = $g5['write_prefix'].$bo_table;

        $subject = sql_escape_string($title);
        $content_sql = sql_escape_string($content);
        $ca_name = sql_escape_string(isset($data['ca_name']) ? $data['ca_name'] : '정보');
        $wr_1 = sql_escape_string($watch_url);
        $wr_2 = sql_escape_string($summary);
        $mb_id = sql_escape_string(isset($data['mb_id']) ? $data['mb_id'] : 'admin');
        $wr_name = sql_escape_string(isset($data['wr_name']) ? $data['wr_name'] : '세부어때');
        $wr_seo_title = sql_escape_string('yt-'.$video_id);

        $sql = " insert into {$write_table} set
            wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM {$write_table} as sq),
            wr_reply = '',
            wr_comment = 0,
            ca_name = '{$ca_name}',
            wr_option = 'html1',
            wr_subject = '{$subject}',
            wr_content = '{$content_sql}',
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
            wr_hit = 0,
            wr_1 = '{$wr_1}',
            wr_2 = '{$wr_2}',
            wr_3 = '', wr_4 = '', wr_5 = '',
            wr_6 = '', wr_7 = '', wr_8 = '', wr_9 = '', wr_10 = '' ";
        sql_query($sql);

        $wr_id = sql_insert_id();
        sql_query(" update {$write_table} set wr_parent = '{$wr_id}' where wr_id = '{$wr_id}' ");
        sql_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id )
            values ( '{$bo_table}', '{$wr_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', '{$mb_id}' ) ");
        sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write + 1 where bo_table = '{$bo_table}' ");

        return eottae_seed_log('youtube', $title.' ('.$video_id.') created wr_id='.$wr_id);
    }
}

if (!function_exists('eottae_seed_youtube_urls')) {
    function eottae_seed_youtube_urls($urls)
    {
        $logs = array();
        foreach ($urls as $url) {
            $logs[] = eottae_seed_insert_youtube($url);
        }

        return $logs;
    }
}

if (!function_exists('eottae_seed_gallery_table')) {
    function eottae_seed_gallery_table()
    {
        return defined('EOTTae_GALLERY_TABLE') ? EOTTae_GALLERY_TABLE : 'gallery';
    }
}

if (!function_exists('eottae_seed_gallery_board_ready')) {
    function eottae_seed_gallery_board_ready()
    {
        global $g5;

        $bo_table = eottae_seed_gallery_table();
        $row = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = '".sql_escape_string($bo_table)."' ");

        return !empty($row['cnt']);
    }
}

if (!function_exists('eottae_seed_gallery_exists')) {
    function eottae_seed_gallery_exists($subject)
    {
        global $g5;

        $bo_table = eottae_seed_gallery_table();
        $write_table = $g5['write_prefix'].$bo_table;
        $subject = sql_escape_string($subject);
        $row = sql_fetch(" select wr_id from {$write_table} where wr_subject = '{$subject}' and wr_is_comment = 0 limit 1 ");

        return !empty($row['wr_id']);
    }
}

if (!function_exists('eottae_seed_fetch_remote_file')) {
    function eottae_seed_fetch_remote_file($url, $dest_path)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return false;
        }

        $ctx = stream_context_create(array(
            'http' => array(
                'timeout' => 30,
                'user_agent' => 'thecebu-seed/1.0',
                'follow_location' => 1,
            ),
            'ssl' => array(
                'verify_peer' => true,
                'verify_peer_name' => true,
            ),
        ));

        $bytes = @file_get_contents($url, false, $ctx);
        if ($bytes === false || $bytes === '') {
            return false;
        }

        return @file_put_contents($dest_path, $bytes) !== false;
    }
}

if (!function_exists('eottae_seed_attach_board_image')) {
    function eottae_seed_attach_board_image($bo_table, $wr_id, $source_path, $source_name)
    {
        global $g5;

        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || !is_file($source_path)) {
            return false;
        }

        $bo_table = sql_escape_string($bo_table);
        $exists = sql_fetch(" select bf_no from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = '0' limit 1 ");
        if (is_array($exists) && isset($exists['bf_no'])) {
            return true;
        }

        $dest_dir = G5_DATA_PATH.'/file/'.$bo_table;
        if (!is_dir($dest_dir)) {
            @mkdir($dest_dir, G5_DIR_PERMISSION, true);
        }

        $ext = strtolower(pathinfo($source_path, PATHINFO_EXTENSION));
        if ($ext === '') {
            $ext = 'jpg';
        }
        $bf_file = md5(uniqid((string) mt_rand(), true)).'.'.$ext;
        if (!@copy($source_path, $dest_dir.'/'.$bf_file)) {
            return false;
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

        return true;
    }
}

if (!function_exists('eottae_seed_gallery_get_wr_id')) {
    function eottae_seed_gallery_get_wr_id($subject)
    {
        global $g5;

        $bo_table = eottae_seed_gallery_table();
        $write_table = $g5['write_prefix'].$bo_table;
        $subject = sql_escape_string($subject);
        $row = sql_fetch(" select wr_id, wr_file from {$write_table} where wr_subject = '{$subject}' and wr_is_comment = 0 limit 1 ");

        return is_array($row) ? $row : null;
    }
}

if (!function_exists('eottae_seed_insert_gallery')) {
    function eottae_seed_insert_gallery($data)
    {
        global $g5;

        if (!eottae_seed_gallery_board_ready()) {
            return eottae_seed_log('gallery', 'gallery board missing — run install first', false);
        }

        $bo_table = eottae_seed_gallery_table();
        $write_table = $g5['write_prefix'].$bo_table;
        $image_url = isset($data['image_url']) ? trim((string) $data['image_url']) : '';
        if ($image_url === '') {
            return eottae_seed_log('gallery', $data['wr_subject'].' missing image_url', false);
        }

        $existing = eottae_seed_gallery_get_wr_id($data['wr_subject']);
        if (is_array($existing) && !empty($existing['wr_id'])) {
            if ((int) $existing['wr_file'] > 0) {
                return eottae_seed_log('gallery', $data['wr_subject'].' already exists', true);
            }
            $wr_id = (int) $existing['wr_id'];
        } else {
            $subject = sql_escape_string($data['wr_subject']);
            $content = sql_escape_string(isset($data['wr_content']) ? $data['wr_content'] : '<p>'.get_text($data['wr_subject']).'</p>');
            $ca_name = sql_escape_string(isset($data['ca_name']) ? $data['ca_name'] : '풍경');
            $mb_id = sql_escape_string('admin');
            $wr_name = sql_escape_string('세부어때');
            $wr_seo_title = sql_escape_string('gallery-'.md5($data['wr_subject']));
            $wr_hit = isset($data['wr_hit']) ? (int) $data['wr_hit'] : mt_rand(20, 400);
            $wr_datetime = isset($data['wr_datetime']) ? sql_escape_string($data['wr_datetime']) : G5_TIME_YMDHIS;

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
                wr_datetime = '{$wr_datetime}',
                wr_last = '{$wr_datetime}',
                wr_ip = '127.0.0.1',
                wr_hit = '{$wr_hit}',
                wr_1 = '', wr_2 = '', wr_3 = '', wr_4 = '', wr_5 = '',
                wr_6 = '', wr_7 = '', wr_8 = '', wr_9 = '', wr_10 = '' ";
            sql_query($sql);

            $wr_id = sql_insert_id();
            sql_query(" update {$write_table} set wr_parent = '{$wr_id}' where wr_id = '{$wr_id}' ");
            sql_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id )
                values ( '{$bo_table}', '{$wr_id}', '{$wr_id}', '{$wr_datetime}', '{$mb_id}' ) ");
            sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write + 1 where bo_table = '{$bo_table}' ");
        }

        $tmp = G5_DATA_PATH.'/tmp/gallery-seed-'.md5($image_url).'.jpg';
        if (!is_dir(G5_DATA_PATH.'/tmp')) {
            @mkdir(G5_DATA_PATH.'/tmp', G5_DIR_PERMISSION, true);
        }
        if (!eottae_seed_fetch_remote_file($image_url, $tmp)) {
            return eottae_seed_log('gallery', $data['wr_subject'].' image download failed', false);
        }

        $source_name = preg_replace('/[^a-zA-Z0-9._-]+/', '-', $data['wr_subject']).'.jpg';
        if (!eottae_seed_attach_board_image($bo_table, $wr_id, $tmp, $source_name)) {
            @unlink($tmp);

            return eottae_seed_log('gallery', $data['wr_subject'].' image attach failed', false);
        }
        @unlink($tmp);

        return eottae_seed_log('gallery', $data['wr_subject'].' created wr_id='.$wr_id);
    }
}

if (!function_exists('eottae_seed_get_sample_gallery_posts')) {
    function eottae_seed_get_sample_gallery_posts()
    {
        $img = function ($seed) {
            return 'https://picsum.photos/seed/cebu-gallery-'.$seed.'/900/900';
        };

        $samples = array(
            array('wr_subject' => '오슬롭 고래상어 스노클링', 'ca_name' => '풍경', 'image_url' => $img('01')),
            array('wr_subject' => '막탄 해변 석양', 'ca_name' => '풍경', 'image_url' => $img('02')),
            array('wr_subject' => '카와asan 폭포 트레킹', 'ca_name' => '풍경', 'image_url' => $img('03')),
            array('wr_subject' => '세부 시티 야경', 'ca_name' => '풍경', 'image_url' => $img('04')),
            array('wr_subject' => '모알보알 비치 리조트', 'ca_name' => '풍경', 'image_url' => $img('05')),
            array('wr_subject' => '탑스 힐 전망대', 'ca_name' => '풍경', 'image_url' => $img('06')),
            array('wr_subject' => '나팔링 스쿠버다이빙', 'ca_name' => '풍경', 'image_url' => $img('07')),
            array('wr_subject' => '반탸얀 섬 호핑', 'ca_name' => '풍경', 'image_url' => $img('08')),
            array('wr_subject' => '마젤란 십자가', 'ca_name' => '일상', 'image_url' => $img('09')),
            array('wr_subject' => '산 페드로 요새', 'ca_name' => '일상', 'image_url' => $img('10')),
            array('wr_subject' => 'IT Park 저녁 풍경', 'ca_name' => '일상', 'image_url' => $img('11')),
            array('wr_subject' => '아얄라 몰 주말', 'ca_name' => '일상', 'image_url' => $img('12')),
            array('wr_subject' => '세부 로컬 시장', 'ca_name' => '일상', 'image_url' => $img('13')),
            array('wr_subject' => '막탄 새벽 해변 산책', 'ca_name' => '일상', 'image_url' => $img('14')),
            array('wr_subject' => 'JPark 리조트 풀', 'ca_name' => '풍경', 'image_url' => $img('15')),
            array('wr_subject' => '세부 한식당 비빔밥', 'ca_name' => '맛집', 'image_url' => $img('16')),
            array('wr_subject' => '막탄 해산물 레스토랑', 'ca_name' => '맛집', 'image_url' => $img('17')),
            array('wr_subject' => '세부 로컬 카페', 'ca_name' => '맛집', 'image_url' => $img('18')),
            array('wr_subject' => '필리핀 BBQ 디너', 'ca_name' => '맛집', 'image_url' => $img('19')),
            array('wr_subject' => '망고 스무디 한 잔', 'ca_name' => '맛집', 'image_url' => $img('20')),
            array('wr_subject' => '세부 교민 모임', 'ca_name' => '기타', 'image_url' => $img('21')),
            array('wr_subject' => '주말 골프 라운딩', 'ca_name' => '기타', 'image_url' => $img('22')),
            array('wr_subject' => '세부 국제학교 행사', 'ca_name' => '기타', 'image_url' => $img('23')),
            array('wr_subject' => '보홀 데이트립', 'ca_name' => '풍경', 'image_url' => $img('24')),
            array('wr_subject' => '초콜릿 힐스 전망', 'ca_name' => '풍경', 'image_url' => $img('25')),
            array('wr_subject' => '카나와an 해양 보호구역', 'ca_name' => '풍경', 'image_url' => $img('26')),
            array('wr_subject' => '세부 항공뷰', 'ca_name' => '풍경', 'image_url' => $img('27')),
            array('wr_subject' => '랑라스 섬 피크닉', 'ca_name' => '풍경', 'image_url' => $img('28')),
            array('wr_subject' => '세부 트라이시클', 'ca_name' => '일상', 'image_url' => $img('29')),
            array('wr_subject' => '비치 클럽 주말', 'ca_name' => '기타', 'image_url' => $img('30')),
        );

        $offset = 0;
        foreach ($samples as $idx => &$sample) {
            $sample['wr_datetime'] = eottae_seed_community_datetime(-3600 * ($idx + 1 + $offset));
            $sample['wr_content'] = '<p>'.get_text($sample['wr_subject']).' — 세부어때 갤러리 샘플</p>';
        }
        unset($sample);

        return $samples;
    }
}

if (!function_exists('eottae_seed_gallery_samples_run')) {
    function eottae_seed_gallery_samples_run()
    {
        $logs = array();
        foreach (eottae_seed_get_sample_gallery_posts() as $post) {
            $logs[] = eottae_seed_insert_gallery($post);
            usleep(200000);
        }

        return $logs;
    }
}
