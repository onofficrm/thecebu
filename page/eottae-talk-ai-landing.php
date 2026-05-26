<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

$list_url = eottae_talkroom_list_url();
$create_url = eottae_talkroom_create_url();
if (empty($is_member) && function_exists('eottae_login_url')) {
    $create_url = eottae_login_url($create_url);
}

$page_title = '세부어때 AI 톡방 도우미 - 조용한 단톡방을 살리는 커뮤니티 AI';
$page_description = '세부어때 AI 톡방 도우미는 조용한 톡방에 오늘의 질문, 모임 제안, 신규회원 환영 메시지를 자동으로 작성해 커뮤니티 참여를 유도합니다.';
$page_keywords = '세부어때, 세부톡방, AI 도우미, 세부 교민, 단톡방, 오픈채팅, 커뮤니티';

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/ai-talk-landing.css">', 25);

g5_page_start('AI 톡방 도우미');

$pain_points = array(
    array('icon' => '💬', 'title' => '대화가 금방 끊깁니다', 'desc' => '처음엔 반응이 있었는데, 며칠 지나면 아무도 말을 안 합니다.'),
    array('icon' => '😶', 'title' => '방장 혼자 애씁니다', 'desc' => '방장이 계속 말을 걸지 않으면 톡방은 금방 조용해집니다.'),
    array('icon' => '🙋', 'title' => '신규 회원이 어색합니다', 'desc' => '새로 들어온 회원이 먼저 인사하기 어려워 대화가 시작되지 않습니다.'),
    array('icon' => '⚽', 'title' => '모임 제안이 어렵습니다', 'desc' => '운동·모임을 만들고 싶어도 누가 먼저 제안하기 쉽지 않습니다.'),
    array('icon' => '📋', 'title' => '정보가 흘러갑니다', 'desc' => '유용한 대화와 정보가 쌓이지 않고 그냥 지나가 버립니다.'),
    array('icon' => '🌫️', 'title' => '결국 빈 방이 됩니다', 'desc' => '조용해진 방은 다시 찾지 않게 되고, 아무도 들어오지 않습니다.'),
);

$features = array(
    array(
        'icon' => '🌤️',
        'title' => '조용한 방 화제 던지기',
        'desc' => '일정 시간 대화가 없으면 AI가 자연스럽게 질문을 던집니다.',
        'example' => '오늘 방이 조용하네요 😊 세부에서 요즘 자주 가는 맛집 하나씩 추천해볼까요?',
    ),
    array(
        'icon' => '❓',
        'title' => '오늘의 질문',
        'desc' => '매일 가벼운 질문으로 댓글 참여를 유도합니다.',
        'example' => '세부 살면서 ‘이건 한국보다 좋다’ 싶은 게 있으세요?',
    ),
    array(
        'icon' => '🤝',
        'title' => '모임 제안',
        'desc' => '축구, 족구, 골프, 맘수다, 사업자 모임처럼 자연스럽게 모임을 제안합니다.',
        'example' => '이번 주 토요일 족구 한 판 어떠세요? 참여 가능하신 분은 댓글로 ‘참여’ 남겨주세요.',
    ),
    array(
        'icon' => '👋',
        'title' => '신규회원 환영',
        'desc' => '새 회원이 들어오면 어색하지 않게 AI가 먼저 인사합니다.',
        'example' => '새로 오신 회원님 환영합니다 😊 세부 거주 중이신가요, 여행 준비 중이신가요?',
    ),
    array(
        'icon' => '📝',
        'title' => '방 요약',
        'desc' => '놓친 대화를 짧게 요약해 다시 들어오고 싶게 만듭니다.',
        'example' => '오늘 축구톡방 요약: 토요일 풋살 가능 인원 6명, 장소 후보는 세부시티입니다.',
    ),
    array(
        'icon' => '🛒',
        'title' => '중고거래/모임 글쓰기 도움',
        'desc' => '거래글이나 모임글에 필요한 정보를 자연스럽게 안내합니다.',
        'example' => '중고거래 글에는 가격, 지역, 상품 상태, 연락 방법을 함께 적으면 거래가 더 빨라져요.',
    ),
);

