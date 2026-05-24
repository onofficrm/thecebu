<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/thumbnail.lib.php');
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$shop = eottae_shop_from_write($view);
$share_url = G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$view['wr_id'];
$thumb = '';
if ($view['file']['count']) {
    $thumb = $view['file'][0]['path'].'/'.$view['file'][0]['file'];
}
?>

<article class="shop-detail-page board-wrap board-wrap--eottae-shop" id="bo_v" style="width:<?php echo $width; ?>">

    <?php if ($thumb) { ?>
    <div class="shop-detail-page__hero">
        <img src="<?php echo $thumb; ?>" alt="<?php echo $shop['name']; ?>">
    </div>
    <?php } ?>

    <h1 class="shop-detail-page__title"><?php echo get_text($view['wr_subject']); ?></h1>

    <div class="shop-detail-page__meta">
        <?php if ($shop['category']) { ?><span class="shop-detail-page__badge"><?php echo $shop['category']; ?></span><?php } ?>
        <?php if ($shop['region']) { ?><span class="shop-detail-page__badge"><?php echo $shop['region']; ?></span><?php } ?>
        <?php if ($shop['status']) { ?><span class="shop-detail-page__badge"><?php echo $shop['status']; ?></span><?php } ?>
    </div>

    <section class="shop-detail-page__info review-summary">
        <dl>
            <?php if ($shop['address']) { ?><dt>주소</dt><dd><?php echo $shop['address']; ?></dd><?php } ?>
            <?php if ($shop['phone']) { ?><dt>전화</dt><dd><a href="<?php echo eottae_tel_href($shop['phone']); ?>"><?php echo $shop['phone']; ?></a></dd><?php } ?>
            <?php if ($shop['hours']) { ?><dt>영업시간</dt><dd><?php echo $shop['hours']; ?></dd><?php } ?>
            <?php if ($shop['closed']) { ?><dt>휴무일</dt><dd><?php echo $shop['closed']; ?></dd><?php } ?>
            <?php if ($shop['website']) { ?><dt>홈페이지</dt><dd><a href="<?php echo $shop['website']; ?>" target="_blank" rel="noopener noreferrer">바로가기</a></dd><?php } ?>
            <?php if ($shop['sns']) { ?><dt>SNS</dt><dd><a href="<?php echo $shop['sns']; ?>" target="_blank" rel="noopener noreferrer">SNS</a></dd><?php } ?>
        </dl>
    </section>

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

    <section class="shop-detail-page__content" id="bo_v_con">
        <?php echo get_view_thumbnail($view['content']); ?>
    </section>

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

    <footer class="board-footer" style="margin-top:24px">
        <ul class="board-actions btn_bo_user">
            <?php if ($list_href) { ?><li><a href="<?php echo $list_href; ?>" class="btn_b01 btn">목록</a></li><?php } ?>
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href; ?>" class="btn_b01 btn">수정</a></li><?php } ?>
        </ul>
    </footer>
</article>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>
