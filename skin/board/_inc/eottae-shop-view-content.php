<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (empty($shop_youtube_id) && !empty($shop) && function_exists('eottae_shop_youtube_id')) {
    $shop_youtube_id = eottae_shop_youtube_id($shop);
}

$shop_content_html = get_view_thumbnail($view['content']);
$shop_content_plain = trim(strip_tags($shop_content_html));
$shop_has_content = $shop_content_plain !== '';
?>

<section class="shop-detail-page__content" id="bo_v_con"<?php if ($shop_can_edit_content) { ?> data-shop-content-edit data-shop-content-use-editor="<?php echo $shop_content_use_editor ? '1' : '0'; ?>" data-shop-content-token="<?php echo htmlspecialchars($shop_content_token, ENT_QUOTES, 'UTF-8'); ?>" data-shop-content-bo-table="<?php echo htmlspecialchars($bo_table, ENT_QUOTES, 'UTF-8'); ?>" data-shop-content-wr-id="<?php echo (int) $view['wr_id']; ?>" data-shop-content-action="<?php echo G5_URL; ?>/proc/eottae-shop-content-update.php"<?php } ?>>
    <header class="shop-detail-page__content-head">
        <h2 class="shop-detail-page__content-title">업체 소개</h2>
        <?php if ($shop_can_edit_content) { ?>
        <button type="button" class="shop-detail-page__content-edit-btn" data-shop-content-edit-open>본문 수정</button>
        <?php } ?>
    </header>

    <div class="shop-detail-page__content-view" id="shopContentView">
        <?php if ($shop_youtube_id) {
            include_once(G5_SKIN_PATH.'/board/_inc/g5b-youtube.php');
            ?>
        <div class="shop-detail-page__video" aria-label="소개 영상">
            <?php echo g5b_youtube_embed_html($shop_youtube_id, $shop['name'].' 소개 영상'); ?>
        </div>
        <?php } ?>
        <div class="shop-detail-page__content-body" id="shopContentBody" data-translation-content>
            <?php if ($shop_has_content) {
                echo $shop_content_html;
            } else { ?>
            <p class="shop-detail-page__content-empty">등록된 소개글이 없습니다.<?php if ($shop_can_edit_content) { ?> <button type="button" class="shop-detail-page__content-empty-link" data-shop-content-edit-open>지금 작성하기</button><?php } ?></p>
            <?php } ?>
        </div>
    </div>

    <?php if ($shop_can_edit_content) { ?>
    <div class="shop-detail-page__content-edit" id="shopContentEdit" hidden>
        <p class="shop-detail-page__content-edit-label">소개글 편집</p>
        <?php echo $shop_content_editor_html; ?>
        <div class="shop-detail-page__content-edit-actions">
            <button type="button" class="btn_b01 btn shop-detail-page__content-save" data-shop-content-save>저장</button>
            <button type="button" class="btn_b01 btn shop-detail-page__content-cancel" data-shop-content-cancel>취소</button>
        </div>
        <p class="shop-detail-page__content-edit-status" data-shop-content-status role="status" aria-live="polite"></p>
    </div>
    <?php } ?>
</section>
