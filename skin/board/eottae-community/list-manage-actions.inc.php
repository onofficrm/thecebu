<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 목록 카드 관리 버튼 (작성자·최고관리자)
 *
 * @var string $item_manage_delete_href
 * @var string $item_manage_update_href
 * @var bool   $item_manage_show_checkbox
 * @var int    $item_manage_chk_index
 * @var int    $item_manage_wr_id
 */
$item_manage_delete_href = $item_manage_delete_href ?? '';
$item_manage_update_href = $item_manage_update_href ?? '';
$item_manage_show_checkbox = !empty($item_manage_show_checkbox);
$item_manage_chk_index = isset($item_manage_chk_index) ? (int) $item_manage_chk_index : 0;
$item_manage_wr_id = isset($item_manage_wr_id) ? (int) $item_manage_wr_id : 0;

if (!$item_manage_show_checkbox && $item_manage_delete_href === '' && $item_manage_update_href === '') {
    return;
}
?>
<div class="community-post__manage" role="group" aria-label="게시글 관리">
    <?php if ($item_manage_show_checkbox && $item_manage_wr_id > 0) { ?>
    <label class="community-post__manage-chk" for="chk_wr_id_<?php echo $item_manage_chk_index; ?>">
        <input type="checkbox" name="chk_wr_id[]" value="<?php echo $item_manage_wr_id; ?>" id="chk_wr_id_<?php echo $item_manage_chk_index; ?>">
        <span class="sound_only">선택</span>
    </label>
    <?php } ?>
    <?php if ($item_manage_update_href !== '') { ?>
    <a href="<?php echo $item_manage_update_href; ?>" class="community-post__manage-btn">수정</a>
    <?php } ?>
    <?php if ($item_manage_delete_href !== '') { ?>
    <a href="<?php echo $item_manage_delete_href; ?>" class="community-post__manage-btn community-post__manage-btn--delete" onclick="return confirm('이 게시글을 삭제하시겠습니까?');">삭제</a>
    <?php } ?>
</div>
