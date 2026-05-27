<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_golf_join_bootstrap_tables')) {
    function eottae_golf_join_bootstrap_tables()
    {
        global $g5;

        if (!isset($g5['sebu_golf_join_posts_table'])) {
            $g5['sebu_golf_join_posts_table'] = G5_TABLE_PREFIX.'sebu_golf_join_posts';
        }
        if (!isset($g5['sebu_golf_join_members_table'])) {
            $g5['sebu_golf_join_members_table'] = G5_TABLE_PREFIX.'sebu_golf_join_members';
        }
        if (!isset($g5['sebu_golf_courses_table'])) {
            $g5['sebu_golf_courses_table'] = G5_TABLE_PREFIX.'sebu_golf_courses';
        }
        if (!isset($g5['sebu_golf_join_reports_table'])) {
            $g5['sebu_golf_join_reports_table'] = G5_TABLE_PREFIX.'sebu_golf_join_reports';
        }
        if (!isset($g5['sebu_golf_join_chat_rooms_table'])) {
            $g5['sebu_golf_join_chat_rooms_table'] = G5_TABLE_PREFIX.'sebu_golf_join_chat_rooms';
        }
        if (!isset($g5['sebu_golf_join_chat_messages_table'])) {
            $g5['sebu_golf_join_chat_messages_table'] = G5_TABLE_PREFIX.'sebu_golf_join_chat_messages';
        }
    }
}

if (!function_exists('eottae_golf_join_table_names')) {
    function eottae_golf_join_table_names()
    {
        eottae_golf_join_bootstrap_tables();
        global $g5;

        return array(
            'courses'     => $g5['sebu_golf_courses_table'],
            'posts'       => $g5['sebu_golf_join_posts_table'],
            'members'     => $g5['sebu_golf_join_members_table'],
            'reports'     => $g5['sebu_golf_join_reports_table'],
            'chat_rooms'  => $g5['sebu_golf_join_chat_rooms_table'],
            'chat_messages' => $g5['sebu_golf_join_chat_messages_table'],
        );
    }
}

if (!function_exists('eottae_golf_join_admin_url')) {
    function eottae_golf_join_admin_url($tab = 'posts')
    {
        return G5_URL.'/page/eottae-admin-golf-join.php?tab='.urlencode($tab);
    }
}

if (!function_exists('eottae_golf_join_admin_proc_url')) {
    function eottae_golf_join_admin_proc_url()
    {
        return G5_URL.'/proc/eottae-golf-join-admin.php';
    }
}

if (!function_exists('eottae_golf_join_chat_proc_url')) {
    function eottae_golf_join_chat_proc_url()
    {
        return G5_URL.'/proc/eottae-golf-join-chat.php';
    }
}

if (!function_exists('eottae_golf_join_member_table')) {
    function eottae_golf_join_member_table()
    {
        global $g5;

        return isset($g5['member_table']) ? $g5['member_table'] : G5_TABLE_PREFIX.'member';
    }
}

