<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
include_once G5_LIB_PATH.'/eottae-plaza-likes.lib.php';
include_once G5_PATH.'/components/eottae/plaza-like.php';
include_once G5_PATH.'/components/eottae/plaza-report.php';
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
if (function_exists('eottae_plaza_load_assets')) {
    eottae_plaza_load_assets();
}

$view_type = isset($view['ca_name']) ? get_text($view['ca_name']) : '';
$view_region = isset($view['wr_1']) ? get_text($view['wr_1']) : '';
$view_thumb = eottae_plaza_list_thumb($bo_table, (int) $view['wr_id']);
$list_url = function_exists('eottae_plaza_list_url') ? eottae_plaza_list_url() : get_pretty_url($bo_table);
$time_label = eottae_plaza_relative_time($view['wr_datetime'] ?? '');
$view_wr_id = (int) ($view['wr_id'] ?? 0);
$view_like_count = eottae_plaza_like_count($view_wr_id);
$view_is_liked = (!empty($is_member) && !empty($member['mb_id']))
    ? !empty(eottae_plaza_user_liked_batch($member['mb_id'], array($view_wr_id))[$view_wr_id])
    : false;
$view_can_like = !empty($is_member) && !empty($member['mb_id']) && $member['mb_id'] !== ($view['mb_id'] ?? '');
$plaza_member_token = eottae_plaza_member_token();
$plaza_login_url = eottae_plaza_login_url(G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$view_wr_id);
include_once G5_PATH.'/components/eottae/plaza-talk-guide.php';
include_once G5_PATH.'/components/eottae/plaza-related-rooms.php';
include_once G5_PATH.'/components/eottae/plaza-ai-message-ui.php';
$view_is_ai = eottae_plaza_ai_message_is_ai($view);
$view_page_class = $view_is_ai ? ' plaza-view-page--ai is-plaza-ai-message' : '';
?>

<div class="plaza-view-page board-wrap board-wrap--eottae-plaza<?php echo $view_page_class; ?>" id="bo_v" style="width:<?php echo $width; ?>">

    <header class="plaza-view-page__header">
        <a href="<?php echo $list_href ? $list_href : $list_url; ?>" class="plaza-view-page__back">← 목록</a>
    </header>

    <article class="plaza-view-page__article<?php echo $view_is_ai ? ' plaza-view-page__article--ai' : ''; ?>">
        <div class="plaza-view-page__badges">
            <?php if ($view_is_ai) { ?>
            <?php echo eottae_plaza_ai_message_render_badge($view); ?>
            <?php } elseif ($view_type !== '') { ?>
            <span class="plaza-badge <?php echo eottae_plaza_type_badge_class($view_type); ?>"><?php echo $view_type; ?></span>
            <?php } ?>
            <?php if ($view_region !== '' && $view_region !== '기타') { ?>
            <span class="plaza-badge plaza-badge--region"><?php echo $view_region; ?></span>
            <?php } ?>
        </div>

        <h1 class="plaza-view-page__title"><?php echo get_text($view['wr_subject']); ?></h1>

        <div class="plaza-view-page__meta">
            <?php if ($view_is_ai) { ?>
            <span class="plaza-view-page__author"><?php echo eottae_plaza_ai_message_display_name($view); ?></span>
            <?php } else { ?>
            <span class="plaza-view-page__author"><?php echo $view['name']; ?></span>
            <?php } ?>
            <?php if ($time_label !== '') { ?>
            <span class="plaza-view-page__time"><?php echo get_text($time_label); ?></span>
            <?php } ?>
            <span class="plaza-view-page__hit">조회 <?php echo number_format((int) $view['wr_hit']); ?></span>
            <?php if ((int) $view['wr_comment'] > 0) { ?>
            <span class="plaza-view-page__comment">댓글 <?php echo number_format((int) $view['wr_comment']); ?></span>
            <?php } ?>
        </div>

        <?php if ($view_thumb) { ?>
        <div class="plaza-view-page__thumb">
            <img src="<?php echo htmlspecialchars($view_thumb, ENT_QUOTES, 'UTF-8'); ?>" alt="">
        </div>
        <?php } ?>

        <section class="plaza-view-page__body<?php echo $view_is_ai ? ' plaza-view-page__body--ai' : ''; ?>" id="bo_v_con">
            <?php if ($view_is_ai) { ?><div class="plaza-ai-bubble"><?php } ?>
            <?php echo get_view_thumbnail($view['content']); ?>
            <?php if ($view_is_ai) { ?></div><?php } ?>
        </section>
    </article>

    <footer class="plaza-view-page__actions">
        <div class="plaza-view-page__engage">
            <?php
            eottae_plaza_render_like_button(
                $view_wr_id,
                $view_like_count,
                $view_is_liked,
                $view_can_like || (!$is_member && $plaza_login_url !== ''),
                $plaza_login_url
            );
            ?>
            <?php eottae_plaza_render_post_report_button($view, $member, $is_admin); ?>
        </div>
        <div class="plaza-view-page__nav">
        <a href="<?php echo $list_href ? $list_href : $list_url; ?>" class="plaza-btn plaza-btn--ghost">목록</a>
        <?php if ($is_comment_write) { ?>
        <a href="#plaza_comment_form" class="plaza-btn plaza-btn--primary">댓글 쓰기</a>
        <?php } ?>
        <?php if ($update_href && ($is_admin === 'super' || !$view_is_ai)) { ?><a href="<?php echo $update_href; ?>" class="plaza-btn plaza-btn--ghost">수정</a><?php } ?>
        <?php if ($delete_href && ($is_admin === 'super' || !$view_is_ai)) { ?><a href="<?php echo $delete_href; ?>" class="plaza-btn plaza-btn--ghost" onclick="return confirm('삭제하시겠습니까?');">삭제</a><?php } ?>
        </div>
    </footer>

    <?php eottae_plaza_render_related_rooms($view, 3); ?>
    <?php eottae_plaza_render_talk_guide('view'); ?>

    <?php include_once G5_BBS_PATH.'/view_comment.php'; ?>
</div>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>
<?php eottae_plaza_render_like_script($plaza_member_token, $plaza_login_url); ?>
