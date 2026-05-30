<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$exclude_wr_id = isset($market_detail_sidebar_exclude_wr_id) ? (int) $market_detail_sidebar_exclude_wr_id : 0;
$featured = function_exists('eottae_market_recommended_items')
    ? eottae_market_recommended_items($exclude_wr_id, 3)
    : array();
?>

<div class="market-detail-sidebar community-sidebar" aria-label="추천 콘텐츠">
    <?php if (!empty($featured)) { ?>
    <section class="community-sidebar__card">
        <header class="community-sidebar__head">
            <span class="community-sidebar__icon community-sidebar__icon--market" aria-hidden="true">◫</span>
            <h2 class="community-sidebar__title">추천 물품</h2>
        </header>
        <ul class="community-sidebar__shops">
            <?php foreach ($featured as $item) {
                $thumb = !empty($item['thumb']) ? $item['thumb'] : '';
                ?>
            <li>
                <a href="<?php echo $item['url']; ?>" class="community-sidebar__shop">
                    <span class="community-sidebar__shop-thumb">
                        <?php if ($thumb) { ?>
                        <img src="<?php echo htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo get_text($item['subject']); ?>" loading="lazy">
                        <?php } ?>
                    </span>
                    <span class="community-sidebar__shop-body">
                        <span class="community-sidebar__shop-title-row">
                            <strong><?php echo get_text($item['subject']); ?></strong>
                            <span class="community-sidebar__shop-badges">
                                <span class="community-sidebar__shop-badge community-sidebar__shop-badge--price"><?php echo get_text($item['price']); ?></span>
                            </span>
                        </span>
                        <small><?php echo get_text($item['region']); ?> · <?php echo get_text($item['status']); ?></small>
                    </span>
                </a>
            </li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>

    <?php include G5_PATH.'/components/eottae/community-ad-carousel.php'; ?>
</div>
