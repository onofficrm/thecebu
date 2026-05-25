<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_plaza_ai_settings_table')) {
    function eottae_plaza_ai_settings_table()
    {
        global $g5;
        if (!isset($g5['sebu_plaza_ai_settings_table'])) {
            $g5['sebu_plaza_ai_settings_table'] = G5_TABLE_PREFIX.'sebu_plaza_ai_settings';
        }

        return $g5['sebu_plaza_ai_settings_table'];
    }
}

if (!function_exists('eottae_plaza_ai_logs_table')) {
    function eottae_plaza_ai_logs_table()
    {
        global $g5;
        if (!isset($g5['sebu_plaza_ai_logs_table'])) {
            $g5['sebu_plaza_ai_logs_table'] = G5_TABLE_PREFIX.'sebu_plaza_ai_logs';
        }

        return $g5['sebu_plaza_ai_logs_table'];
    }
}

if (!function_exists('eottae_plaza_ai_ensure_schema')) {
    function eottae_plaza_ai_ensure_schema()
    {
        if (!function_exists('eottae_talkroom_table_exists')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $settings_table = eottae_plaza_ai_settings_table();
        $logs_table = eottae_plaza_ai_logs_table();
        $ok = true;

        if (!eottae_talkroom_table_exists($settings_table)) {
            $ok = $ok && (bool) sql_query("
                CREATE TABLE IF NOT EXISTS `{$settings_table}` (
                    `id` tinyint(3) unsigned NOT NULL DEFAULT '1',
                    `daily_question_enabled` tinyint(1) NOT NULL DEFAULT '1',
                    `ai_name` varchar(50) NOT NULL DEFAULT '어때봇',
                    `updated_by` varchar(20) NOT NULL DEFAULT '',
                    `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ", false);
        }

        if (!eottae_talkroom_table_exists($logs_table)) {
            $ok = $ok && (bool) sql_query("
                CREATE TABLE IF NOT EXISTS `{$logs_table}` (
                    `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `trigger_type` varchar(30) NOT NULL DEFAULT '',
                    `message` varchar(500) NOT NULL DEFAULT '',
                    `post_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `status` varchar(20) NOT NULL DEFAULT '',
                    `error_message` varchar(500) NOT NULL DEFAULT '',
                    `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`log_id`),
                    KEY `idx_plaza_ai_log_trigger` (`trigger_type`, `created_at`),
                    KEY `idx_plaza_ai_log_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ", false);
        }

        $row = sql_fetch(" SELECT id FROM `{$settings_table}` WHERE id = 1 LIMIT 1 ", false);
        if (empty($row['id'])) {
            sql_query("
                INSERT INTO `{$settings_table}` SET
                    id = 1,
                    daily_question_enabled = 1,
                    ai_name = '어때봇',
                    updated_at = '".G5_TIME_YMDHIS."'
            ", false);
        }

        return array('ok' => $ok);
    }
}

if (!function_exists('eottae_plaza_ai_default_settings')) {
    function eottae_plaza_ai_default_settings()
    {
        return array(
            'daily_question_enabled' => 1,
            'ai_name'                => '어때봇',
        );
    }
}

if (!function_exists('eottae_plaza_ai_get_settings')) {
    function eottae_plaza_ai_get_settings()
    {
        eottae_plaza_ai_ensure_schema();
        $table = eottae_plaza_ai_settings_table();
        $defaults = eottae_plaza_ai_default_settings();
        $row = sql_fetch(" SELECT * FROM `{$table}` WHERE id = 1 LIMIT 1 ", false);
        if (empty($row)) {
            return $defaults;
        }

        return array(
            'daily_question_enabled' => (int) !empty($row['daily_question_enabled']),
            'ai_name'                => trim((string) ($row['ai_name'] ?? $defaults['ai_name'])) ?: $defaults['ai_name'],
            'updated_by'             => trim((string) ($row['updated_by'] ?? '')),
            'updated_at'             => trim((string) ($row['updated_at'] ?? '')),
        );
    }
}

if (!function_exists('eottae_plaza_ai_save_settings')) {
    function eottae_plaza_ai_save_settings(array $data, $admin_mb_id = '')
    {
        global $is_admin;

        if ($is_admin !== 'super') {
            return array('ok' => false, 'message' => '권한이 없습니다.');
        }

        eottae_plaza_ai_ensure_schema();
        $table = eottae_plaza_ai_settings_table();
        $defaults = eottae_plaza_ai_default_settings();
        $ai_name = trim(strip_tags((string) ($data['ai_name'] ?? $defaults['ai_name'])));
        if ($ai_name === '') {
            $ai_name = $defaults['ai_name'];
        }
        if (mb_strlen($ai_name, 'UTF-8') > 50) {
            $ai_name = mb_substr($ai_name, 0, 50, 'UTF-8');
        }

        $enabled = !empty($data['daily_question_enabled']) ? 1 : 0;
        $admin_mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $admin_mb_id);
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');

        $ok = (bool) sql_query("
            INSERT INTO `{$table}` SET
                id = 1,
                daily_question_enabled = '{$enabled}',
                ai_name = '".sql_escape_string($ai_name)."',
                updated_by = '".sql_escape_string($admin_mb_id)."',
                updated_at = '{$now}'
            ON DUPLICATE KEY UPDATE
                daily_question_enabled = '{$enabled}',
                ai_name = '".sql_escape_string($ai_name)."',
                updated_by = '".sql_escape_string($admin_mb_id)."',
                updated_at = '{$now}'
        ", false);

        return array(
            'ok'      => $ok,
            'message' => $ok ? 'AI 설정을 저장했습니다.' : 'AI 설정 저장에 실패했습니다.',
        );
    }
}

if (!function_exists('eottae_plaza_ai_bot_mb_id')) {
    function eottae_plaza_ai_bot_mb_id()
    {
        if (function_exists('eottae_talkroom_ai_bot_mb_id')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';

            return eottae_talkroom_ai_bot_mb_id();
        }

        if (function_exists('g5site_cfg')) {
            return trim((string) g5site_cfg('talkroom_ai_bot_mb_id', 'sebu_ai'));
        }

        return 'sebu_ai';
    }
}

if (!function_exists('eottae_plaza_ai_is_ai_write_row')) {
    function eottae_plaza_ai_is_ai_write_row($write_row)
    {
        if (!is_array($write_row)) {
            return false;
        }

        $bot_id = eottae_plaza_ai_bot_mb_id();
        if ($bot_id !== '' && ($write_row['mb_id'] ?? '') === $bot_id) {
            return true;
        }

        if (trim((string) ($write_row['ca_name'] ?? '')) === 'AI질문') {
            return true;
        }

        $marker = trim((string) ($write_row['wr_3'] ?? ''));

        return strpos($marker, 'ai:') === 0;
    }
}

if (!function_exists('eottae_plaza_ai_daily_question_templates')) {
    /**
     * @return string[]
     */
    function eottae_plaza_ai_daily_question_templates()
    {
        return array(
            "오늘 세부에서 가장 궁금한 것 하나만 남겨보세요 😊\n여행, 맛집, 병원, 렌트, 모임 뭐든 좋습니다.",
            "세부 살면서 이건 참 좋다 싶은 것 있으세요?\n교민분들의 진짜 생활 이야기를 나눠주세요.",
            "이번 주말 세부에서 뭐 하실 예정인가요?\n집콕, 맛집, 바다, 운동, 쇼핑 중에 골라보세요.",
            "세부 처음 오시는 분들이 제일 궁금한 건 무엇인가요?\n공항픽업, 환전, 마사지, 호핑투어, 숙소 위치 중에서 댓글로 남겨주세요.",
        );
    }
}

if (!function_exists('eottae_plaza_ai_generate_daily_question_via_api')) {
    /**
     * @return array{subject:string, content:string}|null
     */
    function eottae_plaza_ai_generate_daily_question_via_api(array $settings)
    {
        return null;
    }
}

if (!function_exists('eottae_plaza_ai_generate_daily_question_from_template')) {
    /**
     * @return array{subject:string, content:string, prompt_text:string}
     */
    function eottae_plaza_ai_generate_daily_question_from_template(array $settings)
    {
        $templates = eottae_plaza_ai_daily_question_templates();
        $content = $templates[array_rand($templates)];
        $ai_name = trim((string) ($settings['ai_name'] ?? '어때봇'));
        if ($ai_name === '') {
            $ai_name = '어때봇';
        }

        return array(
            'subject'     => '['.$ai_name.'] 오늘의 세부 질문',
            'content'     => $content,
            'prompt_text' => 'template:plaza_daily',
        );
    }
}

if (!function_exists('eottae_plaza_ai_generate_daily_question')) {
    function eottae_plaza_ai_generate_daily_question(array $settings)
    {
        $api = eottae_plaza_ai_generate_daily_question_via_api($settings);
        if (is_array($api) && !empty($api['content'])) {
            $ai_name = trim((string) ($settings['ai_name'] ?? '어때봇'));
            if ($ai_name === '') {
                $ai_name = '어때봇';
            }

            return array(
                'subject'     => !empty($api['subject']) ? (string) $api['subject'] : '['.$ai_name.'] 오늘의 세부 질문',
                'content'     => (string) $api['content'],
                'prompt_text' => (string) ($api['prompt_text'] ?? 'api'),
            );
        }

        return eottae_plaza_ai_generate_daily_question_from_template($settings);
    }
}

if (!function_exists('eottae_plaza_ai_write_log')) {
    function eottae_plaza_ai_write_log($trigger_type, array $fields = array())
    {
        eottae_plaza_ai_ensure_schema();
        $table = eottae_plaza_ai_logs_table();
        $trigger_type = preg_replace('/[^a-z0-9_]/', '', (string) $trigger_type);
        $status = trim((string) ($fields['status'] ?? ''));
        $message = function_exists('eottae_talkroom_clean_text')
            ? eottae_talkroom_clean_text($fields['message'] ?? '', 500)
            : cut_str(strip_tags((string) ($fields['message'] ?? '')), 500, '');
        $error_message = function_exists('eottae_talkroom_clean_text')
            ? eottae_talkroom_clean_text($fields['error_message'] ?? '', 500)
            : cut_str(strip_tags((string) ($fields['error_message'] ?? '')), 500, '');
        $post_id = (int) ($fields['post_id'] ?? 0);
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');

        $ok = (bool) sql_query("
            INSERT INTO `{$table}` SET
                trigger_type = '".sql_escape_string($trigger_type)."',
                message = '".sql_escape_string($message)."',
                post_id = '{$post_id}',
                status = '".sql_escape_string($status)."',
                error_message = '".sql_escape_string($error_message)."',
                created_at = '{$now}'
        ", false);

        return array(
            'ok'     => $ok,
            'log_id' => $ok ? (int) sql_insert_id() : 0,
        );
    }
}

if (!function_exists('eottae_plaza_ai_has_success_log_on_date')) {
    function eottae_plaza_ai_has_success_log_on_date($trigger_type, $target_date = null)
    {
        eottae_plaza_ai_ensure_schema();
        $table = eottae_plaza_ai_logs_table();
        $trigger_type = preg_replace('/[^a-z0-9_]/', '', (string) $trigger_type);
        $target_date = $target_date ?: (defined('G5_TIME_YMDHIS') ? substr(G5_TIME_YMDHIS, 0, 10) : date('Y-m-d'));

        $row = sql_fetch("
            SELECT log_id
            FROM `{$table}`
            WHERE trigger_type = '".sql_escape_string($trigger_type)."'
              AND status = 'success'
              AND DATE(created_at) = '".sql_escape_string($target_date)."'
            LIMIT 1
        ", false);

        return !empty($row['log_id']);
    }
}

if (!function_exists('eottae_plaza_ai_evaluate_daily_question')) {
    /**
     * @return array{ok:bool, reason:string}
     */
    function eottae_plaza_ai_evaluate_daily_question(array $options = array())
    {
        $settings = eottae_plaza_ai_get_settings();
        $is_test = !empty($options['is_test']);
        $force = !empty($options['force']);
        $now = isset($options['now']) ? (string) $options['now'] : (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $target_date = substr($now, 0, 10);

        if (empty($settings['daily_question_enabled']) && !$is_test && !$force) {
            return array('ok' => false, 'reason' => 'daily_question_disabled');
        }

        if (!$is_test && !$force && eottae_plaza_ai_has_success_log_on_date('daily_question', $target_date)) {
            return array('ok' => false, 'reason' => 'already_posted_today');
        }

        include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
        if (!function_exists('eottae_plaza_board_table')) {
            return array('ok' => false, 'reason' => 'plaza_board_missing');
        }

        return array('ok' => true, 'reason' => '');
    }
}

if (!function_exists('eottae_plaza_ai_insert_post')) {
    function eottae_plaza_ai_insert_post($subject, $content, array $options = array())
    {
        global $g5;

        include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
        include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';

        $bo_table = eottae_plaza_board_table();
        $write_table = $g5['write_prefix'].$bo_table;
        $exists = sql_fetch(" SHOW TABLES LIKE '".sql_escape_string($write_table)."' ", false);
        if (empty($exists)) {
            return array('ok' => false, 'message' => '세부광장 게시판을 찾을 수 없습니다.');
        }

        $settings = eottae_plaza_ai_get_settings();
        $ai_name = trim((string) ($options['ai_name'] ?? $settings['ai_name'] ?? '어때봇'));
        if ($ai_name === '') {
            $ai_name = '어때봇';
        }

        $bot = eottae_talkroom_ai_ensure_bot_member($ai_name);
        if (empty($bot['ok'])) {
            return array('ok' => false, 'message' => $bot['message']);
        }

        $member = eottae_talkroom_ai_get_bot_member();
        if (!$member) {
            return array('ok' => false, 'message' => 'AI 계정을 찾을 수 없습니다.');
        }

        $trigger_type = trim((string) ($options['trigger_type'] ?? 'daily_question'));
        $subject = strip_tags((string) $subject);
        $content = (string) $content;
        if ($subject === '' || $content === '') {
            return array('ok' => false, 'message' => '제목과 내용이 필요합니다.');
        }

        $subject_sql = sql_escape_string($subject);
        $content_sql = sql_escape_string($content);
        $ca_name_sql = sql_escape_string('AI질문');
        $region_sql = sql_escape_string('기타');
        $status_sql = sql_escape_string('visible');
        $mb_id = sql_escape_string($member['mb_id']);
        $wr_name = sql_escape_string($ai_name);
        $wr_email = sql_escape_string($member['mb_email'] ?? ($member['mb_id'].'@ai.local'));
        $wr_3 = sql_escape_string('ai:'.$trigger_type);
        $seo = sql_escape_string(preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($subject)));

        sql_query(" INSERT INTO `{$write_table}` SET
            wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM `{$write_table}` AS sq),
            wr_reply = '',
            wr_comment = 0,
            ca_name = '{$ca_name_sql}',
            wr_option = '',
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
            mb_id = '{$mb_id}',
            wr_password = '',
            wr_name = '{$wr_name}',
            wr_email = '{$wr_email}',
            wr_homepage = '',
            wr_datetime = '".G5_TIME_YMDHIS."',
            wr_last = '".G5_TIME_YMDHIS."',
            wr_ip = '127.0.0.1',
            wr_1 = '{$region_sql}',
            wr_2 = '{$status_sql}',
            wr_3 = '{$wr_3}',
            wr_4 = '',
            wr_5 = '',
            wr_6 = '',
            wr_7 = '',
            wr_8 = '',
            wr_9 = '',
            wr_10 = '' ", false);

        $wr_id = (int) sql_insert_id();
        if ($wr_id < 1) {
            return array('ok' => false, 'message' => '게시글 등록에 실패했습니다.');
        }

        sql_query(" UPDATE `{$write_table}` SET wr_parent = '{$wr_id}' WHERE wr_id = '{$wr_id}' ", false);
        sql_query(" INSERT INTO {$g5['board_new_table']}
            (bo_table, wr_id, wr_parent, bn_datetime, mb_id)
            VALUES ('".sql_escape_string($bo_table)."', '{$wr_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', '{$mb_id}') ", false);
        sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '".sql_escape_string($bo_table)."' ", false);

        if (function_exists('delete_cache_latest')) {
            delete_cache_latest($bo_table);
        }

        return array(
            'ok'      => true,
            'message' => 'AI 질문 글이 등록되었습니다.',
            'wr_id'   => $wr_id,
            'subject' => $subject,
            'content' => $content,
        );
    }
}

if (!function_exists('eottae_plaza_ai_run_daily_question')) {
    /**
     * @param array<string, mixed> $options dry_run, is_test, force, now
     * @return array<string, mixed>
     */
    function eottae_plaza_ai_run_daily_question(array $options = array())
    {
        $dry_run = !empty($options['dry_run']);
        $is_test = !empty($options['is_test']);
        $force = !empty($options['force']);
        $now = isset($options['now']) ? (string) $options['now'] : (defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s'));
        $trigger_type = $is_test ? 'admin_test' : 'daily_question';

        $check = eottae_plaza_ai_evaluate_daily_question(array(
            'is_test' => $is_test,
            'force'   => $force,
            'now'     => $now,
        ));

        if (empty($check['ok'])) {
            if (!$dry_run) {
                eottae_plaza_ai_write_log($trigger_type, array(
                    'status'        => 'skipped',
                    'message'       => '조건 미충족',
                    'error_message' => $check['reason'],
                ));
            }

            return array(
                'status'  => 'skipped',
                'reason'  => $check['reason'],
                'message' => '조건 미충족: '.$check['reason'],
            );
        }

        $settings = eottae_plaza_ai_get_settings();
        $generated = eottae_plaza_ai_generate_daily_question($settings);

        if ($dry_run) {
            return array(
                'status'  => 'dry_run',
                'reason'  => 'eligible',
                'subject' => $generated['subject'],
                'content' => $generated['content'],
                'message' => 'dry-run: 게시글 등록 생략',
            );
        }

        $insert = eottae_plaza_ai_insert_post(
            $generated['subject'],
            $generated['content'],
            array(
                'ai_name'      => $settings['ai_name'],
                'trigger_type' => $trigger_type,
            )
        );

        if (empty($insert['ok'])) {
            eottae_plaza_ai_write_log($trigger_type, array(
                'status'        => 'failed',
                'message'       => $insert['message'],
                'error_message' => $insert['message'],
            ));

            return array(
                'status'  => 'failed',
                'reason'  => 'insert_failed',
                'message' => $insert['message'],
            );
        }

        eottae_plaza_ai_write_log($trigger_type, array(
            'status'  => 'success',
            'message' => $generated['prompt_text'] ?? 'posted',
            'post_id' => (int) ($insert['wr_id'] ?? 0),
        ));

        return array(
            'status'  => 'posted',
            'reason'  => 'success',
            'message' => '오늘의 질문을 등록했습니다.',
            'post_id' => (int) ($insert['wr_id'] ?? 0),
            'subject' => $insert['subject'],
            'content' => $insert['content'],
        );
    }
}

if (!function_exists('eottae_plaza_ai_verify_cron_key')) {
    function eottae_plaza_ai_verify_cron_key($provided_key)
    {
        if (function_exists('eottae_talkroom_ai_verify_cron_key')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
            if (eottae_talkroom_ai_verify_cron_key($provided_key)) {
                return true;
            }
        }

        if (!function_exists('g5site_cfg') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }

        $secret = function_exists('g5site_cfg') ? trim((string) g5site_cfg('plaza_ai_cron_key', '')) : '';
        if ($secret === '' && function_exists('g5site_cfg')) {
            $secret = trim((string) g5site_cfg('talkroom_ai_cron_key', ''));
        }

        if ($secret === '') {
            return php_sapi_name() === 'cli';
        }

        return is_string($provided_key) && hash_equals($secret, (string) $provided_key);
    }
}

if (!function_exists('eottae_plaza_ai_admin_url')) {
    function eottae_plaza_ai_admin_url()
    {
        return G5_URL.'/page/eottae-admin-plaza-ai.php';
    }
}

if (!function_exists('eottae_plaza_ai_admin_list_logs')) {
    function eottae_plaza_ai_admin_list_logs($limit = 50)
    {
        eottae_plaza_ai_ensure_schema();
        $table = eottae_plaza_ai_logs_table();
        $limit = max(1, min(200, (int) $limit));

        $result = sql_query("
            SELECT *
            FROM `{$table}`
            ORDER BY created_at DESC, log_id DESC
            LIMIT {$limit}
        ", false);

        $items = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $items[] = array(
                    'log_id'        => (int) ($row['log_id'] ?? 0),
                    'trigger_type'  => get_text($row['trigger_type'] ?? ''),
                    'message'       => get_text($row['message'] ?? ''),
                    'post_id'       => (int) ($row['post_id'] ?? 0),
                    'status'        => get_text($row['status'] ?? ''),
                    'error_message' => get_text($row['error_message'] ?? ''),
                    'created_at'    => trim((string) ($row['created_at'] ?? '')),
                );
            }
        }

        return $items;
    }
}
