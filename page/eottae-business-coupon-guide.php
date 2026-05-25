<?php
include_once(dirname(__FILE__).'/_init.php');

$manage_url = G5_URL.'/page/eottae-business-coupons.php';
$is_biz = $is_member && function_exists('eottae_is_business_member') && eottae_is_business_member($member);

if (!$is_biz) {
    $manage_url = $is_member
        ? G5_URL.'/page/eottae-mypage.php'
        : (function_exists('eottae_login_url') ? eottae_login_url(G5_URL.'/page/eottae-business-coupon-guide.php') : G5_BBS_URL.'/login.php');
}

g5_page_start('사업자 쿠폰 안내');
?>

<main class="eottae-guide-page eottae-guide-page--business">
    <header class="eottae-guide-page__header">
        <a href="<?php echo $is_biz ? $manage_url : G5_URL.'/page/eottae-mypage.php'; ?>" class="eottae-guide-page__back">← <?php echo $is_biz ? '쿠폰 발행 관리' : '마이페이지'; ?></a>
        <p class="eottae-guide-page__badge">사업자 안내</p>
        <h1 class="eottae-guide-page__title">쿠폰 발행·사용 안내</h1>
        <p class="eottae-guide-page__lead">세부어때에서 할인·무료 혜택 쿠폰을 만들어 회원에게 보내고, 매장에서 사용할 때 <strong>사용 완료</strong>만 누르면 누가·언제 썼는지 한눈에 확인할 수 있습니다.</p>
        <?php if ($is_biz) { ?>
        <a href="<?php echo G5_URL; ?>/page/eottae-business-coupons.php" class="eottae-guide-page__cta">쿠폰 발행 관리 바로가기</a>
        <?php } ?>
    </header>

    <section class="eottae-guide-section">
        <h2 class="eottae-guide-section__title">이 기능으로 할 수 있는 일</h2>
        <ul class="eottae-guide-cards">
            <li class="eottae-guide-cards__item">
                <strong>쿠폰 만들기</strong>
                <p>10% 할인, 방문시 음료 무료, ○○원 이상 주문 시 할인 등 매장에 맞는 혜택을 직접 설계합니다.</p>
            </li>
            <li class="eottae-guide-cards__item">
                <strong>회원에게 발행</strong>
                <p>세부어때 회원 아이디(mb_id)로 쿠폰을 지정 인원에게 보냅니다. 발행 장수도 설정할 수 있습니다.</p>
            </li>
            <li class="eottae-guide-cards__item">
                <strong>매장 사용 처리</strong>
                <p>손님이 쿠폰을 보여주면 직원이 <em>사용 완료</em>를 눌러 실제 사용 여부를 기록합니다.</p>
            </li>
        </ul>
    </section>

    <section class="eottae-guide-section">
        <h2 class="eottae-guide-section__title">시작 전 확인</h2>
        <ol class="eottae-guide-checklist">
            <li><strong>사업자 회원</strong>으로 로그인되어 있어야 합니다.</li>
            <li>업체 정보가 등록되어 있으면 고객이 업체를 찾기 쉽습니다. (쿠폰 발행 자체는 업체 등록 없이도 가능)</li>
            <li>쿠폰을 받을 <strong>회원 아이디(mb_id)</strong>를 미리 확인해 두세요.</li>
        </ol>
    </section>

    <section class="eottae-guide-section">
        <h2 class="eottae-guide-section__title">단계별 사용법</h2>

        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">1</span>
            <div class="eottae-guide-step__body">
                <h3>쿠폰 만들기</h3>
                <p><a href="<?php echo G5_URL; ?>/page/eottae-business-coupons.php">쿠폰 발행 관리</a> → <strong>1. 쿠폰 만들기</strong>에서 유형을 고릅니다.</p>
                <table class="eottae-guide-table">
                    <thead>
                        <tr><th>유형</th><th>입력 예시</th><th>고객에게 보이는 혜택</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>할인율</td>
                            <td>할인율 10%</td>
                            <td>10% 할인 쿠폰</td>
                        </tr>
                        <tr>
                            <td>방문 혜택</td>
                            <td>무료 항목: 망고쉐이크 1잔</td>
                            <td>방문시 망고쉐이크 1잔 무료</td>
                        </tr>
                        <tr>
                            <td>주문 조건</td>
                            <td>500페소 이상 · 삼겹살 세트 주문 · 10% 할인</td>
                            <td>500페소 이상 삼겹살 세트 주문시 10% 할인</td>
                        </tr>
                    </tbody>
                </table>
                <p class="eottae-guide-tip">발행 가능 수량(예: 100장)과 만료일을 함께 설정할 수 있습니다.</p>
            </div>
        </article>

        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">2</span>
            <div class="eottae-guide-step__body">
                <h3>회원에게 발행</h3>
                <p><strong>2. 회원에게 발행</strong>에서 만든 쿠폰을 선택하고, 받을 분의 <strong>회원 아이디</strong>를 입력합니다.</p>
                <ul class="eottae-guide-list">
                    <li>한 번에 여러 장 발행하려면 <strong>발행 장수</strong>를 2 이상으로 입력합니다.</li>
                    <li>발행이 완료되면 해당 회원의 <strong>쿠폰함</strong>에 바로 표시됩니다.</li>
                    <li>같은 쿠폰을 이미 보유 중인 회원에게는 중복 발행되지 않을 수 있습니다.</li>
                </ul>
            </div>
        </article>

        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">3</span>
            <div class="eottae-guide-step__body">
                <h3>매장에서 사용 처리 (중요)</h3>
                <p>손님이 결제 전·후에 휴대폰 <strong>쿠폰함 → 매장에서 보여주기</strong>로 쿠폰 화면을 보여줍니다. 화면에 <strong>쿠폰번호(8자리)</strong>와 회원 정보, <strong>사용 완료</strong> 버튼이 나옵니다.</p>
                <p>혜택 적용 후 아래 중 편한 방법으로 <strong>사용 완료</strong>를 처리합니다.</p>
                <ul class="eottae-guide-list">
                    <li>손님 휴대폰 쿠폰 화면에서 <strong>사용 완료</strong> (회원·직원 누구든 가능)</li>
                    <li><strong>사용 대기</strong> 목록에서 해당 손님 옆 <strong>사용 완료</strong> 버튼</li>
                    <li><strong>3. 매장 사용 처리</strong>에 쿠폰번호 또는 회원 아이디 입력 후 처리</li>
                </ul>
                <p class="eottae-guide-tip eottae-guide-tip--warn">실제 할인·무료 제공을 적용한 뒤에 사용 완료를 눌러 주세요. 한 번 처리하면 다시 사용할 수 없습니다.</p>
            </div>
        </article>

        <article class="eottae-guide-step">
            <span class="eottae-guide-step__num">4</span>
            <div class="eottae-guide-step__body">
                <h3>발행·사용 내역 확인</h3>
                <p>같은 페이지 하단 <strong>사용 대기</strong> / <strong>사용 완료 내역</strong>에서 다음을 확인합니다.</p>
                <ul class="eottae-guide-list">
                    <li>어떤 회원에게 발행했는지 (닉네임 · 아이디)</li>
                    <li>쿠폰번호 · 발행 일시</li>
                    <li>사용 완료 처리한 일시</li>
                </ul>
            </div>
        </article>
    </section>

    <section class="eottae-guide-section eottae-guide-section--faq">
        <h2 class="eottae-guide-section__title">자주 묻는 질문</h2>
        <dl class="eottae-guide-faq">
            <dt>회원 아이디를 모르겠어요.</dt>
            <dd>손님에게 세부어때 로그인 아이디(mb_id)를 확인해 달라고 요청하거나, 커뮤니티 닉네임으로 문의 후 관리자·본인 확인 절차를 안내해 주세요.</dd>

            <dt>손님이 직접 사용 완료를 눌러도 되나요?</dt>
            <dd>네. 쿠폰 화면의 <strong>사용 완료</strong>는 회원·직원 누구든 눌러도 됩니다. 혜택 적용 후 한 번만 눌러 주세요.</dd>

            <dt>쿠폰을 잘못 발행했어요.</dt>
            <dd>아직 사용 전이라면 사용 대기 목록에서 확인 후, 필요 시 별도 안내 쿠폰을 재발행하세요. 이미 사용 완료된 쿠폰은 되돌릴 수 없습니다.</dd>

            <dt>발행 수량을 늘리고 싶어요.</dt>
            <dd>쿠폰을 새로 만들거나, 기존 쿠폰의 발행 한도 내에서 추가 발행할 수 있습니다. 한도가 찼다면 새 쿠폰 캠페인을 만드는 것을 권장합니다.</dd>
        </dl>
    </section>

    <footer class="eottae-guide-page__footer">
        <?php if ($is_biz) { ?>
        <a href="<?php echo G5_URL; ?>/page/eottae-business-coupons.php" class="eottae-guide-page__cta">쿠폰 발행 관리 시작하기</a>
        <?php } else { ?>
        <p>사업자 회원으로 로그인 후 이용할 수 있습니다.</p>
        <a href="<?php echo function_exists('eottae_login_url') ? eottae_login_url(G5_URL.'/page/eottae-business-coupons.php') : G5_BBS_URL.'/login.php'; ?>" class="eottae-guide-page__cta">로그인하기</a>
        <?php } ?>
        <p class="eottae-guide-page__footer-link"><a href="<?php echo G5_URL; ?>/page/eottae-coupon-guide.php">고객(회원) 쿠폰 사용 안내 보기</a></p>
    </footer>
</main>

<?php
g5_page_end();
