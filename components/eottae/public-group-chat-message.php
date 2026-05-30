<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_group_chat_member_initial')) {
    function eottae_public_group_chat_member_initial($name)
    {
        $name = trim(strip_tags((string) $name));
        if ($name === '') {
            return '?';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($name, 0, 1, 'UTF-8');
        }

        return substr($name, 0, 1);
    }
}

if (!function_exists('eottae_public_group_chat_attach_profile_fields')) {
    /**
     * @param array<string, mixed> $message
     * @return array<string, mixed>
     */
    function eottae_public_group_chat_attach_profile_fields(array $message)
    {
        if (!empty($message['is_mine'])) {
            return $message;
        }

        if (!empty($message['is_ai'])) {
            $message['profile_avatar_kind'] = 'ai';

            return $message;
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($message['mb_id'] ?? ''));
        if ($mb_id === '') {
            $message['profile_avatar_kind'] = 'guest';
            $message['profile_initial'] = eottae_public_group_chat_member_initial($message['author'] ?? '');

            return $message;
        }

        if (!function_exists('eottae_member_growth_profile_url')) {
            include_once G5_LIB_PATH.'/eottae-member-growth-social.lib.php';
        }

        $message['profile_url'] = function_exists('eottae_member_growth_profile_url')
            ? eottae_member_growth_profile_url($mb_id)
            : '';
        $message['profile_image_url'] = '';

        if (!function_exists('eottae_member_profile_image_url')) {
            include_once G5_LIB_PATH.'/eottae-member-profile.lib.php';
        }
        if (function_exists('eottae_member_profile_image_url')) {
            $message['profile_image_url'] = eottae_member_profile_image_url($mb_id);
        }
        if ($message['profile_image_url'] === '' && function_exists('eottae_estate_member_thumb_url')) {
            include_once G5_LIB_PATH.'/eottae-estate.lib.php';
            $message['profile_image_url'] = eottae_estate_member_thumb_url($mb_id);
        }

        $message['profile_initial'] = eottae_public_group_chat_member_initial($message['author'] ?? '');
        $message['profile_avatar_kind'] = 'member';

        return $message;
    }
}