$room_examples = array(
    array('emoji' => '⚽', 'title' => '세부 축구/족구톡방', 'example' => '이번 주말 운동하실 분 계신가요? 토요일 오후 가능하신 분 댓글 남겨주세요 ⚽'),
    array('emoji' => '👶', 'title' => '세부 맘수다방', 'example' => '오늘의 맘수다 질문입니다 😊 세부에서 아이 키우면서 가장 도움이 됐던 장소가 있으세요?'),
    array('emoji' => '💼', 'title' => '세부 사업자방', 'example' => '오늘의 사업자 질문입니다. 필리핀 직원 채용할 때 가장 중요하게 보는 점은 무엇인가요?'),
    array('emoji' => '✈️', 'title' => '세부 여행자 질문방', 'example' => '세부 처음 오시는 분들이 가장 많이 궁금해하는 건 공항픽업, 환전, 마사지, 호핑투어입니다. 뭐가 궁금하세요?'),
    array('emoji' => '🛍️', 'title' => '세부 중고거래방', 'example' => '안 쓰는 물건이 있다면 올려보세요. 가격, 지역, 상태를 함께 적으면 거래가 빨라집니다 😊'),
    array('emoji' => '🍜', 'title' => '세부 맛집/카페방', 'example' => '오늘 점심 뭐 드셨나요? 세부에서 최근 만족했던 맛집 하나씩 추천해볼까요?'),
);

$compare_kakao = array(
    '대화가 흘러가면 다시 찾기 어려움',
    '조용해지면 방장이 계속 말 걸어야 함',
    '새 회원이 적응하기 어려움',
    '정보가 정리되지 않음',
    '검색 유입이 어려움',
);

$compare_eottae = array(
    'AI가 자연스럽게 대화 시작',
    '오늘의 질문으로 참여 유도',
    '신규회원 환영',
    '모임 제안 가능',
    '대화와 정보가 사이트에 남음',
    'SEO 콘텐츠로 확장 가능',
    '방별 AI 설정 가능',
);

$control_points = array(
    'AI 사용 여부 ON/OFF 가능',
    '방별로 AI 이름과 말투 설정 가능',
    '하루 최대 발언 수 제한 가능',
    '조용한 방에서만 작동 가능',
    '방장이 원하지 않으면 끌 수 있음',
    '최고관리자가 전체 제어 가능',
    'AI 메시지는 “AI 도우미”로 명확히 표시',
);

$create_prompts = array(
    '세부 축구톡방을 만들고 싶으신가요?',
    '맘수다방을 운영해보고 싶으신가요?',
    '사업자 정보교류방을 만들고 싶으신가요?',
    '여행자 질문방을 운영해보고 싶으신가요?',
);

$faqs = array(
    array(
        'q' => 'AI가 자동으로 글을 쓰나요?',
        'a' => '네. 방장이 허용한 경우, 정해진 조건에서만 AI가 질문, 모임 제안, 환영 메시지 등을 작성할 수 있습니다.',
    ),
    array(
        'q' => 'AI 기능을 끌 수 있나요?',
        'a' => '네. 방장은 자기 톡방의 AI 기능을 켜거나 끌 수 있고, 최고관리자는 전체 AI 사용 여부를 관리할 수 있습니다.',
    ),
    array(
        'q' => 'AI가 너무 자주 말하지 않나요?',
        'a' => '하루 최대 발언 수, 활동 시간, 조용한 방 판단 기준을 설정할 수 있어 과도한 발언을 막을 수 있습니다.',
    ),
    array(
        'q' => 'AI가 사람처럼 보이면 혼란스럽지 않나요?',
        'a' => 'AI가 작성한 글에는 “어때봇 · AI 도우미” 표시가 명확하게 붙습니다.',
    ),
    array(
        'q' => '어떤 톡방에 적합한가요?',
        'a' => '축구, 족구, 골프, 맘수다, 사업자방, 여행자 질문방, 맛집방, 중고거래방 등 다양한 톡방에 사용할 수 있습니다.',
    ),
    array(
        'q' => '카카오톡 단톡방을 대체하는 건가요?',
        'a' => '완전히 대체하기보다, 카카오톡에서 흘러가는 대화와 정보를 세부어때에 쌓이게 만드는 보완 역할입니다.',
    ),
);
?>

