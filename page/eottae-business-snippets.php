<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-business-snippets.php'));
}

if (!function_exists('eottae_is_business_member') || !eottae_is_business_member($member)) {
    alert('사업자 회원만 이용할 수 있습니다.', G5_URL.'/page/eottae-mypage.php');
}

include_once G5_LIB_PATH.'/eottae-business-snippet.lib.php';

$community_write_url = function_exists('eottae_business_snippet_write_url')
    ? eottae_business_snippet_write_url(defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community')
    : G5_BBS_URL.'/write.php?bo_table='.(defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community');
$snippet_max = eottae_business_snippet_max_count();

g5_page_start('홍보 문구 관리');
?>

<main class="business-snippets-page">
    <header class="business-snippets-page__header">
        <a href="<?php echo G5_URL; ?>/page/eottae-mypage.php" class="business-snippets-page__back">← 마이페이지</a>
        <h1 class="business-snippets-page__title">자주 쓰는 홍보 문구</h1>
        <p class="business-snippets-page__desc">PC에서 문구를 만들고 관리한 뒤, 커뮤니티에서 <strong>분류 → 광고판</strong>을 선택한 글쓰기 화면에서 불러올 수 있습니다. AI 생성도 PC·모바일 모두에서 이용할 수 있습니다.</p>
    </header>

    <section class="business-snippets-manager" data-business-snippets-manager aria-label="홍보 문구 관리">
        <div class="business-snippets-manager__editor">
            <input type="hidden" id="business_snippet_id" value="0">

            <div class="business-snippets-manager__field">
                <label for="business_snippet_label">문구 이름</label>
                <input type="text" id="business_snippet_label" class="business-snippets-manager__input" maxlength="100" placeholder="예: 주말 할인, 신메뉴 홍보">
            </div>

            <div class="business-snippets-manager__field">
                <label for="business_snippet_subject">게시글 제목</label>
                <input type="text" id="business_snippet_subject" class="business-snippets-manager__input" maxlength="255" placeholder="커뮤니티에 올릴 제목">
            </div>

            <div class="business-snippets-manager__field">
                <label for="business_snippet_content">게시글 내용</label>
                <textarea id="business_snippet_content" class="business-snippets-manager__textarea" rows="10" placeholder="홍보 본문을 입력하세요"></textarea>
            </div>

            <div class="business-snippets-manager__toolbar">
                <button type="button" class="business-snippets__btn business-snippets__btn--primary" data-manager-ai-generate>AI 홍보 문구 생성</button>
                <button type="button" class="business-snippets__btn" data-manager-save>저장</button>
                <button type="button" class="business-snippets__btn" data-manager-reset>새로 작성</button>
                <a href="<?php echo $community_write_url; ?>" class="business-snippets__btn business-snippets__btn--link" data-manager-write-link>커뮤니티 글쓰기</a>
            </div>

            <p class="business-snippets__status" data-manager-status aria-live="polite"></p>
            <p class="business-snippets-manager__hint">최대 <?php echo (int) $snippet_max; ?>개까지 저장할 수 있습니다.</p>
        </div>

        <div class="business-snippets-manager__list-wrap">
            <h2 class="business-snippets-manager__list-title">저장된 문구</h2>
            <ul class="business-snippets-manager__list" data-manager-list></ul>
            <p class="business-snippets__empty" data-manager-empty hidden>저장된 홍보 문구가 없습니다.</p>
        </div>
    </section>
</main>

<?php
g5_page_end();
