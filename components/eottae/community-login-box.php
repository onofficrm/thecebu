<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$eottae_auth = function_exists('eottae_auth_context') ? eottae_auth_context() : array('is_member' => false, 'member' => array());
$is_member = !empty($eottae_auth['is_member']);
$member = isset($eottae_auth['member']) ? $eottae_auth['member'] : array();

$eottae_login_return = function_exists('eottae_current_url') ? eottae_current_url() : G5_URL;
$eottae_login_url = function_exists('eottae_login_url') ? eottae_login_url($eottae_login_return) : G5_BBS_URL.'/login.php';
$eottae_register_url = function_exists('eottae_register_url') ? eottae_register_url() : G5_BBS_URL.'/register.php';
$eottae_password_url = function_exists('eottae_password_lost_url') ? eottae_password_lost_url() : G5_BBS_URL.'/password_lost.php';
$eottae_mypage_url = function_exists('eottae_mypage_url') ? eottae_mypage_url() : G5_URL.'/page/eottae-mypage.php';
$eottae_profile_url = G5_BBS_URL.'/member_confirm.php?url='.urlencode(G5_BBS_URL.'/register_form.php');
$eottae_logout_url = G5_BBS_URL.'/logout.php';

if ($is_member && is_array($member)) {
    $eottae_member_nick = isset($member['mb_nick']) ? get_text($member['mb_nick']) : '회원';
    $eottae_member_point = isset($member['mb_point']) ? (int) $member['mb_point'] : 0;
    $eottae_member_is_biz = function_exists('eottae_is_business_member') && eottae_is_business_member($member);
    $eottae_member_type = $eottae_member_is_biz ? '사업자회원' : '일반회원';
    $eottae_coupon_count = 0;
    if (is_file(G5_LIB_PATH.'/eottae-coupon.lib.php')) {
        include_once G5_LIB_PATH.'/eottae-coupon.lib.php';
        if (function_exists('eottae_coupon_count_active') && !empty($member['mb_id'])) {
            $eottae_coupon_count = (int) eottae_coupon_count_active($member['mb_id']);
        }
    }
    $eottae_initial = function_exists('mb_substr') ? mb_substr($eottae_member_nick, 0, 1, 'UTF-8') : substr($eottae_member_nick, 0, 1);
}
?>

<section class="community-sidebar__card community-sidebar__login" aria-label="회원 로그인">
    <?php if ($is_member) { ?>
    <div class="community-login-box community-login-box--member">
        <div class="community-login-box__profile">
            <span class="community-login-box__avatar" aria-hidden="true"><?php echo htmlspecialchars($eottae_initial, ENT_QUOTES, 'UTF-8'); ?></span>
            <div class="community-login-box__profile-body">
                <p class="community-login-box__welcome"><strong><?php echo $eottae_member_nick; ?></strong>님</p>
                <p class="community-login-box__type"><?php echo $eottae_member_type; ?></p>
            </div>
        </div>

        <div class="community-login-box__stats">
            <a href="<?php echo G5_URL; ?>/page/eottae-points.php" class="community-login-box__stat">
                <span class="community-login-box__stat-label">포인트</span>
                <strong class="community-login-box__stat-value"><?php echo number_format($eottae_member_point); ?>P</strong>
            </a>
            <a href="<?php echo G5_URL; ?>/page/eottae-coupons.php" class="community-login-box__stat">
                <span class="community-login-box__stat-label">쿠폰</span>
                <strong class="community-login-box__stat-value"><?php echo number_format($eottae_coupon_count); ?></strong>
            </a>
        </div>

        <a href="<?php echo $eottae_mypage_url; ?>" class="community-login-box__cta">MY 바로가기</a>

        <div class="community-login-box__links">
            <a href="<?php echo $eottae_profile_url; ?>">정보수정</a>
            <span class="community-login-box__divider" aria-hidden="true"></span>
            <a href="<?php echo $eottae_logout_url; ?>">로그아웃</a>
        </div>
    </div>
    <?php } else { ?>
    <div class="community-login-box community-login-box--guest">
        <h2 class="community-login-box__title">세부어때를 더 편리하게</h2>
        <a href="<?php echo $eottae_login_url; ?>" class="community-login-box__cta">세부어때 로그인</a>
        <div class="community-login-box__links">
            <a href="<?php echo $eottae_register_url; ?>">회원가입</a>
            <span class="community-login-box__divider" aria-hidden="true"></span>
            <a href="<?php echo $eottae_password_url; ?>">아이디/비밀번호 찾기</a>
        </div>
    </div>
    <?php } ?>
</section>
