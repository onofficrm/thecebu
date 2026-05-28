<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-message.lib.php';

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(eottae_message_url()));
}

eottae_message_ensure_schema();

$token = eottae_message_token();
$thread_id = isset($_GET['thread_id']) ? (int) $_GET['thread_id'] : 0;
$current_filter = eottae_message_normalize_filter($_GET['filter'] ?? 'all');
$shop_inquiry_code = isset($_GET['shop']) ? trim((string) $_GET['shop']) : '';
$shop_context = $shop_inquiry_code !== '' ? eottae_message_shop_context($shop_inquiry_code) : array();
$shop_compose_mode = !empty($shop_context['owner_mb_id']);
$shop_compose_body = '';
if ($shop_compose_mode) {
    $shop_label = trim((string) ($shop_context['shop_name'] ?? ''));
    $shop_compose_body = $shop_label !== ''
        ? '안녕하세요. '.$shop_label.' 문의드립니다.'."\n\n"
        : '안녕하세요. 업체 문의드립니다.'."\n\n";
}
$operator_compose_mode = isset($_GET['to']) && trim((string) $_GET['to']) === 'operator';
$golf_join_id = isset($_GET['golf_join']) ? (int) $_GET['golf_join'] : 0;
$golf_join_context = $golf_join_id > 0 ? eottae_message_golf_join_context($golf_join_id) : array();
$golf_join_compose_mode = !empty($golf_join_context['host_mb_id']);
$golf_join_compose_body = '';
if ($golf_join_compose_mode) {
    $golf_label = trim((string) ($golf_join_context['title'] ?? ''));
    if ($golf_label === '') {
        $golf_label = trim((string) ($golf_join_context['course_name'] ?? ''));
    }
    $golf_join_compose_body = $golf_label !== ''
        ? '안녕하세요. '.$golf_label.' 골프조인 문의드립니다.'."\n\n"
        : '안녕하세요. 골프조인 문의드립니다.'."\n\n";
}
$message_filter_options = eottae_message_filter_options();
$message_filter_counts = eottae_message_filter_counts($member['mb_id']);
$message_empty_state = eottae_message_empty_state($current_filter);
$threads = eottae_message_thread_list($member['mb_id'], 50, $current_filter);
$current_thread = $thread_id > 0 ? eottae_message_get_thread($thread_id, $member['mb_id']) : array();
$messages = array();
$other_label = '';
$current_context_label = '';

if (!empty($current_thread['thread_id'])) {
    eottae_message_mark_thread_read($thread_id, $member['mb_id']);
    $messages = eottae_message_list_messages($thread_id, $member['mb_id'], 120);
    $other_mb_id = eottae_message_other_participant($current_thread, $member['mb_id']);
    $other = eottae_message_get_member($other_mb_id);
    $other_label = eottae_message_member_label($other, $other_mb_id);
    $current_context_label = eottae_message_thread_context_label($current_thread);
}

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-message.css">', 24);
add_javascript('<script src="'.G5_JS_URL.'/eottae-message.js" defer></script>', 24);

g5_page_start('쪽지');
?>

