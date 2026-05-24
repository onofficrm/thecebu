<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<section class="board-write board-wrap board-wrap--eottae-community" id="bo_w" style="width:<?php echo $width; ?>">
    <h2 class="sound_only"><?php echo $g5['title']; ?></h2>

    <form name="fwrite" id="fwrite" action="<?php echo $action_url; ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <?php echo $option_hidden; ?>

    <div class="eottae-field">
        <label for="wr_subject">제목</label>
        <input type="text" name="wr_subject" id="wr_subject" value="<?php echo $subject; ?>" required maxlength="255">
    </div>
    <div class="eottae-field">
        <label for="wr_content">내용</label>
        <textarea name="wr_content" id="wr_content" required><?php echo $content; ?></textarea>
    </div>
    <?php if ($is_use_captcha) { echo $captcha_html; } ?>
    <button type="submit" id="btn_submit" class="inquiry-button__btn inquiry-button__btn--inquiry" style="width:100%;margin-top:16px">등록</button>
    </form>
</section>

<script>
function fwrite_submit(f) {
    <?php echo $editor_js; ?>
    <?php echo $captcha_js; ?>
    return true;
}
</script>
