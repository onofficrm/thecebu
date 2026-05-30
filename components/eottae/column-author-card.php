<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_render_avatar_html')) {
    include_once G5_PATH.'/components/eottae/column-author-profile.php';
}

if (!function_exists('eottae_column_author_card_html')) {
    function eottae_column_author_card_html(array $author, $variant = 'default')
    {
        $stats = $author['stats'] ?? array();
        ob_start();
        ?>
        <article class="sebu-author-card sebu-author-card--<?php echo get_text($variant); ?>">
            <div class="sebu-author-card__layout">
                <div class="sebu-author-card__aside">
                    <?php echo eottae_column_render_avatar_html($author, 'md', 'sebu-author-card__avatar'); ?>
                    <a href="<?php echo get_text($author['profile_url'] ?? '#'); ?>" class="sebu-author-card__cta">프로필 보기</a>
                </div>
                <div class="sebu-author-card__body">
                    <?php if (!empty($author['grade_label'])) { ?>
                    <span class="sebu-author-card__grade"><?php echo get_text($author['grade_label']); ?></span>
                    <?php } ?>
                    <h3 class="sebu-author-card__name"><?php echo get_text($author['display_name'] ?? ''); ?></h3>
                    <?php if (!empty($author['title'])) { ?>
                    <p class="sebu-author-card__title"><?php echo get_text($author['title']); ?></p>
                    <?php } ?>
                    <?php if (!empty($author['bio'])) { ?>
                    <p class="sebu-author-card__bio"><?php echo get_text(cut_str($author['bio'], 80, '…')); ?></p>
                    <?php } ?>
                    <dl class="sebu-author-card__stats">
                        <div><dt>작성</dt><dd><?php echo number_format((int) ($stats['column_count'] ?? 0)); ?>개</dd></div>
                        <div><dt>조회</dt><dd><?php echo number_format((int) ($stats['total_views'] ?? 0)); ?></dd></div>
                        <div><dt>공감</dt><dd><?php echo number_format((int) ($stats['total_likes'] ?? 0)); ?></dd></div>
                    </dl>
                    <?php echo eottae_column_render_author_profile_link_badges_html($author, 'sebu-author-card__social sebu-column-social'); ?>
                </div>
            </div>
        </article>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_column_monthly_card_html')) {
    function eottae_column_monthly_card_html(array $monthly)
    {
        $author = $monthly['author'] ?? array();
        $award = $monthly['award'] ?? array();
        $stats = $monthly['month_stats'] ?? array();
        ob_start();
        ?>
        <article class="sebu-writer-monthly">
            <p class="sebu-writer-monthly__label">이달의 칼럼니스트</p>
            <a href="<?php echo get_text($author['profile_url'] ?? '#'); ?>" class="sebu-writer-monthly__link">
                <?php echo eottae_column_render_avatar_html($author, 'md', 'sebu-writer-monthly__avatar'); ?>
                <div class="sebu-writer-monthly__body">
                    <h3 class="sebu-writer-monthly__name"><?php echo get_text($author['display_name'] ?? ''); ?></h3>
                    <?php if (!empty($author['title'])) { ?>
                    <p class="sebu-writer-monthly__title"><?php echo get_text($author['title']); ?></p>
                    <?php } ?>
                    <?php if (!empty($award['reason'])) { ?>
                    <p class="sebu-writer-monthly__reason"><?php echo get_text($award['reason']); ?></p>
                    <?php } ?>
                    <p class="sebu-writer-monthly__stats">
                        이번 달 컬럼 <?php echo number_format((int) ($stats['column_count'] ?? 0)); ?>개
                        · 조회 <?php echo number_format((int) ($stats['total_views'] ?? 0)); ?>
                    </p>
                </div>
            </a>
            <?php if (!empty($monthly['representative_url'])) { ?>
            <a href="<?php echo get_text($monthly['representative_url']); ?>" class="sebu-writer-monthly__column-link">대표 컬럼 보기 →</a>
            <?php } ?>
        </article>
        <?php

        return (string) ob_get_clean();
    }
}
