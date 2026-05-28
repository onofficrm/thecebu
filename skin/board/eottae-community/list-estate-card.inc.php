<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 부동산 목록 — 에어비앤비 스타일 카드
 *
 * @var array<string, mixed> $item
 * @var string $item_bo_table
 */
if (!function_exists('eottae_estate_list_card_data')) {
    include_once G5_LIB_PATH.'/eottae-estate.lib.php';
}

$card = eottae_estate_list_card_data($item, $item_bo_table ?? '');
$initial = function_exists('eottae_estate_member_initial')
    ? eottae_estate_member_initial($card['author'])
    : '?';
$member_thumb = '';
if (!empty($card['mb_id']) && function_exists('eottae_estate_member_thumb_url')) {
    $member_thumb = eottae_estate_member_thumb_url($card['mb_id']);
}
?>
<article class="estate-list-card<?php echo ($card['deal_status'] ?? '') === 'completed' ? ' estate-list-card--completed' : ''; ?>">
    <a href="<?php echo get_text($card['href']); ?>" class="estate-list-card__link">
        <div class="estate-list-card__media">
            <?php if (!empty($card['thumb_url'])) { ?>
            <img
                src="<?php echo htmlspecialchars($card['thumb_url'], ENT_QUOTES, 'UTF-8'); ?>"
                alt=""
                class="estate-list-card__img"
                width="400"
                height="300"
                loading="lazy"
                decoding="async"
            >
            <?php } elseif ($member_thumb !== '') { ?>
            <img
                src="<?php echo htmlspecialchars($member_thumb, ENT_QUOTES, 'UTF-8'); ?>"
                alt=""
                class="estate-list-card__img estate-list-card__img--profile"
                width="400"
                height="300"
                loading="lazy"
                decoding="async"
            >
            <?php } else { ?>
            <span class="estate-list-card__placeholder" aria-hidden="true"><?php echo get_text($initial); ?></span>
            <?php } ?>
            <span class="estate-list-card__deal estate-deal-badge <?php echo get_text($card['deal_class']); ?>">
                <?php echo get_text($card['deal_label']); ?>
            </span>
            <?php if (!empty($card['has_map'])) { ?>
            <span class="estate-list-card__map-pin" aria-hidden="true" title="지도 위치 등록됨">📍</span>
            <?php } ?>
        </div>
        <div class="estate-list-card__body">
            <?php if ($card['price'] !== '') { ?>
            <p class="estate-list-card__price"><?php echo get_text($card['price']); ?></p>
            <?php } ?>
            <h2 class="estate-list-card__title"><?php echo get_text($card['title']); ?></h2>
            <?php if ($card['subtitle'] !== '') { ?>
            <p class="estate-list-card__subtitle"><?php echo get_text($card['subtitle']); ?></p>
            <?php } ?>
            <?php if ($card['meta'] !== '') { ?>
            <p class="estate-list-card__meta"><?php echo get_text($card['meta']); ?></p>
            <?php } ?>
            <p class="estate-list-card__foot">
                <span class="estate-list-card__author"><?php echo get_text($card['author']); ?></span>
                <?php if ($card['time_label'] !== '') { ?>
                <span class="estate-list-card__time"><?php echo get_text($card['time_label']); ?></span>
                <?php } ?>
            </p>
        </div>
    </a>
</article>
