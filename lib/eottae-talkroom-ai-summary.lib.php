<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_summary_min_activity_count')) {
    function eottae_talkroom_ai_summary_min_activity_count()
    {
        if (!function_exists('g5site_cfg') && defined('G5_PATH') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }

        $count = function_exists('g5site_cfg')
            ? (int) g5site_cfg('talkroom_ai_summary_min_activity', 5)
            : 5;

        return max(1, min(50, $count));
    }
}

if (!function_exists('eottae_talkroom_ai_summary_window_times')) {
    /**
     * @return array{start:string, end:string}
     */
    function eottae_talkroom_ai_summary_window_times()
    {
        if (!function_exists('g5site_cfg') && defined('G5_PATH') && is_file(G5_PATH.'/_site.config.php')) {
            include_once G5_PATH.'/_site.config.php';
        }

        $start = function_exists('g5site_cfg')
            ? trim((string) g5site_cfg('talkroom_ai_summary_start_time', '21:00:00'))
            : '21:00:00';
        $end = function_exists('g5site_cfg')
            ? trim((string) g5site_cfg('talkroom_ai_summary_end_time', '23:00:00'))
            : '23:00:00';

        return array(
            'start' => eottae_talkroom_ai_normalize_time($start, '21:00:00'),
            'end'   => eottae_talkroom_ai_normalize_time($end, '23:00:00'),
        );
    }
}

if (!function_exists('eottae_talkroom_ai_is_within_summary_window')) {
    function eottae_talkroom_ai_is_within_summary_window($now = null)
    {
        $now = $now ?: G5_TIME_YMDHIS;
        $current = date('H:i:s', strtotime($now));
        $window = eottae_talkroom_ai_summary_window_times();
        $start = $window['start'];
        $end = $window['end'];

        if ($start <= $end) {
            return $current >= $start && $current <= $end;
        }

        return $current >= $start || $current <= $end;
    }
}

if (!function_exists('eottae_talkroom_ai_redact_summary_text')) {
    function eottae_talkroom_ai_redact_summary_text($text)
    {
        $text = strip_tags((string) $text);
        $text = preg_replace('/[\w.-]+@[\w.-]+\.[A-Za-z]{2,}/u', '[연락처]', $text);
        $text = preg_replace('/(\+?\d{1,3}[\s-]?)?(\d{2,4}[\s-]?){2,3}\d{2,4}/u', '[연락처]', $text);
        $text = preg_replace('/(카카오|카톡|kakao|kakaotalk)[^\s]*/ui', '[연락처]', $text);
        $text = preg_replace('/@[a-z0-9._-]{3,}/ui', '[연락처]', $text);
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }
}

if (!function_exists('eottae_talkroom_ai_is_ai_activity_row')) {
    function eottae_talkroom_ai_is_ai_activity_row(array $row)
    {
        if (function_exists('eottae_talkroom_ai_is_ai_write_row') && eottae_talkroom_ai_is_ai_write_row($row)) {
            return true;
        }

        $wr3 = trim((string) ($row['wr_3'] ?? ''));

        return strpos($wr3, 'ai:') === 0;
    }
}

