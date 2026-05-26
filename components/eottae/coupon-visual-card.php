<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!isset($coupon) || !is_array($coupon)) {
    return;
}

$coupon_opts = isset($coupon_card_opts) && is_array($coupon_card_opts) ? $coupon_card_opts : array();
$present = function_exists('eottae_coupon_visual_present')
    ? eottae_coupon_visual_present($coupon, $coupon_opts)
    : array();

$ci_id = (int) ($coupon['ci_id'] ?? 0);
$is_business = isset($coupon['cp_type']) && $coupon['cp_type'] === 'business';
$is_used = !empty($present['is_used']);
$variant = preg_replace('/[^a-z0-9_-]/', '', (string) ($present['variant'] ?? 'general'));
$compact = !empty($present['compact_headline']);
$show_actions = !isset($coupon_opts['show_actions']) || !empty($coupon_opts['show_actions']);
$member_label = isset($coupon_opts['member_label']) ? (string) $coupon_opts['member_label'] : '';

$benefit_for_modal = $is_business && function_exists('eottae_business_coupon_format_benefit')
    ? eottae_business_coupon_format_benefit($coupon)
    : get_text($coupon['cp_desc'] ?? '');
?>

<article class="coupon-ticket coupon-ticket--<?php echo $variant; ?><?php echo $is_used ? ' coupon-ticket--used' : ''; ?><?php echo $compact ? ' coupon-ticket--compact-offer' : ''; ?>"
         data-coupon-id="<?php echo $ci_id; ?>"
         data-coupon-title="<?php echo htmlspecialchars(get_text($coupon['cp_title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
         data-coupon-benefit="<?php echo htmlspecialchars($benefit_for_modal, ENT_QUOTES, 'UTF-8'); ?>"
         data-coupon-code="<?php echo htmlspecialchars($present['code'], ENT_QUOTES, 'UTF-8'); ?>"
         data-coupon-member="<?php echo htmlspecialchars($member_label, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="coupon-ticket__visual" aria-hidden="false">
        <div class="coupon-ticket__pattern"></div>
        <div class="coupon-ticket__inner">
            <div class="coupon-ticket__top">
                <span class="coupon-ticket__brand">COUPON</span>
                <?php if ($present['badge'] !== '') { ?>
                <span class="coupon-ticket__badge"><?php echo get_text($present['badge']); ?></span>
                <?php } ?>
            </div>
            <p class="coupon-ticket__shop"><?php echo get_text($present['shop_name']); ?></p>
            <div class="coupon-ticket__offer">
                <?php if ($present['headline'] !== '') { ?>
                <span class="coupon-ticket__value"><?php echo get_text($present['headline']); ?></span>
                <?php } ?>
                <?php if ($present['headline_suffix'] !== '') { ?>
                <span class="coupon-ticket__unit"><?php echo get_text($present['headline_suffix']); ?></span>
                <?php } ?>
                <?php if ($present['benefit_label'] !== '') { ?>
                <span class="coupon-ticket__label"><?php echo get_text($present['benefit_label']); ?></span>
                <?php } ?>
            </div>
            <?php if ($present['detail_line'] !== '') { ?>
            <p class="coupon-ticket__detail"><?php echo get_text($present['detail_line']); ?></p>
            <?php } ?>
            <?php if ($present['code'] !== '') { ?>
            <p class="coupon-ticket__code"><span class="coupon-ticket__code-label">NO.</span> <strong><?php echo get_text($present['code']); ?></strong></p>
            <?php } ?>
            <?php if ($is_used) { ?>
            <span class="coupon-ticket__stamp">사용완료</span>
            <?php } ?>
        </div>
        <div class="coupon-ticket__notch coupon-ticket__notch--left" aria-hidden="true"></div>
        <div class="coupon-ticket__notch coupon-ticket__notch--right" aria-hidden="true"></div>
    </div>

    <?php if ($show_actions && !$is_used) { ?>
    <div class="coupon-ticket__actions">
        <?php if (isset($coupon_opts['meta_line']) && $coupon_opts['meta_line'] !== '') { ?>
        <p class="coupon-ticket__meta"><?php echo get_text($coupon_opts['meta_line']); ?></p>
        <?php } ?>
        <?php if ($is_business) { ?>
        <button type="button" class="coupon-ticket__btn coupon-ticket__btn--primary" data-coupon-show="<?php echo $ci_id; ?>"
            data-coupon-ci-id="<?php echo $ci_id; ?>">매장에서 보여주기</button>
        <?php } else { ?>
        <button type="button" class="coupon-ticket__btn" data-coupon-use="<?php echo $ci_id; ?>">사용 완료 처리</button>
        <?php } ?>
    </div>
    <?php } elseif (isset($coupon_opts['meta_line']) && $coupon_opts['meta_line'] !== '') { ?>
    <p class="coupon-ticket__meta coupon-ticket__meta--only"><?php echo get_text($coupon_opts['meta_line']); ?></p>
    <?php } ?>
</article>
