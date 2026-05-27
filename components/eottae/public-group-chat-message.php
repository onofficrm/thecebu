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
            $item_class .= ' public-group-chat__message--ai public-ai-message is-talk-ai-message';
        }

        $author = $is_ai
            ? ($message['ai_display_name'] ?? $message['author'] ?? '어때봇 · AI 도우미')
            : ($message['author'] ?? '익명');
        $time_label = isset($message['time_label']) ? (string) $message['time_label'] : '';
        $text = isset($message['text']) ? (string) $message['text'] : '';
        $action_label = trim((string) ($message['action_label'] ?? ''));
        $action_url = trim((string) ($message['action_url'] ?? ''));
        $has_action = $action_label !== '' && $action_url !== '' && preg_match('#^https?://#i', $action_url);
        $poll_html = '';
        if (!empty($message['poll_options']) && function_exists('eottae_public_ai_poll_render_html')) {
            include_once G5_LIB_PATH.'/eottae-public-ai-poll.lib.php';
            $poll_html = eottae_public_ai_poll_render_html($message['poll_options']);
        } elseif (!empty($message['poll_options_raw'])) {
            include_once G5_LIB_PATH.'/eottae-public-ai-poll.lib.php';
            $poll_html = eottae_public_ai_poll_render_html($message['poll_options_raw']);
        }
        $can_delete = !empty($message['can_delete']);

        ob_start();
        ?>
        <article class="<?php echo $item_class; ?>" data-wr-id="<?php echo (int) $message['wr_id']; ?>">
            <div class="public-group-chat__message-inner">
                <?php if (!$is_mine) { ?>
                <div class="public-group-chat__meta">
                    <?php if ($is_ai) {
                        $ai_badge = eottae_talkroom_ai_message_render_badge($message, 'sm');
                        echo str_replace('talk-ai-msg__badge', 'talk-ai-msg__badge public-ai-badge', $ai_badge);
                    } else { ?>
                    <strong class="public-group-chat__author"><?php echo $author; ?></strong>
                    <?php } ?>
                    <?php if ($can_delete) { ?>
                    <button type="button" class="public-group-chat__delete" data-public-chat-delete="<?php echo (int) $message['wr_id']; ?>" aria-label="AI 메시지 삭제">삭제</button>
                    <?php } ?>
                </div>
                <?php } elseif ($can_delete) { ?>
                <div class="public-group-chat__meta public-group-chat__meta--actions">
                    <button type="button" class="public-group-chat__delete" data-public-chat-delete="<?php echo (int) $message['wr_id']; ?>" aria-label="AI 메시지 삭제">삭제</button>
                </div>
                <?php } ?>
                <div class="public-group-chat__bubble-row">
                    <?php if ($is_mine && $time_label !== '') { ?>
                    <time class="public-group-chat__time"><?php echo $time_label; ?></time>
                    <?php } ?>
                    <div class="public-group-chat__bubble">
                        <p class="public-group-chat__text"><?php echo nl2br(get_text($text)); ?></p>
                        <?php if ($poll_html !== '') { echo $poll_html; } ?>
                        <?php if ($has_action) { ?>
                        <p class="public-group-chat__action-wrap">
                            <a href="<?php echo htmlspecialchars($action_url, ENT_QUOTES, 'UTF-8'); ?>" class="public-group-chat__cta" target="_blank" rel="noopener noreferrer"><?php echo get_text($action_label); ?></a>
                        </p>
                        <?php } ?>
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
