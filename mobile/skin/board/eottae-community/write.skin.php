<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/eottae.lib.php');
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$community_tabs = eottae_community_category_tabs($board);
$write_category = $sca !== '' ? $sca : (isset($write['ca_name']) ? get_text($write['ca_name']) : '');
?>

<div class="community-write-page board-wrap board-wrap--eottae-community" id="bo_w" style="width:<?php echo $width; ?>">

    <header class="community-write-page__header">
        <a href="<?php echo eottae_community_list_url($sca ? array('sca' => $sca) : array()); ?>" class="community-write-page__back">← 목록으로</a>
        <h1 class="community-write-page__title"><?php echo $w === 'u' ? '글 수정' : '글쓰기'; ?></h1>
        <p class="community-write-page__desc">세부 생활 정보를 공유해 주세요. 타인을 비방하거나 욕설, 광고성 글은 안내 없이 삭제될 수 있습니다.</p>
    </header>

    <form name="fwrite" id="fwrite" class="community-write-page__form" action="<?php echo $action_url; ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <?php include G5_PATH.'/components/eottae/board-write-options.php'; ?>

    <?php if ($is_category && !empty($community_tabs)) { ?>
    <div class="community-write-page__field">
        <label for="ca_name">분류</label>
        <select name="ca_name" id="ca_name" class="community-write-page__select" required>
            <option value="">분류 선택</option>
            <?php foreach ($community_tabs as $tab) {
                if ($tab['slug'] === '') {
                    continue;
                } ?>
            <option value="<?php echo get_text($tab['slug']); ?>"<?php echo ($write_category === $tab['slug']) ? ' selected' : ''; ?>><?php echo get_text($tab['label']); ?></option>
            <?php } ?>
        </select>
    </div>
    <?php } ?>

    <div class="community-write-page__field">
        <label for="wr_subject">제목</label>
        <input type="text" name="wr_subject" id="wr_subject" value="<?php echo $subject; ?>" required maxlength="255" placeholder="제목을 입력하세요" class="community-write-page__input">
    </div>

    <div class="community-write-page__field community-write-page__field--content">
        <label for="wr_content">내용</label>
        <textarea name="wr_content" id="wr_content" required class="community-write-page__textarea" placeholder="세부 생활 정보, 꿀팁, 질문 등을 자유롭게 작성해 주세요"><?php echo $content; ?></textarea>
    </div>

    <?php if ($file_count > 0) { ?>
    <div class="community-write-page__photos">
        <p class="community-write-page__photos-label">사진 첨부 <span>(최대 <?php echo (int) $file_count; ?>장)</span></p>
        <div class="community-write-page__photo-grid">
            <?php for ($i = 0; $i < $file_count; $i++) { ?>
            <label class="community-write-page__photo-slot" for="bf_file_<?php echo $i + 1; ?>">
                <input type="file" name="bf_file[]" id="bf_file_<?php echo $i + 1; ?>" accept="image/*" class="community-write-page__photo-input" data-photo-preview>
                <span class="community-write-page__photo-placeholder">+</span>
                <img src="" alt="" class="community-write-page__photo-preview" hidden>
                <?php if ($w === 'u' && isset($file[$i]['file']) && $file[$i]['file']) { ?>
                <span class="community-write-page__photo-current"><?php echo $file[$i]['source']; ?></span>
                <label class="community-write-page__photo-delete">
                    <input type="checkbox" name="bf_file_del[<?php echo $i; ?>]" value="1"> 삭제
                </label>
                <?php } ?>
            </label>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <?php if ($is_use_captcha) { ?>
    <div class="community-write-page__captcha"><?php echo $captcha_html; ?></div>
    <?php } ?>

    <div class="community-write-page__actions">
        <a href="<?php echo eottae_community_list_url(); ?>" class="community-write-page__cancel">취소</a>
        <button type="submit" id="btn_submit" class="community-write-page__submit">등록하기</button>
    </div>
    </form>
</div>

<script>
function fwrite_submit(f) {
    <?php echo $editor_js; ?>

    var subject = "";
    var content = "";
    $.ajax({
        url: g5_bbs_url+"/ajax.filter.php",
        type: "POST",
        data: {
            "subject": f.wr_subject.value,
            "content": f.wr_content.value
        },
        dataType: "json",
        async: false,
        cache: false,
        success: function(data) {
            subject = data.subject;
            content = data.content;
        }
    });

    if (subject) {
        alert("제목에 금지단어('"+subject+"')가 포함되어 있습니다.");
        f.wr_subject.focus();
        return false;
    }
    if (content) {
        alert("내용에 금지단어('"+content+"')가 포함되어 있습니다.");
        f.wr_content.focus();
        return false;
    }

    <?php echo $captcha_js; ?>
    document.getElementById('btn_submit').disabled = true;
    return true;
}
</script>
