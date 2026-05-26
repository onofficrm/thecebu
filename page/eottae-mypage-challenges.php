<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
include_once G5_PATH.'/components/eottae/challenge-card.php';

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', function_exists('eottae_login_url') ? eottae_login_url(eottae_challenge_mypage_url()) : G5_BBS_URL.'/login.php');
}

$summary = eottae_challenge_my_summary($member['mb_id']);
$entries = eottae_challenge_my_entries($member['mb_id'], 50);
$rewards = eottae_challenge_my_rewards($member['mb_id'], 50);

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-challenge.css">', 24);

g5_page_start('내 챌린지');
?>

<main class="mypage-subpage sebu-challenge-page sebu-challenge-page--mypage">
    <?php if (function_exists('eottae_render_mypage_back')) {
        eottae_render_mypage_back();
    } else { ?>
    <p class="mypage-subpage__back"><a href="<?php echo function_exists('eottae_mypage_url') ? eottae_mypage_url() : G5_URL.'/page/eottae-mypage.php'; ?>">← 마이페이지</a></p>
    <?php } ?>

    <h1 class="mypage-subpage__title">내 챌린지 참여</h1>
    <p class="sebu-challenge-page__intro"><?php echo get_text($summary['summary_line'] ?? ''); ?></p>

    <section class="sebu-challenge-mypage-stats">
        <div class="sebu-challenge-mypage-stat">
            <span class="sebu-challenge-mypage-stat__label">인증글</span>
            <span class="sebu-challenge-mypage-stat__value"><?php echo number_format((int) ($summary['entry_count'] ?? 0)); ?></span>
        </div>
        <div class="sebu-challenge-mypage-stat">
            <span class="sebu-challenge-mypage-stat__label">받은 포인트</span>
            <span class="sebu-challenge-mypage-stat__value"><?php echo number_format((int) ($summary['point_total'] ?? 0)); ?>P</span>
        </div>
        <div class="sebu-challenge-mypage-stat">
            <span class="sebu-challenge-mypage-stat__label">뱃지</span>
            <span class="sebu-challenge-mypage-stat__value"><?php echo number_format((int) ($summary['badge_count'] ?? 0)); ?></span>
        </div>
        <div class="sebu-challenge-mypage-stat">
            <span class="sebu-challenge-mypage-stat__label">우수글</span>
            <span class="sebu-challenge-mypage-stat__value"><?php echo number_format((int) ($summary['best_count'] ?? 0)); ?></span>
        </div>
    </section>

    <section class="sebu-challenge-mypage-section">
        <h2 class="sebu-challenge-mypage-section__title">내 인증글</h2>
        <?php if (empty($entries)) { ?>
        <p class="sebu-challenge-empty__desc">아직 참여한 챌린지가 없습니다.</p>
        <a href="<?php echo eottae_challenge_list_url(); ?>" class="sebu-challenge-btn sebu-challenge-btn--primary">챌린지 둘러보기</a>
        <?php } else { ?>
        <ul class="sebu-challenge-entry-grid">
            <?php foreach ($entries as $entry) {
                echo eottae_challenge_entry_card_html($entry);
            } ?>
        </ul>
        <?php } ?>
    </section>

    <?php if (!empty($rewards)) { ?>
    <section class="sebu-challenge-mypage-section">
        <h2 class="sebu-challenge-mypage-section__title">받은 보상</h2>
        <ul class="sebu-challenge-reward-list">
            <?php foreach ($rewards as $reward) { ?>
            <li class="sebu-challenge-reward-list__item">
                <span class="sebu-challenge-badge sebu-challenge-badge--reward"><?php echo get_text($reward['reward_label'] ?? ''); ?></span>
                <span><?php echo get_text($reward['challenge_title'] ?? ''); ?></span>
                <time><?php echo substr(get_text($reward['created_at'] ?? ''), 0, 10); ?></time>
            </li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>
</main>

<?php
g5_page_end();
