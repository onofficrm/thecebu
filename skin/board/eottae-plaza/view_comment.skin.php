<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$plaza_login_url = function_exists('eottae_plaza_login_url')
    ? eottae_plaza_login_url(G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id)
    : G5_BBS_URL.'/login.php';
$plaza_is_super = ($is_admin === 'super');
include_once G5_PATH.'/components/eottae/plaza-report.php';
?>

<section id="bo_vc" class="plaza-comments">
    <h2 class="plaza-comments__title">댓글 <?php echo number_format((int) $view['wr_comment']); ?></h2>

    <?php if (count($list) < 1) { ?>
    <p class="plaza-comments__empty">등록된 댓글이 없습니다.</p>
    <?php } ?>

    <ul class="plaza-comments__list">
        <?php
        for ($i = 0; $i < count($list); $i++) {
            if (function_exists('eottae_plaza_is_comment_visible') && !eottae_plaza_is_comment_visible($list[$i], $plaza_is_super)) {
                continue;
            }
            $comment_id = (int) $list[$i]['wr_id'];
            $comment = $list[$i]['content'];
            $comment = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp|mms)\:\/\/([^[:space:]]+)\.(mp3|wma|wmv|asf|asx|mpg|mpeg)\".*\<\/a\>\]/i", '', $comment);
            ?>
        <li id="c_<?php echo $comment_id; ?>" class="plaza-comment">
            <div class="plaza-comment__head">
                <strong class="plaza-comment__author"><?php
                if (function_exists('eottae_member_growth_render_author_line') && !empty($list[$i]['mb_id'])) {
                    echo eottae_member_growth_render_author_line($list[$i]['mb_id'], $list[$i]['name'], array('inline' => true, 'badge_only' => true));
                } else {
                    echo $list[$i]['name'];
                }
                ?></strong>
                <time class="plaza-comment__time" datetime="<?php echo date('c', strtotime($list[$i]['datetime'])); ?>"><?php echo get_text($list[$i]['datetime']); ?></time>
            </div>
            <div class="plaza-comment__body"><?php echo $comment; ?></div>
            <div class="plaza-comment__actions">
            <?php if (!empty($list[$i]['is_del'])) { ?>
            <a href="<?php echo $list[$i]['del_link']; ?>" class="plaza-comment__delete" onclick="return confirm('댓글을 삭제하시겠습니까?');">삭제</a>
            <?php } ?>
            </div>
        </li>
        <?php } ?>
    </ul>
</section>

<?php if ($is_comment_write) { ?>
<aside id="plaza_comment_form" class="plaza-comment-form">
    <h3 class="plaza-comment-form__title">댓글 쓰기</h3>
    <form name="fviewcomment" id="fviewcomment" action="<?php echo $comment_action_url; ?>" onsubmit="return fviewcomment_submit(this);" method="post" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w === '' ? 'c' : $w; ?>" id="w">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="comment_id" value="<?php echo $c_id ?>" id="comment_id">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="is_good" value="">
    <textarea id="wr_content" name="wr_content" maxlength="1000" required class="plaza-comment-form__textarea" placeholder="댓글을 입력해 주세요"><?php echo $c_wr_content; ?></textarea>
    <button type="submit" id="btn_submit" class="plaza-btn plaza-btn--primary plaza-btn--block">댓글 등록</button>
    </form>
</aside>
<?php } else { ?>
<aside class="plaza-comment-form plaza-comment-form--guest">
    <p class="plaza-comment-form__login">댓글을 남기려면 <a href="<?php echo $plaza_login_url; ?>">로그인</a>해 주세요.</p>
</aside>
<?php } ?>

<script>
function fviewcomment_submit(f)
{
    if (!f.wr_content.value.trim()) {
        alert('댓글을 입력해 주세요.');
        f.wr_content.focus();
        return false;
    }
    document.getElementById('btn_submit').disabled = true;
    return true;
}
</script>
<?php eottae_plaza_render_comment_report_assets($list, $view, $member, $is_admin); ?>
