<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_ai_guard_sensitive_patterns')) {
    /**
     * @return array<string, array<int, string>>
     */
    function eottae_public_ai_guard_sensitive_patterns()
    {
        return array(
            'politics' => array('정치', '선거', '대통령', '국회', '탄핵', '정당', '여당', '야당'),
            'religion' => array('종교', '교회', '성당', '이슬람', '불교', '선교'),
            'crime'    => array('살인', '강도', '마약', '체포', '구속', '범죄', '사건사고', '총격'),
            'legal'    => array('고소', '소송', '사기', '피해', '사기꾼'),
            'privacy'  => array('여권', '비자', '주민번호', '계좌', '계좌번호', '카드번호', '개인정보'),
            'medical'  => array('진단', '암', '수술', '처방', '치료', '입원'),
            'finance'  => array('투자', '수익', '부동산', '코인', '주식', '대박'),
            'adult'    => array('성인', '유흥', '불법', '마사지', '출장'),
            'abuse'    => array('욕설', '비방', '혐오', '차별'),
        );
    }
}

if (!function_exists('eottae_public_ai_guard_news_blocked_categories')) {
    function eottae_public_ai_guard_news_blocked_categories()
    {
        return array('politics', 'crime', 'religion', 'accident', 'dispute', 'adult');
    }
}

if (!function_exists('eottae_public_ai_guard_news_blocked_keywords')) {
    function eottae_public_ai_guard_news_blocked_keywords()
    {
        return array(
            '정치', '선거', '살인', '강도', '마약', '총격', '테러', '분쟁', '종교',
            '사건', '사고', '체포', '구속', '고소', '사기', '성폭력', '유흥',
        );
    }
}

if (!function_exists('eottae_public_ai_guard_scan_text')) {
    /**
     * @return array{is_sensitive:bool, categories:array<int,string>, matches:array<int,string>}
     */
    function eottae_public_ai_guard_scan_text($text)
    {
        $text = mb_strtolower(trim((string) $text), 'UTF-8');
        if ($text === '') {
            return array('is_sensitive' => false, 'categories' => array(), 'matches' => array());
        }

        $categories = array();
        $matches = array();
        foreach (eottae_public_ai_guard_sensitive_patterns() as $category => $words) {
            foreach ($words as $word) {
                $word = mb_strtolower($word, 'UTF-8');
                if ($word !== '' && mb_strpos($text, $word, 0, 'UTF-8') !== false) {
                    $categories[$category] = $category;
                    $matches[] = $word;
                }
            }
        }

        return array(
            'is_sensitive' => !empty($categories),
            'categories'   => array_values($categories),
            'matches'      => array_values(array_unique($matches)),
        );
    }
}

if (!function_exists('eottae_public_ai_guard_news_is_blocked')) {
    function eottae_public_ai_guard_news_is_blocked(array $news)
    {
        $category = trim((string) ($news['category'] ?? ''));
        if ($category !== '' && in_array($category, eottae_public_ai_guard_news_blocked_categories(), true)) {
            return true;
        }

        if (!empty($news['is_sensitive'])) {
            return true;
        }

        $blob = mb_strtolower(
            trim((string) ($news['title'] ?? '')).' '.trim((string) ($news['summary'] ?? '')),
            'UTF-8'
        );
        foreach (eottae_public_ai_guard_news_blocked_keywords() as $word) {
            if ($word !== '' && mb_strpos($blob, mb_strtolower($word, 'UTF-8'), 0, 'UTF-8') !== false) {
                return true;
            }
        }

        $scan = eottae_public_ai_guard_scan_text($blob);

        return !empty($scan['is_sensitive']);
    }
}

