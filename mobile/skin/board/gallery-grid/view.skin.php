<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
include_once(G5_SKIN_PATH.'/board/_inc/g5b-gallery-sidebar.php');

$gallery_grid_skin_css = G5_PATH.'/skin/board/gallery-grid/style.css';
$gallery_grid_skin_ver = is_file($gallery_grid_skin_css) ? (int) filemtime($gallery_grid_skin_css) : 0;
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css?ver='.$gallery_grid_skin_ver.'">', 99);

$gal_related = g5b_gallery_get_related_writes($bo_table, (int) $view['wr_id'], 20);
$gal_views = g5b_gallery_format_views($view['wr_hit']);
$gal_rel_time = g5b_gallery_relative_time($view['wr_datetime']);
$gal_meta_parts = array();
if ($gal_views !== '') {
    $gal_meta_parts[] = $gal_views;
}
if ($gal_rel_time !== '') {
    $gal_meta_parts[] = $gal_rel_time;
}
$gal_meta_line = implode(' • ', $gal_meta_parts);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<article class="board-wrap board-wrap--gallery-grid board-view board-view--gallery board-gal-watch" id="bo_v" style="width:<?php echo $width; ?>">

    <div class="board-gal-watch__toolbar">
        <a href="<?php echo $list_href ?>" class="board-gal-watch__back btn_b01 btn" title="목록"><i class="fa fa-arrow-left" aria-hidden="true"></i><span class="board-gal-watch__back-label">목록</span></a>
        <div class="board-gal-watch__toolbar-actions" id="bo_v_top">
            <ul class="btn_bo_user bo_v_com">
                <?php if ($reply_href) { ?><li><a href="<?php echo $reply_href ?>" class="btn_b01 btn" title="답변"><i class="fa fa-reply" aria-hidden="true"></i><span class="sound_only">답변</span></a></li><?php } ?>
                <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sound_only">글쓰기</span></a></li><?php } ?>
                <?php if ($update_href || $delete_href || $copy_href || $move_href || $search_href) { ?>
                <li>
                    <button type="button" class="btn_more_opt is_view_btn btn_b01 btn" title="옵션"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></button>
                    <ul class="more_opt is_view_btn">
                        <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>">수정</a></li><?php } ?>
                        <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;">삭제</a></li><?php } ?>
                        <?php if ($copy_href) { ?><li><a href="<?php echo $copy_href ?>" onclick="board_move(this.href); return false;">복사</a></li><?php } ?>
                        <?php if ($move_href) { ?><li><a href="<?php echo $move_href ?>" onclick="board_move(this.href); return false;">이동</a></li><?php } ?>
                        <?php if ($search_href) { ?><li><a href="<?php echo $search_href ?>">검색</a></li><?php } ?>
                    </ul>
                </li>
                <?php } ?>
            </ul>
            <script>
            jQuery(function($){
                $(".btn_more_opt.is_view_btn").on("click", function(e) { e.stopPropagation(); $(".more_opt.is_view_btn").toggle(); });
                $(document).on("click", function (e) {
                    if(!$(e.target).closest('.is_view_btn').length) $(".more_opt.is_view_btn").hide();
                });
            });
            </script>
        </div>
    </div>

    <div class="board-gal-watch__layout">
        <div class="board-gal-watch__primary">
            <section class="board-gal-watch__media-wrap" id="bo_v_atc">
                <h1 class="sound_only"><?php echo get_text($view['wr_subject']); ?></h1>
                <?php include_once(G5_SKIN_PATH.'/board/_inc/g5b-gallery-view.php'); ?>
            </section>

            <header class="board-gal-watch__head">
                <h2 class="board-gal-watch__title" id="bo_v_title">
                    <?php if ($category_name) { ?><span class="board-gal-watch__cate"><?php echo $view['ca_name']; ?></span><?php } ?>
                    <span class="board-gal-watch__title-text"><?php echo get_text($view['wr_subject']); ?></span>
                </h2>
            </header>

            <section class="board-gal-watch__meta-row" id="bo_v_info">
                <div class="board-gal-watch__author">
                    <div class="board-gal-watch__avatar"><?php echo get_member_profile_img($view['mb_id']); ?></div>
                    <div class="board-gal-watch__author-text">
                        <strong class="board-gal-watch__author-name"><?php echo $view['name'] ?><?php if ($is_ip_view) { echo ' ('.$ip.')'; } ?></strong>
                        <?php if ($gal_meta_line) { ?><span class="board-gal-watch__stats"><?php echo htmlspecialchars($gal_meta_line, ENT_QUOTES, 'UTF-8'); ?></span><?php } ?>
                    </div>
                </div>
                <div class="board-gal-watch__share" id="bo_v_share">
                    <?php include_once(G5_SNS_PATH.'/view.sns.skin.php'); ?>
                    <?php if ($scrap_href) { ?><a href="<?php echo $scrap_href; ?>" target="_blank" class="btn btn_b03 board-gal-watch__scrap" onclick="win_scrap(this.href); return false;"><i class="fa fa-bookmark" aria-hidden="true"></i> 스크랩</a><?php } ?>
                </div>
            </section>

            <?php if ($is_signature) { ?><div class="board-view__signature"><?php echo $signature ?></div><?php } ?>

            <?php if ($good_href || $nogood_href) { ?>
            <div id="bo_v_act" class="board-gal-watch__vote">
                <?php if ($good_href) { ?><span class="bo_v_act_gng"><a href="<?php echo $good_href.'&amp;'.$qstr ?>" id="good_button" class="bo_v_good"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i><strong><?php echo number_format($view['wr_good']) ?></strong></a><b id="bo_v_act_good"></b></span><?php } ?>
                <?php if ($nogood_href) { ?><span class="bo_v_act_gng"><a href="<?php echo $nogood_href.'&amp;'.$qstr ?>" id="nogood_button" class="bo_v_nogood"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i><strong><?php echo number_format($view['wr_nogood']) ?></strong></a><b id="bo_v_act_nogood"></b></span><?php } ?>
            </div>
            <?php } elseif ($board['bo_use_good'] || $board['bo_use_nogood']) { ?>
            <div id="bo_v_act" class="board-gal-watch__vote">
                <?php if ($board['bo_use_good']) { ?><span class="bo_v_good"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i><strong><?php echo number_format($view['wr_good']) ?></strong></span><?php } ?>
                <?php if ($board['bo_use_nogood']) { ?><span class="bo_v_nogood"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i><strong><?php echo number_format($view['wr_nogood']) ?></strong></span><?php } ?>
            </div>
            <?php } ?>

            <?php
            $cnt = 0;
            if (!empty($view['file']['count'])) {
                for ($i=0; $i<count($view['file']); $i++) {
                    if (empty($view['file'][$i]['source'])) {
                        continue;
                    }
                    if (!empty($view['file'][$i]['view'])) {
                        continue;
                    }
                    if (function_exists('eottae_gallery_file_is_image') && eottae_gallery_file_is_image($view['file'][$i]['source'])) {
                        continue;
                    }
                    $cnt++;
                }
            }
            ?>
            <?php if ($cnt) { ?>
            <section id="bo_v_file" class="board-view__files">
                <h3 class="board-view__section-title">첨부파일</h3>
                <ul>
                <?php for ($i=0; $i<count($view['file']); $i++) {
                    if (empty($view['file'][$i]['source']) || !empty($view['file'][$i]['view'])) {
                        continue;
                    }
                    if (function_exists('eottae_gallery_file_is_image') && eottae_gallery_file_is_image($view['file'][$i]['source'])) {
                        continue;
                    }
                ?>
                    <li>
                        <a href="<?php echo $view['file'][$i]['href']; ?>" class="view_file_download">
                            <strong><?php echo $view['file'][$i]['source'] ?></strong> (<?php echo $view['file'][$i]['size'] ?>)
                        </a>
                    </li>
                <?php } ?>
                </ul>
            </section>
            <?php } ?>

            <?php if (isset($view['link']) && array_filter($view['link'])) { ?>
            <section id="bo_v_link" class="board-view__links">
                <h3 class="board-view__section-title">관련링크</h3>
                <ul>
                <?php for ($i=1; $i<=count($view['link']); $i++) {
                    if (!empty($view['link'][$i])) {
                ?>
                    <li><a href="<?php echo $view['link_href'][$i] ?>" target="_blank" rel="noopener noreferrer"><?php echo cut_str($view['link'][$i], 70); ?></a></li>
                <?php } } ?>
                </ul>
            </section>
            <?php } ?>

            <?php if ($prev_href || $next_href) { ?>
            <nav class="bo_v_nb board-view__nav board-gal-watch__nav">
                <ul>
                    <?php if ($prev_href) { ?><li class="btn_prv"><span class="nb_tit">이전글</span><a href="<?php echo $prev_href ?>"><?php echo $prev_wr_subject;?></a></li><?php } ?>
                    <?php if ($next_href) { ?><li class="btn_next"><span class="nb_tit">다음글</span><a href="<?php echo $next_href ?>"><?php echo $next_wr_subject;?></a></li><?php } ?>
                </ul>
            </nav>
            <?php } ?>

            <section class="board-gal-watch__comments-wrap">
                <?php include_once(G5_BBS_PATH.'/view_comment.php'); ?>
            </section>
        </div>

        <aside class="board-gal-watch__sidebar" aria-label="다른 갤러리">
            <h2 class="board-gal-watch__sidebar-title">다른 갤러리</h2>
            <?php if (empty($gal_related)) { ?>
            <p class="board-gal-watch__sidebar-empty">다른 글이 없습니다.</p>
            <?php } else { ?>
            <ul class="board-gal-sidebar">
                <?php foreach ($gal_related as $gal_row) {
                    echo g5b_gallery_sidebar_item_html($gal_row, $bo_table, (int) $view['wr_id']);
                } ?>
            </ul>
            <?php } ?>
        </aside>
    </div>
