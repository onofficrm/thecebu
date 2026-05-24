<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/thumbnail.lib.php');
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<div class="shop-list-page board-wrap board-wrap--eottae-shop" id="bo_list" style="width:<?php echo $width; ?>">

    <?php if ($is_category) { ?>
    <nav class="board-cate" id="bo_cate">
        <h2 class="sound_only"><?php echo $board['bo_subject'] ?> 카테고리</h2>
        <ul id="bo_cate_ul"><?php echo $category_option ?></ul>
    </nav>
    <?php } ?>

    <header class="shop-list-page__header">
        <div>
            <h1 class="shop-list-page__title"><?php echo get_text($board['bo_subject']); ?></h1>
            <p class="shop-list-page__count">총 <strong><?php echo number_format($total_count); ?></strong>곳</p>
        </div>
        <?php if ($write_href) { ?>
        <a href="<?php echo $write_href; ?>" class="eottae-btn-write">업체 등록</a>
        <?php } ?>
    </header>

    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="sw" value="">

    <div class="shop-list-page__grid">
        <?php
        for ($i = 0; $i < count($list); $i++) {
            $list[$i]['bo_table'] = $bo_table;
            eottae_render_shop_card($list[$i], $bo_table);
        }
        if (count($list) === 0) {
        ?>
        <div class="empty-state">
            <p class="empty-state__title">등록된 업체가 없습니다</p>
            <p>첫 업체를 등록해 보세요.</p>
            <?php if ($write_href) { ?><a href="<?php echo $write_href; ?>" class="eottae-btn-write" style="margin-top:16px">업체 등록</a><?php } ?>
        </div>
        <?php } ?>
    </div>

    <nav class="board-paging" aria-label="페이지"><?php echo $write_pages; ?></nav>
    </form>
</div>

<script>
function fboardlist_submit(f) {
    return true;
}
</script>
