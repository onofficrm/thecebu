<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_render_my_section')) {
    function eottae_talkroom_render_my_section($title, array $rooms, $empty_message, $badge = '')
    {
        ?>
        <section class="talk-my-section">
            <h2 class="talk-my-section__title"><?php echo get_text($title); ?><?php if ($badge !== '') { ?> <span class="talk-my-section__count"><?php echo get_text($badge); ?></span><?php } ?></h2>
            <?php if (empty($rooms)) { ?>
            <p class="talk-my-section__empty"><?php echo get_text($empty_message); ?></p>
            <?php } else { ?>
            <div class="talk-my-grid">
                <?php foreach ($rooms as $room) {
                    eottae_talkroom_render_card($room);
                } ?>
            </div>
            <?php } ?>
        </section>
        <?php
    }
}
