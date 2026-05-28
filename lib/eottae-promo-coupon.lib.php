<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-coupon.lib.php';

if (!function_exists('eottae_promo_coupon_bootstrap_tables')) {
    function eottae_promo_coupon_bootstrap_tables()
    {
        global $g5;

        if (!isset($g5['eottae_coupon_promo_table'])) {
            $g5['eottae_coupon_promo_table'] = G5_TABLE_PREFIX.'eottae_coupon_promo';
        }
        if (!isset($g5['eottae_coupon_promo_award_table'])) {
            $g5['eottae_coupon_promo_award_table'] = G5_TABLE_PREFIX.'eottae_coupon_promo_award';
        }
        if (!isset($g5['eottae_attendance_table'])) {
            $g5['eottae_attendance_table'] = G5_TABLE_PREFIX.'eottae_attendance';
        }
    }
}

if (!function_exists('eottae_promo_coupon_ensure_schema')) {
    function eottae_promo_coupon_ensure_schema()
    {
        global $g5;

        if (!eottae_coupon_ensure_ready()) {
            return false;
        }

        if (function_exists('eottae_business_coupon_ensure_schema')) {
            eottae_business_coupon_ensure_schema();
        }

        eottae_promo_coupon_bootstrap_tables();
        eottae_coupon_bootstrap_tables();

        $promo = $g5['eottae_coupon_promo_table'];
        $award = $g5['eottae_coupon_promo_award_table'];
        $attend = $g5['eottae_attendance_table'];
        $issue = $g5['eottae_coupon_issue_table'];

        sql_query(" CREATE TABLE IF NOT EXISTS `{$promo}` (
            `promo_id` int(11) NOT NULL AUTO_INCREMENT,
            `cp_id` int(11) NOT NULL DEFAULT '0',
            `promo_title` varchar(255) NOT NULL DEFAULT '',
            `promo_desc` text NOT NULL,
            `trigger_type` varchar(30) NOT NULL DEFAULT 'claim',
            `trigger_config` text NOT NULL,
            `promo_status` varchar(20) NOT NULL DEFAULT 'active',
            `promo_starts_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `promo_ends_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `promo_max_total` int(11) NOT NULL DEFAULT '0',
            `promo_max_per_member` int(11) NOT NULL DEFAULT '1',
            `promo_created_by` varchar(20) NOT NULL DEFAULT '',
            `promo_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `promo_updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`promo_id`),
            KEY `cp_id` (`cp_id`),
            KEY `trigger_type` (`trigger_type`),
            KEY `promo_status` (`promo_status`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

        sql_query(" CREATE TABLE IF NOT EXISTS `{$award}` (
            `award_id` int(11) NOT NULL AUTO_INCREMENT,
            `promo_id` int(11) NOT NULL DEFAULT '0',
            `cp_id` int(11) NOT NULL DEFAULT '0',
            `ci_id` int(11) NOT NULL DEFAULT '0',
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `award_key` varchar(120) NOT NULL DEFAULT '',
            `ref_bo_table` varchar(20) NOT NULL DEFAULT '',
            `ref_wr_id` int(11) NOT NULL DEFAULT '0',
            `awarded_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`award_id`),
            UNIQUE KEY `promo_award_key` (`promo_id`, `award_key`),
            KEY `mb_id` (`mb_id`),
            KEY `promo_id` (`promo_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

        sql_query(" CREATE TABLE IF NOT EXISTS `{$attend}` (
            `attend_id` int(11) NOT NULL AUTO_INCREMENT,
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `attend_date` date NOT NULL,
            `streak_count` int(11) NOT NULL DEFAULT '1',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`attend_id`),
            UNIQUE KEY `mb_date` (`mb_id`, `attend_date`),
            KEY `mb_id` (`mb_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

        $issue_cols = array(
            'ci_promo_id' => " int(11) NOT NULL DEFAULT '0' ",
            'ci_award_key' => " varchar(120) NOT NULL DEFAULT '' ",
        );
        foreach ($issue_cols as $col => $def) {
            $exists = sql_fetch(" SHOW COLUMNS FROM `{$issue}` LIKE '{$col}' ");
            if (empty($exists)) {
                sql_query(" ALTER TABLE `{$issue}` ADD `{$col}` {$def} ", false);
            }
        }

        return true;
    }
}

if (!function_exists('eottae_promo_trigger_types')) {
    function eottae_promo_trigger_types()
    {
        return array(
            'claim' => '회원 직접 받기 (선착순·수량 제한)',
            'quiz' => '퀴즈 정답 시 쿠폰',
            'post_count' => '글 N개 작성 시 자동 지급',
            'post_views' => '글 조회수 N회 달성 시 작성자에게 지급',
            'attendance_streak' => '출석 N일 연속 미션 완료',
            'best_comment' => '우수 댓글 선정 (관리자 지급)',
            'admin_grant' => '관리자 지정 회원 지급',
        );
    }
}

if (!function_exists('eottae_promo_trigger_type_label')) {
    function eottae_promo_trigger_type_label($type)
    {
        $types = eottae_promo_trigger_types();
        return isset($types[$type]) ? $types[$type] : $type;
    }
}

if (!function_exists('eottae_promo_parse_config')) {
    function eottae_promo_parse_config($promo)
    {
        if (!is_array($promo)) {
            return array();
        }
        $raw = isset($promo['trigger_config']) ? (string) $promo['trigger_config'] : '';
        if ($raw === '') {
            return array();
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : array();
    }
}

if (!function_exists('eottae_promo_encode_config')) {
    function eottae_promo_encode_config($config)
    {
        if (!is_array($config)) {
            return '{}';
        }
        return json_encode($config, JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('eottae_promo_community_boards')) {
    function eottae_promo_community_boards()
    {
        if (function_exists('eottae_community_hub_board_tables')) {
            return eottae_community_hub_board_tables();
        }

        $community = defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community';

        return array($community);
    }
}

if (!function_exists('eottae_promo_boards_from_config')) {
    function eottae_promo_boards_from_config($config)
    {
        if (!empty($config['bo_tables']) && is_array($config['bo_tables'])) {
            $boards = array();
            foreach ($config['bo_tables'] as $bo) {
                $bo = trim((string) $bo);
                if ($bo !== '') {
                    $boards[] = $bo;
                }
            }
            if (!empty($boards)) {
                return $boards;
            }
        }
        return eottae_promo_community_boards();
    }
}

if (!function_exists('eottae_promo_coupon_get')) {
    function eottae_promo_coupon_get($promo_id)
    {
        global $g5;

        eottae_promo_coupon_ensure_schema();
        $promo_id = (int) $promo_id;
        if ($promo_id < 1) {
            return array();
        }

        $row = sql_fetch(" select p.*, c.cp_title, c.cp_desc, c.cp_type, c.cp_max_issue, c.cp_expires_at
            from {$g5['eottae_coupon_promo_table']} p
            inner join {$g5['eottae_coupon_table']} c on c.cp_id = p.cp_id
            where p.promo_id = '{$promo_id}' limit 1 ");

        return is_array($row) ? $row : array();
    }
}

if (!function_exists('eottae_promo_coupon_is_active')) {
    function eottae_promo_coupon_is_active($promo)
    {
        if (!is_array($promo) || empty($promo['promo_id'])) {
            return false;
        }
        if ((string) $promo['promo_status'] !== 'active') {
            return false;
        }
        $now = G5_TIME_YMDHIS;
        if (!empty($promo['promo_starts_at']) && $promo['promo_starts_at'] !== '0000-00-00 00:00:00' && $promo['promo_starts_at'] > $now) {
            return false;
        }
        if (!empty($promo['promo_ends_at']) && $promo['promo_ends_at'] !== '0000-00-00 00:00:00' && $promo['promo_ends_at'] < $now) {
            return false;
        }
        if (!empty($promo['cp_expires_at']) && $promo['cp_expires_at'] !== '0000-00-00 00:00:00' && $promo['cp_expires_at'] < $now) {
            return false;
        }
        return true;
    }
}

if (!function_exists('eottae_promo_coupon_awarded_count')) {
    function eottae_promo_coupon_awarded_count($promo_id)
    {
        global $g5;

        eottae_promo_coupon_bootstrap_tables();
        $promo_id = (int) $promo_id;
        if ($promo_id < 1) {
            return 0;
        }

        $row = sql_fetch(" select count(*) as cnt from {$g5['eottae_coupon_promo_award_table']} where promo_id = '{$promo_id}' ");
        return isset($row['cnt']) ? (int) $row['cnt'] : 0;
    }
}

if (!function_exists('eottae_promo_coupon_member_award_count')) {
    function eottae_promo_coupon_member_award_count($promo_id, $mb_id)
    {
        global $g5;

        eottae_promo_coupon_bootstrap_tables();
        $promo_id = (int) $promo_id;
        $mb_id = sql_escape_string(trim((string) $mb_id));
        if ($promo_id < 1 || $mb_id === '') {
            return 0;
        }

        $row = sql_fetch(" select count(*) as cnt from {$g5['eottae_coupon_promo_award_table']}
            where promo_id = '{$promo_id}' and mb_id = '{$mb_id}' ");
        return isset($row['cnt']) ? (int) $row['cnt'] : 0;
    }
}

if (!function_exists('eottae_promo_coupon_member_has_award_key')) {
    function eottae_promo_coupon_member_has_award_key($promo_id, $award_key)
    {
        global $g5;

        eottae_promo_coupon_bootstrap_tables();
        $promo_id = (int) $promo_id;
        $award_key = sql_escape_string(trim((string) $award_key));
        if ($promo_id < 1 || $award_key === '') {
            return false;
        }

        $row = sql_fetch(" select award_id from {$g5['eottae_coupon_promo_award_table']}
            where promo_id = '{$promo_id}' and award_key = '{$award_key}' limit 1 ");
        return !empty($row['award_id']);
    }
}

if (!function_exists('eottae_promo_coupon_can_award')) {
    function eottae_promo_coupon_can_award($promo, $mb_id)
    {
        if (!eottae_promo_coupon_is_active($promo)) {
            return array('ok' => false, 'message' => '진행 중인 프로모션이 아닙니다.');
        }

        $promo_id = (int) $promo['promo_id'];
        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '회원 정보가 없습니다.');
        }

        $member = get_member($mb_id);
        if (empty($member['mb_id'])) {
            return array('ok' => false, 'message' => '존재하지 않는 회원입니다.');
        }

        $max_total = isset($promo['promo_max_total']) ? (int) $promo['promo_max_total'] : 0;
        if ($max_total > 0 && eottae_promo_coupon_awarded_count($promo_id) >= $max_total) {
            return array('ok' => false, 'message' => '쿠폰이 모두 소진되었습니다.');
        }

        $max_per = isset($promo['promo_max_per_member']) ? (int) $promo['promo_max_per_member'] : 1;
        if ($max_per > 0 && eottae_promo_coupon_member_award_count($promo_id, $mb_id) >= $max_per) {
            return array('ok' => false, 'message' => '이미 참여하셨습니다.');
        }

        return array('ok' => true, 'message' => '');
    }
}

if (!function_exists('eottae_promo_coupon_award')) {
    function eottae_promo_coupon_award($promo_id, $mb_id, $award_key, $ref = array())
    {
        global $g5;

        eottae_promo_coupon_ensure_schema();
        $promo = eottae_promo_coupon_get($promo_id);
        if (empty($promo['promo_id'])) {
            return array('ok' => false, 'message' => '프로모션을 찾을 수 없습니다.');
        }

        $award_key = trim((string) $award_key);
        if ($award_key === '') {
            return array('ok' => false, 'message' => '지급 키가 없습니다.');
        }

        if (eottae_promo_coupon_member_has_award_key($promo_id, $award_key)) {
            return array('ok' => true, 'message' => '이미 지급된 쿠폰입니다.', 'duplicate' => true);
        }

        $check = eottae_promo_coupon_can_award($promo, $mb_id);
        if (empty($check['ok'])) {
            return $check;
        }

        $cp_id = (int) $promo['cp_id'];
        $mb_esc = sql_escape_string(trim((string) $mb_id));
        $ref_bo = isset($ref['bo_table']) ? sql_escape_string(trim((string) $ref['bo_table'])) : '';
        $ref_wr = isset($ref['wr_id']) ? (int) $ref['wr_id'] : 0;

        sql_query(" insert into {$g5['eottae_coupon_issue_table']} set
            cp_id = '{$cp_id}',
            mb_id = '{$mb_esc}',
            ci_status = 'active',
            ci_datetime = '".G5_TIME_YMDHIS."',
            ci_used_datetime = '0000-00-00 00:00:00',
            ci_promo_id = '{$promo_id}',
            ci_award_key = '".sql_escape_string($award_key)."' ");

        $ci_id = (int) sql_insert_id();

        sql_query(" insert into {$g5['eottae_coupon_promo_award_table']} set
            promo_id = '{$promo_id}',
            cp_id = '{$cp_id}',
            ci_id = '{$ci_id}',
            mb_id = '{$mb_esc}',
            award_key = '".sql_escape_string($award_key)."',
            ref_bo_table = '{$ref_bo}',
            ref_wr_id = '{$ref_wr}',
            awarded_at = '".G5_TIME_YMDHIS."' ");

        return array(
            'ok' => true,
            'message' => '쿠폰이 발급되었습니다.',
            'ci_id' => $ci_id,
            'promo_id' => $promo_id,
        );
    }
}

if (!function_exists('eottae_promo_coupon_create')) {
    function eottae_promo_coupon_create($admin_mb_id, $data = array())
    {
        global $g5;

        eottae_promo_coupon_ensure_schema();
        $admin_mb_id = trim((string) $admin_mb_id);
        if ($admin_mb_id === '') {
            return array('ok' => false, 'message' => '관리자 정보가 없습니다.');
        }

        $title = isset($data['promo_title']) ? trim(strip_tags((string) $data['promo_title'])) : '';
        $desc = isset($data['promo_desc']) ? trim(strip_tags((string) $data['promo_desc'])) : '';
        $trigger_type = isset($data['trigger_type']) ? trim((string) $data['trigger_type']) : '';
        $types = eottae_promo_trigger_types();

        if ($title === '') {
            return array('ok' => false, 'message' => '프로모션 제목을 입력해 주세요.');
        }
        if ($trigger_type === '' || !isset($types[$trigger_type])) {
            return array('ok' => false, 'message' => '지급 조건 유형을 선택해 주세요.');
        }

        $config = isset($data['trigger_config']) && is_array($data['trigger_config']) ? $data['trigger_config'] : array();
        $validate = eottae_promo_validate_trigger_config($trigger_type, $config);
        if (empty($validate['ok'])) {
            return $validate;
        }
        $config = $validate['config'];

        $max_total = isset($data['promo_max_total']) ? max(0, min(100000, (int) $data['promo_max_total'])) : 0;
        $max_per = isset($data['promo_max_per_member']) ? max(1, min(100, (int) $data['promo_max_per_member'])) : 1;
        $starts_at = isset($data['promo_starts_at']) ? trim((string) $data['promo_starts_at']) : '';
        $ends_at = isset($data['promo_ends_at']) ? trim((string) $data['promo_ends_at']) : '';
        $expires_at = isset($data['cp_expires_at']) ? trim((string) $data['cp_expires_at']) : '';

        if (function_exists('cut_str')) {
            $title = cut_str($title, 255, '');
        }

        $cp_code = 'promo_'.substr(md5($admin_mb_id.uniqid('', true)), 0, 16);
        $expires_sql = '0000-00-00 00:00:00';
        if ($expires_at !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $expires_at)) {
            $expires_sql = $expires_at.' 23:59:59';
        }

        $starts_sql = '0000-00-00 00:00:00';
        if ($starts_at !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $starts_at)) {
            $starts_sql = $starts_at.' 00:00:00';
        }
        $ends_sql = '0000-00-00 00:00:00';
        if ($ends_at !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $ends_at)) {
            $ends_sql = $ends_at.' 23:59:59';
        }

        sql_query(" insert into {$g5['eottae_coupon_table']} set
            cp_code = '".sql_escape_string($cp_code)."',
            cp_title = '".sql_escape_string($title)."',
            cp_desc = '".sql_escape_string($desc !== '' ? $desc : $title)."',
            cp_type = 'promo',
            cp_owner_mb_id = '',
            cp_max_issue = '{$max_total}',
            cp_expires_at = '{$expires_sql}',
            cp_datetime = '".G5_TIME_YMDHIS."' ");

        $cp_id = (int) sql_insert_id();

        sql_query(" insert into {$g5['eottae_coupon_promo_table']} set
            cp_id = '{$cp_id}',
            promo_title = '".sql_escape_string($title)."',
            promo_desc = '".sql_escape_string($desc)."',
            trigger_type = '".sql_escape_string($trigger_type)."',
            trigger_config = '".sql_escape_string(eottae_promo_encode_config($config))."',
            promo_status = 'active',
            promo_starts_at = '{$starts_sql}',
            promo_ends_at = '{$ends_sql}',
            promo_max_total = '{$max_total}',
            promo_max_per_member = '{$max_per}',
            promo_created_by = '".sql_escape_string($admin_mb_id)."',
            promo_datetime = '".G5_TIME_YMDHIS."',
            promo_updated_at = '".G5_TIME_YMDHIS."' ");

        return array(
            'ok' => true,
            'message' => '프로모션 쿠폰이 등록되었습니다.',
            'promo_id' => (int) sql_insert_id(),
            'cp_id' => $cp_id,
        );
    }
}

if (!function_exists('eottae_promo_validate_trigger_config')) {
    function eottae_promo_validate_trigger_config($trigger_type, $config)
    {
        if (!is_array($config)) {
            $config = array();
        }

        if ($trigger_type === 'quiz') {
            $question = isset($config['question']) ? trim(strip_tags((string) $config['question'])) : '';
            $answer = isset($config['answer']) ? trim((string) $config['answer']) : '';
            if ($question === '' || $answer === '') {
                return array('ok' => false, 'message' => '퀴즈 질문과 정답을 입력해 주세요.');
            }
            return array('ok' => true, 'config' => array(
                'question' => $question,
                'answer' => $answer,
                'hint' => isset($config['hint']) ? trim(strip_tags((string) $config['hint'])) : '',
            ));
        }

        if ($trigger_type === 'post_count') {
            $min = isset($config['min_posts']) ? max(1, (int) $config['min_posts']) : 0;
            if ($min < 1) {
                return array('ok' => false, 'message' => '필요 글 수를 입력해 주세요.');
            }
            return array('ok' => true, 'config' => array(
                'min_posts' => $min,
                'bo_tables' => eottae_promo_boards_from_config($config),
            ));
        }

        if ($trigger_type === 'post_views') {
            $min = isset($config['min_views']) ? max(1, (int) $config['min_views']) : 0;
            if ($min < 1) {
                return array('ok' => false, 'message' => '필요 조회수를 입력해 주세요.');
            }
            return array('ok' => true, 'config' => array(
                'min_views' => $min,
                'bo_tables' => eottae_promo_boards_from_config($config),
            ));
        }

        if ($trigger_type === 'attendance_streak') {
            $days = isset($config['days']) ? max(1, min(365, (int) $config['days'])) : 0;
            if ($days < 1) {
                return array('ok' => false, 'message' => '출석 연속 일수를 입력해 주세요.');
            }
            return array('ok' => true, 'config' => array('days' => $days));
        }

        if ($trigger_type === 'claim' || $trigger_type === 'admin_grant' || $trigger_type === 'best_comment') {
            return array('ok' => true, 'config' => array(
                'guide' => isset($config['guide']) ? trim(strip_tags((string) $config['guide'])) : '',
            ));
        }

        return array('ok' => false, 'message' => '지원하지 않는 조건 유형입니다.');
    }
}

if (!function_exists('eottae_promo_coupon_update_status')) {
    function eottae_promo_coupon_update_status($promo_id, $status)
    {
        global $g5;

        eottae_promo_coupon_ensure_schema();
        $promo_id = (int) $promo_id;
        $status = in_array($status, array('active', 'paused', 'ended'), true) ? $status : 'paused';
        if ($promo_id < 1) {
            return array('ok' => false, 'message' => '프로모션을 찾을 수 없습니다.');
        }

        sql_query(" update {$g5['eottae_coupon_promo_table']} set
            promo_status = '".sql_escape_string($status)."',
            promo_updated_at = '".G5_TIME_YMDHIS."'
            where promo_id = '{$promo_id}' ");

        return array('ok' => true, 'message' => '상태가 변경되었습니다.');
    }
}

if (!function_exists('eottae_promo_coupon_list')) {
    function eottae_promo_coupon_list($status = '', $limit = 100)
    {
        global $g5;

        eottae_promo_coupon_ensure_schema();
        $limit = max(1, min(200, (int) $limit));
        $where = ' 1 ';
        if ($status !== '') {
            $where .= " and p.promo_status = '".sql_escape_string($status)."' ";
        }

        $result = sql_query(" select p.*, c.cp_title, c.cp_desc, c.cp_expires_at,
                (select count(*) from {$g5['eottae_coupon_promo_award_table']} a where a.promo_id = p.promo_id) as awarded_count
            from {$g5['eottae_coupon_promo_table']} p
            inner join {$g5['eottae_coupon_table']} c on c.cp_id = p.cp_id
            where {$where}
            order by p.promo_id desc
            limit {$limit} ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}

if (!function_exists('eottae_promo_coupon_list_active_by_trigger')) {
    function eottae_promo_coupon_list_active_by_trigger($trigger_type)
    {
        global $g5;

        eottae_promo_coupon_ensure_schema();
        $trigger_type = sql_escape_string(trim((string) $trigger_type));
        if ($trigger_type === '') {
            return array();
        }

        $now = G5_TIME_YMDHIS;
        $result = sql_query(" select p.*, c.cp_title, c.cp_desc, c.cp_expires_at
            from {$g5['eottae_coupon_promo_table']} p
            inner join {$g5['eottae_coupon_table']} c on c.cp_id = p.cp_id
            where p.trigger_type = '{$trigger_type}' and p.promo_status = 'active'
            and (p.promo_starts_at = '0000-00-00 00:00:00' or p.promo_starts_at <= '{$now}')
            and (p.promo_ends_at = '0000-00-00 00:00:00' or p.promo_ends_at >= '{$now}')
            order by p.promo_id desc ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            if (eottae_promo_coupon_is_active($row)) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
}

if (!function_exists('eottae_promo_member_visible_list')) {
    function eottae_promo_member_visible_list($mb_id = '')
    {
        $promos = eottae_promo_coupon_list('active', 50);
        $visible = array();

        foreach ($promos as $promo) {
            if (!eottae_promo_coupon_is_active($promo)) {
                continue;
            }
            $type = (string) $promo['trigger_type'];
            if (!in_array($type, array('claim', 'quiz', 'attendance_streak'), true)) {
                continue;
            }

            $item = $promo;
            $item['config'] = eottae_promo_parse_config($promo);
            $item['awarded_count'] = eottae_promo_coupon_awarded_count((int) $promo['promo_id']);
            $item['member_awarded'] = $mb_id !== '' ? eottae_promo_coupon_member_award_count((int) $promo['promo_id'], $mb_id) : 0;
            $item['can_participate'] = true;
            $item['status_message'] = '';

            if ($mb_id !== '') {
                $check = eottae_promo_coupon_can_award($promo, $mb_id);
                if (empty($check['ok'])) {
                    $item['can_participate'] = false;
                    $item['status_message'] = $check['message'];
                }
            }

            if ($type === 'attendance_streak') {
                $cfg = $item['config'];
                $need = isset($cfg['days']) ? (int) $cfg['days'] : 0;
                $streak = $mb_id !== '' ? eottae_attendance_get_streak($mb_id) : 0;
                $item['attendance_streak'] = $streak;
                $item['attendance_need'] = $need;
            }

            $visible[] = $item;
        }

        return $visible;
    }
}

if (!function_exists('eottae_promo_member_claim')) {
    function eottae_promo_member_claim($promo_id, $mb_id)
    {
        $promo = eottae_promo_coupon_get($promo_id);
        if (empty($promo['promo_id']) || $promo['trigger_type'] !== 'claim') {
            return array('ok' => false, 'message' => '받을 수 있는 프로모션이 아닙니다.');
        }

        return eottae_promo_coupon_award((int) $promo_id, $mb_id, 'claim:'.trim((string) $mb_id));
    }
}

if (!function_exists('eottae_promo_member_quiz')) {
    function eottae_promo_member_quiz($promo_id, $mb_id, $answer)
    {
        $promo = eottae_promo_coupon_get($promo_id);
        if (empty($promo['promo_id']) || $promo['trigger_type'] !== 'quiz') {
            return array('ok' => false, 'message' => '퀴즈 프로모션이 아닙니다.');
        }

        $config = eottae_promo_parse_config($promo);
        $expected = isset($config['answer']) ? trim((string) $config['answer']) : '';
        $given = trim((string) $answer);

        if ($expected === '' || $given === '') {
            return array('ok' => false, 'message' => '정답을 입력해 주세요.');
        }

        $normalize = function ($s) {
            $s = mb_strtolower($s, 'UTF-8');
            $s = preg_replace('/\s+/u', '', $s);
            return $s;
        };

        if ($normalize($expected) !== $normalize($given)) {
            return array('ok' => false, 'message' => '정답이 아닙니다. 다시 시도해 주세요.');
        }

        return eottae_promo_coupon_award((int) $promo_id, $mb_id, 'quiz:'.trim((string) $mb_id));
    }
}

if (!function_exists('eottae_promo_admin_grant')) {
    function eottae_promo_admin_grant($promo_id, $target_mb_id, $admin_mb_id)
    {
        $promo = eottae_promo_coupon_get($promo_id);
        if (empty($promo['promo_id'])) {
            return array('ok' => false, 'message' => '프로모션을 찾을 수 없습니다.');
        }
        if (!in_array($promo['trigger_type'], array('admin_grant'), true)) {
            return array('ok' => false, 'message' => '관리자 지급 유형 프로모션이 아닙니다.');
        }

        $key = 'admin:'.trim((string) $target_mb_id).':'.G5_TIME_YMDHIS.':'.mt_rand(100, 999);
        return eottae_promo_coupon_award((int) $promo_id, $target_mb_id, $key);
    }
}

if (!function_exists('eottae_promo_admin_best_comment')) {
    function eottae_promo_admin_best_comment($promo_id, $bo_table, $wr_id, $comment_wr_id, $admin_mb_id)
    {
        global $g5;

        $promo = eottae_promo_coupon_get($promo_id);
        if (empty($promo['promo_id']) || $promo['trigger_type'] !== 'best_comment') {
            return array('ok' => false, 'message' => '우수 댓글 프로모션이 아닙니다.');
        }

        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        $comment_wr_id = (int) $comment_wr_id;
        if ($bo_table === '' || $comment_wr_id < 1) {
            return array('ok' => false, 'message' => '댓글 정보가 올바르지 않습니다.');
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $comment = sql_fetch(" select wr_id, mb_id, wr_is_comment, wr_comment from {$write_table}
            where wr_id = '{$comment_wr_id}' and wr_is_comment = 1 limit 1 ");
        if (empty($comment['mb_id'])) {
            return array('ok' => false, 'message' => '회원 댓글을 찾을 수 없습니다.');
        }

        $award_key = 'comment:'.$bo_table.':'.$comment_wr_id;
        return eottae_promo_coupon_award(
            (int) $promo_id,
            $comment['mb_id'],
            $award_key,
            array('bo_table' => $bo_table, 'wr_id' => $comment_wr_id)
        );
    }
}

if (!function_exists('eottae_promo_count_member_posts')) {
    function eottae_promo_count_member_posts($mb_id, $boards = array())
    {
        global $g5;

        $mb_id = sql_escape_string(trim((string) $mb_id));
        if ($mb_id === '') {
            return 0;
        }
        if (empty($boards)) {
            $boards = eottae_promo_community_boards();
        }

        $total = 0;
        foreach ($boards as $bo_table) {
            $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
            if ($bo_table === '') {
                continue;
            }
            $write_table = $g5['write_prefix'].$bo_table;
            $row = sql_fetch(" select count(*) as cnt from {$write_table}
                where mb_id = '{$mb_id}' and wr_is_comment = 0 ");
            $total += isset($row['cnt']) ? (int) $row['cnt'] : 0;
        }
        return $total;
    }
}

if (!function_exists('eottae_promo_check_post_count')) {
    function eottae_promo_check_post_count($mb_id)
    {
        if ($mb_id === '') {
            return;
        }

        $promos = eottae_promo_coupon_list_active_by_trigger('post_count');
        foreach ($promos as $promo) {
            $config = eottae_promo_parse_config($promo);
            $min = isset($config['min_posts']) ? (int) $config['min_posts'] : 0;
            if ($min < 1) {
                continue;
            }
            $boards = eottae_promo_boards_from_config($config);
            $count = eottae_promo_count_member_posts($mb_id, $boards);
            if ($count >= $min) {
                eottae_promo_coupon_award(
                    (int) $promo['promo_id'],
                    $mb_id,
                    'post_count:'.$mb_id.':'.$min
                );
            }
        }
    }
}

if (!function_exists('eottae_promo_check_post_views')) {
    function eottae_promo_check_post_views($board, $write, $wr_id)
    {
        if (!is_array($board) || !is_array($write) || empty($write['mb_id'])) {
            return;
        }

        $bo_table = isset($board['bo_table']) ? (string) $board['bo_table'] : '';
        $wr_id = (int) $wr_id;
        if ($bo_table === '' || $wr_id < 1) {
            return;
        }

        global $g5;
        $write_table = $g5['write_prefix'].$bo_table;
        $row = sql_fetch(" select wr_hit from {$write_table} where wr_id = '{$wr_id}' limit 1 ");
        $hits = isset($row['wr_hit']) ? (int) $row['wr_hit'] : 0;

        $promos = eottae_promo_coupon_list_active_by_trigger('post_views');
        foreach ($promos as $promo) {
            $config = eottae_promo_parse_config($promo);
            $min = isset($config['min_views']) ? (int) $config['min_views'] : 0;
            if ($min < 1) {
                continue;
            }
            $boards = eottae_promo_boards_from_config($config);
            if (!in_array($bo_table, $boards, true)) {
                continue;
            }
            if ($hits >= $min) {
                eottae_promo_coupon_award(
                    (int) $promo['promo_id'],
                    $write['mb_id'],
                    'post_views:'.$bo_table.':'.$wr_id.':'.$min,
                    array('bo_table' => $bo_table, 'wr_id' => $wr_id)
                );
            }
        }
    }
}

if (!function_exists('eottae_attendance_get_streak')) {
    function eottae_attendance_get_streak($mb_id)
    {
        global $g5;

        eottae_promo_coupon_bootstrap_tables();
        $mb_id = sql_escape_string(trim((string) $mb_id));
        if ($mb_id === '') {
            return 0;
        }

        $today = G5_TIME_YMD;
        $row = sql_fetch(" select streak_count from {$g5['eottae_attendance_table']}
            where mb_id = '{$mb_id}' and attend_date = '{$today}' limit 1 ");
        if (!empty($row['streak_count'])) {
            return (int) $row['streak_count'];
        }

        $yesterday = date('Y-m-d', strtotime(G5_TIME_YMD.' -1 day'));
        $row = sql_fetch(" select streak_count from {$g5['eottae_attendance_table']}
            where mb_id = '{$mb_id}' and attend_date = '{$yesterday}' limit 1 ");
        return !empty($row['streak_count']) ? (int) $row['streak_count'] : 0;
    }
}

if (!function_exists('eottae_attendance_checked_today')) {
    function eottae_attendance_checked_today($mb_id)
    {
        global $g5;

        eottae_promo_coupon_bootstrap_tables();
        $mb_id = sql_escape_string(trim((string) $mb_id));
        if ($mb_id === '') {
            return false;
        }

        $today = G5_TIME_YMD;
        $row = sql_fetch(" select attend_id from {$g5['eottae_attendance_table']}
            where mb_id = '{$mb_id}' and attend_date = '{$today}' limit 1 ");
        return !empty($row['attend_id']);
    }
}

if (!function_exists('eottae_attendance_checkin')) {
    function eottae_attendance_checkin($mb_id)
    {
        global $g5;

        eottae_promo_coupon_ensure_schema();
        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '로그인 후 이용해 주세요.');
        }

        if (eottae_attendance_checked_today($mb_id)) {
            return array(
                'ok' => true,
                'message' => '오늘은 이미 출석하셨습니다.',
                'duplicate' => true,
                'streak' => eottae_attendance_get_streak($mb_id),
            );
        }

        $mb_esc = sql_escape_string($mb_id);
        $today = G5_TIME_YMD;
        $yesterday = date('Y-m-d', strtotime($today.' -1 day'));

        $prev = sql_fetch(" select streak_count from {$g5['eottae_attendance_table']}
            where mb_id = '{$mb_esc}' and attend_date = '{$yesterday}' limit 1 ");
        $streak = !empty($prev['streak_count']) ? (int) $prev['streak_count'] + 1 : 1;

        sql_query(" insert into {$g5['eottae_attendance_table']} set
            mb_id = '{$mb_esc}',
            attend_date = '{$today}',
            streak_count = '{$streak}',
            created_at = '".G5_TIME_YMDHIS."' ");

        eottae_promo_check_attendance_streak($mb_id, $streak);

        return array(
            'ok' => true,
            'message' => '출석 완료! 연속 '.$streak.'일',
            'streak' => $streak,
        );
    }
}

if (!function_exists('eottae_promo_check_attendance_streak')) {
    function eottae_promo_check_attendance_streak($mb_id, $streak = null)
    {
        if ($streak === null) {
            $streak = eottae_attendance_get_streak($mb_id);
        }
        $streak = (int) $streak;
        if ($streak < 1) {
            return;
        }

        $promos = eottae_promo_coupon_list_active_by_trigger('attendance_streak');
        foreach ($promos as $promo) {
            $config = eottae_promo_parse_config($promo);
            $need = isset($config['days']) ? (int) $config['days'] : 0;
            if ($need < 1 || $streak < $need) {
                continue;
            }
            eottae_promo_coupon_award(
                (int) $promo['promo_id'],
                $mb_id,
                'attendance:'.$mb_id.':'.$need
            );
        }
    }
}

if (!function_exists('eottae_promo_awards_for_member')) {
    function eottae_promo_awards_for_member($mb_id, $limit = 50)
    {
        global $g5;

        eottae_promo_coupon_ensure_schema();
        $mb_id = sql_escape_string(trim((string) $mb_id));
        if ($mb_id === '') {
            return array();
        }

        $limit = max(1, min(100, (int) $limit));
        $result = sql_query(" select a.*, p.promo_title, p.trigger_type, c.cp_title
            from {$g5['eottae_coupon_promo_award_table']} a
            inner join {$g5['eottae_coupon_promo_table']} p on p.promo_id = a.promo_id
            inner join {$g5['eottae_coupon_table']} c on c.cp_id = a.cp_id
            where a.mb_id = '{$mb_id}'
            order by a.award_id desc
            limit {$limit} ");
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}