<main class="mypage-subpage eottae-message-page" data-eottae-message-page>
    <?php eottae_render_mypage_back(); ?>

    <header class="eottae-message-page__head">
        <div>
            <h1 class="mypage-subpage__title">쪽지</h1>
            <p class="eottae-message-page__lead">회원·업체·운영진과 1:1 메시지를 주고받을 수 있습니다.</p>
        </div>
        <a href="<?php echo eottae_message_url(); ?>#message-compose" class="eottae-message-page__compose-link">새 쪽지</a>
    </header>

    <section class="eottae-message-compose" id="message-compose" aria-labelledby="message-compose-title">
        <h2 class="eottae-message-section-title" id="message-compose-title">
            <?php
            if ($shop_compose_mode) {
                echo get_text(($shop_context['shop_name'] ?? '업체').' 쪽지 문의');
            } elseif ($golf_join_compose_mode) {
                echo '골프조인 작성자 문의';
            } elseif ($operator_compose_mode) {
                echo '운영진에게 문의';
            } else {
                echo '새 쪽지 보내기';
            }
            ?>
        </h2>
        <?php if ($shop_inquiry_code !== '' && !$shop_compose_mode) { ?>
        <div class="empty-state">
            <p class="empty-state__title">쪽지 문의를 사용할 수 없습니다</p>
            <p>이 업체는 사업자 회원과 연결되어 있지 않습니다. 업체 상세의 빠른 문의를 이용해 주세요.</p>
        </div>
        <?php } elseif ($shop_compose_mode) { ?>
        <p class="eottae-message-compose__hint">업체 사업자 회원에게 직접 메시지를 보냅니다.</p>
        <form class="eottae-message-form" method="post" action="<?php echo eottae_message_proc_url(); ?>" data-message-form>
            <input type="hidden" name="eottae_message_token" value="<?php echo get_text($token); ?>">
            <input type="hidden" name="action" value="shop_inquiry">
            <input type="hidden" name="inquiry_code" value="<?php echo get_text($shop_context['inquiry_code']); ?>">
            <label class="eottae-message-form__label" for="message_body">문의 내용</label>
            <textarea id="message_body" name="body" class="eottae-message-form__textarea" rows="5" maxlength="3000" required placeholder="문의 내용을 입력해 주세요."><?php echo get_text($shop_compose_body); ?></textarea>
            <button type="submit" class="eottae-message-btn">업체에게 보내기</button>
        </form>
        <?php } elseif ($golf_join_id > 0 && !$golf_join_compose_mode) { ?>
        <div class="empty-state">
            <p class="empty-state__title">작성자에게 쪽지를 보낼 수 없습니다</p>
            <p>골프조인 글이 삭제되었거나 작성자 정보를 확인할 수 없습니다.</p>
        </div>
        <?php } elseif ($golf_join_compose_mode) { ?>
        <p class="eottae-message-compose__hint">골프조인 작성자에게 직접 메시지를 보냅니다.</p>
        <form class="eottae-message-form" method="post" action="<?php echo eottae_message_proc_url(); ?>" data-message-form>
            <input type="hidden" name="eottae_message_token" value="<?php echo get_text($token); ?>">
            <input type="hidden" name="action" value="golf_join_inquiry">
            <input type="hidden" name="join_id" value="<?php echo (int) $golf_join_context['join_id']; ?>">
            <label class="eottae-message-form__label" for="message_body">문의 내용</label>
            <textarea id="message_body" name="body" class="eottae-message-form__textarea" rows="5" maxlength="3000" required placeholder="문의 내용을 입력해 주세요."><?php echo get_text($golf_join_compose_body); ?></textarea>
            <button type="submit" class="eottae-message-btn">작성자에게 보내기</button>
        </form>
        <?php } elseif ($operator_compose_mode) { ?>
        <p class="eottae-message-compose__hint">세부어때 운영진에게 문의·제안을 남깁니다.</p>
        <form class="eottae-message-form" method="post" action="<?php echo eottae_message_proc_url(); ?>" data-message-form>
            <input type="hidden" name="eottae_message_token" value="<?php echo get_text($token); ?>">
            <input type="hidden" name="action" value="operator_inquiry">
            <label class="eottae-message-form__label" for="message_body">문의 내용</label>
            <textarea id="message_body" name="body" class="eottae-message-form__textarea" rows="5" maxlength="3000" required placeholder="운영진에게 전달할 내용을 입력해 주세요."></textarea>
            <button type="submit" class="eottae-message-btn">운영진에게 보내기</button>
        </form>
        <?php } else { ?>
        <form class="eottae-message-form" method="post" action="<?php echo eottae_message_proc_url(); ?>" data-message-form>
            <input type="hidden" name="eottae_message_token" value="<?php echo get_text($token); ?>">
            <input type="hidden" name="action" value="send">
            <label class="eottae-message-form__label" for="message_receiver">받는 회원 ID 또는 닉네임</label>
            <input type="text" id="message_receiver" name="receiver" class="eottae-message-form__input" maxlength="80" required placeholder="예: cebuadmin">
            <label class="eottae-message-form__label" for="message_body">내용</label>
            <textarea id="message_body" name="body" class="eottae-message-form__textarea" rows="4" maxlength="3000" required placeholder="메시지를 입력해 주세요."></textarea>
            <button type="submit" class="eottae-message-btn">보내기</button>
        </form>
        <p class="eottae-message-compose__hint">
            <a href="<?php echo eottae_message_url(array('to' => 'operator')); ?>#message-compose">운영진에게 문의하기</a>
        </p>
        <?php } ?>
    </section>

    <div class="eottae-message-layout">
        <section class="eottae-message-list-panel" aria-labelledby="message-list-title">
            <h2 class="eottae-message-section-title" id="message-list-title">대화 목록</h2>
            <nav class="eottae-message-filter-tabs" aria-label="쪽지함 필터">
                <?php foreach ($message_filter_options as $filter_key => $filter_label) {
                    $filter_href = $filter_key === 'all'
                        ? eottae_message_url()
                        : eottae_message_url(array('filter' => $filter_key));
                    $is_active_filter = $current_filter === $filter_key;
                    $filter_count = (int) ($message_filter_counts[$filter_key] ?? 0);
                    ?>
                <a href="<?php echo get_text($filter_href); ?>" class="eottae-message-filter-tabs__item<?php echo $is_active_filter ? ' is-active' : ''; ?>">
                    <span><?php echo get_text($filter_label); ?></span>
                    <em><?php echo number_format($filter_count); ?></em>
                </a>
                <?php } ?>
            </nav>
            <?php if (empty($threads)) { ?>
            <div class="empty-state">
                <p class="empty-state__title"><?php echo get_text($message_empty_state['title']); ?></p>
                <p><?php echo get_text($message_empty_state['desc']); ?></p>
            </div>
            <?php } else { ?>
            <ul class="eottae-message-thread-list">
                <?php foreach ($threads as $thread) {
                    $active = (int) $thread['thread_id'] === (int) ($current_thread['thread_id'] ?? 0);
                    ?>
                <li>
                    <a href="<?php echo $thread['href']; ?>" class="eottae-message-thread<?php echo $active ? ' is-active' : ''; ?><?php echo !empty($thread['is_unread']) ? ' is-unread' : ''; ?>">
                        <span class="eottae-message-thread__name"><?php echo get_text($thread['other_label']); ?></span>
                        <?php if (!empty($thread['context_label'])) { ?>
                        <span class="eottae-message-thread__context"><?php echo get_text($thread['context_label']); ?></span>
                        <?php } ?>
                        <?php if (!empty($thread['is_unread'])) { ?><span class="eottae-message-thread__badge">새 쪽지</span><?php } ?>
                        <span class="eottae-message-thread__preview"><?php echo $thread['last_body_preview']; ?></span>
                        <time class="eottae-message-thread__time"><?php echo get_text(substr((string) ($thread['last_message_at'] ?? ''), 0, 16)); ?></time>
                    </a>
                </li>
                <?php } ?>
            </ul>
            <?php } ?>
        </section>

        <section class="eottae-message-detail-panel" aria-labelledby="message-detail-title">
            <?php if (empty($current_thread['thread_id'])) { ?>
            <div class="empty-state empty-state--message">
                <p class="empty-state__title">대화를 선택해 주세요</p>
                <p>왼쪽 목록에서 대화를 열거나 새 쪽지를 보내세요.</p>
            </div>
            <?php } else { ?>
            <div class="eottae-message-detail__head">
                <h2 class="eottae-message-section-title" id="message-detail-title">
                    <?php echo get_text($other_label); ?>님과의 대화
                    <?php if ($current_context_label !== '') { ?>
                    <span class="eottae-message-detail__context"><?php echo get_text($current_context_label); ?></span>
                    <?php } ?>
                </h2>
                <form method="post" action="<?php echo eottae_message_proc_url(); ?>" data-message-form data-message-confirm="이 대화를 내 목록에서 숨길까요? 상대방의 대화는 삭제되지 않습니다.">
                    <input type="hidden" name="eottae_message_token" value="<?php echo get_text($token); ?>">
                    <input type="hidden" name="action" value="archive">
                    <input type="hidden" name="thread_id" value="<?php echo (int) $current_thread['thread_id']; ?>">
                    <button type="submit" class="eottae-message-link-btn">보관</button>
                </form>
            </div>

            <div class="eottae-message-bubbles" aria-live="polite">
                <?php foreach ($messages as $msg) {
                    $mine = (string) ($msg['sender_mb_id'] ?? '') === (string) $member['mb_id'];
                    ?>
                <article class="eottae-message-bubble<?php echo $mine ? ' is-mine' : ''; ?>">
                    <p class="eottae-message-bubble__meta">
                        <span><?php echo get_text($mine ? '나' : ($msg['sender_label'] ?? '회원')); ?></span>
                        <time><?php echo get_text(substr((string) ($msg['created_at'] ?? ''), 0, 16)); ?></time>
                    </p>
                    <div class="eottae-message-bubble__body"><?php echo nl2br(get_text($msg['body'] ?? '')); ?></div>
                </article>
                <?php } ?>
            </div>

            <form class="eottae-message-form eottae-message-reply" method="post" action="<?php echo eottae_message_proc_url(); ?>" data-message-form>
                <input type="hidden" name="eottae_message_token" value="<?php echo get_text($token); ?>">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="thread_id" value="<?php echo (int) $current_thread['thread_id']; ?>">
                <label class="sound_only" for="message_reply_body">답장</label>
                <textarea id="message_reply_body" name="body" class="eottae-message-form__textarea" rows="3" maxlength="3000" required placeholder="답장을 입력해 주세요."></textarea>
                <button type="submit" class="eottae-message-btn">답장 보내기</button>
            </form>
            <?php } ?>
        </section>
    </div>
</main>

<?php
g5_page_end();