if (!function_exists('eottae_golf_join_table_exists')) {
    function eottae_golf_join_table_exists($table)
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

if (!function_exists('eottae_golf_join_region_options')) {
    function eottae_golf_join_region_options()
    {
        return array(
            'cebu'      => '세부',
            'mactan'    => '막탄',
            'lapu_lapu' => '라푸라푸',
            'bohol'     => '보홀',
            'clark'     => '클락',
            'manila'    => '마닐라',
        );
    }
}

if (!function_exists('eottae_golf_join_list_url')) {
    function eottae_golf_join_list_url(array $params = array())
    {
        $url = G5_URL.'/golf-join';
        if (!$params) {
            return $url;
        }

        return $url.'?'.http_build_query($params);
    }
}

if (!function_exists('eottae_golf_join_detail_url')) {
    function eottae_golf_join_detail_url($join_id)
    {
        $join_id = (int) $join_id;
        if ($join_id < 1) {
            return eottae_golf_join_list_url();
        }

        return G5_URL.'/golf-join/'.$join_id;
    }
}

if (!function_exists('eottae_golf_join_create_url')) {
    function eottae_golf_join_create_url(array $params = array())
    {
        $url = G5_URL.'/golf-join/create';
        if (!$params) {
            return $url;
        }

        return $url.'?'.http_build_query($params);
    }
}

if (!function_exists('eottae_golf_join_proc_url')) {
    function eottae_golf_join_proc_url()
    {
        return G5_URL.'/proc/eottae-golf-join.php';
    }
}

if (!function_exists('eottae_golf_join_profile_edit_url')) {
    function eottae_golf_join_profile_edit_url()
    {
        return G5_BBS_URL.'/register_form.php';
    }
}

if (!function_exists('eottae_golf_join_manage_url')) {
    function eottae_golf_join_manage_url($join_id)
    {
        return G5_URL.'/page/eottae-golf-join-manage.php?join_id='.(int) $join_id;
    }
}

if (!function_exists('eottae_golf_join_chat_url')) {
    function eottae_golf_join_chat_url($join_id)
    {
        return G5_URL.'/page/eottae-golf-join-chat.php?join_id='.(int) $join_id;
    }
}

if (!function_exists('eottae_golf_join_region_label')) {
    function eottae_golf_join_region_label($code)
    {
        $options = eottae_golf_join_region_options();
        $code = preg_replace('/[^a-z_]/', '', (string) $code);

        return isset($options[$code]) ? $options[$code] : $code;
    }
}

if (!function_exists('eottae_golf_join_time_zone_options')) {
    function eottae_golf_join_time_zone_options()
    {
        return array(
            'morning'   => '오전',
            'afternoon' => '오후',
            'evening'   => '야간',
        );
    }
}

if (!function_exists('eottae_golf_join_age_preference_options')) {
    function eottae_golf_join_age_preference_options()
    {
        return array(
            'any'    => '연령무관',
            '20s'    => '20대',
            '30s'    => '30대',
            '40s'    => '40대',
            '50plus' => '50대 이상',
        );
    }
}

if (!function_exists('eottae_golf_join_score_preference_options')) {
    function eottae_golf_join_score_preference_options()
    {
        return array(
            'any'     => '타수무관',
            'under70' => '70타 미만',
            '70s'     => '70타대',
            '80s'     => '80타대',
            '90s'     => '90타대',
            '100s'    => '100타대',
            'over110' => '110타 이상',
        );
    }
}

if (!function_exists('eottae_golf_join_recruit_slot_options')) {
    function eottae_golf_join_recruit_slot_options()
    {
        return array(
            1 => '1명',
            2 => '2명',
            3 => '3명',
        );
    }
}

if (!function_exists('eottae_golf_join_schedule_slot_options')) {
    function eottae_golf_join_schedule_slot_options()
    {
        return array(
            'morning' => '오전',
            'afternoon' => '오후',
            'evening' => '야간',
            'unknown' => '티타임 미정',
        );
    }
}

if (!function_exists('eottae_golf_join_register_mode_options')) {
    function eottae_golf_join_register_mode_options()
    {
        return array(
            'fixed_tee' => array(
                'title' => '확정된 티타임에 초대하기',
                'desc'  => '예약한 티타임이 있다면, 함께할 멤버만 빠르게 찾아보세요.',
            ),
            'members_first' => array(
                'title' => '원하는 조건의 멤버 먼저 모으기',
                'desc'  => '아직 예약 전이라면, 멤버를 먼저 모은 뒤 티타임을 잡아보세요.',
            ),
        );
    }
}

if (!function_exists('eottae_golf_join_mood_tag_options')) {
    function eottae_golf_join_mood_tag_options()
    {
        return array(
            '명랑골프',
            '매너골프',
            '노멀리건',
            '로대로플레이',
            '내기선호',
            '우천시에도플레이',
            '부담없이 가볍게',
        );
    }
}

if (!function_exists('eottae_golf_join_member_token')) {
    function eottae_golf_join_member_token($regenerate = false)
    {
        $token = get_session('eottae_golf_join_member_token');
        if ($regenerate || $token === '') {
            $token = bin2hex(random_bytes(16));
            set_session('eottae_golf_join_member_token', $token);
        }

        return (string) $token;
    }
}

if (!function_exists('eottae_golf_join_verify_member_token')) {
    function eottae_golf_join_verify_member_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_golf_join_member_token');

        return $token !== '' && $session_token !== '' && hash_equals((string) $session_token, $token);
    }
}

