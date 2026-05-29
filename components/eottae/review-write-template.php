<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_is_review_board') || !eottae_is_review_board($bo_table ?? '')) {
    return;
}

if (function_exists('eottae_review_board_load_assets')) {
    eottae_review_board_load_assets();
}
?>

<section class="sebu-review-template" id="sebuReviewTemplate" aria-labelledby="sebuReviewTemplateTitle">
    <?php include G5_PATH.'/components/eottae/review-shop-picker.php'; ?>

    <div class="community-write-page__field">
        <label for="wr_subject">제목</label>
        <input type="text" name="wr_subject" id="wr_subject" value="<?php echo isset($subject) ? $subject : ''; ?>" required maxlength="255" class="community-write-page__input" placeholder="예: J Park Korean BBQ 이용 후기">
    </div>

    <?php
    $eottae_editor_placeholder = '업체 이용 경험, 추천 메뉴, 분위기, 팁 등을 자유롭게 작성해 주세요.';
    include G5_PATH.'/components/eottae/board-write-editor.php';
    ?>
</section>