<main class="ai-talk-landing" id="ai-talk-landing">
    <!-- 1. 히어로 -->
    <section class="ai-talk-hero" aria-labelledby="ai-talk-hero-title">
        <div class="ai-talk-hero__inner">
            <p class="ai-talk-hero__badge">🤖 세부어때 AI 톡방 도우미</p>
            <h1 class="ai-talk-hero__title" id="ai-talk-hero-title">
                단톡방은 만들었는데,<br>
                <span class="ai-talk-hero__title-accent">왜 아무도 말을 안 할까요?</span>
            </h1>
            <p class="ai-talk-hero__lead">
                조용한 톡방, 이제 AI가 먼저 말을 걸어줍니다.<br>
                세부어때 AI 톡방 도우미는 조용한 톡방에 자연스럽게 화제를 던지고,
                모임을 제안하고, 신규회원에게 인사하며, 우리 톡방의 <strong>분위기 메이커</strong>가 되어줍니다.
            </p>
            <nav class="ai-talk-hero__actions" aria-label="주요 이동">
                <a href="<?php echo $create_url; ?>" class="ai-talk-btn ai-talk-btn--primary">AI 톡방 만들기</a>
                <a href="<?php echo $list_url; ?>" class="ai-talk-btn ai-talk-btn--ghost">세부톡방 둘러보기</a>
            </nav>

            <div class="ai-talk-hero__chat" aria-hidden="true">
                <div class="ai-talk-bubble ai-talk-bubble--ai">
                    <span class="ai-talk-bubble__who">어때봇 · AI 도우미</span>
                    <p>오늘 방이 조용하네요 😊 이번 주말 세부에서 뭐 하실 예정인가요?</p>
                </div>
                <div class="ai-talk-bubble ai-talk-bubble--member">
                    <span class="ai-talk-bubble__who">회원 A</span>
                    <p>족구 가능하신 분 있나요?</p>
                </div>
                <div class="ai-talk-bubble ai-talk-bubble--ai">
                    <span class="ai-talk-bubble__who">어때봇 · AI 도우미</span>
                    <p>좋아요! 토요일 오후 가능하신 분은 댓글로 ‘참여’ 남겨주세요.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. 공감 -->
    <section class="ai-talk-section ai-talk-section--muted" aria-labelledby="ai-talk-pain-title">
        <div class="ai-talk-section__inner">
            <h2 class="ai-talk-section__title" id="ai-talk-pain-title">단톡방 운영, 생각보다 어렵습니다</h2>
            <p class="ai-talk-section__desc">처음엔 사람들이 들어왔지만, 대화가 끊기면 방은 금방 조용해집니다.</p>
            <ul class="ai-talk-card-grid ai-talk-card-grid--pain">
                <?php foreach ($pain_points as $item) { ?>
                <li class="ai-talk-card ai-talk-card--pain">
                    <span class="ai-talk-card__icon" aria-hidden="true"><?php echo $item['icon']; ?></span>
                    <h3 class="ai-talk-card__title"><?php echo get_text($item['title']); ?></h3>
                    <p class="ai-talk-card__text"><?php echo get_text($item['desc']); ?></p>
                </li>
                <?php } ?>
            </ul>
        </div>
    </section>

    <!-- 3. 해결책 -->
    <section class="ai-talk-section" aria-labelledby="ai-talk-solution-title">
        <div class="ai-talk-section__inner">
            <h2 class="ai-talk-section__title" id="ai-talk-solution-title">그래서 세부어때에는 AI 톡방 도우미가 있습니다</h2>
            <p class="ai-talk-section__desc">
                AI가 사람 대신 대화를 장악하는 것이 아니라, 방 분위기를 살리는 <strong>가벼운 도우미</strong> 역할을 합니다.
            </p>
            <blockquote class="ai-talk-quote">
                AI가 대신 떠드는 것이 아니라,<br>
                사람들이 대화할 수 있도록 <strong>자연스럽게 시작점</strong>을 만들어줍니다.
            </blockquote>
            <p class="ai-talk-section__note">방장이 매번 말을 걸지 않아도 됩니다. AI가 우리 톡방의 분위기 메이커가 되어줍니다.</p>
        </div>
    </section>

    <!-- 4. 주요 기능 -->
    <section class="ai-talk-section ai-talk-section--warm" aria-labelledby="ai-talk-features-title">
        <div class="ai-talk-section__inner">
            <h2 class="ai-talk-section__title" id="ai-talk-features-title">AI 도우미가 해주는 일</h2>
            <p class="ai-talk-section__desc">
                조용한 방에는 오늘의 질문을, 운동방에는 모임 제안을, 신규회원에게는 환영 인사를, 바쁜 회원에게는 오늘의 요약을.
            </p>
            <ul class="ai-talk-feature-list">
                <?php foreach ($features as $feature) { ?>
                <li class="ai-talk-feature">
                    <div class="ai-talk-feature__head">
                        <span class="ai-talk-feature__icon" aria-hidden="true"><?php echo $feature['icon']; ?></span>
                        <h3 class="ai-talk-feature__title"><?php echo get_text($feature['title']); ?></h3>
                    </div>
                    <p class="ai-talk-feature__desc"><?php echo get_text($feature['desc']); ?></p>
                    <div class="ai-talk-bubble ai-talk-bubble--sample">
                        <span class="ai-talk-bubble__who">어때봇 · AI 도우미</span>
                        <p><?php echo get_text($feature['example']); ?></p>
                    </div>
                </li>
                <?php } ?>
            </ul>
        </div>
    </section>

    <!-- 5. 방별 활용 -->
    <section class="ai-talk-section" aria-labelledby="ai-talk-rooms-title">
        <div class="ai-talk-section__inner">
            <h2 class="ai-talk-section__title" id="ai-talk-rooms-title">어떤 톡방에서 사용할 수 있나요?</h2>
            <ul class="ai-talk-card-grid ai-talk-card-grid--rooms">
                <?php foreach ($room_examples as $room) { ?>
                <li class="ai-talk-card ai-talk-card--room">
                    <h3 class="ai-talk-card__title"><span aria-hidden="true"><?php echo $room['emoji']; ?></span> <?php echo get_text($room['title']); ?></h3>
                    <div class="ai-talk-bubble ai-talk-bubble--sample ai-talk-bubble--compact">
                        <span class="ai-talk-bubble__who">어때봇 · AI 도우미</span>
                        <p><?php echo get_text($room['example']); ?></p>
                    </div>
                </li>
                <?php } ?>
            </ul>
        </div>
    </section>

    <!-- 6. 차별점 -->
    <section class="ai-talk-section ai-talk-section--muted" aria-labelledby="ai-talk-compare-title">
        <div class="ai-talk-section__inner">
            <h2 class="ai-talk-section__title" id="ai-talk-compare-title">카카오톡 단톡방과 무엇이 다를까요?</h2>
            <div class="ai-talk-compare">
                <div class="ai-talk-compare__col ai-talk-compare__col--kakao">
                    <h3 class="ai-talk-compare__heading">카카오톡 단톡방</h3>
                    <ul class="ai-talk-compare__list">
                        <?php foreach ($compare_kakao as $line) { ?>
                        <li><?php echo get_text($line); ?></li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="ai-talk-compare__col ai-talk-compare__col--eottae">
                    <h3 class="ai-talk-compare__heading">세부어때 AI 톡방</h3>
                    <ul class="ai-talk-compare__list">
                        <?php foreach ($compare_eottae as $line) { ?>
                        <li><?php echo get_text($line); ?></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <p class="ai-talk-quote ai-talk-quote--inline">
                카톡은 실시간 대화에 강하고,<br>
                세부어때는 대화가 <strong>정보와 커뮤니티 자산</strong>으로 쌓이는 데 강합니다.
            </p>
            <p class="ai-talk-section__note">세부어때 톡방은 대화가 흘러가지 않고 커뮤니티 자산으로 쌓입니다.</p>
        </div>
    </section>

    <!-- 7. AI 제어 -->
    <section class="ai-talk-section" aria-labelledby="ai-talk-control-title">
        <div class="ai-talk-section__inner">
            <h2 class="ai-talk-section__title" id="ai-talk-control-title">AI가 너무 많이 끼어들면 불편하지 않을까요?</h2>
            <p class="ai-talk-section__desc">AI는 방장이 설정한 조건에서만 작동합니다. 사람이 대화할 자리를 빼앗지 않습니다.</p>
            <ul class="ai-talk-checklist">
                <?php foreach ($control_points as $point) { ?>
                <li><?php echo get_text($point); ?></li>
                <?php } ?>
            </ul>
            <blockquote class="ai-talk-quote">
                AI는 사람이 대화할 자리를 빼앗지 않습니다.<br>
                사람들이 다시 대화할 수 있도록 <strong>조용히 시작점</strong>을 만들어줄 뿐입니다.
            </blockquote>
        </div>
    </section>

    <!-- 8. 톡방 개설 유도 -->
    <section class="ai-talk-section ai-talk-section--cta" aria-labelledby="ai-talk-create-title">
        <div class="ai-talk-section__inner ai-talk-section__inner--center">
            <h2 class="ai-talk-section__title" id="ai-talk-create-title">이제, 내 톡방을 만들어보세요</h2>
            <ul class="ai-talk-prompt-list">
                <?php foreach ($create_prompts as $prompt) { ?>
                <li><?php echo get_text($prompt); ?></li>
                <?php } ?>
            </ul>
            <p class="ai-talk-section__desc">
                세부어때에서는 회원 누구나 톡방을 바로 만들 수 있습니다.<br>
                개설 즉시 세부톡방 목록에 노출되고,<br>
                AI 도우미가 방 분위기를 함께 살려줍니다.
            </p>
            <nav class="ai-talk-hero__actions" aria-label="톡방 개설">
                <a href="<?php echo $create_url; ?>" class="ai-talk-btn ai-talk-btn--primary">톡방 만들기</a>
                <a href="<?php echo $list_url; ?>" class="ai-talk-btn ai-talk-btn--ghost ai-talk-btn--on-dark">세부톡방 둘러보기</a>
            </nav>
        </div>
    </section>

    <!-- 9. FAQ -->
    <section class="ai-talk-section ai-talk-section--muted" aria-labelledby="ai-talk-faq-title">
        <div class="ai-talk-section__inner">
            <h2 class="ai-talk-section__title" id="ai-talk-faq-title">자주 묻는 질문</h2>
            <div class="ai-talk-faq">
                <?php foreach ($faqs as $i => $faq) { ?>
                <details class="ai-talk-faq__item"<?php echo $i === 0 ? ' open' : ''; ?>>
                    <summary class="ai-talk-faq__q"><?php echo get_text($faq['q']); ?></summary>
                    <p class="ai-talk-faq__a"><?php echo get_text($faq['a']); ?></p>
                </details>
                <?php } ?>
            </div>
        </div>
    </section>

    <!-- 10. 하단 CTA -->
    <section class="ai-talk-final" aria-labelledby="ai-talk-final-title">
        <div class="ai-talk-final__inner">
            <h2 class="ai-talk-final__title" id="ai-talk-final-title">조용한 톡방을 다시 살아나게 하는 방법</h2>
            <p class="ai-talk-final__text">
                이제 톡방을 만들고 방장이 혼자 애쓰지 않아도 됩니다.<br>
                세부어때 AI 톡방 도우미가 오늘의 질문을 던지고, 모임을 제안하고, 새 회원을 환영하며, 우리 톡방의 첫 대화를 만들어줍니다.
            </p>
            <p class="ai-talk-final__tagline">이제 톡방 운영을 혼자 고민하지 마세요.</p>
            <nav class="ai-talk-hero__actions" aria-label="마무리 이동">
                <a href="<?php echo $create_url; ?>" class="ai-talk-btn ai-talk-btn--primary ai-talk-btn--lg">AI 톡방 만들기</a>
                <a href="<?php echo $list_url; ?>" class="ai-talk-btn ai-talk-btn--ghost ai-talk-btn--on-dark">세부톡방 둘러보기</a>
            </nav>
            <p class="ai-talk-final__back"><a href="<?php echo $list_url; ?>">← 세부톡방 목록으로</a></p>
        </div>
    </section>
</main>

<?php
g5_page_end();
