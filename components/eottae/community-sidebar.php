<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$popular = eottae_community_weekly_popular($bo_table, 5);
$featured = function_exists('eottae_api_get_featured_shops') ? eottae_api_get_featured_shops(3) : array();
if (empty($featured) && function_exists('eottae_shop_from_write')) {
    global $g5;
    $shop_table = $g5['write_prefix'].EOTTae_SHOP_TABLE;
    $result = sql_query(" select * from {$shop_table} where wr_is_comment = 0 order by wr_id desc limit 3 ");
    while ($row = sql_fetch_array($result)) {
        $shop = eottae_shop_from_write($row);
        $featured[] = array(
            'wr_id'        => (int) $shop['wr_id'],
            'name'         => $shop['name'],
            'category'     => $shop['category'],
            'region'       => $shop['region'],
            'review_count' => 0,
            'thumb'        => function_exists('eottae_api_shop_thumb') ? eottae_api_shop_thumb((int) $shop['wr_id']) : '',
            'url'          => G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE.'&wr_id='.$shop['wr_id'],
        );
    }
}
?>

<aside class="community-sidebar" aria-label="커뮤니티 사이드바">
    <?php include G5_PATH.'/components/eottae/community-login-box.php'; ?>
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

    <section class="community-sidebar__card">
        <header class="community-sidebar__head">
            <span class="community-sidebar__icon community-sidebar__icon--shop" aria-hidden="true">⌂</span>
            <h2 class="community-sidebar__title">커뮤니티 추천 업체</h2>
        </header>
        <?php if (empty($featured)) { ?>
        <p class="community-sidebar__empty">등록된 추천 업체가 없습니다.</p>
        <?php } else { ?>
        <ul class="community-sidebar__shops">
            <?php foreach ($featured as $shop) {
                $thumb = !empty($shop['thumb']) ? $shop['thumb'] : '';
                ?>
            <li>
                <a href="<?php echo $shop['url']; ?>" class="community-sidebar__shop">
                    <span class="community-sidebar__shop-thumb"<?php if ($thumb) { ?> style="background-image:url('<?php echo htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8'); ?>')"<?php } ?>></span>
                    <span class="community-sidebar__shop-body">
                        <strong><?php echo get_text($shop['name']); ?></strong>
                        <small><?php echo get_text($shop['region']); ?> · <?php echo get_text($shop['category']); ?></small>
                    </span>
                </a>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>

    <?php include G5_PATH.'/components/eottae/community-ad-carousel.php'; ?>
</aside>
