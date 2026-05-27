<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-golf-join.lib.php';

$join_id = isset($_GET['join_id']) ? (int) $_GET['join_id'] : 0;

if (!$is_member || empty($member['mb_id'])) {
    alert(
        '로그인 후 이용할 수 있습니다.',
        function_exists('eottae_login_url') ? eottae_login_url(eottae_golf_join_chat_url($join_id)) : G5_BBS_URL.'/login.php'
    );
}

$post = eottae_golf_join_fetch_post_row($join_id);
if (!$post) {
    alert('조인방을 찾을 수 없습니다.', eottae_golf_join_list_url());
}

$access = eottae_golf_join_can_access_chat($join_id, $member['mb_id']);
if (empty($access['ok'])) {
    alert($access['message'] ?? '채팅방에 입장할 수 없습니다.', eottae_golf_join_detail_url($join_id));
}

$ctx = eottae_golf_join_chat_build_context($join_id, $member['mb_id'], $post);
$member_token = eottae_golf_join_member_token();
$messages = eottae_golf_join_chat_list_messages((int) $ctx['chat_room_id'], 0, 50);

g5_page_start('조인 채팅');
?>

<main class="golf-join-page golf-join-page--chat" id="golf-join-chat"
      data-join-id="<?php echo (int) $join_id; ?>"
      data-viewer-id="<?php echo get_text($member['mb_id']); ?>">

    <header class="golf-join-topbar golf-join-topbar--detail">
        <a href="<?php echo eottae_golf_join_detail_url($join_id); ?>" class="golf-join-topbar__back" aria-label="뒤로가기"><span aria-hidden="true">←</span></a>
        <h1 class="golf-join-topbar__title"><?php echo get_text($ctx['title']); ?></h1>
        <span class="golf-join-topbar__search" aria-hidden="true"></span>
    </header>

    <div class="golf-join-chat-messages" id="golf-join-chat-messages" aria-live="polite">
        <?php if (empty($messages)) { ?>
        <p class="golf-join-chat-empty" id="golf-join-chat-empty">첫 메시지를 남겨 보세요.</p>
        <?php } else { ?>
        <?php foreach ($messages as $msg) {
            $is_mine = ($msg['user_id'] ?? '') === $member['mb_id'];
            ?>
        <div class="golf-join-chat-bubble-wrap<?php echo $is_mine ? ' is-mine' : ''; ?>" data-message-id="<?php echo (int) $msg['id']; ?>">
            <p class="golf-join-chat-bubble__meta"><?php echo get_text($msg['nickname']); ?> · <?php echo get_text($msg['time_label']); ?></p>
            <div class="golf-join-chat-bubble"><?php echo nl2br(get_text($msg['message'])); ?></div>
        </div>
        <?php } ?>
        <?php } ?>
    </div>

    <footer class="golf-join-chat-compose">
        <form id="golf-join-chat-form" class="golf-join-chat-compose__form">
            <input type="text" class="golf-join-chat-compose__input" id="golf-join-chat-input" maxlength="500" placeholder="메시지 입력" autocomplete="off">
            <button type="submit" class="golf-join-chat-compose__send">전송</button>
        </form>
    </footer>
</main>

<script>
window.EOTTaeGolfJoinChat = {
    joinId: <?php echo (int) $join_id; ?>,
    procUrl: <?php echo json_encode(eottae_golf_join_chat_proc_url(), JSON_UNESCAPED_UNICODE); ?>,
    memberToken: <?php echo json_encode($member_token, JSON_UNESCAPED_UNICODE); ?>,
    viewerId: <?php echo json_encode($member['mb_id'], JSON_UNESCAPED_UNICODE); ?>,
    lastId: <?php
    $last = 0;
    foreach ($messages as $m) {
        $last = max($last, (int) ($m['id'] ?? 0));
    }
    echo $last;
    ?>
};
</script>

<?php
add_javascript('<script src="'.G5_JS_URL.'/eottae-golf-join-chat.js" defer></script>', 25);
g5_page_end();