if (!function_exists('eottae_public_ai_guard_apply_to_candidate')) {
    /**
     * @param array<string, mixed> $candidate
     * @return array<string, mixed>
     */
    function eottae_public_ai_guard_apply_to_candidate(array $candidate)
    {
        $message = trim((string) ($candidate['message'] ?? ''));
        $title = trim((string) ($candidate['title'] ?? ''));
        $scan = eottae_public_ai_guard_scan_text($message.' '.$title);

        if (!empty($scan['is_sensitive'])) {
            $candidate['is_sensitive'] = 1;
            $candidate['force_admin_approval'] = 1;
            $memo = trim((string) ($candidate['admin_memo'] ?? ''));
            $candidate['admin_memo'] = $memo !== ''
                ? $memo.'|sensitive:'.implode(',', $scan['categories'])
                : 'sensitive:'.implode(',', $scan['categories']);
        }

        $trigger = trim((string) ($candidate['trigger_type'] ?? ''));
        $source = trim((string) ($candidate['source_type'] ?? ''));
        if ($trigger === 'external_news' || $source === 'external_news') {
            $candidate['force_admin_approval'] = 1;
            $candidate['is_sensitive'] = max((int) ($candidate['is_sensitive'] ?? 0), 1);
        }

        return $candidate;
    }
}

if (!function_exists('eottae_public_ai_guard_can_auto_publish')) {
    function eottae_public_ai_guard_can_auto_publish(array $candidate)
    {
        if (!empty($candidate['force_admin_approval']) || !empty($candidate['is_sensitive'])) {
            return false;
        }

        $trigger = trim((string) ($candidate['trigger_type'] ?? ''));
        $source = trim((string) ($candidate['source_type'] ?? ''));
        if ($trigger === 'external_news' || $source === 'external_news') {
            return false;
        }

        return true;
    }
}

if (!function_exists('eottae_public_ai_public_chat_consecutive_ai_count')) {
    function eottae_public_ai_public_chat_consecutive_ai_count($limit = 5)
    {
        if (!function_exists('eottae_talkroom_public_group_room_id')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-public-chat.lib.php';
        }
        if (!function_exists('eottae_talkroom_ai_is_ai_write_row')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
        }

        $room_id = (int) eottae_talkroom_public_group_room_id();
        if ($room_id < 1) {
            return 0;
        }

        $rows = eottae_talkroom_public_group_list_messages($room_id, max(2, min(10, (int) $limit)));
        $rows = array_reverse($rows);
        $count = 0;
        foreach ($rows as $row) {
            if (eottae_talkroom_ai_is_ai_write_row($row)) {
                $count++;
                continue;
            }
            break;
        }

        return $count;
    }
}

if (!function_exists('eottae_public_ai_public_chat_member_is_active')) {
    function eottae_public_ai_public_chat_member_is_active($minutes = 120, $min_posts = 3)
    {
        if (!function_exists('eottae_talkroom_public_group_room_id')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-public-chat.lib.php';
        }
        if (!function_exists('eottae_talkroom_ai_context_last_member_activity_at')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-context.lib.php';
        }
        if (!function_exists('eottae_talkroom_ai_minutes_since')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai-quiet.lib.php';
        }

        $room_id = (int) eottae_talkroom_public_group_room_id();
        if ($room_id < 1) {
            return false;
        }

        $since = date('Y-m-d H:i:s', strtotime('-'.max(30, (int) $minutes).' minutes'));
        if (!function_exists('eottae_talkroom_write_table')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }

        $write_table = eottae_talkroom_write_table();
        if ($write_table === '') {
            return false;
        }

        $bot_id = function_exists('eottae_talkroom_ai_bot_mb_id')
            ? sql_escape_string(eottae_talkroom_ai_bot_mb_id())
            : 'sebu_ai';
        $visible = eottae_talkroom_post_visible_sql('p');

        $row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM `{$write_table}` p
            WHERE p.wr_is_comment = 0
              AND p.wr_1 = '{$room_id}'
              AND {$visible}
              AND p.wr_3 NOT LIKE 'ai:%'
              AND p.mb_id != '{$bot_id}'
              AND p.wr_datetime >= '".sql_real_escape_string($since)."'
        ", false);

        return (int) ($row['cnt'] ?? 0) >= max(1, (int) $min_posts);
    }
}

if (!function_exists('eottae_public_ai_generate_message_via_openai')) {
    /**
     * @deprecated eottae_public_ai_generate_message() 사용
     */
    function eottae_public_ai_generate_message_via_openai(array $context, array $settings = array())
    {
        if (!function_exists('eottae_public_ai_generate_message')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-openai.lib.php';
        }

        $trigger = trim((string) ($context['trigger_type'] ?? 'admin_manual'));
        $result = eottae_public_ai_generate_message($context, $trigger, null, $settings, array());

        return !empty($result['candidate']) ? $result['candidate'] : null;
    }
}
