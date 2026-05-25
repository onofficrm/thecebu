<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_SKIN_PATH.'/board/_inc/g5b-youtube.php');
include_once(G5_SKIN_PATH.'/board/_inc/g5b-media-board-assets.php');

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<div class="board-wrap board-wrap--youtube-list" id="bo_list" style="width:<?php echo $width; ?>">

    <?php if ($is_category) { ?>
    <nav class="board-cate board-cate--yt" id="bo_cate">
        <h2 class="sound_only"><?php echo $board['bo_subject'] ?> 카테고리</h2>
        <div class="board-cate--yt__scroll">
            <ul id="bo_cate_ul"><?php echo $category_option ?></ul>
        </div>
    </nav>
    <?php } ?>

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

    <header class="board-header board-header--yt" id="bo_btn_top">
        <div class="board-header__info" id="bo_list_total">
            <span class="board-header__count sound_only">Total <strong><?php echo number_format($total_count) ?></strong>건</span>
            <span class="board-header__page sound_only"><?php echo $page ?> 페이지</span>
        </div>
        <ul class="board-actions btn_bo_user board-actions--yt">
            <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin btn board-actions--yt__btn" title="관리자"><i class="fa fa-cog fa-fw" aria-hidden="true"></i><span class="sound_only">관리자</span></a></li><?php } ?>
            <?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b01 btn board-actions--yt__btn" title="RSS"><i class="fa fa-rss" aria-hidden="true"></i><span class="sound_only">RSS</span></a></li><?php } ?>
            <li><button type="button" class="btn_bo_sch btn_b01 btn board-actions--yt__btn" title="검색"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button></li>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="board-actions__write btn_b01 btn board-actions--yt__btn board-actions--yt__write" title="글쓰기"><i class="fa fa-video-camera" aria-hidden="true"></i><span class="board-actions--yt__write-label">영상 등록</span></a></li><?php } ?>
            <?php if ($is_admin == 'super' || $is_auth) { ?>
            <li>
                <button type="button" class="btn_more_opt is_list_btn btn_b01 btn board-actions--yt__btn" title="옵션"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></button>
                <?php if ($is_checkbox) { ?>
                <ul class="more_opt is_list_btn">
                    <li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value"><i class="fa fa-trash-o" aria-hidden="true"></i> 선택삭제</button></li>
                    <li><button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value"><i class="fa fa-files-o" aria-hidden="true"></i> 선택복사</button></li>
                    <li><button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value"><i class="fa fa-arrows" aria-hidden="true"></i> 선택이동</button></li>
                </ul>
                <?php } ?>
            </li>
            <?php } ?>
        </ul>
    </header>

    <?php if ($is_checkbox) { ?>
    <div class="board-list__chkall all_chk chk_box board-yt-admin-chk">
        <input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);" class="selec_chk">
        <label for="chkall"><span></span><b>전체선택</b></label>
    </div>
    <?php } ?>

    <div class="board-list board-list--youtube-list">
        <?php if (count($list) == 0) { ?>
        <p class="board-list__empty board-yt-empty">등록된 영상이 없습니다.</p>
        <?php } else { ?>
        <ul class="board-yt-grid">
        <?php for ($i=0; $i<count($list); $i++) {
            $is_secret = isset($list[$i]['wr_option']) && strstr($list[$i]['wr_option'], 'secret');
            $yt_id = g5b_youtube_id_from_write($list[$i]);
            $thumb_html = g5b_youtube_thumb_html($yt_id, $list[$i]['subject'], $is_secret);
            $channel = g5b_youtube_channel_label($list[$i]);
            $rel_time = g5b_youtube_relative_time(isset($list[$i]['wr_datetime']) ? $list[$i]['wr_datetime'] : '');
            $views = g5b_youtube_format_views($list[$i]['wr_hit']);
            $meta_parts = array();
            if ($channel !== '') {
                $meta_parts[] = $channel;
            }
            if ($views !== '') {
                $meta_parts[] = $views;
            }
            if ($rel_time !== '') {
                $meta_parts[] = $rel_time;
            }
            $meta_line = implode(' • ', $meta_parts);
        ?>
            <li class="board-yt-card <?php if ($list[$i]['is_notice']) echo 'board-yt-card--notice bo_notice'; ?>">
                <?php if ($is_checkbox) { ?>
                <div class="board-yt-card__chk chk_box">
                    <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="selec_chk">
                    <label for="chk_wr_id_<?php echo $i ?>"><span></span><b class="sound_only"><?php echo $list[$i]['subject'] ?></b></label>
                </div>
                <?php } ?>
                <a href="<?php echo $list[$i]['href'] ?>" class="board-yt-card__link">
                    <span class="board-yt-card__media">
                        <span class="board-yt-card__thumb"><?php echo $thumb_html; ?></span>
                        <?php echo g5b_youtube_duration_badge_html($list[$i]); ?>
                        <?php if ($list[$i]['is_notice']) { ?><span class="board-yt-card__badge board-yt-card__badge--notice">공지</span><?php } ?>
                        <?php if ($list[$i]['icon_new']) { ?><span class="board-yt-card__badge board-yt-card__badge--new">NEW</span><?php } ?>
                    </span>
                    <span class="board-yt-card__info">
                        <?php echo g5b_youtube_avatar_html($channel, 'board-yt-card__avatar'); ?>
                        <span class="board-yt-card__text">
                            <span class="board-yt-card__title">
                                <?php echo $list[$i]['icon_reply'] ?>
                                <?php if (isset($list[$i]['icon_secret'])) echo rtrim($list[$i]['icon_secret']); ?>
                                <span class="board-yt-card__title-text"><?php echo $list[$i]['subject'] ?></span>
                                <?php if ($list[$i]['comment_cnt']) { ?><span class="cnt_cmt board-yt-card__cmt"><?php echo $list[$i]['wr_comment']; ?></span><?php } ?>
                            </span>
                            <?php if ($meta_line) { ?><span class="board-yt-card__meta"><?php echo htmlspecialchars($meta_line, ENT_QUOTES, 'UTF-8'); ?></span><?php } ?>
                        </span>
                        <span class="board-yt-card__more" aria-hidden="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></span>
                    </span>
                </a>
            </li>
        <?php } ?>
        </ul>
        <?php } ?>
    </div>

    <nav class="board-paging board-paging--yt" aria-label="게시판 페이지"><?php echo $write_pages; ?></nav>

    <?php if ($write_href) { ?>
    <footer class="board-footer bo_fx board-footer--yt">
        <ul class="board-actions btn_bo_user">
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="board-actions__write btn_b01 btn"><i class="fa fa-video-camera" aria-hidden="true"></i><span>영상 등록</span></a></li><?php } ?>
        </ul>
    </footer>
    <?php } ?>
    </form>

    <div class="board-search bo_sch_wrap">
        <fieldset class="bo_sch board-search__panel">
            <h3 class="board-search__title">검색</h3>
            <form name="fsearch" method="get">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
            <input type="hidden" name="sca" value="<?php echo $sca ?>">
            <input type="hidden" name="sop" value="and">
            <select name="sfl" id="sfl"><?php echo get_board_sfl_select_options($sfl); ?></select>
            <div class="sch_bar board-search__bar">
                <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="sch_input" maxlength="20" placeholder="검색어를 입력해주세요">
                <button type="submit" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i></button>
            </div>
            <button type="button" class="bo_sch_cls board-search__close"><i class="fa fa-times" aria-hidden="true"></i></button>
            </form>
        </fieldset>
        <div class="bo_sch_bg board-search__backdrop"></div>
    </div>
    <script>
    jQuery(function($){
        $(".btn_bo_sch").on("click", function() { $(".bo_sch_wrap").toggle(); });
        $('.bo_sch_bg, .bo_sch_cls').click(function(){ $('.bo_sch_wrap').hide(); });
    });
    </script>
</div>

<?php if ($is_checkbox) { ?>
<script>
function all_checked(sw) {
    var f = document.fboardlist;
    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]") f.elements[i].checked = sw;
    }
}
function fboardlist_submit(f) {
    var chk_count = 0;
    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked) chk_count++;
    }
    if (!chk_count) { alert(document.pressed + "할 게시물을 하나 이상 선택하세요."); return false; }
    if(document.pressed == "선택복사") { select_copy("copy"); return; }
    if(document.pressed == "선택이동") { select_copy("move"); return; }
    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?")) return false;
        f.removeAttribute("target");
        f.action = g5_bbs_url+"/board_list_update.php";
    }
    return true;
}
function select_copy(sw) {
    var f = document.fboardlist;
    window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");
    f.sw.value = sw; f.target = "move"; f.action = g5_bbs_url+"/move.php"; f.submit();
}
jQuery(function($){
    $(".btn_more_opt.is_list_btn").on("click", function(e) { e.stopPropagation(); $(".more_opt.is_list_btn").toggle(); });
    $(document).on("click", function (e) {
        if(!$(e.target).closest('.is_list_btn').length) $(".more_opt.is_list_btn").hide();
    });
});
</script>
<?php } ?>
