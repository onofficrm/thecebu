<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-coupons.php'));
}

g5_page_start('쿠폰함');
?>

<main class="mypage-subpage">
    <h1 class="mypage-subpage__title">쿠폰함</h1>

    <section class="mypage-coupon-summary" style="margin-bottom:20px">
        <div class="mypage-coupon-summary__box" style="grid-column:1/-1">
            <p class="mypage-coupon-summary__label">사용 가능 쿠폰</p>
            <p class="mypage-coupon-summary__value">0</p>
        </div>
    </section>

    <div class="empty-state">
        <p class="empty-state__title">보유 쿠폰이 없습니다</p>
        <p>업체 이벤트·프로모션 쿠폰은 3차 개발에서 자동 발급됩니다.</p>
    </div>

    <div class="coupon-card" style="opacity:0.55;margin-top:16px" aria-hidden="true">
        <h3 class="coupon-card__title">샘플 — 신규 가입 웰컴 쿠폰</h3>
        <p class="coupon-card__desc">UI 미리보기용 샘플 카드입니다. (실제 사용 불가)</p>
    </div>
</main>

<?php
g5_page_end();
