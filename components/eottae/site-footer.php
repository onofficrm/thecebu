<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$eottae_footer_title = isset($g5_site_title) ? $g5_site_title : (function_exists('g5site_cfg') ? g5site_cfg('site_name', '세부어때') : '세부어때');
$eottae_footer_logo_url = function_exists('eottae_site_logo_url')
    ? eottae_site_logo_url('footer_logo_path')
    : (function_exists('g5site_cfg_url') ? g5site_cfg_url('footer_logo_path', '') : '');
$eottae_footer_shop_url = function_exists('eottae_shop_list_url') ? eottae_shop_list_url() : G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE;
$eottae_footer_shop_write = G5_BBS_URL.'/write.php?bo_table='.EOTTae_SHOP_TABLE;
$eottae_footer_community = function_exists('eottae_community_list_url') ? eottae_community_list_url() : G5_BBS_URL.'/board.php?bo_table='.EOTTae_COMMUNITY_TABLE;
$eottae_footer_admin_email = 'jong8040@gmail.com';
$eottae_footer_talk_landing = G5_URL.'/talk/ai.php';
$eottae_footer_coupon_guide = G5_URL.'/page/eottae-coupon-guide.php';
$eottae_footer_business_coupon_guide = G5_URL.'/page/eottae-business-coupon-guide.php';
$eottae_footer_year = date('Y');
?>

<footer id="siteFooter" class="site-footer eottae-footer eottae-gnb-footer">
    <div class="eottae-gnb-footer__outer">
        <div class="eottae-gnb-footer__panel">
            <div class="eottae-gnb-footer__grid">
                <div class="eottae-gnb-footer__brand">
                    <p class="eottae-gnb-footer__logo">
                        <?php if ($eottae_footer_logo_url !== '') { ?>
                        <img src="<?php echo $eottae_footer_logo_url; ?>" alt="<?php echo get_text($eottae_footer_title); ?>" class="eottae-gnb-footer__logo-img">
                        <?php } else { ?>
                        <?php echo get_text($eottae_footer_title); ?>
                        <?php } ?>
                    </p>
                    <p class="eottae-gnb-footer__desc">필리핀 세부 교민, 사업자, 관광객을 위한 최고의 위치기반 생활정보 커뮤니티 플랫폼.</p>
                </div>

                <nav class="eottae-gnb-footer__col" aria-label="바로가기">
                    <h3 class="eottae-gnb-footer__heading">바로가기</h3>
                    <ul class="eottae-gnb-footer__links">
                        <li><a href="<?php echo $eottae_footer_shop_url; ?>">내주변 업소</a></li>
                        <li><a href="<?php echo $eottae_footer_shop_write; ?>">업소등록 안내</a></li>
                        <li><a href="<?php echo $eottae_footer_community; ?>">커뮤니티</a></li>
                    </ul>
                </nav>

                <nav class="eottae-gnb-footer__col" aria-label="서비스 안내">
                    <h3 class="eottae-gnb-footer__heading">서비스 안내</h3>
                    <ul class="eottae-gnb-footer__links">
                        <li><a href="<?php echo $eottae_footer_talk_landing; ?>">세부톡 AI 도우미</a></li>
                        <li><a href="<?php echo $eottae_footer_coupon_guide; ?>">쿠폰 사용 방법</a></li>
                        <li><a href="<?php echo $eottae_footer_business_coupon_guide; ?>">쿠폰 발행 방법 (사업자)</a></li>
                    </ul>
                </nav>

                <div class="eottae-gnb-footer__col">
                    <h3 class="eottae-gnb-footer__heading">고객지원</h3>
                    <ul class="eottae-gnb-footer__links">
                        <li><span>사이트관리자</span></li>
                        <li><a href="mailto:<?php echo get_text($eottae_footer_admin_email); ?>"><?php echo get_text($eottae_footer_admin_email); ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="eottae-gnb-footer__bottom">
                <div class="eottae-gnb-footer__legal">
                    <a href="<?php echo get_pretty_url('content', 'provision'); ?>">이용약관</a>
                    <a href="<?php echo G5_URL; ?>/page/privacy.php" class="is-emphasis">개인정보처리방침</a>
                </div>
                <p class="eottae-gnb-footer__copy">&copy; <?php echo $eottae_footer_year; ?> <?php echo get_text($eottae_footer_title); ?>. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
