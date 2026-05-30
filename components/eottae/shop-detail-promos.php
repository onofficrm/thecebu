<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 업체 상세 — 한 줄 소개, 연결 이벤트, 사업자 쿠폰
 *
 * @var array<string, mixed> $shop
 * @var array<string, mixed> $view
 * @var string $bo_table
 */

$shop_intro = '';
if (function_exists('eottae_shop_seo_get')) {
    $seo = eottae_shop_seo_get($bo_table, (int) ($view['wr_id'] ?? 0));
    $shop_intro = trim(get_text($seo['meta_intro'] ?? ''));
}

$shop_events_text = function_exists('eottae_translation_shop_events_text')
    ? eottae_translation_shop_events_text($bo_table, (int) ($view['wr_id'] ?? 0))
    : '';

$shop_coupons_text = function_exists('eottae_translation_shop_coupons_text')
    ? eottae_translation_shop_coupons_text($view, $bo_table)
    : '';

if ($shop_intro === '' && $shop_events_text === '' && $shop_coupons_text === '') {
    return;
}
?>

<section class="shop-detail-page__promos" aria-label="프로모션 정보">
    <?php if ($shop_intro !== '') { ?>
    <div class="shop-detail-page__promo shop-detail-page__promo--intro">
        <p class="shop-detail-page__intro" data-translation-extra="intro"><?php echo $shop_intro; ?></p>
    </div>
    <?php } ?>

    <?php if ($shop_events_text !== '') { ?>
    <div class="shop-detail-page__promo shop-detail-page__promo--events">
        <h2 class="shop-detail-page__promo-title">이벤트 · 프로모션</h2>
        <div class="shop-detail-page__promo-body shop-detail-page__promo-body--pre" data-translation-extra="events"><?php echo get_text($shop_events_text); ?></div>
    </div>
    <?php } ?>

    <?php if ($shop_coupons_text !== '') { ?>
    <div class="shop-detail-page__promo shop-detail-page__promo--coupons">
        <h2 class="shop-detail-page__promo-title">쿠폰</h2>
        <div class="shop-detail-page__promo-body shop-detail-page__promo-body--pre" data-translation-extra="coupons"><?php echo get_text($shop_coupons_text); ?></div>
    </div>
    <?php } ?>
</section>
