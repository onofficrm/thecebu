<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/eottae.lib.php');
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$view_category = isset($view['ca_name']) ? get_text($view['ca_name']) : '';
$view_thumb = eottae_community_list_thumb($bo_table, $view['wr_id']);
$list_url = eottae_community_list_url($view_category !== '' ? array('sca' => $view_category) : array());
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

    <article class="community-view-page__article">
        <h1 class="community-view-page__title"><?php echo get_text($view['wr_subject']); ?></h1>

        <div class="community-view-page__meta">
            <span class="community-view-page__author"><?php echo $view['name']; ?></span>
            <time datetime="<?php echo date('c', strtotime($view['wr_datetime'])); ?>"><?php echo date('Y.m.d H:i', strtotime($view['wr_datetime'])); ?></time>
            <span>조회 <?php echo number_format($view['wr_hit']); ?></span>
            <?php if ($view['wr_comment']) { ?><span>댓글 <?php echo number_format($view['wr_comment']); ?></span><?php } ?>
        </div>

        <?php if ($view_thumb) { ?>
        <div class="community-view-page__thumb">
            <img src="<?php echo $view_thumb; ?>" alt="">
        </div>
        <?php } ?>

        <section class="community-view-page__body" id="bo_v_con">
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
