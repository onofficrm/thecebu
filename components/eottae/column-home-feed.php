<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_home_feed_html')) {
    function eottae_column_home_thumb_html(array $post, $class = '')
    {
        $thumb_url = trim((string) ($post['thumbnail_url'] ?? ''));
        $has_thumb = $thumb_url !== '' && stripos($thumb_url, 'no_img') === false;
        $class = trim((string) $class);
        $class_attr = $class !== '' ? ' '.$class : '';

        ob_start();
        ?>
        <span class="sebu-column-home-thumb<?php echo $class_attr; ?><?php echo $has_thumb ? ' has-image' : ' is-empty'; ?>" aria-hidden="true">
            <?php if ($has_thumb) { ?>
            <img src="<?php echo get_text($thumb_url); ?>" alt="" loading="lazy" decoding="async">
            <?php } else { ?>
            <span class="sebu-column-home-thumb__pattern"></span>
            <?php } ?>
        </span>
        <?php

        return (string) ob_get_clean();
    }

    function eottae_column_home_meta_html(array $post, $class = '', array $parts_order = null)
    {
        $class = trim((string) $class);
        $class_attr = $class !== '' ? ' '.$class : '';
        $author = trim((string) ($post['author_name'] ?? ''));
        $read_time = trim((string) ($post['read_time_label'] ?? ''));
        $category = trim((string) ($post['category_label'] ?? ''));
        $date = trim((string) ($post['date_label'] ?? ''));

        if ($parts_order === null) {
            $parts = array_filter(array($category, $author, $read_time));
        } else {
            $map = array(
                'category'  => $category,
                'author'    => $author,
                'read_time' => $read_time,
                'date'      => $date,
            );
            $parts = array();
            foreach ($parts_order as $key) {
                if (!empty($map[$key])) {
                    $parts[] = $map[$key];
                }
            }
        }

        return $parts
            ? '<span class="sebu-column-home-meta'.$class_attr.'">'.get_text(implode(' · ', $parts)).'</span>'
            : '';
    }

    function eottae_column_home_rank_item_html(array $post, $rank)
    {
        $rank = max(1, (int) $rank);
        $is_first = $rank === 1;

        ob_start();
        ?>
        <li class="sebu-column-home-rank<?php echo $is_first ? ' sebu-column-home-rank--lead' : ' sebu-column-home-rank--plain'; ?>">
            <a href="<?php echo get_text($post['view_url'] ?? '#'); ?>" class="sebu-column-home-rank__link">
                <span class="sebu-column-home-rank__num"><?php echo sprintf('%02d', $rank); ?></span>
                <?php if ($is_first) { ?>
                <?php echo eottae_column_home_thumb_html($post, 'sebu-column-home-rank__thumb'); ?>
                <?php } ?>
                <span class="sebu-column-home-rank__body">
                    <strong class="sebu-column-home-rank__title"><?php echo get_text($post['wr_subject'] ?? ''); ?></strong>
                    <?php echo eottae_column_home_meta_html($post, 'sebu-column-home-rank__meta', array('category', 'author', 'read_time')); ?>
                </span>
            </a>
        </li>
        <?php

        return (string) ob_get_clean();
    }

    function eottae_column_home_recent_item_html(array $post)
    {
        ob_start();
        ?>
        <li class="sebu-column-home-recent">
            <a href="<?php echo get_text($post['view_url'] ?? '#'); ?>" class="sebu-column-home-recent__link">
                <?php echo eottae_column_home_thumb_html($post, 'sebu-column-home-recent__thumb'); ?>
                <span class="sebu-column-home-recent__body">
                    <strong class="sebu-column-home-recent__title"><?php echo get_text($post['wr_subject'] ?? ''); ?></strong>
                    <?php echo eottae_column_home_meta_html($post, 'sebu-column-home-recent__meta', array('author', 'date', 'read_time')); ?>
                </span>
            </a>
        </li>
        <?php

        return (string) ob_get_clean();
    }

    function eottae_column_home_feed_html()
    {
        global $is_member, $member, $is_admin;

        include_once G5_LIB_PATH.'/eottae-column.lib.php';
        include_once G5_LIB_PATH.'/eottae-column-likes.lib.php';
        include_once G5_PATH.'/components/eottae/column-card.php';
        include_once G5_PATH.'/components/eottae/column-author-card.php';

        eottae_column_ensure_schema();

        $featured = eottae_column_list(array('limit' => 1, 'featured_only' => true));
        if (empty($featured)) {
            $featured = eottae_column_list(array('limit' => 1, 'recommended_only' => true));
        }
        if (empty($featured)) {
            $featured = eottae_column_list(array('limit' => 1, 'sort' => 'popular'));
        }

        $exclude_ids = array();
        if (!empty($featured[0]['wr_id'])) {
            $exclude_ids[] = (int) $featured[0]['wr_id'];
        }

        $popular = eottae_column_list(array('limit' => 6, 'sort' => 'popular'));
        $popular = array_values(array_filter($popular, function ($post) use ($exclude_ids) {
            return !in_array((int) ($post['wr_id'] ?? 0), $exclude_ids, true);
        }));
        $popular = array_slice($popular, 0, 3);
        foreach ($popular as $post) {
            if (!empty($post['wr_id'])) {
                $exclude_ids[] = (int) $post['wr_id'];
            }
        }

        $latest = eottae_column_list(array('limit' => 8));
        $latest = array_values(array_filter($latest, function ($post) use ($exclude_ids) {
            return !in_array((int) ($post['wr_id'] ?? 0), $exclude_ids, true);
        }));
        $latest = array_slice($latest, 0, 6);
        $monthly = eottae_column_get_monthly_columnist();
        $list_url = eottae_column_list_url();
        $write_url = eottae_column_write_url();
        $can_write = !empty($is_member) && function_exists('eottae_column_can_write')
            ? eottae_column_can_write($member['mb_id'] ?? '', ($is_admin === 'super'))
            : false;

        if (empty($featured) && empty($popular) && empty($latest) && !$monthly) {
            return '';
        }

        ob_start();
        ?>
        <section class="sebu-column-home" id="eottae-home-column" aria-labelledby="sebu-column-home-title">
            <div class="sebu-column-home__inner">
                <header class="sebu-column-home__head">
                    <div class="sebu-column-home__copy">
                        <p class="sebu-column-home__eyebrow">Column · Editor's Picks</p>
                        <h2 class="sebu-column-home__title" id="sebu-column-home-title">세부 인사이트 컬럼</h2>
                        <p class="sebu-column-home__desc">대표 추천 · 인기 TOP 3 · 최신 글을 빠르게 훑어보세요.</p>
                    </div>
                    <div class="sebu-column-home__actions">
                        <?php if ($can_write) { ?>
                        <a href="<?php echo get_text($write_url); ?>" class="sebu-column-home__action sebu-column-home__action--primary">컬럼 작성</a>
                        <?php } ?>
                        <a href="<?php echo get_text($list_url); ?>" class="sebu-column-home__action">전체 보기</a>
                    </div>
                </header>

                <div class="sebu-column-home__layout">
                <?php if (!empty($featured[0])) { ?>
                <div class="sebu-column-home__featured">
                    <p class="sebu-column-home__featured-label">이번 주 추천 컬럼</p>
                    <?php echo eottae_column_card_html($featured[0], 'featured'); ?>
                </div>
                <?php } ?>

                <div class="sebu-column-home__side">
                <?php if (!empty($popular)) { ?>
                <div class="sebu-column-home__popular">
                    <p class="sebu-column-home__latest-label">인기 컬럼 TOP 3</p>
                    <ol class="sebu-column-home__rank-list">
                    <?php foreach ($popular as $idx => $post) { ?>
                    <?php echo eottae_column_home_rank_item_html($post, (int) $idx + 1); ?>
                    <?php } ?>
                    </ol>
                </div>
                <?php } ?>
                </div>
                </div>

                <?php if (!empty($latest) || $monthly) { ?>
                <div class="sebu-column-home__recent-block">
                    <div class="sebu-column-home__section-head">
                        <p class="sebu-column-home__latest-label">최근 컬럼</p>
                        <a href="<?php echo $list_url; ?>" class="sebu-column-home__section-link">더 보기</a>
                    </div>
                    <div class="sebu-column-home__recent-grid<?php echo $monthly ? ' has-monthly' : ''; ?>">
                        <?php if ($monthly) {
                            include_once G5_PATH.'/components/eottae/column-author-card.php';
                            ?>
                        <div class="sebu-column-home__monthly">
                            <?php echo eottae_column_monthly_card_html($monthly); ?>
                        </div>
                        <?php } ?>

                        <?php if (!empty($latest)) { ?>
                        <ul class="sebu-column-home__recent-list">
                            <?php foreach ($latest as $post) { ?>
                            <?php echo eottae_column_home_recent_item_html($post); ?>
                            <?php } ?>
                        </ul>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>

                <div class="sebu-column-home__footer">
                    <a href="<?php echo $list_url; ?>" class="sebu-column-home__more"><?php echo function_exists('eottae_column_menu_label') ? eottae_column_menu_label() : '컬럼'; ?> 전체 보기</a>
                </div>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
