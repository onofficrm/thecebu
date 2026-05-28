<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 이벤트/프로모션 목록 카드
 *
 * @var array<string, mixed> $item
 * @var string $event_status
 * @var string $event_type
 * @var string $event_display_name
 * @var string $event_benefit
 * @var string $event_period_label
 * @var array<string, mixed>|null $event_shop
 */
$event_status = $event_status ?? 'active';
$event_type = $event_type ?? 'other';
$event_display_name = $event_display_name ?? '';
$event_benefit = $event_benefit ?? '';
$event_period_label = $event_period_label ?? '';
$event_shop = $event_shop ?? null;

$card_class = 'event-post';
if ($event_status === 'ended') {
    $card_class .= ' event-post--ended';
}
?>
<article class="<?php echo $card_class; ?>">
    <a href="<?php echo $item['href']; ?>" class="event-post__link">
        <div class="event-post__head">
            <?php echo eottae_event_render_status_badge($event_status); ?>
            <?php echo eottae_event_render_type_badge($event_type); ?>
        </div>
        <h2 class="event-post__title"><?php echo $item['subject']; ?></h2>
        <?php if ($event_display_name !== '') { ?>
        <p class="event-post__meta"><?php echo get_text($event_display_name); ?></p>
        <?php } ?>
        <?php if ($event_benefit !== '') { ?>
        <p class="event-post__benefit"><?php echo get_text($event_benefit); ?></p>
        <?php } ?>
        <p class="event-post__period"><?php echo get_text($event_period_label); ?></p>
    </a>
    <div class="event-post__actions">
        <?php if (is_array($event_shop) && !empty($event_shop['view_url'])) { ?>
        <a href="<?php echo get_text($event_shop['view_url']); ?>" class="event-post__btn event-post__btn--shop" onclick="event.stopPropagation();">업체정보 보기</a>
        <?php } ?>
        <a href="<?php echo $item['href']; ?>" class="event-post__btn event-post__btn--detail">상세보기</a>
    </div>
</article>
