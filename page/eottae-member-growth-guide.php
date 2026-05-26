<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';

$ranking_url = function_exists('eottae_member_growth_ranking_url')
    ? eottae_member_growth_ranking_url('week')
    : G5_URL.'/ranking/';
$badge_book_url = function_exists('eottae_member_growth_badge_book_url')
    ? eottae_member_growth_badge_book_url()
    : G5_URL.'/badges/';
$mypage_badges_url = function_exists('eottae_member_growth_mypage_url')
    ? eottae_member_growth_mypage_url()
    : G5_URL.'/mypage/badges.php';
$community_url = function_exists('eottae_community_list_url')
    ? eottae_community_list_url()
    : G5_BBS_URL.'/board.php?bo_table='.EOTTae_COMMUNITY_TABLE;
$challenge_url = function_exists('eottae_challenge_list_url')
    ? eottae_challenge_list_url()
    : G5_URL.'/challenge/';

if (!$is_member) {
    $cta_url = function_exists('eottae_login_url')
        ? eottae_login_url($mypage_badges_url)
        : G5_BBS_URL.'/login.php?url='.urlencode($mypage_badges_url);
    $cta_label = '로그인 후 내 등급 보기';
    $back_url = G5_URL;
    $back_label = '홈';
} else {
    $cta_url = $mypage_badges_url;
    $cta_label = '내 등급·뱃지 보기';
    $back_url = function_exists('eottae_mypage_url') ? eottae_mypage_url() : G5_URL.'/page/eottae-mypage.php';
    $back_label = '마이페이지';
}

$score_rules = function_exists('eottae_member_growth_score_rules') ? eottae_member_growth_score_rules() : array();
$daily_cap = function_exists('eottae_member_growth_daily_total_cap') ? eottae_member_growth_daily_total_cap() : 500;

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-member-growth.css">', 24);

g5_page_start('활동 등급·뱃지 안내');
?>

