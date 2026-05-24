<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-mypage.php'));
}

$is_biz = eottae_is_business_member($member);
$point = isset($member['mb_point']) ? (int) $member['mb_point'] : 0;

g5_page_start('마이페이지');
?>

<main class="mypage-dashboard">
    <section class="mypage-profile-card">
        <p class="mypage-profile-card__type"><?php echo $is_biz ? '사업자회원' : '일반회원'; ?></p>
        <h1 class="mypage-profile-card__name"><?php echo get_text($member['mb_nick']); ?>님</h1>
        <p><?php echo get_text($member['mb_email']); ?></p>
    </section>

    <section class="mypage-point-summary">
        <div class="mypage-point-summary__box">
            <p class="mypage-point-summary__label">포인트</p>
            <p class="mypage-point-summary__value"><?php echo number_format($point); ?>P</p>
        </div>
        <div class="mypage-coupon-summary__box">
            <p class="mypage-coupon-summary__label">쿠폰</p>
            <p class="mypage-coupon-summary__value">0</p>
        </div>
    </section>

    <nav class="mypage-quick-menu" aria-label="마이페이지 메뉴">
        <a href="<?php echo G5_BBS_URL; ?>/point.php" class="mypage-quick-menu__item">포인트</a>
        <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo EOTTae_COMMUNITY_TABLE; ?>" class="mypage-quick-menu__item">내 활동</a>
        <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo EOTTae_SHOP_TABLE; ?>" class="mypage-quick-menu__item">저장 업체</a>
        <a href="<?php echo G5_BBS_URL; ?>/memo.php" class="mypage-quick-menu__item">쪽지</a>
        <a href="<?php echo G5_BBS_URL; ?>/member_confirm.php?url=<?php echo urlencode(G5_BBS_URL.'/register_form.php'); ?>" class="mypage-quick-menu__item">정보수정</a>
        <a href="<?php echo G5_BBS_URL; ?>/logout.php" class="mypage-quick-menu__item">로그아웃</a>
    </nav>

    <?php if ($is_biz) { ?>
    <section class="business-dashboard">
        <h2 class="business-dashboard__title">사업자 대시보드</h2>
        <p>내 업체 통계·리뷰 답변 기능은 2차 개발에서 제공됩니다.</p>
        <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo EOTTae_SHOP_TABLE; ?>&amp;mode=write" class="eottae-btn-write" style="margin-top:12px;display:inline-flex">업체 등록</a>
        <?php eottae_render_inquiry_buttons('business', array()); ?>
    </section>
    <?php } ?>
</main>

<?php
g5_page_end();
