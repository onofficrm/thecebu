<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$eottae_gnb_links = eottae_gnb_nav_links();
$eottae_site_title = isset($g5_site_title) ? $g5_site_title : '세부어때';
$eottae_shop_write_url = function_exists('eottae_shop_write_url') ? eottae_shop_write_url() : G5_BBS_URL.'/write.php?bo_table='.EOTTae_SHOP_TABLE;
?>

<header id="siteHeader" class="site-header mobile-header eottae-header eottae-gnb-header">
    <h1 id="hd_h1" class="sound_only"><?php echo $g5['title']; ?></h1>
    <div class="site-header__skip">
        <a href="#container">본문 바로가기</a>
    </div>

    <?php if (defined('_INDEX_')) {
        include G5_BBS_PATH.'/newwin.inc.php';
    } ?>

    <div class="eottae-gnb-header__wrap">
        <div class="eottae-gnb-header__shell">
            <div class="eottae-gnb-header__inner">
                <div class="eottae-gnb-header__left">
                    <a href="<?php echo G5_URL; ?>/" class="eottae-gnb-header__logo">
                        <span class="eottae-gnb-header__logo-mark" aria-hidden="true">🌴</span>
                        <span class="eottae-gnb-header__logo-text"><?php echo get_text($eottae_site_title); ?></span>
                    </a>

                    <nav class="eottae-gnb-header__nav" aria-label="메인메뉴">
                        <?php foreach ($eottae_gnb_links as $link) {
                            $active = eottae_gnb_link_is_active($link['key']);
                            ?>
                        <a href="<?php echo $link['href']; ?>" class="eottae-gnb-header__nav-link<?php echo $active ? ' is-active' : ''; ?>">
                            <?php echo get_text($link['label']); ?>
                        </a>
                        <?php } ?>
                    </nav>
                </div>

                <div class="eottae-gnb-header__actions">
                    <?php if ($is_member) { ?>
                    <a href="<?php echo G5_URL; ?>/page/eottae-mypage.php" class="eottae-gnb-header__btn eottae-gnb-header__btn--text eottae-gnb-header__btn--desktop">MY</a>
                    <?php if ($is_admin) { ?>
                    <a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>" class="eottae-gnb-header__btn eottae-gnb-header__btn--text eottae-gnb-header__btn--desktop">관리자</a>
                    <?php } ?>
                    <?php } else { ?>
                    <a href="<?php echo G5_BBS_URL; ?>/login.php" class="eottae-gnb-header__btn eottae-gnb-header__btn--text eottae-gnb-header__btn--desktop">로그인</a>
                    <a href="<?php echo G5_BBS_URL; ?>/register.php" class="eottae-gnb-header__btn eottae-gnb-header__btn--text eottae-gnb-header__btn--desktop">회원가입</a>
                    <?php } ?>
                    <a href="<?php echo $eottae_shop_write_url; ?>" class="eottae-gnb-header__btn eottae-gnb-header__btn--register eottae-gnb-header__btn--desktop">업소등록</a>

                    <button type="button" class="eottae-gnb-header__icon-btn eottae-gnb-header__menu-btn site-header__menu-btn" aria-controls="siteMobileNav" aria-expanded="false" aria-label="메뉴 열기">
                        <svg class="eottae-gnb-header__icon eottae-gnb-header__icon--menu" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            <div id="siteMobileNav" class="eottae-gnb-header__mobile site-header__mobile-nav" aria-hidden="true">
                <nav class="eottae-gnb-header__mobile-nav">
                    <?php foreach ($eottae_gnb_links as $link) {
                        $active = eottae_gnb_link_is_active($link['key']);
                        ?>
                    <a href="<?php echo $link['href']; ?>" class="eottae-gnb-header__mobile-link<?php echo $active ? ' is-active' : ''; ?>">
                        <?php echo get_text($link['label']); ?>
                    </a>
                    <?php } ?>
                </nav>
                <div class="eottae-gnb-header__mobile-auth">
                    <?php if ($is_member) { ?>
                    <a href="<?php echo G5_URL; ?>/page/eottae-mypage.php" class="eottae-gnb-header__btn eottae-gnb-header__btn--ghost">MY</a>
                    <a href="<?php echo G5_BBS_URL; ?>/logout.php" class="eottae-gnb-header__btn eottae-gnb-header__btn--ghost">로그아웃</a>
                    <?php } else { ?>
                    <a href="<?php echo G5_BBS_URL; ?>/login.php" class="eottae-gnb-header__btn eottae-gnb-header__btn--ghost">로그인</a>
                    <a href="<?php echo G5_BBS_URL; ?>/register.php" class="eottae-gnb-header__btn eottae-gnb-header__btn--ghost">회원가입</a>
                    <?php } ?>
                    <a href="<?php echo $eottae_shop_write_url; ?>" class="eottae-gnb-header__btn eottae-gnb-header__btn--register">업소등록</a>
                </div>
            </div>
        </div>
    </div>
    <div class="site-header__overlay eottae-gnb-header__overlay" aria-hidden="true"></div>
</header>
