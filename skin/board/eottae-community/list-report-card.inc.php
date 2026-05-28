<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * @var array<string, mixed> $item
 */
if (!function_exists('eottae_report_list_card_data')) {
    include_once G5_LIB_PATH.'/eottae-report.lib.php';
}

$card = eottae_report_list_card_data($item, $item_bo_table ?? '');
$card_class = 'report-card';
if ($card['status'] === 'rejected') {
    $card_class .= ' report-card--rejected';
}
?>
<article class="<?php echo $card_class; ?>">
    <a href="<?php echo $card['href']; ?>" class="report-card__link">
        <div class="report-card__head">
            <?php echo eottae_report_render_status_badge($card['status']); ?>
            <?php echo eottae_report_render_type_badge($card['type']); ?>
        </div>
        <h2 class="report-card__title"><?php echo $card['subject']; ?></h2>
        <p class="report-card__meta">
            <span class="report-card__meta-item"><?php echo get_text($card['region']); ?></span>
            <span class="report-card__meta-item"><?php echo get_text($card['author']); ?></span>
            <?php if ($card['time_label'] !== '') { ?>
            <span class="report-card__meta-item"><?php echo get_text($card['time_label']); ?></span>
            <?php } ?>
            <?php if ($card['has_photo']) { ?>
            <span class="report-card__meta-item report-card__photo">📷 사진</span>
            <?php } ?>
            <?php if ($card['shop_name'] !== '') { ?>
            <span class="report-card__meta-item report-card__meta-item--shop"><?php echo $card['shop_name']; ?></span>
            <?php } ?>
        </p>
    </a>
</article>
