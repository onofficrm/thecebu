<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
include_once(G5_SKIN_PATH.'/board/_inc/g5b-youtube.php');

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$yt_id = g5b_youtube_id_from_write($view);
$yt_summary = !empty($view['wr_2']) ? get_text($view['wr_2']) : '';
$yt_channel = g5b_youtube_channel_label($view);
$yt_views = g5b_youtube_format_views($view['wr_hit']);
$yt_rel_time = g5b_youtube_relative_time($view['wr_datetime']);
$yt_meta_parts = array();
if ($yt_views !== '') {
    $yt_meta_parts[] = $yt_views;
}
if ($yt_rel_time !== '') {
    $yt_meta_parts[] = $yt_rel_time;
}
$yt_meta_line = implode(' • ', $yt_meta_parts);
$yt_related = g5b_youtube_get_related_writes($bo_table, (int) $view['wr_id'], 20);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<article class="board-wrap board-wrap--youtube-list board-view board-view--youtube board-yt-watch" id="bo_v" style="width:<?php echo $width; ?>">

    <div class="board-yt-watch__toolbar">
        <a href="<?php echo $list_href ?>" class="board-yt-watch__back btn_b01 btn" title="목록"><i class="fa fa-arrow-left" aria-hidden="true"></i><span class="board-yt-watch__back-label">목록</span></a>
        <div class="board-yt-watch__toolbar-actions" id="bo_v_top">
            <ul class="btn_bo_user bo_v_com">
                <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="글쓰기"><i class="fa fa-video-camera" aria-hidden="true"></i><span class="sound_only">글쓰기</span></a></li><?php } ?>
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

    <div class="board-yt-watch__layout">
        <div class="board-yt-watch__primary">
            <section class="board-yt-watch__player-wrap" id="bo_v_atc">
                <h1 class="sound_only"><?php echo get_text($view['wr_subject']); ?></h1>
                <div class="board-yt-watch__player">
                    <?php
                    if ($yt_id) {
                        echo g5b_youtube_embed_html($yt_id, $view['wr_subject']);
                    } else {
                        echo g5b_youtube_fallback_html();
                    }
                    ?>
                </div>
            </section>

            <header class="board-yt-watch__head">
                <h2 class="board-yt-watch__title" id="bo_v_title">
                    <?php if ($category_name) { ?><span class="board-yt-watch__cate"><?php echo $view['ca_name']; ?></span><?php } ?>
                    <span class="board-yt-watch__title-text"><?php echo get_text($view['wr_subject']); ?></span>
                </h2>
            </header>

            <section class="board-yt-watch__meta-row" id="bo_v_info">
                <div class="board-yt-watch__channel">
                    <?php echo g5b_youtube_avatar_html($yt_channel, 'board-yt-watch__avatar'); ?>
                    <div class="board-yt-watch__channel-text">
                        <strong class="board-yt-watch__channel-name"><?php echo $yt_channel !== '' ? htmlspecialchars($yt_channel, ENT_QUOTES, 'UTF-8') : get_text($view['name']); ?></strong>
                        <?php if ($yt_meta_line) { ?><span class="board-yt-watch__stats"><?php echo htmlspecialchars($yt_meta_line, ENT_QUOTES, 'UTF-8'); ?></span><?php } ?>
                    </div>
                </div>
                <div class="board-yt-watch__share" id="bo_v_share">
                    <?php include_once(G5_SNS_PATH.'/view.sns.skin.php'); ?>
                    <?php if ($scrap_href) { ?><a href="<?php echo $scrap_href; ?>" target="_blank" class="btn btn_b03 board-yt-watch__scrap" onclick="win_scrap(this.href); return false;"><i class="fa fa-bookmark" aria-hidden="true"></i> 스크랩</a><?php } ?>
                </div>
            </section>

            <?php if ($yt_summary || trim($view['content'])) { ?>
            <section class="board-yt-watch__desc">
                <?php if ($yt_summary) { ?><p class="board-yt-watch__summary"><?php echo nl2br($yt_summary); ?></p><?php } ?>
                <?php if (trim($view['content'])) { ?>
                <div id="bo_v_con" class="board-yt-watch__content"><?php echo get_view_thumbnail($view['content']); ?></div>
                <?php } ?>
            </section>
            <?php } ?>

            <?php if ($good_href || $nogood_href) { ?>
            <div id="bo_v_act" class="board-yt-watch__vote">
                <?php if ($good_href) { ?><a href="<?php echo $good_href.'&amp;'.$qstr ?>" id="good_button" class="bo_v_good"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i><strong><?php echo number_format($view['wr_good']) ?></strong></a><?php } ?>
                <?php if ($nogood_href) { ?><a href="<?php echo $nogood_href.'&amp;'.$qstr ?>" id="nogood_button" class="bo_v_nogood"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i><strong><?php echo number_format($view['wr_nogood']) ?></strong></a><?php } ?>
            </div>
            <?php } ?>

            <section class="board-yt-watch__comments-wrap">
                <?php include_once(G5_BBS_PATH.'/view_comment.php'); ?>
            </section>
        </div>

        <aside class="board-yt-watch__sidebar" aria-label="다른 영상">
            <h2 class="board-yt-watch__sidebar-title">다른 영상</h2>
            <?php if (empty($yt_related)) { ?>
            <p class="board-yt-watch__sidebar-empty">다른 영상이 없습니다.</p>
            <?php } else { ?>
            <ul class="board-yt-sidebar">
                <?php foreach ($yt_related as $yt_row) {
                    echo g5b_youtube_sidebar_item_html($yt_row, $bo_table, (int) $view['wr_id']);
                } ?>
            </ul>
            <?php } ?>
        </aside>
    </div>

    <?php
    if ($yt_id) {
        g5b_youtube_print_video_schema($view, $yt_id);
    }
    ?>
</article>

<script>
<?php if ($board['bo_download_point'] < 0) { ?>
$(function() {
    $("a.view_file_download").click(function() {
        if(!g5_is_member) { alert("다운로드 권한이 없습니다."); return false; }
        if(confirm("포인트가 차감됩니다. 다운로드하시겠습니까?")) {
            $(this).attr("href", $(this).attr("href")+"&js=on");
            return true;
        }
        return false;
    });
});
<?php } ?>
function board_move(href) { window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1"); }
$(function() {
    $("a.view_image").click(function() { window.open(this.href, "large_image", "resizable=yes,scrollbars=yes"); return false; });
    $("#bo_v_atc").viewimageresize();
});
</script>
