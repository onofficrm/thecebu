<?php
/**
 * 세부어때 1차 DB/게시판 설치 헬퍼
 * setup/tools/eottae-install.php 에서만 include
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_install_create_write_table')) {
    function eottae_install_create_write_table($bo_table)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', $bo_table);
        if ($bo_table === '') {
            return false;
        }

        $create_table = $g5['write_prefix'].$bo_table;
        $exists = sql_fetch(" SHOW TABLES LIKE '{$create_table}' ");
        if (!empty($exists)) {
            return true;
        }

        $file = file(G5_ADMIN_PATH.'/sql_write.sql');
        if (!$file) {
            return false;
        }

        if (function_exists('get_db_create_replace')) {
            $file = get_db_create_replace($file);
        }

        $sql = implode("\n", $file);
        $sql = preg_replace(array('/__TABLE_NAME__/', '/;/'), array($create_table, ''), $sql);

        return (bool) sql_query($sql, false);
    }
}

if (!function_exists('eottae_install_board_exists')) {
    function eottae_install_board_exists($bo_table)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', $bo_table);
        $row = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = '{$bo_table}' ");

        return !empty($row['cnt']);
    }
}

if (!function_exists('eottae_install_create_board')) {
    function eottae_install_create_board($def)
    {
        global $g5, $config;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', $def['bo_table']);
        if ($bo_table === '') {
            return array('ok' => false, 'action' => 'error', 'message' => 'bo_table invalid');
        }

        if (eottae_install_board_exists($bo_table)) {
            return array('ok' => true, 'action' => 'skip', 'message' => $bo_table.' already exists');
        }

        $gr_id = isset($def['gr_id']) ? sql_escape_string($def['gr_id']) : 'community';
        $bo_subject = sql_escape_string($def['bo_subject']);
        $bo_skin = sql_escape_string($def['bo_skin']);
        $bo_mobile_skin = sql_escape_string(isset($def['bo_mobile_skin']) ? $def['bo_mobile_skin'] : $def['bo_skin']);
        $bo_read_level = (int) (isset($def['bo_read_level']) ? $def['bo_read_level'] : 1);
        $bo_write_level = (int) (isset($def['bo_write_level']) ? $def['bo_write_level'] : 2);
        $bo_comment_level = (int) (isset($def['bo_comment_level']) ? $def['bo_comment_level'] : 2);
        $bo_use_category = (int) (isset($def['bo_use_category']) ? $def['bo_use_category'] : 0);
        $bo_category_list = sql_escape_string(isset($def['bo_category_list']) ? $def['bo_category_list'] : '');
        $bo_use_secret = (int) (isset($def['bo_use_secret']) ? $def['bo_use_secret'] : 0);
        $bo_upload_count = (int) (isset($def['bo_upload_count']) ? $def['bo_upload_count'] : 5);
        $bo_upload_size = (int) (isset($def['bo_upload_size']) ? $def['bo_upload_size'] : 10485760);
        $bo_use_dhtml_editor = (int) (isset($def['bo_use_dhtml_editor']) ? $def['bo_use_dhtml_editor'] : 1);
        $bo_order = (int) (isset($def['bo_order']) ? $def['bo_order'] : 0);

        $extra_subj_sql = '';
        for ($i = 1; $i <= 10; $i++) {
            $key = 'bo_'.$i.'_subj';
            $val = isset($def[$key]) ? sql_escape_string($def[$key]) : '';
            $extra_subj_sql .= ", bo_{$i}_subj = '{$val}' ";
        }

        $sql = " insert into {$g5['board_table']} set
            bo_table = '{$bo_table}',
            gr_id = '{$gr_id}',
            bo_subject = '{$bo_subject}',
            bo_mobile_subject = '{$bo_subject}',
            bo_device = 'both',
            bo_admin = '',
            bo_list_level = '1',
            bo_read_level = '{$bo_read_level}',
            bo_write_level = '{$bo_write_level}',
            bo_reply_level = '{$bo_write_level}',
            bo_comment_level = '{$bo_comment_level}',
            bo_html_level = '1',
            bo_link_level = '1',
            bo_count_modify = '1',
            bo_count_delete = '1',
            bo_upload_level = '{$bo_write_level}',
            bo_download_level = '1',
            bo_read_point = '0',
            bo_write_point = '0',
            bo_comment_point = '0',
            bo_download_point = '0',
            bo_use_category = '{$bo_use_category}',
            bo_category_list = '{$bo_category_list}',
            bo_use_sideview = '0',
            bo_use_file_content = '0',
            bo_use_secret = '{$bo_use_secret}',
            bo_use_dhtml_editor = '{$bo_use_dhtml_editor}',
            bo_select_editor = '',
            bo_use_rss_view = '0',
            bo_use_good = '0',
            bo_use_nogood = '0',
            bo_use_name = '0',
            bo_use_signature = '0',
            bo_use_ip_view = '0',
            bo_use_list_view = '0',
            bo_use_list_file = '1',
            bo_use_list_content = '0',
            bo_use_email = '0',
            bo_use_cert = '',
            bo_use_sns = '0',
            bo_use_captcha = '0',
            bo_table_width = '100',
            bo_subject_len = '60',
            bo_mobile_subject_len = '30',
            bo_page_rows = '12',
            bo_mobile_page_rows = '12',
            bo_new = '24',
            bo_hot = '100',
            bo_image_width = '835',
            bo_skin = '{$bo_skin}',
            bo_mobile_skin = '{$bo_mobile_skin}',
            bo_include_head = '_head.php',
            bo_include_tail = '_tail.php',
            bo_content_head = '',
            bo_content_tail = '',
            bo_mobile_content_head = '',
            bo_mobile_content_tail = '',
            bo_insert_content = '',
            bo_gallery_cols = '3',
            bo_gallery_width = '300',
            bo_gallery_height = '300',
            bo_mobile_gallery_width = '200',
            bo_mobile_gallery_height = '200',
            bo_upload_count = '{$bo_upload_count}',
            bo_upload_size = '{$bo_upload_size}',
            bo_reply_order = '1',
            bo_use_search = '1',
            bo_order = '{$bo_order}',
            bo_count_write = '0',
            bo_count_comment = '0',
            bo_write_min = '0',
            bo_write_max = '0',
            bo_comment_min = '0',
            bo_comment_max = '0',
            bo_notice = '',
            bo_sort_field = ''
            {$extra_subj_sql} ";

        sql_query($sql);

        if (!eottae_install_create_write_table($bo_table)) {
            return array('ok' => false, 'action' => 'error', 'message' => $bo_table.' write table create failed');
        }

        return array('ok' => true, 'action' => 'create', 'message' => $bo_table.' created');
    }
}

if (!function_exists('eottae_install_ensure_group')) {
    function eottae_install_ensure_group($gr_id, $gr_subject)
    {
        global $g5;

        $gr_id = preg_replace('/[^a-z0-9_]/', '', $gr_id);
        if ($gr_id === '') {
            return false;
        }

        $row = sql_fetch(" select count(*) as cnt from {$g5['group_table']} where gr_id = '{$gr_id}' ");
        if (!empty($row['cnt'])) {
            return true;
        }

        $gr_subject = sql_escape_string($gr_subject);
        sql_query(" insert into {$g5['group_table']} set gr_id = '{$gr_id}', gr_subject = '{$gr_subject}' ");

        return true;
    }
}

if (!function_exists('eottae_install_update_config')) {
    function eottae_install_update_config()
    {
        global $g5;

        $updates = array(
            " cf_theme = '' ",
            " cf_member_skin = 'eottae' ",
            " cf_mobile_member_skin = 'eottae' ",
        );

        foreach ($updates as $set) {
            sql_query(" update {$g5['config_table']} set {$set} ");
        }

        return true;
    }
}

if (!function_exists('eottae_install_shop_board_def')) {
    function eottae_install_shop_board_def($bo_table, $bo_subject, $bo_order, $category_list = '기타')
    {
        return array(
            'bo_table'         => $bo_table,
            'bo_subject'       => $bo_subject,
            'bo_skin'          => 'eottae-shop',
            'bo_mobile_skin'   => 'eottae-shop',
            'gr_id'            => 'community',
            'bo_read_level'    => 1,
            'bo_write_level'   => 2,
            'bo_comment_level' => 2,
            'bo_use_category'  => 1,
            'bo_category_list' => $category_list,
            'bo_upload_count'  => 10,
            'bo_upload_size'   => 10485760,
            'bo_order'         => (int) $bo_order,
            'bo_1_subj'        => '카테고리',
            'bo_2_subj'        => '대표 지역',
            'bo_3_subj'        => '주소',
            'bo_4_subj'        => '전화번호',
            'bo_5_subj'        => '문의코드',
            'bo_6_subj'        => '영업시간',
            'bo_7_subj'        => '휴무일',
            'bo_8_subj'        => '영업상태',
            'bo_9_subj'        => '위도',
            'bo_10_subj'       => '경도',
        );
    }
}

if (!function_exists('eottae_install_plaza_board_def')) {
    function eottae_install_plaza_board_def()
    {
        return array(
            'bo_table'         => 'plaza',
            'bo_subject'       => '세부광장',
            'bo_skin'          => 'eottae-plaza',
            'bo_mobile_skin'   => 'eottae-plaza',
            'gr_id'            => 'community',
            'bo_read_level'    => 1,
            'bo_write_level'   => 2,
            'bo_comment_level' => 2,
            'bo_use_category'  => 1,
            'bo_category_list' => '한마디|질문|정보공유|모임제안|홍보/거래',
            'bo_upload_count'  => 3,
            'bo_use_good'      => 0,
            'bo_use_dhtml_editor' => 0,
            'bo_order'         => 9,
            'bo_1_subj'        => '지역',
            'bo_2_subj'        => '상태',
        );
    }
}

if (!function_exists('eottae_install_column_board_def')) {
    function eottae_install_column_board_def()
    {
        if (!function_exists('eottae_column_board_def')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }

        return eottae_column_board_def();
    }
}

if (!function_exists('eottae_install_community_board_def')) {
    function eottae_install_community_board_def($bo_table, $bo_subject, $bo_order, $category_list, $skin = 'eottae-community')
    {
        return array(
            'bo_table'         => $bo_table,
            'bo_subject'       => $bo_subject,
            'bo_skin'          => $skin,
            'bo_mobile_skin'   => $skin,
            'gr_id'            => 'community',
            'bo_read_level'    => 1,
            'bo_write_level'   => 2,
            'bo_comment_level' => 2,
            'bo_use_category'  => 1,
            'bo_category_list' => $category_list,
            'bo_upload_count'  => 7,
            'bo_order'         => (int) $bo_order,
        );
    }
}

if (!function_exists('eottae_install_get_board_defs')) {
    function eottae_install_get_board_defs()
    {
        $defs = array(
            eottae_install_shop_board_def('shop', '업체·내주변', 1, eottae_shop_master_category_pipe()),
            eottae_install_shop_board_def('food', '맛집', 2, '한식|중식|일식|양식|카페|기타'),
            eottae_install_shop_board_def('massage', '마사지·스파', 3, '마사지|스파|네일|기타'),
            eottae_install_shop_board_def('rentcar', '렌트카', 4, '세단|SUV|밴|오토바이|기타'),
            eottae_install_shop_board_def('tour', '투어·액티비티', 5, '호핑|다이빙|시티투어|기타'),
            eottae_install_plaza_board_def(),
            function_exists('eottae_install_column_board_def') ? eottae_install_column_board_def() : array(
                'bo_table'         => 'column',
                'bo_subject'       => '생활정보 컬럼',
                'bo_skin'          => 'eottae-column',
                'bo_mobile_skin'   => 'eottae-column',
                'gr_id'            => 'community',
                'bo_read_level'    => 1,
                'bo_write_level'   => 10,
                'bo_comment_level' => 2,
                'bo_use_category'  => 1,
                'bo_category_list' => '생활정보|병원/건강|교육/가족|집/렌트|비자/행정|교통/차량|맛집/장보기|사업/창업|지역정보|정착이야기|교민 인터뷰|웹툰',
                'bo_upload_count'  => 10,
                'bo_order'         => 16,
            ),
            // 신규 설치: 생활정보(community)는 분류 없이 독립 게시판. 기존 ca_name 데이터 마이그레이션은 별도 작업.
            eottae_install_community_board_def('community', '생활정보', 10, ''),
            array_merge(
                eottae_install_community_board_def('free', '자유게시판', 10, ''),
                array(
                    'bo_order'        => 17,
                    'bo_use_category' => 0,
                )
            ),
            array_merge(
                eottae_install_community_board_def('people', '사람찾기', 11, ''),
                array('bo_use_category' => 0)
            ),
            array_merge(
                eottae_install_community_board_def('job', '구인구직', 12, '구인|구직|알바|기타'),
                array(
                    'bo_1_subj'  => '자동분류 지역',
                    'bo_2_subj'  => '모집상태',
                    'bo_3_subj'  => '구인구직 템플릿 JSON',
                    'bo_4_subj'  => '상세위치',
                    'bo_5_subj'  => '위도',
                    'bo_6_subj'  => '경도',
                    'bo_7_subj'  => '지도표시 여부',
                    'bo_8_subj'  => '예비',
                    'bo_9_subj'  => '예비',
                    'bo_10_subj' => '예비',
                )
            ),
            array_merge(
                eottae_install_community_board_def('estate', '부동산', 13, '매매|전월세|양도|기타'),
                array(
                    'bo_1_subj'  => '자동분류 지역',
                    'bo_2_subj'  => '거래상태',
                    'bo_3_subj'  => '부동산 템플릿 JSON',
                    'bo_4_subj'  => '상세위치',
                    'bo_5_subj'  => '위도',
                    'bo_6_subj'  => '경도',
                    'bo_7_subj'  => '지도표시 여부',
                    'bo_8_subj'  => '예비',
                    'bo_9_subj'  => '예비',
                    'bo_10_subj' => '예비',
                )
            ),
            array_merge(
                eottae_install_community_board_def('gallery', '갤러리', 14, '풍경|맛집|일상|기타', 'gallery-grid'),
                array(
                    'bo_upload_count' => 10,
                    'bo_upload_size'  => 20971520,
                    'bo_upload_level' => 1,
                )
            ),
            array_merge(
                eottae_install_community_board_def('youtube', '유튜브', 15, 'Vlog|맛집|정보|기타', 'youtube-list'),
                array('bo_1_subj' => '유튜브 URL')
            ),
            function_exists('eottae_talkroom_board_def') ? eottae_talkroom_board_def() : array(
                'bo_table'         => 'talkroom',
                'bo_subject'       => '세부톡방',
                'bo_skin'          => 'eottae-community',
                'bo_mobile_skin'   => 'eottae-community',
                'gr_id'            => 'community',
                'bo_read_level'    => 1,
                'bo_write_level'   => 2,
                'bo_comment_level' => 2,
                'bo_use_category'  => 1,
                'bo_category_list' => '일반|질문|정보|모임|모임모집|모임공지|공지',
                'bo_upload_count'  => 7,
                'bo_order'         => 20,
                'bo_1_subj'        => '톡방 ID',
                'bo_2_subj'        => '삭제상태',
                'bo_3_subj'        => '삭제자',
            ),
            array_merge(
                eottae_install_community_board_def('report', '세부 제보함', 18, ''),
                array(
                    'bo_use_category'     => 0,
                    'bo_use_dhtml_editor' => 0,
                    'bo_upload_count'     => 5,
                    'bo_1_subj'           => '제보 유형',
                    'bo_2_subj'           => '지역',
                    'bo_3_subj'           => '익명 여부',
                    'bo_4_subj'           => '연락 가능 여부',
                    'bo_5_subj'           => '연락처',
                    'bo_6_subj'           => '관련 업체명',
                    'bo_7_subj'           => '관련 링크',
                    'bo_8_subj'           => '제보 상태',
                    'bo_9_subj'           => '관리자 메모',
                    'bo_10_subj'          => '공개 전환 예비',
                )
            ),
            array_merge(
                eottae_install_community_board_def('market', '중고장터', 19, '', 'eottae-market'),
                array(
                    'bo_use_category'     => 0,
                    'bo_use_dhtml_editor' => 0,
                    'bo_upload_count'     => 8,
                    'bo_order'            => 19,
                    'bo_1_subj'           => '가격',
                    'bo_2_subj'           => '거래상태',
                    'bo_3_subj'           => '자동분류 지역',
                    'bo_4_subj'           => '상세위치',
                    'bo_5_subj'           => '위도',
                    'bo_6_subj'           => '경도',
                    'bo_7_subj'           => '연락방법',
                    'bo_8_subj'           => '가격제안 가능 여부',
                    'bo_9_subj'           => '지도표시 여부',
                    'bo_10_subj'          => '예비',
                )
            ),
            array(
                'bo_table'         => 'inquiry',
                'bo_subject'       => '문의·상담',
                'bo_skin'          => 'landing-inquiry',
                'bo_mobile_skin'   => 'landing-inquiry',
                'gr_id'            => 'community',
                'bo_read_level'    => 10,
                'bo_write_level'   => 1,
                'bo_comment_level' => 2,
                'bo_use_category'  => 0,
                'bo_use_secret'    => 1,
                'bo_upload_count'  => 3,
                'bo_order'         => 99,
            ),
            array(
                'bo_table'         => 'review',
                'bo_subject'       => '업체 리뷰',
                'bo_skin'          => 'eottae-community',
                'bo_mobile_skin'   => 'eottae-community',
                'gr_id'            => 'community',
                'bo_read_level'    => 1,
                'bo_write_level'   => 2,
                'bo_comment_level' => 5,
                'bo_use_category'  => 0,
                'bo_upload_count'  => 3,
                'bo_upload_size'   => 5242880,
                'bo_order'         => 3,
                'bo_1_subj'        => '업체 wr_id',
                'bo_2_subj'        => '별점',
                'bo_3_subj'        => '업체명',
                'bo_4_subj'        => '상태',
                'bo_5_subj'        => '사진수',
            ),
            array(
                'bo_table'         => 'event',
                'bo_subject'       => '이벤트·프로모션',
                'bo_skin'          => 'eottae-community',
                'bo_mobile_skin'   => 'eottae-community',
                'gr_id'            => 'community',
                'bo_read_level'    => 1,
                'bo_write_level'   => 2,
                'bo_comment_level' => 2,
                'bo_use_category'  => 0,
                'bo_upload_count'  => 3,
                'bo_order'         => 90,
            ),
        );

        return $defs;
    }
}

if (!function_exists('eottae_install_run')) {
    function eottae_install_run()
    {
        $log = array();

        eottae_install_ensure_group('community', '커뮤니티');

        foreach (eottae_install_get_board_defs() as $def) {
            $log[] = eottae_install_create_board($def);
        }

        eottae_install_update_config();
        $log[] = array('ok' => true, 'action' => 'config', 'message' => 'cf_theme cleared, member skin = eottae');

        if (function_exists('eottae_install_create_coupon_tables')) {
            include_once G5_LIB_PATH.'/eottae-coupon.lib.php';
            eottae_install_create_coupon_tables();
            foreach (eottae_coupon_seed_defaults() as $entry) {
                $log[] = $entry;
            }
        }

        if (function_exists('run_event')) {
            run_event('cache_delete', 'board');
        }

        return $log;
    }
}

if (!function_exists('eottae_install_update_existing_boards')) {
    /** 기존 shop/community 게시판 스킨·여분필드만 갱신 */
    function eottae_install_update_existing_boards()
    {
        global $g5;

        $log = array();

        foreach (eottae_install_get_board_defs() as $def) {
            $bo_table = preg_replace('/[^a-z0-9_]/', '', $def['bo_table']);
            if (!eottae_install_board_exists($bo_table)) {
                continue;
            }

            $sets = array(
                "bo_skin = '".sql_escape_string($def['bo_skin'])."'",
                "bo_mobile_skin = '".sql_escape_string($def['bo_mobile_skin'])."'",
                "bo_use_category = '".(int) $def['bo_use_category']."'",
            );
            if (isset($def['bo_category_list'])) {
                $sets[] = "bo_category_list = '".sql_escape_string($def['bo_category_list'])."'";
            }

            if (function_exists('eottae_is_shop_board') && eottae_is_shop_board($bo_table)) {
                for ($i = 1; $i <= 10; $i++) {
                    $key = 'bo_'.$i.'_subj';
                    if (isset($def[$key])) {
                        $sets[] = "bo_{$i}_subj = '".sql_escape_string($def[$key])."'";
                    }
                }
                $sets[] = "bo_upload_count = '10'";
            }

            if (function_exists('eottae_community_board_table') && $bo_table === eottae_community_board_table()) {
                $sets[] = "bo_upload_count = '".(int) (isset($def['bo_upload_count']) ? $def['bo_upload_count'] : 7)."'";
            }

            if (function_exists('eottae_gallery_board_table') && $bo_table === eottae_gallery_board_table()) {
                $sets[] = "bo_upload_count = '10'";
                $sets[] = "bo_upload_size = '20971520'";
                $sets[] = "bo_upload_level = '1'";
            }

            sql_query(" update {$g5['board_table']} set ".implode(', ', $sets)." where bo_table = '{$bo_table}' ");
            $log[] = array('ok' => true, 'action' => 'update', 'message' => $bo_table.' skin/fields updated');
        }

        eottae_install_update_config();
        $log[] = array('ok' => true, 'action' => 'config', 'message' => 'config updated');

        return $log;
    }
}
