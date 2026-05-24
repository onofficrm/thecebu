<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-coupons.php'));
}

include_once G5_LIB_PATH.'/eottae-coupon.lib.php';
eottae_coupon_ensure_ready();

$welcome = eottae_coupon_ensure_welcome($member['mb_id']);

$active_coupons = eottae_coupon_get_member_list($member['mb_id'], 'active');
$used_coupons = eottae_coupon_get_member_list($member['mb_id'], 'used');
$active_count = count($active_coupons);

g5_page_start('쿠폰함');
?>

<main class="mypage-subpage">
    <?php eottae_render_mypage_back(); ?>
    <h1 class="mypage-subpage__title">쿠폰함</h1>

    <?php if (!empty($welcome['ok']) && empty($welcome['duplicate'])) { ?>
    <p class="mypage-subpage__notice" role="status">신규 가입 웰컴 쿠폰이 발급되었습니다.</p>
    <?php } ?>

    <section class="mypage-coupon-summary" style="margin-bottom:20px">
        <div class="mypage-coupon-summary__box" style="grid-column:1/-1">
            <p class="mypage-coupon-summary__label">사용 가능 쿠폰</p>
            <p class="mypage-coupon-summary__value"><?php echo number_format($active_count); ?></p>
        </div>
    </section>

    <?php if (empty($active_coupons) && empty($used_coupons)) { ?>
    <div class="empty-state">
        <p class="empty-state__title">보유 쿠폰이 없습니다</p>
        <p>회원 가입 시 웰컴 쿠폰이 자동 발급됩니다. 첫 리뷰 작성 시 감사 쿠폰도 받을 수 있습니다.</p>
    </div>
    <?php } else { ?>

    <?php if (!empty($active_coupons)) { ?>
    <h2 class="mypage-subpage__subtitle" style="font-size:1rem;margin:0 0 12px">사용 가능</h2>
    <div class="mypage-coupon-list" style="display:grid;gap:12px;margin-bottom:24px">
        <?php foreach ($active_coupons as $coupon) { ?>
        <article class="coupon-card" data-coupon-id="<?php echo (int) $coupon['ci_id']; ?>">
            <h3 class="coupon-card__title"><?php echo get_text($coupon['cp_title']); ?></h3>
            <p class="coupon-card__desc"><?php echo get_text($coupon['cp_desc']); ?></p>
            <p style="font-size:12px;color:var(--eottae-muted);margin:8px 0 0">발급일 <?php echo substr($coupon['ci_datetime'], 0, 10); ?></p>
            <button type="button" class="eottae-btn-write coupon-card__use-btn" style="margin-top:12px" data-coupon-use="<?php echo (int) $coupon['ci_id']; ?>">사용 완료 처리</button>
        </article>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if (!empty($used_coupons)) { ?>
    <h2 class="mypage-subpage__subtitle" style="font-size:1rem;margin:0 0 12px">사용 완료</h2>
    <div class="mypage-coupon-list" style="display:grid;gap:12px">
        <?php foreach ($used_coupons as $coupon) { ?>
        <article class="coupon-card" style="opacity:0.65">
            <h3 class="coupon-card__title"><?php echo get_text($coupon['cp_title']); ?></h3>
            <p class="coupon-card__desc"><?php echo get_text($coupon['cp_desc']); ?></p>
            <p style="font-size:12px;color:var(--eottae-muted);margin:8px 0 0">사용일 <?php echo substr($coupon['ci_used_datetime'], 0, 10); ?></p>
        </article>
        <?php } ?>
    </div>
    <?php } ?>

    <?php } ?>
</main>

<script>
(function () {
    document.querySelectorAll('[data-coupon-use]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('이 쿠폰을 사용 완료 처리할까요?')) {
                return;
            }
            var ciId = btn.getAttribute('data-coupon-use');
            var body = new FormData();
            body.append('ci_id', ciId);
            fetch('/proc/eottae-coupon-use.php', { method: 'POST', body: body, credentials: 'same-origin' })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    alert(data.message || (data.success ? '처리되었습니다.' : '오류가 발생했습니다.'));
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(function () {
                    alert('요청 중 오류가 발생했습니다.');
                });
        });
    });
})();
</script>

<?php
g5_page_end();
