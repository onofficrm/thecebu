<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_sensitive_keyword_groups')) {
    /**
     * 민감 키워드 목록 (카테고리별 관리)
     *
     * @return array<string, string[]>
     */
    function eottae_talkroom_ai_sensitive_keyword_groups()
    {
        return array(
            'profanity' => array(
                '씨발', '시발', 'ㅅㅂ', 'ㅂㅅ', '병신', '지랄', '개새', 'fuck', 'shit', 'damn', 'asshole',
            ),
            'dispute' => array(
                '분쟁', '사기꾼', '사기당', '사기쳤', '고소', '소송', '신고합니다', '신고할', '협박', '비방', '허위사실', '명예훼손',
            ),
            'politics' => array(
                '대통령', '정치', '정치인', '선거', '탄핵', '정당', '여당', '야당', '국민의힘', '민주당',
            ),
            'religion' => array(
                '종교', '종교논쟁', '교회', '성당', '예수', 'allah', '알라', '모슬람', '불교',
            ),
            'fraud' => array(
                '사기', '피싱', '먹튀', '대출사기', '환불거부',
            ),
            'legal' => array(
                '고소', '소송', '경찰신고', '형사고발',
            ),
            'personal' => array(
                '여권', '비자', '이민', '추방', '체포', '구속', '납치', '실종',
            ),
            'privacy' => array(
                '주민번호', '여권번호', '계좌번호', '통장', '카드번호', '비밀번호',
            ),
        );
    }
}

if (!function_exists('eottae_talkroom_ai_sensitive_pattern_groups')) {
    /**
     * @return array<string, string>
     */
    function eottae_talkroom_ai_sensitive_pattern_groups()
    {
        return array(
            'phone_heavy'    => '/(\+?\d{1,3}[\s-]?)?(\d{2,4}[\s-]?){2,3}\d{2,4}/u',
            'account_number' => '/\d{3,4}[\s-]?\d{2,4}[\s-]?\d{4,6}/u',
            'email'          => '/[\w.-]+@[\w.-]+\.[A-Za-z]{2,}/u',
            'passport'       => '/[A-Z]{1,2}\d{6,9}/u',
            'kakao_id'       => '/(카카오|카톡|kakao|kakaotalk|텔레|telegram|whatsapp|라인)[^\s]*/ui',
        );
    }
}

if (!function_exists('eottae_talkroom_ai_detect_sensitive_content')) {
    /**
     * @return array{hit:bool, category:string, reason:string, matched:string}
     */
    function eottae_talkroom_ai_detect_sensitive_content($text)
    {
        $text = trim(strip_tags((string) $text));
        $plain = preg_replace('/\s+/u', ' ', $text);
        $empty = array(
            'hit'     => false,
            'category'=> '',
            'reason'  => '',
            'matched' => '',
        );

        if ($plain === '') {
            return $empty;
        }

        $lower = mb_strtolower($plain, 'UTF-8');

        foreach (eottae_talkroom_ai_sensitive_keyword_groups() as $category => $keywords) {
            foreach ($keywords as $keyword) {
                $keyword = trim((string) $keyword);
                if ($keyword === '') {
                    continue;
                }
                if (mb_stripos($plain, $keyword, 0, 'UTF-8') !== false) {
                    return array(
                        'hit'      => true,
                        'category' => $category,
                        'reason'   => 'sensitive_'.$category,
                        'matched'  => $keyword,
                    );
                }
            }
        }

        $phone_count = 0;
        if (preg_match_all(eottae_talkroom_ai_sensitive_pattern_groups()['phone_heavy'], $plain, $phones)) {
            $phone_count = count($phones[0]);
        }
        if ($phone_count >= 2) {
            return array(
                'hit'      => true,
                'category' => 'phone_heavy',
                'reason'   => 'sensitive_phone_heavy',
                'matched'  => 'phone:'.$phone_count,
            );
        }

        foreach (eottae_talkroom_ai_sensitive_pattern_groups() as $category => $pattern) {
            if ($category === 'phone_heavy') {
                continue;
            }
            if (@preg_match($pattern, $plain, $m)) {
                return array(
                    'hit'      => true,
                    'category' => $category,
                    'reason'   => 'sensitive_'.$category,
                    'matched'  => isset($m[0]) ? (string) $m[0] : $category,
                );
            }
        }

        $pii_score = 0;
        if (preg_match_all(eottae_talkroom_ai_sensitive_pattern_groups()['email'], $plain, $m)) {
            $pii_score += count($m[0]);
        }
        if (preg_match_all(eottae_talkroom_ai_sensitive_pattern_groups()['kakao_id'], $plain, $m)) {
            $pii_score += count($m[0]);
        }
        if ($phone_count > 0) {
            $pii_score += $phone_count;
        }
        if ($pii_score >= 2) {
            return array(
                'hit'      => true,
                'category' => 'pii_heavy',
                'reason'   => 'sensitive_pii_heavy',
                'matched'  => 'pii_score:'.$pii_score,
            );
        }

        return $empty;
    }
}

if (!function_exists('eottae_talkroom_ai_should_skip_reaction_for_content')) {
    function eottae_talkroom_ai_should_skip_reaction_for_content($text)
    {
        $detected = eottae_talkroom_ai_detect_sensitive_content($text);

        return !empty($detected['hit']) ? $detected : null;
    }
}
