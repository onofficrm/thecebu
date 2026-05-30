<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

global $g5;

$eottae_auth = function_exists('eottae_auth_context') ? eottae_auth_context() : array('is_member' => false, 'is_admin' => false, 'member' => array('mb_id' => '', 'mb_level' => 1, 'mb_point' => 0));
$is_member = !empty($eottae_auth['is_member']);
$is_admin = !empty($eottae_auth['is_admin']) ? $eottae_auth['is_admin'] : '';
$member = isset($eottae_auth['member']) ? $eottae_auth['member'] : array('mb_id' => '', 'mb_level' => 1, 'mb_point' => 0);

include_once G5_PATH.'/components/eottae/gnb-nav-items.php';
$eottae_gnb_links = eottae_gnb_nav_links();
$eottae_gnb_menu_items = function_exists('eottae_gnb_nav_menu') ? eottae_gnb_nav_menu() : array();
$eottae_site_title = isset($g5_site_title) ? $g5_site_title : '세부어때';
$eottae_header_logo_url = function_exists('eottae_site_logo_url')
    ? eottae_site_logo_url('logo_path')
    : (function_exists('g5site_cfg_url') ? g5site_cfg_url('logo_path', '') : '');
$eottae_shop_write_url = function_exists('eottae_shop_write_url') ? eottae_shop_write_url() : G5_BBS_URL.'/write.php?bo_table='.EOTTae_SHOP_TABLE;
$eottae_login_return = function_exists('eottae_current_url') ? eottae_current_url() : G5_URL;
$eottae_login_href = function_exists('eottae_login_url') ? eottae_login_url($eottae_login_return) : G5_BBS_URL.'/login.php';
$eottae_register_href = function_exists('eottae_register_url') ? eottae_register_url() : G5_BBS_URL.'/register.php';
$eottae_logout_href = G5_BBS_URL.'/logout.php';
$eottae_mypage_href = function_exists('eottae_mypage_url') ? eottae_mypage_url() : G5_URL.'/page/eottae-mypage.php';
$eottae_talk_action_links = array();
foreach ($eottae_gnb_links as $link) {
    if (!empty($link['desktop_action'])) {
        $eottae_talk_action_links[] = $link;
    }
}
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
        <div class="eottae-gnb-header__shell" data-eottae-gnb-shell>
            <div class="eottae-gnb-header__desktop-head" data-eottae-gnb-desktop-head>
                <div class="eottae-gnb-header__inner">
                    <div class="eottae-gnb-header__left">
                        <a href="<?php echo G5_URL; ?>/" class="eottae-gnb-header__logo">
                            <?php if ($eottae_header_logo_url !== '') { ?>
                            <img src="<?php echo $eottae_header_logo_url; ?>" alt="<?php echo get_text($eottae_site_title); ?>" class="eottae-gnb-header__logo-img">
                            <?php } else { ?>
                            <span class="eottae-gnb-header__logo-text"><?php echo get_text($eottae_site_title); ?></span>
                            <?php } ?>
                        </a>

                        <nav class="eottae-gnb-header__nav" aria-label="메인메뉴" data-eottae-gnb-nav>
                            <?php eottae_gnb_render_nav_items($eottae_gnb_menu_items, 'desktop'); ?>
                        </nav>
                    </div>

                    <div class="eottae-gnb-header__actions">
                        <?php if ($is_member) { ?>
                        <a href="<?php echo $eottae_logout_href; ?>" class="eottae-gnb-header__btn eottae-gnb-header__btn--text eottae-gnb-header__btn--desktop" data-i18n="button.logout">로그아웃</a>
                        <?php if ($is_admin) { ?>
                        <a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>" class="eottae-gnb-header__btn eottae-gnb-header__btn--text eottae-gnb-header__btn--desktop" data-i18n="common.admin">관리자</a>
                        <?php } ?>
                        <?php } else { ?>
                        <a href="<?php echo $eottae_login_href; ?>" class="eottae-gnb-header__btn eottae-gnb-header__btn--text eottae-gnb-header__btn--desktop" data-i18n="button.login">로그인</a>
                        <a href="<?php echo $eottae_register_href; ?>" class="eottae-gnb-header__btn eottae-gnb-header__btn--text eottae-gnb-header__btn--desktop" data-i18n="button.register">회원가입</a>
                        <?php } ?>
                        <?php foreach ($eottae_talk_action_links as $link) {
                            $active = eottae_gnb_link_is_active($link['key']);
                            ?>
                        <a href="<?php echo $link['href']; ?>" class="eottae-gnb-header__btn eottae-gnb-header__btn--talk eottae-gnb-header__btn--desktop<?php echo $active ? ' is-active' : ''; ?>">
                            <?php echo get_text($link['label']); ?>
                        </a>
                        <?php } ?>
                        <a href="<?php echo $eottae_shop_write_url; ?>" class="eottae-gnb-header__btn eottae-gnb-header__btn--register eottae-gnb-header__btn--desktop" data-i18n="button.shop_register">업소등록</a>
                        <?php echo function_exists('eottae_i18n_language_select_html') ? eottae_i18n_language_select_html('eottae-language--desktop') : ''; ?>

                        <button type="button" class="eottae-gnb-header__icon-btn eottae-gnb-header__menu-btn site-header__menu-btn" aria-controls="siteMobileNav" aria-expanded="false" aria-label="메뉴 열기" data-i18n-aria-label="common.open_menu">
                            <svg class="eottae-gnb-header__icon eottae-gnb-header__icon--menu" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                    </div>
                </div>

                <?php eottae_gnb_render_desktop_mega_panel($eottae_gnb_menu_items); ?>
            </div>

            <div id="siteMobileNav" class="eottae-gnb-header__mobile site-header__mobile-nav" aria-hidden="true">
                <?php echo function_exists('eottae_i18n_language_select_html') ? eottae_i18n_language_select_html('eottae-language--mobile') : ''; ?>
                <nav class="eottae-gnb-header__mobile-nav">
                    <?php eottae_gnb_render_nav_items($eottae_gnb_menu_items, 'mobile'); ?>
                </nav>
                <div class="eottae-gnb-header__mobile-auth">
                    <?php if ($is_member) { ?>
                    <a href="<?php echo $eottae_logout_href; ?>" class="eottae-gnb-header__btn eottae-gnb-header__btn--ghost" data-i18n="button.logout">로그아웃</a>
                    <?php } else { ?>
                    <a href="<?php echo $eottae_login_href; ?>" class="eottae-gnb-header__btn eottae-gnb-header__btn--ghost" data-i18n="button.login">로그인</a>
                    <a href="<?php echo $eottae_register_href; ?>" class="eottae-gnb-header__btn eottae-gnb-header__btn--ghost" data-i18n="button.register">회원가입</a>
                    <?php } ?>
                    <?php foreach ($eottae_talk_action_links as $link) {
                        $active = eottae_gnb_link_is_active($link['key']);
                        ?>
                    <a href="<?php echo $link['href']; ?>" class="eottae-gnb-header__btn eottae-gnb-header__btn--talk eottae-gnb-header__btn--mobile-action<?php echo $active ? ' is-active' : ''; ?>">
                        <?php echo get_text($link['label']); ?>
                    </a>
                    <?php } ?>
                    <a href="<?php echo $eottae_shop_write_url; ?>" class="eottae-gnb-header__btn eottae-gnb-header__btn--register" data-i18n="button.shop_register">업소등록</a>
                </div>
            </div>
        </div>
    </div>
    <div class="site-header__overlay eottae-gnb-header__overlay" aria-hidden="true"></div>
</header>
