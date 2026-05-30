<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_group_chat_life_ai_icon_html')) {
    function eottae_public_group_chat_life_ai_icon_html()
    {
        return '<span class="public-group-chat__composer-action-icon" aria-hidden="true">'
            .'<svg viewBox="0 0 24 24" focusable="false"><path fill="currentColor" d="M9 3a1 1 0 011 1v1h4V4a1 1 0 112 0v1h1.5A2.5 2.5 0 0119 7.5V11a7 7 0 11-14 0V7.5A2.5 2.5 0 018.5 4H9V4a1 1 0 011-1zm-1 9a5 5 0 1010 0v-4.5a.5.5 0 00-.5-.5h-9a.5.5 0 00-.5.5V12zM8 18h8v2H8v-2z"></path></svg>'
            .'</span>';
    }
}

if (!function_exists('eottae_public_group_chat_life_ai_button_html')) {
    function eottae_public_group_chat_life_ai_button_html($extra_class = '', $tag = 'button', $attrs = '')
    {
        $class = trim('public-group-chat__life-ai public-group-chat__composer-action public-group-chat__composer-action--wide '.$extra_class);
        $label = 'AI 세부생활답변';

        return '<'.$tag.' type="'.($tag === 'button' ? 'button' : 'submit').'" class="'.$class.'" '.$attrs
            .' aria-label="'.$label.'" title="'.$label.'">'
            .eottae_public_group_chat_life_ai_icon_html()
            .'<span class="public-group-chat__composer-action-text" aria-hidden="true">'.$label.'</span>'
            .'</'.$tag.'>';
    }
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
        $has_more_history = false;
        $oldest_wr_id = 0;

        if (!empty($messages)) {
            foreach ($messages as $message) {
                $wr_id = (int) ($message['wr_id'] ?? 0);
                if ($wr_id < 1) {
                    continue;
                }
                if ($oldest_wr_id < 1 || $wr_id < $oldest_wr_id) {
                    $oldest_wr_id = $wr_id;
                }
            }
        }

        if ($defer_history && $oldest_wr_id > 0 && (int) ($payload['room_id'] ?? 0) > 0) {
            $older_rows = eottae_talkroom_public_group_list_messages_before((int) $payload['room_id'], $oldest_wr_id, 1);
            $has_more_history = !empty($older_rows);
        }

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
            data-home-preview="1"
            data-defer-history="1"
            data-history-total="<?php echo (int) $total_limit; ?>"
            data-has-more-history="<?php echo $has_more_history ? '1' : '0'; ?>"
            data-enter-url="<?php echo get_text($payload['enter_href']); ?>"
            <?php if ($oldest_wr_id > 0) { ?>
            data-oldest-wr-id="<?php echo (int) $oldest_wr_id; ?>"
            <?php } ?>
            <?php } ?>
        >
            <div class="public-group-chat__inner">
                <header class="public-group-chat__head">
                    <div class="public-group-chat__title-row">
                        <span class="public-group-chat__emoji" aria-hidden="true"><?php echo $payload['room_emoji']; ?></span>
                        <div class="public-group-chat__title-wrap">
                            <h2 class="public-group-chat__title" id="public-group-chat-title">🤖 세부생활AI봇</h2>
                            <p class="public-group-chat__desc">세부 생활정보를 질문하면 AI가 공개톡으로 답변합니다</p>
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
                        <a href="<?php echo $payload['enter_href']; ?>" class="public-group-chat__enter">전체보기</a>
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
                        <p class="public-group-chat__hint">회원가입 또는 로그인 후 세부 생활 질문을 남길 수 있습니다.</p>
                        <div class="public-group-chat__composer-actions">
                            <a href="<?php echo $payload['login_href']; ?>" class="public-group-chat__action">로그인</a>
                            <a href="<?php echo $payload['register_href']; ?>" class="public-group-chat__action public-group-chat__action--register">회원가입</a>
                        </div>
                    </div>
                    <?php } else { ?>
                    <form class="public-group-chat__composer" id="eottae-public-chat-form" action="#" method="post">
                        <label class="sr-only" for="eottae-public-chat-input">세부 생활 질문 입력</label>
                        <div class="public-group-chat__composer-field">
                            <textarea
                                id="eottae-public-chat-input"
                                class="public-group-chat__input"
                                rows="2"
                                maxlength="500"
                                placeholder="세부 생활, 여행, 병원, 환전, 교통 등 궁금한 점을 물어보세요"
                            ></textarea>
                            <button type="button" class="public-group-chat__life-ai public-group-chat__composer-action public-group-chat__composer-action--wide" data-public-chat-life-ai aria-label="AI 세부생활답변" title="AI 세부생활답변">
                                <?php echo eottae_public_group_chat_life_ai_icon_html(); ?>
                                <span class="public-group-chat__composer-action-text" aria-hidden="true">AI 세부생활답변</span>
                            </button>
                        </div>
                        <p class="public-group-chat__life-ai-status" data-public-chat-life-ai-status aria-live="polite"></p>
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
