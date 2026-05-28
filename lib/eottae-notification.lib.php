<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_mypage_activity_board_defs')) {
    function eottae_mypage_activity_board_defs()
    {
        return array(
            array('label' => '생활정보', 'bo_table' => defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community'),
            array('label' => '자유게시판', 'bo_table' => function_exists('eottae_free_board_table') ? eottae_free_board_table() : 'free'),
            array('label' => '업체리뷰', 'bo_table' => defined('EOTTae_REVIEW_TABLE') ? EOTTae_REVIEW_TABLE : 'review'),
            array('label' => '이벤트/프로모션', 'bo_table' => defined('EOTTae_EVENT_TABLE') ? EOTTae_EVENT_TABLE : 'event'),
            array('label' => '중고장터', 'bo_table' => defined('EOTTae_MARKET_TABLE') ? EOTTae_MARKET_TABLE : 'market'),
            array('label' => '부동산', 'bo_table' => defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate'),
            array('label' => '구인구직', 'bo_table' => defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job'),
        );
    }
}

if (!function_exists('eottae_mypage_write_table_exists')) {
    function eottae_mypage_write_table_exists($bo_table)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return false;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $row = sql_fetch(" SHOW TABLES LIKE '".sql_escape_string($write_table)."' ", false);

        return !empty($row);
    }
}

if (!function_exists('eottae_mypage_my_comment_summary')) {
    function eottae_mypage_my_comment_summary($mb_id)
    {
        global $g5;

        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return array('count' => 0, 'latest' => null);
        }

        $mb_sql = sql_escape_string($mb_id);
        $since = date('Y-m-d H:i:s', G5_SERVER_TIME - 30 * 86400);
        $total = 0;
        $latest = null;

        foreach (eottae_mypage_activity_board_defs() as $board) {
            $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) ($board['bo_table'] ?? ''));
            if ($bo_table === '' || !eottae_mypage_write_table_exists($bo_table)) {
                continue;
            }

            $write_table = $g5['write_prefix'].$bo_table;
            $count_row = sql_fetch("
                SELECT COUNT(*) AS cnt
                FROM `{$write_table}` c
                INNER JOIN `{$write_table}` p ON p.wr_id = c.wr_parent AND p.wr_is_comment = 0
                WHERE c.wr_is_comment = 1
                  AND p.mb_id = '{$mb_sql}'
                  AND c.mb_id <> '{$mb_sql}'
                  AND c.wr_datetime >= '".sql_escape_string($since)."'
            ", false);
            $total += (int) ($count_row['cnt'] ?? 0);

            $row = sql_fetch("
                SELECT c.wr_id AS comment_id, c.wr_parent, c.wr_content, c.wr_name, c.wr_datetime, p.wr_subject
                FROM `{$write_table}` c
                INNER JOIN `{$write_table}` p ON p.wr_id = c.wr_parent AND p.wr_is_comment = 0
                WHERE c.wr_is_comment = 1
                  AND p.mb_id = '{$mb_sql}'
                  AND c.mb_id <> '{$mb_sql}'
                  AND c.wr_datetime >= '".sql_escape_string($since)."'
                ORDER BY c.wr_datetime DESC, c.wr_id DESC
                LIMIT 1
            ", false);

            if (!empty($row['comment_id']) && (empty($latest) || strcmp((string) $row['wr_datetime'], (string) $latest['datetime']) > 0)) {
                $preview = trim(strip_tags((string) ($row['wr_content'] ?? '')));
                if (function_exists('cut_str')) {
                    $preview = cut_str($preview, 70);
                }
                $latest = array(
                    'board'    => (string) ($board['label'] ?? ''),
                    'title'    => get_text($row['wr_subject'] ?? ''),
                    'author'   => get_text($row['wr_name'] ?? ''),
                    'preview'  => get_text($preview),
                    'datetime' => (string) ($row['wr_datetime'] ?? ''),
                    'href'     => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.(int) $row['wr_parent'].'#c_'.(int) $row['comment_id'],
                );
            }
        }

        return array('count' => $total, 'latest' => $latest);
    }
}

if (!function_exists('eottae_mypage_notification_summary')) {
    function eottae_mypage_notification_summary($mb_id)
    {
        $mb_id = trim((string) $mb_id);
        if ($mb_id === '') {
            return array(
                'total'          => 0,
                'message_unread' => 0,
                'comment_count'  => 0,
                'talk_activity'  => 0,
                'comment_summary'=> array('count' => 0, 'latest' => null),
                'talk_hub'       => array(),
            );
        }

        if (!function_exists('eottae_message_unread_count') && is_file(G5_LIB_PATH.'/eottae-message.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-message.lib.php';
        }
        if (!function_exists('eottae_talkroom_mypage_hub_summary')) {
            if (is_file(G5_LIB_PATH.'/eottae-talkroom.lib.php')) {
                include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
            }
            if (is_file(G5_LIB_PATH.'/eottae-talkroom-dashboard.lib.php')) {
                include_once G5_LIB_PATH.'/eottae-talkroom-dashboard.lib.php';
            }
        }

        $message_unread = function_exists('eottae_message_unread_count') ? (int) eottae_message_unread_count($mb_id) : 0;
        $comment_summary = eottae_mypage_my_comment_summary($mb_id);
        $comment_count = (int) ($comment_summary['count'] ?? 0);
        $talk_hub = function_exists('eottae_talkroom_mypage_hub_summary')
            ? eottae_talkroom_mypage_hub_summary($mb_id)
            : array();
        $talk_activity = (int) ($talk_hub['new_posts'] ?? 0)
            + (int) ($talk_hub['new_comments'] ?? 0)
            + (int) ($talk_hub['notifications'] ?? 0)
            + (int) ($talk_hub['owner_tasks'] ?? 0);

        return array(
            'total'          => $message_unread + $comment_count + $talk_activity,
            'message_unread' => $message_unread,
            'comment_count'  => $comment_count,
            'talk_activity'  => $talk_activity,
            'comment_summary'=> $comment_summary,
            'talk_hub'       => $talk_hub,
        );
    }
}