if (!function_exists('eottae_talkroom_ai_fetch_room_activity_for_date')) {
    /**
     * @return array{posts:array, comments:array, total:int, post_count:int, comment_count:int}
     */
    function eottae_talkroom_ai_fetch_room_activity_for_date($room_id, $target_date = null)
    {
        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $target_date = $target_date ?: G5_TIME_YMD;
        $write_table = eottae_talkroom_write_table();
        $empty = array(
            'posts'          => array(),
            'comments'       => array(),
            'total'          => 0,
            'post_count'     => 0,
            'comment_count'  => 0,
        );

        if ($room_id < 1 || $write_table === '') {
            return $empty;
        }

        $start = sql_escape_string($target_date.' 00:00:00');
        $end = sql_escape_string($target_date.' 23:59:59');
        $visible = eottae_talkroom_post_visible_sql();
        $visible_c = eottae_talkroom_post_visible_sql('c');
        $visible_p = eottae_talkroom_post_visible_sql('p');
        $bot_id = sql_escape_string(eottae_talkroom_ai_bot_mb_id());

        $posts = array();
        $result = sql_query("
            SELECT wr_id, wr_subject, wr_comment, wr_datetime, mb_id, wr_3, ca_name
            FROM `{$write_table}`
            WHERE wr_is_comment = 0
              AND wr_1 = '{$room_id}'
              AND {$visible}
              AND wr_datetime >= '{$start}'
              AND wr_datetime <= '{$end}'
              AND wr_3 NOT LIKE 'ai:%'
              AND mb_id != '{$bot_id}'
            ORDER BY wr_id DESC
        ", false);

        if ($result) {
            while ($row = sql_fetch_array($result)) {
                if (eottae_talkroom_ai_is_ai_activity_row($row)) {
                    continue;
                }
                $posts[] = array(
                    'wr_id'         => (int) $row['wr_id'],
                    'subject'       => eottae_talkroom_ai_redact_summary_text($row['wr_subject'] ?? ''),
                    'comment_total' => (int) ($row['wr_comment'] ?? 0),
                    'datetime'      => (string) ($row['wr_datetime'] ?? ''),
                    'ca_name'       => get_text($row['ca_name'] ?? ''),
                );
            }
        }

        $comments = array();
        $result = sql_query("
            SELECT c.wr_id, c.wr_parent, c.wr_content, c.wr_datetime, c.mb_id, c.wr_3,
                   p.wr_subject AS parent_subject
            FROM `{$write_table}` c
            INNER JOIN `{$write_table}` p
                ON c.wr_parent = p.wr_id
            WHERE p.wr_1 = '{$room_id}'
              AND c.wr_is_comment = 1
              AND p.wr_is_comment = 0
              AND {$visible_c}
              AND {$visible_p}
              AND c.wr_datetime >= '{$start}'
              AND c.wr_datetime <= '{$end}'
              AND c.wr_3 NOT LIKE 'ai:%'
              AND c.mb_id != '{$bot_id}'
            ORDER BY c.wr_id DESC
        ", false);

        if ($result) {
            while ($row = sql_fetch_array($result)) {
                if (eottae_talkroom_ai_is_ai_activity_row($row)) {
                    continue;
                }
                $comments[] = array(
                    'wr_id'          => (int) $row['wr_id'],
                    'parent_wr_id'   => (int) $row['wr_parent'],
                    'parent_subject' => eottae_talkroom_ai_redact_summary_text($row['parent_subject'] ?? ''),
                    'snippet'        => eottae_talkroom_ai_redact_summary_text(cut_str(strip_tags($row['wr_content'] ?? ''), 40, '…')),
                    'datetime'       => (string) ($row['wr_datetime'] ?? ''),
                );
            }
        }

        return array(
            'posts'         => $posts,
            'comments'      => $comments,
            'total'         => count($posts) + count($comments),
            'post_count'    => count($posts),
            'comment_count' => count($comments),
        );
    }
}

if (!function_exists('eottae_talkroom_ai_summary_category_key')) {
    function eottae_talkroom_ai_summary_category_key(array $room)
    {
        $category = trim((string) ($room['category'] ?? ''));
        if (in_array($category, array('kids', 'study'), true)) {
            return 'parenting';
        }
        if ($category === 'expat_life' || $category === 'life') {
            return 'expat_life';
        }

        return $category !== '' ? $category : 'etc';
    }
}

if (!function_exists('eottae_talkroom_ai_summary_intro_line')) {
    function eottae_talkroom_ai_summary_intro_line(array $room)
    {
        $room_name = get_text($room['room_name'] ?? '톡방');
        $key = eottae_talkroom_ai_summary_category_key($room);
        $map = array(
            'sports'     => '오늘 '.$room_name.' 요약입니다 ⚽',
            'parenting'  => '오늘 '.$room_name.' 요약입니다 😊',
            'business'   => '오늘 '.$room_name.' 요약입니다.',
            'travel'     => '오늘 '.$room_name.' 요약입니다 ✈️',
            'food'       => '오늘 '.$room_name.' 요약입니다 🍽',
            'hobby'      => '오늘 '.$room_name.' 요약입니다 🎉',
            'expat_life' => '오늘 '.$room_name.' 요약입니다 😊',
            'used'       => '오늘 '.$room_name.' 요약입니다.',
            'etc'        => '오늘 '.$room_name.' 요약입니다.',
        );

        return isset($map[$key]) ? $map[$key] : $map['etc'];
    }
}

if (!function_exists('eottae_talkroom_ai_build_summary_topics')) {
    /**
     * @param array<int, array<string, mixed>> $posts
     * @param array<int, array<string, mixed>> $comments
     * @return array<int, array<string, mixed>>
     */
    function eottae_talkroom_ai_build_summary_topics(array $posts, array $comments)
    {
        $topics = array();

        foreach ($posts as $post) {
            $wr_id = (int) $post['wr_id'];
            $subject = trim((string) ($post['subject'] ?? ''));
            if ($subject === '' || strpos($subject, '[어때봇]') === 0) {
                continue;
            }
            $topics[$wr_id] = array(
                'subject'         => $subject,
                'comments_today'  => 0,
                'is_post_today'   => 1,
            );
        }

        foreach ($comments as $comment) {
            $parent_id = (int) ($comment['parent_wr_id'] ?? 0);
            if ($parent_id < 1) {
                continue;
            }
            if (!isset($topics[$parent_id])) {
                $subject = trim((string) ($comment['parent_subject'] ?? ''));
                if ($subject === '') {
                    continue;
                }
                $topics[$parent_id] = array(
                    'subject'        => $subject,
                    'comments_today' => 0,
                    'is_post_today'  => 0,
                );
            }
            $topics[$parent_id]['comments_today']++;
        }

        $list = array_values($topics);
        usort($list, function ($a, $b) {
            $score_a = ((int) $a['comments_today'] * 10) + ((int) $a['is_post_today'] * 5);
            $score_b = ((int) $b['comments_today'] * 10) + ((int) $b['is_post_today'] * 5);
            if ($score_a === $score_b) {
                return 0;
            }

            return ($score_a > $score_b) ? -1 : 1;
        });

        return array_slice($list, 0, 5);
    }
}

if (!function_exists('eottae_talkroom_ai_format_summary_topic_line')) {
    function eottae_talkroom_ai_format_summary_topic_line(array $topic, $index)
    {
        $subject = trim((string) ($topic['subject'] ?? ''));
        if ($subject === '') {
            return '';
        }

        $subject = cut_str($subject, 60, '…');
        $comments_today = (int) ($topic['comments_today'] ?? 0);

        if ($comments_today >= 3) {
            return $index.'. '.$subject.' 관련 댓글이 '.$comments_today.'개 정도 올라왔습니다.';
        }
        if ($comments_today >= 1) {
            return $index.'. '.$subject.' 관련 이야기가 있었습니다.';
        }

        return $index.'. '.$subject.' 글이 올라왔습니다.';
    }
}

if (!function_exists('eottae_talkroom_ai_generate_room_summary_via_api')) {
    /**
     * @return array{subject:string, content:string, prompt_text:string}|null
     */
    function eottae_talkroom_ai_generate_room_summary_via_api($room_id, array $posts, array $comments, array $room, array $settings)
    {
        return null;
    }
}

if (!function_exists('eottae_talkroom_ai_generate_room_summary_from_template')) {
    /**
     * @return array{subject:string, content:string, prompt_text:string}
     */
    function eottae_talkroom_ai_generate_room_summary_from_template($room_id, array $posts, array $comments, array $room, array $settings, array $activity = array())
    {
        $topics = eottae_talkroom_ai_build_summary_topics($posts, $comments);
        $lines = array(eottae_talkroom_ai_summary_intro_line($room));
        $index = 1;

        foreach ($topics as $topic) {
            if ($index > 3) {
                break;
            }
            $line = eottae_talkroom_ai_format_summary_topic_line($topic, $index);
            if ($line !== '') {
                $lines[] = $line;
                $index++;
            }
        }

        if ($index === 1) {
            $post_count = (int) ($activity['post_count'] ?? count($posts));
            $comment_count = (int) ($activity['comment_count'] ?? count($comments));
            $lines[] = '1. 오늘 게시글 '.$post_count.'건, 댓글 '.$comment_count.'건이 올라왔습니다.';
            $index = 2;
        }

        if ($index <= 3) {
            $lines[] = '오랜만에 들어오셨다면 위 내용을 참고해 주세요.';
        }

        $content = implode("\n", $lines);
        $content = cut_str($content, 600, '…');

        $ai_name = trim((string) ($settings['ai_name'] ?? '어때봇'));
        if ($ai_name === '') {
            $ai_name = '어때봇';
        }

        return array(
            'subject'     => '['.$ai_name.'] 오늘의 톡방 요약',
            'content'     => $content,
            'prompt_text' => 'template:'.eottae_talkroom_ai_summary_category_key($room).'|topics:'.count($topics),
        );
    }
}

if (!function_exists('eottae_talkroom_ai_generate_room_summary')) {
    /**
     * @return array{subject:string, content:string, prompt_text:string}
     */
    function eottae_talkroom_ai_generate_room_summary($room_id, array $posts, array $comments, array $room = array(), array $settings = array(), array $activity = array())
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        if (empty($room)) {
            $room = eottae_talkroom_get_operating_room($room_id);
        }
        if (!$room) {
            $room = array('room_name' => '톡방', 'category' => 'etc');
        }
        if (empty($settings)) {
            $settings = eottae_talkroom_ai_get_settings($room_id);
        }

        $api = eottae_talkroom_ai_generate_room_summary_via_api($room_id, $posts, $comments, $room, $settings);
        if (is_array($api) && !empty($api['content'])) {
            $ai_name = trim((string) ($settings['ai_name'] ?? '어때봇'));
            if ($ai_name === '') {
                $ai_name = '어때봇';
            }

            return array(
                'subject'     => !empty($api['subject']) ? (string) $api['subject'] : '['.$ai_name.'] 오늘의 톡방 요약',
                'content'     => eottae_talkroom_ai_redact_summary_text($api['content']),
                'prompt_text' => (string) ($api['prompt_text'] ?? 'api'),
            );
        }

        return eottae_talkroom_ai_generate_room_summary_from_template($room_id, $posts, $comments, $room, $settings, $activity);
    }
}

if (!function_exists('eottae_talkroom_ai_evaluate_room_summary')) {
    /**
     * @return array{ok:bool, reason:string}
     */
    function eottae_talkroom_ai_evaluate_room_summary($room_id, $now = null, array $options = array())
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $now = $now ?: G5_TIME_YMDHIS;
        $target_date = substr($now, 0, 10);
        $force = !empty($options['force']);
        $is_test = !empty($options['is_test']);
        $bypass = $force && $is_test;

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array('ok' => false, 'reason' => 'not_operating');
        }

        $settings = eottae_talkroom_ai_get_settings($room_id);
        if (!$bypass) {
            $shared = eottae_talkroom_ai_evaluate_shared_limits($room_id, $now, array(
                'force'   => $force,
                'is_test' => $is_test,
            ));
            if (empty($shared['ok'])) {
                return array('ok' => false, 'reason' => $shared['reason']);
            }

            if (empty($settings['summary_enabled'])) {
                return array('ok' => false, 'reason' => 'summary_disabled');
            }
            if (!eottae_talkroom_ai_is_within_summary_window($now)) {
                return array('ok' => false, 'reason' => 'outside_summary_window');
            }

            if (eottae_talkroom_ai_has_success_log_on_date($room_id, 'summary', $target_date)) {
                return array('ok' => false, 'reason' => 'already_summarized_today');
            }

            $activity = eottae_talkroom_ai_fetch_room_activity_for_date($room_id, $target_date);
            if ((int) $activity['total'] < eottae_talkroom_ai_summary_min_activity_count()) {
                return array('ok' => false, 'reason' => 'insufficient_activity');
            }
        }

        return array('ok' => true, 'reason' => 'eligible');
    }
}

