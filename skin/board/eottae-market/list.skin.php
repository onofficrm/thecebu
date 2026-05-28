<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-market.lib.php';
include_once G5_LIB_PATH.'/thumbnail.lib.php';
eottae_market_load_assets(false);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$write_label = '상품 등록';
$cebu_map_url = G5_URL.'/cebu-map/?type=market';
$cebu_map_near_url = $cebu_map_url.'&near=1';
?>

<div class="market-page board-wrap board-wrap--eottae-market" id="bo_list" style="width:<?php echo $width; ?>">
    <header class="market-hero">
        <div>
            <p class="market-hero__eyebrow">세부어때 중고장터</p>
            <h1 class="market-hero__title">중고장터</h1>
            <p class="market-hero__desc">세부에서 필요한 물건을 가까운 위치 기준으로 쉽고 빠르게 거래해보세요.</p>
        </div>
        <?php if ($write_href) { ?>
        <a href="<?php echo $write_href; ?>" class="market-hero__write"><?php echo $write_label; ?></a>
        <?php } ?>
    </header>

    <nav class="market-map-actions" aria-label="중고장터 지도 보기">
        <a href="<?php echo get_text($cebu_map_url); ?>" class="market-map-actions__btn market-map-actions__btn--primary">지도에서 보기</a>
        <a href="<?php echo get_text($cebu_map_near_url); ?>" class="market-map-actions__btn">내 주변 중고물품 보기</a>
    </nav>

    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
    <input type="hidden" name="stx" value="<?php echo $stx; ?>">
    <input type="hidden" name="spt" value="<?php echo $spt; ?>">
    <input type="hidden" name="sca" value="<?php echo $sca; ?>">
    <input type="hidden" name="sst" value="<?php echo $sst; ?>">
    <input type="hidden" name="sod" value="<?php echo $sod; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">

    <section class="market-grid" aria-label="중고장터 상품 목록">
        <?php for ($i = 0; $i < count($list); $i++) {
            $item = $list[$i];
            $thumb = eottae_market_thumb_url($bo_table, (int) $item['wr_id']);
            $status = eottae_market_normalize_status($item['wr_2'] ?? 'selling');
            $price = eottae_market_format_price($item['wr_1'] ?? 0);
            $region = eottae_market_region_label($item['wr_3'] ?? '');
            $location = get_text($item['wr_4'] ?? '');
            $offer = eottae_market_offer_label($item['wr_8'] ?? '0');
            $time_label = function_exists('eottae_community_relative_time')
                ? eottae_community_relative_time($item['wr_datetime'] ?? '')
                : date('Y.m.d', strtotime($item['wr_datetime'] ?? G5_TIME_YMDHIS));
            ?>
        <article class="market-card market-card--<?php echo $status; ?>">
            <a href="<?php echo $item['href']; ?>" class="market-card__link">
                <div class="market-card__thumb">
                    <?php if ($thumb !== '') { ?>
                    <img src="<?php echo htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy" decoding="async">
                    <?php } else { ?>
                    <span class="market-card__thumb-empty" aria-hidden="true">중고</span>
                    <?php } ?>
                    <?php echo eottae_market_render_status_badge($status); ?>
                </div>
                <div class="market-card__body">
                    <h2 class="market-card__title"><?php echo $item['subject']; ?></h2>
                    <p class="market-card__price"><?php echo $price; ?></p>
                    <p class="market-card__meta">
                        <span><?php echo get_text($region); ?></span>
                        <?php if ($location !== '') { ?><span><?php echo $location; ?></span><?php } ?>
                    </p>
                    <p class="market-card__foot">
                        <span><?php echo get_text($offer); ?></span>
                        <time datetime="<?php echo date('c', strtotime($item['wr_datetime'])); ?>"><?php echo get_text($time_label); ?></time>
                    </p>
                </div>
            </a>
        </article>
        <?php } ?>
    </section>

    <?php if (count($list) === 0) { ?>
    <div class="empty-state market-empty">
        <p class="empty-state__title">등록된 상품이 없습니다</p>
        <p>세부에서 거래할 첫 중고물품을 등록해보세요.</p>
        <?php if ($write_href) { ?><a href="<?php echo $write_href; ?>" class="market-hero__write market-hero__write--inline">상품 등록</a><?php } ?>
    </div>
    <?php } ?>

    <nav class="board-paging market-paging" aria-label="페이지"><?php echo $write_pages; ?></nav>
    </form>
</div>
