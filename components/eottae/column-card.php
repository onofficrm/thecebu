<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_card_html')) {
    function eottae_column_card_html(array $post, $variant = 'default')
    {
        $variant = in_array($variant, array('default', 'featured', 'compact'), true) ? $variant : 'default';
        $thumb_url = trim((string) ($post['thumbnail_url'] ?? ''));
        $has_thumb = $thumb_url !== '' && stripos($thumb_url, 'no_img') === false;
        $badges = array();
        if (!empty($post['is_featured'])) {
            $badges[] = '<span class="sebu-column-badge sebu-column-badge--featured">Editor\'s Pick</span>';
        }
        if (!empty($post['is_recommended'])) {
            $badges[] = '<span class="sebu-column-badge sebu-column-badge--popular">Trending</span>';
        }

        ob_start();
        ?>
        <article class="sebu-column-card sebu-column-card--<?php echo $variant; ?><?php echo $has_thumb ? '' : ' is-no-thumb'; ?>">
            <a href="<?php echo get_text($post['view_url'] ?? '#'); ?>" class="sebu-column-card__link">
                <div class="sebu-column-card__media">
                    <div class="sebu-column-card__thumb"<?php echo $has_thumb ? ' style="background-image:url(\''.get_text($thumb_url).'\')"' : ''; ?>>
                        <?php if (!$has_thumb) { ?>
                        <span class="sebu-column-card__thumb-pattern" aria-hidden="true"></span>
                        <?php } ?>
                        <div class="sebu-column-card__thumb-shade" aria-hidden="true"></div>
                        <?php if (!empty($post['category_label'])) { ?>
                        <span class="sebu-column-card__category"><?php echo get_text($post['category_label']); ?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="sebu-column-card__body">
                    <div class="sebu-column-card__badges"><?php echo implode('', $badges); ?></div>
                    <h3 class="sebu-column-card__title"><?php echo get_text($post['wr_subject'] ?? ''); ?></h3>
                    <?php if (!empty($post['summary'])) { ?>
                    <p class="sebu-column-card__summary"><?php echo get_text($post['summary']); ?></p>
                    <?php } ?>
                    <div class="sebu-column-card__footer">
                        <div class="sebu-column-card__author">
                            <?php if (!empty($post['author_image_url']) && stripos($post['author_image_url'], 'no_img') === false) { ?>
                            <img src="<?php echo get_text($post['author_image_url']); ?>" alt="" class="sebu-column-card__avatar" width="36" height="36" loading="lazy">
                            <?php } else { ?>
                            <?php
                            $initial = (string) ($post['author_name'] ?? 'C');
                            $initial = function_exists('mb_substr') ? mb_substr($initial, 0, 1, 'UTF-8') : substr($initial, 0, 1);
                            ?>
                            <span class="sebu-column-card__avatar sebu-column-card__avatar--initial" aria-hidden="true"><?php echo get_text($initial); ?></span>
                            <?php } ?>
                            <div class="sebu-column-card__author-meta">
                                <span class="sebu-column-card__author-name"><?php echo get_text($post['author_name'] ?? ''); ?></span>
                                <?php if (!empty($post['author_title'])) { ?>
                                <span class="sebu-column-card__author-title"><?php echo get_text($post['author_title']); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                        <span class="sebu-column-card__read">
                            <?php echo get_text($post['read_time_label'] ?? '읽기'); ?>
                            <span class="sebu-column-card__read-arrow" aria-hidden="true">→</span>
                        </span>
                    </div>
                </div>
            </a>
        </article>
        <?php

        return (string) ob_get_clean();
    }
}
