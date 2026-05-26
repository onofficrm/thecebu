<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_member_growth_home_spotlight_html')) {
    function eottae_member_growth_home_spotlight_html()
    {
        include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
        include_once G5_PATH.'/components/eottae/member-growth-display.php';

        eottae_member_growth_ensure_schema();
        $spotlight = eottae_member_growth_weekly_spotlight_member();
        $recent_badges = eottae_member_growth_recent_badge_feed(4);
        $challenge_spotlight = eottae_member_growth_challenge_spotlight_list(3);
        $ranking_url = eottae_member_growth_ranking_url('week');
        $badge_book_url = eottae_member_growth_badge_book_url();

        if (empty($spotlight) && empty($recent_badges) && empty($challenge_spotlight)) {
            return '';
        }

        ob_start();
        ?>
        <section class="sebu-featured-member" id="eottae-member-spotlight" aria-labelledby="sebu-featured-member-title">
            <div class="sebu-featured-member__inner">
                <header class="sebu-featured-member__head">
                    <h2 class="sebu-featured-member__title" id="sebu-featured-member-title">이번 주 세부어때 회원</h2>
                    <p class="sebu-featured-member__desc">좋은 활동으로 커뮤니티를 밝혀 주신 분들을 소개합니다.</p>
                </header>

                <?php if (!empty($spotlight)) {
                    $profile = $spotlight['profile'] ?? array();
                    $is_auto = !empty($spotlight['is_auto_suggested']);
                    ?>
                <article class="sebu-featured-member__card">
                    <p class="sebu-featured-member__label"><?php echo $is_auto ? '이번 주 활발한 회원' : '이번 주 우수회원'; ?></p>
                    <div class="sebu-featured-member__who">
                        <a href="<?php echo get_text($spotlight['profile_url'] ?? '#'); ?>" class="sebu-featured-member__name">
                            <?php echo get_text($spotlight['display_nick'] ?? ''); ?>님
                        </a>
                        <?php if (!empty($profile['main_badge'])) {
                            echo eottae_member_growth_render_badge($profile['main_badge'], true);
                        } elseif (!empty($profile['level'])) {
                            echo eottae_member_growth_render_level_chip($profile['level']);
                        } ?>
                    </div>
                    <?php if (!empty($spotlight['intro_text'])) { ?>
                    <p class="sebu-featured-member__intro"><?php echo get_text($spotlight['intro_text']); ?></p>
                    <?php } ?>
                    <?php if (!empty($spotlight['activity_summary'])) { ?>
                    <p class="sebu-featured-member__summary"><?php echo get_text($spotlight['activity_summary']); ?></p>
                    <?php } ?>
                </article>
                <?php } ?>

                <?php if (!empty($challenge_spotlight)) { ?>
                <div class="sebu-featured-member__challenge">
                    <h3 class="sebu-featured-member__recent-title">챌린지 우수 참여</h3>
                    <ul class="sebu-featured-member__recent-list">
                        <?php foreach ($challenge_spotlight as $item) {
                            $cp = $item['profile'] ?? array();
                            ?>
                        <li>
                            <a href="<?php echo get_text($item['profile_url'] ?? '#'); ?>" class="sebu-featured-member__recent-item">
                                <span class="sebu-featured-member__recent-nick"><?php echo get_text($item['display_nick'] ?? ''); ?></span>
                                <span class="sebu-featured-member__recent-badge">
                                    🏆 이번 주 <?php echo (int) ($item['entry_count'] ?? 0); ?>회 참여
                                    <?php if (!empty($cp['main_badge']['badge_name'])) { ?>
                                    · <?php echo get_text($cp['main_badge']['badge_name']); ?>
                                    <?php } ?>
                                </span>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>

                <?php if (!empty($recent_badges)) { ?>
                <div class="sebu-featured-member__recent">
                    <h3 class="sebu-featured-member__recent-title">새 뱃지를 획득한 회원</h3>
                    <ul class="sebu-featured-member__recent-list">
                        <?php foreach ($recent_badges as $item) { ?>
                        <li>
                            <a href="<?php echo get_text($item['profile_url'] ?? '#'); ?>" class="sebu-featured-member__recent-item">
                                <span class="sebu-featured-member__recent-nick"><?php echo get_text($item['display_nick'] ?? ''); ?></span>
                                <span class="sebu-featured-member__recent-badge">
                                    <?php echo trim((string) ($item['badge_icon'] ?? '')); ?>
                                    <?php echo get_text($item['badge_name'] ?? ''); ?>
                                </span>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>

                <footer class="sebu-featured-member__footer">
                    <a href="<?php echo $ranking_url; ?>" class="sebu-featured-member__link">활동 랭킹 보기</a>
                    <a href="<?php echo $badge_book_url; ?>" class="sebu-featured-member__link">뱃지 도감</a>
                </footer>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
