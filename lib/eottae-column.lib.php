<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_bootstrap_tables')) {
    function eottae_column_bootstrap_tables()
    {
        global $g5;

        $g5['sebu_column_authors_table'] = G5_TABLE_PREFIX.'sebu_column_authors';
        $g5['sebu_columns_meta_table'] = G5_TABLE_PREFIX.'sebu_columns_meta';
        $g5['sebu_column_likes_table'] = G5_TABLE_PREFIX.'sebu_column_likes';
        $g5['sebu_column_bookmarks_table'] = G5_TABLE_PREFIX.'sebu_column_bookmarks';
        $g5['sebu_column_reports_table'] = G5_TABLE_PREFIX.'sebu_column_reports';
        $g5['sebu_column_author_awards_table'] = G5_TABLE_PREFIX.'sebu_column_author_awards';
        $g5['sebu_column_author_applications_table'] = G5_TABLE_PREFIX.'sebu_column_author_applications';
    }
}

if (!function_exists('eottae_column_board_table')) {
    function eottae_column_board_table()
    {
        return defined('EOTTae_COLUMN_TABLE') ? EOTTae_COLUMN_TABLE : 'column';
    }
}

if (!function_exists('eottae_column_table_exists')) {
    function eottae_column_table_exists($table)
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

if (!function_exists('eottae_column_table_has_column')) {
    function eottae_column_table_has_column($table, $column)
    {
        $table = preg_replace('/[^a-z0-9_]/i', '', (string) $table);
        $column = preg_replace('/[^a-z0-9_]/i', '', (string) $column);
        if ($table === '' || $column === '' || !eottae_column_table_exists($table)) {
            return false;
        }

        $row = sql_fetch(" SHOW COLUMNS FROM `{$table}` LIKE '".sql_escape_string($column)."' ", false);

        return is_array($row) && !empty($row['Field']);
    }
}

if (!function_exists('eottae_column_social_field_keys')) {
    function eottae_column_social_field_keys()
    {
        return array('youtube_url', 'facebook_url', 'instagram_url', 'tiktok_url', 'naver_blog_url');
    }
}

if (!function_exists('eottae_column_social_platform_labels')) {
    function eottae_column_social_platform_labels()
    {
        return array(
            'youtube_url'     => 'YouTube',
            'facebook_url'    => 'Facebook',
            'instagram_url'   => 'Instagram',
            'tiktok_url'      => 'TikTok',
            'naver_blog_url'  => '네이버 블로그',
        );
    }
}

if (!function_exists('eottae_column_normalize_url')) {
    function eottae_column_normalize_url($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
    }
}

if (!function_exists('eottae_column_collect_social_from_input')) {
    function eottae_column_collect_social_from_input(array $input)
    {
        $values = array();
        foreach (eottae_column_social_field_keys() as $key) {
            $values[$key] = eottae_column_normalize_url($input[$key] ?? '');
        }

        $legacy = eottae_column_normalize_url($input['sns_url'] ?? '');
        if ($legacy !== '' && $values['youtube_url'] === '') {
            $values['youtube_url'] = $legacy;
        }

        return $values;
    }
}

if (!function_exists('eottae_column_social_from_row')) {
    function eottae_column_social_from_row(array $row)
    {
        $values = array();
        foreach (eottae_column_social_field_keys() as $key) {
            $values[$key] = eottae_column_normalize_url($row[$key] ?? '');
        }

        $legacy = eottae_column_normalize_url($row['sns_url'] ?? '');
        if ($legacy !== '' && $values['youtube_url'] === '') {
            $values['youtube_url'] = $legacy;
        }

        return $values;
    }
}

if (!function_exists('eottae_column_initials_from_name')) {
    function eottae_column_initials_from_name($name)
    {
        $name = trim(preg_replace('/\s+/u', ' ', (string) $name));
        if ($name === '') {
            return '?';
        }

        if (preg_match('/^[a-zA-Z]/u', $name)) {
            $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
            if (count($parts) >= 2) {
                return mb_strtoupper(mb_substr($parts[0], 0, 1, 'UTF-8').mb_substr($parts[1], 0, 1, 'UTF-8'), 'UTF-8');
            }

            return mb_strtoupper(mb_substr($name, 0, 2, 'UTF-8'), 'UTF-8');
        }

        $compact = preg_replace('/\s+/u', '', $name);

        return mb_substr($compact, 0, 2, 'UTF-8');
    }
}

if (!function_exists('eottae_column_author_social_links')) {
    function eottae_column_author_social_links(array $author)
    {
        $labels = eottae_column_social_platform_labels();
        $links = array();
        foreach (eottae_column_social_field_keys() as $key) {
            $url = eottae_column_normalize_url($author[$key] ?? '');
            if ($url === '') {
                continue;
            }
            $links[] = array(
                'key'   => $key,
                'url'   => $url,
                'label' => $labels[$key] ?? $key,
            );
        }

        return $links;
    }
}

if (!function_exists('eottae_column_migrate_profile_columns')) {
    function eottae_column_migrate_profile_columns()
    {
        eottae_column_bootstrap_tables();
        global $g5;

        $social_columns = array_fill_keys(
            eottae_column_social_field_keys(),
            "varchar(255) NOT NULL DEFAULT ''"
        );
        $author_columns = $social_columns;
        $application_columns = array_merge(
            array('profile_image' => "varchar(255) NOT NULL DEFAULT ''"),
            $social_columns
        );

        $tables = array(
            $g5['sebu_column_authors_table']       => $author_columns,
            $g5['sebu_column_author_applications_table'] => $application_columns,
        );

        foreach ($tables as $table => $columns) {
            if (!eottae_column_table_exists($table)) {
                continue;
            }
            foreach ($columns as $column => $definition) {
                if (!eottae_column_table_has_column($table, $column)) {
                    sql_query(" ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition} ", false);
                }
            }
        }
    }
}

if (!function_exists('eottae_column_board_def')) {
    function eottae_column_board_def()
    {
        $cats = implode('|', array_values(eottae_column_category_options()));

        return array(
            'bo_table'         => eottae_column_board_table(),
            'bo_subject'       => '생활정보 컬럼',
            'bo_skin'          => 'eottae-column',
            'bo_mobile_skin'   => 'eottae-column',
            'gr_id'            => 'community',
            'bo_read_level'    => 1,
            'bo_write_level'   => 10,
            'bo_comment_level' => 2,
            'bo_use_category'  => 1,
            'bo_category_list' => $cats,
            'bo_upload_count'  => 10,
            'bo_use_dhtml_editor' => 1,
            'bo_order'         => 16,
            'bo_1_subj'        => '부제목',
            'bo_2_subj'        => '발행상태',
        );
    }
}

if (!function_exists('eottae_column_category_options')) {
    function eottae_column_category_options()
    {
        return array(
            'life'        => '생활정보',
            'health'      => '병원/건강',
            'family'      => '교육/가족',
            'rent'        => '집/렌트',
            'visa'        => '비자/행정',
            'transport'   => '교통/차량',
            'food'        => '맛집/장보기',
            'business'    => '사업/창업',
            'area'        => '지역정보',
            'settlement'  => '정착이야기',
            'interview'   => '교민 인터뷰',
        );
    }
}

if (!function_exists('eottae_column_category_label')) {
    function eottae_column_category_label($code)
    {
        $options = eottae_column_category_options();
        $code = preg_replace('/[^a-z_]/', '', (string) $code);

        return isset($options[$code]) ? $options[$code] : '';
    }
}

if (!function_exists('eottae_column_category_descriptions')) {
    function eottae_column_category_descriptions()
    {
        return array(
            'life'       => '병원, 약국, 인터넷, 택배, 은행처럼 세부 일상에 바로 필요한 생활정보를 모았습니다.',
            'health'     => '세부 교민이 자주 찾는 병원, 약국, 응급 상황 대처법과 건강 정보를 정리합니다.',
            'family'     => '국제학교, 영어캠프, 아이 병원, 가족 나들이처럼 가족 생활에 필요한 정보를 소개합니다.',
            'rent'       => '콘도 렌트, 집 구하기, 계약 전 확인할 점과 지역별 주거 정보를 다룹니다.',
            'visa'       => '비자, 행정, 장기거주 준비와 생활 서류에 대한 현실적인 경험을 공유합니다.',
            'transport'  => 'Grab, 택시, 오토바이, 차량 구매와 세부 이동에 필요한 정보를 모았습니다.',
            'food'       => '한인마트, 장보기, 맛집, 생활 식재료 등 세부 식생활 정보를 소개합니다.',
            'business'   => '사업자 등록, 필리핀 직원 채용, 세금, 가게 운영과 마케팅 정보를 다룹니다.',
            'area'       => '세부시티, 막탄, 라푸라푸, 만다우에, IT Park 등 지역별 생활 정보를 모았습니다.',
            'settlement' => '세부 이주 준비, 한달살기와 장기거주, 생활비와 교민사회 적응 이야기를 전합니다.',
            'interview'  => '세부 교민과 현지 생활자를 만나 실제 경험과 노하우를 인터뷰로 전합니다.',
        );
    }
}

if (!function_exists('eottae_column_category_description')) {
    function eottae_column_category_description($code)
    {
        $descriptions = eottae_column_category_descriptions();
        $code = preg_replace('/[^a-z_]/', '', (string) $code);

        return isset($descriptions[$code]) ? $descriptions[$code] : '세부 교민에게 필요한 생활정보 컬럼을 모았습니다.';
    }
}

if (!function_exists('eottae_column_status_options')) {
    function eottae_column_status_options()
    {
        return array(
            'draft'     => '임시저장',
            'published' => '발행',
            'hidden'    => '숨김',
        );
    }
}

if (!function_exists('eottae_column_status_label')) {
    function eottae_column_status_label($status)
    {
        $options = eottae_column_status_options();
        $status = preg_replace('/[^a-z_]/', '', (string) $status);

        return isset($options[$status]) ? $options[$status] : $status;
    }
}

if (!function_exists('eottae_column_area_options')) {
    function eottae_column_area_options()
    {
        if (function_exists('eottae_challenge_area_options')) {
            return eottae_challenge_area_options();
        }

        return array(
            'cebu_city' => '세부시티',
            'mactan'    => '막탄',
            'lapu_lapu' => '라푸라푸',
            'mandaue'   => '만다우에',
            'talamban'  => '탈람반',
            'banilad'   => '바닐라드',
            'it_park'   => 'IT Park',
            'ayala'     => '아얄라',
            'sm_city'   => 'SM City',
            'etc'       => '기타',
        );
    }
}

if (!function_exists('eottae_column_area_label')) {
    function eottae_column_area_label($code)
    {
        $options = eottae_column_area_options();
        $code = preg_replace('/[^a-z0-9_]/', '', (string) $code);

        return isset($options[$code]) ? $options[$code] : '';
    }
}

if (!function_exists('eottae_column_grade_options')) {
    function eottae_column_grade_options()
    {
        return array(
            'rookie'    => '새내기 리포터',
            'reporter'  => '세부 리포터',
            'columnist' => '생활정보 칼럼니스트',
            'popular'   => '인기 칼럼니스트',
            'official'  => '세부어때 공식 칼럼니스트',
        );
    }
}

if (!function_exists('eottae_column_list_url')) {
    function eottae_column_list_url(array $params = array())
    {
        $url = G5_URL.'/column/';
        if (!empty($params)) {
            $url .= '?'.http_build_query($params);
        }

        return $url;
    }
}

if (!function_exists('eottae_column_category_url')) {
    function eottae_column_category_url($category, array $params = array())
    {
        $category = preg_replace('/[^a-z_]/', '', (string) $category);
        $url = G5_URL.'/column/category.php?category='.urlencode($category);
        if (!empty($params)) {
            $url .= '&'.http_build_query($params);
        }

        return $url;
    }
}

if (!function_exists('eottae_column_view_url')) {
    function eottae_column_view_url($wr_id)
    {
        $wr_id = (int) $wr_id;

        return G5_URL.'/column/view.php?wr_id='.$wr_id;
    }
}

if (!function_exists('eottae_column_write_url')) {
    function eottae_column_write_url($wr_id = 0)
    {
        $url = G5_URL.'/page/eottae-column-write.php';
        if ((int) $wr_id > 0) {
            $url .= '?wr_id='.(int) $wr_id;
        }

        return $url;
    }
}

if (!function_exists('eottae_column_apply_url')) {
    function eottae_column_apply_url()
    {
        return G5_URL.'/page/eottae-column-apply.php';
    }
}

if (!function_exists('eottae_column_author_url')) {
    function eottae_column_author_url($mb_id)
    {
        return G5_URL.'/column/author.php?mb_id='.urlencode((string) $mb_id);
    }
}

if (!function_exists('eottae_column_mypage_url')) {
    function eottae_column_mypage_url()
    {
        return G5_URL.'/mypage/column.php';
    }
}

if (!function_exists('eottae_column_proc_url')) {
    function eottae_column_proc_url()
    {
        return G5_URL.'/proc/eottae-column.php';
    }
}

if (!function_exists('eottae_column_admin_url')) {
    function eottae_column_admin_url(array $params = array())
    {
        $url = G5_URL.'/page/eottae-admin-column.php';
        if (!empty($params)) {
            $url .= '?'.http_build_query($params);
        }

        return $url;
    }
}

if (!function_exists('eottae_column_admin_proc_url')) {
    function eottae_column_admin_proc_url()
    {
        return G5_URL.'/proc/eottae-column-admin.php';
    }
}

if (!function_exists('eottae_column_pending_application_count')) {
    function eottae_column_pending_application_count()
    {
        eottae_column_bootstrap_tables();
        global $g5;

        $table = $g5['sebu_column_author_applications_table'];
        if (!eottae_column_table_exists($table)) {
            return 0;
        }

        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$table}`
            WHERE status = 'pending'
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_column_member_token')) {
    function eottae_column_member_token($regenerate = false)
    {
        $token = get_session('eottae_column_member_token');
        if ($regenerate || $token === '') {
            $token = bin2hex(random_bytes(16));
            set_session('eottae_column_member_token', $token);
        }

        return (string) $token;
    }
}

if (!function_exists('eottae_column_verify_member_token')) {
    function eottae_column_verify_member_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_column_member_token');

        return $token !== '' && $session_token !== '' && hash_equals((string) $session_token, $token);
    }
}

if (!function_exists('eottae_column_ensure_schema')) {
    function eottae_column_ensure_schema()
    {
        eottae_column_bootstrap_tables();
        global $g5;

        $results = array();

        if (!eottae_column_table_exists($g5['sebu_column_authors_table'])) {
            $ok = (bool) sql_query("
                CREATE TABLE IF NOT EXISTS `{$g5['sebu_column_authors_table']}` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `mb_id` varchar(20) NOT NULL DEFAULT '',
                    `pen_name` varchar(80) NOT NULL DEFAULT '',
                    `title` varchar(120) NOT NULL DEFAULT '',
                    `specialty` varchar(200) NOT NULL DEFAULT '',
                    `bio` text NOT NULL,
                    `profile_image` varchar(255) NOT NULL DEFAULT '',
                    `area` varchar(60) NOT NULL DEFAULT '',
                    `website_url` varchar(255) NOT NULL DEFAULT '',
                    `sns_url` varchar(255) NOT NULL DEFAULT '',
                    `youtube_url` varchar(255) NOT NULL DEFAULT '',
                    `facebook_url` varchar(255) NOT NULL DEFAULT '',
                    `instagram_url` varchar(255) NOT NULL DEFAULT '',
                    `tiktok_url` varchar(255) NOT NULL DEFAULT '',
                    `naver_blog_url` varchar(255) NOT NULL DEFAULT '',
                    `contact_open` tinyint(1) NOT NULL DEFAULT '0',
                    `is_official` tinyint(1) NOT NULL DEFAULT '0',
                    `is_active` tinyint(1) NOT NULL DEFAULT '1',
                    `is_visible` tinyint(1) NOT NULL DEFAULT '1',
                    `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uk_column_author_mb` (`mb_id`),
                    KEY `idx_column_author_active` (`is_active`, `is_visible`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ", false);
            $results['authors'] = $ok ? 'created' : 'failed';
        }

        if (!eottae_column_table_exists($g5['sebu_columns_meta_table'])) {
            $ok = (bool) sql_query("
                CREATE TABLE IF NOT EXISTS `{$g5['sebu_columns_meta_table']}` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `wr_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `subtitle` varchar(255) NOT NULL DEFAULT '',
                    `summary` varchar(500) NOT NULL DEFAULT '',
                    `thumbnail` varchar(255) NOT NULL DEFAULT '',
                    `category` varchar(40) NOT NULL DEFAULT '',
                    `tags` varchar(255) NOT NULL DEFAULT '',
                    `area` varchar(60) NOT NULL DEFAULT '',
                    `related_url` varchar(255) NOT NULL DEFAULT '',
                    `related_room_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `related_event_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `read_time` smallint(5) unsigned NOT NULL DEFAULT '0',
                    `is_featured` tinyint(1) NOT NULL DEFAULT '0',
                    `is_recommended` tinyint(1) NOT NULL DEFAULT '0',
                    `is_representative` tinyint(1) NOT NULL DEFAULT '0',
                    `status` varchar(20) NOT NULL DEFAULT 'published',
                    `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uk_column_meta_wr` (`wr_id`),
                    KEY `idx_column_meta_cat` (`category`, `status`),
                    KEY `idx_column_meta_featured` (`is_featured`, `status`),
                    KEY `idx_column_meta_recommended` (`is_recommended`, `status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ", false);
            $results['meta'] = $ok ? 'created' : 'failed';
        }

        if (function_exists('eottae_column_likes_ensure_schema')) {
            include_once G5_LIB_PATH.'/eottae-column-likes.lib.php';
            eottae_column_likes_ensure_schema();
        }
        if (function_exists('eottae_column_bookmarks_ensure_schema')) {
            include_once G5_LIB_PATH.'/eottae-column-bookmarks.lib.php';
            eottae_column_bookmarks_ensure_schema();
        }
        if (function_exists('eottae_column_reports_ensure_schema')) {
            include_once G5_LIB_PATH.'/eottae-column-report.lib.php';
            eottae_column_reports_ensure_schema();
        }

        if (!eottae_column_table_exists($g5['sebu_column_author_awards_table'])) {
            $ok = (bool) sql_query("
                CREATE TABLE IF NOT EXISTS `{$g5['sebu_column_author_awards_table']}` (
                    `award_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `mb_id` varchar(20) NOT NULL DEFAULT '',
                    `award_type` varchar(40) NOT NULL DEFAULT '',
                    `title` varchar(120) NOT NULL DEFAULT '',
                    `reason` varchar(500) NOT NULL DEFAULT '',
                    `start_date` date NOT NULL DEFAULT '0000-00-00',
                    `end_date` date NOT NULL DEFAULT '0000-00-00',
                    `show_on_main` tinyint(1) NOT NULL DEFAULT '0',
                    `is_active` tinyint(1) NOT NULL DEFAULT '1',
                    `created_by` varchar(20) NOT NULL DEFAULT '',
                    `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`award_id`),
                    KEY `idx_column_award_mb` (`mb_id`, `is_active`),
                    KEY `idx_column_award_type` (`award_type`, `is_active`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ", false);
            $results['awards'] = $ok ? 'created' : 'failed';
        }

        if (!eottae_column_table_exists($g5['sebu_column_author_applications_table'])) {
            $ok = (bool) sql_query("
                CREATE TABLE IF NOT EXISTS `{$g5['sebu_column_author_applications_table']}` (
                    `application_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `mb_id` varchar(20) NOT NULL DEFAULT '',
                    `pen_name` varchar(80) NOT NULL DEFAULT '',
                    `title` varchar(120) NOT NULL DEFAULT '',
                    `specialty` varchar(200) NOT NULL DEFAULT '',
                    `bio` text NOT NULL,
                    `area` varchar(60) NOT NULL DEFAULT '',
                    `website_url` varchar(255) NOT NULL DEFAULT '',
                    `sns_url` varchar(255) NOT NULL DEFAULT '',
                    `profile_image` varchar(255) NOT NULL DEFAULT '',
                    `youtube_url` varchar(255) NOT NULL DEFAULT '',
                    `facebook_url` varchar(255) NOT NULL DEFAULT '',
                    `instagram_url` varchar(255) NOT NULL DEFAULT '',
                    `tiktok_url` varchar(255) NOT NULL DEFAULT '',
                    `naver_blog_url` varchar(255) NOT NULL DEFAULT '',
                    `sample_url` varchar(255) NOT NULL DEFAULT '',
                    `message` text NOT NULL,
                    `status` varchar(20) NOT NULL DEFAULT 'pending',
                    `review_memo` varchar(500) NOT NULL DEFAULT '',
                    `reviewed_by` varchar(20) NOT NULL DEFAULT '',
                    `reviewed_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`application_id`),
                    KEY `idx_column_application_mb` (`mb_id`, `status`),
                    KEY `idx_column_application_status` (`status`, `application_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ", false);
            $results['applications'] = $ok ? 'created' : 'failed';
        }

        eottae_column_ensure_board();
        eottae_column_migrate_profile_columns();

        return array('ok' => true, 'results' => $results);
    }
}

