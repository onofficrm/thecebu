<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-columnist-recruit.lib.php';
include_once G5_LIB_PATH.'/eottae-column.lib.php';

global $is_member, $member;

$recruit_url = eottae_columnist_recruit_url();
$column_list_url = eottae_column_list_url();
$apply_member_url = eottae_column_apply_url();
$proc_url = eottae_columnist_recruit_proc_url();
$token = eottae_columnist_recruit_token();
$img_base = eottae_columnist_recruit_img_base();
$interests = eottae_columnist_recruit_interest_options();
$submitted = isset($_GET['submitted']) && $_GET['submitted'] === '1';

$is_columnist = $is_member && eottae_column_is_columnist($member['mb_id'] ?? '');
$pending_application = $is_member ? eottae_column_get_latest_application($member['mb_id'] ?? '') : null;
$has_pending = $pending_application && ($pending_application['status'] ?? '') === 'pending';

$pen_name_default = '';
if ($is_member) {
    $pen_name_default = trim((string) ($member['mb_nick'] ?? $member['mb_name'] ?? ''));
}

$page_title = '세부어때 컬럼리스트 모집 | 세부 생활정보를 함께 만들어갈 분을 찾습니다';
$page_description = '세부어때에서 세부의 맛집, 생활정보, 부동산, 교육, 취미, 사업 이야기를 함께 나눌 컬럼리스트를 모집합니다. 직접 경험한 세부 이야기를 나누고 커뮤니티와 함께 성장해보세요.';
$page_keywords = '세부 컬럼리스트, 세부 생활정보, 세부 교민 커뮤니티, 세부 맛집 정보, 세부 부동산 정보, 세부어때 컬럼';
$page_canonical = $recruit_url;

add_stylesheet('<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700;800&display=swap">', 20);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-columnist-recruit.css">', 24);
add_javascript('<script src="'.G5_JS_URL.'/eottae-columnist-recruit.js" defer></script>', 24);

g5_page_start('컬럼리스트 모집');
?>

