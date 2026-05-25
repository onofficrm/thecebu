<?php
include_once(dirname(__FILE__).'/_init.php');

$coupon_url = G5_URL.'/page/eottae-coupons.php';
if (!$is_member) {
    $coupon_url = function_exists('eottae_login_url')
        ? eottae_login_url(G5_URL.'/page/eottae-coupons.php')
        : G5_BBS_URL.'/login.php?url='.urlencode(G5_URL.'/page/eottae-coupons.php');
}

g5_page_start('쿠폰 사용 안내');
?>

<main class="eottae-guide-page eottae-guide-page--member">
    <header class="eottae-guide-page__header">
        <a href="<?php echo $is_member ? G5_URL.'/page/eottae-coupons.php' : G5_URL; ?>" class="eottae-guide-page__back">← <?php echo $is_member ? '쿠폰함' : '홈'; ?></a>
        <p class="eottae-guide-page__badge">회원 안내</p>
        <h1 class="eottae-guide-page__title">쿠폰 사용 안내</h1>
        <p class="eottae-guide-page__lead">세부어때 <strong>쿠폰함</strong>에 모아 둔 할인·무료 쿠폰을 매장에서 보여 주고 혜택을 받는 방법을 안내합니다.</p>
        <a href="<?php echo $coupon_url; ?>" class="eottae-guide-page__cta"><?php echo $is_member ? '내 쿠폰함 보기' : '로그인 후 쿠폰함'; ?></a>
    </header>

    <section class="eottae-guide-section">
        <h2 class="eottae-guide-section__title">쿠폰을 받는 방법</h2>
        <ul class="eottae-guide-cards">
            <li class="eottae-guide-cards__item">
                <strong>회원 가입</strong>
                <p>가입 시 웰컴 쿠폰이 자동으로 쿠폰함에 들어옵니다.</p>
            </li>
            <li class="eottae-guide-cards__item">
                <strong>업체 발행</strong>
                <p>맛집·카페 등 사업자가 회원 아이디로 직접 보내 준 쿠폰입니다. 업체 이름·혜택이 함께 표시됩니다.</p>
            </li>
            <li class="eottae-guide-cards__item">
                <strong>이벤트·리뷰</strong>
                <p>첫 리뷰 작성 등 활동에 따라 감사 쿠폰이 발급될 수 있습니다.</p>
            </li>
        </ul>
    </section>

    <section class="eottae-guide-section">
        <h2 class="eottae-guide-section__title">매장에서 사용하는 방법</h2>

        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">1</span>
            <div class="eottae-guide-step__body">
                <h3>쿠폰함 열기</h3>
                <p>로그인 후 <a href="<?php echo G5_URL; ?>/page/eottae-mypage.php">마이페이지</a> 또는 상단 메뉴에서 <strong>쿠폰함</strong>으로 이동합니다.</p>
            </div>
        </article>

        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">2</span>
            <div class="eottae-guide-step__body">
                <h3>사용할 쿠폰 선택</h3>
                <p><strong>사용 가능</strong> 목록에서 오늘 쓸 쿠폰을 확인합니다. 제목·혜택 내용·쿠폰번호를 꼭 읽어 주세요.</p>
                <p class="eottae-guide-tip">업체 쿠폰은 주황색 <strong>업체 쿠폰</strong> 표시가 붙습니다.</p>
            </div>
        </article>

        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">3</span>
            <div class="eottae-guide-step__body">
                <h3>매장에서 보여주기</h3>
                <p>계산·주문 전에 직원에게 <strong>「매장에서 보여주기」</strong> 버튼을 눌러 전체 화면 쿠폰을 보여 줍니다.</p>
                <ul class="eottae-guide-list">
                    <li>화면에 쿠폰명 · 혜택 · 회원 정보 · <strong>쿠폰번호</strong>가 표시됩니다.</li>
                    <li>혜택 적용 후 화면 아래 <strong>사용 완료</strong>를 누르면 쿠폰이 사용 처리됩니다.</li>
                </ul>
            </div>
        </article>

        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">4</span>
            <div class="eottae-guide-step__body">
                <h3>사용 완료 누르기</h3>
                <p>혜택(할인·무료 제공)을 적용한 뒤, 쿠폰 화면 아래 <strong>사용 완료</strong> 버튼을 눌러 주세요.</p>
                <ul class="eottae-guide-list">
                    <li>회원 본인이 눌러도 되고, 매장 직원이 눌러도 됩니다.</li>
                    <li>처리되면 쿠폰함 <strong>사용 가능</strong> 목록에서 사라지고 <strong>사용 완료</strong>로 이동합니다.</li>
                </ul>
            </div>
        </article>
    </section>

    <section class="eottae-guide-section">
        <h2 class="eottae-guide-section__title">쿠폰 종류별 참고</h2>
        <table class="eottae-guide-table">
            <thead>
                <tr><th>표시 예시</th><th>의미</th></tr>
            </thead>
            <tbody>
                <tr><td>10% 할인 쿠폰</td><td>결제 금액에서 10% 할인 (업체 안내 기준)</td></tr>
                <tr><td>방문시 ○○ 무료</td><td>방문 시 해당 메뉴·상품 무료 제공</td></tr>
                <tr><td>○○ 이상 주문시 …</td><td>조건 금액·메뉴를 충족하면 할인 또는 무료</td></tr>
            </tbody>
        </table>
    </section>

    <section class="eottae-guide-section eottae-guide-section--faq">
        <h2 class="eottae-guide-section__title">자주 묻는 질문</h2>
        <dl class="eottae-guide-faq">
            <dt>쿠폰이 안 보여요.</dt>
            <dd>로그인한 아이디가 발행받은 아이디와 같은지 확인하세요. 업체가 방금 발행했다면 새로고침 후 다시 확인해 보세요.</dd>

            <dt>「사용 완료」 버튼은 어디에 있나요?</dt>
            <dd>업체 쿠폰은 <strong>매장에서 보여주기</strong> 화면 하단에 있습니다. 웰컴·이벤트 쿠폰은 쿠폰함 목록에서 바로 누를 수 있습니다.</dd>

            <dt>쿠폰을 캡처해서 써도 되나요?</dt>
            <dd>쿠폰번호와 회원 정보가 맞아야 하며, 직원 확인 후 1회 사용 처리됩니다. 유효기간·조건은 업체 안내를 따릅니다.</dd>

            <dt>웰컴 쿠폰은 어디서 쓰나요?</dt>
            <dd>제휴·이벤트 안내에 따라 사용처가 정해집니다. 쿠폰 설명란을 확인하거나 고객센터로 문의해 주세요.</dd>
        </dl>
    </section>

    <footer class="eottae-guide-page__footer">
        <a href="<?php echo $coupon_url; ?>" class="eottae-guide-page__cta"><?php echo $is_member ? '내 쿠폰함 열기' : '로그인하고 쿠폰함 가기'; ?></a>
        <p class="eottae-guide-page__footer-link"><a href="<?php echo G5_URL; ?>/page/eottae-business-coupon-guide.php">사업자(업체) 쿠폰 발행 안내 보기</a></p>
    </footer>
</main>

<?php
g5_page_end();