if (!function_exists('eottae_public_group_chat_avatar_html')) {
    /**
     * @param array<string, mixed> $message
     */
    function eottae_public_group_chat_avatar_html(array $message)
    {
        if (!empty($message['is_mine'])) {
            return '';
        }

        $author = get_text($message['author_display'] ?? $message['author'] ?? '익명');
        $profile_url = trim((string) ($message['profile_url'] ?? ''));
        $image_url = trim((string) ($message['profile_image_url'] ?? ''));
        $initial = get_text($message['profile_initial'] ?? eottae_public_group_chat_member_initial($author));

        if (!empty($message['is_ai']) || ($message['profile_avatar_kind'] ?? '') === 'ai') {
            return '<span class="public-group-chat__avatar public-group-chat__avatar--ai" aria-hidden="true"><span class="public-group-chat__avatar-emoji">🤖</span></span>';
        }

        $inner = '';
        if ($image_url !== '') {
            $inner = '<img src="'.htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8').'" alt="" class="public-group-chat__avatar-img" width="38" height="38" loading="lazy" decoding="async">';
        } else {
            $inner = '<span class="public-group-chat__avatar-initial" aria-hidden="true">'.htmlspecialchars($initial, ENT_QUOTES, 'UTF-8').'</span>';
        }

        $title = $author !== '' ? $author.' 회원정보 보기' : '회원정보 보기';
        if ($profile_url !== '') {
            return '<a href="'.htmlspecialchars($profile_url, ENT_QUOTES, 'UTF-8').'" class="public-group-chat__avatar" title="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'">'.$inner.'</a>';
        }

        return '<span class="public-group-chat__avatar public-group-chat__avatar--static" aria-hidden="true">'.$inner.'</span>';
    }
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

        $message = eottae_public_group_chat_attach_profile_fields($message);

        $is_mine = !empty($message['is_mine']);
        $is_ai = !empty($message['is_ai']);
        $item_class = 'public-group-chat__message';
        if ($is_mine) {
            $item_class .= ' public-group-chat__message--mine';
        }
        if ($is_ai) {
            $item_class .= ' public-group-chat__message--ai public-ai-message is-talk-ai-message';
        }
        if (!$is_mine) {
            $item_class .= ' public-group-chat__message--peer';
        }

        $author = $is_ai
            ? ($message['ai_display_name'] ?? $message['author'] ?? '어때봇 · AI 도우미')
            : ($message['author'] ?? '익명');
        $time_label = isset($message['time_label']) ? (string) $message['time_label'] : '';
        $unread_count = $is_mine ? max(0, (int) ($message['unread_count'] ?? 0)) : 0;
        $text = isset($message['text']) ? (string) $message['text'] : '';
        $action_label = trim((string) ($message['action_label'] ?? ''));
        $action_url = trim((string) ($message['action_url'] ?? ''));
        $calendar_event_id = max(0, (int) ($message['calendar_event_id'] ?? 0));
        if ($calendar_event_id < 1 && $action_url !== '') {
            if (!function_exists('eottae_calendar_event_id_from_url')) {
                include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
            }
            $calendar_event_id = eottae_calendar_event_id_from_url($action_url);
        }
        $has_calendar_action = $action_label !== '' && $calendar_event_id > 0;
        $has_action = !$has_calendar_action
            && $action_label !== ''
            && $action_url !== ''
            && preg_match('#^https?://#i', $action_url);
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
                <?php
                $avatar_html = eottae_public_group_chat_avatar_html($message);
                if ($avatar_html !== '') {
                    echo $avatar_html;
                }
                ?>
                <div class="public-group-chat__message-body">
                <?php if (!$is_mine) { ?>
                <div class="public-group-chat__meta">
                    <?php if ($is_ai) {
                        $ai_badge = eottae_talkroom_ai_message_render_badge($message, 'sm');
                        echo str_replace('talk-ai-msg__badge', 'talk-ai-msg__badge public-ai-badge', $ai_badge);
                    } else { ?>
                    <strong class="public-group-chat__author"><?php echo $author; ?></strong>
                    <?php } ?>
                    <?php if ($can_delete) { ?>
                    <button type="button" class="public-group-chat__delete" data-public-chat-delete="<?php echo (int) $message['wr_id']; ?>" aria-label="메시지 삭제">삭제</button>
                    <?php } ?>
                </div>
                <?php } elseif ($can_delete) { ?>
                <div class="public-group-chat__meta public-group-chat__meta--actions">
                    <button type="button" class="public-group-chat__delete" data-public-chat-delete="<?php echo (int) $message['wr_id']; ?>" aria-label="메시지 삭제">삭제</button>
                </div>
                <?php } ?>
                <div class="public-group-chat__bubble-row">
                    <?php if ($is_mine) {
                        if ($unread_count > 0) { ?>
                    <span class="public-group-chat__unread" aria-label="읽지 않은 <?php echo (int) $unread_count; ?>명"><?php echo (int) $unread_count; ?></span>
                        <?php }
                        if ($time_label !== '') { ?>
                    <time class="public-group-chat__time"><?php echo $time_label; ?></time>
                        <?php }
                    } ?>
                    <div class="public-group-chat__bubble">
                        <p class="public-group-chat__text"><?php echo nl2br(get_text($text)); ?></p>
                        <?php if ($poll_html !== '') { echo $poll_html; } ?>
                        <?php if ($has_calendar_action) { ?>
                        <p class="public-group-chat__action-wrap">
                            <a href="#" class="public-group-chat__cta" data-sebu-cal-event="<?php echo (int) $calendar_event_id; ?>" role="button"><?php echo get_text($action_label); ?></a>
                        </p>
                        <?php } elseif ($has_action) { ?>
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
            </div>
        </article>
        <?php

        return (string) ob_get_clean();
    }
}
