<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_chat_html')) {
    /**
     * @param int $room_id
     * @param array<string, mixed>|null $ctx
     */
    function eottae_talkroom_chat_html($room_id, ?array $ctx = null)
    {
        include_once G5_LIB_PATH.'/eottae-talkroom-public-chat.lib.php';
        include_once G5_PATH.'/components/eottae/public-group-chat-message.php';
        if (!function_exists('eottae_public_group_chat_life_ai_icon_html')) {
            include_once G5_PATH.'/components/eottae/public-group-chat.php';
        }

        global $member;

        $viewer_mb_id = !empty($member['mb_id']) ? (string) $member['mb_id'] : '';
        $payload = eottae_talkroom_room_chat_payload((int) $room_id, $viewer_mb_id, $ctx, 25);
        $messages = $payload['messages'];
        $api_url = G5_URL.'/proc/eottae-talkroom-room-chat.php';
        $is_public_group_chat = function_exists('eottae_talkroom_public_group_room_id')
            && (int) ($payload['room_id'] ?? 0) === (int) eottae_talkroom_public_group_room_id();
        $send_action_label = $is_public_group_chat ? 'AI 세부생활답변' : '전송';
        $send_action_class = $is_public_group_chat ? ' public-group-chat__composer-action--wide' : '';

        ob_start();
        ?>
        <section
            class="public-group-chat talk-room-chat public-group-chat--kakao"
            id="eottae-talkroom-chat"
            aria-label="<?php echo get_text($payload['room_name']); ?> 대화"
            data-room-id="<?php echo (int) $payload['room_id']; ?>"
            data-last-wr-id="<?php echo (int) $payload['last_wr_id']; ?>"
            data-first-wr-id="<?php echo (int) ($payload['first_wr_id'] ?? 0); ?>"
            data-has-older="<?php echo !empty($payload['has_older']) ? '1' : '0'; ?>"
            data-history-batch="15"
            data-poll-url="<?php echo get_text($api_url); ?>"
            data-send-url="<?php echo get_text($api_url); ?>"
            data-member-token="<?php echo get_text($payload['member_token']); ?>"
            data-can-send="<?php echo !empty($payload['can_send']) ? '1' : '0'; ?>"
            data-is-member="<?php echo !empty($payload['is_member']) ? '1' : '0'; ?>"
            data-login-url="<?php echo get_text($payload['login_href']); ?>"
            data-register-url="<?php echo get_text($payload['register_href']); ?>"
            data-needs-join="<?php echo !empty($payload['needs_join']) ? '1' : '0'; ?>"
            data-can-manage-ai="<?php echo !empty($payload['can_manage_ai']) ? '1' : '0'; ?>"
            <?php if ($is_public_group_chat) { ?>
            data-life-qa="1"
            <?php } ?>
        >
            <div class="public-group-chat__inner">
                <header class="public-group-chat__head talk-room-chat__head">
                    <div class="public-group-chat__title-row">
                        <span class="public-group-chat__emoji" aria-hidden="true"><?php echo get_text($payload['room_emoji']); ?></span>
                        <div class="public-group-chat__title-wrap">
                            <h2 class="public-group-chat__title"><?php echo get_text($payload['room_name']); ?></h2>
                            <?php if ($payload['room_desc'] !== '') { ?>
                            <p class="public-group-chat__desc"><?php echo get_text($payload['room_desc']); ?></p>
                            <?php } else { ?>
                            <p class="public-group-chat__desc">회원 누구나 참여 · AI 도우미가 대화를 돕습니다</p>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="public-group-chat__head-actions">
                        <button type="button" class="public-group-chat__fullscreen" data-public-chat-fullscreen aria-pressed="false" aria-label="전체화면으로 보기" hidden>
                            <span class="public-group-chat__fullscreen-icon" aria-hidden="true">⛶</span>
                            <span class="public-group-chat__fullscreen-label">전체화면</span>
                        </button>
                        <span class="public-group-chat__live-badge" aria-hidden="true">LIVE</span>
                        <?php if (!empty($payload['can_manage_ai'])) { ?>
                        <button type="button" class="public-group-chat__ai-speak" data-public-chat-ai-speak aria-label="AI 말걸기">🤖 AI 말걸기</button>
                        <?php } ?>
                    </div>
                </header>

                <div class="public-group-chat__panel talk-room-chat__panel">
                    <?php if (empty($payload['can_view'])) { ?>
                    <div class="public-group-chat__composer public-group-chat__composer--disabled">
                        <p class="public-group-chat__hint">비공개 톡방은 참여 승인 후 대화를 볼 수 있습니다.</p>
                    </div>
                    <?php } else { ?>
                    <div class="public-group-chat__messages talk-room-chat__messages" id="eottae-talkroom-chat-messages" aria-live="polite">
                        <div class="public-group-chat__history-top" data-chat-history-top hidden>
                            <span class="public-group-chat__history-spinner">이전 대화 불러오는 중…</span>
                        </div>
                        <?php if (empty($messages)) { ?>
                        <p class="public-group-chat__empty">아직 대화가 없습니다. 첫 메시지를 남겨 보세요.</p>
                        <?php } else {
                            foreach ($messages as $message) {
                                echo eottae_public_group_chat_message_html($message);
                            }
                        } ?>
                    </div>

                    <?php if (empty($payload['is_member'])) { ?>
                    <div class="public-group-chat__composer public-group-chat__composer--login">
                        <p class="public-group-chat__hint">회원가입 또는 로그인 후 실시간 대화에 참여할 수 있습니다.</p>
                        <div class="public-group-chat__composer-actions">
                            <a href="<?php echo $payload['login_href']; ?>" class="public-group-chat__action">로그인</a>
                            <a href="<?php echo $payload['register_href']; ?>" class="public-group-chat__action public-group-chat__action--register">회원가입</a>
                        </div>
                    </div>
                    <?php } elseif (!empty($payload['needs_join'])) { ?>
                    <div class="public-group-chat__composer public-group-chat__composer--join">
                        <div class="public-group-chat__composer-row">
                            <button type="button" class="public-group-chat__join" data-talk-join="<?php echo (int) $payload['room_id']; ?>">참여하기</button>
                            <div class="public-group-chat__composer-field">
                                <label class="sr-only" for="eottae-talkroom-chat-input">메시지 입력</label>
                                <textarea
                                    id="eottae-talkroom-chat-input"
                                    class="public-group-chat__input"
                                    rows="1"
                                    maxlength="500"
                                    placeholder="참여 후 메시지를 입력하세요"
                                    disabled
                                ></textarea>
                                <button type="button" class="public-group-chat__send public-group-chat__composer-action<?php echo $send_action_class; ?>" disabled aria-label="<?php echo get_text($send_action_label); ?>"><span class="public-group-chat__composer-action-text" aria-hidden="true"><?php echo get_text($send_action_label); ?></span></button>
                            </div>
                        </div>
                    </div>
                    <?php } elseif (!empty($payload['membership_pending'])) { ?>
                    <div class="public-group-chat__composer public-group-chat__composer--disabled public-group-chat__composer--pending">
                        <p class="public-group-chat__hint">참여 승인 대기 중입니다. 승인 후 메시지를 보낼 수 있습니다.</p>
                        <div class="public-group-chat__composer-field">
                            <textarea class="public-group-chat__input" rows="1" placeholder="메시지를 입력하세요" disabled></textarea>
                            <button type="button" class="public-group-chat__send public-group-chat__composer-action<?php echo $send_action_class; ?>" disabled aria-label="<?php echo get_text($send_action_label); ?>"><span class="public-group-chat__composer-action-text" aria-hidden="true"><?php echo get_text($send_action_label); ?></span></button>
                        </div>
                    </div>
                    <?php } elseif (!empty($payload['join_blocked'])) { ?>
                    <div class="public-group-chat__composer public-group-chat__composer--disabled">
                        <p class="public-group-chat__hint"><?php echo $payload['join_hint'] !== '' ? get_text($payload['join_hint']) : '이 톡방에 참여할 수 없습니다.'; ?></p>
                        <div class="public-group-chat__composer-field">
                            <textarea class="public-group-chat__input" rows="1" placeholder="메시지를 입력하세요" disabled></textarea>
                            <button type="button" class="public-group-chat__send public-group-chat__composer-action<?php echo $send_action_class; ?>" disabled aria-label="<?php echo get_text($send_action_label); ?>"><span class="public-group-chat__composer-action-text" aria-hidden="true"><?php echo get_text($send_action_label); ?></span></button>
                        </div>
                    </div>
                    <?php } else { ?>
                    <form class="public-group-chat__composer" id="eottae-talkroom-chat-form" action="#" method="post">
                        <label class="sr-only" for="eottae-talkroom-chat-input">메시지 입력</label>
                        <div class="public-group-chat__composer-field">
                            <textarea
                                id="eottae-talkroom-chat-input"
                                class="public-group-chat__input"
                                rows="1"
                                maxlength="500"
                                placeholder="메시지를 입력하세요"
                                <?php echo empty($payload['can_send']) ? 'disabled' : ''; ?>
                            ></textarea>
                            <button type="submit" class="public-group-chat__send public-group-chat__life-ai public-group-chat__composer-action<?php echo $send_action_class; ?>" <?php echo empty($payload['can_send']) ? 'disabled' : ''; ?> aria-label="<?php echo get_text($send_action_label); ?>">
                                <?php if ($is_public_group_chat && function_exists('eottae_public_group_chat_life_ai_icon_html')) {
                                    echo eottae_public_group_chat_life_ai_icon_html();
                                } ?>
                                <span class="public-group-chat__composer-action-text" aria-hidden="true"><?php echo get_text($send_action_label); ?></span>
                            </button>
                        </div>
                    </form>
                    <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