if (!function_exists('eottae_golf_join_host_profile_from_member')) {
    /**
     * @param array<string, mixed> $member
     * @return array<string, string>
     */
    function eottae_golf_join_host_profile_from_member(array $member)
    {
        $sex = strtoupper(substr((string) ($member['mb_sex'] ?? ''), 0, 1));
        $gender = in_array($sex, array('M', 'F'), true) ? $sex : '';

        return array(
            'nickname'    => trim((string) ($member['mb_nick'] ?? $member['mb_id'] ?? '')),
            'gender'      => $gender,
            'age_group'   => trim((string) ($member['mb_2'] ?? '')),
            'score_range' => trim((string) ($member['mb_3'] ?? '')),
        );
    }
}

if (!function_exists('eottae_golf_join_host_profile_labels')) {
    /**
     * @param array<string, string> $profile
     * @return array<string, string>
     */
    function eottae_golf_join_host_profile_labels(array $profile)
    {
        $gender_map = array('M' => '남성', 'F' => '여성');
        $age_map = eottae_golf_join_age_preference_options();
        $score_map = eottae_golf_join_score_preference_options();

        $age = preg_replace('/[^a-z0-9_]/', '', (string) ($profile['age_group'] ?? ''));
        $score = preg_replace('/[^a-z0-9_]/', '', (string) ($profile['score_range'] ?? ''));

        return array(
            'gender' => $gender_map[$profile['gender'] ?? ''] ?? '미입력',
            'age'    => isset($age_map[$age]) ? $age_map[$age] : (($profile['age_group'] ?? '') !== '' ? $profile['age_group'] : '미입력'),
            'score'  => isset($score_map[$score]) ? $score_map[$score] : (($profile['score_range'] ?? '') !== '' ? $profile['score_range'] : '미입력'),
        );
    }
}

if (!function_exists('eottae_golf_join_gender_preference_detail_labels')) {
    function eottae_golf_join_gender_preference_detail_labels()
    {
        return array(
            'any'    => '성별무관',
            'male'   => '남자만',
            'female' => '여자만',
            'couple' => '부부커플',
        );
    }
}

if (!function_exists('eottae_golf_join_post_status_options')) {
    function eottae_golf_join_post_status_options()
    {
        return array(
            'recruiting' => '모집중',
            'full'       => '정원마감',
            'closed'     => '모집종료',
            'cancelled'  => '취소',
        );
    }
}

if (!function_exists('eottae_golf_join_member_status_options')) {
    function eottae_golf_join_member_status_options()
    {
        return array(
            'pending'   => '승인대기',
            'approved'  => '참여확정',
            'rejected'  => '거절',
            'cancelled' => '취소',
        );
    }
}

if (!function_exists('eottae_golf_join_gender_preference_options')) {
    function eottae_golf_join_gender_preference_options()
    {
        return array(
            'any'    => '성별무관',
            'male'   => '남성',
            'female' => '여성',
            'couple' => '커플',
        );
    }
}

if (!function_exists('eottae_golf_join_schema_status')) {
    function eottae_golf_join_schema_status()
    {
        $tables = eottae_golf_join_table_names();
        $status = array();

        foreach ($tables as $key => $table) {
            $status[$key] = array(
                'table'  => $table,
                'exists' => eottae_golf_join_table_exists($table),
            );
        }

        if (function_exists('eottae_golf_join_upgrade_schema')) {
            eottae_golf_join_upgrade_schema();
        }

        return $status;
    }
}

