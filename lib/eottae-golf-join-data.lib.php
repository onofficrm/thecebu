<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_golf_join_use_mock_data')) {
    /**
     * true: UI용 목 데이터 (DB 비어 있거나 ?mock=1)
     */
    function eottae_golf_join_use_mock_data()
    {
        if (!empty($_GET['mock']) && (string) $_GET['mock'] === '1') {
            return true;
        }

        $tables = eottae_golf_join_table_names();
        if (!eottae_golf_join_table_exists($tables['posts'])) {
            return true;
        }

        $row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$tables['posts']}`
            WHERE deleted_at = '0000-00-00 00:00:00' ", false);

        return (int) ($row['cnt'] ?? 0) < 1;
    }
}

if (!function_exists('eottae_golf_join_parse_filters')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_golf_join_parse_filters()
    {
        $region = isset($_GET['region']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['region']) : '';
        $date_preset = isset($_GET['date_preset']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['date_preset']) : '';
        $date = isset($_GET['date']) ? preg_replace('/[^0-9\-]/', '', (string) $_GET['date']) : '';
        $time_zone = isset($_GET['time_zone']) ? preg_replace('/[^a-z]/', '', (string) $_GET['time_zone']) : '';
        $sort = isset($_GET['sort']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['sort']) : 'round_date';
        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

        if (!in_array($sort, array('seats', 'latest', 'round_date'), true)) {
            $sort = 'round_date';
        }

        return array(
            'region'        => $region,
            'date_preset'   => $date_preset,
            'date'          => $date,
            'time_zone'     => $time_zone,
            'exclude_full'  => !empty($_GET['exclude_full']),
            'sort'          => $sort,
            'q'             => $q,
        );
    }
}

if (!function_exists('eottae_golf_join_csv_to_list')) {
    function eottae_golf_join_csv_to_list($csv)
    {
        $csv = trim((string) $csv);
        if ($csv === '') {
            return array();
        }

        $parts = preg_split('/\s*,\s*/', $csv);

        return array_values(array_filter(array_map('trim', $parts ?: array())));
    }
}

if (!function_exists('eottae_golf_join_labels_from_codes')) {
    /**
     * @param array<string, string> $options
     * @param string|array<int, string> $codes
     * @return array<int, string>
     */
    function eottae_golf_join_labels_from_codes(array $options, $codes)
    {
        if (is_string($codes)) {
            $codes = eottae_golf_join_csv_to_list($codes);
        }
        if (!is_array($codes)) {
            return array();
        }

        $labels = array();
        foreach ($codes as $code) {
            $code = preg_replace('/[^a-z0-9_]/', '', (string) $code);
            if ($code === '') {
                continue;
            }
            $labels[] = isset($options[$code]) ? $options[$code] : $code;
        }

        return $labels;
    }
}

if (!function_exists('eottae_golf_join_format_round_date')) {
    function eottae_golf_join_format_round_date($date)
    {
        $date = (string) $date;
        if ($date === '' || $date === '0000-00-00') {
            return '';
        }

        $ts = strtotime($date);
        if (!$ts) {
            return $date;
        }

        $week = array('일', '월', '화', '수', '목', '금', '토');

        return date('n월 j일', $ts).' ('.$week[(int) date('w', $ts)].')';
    }
}

if (!function_exists('eottae_golf_join_format_tee_time')) {
    function eottae_golf_join_format_tee_time($time, $is_unknown = false)
    {
        if ($is_unknown) {
            return '티타임 미정';
        }

        $time = trim((string) $time);
        if ($time === '' || $time === '00:00:00') {
            return '티타임 미정';
        }

        $ts = strtotime('1970-01-01 '.$time);

        return $ts ? date('H:i', $ts) : $time;
    }
}

if (!function_exists('eottae_golf_join_format_price')) {
    function eottae_golf_join_format_price($amount)
    {
        $amount = (int) $amount;
        if ($amount < 1) {
            return '가격 협의';
        }

        return '₱'.number_format($amount);
    }
}

if (!function_exists('eottae_golf_join_status_meta')) {
    /**
     * @return array{label: string, class: string, banner: string, banner_tone: string}
     */
    function eottae_golf_join_status_meta($status)
    {
        $status = preg_replace('/[^a-z]/', '', (string) $status);
        $map = array(
            'recruiting' => array(
                'label'       => '모집중',
                'class'       => 'recruiting',
                'banner'      => '함께할 멤버를 모집중이에요!',
                'banner_tone' => 'info',
            ),
            'full' => array(
                'label'       => '모집완료',
                'class'       => 'full',
                'banner'      => '모집이 완료된 조인방이에요!',
                'banner_tone' => 'success',
            ),
            'closed' => array(
                'label'       => '마감',
                'class'       => 'closed',
                'banner'      => '마감된 조인방입니다.',
                'banner_tone' => 'muted',
            ),
            'cancelled' => array(
                'label'       => '취소',
                'class'       => 'cancelled',
                'banner'      => '마감된 조인방입니다.',
                'banner_tone' => 'muted',
            ),
        );

        return $map[$status] ?? $map['recruiting'];
    }
}

if (!function_exists('eottae_golf_join_normalize_post_row')) {
    /**
     * DB·목 데이터 공통 뷰 모델
     *
     * @param array<string, mixed> $row
     * @param array<int, array<string, mixed>> $members
     * @return array<string, mixed>
     */
    function eottae_golf_join_normalize_post_row(array $row, array $members = array())
    {
        $id = (int) ($row['id'] ?? 0);
        $status = preg_replace('/[^a-z]/', '', (string) ($row['status'] ?? 'recruiting'));
        $status_meta = eottae_golf_join_status_meta($status);
        $is_tee_unknown = !empty($row['is_tee_time_unknown']);
        $gender_code = preg_replace('/[^a-z]/', '', (string) ($row['gender_preference'] ?? 'any'));
        $gender_labels = eottae_golf_join_gender_preference_detail_labels();
        $age_labels = eottae_golf_join_labels_from_codes(
            eottae_golf_join_age_preference_options(),
            $row['age_preferences'] ?? ''
        );
        $score_labels = eottae_golf_join_labels_from_codes(
            eottae_golf_join_score_preference_options(),
            $row['score_preferences'] ?? ''
        );
        $mood_tags = eottae_golf_join_csv_to_list($row['mood_tags'] ?? '');
        $recruit = max(1, (int) ($row['recruit_count'] ?? 4));
        $current = max(0, (int) ($row['current_count'] ?? 0));
        $seats_left = max(0, $recruit - $current);

        $member_tags = array();
        if (isset($gender_labels[$gender_code])) {
            $member_tags[] = $gender_labels[$gender_code];
        }
        foreach ($age_labels as $label) {
            if ($label !== '연령무관') {
                $member_tags[] = $label;
            }
        }
        foreach ($score_labels as $label) {
            if ($label !== '타수무관') {
                $member_tags[] = $label;
            }
        }

        $host_nick = (string) ($row['host_nickname'] ?? '');
        if ($host_nick === '' && $members) {
            foreach ($members as $m) {
                if (($m['role'] ?? '') === 'host') {
                    $host_nick = (string) ($m['nickname'] ?? '');
                    break;
                }
            }
        }

        return array_merge($row, array(
            'id'                      => $id,
            'status'                  => $status,
            'status_label'            => $status_meta['label'],
            'status_class'            => $status_meta['class'],
            'banner_message'          => $status_meta['banner'],
            'banner_tone'             => $status_meta['banner_tone'],
            'region_label'            => eottae_golf_join_region_label($row['region'] ?? ''),
            'round_date_label'        => eottae_golf_join_format_round_date($row['round_date'] ?? ''),
            'tee_time_label'          => eottae_golf_join_format_tee_time($row['tee_time'] ?? '', $is_tee_unknown),
            'is_tee_time_unknown'     => $is_tee_unknown,
            'time_zone_label'         => eottae_golf_join_time_zone_options()[$row['time_zone'] ?? ''] ?? '',
            'price_label'             => eottae_golf_join_format_price($row['price'] ?? 0),
            'recruit_count'           => $recruit,
            'current_count'           => $current,
            'seats_left'              => $seats_left,
            'gender_preference_label' => $gender_labels[$gender_code] ?? '성별무관',
            'age_preference_labels'   => $age_labels,
            'score_preference_labels' => $score_labels,
            'mood_tags'               => $mood_tags,
            'member_condition_tags'   => $member_tags,
            'host_nickname'           => $host_nick,
            'detail_url'              => eottae_golf_join_detail_url($id),
            'members'                 => $members,
        ));
    }
}

if (!function_exists('eottae_golf_join_mock_posts_raw')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_golf_join_mock_posts_raw()
    {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $week_end = date('Y-m-d', strtotime('+5 days'));

        return array(
            array(
                'id' => 101,
                'user_id' => 'demo_host',
                'host_nickname' => '세부골퍼민',
                'title' => '주말 오전 라운드 같이 가요',
                'region' => 'cebu',
                'golf_course_id' => 1,
                'golf_course_name' => 'Cebu Country Club',
                'round_date' => $tomorrow,
                'tee_time' => '06:30:00',
                'is_tee_time_unknown' => 0,
                'time_zone' => 'morning',
                'price' => 4800,
                'recruit_count' => 4,
                'current_count' => 2,
                'gender_preference' => 'any',
                'age_preferences' => '30s,40s',
                'score_preferences' => '80s,90s',
                'mood_tags' => '즐겜라운드,맥주한잔',
                'description' => "세부 시티에서 가볍게 치실 분 구합니다.\n초보 환영, 분위기 좋게 즐겨요!",
                'status' => 'recruiting',
                'view_count' => 48,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'members' => array(
                    array('role' => 'host', 'user_id' => 'demo_host', 'nickname' => '세부골퍼민', 'status' => 'approved', 'gender' => 'M', 'age_group' => '40s', 'score_range' => '90s'),
                    array('role' => 'member', 'user_id' => 'demo_m1', 'nickname' => '라운딩메이트', 'status' => 'approved', 'gender' => 'M', 'age_group' => '30s', 'score_range' => '80s'),
                ),
            ),
            array(
                'id' => 102,
                'user_id' => 'demo_host2',
                'host_nickname' => '막탄언니',
                'title' => '막탄 야간 라운드 2자리',
                'region' => 'mactan',
                'golf_course_id' => 3,
                'golf_course_name' => 'Mactan Island Golf Club',
                'round_date' => $today,
                'tee_time' => '15:00:00',
                'is_tee_time_unknown' => 0,
                'time_zone' => 'afternoon',
                'price' => 5200,
                'recruit_count' => 4,
                'current_count' => 4,
                'gender_preference' => 'female',
                'age_preferences' => '30s',
                'score_preferences' => '90s',
                'mood_tags' => '실력향상,사진인증',
                'description' => '여성 라운드 위주로 모집합니다. 끝나고 가볍게 식사 가능해요.',
                'status' => 'full',
                'view_count' => 92,
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 hours')),
                'members' => array(
                    array('role' => 'host', 'user_id' => 'demo_host2', 'nickname' => '막탄언니', 'status' => 'approved', 'gender' => 'F', 'age_group' => '30s', 'score_range' => '90s'),
                    array('role' => 'member', 'user_id' => 'u1', 'nickname' => '필라골퍼', 'status' => 'approved', 'gender' => 'F', 'age_group' => '30s', 'score_range' => '100s'),
                    array('role' => 'member', 'user_id' => 'u2', 'nickname' => '골린이탈출', 'status' => 'approved', 'gender' => 'F', 'age_group' => '20s', 'score_range' => '90s'),
                    array('role' => 'member', 'user_id' => 'u3', 'nickname' => '쎄부총각', 'status' => 'approved', 'gender' => 'M', 'age_group' => '40s', 'score_range' => '80s'),
                ),
            ),
            array(
                'id' => 103,
                'user_id' => 'demo_host3',
                'host_nickname' => '클락프로',
                'title' => '클락 주말 골프 번개',
                'region' => 'clark',
                'golf_course_id' => 0,
                'golf_course_name' => 'Clark Sun Valley',
                'round_date' => $week_end,
                'tee_time' => null,
                'is_tee_time_unknown' => 1,
                'time_zone' => 'morning',
                'price' => 3500,
                'recruit_count' => 3,
                'current_count' => 1,
                'gender_preference' => 'male',
                'age_preferences' => 'any',
                'score_preferences' => '70s,80s',
                'mood_tags' => '번개모임,카풀가능',
                'description' => '티타임은 전날 확정 예정입니다. 클락 출발 카풀 가능.',
                'status' => 'recruiting',
                'view_count' => 21,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'members' => array(
                    array('role' => 'host', 'user_id' => 'demo_host3', 'nickname' => '클락프로', 'status' => 'approved', 'gender' => 'M', 'age_group' => '40s', 'score_range' => '70s'),
                ),
            ),
            array(
                'id' => 104,
                'user_id' => 'demo_host4',
                'host_nickname' => '보홀여행',
                'title' => '보홀 2박 골프 조인',
                'region' => 'bohol',
                'golf_course_id' => 0,
                'golf_course_name' => 'Panglao Island Golf',
                'round_date' => date('Y-m-d', strtotime('+10 days')),
                'tee_time' => '07:00:00',
                'is_tee_time_unknown' => 0,
                'time_zone' => 'morning',
                'price' => 6200,
                'recruit_count' => 4,
                'current_count' => 3,
                'gender_preference' => 'couple',
                'age_preferences' => '30s,40s',
                'score_preferences' => 'any',
                'mood_tags' => '여행골프,리조트',
                'description' => '보홀 리조트 골프 여행 같이 가실 커플·친구 모집합니다.',
                'status' => 'recruiting',
                'view_count' => 35,
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'members' => array(
                    array('role' => 'host', 'user_id' => 'demo_host4', 'nickname' => '보홀여행', 'status' => 'approved', 'gender' => 'F', 'age_group' => '30s', 'score_range' => '90s'),
                    array('role' => 'companion', 'user_id' => 'demo_host4b', 'nickname' => '보홀남편', 'status' => 'approved', 'gender' => 'M', 'age_group' => '30s', 'score_range' => '90s'),
                    array('role' => 'member', 'user_id' => 'u4', 'nickname' => '커플골퍼', 'status' => 'approved', 'gender' => 'F', 'age_group' => '40s', 'score_range' => '100s'),
                ),
            ),
            array(
                'id' => 105,
                'user_id' => 'demo_host5',
                'host_nickname' => '라푸골프',
                'title' => '라푸라푸 저녁 라운드',
                'region' => 'lapu_lapu',
                'golf_course_id' => 3,
                'golf_course_name' => 'Mactan Island Golf Club',
                'round_date' => $tomorrow,
                'tee_time' => '17:30:00',
                'is_tee_time_unknown' => 0,
                'time_zone' => 'evening',
                'price' => 0,
                'recruit_count' => 4,
                'current_count' => 2,
                'gender_preference' => 'any',
                'age_preferences' => '20s,30s',
                'score_preferences' => '80s,90s,100s',
                'mood_tags' => '야간라운드,맥주한잔',
                'description' => '퇴근 후 가볍게 치고 싶은 분 환영합니다.',
                'status' => 'recruiting',
                'view_count' => 17,
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                'members' => array(
                    array('role' => 'host', 'user_id' => 'demo_host5', 'nickname' => '라푸골프', 'status' => 'approved', 'gender' => 'M', 'age_group' => '30s', 'score_range' => '80s'),
                    array('role' => 'member', 'user_id' => 'u5', 'nickname' => '직장인A', 'status' => 'approved', 'gender' => 'M', 'age_group' => '20s', 'score_range' => '100s'),
                ),
            ),
            array(
                'id' => 106,
                'user_id' => 'demo_host6',
                'host_nickname' => '마닐라출장',
                'title' => '마닐라 비즈니스 골프',
                'region' => 'manila',
                'golf_course_id' => 0,
                'golf_course_name' => 'Wack Wack Golf',
                'round_date' => date('Y-m-d', strtotime('+3 days')),
                'tee_time' => '06:00:00',
                'is_tee_time_unknown' => 0,
                'time_zone' => 'morning',
                'price' => 7800,
                'recruit_count' => 4,
                'current_count' => 4,
                'gender_preference' => 'male',
                'age_preferences' => '40s,50plus',
                'score_preferences' => '70s,80s',
                'mood_tags' => '비즈니스,조용한라운드',
                'description' => '출장 중 가볍게 라운드 하실 분.',
                'status' => 'full',
                'view_count' => 64,
                'created_at' => date('Y-m-d H:i:s', strtotime('-12 hours')),
                'members' => array(
                    array('role' => 'host', 'user_id' => 'demo_host6', 'nickname' => '마닐라출장', 'status' => 'approved', 'gender' => 'M', 'age_group' => '50plus', 'score_range' => '80s'),
                ),
            ),
            array(
                'id' => 107,
                'user_id' => 'demo_host',
                'host_nickname' => '세부골퍼민',
                'title' => '알타비스타 오후 라운드',
                'region' => 'cebu',
                'golf_course_id' => 2,
                'golf_course_name' => 'Alta Vista Golf & Country Club',
                'round_date' => date('Y-m-d', strtotime('-2 days')),
                'tee_time' => '13:00:00',
                'is_tee_time_unknown' => 0,
                'time_zone' => 'afternoon',
                'price' => 4100,
                'recruit_count' => 4,
                'current_count' => 4,
                'gender_preference' => 'any',
                'age_preferences' => 'any',
                'score_preferences' => 'any',
                'mood_tags' => '',
                'description' => '종료된 라운드 샘플입니다.',
                'status' => 'closed',
                'view_count' => 120,
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                'members' => array(),
            ),
        );
    }
}

if (!function_exists('eottae_golf_join_fetch_members_for_posts')) {
    /**
     * @param array<int, int> $post_ids
     * @return array<int, array<int, array<string, mixed>>>
     */
    function eottae_golf_join_fetch_members_for_posts(array $post_ids)
    {
        $post_ids = array_values(array_filter(array_map('intval', $post_ids)));
        if (!$post_ids || !eottae_golf_join_table_exists(eottae_golf_join_table_names()['members'])) {
            return array();
        }

        $member_table = eottae_golf_join_member_table();
        $members_table = eottae_golf_join_table_names()['members'];
        $ids_sql = implode(',', $post_ids);
        $result = sql_query("
            SELECT m.*, mb.mb_nick AS member_nick
            FROM `{$members_table}` m
            LEFT JOIN `{$member_table}` mb ON mb.mb_id = m.user_id
            WHERE m.post_id IN ({$ids_sql})
            ORDER BY FIELD(m.role, 'host', 'companion', 'member'), m.id ASC
        ", false);

        $grouped = array();
        while ($row = sql_fetch_array($result)) {
            $pid = (int) ($row['post_id'] ?? 0);
            if ($pid < 1) {
                continue;
            }
            $nick = trim((string) ($row['nickname'] ?? ''));
            if ($nick === '') {
                $nick = trim((string) ($row['member_nick'] ?? ''));
            }
            $grouped[$pid][] = array(
                'id'          => (int) ($row['id'] ?? 0),
                'post_id'     => $pid,
                'user_id'     => (string) ($row['user_id'] ?? ''),
                'role'        => (string) ($row['role'] ?? 'member'),
                'status'      => (string) ($row['status'] ?? ''),
                'nickname'    => $nick,
                'gender'      => (string) ($row['gender'] ?? ''),
                'age_group'   => (string) ($row['age_group'] ?? ''),
                'score_range' => (string) ($row['score_range'] ?? ''),
                'message'     => (string) ($row['message'] ?? ''),
            );
        }

        return $grouped;
    }
}

if (!function_exists('eottae_golf_join_fetch_posts_from_db')) {
    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    function eottae_golf_join_fetch_posts_from_db(array $filters)
    {
        $tables = eottae_golf_join_table_names();
        if (!eottae_golf_join_table_exists($tables['posts'])) {
            return array();
        }

        $member_table = eottae_golf_join_member_table();
        $where = array(" p.deleted_at = '0000-00-00 00:00:00' ");

        if (!empty($filters['region'])) {
            $where[] = " p.region = '".sql_escape_string($filters['region'])."' ";
        }
        if (!empty($filters['time_zone'])) {
            $where[] = " p.time_zone = '".sql_escape_string($filters['time_zone'])."' ";
        }
        if (!empty($filters['exclude_full'])) {
            $where[] = " p.status IN ('recruiting') ";
        }
        if (!empty($filters['q'])) {
            $q = sql_escape_string($filters['q']);
            $where[] = " ( p.title LIKE '%{$q}%' OR p.golf_course_name LIKE '%{$q}%' OR p.description LIKE '%{$q}%' ) ";
        }

        $date_sql = eottae_golf_join_date_filter_sql($filters);
        if ($date_sql !== '') {
            $where[] = $date_sql;
        }

        $order = ' p.round_date ASC, p.tee_time ASC ';
        if (($filters['sort'] ?? '') === 'latest') {
            $order = ' p.created_at DESC ';
        } elseif (($filters['sort'] ?? '') === 'seats') {
            $order = ' (p.recruit_count - p.current_count) DESC, p.round_date ASC ';
        }

        $sql = "
            SELECT p.*, mb.mb_nick AS host_nickname
            FROM `{$tables['posts']}` p
            LEFT JOIN `{$member_table}` mb ON mb.mb_id = p.user_id
            WHERE ".implode(' AND ', $where)."
            ORDER BY {$order}
            LIMIT 100
        ";

        $result = sql_query($sql, false);
        $rows = array();
        $ids = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = $row;
            $ids[] = (int) ($row['id'] ?? 0);
        }

        $members_map = eottae_golf_join_fetch_members_for_posts($ids);
        $normalized = array();
        foreach ($rows as $row) {
            $pid = (int) ($row['id'] ?? 0);
            $members = $members_map[$pid] ?? array();
            $normalized[] = eottae_golf_join_normalize_post_row($row, $members);
        }

        return $normalized;
    }
}

if (!function_exists('eottae_golf_join_date_filter_sql')) {
    /**
     * @param array<string, mixed> $filters
     */
    function eottae_golf_join_date_filter_sql(array $filters)
    {
        $preset = (string) ($filters['date_preset'] ?? '');
        $today = date('Y-m-d');

        if ($preset === 'today') {
            return " p.round_date = '{$today}' ";
        }
        if ($preset === 'tomorrow') {
            $d = date('Y-m-d', strtotime('+1 day'));

            return " p.round_date = '{$d}' ";
        }
        if ($preset === 'week') {
            $end = date('Y-m-d', strtotime('sunday this week'));

            return " p.round_date >= '{$today}' AND p.round_date <= '{$end}' ";
        }
        if ($preset === 'custom' && !empty($filters['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date'])) {
            $d = sql_escape_string($filters['date']);

            return " p.round_date = '{$d}' ";
        }

        return '';
    }
}

if (!function_exists('eottae_golf_join_apply_list_filters')) {
    /**
     * @param array<int, array<string, mixed>> $posts
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    function eottae_golf_join_apply_list_filters(array $posts, array $filters)
    {
        $today = date('Y-m-d');

        return array_values(array_filter($posts, function ($post) use ($filters, $today) {
            if (!empty($filters['region']) && ($post['region'] ?? '') !== $filters['region']) {
                return false;
            }
            if (!empty($filters['time_zone']) && ($post['time_zone'] ?? '') !== $filters['time_zone']) {
                return false;
            }
            if (!empty($filters['exclude_full']) && !in_array($post['status'] ?? '', array('recruiting'), true)) {
                return false;
            }
            if (!empty($filters['q'])) {
                $q = mb_strtolower($filters['q'], 'UTF-8');
                $hay = mb_strtolower(
                    ($post['title'] ?? '').' '.($post['golf_course_name'] ?? '').' '.($post['description'] ?? ''),
                    'UTF-8'
                );
                if (mb_strpos($hay, $q, 0, 'UTF-8') === false) {
                    return false;
                }
            }

            $round = (string) ($post['round_date'] ?? '');
            $preset = (string) ($filters['date_preset'] ?? '');
            if ($preset === 'today' && $round !== $today) {
                return false;
            }
            if ($preset === 'tomorrow' && $round !== date('Y-m-d', strtotime('+1 day'))) {
                return false;
            }
            if ($preset === 'week') {
                $end = date('Y-m-d', strtotime('sunday this week'));
                if ($round < $today || $round > $end) {
                    return false;
                }
            }
            if ($preset === 'custom' && !empty($filters['date']) && $round !== $filters['date']) {
                return false;
            }

            return true;
        }));
    }
}

if (!function_exists('eottae_golf_join_sort_posts')) {
    /**
     * @param array<int, array<string, mixed>> $posts
     * @param array<string, mixed> $filters
     */
    function eottae_golf_join_sort_posts(array $posts, array $filters)
    {
        $sort = (string) ($filters['sort'] ?? 'round_date');

        usort($posts, function ($a, $b) use ($sort) {
            if ($sort === 'latest') {
                return strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
            }
            if ($sort === 'seats') {
                $sa = (int) ($a['seats_left'] ?? 0);
                $sb = (int) ($b['seats_left'] ?? 0);
                if ($sa !== $sb) {
                    return $sb <=> $sa;
                }
            }

            $da = (string) ($a['round_date'] ?? '');
            $db = (string) ($b['round_date'] ?? '');
            if ($da !== $db) {
                return strcmp($da, $db);
            }

            return strcmp((string) ($a['tee_time'] ?? ''), (string) ($b['tee_time'] ?? ''));
        });

        return $posts;
    }
}

if (!function_exists('eottae_golf_join_list_posts')) {
    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    function eottae_golf_join_list_posts(array $filters = array())
    {
        if (!$filters) {
            $filters = eottae_golf_join_parse_filters();
        }

        if (eottae_golf_join_use_mock_data()) {
            $raw = eottae_golf_join_mock_posts_raw();
            $posts = array();
            foreach ($raw as $row) {
                $members = $row['members'] ?? array();
                unset($row['members']);
                $posts[] = eottae_golf_join_normalize_post_row($row, $members);
            }
            $posts = eottae_golf_join_apply_list_filters($posts, $filters);

            return eottae_golf_join_sort_posts($posts, $filters);
        }

        $posts = eottae_golf_join_fetch_posts_from_db($filters);

        return eottae_golf_join_sort_posts($posts, $filters);
    }
}

if (!function_exists('eottae_golf_join_get_post')) {
    /**
     * @return array<string, mixed>|null
     */
    function eottae_golf_join_get_post($join_id, $viewer_mb_id = '')
    {
        $join_id = (int) $join_id;
        if ($join_id < 1) {
            return null;
        }

        $post = null;

        if (eottae_golf_join_use_mock_data()) {
            foreach (eottae_golf_join_mock_posts_raw() as $row) {
                if ((int) ($row['id'] ?? 0) === $join_id) {
                    $members = $row['members'] ?? array();
                    unset($row['members']);
                    $post = eottae_golf_join_normalize_post_row($row, $members);
                    break;
                }
            }
        } else {
            $tables = eottae_golf_join_table_names();
            $member_table = eottae_golf_join_member_table();
            $row = sql_fetch("
                SELECT p.*, mb.mb_nick AS host_nickname
                FROM `{$tables['posts']}` p
                LEFT JOIN `{$member_table}` mb ON mb.mb_id = p.user_id
                WHERE p.id = '{$join_id}'
                  AND p.deleted_at = '0000-00-00 00:00:00'
                LIMIT 1
            ", false);
            if (!empty($row['id'])) {
                $members_map = eottae_golf_join_fetch_members_for_posts(array($join_id));
                $post = eottae_golf_join_normalize_post_row($row, $members_map[$join_id] ?? array());
            }
        }

        if (!$post) {
            return null;
        }

        $post['viewer'] = eottae_golf_join_build_viewer_context($post, $viewer_mb_id);

        return $post;
    }
}

if (!function_exists('eottae_golf_join_build_viewer_context')) {
    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    function eottae_golf_join_build_viewer_context(array $post, $viewer_mb_id = '')
    {
        $viewer_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $viewer_mb_id);
        $host_id = (string) ($post['user_id'] ?? '');
        $status = (string) ($post['status'] ?? '');
        $member_status = 'none';

        foreach ($post['members'] ?? array() as $m) {
            if (($m['user_id'] ?? '') === $viewer_mb_id) {
                $member_status = (string) ($m['status'] ?? 'none');
                break;
            }
        }

        $is_host = $viewer_mb_id !== '' && $viewer_mb_id === $host_id;
        $is_logged_in = $viewer_mb_id !== '';
        $is_recruiting = $status === 'recruiting';
        $is_full = $status === 'full';
        $has_seat = function_exists('eottae_golf_join_has_seat_available')
            ? eottae_golf_join_has_seat_available($post)
            : ((int) ($post['current_count'] ?? 0) < (int) ($post['recruit_count'] ?? 1));
        $can_apply_status = in_array($member_status, array('none', 'cancelled'), true);

        $can_chat = false;
        if ($is_logged_in && $status !== 'cancelled') {
            if ($is_host) {
                $can_chat = true;
            } elseif ($member_status === 'approved') {
                $can_chat = true;
            }
        }

        return array(
            'is_logged_in'      => $is_logged_in,
            'is_host'           => $is_host,
            'member_status'     => $member_status,
            'can_apply'         => $is_logged_in && !$is_host && $is_recruiting && $has_seat && $can_apply_status,
            'show_applied'      => $member_status === 'pending',
            'show_cancel_apply' => $member_status === 'pending',
            'show_manage'       => $is_host,
            'show_close'        => $is_host && in_array($status, array('recruiting', 'full'), true),
            'show_chat'         => $can_chat,
            'show_disabled'     => !$is_recruiting && !$is_full,
        );
    }
}

if (!function_exists('eottae_golf_join_approved_members')) {
    /**
     * @param array<string, mixed> $post
     * @return array<int, array<string, mixed>>
     */
    function eottae_golf_join_approved_members(array $post)
    {
        $approved = array();
        foreach ($post['members'] ?? array() as $m) {
            if (($m['status'] ?? '') === 'approved') {
                $approved[] = $m;
            }
        }

        return $approved;
    }
}

if (!function_exists('eottae_golf_join_list_courses')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_golf_join_list_courses($region = '')
    {
        $tables = eottae_golf_join_table_names();
        if (!eottae_golf_join_table_exists($tables['courses'])) {
            return array();
        }

        $where = " is_active = '1' ";
        $region = preg_replace('/[^a-z_]/', '', (string) $region);
        if ($region !== '') {
            $where .= " AND region = '".sql_escape_string($region)."' ";
        }

        $result = sql_query("
            SELECT id, region, name, address
            FROM `{$tables['courses']}`
            WHERE {$where}
            ORDER BY region ASC, name ASC
        ", false);

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = array(
                'id'          => (int) ($row['id'] ?? 0),
                'region'      => (string) ($row['region'] ?? ''),
                'region_label'=> eottae_golf_join_region_label($row['region'] ?? ''),
                'name'        => (string) ($row['name'] ?? ''),
                'address'     => (string) ($row['address'] ?? ''),
            );
        }

        return $rows;
    }
}

if (!function_exists('eottae_golf_join_validate_create_input')) {
    /**
     * @param array<string, mixed> $input
     * @return array{ok: bool, message: string, data?: array<string, mixed>}
     */
    function eottae_golf_join_validate_create_input(array $input)
    {
        $register_mode = preg_replace('/[^a-z_]/', '', (string) ($input['register_mode'] ?? ''));
        if (!in_array($register_mode, array('fixed_tee', 'members_first'), true)) {
            return array('ok' => false, 'message' => '등록 방식을 선택해 주세요.');
        }

        $round_date = preg_replace('/[^0-9\-]/', '', (string) ($input['round_date'] ?? ''));
        if ($round_date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $round_date)) {
            return array('ok' => false, 'message' => '라운드 날짜를 선택해 주세요.');
        }

        $schedule_slot = preg_replace('/[^a-z]/', '', (string) ($input['schedule_slot'] ?? ''));
        $schedule_options = eottae_golf_join_schedule_slot_options();
        if (!isset($schedule_options[$schedule_slot])) {
            return array('ok' => false, 'message' => '시간대를 선택해 주세요.');
        }

        $golf_course_id = (int) ($input['golf_course_id'] ?? 0);
        $golf_course_name = trim((string) ($input['golf_course_name'] ?? ''));
        $course_custom = trim((string) ($input['golf_course_custom'] ?? ''));
        if ($golf_course_id < 1 && $course_custom !== '') {
            $golf_course_name = $course_custom;
            $golf_course_id = 0;
        }
        if ($golf_course_name === '') {
            return array('ok' => false, 'message' => '골프장을 선택하거나 직접 입력해 주세요.');
        }

        $region = preg_replace('/[^a-z_]/', '', (string) ($input['region'] ?? ''));
        if ($region === '' && $golf_course_id > 0) {
            $courses = eottae_golf_join_list_courses();
            foreach ($courses as $c) {
                if ((int) ($c['id'] ?? 0) === $golf_course_id) {
                    $region = (string) ($c['region'] ?? '');
                    break;
                }
            }
        }
        if ($region === '' || !isset(eottae_golf_join_region_options()[$region])) {
            return array('ok' => false, 'message' => '지역 정보를 확인해 주세요.');
        }

        $recruit_slots = (int) ($input['recruit_slots'] ?? 0);
        if (!in_array($recruit_slots, array(1, 2, 3), true)) {
            return array('ok' => false, 'message' => '모집 인원을 선택해 주세요.');
        }

        $gender = preg_replace('/[^a-z]/', '', (string) ($input['gender_preference'] ?? ''));
        if (!isset(eottae_golf_join_gender_preference_options()[$gender])) {
            return array('ok' => false, 'message' => '성별 조건을 선택해 주세요.');
        }

        $age = preg_replace('/[^a-z0-9_]/', '', (string) ($input['age_preference'] ?? ''));
        if (!isset(eottae_golf_join_age_preference_options()[$age])) {
            return array('ok' => false, 'message' => '나이 조건을 선택해 주세요.');
        }

        $score = preg_replace('/[^a-z0-9_]/', '', (string) ($input['score_preference'] ?? ''));
        if (!isset(eottae_golf_join_score_preference_options()[$score])) {
            return array('ok' => false, 'message' => '타수 조건을 선택해 주세요.');
        }

        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') {
            return array('ok' => false, 'message' => '방 제목을 입력해 주세요.');
        }
        if (function_exists('mb_strlen') && mb_strlen($title, 'UTF-8') > 120) {
            return array('ok' => false, 'message' => '방 제목은 120자 이내로 입력해 주세요.');
        }

        $description = trim((string) ($input['description'] ?? ''));
        if ($description === '') {
            return array('ok' => false, 'message' => '방 소개글을 입력해 주세요.');
        }

        $mood_tags = isset($input['mood_tags']) ? (array) $input['mood_tags'] : array();
        $allowed_tags = eottae_golf_join_mood_tag_options();
        $mood_clean = array();
        foreach ($mood_tags as $tag) {
            $tag = trim((string) $tag);
            if ($tag !== '' && in_array($tag, $allowed_tags, true) && !in_array($tag, $mood_clean, true)) {
                $mood_clean[] = $tag;
            }
        }
        if (count($mood_clean) > 3) {
            return array('ok' => false, 'message' => '분위기 태그는 최대 3개까지 선택할 수 있습니다.');
        }

        $is_unknown = $schedule_slot === 'unknown' ? 1 : 0;
        $time_zone = $is_unknown ? '' : $schedule_slot;
        $tee_time = null;
        if (!$is_unknown && $register_mode === 'fixed_tee') {
            $tee_raw = trim((string) ($input['tee_time'] ?? ''));
            if ($tee_raw !== '' && preg_match('/^\d{2}:\d{2}/', $tee_raw)) {
                $tee_time = substr($tee_raw, 0, 5).':00';
            }
        }

        $recruit_count = $recruit_slots + 1;

        return array(
            'ok'      => true,
            'message' => '',
            'data'    => array(
                'register_mode'      => $register_mode,
                'round_date'         => $round_date,
                'schedule_slot'      => $schedule_slot,
                'is_tee_time_unknown'=> $is_unknown,
                'time_zone'          => $time_zone,
                'tee_time'           => $tee_time,
                'golf_course_id'     => $golf_course_id > 0 ? $golf_course_id : null,
                'golf_course_name'   => $golf_course_name,
                'region'             => $region,
                'recruit_slots'      => $recruit_slots,
                'recruit_count'      => $recruit_count,
                'gender_preference'  => $gender,
                'age_preferences'    => $age,
                'score_preferences'  => $score,
                'title'              => $title,
                'description'        => $description,
                'mood_tags'          => $mood_clean,
                'host_nickname'      => trim((string) ($input['host_nickname'] ?? '')),
                'host_gender'        => strtoupper(substr((string) ($input['host_gender'] ?? ''), 0, 1)),
                'host_age_group'     => preg_replace('/[^a-z0-9_]/', '', (string) ($input['host_age_group'] ?? '')),
                'host_score_range'   => preg_replace('/[^a-z0-9_]/', '', (string) ($input['host_score_range'] ?? '')),
            ),
        );
    }
}

if (!function_exists('eottae_golf_join_create_post')) {
    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $member
     * @return array{ok: bool, message: string, join_id?: int}
     */
    function eottae_golf_join_create_post(array $input, array $member)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($member['mb_id'] ?? ''));
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인이 필요합니다.');
        }

        $check = eottae_golf_join_validate_create_input($input);
        if (empty($check['ok'])) {
            return array('ok' => false, 'message' => $check['message'] ?? '입력값을 확인해 주세요.');
        }

        $data = $check['data'];
        eottae_golf_join_ensure_schema();

        $tables = eottae_golf_join_table_names();
        if (!eottae_golf_join_table_exists($tables['posts'])) {
            return array('ok' => false, 'message' => '골프조인 DB가 준비되지 않았습니다.');
        }

        $now = G5_TIME_YMDHIS;
        $course_id_sql = $data['golf_course_id'] ? "'".(int) $data['golf_course_id']."'" : 'NULL';
        $tee_sql = $data['tee_time'] ? "'".sql_escape_string($data['tee_time'])."'" : 'NULL';

        $sql = "
            INSERT INTO `{$tables['posts']}` SET
                user_id = '".sql_escape_string($mb_id)."',
                title = '".sql_escape_string($data['title'])."',
                region = '".sql_escape_string($data['region'])."',
                golf_course_id = {$course_id_sql},
                golf_course_name = '".sql_escape_string($data['golf_course_name'])."',
                round_date = '".sql_escape_string($data['round_date'])."',
                tee_time = {$tee_sql},
                is_tee_time_unknown = '".(int) $data['is_tee_time_unknown']."',
                time_zone = '".sql_escape_string($data['time_zone'])."',
                price = '0',
                recruit_count = '".(int) $data['recruit_count']."',
                current_count = '1',
                gender_preference = '".sql_escape_string($data['gender_preference'])."',
                age_preferences = '".sql_escape_string($data['age_preferences'])."',
                score_preferences = '".sql_escape_string($data['score_preferences'])."',
                mood_tags = '".sql_escape_string(implode(',', $data['mood_tags']))."',
                description = '".sql_escape_string($data['description'])."',
                status = 'recruiting',
                register_mode = '".sql_escape_string($data['register_mode'])."',
                view_count = '0',
                report_count = '0',
                created_at = '{$now}',
                updated_at = '{$now}',
                deleted_at = '0000-00-00 00:00:00'
        ";

        if (!sql_query($sql, false)) {
            return array('ok' => false, 'message' => '조인 등록에 실패했습니다. 잠시 후 다시 시도해 주세요.');
        }

        $join_id = (int) sql_insert_id();
        if ($join_id < 1) {
            return array('ok' => false, 'message' => '조인 등록에 실패했습니다.');
        }

        $host_nick = $data['host_nickname'] !== ''
            ? $data['host_nickname']
            : trim((string) ($member['mb_nick'] ?? $mb_id));
        $host_gender = in_array($data['host_gender'], array('M', 'F'), true) ? $data['host_gender'] : '';

        if (eottae_golf_join_table_exists($tables['members'])) {
            sql_query("
                INSERT INTO `{$tables['members']}` SET
                    post_id = '{$join_id}',
                    user_id = '".sql_escape_string($mb_id)."',
                    role = 'host',
                    status = 'approved',
                    nickname = '".sql_escape_string($host_nick)."',
                    gender = '".sql_escape_string($host_gender)."',
                    age_group = '".sql_escape_string($data['host_age_group'])."',
                    score_range = '".sql_escape_string($data['host_score_range'])."',
                    message = '',
                    created_at = '{$now}',
                    updated_at = '{$now}'
            ", false);
        }

        return array(
            'ok'      => true,
            'message' => '골프조인이 등록되었습니다.',
            'join_id' => $join_id,
        );
    }
}
