<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/eottae.lib.php');
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$view_category = isset($view['ca_name']) ? get_text($view['ca_name']) : '';
$list_url = eottae_community_list_url($view_category !== '' ? array('sca' => $view_category) : array());

$is_talkroom_board = function_exists('eottae_talkroom_board_table') && $bo_table === eottae_talkroom_board_table();
$is_ai_post = false;
if ($is_talkroom_board) {
    include_once G5_PATH.'/components/eottae/talk-ai-message-ui.php';
    $is_ai_post = eottae_talkroom_ai_message_is_ai($view);
}
$article_class = 'community-view-page__article';
if ($is_ai_post) {
    $article_class .= ' community-view-page__article--ai is-talk-ai-message';
}
?>

<div class="community-view-page board-wrap board-wrap--eottae-community" id="bo_v" style="width:<?php echo $width; ?>">

<div class="community-view-page__layout">
<main class="community-view-page__main">

    <header class="community-view-page__header">
        <a href="<?php echo $list_href ? $list_href : $list_url; ?>" class="community-view-page__back">← 목록으로</a>
        <?php if ($view_category) { ?>
        <span class="community-view-page__category"><?php echo $view_category; ?></span>
        <?php } ?>
    </header>

    <article class="<?php echo $article_class; ?>">
        <?php if ($is_ai_post) { ?>
        <div class="community-view-page__ai-label">
            <?php echo eottae_talkroom_ai_message_render_badge($view); ?>
        </div>
        <?php } ?>

        <h1 class="community-view-page__title<?php echo $is_ai_post ? ' talk-ai-msg__title' : ''; ?>"><?php echo get_text($view['wr_subject']); ?></h1>

        <div class="community-view-page__meta">
            <?php if ($is_ai_post) { ?>
            <span class="community-view-page__author talk-ai-msg__author-line"><?php echo eottae_talkroom_ai_message_display_name($view); ?></span>
            <?php } else { ?>
            <span class="community-view-page__author"><?php echo $view['name']; ?></span>
            <?php } ?>
            <time datetime="<?php echo date('c', strtotime($view['wr_datetime'])); ?>"><?php echo date('Y.m.d H:i', strtotime($view['wr_datetime'])); ?></time>
            <span>조회 <?php echo number_format($view['wr_hit']); ?></span>
            <?php if ($view['wr_comment']) { ?><span>댓글 <?php echo number_format($view['wr_comment']); ?></span><?php } ?>
        </div>

        <?php include_once G5_PATH.'/components/eottae/community-view-media.php'; ?>

        <?php if (function_exists('eottae_is_community_board') && eottae_is_community_board($bo_table)) {
            include_once G5_PATH.'/components/eottae/community-view-links.php';
        } ?>

        <section class="community-view-page__body talk-ai-msg__body<?php echo $is_ai_post ? ' talk-ai-msg__body--ai' : ''; ?>" id="bo_v_con">
            <?php echo get_view_thumbnail($view['content']); ?>
        </section>
    </article>

    <?php include_once(G5_BBS_PATH.'/view_comment.php'); ?>

    <footer class="community-view-page__footer">
        <ul class="board-actions btn_bo_user community-view-page__actions">
            <?php if ($list_href) { ?><li><a href="<?php echo $list_href; ?>" class="btn_b01 btn">목록</a></li><?php } ?>
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href; ?>" class="btn_b01 btn">수정</a></li><?php } ?>
            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href; ?>" class="btn_b01 btn" onclick="return confirm('삭제하시겠습니까?');">삭제</a></li><?php } ?>
            <?php if ($reply_href) { ?><li><a href="<?php echo $reply_href; ?>" class="btn_b01 btn">답변</a></li><?php } ?>
        </ul>
    </footer>
</main>

<?php include_once(G5_PATH.'/components/eottae/community-sidebar.php'); ?>
</div>

</div>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>
<script>
$(function() {
    $(".community-view-page__gallery, .community-view-page__body").viewimageresize();
    $("a.view_image").on("click", function() {
        window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });
});
</script>
