<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_challenge_home_feed_html')) {
    function eottae_challenge_home_feed_html($limit = 3)
    {
        include_once G5_LIB_PATH.'/eottae-challenge.lib.php';

        eottae_challenge_ensure_schema();
        $challenges = eottae_challenge_get_active_featured((int) $limit);
        $list_url = eottae_challenge_list_url();

        ob_start();
        ?>
        <section class="sebu-challenge-home" id="eottae-home-challenge" aria-labelledby="sebu-challenge-home-title">
            <div class="sebu-challenge-home__inner">
                <header class="sebu-challenge-home__head">
                    <h2 class="sebu-challenge-home__title" id="sebu-challenge-home-title">이번 주 세부어때 챌린지</h2>
                    <p class="sebu-challenge-home__desc">사진 1장과 한 줄 후기만 올리면 참여 완료!</p>
                </header>

                <?php if (empty($challenges)) { ?>
                <p class="sebu-challenge-home__empty">진행 중인 챌린지가 없습니다.</p>
                <?php } else { ?>
                <ul class="sebu-challenge-home__list">
                    <?php foreach ($challenges as $challenge) {
                        $status = (string) ($challenge['display_status'] ?? 'active');
                        if ($status !== 'active') {
                            continue;
                        }
                        ?>
                    <li class="sebu-challenge-home__item">
                        <article class="sebu-challenge-home__card">
                            <span class="sebu-challenge-home__icon" aria-hidden="true"><?php echo get_text($challenge['icon_display'] ?? '🏆'); ?></span>
                            <div class="sebu-challenge-home__body">
                                <h3 class="sebu-challenge-home__challenge-title">
                                    <a href="<?php echo get_text($challenge['view_url'] ?? '#'); ?>"><?php echo get_text($challenge['title'] ?? ''); ?></a>
                                </h3>
                                <p class="sebu-challenge-home__stats">
                                    참여 <?php echo number_format((int) ($challenge['participant_count'] ?? 0)); ?>명
                                    · 인증 <?php echo number_format((int) ($challenge['entry_count'] ?? 0)); ?>개
                                </p>
                                <a href="<?php echo get_text($challenge['write_url'] ?? $challenge['view_url'] ?? '#'); ?>" class="sebu-challenge-btn sebu-challenge-btn--primary sebu-challenge-btn--sm">참여하기</a>
                            </div>
                        </article>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>

                <div class="sebu-challenge-home__footer">
                    <a href="<?php echo $list_url; ?>" class="sebu-challenge-home__more">챌린지 전체 보기</a>
                </div>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