<main class="columnist-page sebu-columnist-recruit" data-sebu-columnist-recruit>
    <?php if ($submitted) { ?>
    <section class="sebu-columnist-success" aria-live="polite">
        <div class="sebu-columnist-success__icon" aria-hidden="true">🎉</div>
        <h1 class="sebu-columnist-success__title">신청이 접수되었습니다</h1>
        <p>세부어때 컬럼리스트 신청이 접수되었습니다.<br>운영진이 확인 후 연락드리겠습니다.<br>세부의 좋은 정보를 함께 만들어주셔서 감사합니다.</p>
        <div class="sebu-columnist-success__actions">
            <a href="<?php echo G5_URL; ?>" class="sebu-columnist-btn sebu-columnist-btn--primary">홈으로 이동</a>
            <a href="<?php echo $column_list_url; ?>" class="sebu-columnist-btn sebu-columnist-btn--secondary">컬럼 더 보기</a>
        </div>
    </section>
    <?php } else { ?>

    <header class="sebu-columnist-hero sebu-columnist-recruit__section">
        <div class="sebu-columnist-hero__copy">
            <p class="sebu-columnist-hero__badge">컬럼리스트 모집</p>
            <h1 class="sebu-columnist-hero__title">세부의 이야기를 함께 만들어갈 컬럼리스트를 찾습니다</h1>
            <p class="sebu-columnist-hero__lead">맛집, 생활정보, 부동산, 교육, 취미, 사업 이야기까지.<br>당신이 알고 있는 세부 이야기가 누군가에게는 꼭 필요한 정보가 됩니다.</p>
            <div class="sebu-columnist-hero__actions">
                <a href="#columnist-apply-form" class="sebu-columnist-btn sebu-columnist-btn--primary" data-scroll-to="columnist-apply-form">컬럼리스트 신청하기</a>
                <a href="#columnist-topics" class="sebu-columnist-btn sebu-columnist-btn--secondary" data-scroll-to="columnist-topics">어떤 글을 쓰면 좋을까요?</a>
            </div>
        </div>
        <div class="sebu-columnist-hero__visual" id="columnist-hero-visual">
            <img
                src="<?php echo get_text($img_base.'/hero-columnist.png'); ?>"
                alt="세부어때 컬럼니스트 모집 — 세부의 좋은 정보를 함께 나누는 일러스트"
                class="sebu-columnist-hero__img"
                loading="eager"
                onerror="this.classList.add('is-hidden');this.parentElement.classList.add('is-placeholder');"
            >
            <div class="sebu-columnist-hero__illus" aria-hidden="true">
                <div class="sebu-columnist-hero__illus-map"></div>
                <div class="sebu-columnist-hero__illus-people">
                    <span>👩‍💻</span><span>🗣️</span><span>📱</span>
                </div>
                <p class="sebu-columnist-hero__illus-caption">세부 지도 · 말풍선 · 글쓰기</p>
            </div>
        </div>
    </header>

    <section class="sebu-columnist-recruit__section" aria-labelledby="columnist-story-title">
        <h2 class="sebu-columnist-recruit__section-title" id="columnist-story-title">이런 분들을 기다리고 있어요</h2>
        <?php
        include_once G5_PATH.'/components/eottae/columnist-recruit-comic.php';
        echo eottae_columnist_recruit_comic_html();
        ?>
    </section>

    <section class="sebu-columnist-recruit__section sebu-columnist-split" aria-labelledby="columnist-why-title">
        <div>
            <h2 class="sebu-columnist-recruit__section-title" id="columnist-why-title">세부에는 아직 정리되지 않은 좋은 정보가 많습니다</h2>
            <p class="sebu-columnist-recruit__section-lead">세부에는 많은 이야기가 있습니다. 맛집, 병원, 학교, 부동산, 취미 모임, 사업 이야기, 생활 팁까지. 하지만 이런 정보들은 아직 여기저기 흩어져 있습니다.</p>
            <p class="sebu-columnist-recruit__section-lead"><strong>누군가는 알고 있지만, 누군가는 몰라서 불편을 겪고 있습니다.</strong></p>
            <p class="sebu-columnist-recruit__section-lead">세부어때는 세부에 사는 사람들과 세부를 찾는 사람들이 진짜 도움이 되는 정보를 쉽게 찾을 수 있는 커뮤니티가 되고 싶습니다. 그리고 그 일을 함께할 <strong>컬럼리스트</strong>를 찾고 있습니다.</p>
        </div>
        <div class="sebu-columnist-split__box">
            <ul>
                <li>맛집 · 카페 · 배달 맛집</li>
                <li>병원 · 약국 · 비자 · 생활 팁</li>
                <li>국제학교 · 육아 · 가족 생활</li>
                <li>콘도 · 렌트 · 지역별 주거 정보</li>
                <li>골프 · 모임 · 사업 이야기</li>
            </ul>
        </div>
    </section>

    <section class="sebu-columnist-recruit__section" aria-labelledby="columnist-welcome-title">
        <h2 class="sebu-columnist-recruit__section-title" id="columnist-welcome-title">이런 분이라면 누구나 환영합니다</h2>
        <ul class="sebu-columnist-cards sebu-columnist-cards--2">
            <?php
            $welcome_items = array(
                '세부에 살고 있거나 세부 생활을 잘 아는 분',
                '맛집, 생활정보, 교육, 부동산, 취미, 사업 등 특정 분야에 관심이 많은 분',
                '자신의 경험을 글로 나누고 싶은 분',
                '내 이름이나 활동을 세부 지역사회에 알리고 싶은 분',
                '세부어때 커뮤니티와 함께 성장하고 싶은 분',
            );
            foreach ($welcome_items as $item) {
                ?>
            <li class="sebu-columnist-card sebu-columnist-card--warm">
                <p class="sebu-columnist-card__text"><?php echo get_text($item); ?></p>
            </li>
            <?php } ?>
        </ul>
        <p class="sebu-columnist-emphasis">전문 작가가 아니어도 괜찮습니다.<br>사진 몇 장과 직접 경험한 이야기만 있어도 충분합니다.</p>
    </section>

    <section class="sebu-columnist-recruit__section" aria-labelledby="columnist-benefits-title">
        <h2 class="sebu-columnist-recruit__section-title" id="columnist-benefits-title">컬럼리스트가 되면 이런 점이 좋습니다</h2>
        <div class="sebu-columnist-cards sebu-columnist-cards--2">
            <article class="sebu-columnist-card">
                <span class="sebu-columnist-card__icon" aria-hidden="true">✨</span>
                <h3 class="sebu-columnist-card__title">내 이름과 활동을 알릴 수 있습니다</h3>
                <p class="sebu-columnist-card__text">컬럼에는 작성자의 이름 또는 닉네임이 표시됩니다. 꾸준히 글을 쓰면 세부어때 안에서 자연스럽게 인지도가 쌓입니다.</p>
            </article>
            <article class="sebu-columnist-card">
                <span class="sebu-columnist-card__icon" aria-hidden="true">📚</span>
                <h3 class="sebu-columnist-card__title">전문 분야를 보여줄 수 있습니다</h3>
                <p class="sebu-columnist-card__text">맛집, 부동산, 교육, 생활정보, 취미, 사업 등 내가 잘 아는 분야를 꾸준히 소개하면 신뢰가 쌓입니다.</p>
            </article>
            <article class="sebu-columnist-card">
                <span class="sebu-columnist-card__icon" aria-hidden="true">📣</span>
                <h3 class="sebu-columnist-card__title">자연스러운 홍보가 가능합니다</h3>
                <p class="sebu-columnist-card__text">단순 광고가 아니라 정보성 글을 통해 업체나 개인 활동을 자연스럽게 알릴 수 있습니다.</p>
            </article>
            <article class="sebu-columnist-card">
                <span class="sebu-columnist-card__icon" aria-hidden="true">🚀</span>
                <h3 class="sebu-columnist-card__title">세부어때와 함께 성장할 수 있습니다</h3>
                <p class="sebu-columnist-card__text">좋은 글은 메인 노출, 추천 컬럼, 우수 컬럼리스트 소개 등 더 많은 사람들에게 보여질 수 있습니다.</p>
            </article>
        </div>
    </section>

    <section class="sebu-columnist-recruit__section" aria-labelledby="columnist-rewards-title">
        <h2 class="sebu-columnist-recruit__section-title" id="columnist-rewards-title">좋은 정보를 나누는 분들에게 혜택을 드리고 싶습니다</h2>
        <p class="sebu-columnist-recruit__section-lead">세부어때는 좋은 정보를 제공하는 컬럼리스트분들이 더 돋보이고, 더 많은 기회를 얻을 수 있도록 지원하려고 합니다.</p>
        <div class="sebu-columnist-benefits">
            <?php
            $rewards = array(
                array('icon' => '⭐', 'text' => '우수 컬럼 메인 노출'),
                array('icon' => '🏅', 'text' => '컬럼리스트 전용 뱃지 제공'),
                array('icon' => '👤', 'text' => '작성자 프로필 노출'),
                array('icon' => '📂', 'text' => '작성한 글 모아보기 제공'),
                array('icon' => '🏪', 'text' => '업체 홍보 기회 제공'),
                array('icon' => '🎫', 'text' => '세부어때 제휴 쿠폰 우선 제공'),
                array('icon' => '🎉', 'text' => '이벤트 참여 우선권'),
                array('icon' => '🤝', 'text' => '광고 또는 제휴 기회 연결'),
                array('icon' => '💡', 'text' => '추후 원고료 또는 포인트 보상 검토', 'note' => '운영 정책에 따라 제공 가능'),
            );
            foreach ($rewards as $reward) {
                ?>
            <div class="sebu-columnist-benefit">
                <span class="sebu-columnist-benefit__icon" aria-hidden="true"><?php echo get_text($reward['icon']); ?></span>
                <p class="sebu-columnist-benefit__text">
                    <?php echo get_text($reward['text']); ?>
                    <?php if (!empty($reward['note'])) { ?>
                    <small><?php echo get_text($reward['note']); ?></small>
                    <?php } ?>
                </p>
            </div>
            <?php } ?>
        </div>
    </section>

    <section class="sebu-columnist-recruit__section" id="columnist-topics" aria-labelledby="columnist-topics-title">
        <h2 class="sebu-columnist-recruit__section-title" id="columnist-topics-title">어떤 주제로 써도 좋습니다</h2>
        <div class="sebu-columnist-cards sebu-columnist-cards--3">
            <?php
            $topics = array(
                array('title' => '맛집/카페', 'items' => array('세부 맛집 추천', '카페 후기', '배달 맛집')),
                array('title' => '생활정보', 'items' => array('병원, 약국, 은행', '비자, 교통, 생활 팁', '세부 생활비 이야기')),
                array('title' => '교육/육아', 'items' => array('국제학교', '영어캠프', '아이들과 갈 만한 곳', '가족 생활 정보')),
                array('title' => '부동산', 'items' => array('콘도, 하우스 렌트', '지역별 장단점', '집 구할 때 주의사항')),
                array('title' => '취미/모임', 'items' => array('골프, 피클볼, 축구, 농구', '모터바이크, 다이빙', '동호회 이야기')),
                array('title' => '사업/창업', 'items' => array('세부에서 사업하며 느낀 점', '현지 직원 고용', '마케팅, 매장 운영 이야기')),
            );
            foreach ($topics as $topic) {
                ?>
            <article class="sebu-columnist-card">
                <h3 class="sebu-columnist-card__title"><?php echo get_text($topic['title']); ?></h3>
                <ul>
                    <?php foreach ($topic['items'] as $item) { ?>
                    <li><?php echo get_text($item); ?></li>
                    <?php } ?>
                </ul>
            </article>
            <?php } ?>
        </div>
        <p class="sebu-columnist-emphasis">내가 직접 겪은 경험이라면,<br>그 자체로 누군가에게는 큰 도움이 됩니다.</p>
    </section>

    <section class="sebu-columnist-recruit__section" aria-labelledby="columnist-how-title">
        <h2 class="sebu-columnist-recruit__section-title" id="columnist-how-title">좋은 컬럼은 어렵지 않습니다</h2>
        <div class="sebu-columnist-steps">
            <?php
            $how_steps = array(
                array('title' => '경험을 바탕으로 써주세요', 'text' => '인터넷에 있는 정보보다 중요한 것은 직접 가보고, 겪어보고, 느낀 이야기입니다.'),
                array('title' => '너무 광고처럼 쓰지 않아도 됩니다', 'text' => '장점과 주의할 점을 함께 알려주면 오히려 더 신뢰가 생깁니다.'),
                array('title' => '사진을 함께 올려주세요', 'text' => '직접 찍은 사진 1~3장만 있어도 글의 신뢰도가 높아집니다.'),
                array('title' => '읽는 사람을 생각해주세요', 'text' => '처음 세부에 오는 사람, 세부에 사는 교민, 현지에서 사업하는 사람에게 도움이 되는 글이면 좋습니다.'),
            );
            $step_num = 1;
            foreach ($how_steps as $step) {
                ?>
            <article class="sebu-columnist-step">
                <span class="sebu-columnist-step__num"><?php echo $step_num; ?></span>
                <h3 class="sebu-columnist-card__title"><?php echo get_text($step['title']); ?></h3>
                <p class="sebu-columnist-card__text"><?php echo get_text($step['text']); ?></p>
            </article>
            <?php
                $step_num++;
            }
            ?>
        </div>
    </section>

    <section class="sebu-columnist-recruit__section" aria-labelledby="columnist-examples-title">
        <h2 class="sebu-columnist-recruit__section-title" id="columnist-examples-title">이런 컬럼리스트로 활동할 수 있습니다</h2>
        <div class="sebu-columnist-cards sebu-columnist-cards--3">
            <?php
            $examples = array(
                array('emoji' => '🍜', 'role' => '맛집 컬럼리스트', 'desc' => '세부 맛집, 카페, 한식당, 로컬 음식점, 배달 맛집 등을 소개합니다.'),
                array('emoji' => '🏥', 'role' => '생활정보 컬럼리스트', 'desc' => '병원, 약국, 은행, 비자, 교통, 생활 팁을 정리합니다.'),
                array('emoji' => '👨‍👩‍👧', 'role' => '교육/육아 컬럼리스트', 'desc' => '국제학교, 영어캠프, 학원, 아이들과 갈 만한 곳을 소개합니다.'),
                array('emoji' => '🏠', 'role' => '부동산 컬럼리스트', 'desc' => '세부 콘도, 하우스, 렌트, 지역별 장단점과 주의사항을 알려줍니다.'),
                array('emoji' => '⛳', 'role' => '취미/모임 컬럼리스트', 'desc' => '골프, 피클볼, 축구, 농구, 모터바이크, 다이빙 정보를 소개합니다.'),
                array('emoji' => '💼', 'role' => '사업/창업 컬럼리스트', 'desc' => '세부에서 사업하며 겪은 경험, 현지 직원 고용, 마케팅 이야기를 나눕니다.'),
            );
            foreach ($examples as $ex) {
                ?>
            <article class="sebu-columnist-card sebu-columnist-profile">
                <div class="sebu-columnist-profile__avatar" aria-hidden="true"><?php echo get_text($ex['emoji']); ?></div>
                <h3 class="sebu-columnist-profile__role"><?php echo get_text($ex['role']); ?></h3>
                <p class="sebu-columnist-card__text"><?php echo get_text($ex['desc']); ?></p>
            </article>
            <?php } ?>
        </div>
    </section>

    <section class="sebu-columnist-recruit__section sebu-columnist-cta-band" aria-labelledby="columnist-cta-title">
        <h2 class="sebu-columnist-recruit__section-title" id="columnist-cta-title">세부어때 컬럼리스트로 함께해주세요</h2>
        <p>세부어때는 완벽한 글보다 진짜 도움이 되는 글을 원합니다.<br>멋진 문장보다 중요한 것은 <strong>경험</strong>입니다. 긴 글보다 중요한 것은 <strong>진심</strong>입니다.</p>
        <p style="margin-top:12px">세부를 좋아하는 마음, 세부 생활을 나누고 싶은 마음, 다른 사람에게 도움이 되고 싶은 마음이 있다면 충분합니다.</p>
        <p style="margin-top:20px">
            <a href="#columnist-apply-form" class="sebu-columnist-btn sebu-columnist-btn--primary" data-scroll-to="columnist-apply-form">컬럼리스트 신청하기</a>
        </p>
    </section>

    <section class="sebu-columnist-recruit__section" id="columnist-apply-form" aria-labelledby="columnist-form-title">
        <h2 class="sebu-columnist-recruit__section-title" id="columnist-form-title">컬럼리스트 신청</h2>

        <?php if ($is_columnist) { ?>
        <div class="sebu-columnist-form-wrap">
            <p class="sebu-columnist-recruit__section-lead">이미 컬럼리스트로 등록되어 있습니다. 컬럼 작성은 아래에서 시작해 보세요.</p>
            <a href="<?php echo eottae_column_write_url(); ?>" class="sebu-columnist-btn sebu-columnist-btn--primary">컬럼 작성하기</a>
        </div>
        <?php } elseif ($has_pending) { ?>
        <div class="sebu-columnist-form-wrap">
            <p class="sebu-columnist-recruit__section-lead">검토 중인 신청이 있습니다. 결과는 마이페이지에서 확인할 수 있습니다.</p>
            <a href="<?php echo eottae_column_mypage_url(); ?>" class="sebu-columnist-btn sebu-columnist-btn--primary">마이페이지로 이동</a>
        </div>
        <?php } else { ?>
        <div class="sebu-columnist-form-wrap">
            <?php if ($is_member) { ?>
            <p class="sebu-columnist-form__hint" style="margin-top:0;margin-bottom:16px">
                로그인 회원은 프로필 사진·SNS 링크까지 포함한 <a href="<?php echo $apply_member_url; ?>">정식 신청서</a>로도 신청할 수 있습니다.
            </p>
            <?php } ?>

            <form class="sebu-columnist-form" method="post" action="<?php echo $proc_url; ?>" data-columnist-recruit-form novalidate>
                <input type="hidden" name="eottae_columnist_recruit_token" value="<?php echo get_text($token); ?>">
                <input type="hidden" name="referer" value="<?php echo get_text($recruit_url); ?>">

                <div class="sebu-columnist-form__row">
                    <label class="sebu-columnist-form__label" for="col_pen_name">이름 또는 닉네임 <span class="is-required">*</span></label>
                    <input type="text" id="col_pen_name" name="pen_name" class="sebu-columnist-form__input" maxlength="80" required value="<?php echo get_text($pen_name_default); ?>">
                </div>

                <div class="sebu-columnist-form__row">
                    <label class="sebu-columnist-form__label" for="col_phone">연락처</label>
                    <input type="tel" id="col_phone" name="contact_phone" class="sebu-columnist-form__input" maxlength="30" placeholder="전화번호">
                </div>

                <div class="sebu-columnist-form__row">
                    <label class="sebu-columnist-form__label" for="col_kakao">카카오톡 ID</label>
                    <input type="text" id="col_kakao" name="contact_kakao" class="sebu-columnist-form__input" maxlength="80" placeholder="카카오톡 ID">
                    <p class="sebu-columnist-form__hint">연락처 또는 카카오톡 ID 중 하나는 필수입니다.</p>
                </div>

                <div class="sebu-columnist-form__row">
                    <label class="sebu-columnist-form__label" for="col_email">이메일</label>
                    <input type="email" id="col_email" name="contact_email" class="sebu-columnist-form__input" maxlength="100" placeholder="example@email.com">
                </div>

                <div class="sebu-columnist-form__row">
                    <label class="sebu-columnist-form__label" for="col_interest">관심 분야 <span class="is-required">*</span></label>
                    <select id="col_interest" name="interest" class="sebu-columnist-form__select" required>
                        <option value="">선택해 주세요</option>
                        <?php foreach ($interests as $code => $label) { ?>
                        <option value="<?php echo get_text($code); ?>"><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="sebu-columnist-form__row">
                    <label class="sebu-columnist-form__label" for="col_topic">작성해보고 싶은 주제</label>
                    <input type="text" id="col_topic" name="topic_idea" class="sebu-columnist-form__input" maxlength="200" placeholder="예: IT Park 맛집, 국제학교 생활">
                </div>

                <div class="sebu-columnist-form__row">
                    <label class="sebu-columnist-form__label" for="col_channel">운영 중인 업체나 채널 링크</label>
                    <input type="url" id="col_channel" name="channel_url" class="sebu-columnist-form__input" maxlength="255" placeholder="https://">
                </div>

                <div class="sebu-columnist-form__row">
                    <label class="sebu-columnist-form__label" for="col_bio">간단한 자기소개 <span class="is-required">*</span></label>
                    <textarea id="col_bio" name="bio" class="sebu-columnist-form__textarea" rows="4" maxlength="2000" required placeholder="세부에서 어떤 경험과 정보를 나눌 수 있는지 소개해 주세요."></textarea>
                </div>

                <p class="sebu-columnist-form__privacy">연락처, 이메일, 카카오톡 ID 등 개인정보가 포함될 수 있으니 제출 전 내용을 확인해 주세요. 접수된 정보는 컬럼리스트 검토 목적으로만 사용됩니다.</p>

                <button type="submit" class="sebu-columnist-btn sebu-columnist-btn--primary">컬럼리스트 신청 제출하기</button>
            </form>
        </div>
        <?php } ?>
    </section>

    <?php } ?>
</main>

<?php
g5_page_end();
