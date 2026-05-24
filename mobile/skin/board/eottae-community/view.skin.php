<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<article class="board-view-page board-wrap board-wrap--eottae-community" id="bo_v" style="width:<?php echo $width; ?>">

    <h1 class="board-view-page__title"><?php echo get_text($view['wr_subject']); ?></h1>

    <div class="board-view-page__meta">
        <span><?php echo $view['name']; ?></span>
        <span><?php echo date('Y.m.d H:i', strtotime($view['wr_datetime'])); ?></span>
        <span>조회 <?php echo number_format($view['wr_hit']); ?></span>
    </div>

    <section class="board-view-page__body" id="bo_v_con">
        <?php echo get_view_thumbnail($view['content']); ?>
    </section>

    <?php include_once(G5_BBS_PATH.'/view_comment.php'); ?>

    <footer style="margin-top:24px">
        <ul class="board-actions btn_bo_user">
            <?php if ($list_href) { ?><li><a href="<?php echo $list_href; ?>" class="btn_b01 btn">목록</a></li><?php } ?>
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href; ?>" class="btn_b01 btn">수정</a></li><?php } ?>
            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href; ?>" class="btn_b01 btn" onclick="return confirm('삭제하시겠습니까?');">삭제</a></li><?php } ?>
        </ul>
    </footer>
</article>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>
