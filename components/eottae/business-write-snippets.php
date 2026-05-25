<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (empty($is_member) || !function_exists('eottae_is_business_member') || !eottae_is_business_member($member)) {
    return;
}
?>

<section class="business-snippets" data-business-snippets aria-label="자주 쓰는 홍보 문구">
    <button type="button" class="business-snippets__toggle" data-snippets-toggle aria-expanded="false">
        자주 쓰는 홍보 문구
    </button>

    <div class="business-snippets__panel" data-snippets-panel hidden>
        <p class="business-snippets__desc">PC·모바일에서 저장해 두었다가 한 번에 불러와 게시글에 붙여 넣을 수 있습니다.</p>

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
