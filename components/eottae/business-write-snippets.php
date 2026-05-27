<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (empty($is_member) || !function_exists('eottae_is_business_member') || !eottae_is_business_member($member)) {
    return;
}

include_once G5_LIB_PATH.'/eottae-business-snippet.lib.php';

if (empty($board) || !eottae_business_snippet_board_has_promo_category($board)) {
    return;
}

$snippet_write_category = isset($write_category) ? (string) $write_category : '';
$snippet_allowed_now = eottae_business_snippet_write_allowed($bo_table, $snippet_write_category);
$snippet_categories = eottae_business_snippet_promo_category_names();
?>

<section class="business-snippets<?php echo $snippet_allowed_now ? '' : ' business-snippets--hidden'; ?>"
         data-business-snippets
         data-snippets-allowed-categories="<?php echo htmlspecialchars(implode(',', $snippet_categories), ENT_QUOTES, 'UTF-8'); ?>"
         <?php echo $snippet_allowed_now ? '' : ' hidden'; ?>
         aria-label="자주 쓰는 홍보 문구">
    <button type="button" class="business-snippets__toggle" data-snippets-toggle aria-expanded="false">
        자주 쓰는 홍보 문구
    </button>

    <div class="business-snippets__panel" data-snippets-panel hidden>
        <p class="business-snippets__desc">분류를 <strong>광고판</strong>으로 선택한 경우에만 사용할 수 있습니다. 저장해 둔 문구를 불러와 게시글에 붙여 넣을 수 있습니다.</p>

        <div class="business-snippets__toolbar">
            <button type="button" class="business-snippets__btn business-snippets__btn--primary" data-snippets-ai-generate>AI 홍보 문구 생성</button>
            <button type="button" class="business-snippets__btn" data-snippets-save-current>현재 내용 저장</button>
            <a href="<?php echo G5_URL; ?>/page/eottae-business-snippets.php" class="business-snippets__btn business-snippets__btn--link">문구 관리</a>
        </div>

        <p class="eottae-field__hint business-snippets__status" data-snippets-status aria-live="polite"></p>

        <ul class="business-snippets__list" data-snippets-list></ul>
        <p class="business-snippets__empty" data-snippets-empty hidden>저장된 홍보 문구가 없습니다. AI로 생성하거나 현재 내용을 저장해 보세요.</p>
    </div>
</section>
