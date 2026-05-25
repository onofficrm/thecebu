<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-mypage.php'));
}

include_once G5_LIB_PATH.'/eottae-coupon.lib.php';
include_once G5_LIB_PATH.'/eottae-shop-owner.lib.php';

$is_biz = eottae_is_business_member($member);
$point = isset($member['mb_point']) ? (int) $member['mb_point'] : 0;
$coupon_count = eottae_coupon_count_active($member['mb_id']);
$pending_replies = $is_biz ? eottae_business_pending_replies_count($member['mb_id']) : 0;
$my_review_count = count(eottae_get_member_reviews($member['mb_id'], 100));
$saved_count = count(eottae_get_saved_shop_ids($member['mb_id'], 100));
$inquiry_count = count(eottae_get_member_inquiries($member['mb_id'], 100));
$my_shop_posts = $is_biz ? eottae_business_shop_posts($member['mb_id'], 20) : array();

g5_page_start('마이페이지');
?>

<main class="mypage-dashboard">
    <section class="mypage-profile-card">
        <p class="mypage-profile-card__type"><?php echo $is_biz ? '사업자회원' : '일반회원'; ?></p>
        <h1 class="mypage-profile-card__name"><?php echo get_text($member['mb_nick']); ?>님</h1>
        <p><?php echo get_text($member['mb_email']); ?></p>
    </section>

    <section class="mypage-point-summary">
        <a href="<?php echo G5_URL; ?>/page/eottae-points.php" class="mypage-point-summary__box" style="text-decoration:none;color:inherit">
            <p class="mypage-point-summary__label">포인트</p>
            <p class="mypage-point-summary__value"><?php echo number_format($point); ?>P</p>
        </a>
        <a href="<?php echo G5_URL; ?>/page/eottae-coupons.php" class="mypage-coupon-summary__box" style="text-decoration:none;color:inherit">
            <p class="mypage-point-summary__label">쿠폰</p>
            <p class="mypage-point-summary__value"><?php echo number_format($coupon_count); ?></p>
        </a>
    </section>

    <nav class="mypage-quick-menu" aria-label="마이페이지 메뉴">
        <a href="<?php echo G5_URL; ?>/page/eottae-points.php" class="mypage-quick-menu__item">포인트</a>
        <a href="<?php echo G5_URL; ?>/page/eottae-coupons.php" class="mypage-quick-menu__item">쿠폰함</a>
        <a href="<?php echo G5_URL; ?>/page/eottae-my-reviews.php" class="mypage-quick-menu__item">내 리뷰<?php if (!$is_biz && $my_review_count > 0) { ?> (<?php echo $my_review_count; ?>)<?php } ?></a>
        <a href="<?php echo G5_URL; ?>/page/eottae-saved-shops.php" class="mypage-quick-menu__item">찜·최근<?php if ($saved_count > 0) { ?> (<?php echo $saved_count; ?>)<?php } ?></a>
        <a href="<?php echo G5_URL; ?>/page/eottae-inquiries.php" class="mypage-quick-menu__item">문의<?php if ($inquiry_count > 0) { ?> (<?php echo $inquiry_count; ?>)<?php } ?></a>
        <a href="<?php echo G5_URL; ?>/page/eottae-events.php" class="mypage-quick-menu__item">이벤트</a>
        <a href="<?php echo G5_URL; ?>/page/eottae-business-snippets.php" class="mypage-quick-menu__item">홍보 문구</a>
        <?php if ($is_biz) { ?><a href="<?php echo G5_URL; ?>/page/eottae-business-coupons.php" class="mypage-quick-menu__item">쿠폰 발행</a><?php } ?>
        <a href="<?php echo G5_URL; ?>/page/eottae-coupon-guide.php" class="mypage-quick-menu__item">쿠폰 안내</a>
        <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo EOTTae_COMMUNITY_TABLE; ?>" class="mypage-quick-menu__item">내 활동</a>
        <a href="<?php echo G5_BBS_URL; ?>/member_confirm.php?url=<?php echo urlencode(G5_BBS_URL.'/register_form.php'); ?>" class="mypage-quick-menu__item">정보수정</a>
        <a href="<?php echo G5_BBS_URL; ?>/logout.php" class="mypage-quick-menu__item">로그아웃</a>
    </nav>

    <?php if ($is_biz) { ?>
    <section class="business-dashboard">
        <h2 class="business-dashboard__title">사업자 대시보드</h2>
        <?php if ($pending_replies > 0) { ?>
        <p><strong><?php echo number_format($pending_replies); ?>건</strong>의 리뷰에 답변이 필요합니다.</p>
        <?php } else { ?>
        <p>새로운 리뷰 답변 요청이 없습니다.</p>
        <?php } ?>
        <a href="<?php echo G5_BBS_URL; ?>/write.php?bo_table=<?php echo EOTTae_SHOP_TABLE; ?>" class="eottae-btn-write" style="margin-top:12px;display:inline-flex">업체 등록</a>
        <a href="<?php echo G5_URL; ?>/page/eottae-business-snippets.php" class="eottae-btn-write" style="margin-top:8px;display:inline-flex">홍보 문구 관리</a>
        <a href="<?php echo G5_URL; ?>/page/eottae-business-coupons.php" class="eottae-btn-write" style="margin-top:8px;display:inline-flex">쿠폰 발행 관리</a>
        <a href="<?php echo G5_URL; ?>/page/eottae-business-coupon-guide.php" class="eottae-btn-write" style="margin-top:8px;display:inline-flex">쿠폰 발행 안내</a>
        <a href="<?php echo G5_BBS_URL; ?>/write.php?bo_table=<?php echo EOTTae_COMMUNITY_TABLE; ?>" class="eottae-btn-write" style="margin-top:8px;display:inline-flex">커뮤니티 글쓰기</a>
        <?php eottae_render_inquiry_buttons('business', array()); ?>

        <?php if (!empty($my_shop_posts)) { ?>
        <div class="business-dashboard__shops">
            <h3 class="business-dashboard__shops-title">내 업체</h3>
            <ul class="business-dashboard__shop-list">
                <?php foreach ($my_shop_posts as $shop_row) { ?>
                <li class="business-dashboard__shop-item">
                    <a href="<?php echo $shop_row['view_url']; ?>" class="business-dashboard__shop-name"><?php echo $shop_row['subject']; ?></a>
                    <a href="<?php echo $shop_row['update_url']; ?>" class="business-dashboard__shop-edit">수정</a>
                    <?php if (!empty($shop_row['delete_url'])) { ?>
                    <a href="<?php echo $shop_row['delete_url']; ?>" class="business-dashboard__shop-delete" onclick="del(this.href); return false;">삭제</a>
                    <?php } ?>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
    </section>
    <?php } ?>
</main>

<?php
g5_page_end();
