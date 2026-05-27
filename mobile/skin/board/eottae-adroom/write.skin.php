<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae.lib.php';
include_once G5_LIB_PATH.'/eottae-adroom.lib.php';
include_once G5_PATH.'/components/eottae/adroom-shop-picker.php';
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/eottae-adroom.js"></script>', 10);

global $member, $is_admin;

$adroom_tabs = eottae_adroom_category_tabs($board);
$write_category = $sca !== '' ? $sca : (isset($write['ca_name']) ? get_text($write['ca_name']) : '');
$list_url = eottae_adroom_list_url();

$selected_bo = isset($write['wr_1']) ? (string) $write['wr_1'] : '';
$selected_wr_id = isset($write['wr_2']) ? (int) $write['wr_2'] : 0;
$member_shops = array();
if (!empty($member['mb_id'])) {
    $member_shops = eottae_adroom_member_shops($member['mb_id']);
}
if ($selected_bo === '' && $selected_wr_id < 1 && count($member_shops) === 1) {
    $selected_bo = (string) ($member_shops[0]['bo_table'] ?? '');
    $selected_wr_id = (int) ($member_shops[0]['wr_id'] ?? 0);
}
?>

<div class="adroom-write board-wrap board-wrap--eottae-adroom" id="bo_w" style="width:<?php echo $width; ?>">

    <header class="adroom-write__header">
        <a href="<?php echo $list_url; ?>" class="adroom-write__back">← 광고방 목록</a>
        <h1 class="adroom-write__title"><?php echo $w === 'u' ? '광고 수정' : '광고 등록'; ?></h1>
        <p class="adroom-write__desc">홍보·이벤트·할인 정보를 작성하고, 노출할 업체를 선택하세요. 업체 지도·주소가 글과 함께 연동됩니다.</p>
    </header>

    <form name="fwrite" id="fwrite" class="adroom-write__form" action="<?php echo $action_url; ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>">
    <input type="hidden" name="sca" value="<?php echo $sca; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <?php include G5_PATH.'/components/eottae/board-write-options.php'; ?>

    <?php echo eottae_adroom_render_shop_picker($member_shops, $selected_bo, $selected_wr_id); ?>

    <?php if ($is_category && !empty($adroom_tabs)) { ?>
    <div class="adroom-write__field">
        <label for="ca_name">분류</label>
        <select name="ca_name" id="ca_name" class="adroom-write__select" required>
            <option value="">분류 선택</option>
            <?php foreach ($adroom_tabs as $tab) {
                if ($tab['slug'] === '') {
                    continue;
                } ?>
            <option value="<?php echo get_text($tab['slug']); ?>"<?php echo ($write_category === $tab['slug']) ? ' selected' : ''; ?>><?php echo get_text($tab['label']); ?></option>
            <?php } ?>
        </select>
    </div>
    <?php } ?>

    <div class="adroom-write__field">
        <label for="wr_subject">제목 <span class="adroom-required">*</span></label>
        <input type="text" name="wr_subject" id="wr_subject" value="<?php echo get_text($subject); ?>" required maxlength="255" class="adroom-write__input" placeholder="예) 오픈 기념 20% 할인 이벤트">
    </div>

    <div class="adroom-write__field adroom-write__field--content">
        <label for="wr_content">본문 <span class="adroom-required">*</span></label>
        <?php echo $editor_html; ?>
    </div>

    <?php
    $file_count = isset($file_count) ? (int) $file_count : 0;
    if ($file_count > 0) {
        ?>
    <div class="adroom-write__field adroom-write__field--files">
        <label>대표 이미지</label>
        <p class="adroom-write__hint">첨부 이미지가 있으면 목록 썸네일로 사용됩니다. 없으면 연동 업체 사진이 표시됩니다.</p>
        <?php for ($i = 0; $i < $file_count; $i++) { ?>
        <div class="adroom-write__file">
            <input type="file" name="bf_file[]" id="bf_file_<?php echo $i; ?>" title="파일첨부 <?php echo $i + 1; ?>" class="adroom-write__file-input">
            <?php if ($w === 'u' && !empty($file[$i]['file'])) { ?>
            <label class="adroom-write__file-del"><input type="checkbox" name="bf_file_del[<?php echo $i; ?>]" value="1"> <?php echo get_text($file[$i]['source']); ?> 삭제</label>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if (!empty($is_use_captcha)) { ?>
    <div class="adroom-write__field"><?php echo $captcha_html; ?></div>
    <?php } ?>

    <div class="adroom-write__actions">
        <a href="<?php echo $list_url; ?>" class="adroom-btn adroom-btn--ghost">취소</a>
        <button type="submit" class="adroom-btn adroom-btn--primary" id="btn_submit">등록하기</button>
    </div>
    </form>
</div>

<script>
function fwrite_submit(f) {
    <?php echo $editor_js; ?>

    var bo = document.getElementById('eottae_adroom_shop_bo_table');
    var wr = document.getElementById('eottae_adroom_shop_wr_id');
    if (bo && wr && (!bo.value || !parseInt(wr.value, 10))) {
        alert('연동할 업체를 선택해 주세요.');
        return false;
    }
    <?php echo $captcha_js; ?>
    document.getElementById('btn_submit').disabled = true;
    return true;
}
</script>
