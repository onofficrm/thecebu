<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-challenge.lib.php';

$challenge_list_url = function_exists('eottae_challenge_list_url')
    ? eottae_challenge_list_url()
    : G5_URL.'/challenge/';
$mypage_challenge_url = function_exists('eottae_challenge_mypage_url')
    ? eottae_challenge_mypage_url()
    : G5_URL.'/mypage/challenges.php';

if (!$is_member) {
    $cta_url = function_exists('eottae_login_url')
        ? eottae_login_url($challenge_list_url)
        : G5_BBS_URL.'/login.php?url='.urlencode($challenge_list_url);
    $cta_label = '로그인 후 챌린지 참여';
    $back_url = G5_URL;
    $back_label = '홈';
} else {
    $cta_url = $challenge_list_url;
    $cta_label = '챌린지 참여하기';
    $back_url = function_exists('eottae_mypage_url') ? eottae_mypage_url() : G5_URL.'/page/eottae-mypage.php';
    $back_label = '마이페이지';
}

g5_page_start('챌린지 참여 안내');
?>

<main class="eottae-guide-page eottae-guide-page--challenge">
    <header class="eottae-guide-page__header">
        <a href="<?php echo $back_url; ?>" class="eottae-guide-page__back">← <?php echo get_text($back_label); ?></a>
        <p class="eottae-guide-page__badge">챌린지 안내</p>
        <h1 class="eottae-guide-page__title">세부어때 챌린지 참여 방법</h1>
        <p class="eottae-guide-page__lead">세부 생활을 함께 기록하는 <strong>챌린지</strong>에 참여하고, 포인트·뱃지·커뮤니티 활동을 즐겨 보세요.</p>
        <a href="<?php echo $cta_url; ?>" class="eottae-guide-page__cta"><?php echo get_text($cta_label); ?></a>
    </header>

    <section class="eottae-guide-section">
        <h2 class="eottae-guide-section__title">챌린지가 뭔가요?</h2>
        <ul class="eottae-guide-cards">
            <li class="eottae-guide-cards__item">
                <strong>주제별 참여</strong>
                <p>맛집 인증, 세부 사진, 생활팁 등 매주·매월 정해진 주제에 참여합니다.</p>
            </li>
            <li class="eottae-guide-cards__item">
                <strong>사진·글 인증</strong>
                <p>사진 1장과 짧은 후기만 올려도 참여가 완료됩니다.</p>
            </li>
            <li class="eottae-guide-cards__item">
                <strong>보상과 재미</strong>
                <p>참여 포인트, 뱃지, 우수 인증글 선정 등 활동 동기를 제공합니다.</p>
            </li>
        </ul>
    </section>

    <section class="eottae-guide-section">
        <h2 class="eottae-guide-section__title">참여하는 방법</h2>

        <?php
        include_once G5_PATH.'/components/eottae/challenge-guide-comic.php';
        echo eottae_challenge_guide_comic_html();
        ?>

        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">1</span>
            <div class="eottae-guide-step__body">
                <h3>챌린지 목록 보기</h3>
                <p><a href="<?php echo $challenge_list_url; ?>">챌린지</a> 메뉴에서 진행 중인 챌린지를 확인합니다. 맛집·사진·생활팁 등 관심 주제를 골라 보세요.</p>
            </div>
        </article>

        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">2</span>
            <div class="eottae-guide-step__body">
                <h3>인증글 작성</h3>
                <p><strong>참여하기</strong> 버튼을 누르고 제목·내용·사진을 작성합니다. 지역, 장소명, 관련 세부톡방도 함께 적을 수 있습니다.</p>
                <p class="eottae-guide-tip">로그인 회원만 참여할 수 있습니다.</p>
            </div>
        </article>

        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">3</span>
            <div class="eottae-guide-step__body">
                <h3>참여 완료 · 포인트</h3>
                <p>제출이 완료되면 챌린지별 참여 포인트가 지급될 수 있습니다. <a href="<?php echo $mypage_challenge_url; ?>">내 챌린지 참여 내역</a>에서 확인하세요.</p>
            </div>
        </article>

        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">4</span>
            <div class="eottae-guide-step__body">
                <h3>공감 · 댓글 · 정보 나누기</h3>
                <p>다른 회원의 인증글에 공감과 댓글을 남기며 세부 생활 정보를 나눠 보세요. 우수 인증글은 메인에 노출될 수 있습니다.</p>
            </div>
        </article>
    </section>

    <section class="eottae-guide-section">
        <h2 class="eottae-guide-section__title">챌린지 예시</h2>
        <table class="eottae-guide-table">
            <thead>
                <tr><th>챌린지</th><th>참여 방법</th></tr>
            </thead>
            <tbody>
                <tr><td>세부 맛집 인증</td><td>맛집 사진 + 위치·한 줄 후기</td></tr>
                <tr><td>세부 사진 한 장</td><td>오늘 본 세부 풍경·일상 사진</td></tr>
                <tr><td>세부 생활팁 공유</td><td>정착·생활에 도움 되는 팁 글</td></tr>
                <tr><td>세부톡 모임 후기</td><td>모임 참여 사진과 후기</td></tr>
            </tbody>
        </table>
    </section>

    <section class="eottae-guide-section eottae-guide-section--faq">
        <h2 class="eottae-guide-section__title">자주 묻는 질문</h2>
        <dl class="eottae-guide-faq">
            <dt>비회원도 볼 수 있나요?</dt>
            <dd>챌린지 목록과 인증글은 누구나 볼 수 있습니다. 참여·댓글·공감은 로그인 후 가능합니다.</dd>

            <dt>같은 챌린지에 여러 번 참여할 수 있나요?</dt>
            <dd>현재는 동일 챌린지에 여러 번 참여할 수 있습니다. 주제가 바뀔 때마다 새로 올려 주세요.</dd>

            <dt>포인트는 언제 받나요?</dt>
            <dd>인증글 제출이 완료되면 챌린지별 설정에 따라 참여 포인트가 자동 지급됩니다.</dd>

            <dt>부적절한 글은 어떻게 하나요?</dt>
            <dd>인증글 상세에서 신고할 수 있으며, 운영자가 확인 후 조치합니다.</dd>
        </dl>
    </section>

    <footer class="eottae-guide-page__footer">
        <a href="<?php echo $cta_url; ?>" class="eottae-guide-page__cta"><?php echo get_text($cta_label); ?></a>
        <p class="eottae-guide-page__footer-link"><a href="<?php echo $challenge_list_url; ?>">진행 중인 챌린지 보기</a></p>
    </footer>
</main>

<?php
g5_page_end();
