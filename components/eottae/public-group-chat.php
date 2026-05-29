<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_group_chat_html')) {
    function eottae_public_group_chat_html($limit = 20, $defer_history = false)
    {
        include_once G5_LIB_PATH.'/eottae-talkroom-public-chat.lib.php';
        include_once G5_PATH.'/components/eottae/public-group-chat-message.php';

        global $member;

        $viewer_mb_id = !empty($member['mb_id']) ? (string) $member['mb_id'] : '';
        $total_limit = max(1, (int) $limit);
        $initial_limit = $defer_history ? 1 : $total_limit;
        $payload = eottae_talkroom_public_group_chat_payload($initial_limit, $viewer_mb_id);
        $messages = $payload['messages'];

        ob_start();
        ?>
        <section
            class="public-group-chat public-group-chat--kakao public-group-chat--hero home-hero-chat-column"
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
            data-can-manage-ai="<?php echo !empty($payload['can_manage_ai']) ? '1' : '0'; ?>"
            <?php if ($defer_history) { ?>
            data-defer-history="1"
            data-history-total="<?php echo (int) $total_limit; ?>"
            <?php } ?>
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
                        <button type="button" class="public-group-chat__fullscreen" data-public-chat-fullscreen aria-pressed="false" aria-label="전체화면으로 보기" hidden>
                            <span class="public-group-chat__fullscreen-icon" aria-hidden="true">⛶</span>
                            <span class="public-group-chat__fullscreen-label">전체화면</span>
                        </button>
                        <?php if ((int) $payload['room_id'] > 0) { ?>
                        <span class="public-group-chat__live-badge" aria-hidden="true">LIVE</span>
                        <?php } ?>
                        <?php if (!empty($payload['can_manage_ai'])) { ?>
                        <button type="button" class="public-group-chat__ai-speak" data-public-chat-ai-speak aria-label="AI 말걸기">🤖 AI 말걸기</button>
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
                                echo eottae_public_group_chat_message_html($message);
                            }
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
                            <button type="submit" class="public-group-chat__send" <?php echo empty($payload['can_send']) ? 'disabled' : ''; ?>>전송</button>
                        </div>
                        <div class="public-group-chat__quick-actions">
                            <button type="button" class="public-group-chat__life-ai eottae-ai-btn" data-public-chat-life-ai>세부 생활 질문</button>
                            <span class="public-group-chat__life-ai-status" data-public-chat-life-ai-status aria-live="polite"></span>
                        </div>
                    </form>
                    <?php } ?>
                </div>

            </div>
        </section>
        <?php
        if (!empty($payload['is_member'])) {
            $manage_js = defined('G5_JS_URL') ? G5_JS_URL.'/eottae-public-chat-manage.js' : '/js/eottae-public-chat-manage.js';
            $manage_path = defined('G5_PATH') ? G5_PATH.'/js/eottae-public-chat-manage.js' : '';
            if ($manage_path !== '' && is_file($manage_path)) {
                $manage_js .= '?ver='.(int) filemtime($manage_path);
            }
            echo '<script src="'.htmlspecialchars($manage_js, ENT_QUOTES, 'UTF-8').'" defer></script>';
        }

        return (string) ob_get_clean();
    }
}
