<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/thumbnail.lib.php');
include_once(G5_LIB_PATH.'/eottae.lib.php');
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$shop = eottae_shop_from_write($view);
eottae_enqueue_google_maps();
eottae_track_recent_shop($view['wr_id']);
$shop_is_saved = $is_member && eottae_is_shop_saved($member['mb_id'], $view['wr_id']);
$share_url = G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$view['wr_id'];
$gallery = eottae_shop_gallery_images($view);
$summary = eottae_get_shop_review_summary($view['wr_id']);
$flags = eottae_shop_detail_flags($shop, $summary);
$shop_map = array(
    'name'    => $shop['name'],
    'region'  => $shop['region'],
    'address' => $shop['address'],
    'lat'     => $shop['lat'],
    'lng'     => $shop['lng'],
);
$is_ad = isset($view['wr_link2']) && stripos((string) $view['wr_link2'], 'ad') !== false;
?>

<article class="shop-detail-page board-wrap board-wrap--eottae-shop" id="bo_v" style="width:<?php echo $width; ?>">

    <header class="shop-detail-page__topbar">
        <a href="<?php echo $list_href ? $list_href : eottae_shop_list_url(); ?>" class="shop-detail-page__back">← 내주변 목록</a>
        <span class="shop-detail-page__save-wrap"><?php eottae_render_shop_save_button($view['wr_id'], $shop_is_saved); ?></span>
    </header>

    <div class="shop-detail-page__layout">
        <main class="shop-detail-page__main">

            <?php if (!empty($gallery)) { ?>
            <div class="shop-detail-page__gallery">
                <div class="shop-detail-page__hero">
                    <img src="<?php echo $gallery[0]['src']; ?>" alt="<?php echo $shop['name']; ?>" id="shopDetailHeroImg">
                    <?php if ($is_ad) { ?>
                    <span class="shop-detail-page__flag shop-detail-page__flag--ad">광고</span>
                    <?php } elseif ($flags['recommended']) { ?>
                    <span class="shop-detail-page__flag shop-detail-page__flag--pick">추천</span>
                    <?php } ?>
                </div>
                <?php if (count($gallery) > 1) { ?>
                <div class="shop-detail-page__thumbs">
                    <?php foreach ($gallery as $img) { ?>
                    <button type="button" class="shop-detail-page__thumb" data-gallery-src="<?php echo htmlspecialchars($img['src'], ENT_QUOTES, 'UTF-8'); ?>">
                        <img src="<?php echo $img['src']; ?>" alt="">
                    </button>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
            <?php } ?>

            <div class="shop-detail-page__head">
                <div class="shop-detail-page__tags">
                    <?php if ($shop['category']) { ?><span class="shop-detail-page__badge"><?php echo $shop['category']; ?></span><?php } ?>
                    <?php if ($shop['region']) { ?><span class="shop-detail-page__badge shop-detail-page__badge--muted"><?php echo $shop['region']; ?></span><?php } ?>
                    <?php if ($shop['status']) { ?><span class="shop-detail-page__badge shop-detail-page__badge--status"><?php echo $shop['status']; ?></span><?php } ?>
                </div>
                <h1 class="shop-detail-page__title"><?php echo get_text($view['wr_subject']); ?></h1>
                <p class="shop-detail-page__rating">
                    <span class="shop-detail-page__stars">★ <?php echo number_format($summary['average'], 1); ?></span>
                    <span class="shop-detail-page__reviews">리뷰 <?php echo number_format($summary['count']); ?></span>
                    <a href="#shop-reviews" class="shop-detail-page__review-link">리뷰 보기</a>
                </p>
            </div>

            <section class="shop-detail-page__content" id="bo_v_con">
                <?php echo get_view_thumbnail($view['content']); ?>
            </section>

            <?php eottae_render_review_section($view['wr_id'], $view['wr_subject']); ?>
        </main>

        <aside class="shop-detail-page__aside">
            <section class="shop-detail-page__info">
                <h2 class="shop-detail-page__info-title">업체 정보</h2>
                <dl class="shop-detail-page__info-list">
                    <?php if ($shop['address']) { ?><div><dt>주소</dt><dd><?php echo $shop['address']; ?></dd></div><?php } ?>
                    <?php if ($shop['phone']) { ?><div><dt>전화</dt><dd><a href="<?php echo eottae_tel_href($shop['phone']); ?>"><?php echo $shop['phone']; ?></a></dd></div><?php } ?>
                    <?php if ($shop['hours']) { ?><div><dt>영업시간</dt><dd><?php echo $shop['hours']; ?></dd></div><?php } ?>
                    <?php if ($shop['closed']) { ?><div><dt>휴무일</dt><dd><?php echo $shop['closed']; ?></dd></div><?php } ?>
                    <?php if ($shop['website']) { ?><div><dt>홈페이지</dt><dd><a href="<?php echo $shop['website']; ?>" target="_blank" rel="noopener noreferrer">바로가기</a></dd></div><?php } ?>
                    <?php if ($shop['sns'] && stripos($shop['sns'], 'ad') === false) { ?><div><dt>SNS</dt><dd><a href="<?php echo $shop['sns']; ?>" target="_blank" rel="noopener noreferrer">SNS</a></dd></div><?php } ?>
                </dl>
            </section>

            <?php
            eottae_load_component('shop-detail-map');
            include G5_PATH.'/components/eottae/shop-detail-map.php';
            ?>

            <?php
            eottae_render_inquiry_buttons('detail', array(
                'phone'         => $shop['phone'],
                'inquiry_code'  => $shop['inquiry_code'],
                'lat'           => $shop['lat'],
                'lng'           => $shop['lng'],
                'address'       => $shop['address'],
                'share_url'     => $share_url,
            ));
            ?>
        </aside>
    </div>

    <?php
    eottae_render_inquiry_buttons('mobile-bar', array(
        'phone'         => $shop['phone'],
        'inquiry_code'  => $shop['inquiry_code'],
        'lat'           => $shop['lat'],
        'lng'           => $shop['lng'],
        'address'       => $shop['address'],
        'share_url'     => $share_url,
    ));
    ?>

    <footer class="shop-detail-page__footer">
        <ul class="board-actions btn_bo_user">
            <?php if ($list_href) { ?><li><a href="<?php echo $list_href; ?>" class="btn_b01 btn">목록</a></li><?php } ?>
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href; ?>" class="btn_b01 btn">수정</a></li><?php } ?>
        </ul>
    </footer>
</article>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>
