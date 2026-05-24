<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_ad_get_active')) {
    include_once G5_LIB_PATH.'/eottae-ad.lib.php';
}

$eottae_sidebar_ads = eottae_ad_get_active(EOTTae_AD_SLOT_SIDEBAR, 10);
$eottae_shop_write_url = function_exists('eottae_shop_write_url') ? eottae_shop_write_url() : G5_BBS_URL.'/write.php?bo_table='.EOTTae_SHOP_TABLE;
?>

<section class="community-ad-carousel" data-ad-carousel aria-label="추천 광고">
    <?php if (!empty($eottae_sidebar_ads)) { ?>
    <div class="community-ad-carousel__viewport">
        <div class="community-ad-carousel__track" data-ad-track>
            <?php foreach ($eottae_sidebar_ads as $idx => $ad) {
                $href = $ad['link'] !== '' ? $ad['link'] : ($ad['shop_url'] !== '' ? $ad['shop_url'] : '#');
                $target = preg_match('#^https?://#', $href) ? ' target="_blank" rel="noopener noreferrer"' : '';
                $bg = $ad['image'] !== '' ? $ad['image'] : '';
                ?>
            <article class="community-ad-carousel__slide<?php echo $idx === 0 ? ' is-active' : ''; ?>" data-ad-slide="<?php echo (int) $idx; ?>">
                <a href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>" class="community-ad-carousel__card"<?php echo $target; ?><?php if ($bg !== '') { ?> style="--ad-bg-image: url('<?php echo htmlspecialchars($bg, ENT_QUOTES, 'UTF-8'); ?>')"<?php } ?>>
                    <span class="community-ad-carousel__bg" aria-hidden="true"></span>
                    <span class="community-ad-carousel__overlay" aria-hidden="true"></span>
                    <span class="community-ad-carousel__body">
                        <h3 class="community-ad-carousel__title"><?php echo $ad['title']; ?></h3>
                        <?php if ($ad['subtitle'] !== '') { ?>
                        <p class="community-ad-carousel__subtitle"><?php echo $ad['subtitle']; ?></p>
                        <?php } ?>
                        <span class="community-ad-carousel__cta"><?php echo $ad['cta']; ?></span>
                    </span>
                    <span class="community-ad-carousel__tag">AD</span>
                </a>
            </article>
            <?php } ?>
        </div>
    </div>
    <?php if (count($eottae_sidebar_ads) > 1) { ?>
    <div class="community-ad-carousel__dots" data-ad-dots role="tablist" aria-label="광고 슬라이드">
        <?php foreach ($eottae_sidebar_ads as $idx => $ad) { ?>
        <button type="button" class="community-ad-carousel__dot<?php echo $idx === 0 ? ' is-active' : ''; ?>" data-ad-dot="<?php echo (int) $idx; ?>" aria-label="<?php echo (int) ($idx + 1); ?>번 광고" aria-selected="<?php echo $idx === 0 ? 'true' : 'false'; ?>"></button>
        <?php } ?>
    </div>
    <?php } ?>
    <?php } else { ?>
    <div class="community-ad-carousel__fallback community-sidebar__ad">
        <p class="community-sidebar__ad-kicker">사장님, 가게 홍보가 필요하신가요?</p>
        <p class="community-sidebar__ad-text">업체 등록 후 관리자 승인 시 이 자리에 광고가 노출됩니다.</p>
        <a href="<?php echo $eottae_shop_write_url; ?>" class="community-sidebar__ad-btn">무료 업소등록</a>
        <span class="community-sidebar__ad-tag">AD</span>
    </div>
    <?php } ?>
</section>