if (!function_exists('eottae_column_ensure_board')) {
    function eottae_column_ensure_board()
    {
        $install_lib = G5_PATH.'/setup/tools/eottae-install.lib.php';
        if (!is_file($install_lib)) {
            return array('ok' => false, 'message' => 'install helper missing');
        }

        include_once $install_lib;
        if (!function_exists('eottae_install_board_exists') || !function_exists('eottae_install_create_board')) {
            return array('ok' => false, 'message' => 'install helper incomplete');
        }

        $bo_table = eottae_column_board_table();
        if (eottae_install_board_exists($bo_table)) {
            return array('ok' => true, 'action' => 'skip');
        }

        if (function_exists('eottae_install_ensure_group')) {
            eottae_install_ensure_group('community', '커뮤니티');
        }

        return eottae_install_create_board(eottae_column_board_def());
    }
}

if (!function_exists('eottae_column_write_table')) {
    function eottae_column_write_table()
    {
        global $g5;

        return $g5['write_prefix'].eottae_column_board_table();
    }
}

if (!function_exists('eottae_column_is_columnist')) {
    function eottae_column_is_columnist($mb_id)
    {
        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return false;
        }

        eottae_column_bootstrap_tables();
        global $g5;
        $table = $g5['sebu_column_authors_table'];
        if (!eottae_column_table_exists($table)) {
            return false;
        }

        $mb_id_sql = sql_escape_string($mb_id);
        $row = sql_fetch("
            SELECT id
            FROM `{$table}`
            WHERE mb_id = '{$mb_id_sql}'
              AND is_active = 1
            LIMIT 1
        ", false);

        return !empty($row['id']);
    }
}

