<?php
/**
 * 세부어때 쿠폰 테이블·발급 (setup/install, lib에서 include)
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_coupon_bootstrap_tables')) {
    function eottae_coupon_bootstrap_tables()
    {
        global $g5;

        if (!isset($g5['eottae_coupon_table'])) {
            $g5['eottae_coupon_table'] = G5_TABLE_PREFIX.'eottae_coupon';
        }
        if (!isset($g5['eottae_coupon_issue_table'])) {
            $g5['eottae_coupon_issue_table'] = G5_TABLE_PREFIX.'eottae_coupon_issue';
        }
    }
}

eottae_coupon_bootstrap_tables();

if (!function_exists('eottae_coupon_tables_ready')) {
    function eottae_coupon_tables_ready()
    {
        global $g5;

        static $ready = null;
        if ($ready !== null) {
            return $ready;
        }

        eottae_coupon_bootstrap_tables();
        $table = $g5['eottae_coupon_table'];
        $row = sql_fetch(" SHOW TABLES LIKE '{$table}' ");
        $ready = !empty($row);

        return $ready;
    }
}

if (!function_exists('eottae_install_create_coupon_tables')) {
    function eottae_install_create_coupon_tables()
    {
        global $g5;

        eottae_coupon_bootstrap_tables();

        $coupon = $g5['eottae_coupon_table'];
        $issue = $g5['eottae_coupon_issue_table'];

        sql_query(" CREATE TABLE IF NOT EXISTS `{$coupon}` (
            `cp_id` int(11) NOT NULL AUTO_INCREMENT,
            `cp_code` varchar(50) NOT NULL DEFAULT '',
            `cp_title` varchar(255) NOT NULL DEFAULT '',
            `cp_desc` text NOT NULL,
            `cp_type` varchar(30) NOT NULL DEFAULT 'general',
            `cp_max_issue` int(11) NOT NULL DEFAULT '0',
            `cp_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`cp_id`),
            UNIQUE KEY `cp_code` (`cp_code`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

        sql_query(" CREATE TABLE IF NOT EXISTS `{$issue}` (
            `ci_id` int(11) NOT NULL AUTO_INCREMENT,
            `cp_id` int(11) NOT NULL DEFAULT '0',
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `ci_status` varchar(20) NOT NULL DEFAULT 'active',
            `ci_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `ci_used_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`ci_id`),
            KEY `mb_id` (`mb_id`),
            KEY `cp_mb` (`cp_id`, `mb_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);

        return true;
    }
}

if (!function_exists('eottae_coupon_seed_defaults')) {
    function eottae_coupon_seed_defaults()
    {
        global $g5;

        eottae_coupon_bootstrap_tables();
        $table = $g5['eottae_coupon_table'];

        $items = array(
            array(
                'cp_code'  => 'welcome',
                'cp_title' => '신규 가입 웰컴 쿠폰',
                'cp_desc'  => '세부어때에 오신 것을 환영합니다. 제휴 업소에서 사용 가능한 웰컴 혜택 쿠폰입니다.',
                'cp_type'  => 'welcome',
            ),
            array(
                'cp_code'  => 'review_bonus',
                'cp_title' => '첫 리뷰 작성 감사 쿠폰',
                'cp_desc'  => '첫 업체 리뷰 작성 회원에게 자동 발급되는 감사 쿠폰입니다.',
                'cp_type'  => 'review',
            ),
        );

        $logs = array();
        foreach ($items as $item) {
            $code = sql_escape_string($item['cp_code']);
            $exists = sql_fetch(" select cp_id from {$table} where cp_code = '{$code}' limit 1 ");
            if (!empty($exists['cp_id'])) {
                $logs[] = array('ok' => true, 'action' => 'coupon', 'message' => $item['cp_code'].' already exists');
                continue;
            }
            $title = sql_escape_string($item['cp_title']);
            $desc = sql_escape_string($item['cp_desc']);
            $type = sql_escape_string($item['cp_type']);
            sql_query(" insert into {$table} set
                cp_code = '{$code}',
                cp_title = '{$title}',
                cp_desc = '{$desc}',
                cp_type = '{$type}',
                cp_max_issue = '0',
                cp_datetime = '".G5_TIME_YMDHIS."' ");
            $logs[] = array('ok' => true, 'action' => 'coupon', 'message' => $item['cp_code'].' created');
        }

        return $logs;
    }
}

if (!function_exists('eottae_coupon_get_by_code')) {
    function eottae_coupon_get_by_code($cp_code)
    {
        global $g5;

        eottae_coupon_bootstrap_tables();
        $cp_code = sql_escape_string((string) $cp_code);
        if ($cp_code === '') {
            return null;
        }

        return sql_fetch(" select * from {$g5['eottae_coupon_table']} where cp_code = '{$cp_code}' limit 1 ");
    }
}

if (!function_exists('eottae_coupon_member_has')) {
    function eottae_coupon_member_has($mb_id, $cp_id)
    {
        global $g5;

        eottae_coupon_bootstrap_tables();
        $mb_id = sql_escape_string((string) $mb_id);
        $cp_id = (int) $cp_id;
        if ($mb_id === '' || $cp_id < 1) {
            return false;
        }

        $row = sql_fetch(" select ci_id from {$g5['eottae_coupon_issue_table']}
            where mb_id = '{$mb_id}' and cp_id = '{$cp_id}' limit 1 ");

        return !empty($row['ci_id']);
    }
}

if (!function_exists('eottae_coupon_issue')) {
    function eottae_coupon_issue($mb_id, $cp_code)
    {
        global $g5;

        if (!eottae_coupon_tables_ready()) {
            return array('ok' => false, 'message' => '쿠폰 시스템이 준비되지 않았습니다.');
        }

        eottae_coupon_bootstrap_tables();
        $mb_id = sql_escape_string((string) $mb_id);
        if ($mb_id === '') {
            return array('ok' => false, 'message' => '회원 정보가 없습니다.');
        }

        $coupon = eottae_coupon_get_by_code($cp_code);
        if (!$coupon || empty($coupon['cp_id'])) {
            return array('ok' => false, 'message' => '쿠폰을 찾을 수 없습니다.');
        }

        if (eottae_coupon_member_has($mb_id, (int) $coupon['cp_id'])) {
            return array('ok' => true, 'message' => '이미 발급된 쿠폰입니다.', 'duplicate' => true);
        }

        $cp_id = (int) $coupon['cp_id'];
        sql_query(" insert into {$g5['eottae_coupon_issue_table']} set
            cp_id = '{$cp_id}',
            mb_id = '{$mb_id}',
            ci_status = 'active',
            ci_datetime = '".G5_TIME_YMDHIS."',
            ci_used_datetime = '0000-00-00 00:00:00' ");

        return array('ok' => true, 'message' => '쿠폰이 발급되었습니다.', 'cp_id' => $cp_id);
    }
}

if (!function_exists('eottae_coupon_ensure_welcome')) {
    function eottae_coupon_ensure_welcome($mb_id)
    {
        return eottae_coupon_issue($mb_id, defined('EOTTae_WELCOME_COUPON_CODE') ? EOTTae_WELCOME_COUPON_CODE : 'welcome');
    }
}

if (!function_exists('eottae_coupon_ensure_review_bonus')) {
    function eottae_coupon_ensure_review_bonus($mb_id)
    {
        return eottae_coupon_issue($mb_id, defined('EOTTae_REVIEW_COUPON_CODE') ? EOTTae_REVIEW_COUPON_CODE : 'review_bonus');
    }
}

if (!function_exists('eottae_coupon_get_member_list')) {
    function eottae_coupon_get_member_list($mb_id, $status = '')
    {
        global $g5;

        if (!eottae_coupon_tables_ready()) {
            return array();
        }

        eottae_coupon_bootstrap_tables();
        $mb_id = sql_escape_string((string) $mb_id);
        if ($mb_id === '') {
            return array();
        }

        $where = " i.mb_id = '{$mb_id}' ";
        if ($status !== '') {
            $status = sql_escape_string($status);
            $where .= " and i.ci_status = '{$status}' ";
        }

        $sql = " select i.*, c.cp_code, c.cp_title, c.cp_desc, c.cp_type
            from {$g5['eottae_coupon_issue_table']} i
            inner join {$g5['eottae_coupon_table']} c on c.cp_id = i.cp_id
            where {$where}
            order by i.ci_id desc ";
        $result = sql_query($sql);
        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $rows[] = $row;
        }

        return $rows;
    }
}

if (!function_exists('eottae_coupon_count_active')) {
    function eottae_coupon_count_active($mb_id)
    {
        return count(eottae_coupon_get_member_list($mb_id, 'active'));
    }
}

if (!function_exists('eottae_coupon_use')) {
    function eottae_coupon_use($mb_id, $ci_id)
    {
        global $g5;

        eottae_coupon_bootstrap_tables();
        $mb_id = sql_escape_string((string) $mb_id);
        $ci_id = (int) $ci_id;
        if ($mb_id === '' || $ci_id < 1) {
            return array('ok' => false, 'message' => '잘못된 요청입니다.');
        }

        $row = sql_fetch(" select * from {$g5['eottae_coupon_issue_table']}
            where ci_id = '{$ci_id}' and mb_id = '{$mb_id}' limit 1 ");
        if (empty($row['ci_id'])) {
            return array('ok' => false, 'message' => '쿠폰을 찾을 수 없습니다.');
        }
        if ($row['ci_status'] !== 'active') {
            return array('ok' => false, 'message' => '이미 사용되었거나 만료된 쿠폰입니다.');
        }

        sql_query(" update {$g5['eottae_coupon_issue_table']} set
            ci_status = 'used',
            ci_used_datetime = '".G5_TIME_YMDHIS."'
            where ci_id = '{$ci_id}' and mb_id = '{$mb_id}' ");

        return array('ok' => true, 'message' => '쿠폰 사용이 완료되었습니다.');
    }
}
