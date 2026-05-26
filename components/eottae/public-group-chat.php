<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_group_chat_html')) {
    function eottae_public_group_chat_html($limit = 20)
    {
        include_once G5_LIB_PATH.'/eottae-talkroom-public-chat.lib.php';
        include_once G5_PATH.'/components/eottae/talk-ai-message-ui.php';

        global $member;

        $viewer_mb_id = !empty($member['mb_id']) ? (string) $member['mb_id'] : '';
        $payload = eottae_talkroom_public_group_chat_payload($limit, $viewer_mb_id);
        $messages = $payload['messages'];

        ob_start();
        ?>
        <section
            class="public-group-chat"
            id="eottae-home-public-chat"
            aria-labelledby="public-group-chat-title"
            data-room-id="<?php echo (int) $payload['room_id']; ?>"
            data-last-wr-id="<?php echo (int) $payload['last_wr_id']; ?>"
            data-poll-url="<?php echo G5_URL; ?>/proc/eottae-talkroom-public-chat.php"
            data-send-url="<?php echo G5_URL; ?>/proc/eottae-talkroom-public-chat.php"
            data-member-token="<?php echo get_text($payload['member_token']); ?>"
            data-can-send="<?php echo !empty($payload['can_send']) ? '1' : '0'; ?>"
            data-is-member="<?php echo !empty($payload['is_member']) ? '1' : '0'; ?>"
            data-login-url="<?php echo get_text($payload['login_href']); ?>"
            data-register-url="<?php echo get_text($payload['register_href']); ?>"
            data-needs-join="<?php echo !empty($payload['needs_join']) ? '1' : '0'; ?>"
        >
            <div class="public-group-chat__inner">
                <header class="public-group-chat__head">
                    <div class="public-group-chat__title-row">
                        <span class="public-group-chat__emoji" aria-hidden="true"><?php echo $payload['room_emoji']; ?></span>
                        <div class="public-group-chat__title-wrap">
                            <h2 class="public-group-chat__title" id="public-group-chat-title"><?php echo get_text($payload['room_emoji'].' '.$payload['room_name']); ?></h2>
                            <p class="public-group-chat__desc">회원 누구나 참여 · AI 도우미가 대화를 돕습니다</p>
                        </div>
                    </div>
                    <div class="public-group-chat__head-actions">
                        <?php if ((int) $payload['room_id'] > 0) { ?>
                        <span class="public-group-chat__live-badge" aria-hidden="true">LIVE</span>
                        <?php } ?>
                        <a href="<?php echo $payload['enter_href']; ?>" class="public-group-chat__enter">단체톡방 입장</a>
                    </div>
                </header>

                <div class="public-group-chat__panel">
                    <div class="public-group-chat__messages" id="eottae-public-chat-messages" aria-live="polite">
                        <?php if (empty($messages)) { ?>
                        <p class="public-group-chat__empty">아직 대화가 없습니다. 첫 메시지를 남겨 보세요.</p>
                        <?php } else {
                            foreach ($messages as $message) {
                                $item_class = 'public-group-chat__message';
                                if (!empty($message['is_mine'])) {
                                    $item_class .= ' public-group-chat__message--mine';
                                }
                                if (!empty($message['is_ai'])) {
                                    $item_class .= ' public-group-chat__message--ai is-talk-ai-message';
                                }
                                ?>
                        <article class="<?php echo $item_class; ?>" data-wr-id="<?php echo (int) $message['wr_id']; ?>">
                            <div class="public-group-chat__bubble">
                                <?php if (!empty($message['is_ai'])) { ?>
                                <?php echo eottae_talkroom_ai_message_render_badge($message, 'sm'); ?>
                                <?php } ?>
                                <strong class="public-group-chat__author"><?php echo $message['author']; ?></strong>
                                <p class="public-group-chat__text"><?php echo nl2br(get_text($message['text'])); ?></p>
                                <?php if ($message['time_label'] !== '') { ?>
                                <time class="public-group-chat__time"><?php echo $message['time_label']; ?></time>
                                <?php } ?>
                            </div>
                        </article>
                        <?php }
                        } ?>
                    </div>

                    <?php if ((int) $payload['room_id'] < 1) { ?>
                    <div class="public-group-chat__composer public-group-chat__composer--disabled">
                        <p class="public-group-chat__hint">공개 단체톡방을 준비 중입니다.</p>
                        <a href="<?php echo $payload['list_href']; ?>" class="public-group-chat__action">세부톡방 둘러보기</a>
                    </div>
                    <?php } elseif (empty($payload['is_member'])) { ?>
                    <div class="public-group-chat__composer public-group-chat__composer--login">
                        <p class="public-group-chat__hint">회원가입 또는 로그인 후 실시간 대화에 참여할 수 있습니다.</p>
                        <div class="public-group-chat__composer-actions">
                            <a href="<?php echo $payload['login_href']; ?>" class="public-group-chat__action">로그인</a>
                            <a href="<?php echo $payload['register_href']; ?>" class="public-group-chat__action public-group-chat__action--register">회원가입</a>
                        </div>
                    </div>
                    <?php } else { ?>
                    <form class="public-group-chat__composer" id="eottae-public-chat-form" action="#" method="post">
                        <label class="sr-only" for="eottae-public-chat-input">메시지 입력</label>
                        <div class="public-group-chat__composer-field">
                            <textarea
                                id="eottae-public-chat-input"
                                class="public-group-chat__input"
                                rows="2"
                                maxlength="500"
                                placeholder="세부 소식, 질문, 한마디를 남겨 보세요"
                            ></textarea>
                            <button type="submit" class="public-group-chat__send" <?php echo empty($payload['can_send']) ? 'disabled' : ''; ?>>보내기</button>
                        </div>
                    </form>
                    <?php } ?>
                </div>

            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