</article>

<script>
<?php if ($board['bo_download_point'] < 0) { ?>
$(function() {
    $("a.view_file_download").click(function() {
        if(!g5_is_member) { alert("다운로드 권한이 없습니다."); return false; }
        if(confirm("파일 다운로드 시 포인트가 차감됩니다. 계속하시겠습니까?")) {
            $(this).attr("href", $(this).attr("href")+"&js=on");
            return true;
        }
        return false;
    });
});
<?php } ?>
function board_move(href) { window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1"); }
</script>
<script>
$(function() {
    $("a.view_image").click(function() {
        window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });
    $("#good_button, #nogood_button").click(function() {
        var $tx = this.id == "good_button" ? $("#bo_v_act_good") : $("#bo_v_act_nogood");
        excute_good(this.href, $(this), $tx);
        return false;
    });
    $("#bo_v_atc").viewimageresize();
});
function excute_good(href, $el, $tx) {
    $.post(href, { js: "on" }, function(data) {
        if (data.error) { alert(data.error); return; }
        if (data.count) {
            $el.find("strong").text(number_format(String(data.count)));
            $tx.text(this.id == "nogood_button" ? "비추천하셨습니다." : "추천하셨습니다.").fadeIn(200).delay(2500).fadeOut(200);
        }
    }, "json");
}
</script>