if (!function_exists('eottae_column_can_write')) {
    function eottae_column_can_write($mb_id, $is_super = false)
    {
        if ($is_super) {
            return true;
        }

        return eottae_column_is_columnist($mb_id);
    }
}

if (!function_exists('eottae_column_can_edit')) {
    function eottae_column_can_edit($mb_id, $wr_id, $is_super = false)
    {
        if ($is_super) {
            return true;
        }

        $post = eottae_column_get_write_row($wr_id);
        if (!$post) {
            return false;
        }

        return trim((string) $mb_id) !== '' && trim((string) $mb_id) === trim((string) ($post['mb_id'] ?? ''));
    }
}

if (!function_exists('eottae_column_can_delete')) {
    function eottae_column_can_delete($mb_id, $wr_id, $is_super = false)
    {
        return eottae_column_can_edit($mb_id, $wr_id, $is_super);
    }
}

if (!function_exists('eottae_column_delete_post')) {
    /**
     * @return array{ok: bool, message: string, list_url?: string}
     */
    function eottae_column_delete_post($wr_id, $mb_id, $is_super = false)
    {
        global $g5;

        $wr_id = (int) $wr_id;
        $mb_id = trim((string) $mb_id);
        if ($wr_id < 1) {
            return array('ok' => false, 'message' => '잘못된 요청입니다.');
        }
        if (!eottae_column_can_delete($mb_id, $wr_id, $is_super)) {
            return array('ok' => false, 'message' => '삭제 권한이 없습니다.');
        }

        $post = eottae_column_get_write_row($wr_id);
        if (!$post) {
            return array('ok' => false, 'message' => '글을 찾을 수 없습니다.');
        }

        $write_table = eottae_column_write_table();
        $bo_table = eottae_column_board_table();
        $bo_table_sql = sql_escape_string($bo_table);

        $comment_row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$write_table}`
            WHERE wr_parent = '{$wr_id}'
              AND wr_is_comment = 1
        ", false);
        $comment_cnt = (int) ($comment_row['cnt'] ?? 0);

        eottae_column_bootstrap_tables();
        $meta = eottae_column_get_meta($wr_id);
        if ($meta && !empty($meta['thumbnail'])) {
            $thumb_path = (string) $meta['thumbnail'];
            if ($thumb_path !== '' && strpos($thumb_path, '..') === false) {
                $file = G5_DATA_PATH.'/'.ltrim($thumb_path, '/');
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }

        $meta_table = $g5['sebu_columns_meta_table'];
        if (eottae_column_table_exists($meta_table)) {
            sql_query(" DELETE FROM `{$meta_table}` WHERE wr_id = '{$wr_id}' ", false);
        }

        if (function_exists('eottae_column_likes_table')) {
            include_once G5_LIB_PATH.'/eottae-column-likes.lib.php';
            $likes_table = eottae_column_likes_table();
            if (eottae_column_table_exists($likes_table)) {
                sql_query(" DELETE FROM `{$likes_table}` WHERE wr_id = '{$wr_id}' ", false);
            }
        }

        if (function_exists('eottae_column_bookmarks_table')) {
            include_once G5_LIB_PATH.'/eottae-column-bookmarks.lib.php';
            $bookmarks_table = eottae_column_bookmarks_table();
            if (eottae_column_table_exists($bookmarks_table)) {
                sql_query(" DELETE FROM `{$bookmarks_table}` WHERE wr_id = '{$wr_id}' ", false);
            }
        }

        if (function_exists('eottae_column_reports_table')) {
            include_once G5_LIB_PATH.'/eottae-column-report.lib.php';
            $reports_table = eottae_column_reports_table();
            if (eottae_column_table_exists($reports_table)) {
                sql_query(" DELETE FROM `{$reports_table}` WHERE wr_id = '{$wr_id}' ", false);
            }
        }

        sql_query(" DELETE FROM `{$write_table}` WHERE wr_parent = '{$wr_id}' ", false);
        sql_query("
            DELETE FROM {$g5['board_new_table']}
            WHERE bo_table = '{$bo_table_sql}'
              AND wr_parent = '{$wr_id}'
        ", false);
        sql_query("
            DELETE FROM {$g5['scrap_table']}
            WHERE bo_table = '{$bo_table_sql}'
              AND wr_id = '{$wr_id}'
        ", false);
        sql_query("
            UPDATE {$g5['board_table']} SET
                bo_count_write = IF(bo_count_write > 0, bo_count_write - 1, 0),
                bo_count_comment = IF(bo_count_comment > {$comment_cnt}, bo_count_comment - {$comment_cnt}, 0)
            WHERE bo_table = '{$bo_table_sql}'
        ", false);

        $author_mb_id = trim((string) ($post['mb_id'] ?? ''));
        if ($author_mb_id !== '') {
            eottae_column_sync_author_badges($author_mb_id);
        }

        if (function_exists('delete_cache_latest')) {
            delete_cache_latest($bo_table);
        }

        return array(
            'ok'       => true,
            'message'  => '컬럼이 삭제되었습니다.',
            'list_url' => eottae_column_list_url(),
        );
    }
}

if (!function_exists('eottae_column_get_write_row')) {
    function eottae_column_get_write_row($wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return null;
        }

        global $g5;
        $write_table = eottae_column_write_table();
        $row = sql_fetch("
            SELECT *
            FROM `{$write_table}`
            WHERE wr_id = '{$wr_id}'
              AND wr_is_comment = 0
            LIMIT 1
        ", false);

        return is_array($row) && !empty($row['wr_id']) ? $row : null;
    }
}

if (!function_exists('eottae_column_get_meta')) {
    function eottae_column_get_meta($wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return null;
        }

        eottae_column_bootstrap_tables();
        global $g5;
        $table = $g5['sebu_columns_meta_table'];
        if (!eottae_column_table_exists($table)) {
            return null;
        }

        $row = sql_fetch("
            SELECT *
            FROM `{$table}`
            WHERE wr_id = '{$wr_id}'
            LIMIT 1
        ", false);

        return is_array($row) && !empty($row['id']) ? $row : null;
    }
}

if (!function_exists('eottae_column_get_meta_batch')) {
    function eottae_column_get_meta_batch(array $wr_ids)
    {
        $wr_ids = array_values(array_unique(array_filter(array_map('intval', $wr_ids))));
        if (empty($wr_ids)) {
            return array();
        }

        eottae_column_bootstrap_tables();
        global $g5;
        $table = $g5['sebu_columns_meta_table'];
        if (!eottae_column_table_exists($table)) {
            return array();
        }

        $in = implode(',', $wr_ids);
        $result = sql_query(" SELECT * FROM `{$table}` WHERE wr_id IN ({$in}) ", false);
        $map = array();
        while ($row = sql_fetch_array($result)) {
            $map[(int) $row['wr_id']] = $row;
        }

        return $map;
    }
}

if (!function_exists('eottae_column_get_author')) {
    function eottae_column_get_author($mb_id)
    {
        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return null;
        }

        eottae_column_bootstrap_tables();
        global $g5;
        $table = $g5['sebu_column_authors_table'];
        if (!eottae_column_table_exists($table)) {
            return null;
        }

        $mb_id_sql = sql_escape_string($mb_id);
        $row = sql_fetch("
            SELECT a.*, m.mb_nick, m.mb_name
            FROM `{$table}` a
            LEFT JOIN {$g5['member_table']} m ON m.mb_id = a.mb_id
            WHERE a.mb_id = '{$mb_id_sql}'
            LIMIT 1
        ", false);

        if (!is_array($row) || empty($row['id'])) {
            return null;
        }

        return eottae_column_enrich_author($row);
    }
}

if (!function_exists('eottae_column_enrich_author')) {
    function eottae_column_enrich_author(array $author)
    {
        $stats = eottae_column_author_stats($author['mb_id'] ?? '');
        $author['display_name'] = trim((string) ($author['pen_name'] ?? '')) !== ''
            ? trim((string) $author['pen_name'])
            : trim((string) ($author['mb_nick'] ?? $author['mb_name'] ?? ''));
        $author['stats'] = $stats;
        $author['grade'] = eottae_column_author_grade($stats, !empty($author['is_official']));
        $author['grade_label'] = eottae_column_grade_label($author['grade']);
        $author['profile_url'] = eottae_column_author_url($author['mb_id'] ?? '');
        $author['has_profile_image'] = trim((string) ($author['profile_image'] ?? '')) !== '';
        $author['profile_image_url'] = $author['has_profile_image']
            ? eottae_column_profile_image_url($author['profile_image'] ?? '')
            : '';
        $author['profile_initials'] = eottae_column_initials_from_name($author['display_name'] ?? '');
        $author['social_links'] = eottae_column_author_social_links($author);
        $author['area_label'] = eottae_column_area_label($author['area'] ?? '');

        return $author;
    }
}

if (!function_exists('eottae_column_grade_label')) {
    function eottae_column_grade_label($grade)
    {
        $options = eottae_column_grade_options();
        $grade = preg_replace('/[^a-z_]/', '', (string) $grade);

        return isset($options[$grade]) ? $options[$grade] : '';
    }
}

if (!function_exists('eottae_column_author_grade')) {
    function eottae_column_author_grade(array $stats, $is_official = false)
    {
        if ($is_official) {
            return 'official';
        }

        $columns = (int) ($stats['column_count'] ?? 0);
        $views = (int) ($stats['total_views'] ?? 0);
        $likes = (int) ($stats['total_likes'] ?? 0);

        if ($views >= 10000 || $likes >= 300) {
            return 'popular';
        }
        if ($columns >= 10 || $views >= 3000) {
            return 'columnist';
        }
        if ($columns >= 5) {
            return 'reporter';
        }
        if ($columns >= 1) {
            return 'rookie';
        }

        return 'rookie';
    }
}

if (!function_exists('eottae_column_author_stats')) {
    function eottae_column_author_stats($mb_id)
    {
        $mb_id = trim((string) $mb_id);
        $empty = array(
            'column_count'   => 0,
            'total_views'    => 0,
            'total_likes'    => 0,
            'total_comments' => 0,
            'last_written'   => '',
        );
        if ($mb_id === '') {
            return $empty;
        }

        global $g5;
        $write_table = eottae_column_write_table();
        $mb_id_sql = sql_escape_string($mb_id);
        $meta_table = $g5['sebu_columns_meta_table'];

        $row = sql_fetch("
            SELECT COUNT(*) AS column_count,
                   IFNULL(SUM(w.wr_hit), 0) AS total_views,
                   IFNULL(SUM(w.wr_comment), 0) AS total_comments,
                   MAX(w.wr_datetime) AS last_written
            FROM `{$write_table}` w
            INNER JOIN `{$meta_table}` m ON m.wr_id = w.wr_id
            WHERE w.mb_id = '{$mb_id_sql}'
              AND w.wr_is_comment = 0
              AND m.status = 'published'
        ", false);

        $stats = array(
            'column_count'   => (int) ($row['column_count'] ?? 0),
            'total_views'    => (int) ($row['total_views'] ?? 0),
            'total_comments' => (int) ($row['total_comments'] ?? 0),
            'last_written'   => (string) ($row['last_written'] ?? ''),
            'total_likes'    => 0,
        );

        if (function_exists('eottae_column_author_total_likes')) {
            $stats['total_likes'] = eottae_column_author_total_likes($mb_id);
        }

        return $stats;
    }
}

if (!function_exists('eottae_column_profile_image_url')) {
    function eottae_column_profile_image_url($path, $mb_id = '')
    {
        $path = trim((string) $path);
        if ($path !== '') {
            if (preg_match('#^https?://#i', $path)) {
                return $path;
            }

            return G5_DATA_URL.'/'.ltrim($path, '/');
        }

        return '';
    }
}

if (!function_exists('eottae_column_thumbnail_url')) {
    function eottae_column_thumbnail_url($path, $fallback_content = '')
    {
        $path = trim((string) $path);
        if ($path !== '') {
            if (preg_match('#^https?://#i', $path)) {
                return $path;
            }

            return G5_DATA_URL.'/'.ltrim($path, '/');
        }

        if ($fallback_content !== '' && function_exists('eottae_extract_first_image')) {
            $img = eottae_extract_first_image($fallback_content);
            if ($img !== '') {
                return $img;
            }
        }

        return G5_IMG_URL.'/no_img.png';
    }
}

if (!function_exists('eottae_column_calc_read_time')) {
    function eottae_column_calc_read_time($content)
    {
        $text = strip_tags((string) $content);
        $text = preg_replace('/\s+/u', '', $text);
        $len = mb_strlen($text, 'UTF-8');
        $minutes = max(1, (int) ceil($len / 500));

        return $minutes;
    }
}

if (!function_exists('eottae_column_enrich_post')) {
    function eottae_column_enrich_post(array $row, array $opts = array())
    {
        $wr_id = (int) ($row['wr_id'] ?? 0);
        $meta = eottae_column_get_meta($wr_id);
        if (!$meta) {
            $meta = array(
                'category' => '',
                'subtitle' => '',
                'summary'  => '',
                'status'   => 'published',
            );
        }

        $author = eottae_column_get_author($row['mb_id'] ?? '');
        $like_count = function_exists('eottae_column_like_count') ? eottae_column_like_count($wr_id) : 0;
        $read_time = (int) ($meta['read_time'] ?? 0);
        if ($read_time < 1) {
            $read_time = eottae_column_calc_read_time($row['wr_content'] ?? '');
        }

        $category = (string) ($meta['category'] ?? '');
        if ($category === '' && !empty($row['ca_name'])) {
            $category = array_search($row['ca_name'], eottae_column_category_options(), true) ?: '';
        }

        $summary = trim((string) ($meta['summary'] ?? ''));
        if ($summary === '') {
            $summary = cut_str(strip_tags($row['wr_content'] ?? ''), 120, '…');
        }

        $enriched = array_merge($row, array(
            'meta'              => $meta,
            'subtitle'          => (string) ($meta['subtitle'] ?? ''),
            'summary'           => $summary,
            'category'          => $category,
            'category_label'    => eottae_column_category_label($category),
            'area'              => (string) ($meta['area'] ?? ''),
            'area_label'        => eottae_column_area_label($meta['area'] ?? ''),
            'tags'              => (string) ($meta['tags'] ?? ''),
            'thumbnail_url'     => eottae_column_thumbnail_url($meta['thumbnail'] ?? '', $row['wr_content'] ?? ''),
            'read_time'         => $read_time,
            'read_time_label'   => $read_time.'분 읽기',
            'status'            => (string) ($meta['status'] ?? 'published'),
            'is_featured'       => !empty($meta['is_featured']),
            'is_recommended'    => !empty($meta['is_recommended']),
            'is_representative' => !empty($meta['is_representative']),
            'like_count'        => $like_count,
            'view_url'          => eottae_column_view_url($wr_id),
            'author'            => $author,
            'author_name'       => $author ? ($author['display_name'] ?? '') : get_text($row['wr_name'] ?? ''),
            'author_title'      => $author ? ($author['title'] ?? '') : '',
            'author_profile_url'=> $author ? ($author['profile_url'] ?? '') : '',
            'author_image_url'  => $author ? ($author['profile_image_url'] ?? '') : '',
            'date_label'        => substr((string) ($row['wr_datetime'] ?? ''), 0, 10),
            'modified_label'    => substr((string) ($row['wr_last'] ?? ''), 0, 10),
        ));

        if (!empty($opts['member_mb_id']) && function_exists('eottae_column_member_liked')) {
            $enriched['liked'] = eottae_column_member_liked($wr_id, $opts['member_mb_id']);
        }
        if (!empty($opts['member_mb_id']) && function_exists('eottae_column_member_bookmarked')) {
            $enriched['bookmarked'] = eottae_column_member_bookmarked($wr_id, $opts['member_mb_id']);
        }

        return $enriched;
    }
}

if (!function_exists('eottae_column_list')) {
    function eottae_column_list(array $opts = array())
    {
        global $g5;

        $limit = max(1, min(100, (int) ($opts['limit'] ?? 20)));
        $offset = max(0, (int) ($opts['offset'] ?? 0));
        $category = preg_replace('/[^a-z_]/', '', (string) ($opts['category'] ?? ''));
        $mb_id = trim((string) ($opts['mb_id'] ?? ''));
        $status = preg_replace('/[^a-z_]/', '', (string) ($opts['status'] ?? 'published'));
        $include_hidden = !empty($opts['include_hidden']);
        $sort = preg_replace('/[^a-z_]/', '', (string) ($opts['sort'] ?? 'latest'));
        $featured_only = !empty($opts['featured_only']);
        $recommended_only = !empty($opts['recommended_only']);
        $member_mb_id = trim((string) ($opts['member_mb_id'] ?? ''));

        $write_table = eottae_column_write_table();
        $meta_table = $g5['sebu_columns_meta_table'];

        $where = " w.wr_is_comment = 0 ";
        if (!$include_hidden) {
            if ($status !== '') {
                $where .= " AND m.status = '".sql_escape_string($status)."' ";
            } else {
                $where .= " AND m.status = 'published' ";
            }
        }
        if ($category !== '') {
            $where .= " AND m.category = '".sql_escape_string($category)."' ";
        }
        if ($mb_id !== '') {
            $where .= " AND w.mb_id = '".sql_escape_string($mb_id)."' ";
        }
        if ($featured_only) {
            $where .= " AND m.is_featured = 1 ";
        }
        if ($recommended_only) {
            $where .= " AND m.is_recommended = 1 ";
        }

        $order = 'w.wr_datetime DESC';
        if ($sort === 'popular') {
            $order = 'w.wr_hit DESC, w.wr_datetime DESC';
        } elseif ($sort === 'likes') {
            $order = 'like_count DESC, w.wr_datetime DESC';
        } elseif ($sort === 'comments') {
            $order = 'w.wr_comment DESC, w.wr_datetime DESC';
        }

        $likes_table = $g5['sebu_column_likes_table'] ?? '';
        $like_join = '';
        $like_select = '0 AS like_count';
        if ($likes_table !== '' && eottae_column_table_exists($likes_table)) {
            $like_join = " LEFT JOIN (
                SELECT wr_id, COUNT(*) AS like_count
                FROM `{$likes_table}`
                GROUP BY wr_id
            ) lk ON lk.wr_id = w.wr_id ";
            $like_select = 'IFNULL(lk.like_count, 0) AS like_count';
        }

        $sql = "
            SELECT w.*, m.*, {$like_select}
            FROM `{$write_table}` w
            INNER JOIN `{$meta_table}` m ON m.wr_id = w.wr_id
            {$like_join}
            WHERE {$where}
            ORDER BY {$order}
            LIMIT {$offset}, {$limit}
        ";

        $result = sql_query($sql, false);
        $items = array();
        while ($row = sql_fetch_array($result)) {
            $items[] = eottae_column_enrich_post($row, array('member_mb_id' => $member_mb_id));
        }

        return $items;
    }
}

if (!function_exists('eottae_column_get_post')) {
    function eottae_column_get_post($wr_id, array $opts = array())
    {
        $row = eottae_column_get_write_row($wr_id);
        if (!$row) {
            return null;
        }

        $post = eottae_column_enrich_post($row, $opts);
        $status = (string) ($post['status'] ?? 'published');
        $include_hidden = !empty($opts['include_hidden']);
        $viewer_mb_id = trim((string) ($opts['member_mb_id'] ?? ''));
        $is_owner = $viewer_mb_id !== '' && $viewer_mb_id === trim((string) ($row['mb_id'] ?? ''));
        $is_super = !empty($opts['is_super']);

        if (!$include_hidden && $status !== 'published' && !$is_owner && !$is_super) {
            return null;
        }

        if (empty($opts['skip_hit'])) {
            eottae_column_increment_hit($wr_id);
            $post['wr_hit'] = (int) ($post['wr_hit'] ?? 0) + 1;
        }

        return $post;
    }
}

if (!function_exists('eottae_column_increment_hit')) {
    function eottae_column_increment_hit($wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return;
        }

        $write_table = eottae_column_write_table();
        sql_query(" UPDATE `{$write_table}` SET wr_hit = wr_hit + 1 WHERE wr_id = '{$wr_id}' ", false);
    }
}

if (!function_exists('eottae_column_save_post')) {
    function eottae_column_save_post(array $input, array $writer, $is_super = false)
    {
        global $g5;

        $mb_id = trim((string) ($writer['mb_id'] ?? ''));
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인이 필요합니다.');
        }
        if (!eottae_column_can_write($mb_id, $is_super)) {
            return array('ok' => false, 'message' => '칼럼니스트만 컬럼을 작성할 수 있습니다.');
        }

        $wr_id = (int) ($input['wr_id'] ?? 0);
        if ($wr_id > 0 && !eottae_column_can_edit($mb_id, $wr_id, $is_super)) {
            return array('ok' => false, 'message' => '수정 권한이 없습니다.');
        }

        $subject = trim(strip_tags((string) ($input['wr_subject'] ?? $input['title'] ?? '')));
        $content = trim((string) ($input['wr_content'] ?? $input['content'] ?? ''));
        if ($subject === '') {
            return array('ok' => false, 'message' => '제목을 입력해 주세요.');
        }
        if ($content === '') {
            return array('ok' => false, 'message' => '본문을 입력해 주세요.');
        }

        $status = preg_replace('/[^a-z_]/', '', (string) ($input['status'] ?? 'published'));
        if (!isset(eottae_column_status_options()[$status])) {
            $status = 'published';
        }

        $category = preg_replace('/[^a-z_]/', '', (string) ($input['category'] ?? ''));
        $category_label = eottae_column_category_label($category);
        $subtitle = trim(strip_tags((string) ($input['subtitle'] ?? '')));
        $summary = trim(strip_tags((string) ($input['summary'] ?? '')));
        $tags = trim(strip_tags((string) ($input['tags'] ?? '')));
        $area = preg_replace('/[^a-z0-9_]/', '', (string) ($input['area'] ?? ''));
        $related_url = trim((string) ($input['related_url'] ?? ''));
        $related_room_id = (int) ($input['related_room_id'] ?? 0);
        $related_event_id = (int) ($input['related_event_id'] ?? 0);
        $read_time = eottae_column_calc_read_time($content);
        $is_featured = $is_super && !empty($input['is_featured']) ? 1 : 0;
        $is_recommended = $is_super && !empty($input['is_recommended']) ? 1 : 0;
        $is_representative = $is_super && !empty($input['is_representative']) ? 1 : 0;

        if ($wr_id > 0) {
            $existing = eottae_column_get_write_row($wr_id);
            if (!$existing) {
                return array('ok' => false, 'message' => '글을 찾을 수 없습니다.');
            }
            if (!$is_super) {
                $meta_existing = eottae_column_get_meta($wr_id);
                $is_featured = (int) ($meta_existing['is_featured'] ?? 0);
                $is_recommended = (int) ($meta_existing['is_recommended'] ?? 0);
                $is_representative = (int) ($meta_existing['is_representative'] ?? 0);
            }
        }

        $author = eottae_column_get_author($mb_id);
        $wr_name = $author ? ($author['display_name'] ?? '') : get_text($writer['mb_nick'] ?? $writer['mb_name'] ?? '');
        $wr_email = sql_escape_string($writer['mb_email'] ?? '');
        $mb_id_sql = sql_escape_string($mb_id);
        $subject_sql = sql_escape_string($subject);
        $content_sql = sql_escape_string($content);
        $ca_name_sql = sql_escape_string($category_label !== '' ? $category_label : '생활정보');
        $wr_name_sql = sql_escape_string($wr_name);
        $seo = sql_escape_string(preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($subject)));
        $write_table = eottae_column_write_table();
        $bo_table = eottae_column_board_table();
        $now = G5_TIME_YMDHIS;

        if ($wr_id < 1) {
            sql_query(" INSERT INTO `{$write_table}` SET
                wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM `{$write_table}` AS sq),
                wr_reply = '',
                wr_comment = 0,
                ca_name = '{$ca_name_sql}',
                wr_option = 'html1',
                wr_subject = '{$subject_sql}',
                wr_content = '{$content_sql}',
                wr_seo_title = '{$seo}',
                wr_link1 = '',
                wr_link2 = '',
                wr_link1_hit = 0,
                wr_link2_hit = 0,
                wr_hit = 0,
                wr_good = 0,
                wr_nogood = 0,
                mb_id = '{$mb_id_sql}',
                wr_password = '',
                wr_name = '{$wr_name_sql}',
                wr_email = '{$wr_email}',
                wr_homepage = '',
                wr_datetime = '{$now}',
                wr_last = '{$now}',
                wr_ip = '".sql_escape_string($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1')."',
                wr_1 = '".sql_escape_string($subtitle)."',
                wr_2 = '".sql_escape_string($status)."'
            ", false);
            $wr_id = (int) sql_insert_id();
            if ($wr_id < 1) {
                return array('ok' => false, 'message' => '컬럼 등록에 실패했습니다.');
            }
            sql_query(" UPDATE `{$write_table}` SET wr_parent = '{$wr_id}' WHERE wr_id = '{$wr_id}' ", false);
            sql_query(" INSERT INTO {$g5['board_new_table']}
                (bo_table, wr_id, wr_parent, bn_datetime, mb_id)
                VALUES ('".sql_escape_string($bo_table)."', '{$wr_id}', '{$wr_id}', '{$now}', '{$mb_id_sql}')
            ", false);
            sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '".sql_escape_string($bo_table)."' ", false);
        } else {
            sql_query(" UPDATE `{$write_table}` SET
                ca_name = '{$ca_name_sql}',
                wr_subject = '{$subject_sql}',
                wr_content = '{$content_sql}',
                wr_seo_title = '{$seo}',
                wr_name = '{$wr_name_sql}',
                wr_last = '{$now}',
                wr_1 = '".sql_escape_string($subtitle)."',
                wr_2 = '".sql_escape_string($status)."'
                WHERE wr_id = '{$wr_id}'
            ", false);
        }

        $thumbnail = '';
        if (!empty($input['thumbnail_keep'])) {
            $meta_old = eottae_column_get_meta($wr_id);
            $thumbnail = (string) ($meta_old['thumbnail'] ?? '');
        }
        if (!empty($_FILES['thumbnail']['tmp_name'])) {
            $upload = eottae_column_upload_thumbnail($_FILES['thumbnail'], $wr_id);
            if (!empty($upload['ok']) && !empty($upload['path'])) {
                $thumbnail = $upload['path'];
            }
        }

        eottae_column_bootstrap_tables();
        $meta_table = $g5['sebu_columns_meta_table'];
        $meta_exists = eottae_column_get_meta($wr_id);
        $thumb_sql = sql_escape_string($thumbnail);

        if ($meta_exists) {
            sql_query(" UPDATE `{$meta_table}` SET
                subtitle = '".sql_escape_string($subtitle)."',
                summary = '".sql_escape_string($summary)."',
                thumbnail = '{$thumb_sql}',
                category = '".sql_escape_string($category)."',
                tags = '".sql_escape_string($tags)."',
                area = '".sql_escape_string($area)."',
                related_url = '".sql_escape_string($related_url)."',
                related_room_id = '{$related_room_id}',
                related_event_id = '{$related_event_id}',
                read_time = '{$read_time}',
                is_featured = '{$is_featured}',
                is_recommended = '{$is_recommended}',
                is_representative = '{$is_representative}',
                status = '".sql_escape_string($status)."',
                updated_at = '{$now}'
                WHERE wr_id = '{$wr_id}'
            ", false);
        } else {
            sql_query(" INSERT INTO `{$meta_table}` SET
                wr_id = '{$wr_id}',
                subtitle = '".sql_escape_string($subtitle)."',
                summary = '".sql_escape_string($summary)."',
                thumbnail = '{$thumb_sql}',
                category = '".sql_escape_string($category)."',
                tags = '".sql_escape_string($tags)."',
                area = '".sql_escape_string($area)."',
                related_url = '".sql_escape_string($related_url)."',
                related_room_id = '{$related_room_id}',
                related_event_id = '{$related_event_id}',
                read_time = '{$read_time}',
                is_featured = '{$is_featured}',
                is_recommended = '{$is_recommended}',
                is_representative = '{$is_representative}',
                status = '".sql_escape_string($status)."',
                created_at = '{$now}',
                updated_at = '{$now}'
            ", false);
        }

        if ($status === 'published' && function_exists('eottae_member_growth_add_score')) {
            include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
            eottae_member_growth_add_score($mb_id, 'life_info_post', 0, 'column', $wr_id, '생활정보 컬럼 발행');
        }

        eottae_column_sync_author_badges($mb_id);

        if (function_exists('delete_cache_latest')) {
            delete_cache_latest($bo_table);
        }

        return array(
            'ok'     => true,
            'wr_id'  => $wr_id,
            'message'=> $status === 'draft' ? '임시저장되었습니다.' : '컬럼이 발행되었습니다.',
            'view_url' => eottae_column_view_url($wr_id),
        );
    }
}

if (!function_exists('eottae_column_upload_thumbnail')) {
    function eottae_column_upload_thumbnail(array $file, $wr_id = 0)
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return array('ok' => false, 'message' => '업로드 파일이 없습니다.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'), true)) {
            return array('ok' => false, 'message' => '이미지 파일만 업로드할 수 있습니다.');
        }

        $dir = G5_DATA_PATH.'/column/thumbnails';
        if (!is_dir($dir)) {
            @mkdir($dir, G5_DIR_PERMISSION, true);
        }

        $filename = 'col_'.(int) $wr_id.'_'.date('YmdHis').'_'.substr(md5(uniqid('', true)), 0, 8).'.'.$ext;
        $dest = $dir.'/'.$filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return array('ok' => false, 'message' => '썸네일 저장에 실패했습니다.');
        }

        @chmod($dest, G5_FILE_PERMISSION);

        return array('ok' => true, 'path' => 'column/thumbnails/'.$filename);
    }
}

if (!function_exists('eottae_column_save_author')) {
    function eottae_column_save_author(array $input, $admin_mode = false)
    {
        eottae_column_bootstrap_tables();
        global $g5;

        $mb_id = trim((string) ($input['mb_id'] ?? ''));
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '회원 ID가 필요합니다.');
        }

        $now = G5_TIME_YMDHIS;
        $existing = eottae_column_get_author($mb_id);
        $pen_name = trim(strip_tags((string) ($input['pen_name'] ?? '')));
        $title = trim(strip_tags((string) ($input['title'] ?? '')));
        $specialty = trim(strip_tags((string) ($input['specialty'] ?? '')));
        $bio = trim((string) ($input['bio'] ?? ''));
        $area = preg_replace('/[^a-z0-9_]/', '', (string) ($input['area'] ?? ''));
        $website_url = eottae_column_normalize_url($input['website_url'] ?? '');
        $social = eottae_column_collect_social_from_input($input);
        $sns_url = $social['youtube_url'];
        $contact_open = !empty($input['contact_open']) ? 1 : 0;
        $is_active = !isset($input['is_active']) || !empty($input['is_active']) ? 1 : 0;
        $is_visible = !isset($input['is_visible']) || !empty($input['is_visible']) ? 1 : 0;
        $is_official = $admin_mode && !empty($input['is_official']) ? 1 : (int) ($existing['is_official'] ?? 0);

        $profile_image = (string) ($existing['profile_image'] ?? '');
        if (!empty($input['profile_image_keep'])) {
            $profile_image = (string) ($existing['profile_image'] ?? '');
        }
        if (!empty($input['profile_image']) && empty($_FILES['profile_image']['tmp_name'])) {
            $profile_image = trim((string) $input['profile_image']);
        }
        if (!empty($_FILES['profile_image']['tmp_name'])) {
            $upload = eottae_column_upload_profile_image($_FILES['profile_image'], $mb_id);
            if (!empty($upload['ok']) && !empty($upload['path'])) {
                $profile_image = $upload['path'];
            }
        }

        $social_sql = '';
        foreach ($social as $key => $value) {
            $social_sql .= ", {$key} = '".sql_escape_string($value)."'";
        }

        $table = $g5['sebu_column_authors_table'];
        $mb_id_sql = sql_escape_string($mb_id);

        if ($existing) {
            sql_query(" UPDATE `{$table}` SET
                pen_name = '".sql_escape_string($pen_name)."',
                title = '".sql_escape_string($title)."',
                specialty = '".sql_escape_string($specialty)."',
                bio = '".sql_escape_string($bio)."',
                profile_image = '".sql_escape_string($profile_image)."',
                area = '".sql_escape_string($area)."',
                website_url = '".sql_escape_string($website_url)."',
                sns_url = '".sql_escape_string($sns_url)."'
                {$social_sql},
                contact_open = '{$contact_open}',
                is_official = '{$is_official}',
                is_active = '{$is_active}',
                is_visible = '{$is_visible}',
                updated_at = '{$now}'
                WHERE mb_id = '{$mb_id_sql}'
            ", false);
        } else {
            sql_query(" INSERT INTO `{$table}` SET
                mb_id = '{$mb_id_sql}',
                pen_name = '".sql_escape_string($pen_name)."',
                title = '".sql_escape_string($title)."',
                specialty = '".sql_escape_string($specialty)."',
                bio = '".sql_escape_string($bio)."',
                profile_image = '".sql_escape_string($profile_image)."',
                area = '".sql_escape_string($area)."',
                website_url = '".sql_escape_string($website_url)."',
                sns_url = '".sql_escape_string($sns_url)."'
                {$social_sql},
                contact_open = '{$contact_open}',
                is_official = '{$is_official}',
                is_active = '{$is_active}',
                is_visible = '{$is_visible}',
                created_at = '{$now}',
                updated_at = '{$now}'
            ", false);
        }

        eottae_column_sync_author_badges($mb_id);

        return array('ok' => true, 'message' => '칼럼니스트 정보가 저장되었습니다.');
    }
}

if (!function_exists('eottae_column_upload_profile_image')) {
    function eottae_column_upload_profile_image(array $file, $mb_id)
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return array('ok' => false);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'), true)) {
            return array('ok' => false, 'message' => '이미지 파일만 업로드할 수 있습니다.');
        }

        $dir = G5_DATA_PATH.'/column/authors';
        if (!is_dir($dir)) {
            @mkdir($dir, G5_DIR_PERMISSION, true);
        }

        $safe_mb = preg_replace('/[^a-z0-9_]/', '', (string) $mb_id);
        $filename = 'author_'.$safe_mb.'_'.date('YmdHis').'.'.$ext;
        $dest = $dir.'/'.$filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return array('ok' => false);
        }
        @chmod($dest, G5_FILE_PERMISSION);

        return array('ok' => true, 'path' => 'column/authors/'.$filename);
    }
}

if (!function_exists('eottae_column_application_status_options')) {
    function eottae_column_application_status_options()
    {
        return array(
            'pending'  => '검토중',
            'approved' => '승인',
            'rejected' => '반려',
        );
    }
}

if (!function_exists('eottae_column_application_status_label')) {
    function eottae_column_application_status_label($status)
    {
        $options = eottae_column_application_status_options();
        $status = preg_replace('/[^a-z_]/', '', (string) $status);

        return isset($options[$status]) ? $options[$status] : $status;
    }
}

if (!function_exists('eottae_column_get_latest_application')) {
    function eottae_column_get_latest_application($mb_id)
    {
        eottae_column_bootstrap_tables();
        global $g5;

        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return null;
        }

        $table = $g5['sebu_column_author_applications_table'];
        if (!eottae_column_table_exists($table)) {
            return null;
        }

        $row = sql_fetch("
            SELECT *
            FROM `{$table}`
            WHERE mb_id = '".sql_escape_string($mb_id)."'
            ORDER BY application_id DESC
            LIMIT 1
        ", false);

        return is_array($row) && !empty($row['application_id']) ? $row : null;
    }
}

if (!function_exists('eottae_column_submit_application')) {
    function eottae_column_submit_application(array $input, array $member)
    {
        eottae_column_bootstrap_tables();
        global $g5;

        $mb_id = trim((string) ($member['mb_id'] ?? ''));
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 신청할 수 있습니다.');
        }
        if (eottae_column_is_columnist($mb_id)) {
            return array('ok' => false, 'message' => '이미 칼럼니스트로 등록되어 있습니다.');
        }

        $latest = eottae_column_get_latest_application($mb_id);
        if ($latest && ($latest['status'] ?? '') === 'pending') {
            return array('ok' => false, 'message' => '이미 검토 중인 신청서가 있습니다.');
        }

        $pen_name = trim(strip_tags((string) ($input['pen_name'] ?? '')));
        $title = trim(strip_tags((string) ($input['title'] ?? '')));
        $specialty = trim(strip_tags((string) ($input['specialty'] ?? '')));
        $bio = trim((string) ($input['bio'] ?? ''));
        $area = preg_replace('/[^a-z0-9_]/', '', (string) ($input['area'] ?? ''));
        $website_url = eottae_column_normalize_url($input['website_url'] ?? '');
        $social = eottae_column_collect_social_from_input($input);
        $sns_url = $social['youtube_url'];
        $sample_url = eottae_column_normalize_url($input['sample_url'] ?? '');
        $message = trim((string) ($input['message'] ?? ''));

        if ($pen_name === '') {
            $pen_name = trim((string) ($member['mb_nick'] ?? $member['mb_name'] ?? $mb_id));
        }
        if ($title === '' || $specialty === '' || $bio === '') {
            return array('ok' => false, 'message' => '타이틀, 전문 분야, 소개글은 필수입니다.');
        }

        $profile_image = '';
        if (!empty($_FILES['profile_image']['tmp_name'])) {
            $upload = eottae_column_upload_profile_image($_FILES['profile_image'], $mb_id);
            if (empty($upload['ok'])) {
                return array('ok' => false, 'message' => $upload['message'] ?? '프로필 사진 업로드에 실패했습니다.');
            }
            $profile_image = (string) ($upload['path'] ?? '');
        }

        $social_sql = '';
        foreach ($social as $key => $value) {
            $social_sql .= ", {$key} = '".sql_escape_string($value)."'";
        }

        $table = $g5['sebu_column_author_applications_table'];
        $now = G5_TIME_YMDHIS;

        sql_query(" INSERT INTO `{$table}` SET
            mb_id = '".sql_escape_string($mb_id)."',
            pen_name = '".sql_escape_string($pen_name)."',
            title = '".sql_escape_string($title)."',
            specialty = '".sql_escape_string($specialty)."',
            bio = '".sql_escape_string($bio)."',
            area = '".sql_escape_string($area)."',
            website_url = '".sql_escape_string($website_url)."',
            sns_url = '".sql_escape_string($sns_url)."',
            profile_image = '".sql_escape_string($profile_image)."'
            {$social_sql},
            sample_url = '".sql_escape_string($sample_url)."',
            message = '".sql_escape_string($message)."',
            status = 'pending',
            created_at = '{$now}',
            updated_at = '{$now}'
        ", false);

        $application_id = (int) sql_insert_id();
        if ($application_id < 1) {
            return array('ok' => false, 'message' => '신청서 저장에 실패했습니다.');
        }

        return array('ok' => true, 'message' => '칼럼니스트 신청이 접수되었습니다.', 'application_id' => $application_id);
    }
}

if (!function_exists('eottae_column_list_applications')) {
    function eottae_column_list_applications($status = 'pending', $limit = 100)
    {
        eottae_column_bootstrap_tables();
        global $g5;

        $table = $g5['sebu_column_author_applications_table'];
        if (!eottae_column_table_exists($table)) {
            return array();
        }

        $status = preg_replace('/[^a-z_]/', '', (string) $status);
        $limit = max(1, min(200, (int) $limit));
        $where = '1=1';
        if ($status !== '') {
            $where .= " AND a.status = '".sql_escape_string($status)."' ";
        }

        $result = sql_query("
            SELECT a.*, m.mb_nick, m.mb_name, m.mb_email
            FROM `{$table}` a
            LEFT JOIN {$g5['member_table']} m ON m.mb_id = a.mb_id
            WHERE {$where}
            ORDER BY a.application_id DESC
            LIMIT {$limit}
        ", false);

        $items = array();
        while ($row = sql_fetch_array($result)) {
            $row['status_label'] = eottae_column_application_status_label($row['status'] ?? '');
            $row['area_label'] = eottae_column_area_label($row['area'] ?? '');
            $row['has_profile_image'] = trim((string) ($row['profile_image'] ?? '')) !== '';
            $row['profile_image_url'] = $row['has_profile_image']
                ? eottae_column_profile_image_url($row['profile_image'] ?? '')
                : '';
            $row['profile_initials'] = eottae_column_initials_from_name($row['pen_name'] ?? $row['mb_nick'] ?? '');
            $row['social_links'] = eottae_column_author_social_links($row);
            $items[] = $row;
        }

        return $items;
    }
}

if (!function_exists('eottae_column_review_application')) {
    function eottae_column_review_application($application_id, $decision, $admin_mb_id = '', $memo = '')
    {
        eottae_column_bootstrap_tables();
        global $g5;

        $application_id = (int) $application_id;
        $decision = preg_replace('/[^a-z_]/', '', (string) $decision);
        if ($application_id < 1 || !in_array($decision, array('approve', 'reject'), true)) {
            return array('ok' => false, 'message' => '잘못된 요청입니다.');
        }

        $table = $g5['sebu_column_author_applications_table'];
        $application = sql_fetch(" SELECT * FROM `{$table}` WHERE application_id = '{$application_id}' LIMIT 1 ", false);
        if (empty($application['application_id'])) {
            return array('ok' => false, 'message' => '신청서를 찾을 수 없습니다.');
        }
        if (($application['status'] ?? '') !== 'pending') {
            return array('ok' => false, 'message' => '이미 처리된 신청서입니다.');
        }

        $status = $decision === 'approve' ? 'approved' : 'rejected';
        $now = G5_TIME_YMDHIS;
        sql_query(" UPDATE `{$table}` SET
            status = '".sql_escape_string($status)."',
            review_memo = '".sql_escape_string(trim(strip_tags((string) $memo)))."',
            reviewed_by = '".sql_escape_string($admin_mb_id)."',
            reviewed_at = '{$now}',
            updated_at = '{$now}'
            WHERE application_id = '{$application_id}'
        ", false);

        if ($decision === 'approve') {
            $author_input = array_merge(
                array(
                    'mb_id'          => $application['mb_id'],
                    'pen_name'       => $application['pen_name'],
                    'title'          => $application['title'],
                    'specialty'      => $application['specialty'],
                    'bio'            => $application['bio'],
                    'area'           => $application['area'],
                    'website_url'    => $application['website_url'],
                    'profile_image'  => $application['profile_image'] ?? '',
                    'is_active'      => 1,
                    'is_visible'     => 1,
                    'is_official'    => 0,
                ),
                eottae_column_social_from_row($application)
            );
            $saved = eottae_column_save_author($author_input, true);
            if (empty($saved['ok'])) {
                return $saved;
            }
        }

        return array('ok' => true, 'message' => $decision === 'approve' ? '칼럼니스트로 승인했습니다.' : '신청을 반려했습니다.');
    }
}

if (!function_exists('eottae_column_list_authors')) {
    function eottae_column_list_authors(array $opts = array())
    {
        eottae_column_bootstrap_tables();
        global $g5;

        $limit = max(1, min(50, (int) ($opts['limit'] ?? 10)));
        $table = $g5['sebu_column_authors_table'];
        $result = sql_query("
            SELECT a.*, m.mb_nick, m.mb_name
            FROM `{$table}` a
            LEFT JOIN {$g5['member_table']} m ON m.mb_id = a.mb_id
            WHERE a.is_active = 1 AND a.is_visible = 1
            ORDER BY a.updated_at DESC
            LIMIT {$limit}
        ", false);

        $items = array();
        while ($row = sql_fetch_array($result)) {
            $author = eottae_column_enrich_author($row);
            if ((int) ($author['stats']['column_count'] ?? 0) > 0) {
                $items[] = $author;
            }
        }

        usort($items, function ($a, $b) {
            return (int) ($b['stats']['total_views'] ?? 0) <=> (int) ($a['stats']['total_views'] ?? 0);
        });

        return array_slice($items, 0, $limit);
    }
}

if (!function_exists('eottae_column_get_monthly_columnist')) {
    function eottae_column_get_monthly_columnist()
    {
        eottae_column_bootstrap_tables();
        global $g5;

        $table = $g5['sebu_column_author_awards_table'];
        if (!eottae_column_table_exists($table)) {
            return null;
        }

        $today = date('Y-m-d');
        $row = sql_fetch("
            SELECT *
            FROM `{$table}`
            WHERE award_type = 'monthly_columnist'
              AND is_active = 1
              AND (start_date = '0000-00-00' OR start_date <= '{$today}')
              AND (end_date = '0000-00-00' OR end_date >= '{$today}')
            ORDER BY award_id DESC
            LIMIT 1
        ", false);

        if (!is_array($row) || empty($row['award_id'])) {
            return null;
        }

        $author = eottae_column_get_author($row['mb_id'] ?? '');
        if (!$author) {
            return null;
        }

        $month_start = date('Y-m-01');
        $stats = eottae_column_author_month_stats($author['mb_id'], $month_start);

        return array(
            'award'  => $row,
            'author' => $author,
            'month_stats' => $stats,
            'representative_url' => eottae_column_author_representative_url($author['mb_id']),
        );
    }
}

if (!function_exists('eottae_column_author_month_stats')) {
    function eottae_column_author_month_stats($mb_id, $from_date)
    {
        global $g5;
        $write_table = eottae_column_write_table();
        $meta_table = $g5['sebu_columns_meta_table'];
        $mb_id_sql = sql_escape_string($mb_id);
        $from_date_sql = sql_escape_string($from_date);

        $row = sql_fetch("
            SELECT COUNT(*) AS column_count, IFNULL(SUM(w.wr_hit), 0) AS total_views
            FROM `{$write_table}` w
            INNER JOIN `{$meta_table}` m ON m.wr_id = w.wr_id
            WHERE w.mb_id = '{$mb_id_sql}'
              AND w.wr_is_comment = 0
              AND m.status = 'published'
              AND w.wr_datetime >= '{$from_date_sql} 00:00:00'
        ", false);

        return array(
            'column_count' => (int) ($row['column_count'] ?? 0),
            'total_views'  => (int) ($row['total_views'] ?? 0),
        );
    }
}

if (!function_exists('eottae_column_author_representative_url')) {
    function eottae_column_author_representative_url($mb_id)
    {
        $posts = eottae_column_list(array(
            'mb_id' => $mb_id,
            'limit' => 1,
            'sort'  => 'popular',
        ));
        if (!empty($posts[0]['view_url'])) {
            return $posts[0]['view_url'];
        }

        return eottae_column_author_url($mb_id);
    }
}

if (!function_exists('eottae_column_save_monthly_award')) {
    function eottae_column_save_monthly_award(array $input, $admin_mb_id = '')
    {
        eottae_column_bootstrap_tables();
        global $g5;

        $mb_id = trim((string) ($input['mb_id'] ?? ''));
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '칼럼니스트를 선택해 주세요.');
        }

        $reason = trim(strip_tags((string) ($input['reason'] ?? '')));
        $start_date = trim((string) ($input['start_date'] ?? date('Y-m-01')));
        $end_date = trim((string) ($input['end_date'] ?? date('Y-m-t')));
        $show_on_main = !empty($input['show_on_main']) ? 1 : 0;
        $author = eottae_column_get_author($mb_id);
        $title = $author ? ($author['display_name'] ?? '') : $mb_id;

        sql_query(" UPDATE `{$g5['sebu_column_author_awards_table']}`
            SET is_active = 0
            WHERE award_type = 'monthly_columnist' AND is_active = 1
        ", false);

        sql_query(" INSERT INTO `{$g5['sebu_column_author_awards_table']}` SET
            mb_id = '".sql_escape_string($mb_id)."',
            award_type = 'monthly_columnist',
            title = '".sql_escape_string($title)."',
            reason = '".sql_escape_string($reason)."',
            start_date = '".sql_escape_string($start_date)."',
            end_date = '".sql_escape_string($end_date)."',
            show_on_main = '{$show_on_main}',
            is_active = 1,
            created_by = '".sql_escape_string($admin_mb_id)."',
            created_at = '".G5_TIME_YMDHIS."'
        ", false);

        if (function_exists('eottae_member_growth_award_badge')) {
            include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
            $badge_id = eottae_column_badge_id_by_code('monthly_columnist');
            if ($badge_id > 0) {
                eottae_member_growth_award_badge($mb_id, $badge_id, $admin_mb_id, '이달의 칼럼니스트');
            }
        }

        return array('ok' => true, 'message' => '이달의 칼럼니스트가 설정되었습니다.');
    }
}

if (!function_exists('eottae_column_badge_definitions')) {
    function eottae_column_badge_definitions()
    {
        return array(
            'first_column'      => array('name' => '첫 컬럼 작성', 'icon' => '📝'),
            'popular_column'    => array('name' => '인기 컬럼 선정', 'icon' => '🔥'),
            'monthly_columnist' => array('name' => '이달의 칼럼니스트', 'icon' => '⭐'),
            'life_expert'       => array('name' => '생활정보 전문가', 'icon' => '🏠'),
            'family_expert'     => array('name' => '교육정보 전문가', 'icon' => '👨‍👩‍👧'),
            'business_expert'   => array('name' => '사업정보 전문가', 'icon' => '💼'),
            'area_reporter'     => array('name' => '지역소식 리포터', 'icon' => '📍'),
            'interviewer'       => array('name' => '교민 인터뷰어', 'icon' => '🎤'),
            'views_10k'         => array('name' => '누적 조회 1만', 'icon' => '👀'),
            'likes_100'         => array('name' => '공감 100개', 'icon' => '💛'),
            'official'          => array('name' => '공식 칼럼니스트', 'icon' => '✅'),
        );
    }
}

if (!function_exists('eottae_column_badge_id_by_code')) {
    function eottae_column_badge_id_by_code($code)
    {
        eottae_column_bootstrap_tables();
        global $g5;
        $table = $g5['sebu_badges_table'] ?? G5_TABLE_PREFIX.'sebu_badges';
        if (!eottae_column_table_exists($table)) {
            return 0;
        }

        $badge_code = 'column_'.preg_replace('/[^a-z_]/', '', (string) $code);
        $row = sql_fetch(" SELECT badge_id FROM `{$table}` WHERE badge_code = '".sql_escape_string($badge_code)."' LIMIT 1 ", false);

        return (int) ($row['badge_id'] ?? 0);
    }
}

if (!function_exists('eottae_column_ensure_badges')) {
    function eottae_column_ensure_badges()
    {
        if (!function_exists('eottae_member_growth_ensure_schema')) {
            include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
        }
        eottae_member_growth_ensure_schema();

        global $g5;
        $table = $g5['sebu_badges_table'] ?? G5_TABLE_PREFIX.'sebu_badges';
        if (!eottae_column_table_exists($table)) {
            return;
        }

        foreach (eottae_column_badge_definitions() as $code => $def) {
            $badge_code = 'column_'.$code;
            $exists = sql_fetch(" SELECT badge_id FROM `{$table}` WHERE badge_code = '".sql_escape_string($badge_code)."' LIMIT 1 ", false);
            if (!empty($exists['badge_id'])) {
                continue;
            }
            sql_query(" INSERT INTO `{$table}` SET
                badge_code = '".sql_escape_string($badge_code)."',
                badge_name = '".sql_escape_string($def['name'])."',
                badge_icon = '".sql_escape_string($def['icon'])."',
                badge_group = 'column',
                badge_desc = '".sql_escape_string('생활정보 컬럼')."',
                is_active = 1,
                sort_order = 0,
                created_at = '".G5_TIME_YMDHIS."'
            ", false);
        }
    }
}

if (!function_exists('eottae_column_sync_author_badges')) {
    function eottae_column_sync_author_badges($mb_id)
    {
        if (!function_exists('eottae_member_growth_award_badge')) {
            include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
        }
        eottae_column_ensure_badges();

        $author = eottae_column_get_author($mb_id);
        if (!$author) {
            return;
        }

        $stats = $author['stats'] ?? eottae_column_author_stats($mb_id);
        $awards = array();
        if ((int) ($stats['column_count'] ?? 0) >= 1) {
            $awards[] = 'first_column';
        }
        if ((int) ($stats['total_views'] ?? 0) >= 10000) {
            $awards[] = 'views_10k';
        }
        if ((int) ($stats['total_likes'] ?? 0) >= 100) {
            $awards[] = 'likes_100';
        }
        if (!empty($author['is_official'])) {
            $awards[] = 'official';
        }

        $specialty = (string) ($author['specialty'] ?? '');
        if (strpos($specialty, '생활') !== false) {
            $awards[] = 'life_expert';
        }
        if (strpos($specialty, '교육') !== false || strpos($specialty, '가족') !== false) {
            $awards[] = 'family_expert';
        }
        if (strpos($specialty, '사업') !== false) {
            $awards[] = 'business_expert';
        }
        if (strpos($specialty, '지역') !== false) {
            $awards[] = 'area_reporter';
        }
        if (strpos($specialty, '인터뷰') !== false) {
            $awards[] = 'interviewer';
        }

        foreach (array_unique($awards) as $code) {
            $badge_id = eottae_column_badge_id_by_code($code);
            if ($badge_id > 0) {
                eottae_member_growth_award_badge($mb_id, $badge_id, 'system', '컬럼 활동');
            }
        }
    }
}

if (!function_exists('eottae_column_apply_seo')) {
    function eottae_column_apply_seo(array $post)
    {
        global $page_title, $page_description, $page_og_image, $page_canonical;

        $title = get_text($post['wr_subject'] ?? '');
        $desc = get_text($post['summary'] ?? '');
        $page_title = $title !== '' ? $title.' | 세부 생활정보 컬럼' : '세부 생활정보 컬럼';
        $page_description = $desc;
        $page_og_image = $post['thumbnail_url'] ?? '';
        $page_canonical = $post['view_url'] ?? eottae_column_view_url($post['wr_id'] ?? 0);
    }
}

if (!function_exists('eottae_column_list_comments')) {
    function eottae_column_list_comments($wr_id)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return array();
        }

        $write_table = eottae_column_write_table();
        $result = sql_query("
            SELECT wr_id, wr_content, wr_name, mb_id, wr_datetime
            FROM `{$write_table}`
            WHERE wr_parent = '{$wr_id}'
              AND wr_is_comment = 1
            ORDER BY wr_comment ASC, wr_id ASC
        ", false);

        $items = array();
        while ($row = sql_fetch_array($result)) {
            $items[] = $row;
        }

        return $items;
    }
}

if (!function_exists('eottae_column_admin_set_flags')) {
    function eottae_column_admin_set_flags($wr_id, array $flags)
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return array('ok' => false);
        }

        eottae_column_bootstrap_tables();
        global $g5;
        $meta_table = $g5['sebu_columns_meta_table'];
        $sets = array();
        if (isset($flags['is_featured'])) {
            $sets[] = "is_featured = '".(!empty($flags['is_featured']) ? 1 : 0)."'";
        }
        if (isset($flags['is_recommended'])) {
            $sets[] = "is_recommended = '".(!empty($flags['is_recommended']) ? 1 : 0)."'";
        }
        if (isset($flags['status'])) {
            $status = preg_replace('/[^a-z_]/', '', (string) $flags['status']);
            $sets[] = "status = '".sql_escape_string($status)."'";
            $write_table = eottae_column_write_table();
            sql_query(" UPDATE `{$write_table}` SET wr_2 = '".sql_escape_string($status)."' WHERE wr_id = '{$wr_id}' ", false);
        }
        if (empty($sets)) {
            return array('ok' => false, 'message' => '변경할 항목이 없습니다.');
        }
        $sets[] = "updated_at = '".G5_TIME_YMDHIS."'";
        sql_query(" UPDATE `{$meta_table}` SET ".implode(', ', $sets)." WHERE wr_id = '{$wr_id}' ", false);

        return array('ok' => true, 'message' => '저장되었습니다.');
    }
}

if (!function_exists('eottae_column_admin_list_authors')) {
    function eottae_column_admin_list_authors($search = '')
    {
        eottae_column_bootstrap_tables();
        global $g5;
        $table = $g5['sebu_column_authors_table'];
        $where = '1=1';
        if ($search !== '') {
            $q = sql_escape_string($search);
            $where .= " AND (a.mb_id LIKE '%{$q}%' OR a.pen_name LIKE '%{$q}%' OR m.mb_nick LIKE '%{$q}%') ";
        }

        $result = sql_query("
            SELECT a.*, m.mb_nick, m.mb_name
            FROM `{$table}` a
            LEFT JOIN {$g5['member_table']} m ON m.mb_id = a.mb_id
            WHERE {$where}
            ORDER BY a.updated_at DESC
            LIMIT 200
        ", false);

        $items = array();
        while ($row = sql_fetch_array($result)) {
            $items[] = eottae_column_enrich_author($row);
        }

        return $items;
    }
}
