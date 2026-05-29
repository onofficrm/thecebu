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
        $latest = array_slice($latest, 0, 4);
        $monthly = eottae_column_get_monthly_columnist();
        $list_url = eottae_column_list_url();

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
                    </div>
                    <p class="sebu-column-home__desc">추천 컬럼, 많이 읽은 글, 새로 올라온 이야기를 한눈에 모았습니다.</p>
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
                    <ol class="sebu-column-home__list sebu-column-home__list--ranked">
                    <?php foreach ($popular as $idx => $post) { ?>
                    <li class="sebu-column-home__item" style="--rank: '<?php echo (int) $idx + 1; ?>'"><?php echo eottae_column_card_html($post, 'compact'); ?></li>
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
                        <ul class="sebu-column-home__list sebu-column-home__list--recent">
                            <?php foreach ($latest as $post) { ?>
                            <li class="sebu-column-home__item"><?php echo eottae_column_card_html($post, 'compact'); ?></li>
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
