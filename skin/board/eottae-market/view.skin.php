<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-market.lib.php';
eottae_market_load_assets(true);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$market_status = eottae_market_normalize_status($view['wr_2'] ?? 'selling');
$market_region = eottae_market_region_label($view['wr_3'] ?? '');
$market_location = get_text($view['wr_4'] ?? '');
$market_lat = trim((string) ($view['wr_5'] ?? ''));
$market_lng = trim((string) ($view['wr_6'] ?? ''));
$market_contact = get_text($view['wr_7'] ?? '');
$market_offer = eottae_market_offer_label($view['wr_8'] ?? '0');
$market_map_show = (string) ($view['wr_9'] ?? '1') !== '0';
$market_can_change = eottae_market_can_change_status($view, $member['mb_id'] ?? '', !empty($is_admin));
$market_status_proc = G5_URL.'/proc/eottae-market-status.php';
$market_is_free = eottae_market_is_free_giveaway($view);
?>

<div class="market-view board-wrap board-wrap--eottae-market" id="bo_v" style="width:<?php echo $width; ?>">
    <header class="market-view__top">
        <a href="<?php echo $list_href ?: get_pretty_url($bo_table); ?>" class="market-view__back">← 목록으로</a>
    </header>

    <article class="market-view__article">
        <section class="market-view__gallery" aria-label="상품 사진">
            <?php
            $has_photo = false;
            for ($i = 0; $i < count($view['file']); $i++) {
                if (empty($view['file'][$i]['view'])) {
                    continue;
                }
                $has_photo = true;
                echo '<div class="market-view__photo">'.$view['file'][$i]['view'].'</div>';
            }
            if (!$has_photo) { ?>
            <div class="market-view__photo market-view__photo--empty">사진 없음</div>
            <?php } ?>
        </section>

        <section class="market-view__summary">
            <div class="market-view__badges">
                <?php echo eottae_market_render_status_badge($market_status, 'market-status-badge--view'); ?>
                <?php if ($market_is_free) { ?><span class="market-free-badge">무료나눔</span><?php } ?>
                <?php if (!$market_is_free) { ?><span class="market-offer-badge"><?php echo get_text($market_offer); ?></span><?php } ?>
            </div>
            <h1 class="market-view__title"><?php echo get_text($view['wr_subject']); ?></h1>
            <p class="market-view__price<?php echo $market_is_free ? ' market-view__price--free' : ''; ?>"><?php echo eottae_market_format_price($view['wr_1'] ?? 0, $view['wr_10'] ?? ''); ?></p>
            <dl class="market-view__meta">
                <div>
                    <dt>지역</dt>
                    <dd><?php echo get_text($market_region); ?></dd>
                </div>
                <div>
                    <dt>상세위치</dt>
                    <dd><?php echo $market_location; ?></dd>
                </div>
                <div>
                    <dt>연락방법</dt>
                    <dd><?php echo $market_contact; ?></dd>
                </div>
                <div>
                    <dt>등록일</dt>
                    <dd><?php echo date('Y.m.d H:i', strtotime($view['wr_datetime'])); ?></dd>
                </div>
            </dl>

            <?php if ($market_can_change) { ?>
            <div class="market-status-panel" data-market-status-panel data-proc-url="<?php echo $market_status_proc; ?>" data-bo-table="<?php echo get_text($bo_table); ?>" data-wr-id="<?php echo (int) $view['wr_id']; ?>">
                <span class="market-status-panel__label">거래상태 변경</span>
                <div class="market-status-panel__actions">
                    <?php foreach (eottae_market_statuses() as $key => $label) { ?>
                    <button type="button" class="market-status-panel__btn<?php echo $market_status === $key ? ' is-active' : ''; ?>" data-market-status="<?php echo get_text($key); ?>" aria-pressed="<?php echo $market_status === $key ? 'true' : 'false'; ?>"><?php echo get_text($label); ?></button>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </section>

        <section class="market-view__content" id="bo_v_con">
            <h2>상품설명</h2>
            <?php echo get_view_thumbnail($view['content']); ?>
        </section>

        <?php if ($market_map_show && $market_lat !== '' && $market_lng !== '' && is_numeric($market_lat) && is_numeric($market_lng)) {
            $shop_map = array(
                'address' => $market_location,
                'lat'     => $market_lat,
                'lng'     => $market_lng,
                'name'    => get_text($view['wr_subject']),
            );
            if (function_exists('eottae_enqueue_google_maps')) {
                eottae_enqueue_google_maps();
            }
            echo '<section class="market-view__map" aria-label="거래 위치"><h2>거래 위치</h2>';
            include G5_PATH.'/components/eottae/shop-detail-map.php';
            echo '</section>';
        } ?>
    </article>

    <footer class="market-view__footer">
        <ul class="board-actions btn_bo_user market-view__actions">
            <?php if ($list_href) { ?><li><a href="<?php echo $list_href; ?>" class="btn_b01 btn">목록</a></li><?php } ?>
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href; ?>" class="btn_b01 btn">수정</a></li><?php } ?>
            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href; ?>" class="btn_b01 btn" onclick="return confirm('삭제하시겠습니까?');">삭제</a></li><?php } ?>
        </ul>
    </footer>
</div>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>
<script>
$(function() {
    $(".market-view__gallery, .market-view__content").viewimageresize();
});
</script>
