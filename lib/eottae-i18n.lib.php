<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_i18n_key_for_text')) {
    function eottae_i18n_key_for_text($text)
    {
        static $map = null;

        if ($map === null) {
            $map = array(
                '홈' => 'menu.home',
                '내주변' => 'menu.nearby',
                '내 주변 맛집' => 'menu.nearby_food',
                '내 주변 업체' => 'menu.nearby_business',
                '내 주변 병원' => 'menu.nearby_hospital',
                '내 주변 생활편의' => 'menu.nearby_convenience',
                '커뮤니티' => 'menu.community',
                '생활정보' => 'menu.life_info',
                '자유게시판' => 'menu.free_board',
                '업체리뷰' => 'menu.business_review',
                '사람찾기' => 'menu.people_finder',
                '이벤트/프로모션' => 'menu.events_promotions',
                '제보함' => 'menu.report_box',
                '생활지도' => 'menu.life_map',
                '전체지도' => 'menu.all_map',
                '구인구직' => 'menu.jobs',
                '중고장터' => 'menu.market',
                '부동산' => 'menu.real_estate',
                '골프조인' => 'menu.golf_join',
                '조인 모집' => 'menu.join_recruit',
                '모집중' => 'menu.recruiting',
                '마감' => 'menu.closed',
                '골프장 정보' => 'menu.golf_course_info',
                '컬럼' => 'menu.column',
                '전체 컬럼' => 'menu.all_columns',
                '컬럼리스트 소개' => 'menu.columnist_intro',
                '컬럼리스트 신청' => 'menu.columnist_apply',
                '미디어' => 'menu.media',
                '갤러리' => 'menu.gallery',
                '유튜브' => 'menu.youtube',
                '광고방' => 'home.adroom',
                'MY' => 'menu.my',
                '내 프로필' => 'menu.my_profile',
                '내가 쓴 글' => 'menu.my_posts',
                '찜한 글' => 'menu.saved_posts',
                '내 업체' => 'menu.my_business',
                '내 신청내역' => 'menu.my_applications',
                '쪽지' => 'menu.messages',
                '로그인' => 'button.login',
                '로그아웃' => 'button.logout',
                '회원가입' => 'button.register',
                '글쓰기' => 'button.write',
                '수정' => 'button.edit',
                '삭제' => 'button.delete',
                '저장' => 'button.save',
                '취소' => 'button.cancel',
                '검색' => 'button.search',
                '댓글' => 'button.comment',
                '좋아요' => 'button.like',
                '찜하기' => 'button.bookmark',
                '업체등록' => 'button.business_register',
                '업소등록' => 'button.shop_register',
                '지도보기' => 'button.view_map',
                '자세히 보기' => 'button.view_detail',
                '더보기' => 'button.more',
                '목록으로' => 'button.back_to_list',
                '등록하기' => 'button.submit',
                '신청하기' => 'button.apply',
                '문의하기' => 'button.inquire',
                '거래중' => 'status.trading',
                '거래완료' => 'status.completed_trade',
                '예약중' => 'status.reserved',
                '완료' => 'status.completed',
                '승인대기' => 'status.pending_approval',
                '승인완료' => 'status.approved',
            );
        }

        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)));
        if (preg_match('/^쪽지\s*\([0-9,]+\)$/u', $text)) {
            return 'menu.messages';
        }

        return isset($map[$text]) ? $map[$text] : '';
    }
}

if (!function_exists('eottae_i18n_attrs')) {
    function eottae_i18n_attrs($key, $attrs = array())
    {
        $key = (string) $key;
        if ($key === '') {
            return '';
        }

        $html = ' data-i18n="'.get_text($key).'"';
        foreach ((array) $attrs as $attr => $attr_key) {
            if ($attr !== '' && $attr_key !== '') {
                $html .= ' data-i18n-'.get_text($attr).'="'.get_text($attr_key).'"';
            }
        }

        return $html;
    }
}

if (!function_exists('eottae_i18n_text_attrs')) {
    function eottae_i18n_text_attrs($text, $attrs = array())
    {
        return eottae_i18n_attrs(eottae_i18n_key_for_text($text), $attrs);
    }
}

if (!function_exists('eottae_i18n_label_html')) {
    function eottae_i18n_label_html($text, $attrs = array(), $class = '')
    {
        $class_attr = $class !== '' ? ' class="'.get_text($class).'"' : '';

        return '<span'.$class_attr.eottae_i18n_text_attrs($text, $attrs).'>'.get_text($text).'</span>';
    }
}

if (!function_exists('eottae_i18n_language_definitions')) {
    function eottae_i18n_language_definitions()
    {
        return array(
            'ko' => array('flag' => '🇰🇷', 'label' => '한국어', 'i18n' => 'language.ko'),
            'ja' => array('flag' => '🇯🇵', 'label' => '日本語', 'i18n' => 'language.ja'),
            'zh' => array('flag' => '🇨🇳', 'label' => '中文', 'i18n' => 'language.zh'),
            'en' => array('flag' => '🇺🇸', 'label' => 'English', 'i18n' => 'language.en'),
        );
    }
}

if (!function_exists('eottae_i18n_language_badge_html')) {
    function eottae_i18n_language_badge_html($class = '')
    {
        return eottae_i18n_language_select_html($class);
    }
}

if (!function_exists('eottae_i18n_language_select_html')) {
    function eottae_i18n_language_select_html($class = '')
    {
        static $select_seq = 0;
        $select_seq += 1;

        $class = trim('eottae-language '.(string) $class);
        $languages = eottae_i18n_language_definitions();
        $select_id = 'eottae-language-select-'.$select_seq;

        ob_start();
        ?>
        <div class="<?php echo get_text($class); ?>" data-eottae-language-control>
            <div class="eottae-language__select-wrap">
                <select
                    id="<?php echo get_text($select_id); ?>"
                    class="eottae-language__select"
                    data-eottae-language-select
                    aria-label="언어 선택"
                    data-i18n-aria-label="language.select_label"
                >
                    <?php foreach ($languages as $code => $meta) { ?>
                    <option value="<?php echo get_text($code); ?>">
                        <?php echo get_text($meta['flag'].' '.$meta['label']); ?>
                    </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <?php
        return trim(ob_get_clean());
    }
}
