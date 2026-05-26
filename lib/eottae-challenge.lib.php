<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_challenge_bootstrap_tables')) {
    function eottae_challenge_bootstrap_tables()
    {
        global $g5;

        if (!isset($g5['sebu_challenges_table'])) {
            $g5['sebu_challenges_table'] = G5_TABLE_PREFIX.'sebu_challenges';
        }
        if (!isset($g5['sebu_challenge_entries_table'])) {
            $g5['sebu_challenge_entries_table'] = G5_TABLE_PREFIX.'sebu_challenge_entries';
        }
        if (!isset($g5['sebu_challenge_rewards_table'])) {
            $g5['sebu_challenge_rewards_table'] = G5_TABLE_PREFIX.'sebu_challenge_rewards';
        }
        if (!isset($g5['sebu_challenge_comments_table'])) {
            $g5['sebu_challenge_comments_table'] = G5_TABLE_PREFIX.'sebu_challenge_comments';
        }
    }
}

if (!function_exists('eottae_challenge_table_exists')) {
    function eottae_challenge_table_exists($table)
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

if (!function_exists('eottae_challenge_challenges_table')) {
    function eottae_challenge_challenges_table()
    {
        eottae_challenge_bootstrap_tables();
        global $g5;

        return $g5['sebu_challenges_table'];
    }
}

if (!function_exists('eottae_challenge_entries_table')) {
    function eottae_challenge_entries_table()
    {
        eottae_challenge_bootstrap_tables();
        global $g5;

        return $g5['sebu_challenge_entries_table'];
    }
}

if (!function_exists('eottae_challenge_rewards_table')) {
    function eottae_challenge_rewards_table()
    {
        eottae_challenge_bootstrap_tables();
        global $g5;

        return $g5['sebu_challenge_rewards_table'];
    }
}

if (!function_exists('eottae_challenge_comments_table')) {
    function eottae_challenge_comments_table()
    {
        eottae_challenge_bootstrap_tables();
        global $g5;

        return $g5['sebu_challenge_comments_table'];
    }
}

if (!function_exists('eottae_challenge_area_options')) {
    function eottae_challenge_area_options()
    {
        if (function_exists('eottae_calendar_area_options')) {
            return eottae_calendar_area_options();
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

if (!function_exists('eottae_challenge_area_label')) {
    function eottae_challenge_area_label($code)
    {
        $options = eottae_challenge_area_options();

        return isset($options[$code]) ? $options[$code] : '';
    }
}

if (!function_exists('eottae_challenge_badge_options')) {
    function eottae_challenge_badge_options()
    {
        return array(
            'food_explorer'   => '맛집 탐험가',
            'life_master'     => '세부 생활고수',
            'fitness_prover'  => '운동 인증러',
            'free_giver'      => '무료나눔 천사',
            'photo_artist'    => '세부 사진작가',
            'settlement_writer'=> '정착기 작가',
            'talk_meetup_king'=> '세부톡 모임왕',
        );
    }
}

if (!function_exists('eottae_challenge_badge_label')) {
    function eottae_challenge_badge_label($code)
    {
        $options = eottae_challenge_badge_options();
        $code = preg_replace('/[^a-z_]/', '', (string) $code);

        return isset($options[$code]) ? $options[$code] : $code;
    }
}

if (!function_exists('eottae_challenge_status_options')) {
    function eottae_challenge_status_options()
    {
        return array(
            'active'   => '진행중',
            'scheduled'=> '예정',
            'ended'    => '종료',
            'hidden'   => '숨김',
        );
    }
}

if (!function_exists('eottae_challenge_display_status')) {
    /**
     * @param array<string, mixed> $challenge
     */
    function eottae_challenge_display_status(array $challenge)
    {
        $stored = preg_replace('/[^a-z_]/', '', (string) ($challenge['status'] ?? ''));
        if ($stored === 'hidden') {
            return 'hidden';
        }
        if ($stored === 'ended') {
            return 'ended';
        }

        $today = date('Y-m-d');
        $start = (string) ($challenge['start_date'] ?? '');
        $end = (string) ($challenge['end_date'] ?? '');

        if ($start !== '' && $today < $start) {
            return 'scheduled';
        }
        if ($end !== '' && $today > $end) {
            return 'ended';
        }

        return 'active';
    }
}

if (!function_exists('eottae_challenge_display_status_label')) {
    function eottae_challenge_display_status_label($status)
    {
        $map = array(
            'active'    => '진행중',
            'scheduled' => '예정',
            'ended'     => '종료',
            'hidden'    => '숨김',
        );

        return isset($map[$status]) ? $map[$status] : '진행중';
    }
}

if (!function_exists('eottae_challenge_display_status_class')) {
    function eottae_challenge_display_status_class($status)
    {
        $status = preg_replace('/[^a-z_]/', '', (string) $status);

        return 'sebu-challenge-status sebu-challenge-status--'.($status !== '' ? $status : 'active');
    }
}

if (!function_exists('eottae_challenge_list_url')) {
    function eottae_challenge_list_url(array $params = array())
    {
        $url = G5_URL.'/challenge/';
        if (!$params) {
            return $url;
        }

        return $url.'?'.http_build_query($params);
    }
}

if (!function_exists('eottae_challenge_view_url')) {
    function eottae_challenge_view_url($challenge_id)
    {
        return G5_URL.'/page/eottae-challenge-view.php?challenge_id='.(int) $challenge_id;
    }
}

if (!function_exists('eottae_challenge_write_url')) {
    function eottae_challenge_write_url($challenge_id)
    {
        return G5_URL.'/page/eottae-challenge-write.php?challenge_id='.(int) $challenge_id;
    }
}

if (!function_exists('eottae_challenge_entry_url')) {
    function eottae_challenge_entry_url($entry_id)
    {
        return G5_URL.'/page/eottae-challenge-entry.php?entry_id='.(int) $entry_id;
    }
}

if (!function_exists('eottae_challenge_admin_url')) {
    function eottae_challenge_admin_url()
    {
        return G5_URL.'/page/eottae-admin-challenges.php';
    }
}

if (!function_exists('eottae_challenge_mypage_url')) {
    function eottae_challenge_mypage_url()
    {
        return G5_URL.'/mypage/challenges.php';
    }
}

if (!function_exists('eottae_challenge_proc_url')) {
    function eottae_challenge_proc_url()
    {
        return G5_URL.'/proc/eottae-challenge.php';
    }
}

if (!function_exists('eottae_challenge_admin_proc_url')) {
    function eottae_challenge_admin_proc_url()
    {
        return G5_URL.'/proc/eottae-challenge-admin.php';
    }
}

if (!function_exists('eottae_challenge_image_url')) {
    function eottae_challenge_image_url($filename)
    {
        $filename = basename((string) $filename);
        if ($filename === '' || !defined('G5_DATA_URL')) {
            return '';
        }

        return G5_DATA_URL.'/challenge/'.$filename;
    }
}

if (!function_exists('eottae_challenge_member_token')) {
    function eottae_challenge_member_token($regenerate = false)
    {
        $token = get_session('eottae_challenge_member_token');
        if ($regenerate || $token === '') {
            $token = bin2hex(random_bytes(16));
            set_session('eottae_challenge_member_token', $token);
        }

        return (string) $token;
    }
}

if (!function_exists('eottae_challenge_verify_member_token')) {
    function eottae_challenge_verify_member_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_challenge_member_token');

        return $token !== '' && $session_token !== '' && hash_equals((string) $session_token, $token);
    }
}

if (!function_exists('eottae_challenge_ensure_schema')) {
    function eottae_challenge_ensure_schema()
    {
        eottae_challenge_bootstrap_tables();

        $challenges = eottae_challenge_challenges_table();
        $entries = eottae_challenge_entries_table();
        $rewards = eottae_challenge_rewards_table();
        $comments = eottae_challenge_comments_table();

        $results = array();

        $challenges_ddl = " CREATE TABLE IF NOT EXISTS `{$challenges}` (
            `challenge_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(200) NOT NULL DEFAULT '',
            `description` text NOT NULL,
            `start_date` date NOT NULL,
            `end_date` date NOT NULL,
            `status` varchar(20) NOT NULL DEFAULT 'active',
            `image` varchar(255) NOT NULL DEFAULT '',
            `icon` varchar(20) NOT NULL DEFAULT '',
            `how_to_join` text NOT NULL,
            `conditions_text` text NOT NULL,
            `notice_text` text NOT NULL,
            `reward_text` text NOT NULL,
            `reward_point` int(11) NOT NULL DEFAULT '0',
            `reward_badge` varchar(40) NOT NULL DEFAULT '',
            `select_best` tinyint(1) NOT NULL DEFAULT '1',
            `is_featured` tinyint(1) NOT NULL DEFAULT '0',
            `created_by` varchar(20) NOT NULL DEFAULT '',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`challenge_id`),
            KEY `idx_status_dates` (`status`, `start_date`, `end_date`),
            KEY `idx_featured` (`is_featured`, `status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";
        $results[] = array('table' => $challenges, 'ok' => (bool) sql_query($challenges_ddl, false));

        $entries_ddl = " CREATE TABLE IF NOT EXISTS `{$entries}` (
            `entry_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `challenge_id` int(11) unsigned NOT NULL DEFAULT '0',
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `writer_name` varchar(100) NOT NULL DEFAULT '',
            `title` varchar(200) NOT NULL DEFAULT '',
            `content` text NOT NULL,
            `image` varchar(255) NOT NULL DEFAULT '',
            `area` varchar(30) NOT NULL DEFAULT '',
            `place_name` varchar(200) NOT NULL DEFAULT '',
            `related_url` varchar(500) NOT NULL DEFAULT '',
            `related_room_id` int(11) unsigned NOT NULL DEFAULT '0',
            `is_public` tinyint(1) NOT NULL DEFAULT '1',
            `status` varchar(20) NOT NULL DEFAULT 'active',
            `is_best` tinyint(1) NOT NULL DEFAULT '0',
            `point_given` int(11) NOT NULL DEFAULT '0',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`entry_id`),
            KEY `idx_challenge` (`challenge_id`, `status`, `created_at`),
            KEY `idx_mb_id` (`mb_id`),
            KEY `idx_best` (`is_best`, `challenge_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";
        $results[] = array('table' => $entries, 'ok' => (bool) sql_query($entries_ddl, false));

        $rewards_ddl = " CREATE TABLE IF NOT EXISTS `{$rewards}` (
            `reward_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `challenge_id` int(11) unsigned NOT NULL DEFAULT '0',
            `entry_id` int(11) unsigned NOT NULL DEFAULT '0',
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `reward_type` varchar(20) NOT NULL DEFAULT '',
            `reward_value` varchar(100) NOT NULL DEFAULT '',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`reward_id`),
            KEY `idx_mb_id` (`mb_id`),
            KEY `idx_challenge_entry` (`challenge_id`, `entry_id`),
            KEY `idx_type` (`reward_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";
        $results[] = array('table' => $rewards, 'ok' => (bool) sql_query($rewards_ddl, false));

        $comments_ddl = " CREATE TABLE IF NOT EXISTS `{$comments}` (
            `comment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `entry_id` int(11) unsigned NOT NULL DEFAULT '0',
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `writer_name` varchar(100) NOT NULL DEFAULT '',
            `content` varchar(1000) NOT NULL DEFAULT '',
            `status` varchar(20) NOT NULL DEFAULT 'active',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`comment_id`),
            KEY `idx_entry` (`entry_id`, `status`, `created_at`),
            KEY `idx_mb_id` (`mb_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ";
        $results[] = array('table' => $comments, 'ok' => (bool) sql_query($comments_ddl, false));

        if (function_exists('eottae_challenge_likes_ensure_schema')) {
            $results[] = eottae_challenge_likes_ensure_schema();
        } else {
            $likes_lib = G5_LIB_PATH.'/eottae-challenge-likes.lib.php';
            if (is_file($likes_lib)) {
                include_once $likes_lib;
                if (function_exists('eottae_challenge_likes_ensure_schema')) {
                    $results[] = eottae_challenge_likes_ensure_schema();
                }
            }
        }
        if (function_exists('eottae_challenge_reports_ensure_schema')) {
            $results[] = eottae_challenge_reports_ensure_schema();
        } else {
            $reports_lib = G5_LIB_PATH.'/eottae-challenge-report.lib.php';
            if (is_file($reports_lib)) {
                include_once $reports_lib;
                if (function_exists('eottae_challenge_reports_ensure_schema')) {
                    $results[] = eottae_challenge_reports_ensure_schema();
                }
            }
        }

        eottae_challenge_seed_samples_if_empty();

        return $results;
    }
}

if (!function_exists('eottae_challenge_seed_samples_if_empty')) {
    function eottae_challenge_seed_samples_if_empty()
    {
        $table = eottae_challenge_challenges_table();
        if (!eottae_challenge_table_exists($table)) {
            return;
        }

        $row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$table}` ", false);
        if ((int) ($row['cnt'] ?? 0) > 0) {
            return;
        }

        $today = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+30 days'));
        $samples = array(
            array(
                'title'           => '세부 맛집 인증 챌린지',
                'description'     => '세부에서 자주 가는 맛집을 사진과 함께 공유해 주세요. 한 줄 후기만으로도 참여 완료!',
                'icon'            => '🍜',
                'how_to_join'     => "1. 세부에서 자주 가는 맛집 사진을 올려주세요.\n2. 위치와 한 줄 후기를 함께 남겨주세요.\n3. 댓글로 다른 회원들과 추천을 나눠보세요.",
                'conditions_text' => '로그인 회원 누구나 참여 가능합니다. 본인이 직접 방문한 맛집만 등록해 주세요.',
                'notice_text'     => '타인 사진 무단 사용, 허위 정보, 광고성 글은 삭제될 수 있습니다.',
                'reward_text'     => "참여 포인트 100P\n우수 인증글 메인 노출\n맛집 탐험가 뱃지 지급",
                'reward_point'    => 100,
                'reward_badge'    => 'food_explorer',
                'is_featured'     => 1,
            ),
            array(
                'title'           => '세부 사진 한 장 챌린지',
                'description'     => '오늘 본 세부의 풍경, 일상, 맛있는 한 끼 — 사진 한 장으로 기록해 보세요.',
                'icon'            => '📸',
                'how_to_join'     => "1. 세부에서 찍은 사진 1장을 업로드하세요.\n2. 짧은 설명을 함께 남겨주세요.\n3. 다른 회원 글에 공감과 댓글을 남겨보세요.",
                'conditions_text' => '세부와 관련된 사진이면 주제 제한 없이 참여 가능합니다.',
                'notice_text'     => '개인정보가 포함된 사진은 업로드하지 마세요.',
                'reward_text'     => "참여 포인트 50P\n세부 사진작가 뱃지 지급",
                'reward_point'    => 50,
                'reward_badge'    => 'photo_artist',
                'is_featured'     => 1,
            ),
            array(
                'title'           => '세부 생활팁 공유 챌린지',
                'description'     => '정착·생활에 도움이 되는 팁을 나눠 주세요. 작은 정보도 큰 도움이 됩니다.',
                'icon'            => '💡',
                'how_to_join'     => "1. 세부 생활에 도움이 되는 팁을 글로 작성하세요.\n2. 가능하면 지역 정보를 함께 적어주세요.\n3. 댓글로 추가 팁을 나눠보세요.",
                'conditions_text' => '실제 경험에 기반한 생활 팁을 환영합니다.',
                'notice_text'     => '확인되지 않은 정보는 수정 요청될 수 있습니다.',
                'reward_text'     => "참여 포인트 80P\n세부 생활고수 뱃지 지급",
                'reward_point'    => 80,
                'reward_badge'    => 'life_master',
                'is_featured'     => 1,
            ),
        );

        $now = G5_TIME_YMDHIS;
        foreach ($samples as $sample) {
            sql_query("
                INSERT INTO `{$table}`
                SET
                    title = '".sql_escape_string($sample['title'])."',
                    description = '".sql_escape_string($sample['description'])."',
                    start_date = '{$today}',
                    end_date = '{$end}',
                    status = 'active',
                    image = '',
                    icon = '".sql_escape_string($sample['icon'])."',
                    how_to_join = '".sql_escape_string($sample['how_to_join'])."',
                    conditions_text = '".sql_escape_string($sample['conditions_text'])."',
                    notice_text = '".sql_escape_string($sample['notice_text'])."',
                    reward_text = '".sql_escape_string($sample['reward_text'])."',
                    reward_point = '".(int) $sample['reward_point']."',
                    reward_badge = '".sql_escape_string($sample['reward_badge'])."',
                    select_best = '1',
                    is_featured = '".(int) $sample['is_featured']."',
                    created_by = 'system',
                    created_at = '{$now}',
                    updated_at = '{$now}'
            ", false);
        }
    }
}

if (!function_exists('eottae_challenge_format_row')) {
    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    function eottae_challenge_format_row(array $row)
    {
        $challenge_id = (int) ($row['challenge_id'] ?? 0);
        $display_status = eottae_challenge_display_status($row);
        $image = (string) ($row['image'] ?? '');
        $icon = trim((string) ($row['icon'] ?? ''));

        return array_merge($row, array(
            'challenge_id'      => $challenge_id,
            'display_status'    => $display_status,
            'display_status_label' => eottae_challenge_display_status_label($display_status),
            'display_status_class' => eottae_challenge_display_status_class($display_status),
            'image_url'         => $image !== '' ? eottae_challenge_image_url($image) : '',
            'icon_display'      => $icon !== '' ? $icon : '🏆',
            'reward_badge_label'=> eottae_challenge_badge_label($row['reward_badge'] ?? ''),
            'period_label'      => eottae_challenge_period_label($row),
            'participant_count' => eottae_challenge_count_participants($challenge_id),
            'entry_count'       => eottae_challenge_count_entries($challenge_id),
            'view_url'          => eottae_challenge_view_url($challenge_id),
            'write_url'         => eottae_challenge_write_url($challenge_id),
        ));
    }
}

if (!function_exists('eottae_challenge_period_label')) {
    function eottae_challenge_period_label(array $row)
    {
        $start = (string) ($row['start_date'] ?? '');
        $end = (string) ($row['end_date'] ?? '');
        if ($start === '' && $end === '') {
            return '';
        }
        if ($start !== '' && $end !== '') {
            return $start.' ~ '.$end;
        }

        return $start !== '' ? $start : $end;
    }
}

if (!function_exists('eottae_challenge_get')) {
    function eottae_challenge_get($challenge_id, $include_hidden = false)
    {
        $challenge_id = (int) $challenge_id;
        if ($challenge_id < 1) {
            return null;
        }

        $table = eottae_challenge_challenges_table();
        $row = sql_fetch("
            SELECT *
            FROM `{$table}`
            WHERE challenge_id = '{$challenge_id}'
            LIMIT 1
        ", false);

        if (!is_array($row) || empty($row['challenge_id'])) {
            return null;
        }

        if (!$include_hidden && ($row['status'] ?? '') === 'hidden') {
            return null;
        }

        return eottae_challenge_format_row($row);
    }
}

if (!function_exists('eottae_challenge_list')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_challenge_list(array $options = array())
    {
        $table = eottae_challenge_challenges_table();
        $limit = max(1, min(100, (int) ($options['limit'] ?? 50)));
        $offset = max(0, (int) ($options['offset'] ?? 0));
        $include_hidden = !empty($options['include_hidden']);
        $featured_only = !empty($options['featured_only']);
        $display_status = preg_replace('/[^a-z_]/', '', (string) ($options['display_status'] ?? ''));

        $where = array('1=1');
        if (!$include_hidden) {
            $where[] = " status <> 'hidden' ";
        }
        if ($featured_only) {
            $where[] = " is_featured = 1 ";
        }

        $sql = "
            SELECT *
            FROM `{$table}`
            WHERE ".implode(' AND ', $where)."
            ORDER BY is_featured DESC, start_date DESC, challenge_id DESC
            LIMIT {$offset}, {$limit}
        ";

        $items = array();
        $result = sql_query($sql, false);
        while ($row = sql_fetch_array($result)) {
            if (!is_array($row)) {
                continue;
            }
            $formatted = eottae_challenge_format_row($row);
            if ($display_status !== '' && ($formatted['display_status'] ?? '') !== $display_status) {
                continue;
            }
            $items[] = $formatted;
        }

        return $items;
    }
}

if (!function_exists('eottae_challenge_get_active_featured')) {
    function eottae_challenge_get_active_featured($limit = 3)
    {
        $items = eottae_challenge_list(array(
            'limit'          => max(1, (int) $limit) * 3,
            'featured_only'    => true,
        ));

        $active = array();
        foreach ($items as $item) {
            if (($item['display_status'] ?? '') !== 'active') {
                continue;
            }
            $active[] = $item;
            if (count($active) >= (int) $limit) {
                break;
            }
        }

        return $active;
    }
}

if (!function_exists('eottae_challenge_count_participants')) {
    function eottae_challenge_count_participants($challenge_id)
    {
        $challenge_id = (int) $challenge_id;
        if ($challenge_id < 1) {
            return 0;
        }

        $table = eottae_challenge_entries_table();
        $row = sql_fetch("
            SELECT COUNT(DISTINCT mb_id) AS cnt
            FROM `{$table}`
            WHERE challenge_id = '{$challenge_id}'
              AND status = 'active'
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_challenge_count_entries')) {
    function eottae_challenge_count_entries($challenge_id)
    {
        $challenge_id = (int) $challenge_id;
        if ($challenge_id < 1) {
            return 0;
        }

        $table = eottae_challenge_entries_table();
        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$table}`
            WHERE challenge_id = '{$challenge_id}'
              AND status = 'active'
              AND is_public = 1
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_challenge_can_participate')) {
    function eottae_challenge_can_participate($challenge_id, $mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 참여할 수 있습니다.');
        }

        $challenge = eottae_challenge_get($challenge_id);
        if (!$challenge) {
            return array('ok' => false, 'message' => '챌린지를 찾을 수 없습니다.');
        }

        if (($challenge['display_status'] ?? '') !== 'active') {
            return array('ok' => false, 'message' => '현재 참여할 수 없는 챌린지입니다.');
        }

        return array('ok' => true, 'challenge' => $challenge);
    }
}

if (!function_exists('eottae_challenge_sanitize_url')) {
    function eottae_challenge_sanitize_url($url)
    {
        $url = trim(strip_tags((string) $url));
        if ($url === '') {
            return '';
        }
        if (!preg_match('#^https?://#i', $url)) {
            return '';
        }

        return substr($url, 0, 500);
    }
}

if (!function_exists('eottae_challenge_upload_dir')) {
    function eottae_challenge_upload_dir()
    {
        $dir = G5_DATA_PATH.'/challenge';
        if (!is_dir($dir)) {
            @mkdir($dir, G5_DIR_PERMISSION, true);
            @chmod($dir, G5_DIR_PERMISSION);
        }

        return $dir;
    }
}

if (!function_exists('eottae_challenge_save_upload_image')) {
    function eottae_challenge_save_upload_image($field = 'entry_image')
    {
        if (empty($_FILES[$field]) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
            return array('ok' => true, 'filename' => '');
        }

        $file = $_FILES[$field];
        if (!empty($file['error'])) {
            return array('ok' => false, 'message' => '이미지 업로드에 실패했습니다.');
        }

        $max_size = 5 * 1024 * 1024;
        if ((int) $file['size'] > $max_size) {
            return array('ok' => false, 'message' => '이미지는 5MB 이하만 업로드할 수 있습니다.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (!in_array($ext, $allowed, true)) {
            $img_info = @getimagesize($file['tmp_name']);
            if (!$img_info) {
                return array('ok' => false, 'message' => '이미지 파일만 업로드할 수 있습니다.');
            }
            $ext = 'jpg';
        }

        $dir = eottae_challenge_upload_dir();
        $filename = 'ch_'.date('YmdHis').'_'.bin2hex(random_bytes(4)).'.'.$ext;
        $dest = $dir.'/'.$filename;

        if (!@move_uploaded_file($file['tmp_name'], $dest)) {
            return array('ok' => false, 'message' => '이미지 저장에 실패했습니다.');
        }
        @chmod($dest, G5_FILE_PERMISSION);

        return array('ok' => true, 'filename' => $filename);
    }
}

if (!function_exists('eottae_challenge_validate_room_access')) {
    function eottae_challenge_validate_room_access($room_id, $mb_id)
    {
        $room_id = (int) $room_id;
        if ($room_id < 1) {
            return true;
        }

        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return false;
        }

        if ($mb_id !== '' && $mb_id === ($room['owner_mb_id'] ?? '')) {
            return true;
        }

        $member_row = eottae_talkroom_get_member_row($room_id, $mb_id);
        $membership = eottae_talkroom_membership_state($room, $member_row, $mb_id);

        return in_array($membership, array('owner', 'active'), true);
    }
}

if (!function_exists('eottae_challenge_create_entry')) {
    function eottae_challenge_create_entry($challenge_id, array $input, array $writer)
    {
        $check = eottae_challenge_can_participate($challenge_id, $writer['mb_id'] ?? '');
        if (empty($check['ok'])) {
            return array('ok' => false, 'message' => $check['message'] ?? '참여할 수 없습니다.');
        }

        $title = trim(strip_tags((string) ($input['title'] ?? '')));
        $content = trim((string) ($input['content'] ?? ''));
        if ($title === '') {
            return array('ok' => false, 'message' => '제목을 입력해 주세요.');
        }
        if ($content === '') {
            return array('ok' => false, 'message' => '내용을 입력해 주세요.');
        }

        $area = preg_replace('/[^a-z_]/', '', (string) ($input['area'] ?? ''));
        if ($area === '' || !isset(eottae_challenge_area_options()[$area])) {
            $area = 'etc';
        }

        $related_room_id = (int) ($input['related_room_id'] ?? 0);
        if ($related_room_id > 0 && !eottae_challenge_validate_room_access($related_room_id, $writer['mb_id'] ?? '')) {
            return array('ok' => false, 'message' => '선택한 세부톡방에 참여 중인 경우에만 연결할 수 있습니다.');
        }

        $upload = eottae_challenge_save_upload_image('entry_image');
        if (empty($upload['ok'])) {
            return array('ok' => false, 'message' => $upload['message'] ?? '이미지 업로드 실패');
        }

        $table = eottae_challenge_entries_table();
        $now = G5_TIME_YMDHIS;
        $writer_name = get_text($writer['mb_nick'] ?? ($writer['mb_name'] ?? ''));
        $is_public = !empty($input['is_public']) ? 1 : 0;
        if (!isset($input['is_public'])) {
            $is_public = 1;
        }

        sql_query("
            INSERT INTO `{$table}`
            SET
                challenge_id = '".(int) $challenge_id."',
                mb_id = '".sql_escape_string($writer['mb_id'])."',
                writer_name = '".sql_escape_string($writer_name)."',
                title = '".sql_escape_string($title)."',
                content = '".sql_escape_string($content)."',
                image = '".sql_escape_string($upload['filename'] ?? '')."',
                area = '".sql_escape_string($area)."',
                place_name = '".sql_escape_string(trim(strip_tags((string) ($input['place_name'] ?? ''))))."',
                related_url = '".sql_escape_string(eottae_challenge_sanitize_url($input['related_url'] ?? ''))."',
                related_room_id = '{$related_room_id}',
                is_public = '{$is_public}',
                status = 'active',
                created_at = '{$now}',
                updated_at = '{$now}'
        ", false);

        $entry_id = (int) sql_insert_id();
        if ($entry_id < 1) {
            return array('ok' => false, 'message' => '참여글 저장에 실패했습니다.');
        }

        $reward = eottae_challenge_grant_entry_rewards($entry_id);

        if (function_exists('eottae_member_growth_add_score') && !empty($writer['mb_id'])) {
            eottae_member_growth_add_score($writer['mb_id'], 'challenge_entry', 0, 'challenge', $entry_id, '챌린지 참여');
        }

        return array(
            'ok'       => true,
            'entry_id' => $entry_id,
            'reward'   => $reward,
            'message'  => '챌린지 참여가 완료되었습니다!',
        );
    }
}

if (!function_exists('eottae_challenge_grant_entry_rewards')) {
    function eottae_challenge_grant_entry_rewards($entry_id)
    {
        global $config;

        $entry_id = (int) $entry_id;
        $entry = eottae_challenge_get_entry($entry_id, true);
        if (!$entry) {
            return array('point' => 0, 'badge' => '');
        }

        $challenge = eottae_challenge_get((int) $entry['challenge_id'], true);
        if (!$challenge) {
            return array('point' => 0, 'badge' => '');
        }

        $mb_id = (string) ($entry['mb_id'] ?? '');
        $point = (int) ($challenge['reward_point'] ?? 0);
        $badge = preg_replace('/[^a-z_]/', '', (string) ($challenge['reward_badge'] ?? ''));
        $granted_point = 0;

        if ($point > 0 && $mb_id !== '' && !empty($config['cf_use_point']) && (int) ($entry['point_given'] ?? 0) < 1) {
            $result = insert_point(
                $mb_id,
                $point,
                '챌린지 참여: '.get_text($challenge['title'] ?? ''),
                'challenge',
                (string) $entry_id,
                'entry'
            );
            if ($result === 1) {
                $granted_point = $point;
                $table = eottae_challenge_entries_table();
                sql_query(" UPDATE `{$table}` SET point_given = '{$point}', updated_at = '".G5_TIME_YMDHIS."' WHERE entry_id = '{$entry_id}' ", false);
                eottae_challenge_log_reward((int) $challenge['challenge_id'], $entry_id, $mb_id, 'point', (string) $point);
            }
        }

        if ($badge !== '' && $mb_id !== '') {
            eottae_challenge_log_reward((int) $challenge['challenge_id'], $entry_id, $mb_id, 'badge', $badge);
        }

        return array('point' => $granted_point, 'badge' => $badge);
    }
}

if (!function_exists('eottae_challenge_log_reward')) {
    function eottae_challenge_log_reward($challenge_id, $entry_id, $mb_id, $type, $value)
    {
        $table = eottae_challenge_rewards_table();
        $challenge_id = (int) $challenge_id;
        $entry_id = (int) $entry_id;
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $type = preg_replace('/[^a-z_]/', '', (string) $type);
        $value = substr(strip_tags((string) $value), 0, 100);

        if ($challenge_id < 1 || $mb_id === '' || $type === '' || $value === '') {
            return false;
        }

        $exists = sql_fetch("
            SELECT reward_id
            FROM `{$table}`
            WHERE challenge_id = '{$challenge_id}'
              AND entry_id = '{$entry_id}'
              AND mb_id = '".sql_escape_string($mb_id)."'
              AND reward_type = '".sql_escape_string($type)."'
              AND reward_value = '".sql_escape_string($value)."'
            LIMIT 1
        ", false);
        if (!empty($exists['reward_id'])) {
            return true;
        }

        return (bool) sql_query("
            INSERT INTO `{$table}`
            SET
                challenge_id = '{$challenge_id}',
                entry_id = '{$entry_id}',
                mb_id = '".sql_escape_string($mb_id)."',
                reward_type = '".sql_escape_string($type)."',
                reward_value = '".sql_escape_string($value)."',
                created_at = '".G5_TIME_YMDHIS."'
        ", false);
    }
}

if (!function_exists('eottae_challenge_get_entry')) {
    function eottae_challenge_get_entry($entry_id, $include_private = false)
    {
        $entry_id = (int) $entry_id;
        if ($entry_id < 1) {
            return null;
        }

        $table = eottae_challenge_entries_table();
        $row = sql_fetch(" SELECT * FROM `{$table}` WHERE entry_id = '{$entry_id}' LIMIT 1 ", false);
        if (!is_array($row) || empty($row['entry_id'])) {
            return null;
        }

        if (($row['status'] ?? '') !== 'active') {
            return null;
        }
        if (!$include_private && empty($row['is_public'])) {
            return null;
        }

        return eottae_challenge_format_entry_row($row);
    }
}

if (!function_exists('eottae_challenge_get_entry_owned')) {
    function eottae_challenge_get_entry_owned($entry_id, $mb_id, $is_super = false)
    {
        $entry_id = (int) $entry_id;
        $table = eottae_challenge_entries_table();
        $row = sql_fetch(" SELECT * FROM `{$table}` WHERE entry_id = '{$entry_id}' LIMIT 1 ", false);
        if (!is_array($row) || empty($row['entry_id']) || ($row['status'] ?? '') === 'deleted') {
            return null;
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if (!$is_super && $mb_id !== ($row['mb_id'] ?? '')) {
            return null;
        }

        return eottae_challenge_format_entry_row($row);
    }
}

if (!function_exists('eottae_challenge_format_entry_row')) {
    function eottae_challenge_format_entry_row(array $row)
    {
        $entry_id = (int) ($row['entry_id'] ?? 0);
        $challenge_id = (int) ($row['challenge_id'] ?? 0);
        $image = (string) ($row['image'] ?? '');
        $challenge = eottae_challenge_get($challenge_id, true);

        $like_count = function_exists('eottae_challenge_like_count')
            ? eottae_challenge_like_count($entry_id)
            : 0;
        $comment_count = eottae_challenge_comment_count($entry_id);

        $room_name = '';
        $room_id = (int) ($row['related_room_id'] ?? 0);
        if ($room_id > 0 && function_exists('eottae_talkroom_get_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
            $room = eottae_talkroom_get_room($room_id);
            if (is_array($room)) {
                $room_name = get_text($room['room_name'] ?? '');
            }
        }

        return array_merge($row, array(
            'entry_id'       => $entry_id,
            'challenge_id' => $challenge_id,
            'challenge_title'=> is_array($challenge) ? get_text($challenge['title'] ?? '') : '',
            'area_label'     => eottae_challenge_area_label($row['area'] ?? ''),
            'image_url'      => $image !== '' ? eottae_challenge_image_url($image) : '',
            'like_count'     => $like_count,
            'comment_count'  => $comment_count,
            'room_name'      => $room_name,
            'time_label'     => function_exists('eottae_community_relative_time')
                ? eottae_community_relative_time($row['created_at'] ?? '')
                : substr((string) ($row['created_at'] ?? ''), 0, 16),
            'href'           => eottae_challenge_entry_url($entry_id),
            'is_best'        => !empty($row['is_best']) ? 1 : 0,
        ));
    }
}

if (!function_exists('eottae_challenge_list_entries')) {
    function eottae_challenge_list_entries($challenge_id, array $options = array())
    {
        $challenge_id = (int) $challenge_id;
        $limit = max(1, min(100, (int) ($options['limit'] ?? 20)));
        $offset = max(0, (int) ($options['offset'] ?? 0));
        $best_only = !empty($options['best_only']);
        $include_private = !empty($options['include_private']);

        $table = eottae_challenge_entries_table();
        $where = array(" challenge_id = '{$challenge_id}' ", " status = 'active' ");
        if (!$include_private) {
            $where[] = " is_public = 1 ";
        }
        if ($best_only) {
            $where[] = " is_best = 1 ";
        }

        $items = array();
        $result = sql_query("
            SELECT *
            FROM `{$table}`
            WHERE ".implode(' AND ', $where)."
            ORDER BY is_best DESC, created_at DESC
            LIMIT {$offset}, {$limit}
        ", false);

        while ($row = sql_fetch_array($result)) {
            if (!is_array($row)) {
                continue;
            }
            $items[] = eottae_challenge_format_entry_row($row);
        }

        return $items;
    }
}

if (!function_exists('eottae_challenge_delete_entry')) {
    function eottae_challenge_delete_entry($entry_id, $mb_id, $is_super = false)
    {
        $entry = eottae_challenge_get_entry_owned($entry_id, $mb_id, $is_super);
        if (!$entry) {
            return array('ok' => false, 'message' => '삭제 권한이 없습니다.');
        }

        $table = eottae_challenge_entries_table();
        sql_query("
            UPDATE `{$table}`
            SET status = 'deleted', updated_at = '".G5_TIME_YMDHIS."'
            WHERE entry_id = '".(int) $entry_id."'
        ", false);

        return array('ok' => true, 'message' => '참여글이 삭제되었습니다.');
    }
}

if (!function_exists('eottae_challenge_set_entry_best')) {
    function eottae_challenge_set_entry_best($entry_id, $is_best, $admin_mb_id)
    {
        $entry_id = (int) $entry_id;
        $table = eottae_challenge_entries_table();
        $row = sql_fetch(" SELECT * FROM `{$table}` WHERE entry_id = '{$entry_id}' AND status = 'active' LIMIT 1 ", false);
        if (empty($row['entry_id'])) {
            return array('ok' => false, 'message' => '참여글을 찾을 수 없습니다.');
        }

        $flag = $is_best ? 1 : 0;
        sql_query(" UPDATE `{$table}` SET is_best = '{$flag}', updated_at = '".G5_TIME_YMDHIS."' WHERE entry_id = '{$entry_id}' ", false);

        if ($flag) {
            eottae_challenge_log_reward((int) $row['challenge_id'], $entry_id, (string) $row['mb_id'], 'best', '1');
            if (function_exists('eottae_member_growth_on_best_post') && !empty($row['mb_id'])) {
                eottae_member_growth_on_best_post((string) $row['mb_id'], $entry_id, 'challenge_entry', '챌린지 우수글');
            }
        }

        return array('ok' => true, 'message' => $is_best ? '우수글로 선정했습니다.' : '우수글 선정을 해제했습니다.');
    }
}

if (!function_exists('eottae_challenge_admin_save')) {
    function eottae_challenge_admin_save(array $input, $admin_mb_id, $challenge_id = 0)
    {
        $challenge_id = (int) $challenge_id;
        $title = trim(strip_tags((string) ($input['title'] ?? '')));
        if ($title === '') {
            return array('ok' => false, 'message' => '챌린지 제목을 입력해 주세요.');
        }

        $start_date = trim((string) ($input['start_date'] ?? ''));
        $end_date = trim((string) ($input['end_date'] ?? ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            return array('ok' => false, 'message' => '시작일과 종료일을 올바르게 입력해 주세요.');
        }

        $status = preg_replace('/[^a-z_]/', '', (string) ($input['status'] ?? 'active'));
        if (!isset(eottae_challenge_status_options()[$status])) {
            $status = 'active';
        }

        $reward_badge = preg_replace('/[^a-z_]/', '', (string) ($input['reward_badge'] ?? ''));
        $table = eottae_challenge_challenges_table();
        $now = G5_TIME_YMDHIS;
        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);

        $image_sql = '';
        if (!empty($_FILES['challenge_image']) && is_uploaded_file($_FILES['challenge_image']['tmp_name'])) {
            $upload = eottae_challenge_save_upload_image('challenge_image');
            if (!empty($upload['ok']) && !empty($upload['filename'])) {
                $image_sql = ", image = '".sql_escape_string($upload['filename'])."' ";
            }
        }

        $fields = "
            title = '".sql_escape_string($title)."',
            description = '".sql_escape_string(trim((string) ($input['description'] ?? '')))."',
            start_date = '".sql_escape_string($start_date)."',
            end_date = '".sql_escape_string($end_date)."',
            status = '".sql_escape_string($status)."',
            icon = '".sql_escape_string(trim(strip_tags((string) ($input['icon'] ?? ''))))."',
            how_to_join = '".sql_escape_string(trim((string) ($input['how_to_join'] ?? '')))."',
            conditions_text = '".sql_escape_string(trim((string) ($input['conditions_text'] ?? '')))."',
            notice_text = '".sql_escape_string(trim((string) ($input['notice_text'] ?? '')))."',
            reward_text = '".sql_escape_string(trim((string) ($input['reward_text'] ?? '')))."',
            reward_point = '".max(0, (int) ($input['reward_point'] ?? 0))."',
            reward_badge = '".sql_escape_string($reward_badge)."',
            select_best = '".(!empty($input['select_best']) ? 1 : 0)."',
            is_featured = '".(!empty($input['is_featured']) ? 1 : 0)."',
            updated_at = '{$now}'
            {$image_sql}
        ";

        if ($challenge_id > 0) {
            sql_query(" UPDATE `{$table}` SET {$fields} WHERE challenge_id = '{$challenge_id}' ", false);
        } else {
            sql_query("
                INSERT INTO `{$table}`
                SET {$fields},
                    created_by = '".sql_escape_string($admin_mb_id)."',
                    created_at = '{$now}'
            ", false);
            $challenge_id = (int) sql_insert_id();
        }

        return array('ok' => true, 'challenge_id' => $challenge_id, 'message' => '저장되었습니다.');
    }
}

if (!function_exists('eottae_challenge_admin_delete')) {
    function eottae_challenge_admin_delete($challenge_id)
    {
        $challenge_id = (int) $challenge_id;
        if ($challenge_id < 1) {
            return array('ok' => false, 'message' => '챌린지 ID가 올바르지 않습니다.');
        }

        $table = eottae_challenge_challenges_table();
        sql_query(" UPDATE `{$table}` SET status = 'hidden', updated_at = '".G5_TIME_YMDHIS."' WHERE challenge_id = '{$challenge_id}' ", false);

        return array('ok' => true, 'message' => '챌린지를 숨김 처리했습니다.');
    }
}

if (!function_exists('eottae_challenge_comment_count')) {
    function eottae_challenge_comment_count($entry_id)
    {
        $entry_id = (int) $entry_id;
        if ($entry_id < 1) {
            return 0;
        }

        $table = eottae_challenge_comments_table();
        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$table}`
            WHERE entry_id = '{$entry_id}' AND status = 'active'
        ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_challenge_list_comments')) {
    function eottae_challenge_list_comments($entry_id, $limit = 50)
    {
        $entry_id = (int) $entry_id;
        $limit = max(1, min(100, (int) $limit));
        $table = eottae_challenge_comments_table();
        $items = array();

        $result = sql_query("
            SELECT *
            FROM `{$table}`
            WHERE entry_id = '{$entry_id}' AND status = 'active'
            ORDER BY created_at ASC
            LIMIT {$limit}
        ", false);

        while ($row = sql_fetch_array($result)) {
            if (!is_array($row)) {
                continue;
            }
            $items[] = array(
                'comment_id'  => (int) ($row['comment_id'] ?? 0),
                'writer_name' => get_text($row['writer_name'] ?? ''),
                'content'     => get_text($row['content'] ?? ''),
                'time_label'  => function_exists('eottae_community_relative_time')
                    ? eottae_community_relative_time($row['created_at'] ?? '')
                    : substr((string) ($row['created_at'] ?? ''), 0, 16),
            );
        }

        return $items;
    }
}

if (!function_exists('eottae_challenge_add_comment')) {
    function eottae_challenge_add_comment($entry_id, $mb_id, $writer_name, $content)
    {
        $entry = eottae_challenge_get_entry($entry_id, true);
        if (!$entry) {
            return array('ok' => false, 'message' => '참여글을 찾을 수 없습니다.');
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $content = trim(strip_tags((string) $content));
        if ($mb_id === '' || $content === '') {
            return array('ok' => false, 'message' => '댓글 내용을 입력해 주세요.');
        }

        $table = eottae_challenge_comments_table();
        sql_query("
            INSERT INTO `{$table}`
            SET
                entry_id = '".(int) $entry_id."',
                mb_id = '".sql_escape_string($mb_id)."',
                writer_name = '".sql_escape_string(get_text($writer_name))."',
                content = '".sql_escape_string($content)."',
                status = 'active',
                created_at = '".G5_TIME_YMDHIS."'
        ", false);

        return array('ok' => true, 'message' => '댓글이 등록되었습니다.');
    }
}

if (!function_exists('eottae_challenge_my_entries')) {
    function eottae_challenge_my_entries($mb_id, $limit = 30)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array();
        }

        $table = eottae_challenge_entries_table();
        $limit = max(1, min(100, (int) $limit));
        $items = array();

        $result = sql_query("
            SELECT *
            FROM `{$table}`
            WHERE mb_id = '".sql_escape_string($mb_id)."'
              AND status = 'active'
            ORDER BY created_at DESC
            LIMIT {$limit}
        ", false);

        while ($row = sql_fetch_array($result)) {
            if (!is_array($row)) {
                continue;
            }
            $items[] = eottae_challenge_format_entry_row($row);
        }

        return $items;
    }
}

if (!function_exists('eottae_challenge_my_rewards')) {
    function eottae_challenge_my_rewards($mb_id, $limit = 50)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array();
        }

        $table = eottae_challenge_rewards_table();
        $limit = max(1, min(100, (int) $limit));
        $items = array();

        $result = sql_query("
            SELECT r.*, c.title AS challenge_title
            FROM `{$table}` r
            LEFT JOIN `".eottae_challenge_challenges_table()."` c ON c.challenge_id = r.challenge_id
            WHERE r.mb_id = '".sql_escape_string($mb_id)."'
            ORDER BY r.created_at DESC
            LIMIT {$limit}
        ", false);

        while ($row = sql_fetch_array($result)) {
            if (!is_array($row)) {
                continue;
            }
            $label = (string) ($row['reward_value'] ?? '');
            if (($row['reward_type'] ?? '') === 'badge') {
                $label = eottae_challenge_badge_label($label);
            } elseif (($row['reward_type'] ?? '') === 'point') {
                $label = number_format((int) $label).'P';
            }
            $items[] = array(
                'reward_type'    => (string) ($row['reward_type'] ?? ''),
                'reward_label'   => $label,
                'challenge_title'=> get_text($row['challenge_title'] ?? ''),
                'created_at'     => (string) ($row['created_at'] ?? ''),
            );
        }

        return $items;
    }
}

if (!function_exists('eottae_challenge_my_summary')) {
    function eottae_challenge_my_summary($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $empty = array(
            'entry_count'  => 0,
            'point_total'  => 0,
            'badge_count'  => 0,
            'best_count'   => 0,
            'summary_line' => '아직 참여한 챌린지가 없습니다.',
        );
        if ($mb_id === '') {
            return $empty;
        }

        $entries_table = eottae_challenge_entries_table();
        $rewards_table = eottae_challenge_rewards_table();

        $entry_row = sql_fetch("
            SELECT COUNT(*) AS cnt, SUM(point_given) AS points, SUM(is_best) AS best_cnt
            FROM `{$entries_table}`
            WHERE mb_id = '".sql_escape_string($mb_id)."' AND status = 'active'
        ", false);

        $badge_row = sql_fetch("
            SELECT COUNT(DISTINCT reward_value) AS cnt
            FROM `{$rewards_table}`
            WHERE mb_id = '".sql_escape_string($mb_id)."' AND reward_type = 'badge'
        ", false);

        $entry_count = (int) ($entry_row['cnt'] ?? 0);
        $point_total = (int) ($entry_row['points'] ?? 0);
        $badge_count = (int) ($badge_row['cnt'] ?? 0);
        $best_count = (int) ($entry_row['best_cnt'] ?? 0);

        $summary_line = $empty['summary_line'];
        if ($entry_count > 0) {
            $parts = array('인증글 '.number_format($entry_count));
            if ($point_total > 0) {
                $parts[] = '포인트 '.number_format($point_total).'P';
            }
            if ($badge_count > 0) {
                $parts[] = '뱃지 '.number_format($badge_count);
            }
            $summary_line = implode(' · ', $parts);
        }

        return array(
            'entry_count'  => $entry_count,
            'point_total'  => $point_total,
            'badge_count'  => $badge_count,
            'best_count'   => $best_count,
            'summary_line' => $summary_line,
        );
    }
}

if (!function_exists('eottae_challenge_member_room_options')) {
    function eottae_challenge_member_room_options($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '' || !function_exists('eottae_talkroom_list_my_rooms')) {
            return array();
        }

        include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        $my = eottae_talkroom_list_my_rooms($mb_id);
        $options = array();

        foreach (array('created', 'joined') as $key) {
            if (empty($my[$key]) || !is_array($my[$key])) {
                continue;
            }
            foreach ($my[$key] as $room) {
                $room_id = (int) ($room['room_id'] ?? 0);
                if ($room_id < 1) {
                    continue;
                }
                if ($key === 'joined' && ($room['member_status'] ?? '') !== 'active') {
                    continue;
                }
                $options[$room_id] = get_text($room['room_name'] ?? '');
            }
        }

        return $options;
    }
}
