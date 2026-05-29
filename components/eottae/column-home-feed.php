<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_home_feed_html')) {
    function eottae_column_home_feed_html()
    {
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

        $latest = eottae_column_list(array('limit' => 3));
        $monthly = eottae_column_get_monthly_columnist();
        $list_url = eottae_column_list_url();

        if (empty($featured) && empty($latest) && !$monthly) {
            return '';
        }

        ob_start();
        ?>
        <section class="sebu-column-home" id="eottae-home-column" aria-labelledby="sebu-column-home-title">
            <div class="sebu-column-home__inner">
                <header class="sebu-column-home__head">
                    <div class="sebu-column-home__copy">
                        <p class="sebu-column-home__eyebrow">Column · Latest Stories</p>
                        <h2 class="sebu-column-home__title" id="sebu-column-home-title"><?php echo function_exists('eottae_column_menu_label') ? eottae_column_menu_label() : '컬럼'; ?> 최신글</h2>
                    </div>
                    <p class="sebu-column-home__desc">세부 교민에게 필요한 생활정보와 현지 인사이트를 칼럼니스트가 직접 전합니다.</p>
                </header>

                <div class="sebu-column-home__layout">
                <?php if (!empty($featured[0])) { ?>
                <div class="sebu-column-home__featured">
                    <p class="sebu-column-home__featured-label">이번 주 추천 컬럼</p>
                    <?php echo eottae_column_card_html($featured[0], 'featured'); ?>
                </div>
                <?php } ?>

                <div class="sebu-column-home__side">
                <?php if ($monthly) {
                    include_once G5_PATH.'/components/eottae/column-author-card.php';
                    ?>
                <div class="sebu-column-home__monthly">
                    <?php echo eottae_column_monthly_card_html($monthly); ?>
                </div>
                <?php } ?>

                <?php if (!empty($latest)) { ?>
                <p class="sebu-column-home__latest-label">방금 올라온 컬럼</p>
                <ul class="sebu-column-home__list">
                    <?php foreach ($latest as $post) { ?>
                    <li class="sebu-column-home__item"><?php echo eottae_column_card_html($post, 'compact'); ?></li>
                    <?php } ?>
                </ul>
                <?php } ?>
                </div>
                </div>

                <div class="sebu-column-home__footer">
                    <a href="<?php echo $list_url; ?>" class="sebu-column-home__more"><?php echo function_exists('eottae_column_menu_label') ? eottae_column_menu_label() : '컬럼'; ?> 전체 보기</a>
                </div>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
