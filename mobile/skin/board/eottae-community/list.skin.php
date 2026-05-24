<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<div class="board-list-page board-wrap board-wrap--eottae-community" id="bo_list" style="width:<?php echo $width; ?>">

    <?php if ($is_category) { ?>
    <nav class="board-sidebar board-cate" id="bo_cate">
        <h2 class="sound_only"><?php echo $board['bo_subject'] ?> 카테고리</h2>
        <ul id="bo_cate_ul"><?php echo $category_option ?></ul>
    </nav>
    <?php } ?>

    <header class="board-list-page__header">
        <h1 class="board-list-page__title"><?php echo get_text($board['bo_subject']); ?></h1>
        <?php if ($write_href) { ?><a href="<?php echo $write_href; ?>" class="eottae-btn-write">글쓰기</a><?php } ?>
    </header>

    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">

    <div class="board-list-page__list">
        <?php for ($i = 0; $i < count($list); $i++) { ?>
        <a href="<?php echo $list[$i]['href']; ?>" class="board-post-item<?php echo $list[$i]['is_notice'] ? ' board-post-item--notice' : ''; ?>">
            <h2 class="board-post-item__subject">
                <?php if ($list[$i]['is_notice']) { ?><span class="board-post-item__badge">공지</span><?php } ?>
                <?php echo $list[$i]['subject']; ?>
            </h2>
            <p class="board-post-item__meta">
                <span><?php echo $list[$i]['name']; ?></span>
                <span><?php echo $list[$i]['datetime2']; ?></span>
                <span>조회 <?php echo $list[$i]['wr_hit']; ?></span>
                <?php if ($list[$i]['comment_cnt']) { ?><span>댓글 <?php echo $list[$i]['comment_cnt']; ?></span><?php } ?>
            </p>
        </a>
        <?php } ?>
        <?php if (count($list) === 0) { ?>
        <div class="empty-state">
            <p class="empty-state__title">게시글이 없습니다</p>
            <p>첫 글을 작성해 보세요.</p>
        </div>
        <?php } ?>
    </div>

    <nav class="board-paging"><?php echo $write_pages; ?></nav>
    </form>
</div>
