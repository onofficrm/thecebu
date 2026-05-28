<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_message_bootstrap_tables')) {
    function eottae_message_bootstrap_tables()
    {
        global $g5;

        if (!isset($g5['sebu_message_threads_table'])) {
            $g5['sebu_message_threads_table'] = G5_TABLE_PREFIX.'sebu_message_threads';
        }
        if (!isset($g5['sebu_messages_table'])) {
            $g5['sebu_messages_table'] = G5_TABLE_PREFIX.'sebu_messages';
        }
    }
}

if (!function_exists('eottae_message_table_names')) {
    function eottae_message_table_names()
    {
        eottae_message_bootstrap_tables();
        global $g5;

        return array(
            'threads'  => $g5['sebu_message_threads_table'],
            'messages' => $g5['sebu_messages_table'],
        );
    }
}

if (!function_exists('eottae_message_member_table')) {
    function eottae_message_member_table()
    {
        global $g5;

        return isset($g5['member_table']) ? $g5['member_table'] : G5_TABLE_PREFIX.'member';
    }
}

if (!function_exists('eottae_message_table_exists')) {
    function eottae_message_table_exists($table)
    {
        if (!function_exists('eottae_talkroom_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        return eottae_talkroom_table_exists($table);
    }
}

if (!function_exists('eottae_message_ensure_schema')) {
    function eottae_message_ensure_schema()
    {
        $tables = eottae_message_table_names();

        $ddl = array(
            'threads' => " CREATE TABLE IF NOT EXISTS `{$tables['threads']}` (
                `thread_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `subject` varchar(200) NOT NULL DEFAULT '',
                `participant_a` varchar(20) NOT NULL DEFAULT '',
                `participant_b` varchar(20) NOT NULL DEFAULT '',
                `context_type` varchar(40) NOT NULL DEFAULT 'direct',
                `context_id` int(11) unsigned NOT NULL DEFAULT '0',
                `last_message_id` int(11) unsigned NOT NULL DEFAULT '0',
                `last_message_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `a_last_read_id` int(11) unsigned NOT NULL DEFAULT '0',
                `b_last_read_id` int(11) unsigned NOT NULL DEFAULT '0',
                `a_archived` tinyint(1) NOT NULL DEFAULT '0',
                `b_archived` tinyint(1) NOT NULL DEFAULT '0',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`thread_id`),
                KEY `idx_participant_a` (`participant_a`),
                KEY `idx_participant_b` (`participant_b`),
                KEY `idx_last_message_at` (`last_message_at`),
                KEY `idx_context` (`context_type`, `context_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ",
            'messages' => " CREATE TABLE IF NOT EXISTS `{$tables['messages']}` (
                `message_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `thread_id` int(11) unsigned NOT NULL DEFAULT '0',
                `sender_mb_id` varchar(20) NOT NULL DEFAULT '',
                `receiver_mb_id` varchar(20) NOT NULL DEFAULT '',
                `body` text NOT NULL,
                `is_system` tinyint(1) NOT NULL DEFAULT '0',
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`message_id`),
                KEY `idx_thread_id` (`thread_id`),
                KEY `idx_sender` (`sender_mb_id`),
                KEY `idx_receiver` (`receiver_mb_id`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ",
        );

        foreach ($ddl as $sql) {
            sql_query($sql, false);
        }
    }
}

if (!function_exists('eottae_message_url')) {
    function eottae_message_url($params = array())
    {
        $url = G5_URL.'/page/eottae-messages.php';
        if (!empty($params)) {
            $url .= '?'.http_build_query($params);
        }

        return $url;
    }
}

if (!function_exists('eottae_message_proc_url')) {
    function eottae_message_proc_url()
    {
        return G5_URL.'/proc/eottae-message.php';
    }
}

if (!function_exists('eottae_message_token')) {
    function eottae_message_token($regenerate = false)
    {
        $token = get_session('eottae_message_token');
        if ($regenerate || $token === '' || $token === null) {
            try {
                $token = bin2hex(random_bytes(16));
            } catch (Exception $e) {
                $token = md5(uniqid((string) mt_rand(), true));
            }
            set_session('eottae_message_token', $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_message_verify_token')) {
    function eottae_message_verify_token($token)
    {
        $token = trim((string) $token);
        $session_token = get_session('eottae_message_token');

        return $token !== '' && $session_token !== '' && hash_equals($session_token, $token);
    }
}

if (!function_exists('eottae_message_sanitize_member_id')) {
    function eottae_message_sanitize_member_id($mb_id)
    {
        return preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
    }
}

if (!function_exists('eottae_message_get_member')) {
    function eottae_message_get_member($keyword)
    {
        $keyword = trim((string) $keyword);
        if ($keyword === '') {
            return array();
        }

        $member_table = eottae_message_member_table();
        $escaped = sql_escape_string($keyword);
        $row = sql_fetch("
            SELECT mb_id, mb_nick, mb_name
            FROM `{$member_table}`
            WHERE mb_id = '{$escaped}' OR mb_nick = '{$escaped}'
            LIMIT 1
        ", false);

        return is_array($row) ? $row : array();
    }
}

if (!function_exists('eottae_message_member_label')) {
    function eottae_message_member_label($row, $fallback = '')
    {
        if (is_array($row)) {
            $nick = trim((string) ($row['mb_nick'] ?? ''));
            if ($nick !== '') {
                return $nick;
            }
            $name = trim((string) ($row['mb_name'] ?? ''));
            if ($name !== '') {
                return $name;
            }
            $id = trim((string) ($row['mb_id'] ?? ''));
            if ($id !== '') {
                return $id;
            }
        }

        return (string) $fallback;
    }
}

if (!function_exists('eottae_message_find_thread')) {
    function eottae_message_find_thread($mb_a, $mb_b, $context_type = 'direct', $context_id = 0)
    {
        $tables = eottae_message_table_names();
        $mb_a = eottae_message_sanitize_member_id($mb_a);
        $mb_b = eottae_message_sanitize_member_id($mb_b);
        $context_type = preg_replace('/[^a-z0-9_-]/i', '', (string) $context_type);
        $context_id = (int) $context_id;

        $first = strcmp($mb_a, $mb_b) <= 0 ? $mb_a : $mb_b;
        $second = $first === $mb_a ? $mb_b : $mb_a;

        return sql_fetch("
            SELECT *
            FROM `{$tables['threads']}`
            WHERE participant_a = '".sql_escape_string($first)."'
              AND participant_b = '".sql_escape_string($second)."'
              AND context_type = '".sql_escape_string($context_type)."'
              AND context_id = '{$context_id}'
            LIMIT 1
        ", false);
    }
}

if (!function_exists('eottae_message_create_thread')) {
    function eottae_message_create_thread($mb_a, $mb_b, $subject = '', $context_type = 'direct', $context_id = 0)
    {
        $tables = eottae_message_table_names();
        $mb_a = eottae_message_sanitize_member_id($mb_a);
        $mb_b = eottae_message_sanitize_member_id($mb_b);
        $first = strcmp($mb_a, $mb_b) <= 0 ? $mb_a : $mb_b;
        $second = $first === $mb_a ? $mb_b : $mb_a;
        $context_type = preg_replace('/[^a-z0-9_-]/i', '', (string) $context_type);
        $context_id = (int) $context_id;
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');

        sql_query("
            INSERT INTO `{$tables['threads']}` SET
                subject = '".sql_escape_string(trim((string) $subject))."',
                participant_a = '".sql_escape_string($first)."',
                participant_b = '".sql_escape_string($second)."',
                context_type = '".sql_escape_string($context_type)."',
                context_id = '{$context_id}',
                created_at = '{$now}',
                updated_at = '{$now}'
        ", false);

        return (int) sql_insert_id();
    }
}

if (!function_exists('eottae_message_get_thread')) {
    function eottae_message_get_thread($thread_id, $viewer_mb_id = '')
    {
        $tables = eottae_message_table_names();
        $thread_id = (int) $thread_id;
        if ($thread_id < 1) {
            return array();
        }

        $where = "thread_id = '{$thread_id}'";
        $viewer_mb_id = eottae_message_sanitize_member_id($viewer_mb_id);
        if ($viewer_mb_id !== '') {
            $viewer = sql_escape_string($viewer_mb_id);
            $where .= " AND (participant_a = '{$viewer}' OR participant_b = '{$viewer}')";
        }

        $row = sql_fetch("SELECT * FROM `{$tables['threads']}` WHERE {$where} LIMIT 1", false);

        return is_array($row) ? $row : array();
    }
}

if (!function_exists('eottae_message_other_participant')) {
    function eottae_message_other_participant($thread, $viewer_mb_id)
    {
        $viewer_mb_id = eottae_message_sanitize_member_id($viewer_mb_id);
        $a = (string) ($thread['participant_a'] ?? '');
        $b = (string) ($thread['participant_b'] ?? '');

        return $viewer_mb_id === $a ? $b : $a;
    }
}

if (!function_exists('eottae_message_operator_mb_id')) {
    function eottae_message_operator_mb_id()
    {
        global $config;

        $admin = isset($config['cf_admin']) ? eottae_message_sanitize_member_id($config['cf_admin']) : '';

        return $admin;
    }
}

if (!function_exists('eottae_message_parse_shop_inquiry_code')) {
    /**
     * @return array{bo_table:string, wr_id:int}
     */
    function eottae_message_parse_shop_inquiry_code($code)
    {
        $code = trim((string) $code);
        if (!preg_match('/^shop-([a-z0-9_]+)-(\d+)$/i', $code, $matches)) {
            return array('bo_table' => '', 'wr_id' => 0);
        }

        return array(
            'bo_table' => preg_replace('/[^a-z0-9_]/', '', (string) $matches[1]),
            'wr_id'    => (int) $matches[2],
        );
    }
}

if (!function_exists('eottae_message_shop_context')) {
    /**
     * @return array{bo_table:string, wr_id:int, shop_name:string, owner_mb_id:string, inquiry_code:string, subject:string}
     */
    function eottae_message_shop_context($inquiry_code)
    {
        $parsed = eottae_message_parse_shop_inquiry_code($inquiry_code);
        $context = array(
            'bo_table'      => $parsed['bo_table'],
            'wr_id'         => (int) $parsed['wr_id'],
            'shop_name'     => '',
            'owner_mb_id'   => '',
            'inquiry_code'  => trim((string) $inquiry_code),
            'subject'       => '',
        );

        if ($context['bo_table'] === '' || $context['wr_id'] < 1) {
            return $context;
        }

        global $g5;
        $write_table = $g5['write_prefix'].$context['bo_table'];
        $wr = sql_fetch("
            SELECT *
            FROM `{$write_table}`
            WHERE wr_id = '".(int) $context['wr_id']."'
              AND wr_is_comment = 0
            LIMIT 1
        ", false);
        if (!is_array($wr) || empty($wr['wr_id'])) {
            return $context;
        }

        if (function_exists('eottae_shop_from_write')) {
            $shop = eottae_shop_from_write($wr, $context['bo_table']);
            $context['shop_name'] = trim((string) ($shop['name'] ?? ''));
            if ($context['inquiry_code'] === '' && !empty($shop['inquiry_code'])) {
                $context['inquiry_code'] = (string) $shop['inquiry_code'];
            }
        } elseif (!empty($wr['wr_subject'])) {
            $context['shop_name'] = get_text($wr['wr_subject']);
        }

        if (is_file(G5_LIB_PATH.'/eottae-shop-owner.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-shop-owner.lib.php';
        }
        if (function_exists('eottae_shop_owner_mb_id_from_write')) {
            $context['owner_mb_id'] = eottae_message_sanitize_member_id(eottae_shop_owner_mb_id_from_write($wr));
        }

        if ($context['shop_name'] !== '') {
            $context['subject'] = '[업체] '.$context['shop_name'];
        }

        return $context;
    }
}

if (!function_exists('eottae_message_shop_owner_from_inquiry_code')) {
    function eottae_message_shop_owner_from_inquiry_code($inquiry_code)
    {
        $context = eottae_message_shop_context($inquiry_code);

        return (string) ($context['owner_mb_id'] ?? '');
    }
}

if (!function_exists('eottae_message_thread_context_label')) {
    function eottae_message_thread_context_label($thread)
    {
        if (!is_array($thread)) {
            return '';
        }

        $type = (string) ($thread['context_type'] ?? '');
        if ($type === 'shop') {
            $subject = trim((string) ($thread['subject'] ?? ''));
            if ($subject !== '') {
                return $subject;
            }

            return '업체 문의';
        }
        if ($type === 'operator') {
            return '운영진';
        }
        if ($type === 'golf_join') {
            $subject = trim((string) ($thread['subject'] ?? ''));
            if ($subject !== '') {
                return $subject;
            }

            return '골프조인';
        }
        if ($type === 'report') {
            $subject = trim((string) ($thread['subject'] ?? ''));
            if ($subject !== '') {
                return $subject;
            }

            return '제보함';
        }

        return '';
    }
}

if (!function_exists('eottae_message_validate_body')) {
    function eottae_message_validate_body($body)
    {
        $body = trim((string) $body);
        if ($body === '') {
            return array('ok' => false, 'message' => '메시지 내용을 입력해 주세요.', 'body' => '');
        }
        if (function_exists('mb_strlen') ? mb_strlen($body, 'UTF-8') > 3000 : strlen($body) > 9000) {
            return array('ok' => false, 'message' => '메시지는 3,000자 이내로 입력해 주세요.', 'body' => '');
        }

        return array('ok' => true, 'message' => '', 'body' => $body);
    }
}

if (!function_exists('eottae_message_send_to_member')) {
    function eottae_message_send_to_member($sender, $receiver_mb_id, $body, $context_type = 'direct', $context_id = 0, $subject = '')
    {
        eottae_message_ensure_schema();

        $sender_mb_id = eottae_message_sanitize_member_id(is_array($sender) ? ($sender['mb_id'] ?? '') : $sender);
        $receiver_mb_id = eottae_message_sanitize_member_id($receiver_mb_id);
        $validated = eottae_message_validate_body($body);
        if (empty($validated['ok'])) {
            return array('ok' => false, 'message' => $validated['message'] ?? '');
        }
        $body = (string) $validated['body'];

        if ($sender_mb_id === '') {
            return array('ok' => false, 'message' => '로그인이 필요합니다.');
        }
        if ($receiver_mb_id === '') {
            return array('ok' => false, 'message' => '받는 회원을 찾을 수 없습니다.');
        }
        if ($receiver_mb_id === $sender_mb_id) {
            return array('ok' => false, 'message' => '나에게는 메시지를 보낼 수 없습니다.');
        }

        $context_type = preg_replace('/[^a-z0-9_-]/i', '', (string) $context_type);
        if ($context_type === '') {
            $context_type = 'direct';
        }
        $context_id = (int) $context_id;

        $thread = eottae_message_find_thread($sender_mb_id, $receiver_mb_id, $context_type, $context_id);
        $thread_id = (int) ($thread['thread_id'] ?? 0);
        if ($thread_id < 1) {
            $thread_id = eottae_message_create_thread($sender_mb_id, $receiver_mb_id, $subject, $context_type, $context_id);
        } elseif ($subject !== '' && trim((string) ($thread['subject'] ?? '')) === '') {
            $tables = eottae_message_table_names();
            sql_query("
                UPDATE `{$tables['threads']}`
                SET subject = '".sql_escape_string($subject)."'
                WHERE thread_id = '{$thread_id}'
            ", false);
        }
        if ($thread_id < 1) {
            return array('ok' => false, 'message' => '대화를 만들 수 없습니다.');
        }

        return eottae_message_reply($thread_id, $sender, $body);
    }
}

if (!function_exists('eottae_message_send')) {
    function eottae_message_send($sender, $receiver_keyword, $body, $subject = '')
    {
        $receiver = eottae_message_get_member($receiver_keyword);
        $receiver_mb_id = eottae_message_sanitize_member_id($receiver['mb_id'] ?? '');
        if ($receiver_mb_id === '') {
            return array('ok' => false, 'message' => '받는 회원을 찾을 수 없습니다. 회원 ID 또는 닉네임을 확인해 주세요.');
        }

        return eottae_message_send_to_member($sender, $receiver_mb_id, $body, 'direct', 0, $subject);
    }
}

if (!function_exists('eottae_message_send_shop_inquiry')) {
    function eottae_message_send_shop_inquiry($sender, $inquiry_code, $body)
    {
        $context = eottae_message_shop_context($inquiry_code);
        if ($context['owner_mb_id'] === '') {
            return array('ok' => false, 'message' => '이 업체는 쪽지 문의를 받을 수 없습니다. 빠른 문의를 이용해 주세요.');
        }

        return eottae_message_send_to_member(
            $sender,
            $context['owner_mb_id'],
            $body,
            'shop',
            (int) $context['wr_id'],
            (string) $context['subject']
        );
    }
}

if (!function_exists('eottae_message_send_operator')) {
    function eottae_message_send_operator($sender, $body, $subject = '세부어때 운영진')
    {
        $operator_mb_id = eottae_message_operator_mb_id();
        if ($operator_mb_id === '') {
            return array('ok' => false, 'message' => '운영진 계정이 설정되어 있지 않습니다.');
        }

        return eottae_message_send_to_member($sender, $operator_mb_id, $body, 'operator', 0, $subject);
    }
}

if (!function_exists('eottae_message_golf_join_context')) {
    /**
     * @return array{join_id:int, host_mb_id:string, title:string, course_name:string, subject:string}
     */
    function eottae_message_golf_join_context($join_id)
    {
        $join_id = (int) $join_id;
        $context = array(
            'join_id'     => $join_id,
            'host_mb_id'  => '',
            'title'       => '',
            'course_name' => '',
            'subject'     => '',
        );
        if ($join_id < 1) {
            return $context;
        }

        if (!function_exists('eottae_golf_join_get_post') && is_file(G5_LIB_PATH.'/eottae-golf-join.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-golf-join.lib.php';
        }
        if (!function_exists('eottae_golf_join_get_post')) {
            return $context;
        }

        $post = eottae_golf_join_get_post($join_id);
        if (!$post) {
            return $context;
        }

        $context['host_mb_id'] = eottae_message_sanitize_member_id($post['user_id'] ?? '');
        $context['title'] = trim((string) ($post['title'] ?? ''));
        $context['course_name'] = trim((string) ($post['golf_course_name'] ?? ''));
        $label = $context['title'] !== '' ? $context['title'] : $context['course_name'];
        $context['subject'] = '[골프조인] '.($label !== '' ? $label : '조인 문의');

        return $context;
    }
}

if (!function_exists('eottae_message_send_golf_join_inquiry')) {
    function eottae_message_send_golf_join_inquiry($sender, $join_id, $body)
    {
        $context = eottae_message_golf_join_context($join_id);
        if ($context['host_mb_id'] === '') {
            return array('ok' => false, 'message' => '조인 작성자를 찾을 수 없습니다.');
        }

        return eottae_message_send_to_member(
            $sender,
            $context['host_mb_id'],
            $body,
            'golf_join',
            (int) $context['join_id'],
            (string) $context['subject']
        );
    }
}

if (!function_exists('eottae_message_report_context')) {
    /**
     * @return array{bo_table:string, wr_id:int, reporter_mb_id:string, subject:string, report_title:string}
     */
    function eottae_message_report_context($bo_table, $wr_id)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        $wr_id = (int) $wr_id;
        $context = array(
            'bo_table'        => $bo_table,
            'wr_id'           => $wr_id,
            'reporter_mb_id'  => '',
            'subject'         => '',
            'report_title'    => '',
        );
        if ($bo_table === '' || $wr_id < 1) {
            return $context;
        }

        if (!function_exists('eottae_is_report_board') && is_file(G5_LIB_PATH.'/eottae-report.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-report.lib.php';
        }
        if (!function_exists('eottae_is_report_board') || !eottae_is_report_board($bo_table)) {
            return $context;
        }

        global $g5;
        $write_table = $g5['write_prefix'].$bo_table;
        $row = sql_fetch("
            SELECT wr_id, mb_id, wr_subject
            FROM `{$write_table}`
            WHERE wr_id = '{$wr_id}'
              AND wr_is_comment = 0
            LIMIT 1
        ", false);
        if (!is_array($row) || empty($row['wr_id'])) {
            return $context;
        }

        $context['reporter_mb_id'] = eottae_message_sanitize_member_id($row['mb_id'] ?? '');
        $context['report_title'] = trim((string) ($row['wr_subject'] ?? ''));
        $context['subject'] = '[제보함] '.($context['report_title'] !== '' ? $context['report_title'] : '제보 답변');

        return $context;
    }
}

if (!function_exists('eottae_message_send_report_reply')) {
    function eottae_message_send_report_reply($sender, $bo_table, $wr_id, $body)
    {
        $context = eottae_message_report_context($bo_table, $wr_id);
        if ($context['reporter_mb_id'] === '') {
            return array('ok' => false, 'message' => '회원 제보가 아니어서 쪽지 답변을 보낼 수 없습니다.');
        }

        return eottae_message_send_to_member(
            $sender,
            $context['reporter_mb_id'],
            $body,
            'report',
            (int) $context['wr_id'],
            (string) $context['subject']
        );
    }
}

if (!function_exists('eottae_message_reply')) {
    function eottae_message_reply($thread_id, $sender, $body)
    {
        eottae_message_ensure_schema();

        $tables = eottae_message_table_names();
        $sender_mb_id = eottae_message_sanitize_member_id(is_array($sender) ? ($sender['mb_id'] ?? '') : $sender);
        $thread = eottae_message_get_thread($thread_id, $sender_mb_id);
        if (empty($thread['thread_id'])) {
            return array('ok' => false, 'message' => '대화를 찾을 수 없습니다.');
        }

        $validated = eottae_message_validate_body($body);
        if (empty($validated['ok'])) {
            return array('ok' => false, 'message' => $validated['message'] ?? '');
        }
        $body = (string) $validated['body'];

        $receiver_mb_id = eottae_message_other_participant($thread, $sender_mb_id);
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        sql_query("
            INSERT INTO `{$tables['messages']}` SET
                thread_id = '".(int) $thread['thread_id']."',
                sender_mb_id = '".sql_escape_string($sender_mb_id)."',
                receiver_mb_id = '".sql_escape_string($receiver_mb_id)."',
                body = '".sql_escape_string($body)."',
                created_at = '{$now}'
        ", false);

        $message_id = (int) sql_insert_id();
        if ($message_id < 1) {
            return array('ok' => false, 'message' => '메시지를 저장할 수 없습니다.');
        }

        $read_col = $sender_mb_id === (string) $thread['participant_a'] ? 'a_last_read_id' : 'b_last_read_id';
        sql_query("
            UPDATE `{$tables['threads']}` SET
                last_message_id = '{$message_id}',
                last_message_at = '{$now}',
                {$read_col} = '{$message_id}',
                a_archived = 0,
                b_archived = 0,
                updated_at = '{$now}'
            WHERE thread_id = '".(int) $thread['thread_id']."'
        ", false);

        return array(
            'ok' => true,
            'message' => '메시지를 보냈습니다.',
            'thread_id' => (int) $thread['thread_id'],
            'message_id' => $message_id,
        );
    }
}

if (!function_exists('eottae_message_filter_options')) {
    function eottae_message_filter_options()
    {
        return array(
            'all'       => '전체',
            'unread'    => '안읽음',
            'direct'    => '일반',
            'shop'      => '업체문의',
            'golf_join' => '골프조인',
            'report'    => '제보함',
            'operator'  => '운영진',
        );
    }
}

if (!function_exists('eottae_message_normalize_filter')) {
    function eottae_message_normalize_filter($filter)
    {
        $filter = preg_replace('/[^a-z0-9_]/', '', (string) $filter);
        $options = eottae_message_filter_options();

        return isset($options[$filter]) ? $filter : 'all';
    }
}

if (!function_exists('eottae_message_empty_state')) {
    /**
     * @return array{title:string, desc:string}
     */
    function eottae_message_empty_state($filter)
    {
        $filter = eottae_message_normalize_filter($filter);
        $states = array(
            'all'       => array('title' => '아직 쪽지가 없습니다', 'desc' => '회원 ID 또는 닉네임으로 새 쪽지를 보내면 이곳에 대화가 표시됩니다.'),
            'unread'    => array('title' => '읽지 않은 쪽지가 없습니다', 'desc' => '새 쪽지가 도착하면 이곳에서 바로 확인할 수 있습니다.'),
            'direct'    => array('title' => '일반 쪽지가 없습니다', 'desc' => '회원과 주고받은 일반 쪽지가 이곳에 표시됩니다.'),
            'shop'      => array('title' => '업체 문의 대화가 없습니다', 'desc' => '업체 상세의 문의하기로 시작한 대화가 이곳에 표시됩니다.'),
            'golf_join' => array('title' => '골프조인 문의가 없습니다', 'desc' => '골프조인 작성자와 주고받은 문의가 이곳에 표시됩니다.'),
            'report'    => array('title' => '제보함 답변이 없습니다', 'desc' => '제보함 운영진 답변 대화가 이곳에 표시됩니다.'),
            'operator'  => array('title' => '운영진 대화가 없습니다', 'desc' => '운영진에게 문의하거나 답변을 받으면 이곳에 표시됩니다.'),
        );

        return $states[$filter];
    }
}

if (!function_exists('eottae_message_thread_list')) {
    function eottae_message_thread_list($mb_id, $limit = 30, $filter = 'all')
    {
        eottae_message_ensure_schema();

        $tables = eottae_message_table_names();
        $member_table = eottae_message_member_table();
        $mb_id = eottae_message_sanitize_member_id($mb_id);
        $limit = max(1, min(100, (int) $limit));
        $filter = eottae_message_normalize_filter($filter);
        if ($mb_id === '') {
            return array();
        }

        $escaped = sql_escape_string($mb_id);
        $where = "((t.participant_a = '{$escaped}' AND t.a_archived = 0)
               OR (t.participant_b = '{$escaped}' AND t.b_archived = 0))";
        if ($filter === 'unread') {
            $where .= " AND (
                (t.participant_a = '{$escaped}' AND t.last_message_id > t.a_last_read_id AND t.last_message_id > 0)
                OR (t.participant_b = '{$escaped}' AND t.last_message_id > t.b_last_read_id AND t.last_message_id > 0)
            )";
        } elseif ($filter !== 'all') {
            $where .= " AND t.context_type = '".sql_escape_string($filter)."'";
        }

        $result = sql_query("
            SELECT t.*, m.body AS last_body, m.sender_mb_id AS last_sender_mb_id,
                   ma.mb_nick AS a_nick, ma.mb_name AS a_name,
                   mb.mb_nick AS b_nick, mb.mb_name AS b_name
            FROM `{$tables['threads']}` t
            LEFT JOIN `{$tables['messages']}` m ON m.message_id = t.last_message_id
            LEFT JOIN `{$member_table}` ma ON ma.mb_id = t.participant_a
            LEFT JOIN `{$member_table}` mb ON mb.mb_id = t.participant_b
            WHERE {$where}
            ORDER BY t.last_message_at DESC, t.thread_id DESC
            LIMIT {$limit}
        ", false);

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $is_a = $mb_id === (string) $row['participant_a'];
            $other = $is_a
                ? array('mb_id' => $row['participant_b'], 'mb_nick' => $row['b_nick'], 'mb_name' => $row['b_name'])
                : array('mb_id' => $row['participant_a'], 'mb_nick' => $row['a_nick'], 'mb_name' => $row['a_name']);
            $last_read_id = (int) ($is_a ? $row['a_last_read_id'] : $row['b_last_read_id']);
            $last_message_id = (int) ($row['last_message_id'] ?? 0);
            $row['other_mb_id'] = (string) $other['mb_id'];
            $row['other_label'] = eottae_message_member_label($other, $row['other_mb_id']);
            $row['context_label'] = eottae_message_thread_context_label($row);
            $row['is_unread'] = $last_message_id > $last_read_id && (string) ($row['last_sender_mb_id'] ?? '') !== $mb_id;
            $row['last_body_preview'] = get_text(cut_str(strip_tags((string) ($row['last_body'] ?? '')), 80));
            $row['href'] = eottae_message_url(array('thread_id' => (int) $row['thread_id']));
            $rows[] = $row;
        }

        return $rows;
    }
}

if (!function_exists('eottae_message_filter_counts')) {
    function eottae_message_filter_counts($mb_id)
    {
        eottae_message_ensure_schema();

        $tables = eottae_message_table_names();
        $mb_id = eottae_message_sanitize_member_id($mb_id);
        $counts = array_fill_keys(array_keys(eottae_message_filter_options()), 0);
        if ($mb_id === '') {
            return $counts;
        }

        $escaped = sql_escape_string($mb_id);
        $result = sql_query("
            SELECT t.context_type,
                   SUM(CASE
                       WHEN (
                           (t.participant_a = '{$escaped}' AND t.last_message_id > t.a_last_read_id AND t.last_message_id > 0)
                           OR (t.participant_b = '{$escaped}' AND t.last_message_id > t.b_last_read_id AND t.last_message_id > 0)
                       ) THEN 1 ELSE 0 END) AS unread_cnt,
                   COUNT(*) AS cnt
            FROM `{$tables['threads']}` t
            WHERE (t.participant_a = '{$escaped}' AND t.a_archived = 0)
               OR (t.participant_b = '{$escaped}' AND t.b_archived = 0)
            GROUP BY t.context_type
        ", false);

        while ($row = sql_fetch_array($result)) {
            $type = eottae_message_normalize_filter($row['context_type'] ?? 'direct');
            if ($type === 'all' || $type === 'unread') {
                $type = 'direct';
            }
            $cnt = (int) ($row['cnt'] ?? 0);
            $unread = (int) ($row['unread_cnt'] ?? 0);
            $counts['all'] += $cnt;
            $counts['unread'] += $unread;
            if (isset($counts[$type])) {
                $counts[$type] += $cnt;
            }
        }

        return $counts;
    }
}

if (!function_exists('eottae_message_list_messages')) {
    function eottae_message_list_messages($thread_id, $viewer_mb_id, $limit = 100)
    {
        eottae_message_ensure_schema();

        $thread = eottae_message_get_thread($thread_id, $viewer_mb_id);
        if (empty($thread['thread_id'])) {
            return array();
        }

        $tables = eottae_message_table_names();
        $member_table = eottae_message_member_table();
        $limit = max(1, min(200, (int) $limit));
        $result = sql_query("
            SELECT m.*, mem.mb_nick, mem.mb_name
            FROM `{$tables['messages']}` m
            LEFT JOIN `{$member_table}` mem ON mem.mb_id = m.sender_mb_id
            WHERE m.thread_id = '".(int) $thread['thread_id']."'
            ORDER BY m.message_id ASC
            LIMIT {$limit}
        ", false);

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            $row['sender_label'] = eottae_message_member_label($row, $row['sender_mb_id'] ?? '');
            $rows[] = $row;
        }

        return $rows;
    }
}

if (!function_exists('eottae_message_mark_thread_read')) {
    function eottae_message_mark_thread_read($thread_id, $mb_id)
    {
        $tables = eottae_message_table_names();
        $thread = eottae_message_get_thread($thread_id, $mb_id);
        if (empty($thread['thread_id'])) {
            return false;
        }

        $mb_id = eottae_message_sanitize_member_id($mb_id);
        $col = $mb_id === (string) $thread['participant_a'] ? 'a_last_read_id' : 'b_last_read_id';
        $last_message_id = (int) ($thread['last_message_id'] ?? 0);
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        sql_query("
            UPDATE `{$tables['threads']}`
            SET {$col} = '{$last_message_id}', updated_at = '{$now}'
            WHERE thread_id = '".(int) $thread['thread_id']."'
        ", false);

        return true;
    }
}

if (!function_exists('eottae_message_unread_count')) {
    function eottae_message_unread_count($mb_id)
    {
        eottae_message_ensure_schema();

        $tables = eottae_message_table_names();
        $mb_id = eottae_message_sanitize_member_id($mb_id);
        if ($mb_id === '') {
            return 0;
        }
        $escaped = sql_escape_string($mb_id);
        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$tables['threads']}`
            WHERE (
                participant_a = '{$escaped}'
                AND a_archived = 0
                AND last_message_id > a_last_read_id
                AND last_message_id > 0
            ) OR (
                participant_b = '{$escaped}'
                AND b_archived = 0
                AND last_message_id > b_last_read_id
                AND last_message_id > 0
            )
        ", false);

        return isset($row['cnt']) ? (int) $row['cnt'] : 0;
    }
}

if (!function_exists('eottae_message_mypage_summary')) {
    /**
     * @return array{unread_count:int, thread_count:int, latest:array<string,mixed>|null, summary_line:string}
     */
    function eottae_message_mypage_summary($mb_id)
    {
        $mb_id = eottae_message_sanitize_member_id($mb_id);
        if ($mb_id === '') {
            return array(
                'unread_count' => 0,
                'thread_count' => 0,
                'latest'       => null,
                'summary_line' => '로그인 후 쪽지를 확인할 수 있습니다.',
            );
        }

        $threads = eottae_message_thread_list($mb_id, 20);
        $unread_count = 0;
        foreach ($threads as $thread) {
            if (!empty($thread['is_unread'])) {
                $unread_count++;
            }
        }

        $latest = !empty($threads[0]) ? $threads[0] : null;
        if ($unread_count > 0) {
            $summary = '읽지 않은 쪽지 '.number_format($unread_count).'건이 있습니다.';
        } elseif ($latest) {
            $name = trim((string) ($latest['other_label'] ?? ''));
            $summary = ($name !== '' ? $name.'님과의 ' : '').'최근 대화를 이어갈 수 있습니다.';
        } else {
            $summary = '업체 문의, 골프조인, 제보 답변을 쪽지로 확인하세요.';
        }

        return array(
            'unread_count' => $unread_count,
            'thread_count' => count($threads),
            'latest'       => $latest,
            'summary_line' => $summary,
        );
    }
}

if (!function_exists('eottae_message_archive_thread')) {
    function eottae_message_archive_thread($thread_id, $mb_id)
    {
        $tables = eottae_message_table_names();
        $thread = eottae_message_get_thread($thread_id, $mb_id);
        if (empty($thread['thread_id'])) {
            return false;
        }

        $mb_id = eottae_message_sanitize_member_id($mb_id);
        $col = $mb_id === (string) $thread['participant_a'] ? 'a_archived' : 'b_archived';
        sql_query("
            UPDATE `{$tables['threads']}` SET {$col} = 1
            WHERE thread_id = '".(int) $thread['thread_id']."'
        ", false);

        return true;
    }
}