if (!function_exists('eottae_talkroom_ai_list_summary_candidate_room_ids')) {
    /**
     * @return int[]
     */
    function eottae_talkroom_ai_list_summary_candidate_room_ids($limit = 200)
    {
        if (!function_exists('eottae_talkroom_table_names')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $tables = eottae_talkroom_table_names();
        $ai_tables = eottae_talkroom_ai_table_names();
        if (!eottae_talkroom_table_exists($tables['rooms']) || !eottae_talkroom_ai_table_exists($ai_tables['settings'])) {
            return array();
        }

        $limit = max(1, min(500, (int) $limit));
        $result = sql_query("
            SELECT r.room_id
            FROM `{$tables['rooms']}` r
            INNER JOIN `{$ai_tables['settings']}` s ON s.room_id = r.room_id
            WHERE r.status IN ('approved', 'active')
              AND s.ai_enabled = 1
              AND s.summary_enabled = 1
            ORDER BY r.room_id ASC
            LIMIT {$limit}
        ", false);

        $ids = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $ids[] = (int) $row['room_id'];
            }
        }

        return $ids;
    }
}

if (!function_exists('eottae_talkroom_ai_run_summary_trigger')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_run_summary_trigger($room_id, $dry_run = false, $now = null, array $options = array())
    {
        if (!function_exists('eottae_talkroom_get_operating_room')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $room_id = (int) $room_id;
        $now = $now ?: G5_TIME_YMDHIS;
        $target_date = substr($now, 0, 10);
        $is_test = !empty($options['is_test']);
        $trigger_type = $is_test ? 'admin_test' : 'summary';
        $ca_name = $is_test ? 'AI·요약(테스트)' : 'AI·요약';

        $room = eottae_talkroom_get_operating_room($room_id);
        if (!$room) {
            return array(
                'room_id' => $room_id,
                'status'  => 'skipped',
                'reason'  => 'not_operating',
                'message' => '운영 중인 톡방이 아닙니다.',
            );
        }

        $settings = eottae_talkroom_ai_get_settings($room_id);
        $check = eottae_talkroom_ai_evaluate_room_summary($room_id, $now, $options);
        if (empty($check['ok'])) {
            if (!$dry_run) {
                eottae_talkroom_ai_write_log($room_id, $trigger_type, array(
                    'status'        => 'skipped',
                    'error_message' => $check['reason'],
                    'prompt_text'   => $is_test ? 'admin_test_precheck' : 'precheck',
                ));
            }

            return array(
                'room_id'   => $room_id,
                'room_name' => get_text($room['room_name'] ?? ''),
                'status'    => 'skipped',
                'reason'    => $check['reason'],
                'message'   => '조건 미충족: '.$check['reason'],
            );
        }

        $activity = eottae_talkroom_ai_fetch_room_activity_for_date($room_id, $target_date);
        $generated = eottae_talkroom_ai_generate_room_summary(
            $room_id,
            $activity['posts'],
            $activity['comments'],
            $room,
            $settings,
            $activity
        );

        if ($dry_run) {
            return array(
                'room_id'   => $room_id,
                'room_name' => get_text($room['room_name'] ?? ''),
                'status'    => 'dry_run',
                'reason'    => 'eligible',
                'subject'   => $generated['subject'],
                'content'   => $generated['content'],
                'activity'  => $activity['total'],
                'message'   => 'dry-run: 게시글 등록 생략',
            );
        }

        $insert = eottae_talkroom_ai_insert_post(
            $room_id,
            $generated['subject'],
            $generated['content'],
            array(
                'ai_name'      => $settings['ai_name'] ?? '어때봇',
                'trigger_type' => $trigger_type,
                'ca_name'      => $ca_name,
            )
        );

        if (empty($insert['ok'])) {
            eottae_talkroom_ai_write_log($room_id, $trigger_type, array(
                'status'        => 'failed',
                'prompt_text'   => $generated['prompt_text'],
                'response_text' => $generated['content'],
                'error_message' => $insert['message'],
            ));

            return array(
                'room_id'   => $room_id,
                'room_name' => get_text($room['room_name'] ?? ''),
                'status'    => 'failed',
                'reason'    => 'insert_failed',
                'message'   => $insert['message'],
            );
        }

        eottae_talkroom_ai_increment_daily_count($room_id, $now, array('is_test' => $is_test));
        eottae_talkroom_ai_write_log($room_id, $trigger_type, array(
            'status'        => 'success',
            'prompt_text'   => $generated['prompt_text'].($is_test ? '|mode:test' : ''),
            'response_text' => $generated['content'],
            'post_id'       => (int) ($insert['wr_id'] ?? 0),
        ));

        return array(
            'room_id'   => $room_id,
            'room_name' => get_text($room['room_name'] ?? ''),
            'status'    => 'success',
            'reason'    => $is_test ? 'test_posted' : 'posted',
            'post_id'   => (int) ($insert['wr_id'] ?? 0),
            'subject'   => $generated['subject'],
            'content'   => $generated['content'],
            'activity'  => $activity['total'],
            'message'   => '오늘의 톡방 요약 게시글 등록 완료',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_run_summary_cron')) {
    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_run_summary_cron(array $options = array())
    {
        eottae_talkroom_ai_ensure_schema();

        $dry_run = !empty($options['dry_run']);
        $room_id = isset($options['room_id']) ? (int) $options['room_id'] : 0;
        $limit = isset($options['limit']) ? (int) $options['limit'] : 200;
        $now = isset($options['now']) ? (string) $options['now'] : G5_TIME_YMDHIS;

        $results = array();
        $summary = array(
            'checked' => 0,
            'posted'  => 0,
            'skipped' => 0,
            'failed'  => 0,
            'dry_run' => $dry_run ? 1 : 0,
        );

        $room_ids = $room_id > 0
            ? array($room_id)
            : eottae_talkroom_ai_list_summary_candidate_room_ids($limit);

        foreach ($room_ids as $id) {
            $summary['checked']++;
            $result = eottae_talkroom_ai_run_summary_trigger($id, $dry_run, $now);
            $results[] = $result;

            if (($result['status'] ?? '') === 'success') {
                $summary['posted']++;
            } elseif (($result['status'] ?? '') === 'failed') {
                $summary['failed']++;
            } else {
                $summary['skipped']++;
            }
        }

        return array(
            'summary' => $summary,
            'results' => $results,
        );
    }
}
