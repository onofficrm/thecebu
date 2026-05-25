<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$popular = eottae_community_weekly_popular('', 5);
$featured = function_exists('eottae_api_get_featured_shops') ? eottae_api_get_featured_shops(3) : array();
if (empty($featured) && function_exists('eottae_shop_from_write')) {
    global $g5;
    $shop_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
    $result = sql_query(" select * from {$shop_table} where wr_is_comment = 0 order by wr_id desc limit 3 ");
    while ($row = sql_fetch_array($result)) {
        $shop_row = eottae_shop_from_write($row);
        $featured[] = array(
            'wr_id'        => (int) $shop_row['wr_id'],
            'name'         => $shop_row['name'],
            'category'     => $shop_row['category'],
            'region'       => $shop_row['region'],
            'review_count' => 0,
            'thumb'        => function_exists('eottae_api_shop_thumb') ? eottae_api_shop_thumb((int) $shop_row['wr_id']) : '',
            'url'          => G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE.'&wr_id='.$shop_row['wr_id'],
        );
    }
}
?>

<div class="shop-detail-sidebar community-sidebar" aria-label="추천 콘텐츠">
    <section class="community-sidebar__card">
        <header class="community-sidebar__head">
            <span class="community-sidebar__icon community-sidebar__icon--hot" aria-hidden="true">↗</span>
            <h2 class="community-sidebar__title">주간 인기글</h2>
        </header>
        <?php if (empty($popular)) { ?>
        <p class="community-sidebar__empty">이번 주 인기글이 없습니다.</p>
        <?php } else { ?>
        <ol class="community-sidebar__popular">
            <?php foreach ($popular as $idx => $post) { ?>
            <li class="community-sidebar__popular-item">
                <span class="community-sidebar__rank"><?php echo $idx + 1; ?></span>
                <div class="community-sidebar__popular-body">
                    <a href="<?php echo $post['url']; ?>" class="community-sidebar__popular-link"><?php echo $post['subject']; ?></a>
                    <p class="community-sidebar__popular-meta">
                        <span>조회 <?php echo number_format($post['hit']); ?></span>
                        <span class="community-sidebar__popular-comment"><?php echo number_format($post['comment']); ?></span>
                    </p>
                </div>
            </li>
            <?php } ?>
        </ol>
        <?php } ?>
    </section>

    <?php if (!empty($featured)) { ?>
    <section class="community-sidebar__card">
        <header class="community-sidebar__head">
            <span class="community-sidebar__icon community-sidebar__icon--shop" aria-hidden="true">⌂</span>
            <h2 class="community-sidebar__title">추천 업체</h2>
        </header>
        <ul class="community-sidebar__shops">
            <?php foreach ($featured as $shop_item) {
                $thumb = !empty($shop_item['thumb']) ? $shop_item['thumb'] : '';
                ?>
            <li>
                <a href="<?php echo $shop_item['url']; ?>" class="community-sidebar__shop">
                    <span class="community-sidebar__shop-thumb"<?php if ($thumb) { ?> style="background-image:url('<?php echo htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8'); ?>')"<?php } ?>></span>
                    <span class="community-sidebar__shop-body">
                        <strong><?php echo get_text($shop_item['name']); ?></strong>
                        <small><?php echo get_text($shop_item['region']); ?> · <?php echo get_text($shop_item['category']); ?></small>
                    </span>
                </a>
            </li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>

    <?php include G5_PATH.'/components/eottae/community-ad-carousel.php'; ?>
</div>
