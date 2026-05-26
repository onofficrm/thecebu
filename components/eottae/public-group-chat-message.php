<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_group_chat_message_html')) {
    /**
     * @param array<string, mixed> $message
     */
    function eottae_public_group_chat_message_html(array $message)
    {
        if (empty($message['wr_id'])) {
            return '';
        }

        include_once G5_PATH.'/components/eottae/talk-ai-message-ui.php';

        $is_mine = !empty($message['is_mine']);
        $is_ai = !empty($message['is_ai']);
        $item_class = 'public-group-chat__message';
        if ($is_mine) {
            $item_class .= ' public-group-chat__message--mine';
        }
        if ($is_ai) {
            $item_class .= ' public-group-chat__message--ai is-talk-ai-message';
        }

        $author = $is_ai
            ? ($message['ai_display_name'] ?? $message['author'] ?? '어때봇 · AI 도우미')
            : ($message['author'] ?? '익명');
        $time_label = isset($message['time_label']) ? (string) $message['time_label'] : '';
        $text = isset($message['text']) ? (string) $message['text'] : '';

        ob_start();
        ?>
        <article class="<?php echo $item_class; ?>" data-wr-id="<?php echo (int) $message['wr_id']; ?>">
            <div class="public-group-chat__message-inner">
                <?php if (!$is_mine) { ?>
                <div class="public-group-chat__meta">
                    <?php if ($is_ai) { ?>
                    <?php echo eottae_talkroom_ai_message_render_badge($message, 'sm'); ?>
                    <?php } else { ?>
                    <strong class="public-group-chat__author"><?php echo $author; ?></strong>
                    <?php } ?>
                </div>
                <?php } ?>
                <div class="public-group-chat__bubble-row">
                    <?php if ($is_mine && $time_label !== '') { ?>
                    <time class="public-group-chat__time"><?php echo $time_label; ?></time>
                    <?php } ?>
                    <div class="public-group-chat__bubble">
                        <p class="public-group-chat__text"><?php echo nl2br(get_text($text)); ?></p>
                    </div>
                    <?php if (!$is_mine && $time_label !== '') { ?>
                    <time class="public-group-chat__time"><?php echo $time_label; ?></time>
                    <?php } ?>
                </div>
            </div>
        </article>
        <?php

        return (string) ob_get_clean();
    }
}
