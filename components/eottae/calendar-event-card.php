<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_calendar_event_card_html')) {
    function eottae_calendar_event_card_html(array $event, $variant = 'list', array $options = array())
    {
        if (empty($event['event_id'])) {
            return '';
        }

        $variant = $variant === 'month' ? 'month' : 'list';
        $href = (string) ($event['detail_href'] ?? '#');
        $show_report = !empty($options['show_report']);
        $member = isset($options['member']) && is_array($options['member']) ? $options['member'] : array();
        $is_admin = isset($options['is_admin']) ? $options['is_admin'] : '';

        ob_start();
        ?>
        <article class="sebu-cal-event-card sebu-cal-event-card--<?php echo $variant; ?>">
            <a href="<?php echo $href; ?>" class="sebu-cal-event-card__link">
                <div class="sebu-cal-event-card__head">
                    <div class="sebu-cal-event-card__badges">
                        <span class="sebu-cal-event-card__category <?php echo get_text($event['category_class'] ?? ''); ?>"><?php echo get_text($event['category_label'] ?? ''); ?></span>
                        <span class="sebu-cal-event-card__badge <?php echo get_text($event['badge_class'] ?? 'calendar-badge-default'); ?>"><?php echo get_text($event['badge_label'] ?? ''); ?></span>
                        <?php if (!empty($event['is_google'])) { ?>
                        <span class="sebu-cal-event-card__source">Google</span>
                        <?php } ?>
                    </div>
                    <h3 class="sebu-cal-event-card__title"><?php echo get_text($event['title'] ?? ''); ?></h3>
                </div>
                <div class="sebu-cal-event-card__meta">
                    <span class="sebu-cal-event-card__date"><?php echo get_text($event['date_label'] ?? ''); ?></span>
                    <?php if (!empty($event['time_label'])) { ?>
                    <span class="sebu-cal-event-card__time"><?php echo get_text($event['time_label']); ?></span>
                    <?php } ?>
                </div>
                <?php if (!empty($event['location']) || !empty($event['area_label'])) { ?>
                <p class="sebu-cal-event-card__location">
                    <?php if (!empty($event['area_label'])) { ?><span><?php echo get_text($event['area_label']); ?></span><?php } ?>
                    <?php if (!empty($event['location'])) { ?><span><?php echo get_text($event['location']); ?></span><?php } ?>
                </p>
                <?php } ?>
                <div class="sebu-cal-event-card__foot">
                    <span class="sebu-cal-event-card__writer"><?php echo get_text($event['writer_display'] ?? $event['writer_name'] ?? ''); ?></span>
                    <?php if (!empty($event['related_room_name'])) { ?>
                    <span class="sebu-cal-event-card__room"><?php echo get_text($event['related_room_name']); ?></span>
                    <?php } ?>
                </div>
            </a>
            <?php if ($show_report && function_exists('eottae_calendar_render_event_report_button')) {
                include_once G5_PATH.'/components/eottae/calendar-report.php';
                eottae_calendar_render_event_report_button($event, $member, $is_admin, 'card');
            } ?>
        </article>
        <?php

        return (string) ob_get_clean();
    }
}