if (!function_exists('eottae_golf_join_ensure_schema')) {
    /**
     * 골프조인 테이블 생성 (CREATE TABLE IF NOT EXISTS)
     *
     * 실제 테이블명: g5_sebu_golf_join_posts, g5_sebu_golf_join_members,
     *               g5_sebu_golf_courses, g5_sebu_golf_join_reports
     * 회원 연결: user_id / reporter_user_id → g5_member.mb_id (FK)
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_golf_join_ensure_schema()
    {
        static $done = false;
        if ($done) {
            return array();
        }
        $done = true;

        $tables = eottae_golf_join_table_names();
        $member_table = eottae_golf_join_member_table();
        $results = array();

        $courses_ddl = " CREATE TABLE IF NOT EXISTS `{$tables['courses']}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `region` varchar(30) NOT NULL DEFAULT '',
            `name` varchar(120) NOT NULL DEFAULT '',
            `address` varchar(255) NOT NULL DEFAULT '',
            `map_url` varchar(500) NOT NULL DEFAULT '',
            `phone` varchar(50) NOT NULL DEFAULT '',
            `price_info` varchar(255) NOT NULL DEFAULT '',
            `description` text NOT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT '1',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            KEY `idx_region_active` (`region`, `is_active`),
            KEY `idx_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";

        $posts_ddl = " CREATE TABLE IF NOT EXISTS `{$tables['posts']}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` varchar(20) NOT NULL DEFAULT '',
            `title` varchar(120) NOT NULL DEFAULT '',
            `region` varchar(30) NOT NULL DEFAULT '',
            `golf_course_id` int(11) unsigned DEFAULT NULL,
            `golf_course_name` varchar(120) NOT NULL DEFAULT '',
            `round_date` date NOT NULL,
            `tee_time` time DEFAULT NULL,
            `is_tee_time_unknown` tinyint(1) NOT NULL DEFAULT '0',
            `time_zone` varchar(20) NOT NULL DEFAULT '',
            `price` int(11) unsigned NOT NULL DEFAULT '0',
            `recruit_count` tinyint(3) unsigned NOT NULL DEFAULT '4',
            `current_count` tinyint(3) unsigned NOT NULL DEFAULT '1',
            `gender_preference` varchar(20) NOT NULL DEFAULT 'any',
            `age_preferences` varchar(255) NOT NULL DEFAULT '',
            `score_preferences` varchar(255) NOT NULL DEFAULT '',
            `mood_tags` varchar(500) NOT NULL DEFAULT '',
            `description` text NOT NULL,
            `status` varchar(20) NOT NULL DEFAULT 'recruiting',
            `register_mode` varchar(20) NOT NULL DEFAULT '',
            `view_count` int(11) unsigned NOT NULL DEFAULT '0',
            `report_count` int(11) unsigned NOT NULL DEFAULT '0',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `deleted_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_region_round` (`region`, `round_date`),
            KEY `idx_status_round` (`status`, `round_date`),
            KEY `idx_golf_course_id` (`golf_course_id`),
            KEY `idx_deleted_at` (`deleted_at`),
            CONSTRAINT `fk_golf_join_posts_user`
                FOREIGN KEY (`user_id`) REFERENCES `{$member_table}` (`mb_id`)
                ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_golf_join_posts_course`
                FOREIGN KEY (`golf_course_id`) REFERENCES `{$tables['courses']}` (`id`)
                ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";

        $members_ddl = " CREATE TABLE IF NOT EXISTS `{$tables['members']}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `post_id` int(11) unsigned NOT NULL DEFAULT '0',
            `user_id` varchar(20) NOT NULL DEFAULT '',
            `role` varchar(20) NOT NULL DEFAULT 'member',
            `status` varchar(20) NOT NULL DEFAULT 'pending',
            `nickname` varchar(100) NOT NULL DEFAULT '',
            `gender` char(1) NOT NULL DEFAULT '',
            `age_group` varchar(30) NOT NULL DEFAULT '',
            `score_range` varchar(30) NOT NULL DEFAULT '',
            `message` varchar(500) NOT NULL DEFAULT '',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_golf_join_post_user` (`post_id`, `user_id`),
            KEY `idx_post_id` (`post_id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_status` (`status`),
            CONSTRAINT `fk_golf_join_members_post`
                FOREIGN KEY (`post_id`) REFERENCES `{$tables['posts']}` (`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_golf_join_members_user`
                FOREIGN KEY (`user_id`) REFERENCES `{$member_table}` (`mb_id`)
                ON DELETE RESTRICT ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";

        $reports_ddl = " CREATE TABLE IF NOT EXISTS `{$tables['reports']}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `post_id` int(11) unsigned NOT NULL DEFAULT '0',
            `reporter_user_id` varchar(20) NOT NULL DEFAULT '',
            `reason` varchar(50) NOT NULL DEFAULT '',
            `memo` varchar(500) NOT NULL DEFAULT '',
            `status` varchar(20) NOT NULL DEFAULT 'pending',
            `admin_memo` varchar(500) NOT NULL DEFAULT '',
            `handled_by` varchar(20) NOT NULL DEFAULT '',
            `resolved_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            KEY `idx_post_id` (`post_id`),
            KEY `idx_reporter` (`reporter_user_id`),
            KEY `idx_status` (`status`),
            CONSTRAINT `fk_golf_join_reports_post`
                FOREIGN KEY (`post_id`) REFERENCES `{$tables['posts']}` (`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_golf_join_reports_reporter`
                FOREIGN KEY (`reporter_user_id`) REFERENCES `{$member_table}` (`mb_id`)
                ON DELETE RESTRICT ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";

        $chat_rooms_ddl = " CREATE TABLE IF NOT EXISTS `{$tables['chat_rooms']}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `post_id` int(11) unsigned NOT NULL DEFAULT '0',
            `status` varchar(20) NOT NULL DEFAULT 'active',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_golf_join_chat_post` (`post_id`),
            KEY `idx_status` (`status`),
            CONSTRAINT `fk_golf_join_chat_room_post`
                FOREIGN KEY (`post_id`) REFERENCES `{$tables['posts']}` (`id`)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";

        $chat_messages_ddl = " CREATE TABLE IF NOT EXISTS `{$tables['chat_messages']}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `chat_room_id` int(11) unsigned NOT NULL DEFAULT '0',
            `user_id` varchar(20) NOT NULL DEFAULT '',
            `message` text NOT NULL,
            `is_system` tinyint(1) NOT NULL DEFAULT '0',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            KEY `idx_chat_room_id` (`chat_room_id`, `id`),
            KEY `idx_user_id` (`user_id`),
            CONSTRAINT `fk_golf_join_chat_msg_room`
                FOREIGN KEY (`chat_room_id`) REFERENCES `{$tables['chat_rooms']}` (`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_golf_join_chat_msg_user`
                FOREIGN KEY (`user_id`) REFERENCES `{$member_table}` (`mb_id`)
                ON DELETE RESTRICT ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";

        $ddl = array(
            'courses'       => $courses_ddl,
            'posts'         => $posts_ddl,
            'members'       => $members_ddl,
            'reports'       => $reports_ddl,
            'chat_rooms'    => $chat_rooms_ddl,
            'chat_messages' => $chat_messages_ddl,
        );

        foreach ($ddl as $key => $sql) {
            $table = $tables[$key];
            $existed = eottae_golf_join_table_exists($table);
            $ok = (bool) sql_query($sql, false);
            $results[] = array(
                'table'   => $table,
                'key'     => $key,
                'existed' => $existed,
                'ok'      => $ok,
                'action'  => $existed ? 'exists' : ($ok ? 'created' : 'failed'),
            );
        }

        if (function_exists('eottae_golf_join_seed_courses_if_empty')) {
            eottae_golf_join_seed_courses_if_empty();
        }

        if (function_exists('eottae_golf_join_upgrade_schema')) {
            eottae_golf_join_upgrade_schema();
        }

        return $results;
    }
}

if (!function_exists('eottae_golf_join_upgrade_schema')) {
    function eottae_golf_join_upgrade_schema()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        $tables = eottae_golf_join_table_names();
        if (!eottae_golf_join_table_exists($tables['posts'])) {
            return;
        }

        $col = sql_fetch(" SHOW COLUMNS FROM `{$tables['posts']}` LIKE 'register_mode' ", false);
        if (empty($col)) {
            sql_query(" ALTER TABLE `{$tables['posts']}`
                ADD COLUMN `register_mode` varchar(20) NOT NULL DEFAULT ''
                AFTER `status` ", false);
        }

        if (eottae_golf_join_table_exists($tables['reports'])) {
            foreach (array(
                'admin_memo'   => "varchar(500) NOT NULL DEFAULT ''",
                'handled_by'   => "varchar(20) NOT NULL DEFAULT ''",
                'resolved_at'  => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'",
            ) as $name => $def) {
                $c = sql_fetch(" SHOW COLUMNS FROM `{$tables['reports']}` LIKE '{$name}' ", false);
                if (empty($c)) {
                    sql_query(" ALTER TABLE `{$tables['reports']}` ADD COLUMN `{$name}` {$def} ", false);
                }
            }
        }
    }
}

if (!function_exists('eottae_golf_join_seed_courses_if_empty')) {
    function eottae_golf_join_seed_courses_if_empty()
    {
        $table = eottae_golf_join_table_names()['courses'];
        if (!eottae_golf_join_table_exists($table)) {
            return;
        }

        $row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$table}` ", false);
        if ((int) ($row['cnt'] ?? 0) > 0) {
            return;
        }

        $now = G5_TIME_YMDHIS;
        $samples = array(
            array(
                'region'      => 'cebu',
                'name'        => 'Cebu Country Club',
                'address'     => 'Cebu City, Cebu, Philippines',
                'map_url'     => '',
                'phone'       => '',
                'price_info'  => '',
                'description' => '세부 시티 대표 골프장',
            ),
            array(
                'region'      => 'cebu',
                'name'        => 'Alta Vista Golf & Country Club',
                'address'     => 'Cebu, Philippines',
                'map_url'     => '',
                'phone'       => '',
                'price_info'  => '',
                'description' => '',
            ),
            array(
                'region'      => 'mactan',
                'name'        => 'Mactan Island Golf Club',
                'address'     => 'Mactan, Cebu, Philippines',
                'map_url'     => '',
                'phone'       => '',
                'price_info'  => '',
                'description' => '',
            ),
            array(
                'region'      => 'cebu',
                'name'        => 'Club Filipino de Cebu',
                'address'     => 'Danao, Cebu, Philippines',
                'map_url'     => '',
                'phone'       => '',
                'price_info'  => '',
                'description' => '',
            ),
            array(
                'region'      => 'cebu',
                'name'        => "Queen's Island Golf & Resort",
                'address'     => 'Medellin, Cebu, Philippines',
                'map_url'     => '',
                'phone'       => '',
                'price_info'  => '',
                'description' => '',
            ),
        );

        foreach ($samples as $sample) {
            sql_query("
                INSERT INTO `{$table}`
                SET
                    region = '".sql_escape_string($sample['region'])."',
                    name = '".sql_escape_string($sample['name'])."',
                    address = '".sql_escape_string($sample['address'])."',
                    map_url = '".sql_escape_string($sample['map_url'])."',
                    phone = '".sql_escape_string($sample['phone'])."',
                    price_info = '".sql_escape_string($sample['price_info'])."',
                    description = '".sql_escape_string($sample['description'])."',
                    is_active = '1',
                    created_at = '{$now}',
                    updated_at = '{$now}'
            ", false);
        }
    }
}

if (!function_exists('eottae_golf_join_drop_schema')) {
    /**
     * 골프조인 테이블 삭제 (롤백용)
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_golf_join_drop_schema()
    {
        $tables = eottae_golf_join_table_names();
        $order = array('chat_messages', 'chat_rooms', 'reports', 'members', 'posts', 'courses');
        $results = array();

        foreach ($order as $key) {
            $table = $tables[$key];
            if (!eottae_golf_join_table_exists($table)) {
                $results[] = array(
                    'table'  => $table,
                    'key'    => $key,
                    'ok'     => true,
                    'action' => 'missing',
                );
                continue;
            }

            $ok = (bool) sql_query(" DROP TABLE IF EXISTS `{$table}` ", false);
            $results[] = array(
                'table'  => $table,
                'key'    => $key,
                'ok'     => $ok,
                'action' => 'dropped',
            );
        }

        return $results;
    }
}

if (!function_exists('eottae_golf_join_is_post_deleted')) {
    /**
     * @param array<string, mixed>|null $post
     */
    function eottae_golf_join_is_post_deleted($post)
    {
        if (!is_array($post)) {
            return true;
        }

        $deleted_at = (string) ($post['deleted_at'] ?? '');

        return $deleted_at !== '' && $deleted_at !== '0000-00-00 00:00:00';
    }
}

$data_lib = G5_LIB_PATH.'/eottae-golf-join-data.lib.php';
if (is_file($data_lib)) {
    include_once $data_lib;
}
$member_lib = G5_LIB_PATH.'/eottae-golf-join-member.lib.php';
if (is_file($member_lib)) {
    include_once $member_lib;
}
$chat_lib = G5_LIB_PATH.'/eottae-golf-join-chat.lib.php';
if (is_file($chat_lib)) {
    include_once $chat_lib;
}
$admin_lib = G5_LIB_PATH.'/eottae-golf-join-admin.lib.php';
if (is_file($admin_lib)) {
    include_once $admin_lib;
}
