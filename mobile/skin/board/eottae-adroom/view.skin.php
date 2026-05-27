<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-adroom.lib.php';
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$list_url = eottae_adroom_list_url();
$view_category = isset($view['ca_name']) ? get_text($view['ca_name']) : '';
$region = isset($view['wr_3']) ? get_text($view['wr_3']) : '';
$shop = eottae_adroom_shop_block($view);
$map_embed = '';
if (!empty($shop['lat']) || !empty($shop['lng']) || !empty($shop['address'])) {
    $map_embed = function_exists('eottae_shop_map_embed_url')
        ? eottae_shop_map_embed_url($shop['lat'] ?? '', $shop['lng'] ?? '', $shop['address'] ?? '')
        : '';
}
?>

<div class="adroom-view board-wrap board-wrap--eottae-adroom" id="bo_v" style="width:<?php echo $width; ?>">

    <header class="adroom-view__header">
        <a href="<?php echo $list_href ? $list_href : $list_url; ?>" class="adroom-view__back">← 광고방 목록</a>
        <div class="adroom-view__tags">
            <?php if ($view_category !== '') { ?>
            <span class="adroom-badge adroom-badge--cate"><?php echo $view_category; ?></span>
            <?php } ?>
            <?php if ($region !== '') { ?>
            <span class="adroom-badge adroom-badge--region"><?php echo $region; ?></span>
            <?php } ?>
        </div>
    </header>

    <article class="adroom-view__article">
        <h1 class="adroom-view__title"><?php echo get_text($view['wr_subject']); ?></h1>

        <div class="adroom-view__meta">
            <span class="adroom-view__author"><?php echo $view['name']; ?></span>
            <time datetime="<?php echo date('c', strtotime($view['wr_datetime'])); ?>"><?php echo date('Y.m.d H:i', strtotime($view['wr_datetime'])); ?></time>
            <span>조회 <?php echo number_format((int) $view['wr_hit']); ?></span>
        </div>

        <?php if (!empty($shop['name'])) { ?>
        <aside class="adroom-view__shop" aria-label="연동 업체">
            <div class="adroom-view__shop-main">
                <h2 class="adroom-view__shop-name"><?php echo get_text($shop['name']); ?></h2>
                <?php if (!empty($shop['region'])) { ?><p class="adroom-view__shop-region"><?php echo get_text($shop['region']); ?></p><?php } ?>
                <?php if (!empty($shop['address'])) { ?><p class="adroom-view__shop-addr"><?php echo get_text($shop['address']); ?></p><?php } ?>
            </div>
            <div class="adroom-view__shop-actions">
                <?php if (!empty($shop['view_url'])) { ?>
                <a href="<?php echo get_text($shop['view_url']); ?>" class="adroom-btn adroom-btn--outline">업체 상세</a>
                <?php } ?>
                <?php if (!empty($shop['map_url'])) { ?>
                <a href="<?php echo get_text($shop['map_url']); ?>" class="adroom-btn adroom-btn--primary" target="_blank" rel="noopener noreferrer">지도 길찾기</a>
                <?php } ?>
            </div>
            <?php if ($map_embed !== '') { ?>
            <div class="adroom-view__map">
                <iframe src="<?php echo htmlspecialchars($map_embed, ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo get_text($shop['name']); ?> 위치" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <?php } ?>
        </aside>
        <?php } ?>

        <section class="adroom-view__body" id="bo_v_con">
            <?php echo get_view_thumbnail($view['content']); ?>
        </section>
    </article>

    <?php include_once G5_BBS_PATH.'/view_comment.php'; ?>

    <footer class="adroom-view__footer">
        <ul class="board-actions btn_bo_user adroom-view__actions">
            <?php if ($list_href) { ?><li><a href="<?php echo $list_href; ?>" class="btn_b01 btn">목록</a></li><?php } ?>
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href; ?>" class="btn_b01 btn">수정</a></li><?php } ?>
            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href; ?>" class="btn_b01 btn" onclick="return confirm('삭제하시겠습니까?');">삭제</a></li><?php } ?>
        </ul>
    </footer>
</div>
