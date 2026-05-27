<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_card_html')) {
    function eottae_column_card_html(array $post, $variant = 'default')
    {
        $variant = in_array($variant, array('default', 'featured', 'compact'), true) ? $variant : 'default';
        $badges = array();
        if (!empty($post['is_featured'])) {
            $badges[] = '<span class="sebu-column-badge sebu-column-badge--featured">추천</span>';
        }
        if (!empty($post['is_recommended'])) {
            $badges[] = '<span class="sebu-column-badge sebu-column-badge--popular">인기</span>';
        }

        ob_start();
        ?>
        <article class="sebu-column-card sebu-column-card--<?php echo $variant; ?>">
            <a href="<?php echo get_text($post['view_url'] ?? '#'); ?>" class="sebu-column-card__link">
                <div class="sebu-column-card__thumb" style="background-image:url('<?php echo get_text($post['thumbnail_url'] ?? ''); ?>')">
                    <?php if (!empty($post['category_label'])) { ?>
                    <span class="sebu-column-card__category"><?php echo get_text($post['category_label']); ?></span>
                    <?php } ?>
                </div>
                <div class="sebu-column-card__body">
                    <?php echo implode('', $badges); ?>
                    <h3 class="sebu-column-card__title"><?php echo get_text($post['wr_subject'] ?? ''); ?></h3>
                    <?php if (!empty($post['summary'])) { ?>
                    <p class="sebu-column-card__summary"><?php echo get_text($post['summary']); ?></p>
                    <?php } ?>
                    <div class="sebu-column-card__author">
                        <img src="<?php echo get_text($post['author_image_url'] ?? ''); ?>" alt="" class="sebu-column-card__avatar" width="32" height="32" loading="lazy">
                        <div class="sebu-column-card__author-meta">
                            <span class="sebu-column-card__author-name"><?php echo get_text($post['author_name'] ?? ''); ?></span>
                            <?php if (!empty($post['author_title'])) { ?>
                            <span class="sebu-column-card__author-title"><?php echo get_text($post['author_title']); ?></span>
                            <?php } ?>
                        </div>
                    </div>
                    <p class="sebu-column-card__stats">
                        조회 <?php echo number_format((int) ($post['wr_hit'] ?? 0)); ?>
                        · 댓글 <?php echo number_format((int) ($post['wr_comment'] ?? 0)); ?>
                        · 공감 <?php echo number_format((int) ($post['like_count'] ?? 0)); ?>
                        · <?php echo get_text($post['read_time_label'] ?? ''); ?>
                    </p>
                </div>
            </a>
        </article>
        <?php

        return (string) ob_get_clean();
    }
}
