<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
if (function_exists('eottae_plaza_load_assets')) {
    eottae_plaza_load_assets();
}

$plaza_types = eottae_plaza_type_options();
$plaza_regions = eottae_plaza_region_options();
$write_type = $sca !== '' ? $sca : (isset($write['ca_name']) ? get_text($write['ca_name']) : '');
$write_region = isset($write['wr_1']) ? get_text($write['wr_1']) : '';
include_once G5_PATH.'/components/eottae/plaza-rules.php';
$list_url = function_exists('eottae_plaza_list_url') ? eottae_plaza_list_url() : get_pretty_url($bo_table);
?>

<div class="plaza-write-page board-wrap board-wrap--eottae-plaza" id="bo_w" style="width:<?php echo $width; ?>">

    <header class="plaza-write-page__header">
        <a href="<?php echo $list_url; ?>" class="plaza-write-page__back">← 목록으로</a>
        <h1 class="plaza-write-page__title"><?php echo $w === 'u' ? '글 수정' : '글쓰기'; ?></h1>
        <p class="plaza-write-page__notice">세부광장은 모두가 보는 공개 공간입니다.</p>
    </header>

    <?php eottae_plaza_render_rules(true); ?>

    <form name="fwrite" id="fwrite" class="plaza-write-page__form" action="<?php echo $action_url; ?>" onsubmit="return plaza_fwrite_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="wr_2" value="<?php echo get_text($write['wr_2'] ?? 'visible'); ?>">
    <?php include G5_PATH.'/components/eottae/board-write-options.php'; ?>

    <div class="plaza-write-page__field">
        <label for="ca_name">글 유형</label>
        <select name="ca_name" id="ca_name" class="plaza-write-page__select" required>
            <option value="">유형 선택</option>
            <?php foreach ($plaza_types as $type_opt) { ?>
            <option value="<?php echo get_text($type_opt['slug']); ?>"<?php echo $write_type === $type_opt['slug'] ? ' selected' : ''; ?>><?php echo get_text($type_opt['label']); ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="plaza-write-page__field">
        <label for="wr_1">지역</label>
        <select name="wr_1" id="wr_1" class="plaza-write-page__select" required>
            <option value="">지역 선택</option>
            <?php foreach ($plaza_regions as $region) { ?>
            <option value="<?php echo get_text($region); ?>"<?php echo $write_region === $region ? ' selected' : ''; ?>><?php echo get_text($region); ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="plaza-write-page__field">
        <label for="wr_subject">제목</label>
        <input type="text" name="wr_subject" id="wr_subject" value="<?php echo $subject; ?>" maxlength="255" placeholder="제목 (선택)" class="plaza-write-page__input">
    </div>

    <div class="plaza-write-page__field plaza-write-page__field--content">
        <label for="wr_content">내용</label>
        <textarea name="wr_content" id="wr_content" required class="plaza-write-page__textarea" placeholder="지금 세부에서 궁금한 것, 나누고 싶은 이야기, 함께할 모임을 남겨보세요."><?php echo $content; ?></textarea>
    </div>

    <?php if ($file_count > 0) { ?>
    <div class="plaza-write-page__photos">
        <p class="plaza-write-page__photos-label">이미지 첨부 <span>(최대 <?php echo (int) $file_count; ?>장)</span></p>
        <div class="plaza-write-page__photo-grid">
            <?php for ($i = 0; $i < $file_count; $i++) { ?>
            <label class="plaza-write-page__photo-slot" for="bf_file_<?php echo $i + 1; ?>">
                <input type="file" name="bf_file[]" id="bf_file_<?php echo $i + 1; ?>" accept="image/*" class="plaza-write-page__photo-input">
                <span class="plaza-write-page__photo-placeholder">+</span>
                <?php if ($w === 'u' && isset($file[$i]['file']) && $file[$i]['file']) { ?>
                <span class="plaza-write-page__photo-current"><?php echo get_text($file[$i]['source']); ?></span>
                <label class="plaza-write-page__photo-delete">
                    <input type="checkbox" name="bf_file_del[<?php echo $i; ?>]" value="1"> 삭제
                </label>
                <?php } ?>
            </label>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <div class="plaza-write-page__actions">
        <button type="submit" class="plaza-btn plaza-btn--primary plaza-btn--block"><?php echo $w === 'u' ? '수정하기' : '등록하기'; ?></button>
        <a href="<?php echo $list_url; ?>" class="plaza-btn plaza-btn--ghost plaza-btn--block">취소</a>
    </div>
    </form>
</div>

<script>
function plaza_fwrite_submit(f) {
    if (!f.ca_name.value) {
        alert('글 유형을 선택해 주세요.');
        f.ca_name.focus();
        return false;
    }
    if (!f.wr_1.value) {
        alert('지역을 선택해 주세요.');
        f.wr_1.focus();
        return false;
    }
    if (!f.wr_content.value.trim()) {
        alert('내용을 입력해 주세요.');
        f.wr_content.focus();
        return false;
    }
    return true;
}
</script>
