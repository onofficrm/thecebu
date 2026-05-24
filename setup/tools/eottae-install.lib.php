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

if (!function_exists('eottae_install_get_board_defs')) {
    function eottae_install_get_board_defs()
    {
        return array(
            array(
                'bo_table'         => 'shop',
                'bo_subject'       => '업체·맛집',
                'bo_skin'          => 'eottae-shop',
                'bo_mobile_skin'   => 'eottae-shop',
                'gr_id'            => 'community',
                'bo_read_level'    => 1,
                'bo_write_level'   => 2,
                'bo_comment_level' => 2,
                'bo_use_category'  => 1,
                'bo_category_list' => '맛집|카페|미용|병원|마트|숙소|기타',
                'bo_upload_count'  => 10,
                'bo_upload_size'   => 10485760,
                'bo_order'         => 1,
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
            ),
            array(
                'bo_table'         => 'community',
                'bo_subject'       => '커뮤니티',
                'bo_skin'          => 'eottae-community',
                'bo_mobile_skin'   => 'eottae-community',
                'gr_id'            => 'community',
                'bo_read_level'    => 1,
                'bo_write_level'   => 2,
                'bo_comment_level' => 2,
                'bo_use_category'  => 1,
                'bo_category_list' => '자유|정보|질문|후기|구인구직',
                'bo_upload_count'  => 5,
                'bo_order'         => 2,
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
                'bo_write_level'   => 5,
                'bo_comment_level' => 2,
                'bo_use_category'  => 1,
                'bo_category_list' => '진행중|예정|종료',
                'bo_upload_count'  => 3,
                'bo_order'         => 4,
            ),
        );
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
                "bo_category_list = '".sql_escape_string($def['bo_category_list'])."'",
            );

            if ($bo_table === 'shop') {
                for ($i = 1; $i <= 10; $i++) {
                    $key = 'bo_'.$i.'_subj';
                    if (isset($def[$key])) {
                        $sets[] = "bo_{$i}_subj = '".sql_escape_string($def[$key])."'";
                    }
                }
                $sets[] = "bo_upload_count = '10'";
            }

            sql_query(" update {$g5['board_table']} set ".implode(', ', $sets)." where bo_table = '{$bo_table}' ");
            $log[] = array('ok' => true, 'action' => 'update', 'message' => $bo_table.' skin/fields updated');
        }

        eottae_install_update_config();
        $log[] = array('ok' => true, 'action' => 'config', 'message' => 'config updated');

        return $log;
    }
}