<main class="eottae-guide-page eottae-guide-page--member-growth">
    <header class="eottae-guide-page__header">
        <a href="<?php echo $back_url; ?>" class="eottae-guide-page__back">← <?php echo get_text($back_label); ?></a>
        <p class="eottae-guide-page__badge">활동 등급 안내</p>
        <h1 class="eottae-guide-page__title">세부어때 활동 등급·뱃지</h1>
        <p class="eottae-guide-page__lead">커뮤니티에 도움이 되는 활동을 하면 <strong>활동 점수</strong>가 쌓이고, 등급과 뱃지가 올라갑니다. 그누보드 포인트와는 별도로 운영됩니다.</p>
        <a href="<?php echo $cta_url; ?>" class="eottae-guide-page__cta"><?php echo get_text($cta_label); ?></a>
    </header>

    <section class="eottae-guide-section">
        <h2 class="eottae-guide-section__title">어떻게 올라가나요?</h2>
        <ul class="eottae-guide-cards">
            <li class="eottae-guide-cards__item">
                <strong>글·댓글·참여</strong>
                <p>커뮤니티 글, 댓글, 세부톡 참여, 일정 등록, 챌린지 참여 등이 점수로 반영됩니다.</p>
            </li>
            <li class="eottae-guide-cards__item">
                <strong>등급 자동 승급</strong>
                <p>누적 활동 점수에 따라 새싹회원부터 세부어때 VIP까지 단계가 올라갑니다.</p>
            </li>
            <li class="eottae-guide-cards__item">
                <strong>뱃지 수집</strong>
                <p>첫 글, 댓글 10개, 챌린지 참여 등 조건을 달성하면 뱃지가 자동으로 지급됩니다.</p>
            </li>
        </ul>
    </section>

    <section class="eottae-guide-section">
        <h2 class="eottae-guide-section__title">활동 점수 예시</h2>
        <table class="eottae-guide-table">
            <thead>
                <tr><th>활동</th><th>점수</th><th>비고</th></tr>
            </thead>
            <tbody>
                <?php
                $guide_rows = array(
                    'register'        => '회원가입 (1회)',
                    'first_post'      => '첫 글 작성 (1회)',
                    'post_write'      => '글 작성',
                    'comment_write'   => '댓글 작성',
                    'talkroom_join'   => '세부톡 참여 (방당 1회)',
                    'talkroom_post'   => '세부톡 글',
                    'life_info_post'  => '생활정보(광장) 글',
                    'calendar_event'  => '일정 등록',
                    'challenge_entry' => '챌린지 참여',
                    'like_received'   => '내 글에 공감 받음',
                    'report_confirmed'=> '유효 신고 (운영 처리)',
                    'best_post'       => '우수글 선정 (1회)',
                );
                foreach ($guide_rows as $key => $label) {
                    if (!isset($score_rules[$key])) {
                        continue;
                    }
                    $rule = $score_rules[$key];
                    $score = (int) ($rule['score'] ?? 0);
                    $daily = (int) ($rule['daily_max'] ?? 0);
                    $note = !empty($rule['once']) ? '1회만' : ($daily > 0 ? '일일 최대 '.number_format($daily).'점' : '');
                    ?>
                <tr>
                    <td><?php echo get_text($label); ?></td>
                    <td>+<?php echo number_format($score); ?></td>
                    <td><?php echo get_text($note); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <p class="eottae-guide-tip">하루 총 획득 상한은 <?php echo number_format($daily_cap); ?>점입니다. 과도한 점수 경쟁을 막기 위한 장치입니다.</p>
    </section>

    <section class="eottae-guide-section">
        <h2 class="eottae-guide-section__title">어디서 보나요?</h2>
        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">1</span>
            <div class="eottae-guide-step__body">
                <h3>글·댓글 옆</h3>
                <p>작성자 닉네임 옆에 등급 칩과 대표 뱃지가 표시됩니다.</p>
            </div>
        </article>
        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">2</span>
            <div class="eottae-guide-step__body">
                <h3>마이페이지 · 프로필</h3>
                <p><a href="<?php echo $mypage_badges_url; ?>">내 뱃지·등급</a>에서 점수, 등급, 획득 뱃지를 확인할 수 있습니다. 다른 회원 프로필에서도 공개 설정된 정보를 볼 수 있습니다.</p>
            </div>
        </article>
        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">3</span>
            <div class="eottae-guide-step__body">
                <h3>랭킹 · 뱃지 도감</h3>
                <p><a href="<?php echo $ranking_url; ?>">활동 랭킹</a>에서 이번 주·이번 달 활약 회원을 볼 수 있고, <a href="<?php echo $badge_book_url; ?>">뱃지 도감</a>에서 전체 뱃지 목록을 확인할 수 있습니다.</p>
            </div>
        </article>
    </section>

    <section class="eottae-guide-section eottae-guide-section--faq">
        <h2 class="eottae-guide-section__title">자주 묻는 질문</h2>
        <dl class="eottae-guide-faq">
            <dt>그누보드 포인트와 같나요?</dt>
            <dd>아닙니다. 활동 점수는 세부어때 전용 등급 시스템이며, 기존 mb_point와는 별도로 관리됩니다.</dd>

            <dt>점수가 안 올라가요</dt>
            <dd>일일 상한에 도달했거나, 이미 받은 1회성 보상(가입·첫 글·같은 톡방 참여 등)일 수 있습니다. 마이페이지에서 최근 점수 내역을 확인해 보세요.</dd>

            <dt>랭킹에 안 나오고 싶어요</dt>
            <dd>마이페이지 뱃지 설정에서 랭킹 제외·닉네임 마스킹을 요청할 수 있습니다. 운영 정책에 따라 반영됩니다.</dd>

            <dt>유효 신고란?</dt>
            <dd>커뮤니티·광장·세부톡·챌린지 등에서 신고한 내용이 운영자에 의해 실제 조치(삭제 등)되면 신고자에게 소량의 활동 점수가 지급될 수 있습니다.</dd>

            <dt>더 많은 점수를 얻으려면?</dt>
            <dd><a href="<?php echo $community_url; ?>">커뮤니티</a> 글·댓글, 세부톡 참여, <a href="<?php echo $challenge_url; ?>">챌린지</a> 참여처럼 다른 회원에게 도움이 되는 활동을 꾸준히 해 보세요.</dd>
        </dl>
    </section>

    <footer class="eottae-guide-page__footer">
        <a href="<?php echo $cta_url; ?>" class="eottae-guide-page__cta"><?php echo get_text($cta_label); ?></a>
        <p class="eottae-guide-page__footer-link">
            <a href="<?php echo $ranking_url; ?>">활동 랭킹</a> ·
            <a href="<?php echo $badge_book_url; ?>">뱃지 도감</a>
        </p>
    </footer>
</main>

<?php
g5_page_end();
